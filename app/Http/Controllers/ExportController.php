<?php

namespace App\Http\Controllers;

use App\Exports\ParticipantRecapExport;
use App\Exports\SemnasParticipantExport;
use App\Exports\TeamRecapExport;
use App\Exports\UserExport;
use App\Models\Event;
use App\Services\GoogleSheetService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function exportTeams(Request $request): StreamedResponse
    {
        $request->validate([
            'event_id' => ['required', 'string', 'exists:event,id'],
        ]);

        $event    = Event::findOrFail($request->input('event_id'));
        $filename = 'rekap-tim-' . Str::slug($event->title) . '-' . now()->format('Y-m-d') . '.csv';

        return response()->stream(function () use ($event) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            TeamRecapExport::write($handle, $event->id);
            fclose($handle);
        }, 200, $this->buildCsvHeaders($filename));
    }

    public function exportParticipants(Request $request): StreamedResponse
    {
        $request->validate([
            'event_id' => ['required', 'string', 'exists:event,id'],
        ]);

        $event    = Event::findOrFail($request->input('event_id'));
        $filename = 'rekap-peserta-' . Str::slug($event->title) . '-' . now()->format('Y-m-d') . '.csv';

        return response()->stream(function () use ($event) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            ParticipantRecapExport::write($handle, $event->id);
            fclose($handle);
        }, 200, $this->buildCsvHeaders($filename));
    }


    public function exportSemnasParticipants(Request $request): StreamedResponse
    {
        $request->validate([
            'event_id' => ['nullable', 'string', 'exists:event,id'],
        ]);

        $eventId = $request->input('event_id');

        if ($eventId) {
            $event    = Event::findOrFail($eventId);
            $filename = 'rekap-semnas-' . Str::slug($event->title) . '-' . now()->format('Y-m-d') . '.csv';
        } else {
            $filename = 'rekap-semnas-semua-' . now()->format('Y-m-d') . '.csv';
        }

        return response()->stream(function () use ($eventId) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            SemnasParticipantExport::write($handle, $eventId);
            fclose($handle);
        }, 200, $this->buildCsvHeaders($filename));
    }

    public function exportTeamsGlobal(): StreamedResponse
    {
        $filename = 'rekap-tim-semua-' . now()->format('Y-m-d') . '.csv';

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            TeamRecapExport::write($handle, null);
            fclose($handle);
        }, 200, $this->buildCsvHeaders($filename));
    }

    public function exportParticipantsGlobal(): StreamedResponse
    {
        $filename = 'rekap-peserta-semua-' . now()->format('Y-m-d') . '.csv';

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            ParticipantRecapExport::write($handle, null);
            fclose($handle);
        }, 200, $this->buildCsvHeaders($filename));
    }

    public function exportUsersGlobal(\Illuminate\Http\Request $request): StreamedResponse
    {
        $filename = 'rekap-pengguna-umum-' . now()->format('Y-m-d') . '.csv';
        $requestedEventId = $request->input('event_id');

        return response()->stream(function () use ($requestedEventId) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            
            $eventIds = $requestedEventId ? [$requestedEventId] : null;
            
            UserExport::write($handle, $eventIds);
            fclose($handle);
        }, 200, $this->buildCsvHeaders($filename));
    }

    public function exportUsersGoogleSheets(Request $request, GoogleSheetService $service)
    {
        try {
            $eventId = $request->input('event_id') ?: null;
            $url = $service->exportUsers($eventId);

            return response()->json([
                'success' => true,
                'url'     => $url,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function exportRecapGoogleSheets(Request $request, GoogleSheetService $service)
    {
        try {
            $exportType = $request->input('export_type'); // 'teams_global', 'participants_global', 'event'
            $eventId = $request->input('event_id');

            if ($exportType === 'teams_global') {
                $url = $service->exportRecap('teams_global');
            } elseif ($exportType === 'participants_global') {
                $url = $service->exportRecap('participants_global');
            } elseif ($exportType === 'event') {
                $event = Event::findOrFail($eventId);
                if ($event->type === 'competition') {
                    $url = $service->exportRecap('teams_event', $event->id);
                } else {
                    $url = $service->exportRecap('participants_event', $event->id);
                }
            } elseif ($exportType === 'submissions_event') {
                $event = Event::findOrFail($eventId);
                $url = $service->exportRecap('submissions_event', $event->id);
            } elseif ($exportType === 'semnas_participants_global') {
                $url = $service->exportRecap('semnas_participants_global');
            } elseif ($exportType === 'semnas_participants_event') {
                $event = Event::findOrFail($eventId);
                $url = $service->exportRecap('semnas_participants_event', $event->id);
            } else {
                abort(400, 'Invalid export type.');
            }

            return response()->json([
                'success' => true,
                'url'     => $url,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function exportSubmissions(Request $request, Event $event): StreamedResponse
    {
        $filename = 'rekap-submisi-' . Str::slug($event->title) . '-' . now()->format('Y-m-d') . '.csv';

        return response()->stream(function () use ($event) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            \App\Exports\SubmissionExport::write($handle, $event->id);
            fclose($handle);
        }, 200, $this->buildCsvHeaders($filename));
    }

    public function exportSubmissionsGoogleSheets(Request $request, Event $event, GoogleSheetService $service)
    {
        try {
            $url = $service->exportRecap('submissions_event', $event->id);
            return response()->json([
                'success' => true,
                'url'     => $url,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    private function buildCsvHeaders(string $filename): array
    {
        return [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];
    }
}
