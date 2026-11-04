<?php
// app/Services/Ai/RouteDescriber.php
//
// Transforme un tracé (waypoints en %) en étapes lisibles, avec des REPÈRES :
//   « Continuez 12 m le long de l'Allée A, en passant devant la Pharmacie »
//   « Tournez à droite au niveau du Stand MTN »
//   « La destination est sur votre gauche »
//
// C'est ce texte factuel que le modèle Llama reformule. Le modèle ne calcule
// jamais de chemin : il ne fait que dire, en langage naturel, ce que l'A* a
// réellement trouvé. Mêmes seuils que scan-v2 (35°, 4 m) pour que la voix et
// l'écran racontent la même chose.
//
// PHP pur (aucune dépendance Laravel) → testable en ligne de commande.

namespace App\Services\Ai;

class RouteDescriber
{
    public const TURN_MIN_ANGLE = 35;   // degrés
    public const MIN_STRAIGHT_M = 4;    // mètres
    private const NEAR_PCT      = 4.0;  // un repère est « proche » à moins de 4 % du plan
    private const SIMPLIFY_EPS  = 1.2;  // % — lissage Douglas-Peucker (comme scan-v2)
    private const JOG_M         = 8;    // m — deux virages opposés plus rapprochés = simple décalage

    private const OPEN_KEYWORDS = [
        'couloir', 'hall', 'accueil', 'sortie', 'entrée', 'entree', 'escalier',
        'ascenseur', 'attente', 'circulation', 'palier', 'allée', 'allee',
        'galerie', 'lobby', 'lounge', 'foyer', 'terrasse', 'passage', 'rampe',
        'esplanade', 'parvis',
    ];

    /**
     * @param array      $rooms      Pièces de l'étage [{id,name,x,y,w,h}]
     * @param array      $path       Waypoints [{x,y}] en %
     * @param float      $widthM     Largeur réelle du bâtiment (m)
     * @param float      $depthM     Profondeur réelle du bâtiment (m)
     * @param array|null $destRoom   Pièce d'arrivée (pour dire « à gauche / à droite »)
     * @return array<int, array>     Étapes structurées
     */
    public function describe(array $rooms, array $path, float $widthM, float $depthM, ?array $destRoom = null): array
    {
        $pts = array_values(array_map(fn ($p) => ['x' => (float) $p['x'], 'y' => (float) $p['y']], $path));
        if (count($pts) < 2) {
            return [];
        }
        // Lisse les « marches d'escalier » de la grille A* (même ε que scan-v2)
        $pts = $this->simplify($pts, self::SIMPLIFY_EPS);
        $n = count($pts);

        // Distance cumulée (m) le long du tracé
        $cum = [0.0];
        for ($i = 1; $i < $n; $i++) {
            $cum[$i] = $cum[$i - 1] + $this->meters($pts[$i - 1], $pts[$i], $widthM, $depthM);
        }

        // Virages significatifs
        $corners = [];
        for ($i = 1; $i < $n - 1; $i++) {
            $ang = $this->turnAngle($pts[$i - 1], $pts[$i], $pts[$i + 1]);
            if (abs($ang) > self::TURN_MIN_ANGLE) {
                $corners[] = ['i' => $i, 'dir' => $ang > 0 ? 'droite' : 'gauche'];
            }
        }

        // Décalages : droite puis gauche (ou l'inverse) à moins de JOG_M l'un de
        // l'autre = on contourne un obstacle, pas un vrai changement de direction.
        // On les supprime pour ne pas noyer le visiteur sous les consignes.
        $changed = true;
        while ($changed) {
            $changed = false;
            for ($k = 0; $k < count($corners) - 1; $k++) {
                $a = $corners[$k];
                $b = $corners[$k + 1];
                if ($a['dir'] !== $b['dir'] && $cum[$b['i']] - $cum[$a['i']] < self::JOG_M) {
                    array_splice($corners, $k, 2);
                    $changed = true;
                    break;
                }
            }
        }

        $steps = [];
        $said = [];          // repères déjà cités récemment → on évite de répéter
        $prev = 0;
        foreach ($corners as $c) {
            $len = $cum[$c['i']] - $cum[$prev];
            if ($len >= self::MIN_STRAIGHT_M) {
                $steps[] = $this->straight($rooms, array_slice($pts, $prev, $c['i'] - $prev + 1), $len, $destRoom, $said);
            }
            $mark = $this->landmarkAt($rooms, $pts[$c['i']], $destRoom, $said);
            $steps[] = ['type' => 'tourner', 'direction' => $c['dir'], 'repere' => $mark];
            if ($mark) $said[] = $mark;
            $prev = $c['i'];
        }

        $last = $pts[$n - 1];
        $len = $cum[$n - 1] - $cum[$prev];
        if ($len >= 2) {
            $steps[] = $this->straight($rooms, array_slice($pts, $prev), $len, $destRoom, $said);
        }

        // Côté de la destination par rapport à la dernière direction de marche
        $arrive = ['type' => 'arrivee'];
        if ($destRoom) {
            $arrive['destination'] = $destRoom['name'] ?? null;
            $before = $pts[$n - 2];
            $c = ['x' => $destRoom['x'] + $destRoom['w'] / 2, 'y' => $destRoom['y'] + $destRoom['h'] / 2];
            $cross = ($last['x'] - $before['x']) * ($c['y'] - $last['y']) - ($last['y'] - $before['y']) * ($c['x'] - $last['x']);
            $dist = hypot($c['x'] - $last['x'], $c['y'] - $last['y']);
            $arrive['cote'] = $dist < 1.0 ? 'devant vous' : ($cross > 0 ? 'sur votre droite' : 'sur votre gauche');
        }
        $steps[] = $arrive;

        return $steps;
    }

