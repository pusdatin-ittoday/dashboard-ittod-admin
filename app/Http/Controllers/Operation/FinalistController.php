<?php

namespace App\Http\Controllers\Operation;

use App\Exports\FinalistExport;
use App\Http\Controllers\Controller;
use App\Models\CompetitionTimeline;
use App\Models\Event;
use App\Models\EventTimeline;
use App\Models\Setting;
use App\Models\Team;
use App\Services\GoogleSheetService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FinalistController extends Controller
{
    /**
     * Query builder for finalists with filtering and role-based permissions.
     */
    protected function buildFinalistQuery(Request $request): Builder
    {
        $query = Team::query()
            ->join('event', 'team.competition_id', '=', 'event.id')
            ->select('team.*')
            ->where('event.type', 'competition')
            ->where('team.is_verified', 'approved'); // hanya tim yang sudah lunas/diverifikasi

        // Panitia lomba hanya lihat event yang di-assign
        if (auth()->user()?->role === 'panitia_lomba') {
            $assignedEventIds = auth()->user()->events
                ->where('type', 'competition')
                ->pluck('id')
                ->toArray();
            $query->whereIn('team.competition_id', $assignedEventIds);
        }

        // Filter by event
        $selectedEventId = $request->input('event_id', '');
        if ($selectedEventId) {
            $query->where('team.competition_id', $selectedEventId);
        }

        // Search by team name
        $search = trim($request->input('search', ''));
        if ($search !== '') {
            $query->where('team.team_name', 'like', "%{$search}%");
        }

        // Filter by finalist status
        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'finalist') {
                $query->where('team.is_finalist', true)->whereNull('team.rank');
            } elseif ($status === 'winner') {
                $query->where('team.is_finalist', true)->whereNotNull('team.rank');
            } elseif ($status === 'none') {
                $query->where('team.is_finalist', false);
            }
        }

        return $query->orderBy('event.title', 'asc')
            ->orderByRaw('team.is_finalist DESC, ISNULL(team.rank) ASC, team.rank ASC, team.team_name ASC, team.id ASC');
    }

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

        $selectedEventId = $request->input('event_id', '');

        $teams = $this->buildFinalistQuery($request)
            ->with(['event', 'members.user'])
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

    public function exportCsv(Request $request): StreamedResponse
    {
        abort_unless(in_array(auth()->user()?->role, ['superadmin', 'panitia_lomba'], true), 403);

        $selectedEventId = $request->input('event_id');
        $event = $selectedEventId ? Event::find($selectedEventId) : null;

        $suffix = $event ? Str::slug($event->title) : 'semua-kompetisi';
        $filename = 'rekap-finalis-' . $suffix . '-' . now()->format('Y-m-d') . '.csv';

        $query = $this->buildFinalistQuery($request);

        return response()->stream(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            FinalistExport::write($handle, $query);
            fclose($handle);
        }, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ]);
    }

    public function exportSheets(Request $request, GoogleSheetService $service): JsonResponse
    {
        abort_unless(in_array(auth()->user()?->role, ['superadmin', 'panitia_lomba'], true), 403);

        try {
            $selectedEventId = $request->input('event_id');
            $event = $selectedEventId ? Event::find($selectedEventId) : null;

            $sheetTitle = $event
                ? 'Finalis - ' . Str::limit($event->title, 20)
                : 'Finalis & Juara';

            $query = $this->buildFinalistQuery($request);

            $url = $service->exportFromCallback($sheetTitle, function ($handle) use ($query) {
                FinalistExport::write($handle, $query);
            });

            return response()->json([
                'success' => true,
                'url'     => $url,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
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
