<?php

namespace App\Exports;

use App\Models\Team;

class TeamRecapExport
{
    public static array $headers = [
        'team_name',
        'competition_id',
        'full_name',
        'email',
        'phone_number',
        'id_line',
        'id_discord',
        'nama_sekolah',
        'role',
        'is_registration_complete',
        'team_verified',
    ];

    /**
     * @param  resource  $handle
     * @param  string|null  $eventId
     */
    public static function write($handle, ?string $eventId = null): void
    {
        $headers = self::$headers;
        $isIndividualEvent = false;

        if ($eventId) {
            $event = \App\Models\Event::find($eventId);
            if ($event && $event->participation_type === 'individual') {
                $isIndividualEvent = true;
                // Remove 'role' and 'team_verified' columns
                $headers = array_values(array_diff($headers, ['role', 'team_verified']));
            }
        }

        // 1. Gather dynamic submission keys
        $submissionKeys = [];
        $querySubmissions = \App\Models\CompetitionSubmission::whereNotNull('submission_object');
        if ($eventId) {
            $querySubmissions->where('competition_id', $eventId);
        }
        
        $submissions = $querySubmissions->get(['submission_object']);
        foreach ($submissions as $sub) {
            if (is_array($sub->submission_object)) {
                foreach (array_keys($sub->submission_object) as $key) {
                    $submissionKeys[$key] = true;
                }
            }
        }
        $submissionKeys = array_keys($submissionKeys);
        
        // 2. Append submission keys to headers
        $headers = array_merge($headers, $submissionKeys);

        fputcsv($handle, $headers);

        Team::with([
            'event',
            'members.user.identity',
            'submissions'
        ])
            ->when($eventId, fn ($q) => $q->where('competition_id', $eventId))
            ->orderBy('created_at')
            ->chunk(100, function ($teams) use ($handle, $isIndividualEvent, $submissionKeys) {
                foreach ($teams as $team) {
                    $isIndividual = $team->event?->participation_type === 'individual';
                    $submissionObj = $team->submissions->first()?->submission_object ?? [];
                    
                    foreach ($team->members as $member) {
                        $user = $member->user;
                        $identity = $user?->identity;

                        $row = [
                            $isIndividual ? ($user?->full_name ?? '-') : $team->team_name,
                            $team->competition_id,
                            $user?->full_name ?? '-',
                            $identity?->email ?? '-',
                            $user?->phone_number ?? '-',
                            $user?->id_line ?? '-',
                            $user?->id_discord ?? '-',
                            $user?->nama_sekolah ?? '-',
                        ];

                        if (!$isIndividualEvent) {
                            if ($isIndividual) {
                                $row[] = '-'; // role
                                $row[] = $user?->is_registration_complete ? 'Lengkap' : 'Belum Lengkap';
                                $row[] = '-'; // team_verified
                            } else {
                                $row[] = $member->role;
                                $row[] = $user?->is_registration_complete ? 'Lengkap' : 'Belum Lengkap';
                                $row[] = $team->is_verified === 'approved' ? 'Approved' : 'Pending';
                            }
                        } else {
                            $row[] = $user?->is_registration_complete ? 'Lengkap' : 'Belum Lengkap';
                        }
                        
                        // Append submission fields
                        foreach ($submissionKeys as $key) {
                            $val = $submissionObj[$key] ?? '-';
                            if (is_array($val) || is_object($val)) {
                                $row[] = json_encode($val);
                            } else {
                                $row[] = (string) $val;
                            }
                        }

                        fputcsv($handle, $row);
                    }
                }
            });
    }
}