    /**
     * Version texte compacte, à donner au modèle comme résultat d'outil.
     */
    public function toText(array $steps): string
    {
        $lines = [];
        foreach ($steps as $i => $s) {
            $n = $i + 1;
            switch ($s['type']) {
                case 'tout_droit':
                    $t = "Avancez d'environ {$s['distance_m']} m";
                    if (!empty($s['zone']))   $t .= " dans « {$s['zone']} »";
                    if (!empty($s['devant'])) $t .= ', en passant devant « ' . implode(' », « ', $s['devant']) . ' »';
                    break;
                case 'tourner':
                    $t = "Tournez à {$s['direction']}";
                    if (!empty($s['repere'])) $t .= " au niveau de « {$s['repere']} »";
                    break;
                case 'changer_etage':
                    $t = "Prenez « {$s['connecteur']} » et {$s['sens']} jusqu'au niveau « {$s['vers']} »";
                    break;
                case 'arrivee':
                    $t = 'Vous êtes arrivé' . (!empty($s['destination']) ? " à « {$s['destination']} »" : '')
                        . (!empty($s['cote']) ? ", {$s['cote']}" : '');
                    break;
                default:
                    $t = json_encode($s, JSON_UNESCAPED_UNICODE);
            }
            $lines[] = "{$n}. {$t}.";
        }
        return implode("\n", $lines);
    }

    // ── Détails ───────────────────────────────────────────────────────────

    /** @param array $line portion de tracé (points en %) parcourue en ligne droite */
    private function straight(array $rooms, array $line, float $m, ?array $destRoom, array &$said): array
    {
        $step = ['type' => 'tout_droit', 'distance_m' => max(5, (int) (round($m / 5) * 5))];
        $samples = $this->sample($line, 9);

        // Zone traversée : la zone de circulation qui contient le milieu du trajet
        $mid = $samples[4];
        foreach ($rooms as $r) {
            if ($this->isOpen($r) && $this->inside($mid, $r) && !empty($r['name'])) {
                $step['zone'] = $r['name'];
                break;
            }
        }

        // Pièces longées (max 2, dans l'ordre de passage), sans répéter ce qui vient d'être dit
        $seen = [];
        foreach (array_slice($samples, 1, 7) as $p) {
            foreach ($rooms as $r) {
                if ($this->isOpen($r) || empty($r['name']) || $this->same($r, $destRoom)) continue;
                if (in_array($r['name'], $seen, true) || in_array($r['name'], array_slice($said, -2), true)) continue;
                if ($this->distToRoom($p, $r) <= self::NEAR_PCT * 0.6) {
                    $seen[] = $r['name'];
                }
            }
            if (count($seen) >= 2) break;
        }
        if ($seen) {
            $step['devant'] = array_slice($seen, 0, 2);
            array_push($said, ...$step['devant']);
        }
        return $step;
    }

