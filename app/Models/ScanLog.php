<?php
// =============================================
// app/Models/ScanLog.php
// =============================================
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScanLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'qr_code_id', 'scanned_at', 'user_agent', 'ip_address', 'device_type',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
    ];

    public function qrCode()
    {
        return $this->belongsTo(QrCode::class);
    }

    public static function record(QrCode $qr, \Illuminate\Http\Request $request): void
    {
        $ua = $request->userAgent() ?? '';
        $device = 'desktop';
        if (preg_match('/(android|iphone|ipad|mobile)/i', $ua)) $device = 'mobile';
        elseif (preg_match('/tablet/i', $ua)) $device = 'tablet';

        static::create([
            'qr_code_id'  => $qr->id,
            'scanned_at'  => now(),
            'user_agent'  => substr($ua, 0, 255),
            'ip_address'  => $request->ip(),
            'device_type' => $device,
        ]);
    }
}
