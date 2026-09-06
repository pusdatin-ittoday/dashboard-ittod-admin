<?php

namespace App\Http\Controllers\Operation;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinalistController extends Controller
{
    public function index(Request $request): View
    {
        // Hanya superadmin dan panitia_lomba yang bisa akses
        abort_unless(in_array(auth()->user()?->role, ['superadmin', 'panitia_lomba'], true), 403);

        // Ambil hanya event kompetisi
        if (auth()->user()?->role === 'panitia_lomba') {
            $events = auth()->user()->events()
                ->where('type', 'competition')
                ->orderBy('title')
                ->get();
        } else {
            $events = Event::where('type', 'competition')
                ->orderBy('title')
                ->get();
        }

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
        if ($request->filled('event_id')) {
            $query->where('competition_id', $request->input('event_id'));
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

        return view('operation.finalist.index', [
            'teams'           => $teams,
            'events'          => $events,
            'selectedEventId' => $request->input('event_id', ''),
            'selectedStatus'  => $request->input('status', ''),
        ]);
    }
}
