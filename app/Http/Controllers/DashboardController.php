<?php

// =============================================
// app/Http/Controllers/DashboardController.php
// =============================================
namespace App\Http\Controllers;
 
use App\Models\Site;
use App\Models\Destination;
use App\Models\Building;
use App\Models\Floor;
use App\Models\QrCode;
 
class DashboardController extends Controller
{
    public function index()
    {

     $sites = Site::withCount('buildings')
            ->with(['buildings' => fn($q) => $q->withCount('floors')])
            ->take(6)
            ->latest()
            ->get()
            ->map(function (Site $site) {
                $building = $site->buildings->first();
                $floorsCount = $site->buildings->sum(fn($b) => $b->floors_count);

                $floorIds = $site->buildings->flatMap(fn($b) => $b->floors->pluck('id'));
                if ($floorIds->isEmpty() && $building) {
                    $floorIds = $building->floors()->pluck('id');
                }

                return [
                    'id'            => $site->id,
                    'name'          => $site->name,
                    'address'       => $site->address,
                    'building'      => $building?->name,
                    'floors'        => $floorsCount,
                    'destinations'  => Destination::whereIn('floor_id', $floorIds)->count(),
                    'count_qrcodes'       => QrCode::whereIn('floor_id', $floorIds)->count(),
                    'edit_url'      => route('plan-editor.edit', $site->id),
                    'duplicate_url' => route('plan-editor.duplicate', $site->id),
                    'destroy_url'   => route('plan-editor.destroy', $site->id),
                ];
            });
        return view('dashboard', [
            'stats' => [
                'sites'     => Site::count(),
                'buildings' => Building::count(),
                'floors'    => Floor::count(),
                'qrcodes'   => QrCode::count(),
                'scans'     => QrCode::sum('scan_count'),
            ],
            'sites'       => $sites,
            'createUrl'   => route('plan-editor.create'),
            'importUrl'   => route('plan-editor.import'),
            'recentScans' => QrCode::with('site')->whereNotNull('last_scanned_at')->orderByDesc('last_scanned_at')->take(5)->get(),
        ]);
    }
}