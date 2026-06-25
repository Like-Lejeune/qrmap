<?php

// =============================================
// app/Http/Controllers/ExportController.php
// =============================================
namespace App\Http\Controllers;
 
use App\Models\QrCode;
use App\Models\ScanLog;
use App\Models\Site;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class ExportController extends Controller
{
    /** Export CSV de tous les QR codes */
    public function qrCodesCsv(): Response
    {
        $qrcodes = QrCode::with(['site', 'building', 'floor'])->get();
 
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="qrcodes_' . now()->format('Ymd_His') . '.csv"',
        ];
 
        $csv = $this->buildCsv(
            ['UUID', 'Emplacement', 'Site', 'Bâtiment', 'Étage', 'Position X', 'Position Y', 'Scans', 'Dernier scan', 'URL de scan', 'Créé le'],
            $qrcodes->map(fn($q) => [
                $q->uuid,
                $q->location_label ?? '',
                $q->site->name ?? '',
                $q->building->name ?? '',
                $q->floor->label ?? '',
                $q->pos_x ?? '',
                $q->pos_y ?? '',
                $q->scan_count,
                $q->last_scanned_at?->format('d/m/Y H:i') ?? '',
                route('scan.show', $q->uuid),
                $q->created_at->format('d/m/Y H:i'),
            ])->toArray()
        );
 
        return response($csv, 200, $headers);
    }
 
    /** Export CSV de l'historique des scans */
    public function scanLogsCsv(): Response
    {
        $logs = ScanLog::with(['qrCode.site', 'qrCode.building'])
            ->orderByDesc('scanned_at')
            ->limit(50000)
            ->get();
 
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="scan_logs_' . now()->format('Ymd_His') . '.csv"',
        ];
 
        $csv = $this->buildCsv(
            ['Date', 'Heure', 'Emplacement', 'Site', 'Bâtiment', 'Appareil', 'IP'],
            $logs->map(fn($l) => [
                $l->scanned_at->format('d/m/Y'),
                $l->scanned_at->format('H:i:s'),
                $l->qrCode->location_label ?? '',
                $l->qrCode->site->name ?? '',
                $l->qrCode->building->name ?? '',
                $l->device_type,
                $l->ip_address ?? '',
            ])->toArray()
        );
 
        return response($csv, 200, $headers);
    }
 
    /** Export CSV par site */
    public function siteCsv(Site $site): Response
    {
        $qrcodes = $site->qrCodes()->with(['building', 'floor'])->get();
 
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' .'"\"'.Str::slug($site->name) . '_qrcodes.csv"',
        ];
 
        $csv = $this->buildCsv(
            ['UUID', 'Emplacement', 'Bâtiment', 'Étage', 'Scans', 'URL'],
            $qrcodes->map(fn($q) => [
                $q->uuid,
                $q->location_label ?? '',
                $q->building->name ?? '',
                $q->floor->label ?? '',
                $q->scan_count,
                route('scan.show', $q->uuid),
            ])->toArray()
        );
 
        return response($csv, 200, $headers);
    }
 
    private function buildCsv(array $headers, array $rows): string
    {
        $output = fopen('php://memory', 'w');
        // BOM UTF-8 pour Excel
        fwrite($output, "\xEF\xBB\xBF");
        fputcsv($output, $headers, ';');
        foreach ($rows as $row) {
            fputcsv($output, $row, ';');
        }
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        return $csv;
    }
}
