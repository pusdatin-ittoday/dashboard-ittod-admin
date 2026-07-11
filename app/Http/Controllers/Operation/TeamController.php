<?php

namespace App\Http\Controllers\Operation;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\TeamMember;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    // Menampilkan daftar semua tim (UC-04)
    public function index(Request $request) {
        abort_unless(in_array(auth()->user()->role, ['superadmin', 'panitia_lomba'], true), 403);
        
        $query = Team::with(['event', 'members.user']);
        $filterEventId = $request->input('event_id');

        if ($filterEventId) {
            if ($filterEventId === 'all_teams') {
                $query->whereHas('event', function($q) {
                    $q->where('type', 'competition');
                });
            } elseif ($filterEventId === 'all_participants') {
                $query->whereHas('event', function($q) {
                    $q->where('type', 'seminar');
                });
            } else {
                if (auth()->user()->role === 'panitia_lomba') {
                    abort_unless(auth()->user()->events->contains('id', $filterEventId), 403);
                }
                $query->where('competition_id', $filterEventId);
            }
        } elseif (auth()->user()->role === 'panitia_lomba') {
            $query->whereIn('competition_id', auth()->user()->events->pluck('id'));
        }
        
        $teams = $query->get();

        // Get events list for dropdown filter
        if (auth()->user()->role === 'panitia_lomba') {
            $events = auth()->user()->events()->orderBy('title')->get();
        } else {
            $events = \App\Models\Event::orderBy('title')->get();
        }
        
        return view('operation.teams.index', compact('teams', 'events', 'filterEventId'));
    }

    // Melihat detail berkas identitas (REQ-08)
    public function show(string $id) {
        abort_unless(in_array(auth()->user()->role, ['superadmin', 'panitia_lomba'], true), 403);
        $team = Team::with(['event', 'members.user', 'members.kartu', 'paymentProof'])->findOrFail($id);
        
        if (auth()->user()->role === 'panitia_lomba') {
            abort_unless(auth()->user()->events->contains('id', $team->competition_id), 403);
        }
        
        return view('operation.teams.show', compact('team'));
    }

    // Mengubah status verifikasi berkas tim (REQ-08)
    public function updateStatus(Request $request, string $id) {
        abort_unless(in_array(auth()->user()->role, ['superadmin', 'panitia_lomba'], true), 403);
        $team = Team::with('members')->findOrFail($id);
        
        if (auth()->user()->role === 'panitia_lomba') {
            abort_unless(auth()->user()->events->contains('id', $team->competition_id), 403);
        }

        if ($team->is_document_verified === 'approved') {
            return back()->with('error', 'Berkas tim yang sudah disetujui tidak dapat diubah statusnya.');
        }
        
        $request->validate([
            'is_document_verified' => 'required|in:pending,approved,rejected',
            'verification_error' => 'required_if:is_document_verified,rejected|nullable|string',
        ]);

        if ($request->is_document_verified === 'approved') {
            $unverifiedMembers = $team->members
                ->filter(fn (TeamMember $member) => !$member->is_verified);

            if ($unverifiedMembers->isNotEmpty()) {
                return back()
                    ->withErrors([
                        'is_document_verified' => 'Berkas tim belum bisa disetujui karena masih ada anggota tim yang belum diverifikasi secara individual (Setuju/Tolak).',
                    ])
                    ->withInput();
            }
        }

        $teamUpdates = [
            'is_document_verified' => $request->is_document_verified,
            'verification_error' => $request->is_document_verified === 'rejected' ? $request->verification_error : null
        ];

        if ($request->is_document_verified === 'approved') {
            $teamUpdates['is_verified'] = 'pending';
        }

        $team->update($teamUpdates);

        return redirect()
            ->route('operation.teams.index')
            ->with('success', 'Status verifikasi tim berhasil diperbarui!');
    }

    // Mengubah status verifikasi dokumen anggota secara individual
    public function updateMemberStatus(Request $request, string $teamId, string $userId) {
        abort_unless(in_array(auth()->user()->role, ['superadmin', 'panitia_lomba'], true), 403);
        $member = TeamMember::where('team_id', $teamId)
            ->where('user_id', $userId)
            ->firstOrFail();

        $team = Team::findOrFail($teamId);

        if (auth()->user()->role === 'panitia_lomba') {
            abort_unless(auth()->user()->events->contains('id', $team->competition_id), 403);
        }

        if ($team->is_document_verified === 'approved') {
            return back()->with('error', 'Berkas tim yang sudah disetujui tidak dapat diubah status anggotanya.');
        }

        $request->validate([
            'action' => 'required|in:approve,reject',
            'verification_error' => 'required_if:action,reject|nullable|string|max:1000',
        ]);

        $verificationError = $request->action === 'reject' && filled($request->verification_error)
            ? trim($request->verification_error)
            : null;

        $member->update([
            'is_verified' => $request->action === 'approve',
            'verification_error' => $verificationError
        ]);

        if (filled($verificationError) && $team->is_document_verified === 'approved') {
            $team->update([
                'is_document_verified' => 'pending',
                'verification_error' => 'Persetujuan dibatalkan otomatis karena ada catatan revisi pada anggota tim.'
            ]);
        }

        return back()->with('success', 'Status verifikasi dokumen anggota berhasil diperbarui!');
    }
}
