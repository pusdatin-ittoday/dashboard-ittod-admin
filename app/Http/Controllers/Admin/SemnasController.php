<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SemnasController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(in_array(auth()->user()?->role, ['superadmin', 'admin_biasa']), 403);

        $filterEventId = $request->input('event_id');

        // Ambil semua event bertipe non_competition untuk filter dropdown
        $events = Event::where('type', 'non_competition')->orderBy('title')->get();

        // Query peserta semnas: event_participant INNER JOIN semnas_participant JOIN user JOIN event
        $query = DB::table('event_participant')
            ->join('semnas_participant', function ($join) {
                $join->on('semnas_participant.user_id', '=', 'event_participant.user_id')
                     ->on('semnas_participant.event_id', '=', 'event_participant.event_id');
            })
            ->join('user', 'event_participant.user_id', '=', 'user.id')
            ->join('event', 'event_participant.event_id', '=', 'event.id')
            ->select([
                'user.id as user_id',
                'user.full_name',
                'user.nama_sekolah',
                'user.id_instagram',
                'user.ktm_key',
                'event.id as event_id',
                'event.title as event_title',
                'semnas_participant.kenal_sentral_komputer',
                'semnas_participant.sumber_kenal_sentral',
                'semnas_participant.kenal_acer',
                'semnas_participant.kenal_nvidia',
                'semnas_participant.kenal_microsoft',
                'semnas_participant.ig_follow_proof_key',
                'event_participant.date_added',
            ])
            ->when($filterEventId, fn ($q) => $q->where('event_participant.event_id', $filterEventId))
            ->orderByDesc('event_participant.date_added');

        $participants = $query->paginate(50)->withQueryString();

        $r2Public = rtrim(env('R2_PUBLIC', 'https://cdn.ittoday.web.id'), '/');

        foreach ($participants as $p) {
            // Resolve KTM URL
            if ($p->ktm_key) {
                $p->ktm_url = str_starts_with($p->ktm_key, 'http')
                    ? $p->ktm_key
                    : $r2Public . '/' . ltrim($p->ktm_key, '/');
            } else {
                $p->ktm_url = null;
            }

            // Resolve IG follow proof URL
            if ($p->ig_follow_proof_key) {
                $p->ig_follow_url = str_starts_with($p->ig_follow_proof_key, 'http')
                    ? $p->ig_follow_proof_key
                    : $r2Public . '/' . ltrim($p->ig_follow_proof_key, '/');
            } else {
                $p->ig_follow_url = null;
            }
        }

        return view('admin.semnas.index', compact(
            'participants',
            'events',
            'filterEventId',
        ));
    }
}
