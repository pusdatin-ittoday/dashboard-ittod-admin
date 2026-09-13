<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;

class SemnasParticipantExport
{
    public static array $headers = [
        'Nama',
        'NIM (Kartu Institusi)',
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
     * Resolve an R2 key or URL to a full public URL.
     */
    public static function resolveR2Url(?string $key): string
    {
        if (empty($key)) {
            return '-';
        }
        if (str_starts_with($key, 'http://') || str_starts_with($key, 'https://')) {
            return $key;
        }
        $base = rtrim(env('R2_PUBLIC', 'https://cdn.ittoday.web.id'), '/');
        return $base . '/' . ltrim($key, '/');
    }

    /**
     * Write CSV rows to the given file handle.
     *
     * @param  resource  $handle
     * @param  string|null  $eventId
     */
    public static function write($handle, ?string $eventId = null): void
    {
        fputcsv($handle, self::$headers);

        DB::table('event_participant')
            ->join('semnas_participant', function ($join) {
                $join->on('semnas_participant.user_id', '=', 'event_participant.user_id')
                     ->on('semnas_participant.event_id', '=', 'event_participant.event_id');
            })
            ->join('user', 'event_participant.user_id', '=', 'user.id')
            ->when($eventId, fn ($q) => $q->where('event_participant.event_id', $eventId))
            ->select([
                'user.full_name',
                'user.ktm_key',
                'user.nama_sekolah',
                'semnas_participant.kenal_sentral_komputer',
                'semnas_participant.sumber_kenal_sentral',
                'semnas_participant.kenal_acer',
                'semnas_participant.kenal_nvidia',
                'semnas_participant.kenal_microsoft',
                'user.id_instagram',
                'semnas_participant.ig_follow_proof_key',
            ])
            ->orderBy('event_participant.date_added')
            ->chunk(100, function ($rows) use ($handle) {
                foreach ($rows as $row) {
                    fputcsv($handle, [
                        $row->full_name,
                        self::resolveR2Url($row->ktm_key),
                        $row->nama_sekolah ?? '-',
                        $row->kenal_sentral_komputer ? 'Ya' : 'Tidak',
                        $row->sumber_kenal_sentral ?? '-',
                        $row->kenal_acer ? 'Ya' : 'Tidak',
                        $row->kenal_nvidia ? 'Ya' : 'Tidak',
                        $row->kenal_microsoft ? 'Ya' : 'Tidak',
                        $row->id_instagram ?? '-',
                        self::resolveR2Url($row->ig_follow_proof_key),
                    ]);
                }
            });
    }
}
