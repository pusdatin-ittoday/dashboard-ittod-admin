<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;

class FinalistExport
{
    public static array $headers = [
        'No',
        'Tim / Peserta',
        'Tipe',
        'Kode Tim',
        'Cabang Kompetisi',
        'Ketua / Kontak Utama',
        'Email',
        'No HP',
        'Asal Sekolah / Institusi',
        'Daftar Anggota & Peran',
        'Status Finalis',
        'Peringkat Juara',
    ];

    /**
     * Write finalist data to a stream handle.
     *
     * @param resource $handle
     * @param Builder  $query
     */
    public static function write($handle, Builder $query): void
    {
        fputcsv($handle, self::$headers);

        $no = 1;

        // Clone query with eager loading to prevent mutating original builder
        (clone $query)
            ->with(['event', 'members.user.identity'])
            ->chunk(100, function ($teams) use ($handle, &$no) {
                foreach ($teams as $team) {
                    $isIndividual = $team->event?->participation_type === 'individual';
                    $primaryMember = $team->members->firstWhere('role', 'leader') ?? $team->members->first();
                    $displayName = $isIndividual
                        ? ($primaryMember?->user?->full_name ?? 'Peserta')
                        : $team->team_name;

                    // Build member list string
                    $memberDetails = [];
                    foreach ($team->members as $member) {
                        $roleLabel = $isIndividual ? 'Peserta' : ($member->role === 'leader' ? 'Ketua' : 'Anggota');
                        $name = $member->user?->full_name ?? '-';
                        $school = $member->user?->nama_sekolah ? " ({$member->user->nama_sekolah})" : '';
                        $memberDetails[] = "{$roleLabel}: {$name}{$school}";
                    }
                    $membersSummary = implode('; ', $memberDetails);

                    // Finalist & Winner status
                    if ($team->rank) {
                        $statusFinalis = 'Juara ' . $team->rank;
                        $rankLabel = (string) $team->rank;
                    } elseif ($team->is_finalist) {
                        $statusFinalis = 'Finalis';
                        $rankLabel = '-';
                    } else {
                        $statusFinalis = 'Belum Ditandai';
                        $rankLabel = '-';
                    }

                    $primaryUser = $primaryMember?->user;
                    $identity = $primaryUser?->identity;

                    $row = [
                        $no++,
                        $displayName,
                        $isIndividual ? 'Individu' : 'Tim',
                        $isIndividual ? '-' : ($team->team_code ?? '-'),
                        $team->event?->title ?? $team->competition_id,
                        $primaryUser?->full_name ?? '-',
                        $primaryUser?->email ?? $identity?->email ?? '-',
                        $primaryUser?->phone_number ?? '-',
                        $primaryUser?->nama_sekolah ?? '-',
                        $membersSummary ?: '-',
                        $statusFinalis,
                        $rankLabel,
                    ];

                    fputcsv($handle, $row);
                }
            });
    }
}
