<?php
// app/Services/AStarPathfinder.php
//
// Algorithme A* sur grille canvas.
//
// MODÈLE DE NAVIGATION :
// - Tout l'espace du plan est navigable par défaut (les couloirs ET l'intérieur
//   des salles sont franchissables).
// - Les ZONES DE CIRCULATION (couloir, hall, allée, galerie, lobby, salle
//   d'attente, ascenseur, escalier, palier...) ne bloquent JAMAIS : le chemin
//   peut donc passer librement à travers elles pour rejoindre la destination.
// - Les autres pièces (bureaux, salles fermées, blocs...) sont des obstacles,
//   SAUF si elles sont la destination ou contiennent le point de départ.
//
// ── NOUVEAU : POINT DE PORTE ──────────────────────────────────────────────
// Une pièce peut désormais déclarer une PORTE : room['door'] = ['x'=>%, 'y'=>%].
// - targetPoint($room) renvoie la porte si elle existe, sinon le centre.
// - Quand on vise une porte, on appelle find(..., $solidEnd: true) : la pièce
//   RESTE un obstacle, et le chemin s'arrête proprement sur le seuil au lieu
//   de traverser les murs pour rejoindre un centre théorique.
// - Avantage : plus besoin d'ajouter des zones d'accès artificielles sur le
//   plan, et l'arrivée est plus réaliste (c'est une entrée, pas un milieu).
//
// MULTI-ÉTAGES :
// - findConnectorRoom() localise l'ascenseur / l'escalier le plus pertinent
//   d'un étage. Le contrôleur l'utilise pour découper un trajet inter-étages
//   en deux tronçons : départ → ascenseur (étage courant), puis
//   ascenseur → destination (étage d'arrivée).
//
// Le résultat est une liste de waypoints en % retournée au frontend.

namespace App\Services;

class AStarPathfinder
{
    // Résolution de la grille (plus grand = plus précis mais plus lent)
    private const GRID = 100;

    // Mots-clés (insensibles à la casse) indiquant une zone TOUJOURS libre
    // (couloirs, halls, accueils, sorties, allées, galeries...) — ces pièces
    // ne bloquent jamais : le chemin peut passer à travers.
    public const OPEN_KEYWORDS = [
        'couloir', 'hall', 'accueil', 'sortie', 'entrée', 'entree',
        'escalier', 'ascenseur', 'attente', 'circulation', 'palier',
        'allée', 'allee', 'galerie', 'lobby', 'lounge', 'foyer',
        'terrasse', 'passage', 'rampe', 'esplanade', 'parvis',
    ];

    // Mots-clés identifiant un CONNECTEUR vertical (changement d'étage).
    public const CONNECTOR_KEYWORDS = [
        'ascenseur', 'escalier', 'palier', 'élévateur', 'elevateur',
    ];

