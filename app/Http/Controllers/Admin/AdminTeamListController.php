<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminTeamListController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(in_array(auth()->user()?->role, ['superadmin', 'admin_biasa', 'panitia_lomba'], true), 403);

        $query = Team::with(['event', 'members.user', 'paymentProof', 'submissions']);

        // Filter by Panitia Lomba assigned events if restricted
        if (auth()->user()?->role === 'panitia_lomba') {
            $assignedEventIds = auth()->user()->events->pluck('id')->toArray();
            $query->whereIn('competition_id', $assignedEventIds);
        }

        // Filter by specific event/competition
        if ($request->filled('event_id')) {
            $query->where('competition_id', $request->input('event_id'));
        }

        // Filter by Status Berkas (is_document_verified)
        if ($request->filled('status_berkas')) {
            $statusBerkas = strtolower($request->input('status_berkas'));
            if ($statusBerkas === 'verified' || $statusBerkas === 'approved' || $statusBerkas === '1') {
                $query->where(function ($q) {
                    $q->where('is_document_verified', 'verified')
                      ->orWhere('is_document_verified', 'approved')
                      ->orWhere('is_document_verified', '1');
                });
            } elseif ($statusBerkas === 'rejected' || $statusBerkas === '0') {
                $query->where(function ($q) {
                    $q->where('is_document_verified', 'rejected')
                      ->orWhere('is_document_verified', '0');
                });
            } elseif ($statusBerkas === 'pending') {
                $query->where(function ($q) {
                    $q->where('is_document_verified', 'pending')
                      ->orWhereNull('is_document_verified');
                });
            }
        }

        // Filter by Status Pembayaran (is_verified)
        if ($request->filled('status_pembayaran')) {
            $statusBayar = strtolower($request->input('status_pembayaran'));
            if ($statusBayar === 'verified' || $statusBayar === 'approved' || $statusBayar === '1') {
                $query->where(function ($q) {
                    $q->where('is_verified', 'approved')
                      ->orWhere('is_verified', 'verified')
                      ->orWhere('is_verified', '1');
                });
            } elseif ($statusBayar === 'rejected' || $statusBayar === '0') {
                $query->where(function ($q) {
                    $q->where('is_verified', 'rejected')
                      ->orWhere('is_verified', '0');
                });
            } elseif ($statusBayar === 'pending') {
                $query->where(function ($q) {
                    $q->where('is_verified', 'pending')
                      ->orWhereNull('is_verified');
                });
            }
        }

        // Filter by Date / Batch
        if ($request->filled('batch')) {
            $batch = $request->input('batch');
            if ($batch === 'today') {
                $query->whereDate('created_at', now()->today());
            } elseif ($batch === 'this_week') {
                $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
            } elseif ($batch === 'this_month') {
                $query->whereYear('created_at', now()->year)->whereMonth('created_at', now()->month);
            }
        }

        // Search Filter (Team Name, Code, Member Name, Email, School)
        if ($request->filled('search')) {
            $search = strtolower(trim($request->input('search')));
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(team_name) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(team_code) LIKE ?', ["%{$search}%"])
                  ->orWhereHas('members.user', function ($uq) use ($search) {
                      $uq->whereRaw('LOWER(full_name) LIKE ?', ["%{$search}%"])
                         ->orWhereRaw('LOWER(email) LIKE ?', ["%{$search}%"])
                         ->orWhereRaw('LOWER(nama_sekolah) LIKE ?', ["%{$search}%"])
                         ->orWhereRaw('LOWER(phone_number) LIKE ?', ["%{$search}%"]);
                  });
            });
        }

        $teams = $query->latest('created_at')->get();

        // Dropdown events list for filter
        if (auth()->user()?->role === 'panitia_lomba') {
            $events = auth()->user()->events()->orderBy('title')->get();
        } else {
            $events = Event::orderBy('title')->get();
        }

        // Summary Stats
        $stats = [
            'total_teams' => $teams->count(),
            'verified_berkas' => $teams->filter(fn($t) => in_array($t->is_document_verified, ['verified', 'approved', '1', 1], true))->count(),
            'verified_pembayaran' => $teams->filter(fn($t) => in_array($t->is_verified, ['approved', 'verified', '1', 1], true))->count(),
            'pending_teams' => $teams->filter(fn($t) => in_array($t->is_verified, ['pending', null], true) || in_array($t->is_document_verified, ['pending', null], true))->count(),
            'total_members' => $teams->sum(fn($t) => $t->members->count()),
        ];

        return view('admin.teams-list.index', [
            'teams' => $teams,
            'events' => $events,
            'stats' => $stats,
            'selectedEventId' => $request->input('event_id', ''),
            'selectedStatusBerkas' => $request->input('status_berkas', ''),
            'selectedStatusPembayaran' => $request->input('status_pembayaran', ''),
            'selectedBatch' => $request->input('batch', ''),
            'searchQuery' => $request->input('search', ''),
        ]);
    }
}