    /** Points régulièrement espacés le long d'une polyligne */
    private function sample(array $line, int $count): array
    {
        $seg = [];
        $total = 0.0;
        for ($i = 1; $i < count($line); $i++) {
            $l = hypot($line[$i]['x'] - $line[$i - 1]['x'], $line[$i]['y'] - $line[$i - 1]['y']);
            $seg[] = $l;
            $total += $l;
        }
        $out = [];
        for ($k = 0; $k < $count; $k++) {
            $target = $total * $k / max(1, $count - 1);
            $i = 0;
            while ($i < count($seg) - 1 && $target > $seg[$i]) {
                $target -= $seg[$i];
                $i++;
            }
            $t = $seg ? ($seg[$i] > 0 ? min(1, $target / $seg[$i]) : 0) : 0;
            $a = $line[$i];
            $b = $line[min($i + 1, count($line) - 1)];
            $out[] = ['x' => $a['x'] + ($b['x'] - $a['x']) * $t, 'y' => $a['y'] + ($b['y'] - $a['y']) * $t];
        }
        return $out;
    }

    private function simplify(array $pts, float $eps): array
    {
        if (count($pts) <= 2) return $pts;
        $end = count($pts) - 1;
        $dmax = 0;
        $idx = 0;
        for ($i = 1; $i < $end; $i++) {
            $d = $this->segDist($pts[$i], $pts[0], $pts[$end]);
            if ($d > $dmax) { $dmax = $d; $idx = $i; }
        }
        if ($dmax <= $eps) return [$pts[0], $pts[$end]];
        $l = $this->simplify(array_slice($pts, 0, $idx + 1), $eps);
        $r = $this->simplify(array_slice($pts, $idx), $eps);
        return array_merge(array_slice($l, 0, -1), $r);
    }

    private function segDist(array $p, array $a, array $b): float
    {
        $dx = $b['x'] - $a['x']; $dy = $b['y'] - $a['y'];
        $l = $dx * $dx + $dy * $dy;
        if ($l == 0.0) return hypot($p['x'] - $a['x'], $p['y'] - $a['y']);
        $t = max(0, min(1, (($p['x'] - $a['x']) * $dx + ($p['y'] - $a['y']) * $dy) / $l));
        return hypot($p['x'] - ($a['x'] + $t * $dx), $p['y'] - ($a['y'] + $t * $dy));
    }

    private function landmarkAt(array $rooms, array $p, ?array $destRoom, array $said = []): ?string
    {
        $best = null;
        $bestD = self::NEAR_PCT;
        foreach ($rooms as $r) {
            if (empty($r['name']) || $this->same($r, $destRoom)) continue;
            if (in_array($r['name'], array_slice($said, -2), true)) continue;
            // Une pièce fermée nommée est un meilleur repère qu'un couloir
            $d = $this->distToRoom($p, $r) + ($this->isOpen($r) ? 1.5 : 0);
            if ($d < $bestD) {
                $bestD = $d;
                $best = $r['name'];
            }
        }
        return $best;
    }

    private function isOpen(array $r): bool
    {
        $n = mb_strtolower($r['name'] ?? '');
        foreach (self::OPEN_KEYWORDS as $kw) {
            if (str_contains($n, $kw)) return true;
        }
        return false;
    }

    private function same(array $r, ?array $other): bool
    {
        return $other && ($r['id'] ?? null) !== null && ($r['id'] ?? null) === ($other['id'] ?? null);
    }

    private function inside(array $p, array $r): bool
    {
        return $p['x'] >= $r['x'] && $p['x'] <= $r['x'] + $r['w'] && $p['y'] >= $r['y'] && $p['y'] <= $r['y'] + $r['h'];
    }

    private function distToRoom(array $p, array $r): float
    {
        $dx = max($r['x'] - $p['x'], 0, $p['x'] - ($r['x'] + $r['w']));
        $dy = max($r['y'] - $p['y'], 0, $p['y'] - ($r['y'] + $r['h']));
        return hypot($dx, $dy);
    }

    private function meters(array $a, array $b, float $w, float $d): float
    {
        return hypot(($b['x'] - $a['x']) / 100 * $w, ($b['y'] - $a['y']) / 100 * $d);
    }

    /** > 0 = à droite (repère écran, y vers le bas) */
    private function turnAngle(array $a, array $b, array $c): float
    {
        $v1x = $b['x'] - $a['x']; $v1y = $b['y'] - $a['y'];
        $v2x = $c['x'] - $b['x']; $v2y = $c['y'] - $b['y'];
        return rad2deg(atan2($v1x * $v2y - $v1y * $v2x, $v1x * $v2x + $v1y * $v2y));
    }
}