    /**
     * Calcule le chemin entre deux points sur un plan d'étage.
     *
     * @param  array  $rooms     Pièces [{id, x, y, w, h, name, door?}] (coordonnées en %)
     * @param  array  $from      Point de départ  ['x' => float, 'y' => float] (en %)
     * @param  array  $to        Point d'arrivée  ['x' => float, 'y' => float] (en %)
     * @param  bool   $solidEnd  true quand $to est une PORTE : la pièce d'arrivée
     *                           reste un obstacle et le chemin s'arrête au seuil.
     * @return array|null        Waypoints [['x'=>float,'y'=>float]] ou null
     */
    public function find(array $rooms, array $from, array $to, bool $solidEnd = false): ?array
    {
        $G = self::GRID;

        // ── 1. Construire la grille de navigation ──────────────────────────
        // 0 = libre, 1 = mur (obstacle)
        $grid = array_fill(0, $G, array_fill(0, $G, 0));

        $startX = (int) round((float) $from['x'] / 100 * $G);
        $startY = (int) round((float) $from['y'] / 100 * $G);
        $endX   = (int) round((float) $to['x']   / 100 * $G);
        $endY   = (int) round((float) $to['y']   / 100 * $G);

        $startX = max(0, min($G - 1, $startX));
        $startY = max(0, min($G - 1, $startY));
        $endX   = max(0, min($G - 1, $endX));
        $endY   = max(0, min($G - 1, $endY));

        foreach ($rooms as $room) {
            $name = mb_strtolower($room['name'] ?? '');

            // Les zones de circulation (couloirs, halls, allées, etc.) restent libres
            if (self::isOpenZone($name)) {
                continue;
            }

            $rx = (int) round((float) $room['x'] / 100 * $G);
            $ry = (int) round((float) $room['y'] / 100 * $G);
            $rw = (int) round((float) $room['w'] / 100 * $G);
            $rh = (int) round((float) $room['h'] / 100 * $G);

            // Si le DÉPART se trouve à l'intérieur de cette pièce, elle ne doit
            // pas être un obstacle (sinon le point de départ est piégé).
            if ($this->pointInRect($startX, $startY, $rx, $ry, $rw, $rh)) {
                continue;
            }

            // Si l'ARRIVÉE est à l'intérieur :
            //  - mode "porte" ($solidEnd) : la pièce reste solide. Le chemin
            //    s'arrête sur le seuil (la case de la porte est libérée plus bas).
            //  - mode historique (centre) : on exempte la pièce, sinon le centre
            //    serait inatteignable.
            if (!$solidEnd && $this->pointInRect($endX, $endY, $rx, $ry, $rw, $rh)) {
                continue;
            }

            // Marquer la pièce comme obstacle
            $margin = 0;
            for ($cy = $ry + $margin; $cy < min($ry + $rh - $margin, $G); $cy++) {
                for ($cx = $rx + $margin; $cx < min($rx + $rw - $margin, $G); $cx++) {
                    if ($cx >= 0 && $cy >= 0) {
                        $grid[$cy][$cx] = 1;
                    }
                }
            }
        }

        // Sécurité : les cases de départ/arrivée sont toujours libres.
        // En mode porte, c'est exactement ce qui perce le seuil dans le mur.
        $grid[$startY][$startX] = 0;
        $grid[$endY][$endX]     = 0;

        // ── 2. A* ──────────────────────────────────────────────────────────
        $open     = new \SplMinHeap();
        $closed   = [];
        $cameFrom = [];
        $gScore   = [];
        $fScore   = [];

        $key = fn($x, $y) => $y * $G + $x;
        $h   = fn($x, $y) => abs($x - $endX) + abs($y - $endY); // Manhattan

        $startKey = $key($startX, $startY);
        $gScore[$startKey] = 0;
        $fScore[$startKey] = $h($startX, $startY);
        $open->insert([$fScore[$startKey], $startX, $startY]);

        $directions = [[0,1],[0,-1],[1,0],[-1,0],[1,1],[1,-1],[-1,1],[-1,-1]];

        $found = false;
        $iterations = 0;
        $maxIterations = $G * $G * 2;

        while (!$open->isEmpty() && $iterations++ < $maxIterations) {
            [, $cx, $cy] = $open->extract();
            $ck = $key($cx, $cy);

            if ($cx === $endX && $cy === $endY) {
                $found = true;
                break;
            }

            if (isset($closed[$ck])) continue;
            $closed[$ck] = true;

            foreach ($directions as [$dx, $dy]) {
                $nx = $cx + $dx;
                $ny = $cy + $dy;

                if ($nx < 0 || $nx >= $G || $ny < 0 || $ny >= $G) continue;
                if ($grid[$ny][$nx] === 1) continue;

                // Empêcher les coupes en diagonale à travers un coin de mur
                if ($dx !== 0 && $dy !== 0) {
                    if ($grid[$cy][$nx] === 1 && $grid[$ny][$cx] === 1) continue;
                }

                $nk = $key($nx, $ny);
                if (isset($closed[$nk])) continue;

                $tentativeG = ($gScore[$ck] ?? INF) + (($dx !== 0 && $dy !== 0) ? 1.414 : 1.0);

                if ($tentativeG < ($gScore[$nk] ?? INF)) {
                    $cameFrom[$nk] = [$cx, $cy];
                    $gScore[$nk]   = $tentativeG;
                    $fScore[$nk]   = $tentativeG + $h($nx, $ny);
                    $open->insert([$fScore[$nk], $nx, $ny]);
                }
            }
        }

        if (!$found) {
            // Dernier recours : ligne droite (mieux que rien côté UI)
            return [
                ['x' => round((float) $from['x'], 1), 'y' => round((float) $from['y'], 1)],
                ['x' => round((float) $to['x'], 1),   'y' => round((float) $to['y'], 1)],
            ];
        }

        // ── 3. Reconstruire le chemin ──────────────────────────────────────
        $path = [];
        $cx = $endX;
        $cy = $endY;

        while (!($cx === $startX && $cy === $startY)) {
            $path[] = ['x' => round($cx / $G * 100, 1), 'y' => round($cy / $G * 100, 1)];
            [$cx, $cy] = $cameFrom[$key($cx, $cy)] ?? [$startX, $startY];
        }
        $path[] = ['x' => round($startX / $G * 100, 1), 'y' => round($startY / $G * 100, 1)];

        $path = array_reverse($path);

        // ── 4. Simplifier le chemin (Douglas-Peucker) ─────────────────────
        return $this->simplify($path, 1.5);
    }

