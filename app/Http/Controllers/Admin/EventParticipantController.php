<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
                'event.title as event_title',
                'event.type as event_type'
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

        $participants = $query->orderByDesc('event_participant.date_added')->paginate(50)->withQueryString();

        $statsQuery = DB::table('event_participant')
            ->join('event', 'event_participant.event_id', '=', 'event.id')
            ->where('event.type', 'non_competition');
            
        $pendingCount = (clone $statsQuery)->where('payment_verification', 'pending')->count();
        $acceptedCount = (clone $statsQuery)->where('payment_verification', 'accepted')->count();
        $rejectedCount = (clone $statsQuery)->where('payment_verification', 'rejected')->count();

        return view('admin.event-participants.index', compact('participants', 'events', 'pendingCount', 'acceptedCount', 'rejectedCount', 'filterStatus'));
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

        DB::table('event_participant')
            ->where('user_id', $request->user_id)
            ->where('event_id', $request->event_id)
            ->update([
                'payment_verification' => $status
            ]);

        return back()->with('success', 'Status verifikasi berhasil diperbarui.');
    }
}
