<?php

namespace App\Http\Controllers;

use App\Exports\ParticipantRecapExport;
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
        abort_unless(in_array(auth()->user()->role, ['superadmin', 'panitia_lomba', 'admin_biasa']), 403);
        $request->validate([
            'event_id' => ['required', 'string', 'exists:event,id'],
        ]);

        $event    = Event::findOrFail($request->input('event_id'));

        if (auth()->user()->role === 'panitia_lomba') {
            abort_unless(auth()->user()->events->contains('id', $event->id), 403);
        }

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
        abort_unless(in_array(auth()->user()->role, ['superadmin', 'panitia_lomba', 'admin_biasa']), 403);
        $request->validate([
            'event_id' => ['required', 'string', 'exists:event,id'],
        ]);

        $event    = Event::findOrFail($request->input('event_id'));

        if (auth()->user()->role === 'panitia_lomba') {
            abort_unless(auth()->user()->events->contains('id', $event->id), 403);
        }

        $filename = 'rekap-peserta-' . Str::slug($event->title) . '-' . now()->format('Y-m-d') . '.csv';

        return response()->stream(function () use ($event) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            ParticipantRecapExport::write($handle, $event->id);
            fclose($handle);
        }, 200, $this->buildCsvHeaders($filename));
    }


    public function exportTeamsGlobal(): StreamedResponse
    {
        abort_unless(in_array(auth()->user()->role, ['superadmin', 'admin_biasa']), 403);
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
        abort_unless(in_array(auth()->user()->role, ['superadmin', 'admin_biasa']), 403);
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
        $userRole = auth()->user()->role;
        abort_unless(in_array($userRole, ['superadmin', 'admin_biasa', 'panitia_lomba']), 403);
        $filename = 'rekap-pengguna-umum-' . now()->format('Y-m-d') . '.csv';

        $requestedEventId = $request->input('event_id');

        return response()->stream(function () use ($userRole, $requestedEventId) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            
            $eventIds = null;
            if ($requestedEventId) {
                $eventIds = [$requestedEventId];
            } elseif ($userRole === 'panitia_lomba') {
                $eventIds = auth()->user()->events->pluck('id')->toArray();
            }
            
            UserExport::write($handle, $eventIds);
            fclose($handle);
        }, 200, $this->buildCsvHeaders($filename));
    }

    public function exportUsersGoogleSheets(Request $request, GoogleSheetService $service)
    {
        $userRole = auth()->user()->role;
        abort_unless(in_array($userRole, ['superadmin', 'admin_biasa']), 403);

        try {
            $eventId = $request->input('event_id');
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
        abort_unless(in_array(auth()->user()->role, ['superadmin', 'admin_biasa']), 403);

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
