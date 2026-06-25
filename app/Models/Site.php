<?php
// app/Models/Site.php

namespace App\Models;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    use HasTenant;

    protected $fillable = [
        'user_id', 'name', 'address', 'latitude', 'longitude', 'description',
    ];

    public function buildings() { return $this->hasMany(Building::class); }
    public function qrCodes()   { return $this->hasMany(QrCode::class); }
    public function user()      { return $this->belongsTo(User::class); }
}