<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;

class ParticipantRecapExport
{
    public static array $headers = [
        'id',
        'full_name',
        'phone_number',
        'nama_sekolah',
        'event_id',
        'is_minetoday',
        'date_added',
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
            ->when($eventId, fn ($q) => $q->where('event_participant.event_id', $eventId))
            ->select([
                'user.id',
                'user.full_name',
                'user.phone_number',
                'user.nama_sekolah',
                'event_participant.event_id',
                'event_participant.date_added',
                DB::raw('EXISTS (
                    SELECT 1 
                    FROM team_member 
                    JOIN team ON team_member.team_id = team.id 
                    WHERE team_member.user_id = user.id 
                      AND (LOWER(team.competition_id) = "minetoday" OR LOWER(team.competition_id) = "mine-today")
                ) as is_minetoday'),
            ])
            ->orderBy('event_participant.date_added')
            ->chunk(100, function ($rows) use ($handle) {
                foreach ($rows as $row) {
                    fputcsv($handle, [
                        $row->id,
                        $row->full_name,
                        $row->phone_number ?? '-',
                        $row->nama_sekolah ?? '-',
                        $row->event_id,
                        $row->is_minetoday ? 'Ya' : 'Tidak',
                        $row->date_added ? date('d/m/Y', strtotime($row->date_added)) : '-',
                    ]);
                }
            });
    }
}

