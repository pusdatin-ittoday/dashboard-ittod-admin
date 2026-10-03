<?php

namespace App\Exports;

use App\Models\Event;
use App\Models\Team;
use Illuminate\Support\Facades\DB;

class TeamListExport
{
    /**
     * Write CSV/Sheet content according to event type and filter conditions.
     *
     * @param  resource  $handle
     * @param  array     $filters
     */
    public static function write($handle, array $filters = []): void
    {
        $eventId = $filters['event_id'] ?? null;
        $event = $eventId && !in_array($eventId, ['all_teams', 'all_events', 'all_global'], true) 
            ? Event::find($eventId) 
            : null;

        // 1. Non-competition events (Single or All)
        if (($event && $event->type === 'non_competition') || $eventId === 'all_events') {
            self::writeNonCompetitionEvents($handle, $event, $filters);
            return;
        }

        // 2. Individual Competition
        if ($event && $event->type === 'competition' && $event->participation_type === 'individual') {
            self::writeIndividualCompetition($handle, $event, $filters);
            return;
        }

        // 3. Team Competition (Single or All Competitions)
        self::writeTeamCompetition($handle, $event, $filters);
    }

    /**
     * Format 1: Kompetisi - Tim
     * Headers: Nama_Kompetisi, Nama_Tim, Nama, Posisi_Tim, Instansi, Status Berkas, Status Pembayaran, Waktu Daftar
     * Dikelompokkan berdasarkan nama_timnya dan nama_kompetisinya
     */
    protected static function writeTeamCompetition($handle, ?Event $event, array $filters): void
    {
        fputcsv($handle, [
            'Nama_Kompetisi',
            'Nama_Tim',
            'Nama',
            'Posisi_Tim',
            'Instansi',
            'Status Berkas',
            'Status Pembayaran',
            'Waktu Daftar',
        ]);

        $query = Team::with(['event', 'members.user']);

        // Panitia restricted access
        if (auth()->check() && auth()->user()->role === 'panitia_lomba') {
            $assignedEventIds = auth()->user()->events->pluck('id')->toArray();
            $query->whereIn('competition_id', $assignedEventIds);
        }

        if ($event) {
            $query->where('competition_id', $event->id);
        } else {
            $query->whereHas('event', function ($q) {
                $q->where('type', 'competition');
            });
        }

        self::applyTeamFilters($query, $filters);

        // Group / order by competition and team name
        $query->orderBy('competition_id', 'asc')
              ->orderBy('team_name', 'asc');

        $query->chunk(100, function ($teams) use ($handle) {
            foreach ($teams as $team) {
                $compName = $team->event?->title ?? $team->competition_id;
                $statusBerkas = self::resolveStatusBerkas($team->is_document_verified);
                $statusBayar = self::resolveStatusPembayaran($team->is_verified, !empty($team->payment_proof_id));
                $waktuDaftar = $team->created_at ? $team->created_at->format('d/m/Y H:i') : '-';

                $members = $team->members;
                if ($members->isEmpty()) {
                    fputcsv($handle, [
                        $compName,
                        $team->team_name,
                        '-',
                        '-',
                        '-',
                        $statusBerkas,
                        $statusBayar,
                        $waktuDaftar,
                    ]);
                } else {
                    foreach ($members as $member) {
                        $user = $member->user;
                        $posisi = $member->role === 'leader' ? 'Ketua' : 'Anggota';
                        $instansi = $user?->nama_sekolah ?: '-';

                        fputcsv($handle, [
                            $compName,
                            $team->team_name,
                            $user?->full_name ?? '-',
                            $posisi,
                            $instansi,
                            $statusBerkas,
                            $statusBayar,
                            $waktuDaftar,
                        ]);
                    }
                }
            }
        });
    }

    /**
     * Format 1: Kompetisi - Individu
     * Headers: Nama_Kompetisi, Nama, Status Berkas, Status Pembayaran, Waktu_Daftar
     */
    protected static function writeIndividualCompetition($handle, Event $event, array $filters): void
    {
        fputcsv($handle, [
            'Nama_Kompetisi',
            'Nama',
            'Status Berkas',
            'Status Pembayaran',
            'Waktu_Daftar',
        ]);

        $query = Team::with(['event', 'members.user'])
            ->where('competition_id', $event->id);

        self::applyTeamFilters($query, $filters);

        $query->orderBy('created_at', 'asc');

        $query->chunk(100, function ($teams) use ($handle, $event) {
            foreach ($teams as $team) {
                $primaryMember = $team->members->firstWhere('role', 'leader') ?? $team->members->first();
                $userName = $primaryMember?->user?->full_name ?? ($team->team_name ?? '-');
                $statusBerkas = self::resolveStatusBerkas($team->is_document_verified);
                $statusBayar = self::resolveStatusPembayaran($team->is_verified, !empty($team->payment_proof_id));
                $waktuDaftar = $team->created_at ? $team->created_at->format('d/m/Y H:i') : '-';

                fputcsv($handle, [
                    $event->title,
                    $userName,
                    $statusBerkas,
                    $statusBayar,
                    $waktuDaftar,
                ]);
            }
        });
    }

