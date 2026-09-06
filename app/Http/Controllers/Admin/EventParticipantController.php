<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\CompetitionSubmission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Carbon\Carbon;

class EventParticipantController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(in_array(auth()->user()?->role, ['superadmin', 'admin_biasa']), 403);

        $eventsNonCompetition = Event::where('type', 'non_competition')->orderBy('title')->get();
        $eventsCompetition = Event::where('type', 'competition')->orderBy('title')->get();
        $allEvents = Event::orderBy('type')->orderBy('title')->get();

        $typeFilter = $request->input('type'); // 'all', 'non_competition', 'competition'
        $eventFilter = $request->input('event_id');
        $filterStatus = $request->input('status', 'default');
        $search = $request->input('search');

        // 1. Query non-competition participants
        $q1 = DB::table('event_participant')
            ->join('user', 'event_participant.user_id', '=', 'user.id')
            ->join('event', 'event_participant.event_id', '=', 'event.id')
            ->select([
                DB::raw("'non_competition' as entity_type"),
                'event_participant.user_id as user_id',
                'event_participant.event_id as event_id',
                'event.title as event_title',
                'event.type as event_type',
                'user.full_name as full_name',
                DB::raw("'' as team_code"),
                DB::raw("'' as team_id"),
                'user.email as email',
                'user.phone_number as phone_number',
                'user.nama_sekolah as nama_sekolah',
                'event_participant.payment_proof as payment_proof',
                'event_participant.payment_verification as payment_verification',
                DB::raw("NULL as verification_error"),
                'event_participant.date_added as date_added',
                DB::raw('(
                    SELECT m.created_at 
                    FROM media m 
                    WHERE m.url = event_participant.payment_proof 
                    ORDER BY m.created_at DESC 
                    LIMIT 1
                ) as payment_proof_submitted_at'),
                DB::raw('EXISTS (
                    SELECT 1 
                    FROM team_member 
                    JOIN team ON team_member.team_id = team.id 
                    WHERE team_member.user_id = user.id 
                      AND (LOWER(team.competition_id) = "minetoday" OR LOWER(team.competition_id) = "mine-today")
                ) as is_minetoday'),
                DB::raw('1 as member_count')
            ]);

        // 2. Query competition teams
        $q2 = DB::table('team')
            ->join('event', 'team.competition_id', '=', 'event.id')
            ->leftJoin('media', 'team.payment_proof_id', '=', 'media.id')
            ->leftJoin('team_member', function ($join) {
                $join->on('team.id', '=', 'team_member.team_id')
                    ->where('team_member.role', '=', 'leader');
            })
            ->leftJoin('user', 'team_member.user_id', '=', 'user.id')
            ->where('event.type', 'competition')
            ->select([
                DB::raw("'competition' as entity_type"),
                'user.id as user_id',
                'team.competition_id as event_id',
                'event.title as event_title',
                'event.type as event_type',
                'team.team_name as full_name',
                'team.team_code as team_code',
                'team.id as team_id',
                'user.email as email',
                'user.phone_number as phone_number',
                'user.nama_sekolah as nama_sekolah',
                'media.url as payment_proof',
                DB::raw("CASE WHEN team.is_verified = 'approved' THEN 'accepted' ELSE team.is_verified END as payment_verification"),
                'team.verification_error as verification_error',
                'team.created_at as date_added',
                'media.created_at as payment_proof_submitted_at',
                DB::raw('0 as is_minetoday'),
                DB::raw('(SELECT COUNT(*) FROM team_member WHERE team_member.team_id = team.id) as member_count')
            ]);

        if ($typeFilter === 'non_competition') {
            $unionQuery = $q1;
        } elseif ($typeFilter === 'competition') {
            $unionQuery = $q2;
        } else {
            $unionQuery = $q1->unionAll($q2);
        }

        $query = DB::query()->fromSub($unionQuery, 'p');

        if ($eventFilter) {
            $query->where('p.event_id', $eventFilter);
        }

        if ($filterStatus === 'default') {
            $query->whereIn('p.payment_verification', ['pending', 'rejected']);
        } elseif (in_array($filterStatus, ['pending', 'accepted', 'rejected'])) {
            $query->where('p.payment_verification', $filterStatus);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('p.full_name', 'like', "%{$search}%")
                  ->orWhere('p.team_code', 'like', "%{$search}%")
                  ->orWhere('p.email', 'like', "%{$search}%")
                  ->orWhere('p.phone_number', 'like', "%{$search}%")
                  ->orWhere('p.nama_sekolah', 'like', "%{$search}%");
            });
        }

        $participants = $query
            ->orderByRaw("CASE WHEN p.payment_verification = 'pending' THEN 0 ELSE 1 END")
            ->orderByDesc('p.date_added')
            ->paginate(50)
            ->withQueryString();

        $r2Public = env('R2_PUBLIC', 'https://cdn.ittoday.web.id');

        foreach ($participants as $p) {
            if ($p->entity_type === 'competition') {
                $p->category = 'Tim Kompetisi';
                $p->category_badge = 'purple';
            } else {
                $namaSekolah = strtolower($p->nama_sekolah ?? '');
                $email = strtolower($p->email ?? '');
                $isIpb = str_contains($namaSekolah, 'ipb') || str_contains($namaSekolah, 'institut pertanian bogor') || str_ends_with($email, 'ipb.ac.id') || str_contains($email, '@apps.ipb.ac.id');
                
                if ($p->is_minetoday) {
                    $p->category = 'MineToday';
                    $p->category_badge = 'amber';
                } elseif ($isIpb) {
                    $p->category = 'Mahasiswa IPB';
                    $p->category_badge = 'blue';
                } else {
                    $p->category = 'Umum';
                    $p->category_badge = 'gray';
                }
            }

            // Resolve payment proof URL
            if ($p->payment_proof) {
                if (str_starts_with($p->payment_proof, 'http')) {
                    $p->payment_proof_url = $p->payment_proof;
                } else {
                    $p->payment_proof_url = rtrim($r2Public, '/') . '/' . ltrim($p->payment_proof, '/');
                }
            } else {
                $p->payment_proof_url = null;
            }

            // Format registration date
            if ($p->date_added) {
                if (is_numeric($p->date_added)) {
                    $ts = strlen((string)$p->date_added) >= 13 ? (int)($p->date_added / 1000) : (int)$p->date_added;
                    $p->date_added_formatted = Carbon::createFromTimestamp($ts)->format('d M Y H:i');
                } else {
                    $p->date_added_formatted = Carbon::parse($p->date_added)->format('d M Y H:i');
                }
            } else {
                $p->date_added_formatted = '-';
            }

            // Format payment proof submit date
            if (!empty($p->payment_proof_submitted_at)) {
                if (is_numeric($p->payment_proof_submitted_at)) {
                    $ts = strlen((string)$p->payment_proof_submitted_at) >= 13 ? (int)($p->payment_proof_submitted_at / 1000) : (int)$p->payment_proof_submitted_at;
                    $p->payment_proof_submitted_at_formatted = Carbon::createFromTimestamp($ts)->format('d M Y H:i');
                } else {
                    $p->payment_proof_submitted_at_formatted = Carbon::parse($p->payment_proof_submitted_at)->format('d M Y H:i');
                }
            } else {
                $p->payment_proof_submitted_at_formatted = null;
            }
        }

        // Stats counts across all payment entities
        $allStatsQuery = DB::query()->fromSub($q1->unionAll($q2), 'st');
        if ($typeFilter === 'non_competition') {
            $allStatsQuery = DB::query()->fromSub($q1, 'st');
        } elseif ($typeFilter === 'competition') {
            $allStatsQuery = DB::query()->fromSub($q2, 'st');
        }

        $pendingCount = (clone $allStatsQuery)->where('payment_verification', 'pending')->count();
        $acceptedCount = (clone $allStatsQuery)->where('payment_verification', 'accepted')->count();
        $rejectedCount = (clone $allStatsQuery)->where('payment_verification', 'rejected')->count();

        // Get users list for manual addition dropdown
        $allUsers = User::orderBy('full_name')->select('id', 'full_name', 'email', 'nama_sekolah')->get();

        return view('admin.event-participants.index', compact(
            'participants',
            'eventsNonCompetition',
            'eventsCompetition',
            'allEvents',
            'pendingCount',
            'acceptedCount',
            'rejectedCount',
            'filterStatus',
            'typeFilter',
            'search',
            'allUsers'
        ));
    }

    public function store(Request $request)
    {
        abort_unless(in_array(auth()->user()?->role, ['superadmin', 'admin_biasa']), 403);

        $request->validate([
            'user_id' => 'required|string',
            'event_id' => 'required|string',
            'payment_verification' => 'required|in:accepted,pending,rejected',
            'payment_proof_file' => 'nullable|image|max:3072',
        ]);

        $user = User::find($request->user_id);
        if (!$user) {
            return back()->with('error', 'User tidak ditemukan.');
        }

        $event = Event::find($request->event_id);
        if (!$event) {
            return back()->with('error', 'Event tidak ditemukan.');
        }

        $paymentProofKey = null;
        if ($request->hasFile('payment_proof_file')) {
            $file = $request->file('payment_proof_file');
            $path = $file->store('uploads/admin_manual', 'public');
            $paymentProofKey = Storage::disk('public')->url($path);
        }

        DB::table('event_participant')->updateOrInsert(
            [
                'user_id' => $user->id,
                'event_id' => $event->id,
            ],
            [
                'payment_verification' => $request->payment_verification,
                'payment_proof' => $paymentProofKey,
                'date_added' => now(),
            ]
        );

        return back()->with('success', "Peserta {$user->full_name} berhasil didaftarkan ke {$event->title}.");
    }

    public function verify(Request $request)
    {
        abort_unless(in_array(auth()->user()?->role, ['superadmin', 'admin_biasa']), 403);

        $request->validate([
            'action' => 'required|in:accept,reject',
            'entity_type' => 'nullable|in:non_competition,competition',
            'user_id' => 'nullable|string',
            'event_id' => 'nullable|string',
            'team_id' => 'nullable|string',
            'verification_error' => 'nullable|string|max:1000',
        ]);

        $status = $request->action === 'accept' ? 'accepted' : 'rejected';

        // Competition team verification
        if ($request->entity_type === 'competition' || $request->filled('team_id')) {
            $team = Team::find($request->team_id);
            if (!$team) {
                return back()->with('error', 'Tim kompetisi tidak ditemukan.');
            }

            $team->update([
                'is_verified' => $request->action === 'accept' ? 'approved' : 'rejected',
                'verification_error' => $request->action === 'accept' ? null : $request->verification_error,
            ]);

            return back()->with('success', "Status verifikasi tim {$team->team_name} berhasil diperbarui.");
        }

        // Non-competition participant verification
        $participant = DB::table('event_participant')
            ->where('user_id', $request->user_id)
            ->where('event_id', $request->event_id)
            ->first();

        if (!$participant) {
            return back()->with('error', 'Data peserta tidak ditemukan.');
        }

        DB::transaction(function () use ($request, $status) {
            DB::table('event_participant')
                ->where('user_id', $request->user_id)
                ->where('event_id', $request->event_id)
                ->update([
                    'payment_verification' => $status
                ]);

            // Sync with team if individual team exists
            $indTeam = Team::where('competition_id', $request->event_id)
                ->whereHas('members', function ($q) use ($request) {
                    $q->where('user_id', $request->user_id);
                })
                ->first();

            if ($indTeam) {
                $indTeam->update([
                    'is_verified' => $status === 'accepted' ? 'approved' : 'rejected',
                ]);
            }
        });

        return back()->with('success', 'Status verifikasi berhasil diperbarui.');
    }

    public function destroy(Request $request)
    {
        abort_unless(in_array(auth()->user()?->role, ['superadmin', 'admin_biasa']), 403);

        // Delete competition team
        if ($request->entity_type === 'competition' || $request->filled('team_id')) {
            $team = Team::find($request->team_id);
            if ($team) {
                DB::transaction(function () use ($team) {
                    CompetitionSubmission::where('team_id', $team->id)->delete();
                    TeamMember::where('team_id', $team->id)->delete();
                    $team->delete();
                });
                return back()->with('success', 'Data tim kompetisi berhasil dihapus.');
            }
            return back()->with('error', 'Tim tidak ditemukan.');
        }

        // Delete non-competition participant
        $deleted = DB::transaction(function () use ($request) {
            $count = DB::table('event_participant')
                ->where('user_id', $request->user_id)
                ->where('event_id', $request->event_id)
                ->delete();

            $individualTeams = Team::where('competition_id', $request->event_id)
                ->where('max_member', 1)
                ->whereHas('members', function ($q) use ($request) {
                    $q->where('user_id', $request->user_id);
                })
                ->get();

            foreach ($individualTeams as $team) {
                CompetitionSubmission::where('team_id', $team->id)->delete();
                TeamMember::where('team_id', $team->id)->delete();
                $team->delete();
            }

            return $count > 0 || $individualTeams->isNotEmpty();
        });

        if ($deleted) {
            return back()->with('success', 'Peserta berhasil dihapus dari kegiatan.');
        }

        return back()->with('error', 'Peserta tidak ditemukan.');
    }
}
