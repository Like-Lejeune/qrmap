<?php
// app/Models/Destination.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Destination extends Model
{
    protected $fillable = [
        'floor_id', 'room_id', 'label',
        'category', 'description', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    // Icônes par catégorie (utilisées dans la vue)
    public const CATEGORY_ICONS = [
        'medical' => '🏥',
        'admin'   => '🏛',
        'service' => '☕',
        'exit'    => '🚪',
        'other'   => '📍',
    ];

    public function floor(): BelongsTo
    {
        return $this->belongsTo(Floor::class);
    }

    // Accesseur pratique pour l'icône
    public function getIconAttribute(): string
    {
        return self::CATEGORY_ICONS[$this->category] ?? '📍';
    }

    // Retourne la pièce correspondante depuis le JSON rooms du Floor
    public function getRoom(): ?array
    {
        $rooms = $this->floor->rooms ?? [];
        foreach ($rooms as $room) {
            if (($room['id'] ?? null) === $this->room_id) {
                return $room;
            }
        }
        return null;
    }

    // Scope pour les destinations actives triées
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('label');
    }
}