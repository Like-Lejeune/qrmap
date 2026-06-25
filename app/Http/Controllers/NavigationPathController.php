<?php
// app/Http/Controllers/NavigationPathController.php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\NavigationPath;
use App\Models\QrCode;
use App\Models\Site;
use App\Services\AStarPathfinder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NavigationPathController extends Controller
{
    /**
     * Affiche l'éditeur de tracé pour un site.
     * GET /admin/paths/{site}/editor
     */
    public function editor(Site $site)
    {
        $building = $site->buildings()->with(['floors.destinations'])->first();

        $origins = collect();
        $dests   = collect();

        if ($building) {
            foreach ($building->floors->sortBy('level') as $floor) {
                foreach (QrCode::where('floor_id', $floor->id)->get() as $qr) {
                    $origins->push([
                        'id'          => $qr->id,
                        'label'       => $qr->location_label ?? ('QR #' . $qr->id),
                        'floor_level' => $floor->level,
                        'x'           => $qr->pos_x,
                        'y'           => $qr->pos_y,
                    ]);
                }
                foreach ($floor->destinations as $d) {
                    $room = $this->resolveRoom($floor, $d);
                    $dests->push([
                        'id'          => $d->id,
                        'label'       => $d->label,
                        'floor_level' => $floor->level,
                        'x'           => $room['cx'] ?? null,
                        'y'           => $room['cy'] ?? null,
                    ]);
                }
            }
        }

        return view('admin.path-editor', [
            'site'    => $site,
            'origins' => $origins->values(),
            'dests'   => $dests->values(),
        ]);
    }

    /**
     * Calcule le(s) tronçon(s) d'un trajet (mono ou multi-étage) pour l'éditeur.
     * Si un tracé est déjà enregistré, ses waypoints sont réutilisés par tronçon.
     * GET /admin/paths/compute?qr_code_id=..&destination_id=..
     */
    public function compute(Request $request, AStarPathfinder $astar): JsonResponse
    {
        $data = $request->validate([
            'qr_code_id'     => 'required|exists:qr_codes,id',
            'destination_id' => 'required|exists:destinations,id',
        ]);

        $qr   = QrCode::with('floor')->findOrFail($data['qr_code_id']);
        $dest = Destination::with('floor')->findOrFail($data['destination_id']);

        $existing = NavigationPath::where('qr_code_id', $qr->id)
            ->where('destination_id', $dest->id)->first();
        $saved = $existing ? $existing->segmentsData() : null;

        $sameFloor = $qr->floor_id === $dest->floor_id;

        // ── Mono-étage : un seul tronçon ───────────────────────────────────
        if ($sameFloor) {
            $floor = $qr->floor;
            $rooms = $floor->rooms ?? [];
            $from  = ['x' => (float) $qr->pos_x, 'y' => (float) $qr->pos_y];
            $room  = $this->resolveRoom($floor, $dest);
            $to    = ['x' => $room['cx'] ?? 50, 'y' => $room['cy'] ?? 50];

            $wp = ($saved[0]['waypoints'] ?? null) ?: $astar->find($rooms, $from, $to);

            return response()->json([
                'multi'    => false,
                'exists'   => (bool) $existing,
                'segments' => [[
                    'floor_id'     => $floor->id,
                    'floor_label'  => $floor->label,
                    'role'         => 'direct',
                    'rooms'        => $rooms,
                    'from'         => $from,
                    'to'           => $to,
                    'waypoints'    => $wp,
                    'smooth'       => (bool) ($saved[0]['smooth'] ?? false),
                    'editable_end' => true,   // destination déplaçable
                ]],
            ]);
        }

        // ── Multi-étage : deux tronçons autour du connecteur ───────────────
        $cf = $qr->floor;  $df = $dest->floor;
        $cr = $cf->rooms ?? [];  $dr = $df->rooms ?? [];
        $from = ['x' => (float) $qr->pos_x, 'y' => (float) $qr->pos_y];

        $connC = $astar->findConnectorRoom($cr, $from);
        $near  = $connC ? $astar->roomCenter($connC) : null;
        $connD = $astar->findConnectorRoom($dr, $near);

        $room = $this->resolveRoom($df, $dest);
        $to   = ['x' => $room['cx'] ?? 50, 'y' => $room['cy'] ?? 50];

        $connCcenter = $connC ? $astar->roomCenter($connC) : ['x' => 50, 'y' => 95];
        $connDcenter = $connD ? $astar->roomCenter($connD) : $connCcenter;

        $leg1 = ($saved[0]['waypoints'] ?? null) ?: ($astar->find($cr, $from, $connCcenter) ?: [$from, $connCcenter]);
        $leg2 = ($saved[1]['waypoints'] ?? null) ?: ($astar->find($dr, $connDcenter, $to) ?: [$connDcenter, $to]);

        return response()->json([
            'multi'    => true,
            'exists'   => (bool) $existing,
            'segments' => [
                [
                    'floor_id'     => $cf->id,
                    'floor_label'  => $cf->label,
                    'role'         => 'vers_connecteur',
                    'rooms'        => $cr,
                    'from'         => $from,
                    'to'           => $connCcenter,
                    'waypoints'    => $leg1,
                    'smooth'       => (bool) ($saved[0]['smooth'] ?? false),
                    'editable_end' => false,   // arrivée = ascenseur (fixe)
                    'connector'    => $connC,
                ],
                [
                    'floor_id'     => $df->id,
                    'floor_label'  => $df->label,
                    'role'         => 'depuis_connecteur',
                    'rooms'        => $dr,
                    'from'         => $connDcenter,
                    'to'           => $to,
                    'waypoints'    => $leg2,
                    'smooth'       => (bool) ($saved[1]['smooth'] ?? false),
                    'editable_end' => true,    // destination déplaçable
                    'connector'    => $connD,
                ],
            ],
        ]);
    }

    /**
     * Enregistre le tracé (un ou plusieurs tronçons).
     * POST /admin/paths
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'qr_code_id'                => 'required|exists:qr_codes,id',
            'destination_id'            => 'required|exists:destinations,id',
            'segments'                  => 'required|array|min:1',
            'segments.*.floor_id'       => 'nullable|integer',
            'segments.*.role'           => 'nullable|string|max:32',
            'segments.*.smooth'         => 'boolean',
            'segments.*.waypoints'      => 'required|array|min:2',
            'segments.*.waypoints.*.x'  => 'required|numeric',
            'segments.*.waypoints.*.y'  => 'required|numeric',
        ]);

        $segments = array_map(function ($s) {
            return [
                'floor_id'  => $s['floor_id'] ?? null,
                'role'      => $s['role'] ?? 'direct',
                'smooth'    => (bool) ($s['smooth'] ?? false),
                'waypoints' => $s['waypoints'],
            ];
        }, $data['segments']);

        $isMulti = count($segments) > 1;
        $first   = $segments[0];

        $path = NavigationPath::updateOrCreate(
            ['qr_code_id' => $data['qr_code_id'], 'destination_id' => $data['destination_id']],
            [
                // Compat mono-étage : 1er tronçon dans waypoints/smooth
                'waypoints' => $first['waypoints'],
                'smooth'    => $first['smooth'],
                // Multi-étage : tableau complet (sinon null)
                'segments'  => $isMulti ? $segments : null,
            ]
        );

        return response()->json(['id' => $path->id, 'message' => 'Tracé enregistré', 'multi' => $isMulti]);
    }

    /**
     * Supprime le tracé enregistré → retour A* automatique.
     * DELETE /admin/paths
     */
    public function destroy(Request $request): JsonResponse
    {
        $data = $request->validate([
            'qr_code_id'     => 'required|integer',
            'destination_id' => 'required|integer',
        ]);

        NavigationPath::where('qr_code_id', $data['qr_code_id'])
            ->where('destination_id', $data['destination_id'])
            ->delete();

        return response()->json(['message' => 'Tracé réinitialisé (A* automatique)']);
    }

    // ──────────────────────────────────────────────────────────────────

    private function resolveRoom($floor, Destination $dest): array
    {
        $rooms = $floor->rooms ?? [];
        foreach ($rooms as $r) {
            if (($r['id'] ?? null) === $dest->room_id) {
                $x = $r['x'] ?? 0; $y = $r['y'] ?? 0;
                $w = $r['w'] ?? 0; $h = $r['h'] ?? 0;
                return [
                    'cx' => $x + $w / 2, 'cy' => $y + $h / 2,
                    'x'  => $x, 'y' => $y, 'w' => $w, 'h' => $h,
                ];
            }
        }
        return ['cx' => 50, 'cy' => 50];
    }
}