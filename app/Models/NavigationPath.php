<?php
// app/Models/NavigationPath.php
//
// Modèle de tracé de navigation.
// Un enregistrement = un couple (qr_code_id, destination_id).
//
// Colonnes :
//   - waypoints : tracé du 1er tronçon (compat mono-étage) — array|null
//   - end_point : point d'arrivée précis (porte) du 1er tronçon {x,y}|null
//   - smooth    : lissage du 1er tronçon — bool
//   - segments  : tableau complet des tronçons (multi-étage) — array|null
//
// segmentsData() est la SOURCE DE VÉRITÉ lue par le contrôleur (compute()).
// Elle DOIT exposer end_point pour chaque tronçon, sinon le point d'arrivée
// déplacé par l'admin est perdu au recalcul.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NavigationPath extends Model
{
    protected $fillable = [
        'qr_code_id',
        'destination_id',
        'waypoints',
        'end_point',   // ← porte du 1er tronçon
        'smooth',
        'segments',
    ];

    protected $casts = [
        'waypoints' => 'array',
        'end_point' => 'array',   // {x: float, y: float} | null
        'smooth'    => 'boolean',
        'segments'  => 'array',
    ];

    public function qrCode(): BelongsTo
    {
        return $this->belongsTo(QrCode::class);
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }

    /**
     * Renvoie les tronçons enregistrés, dans un format homogène toujours
     * consommable par le contrôleur — que le trajet soit mono ou multi-étage.
     *
     * Chaque tronçon expose AU MINIMUM :
     *   ['floor_id', 'role', 'smooth', 'end_point', 'waypoints']
     *
     * end_point est la clé qui manquait : sans elle, la porte déplacée par
     * l'admin n'est jamais relue et l'A* repart vers le centre.
     *
     * @return array<int, array>
     */
    public function segmentsData(): array
    {
        // ── Cas multi-étage : la colonne segments fait foi ─────────────────
        if (is_array($this->segments) && count($this->segments) > 0) {
            return array_map(function ($s) {
                return [
                    'floor_id'  => $s['floor_id']  ?? null,
                    'role'      => $s['role']      ?? 'direct',
                    'smooth'    => (bool) ($s['smooth'] ?? false),
                    'end_point' => $s['end_point'] ?? null,   // ← propagé
                    'waypoints' => $s['waypoints'] ?? null,
                ];
            }, $this->segments);
        }

        // ── Cas mono-étage : reconstruit depuis les colonnes plates ────────
        return [[
            'floor_id'  => optional($this->qrCode)->floor_id,
            'role'      => 'direct',
            'smooth'    => (bool) $this->smooth,
            'end_point' => $this->end_point,   // ← porte racine propagée
            'waypoints' => $this->waypoints,
        ]];
    }

    /**
     * Le 1er tronçon a-t-il un point d'arrivée précis (porte) valide ?
     */
    public function hasEndPoint(): bool
    {
        $e = $this->end_point;

        return is_array($e)
            && isset($e['x'], $e['y'])
            && is_numeric($e['x'])
            && is_numeric($e['y']);
    }
}