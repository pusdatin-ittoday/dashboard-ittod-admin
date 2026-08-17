<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class EventParticipantController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(in_array(auth()->user()?->role, ['superadmin', 'admin_biasa']), 403);

        $events = Event::where('type', 'non_competition')->orderBy('title')->get();
        
        $query = DB::table('event_participant')
            ->join('user', 'event_participant.user_id', '=', 'user.id')
            ->join('event', 'event_participant.event_id', '=', 'event.id')
            ->select(
                'event_participant.*',
                'user.full_name',
                'user.email',
                'user.phone_number',
                'user.nama_sekolah',
                'event.title as event_title',
                'event.type as event_type',
                DB::raw('EXISTS (
                    SELECT 1 
                    FROM team_member 
                    JOIN team ON team_member.team_id = team.id 
                    WHERE team_member.user_id = user.id 
                      AND (LOWER(team.competition_id) = "minetoday" OR LOWER(team.competition_id) = "mine-today")
                ) as is_minetoday')
            )
            ->where('event.type', 'non_competition');

        if ($request->filled('event_id')) {
            $query->where('event_participant.event_id', $request->event_id);
        }

        $filterStatus = $request->input('status', 'default');
        
        if ($filterStatus === 'default') {
            $query->whereIn('event_participant.payment_verification', ['pending', 'rejected']);
        } elseif (in_array($filterStatus, ['pending', 'accepted', 'rejected'])) {
            $query->where('event_participant.payment_verification', $filterStatus);
        }
        // if 'all', do not filter by status

        $search = $request->input('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('user.full_name', 'like', "%{$search}%")
                  ->orWhere('user.email', 'like', "%{$search}%")
                  ->orWhere('user.phone_number', 'like', "%{$search}%")
                  ->orWhere('user.nama_sekolah', 'like', "%{$search}%");
            });
        }

        $participants = $query
            ->orderByRaw("CASE WHEN event_participant.payment_verification = 'pending' THEN 0 ELSE 1 END")
            ->orderByDesc('event_participant.date_added')
            ->paginate(50)
            ->withQueryString();

        $r2Public = env('R2_PUBLIC', 'https://cdn.ittoday.web.id');

        foreach ($participants as $p) {
            // Determine category
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
        }

        $statsQuery = DB::table('event_participant')
            ->join('event', 'event_participant.event_id', '=', 'event.id')
            ->where('event.type', 'non_competition');
            
        $pendingCount = (clone $statsQuery)->where('payment_verification', 'pending')->count();
        $acceptedCount = (clone $statsQuery)->where('payment_verification', 'accepted')->count();
        $rejectedCount = (clone $statsQuery)->where('payment_verification', 'rejected')->count();

        // Get users list for manual addition dropdown
        $allUsers = User::orderBy('full_name')->select('id', 'full_name', 'email', 'nama_sekolah')->get();

        return view('admin.event-participants.index', compact(
            'participants',
            'events',
            'pendingCount',
            'acceptedCount',
            'rejectedCount',
            'filterStatus',
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
            $paymentProofKey = Storage::url($path);
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

        return back()->with('success', "Peserta {$user->full_name} berhasil ditambahkan ke {$event->title}.");
    }

    public function verify(Request $request)
    {
        abort_unless(in_array(auth()->user()?->role, ['superadmin', 'admin_biasa']), 403);

        $request->validate([
            'user_id' => 'required|string',
            'event_id' => 'required|string',
            'action' => 'required|in:accept,reject',
        ]);

        $participant = DB::table('event_participant')
            ->where('user_id', $request->user_id)
            ->where('event_id', $request->event_id)
            ->first();

        if (!$participant) {
            return back()->with('error', 'Data tidak ditemukan.');
        }

        if ($participant->payment_verification === 'accepted') {
            return back()->with('error', 'Status yang sudah diterima tidak dapat diubah.');
        }

        $status = $request->action === 'accept' ? 'accepted' : 'rejected';

        DB::transaction(function () use ($request, $status) {
            DB::table('event_participant')
                ->where('user_id', $request->user_id)
                ->where('event_id', $request->event_id)
                ->update([
                    'payment_verification' => $status
                ]);

            // Sync with team if individual team exists
            $indTeam = \App\Models\Team::where('competition_id', $request->event_id)
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

        $request->validate([
            'user_id' => 'required|string',
            'event_id' => 'required|string',
        ]);

        $deleted = DB::table('event_participant')
            ->where('user_id', $request->user_id)
            ->where('event_id', $request->event_id)
            ->delete();

        if ($deleted) {
            return back()->with('success', 'Peserta berhasil dihapus dari kegiatan.');
        }

        return back()->with('error', 'Peserta tidak ditemukan.');
    }
}

