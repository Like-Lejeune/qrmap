<?php
// app/Models/NavigationPath.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NavigationPath extends Model
{
    protected $fillable = [
        'qr_code_id', 'destination_id', 'waypoints', 'smooth', 'segments',
    ];

    protected $casts = [
        'waypoints' => 'array',
        'smooth'    => 'boolean',
        'segments'  => 'array',
    ];

    public function qrCode(): BelongsTo { return $this->belongsTo(QrCode::class); }
    public function destination(): BelongsTo { return $this->belongsTo(Destination::class); }

    /**
     * Renvoie les tronçons enregistrés sous forme normalisée :
     *  - si 'segments' existe (multi-étage) → tel quel
     *  - sinon (mono-étage legacy) → un unique tronçon depuis waypoints/smooth
     * Renvoie null si rien n'est enregistré d'exploitable.
     */
    public function segmentsData(): ?array
    {
        if (is_array($this->segments) && count($this->segments)) {
            return $this->segments;
        }
        if (is_array($this->waypoints) && count($this->waypoints)) {
            return [[
                'role'      => 'direct',
                'smooth'    => (bool) $this->smooth,
                'waypoints' => $this->waypoints,
            ]];
        }
        return null;
    }
}