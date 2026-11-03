<?php

// =============================================
// app/Http/Controllers/ScanController.php
// =============================================
namespace App\Http\Controllers;
 
use App\Models\QrCode;
use App\Models\ScanLog;
use Illuminate\Http\Request;

class ScanController extends Controller
{
    public function show(string $uuid, Request $request)
    {
        $qrcode = QrCode::with(['site', 'building.floors', 'floor'])
            ->where('uuid', $uuid)
            ->firstOrFail();
 
        // Incrément + log
        $qrcode->increment('scan_count');
        $qrcode->update(['last_scanned_at' => now()]);
        ScanLog::record($qrcode, $request);
 
        $site         = $qrcode->site;
        $building     = $qrcode->building;
        $currentFloor = $qrcode->floor;
        $allFloors    = $building
            ? $building->floors->sortBy('level')->values()
            : collect();
 
<<<<<<< Updated upstream
        return view('scan.show_AB', compact(
=======
        return view('scan.show_immersive', compact(
>>>>>>> Stashed changes
            'qrcode', 'site', 'building', 'currentFloor', 'allFloors'
        ));
    }
}