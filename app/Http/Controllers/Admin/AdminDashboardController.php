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
        return view('admin.dashboard', [
            'stats' => [
                'events' => Event::count(),
                'teams' => Team::count(),
                'pendingTransactions' => Team::where('is_verified', false)
                    ->whereNull('verification_error')
                    ->count(),
                'rejectedTransactions' => Team::where('is_verified', false)
                    ->whereNotNull('verification_error')
                    ->count(),
            ],
        ]);
    }

    public function staff(): View
    {
        abort_unless($this->isAdminStaff(), 403);

        return view('admin.staff.index', [
            'staffAccounts' => UserIdentity::with(['user', 'events'])
                ->whereIn('role', ['superadmin', 'admin_keuangan', 'panitia'])
                ->orderBy('role')
                ->orderBy('email')
                ->get(),
            'events' => Event::orderBy('title')->get(),
            'canManageStaff' => auth()->user()?->role === 'superadmin',
        ]);
    }

    public function storeStaff(Request $request): RedirectResponse
    {
        $this->ensureSuperadmin();

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:191'],
            'email' => ['required', 'email', 'max:191', 'unique:user,email', 'unique:user_identity,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
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
                'hash' => Hash::make($validated['password']),
                'is_verified' => $request->boolean('is_verified'),
                'role' => $validated['role'],
            ]);

            $staff->events()->sync($validated['role'] === 'panitia' ? ($validated['event_ids'] ?? []) : []);
        });

        return back()->with('status', 'Akun staff berhasil ditambahkan.');
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
            'is_verified' => (bool) $staff->is_verified,
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
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
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
                'is_verified' => $request->boolean('is_verified'),
                'role' => $validated['role'],
            ];

            if (! empty($validated['password'])) {
                $identityPayload['hash'] = Hash::make($validated['password']);
            }

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

    public function transactions(): View
    {
        abort_unless(in_array(auth()->user()?->role, ['superadmin', 'admin_keuangan']), 403);
        $teams = Team::with(['event', 'paymentProof', 'members'])
            ->where('is_document_verified', 1)
            ->latest('created_at')
            ->get()
            ->map(function (Team $team) {
                $team->payment_proof_url = $this->mediaUrl($team->paymentProof?->url);

                return $team;
            });

        return view('admin.transactions.index', [
            'teams' => $teams,
        ]);
    }

    public function acceptTransaction(Team $team): RedirectResponse
    {
        abort_unless(in_array(auth()->user()?->role, ['superadmin', 'admin_keuangan']), 403);

        $team->update([
            'is_verified' => true,
            'verification_error' => null,
        ]);

        return back()->with('status', "Transaksi {$team->team_name} diterima.");
    }

    public function rejectTransaction(Request $request, Team $team): RedirectResponse
    {
        abort_unless(in_array(auth()->user()?->role, ['superadmin', 'admin_keuangan']), 403);

        $validated = $request->validate([
            'verification_error' => ['required', 'string', 'max:1000'],
        ]);

        $team->update([
            'is_verified' => false,
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
        abort_unless(in_array(auth()->user()?->role, ['superadmin', 'panitia']), 403);

        $user = auth()->user();
        $query = Event::withCount(['teams', 'timelines'])
            ->where('type', 'competition')
            ->orderBy('title');

        if ($user->role === 'panitia') {
            $query->whereIn('id', $user->events->pluck('id'));
        }

        return view('admin.timelines.index', [
            'events' => $query->get(),
            'canManageTimelines' => $user->role === 'superadmin',
        ]);
    }

    public function timelineAgenda(Event $event): View
    {
        abort_unless(in_array(auth()->user()?->role, ['superadmin', 'panitia']), 403);
        $this->abortUnlessCompetition($event);

        if (auth()->user()->role === 'panitia') {
            abort_unless(auth()->user()->events->contains('id', $event->id), 403);
        }

        $eventsQuery = Event::where('type', 'competition')->orderBy('title');
        if (auth()->user()->role === 'panitia') {
            $eventsQuery->whereIn('id', auth()->user()->events->pluck('id'));
        }

        return view('admin.timelines.agenda', [
            'event' => $event->load(['timelines' => fn ($query) => $query->orderBy('date')])
                ->loadCount('teams'),
            'events' => $eventsQuery->get(),
            'canManageTimelines' => auth()->user()?->role === 'superadmin',
        ]);
    }

    public function storeCompetition(Request $request): RedirectResponse
    {
        $this->ensureSuperadmin();

        $validated = $this->validateCompetition($request);

        Event::create([
            ...$validated,
            'id' => (string) Str::uuid(),
            'type' => 'competition',
            'is_active' => true,
            'max_noncompetition_participant' => null,
        ]);

        return back()->with('status', 'Kompetisi berhasil ditambahkan.');
    }

    public function updateCompetition(Request $request, Event $event): RedirectResponse
    {
        $this->ensureSuperadmin();
        $this->abortUnlessCompetition($event);

        $event->update($this->validateCompetition($request));

        return back()->with('status', 'Kompetisi berhasil diperbarui.');
    }

    public function destroyCompetition(Event $event): RedirectResponse
    {
        $this->ensureSuperadmin();
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
        $this->ensureSuperadmin();
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
        $this->ensureSuperadmin();

        $validated = $this->validateCompetitionTimeline($request);

        EventTimeline::create($validated);

        return back()->with('status', 'Agenda kompetisi berhasil ditambahkan.');
    }

    public function updateTimeline(Request $request, EventTimeline $timeline): RedirectResponse
    {
        $this->ensureSuperadmin();
        $this->abortUnlessCompetitionTimeline($timeline);

        $validated = $this->validateCompetitionTimeline($request);

        $timeline->update($validated);

        return back()->with('status', 'Agenda kompetisi berhasil diperbarui.');
    }

    public function destroyTimeline(EventTimeline $timeline): RedirectResponse
    {
        $this->ensureSuperadmin();
        $this->abortUnlessCompetitionTimeline($timeline);

        $timeline->delete();

        return back()->with('status', 'Agenda kompetisi berhasil dihapus.');
    }

    public function announcements(): View
    {
        abort_unless(in_array(auth()->user()?->role, ['superadmin', 'panitia']), 403);
        
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
        $eventRules = ['required', 'string', Rule::exists('event', 'id')];
        
        if ($user->role === 'panitia') {
            $eventRules[] = Rule::in($user->events->pluck('id')->toArray());
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
        $eventRules = ['required', 'string', Rule::exists('event', 'id')];
        
        if ($user->role === 'panitia') {
            abort_unless($user->events->contains('id', $announcement->event_id), 403);
            $eventRules[] = Rule::in($user->events->pluck('id')->toArray());
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

    private function isAdminStaff(): bool
    {
        return in_array(auth()->user()?->role, ['superadmin', 'admin_keuangan', 'panitia'], true);
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
            'description' => ['required', 'string', 'max:2000'],
            'guide_book_url' => ['required', 'url', 'max:500'],
            'price' => ['required', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'contact_person1' => ['required', 'string', 'max:191'],
            'contact_person2' => ['nullable', 'string', 'max:191'],
        ]);
    }

    private function abortUnlessCompetition(?Event $event): void
    {
        abort_unless($event?->type === 'competition', 403);
    }
}
