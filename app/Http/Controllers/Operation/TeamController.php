<?php

namespace App\Http\Controllers\Operation;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\TeamMember;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    // Menampilkan daftar semua tim (UC-04)
    public function index() {
        $teams = Team::with(['event', 'members.user'])->get(); 
        return view('operation.teams.index', compact('teams'));
    }

    // Melihat detail berkas identitas (REQ-08)
    public function show(string $id) {
        $team = Team::with(['event', 'members.user', 'members.kartu', 'paymentProof'])->findOrFail($id);
        return view('operation.teams.show', compact('team'));
    }

    // Mengubah status verifikasi berkas tim (REQ-08)
    public function updateStatus(Request $request, string $id) {
        $team = Team::findOrFail($id);
        
        $request->validate([
            'is_verified' => 'required|in:0,1',
            'verification_error' => 'nullable|string',
        ]);

        $team->update([
            'is_verified' => (int) $request->is_verified,
            'verification_error' => $request->is_verified == 0 ? $request->verification_error : null
        ]);

        return redirect()
            ->route('operation.teams.index')
            ->with('success', 'Status verifikasi tim berhasil diperbarui!');
    }

    // Mengubah status verifikasi dokumen anggota secara individual
    public function updateMemberStatus(Request $request, string $teamId, string $userId) {
        $member = TeamMember::where('team_id', $teamId)
            ->where('user_id', $userId)
            ->firstOrFail();

        $request->validate([
            'verification_error' => 'nullable|string',
        ]);

        $member->update([
            'verification_error' => $request->verification_error
        ]);

        return back()->with('success', 'Status verifikasi dokumen anggota berhasil diperbarui!');
    }
}
