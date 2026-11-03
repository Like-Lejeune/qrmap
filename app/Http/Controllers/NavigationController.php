<?php
// app/Http/Controllers/NavigationController.php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\Floor;
use App\Models\NavigationPath;
use App\Models\QrCode;
use App\Services\AStarPathfinder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NavigationController extends Controller
{
    /**
     * GET /api/v1/scan/{uuid}/destinations
     * Retourne toutes les destinations accessibles depuis un QR code scanné.
     * Groupées par étage pour faciliter l'affichage.
     */
    public function destinations(string $uuid): JsonResponse
    {
        $qr = QrCode::with(['floor.building.floors'])->where('uuid', $uuid)->firstOrFail();

        $building = $qr->floor?->building;

        if (!$building) {
            return response()->json(['destinations' => [], 'floors' => []]);
        }

        $floors = $building->floors()
            ->with(['destinations' => fn($q) => $q->active()])
            ->orderBy('level')
            ->get();

        $result = $floors
            ->filter(fn($f) => $f->destinations->isNotEmpty())
            ->map(fn($f) => [
                'floor_id'    => $f->id,
                'floor_level' => $f->level,
                'floor_label' => $f->label ?? 'Niveau ' . $f->level,
                'destinations' => $f->destinations->map(fn($d) => [
                    'id'          => $d->id,
                    'room_id'     => $d->room_id,
                    'label'       => $d->label,
                    'description' => $d->description,
                    'category'    => $d->category,
                    'icon'        => $d->icon,
                    'floor_id'    => $d->floor_id,
                    'floor_label' => $f->label ?? 'Niveau ' . $f->level,
                    'floor_level' => $f->level,
                    'same_floor'  => $d->floor_id === $qr->floor_id,
                    'room'        => $d->getRoom(),
                ]),
            ])
            ->values();

        return response()->json([
            'qr_position' => ['x' => $qr->pos_x, 'y' => $qr->pos_y],
            'current_floor_id' => $qr->floor_id,
            'destinations' => $result,
        ]);
    }

    /**
     * POST /api/v1/navigate
     * Calcule le chemin A* entre le QR code et une destination.
     *
     * Body: { uuid, destination_id }
     */
    public function navigate(Request $request, AStarPathfinder $finder): JsonResponse
    {
        $data = $request->validate([
            'uuid'           => 'required|string|exists:qr_codes,uuid',
            'destination_id' => 'required|integer|exists:destinations,id',
        ]);

        $qr   = QrCode::with('floor')->where('uuid', $data['uuid'])->firstOrFail();
        $dest = Destination::with('floor')->findOrFail($data['destination_id']);

        $sameFloor = $qr->floor_id === $dest->floor_id;

        // ── Chemin sur le même étage ───────────────────────────────────────
        if ($sameFloor) {
            return response()->json($this->buildSameFloorResponse($qr, $dest, $finder));
        }

        // ── Chemin multi-étages (deux tronçons autour de l'ascenseur) ──────
        return response()->json($this->buildMultiFloorResponse($qr, $dest, $finder));
    }

    /**
     * Trajet sur un seul étage : QR → destination, en traversant librement
     * couloirs / allées / halls / salles d'attente.
     *
     * Si un tracé corrigé par l'admin existe pour ce couple (QR, destination),
     * il est servi en priorité sur le calcul A*.
     */
    private function buildSameFloorResponse(QrCode $qr, Destination $dest, AStarPathfinder $finder): array
    {
        $floor = $qr->floor;
        $rooms = $floor->rooms ?? [];

        $from = ['x' => (float) $qr->pos_x, 'y' => (float) $qr->pos_y];

      $destRoom = $dest->getRoom();

        // Override enregistré par l'admin (tracé complet et/ou point de porte)
        $override = NavigationPath::where('qr_code_id', $qr->id)
            ->where('destination_id', $dest->id)
            ->first();

        // ── Point d'arrivée : porte enregistrée > porte du plan > centre ───
        if ($override && $override->hasEndPoint()) {
            $to       = ['x' => (float) $override->end_point['x'], 'y' => (float) $override->end_point['y']];
            $solidEnd = true;
        } elseif ($destRoom && $finder->hasDoor($destRoom)) {
            $to       = $finder->targetPoint($destRoom);   // porte déclarée dans le plan
            $solidEnd = true;
        } elseif ($destRoom) {
            $to       = $finder->roomCenter($destRoom);     // centre (historique)
            $solidEnd = false;
        } else {
            $to       = ['x' => 50, 'y' => 50];
            $solidEnd = false;
        }

        // ── Chemin ─────────────────────────────────────────────────────────
        // 1. tracé manuel complet enregistré → servi tel quel
        // 2. sinon A* (re)calculé vers $to (porte ou centre)
        if ($override && !empty($override->waypoints)) {
            $path   = $override->waypoints;
            $smooth = (bool) $override->smooth;
        } else {
            $path   = $finder->find($rooms, $from, $to, $solidEnd) ?? [$from, $to];
            $smooth = (bool) ($override->smooth ?? false);
        }

        return [
            'type'        => 'same_floor',
            'floor_id'    => $floor->id,
            'floor_label' => $floor->label,
            'path'        => $path,
            'smooth'      => $smooth,
            'destination' => ['label' => $dest->label, 'room' => $destRoom],
            'instruction' => 'Suivez le chemin vers ' . $dest->label . '.',
            // Format unifié "segments" consommé par le frontend (1 seul tronçon ici)
            'segments' => [[
                'floor_id'    => $floor->id,
                'floor_label' => $floor->label,
                'floor_level' => $floor->level,
                'role'        => 'direct',
                'path'        => $path,
                'smooth'      => $smooth,
            ]],
        ];
    }

    /**
     * Trajet multi-étages :
     *   Tronçon 1 — étage courant : QR → ascenseur/escalier.
     *   (changement d'étage via le connecteur matérialisé)
     *   Tronçon 2 — étage destination : ascenseur/escalier → destination.
     */
    private function buildMultiFloorResponse(QrCode $qr, Destination $dest, AStarPathfinder $finder): array
    {
        $currentFloor = $qr->floor;
        $destFloor    = $dest->floor;

        $currentRooms = $currentFloor->rooms ?? [];
        $destRooms    = $destFloor->rooms ?? [];

        $from = ['x' => (float) $qr->pos_x, 'y' => (float) $qr->pos_y];

        // 1) Connecteur sur l'étage courant (le plus proche du point de départ)
        $connCurrent = $finder->findConnectorRoom($currentRooms, $from);

        // 2) Connecteur sur l'étage destination (aligné avec celui de départ si possible)
        $near = $connCurrent ? $finder->roomCenter($connCurrent) : null;
        $connDest = $finder->findConnectorRoom($destRooms, $near);

        // Point d'arrivée final : end_point enregistré > porte du plan > centre.
        $destRoom = $dest->getRoom();

        $override = NavigationPath::where('qr_code_id', $qr->id)
            ->where('destination_id', $dest->id)
            ->first();

        if ($override && $override->hasEndPoint()) {
            $to       = ['x' => (float) $override->end_point['x'], 'y' => (float) $override->end_point['y']];
            $solidEnd = true;
        } elseif ($destRoom) {
            $to       = $finder->targetPoint($destRoom);
            $solidEnd = $finder->hasDoor($destRoom);
        } else {
            $to       = ['x' => 50, 'y' => 50];
            $solidEnd = false;
        }

        // ── Tronçon 1 : départ → connecteur (étage courant) ────────────────
        if ($connCurrent) {
            $connCurrentCenter = $finder->roomCenter($connCurrent);
            $leg1 = $finder->find($currentRooms, $from, $connCurrentCenter) ?? [$from, $connCurrentCenter];
        } else {
            // Pas de connecteur identifié : on se dirige vers le bord bas du plan
            $connCurrentCenter = ['x' => 50, 'y' => 95];
            $leg1 = $finder->find($currentRooms, $from, $connCurrentCenter) ?? [$from, $connCurrentCenter];
        }

        // ── Tronçon 2 : connecteur → destination (étage d'arrivée) ─────────
        $connDestCenter = $connDest ? $finder->roomCenter($connDest) : $connCurrentCenter;
        $leg2 = $finder->find($destRooms, $connDestCenter, $to, $solidEnd) ?? [$connDestCenter, $to];

        // ── Instruction texte ──────────────────────────────────────────────
        $levelDiff = $destFloor->level - $currentFloor->level;
        $direction = $levelDiff > 0 ? 'montez' : 'descendez';
        $nLevels   = abs($levelDiff);
        $connLabel = $connCurrent['name'] ?? ($connDest['name'] ?? 'escaliers/ascenseur');

        $instruction = sprintf(
            'Dirigez-vous vers « %s », %s de %d niveau%s, puis suivez le chemin jusqu\'à %s (%s).',
            $connLabel,
            $direction,
            $nLevels,
            $nLevels > 1 ? 'x' : '',
            $dest->label,
            $destFloor->label
        );
        $override = \App\Models\NavigationPath::where('qr_code_id', $qr->id)
        ->where('destination_id', $dest->id)
        ->first();
        
        $ovSegs   = $override ? $override->segmentsData() : null;
        $smooth1  = false;
        $smooth2  = false;
        
        if ($ovSegs && count($ovSegs) >= 1 && !empty($ovSegs[0]['waypoints'])) {
            $leg1    = $ovSegs[0]['waypoints'];
            $smooth1 = (bool) ($ovSegs[0]['smooth'] ?? false);
        }
        if ($ovSegs && count($ovSegs) >= 2 && !empty($ovSegs[1]['waypoints'])) {
            $leg2    = $ovSegs[1]['waypoints'];
            $smooth2 = (bool) ($ovSegs[1]['smooth'] ?? false);
        }

        return [
            'type'          => 'multi_floor',
            'current_floor' => ['id' => $currentFloor->id, 'label' => $currentFloor->label, 'level' => $currentFloor->level],
            'target_floor'  => ['id' => $destFloor->id,    'label' => $destFloor->label,    'level' => $destFloor->level],
            'connector'     => [
                'from' => $connCurrent,
                'to'   => $connDest,
            ],
            'destination'   => ['label' => $dest->label, 'room' => $destRoom],
            'instruction'   => $instruction,

            // Rétro-compatibilité : "path" = tronçon 1 (étage courant)
            'path'          => $leg1,
            'floor_id'      => $currentFloor->id,
            'smooth'        => $smooth1,

            // Format complet en deux tronçons (consommé par le frontend)
            'segments' => [
                [
                    'floor_id'    => $currentFloor->id,
                    'floor_label' => $currentFloor->label,
                    'floor_level' => $currentFloor->level,
                    'role'        => 'vers_connecteur',
                    'connector'   => $connCurrent,
                    'path'        => $leg1,
                    'smooth'      => $smooth1,
                ],
                [
                    'floor_id'    => $destFloor->id,
                    'floor_label' => $destFloor->label,
                    'floor_level' => $destFloor->level,
                    'role'        => 'depuis_connecteur',
                    'connector'   => $connDest,
                    'path'        => $leg2,
                    'smooth'      => $smooth2,
                ],
            ],
        ];
    }
}