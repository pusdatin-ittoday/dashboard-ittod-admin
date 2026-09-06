<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\CompetitionSubmission;
use App\Models\EventAnnouncement;
use App\Models\CompetitionTimeline;
use App\Models\EventTimeline;
use App\Models\Media;
use App\Models\Team;
use App\Models\User;
use App\Models\UserIdentity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function dashboard(): View
    {
        abort_unless($this->isAdminStaff(), 403);
        
        $user = auth()->user();
        $role = $user->role;

        $globalStats = null;
        if (in_array($role, ['superadmin', 'admin_biasa'])) {
            $globalStats = [
                'events' => Event::count(),
                'teams' => Team::count(),
                'pendingTransactions' => Team::where('is_verified', 'pending')->count(),
                'rejectedTransactions' => Team::where('is_verified', 'rejected')->count(),
            ];
        }

        $competitions = null;
        if (in_array($role, ['superadmin', 'panitia_lomba'])) {
            $query = Event::where('type', 'competition')->orderBy('title');
            if ($role === 'panitia_lomba') {
                $query->whereIn('id', $user->events->pluck('id'));
            }

            $competitions = $query->withCount([
                'teams as total_teams',
                'teams as verified_teams' => function ($q) {
                    $q->where('is_verified', 'approved');
                },
                'teams as pending_teams' => function ($q) {
                    $q->where('is_verified', 'pending');
                },
                'teams as rejected_teams' => function ($q) {
                    $q->where('is_verified', 'rejected');
                },
                'submissions as submitted_teams'
            ])->get();
        }

        return view('admin.dashboard', [
            'globalStats' => $globalStats,
            'competitions' => $competitions,
            'userRole' => $role,
        ]);
    }

    public function staff(): View
    {
        $this->ensureSuperadmin();

        return view('admin.staff.index', [
            'staffAccounts' => UserIdentity::with(['user', 'events'])
                ->whereIn('role', ['superadmin', 'admin_biasa', 'panitia_lomba'])
                ->orderBy('role')
                ->orderBy('email')
                ->get(),
            'events' => Event::where('type', 'competition')->orderBy('title')->get(),
            'canManageStaff' => auth()->user()?->role === 'superadmin',
        ]);
    }

    public function storeStaff(Request $request): RedirectResponse
    {
        $this->ensureSuperadmin();

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:191'],
            'email' => ['required', 'email', 'max:191', 'unique:user,email', 'unique:user_identity,email'],
            'role' => ['required', Rule::in(['superadmin', 'admin_biasa', 'panitia_lomba'])],
            'event_ids' => ['array'],
            'event_ids.*' => [
                'string',
                Rule::exists('event', 'id'),
            ],
        ]);

        DB::transaction(function () use ($validated, $request) {
            $id = (string) Str::uuid();

            User::create([
                'id' => $id,
                'email' => $validated['email'],
                'full_name' => $validated['full_name'],
                'is_registration_complete' => true,
            ]);

            $staff = UserIdentity::create([
                'id' => $id,
                'email' => $validated['email'],
                'provider' => 'basic',
                'hash' => Hash::make(Str::random(24)),
                'is_verified' => false,
                'role' => $validated['role'],
            ]);

            $staff->events()->sync($validated['role'] === 'panitia_lomba' ? ($validated['event_ids'] ?? []) : []);
        });

        // Send a password reset link to the newly created user
        \Illuminate\Support\Facades\Password::broker()->sendResetLink(['email' => $validated['email']]);

        return back()->with('status', 'Akun staff berhasil ditambahkan dan email pengaturan password telah dikirim.');
    }

    public function showStaff(UserIdentity $staff): JsonResponse
    {
        $this->ensureSuperadmin();
        abort_unless(in_array($staff->role, ['superadmin', 'admin_biasa', 'panitia_lomba'], true), 404);

        $staff->load(['user', 'events:id']);

        return response()->json([
            'id' => $staff->id,
            'full_name' => $staff->user?->full_name ?? '',
            'email' => $staff->email,
            'role' => $staff->role,
            'is_verified' => $staff->is_verified,
            'event_ids' => $staff->events->pluck('id')->values(),
            'update_url' => route('admin.staff.update', $staff),
        ]);
    }

    public function updateStaff(Request $request, UserIdentity $staff): RedirectResponse
    {
        $this->ensureSuperadmin();

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:191'],
            'email' => [
                'required',
                'email',
                'max:191',
                Rule::unique('user', 'email')->ignore($staff->id, 'id'),
                Rule::unique('user_identity', 'email')->ignore($staff->id, 'id'),
            ],
            'role' => ['required', Rule::in(['superadmin', 'admin_biasa', 'panitia_lomba'])],
            'event_ids' => ['array'],
            'event_ids.*' => [
                'string',
                Rule::exists('event', 'id'),
            ],
        ]);

        if ($staff->role === 'superadmin' && $validated['role'] !== 'superadmin') {
            $this->abortIfLastSuperadmin($staff);
        }

        DB::transaction(function () use ($validated, $request, $staff) {
            $staff->user()->update([
                'email' => $validated['email'],
                'full_name' => $validated['full_name'],
            ]);

            $identityPayload = [
                'email' => $validated['email'],
                'is_verified' => $request->input('is_verified'),
                'role' => $validated['role'],
            ];

            $staff->update($identityPayload);
            $staff->events()->sync($validated['role'] === 'panitia_lomba' ? ($validated['event_ids'] ?? []) : []);
        });

        return back()->with('status', 'Akun staff berhasil diperbarui.');
    }

    public function destroyStaff(UserIdentity $staff): RedirectResponse
    {
        $this->ensureSuperadmin();

        abort_if(auth()->id() === $staff->id, 403, 'Akun yang sedang login tidak bisa dihapus.');

        if ($staff->role === 'superadmin') {
            $this->abortIfLastSuperadmin($staff);
        }

        DB::transaction(function () use ($staff) {
            $user = $staff->user;

            $staff->events()->detach();
            $staff->delete();
            $user?->delete();
        });

        return back()->with('status', 'Akun staff berhasil dihapus.');
    }

    public function users(\Illuminate\Http\Request $request): View
    {
        $userRole = auth()->user()?->role;
        abort_unless(in_array($userRole, ['superadmin', 'admin_biasa', 'panitia_lomba']), 403);

        $eventsQuery = \App\Models\Event::query()->orderBy('title');
        if ($userRole === 'panitia_lomba') {
            $eventsQuery->whereIn('id', auth()->user()->events->pluck('id'));
        }
        $events = $eventsQuery->get();

        $query = UserIdentity::with('user')->where('role', 'user');

        $filterEventId = $request->input('event_id');
        
        if ($filterEventId) {
            $query->whereHas('user', function ($q) use ($filterEventId) {
                $q->whereHas('teams', function ($q2) use ($filterEventId) {
                    $q2->where('competition_id', $filterEventId);
                })->orWhereHas('events', function ($q2) use ($filterEventId) {
                    $q2->where('event_id', $filterEventId);
                });
            });
        } elseif ($userRole === 'panitia_lomba') {
            $eventIds = $events->pluck('id');
            $query->whereHas('user', function ($q) use ($eventIds) {
                $q->whereHas('teams', function ($q2) use ($eventIds) {
                    $q2->whereIn('competition_id', $eventIds);
                })->orWhereHas('events', function ($q2) use ($eventIds) {
                    $q2->whereIn('event_id', $eventIds);
                });
            });
        }

        $search = $request->input('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('full_name', 'like', "%{$search}%")
                         ->orWhere('phone_number', 'like', "%{$search}%")
                         ->orWhere('nama_sekolah', 'like', "%{$search}%");
                  });
            });
        }

        $users = $query->orderBy('email')->paginate(50)->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'events' => $events,
            'filterEventId' => $filterEventId,
            'search' => $search,
        ]);
    }

    public function transactions(\Illuminate\Http\Request $request): View
    {
        abort_unless(in_array(auth()->user()?->role, ['superadmin', 'admin_biasa']), 403);
        $query = Team::with(['event', 'paymentProof', 'members.user'])
            ->where('is_document_verified', 'approved');

        $filterCompetition = $request->input('competition_id');
        if ($filterCompetition) {
            $query->where('competition_id', $filterCompetition);
        }

        $filterStatus = $request->input('status', 'default');
        
        if ($filterStatus === 'default') {
            $query->whereIn('is_verified', ['pending', 'rejected']);
        } elseif ($filterStatus === 'pending') {
            $query->where('is_verified', 'pending');
        } elseif ($filterStatus === 'accepted') {
            $query->where('is_verified', 'approved');
        } elseif ($filterStatus === 'rejected') {
            $query->where('is_verified', 'rejected');
        }

        $search = $request->input('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('team_name', 'like', "%{$search}%")
                  ->orWhere('team_code', 'like', "%{$search}%")
                  ->orWhereHas('members.user', function ($uq) use ($search) {
                      $uq->where('full_name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $teams = $query->latest('created_at')->paginate(50)->withQueryString();

        $teams->getCollection()->transform(function (Team $team) {
            $team->payment_proof_url = $this->mediaUrl($team->paymentProof?->url);
            return $team;
        });

        $statsQuery = Team::where('is_document_verified', 'approved');
        if ($filterCompetition) {
            $statsQuery->where('competition_id', $filterCompetition);
        }
        $pendingCount = (clone $statsQuery)->where('is_verified', 'pending')->count();
        $acceptedCount = (clone $statsQuery)->where('is_verified', 'approved')->count();
        $rejectedCount = (clone $statsQuery)->where('is_verified', 'rejected')->count();

        $competitions = \App\Models\Event::orderBy('title')->get();

        return view('admin.transactions.index', compact('teams', 'pendingCount', 'acceptedCount', 'rejectedCount', 'filterStatus', 'search', 'competitions', 'filterCompetition'));
    }

    public function acceptTransaction(Team $team): RedirectResponse
    {
        abort_unless(in_array(auth()->user()?->role, ['superadmin', 'admin_biasa']), 403);

        if ($team->is_verified === 'approved') {
            return back()->with('error', 'Transaksi yang sudah diterima tidak dapat diubah.');
        }

        DB::transaction(function () use ($team) {
            $team->update([
                'is_verified' => 'approved',
                'verification_error' => null,
            ]);

            $memberUserIds = $team->members()->pluck('user_id');
            if ($memberUserIds->isNotEmpty()) {
                DB::table('event_participant')
                    ->whereIn('user_id', $memberUserIds)
                    ->where('event_id', $team->competition_id)
                    ->update([
                        'payment_verification' => 'accepted'
                    ]);
            }
        });

        return back()->with('status', "Transaksi {$team->team_name} diterima.");
    }

    public function rejectTransaction(\Illuminate\Http\Request $request, Team $team): RedirectResponse
    {
        abort_unless(in_array(auth()->user()?->role, ['superadmin', 'admin_biasa']), 403);

        if ($team->is_verified === 'approved') {
            return back()->with('error', 'Transaksi yang sudah diterima tidak dapat diubah.');
        }

        $validated = $request->validate([
            'verification_error' => ['required', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($team, $validated) {
            $team->update([
                'is_verified' => 'rejected',
                'verification_error' => $validated['verification_error'],
            ]);

            $memberUserIds = $team->members()->pluck('user_id');
            if ($memberUserIds->isNotEmpty()) {
                DB::table('event_participant')
                    ->whereIn('user_id', $memberUserIds)
                    ->where('event_id', $team->competition_id)
                    ->update([
                        'payment_verification' => 'rejected'
                    ]);
            }
        });

        return back()->with('status', "Transaksi {$team->team_name} ditolak.");
    }

    public function filesParticipants(): View
    {
        abort_unless(in_array(auth()->user()?->role, ['superadmin', 'panitia_lomba']), 403);
        
        $user = auth()->user();
        $query = Event::withCount(['teams', 'participants'])->orderBy('title');
        
        if ($user->role === 'panitia_lomba') {
            $query->whereIn('id', $user->events->pluck('id'));
        }

        return view('admin.files-participants.index', [
            'events' => $query->get(),
        ]);
    }

    public function files(): View
    {
        abort_unless(in_array(auth()->user()?->role, ['superadmin', 'panitia_lomba']), 403);
        return view('admin.files.index', [
            'recentFiles' => Media::with('uploader')
                ->whereIn('grouping', ['competition_submission', 'dokum_tahun_lalu', 'twibbon'])
                ->latest('created_at')
                ->limit(50)
                ->get(),
        ]);
    }

    public function timelines(): View
    {
        abort_unless(in_array(auth()->user()?->role, ['superadmin', 'panitia_lomba', 'admin_biasa']), 403);

        $user = auth()->user();
        $query = Event::withCount(['teams', 'participants', 'timelines'])
            ->with(['timelines' => fn ($query) => $query->where('is_registration', true)])
            ->orderBy('title');

        if ($user->role === 'panitia_lomba') {
            $query->whereIn('id', $user->events->pluck('id'));
        } elseif ($user->role === 'admin_biasa') {
            $query->where('type', 'non_competition');
        }

        $events = $query->get();
        foreach ($events as $item) {
            $item->checkAndCloseIfRegistrationExpired();
        }
        $canManageCompetitions = in_array($user->role, ['superadmin', 'admin_biasa']);

        if (!$canManageCompetitions && $events->count() === 1) {
            $events->load('submissions.team');
        }

        $competitionTimelines = CompetitionTimeline::orderBy('start_date')->get();

        return view('admin.timelines.index', [
            'events' => $events,
            'canManageCompetitions' => $canManageCompetitions,
            'canManageTimelines' => in_array($user->role, ['superadmin', 'panitia_lomba', 'admin_biasa'], true),
            'competitionTimelines' => $competitionTimelines,
        ]);
    }

    public function globalCompetitionAgenda(): View
    {
        $user = auth()->user();
        abort_unless($user?->role === 'superadmin', 403);

        $competitionTimelines = CompetitionTimeline::orderBy('start_date')->get();

        return view('admin.timelines.competition-agenda', [
            'competitionTimelines' => $competitionTimelines,
            'canManageTimelines' => true,
        ]);
    }

    public function storeGlobalTimeline(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()?->role === 'superadmin', 403);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:191'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'description' => ['nullable', 'string'],
        ]);

        CompetitionTimeline::create([
            'id' => (string) Str::uuid(),
            'title' => $validated['title'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'description' => $validated['description'] ?? null,
        ]);

        return back()->with('status', 'Agenda kompetisi global berhasil ditambahkan.');
    }

    public function updateGlobalTimeline(Request $request, CompetitionTimeline $timeline): RedirectResponse
    {
        abort_unless(auth()->user()?->role === 'superadmin', 403);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:191'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'description' => ['nullable', 'string'],
        ]);

        $timeline->update($validated);

        return back()->with('status', 'Agenda kompetisi global berhasil diperbarui.');
    }

    public function destroyGlobalTimeline(CompetitionTimeline $timeline): RedirectResponse
    {
        abort_unless(auth()->user()?->role === 'superadmin', 403);

        $timeline->delete();

        return back()->with('status', 'Agenda kompetisi global berhasil dihapus.');
    }

    public function timelineAgenda(Event $event): View
    {
        $user = auth()->user();
        abort_unless(in_array($user?->role, ['superadmin', 'panitia_lomba', 'admin_biasa']), 403);

        if ($user->role === 'admin_biasa') {
            abort_unless($event->type === 'non_competition', 403);
        } elseif ($user->role === 'panitia_lomba') {
            abort_unless($user->events->contains('id', $event->id), 403);
        }

        $event->checkAndCloseIfRegistrationExpired();

        $eventsQuery = Event::orderBy('title');
        if ($user->role === 'panitia_lomba') {
            $eventsQuery->whereIn('id', $user->events->pluck('id'));
        } elseif ($user->role === 'admin_biasa') {
            $eventsQuery->where('type', 'non_competition');
        }

        $globalTimelines = $event->type === 'competition' 
            ? CompetitionTimeline::orderBy('start_date')->get() 
            : collect();

        return view('admin.timelines.agenda', [
            'event' => $event->load(['timelines' => fn ($query) => $query->orderBy('date')])
                ->loadCount(['teams', 'participants']),
            'events' => $eventsQuery->get(),
            'globalTimelines' => $globalTimelines,
            'canManageTimelines' => in_array($user?->role, ['superadmin', 'panitia_lomba', 'admin_biasa'], true),
        ]);
    }

    public function submissions(Event $event): View
    {
        $user = auth()->user();
        abort_unless(in_array($user?->role, ['superadmin', 'panitia_lomba', 'admin_biasa']), 403);

        if ($user->role === 'admin_biasa') {
            abort_unless($event->type === 'non_competition', 403);
        } elseif ($user->role === 'panitia_lomba') {
            abort_unless($user->events->contains('id', $event->id), 403);
        }

        abort_unless($event->requires_submission, 404, 'Event ini tidak membutuhkan pengumpulan karya.');

        $event->load(['timelines']);

        $request = request();
        $query = \App\Models\Team::where('competition_id', $event->id)
            ->with(['submissions', 'members.user']);

        if ($search = $request->input('search')) {
            $search = strtolower(trim($search));
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(team_name) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(team_code) LIKE ?', ["%{$search}%"])
                  ->orWhereHas('members.user', function ($uq) use ($search) {
                      $uq->whereRaw('LOWER(full_name) LIKE ?', ["%{$search}%"])
                         ->orWhereRaw('LOWER(email) LIKE ?', ["%{$search}%"])
                         ->orWhereRaw('LOWER(nama_sekolah) LIKE ?', ["%{$search}%"]);
                  });
            });
        }

        if ($status = $request->input('status')) {
            if ($status === 'submitted') {
                $query->has('submissions');
            } elseif ($status === 'not_submitted') {
                $query->doesntHave('submissions');
            }
        }

        $teams = $query->latest('created_at')->paginate(10)->withQueryString();

        return view('admin.timelines.submissions', [
            'singleEvent' => $event,
            'teams' => $teams,
            'canManageTimelines' => in_array($user?->role, ['superadmin', 'panitia_lomba', 'admin_biasa'], true),
        ]);
    }

    public function setSubmissionDeadline(Request $request, Event $event): RedirectResponse
    {
        $user = auth()->user();
        abort_unless(in_array($user?->role, ['superadmin', 'panitia_lomba', 'admin_biasa']), 403);

        if ($user->role === 'panitia_lomba') {
            abort_unless($user->events->contains('id', $event->id), 403);
        }

        $request->validate([
            'timeline_id' => ['nullable', 'string', \Illuminate\Validation\Rule::exists('event_timeline', 'id')->where('event_id', $event->id)],
        ]);

        \DB::transaction(function () use ($event, $request) {
            // Set all timelines to false for this event
            EventTimeline::where('event_id', $event->id)->update(['is_submission' => false]);
            
            // If a timeline was selected, set it to true
            if ($request->timeline_id) {
                EventTimeline::where('id', $request->timeline_id)->update(['is_submission' => true]);
            }
        });

        return back()->with('status', 'Batas waktu pengumpulan karya berhasil diatur.');
    }

    public function setRegistrationDeadline(Request $request, Event $event): RedirectResponse
    {
        $user = auth()->user();
        abort_unless(in_array($user?->role, ['superadmin', 'panitia_lomba', 'admin_biasa']), 403);

        if ($user->role === 'admin_biasa') {
            abort_unless($event->type === 'non_competition', 403);
        } elseif ($user->role === 'panitia_lomba') {
            abort_unless($user->events->contains('id', $event->id), 403);
        }

        $request->validate([
            'timeline_id' => ['nullable', 'string', \Illuminate\Validation\Rule::exists('event_timeline', 'id')->where('event_id', $event->id)],
        ]);

        DB::transaction(function () use ($event, $request) {
            // Set all timelines to false for this event
            EventTimeline::where('event_id', $event->id)->update(['is_registration' => false]);

            // If a timeline was selected, set it to true
            if ($request->timeline_id) {
                $timeline = EventTimeline::where('id', $request->timeline_id)->first();
                if ($timeline) {
                    $timeline->update(['is_registration' => true]);
                    $startDate = $timeline->end_date ? $timeline->date : null;
                    $deadline = $timeline->end_date ?? $timeline->date;
                    $now = now();
                    if ($startDate && $now->lessThan($startDate)) {
                        $event->update(['is_active' => false]);
                    } elseif ($deadline && $now->greaterThan($deadline)) {
                        $event->update(['is_active' => false]);
                    } else {
                        $event->update(['is_active' => true]);
                    }
                }
            }
        });

        return back()->with('status', 'Pengaturan timeline pendaftaran berhasil disimpan.');
    }

    public function destroySubmission(Event $event, string $team_id): RedirectResponse
    {
        $user = auth()->user();
        abort_unless(in_array($user?->role, ['superadmin', 'panitia_lomba', 'admin_biasa']), 403);

        if ($user->role === 'panitia_lomba') {
            abort_unless($user->events->contains('id', $event->id), 403);
        }

        $submission = CompetitionSubmission::where('competition_id', $event->id)
            ->where('team_id', $team_id)
            ->firstOrFail();

        CompetitionSubmission::where('competition_id', $event->id)
            ->where('team_id', $team_id)
            ->delete();

        return back()->with('status', 'Data pengumpulan karya berhasil dihapus.');
    }

    public function storeCompetition(Request $request): RedirectResponse
    {
        $this->ensureSuperadminOrAdminKeuangan();

        $validated = $this->validateCompetition($request);
        if (auth()->user()?->role === 'admin_biasa') {
            abort_if($validated['type'] !== 'non_competition', 403, 'Admin Biasa hanya dapat membuat event non-kompetisi.');
        }
        
        $logoUrl = null;
        if ($request->hasFile('logo')) {
            $disk = config('filesystems.default') === 'local' ? 'public' : config('filesystems.default');
            $path = $request->file('logo')->store('events/logos', $disk);
            if ($path) {
                $logoUrl = Storage::disk($disk)->url($path);
            }
        }
        unset($validated['logo']);

        $regMode = $request->input('reg_timeline_mode', 'none');
        $regTimelineData = null;
        $isActive = true;

        if ($regMode === 'global' && $validated['type'] === 'competition') {
            $regValidated = $request->validate([
                'global_timeline_id' => ['required', 'string', Rule::exists('competition_timeline', 'id')],
            ]);
            $globalTimeline = CompetitionTimeline::findOrFail($regValidated['global_timeline_id']);
            $startDate = Carbon::parse($globalTimeline->start_date);
            $endDate = Carbon::parse($globalTimeline->end_date);
            $regTimelineData = [
                'title' => $globalTimeline->title,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'description' => $globalTimeline->description,
            ];
        } elseif ($regMode === 'custom') {
            $regValidated = $request->validate([
                'reg_title' => ['nullable', 'string', 'max:191'],
                'reg_start_date' => ['required', 'date'],
                'reg_end_date' => ['required', 'date', 'after_or_equal:reg_start_date'],
            ]);
            $startDate = Carbon::parse($regValidated['reg_start_date']);
            $endDate = Carbon::parse($regValidated['reg_end_date']);
            $regTimelineData = [
                'title' => $regValidated['reg_title'] ?: 'Pendaftaran',
                'start_date' => $startDate,
                'end_date' => $endDate,
                'description' => null,
            ];
        }

        if ($regTimelineData) {
            $now = Carbon::now();
            if ($now->lessThan($regTimelineData['start_date'])) {
                $isActive = false;
            } elseif ($now->greaterThan($regTimelineData['end_date'])) {
                $isActive = false;
            } else {
                $isActive = true;
            }
        }

        $eventId = (string) Str::uuid();
        DB::transaction(function () use ($validated, $eventId, $logoUrl, $isActive, $regTimelineData) {
            Event::create([
                ...$validated,
                'id' => $eventId,
                'slug' => Str::slug($validated['title']),
                'is_active' => $isActive,
                'logo_url' => $logoUrl,
            ]);

            if ($regTimelineData) {
                EventTimeline::create([
                    'id' => (string) Str::uuid(),
                    'event_id' => $eventId,
                    'title' => $regTimelineData['title'],
                    'date' => $regTimelineData['start_date'],
                    'end_date' => $regTimelineData['end_date'],
                    'description' => $regTimelineData['description'] ?? null,
                    'is_registration' => true,
                    'is_submission' => false,
                ]);
            }
        });

        return back()->with('status', 'Event berhasil ditambahkan.');
    }

    public function updateCompetition(Request $request, Event $event): RedirectResponse
    {
        $this->ensureSuperadminOrAdminKeuangan($event);
        $this->abortUnlessCompetition($event);

        $validated = $this->validateCompetition($request);
        if (auth()->user()?->role === 'admin_biasa') {
            abort_if($validated['type'] !== 'non_competition', 403, 'Admin Biasa tidak dapat mengubah event menjadi kompetisi.');
        }
        
        if ($request->hasFile('logo')) {
            $disk = config('filesystems.default') === 'local' ? 'public' : config('filesystems.default');
            $path = $request->file('logo')->store('events/logos', $disk);
            if ($path) {
                $validated['logo_url'] = Storage::disk($disk)->url($path);
            }
        }
        unset($validated['logo']);

        $event->update($validated);

        return back()->with('status', 'Kompetisi berhasil diperbarui.');
    }

    public function updatePanitiaDetails(Request $request, Event $event): RedirectResponse
    {
        abort_unless(in_array(auth()->user()?->role, ['superadmin', 'panitia_lomba']), 403);
        $this->abortUnlessCompetition($event);
        $this->abortIfUnassignedPanitia($event->id);

        $validated = $request->validate([
            'description' => ['sometimes', 'required', 'string', 'max:2000'],
            'guide_book_url' => ['sometimes', 'required', 'url', 'max:500'],
            'whatsapp_group_link' => ['sometimes', 'nullable', 'url', 'max:500'],
            'external_platform_link' => ['sometimes', 'nullable', 'url', 'max:500'],
            'contact_person1' => ['nullable', 'string', 'max:191'],
            'contact_person2' => ['nullable', 'string', 'max:191'],
            'submission_fields' => ['nullable', 'string'],
            'max_member' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:10'],
        ]);

        if (array_key_exists('submission_fields', $validated)) {
            $validated['submission_fields'] = json_decode($validated['submission_fields'], true);
        }

        $event->update($validated);

        return back()->with('status', 'Detail kompetisi berhasil diperbarui.');
    }

    public function destroyCompetition(Event $event): RedirectResponse
    {
        $this->ensureSuperadminOrAdminKeuangan($event);
        $this->abortUnlessCompetition($event);

        if ($event->teams()->exists()) {
            $event->update(['is_active' => false]);

            return back()->with('status', 'Kompetisi memiliki tim terdaftar, jadi dinonaktifkan tanpa menghapus data.');
        }

        DB::transaction(function () use ($event) {
            $event->timelines()->delete();
            $event->delete();
        });

        return back()->with('status', 'Kompetisi berhasil dihapus.');
    }

    public function toggleCompetitionStatus(Event $event): RedirectResponse
    {
        $this->ensureSuperadminOrAdminKeuangan($event);
        $this->abortUnlessCompetition($event);

        $event->update([
            'is_active' => ! $event->is_active,
        ]);

        return back()->with('status', $event->is_active
            ? 'Kompetisi berhasil diaktifkan kembali.'
            : 'Kompetisi berhasil dinonaktifkan.');
    }

    public function storeTimeline(Request $request): RedirectResponse
    {
        $this->ensureCompetitionTimelineManager();

        $validated = $this->validateCompetitionTimeline($request);
        $this->abortIfUnassignedPanitia($validated['event_id']);

        DB::transaction(function () use ($validated) {
            if (!empty($validated['is_submission'])) {
                EventTimeline::where('event_id', $validated['event_id'])->update(['is_submission' => false]);
            }
            if (!empty($validated['is_registration'])) {
                EventTimeline::where('event_id', $validated['event_id'])->update(['is_registration' => false]);
                $startDate = !empty($validated['end_date']) && !empty($validated['date']) ? Carbon::parse($validated['date']) : null;
                $deadline = !empty($validated['end_date']) ? Carbon::parse($validated['end_date']) : (!empty($validated['date']) ? Carbon::parse($validated['date']) : null);
                $now = now();
                if ($startDate && $now->lessThan($startDate)) {
                    Event::where('id', $validated['event_id'])->update(['is_active' => false]);
                } elseif ($deadline && $now->greaterThan($deadline)) {
                    Event::where('id', $validated['event_id'])->update(['is_active' => false]);
                } else {
                    Event::where('id', $validated['event_id'])->update(['is_active' => true]);
                }
            }
            EventTimeline::create($validated);
        });

        return back()->with('status', 'Agenda kegiatan berhasil ditambahkan.');
    }

    public function updateTimeline(Request $request, EventTimeline $timeline): RedirectResponse
    {
        $this->ensureCompetitionTimelineManager();
        $this->abortIfUnassignedPanitia($timeline->event_id);

        $validated = $this->validateCompetitionTimeline($request);
        $this->abortIfUnassignedPanitia($validated['event_id']);

        DB::transaction(function () use ($validated, $timeline) {
            if (!empty($validated['is_submission'])) {
                EventTimeline::where('event_id', $validated['event_id'])->update(['is_submission' => false]);
            }
            if (!empty($validated['is_registration'])) {
                EventTimeline::where('event_id', $validated['event_id'])->update(['is_registration' => false]);
                $startDate = !empty($validated['end_date']) && !empty($validated['date']) ? Carbon::parse($validated['date']) : null;
                $deadline = !empty($validated['end_date']) ? Carbon::parse($validated['end_date']) : (!empty($validated['date']) ? Carbon::parse($validated['date']) : null);
                $now = now();
                if ($startDate && $now->lessThan($startDate)) {
                    Event::where('id', $validated['event_id'])->update(['is_active' => false]);
                } elseif ($deadline && $now->greaterThan($deadline)) {
                    Event::where('id', $validated['event_id'])->update(['is_active' => false]);
                } else {
                    Event::where('id', $validated['event_id'])->update(['is_active' => true]);
                }
            }
            $timeline->update($validated);
        });

        return back()->with('status', 'Agenda kegiatan berhasil diperbarui.');
    }

    public function destroyTimeline(EventTimeline $timeline): RedirectResponse
    {
        $this->ensureCompetitionTimelineManager();
        $this->abortIfUnassignedPanitia($timeline->event_id);

        $timeline->delete();

        return back()->with('status', 'Agenda kegiatan berhasil dihapus.');
    }

    public function announcements(): View
    {
        abort_unless($this->isAdminStaff(), 403);
        
        $user = auth()->user();
        $eventsQuery = Event::orderBy('title');
        $announcementsQuery = EventAnnouncement::with(['event', 'author'])
            ->orderBy('priority', 'desc')
            ->orderBy('is_pinned', 'desc')
            ->latest('created_at');

        if ($user->role === 'panitia_lomba') {
            $assignedEventIds = $user->events->pluck('id');
            $eventsQuery->whereIn('id', $assignedEventIds);
            $announcementsQuery->whereIn('event_id', $assignedEventIds);
        }

        return view('admin.announcements.index', [
            'events' => $eventsQuery->get(),
            'announcements' => $announcementsQuery->get(),
        ]);
    }

    public function storeAnnouncement(Request $request): RedirectResponse
    {
        abort_unless($this->isAdminStaff(), 403);

        $user = auth()->user();
        $eventRules = ['nullable', 'string', Rule::exists('event', 'id')];
        
        if ($user->role === 'panitia_lomba') {
            $eventRules = ['required', 'string', Rule::exists('event', 'id'), Rule::in($user->events->pluck('id')->toArray())];
        }

        $validated = $request->validate([
            'event_id' => $eventRules,
            'title' => ['required', 'string', 'max:191'],
            'description' => ['required', 'string'],
            'is_pinned' => ['nullable', 'boolean'],
            'priority' => ['nullable', 'integer', 'min:0', 'max:999'],
        ]);

        $validated['is_pinned'] = (bool) $request->input('is_pinned', false);
        $validated['priority'] = (int) $request->input('priority', 0);

        EventAnnouncement::create([
            'id' => (string) Str::uuid(),
            'author_id' => auth()->id(),
            ...$validated,
        ]);

        return back()->with('status', 'Pengumuman berhasil ditambahkan.');
    }

    public function updateAnnouncement(Request $request, EventAnnouncement $announcement): RedirectResponse
    {
        abort_unless($this->isAdminStaff(), 403);
        
        $user = auth()->user();
        $eventRules = ['nullable', 'string', Rule::exists('event', 'id')];
        
        if ($user->role === 'panitia_lomba') {
            abort_unless($announcement->event_id && $user->events->contains('id', $announcement->event_id), 403);
            $eventRules = ['required', 'string', Rule::exists('event', 'id'), Rule::in($user->events->pluck('id')->toArray())];
        }

        $validated = $request->validate([
            'event_id' => $eventRules,
            'title' => ['required', 'string', 'max:191'],
            'description' => ['required', 'string'],
            'is_pinned' => ['nullable', 'boolean'],
            'priority' => ['nullable', 'integer', 'min:0', 'max:999'],
        ]);

        $validated['is_pinned'] = (bool) $request->input('is_pinned', false);
        $validated['priority'] = (int) $request->input('priority', 0);

        $announcement->update($validated);

        return back()->with('status', 'Pengumuman berhasil diperbarui.');
    }

    public function destroyAnnouncement(EventAnnouncement $announcement): RedirectResponse
    {
        abort_unless($this->isAdminStaff(), 403);

        if (auth()->user()->role === 'panitia_lomba') {
            abort_unless(auth()->user()->events->contains('id', $announcement->event_id), 403);
        }

        $announcement->delete();

        return back()->with('status', 'Pengumuman berhasil dihapus.');
    }

    public function pinAnnouncement(EventAnnouncement $announcement): RedirectResponse
    {
        abort_unless($this->isAdminStaff(), 403);

        if (auth()->user()->role === 'panitia_lomba') {
            abort_unless(auth()->user()->events->contains('id', $announcement->event_id), 403);
        }

        $announcement->update([
            'is_pinned' => !$announcement->is_pinned,
        ]);

        return back()->with('status', $announcement->is_pinned ? 'Pengumuman berhasil disematkan.' : 'Sematkan pengumuman berhasil dilepas.');
    }

    private function mediaUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://', '/'])) {
            return $path;
        }

        $r2Public = env('R2_PUBLIC');
        if ($r2Public) {
            return rtrim($r2Public, '/') . '/' . ltrim($path, '/');
        }

        return Storage::disk('public')->url($path);
    }

    private function ensureSuperadmin(): void
    {
        abort_unless(auth()->user()?->role === 'superadmin', 403);
    }

    private function ensureSuperadminOrAdminKeuangan(?Event $event = null): void
    {
        $role = auth()->user()?->role;
        abort_unless(in_array($role, ['superadmin', 'admin_biasa']), 403);
        
        if ($role === 'admin_biasa' && $event) {
            abort_if($event->type !== 'non_competition', 403, 'Admin Biasa hanya dapat mengelola event non-kompetisi.');
        }
    }

    private function ensureCompetitionTimelineManager(): void
    {
        abort_unless(in_array(auth()->user()?->role, ['superadmin', 'panitia_lomba', 'admin_biasa'], true), 403);
    }

    private function isAdminStaff(): bool
    {
        return in_array(auth()->user()?->role, ['superadmin', 'admin_biasa', 'panitia_lomba'], true);
    }

    private function abortIfUnassignedPanitia(string $eventId): void
    {
        $user = auth()->user();
        if ($user?->role === 'panitia_lomba') {
            abort_unless($user->events->contains('id', $eventId), 403);
        } elseif ($user?->role === 'admin_biasa') {
            $event = Event::find($eventId);
            abort_unless($event && $event->type === 'non_competition', 403);
        }
    }

    private function abortIfLastSuperadmin(UserIdentity $staff): void
    {
        $otherSuperadmins = UserIdentity::where('role', 'superadmin')
            ->where('id', '!=', $staff->id)
            ->exists();

        abort_unless($otherSuperadmins, 403, 'Minimal harus ada satu akun superadmin aktif.');
    }

    private function validateCompetitionTimeline(Request $request): array
    {
        $validated = $request->validate([
            'event_id' => [
                'required',
                'string',
                Rule::exists('event', 'id'),
            ],
            'title' => ['required', 'string', 'max:191'],
            'date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:date'],
            'is_submission' => ['nullable', 'boolean'],
            'is_registration' => ['nullable', 'boolean'],
        ]);

        $validated['is_submission'] = $request->has('is_submission');
        $validated['is_registration'] = $request->has('is_registration');

        return $validated;
    }

    private function validateCompetition(Request $request): array
    {
        if ($request->input('type') === 'non_competition') {
            $request->merge([
                'price' => 0,
                'participation_type' => 'individual',
            ]);
        } else if ($request->input('type') === 'competition') {
            $request->merge([
                'method' => 'offline',
            ]);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:191'],
            'type' => ['required', 'string', \Illuminate\Validation\Rule::in(['competition', 'non_competition'])],
            'description' => ['nullable', 'string', 'max:2000'],
            'guide_book_url' => ['nullable', 'url', 'max:500'],
            'whatsapp_group_link' => ['nullable', 'url', 'max:500'],
            'external_platform_link' => ['nullable', 'url', 'max:500'],
            'price' => ['nullable', 'integer', 'min:0'],
            'participation_type' => ['required', Rule::in(['individual', 'team'])],
            'is_active' => ['sometimes', 'boolean'],
            'requires_submission' => ['sometimes', 'boolean'],
            'contact_person1' => ['nullable', 'string', 'max:191'],
            'contact_person2' => ['nullable', 'string', 'max:191'],
            'max_noncompetition_participant' => ['nullable', 'integer', 'min:1'],
            'method' => ['required', 'string', Rule::in(['online', 'offline'])],
            'logo' => [$request->isMethod('post') ? 'required_if:type,competition' : 'nullable', 'image', 'max:2048'],
            'max_member' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:10'],
        ]);

        if ($validated['participation_type'] === 'individual') {
            $validated['max_member'] = 1;
        } elseif (empty($validated['max_member'])) {
            $validated['max_member'] = 3;
        }

        return $validated;
    }

    public function togglePinAnnouncement(EventAnnouncement $announcement): RedirectResponse
    {
        abort_unless($this->isAdminStaff(), 403);

        if (auth()->user()->role === 'panitia_lomba') {
            abort_unless(auth()->user()->events->contains('id', $announcement->event_id), 403);
        }

        $announcement->update(['is_pinned' => !$announcement->is_pinned]);

        return back()->with('status', $announcement->is_pinned
            ? 'Pengumuman berhasil di-pin.'
            : 'Pin pengumuman berhasil dilepas.');
    }

    private function abortUnlessCompetition(?Event $event): void
    {
        // No longer aborting for non-competition events so they can be managed uniformly
    }

    public function destroyUser(string $id): RedirectResponse
    {
        $userIdentity = UserIdentity::findOrFail($id);
        $this->ensureSuperadmin();

        abort_if(auth()->id() === $userIdentity->id, 403, 'Akun yang sedang login tidak bisa dihapus.');

        try {
            DB::transaction(function () use ($userIdentity) {
                $user = clone $userIdentity->user;

                if ($user) {
                    DB::table('team_member')->where('user_id', $user->id)->delete();
                    DB::table('event_participant')->where('user_id', $user->id)->delete();
                    DB::table('event_staff')->where('user_id', $user->id)->delete();
                    
                    // user_identity memiliki foreign key yang mengarah ke user dengan onDelete('restrict')
                    // sehingga kita harus menghapus user_identity terlebih dahulu.
                    $userIdentity->delete();
                    
                    // Terakhir, kita baru bisa menghapus user.
                    // Jika ada foreign key lain (seperti media atau event_announcement) yang restrict,
                    // ini akan melemparkan QueryException.
                    $user->delete();
                } else {
                    $userIdentity->delete();
                }
            });
        } catch (\Illuminate\Database\QueryException $e) {
            return back()->with('error', 'Akun tidak dapat dihapus karena masih terkait dengan data penting lainnya (misal: riwayat upload file, ketua tim, atau pengumuman).');
        }

        return back()->with('status', 'Akun pengguna berhasil dihapus secara permanen.');
    }
}
