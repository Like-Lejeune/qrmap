<?php
// app/Services/Ai/PlanVision.php
//
// « Photo du plan → pièces dans l'éditeur »
// Le modèle de Meta (Muse Spark, entrée image) lit un plan d'étage (photo du
// plan d'évacuation, scan, export d'architecte) et propose les pièces au
// format de l'éditeur QRMap : nom, rectangle en %, type, porte.
//
// Ce n'est qu'une PREMIÈRE ÉBAUCHE : les positions lues sur une image sont
// approximatives. L'image est affichée en calque sous le plan dans l'éditeur
// pour que l'admin corrige en quelques glisser-déposer.
//
// Tout ce que renvoie le modèle est revalidé ici (bornes, tailles, portes sur
// un bord, noms reconnus par l'A*), car la sortie n'est pas garantie conforme.
//
// PHP pur (aucune dépendance Laravel) → testable en ligne de commande.

namespace App\Services\Ai;

class PlanVision
{
    public const MAX_ROOMS = 120;

    /** Mots que l'A* reconnaît comme zones franchissables (cf. AStarPathfinder). */
    private const OPEN_KEYWORDS = [
        'couloir', 'hall', 'accueil', 'sortie', 'entrée', 'entree', 'escalier',
        'ascenseur', 'attente', 'circulation', 'palier', 'allée', 'allee',
        'galerie', 'lobby', 'lounge', 'foyer', 'terrasse', 'passage', 'rampe',
        'esplanade', 'parvis',
    ];

