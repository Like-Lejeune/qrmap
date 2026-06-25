<?php
// app/Models/QrCode.php

namespace App\Models;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class QrCode extends Model
{
    use HasTenant;

    protected $fillable = [
        'user_id', 'uuid', 'site_id', 'building_id', 'floor_id',
        'location_label', 'pos_x', 'pos_y', 'scan_count', 'last_scanned_at',
    ];

    protected $casts = [
        'last_scanned_at' => 'datetime',
    ];

    public function site()     { return $this->belongsTo(Site::class); }
    public function building() { return $this->belongsTo(Building::class); }
    public function floor()    { return $this->belongsTo(Floor::class); }
    public function user()     { return $this->belongsTo(User::class); }

    public function getHasImageAttribute(): bool
    {
        return Storage::disk('public')->exists('qrcodes/' . $this->uuid . '.svg');
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->has_image
            ? asset('storage/qrcodes/' . $this->uuid . '.svg')
            : null;
    }
}