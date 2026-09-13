<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SemnasController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(in_array(auth()->user()?->role, ['superadmin', 'admin_biasa']), 403);

        // Ambil event Semnas untuk informasi Link WhatsApp Group & dropdown filter
        $semnasEvents = Event::where('type', 'non_competition')
            ->where(function ($q) {
                $q->where('title', 'like', '%Seminar%')
                  ->orWhere('id', 'like', '%semnas%');
            })
            ->orderBy('title')
            ->get();

        if ($semnasEvents->isEmpty()) {
            $semnasEvents = Event::where('type', 'non_competition')->orderBy('title')->get();
        }

        $semnasEvent = $semnasEvents->first();

        $eventFilter = $request->input('event_id');
        $filterStatus = $request->input('status', 'all');
        $search = $request->input('search');

        // Query peserta semnas lengkap dengan verifikasi pembayaran, user info, dan kuesioner
        $baseQuery = DB::table('event_participant')
            ->join('user', 'event_participant.user_id', '=', 'user.id')
            ->join('event', 'event_participant.event_id', '=', 'event.id')
            ->leftJoin('semnas_participant', function ($join) {
                $join->on('event_participant.user_id', '=', 'semnas_participant.user_id')
                     ->on('event_participant.event_id', '=', 'semnas_participant.event_id');
            })
            ->where(function ($q) {
                $q->whereNotNull('semnas_participant.id')
                  ->orWhere('event.title', 'like', '%Seminar%')
                  ->orWhere('event.id', 'like', '%semnas%');
            })
            ->select([
                'event_participant.user_id as user_id',
                'event_participant.event_id as event_id',
                'event.title as event_title',
                'event.whatsapp_group_link as whatsapp_group_link',
                'user.full_name as full_name',
                'user.email as email',
                'user.phone_number as phone_number',
                'user.nama_sekolah as nama_sekolah',
                'event_participant.payment_verification as payment_verification',
                'event_participant.date_added as date_added',
                'semnas_participant.id as semnas_id',
                'semnas_participant.kenal_sentral_komputer as kenal_sentral_komputer',
                'semnas_participant.sumber_kenal_sentral as sumber_kenal_sentral',
                'semnas_participant.kenal_acer as kenal_acer',
                'semnas_participant.kenal_nvidia as kenal_nvidia',
                'semnas_participant.kenal_microsoft as kenal_microsoft',
                'semnas_participant.ig_follow_proof_key as ig_follow_proof_key',
                'semnas_participant.created_at as semnas_submitted_at',
            ]);

        $query = DB::query()->fromSub($baseQuery, 'p');

        if ($eventFilter) {
            $query->where('p.event_id', $eventFilter);
        }

        if (in_array($filterStatus, ['pending', 'accepted', 'rejected'])) {
            $query->where('p.payment_verification', $filterStatus);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('p.full_name', 'like', "%{$search}%")
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

        $r2Public = rtrim(env('R2_PUBLIC', 'https://cdn.ittoday.web.id'), '/');

        foreach ($participants as $p) {
            // Resolve IG follow proof URL & File type (PDF / Image)
            if (!empty($p->ig_follow_proof_key)) {
                $p->ig_proof_url = str_starts_with($p->ig_follow_proof_key, 'http')
                    ? $p->ig_follow_proof_key
                    : $r2Public . '/' . ltrim($p->ig_follow_proof_key, '/');

                $extension = strtolower(pathinfo(parse_url($p->ig_proof_url, PHP_URL_PATH), PATHINFO_EXTENSION));
                $p->is_pdf = ($extension === 'pdf');
            } else {
                $p->ig_proof_url = null;
                $p->is_pdf = false;
            }

            // Format tanggal terdaftar
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
        }

        // Hitung statistik peserta (Pending, Accepted, Rejected)
        $statsQuery = DB::query()->fromSub($baseQuery, 'st');
        if ($eventFilter) {
            $statsQuery->where('st.event_id', $eventFilter);
        }

        $pendingCount = (clone $statsQuery)->where('st.payment_verification', 'pending')->count();
        $acceptedCount = (clone $statsQuery)->where('st.payment_verification', 'accepted')->count();
        $rejectedCount = (clone $statsQuery)->where('st.payment_verification', 'rejected')->count();

        $events = $semnasEvents;
        $filterEventId = $eventFilter;

        return view('admin.semnas.index', compact(
            'participants',
            'semnasEvents',
            'semnasEvent',
            'pendingCount',
            'acceptedCount',
            'rejectedCount',
            'filterStatus',
            'eventFilter',
            'filterEventId',
            'events',
            'search'
        ));
    }
}
