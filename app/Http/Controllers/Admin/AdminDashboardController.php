<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventAnnouncement;
use App\Models\EventTimeline;
use App\Models\Media;
use App\Models\Team;
use App\Models\User;
use App\Models\UserIdentity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        if (in_array($role, ['superadmin', 'admin_keuangan'])) {
            $globalStats = [
                'events' => Event::count(),
                'teams' => Team::count(),
                'pendingTransactions' => Team::where('is_verified', 'pending')->count(),
                'rejectedTransactions' => Team::where('is_verified', 'rejected')->count(),
            ];
        }

        $competitions = null;
        if (in_array($role, ['superadmin', 'panitia'])) {
            $query = Event::where('type', 'competition')->orderBy('title');
            if ($role === 'panitia') {
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
                ->whereIn('role', ['superadmin', 'admin_keuangan', 'panitia'])
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
            'role' => ['required', Rule::in(['superadmin', 'admin_keuangan', 'panitia'])],
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

            $staff->events()->sync($validated['role'] === 'panitia' ? ($validated['event_ids'] ?? []) : []);
        });

        // Send a password reset link to the newly created user
        \Illuminate\Support\Facades\Password::broker()->sendResetLink(['email' => $validated['email']]);

        return back()->with('status', 'Akun staff berhasil ditambahkan dan email pengaturan password telah dikirim.');
    }

    public function showStaff(UserIdentity $staff): JsonResponse
    {
        $this->ensureSuperadmin();
        abort_unless(in_array($staff->role, ['superadmin', 'admin_keuangan', 'panitia'], true), 404);

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
            'role' => ['required', Rule::in(['superadmin', 'admin_keuangan', 'panitia'])],
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
            $staff->events()->sync($validated['role'] === 'panitia' ? ($validated['event_ids'] ?? []) : []);
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
        abort_unless(in_array($userRole, ['superadmin', 'admin_keuangan', 'panitia']), 403);

        $eventsQuery = \App\Models\Event::query()->orderBy('title');
        if ($userRole === 'panitia') {
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
        } elseif ($userRole === 'panitia') {
            $eventIds = $events->pluck('id');
            $query->whereHas('user', function ($q) use ($eventIds) {
                $q->whereHas('teams', function ($q2) use ($eventIds) {
                    $q2->whereIn('competition_id', $eventIds);
                })->orWhereHas('events', function ($q2) use ($eventIds) {
                    $q2->whereIn('event_id', $eventIds);
                });
            });
        }

        $users = $query->orderBy('email')->paginate(50)->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'events' => $events,
            'filterEventId' => $filterEventId,
        ]);
    }

    public function transactions(\Illuminate\Http\Request $request): View
    {
        abort_unless(in_array(auth()->user()?->role, ['superadmin', 'admin_keuangan']), 403);
        $query = Team::with(['event', 'paymentProof', 'members.user'])
            ->where('is_document_verified', 'approved');

        $filterStatus = $request->input('status', 'pending');
        
        if ($filterStatus === 'pending') {
            $query->where('is_verified', 'pending');
        } elseif ($filterStatus === 'approved') {
            $query->where('is_verified', 'approved');
        } elseif ($filterStatus === 'rejected') {
            $query->where('is_verified', 'rejected');
        }

        $teams = $query->latest('created_at')->paginate(50)->withQueryString();

        $teams->getCollection()->transform(function (Team $team) {
            $team->payment_proof_url = $this->mediaUrl($team->paymentProof?->url);
            return $team;
        });

        $statsQuery = Team::where('is_document_verified', 'approved');
        $pendingCount = (clone $statsQuery)->where('is_verified', 'pending')->count();
        $acceptedCount = (clone $statsQuery)->where('is_verified', 'approved')->count();
        $rejectedCount = (clone $statsQuery)->where('is_verified', 'rejected')->count();

        return view('admin.transactions.index', compact('teams', 'pendingCount', 'acceptedCount', 'rejectedCount', 'filterStatus'));
    }

    public function acceptTransaction(Team $team): RedirectResponse
    {
        abort_unless(in_array(auth()->user()?->role, ['superadmin', 'admin_keuangan']), 403);

        if ($team->is_verified === 'approved') {
            return back()->with('error', 'Transaksi yang sudah diterima tidak dapat diubah.');
        }

        $team->update([
            'is_verified' => 'approved',
            'verification_error' => null,
        ]);

        return back()->with('status', "Transaksi {$team->team_name} diterima.");
    }

    public function rejectTransaction(\Illuminate\Http\Request $request, Team $team): RedirectResponse
    {
        abort_unless(in_array(auth()->user()?->role, ['superadmin', 'admin_keuangan']), 403);

        if ($team->is_verified === 'approved') {
            return back()->with('error', 'Transaksi yang sudah diterima tidak dapat diubah.');
        }

        $validated = $request->validate([
            'verification_error' => ['required', 'string', 'max:1000'],
        ]);

        $team->update([
            'is_verified' => 'rejected',
            'verification_error' => $validated['verification_error'],
        ]);

        return back()->with('status', "Transaksi {$team->team_name} ditolak.");
    }

    public function filesParticipants(): View
    {
        abort_unless(in_array(auth()->user()?->role, ['superadmin', 'panitia']), 403);
        
        $user = auth()->user();
        $query = Event::withCount(['teams', 'participants'])->orderBy('title');
        
        if ($user->role === 'panitia') {
            $query->whereIn('id', $user->events->pluck('id'));
        }

        return view('admin.files-participants.index', [
            'events' => $query->get(),
        ]);
    }

    public function files(): View
    {
        abort_unless(in_array(auth()->user()?->role, ['superadmin', 'panitia']), 403);
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
        abort_unless(in_array(auth()->user()?->role, ['superadmin', 'panitia', 'admin_keuangan']), 403);

        $user = auth()->user();
        $query = Event::withCount(['teams', 'timelines'])
            ->orderBy('title');

        if ($user->role === 'panitia') {
            $query->whereIn('id', $user->events->pluck('id'));
        } elseif ($user->role === 'admin_keuangan') {
            $query->where('type', 'non_competition');
        }

        $events = $query->get();
        $canManageCompetitions = in_array($user->role, ['superadmin', 'admin_keuangan']);

        if (!$canManageCompetitions && $events->count() === 1) {
            $events->load('submissions.team');
        }

        return view('admin.timelines.index', [
            'events' => $events,
            'canManageCompetitions' => $canManageCompetitions,
            'canManageTimelines' => in_array($user->role, ['superadmin', 'panitia', 'admin_keuangan'], true),
        ]);
    }

    public function timelineAgenda(Event $event): View
    {
        abort_unless(in_array(auth()->user()?->role, ['superadmin', 'panitia']), 403);
        $this->abortUnlessCompetition($event);

        if (auth()->user()->role === 'panitia') {
            abort_unless(auth()->user()->events->contains('id', $event->id), 403);
        }

        $eventsQuery = Event::orderBy('title');
        if (auth()->user()->role === 'panitia') {
            $eventsQuery->whereIn('id', auth()->user()->events->pluck('id'));
        }

        return view('admin.timelines.agenda', [
            'event' => $event->load(['timelines' => fn ($query) => $query->orderBy('date')])
                ->loadCount('teams'),
            'events' => $eventsQuery->get(),
            'canManageTimelines' => in_array(auth()->user()?->role, ['superadmin', 'panitia'], true),
        ]);
    }

    public function storeCompetition(Request $request): RedirectResponse
    {
        $this->ensureSuperadminOrAdminKeuangan();

        $validated = $this->validateCompetition($request);
        if (auth()->user()?->role === 'admin_keuangan') {
            abort_if($validated['type'] !== 'non_competition', 403, 'Admin Keuangan hanya dapat membuat event non-kompetisi.');
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

        Event::create([
            ...$validated,
            'id' => (string) Str::uuid(),
            'is_active' => true,
            'logo_url' => $logoUrl,
            'max_noncompetition_participant' => null,
        ]);

        return back()->with('status', 'Kompetisi berhasil ditambahkan.');
    }

    public function updateCompetition(Request $request, Event $event): RedirectResponse
    {
        $this->ensureSuperadminOrAdminKeuangan($event);
        $this->abortUnlessCompetition($event);

        $validated = $this->validateCompetition($request);
        if (auth()->user()?->role === 'admin_keuangan') {
            abort_if($validated['type'] !== 'non_competition', 403, 'Admin Keuangan tidak dapat mengubah event menjadi kompetisi.');
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
        abort_unless(in_array(auth()->user()?->role, ['superadmin', 'panitia']), 403);
        $this->abortUnlessCompetition($event);
        $this->abortIfUnassignedPanitia($event->id);

        $validated = $request->validate([
            'description' => ['sometimes', 'required', 'string', 'max:2000'],
            'guide_book_url' => ['sometimes', 'required', 'url', 'max:500'],
            'whatsapp_group_link' => ['sometimes', 'nullable', 'url', 'max:500'],
            'contact_person1' => ['nullable', 'string', 'max:191'],
            'contact_person2' => ['nullable', 'string', 'max:191'],
        ]);

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

        EventTimeline::create($validated);

        return back()->with('status', 'Agenda kompetisi berhasil ditambahkan.');
    }

    public function updateTimeline(Request $request, EventTimeline $timeline): RedirectResponse
    {
        $this->ensureCompetitionTimelineManager();
        $this->abortUnlessCompetitionTimeline($timeline);
        $this->abortIfUnassignedPanitia($timeline->event_id);

        $validated = $this->validateCompetitionTimeline($request);
        $this->abortIfUnassignedPanitia($validated['event_id']);

        $timeline->update($validated);

        return back()->with('status', 'Agenda kompetisi berhasil diperbarui.');
    }

    public function destroyTimeline(EventTimeline $timeline): RedirectResponse
    {
        $this->ensureCompetitionTimelineManager();
        $this->abortUnlessCompetitionTimeline($timeline);
        $this->abortIfUnassignedPanitia($timeline->event_id);

        $timeline->delete();

        return back()->with('status', 'Agenda kompetisi berhasil dihapus.');
    }

    public function announcements(): View
    {
        abort_unless($this->isAdminStaff(), 403);
        
        $user = auth()->user();
        $eventsQuery = Event::orderBy('title');
        $announcementsQuery = EventAnnouncement::with(['event', 'author'])->latest('created_at');

        if ($user->role === 'panitia') {
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
        
        if ($user->role === 'panitia') {
            $eventRules = ['required', 'string', Rule::exists('event', 'id'), Rule::in($user->events->pluck('id')->toArray())];
        }

        $validated = $request->validate([
            'event_id' => $eventRules,
            'title' => ['required', 'string', 'max:191'],
            'description' => ['required', 'string'],
        ]);

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
        
        if ($user->role === 'panitia') {
            abort_unless($announcement->event_id && $user->events->contains('id', $announcement->event_id), 403);
            $eventRules = ['required', 'string', Rule::exists('event', 'id'), Rule::in($user->events->pluck('id')->toArray())];
        }

        $validated = $request->validate([
            'event_id' => $eventRules,
            'title' => ['required', 'string', 'max:191'],
            'description' => ['required', 'string'],
        ]);

        $announcement->update($validated);

        return back()->with('status', 'Pengumuman berhasil diperbarui.');
    }

    public function destroyAnnouncement(EventAnnouncement $announcement): RedirectResponse
    {
        abort_unless($this->isAdminStaff(), 403);

        if (auth()->user()->role === 'panitia') {
            abort_unless(auth()->user()->events->contains('id', $announcement->event_id), 403);
        }

        $announcement->delete();

        return back()->with('status', 'Pengumuman berhasil dihapus.');
    }

    private function mediaUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://', '/'])) {
            return $path;
        }

        return Storage::url($path);
    }

    private function ensureSuperadmin(): void
    {
        abort_unless(auth()->user()?->role === 'superadmin', 403);
    }

    private function ensureSuperadminOrAdminKeuangan(?Event $event = null): void
    {
        $role = auth()->user()?->role;
        abort_unless(in_array($role, ['superadmin', 'admin_keuangan']), 403);
        
        if ($role === 'admin_keuangan' && $event) {
            abort_if($event->type !== 'non_competition', 403, 'Admin Keuangan hanya dapat mengelola event non-kompetisi.');
        }
    }

    private function ensureCompetitionTimelineManager(): void
    {
        abort_unless(in_array(auth()->user()?->role, ['superadmin', 'panitia'], true), 403);
    }

    private function isAdminStaff(): bool
    {
        return in_array(auth()->user()?->role, ['superadmin', 'admin_keuangan', 'panitia'], true);
    }

    private function abortIfUnassignedPanitia(string $eventId): void
    {
        if (auth()->user()?->role === 'panitia') {
            abort_unless(auth()->user()->events->contains('id', $eventId), 403);
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
        return $request->validate([
            'event_id' => [
                'required',
                'string',
                Rule::exists('event', 'id')->where(fn ($query) => $query->where('type', 'competition')),
            ],
            'title' => ['required', 'string', 'max:191'],
            'date' => ['required', 'date'],
        ]);
    }

    private function abortUnlessCompetitionTimeline(EventTimeline $timeline): void
    {
        $this->abortUnlessCompetition($timeline->event);
    }

    private function validateCompetition(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:191'],
            'type' => ['required', 'string', \Illuminate\Validation\Rule::in(['competition', 'non_competition'])],
            'description' => ['nullable', 'string', 'max:2000'],
            'guide_book_url' => ['nullable', 'url', 'max:500'],
            'whatsapp_group_link' => ['nullable', 'url', 'max:500'],
            'price' => ['nullable', 'integer', 'min:0'],
            'participation_type' => ['required', Rule::in(['individual', 'team'])],
            'is_active' => ['sometimes', 'boolean'],
            'requires_submission' => ['sometimes', 'boolean'],
            'contact_person1' => ['nullable', 'string', 'max:191'],
            'contact_person2' => ['nullable', 'string', 'max:191'],
            'logo' => [$request->isMethod('post') ? 'required_if:type,competition' : 'nullable', 'image', 'max:2048'],
        ]);
    }

    private function abortUnlessCompetition(?Event $event): void
    {
        // No longer aborting for non-competition events so they can be managed uniformly
    }
}