    /**
     * Messages + format de réponse à envoyer au modèle.
     *
     * @param string      $imageDataUrl  data:image/...;base64,...
     * @param string|null $hint          précision de l'admin (« RDC », « 40 × 25 m »…)
     * @return array{messages: array, response_format: array}
     */
    public function request(string $imageDataUrl, ?string $hint = null): array
    {
        $instructions = <<<TXT
Tu lis un plan d'étage pour une application de navigation intérieure (QRMap).

Étape 1 — Repère le contour extérieur du bâtiment sur l'image. Donne son
rectangle englobant "building_bbox" en pourcentage de l'IMAGE (x, y = coin
haut-gauche ; w, h = largeur, hauteur ; valeurs de 0 à 100).

Étape 2 — Pour chaque pièce ou zone, donne un rectangle englobant en
pourcentage du CONTOUR DU BÂTIMENT (pas de l'image) : 0,0 = coin haut-gauche
du bâtiment, 100,100 = coin bas-droite.

Règles :
- "kind" : "salle" (bureau, chambre, boutique, WC, local fermé),
  "circulation" (couloir, hall, allée, palier, accueil ouvert, terrasse),
  "connecteur" (escalier, ascenseur).
- "name" : le nom écrit sur le plan, en français. Sans nom lisible, invente un
  nom court descriptif (« Bureau 3 », « Couloir nord »). Une zone de
  circulation DOIT contenir un mot comme Couloir, Hall, Allée, Palier, Accueil
  ou Terrasse ; un connecteur DOIT contenir Escalier ou Ascenseur.
- "door" : le milieu de la porte principale de la pièce, en % du bâtiment, sur
  le bord de la pièce. null si aucune porte n'est visible.
- Ignore le mobilier, les cotes, la légende, le cartouche et les textes hors bâtiment.
- Les rectangles peuvent se chevaucher (un couloir traverse souvent tout l'étage).
- "width_m" / "depth_m" : dimensions extérieures du bâtiment en mètres si des
  cotes ou une échelle sont lisibles, sinon null. N'invente pas de dimensions.
- "notes" : en une phrase, ce qui était difficile à lire.
TXT;
        if ($hint) {
            $instructions .= "\n\nPrécision donnée par l'administrateur : " . mb_substr(trim($hint), 0, 300);
        }

        return [
            'messages' => [[
                'role'    => 'user',
                'content' => [
                    ['type' => 'text', 'text' => $instructions],
                    ['type' => 'image_url', 'image_url' => ['url' => $imageDataUrl]],
                ],
            ]],
            'response_format' => [
                'type'        => 'json_schema',
                'json_schema' => ['name' => 'qrmap_floor_plan', 'schema' => $this->schema()],
            ],
        ];
    }

    public function schema(): array
    {
        $num = ['type' => 'number'];
        $rect = [
            'type'       => 'object',
            'properties' => ['x' => $num, 'y' => $num, 'w' => $num, 'h' => $num],
            'required'   => ['x', 'y', 'w', 'h'],
        ];
        $point = [
            'type'       => 'object',
            'properties' => ['x' => $num, 'y' => $num],
            'required'   => ['x', 'y'],
        ];

        return [
            'type'       => 'object',
            'properties' => [
                'floor_label'   => ['type' => 'string'],
                'building_bbox' => $rect,
                'width_m'       => ['anyOf' => [$num, ['type' => 'null']]],
                'depth_m'       => ['anyOf' => [$num, ['type' => 'null']]],
                'rooms'         => [
                    'type'  => 'array',
                    'items' => [
                        'type'       => 'object',
                        'properties' => [
                            'name' => ['type' => 'string'],
                            'kind' => ['type' => 'string', 'enum' => ['salle', 'circulation', 'connecteur']],
                            'x' => $num, 'y' => $num, 'w' => $num, 'h' => $num,
                            'door' => ['anyOf' => [$point, ['type' => 'null']]],
                        ],
                        'required' => ['name', 'kind', 'x', 'y', 'w', 'h', 'door'],
                    ],
                ],
                'notes' => ['type' => 'string'],
            ],
            'required' => ['floor_label', 'building_bbox', 'width_m', 'depth_m', 'rooms', 'notes'],
        ];
    }

    /**
     * Valide et nettoie la réponse du modèle.
     *
     * @param array $raw    JSON décodé renvoyé par le modèle
     * @param int   $level  niveau de l'étage cible (pour les identifiants F{level}R{n})
     * @return array{rooms: array, building: array, bbox: array, floor_label: ?string, warnings: array}
     */
    public function normalize(array $raw, int $level = 0): array
    {
        $warnings = [];
        $rooms = [];
        $list = is_array($raw['rooms'] ?? null) ? $raw['rooms'] : [];

        if (count($list) > self::MAX_ROOMS) {
            $warnings[] = 'Plus de ' . self::MAX_ROOMS . ' pièces détectées : seules les premières ont été gardées.';
            $list = array_slice($list, 0, self::MAX_ROOMS);
        }

        $dropped = 0;
        $doorsFixed = 0;
        $renamed = 0;
        $unnamed = 0;
        $n = 1;

        foreach ($list as $r) {
            if (!is_array($r)) { $dropped++; continue; }

            $x = $this->num($r['x'] ?? null);
            $y = $this->num($r['y'] ?? null);
            $w = $this->num($r['w'] ?? null);
            $h = $this->num($r['h'] ?? null);
            if ($x === null || $y === null || $w === null || $h === null) { $dropped++; continue; }

            // Rectangle ramené dans [0,100]
            $x1 = $this->clamp($x); $y1 = $this->clamp($y);
            $x2 = $this->clamp($x + $w); $y2 = $this->clamp($y + $h);
            if ($x2 - $x1 < 1 || $y2 - $y1 < 1) { $dropped++; continue; }   // trop petit = bruit

            $kind = in_array($r['kind'] ?? '', ['salle', 'circulation', 'connecteur'], true) ? $r['kind'] : 'salle';
            $name = trim(preg_replace('/\s+/u', ' ', (string) ($r['name'] ?? '')));
            $name = mb_substr($name, 0, 60);

            // Le type de zone est déduit du NOM par l'A* : on garantit le bon mot-clé
            if ($name === '') $unnamed++;
            [$name, $wasRenamed] = $this->ensureKeyword($name, $kind, $n);
            if ($wasRenamed && $kind !== 'salle') $renamed++;

            $room = [
                'id'   => 'F' . $level . 'R' . $n,
                'name' => $name,
                'x'    => round($x1, 1),
                'y'    => round($y1, 1),
                'w'    => round($x2 - $x1, 1),
                'h'    => round($y2 - $y1, 1),
            ];

            // Porte : seulement pour les pièces fermées, recollée sur le bord le plus proche
            $d = $r['door'] ?? null;
            if ($kind === 'salle' && is_array($d)) {
                $dx = $this->num($d['x'] ?? null);
                $dy = $this->num($d['y'] ?? null);
                if ($dx !== null && $dy !== null) {
                    $snap = $this->snapToEdge($room, $dx, $dy);
                    if (abs($snap['x'] - $dx) > 0.5 || abs($snap['y'] - $dy) > 0.5) $doorsFixed++;
                    $room['door'] = ['x' => round($snap['x'], 1), 'y' => round($snap['y'], 1)];
                }
            }

            $rooms[] = $room;
            $n++;
        }

        if ($dropped) $warnings[] = "{$dropped} zone(s) illisible(s) ou trop petite(s) ignorée(s).";
        if ($doorsFixed) $warnings[] = "{$doorsFixed} porte(s) recollée(s) sur le bord de leur pièce.";
        if ($renamed) $warnings[] = "{$renamed} zone(s) de passage renommée(s) pour être reconnues par la navigation.";
        if ($unnamed) $warnings[] = "{$unnamed} pièce(s) sans nom lisible : à renommer.";
        if (!$rooms) $warnings[] = "Aucune pièce exploitable n'a été détectée sur cette image.";
        if (!empty($raw['notes']) && is_string($raw['notes'])) {
            $warnings[] = 'Remarque du modèle : ' . mb_substr(trim($raw['notes']), 0, 200);
        }

        // Rectangle du bâtiment dans l'image (pour aligner le calque de fond)
        $b = is_array($raw['building_bbox'] ?? null) ? $raw['building_bbox'] : [];
        $bx = $this->clamp($this->num($b['x'] ?? null) ?? 0);
        $by = $this->clamp($this->num($b['y'] ?? null) ?? 0);
        $bw = $this->num($b['w'] ?? null);
        $bh = $this->num($b['h'] ?? null);
        $bw = ($bw !== null && $bw >= 5) ? min($bw, 100 - $bx) : 100 - $bx;
        $bh = ($bh !== null && $bh >= 5) ? min($bh, 100 - $by) : 100 - $by;

        // Dimensions : seulement si plausibles (2 m à 1 km)
        $wm = $this->num($raw['width_m'] ?? null);
        $dm = $this->num($raw['depth_m'] ?? null);
        $building = [
            'width_m' => ($wm !== null && $wm >= 2 && $wm <= 1000) ? round($wm, 1) : null,
            'depth_m' => ($dm !== null && $dm >= 2 && $dm <= 1000) ? round($dm, 1) : null,
        ];

        $label = isset($raw['floor_label']) && is_string($raw['floor_label']) ? mb_substr(trim($raw['floor_label']), 0, 60) : null;

        return [
            'rooms'       => $rooms,
            'building'    => $building,
            'bbox'        => ['x' => round($bx, 2), 'y' => round($by, 2), 'w' => round($bw, 2), 'h' => round($bh, 2)],
            'floor_label' => $label ?: null,
            'warnings'    => $warnings,
        ];
    }

    // ── Détails ───────────────────────────────────────────────────────────

    private function ensureKeyword(string $name, string $kind, int $n): array
    {
        $low = mb_strtolower($name);
        $has = fn (array $kws) => (bool) array_filter($kws, fn ($k) => str_contains($low, $k));

        if ($kind === 'connecteur') {
            if ($has(['escalier', 'ascenseur', 'élévateur', 'elevateur'])) return [$name, false];
            return [trim('Escalier ' . $name) ?: "Escalier {$n}", true];
        }
        if ($kind === 'circulation') {
            if ($has(self::OPEN_KEYWORDS)) return [$name, false];
            return [trim('Couloir ' . $name) ?: "Couloir {$n}", true];
        }
        // Pièce fermée : si son nom contient un mot de circulation, l'A* la
        // traiterait comme un passage. On ne touche pas au nom (choix de
        // l'admin), mais une pièce sans nom reçoit un nom neutre.
        return [$name !== '' ? $name : "Pièce {$n}", $name === ''];
    }

    private function snapToEdge(array $r, float $x, float $y): array
    {
        $cx = max($r['x'], min($r['x'] + $r['w'], $x));
        $cy = max($r['y'], min($r['y'] + $r['h'], $y));
        $d = [
            'l' => $cx - $r['x'], 'r' => $r['x'] + $r['w'] - $cx,
            't' => $cy - $r['y'], 'b' => $r['y'] + $r['h'] - $cy,
        ];
        $m = min($d);
        if ($m === $d['l']) return ['x' => $r['x'], 'y' => $cy];
        if ($m === $d['r']) return ['x' => $r['x'] + $r['w'], 'y' => $cy];
        if ($m === $d['t']) return ['x' => $cx, 'y' => $r['y']];
        return ['x' => $cx, 'y' => $r['y'] + $r['h']];
    }

    private function num($v): ?float
    {
        return is_numeric($v) && is_finite((float) $v) ? (float) $v : null;
    }

    private function clamp(float $v): float
    {
        return max(0.0, min(100.0, $v));
    }
}