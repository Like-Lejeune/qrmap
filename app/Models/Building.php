<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Building.php
class Building extends Model {
    protected $fillable = ['site_id', 'name', 'floors_count', 'model_3d', 'floor_plan_2d'];
    protected $casts = ['model_3d' => 'array', 'floor_plan_2d' => 'array'];

    public function site()   { return $this->belongsTo(Site::class); }
    public function floors() { return $this->hasMany(Floor::class); }
}
