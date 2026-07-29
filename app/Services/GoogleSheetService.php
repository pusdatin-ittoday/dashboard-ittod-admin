<?php

namespace App\Services;

use Google\Client;
use Google\Service\Drive;
use Google\Service\Sheets;
use Google\Service\Sheets\ValueRange;
use App\Models\Event;
use Illuminate\Support\Facades\DB;
use Exception;

class GoogleSheetService
{
    protected $client;
    protected $sheetService;
    protected string $masterSpreadsheetId;

    public function __construct()
    {
        $this->client = new Client();

        $base64Credentials = env('GOOGLE_SHEETS_CREDENTIALS_BASE64');
        if ($base64Credentials) {
            $decoded = base64_decode($base64Credentials);
            $json = json_decode($decoded, true);
            if (!$json) {
                throw new Exception("Failed to decode GOOGLE_SHEETS_CREDENTIALS_BASE64. Please ensure it is a valid base64-encoded JSON string.");
            }
            $this->client->setAuthConfig($json);
        } else {
            $credentialsPath = env('GOOGLE_SHEETS_CREDENTIALS', storage_path('app/google/service-account.json'));

            if (!file_exists($credentialsPath)) {
                throw new Exception("Google Service Account credentials not found. Please place service-account.json at {$credentialsPath} or set GOOGLE_SHEETS_CREDENTIALS_BASE64 in your environment.");
            }
            $this->client->setAuthConfig($credentialsPath);
        }

        $this->client->addScope([
            Sheets::SPREADSHEETS,
        ]);

        $this->sheetService = new Sheets($this->client);

        $this->masterSpreadsheetId = env('GOOGLE_SHEETS_SPREADSHEET_ID');
        if (!$this->masterSpreadsheetId) {
            throw new Exception("GOOGLE_SHEETS_SPREADSHEET_ID is not set in environment variables.");
        }
    }

    /**
     * Export users to a tab in the master Google Spreadsheet.
     *
     * @param string|null $eventId
     * @return string URL of the Google Spreadsheet
     */
    public function exportUsers(?string $eventId = null): string
    {
        $sheetTitle = 'Data User';
        if ($eventId) {
            $event = Event::find($eventId);
            if ($event) {
                $sheetTitle = substr('User - ' . preg_replace('/[^A-Za-z0-9 _-]/', '', $event->title), 0, 30);
            }
        }

        // Fetch user data
        $query = DB::table('user_identity')
            ->join('user', 'user_identity.id', '=', 'user.id')
            ->where('user_identity.role', 'user');

        if ($eventId !== null) {
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
                $q->whereNotNull('team_member.team_id')
                  ->orWhereNotNull('event_participant.event_id');
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
                'event_participant.event_id as participant_id'
            ]);
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
        $rows = $query->get();

        if ($eventId !== null) {
            $values = [[
                'Nama Lengkap', 'Email', 'No. HP', 'ID Discord',
                'Pendidikan', 'Nama Sekolah / Instansi',
                'Status Registrasi', 'Status Verifikasi Login', 'Tanggal Daftar',
                'Nama Kompetisi', 'Nama Tim', 'Posisi',
            ]];
        } else {
            $values = [[
                'Nama Lengkap', 'Email', 'No. HP', 'ID Discord',
                'Pendidikan', 'Nama Sekolah / Instansi',
                'Status Registrasi', 'Status Verifikasi Login', 'Tanggal Daftar',
            ]];
        }

        foreach ($rows as $row) {
            if ($eventId !== null) {
                $posisi = '-';
                if ($row->team_role) {
                    $posisi = $row->team_role === 'leader' ? 'Ketua' : 'Anggota';
                } elseif ($row->participant_id) {
                    $posisi = 'Peserta';
                }

                $values[] = [
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
                ];
            } else {
                $values[] = [
                    $row->full_name,
                    $row->email,
                    $row->phone_number ?? '-',
                    $row->id_discord ?? '-',
                    $row->pendidikan ?? '-',
                    $row->nama_sekolah ?? '-',
                    $row->is_registration_complete ? 'Lengkap' : 'Belum Lengkap',
                    $row->is_verified ? 'Terverifikasi' : 'Belum',
                    $row->created_at,
                ];
            }
        }

        $gid = $this->writeToSheet($this->masterSpreadsheetId, $sheetTitle, $values);

