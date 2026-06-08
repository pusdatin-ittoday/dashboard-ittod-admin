<?php

namespace App\Exports;

use App\Models\Team;

class TeamRecapExport
{
    public static array $headers = [
        'Event / Kompetisi',
        'Nama Tim',
        'Kode Tim',
        'Nama Ketua',
        'Email Ketua',
        'No. HP Ketua',
        'Jumlah Anggota',
        'Status Verifikasi Tim',
        'Catatan Error Verifikasi',
        'Bukti Bayar',
        'Tanggal Daftar',
    ];

    /**
     * @param  resource  $handle
     * @param  string|null  $eventId
     */
    public static function write($handle, ?string $eventId = null): void
    {
        fputcsv($handle, self::$headers);

        Team::with([
            'event',
            'members.user.identity',
        ])
            ->when($eventId, fn ($q) => $q->where('competition_id', $eventId))
            ->orderBy('created_at')
            ->chunk(100, function ($teams) use ($handle) {
                foreach ($teams as $team) {
                    $leader = $team->members->firstWhere('role', 'leader');

                    fputcsv($handle, [
                        $team->event?->title ?? '-',
                        $team->team_name,
                        $team->team_code,
                        $leader?->user?->full_name ?? '-',
                        $leader?->user?->identity?->email ?? '-',
                        $leader?->user?->phone_number ?? '-',
                        $team->members->count(),
                        $team->is_verified ? 'Ya' : 'Tidak',
                        $team->verification_error ?? '-',
                        $team->payment_proof_id ? 'Ada' : 'Belum',
                        $team->created_at?->format('d/m/Y H:i') ?? '-',
                    ]);
                }
            });
    }
}
