<?php

namespace App\Http\Controllers\Operation;

use App\Http\Controllers\Controller;
use App\Models\CompetitionTimeline;
use App\Models\Event;
use App\Models\EventTimeline;
use App\Models\Setting;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinalistController extends Controller
{
    public function index(Request $request): View
    {
        // Hanya superadmin dan panitia_lomba yang bisa akses
        abort_unless(in_array(auth()->user()?->role, ['superadmin', 'panitia_lomba'], true), 403);

        // Ambil event kompetisi
        $eventsQuery = (auth()->user()?->role === 'panitia_lomba')
            ? auth()->user()->events()->where('type', 'competition')
            : Event::where('type', 'competition');

        $events = $eventsQuery->with(['timelines' => fn($q) => $q->orderBy('date', 'asc')])
            ->orderBy('title')
            ->get();

        $globalTimelines = CompetitionTimeline::orderBy('start_date', 'asc')->get();

        // Jadwal pengumuman global (serentak untuk semua kompetisi)
        $globalFinalistTimelineId = Setting::get('finalist_timeline_id')
            ?? $events->firstWhere('finalist_timeline_id')?->finalist_timeline_id;
        $globalWinnerTimelineId = Setting::get('winner_timeline_id')
            ?? $events->firstWhere('winner_timeline_id')?->winner_timeline_id;

        $query = Team::with(['event', 'members.user'])
            ->whereHas('event', fn($q) => $q->where('type', 'competition'))
            ->where('is_verified', 'approved'); // hanya tim yang sudah lunas/diverifikasi

        // Panitia lomba hanya lihat event yang di-assign
        if (auth()->user()?->role === 'panitia_lomba') {
            $assignedEventIds = auth()->user()->events
                ->where('type', 'competition')
                ->pluck('id')
                ->toArray();
            $query->whereIn('competition_id', $assignedEventIds);
        }

        // Filter by event
        $selectedEventId = $request->input('event_id', '');
        if ($selectedEventId) {
            $query->where('competition_id', $selectedEventId);
        }

        // Filter by finalist status
        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'finalist') {
                $query->where('is_finalist', true)->whereNull('rank');
            } elseif ($status === 'winner') {
                $query->where('is_finalist', true)->whereNotNull('rank');
            } elseif ($status === 'none') {
                $query->where('is_finalist', false);
            }
        }

        $teams = $query->orderByRaw('is_finalist DESC, ISNULL(`rank`) ASC, `rank` ASC, team_name ASC')
            ->paginate(20)
            ->withQueryString();

        $selectedEvent = $selectedEventId ? $events->firstWhere('id', $selectedEventId) : null;

        return view('operation.finalist.index', [
            'teams'                    => $teams,
            'events'                   => $events,
            'globalTimelines'          => $globalTimelines,
            'globalFinalistTimelineId' => $globalFinalistTimelineId,
            'globalWinnerTimelineId'   => $globalWinnerTimelineId,
            'selectedEvent'            => $selectedEvent,
            'selectedEventId'          => $selectedEventId,
            'selectedStatus'           => $request->input('status', ''),
        ]);
    }

    public function updateAnnouncementSchedule(Request $request): RedirectResponse
    {
        abort_unless(in_array(auth()->user()?->role, ['superadmin', 'panitia_lomba'], true), 403);

        $validated = $request->validate([
            'finalist_timeline_id' => 'nullable|string|max:36',
            'winner_timeline_id'   => 'nullable|string|max:36',
        ]);

        $finalistId = $validated['finalist_timeline_id'] ?: null;
        $winnerId   = $validated['winner_timeline_id'] ?: null;

        // Simpan ke Setting global
        Setting::set('finalist_timeline_id', $finalistId);
        Setting::set('winner_timeline_id', $winnerId);

        // Update semua event kompetisi secara serentak
        Event::where('type', 'competition')->update([
            'finalist_timeline_id' => $finalistId,
            'winner_timeline_id'   => $winnerId,
        ]);

        return back()->with('success', 'Jadwal pengumuman finalis & juara (serentak semua lomba) berhasil disimpan.');
    }
}
