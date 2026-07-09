<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;

class UserExport
{
    public static array $headers = [
        'Nama Lengkap',
        'Email',
        'No. HP',
        'ID Discord',
        'Pendidikan',
        'Nama Sekolah / Instansi',
        'Status Registrasi',
        'Status Verifikasi Login',
        'Tanggal Daftar',
    ];

    /**
     * @param  resource  $handle
     * @param  array|null  $eventIds
     */
    public static function write($handle, ?array $eventIds = null): void
    {
        fputcsv(handle: $handle, fields: self::$headers);

        $query = DB::table('user_identity')
            ->join('user', 'user_identity.id', '=', 'user.id')
            ->where('user_identity.role', 'user')
            ->select([
                'user.id as user_id',
                'user.full_name',
                'user_identity.email',
                'user.phone_number',
                'user.id_discord',
                'user.pendidikan',
                'user.nama_sekolah',
                'user.is_registration_complete',
                'user_identity.is_verified',
                'user_identity.created_at',
            ])
            ->orderBy('user_identity.created_at');

        if ($eventIds !== null) {
            $query->where(function ($q) use ($eventIds) {
                $q->whereExists(function ($q2) use ($eventIds) {
                    $q2->select(DB::raw(1))
                        ->from('team_member')
                        ->join('team', 'team_member.team_id', '=', 'team.id')
                        ->whereColumn('team_member.user_id', 'user.id')
                        ->whereIn('team.competition_id', $eventIds);
                })->orWhereExists(function ($q2) use ($eventIds) {
                    $q2->select(DB::raw(1))
                        ->from('event_participant')
                        ->whereColumn('event_participant.user_id', 'user.id')
                        ->whereIn('event_participant.event_id', $eventIds);
                });
            });
        }

        $query->chunk(100, function ($rows) use ($handle) {
            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->full_name,
                    $row->email,
                    $row->phone_number ?? '-',
                    $row->id_discord ?? '-',
                    $row->pendidikan ?? '-',
                    $row->nama_sekolah ?? '-',
                    $row->is_registration_complete ? 'Lengkap' : 'Belum Lengkap',
                    $row->is_verified ? 'Terverifikasi' : 'Belum',
                    $row->created_at,
                ]);
            }
        });
    }
}