    /**
     * ── NOUVEAU ───────────────────────────────────────────────────────────
     * Point d'arrivée à viser pour une pièce : sa PORTE si elle en déclare une,
     * sinon son centre (comportement historique).
     *
     * Format attendu dans le JSON de la pièce :
     *   'door' => ['x' => 64.0, 'y' => 60.0]   // coordonnées absolues en %
     *
     * @param  array  $room
     * @return array  ['x' => float, 'y' => float]
     */
    public function targetPoint(array $room): array
    {
        $door = $this->doorOf($room);

        return $door ?? $this->roomCenter($room);
    }

    /**
     * ── NOUVEAU ───────────────────────────────────────────────────────────
     * Renvoie la porte d'une pièce si elle est déclarée ET valide, sinon null.
     */
    public function doorOf(array $room): ?array
    {
        $door = $room['door'] ?? null;

        if (!is_array($door)) {
            return null;
        }
        if (!isset($door['x'], $door['y']) || !is_numeric($door['x']) || !is_numeric($door['y'])) {
            return null;
        }

        return [
            'x' => (float) $door['x'],
            'y' => (float) $door['y'],
        ];
    }

    /**
     * ── NOUVEAU ───────────────────────────────────────────────────────────
     * Indique si l'on doit traiter la pièce d'arrivée comme solide
     * (c'est le cas dès qu'une porte est déclarée).
     */
    public function hasDoor(array $room): bool
    {
        return $this->doorOf($room) !== null;
    }

    /**
     * ── NOUVEAU ───────────────────────────────────────────────────────────
     * Projette un point sur le bord le plus proche d'une pièce.
     * Utile côté éditeur : l'admin clique n'importe où, la porte se colle au mur.
     *
     * @return array ['x' => float, 'y' => float, 'side' => 'left'|'right'|'top'|'bottom']
     */
    public function snapToEdge(array $room, float $x, float $y): array
    {
        $rx = (float) $room['x'];
        $ry = (float) $room['y'];
        $rw = (float) $room['w'];
        $rh = (float) $room['h'];

        // Garder le point dans l'emprise de la pièce
        $cx = max($rx, min($rx + $rw, $x));
        $cy = max($ry, min($ry + $rh, $y));

        // Distance à chacun des 4 bords
        $dLeft   = $cx - $rx;
        $dRight  = ($rx + $rw) - $cx;
        $dTop    = $cy - $ry;
        $dBottom = ($ry + $rh) - $cy;

        $min = min($dLeft, $dRight, $dTop, $dBottom);

        if ($min === $dLeft)   return ['x' => $rx,       'y' => $cy, 'side' => 'left'];
        if ($min === $dRight)  return ['x' => $rx + $rw, 'y' => $cy, 'side' => 'right'];
        if ($min === $dTop)    return ['x' => $cx, 'y' => $ry,       'side' => 'top'];

        return ['x' => $cx, 'y' => $ry + $rh, 'side' => 'bottom'];
    }

