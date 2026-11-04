<?php

// =============================================
// routes/api.php  — VERSION FINALE
// =============================================
use App\Http\Controllers\NavigationController;
use App\Http\Controllers\Api\BuildingApiController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\AiGuideController;


Route::post('/v1/scan/{uuid}/guide', [AiGuideController::class, 'chat'])
    ->middleware('throttle:20,1');
Route::prefix('v1')->group(function () {
 
    // Chargement dynamique des selects (admin)
    Route::get('/sites/{site}/buildings',
        [BuildingApiController::class, 'bySite']);
    Route::get('/buildings/{building}/floors',
        [BuildingApiController::class, 'floors']);
    Route::get('/scan/{uuid}/destinations', [NavigationController::class, 'destinations']);
    Route::post('/navigate', [NavigationController::class, 'navigate']);
 
    // === ENDPOINT PUBLIC DE SCAN ===
    // GET /api/v1/scan/{uuid}
    // Retourne les données complètes pour un QR code scanné
    Route::get('/scan/{uuid}', function (string $uuid) {
        $qr = \App\Models\QrCode::with(['site', 'building.floors', 'floor'])
            ->where('uuid', $uuid)
            ->firstOrFail();
 
        return response()->json([
            'status'        => 'ok',
            'uuid'          => $qr->uuid,
            'location'      => $qr->location_label,
            'position'      => ['x' => $qr->pos_x, 'y' => $qr->pos_y],
            'scan_count'    => $qr->scan_count,
            'site' => $qr->site ? [
                'id'        => $qr->site->id,
                'name'      => $qr->site->name,
                'address'   => $qr->site->address,
                'latitude'  => $qr->site->latitude,
                'longitude' => $qr->site->longitude,
            ] : null,
            'building' => $qr->building ? [
                'id'           => $qr->building->id,
                'name'         => $qr->building->name,
                'floors_count' => $qr->building->floors_count,
                'dimensions'   => $qr->building->model_3d,
            ] : null,
            'current_floor' => $qr->floor ? [
                'id'    => $qr->floor->id,
                'level' => $qr->floor->level,
                'label' => $qr->floor->label,
                'rooms' => $qr->floor->rooms ?? [],
            ] : null,
            'all_floors' => $qr->building?->floors
                ->sortBy('level')
                ->map(fn($f) => [
                    'id'    => $f->id,
                    'level' => $f->level,
                    'label' => $f->label,
                    'rooms' => $f->rooms ?? [],
                ])->values() ?? [],
        ]);
    })->name('api.scan');
 
    // === LISTE DES SITES (lecture seule, publique) ===
    // GET /api/v1/sites
    Route::get('/sites', function () {
        return response()->json(
            \App\Models\Site::withCount('buildings')
                ->select('id', 'name', 'address', 'latitude', 'longitude')
                ->get()
        );
    });
 
    // === ENDPOINTS PROTÉGÉS (token Sanctum) ===
    Route::middleware('auth:sanctum')->group(function () {
 
        // Statistiques globales
        Route::get('/stats', function () {
            return response()->json([
                'sites'      => \App\Models\Site::count(),
                'buildings'  => \App\Models\Building::count(),
                'floors'     => \App\Models\Floor::count(),
                'qrcodes'    => \App\Models\QrCode::count(),
                'total_scans'=> \App\Models\QrCode::sum('scan_count'),
                'today_scans'=> \App\Models\ScanLog::whereDate('scanned_at', today())->count(),
            ]);
        });
 
        // CRUD Sites
        Route::apiResource('sites', \App\Http\Controllers\SiteController::class)->only(['show', 'store', 'update']);
 
        // Générer un QR code via API
        Route::post('/qrcodes', function (Request $request) {
            $data = $request->validate([
                'site_id'        => 'required|exists:sites,id',
                'building_id'    => 'nullable|exists:buildings,id',
                'floor_id'       => 'nullable|exists:floors,id',
                'location_label' => 'required|string|max:255',
                'pos_x'          => 'nullable|numeric|min:0|max:100',
                'pos_y'          => 'nullable|numeric|min:0|max:100',
            ]);
            $result = app(\App\Services\QrCodeService::class)->generate($data);
            return response()->json([
                'uuid'      => $result['uuid'],
                'scan_url'  => $result['scan_url'],
                'image_url' => asset('storage/qrcodes/' . $result['uuid'] . '.svg'),
            ], 201);
        });
    });
});