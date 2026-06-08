<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;

class ParticipantRecapExport
{
    public static array $headers = [
        'Event',
        'Nama Lengkap',
        'Email',
        'No. HP',
        'Pendidikan',
        'Nama Sekolah / Instansi',
        'Tanggal Daftar',
        'Status Pembayaran',
    ];

    private static array $paymentStatusMap = [
        'pending'  => 'Pending',
        'accepted' => 'Diterima',
        'rejected' => 'Ditolak',
    ];

    /**
     * @param  resource  $handle
     * @param  string|null  $eventId
     */
    public static function write($handle, ?string $eventId = null): void
    {
        fputcsv($handle, self::$headers);

        DB::table('event_participant')
            ->join('user', 'event_participant.user_id', '=', 'user.id')
            ->join('user_identity', 'user.id', '=', 'user_identity.id')
            ->join('event', 'event_participant.event_id', '=', 'event.id')
            ->when($eventId, fn ($q) => $q->where('event_participant.event_id', $eventId))
            ->select([
                'event.title as event_title',
                'user.full_name',
                'user_identity.email',
                'user.phone_number',
                'user.pendidikan',
                'user.nama_sekolah',
                'event_participant.date_added',
                'event_participant.payment_verification',
            ])
            ->orderBy('event_participant.date_added')
            ->chunk(100, function ($rows) use ($handle) {
                foreach ($rows as $row) {
                    fputcsv($handle, [
                        $row->event_title,
                        $row->full_name,
                        $row->email,
                        $row->phone_number ?? '-',
                        $row->pendidikan ?? '-',
                        $row->nama_sekolah ?? '-',
                        $row->date_added,
                        self::$paymentStatusMap[$row->payment_verification] ?? $row->payment_verification,
                    ]);
                }
            });
    }
}
