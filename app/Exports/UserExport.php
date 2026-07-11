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
        $hasSingleEvent = $eventIds !== null && count($eventIds) === 1;

        if ($hasSingleEvent) {
            fputcsv($handle, array_merge(self::$headers, [
                'Nama Kompetisi',
                'Nama Tim',
                'Posisi'
            ]));
        } else {
            fputcsv($handle, self::$headers);
        }

        $query = DB::table('user_identity')
            ->join('user', 'user_identity.id', '=', 'user.id')
            ->where('user_identity.role', 'user');

        if ($eventIds !== null) {
            if ($hasSingleEvent) {
                $eventId = $eventIds[0];
                $query->leftJoin('team_member', function($join) use ($eventId) {
                    $join->on('team_member.user_id', '=', 'user.id')
                         ->whereIn('team_member.team_id', function($q) use ($eventId) {
                             $q->select('id')->from('team')->where('competition_id', $eventId);
                         });
                })
                ->leftJoin('team', 'team_member.team_id', '=', 'team.id')
                ->leftJoin('event_participant', function($join) use ($eventId) {
                    $join->on('event_participant.user_id', '=', 'user.id')
                         ->where('event_participant.event_id', '=', $eventId);
                })
                ->leftJoin('event', 'event.id', '=', DB::raw("'$eventId'"))
                ->where(function($q) {
                    $q->whereNotNull('team_member.id')
                      ->orWhereNotNull('event_participant.id');
                })
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
                    'event.title as event_title',
                    'team.team_name',
                    'team_member.role as team_role',
                    'event_participant.id as participant_id'
                ]);
            } else {
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
                })
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
                ]);
            }
        } else {
            $query->select([
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
            ]);
        }

        $query->orderBy('user_identity.created_at');

        $query->chunk(100, function ($rows) use ($handle, $hasSingleEvent) {
            foreach ($rows as $row) {
                if ($hasSingleEvent) {
                    $posisi = '-';
                    if ($row->team_role) {
                        $posisi = $row->team_role === 'leader' ? 'Ketua' : 'Anggota';
                    } elseif ($row->participant_id) {
                        $posisi = 'Peserta Seminar';
                    }

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
                        $row->event_title ?? '-',
                        $row->team_name ?? '-',
                        $posisi,
                    ]);
                } else {
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
            }
        });
    }
}