    /**
     * Format 2: Event (Non-Kompetisi)
     * Headers: Nama_Event, Nama_Peserta, Status_Berkas, Status_Pembayaran, Waktu_Daftar
     */
    protected static function writeNonCompetitionEvents($handle, ?Event $event, array $filters): void
    {
        fputcsv($handle, [
            'Nama_Event',
            'Nama_Peserta',
            'Status_Berkas',
            'Status_Pembayaran',
            'Waktu_Daftar',
        ]);

        $query = DB::table('event_participant')
            ->join('user', 'event_participant.user_id', '=', 'user.id')
            ->join('event', 'event_participant.event_id', '=', 'event.id')
            ->where('event.type', 'non_competition');

        if ($event) {
            $query->where('event_participant.event_id', $event->id);
        }

        // Apply filters
        if (!empty($filters['status_berkas'])) {
            $sb = strtolower($filters['status_berkas']);
            if (in_array($sb, ['verified', 'approved', '1'], true)) {
                $query->where('user.is_registration_complete', 1);
            } elseif (in_array($sb, ['rejected', '0'], true)) {
                $query->where('user.is_registration_complete', 0);
            } elseif ($sb === 'pending') {
                $query->where(function ($q) {
                    $q->whereNull('user.is_registration_complete')
                      ->orWhere('user.is_registration_complete', 0);
                });
            }
        }

        if (!empty($filters['status_pembayaran'])) {
            $sp = strtolower($filters['status_pembayaran']);
            if (in_array($sp, ['verified', 'approved', '1', 'lunas'], true)) {
                $query->where('event_participant.payment_verification', 'accepted');
            } elseif (in_array($sp, ['rejected', '0', 'ditolak'], true)) {
                $query->where('event_participant.payment_verification', 'rejected');
            } elseif (in_array($sp, ['unverified', 'pending_verification'], true)) {
                $query->where('event_participant.payment_verification', 'pending')
                      ->whereNotNull('event_participant.payment_proof');
            } elseif (in_array($sp, ['unpaid', 'pending', 'belum_bayar'], true)) {
                $query->where('event_participant.payment_verification', 'pending')
                      ->whereNull('event_participant.payment_proof');
            }
        }

        if (!empty($filters['batch'])) {
            $batch = $filters['batch'];
            if ($batch === 'batch_1' || $batch === 'batch1') {
                $query->where('event_participant.date_added', '<=', '2026-07-31 23:59:59');
            } elseif ($batch === 'batch_2' || $batch === 'batch2') {
                $query->where('event_participant.date_added', '>=', '2026-08-01 00:00:00');
            } elseif ($batch === 'today') {
                $query->whereDate('event_participant.date_added', now()->today());
            } elseif ($batch === 'this_week') {
                $query->whereBetween('event_participant.date_added', [now()->startOfWeek(), now()->endOfWeek()]);
            } elseif ($batch === 'this_month') {
                $query->whereYear('event_participant.date_added', now()->year)
                      ->whereMonth('event_participant.date_added', now()->month);
            }
        }

        if (!empty($filters['search'])) {
            $search = strtolower(trim($filters['search']));
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(user.full_name) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(user.email) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(user.nama_sekolah) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(user.phone_number) LIKE ?', ["%{$search}%"]);
            });
        }

        $query->select([
            'event.title as event_title',
            'user.full_name',
            'user.is_registration_complete',
            'event_participant.payment_verification',
            'event_participant.payment_proof',
            'event_participant.date_added',
        ])
        ->orderBy('event_participant.date_added', 'asc');

        $query->chunk(100, function ($rows) use ($handle) {
            foreach ($rows as $row) {
                $statusBerkas = $row->is_registration_complete ? 'Terverifikasi' : 'Pending';

                if ($row->payment_verification === 'accepted') {
                    $statusBayar = 'Bayar Lunas';
                } elseif ($row->payment_verification === 'rejected') {
                    $statusBayar = 'Bayar Ditolak';
                } elseif (!empty($row->payment_proof)) {
                    $statusBayar = 'Belum Diverifikasi';
                } else {
                    $statusBayar = 'Belum Bayar';
                }

                $waktuDaftar = $row->date_added ? date('d/m/Y H:i', strtotime($row->date_added)) : '-';

                fputcsv($handle, [
                    $row->event_title,
                    $row->full_name ?? '-',
                    $statusBerkas,
                    $statusBayar,
                    $waktuDaftar,
                ]);
            }
        });
    }

    /**
     * Apply common team filters (status_berkas, status_pembayaran, batch, search).
     */
    protected static function applyTeamFilters($query, array $filters): void
    {
        if (!empty($filters['status_berkas'])) {
            $sb = strtolower($filters['status_berkas']);
            if (in_array($sb, ['verified', 'approved', '1'], true)) {
                $query->where(function ($q) {
                    $q->where('is_document_verified', 'verified')
                      ->orWhere('is_document_verified', 'approved')
                      ->orWhere('is_document_verified', '1');
                });
            } elseif (in_array($sb, ['rejected', '0'], true)) {
                $query->where(function ($q) {
                    $q->where('is_document_verified', 'rejected')
                      ->orWhere('is_document_verified', '0');
                });
            } elseif ($sb === 'pending') {
                $query->where(function ($q) {
                    $q->where('is_document_verified', 'pending')
                      ->orWhereNull('is_document_verified');
                });
            }
        }

        if (!empty($filters['status_pembayaran'])) {
            $sp = strtolower($filters['status_pembayaran']);
            if (in_array($sp, ['verified', 'approved', '1', 'lunas'], true)) {
                $query->where(function ($q) {
                    $q->where('is_verified', 'approved')
                      ->orWhere('is_verified', 'verified')
                      ->orWhere('is_verified', '1');
                });
            } elseif (in_array($sp, ['rejected', '0', 'ditolak'], true)) {
                $query->where(function ($q) {
                    $q->where('is_verified', 'rejected')
                      ->orWhere('is_verified', '0');
                });
            } elseif (in_array($sp, ['unverified', 'pending_verification', 'belum_diverifikasi'], true)) {
                $query->whereNotNull('payment_proof_id')
                      ->where(function ($q) {
                          $q->where('is_verified', 'pending')
                            ->orWhereNull('is_verified');
                      });
            } elseif (in_array($sp, ['unpaid', 'pending', 'belum_bayar'], true)) {
                $query->whereNull('payment_proof_id')
                      ->where(function ($q) {
                          $q->where('is_verified', 'pending')
                            ->orWhereNull('is_verified');
                      });
            }
        }

        if (!empty($filters['batch'])) {
            $batch = $filters['batch'];
            if ($batch === 'batch_1' || $batch === 'batch1') {
                $query->where('created_at', '<=', '2026-07-31 23:59:59');
            } elseif ($batch === 'batch_2' || $batch === 'batch2') {
                $query->where('created_at', '>=', '2026-08-01 00:00:00');
            } elseif ($batch === 'today') {
                $query->whereDate('created_at', now()->today());
            } elseif ($batch === 'this_week') {
                $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
            } elseif ($batch === 'this_month') {
                $query->whereYear('created_at', now()->year)->whereMonth('created_at', now()->month);
            }
        }

        if (!empty($filters['search'])) {
            $search = strtolower(trim($filters['search']));
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(team_name) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(team_code) LIKE ?', ["%{$search}%"])
                  ->orWhereHas('members.user', function ($uq) use ($search) {
                      $uq->whereRaw('LOWER(full_name) LIKE ?', ["%{$search}%"])
                         ->orWhereRaw('LOWER(email) LIKE ?', ["%{$search}%"])
                         ->orWhereRaw('LOWER(nama_sekolah) LIKE ?', ["%{$search}%"])
                         ->orWhereRaw('LOWER(phone_number) LIKE ?', ["%{$search}%"]);
                  });
            });
        }
    }

    /**
     * Resolve human-readable status berkas label.
     */
    protected static function resolveStatusBerkas(?string $status): string
    {
        if (in_array($status, ['verified', 'approved', '1', 1], true)) {
            return 'Terverifikasi';
        }
        if (in_array($status, ['rejected', '0'], true)) {
            return 'Ditolak';
        }
        return 'Pending';
    }

    /**
     * Resolve human-readable status pembayaran label.
     */
    protected static function resolveStatusPembayaran(?string $status, bool $hasPaymentProof): string
    {
        if (in_array($status, ['approved', 'verified', '1', 1], true)) {
            return 'Bayar Lunas';
        }
        if (in_array($status, ['rejected', '0'], true)) {
            return 'Bayar Ditolak';
        }
        if ($hasPaymentProof) {
            return 'Belum Diverifikasi';
        }
        return 'Belum Bayar';
    }
}
