<?php

namespace App\Exports;

use App\Models\Event;
use App\Models\CompetitionSubmission;

class SubmissionExport
{
    /**
     * @param  resource  $handle
     * @param  string  $eventId
     */
    public static function write($handle, string $eventId): void
    {
        $event = Event::with('submissions.team.members.user')->findOrFail($eventId);
        
        $headers = [
            'Nama Tim/Peserta',
            'Nama Kompetisi',
            'Tanggal Submit',
        ];
        
        $submissionKeys = [];
        if (!empty($event->submission_fields)) {
            foreach ($event->submission_fields as $field) {
                if (isset($field['label'])) {
                    $submissionKeys[$field['label']] = true;
                }
            }
        }
        
        foreach ($event->submissions as $sub) {
            $subObj = is_string($sub->submission_object) ? json_decode($sub->submission_object, true) : $sub->submission_object;
            if (is_array($subObj)) {
                foreach (array_keys($subObj) as $key) {
                    $submissionKeys[$key] = true;
                }
            }
        }
        $submissionKeys = array_keys($submissionKeys);
        
        $exportHeaders = array_merge($headers, $submissionKeys);

        fputcsv($handle, $exportHeaders);

        foreach ($event->submissions as $sub) {
            $isIndividual = $event->participation_type === 'individual';
            
            $displayName = $sub->team->team_name;
            if ($isIndividual) {
                $primaryMember = $sub->team->members->firstWhere('role', 'leader') ?? $sub->team->members->first();
                $displayName = $primaryMember?->user?->full_name ?? 'Peserta Individual';
            }
            
            $row = [
                $displayName,
                $event->title,
                $sub->created_at ? $sub->created_at->format('Y-m-d H:i:s') : '-',
            ];
            
            $subObj = is_string($sub->submission_object) ? json_decode($sub->submission_object, true) : $sub->submission_object;
            if (!is_array($subObj)) $subObj = [];

            foreach ($submissionKeys as $key) {
                $val = $subObj[$key] ?? '-';
                if (is_array($val) || is_object($val)) {
                    $row[] = json_encode($val);
                } else {
                    $row[] = (string) $val;
                }
            }
            
            fputcsv($handle, $row);
        }
    }
}