    /**
     * Localise le connecteur vertical (ascenseur / escalier / palier) le plus
     * pertinent d'un étage.
     *
     * @param  array       $rooms  Pièces de l'étage
     * @param  array|null  $near   Point de référence ['x','y'] (en %). Si fourni,
     *                             on choisit le connecteur dont le centre est le
     *                             plus proche — utile pour aligner verticalement
     *                             l'ascenseur entre deux étages.
     * @return array|null          La pièce connecteur, ou null si aucune trouvée.
     */
    public function findConnectorRoom(array $rooms, ?array $near = null): ?array
    {
        $candidates = [];

        foreach ($rooms as $room) {
            $name = mb_strtolower($room['name'] ?? '');
            foreach (self::CONNECTOR_KEYWORDS as $kw) {
                if (str_contains($name, $kw)) {
                    $candidates[] = $room;
                    break;
                }
            }
        }

        if (empty($candidates)) {
            return null;
        }

        if ($near === null) {
            return $candidates[0];
        }

        // Choisir le connecteur le plus proche du point de référence
        usort($candidates, function ($a, $b) use ($near) {
            $ca = $this->roomCenter($a);
            $cb = $this->roomCenter($b);
            $da = ($ca['x'] - $near['x']) ** 2 + ($ca['y'] - $near['y']) ** 2;
            $db = ($cb['x'] - $near['x']) ** 2 + ($cb['y'] - $near['y']) ** 2;
            return $da <=> $db;
        });

        return $candidates[0];
    }

    /**
     * Renvoie le centre d'une pièce (coordonnées en %).
     */
    public function roomCenter(array $room): array
    {
        return [
            'x' => (float) $room['x'] + (float) $room['w'] / 2,
            'y' => (float) $room['y'] + (float) $room['h'] / 2,
        ];
    }

    /**
     * Indique si le nom d'une pièce correspond à une zone de circulation
     * toujours franchissable (couloir, hall, ascenseur, escalier, allée...).
     */
    public static function isOpenZone(string $name): bool
    {
        $name = mb_strtolower($name);
        foreach (self::OPEN_KEYWORDS as $kw) {
            if (str_contains($name, $kw)) return true;
        }
        return false;
    }

    private function pointInRect(int $px, int $py, int $rx, int $ry, int $rw, int $rh): bool
    {
        return $px >= $rx && $px < $rx + $rw && $py >= $ry && $py < $ry + $rh;
    }

    /**
     * Simplification du chemin — réduit les waypoints redondants.
     * Algorithme Ramer-Douglas-Peucker.
     */
    private function simplify(array $points, float $epsilon): array
    {
        if (count($points) <= 2) return $points;

        $dmax = 0;
        $index = 0;
        $end = count($points) - 1;

        for ($i = 1; $i < $end; $i++) {
            $d = $this->perpendicularDistance($points[$i], $points[0], $points[$end]);
            if ($d > $dmax) { $dmax = $d; $index = $i; }
        }

        if ($dmax > $epsilon) {
            $left  = $this->simplify(array_slice($points, 0, $index + 1), $epsilon);
            $right = $this->simplify(array_slice($points, $index), $epsilon);
            return array_merge(array_slice($left, 0, -1), $right);
        }

        return [$points[0], $points[$end]];
    }

    private function perpendicularDistance(array $p, array $a, array $b): float
    {
        $dx = $b['x'] - $a['x'];
        $dy = $b['y'] - $a['y'];
        if ($dx === 0.0 && $dy === 0.0) {
            return sqrt(($p['x'] - $a['x']) ** 2 + ($p['y'] - $a['y']) ** 2);
        }
        $t = (($p['x'] - $a['x']) * $dx + ($p['y'] - $a['y']) * $dy) / ($dx**2 + $dy**2);
        $t = max(0, min(1, $t));
        return sqrt(($p['x'] - ($a['x'] + $t * $dx)) ** 2 + ($p['y'] - ($a['y'] + $t * $dy)) ** 2);
    }
}