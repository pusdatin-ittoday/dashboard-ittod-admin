<?php

namespace App\Http\Controllers\Operation;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
                    $q->where('type', 'non_competition');
                });
            } elseif ($filterEventId === 'all_global') {
                // All teams and all non-competition participants
            } else {
                if (auth()->user()->role === 'panitia_lomba') {
                    abort_unless(auth()->user()->events->contains('id', $filterEventId), 403);
                }
                $query->where('competition_id', $filterEventId);
            }
        } elseif (auth()->user()->role === 'panitia_lomba') {
            $query->whereIn('competition_id', auth()->user()->events->pluck('id'));
        }
        
        $teams = $query
            ->orderByRaw("CASE WHEN is_document_verified = 'pending' THEN 0 ELSE 1 END")
            ->orderByDesc('created_at')
            ->get();

        // Get events list for dropdown filter
        if (auth()->user()->role === 'panitia_lomba') {
            $events = auth()->user()->events()->orderBy('title')->get();
        } else {
            $events = \App\Models\Event::orderBy('title')->get();
        }
        
        return view('operation.teams.index', compact('teams', 'events', 'filterEventId'));
    }

    // Menyetujui seluruh berkas secara langsung (Superadmin Only)
    public function approveAllDocuments(Request $request) {
        abort_unless(auth()->user()->role === 'superadmin', 403, 'Aksi ini hanya dapat dilakukan oleh Superadmin.');

        $filterEventId = $request->input('event_id');
        $query = Team::with(['event', 'members.user'])->where('is_document_verified', '!=', 'approved');

        if ($filterEventId) {
            if ($filterEventId === 'all_teams') {
                $query->whereHas('event', function($q) {
                    $q->where('type', 'competition');
                });
            } elseif ($filterEventId === 'all_participants') {
                $query->whereHas('event', function($q) {
                    $q->where('type', 'non_competition');
                });
            } elseif ($filterEventId === 'all_global') {
                // Semua pendaftaran global
            } else {
                $query->where('competition_id', $filterEventId);
            }
        }

        $teams = $query->get();
        $count = $teams->count();

        if ($count === 0) {
            return back()->with('success', 'Semua berkas pada cakupan filter ini sudah terverifikasi.');
        }

        DB::transaction(function () use ($teams) {
            foreach ($teams as $team) {
                $teamUpdates = [
                    'is_document_verified' => 'approved',
                    'verification_error' => null,
                ];

                if ($team->is_verified !== 'approved') {
                    $teamUpdates['is_verified'] = 'pending';
                }

                $team->update($teamUpdates);

                foreach ($team->members as $member) {
                    $member->update([
                        'is_verified' => true,
                        'verification_error' => null,
                    ]);

                    TeamMember::where('user_id', $member->user_id)->update([
                        'is_verified' => true,
                        'verification_error' => null,
                    ]);

                    Team::whereHas('members', function ($q) use ($member) {
                        $q->where('user_id', $member->user_id);
                    })
                    ->where('max_member', 1)
                    ->update([
                        'is_document_verified' => 'approved',
                        'verification_error' => null,
                    ]);

                    $user = $member->user;
                    if ($user) {
                        $sch = strtolower($user->nama_sekolah ?? '');
                        $eml = strtolower($user->email ?? '');
                        $isIpb = str_contains($sch, 'ipb') || str_contains($sch, 'institut pertanian bogor') || str_ends_with($eml, 'ipb.ac.id') || str_contains($eml, '@apps.ipb.ac.id');
                        if ($isIpb) {
                            DB::table('event_participant')
                                ->join('event', 'event_participant.event_id', '=', 'event.id')
                                ->where('event_participant.user_id', $member->user_id)
                                ->where('event.type', 'non_competition')
                                ->update([
                                    'event_participant.payment_verification' => 'accepted'
                                ]);
                        }
                    }
                }
            }
        });

        return redirect()
            ->route('operation.teams.index', $filterEventId ? ['event_id' => $filterEventId] : [])
            ->with('success', "Berhasil menyetujui seluruh berkas untuk {$count} pendaftaran secara langsung!");
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
                if (auth()->user()->role !== 'superadmin') {
                    return back()
                        ->withErrors([
                            'is_document_verified' => 'Berkas tim belum bisa disetujui karena masih ada anggota tim yang belum diverifikasi secara individual (Setuju/Tolak).',
                        ])
                        ->withInput();
                } else {
                    // Superadmin dapat menyetujui langsung seluruh berkas anggota
                    foreach ($team->members as $m) {
                        $m->update([
                            'is_verified' => true,
                            'verification_error' => null,
                        ]);
                    }
                }
            }
        }

        $teamUpdates = [
            'is_document_verified' => $request->is_document_verified,
            'verification_error' => $request->is_document_verified === 'rejected' ? $request->verification_error : null
        ];

        if ($request->is_document_verified === 'approved' && $team->is_verified !== 'approved') {
            $teamUpdates['is_verified'] = 'pending';
        }

        $team->update($teamUpdates);

        // Auto-sync verification across user's other teams/events when team is approved
        if ($request->is_document_verified === 'approved') {
            foreach ($team->members as $m) {
                TeamMember::where('user_id', $m->user_id)->update([
                    'is_verified' => true,
                    'verification_error' => null,
                ]);

                // Auto-approve individual participant registrations of this member
                Team::whereHas('members', function ($q) use ($m) {
                    $q->where('user_id', $m->user_id);
                })
                ->where('max_member', 1)
                ->update([
                    'is_document_verified' => 'approved',
                    'verification_error' => null,
                ]);
            }
        }

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

        $isApproved = $request->action === 'approve';

        DB::transaction(function () use ($member, $team, $userId, $isApproved, $verificationError) {
            $member->update([
                'is_verified' => $isApproved,
                'verification_error' => $verificationError
            ]);

            if ($isApproved) {
                // Auto-sync: Update all other team_members of this user across other events/teams
                TeamMember::where('user_id', $userId)->update([
                    'is_verified' => true,
                    'verification_error' => null,
                ]);

                // Auto-approve individual participant teams of this user
                $individualTeams = Team::whereHas('members', function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                })
                ->where('max_member', 1)
                ->get();

                foreach ($individualTeams as $indTeam) {
                    if ($indTeam->is_document_verified !== 'approved') {
                        $indTeam->update([
                            'is_document_verified' => 'approved',
                            'verification_error' => null,
                        ]);
                    }
                }

                // If the user is registered in non_competition events and is IPB or free, auto-sync event_participant payment_verification
                $user = User::find($userId);
                $isIpb = false;
                if ($user) {
                    $sch = strtolower($user->nama_sekolah ?? '');
                    $eml = strtolower($user->email ?? '');
                    $isIpb = str_contains($sch, 'ipb') || str_contains($sch, 'institut pertanian bogor') || str_ends_with($eml, 'ipb.ac.id') || str_contains($eml, '@apps.ipb.ac.id');
                }

                if ($isIpb) {
                    DB::table('event_participant')
                        ->join('event', 'event_participant.event_id', '=', 'event.id')
                        ->where('event_participant.user_id', $userId)
                        ->where('event.type', 'non_competition')
                        ->update([
                            'event_participant.payment_verification' => 'accepted'
                        ]);
                }
            } else {
                if (filled($verificationError) && $team->is_document_verified === 'approved') {
                    $team->update([
                        'is_document_verified' => 'pending',
                        'verification_error' => 'Persetujuan dibatalkan otomatis karena ada catatan revisi pada anggota tim.'
                    ]);
                }
            }
        });

        return back()->with('success', 'Status verifikasi dokumen anggota berhasil diperbarui!');
    }

    // Menandai tim sebagai Finalis / Juara (Superadmin & Panitia Lomba)
    public function updateFinalist(Request $request, string $id) {
        abort_unless(in_array(auth()->user()->role, ['superadmin', 'panitia_lomba'], true), 403);
        $team = Team::findOrFail($id);

        if (auth()->user()->role === 'panitia_lomba') {
            abort_unless(auth()->user()->events->contains('id', $team->competition_id), 403);
        }

        $request->validate([
            'is_finalist' => 'required|boolean',
            'rank'        => 'nullable|integer|min:1|max:99',
        ]);

        $isFinalist = (bool) $request->is_finalist;
        $rank       = $isFinalist ? ($request->rank ?: null) : null;

        $team->update([
            'is_finalist' => $isFinalist,
            'rank'        => $rank,
        ]);

        $label = $isFinalist
            ? ('Tim ditandai sebagai Finalis' . ($rank ? " (Juara ke-{$rank})" : '') . '!')
            : 'Status Finalis tim berhasil dihapus.';

        return back()->with('success', $label);
    }

    // Menghapus tim secara permanen (Superadmin Only)
    public function destroy(string $id) {
        abort_unless(auth()->user()->role === 'superadmin', 403, 'Aksi ini hanya untuk Superadmin.');
        
        $team = Team::with('members')->findOrFail($id);

        \Illuminate\Support\Facades\DB::transaction(function () use ($team) {
            // If this is an individual team (non-competition event participant), also delete from event_participant
            if ($team->max_member === 1) {
                foreach ($team->members as $m) {
                    DB::table('event_participant')
                        ->where('user_id', $m->user_id)
                        ->where('event_id', $team->competition_id)
                        ->delete();
                }
            }

            \App\Models\CompetitionSubmission::where('team_id', $team->id)->delete();
            TeamMember::where('team_id', $team->id)->delete();
            $team->delete();
        });

        return redirect()
            ->route('operation.teams.index')
            ->with('success', 'Tim/Peserta berhasil dihapus secara permanen!');
    }

    // Mengeluarkan anggota dari tim (Superadmin Only)
    public function destroyMember(string $teamId, string $userId) {
        abort_unless(auth()->user()->role === 'superadmin', 403, 'Aksi ini hanya untuk Superadmin.');
        
        $member = TeamMember::where('team_id', $teamId)
            ->where('user_id', $userId)
            ->firstOrFail();

        $team = Team::findOrFail($teamId);

        \Illuminate\Support\Facades\DB::transaction(function () use ($member, $team) {
            $isLeader = $member->role === 'leader';
            $member->delete();

            $remainingMembers = TeamMember::where('team_id', $team->id)->get();

            if ($remainingMembers->isEmpty()) {
                \App\Models\CompetitionSubmission::where('team_id', $team->id)->delete();
                $team->delete();
            } elseif ($isLeader) {
                $newLeader = $remainingMembers->first();
                $newLeader->update(['role' => 'leader']);
            }
        });

        return back()->with('success', 'Anggota berhasil dikeluarkan dari tim!');
    }

    // Reset status pengubahan nama tim (Superadmin Only)
    public function resetNameChange(string $id) {
        abort_unless(auth()->user()->role === 'superadmin', 403, 'Aksi ini hanya untuk Superadmin.');

        $team = Team::findOrFail($id);
        $team->update([
            'is_name_changed' => false,
        ]);

        return back()->with('success', 'Batas pengubahan nama tim berhasil direset! Tim sekarang dapat mengubah nama tim 1 kali lagi.');
    }

    // Mengubah nama tim secara manual oleh Admin/Superadmin
    public function updateTeamNameAdmin(Request $request, string $id) {
        abort_unless(in_array(auth()->user()->role, ['superadmin', 'panitia_lomba'], true), 403);
        $team = Team::findOrFail($id);

        if (auth()->user()->role === 'panitia_lomba') {
            abort_unless(auth()->user()->events->contains('id', $team->competition_id), 403);
        }

        $request->validate([
            'team_name' => 'required|string|min:3|max:50',
            'previous_team_name' => 'nullable|string|max:50',
        ]);

        $trimmed = trim($request->team_name);

        $exists = Team::where('competition_id', $team->competition_id)
            ->where('team_name', $trimmed)
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['team_name' => 'Nama tim sudah digunakan di kompetisi ini.'])->withInput();
        }

        $prevName = filled($request->previous_team_name)
            ? trim($request->previous_team_name)
            : ($team->team_name !== $trimmed ? $team->team_name : $team->previous_team_name);

        $team->update([
            'previous_team_name' => $prevName,
            'team_name' => $trimmed,
            'is_name_changed' => true,
            'name_changed_at' => now(),
        ]);

        return back()->with('success', 'Nama tim berhasil diperbarui oleh Admin!');
    }

    // Mengubah kapasitas maksimal anggota tim
    public function updateMaxMember(Request $request, string $id) {
        abort_unless(in_array(auth()->user()->role, ['superadmin', 'panitia_lomba'], true), 403);
        $team = Team::findOrFail($id);
        
        if (auth()->user()->role === 'panitia_lomba') {
            abort_unless(auth()->user()->events->contains('id', $team->competition_id), 403);
        }

        $request->validate([
            'max_member' => 'required|integer|min:1|max:10',
        ]);

        $team->update([
            'max_member' => $request->max_member,
        ]);

        return back()->with('success', 'Kapasitas maksimal anggota tim berhasil diperbarui!');
    }
}
