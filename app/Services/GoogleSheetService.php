<?php

namespace App\Services;

use Google\Client;
use Google\Service\Drive;
use Google\Service\Sheets;
use Google\Service\Sheets\Spreadsheet;
use Google\Service\Sheets\ValueRange;
use App\Models\Setting;
use App\Models\Event;
use Illuminate\Support\Facades\DB;
use Exception;

class GoogleSheetService
{
    protected $client;
    protected $sheetService;
    protected $driveService;

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
            Drive::DRIVE
        ]);

        $this->sheetService = new Sheets($this->client);
        $this->driveService = new Drive($this->client);
    }

    /**
     * Export users to a Google Spreadsheet.
     *
     * @param string|null $eventId
     * @return string URL of the Google Spreadsheet
     */
    public function exportUsers(?string $eventId = null): string
    {
        $spreadsheetId = env('GOOGLE_SHEETS_SPREADSHEET_ID');
        $settingKey = 'google_sheet_users';
        $isNewSpreadsheet = false;

        if (!$spreadsheetId) {
            $spreadsheetId = Setting::get($settingKey);
        }

        // Define sheet title
        $sheetTitle = 'Data User';
        if ($eventId) {
            $event = Event::find($eventId);
            if ($event) {
                // Limit title to 30 characters and remove special chars to avoid Google Sheet name issues
                $sheetTitle = substr('User - ' . preg_replace('/[^A-Za-z0-9 _-]/', '', $event->title), 0, 30);
            }
        }

        if (!$spreadsheetId) {
            $isNewSpreadsheet = true;
            // Create a new spreadsheet
            $spreadsheet = new Spreadsheet([
                'properties' => [
                    'title' => 'Data User IT Today'
                ]
            ]);
            $created = $this->sheetService->spreadsheets->create($spreadsheet);
            $spreadsheetId = $created->spreadsheetId;

            // Save to settings table
            Setting::set($settingKey, $spreadsheetId);

            // Make the spreadsheet readable by anyone with the link
            try {
                $permission = new \Google\Service\Drive\Permission([
                    'type' => 'anyone',
                    'role' => 'reader',
                ]);
                $this->driveService->permissions->create($spreadsheetId, $permission);
            } catch (Exception $e) {
                // Log and continue, as the export itself still worked
                logger()->error('Failed to set spreadsheet permission: ' . $e->getMessage());
            }
        }

        // Fetch user data using same logic as UserExport
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
            $values = [
                [
                    'Nama Lengkap',
                    'Email',
                    'No. HP',
                    'ID Discord',
                    'Pendidikan',
                    'Nama Sekolah / Instansi',
                    'Status Registrasi',
                    'Status Verifikasi Login',
                    'Tanggal Daftar',
                    'Nama Kompetisi',
                    'Nama Tim',
                    'Posisi',
                ]
            ];
        } else {
            $values = [
                [
                    'Nama Lengkap',
                    'Email',
                    'No. HP',
                    'ID Discord',
                    'Pendidikan',
                    'Nama Sekolah / Instansi',
                    'Status Registrasi',
                    'Status Verifikasi Login',
                    'Tanggal Daftar',
                ]
            ];
        }

        foreach ($rows as $row) {
            if ($eventId !== null) {
                $posisi = '-';
                if ($row->team_role) {
                    $posisi = $row->team_role === 'leader' ? 'Ketua' : 'Anggota';
                } elseif ($row->participant_id) {
                    $posisi = 'Peserta Seminar';
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

        // Check if the sheet (tab) exists, or create it if it doesn't
        try {
            $spreadsheetInfo = $this->sheetService->spreadsheets->get($spreadsheetId);
            $sheets = $spreadsheetInfo->getSheets();
            $sheetExists = false;
            foreach ($sheets as $s) {
                if ($s->getProperties()->getTitle() === $sheetTitle) {
                    $sheetExists = true;
                    break;
                }
            }

            if (!$sheetExists) {
                // Add the new sheet
                $body = new \Google\Service\Sheets\BatchUpdateSpreadsheetRequest([
                    'requests' => [
                        'addSheet' => [
                            'properties' => [
                                'title' => $sheetTitle
                            ]
                        ]
                    ]
                ]);
                $this->sheetService->spreadsheets->batchUpdate($spreadsheetId, $body);
            }
        } catch (Exception $e) {
            // Fallback to 'Sheet1' if getting/creating custom sheets fails
            $sheetTitle = 'Sheet1';
        }

        // Clear existing data in the specific sheet
        try {
            $this->sheetService->spreadsheets_values->clear(
                $spreadsheetId,
                $sheetTitle,
                new \Google\Service\Sheets\ClearValuesRequest()
            );
        } catch (Exception $e) {
            // Fallback to Sheet1 if clear fails (e.g., sheet title issues)
            $sheetTitle = 'Sheet1';
            $this->sheetService->spreadsheets_values->clear(
                $spreadsheetId,
                $sheetTitle,
                new \Google\Service\Sheets\ClearValuesRequest()
            );
        }

        // Write new data
        $body = new ValueRange([
            'values' => $values
        ]);
        $params = [
            'valueInputOption' => 'RAW'
        ];

        $this->sheetService->spreadsheets_values->update(
            $spreadsheetId,
            $sheetTitle . '!A1',
            $body,
            $params
        );

        return "https://docs.google.com/spreadsheets/d/{$spreadsheetId}";
    }

    /**
     * Export team or participant recaps to a Google Spreadsheet.
     *
     * @param string $type ('teams_global', 'participants_global', 'teams_event', 'participants_event')
     * @param string|null $eventId
     * @return string URL of the Google Spreadsheet
     */
    public function exportRecap(string $type, ?string $eventId = null): string
    {
        $spreadsheetId = env('GOOGLE_SHEETS_SPREADSHEET_ID');
        $settingKey = 'google_sheet_users';

        if (!$spreadsheetId) {
            $spreadsheetId = Setting::get($settingKey);
        }

        if (!$spreadsheetId) {
            // Create a new spreadsheet
            $spreadsheet = new Spreadsheet([
                'properties' => [
                    'title' => 'Data IT Today'
                ]
            ]);
            $created = $this->sheetService->spreadsheets->create($spreadsheet);
            $spreadsheetId = $created->spreadsheetId;

            // Save to settings table
            Setting::set($settingKey, $spreadsheetId);

            // Make the spreadsheet readable by anyone with the link
            try {
                $permission = new \Google\Service\Drive\Permission([
                    'type' => 'anyone',
                    'role' => 'reader',
                ]);
                $this->driveService->permissions->create($spreadsheetId, $permission);
            } catch (Exception $e) {
                logger()->error('Failed to set spreadsheet permission: ' . $e->getMessage());
            }
        }

        // Determine sheet title and write callback
        if ($type === 'teams_global') {
            $sheetTitle = 'Semua Tim';
            $writeCallback = function($handle) {
                \App\Exports\TeamRecapExport::write($handle, null);
            };
        } elseif ($type === 'participants_global') {
            $sheetTitle = 'Semua Peserta Seminar';
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

        // Capture CSV output to memory handle
        $handle = fopen('php://temp', 'r+');
        $writeCallback($handle);
        rewind($handle);

        $values = [];
        while (($row = fgetcsv($handle)) !== false) {
            $values[] = array_map(fn($val) => $val ?? '', $row);
        }
        fclose($handle);

        // Check if the sheet (tab) exists, or create it if it doesn't
        try {
            $spreadsheetInfo = $this->sheetService->spreadsheets->get($spreadsheetId);
            $sheets = $spreadsheetInfo->getSheets();
            $sheetExists = false;
            foreach ($sheets as $s) {
                if ($s->getProperties()->getTitle() === $sheetTitle) {
                    $sheetExists = true;
                    break;
                }
            }

            if (!$sheetExists) {
                $body = new \Google\Service\Sheets\BatchUpdateSpreadsheetRequest([
                    'requests' => [
                        'addSheet' => [
                            'properties' => [
                                'title' => $sheetTitle
                            ]
                        ]
                    ]
                ]);
                $this->sheetService->spreadsheets->batchUpdate($spreadsheetId, $body);
            }
        } catch (Exception $e) {
            $sheetTitle = 'Sheet1';
        }

        // Clear existing data in the specific sheet
        try {
            $this->sheetService->spreadsheets_values->clear(
                $spreadsheetId,
                $sheetTitle,
                new \Google\Service\Sheets\ClearValuesRequest()
            );
        } catch (Exception $e) {
            $sheetTitle = 'Sheet1';
            $this->sheetService->spreadsheets_values->clear(
                $spreadsheetId,
                $sheetTitle,
                new \Google\Service\Sheets\ClearValuesRequest()
            );
        }

        // Write new data
        $body = new ValueRange([
            'values' => $values
        ]);
        $params = [
            'valueInputOption' => 'RAW'
        ];

        $this->sheetService->spreadsheets_values->update(
            $spreadsheetId,
            $sheetTitle . '!A1',
            $body,
            $params
        );

        return "https://docs.google.com/spreadsheets/d/{$spreadsheetId}";
    }
}

