<?php

// =============================================
// app/Http/Controllers/StatsController.php
// =============================================
namespace App\Http\Controllers;

use App\Models\QrCode;
use App\Models\ScanLog;
use App\Models\Site;
use App\Support\Tenancy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    public function index()
    {
        // ── TENANCY ────────────────────────────────────────────────────────
        // QrCode est scopé (HasTenant) : en multi → seulement les QR du client
        // connecté ; en single → tous les QR. ScanLog n'a PAS de user_id, donc
        // on restreint ses requêtes aux QR visibles via leurs IDs.
        $qrIds = QrCode::pluck('id');
        // ───────────────────────────────────────────────────────────────────

        // Scans par jour (30 derniers jours)
        $scansByDay = ScanLog::whereIn('qr_code_id', $qrIds)
            ->selectRaw('DATE(scanned_at) as date, COUNT(*) as total')
            ->where('scanned_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        // Remplir les jours sans scan
        $days = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $days[$date] = $scansByDay[$date]->total ?? 0;
        }

        // Scans par type d'appareil
        $byDevice = ScanLog::whereIn('qr_code_id', $qrIds)
            ->selectRaw('device_type, COUNT(*) as total')
            ->groupBy('device_type')
            ->pluck('total', 'device_type');

        // Top QR codes (déjà scopé par HasTenant)
        $topQr = QrCode::with(['site', 'building'])
            ->orderByDesc('scan_count')
            ->take(10)
            ->get();

        // Scans par site (déjà scopé par HasTenant)
        $bySite = Site::withSum('qrCodes as total_scans', 'scan_count')
            ->orderByDesc('total_scans')
            ->take(8)
            ->get();

        // Totaux (restreints aux QR du tenant)
        $todayScans = ScanLog::whereIn('qr_code_id', $qrIds)->whereDate('scanned_at', today())->count();
        $weekScans  = ScanLog::whereIn('qr_code_id', $qrIds)->where('scanned_at', '>=', now()->startOfWeek())->count();
        $monthScans = ScanLog::whereIn('qr_code_id', $qrIds)->where('scanned_at', '>=', now()->startOfMonth())->count();
        $totalScans = ScanLog::whereIn('qr_code_id', $qrIds)->count();

        return view('stats.index', compact(
            'days', 'byDevice', 'topQr', 'bySite',
            'todayScans', 'weekScans', 'monthScans', 'totalScans'
        ));
    }

    public function qrcode(QrCode $qrcode)
    {
        // ── TENANCY ────────────────────────────────────────────────────────
        // Le route-model-binding résout le QR par ID SANS passer par le scope.
        // En mode multi, on bloque l'accès à un QR appartenant à un autre client.
        if (Tenancy::isMulti()
            && auth()->check()
            && $qrcode->user_id !== auth()->id()) {
            abort(404);
        }
        // ───────────────────────────────────────────────────────────────────

        $qrcode->load(['site', 'building', 'floor']);

        // Les requêtes ScanLog ci-dessous ciblent ce QR précis → déjà sûres.
        $logs = ScanLog::where('qr_code_id', $qrcode->id)
            ->orderByDesc('scanned_at')
            ->paginate(20);

        $scansByDay = ScanLog::where('qr_code_id', $qrcode->id)
            ->selectRaw('DATE(scanned_at) as date, COUNT(*) as total')
            ->where('scanned_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $days = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $days[$date] = $scansByDay[$date]->total ?? 0;
        }

        $byDevice = ScanLog::where('qr_code_id', $qrcode->id)
            ->selectRaw('device_type, COUNT(*) as total')
            ->groupBy('device_type')
            ->pluck('total', 'device_type');

        return view('stats.qrcode', compact('qrcode', 'logs', 'days', 'byDevice'));
    }
}