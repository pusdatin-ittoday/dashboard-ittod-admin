<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;

class UserExport
{
    public static array $headers = [
        'id',
        'email',
        'full_name',
        'birth_date',
        'pendidikan',
        'nama_sekolah',
        'entry_source',
        'phone_number',
        'id_line',
        'id_discord',
        'id_instagram',
        'ktm_key',
        'twibbon_key',
        'jenis_kelamin',
        'is_registration_complete',
        'created_at',
        'updated_at',
    ];

    /**
     * @param  resource  $handle
     * @param  array|null  $eventIds
     */
    public static function write($handle, ?array $eventIds = null): void
    {
        fputcsv($handle, self::$headers);

        $query = DB::table('user_identity')
            ->join('user', 'user_identity.id', '=', 'user.id')
            ->where('user_identity.role', 'user');

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

        $query->select([
            'user.id',
            'user_identity.email',
            'user.full_name',
            'user.birth_date',
            'user.pendidikan',
            'user.nama_sekolah',
            'user.entry_source',
            'user.phone_number',
            'user.id_line',
            'user.id_discord',
            'user.id_instagram',
            'user.ktm_key',
            'user.twibbon_key',
            'user.jenis_kelamin',
            'user.is_registration_complete',
            'user.created_at',
            'user.updated_at',
        ]);

        $query->orderBy('user.created_at');

        $query->chunk(100, function ($rows) use ($handle) {
            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->id,
                    $row->email,
                    $row->full_name,
                    $row->birth_date ? date('d/m/Y', strtotime($row->birth_date)) : '-',
                    $row->pendidikan ?? '-',
                    $row->nama_sekolah ?? '-',
                    $row->entry_source ?? '-',
                    $row->phone_number ?? '-',
                    $row->id_line ?? '-',
                    $row->id_discord ?? '-',
                    $row->id_instagram ?? '-',
                    $row->ktm_key ?? '-',
                    $row->twibbon_key ?? '-',
                    $row->jenis_kelamin ?? '-',
                    $row->is_registration_complete ? 'Lengkap' : 'Belum Lengkap',
                    $row->created_at ? date('d/m/Y', strtotime($row->created_at)) : '-',
                    $row->updated_at ? date('d/m/Y', strtotime($row->updated_at)) : '-',
                ]);
            }
        });
    }
}
