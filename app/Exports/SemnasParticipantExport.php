<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;

class SemnasParticipantExport
{
    public static array $headers = [
        'Nama',
        'Institusi',
        'Kenal Sentral Komputer',
        'Sumber Kenal Sentral',
        'Kenal Acer',
        'Kenal NVIDIA',
        'Kenal Microsoft',
        'Akun IG',
        'isFollowingIgNarsum',
    ];

    /**
     * Write CSV rows to the given file handle.
     *
     * @param  resource  $handle
     * @param  string|null  $eventId
     */
    public static function write($handle, ?string $eventId = null): void
    {
        fputcsv($handle, self::$headers);

        $query = DB::table('event_participant')
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
                'user.full_name',
                'user.nama_sekolah',
                'semnas_participant.kenal_sentral_komputer',
                'semnas_participant.sumber_kenal_sentral',
                'semnas_participant.kenal_acer',
                'semnas_participant.kenal_nvidia',
                'semnas_participant.kenal_microsoft',
                'user.id_instagram',
                'event_participant.payment_verification',
            ]);

        if ($eventId) {
            $query->where('event_participant.event_id', $eventId);
        }

        $query->orderBy('event_participant.date_added')
            ->chunk(100, function ($rows) use ($handle) {
                foreach ($rows as $row) {
                    $isFollowing = ($row->payment_verification === 'accepted');

                    fputcsv($handle, [
                        $row->full_name,
                        $row->nama_sekolah ?? '-',
                        $row->kenal_sentral_komputer ? 'Ya' : 'Tidak',
                        $row->sumber_kenal_sentral ?? '-',
                        $row->kenal_acer ? 'Ya' : 'Tidak',
                        $row->kenal_nvidia ? 'Ya' : 'Tidak',
                        $row->kenal_microsoft ? 'Ya' : 'Tidak',
                        $row->id_instagram ?? '-',
                        $isFollowing ? 'TRUE' : 'FALSE',
                    ]);
                }
            });
    }
}