        return "https://docs.google.com/spreadsheets/d/{$this->masterSpreadsheetId}/edit#gid={$gid}";
    }

    /**
     * Export team or participant recaps to a tab in the master Google Spreadsheet.
     *
     * @param string $type ('teams_global', 'participants_global', 'teams_event', 'participants_event')
     * @param string|null $eventId
     * @return string URL of the Google Spreadsheet
     */
    public function exportRecap(string $type, ?string $eventId = null): string
    {
        if ($type === 'teams_global') {
            $sheetTitle = 'Semua Tim';
            $writeCallback = function($handle) {
                \App\Exports\TeamRecapExport::write($handle, null);
            };
        } elseif ($type === 'participants_global') {
            $sheetTitle = 'Semua Peserta';
            $writeCallback = function($handle) {
                \App\Exports\ParticipantRecapExport::write($handle, null);
            };
        } elseif ($type === 'teams_event') {
            $event = Event::findOrFail($eventId);
            $sheetTitle = substr('Tim - ' . preg_replace('/[^A-Za-z0-9 _-]/', '', $event->title), 0, 30);
            $writeCallback = function($handle) use ($eventId) {
                \App\Exports\TeamRecapExport::write($handle, $eventId);
            };
        } elseif ($type === 'participants_event') {
            $event = Event::findOrFail($eventId);
            $sheetTitle = substr('Peserta - ' . preg_replace('/[^A-Za-z0-9 _-]/', '', $event->title), 0, 30);
            $writeCallback = function($handle) use ($eventId) {
                \App\Exports\ParticipantRecapExport::write($handle, $eventId);
            };
        } else {
            throw new Exception("Invalid export type");
        }

        // Capture CSV output to memory handle and parse into rows
        $handle = fopen('php://temp', 'r+');
        $writeCallback($handle);
        rewind($handle);

        $values = [];
        while (($row = fgetcsv($handle)) !== false) {
            $values[] = array_map(fn($val) => $val ?? '', $row);
        }
        fclose($handle);

        $gid = $this->writeToSheet($this->masterSpreadsheetId, $sheetTitle, $values);

        return "https://docs.google.com/spreadsheets/d/{$this->masterSpreadsheetId}/edit#gid={$gid}";
    }

    /**
     * Write rows to a specific tab in a spreadsheet.
     * Creates the tab if it doesn't exist, clears existing data, then writes.
     * Returns the numeric gid of the tab for use in direct URL links.
     *
     * @param string $spreadsheetId
     * @param string $sheetTitle
     * @param array  $values
     * @return int gid of the target sheet tab
     */
    private function writeToSheet(string $spreadsheetId, string $sheetTitle, array $values): int
    {
        $gid = 0;

        // Ensure the tab exists and capture its gid
        try {
            $spreadsheetInfo = $this->sheetService->spreadsheets->get($spreadsheetId);
            $sheetExists = false;

            foreach ($spreadsheetInfo->getSheets() as $s) {
                if ($s->getProperties()->getTitle() === $sheetTitle) {
                    $sheetExists = true;
                    $gid = (int) $s->getProperties()->getSheetId();
                    break;
                }
            }

            if (!$sheetExists) {
                $body = new \Google\Service\Sheets\BatchUpdateSpreadsheetRequest([
                    'requests' => [
                        'addSheet' => [
                            'properties' => ['title' => $sheetTitle]
                        ]
                    ]
                ]);
                $response = $this->sheetService->spreadsheets->batchUpdate($spreadsheetId, $body);
                // Extract gid from the addSheet reply
                $replies = $response->getReplies();
                if (!empty($replies) && $replies[0]->getAddSheet()) {
                    $gid = (int) $replies[0]->getAddSheet()->getProperties()->getSheetId();
                }
            }
        } catch (Exception $e) {
            // Fallback to Sheet1 (gid 0)
            $sheetTitle = 'Sheet1';
            $gid = 0;
        }

        // Clear existing data
        try {
            $this->sheetService->spreadsheets_values->clear(
                $spreadsheetId,
                $sheetTitle,
                new \Google\Service\Sheets\ClearValuesRequest()
            );
        } catch (Exception $e) {
            $sheetTitle = 'Sheet1';
            $gid = 0;
            $this->sheetService->spreadsheets_values->clear(
                $spreadsheetId,
                $sheetTitle,
                new \Google\Service\Sheets\ClearValuesRequest()
            );
        }

        // Write new data
        $this->sheetService->spreadsheets_values->update(
            $spreadsheetId,
            $sheetTitle . '!A1',
            new ValueRange(['values' => $values]),
            ['valueInputOption' => 'RAW']
        );

        return $gid;
    }
}
