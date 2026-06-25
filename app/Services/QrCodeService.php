<?php

// app/Services/QrCodeService.php

namespace App\Services;

use App\Models\QrCode;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode as QrGenerator;

class QrCodeService
{
    public function generate(array $data): array
    {
        $uuid = Str::uuid()->toString();
        $url  = route('scan.show', $uuid);

        // SVG — aucune extension PHP requise (ni imagick, ni gd)
        $qrSvg = QrGenerator::format('svg')
            ->size(400)
            ->margin(2)
            ->errorCorrection('H')
            ->generate($url);

        Storage::disk('public')->put("qrcodes/{$uuid}.svg", $qrSvg);

        $qrCode = QrCode::create([
            'uuid'           => $uuid,
            'site_id'        => $data['site_id']     ?? null,
            'building_id'    => $data['building_id'] ?? null,
            'floor_id'       => $data['floor_id']    ?? null,
            'location_label' => $data['location_label'],
            'pos_x'          => $data['pos_x'] ?? 50,
            'pos_y'          => $data['pos_y'] ?? 50,
        ]);

        return ['uuid' => $uuid, 'scan_url' => $url, 'qr_code' => $qrCode];
    }
}