<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\QrCode;

class MapController extends Controller
{
    public function scan(string $uuid): JsonResponse
    {
        $qr = QrCode::with(['site', 'building.floors', 'floor'])
            ->where('uuid', $uuid)
            ->firstOrFail();

        // mise à jour des stats
        $qr->increment('scan_count');
        $qr->update(['last_scanned_at' => now()]);

        $response = [
            'qr_uuid'        => $uuid,
            'location_label' => $qr->location_label,
            'scanned_at'     => now()->toIso8601String(),
            'site'           => $this->formatSite($qr->site),
            'building'       => $this->formatBuilding($qr->building),
            'current_floor'  => $qr->floor ? $this->formatFloor($qr->floor) : null,
            'position'       => ['x' => $qr->pos_x, 'y' => $qr->pos_y],
            'all_floors'     => $qr->building
                ? $qr->building->floors->map(fn($f) => $this->formatFloor($f))
                : [],
        ];

        return response()->json($response);
    }

    private function formatSite($site): array {
        return [
            'id'        => $site->id,
            'name'      => $site->name,
            'address'   => $site->address,
            'latitude'  => $site->latitude,
            'longitude' => $site->longitude,
        ];
    }

    private function formatBuilding($building): array {
        return [
            'id'           => $building->id,
            'name'         => $building->name,
            'floors_count' => $building->floors_count,
            'plan_2d'      => $building->floor_plan_2d, // SVG/GeoJSON
            'model_3d'     => $building->model_3d,      // données THREE.js
        ];
    }

    private function formatFloor($floor): array {
        return [
            'id'     => $floor->id,
            'level'  => $floor->level,
            'label'  => $floor->label,
            'plan'   => $floor->plan_2d, // plan de l'étage
            'rooms'  => $floor->rooms,
        ];
    }
}