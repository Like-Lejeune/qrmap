<?php
// app/Http/Controllers/AiGuideController.php
//
// POST /api/v1/scan/{uuid}/guide
// Body : { "messages": [ {"role":"user","content":"Où est la pharmacie ?"}, ... ] }
// Réponse : { "reply": "...", "route": { destination_id, label, ... } | null }
//
// `route` permet au front d'afficher le bouton « Suivre sur le plan », qui
// lance le guidage pas à pas existant (startNavigation) vers la même destination.

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\NavigationPath;
use App\Models\QrCode;
use App\Services\AStarPathfinder;
use App\Services\Ai\AiGuide;
use App\Services\Ai\ModelClient;
use App\Services\Ai\RouteDescriber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class AiGuideController extends Controller
{
    public function chat(Request $request, string $uuid, AStarPathfinder $finder, RouteDescriber $describer): JsonResponse
    {
        $data = $request->validate([
            'messages'           => 'required|array|min:1|max:12',
            'messages.*.role'    => 'required|in:user,assistant',
            'messages.*.content' => 'required|string|max:600',
        ]);

        $qr = QrCode::with('floor.building')->where('uuid', $uuid)->firstOrFail();
        $floor = $qr->floor;
        $building = $floor?->building;
        if (!$building) {
            return response()->json(['reply' => "Ce QR code n'est rattaché à aucun bâtiment.", 'route' => null]);
        }

        $floors = $building->floors()->with(['destinations' => fn ($q) => $q->active()])->orderBy('level')->get();
        $model = $building->model_3d ?? [];
        $W = (float) ($model['width_m'] ?? 40);
        $D = (float) ($model['depth_m'] ?? 25);

        // ── Contexte donné au modèle ───────────────────────────────────────
        $context = [
            'site'         => $building->site?->name ?? $building->name,
            'building'     => $building->name,
            'here'         => $qr->location_label ?? 'entrée',
            'floor'        => $floor->label ?? ('Niveau ' . $floor->level),
            'destinations' => [],
        ];
        foreach ($floors as $f) {
            foreach ($f->destinations as $d) {
                $context['destinations'][] = [
                    'id'          => $d->id,
                    'label'       => $d->label,
                    'floor'       => $f->label ?? ('Niveau ' . $f->level),
                    'category'    => $d->category,
                    'description' => $d->description,
                ];
            }
        }

        // ── Outil d'itinéraire : A* + descripteur (même priorité que /navigate) ──
        $route = function (int $destId) use ($qr, $finder, $describer, $W, $D) {
            $dest = Destination::with('floor')->find($destId);
            if (!$dest) return null;
            return $this->buildRoute($qr, $dest, $finder, $describer, $W, $D);
        };

        $client = ModelClient::fromConfig();
        $guide = new AiGuide(fn (array $m, array $t) => $client->chat($m, $t), $route);

        try {
            $result = $guide->answer($context, $data['messages']);
        } catch (Throwable $e) {
            Log::warning('Guide IA indisponible', ['qr' => $uuid, 'error' => $e->getMessage()]);
            return response()->json([
                'reply' => "Le guide vocal est momentanément indisponible. Choisissez votre destination dans la liste, l'itinéraire s'affichera.",
                'route' => null,
                'error' => true,
            ], 503);
        }

        return response()->json($result);
    }

    /**
     * Itinéraire décrit en texte, mono ou multi-étages.
     * Priorité du point d'arrivée : porte enregistrée > porte du plan > centre.
     */
    private function buildRoute(QrCode $qr, Destination $dest, AStarPathfinder $finder, RouteDescriber $describer, float $W, float $D): array
    {
        $from = ['x' => (float) $qr->pos_x, 'y' => (float) $qr->pos_y];
        $destRoom = $dest->getRoom();

        $override = NavigationPath::where('qr_code_id', $qr->id)->where('destination_id', $dest->id)->first();
        $saved = $override ? $override->segmentsData() : [];
        $lastSaved = $saved[count($saved) - 1] ?? null;

        $ep = $lastSaved['end_point'] ?? null;
        if (is_array($ep) && isset($ep['x'], $ep['y'])) {
            $to = ['x' => (float) $ep['x'], 'y' => (float) $ep['y']];
            $solid = true;
        } elseif ($destRoom) {
            $to = $finder->targetPoint($destRoom);
            $solid = $finder->hasDoor($destRoom);
        } else {
            $to = ['x' => 50, 'y' => 50];
            $solid = false;
        }

        $steps = [];
        $meters = 0.0;
        $len = function (array $p) use ($W, $D) {
            $m = 0.0;
            for ($i = 1; $i < count($p); $i++) {
                $m += hypot(($p[$i]['x'] - $p[$i - 1]['x']) / 100 * $W, ($p[$i]['y'] - $p[$i - 1]['y']) / 100 * $D);
            }
            return $m;
        };

        if ($qr->floor_id === $dest->floor_id) {
            $rooms = $qr->floor->rooms ?? [];
            $path = ($saved[0]['waypoints'] ?? null) ?: ($finder->find($rooms, $from, $to, $solid) ?? [$from, $to]);
            $steps = $describer->describe($rooms, $path, $W, $D, $destRoom);
            $meters = $len($path);
        } else {
            $cf = $qr->floor;
            $df = $dest->floor;
            $cr = $cf->rooms ?? [];
            $dr = $df->rooms ?? [];
            $connC = $finder->findConnectorRoom($cr, $from);
            $connD = $finder->findConnectorRoom($dr, $connC ? $finder->roomCenter($connC) : null);
            $cC = $connC ? $finder->roomCenter($connC) : ['x' => 50, 'y' => 95];
            $cD = $connD ? $finder->roomCenter($connD) : $cC;

            $leg1 = ($saved[0]['waypoints'] ?? null) ?: ($finder->find($cr, $from, $cC) ?? [$from, $cC]);
            $leg2 = ($saved[1]['waypoints'] ?? null) ?: ($finder->find($dr, $cD, $to, $solid) ?? [$cD, $to]);

            $s1 = $describer->describe($cr, $leg1, $W, $D, $connC);
            array_pop($s1); // pas d'« arrivée » devant l'ascenseur
            $steps = array_merge($s1, [[
                'type'       => 'changer_etage',
                'connecteur' => $connC['name'] ?? 'ascenseur / escalier',
                'sens'       => $df->level > $cf->level ? 'montez' : 'descendez',
                'vers'       => $df->label ?? ('Niveau ' . $df->level),
            ]], $describer->describe($dr, $leg2, $W, $D, $destRoom));
            $meters = $len($leg1) + $len($leg2);
        }

        return [
            'destination_id' => $dest->id,
            'label'          => $dest->label,
            'floor_label'    => $dest->floor->label ?? null,
            'distance_m'     => (int) round($meters),
            'minutes'        => max(1, (int) round($meters / 70)),   // ~70 m/min à pied
            'steps'          => $steps,
            'text'           => $describer->toText($steps),
        ];
    }
}