<?php

namespace App\Http\Controllers;

use App\Exports\ParticipantRecapExport;
use App\Exports\TeamRecapExport;
use App\Models\Event;
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
