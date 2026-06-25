<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Floor.php
class Floor extends Model {
    protected $fillable = ['building_id', 'level', 'label', 'plan_2d', 'rooms'];
    protected $casts = ['plan_2d' => 'array', 'rooms' => 'array'];

    public function building() { return $this->belongsTo(Building::class); }
    public function destinations() {return $this->hasMany(Destination::class);
}
}