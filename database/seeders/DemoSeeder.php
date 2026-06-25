<?php
// =============================================
// database/seeders/DemoSeeder.php
// =============================================
namespace Database\Seeders;

use App\Models\Building;
use App\Models\Floor;
use App\Models\QrCode;
use App\Models\ScanLog;
use App\Models\Site;
use App\Services\QrCodeService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        Storage::disk('public')->makeDirectory('qrcodes');

        // ======= SITE 1 : Campus Universitaire =======
        $campus = Site::create([
            'name'        => 'Campus Universitaire',
            'address'     => 'Avenue de l\'Université, Yaoundé, Cameroun',
            'latitude'    => 3.8667,
            'longitude'   => 11.5167,
            'description' => 'Campus principal avec 3 bâtiments principaux.',
        ]);

        $batA = $campus->buildings()->create([
            'name'         => 'Bâtiment A — Administration',
            'floors_count' => 3,
            'model_3d'     => ['width_m' => 45, 'depth_m' => 28, 'floor_height_m' => 3.5],
        ]);

        $batA->floors()->createMany([
            [
                'level' => 0, 'label' => 'Rez-de-chaussée',
                'rooms' => [
                    ['id'=>'A01','name'=>'Accueil',        'x'=>3,  'y'=>3,  'w'=>22,'h'=>45],
                    ['id'=>'A02','name'=>'Salle de réunion','x'=>28, 'y'=>3,  'w'=>30,'h'=>30],
                    ['id'=>'A03','name'=>'Cafétéria',      'x'=>62, 'y'=>3,  'w'=>35,'h'=>45],
                    ['id'=>'A04','name'=>'Couloir',        'x'=>3,  'y'=>52, 'w'=>94,'h'=>12],
                    ['id'=>'A05','name'=>'Bureaux RH',     'x'=>3,  'y'=>68, 'w'=>45,'h'=>28],
                    ['id'=>'A06','name'=>'Archives',       'x'=>52, 'y'=>68, 'w'=>45,'h'=>28],
                ],
            ],
            [
                'level' => 1, 'label' => '1er étage',
                'rooms' => [
                    ['id'=>'B01','name'=>'Salle A1',  'x'=>3,  'y'=>3,  'w'=>30,'h'=>45],
                    ['id'=>'B02','name'=>'Salle A2',  'x'=>36, 'y'=>3,  'w'=>30,'h'=>45],
                    ['id'=>'B03','name'=>'Salle A3',  'x'=>69, 'y'=>3,  'w'=>28,'h'=>45],
                    ['id'=>'B04','name'=>'Couloir',   'x'=>3,  'y'=>52, 'w'=>94,'h'=>12],
                    ['id'=>'B05','name'=>'Bureau Doyen','x'=>3,'y'=>68, 'w'=>45,'h'=>28],
                    ['id'=>'B06','name'=>'Secrétariat','x'=>52,'y'=>68, 'w'=>45,'h'=>28],
                ],
            ],
            [
                'level' => 2, 'label' => '2e étage',
                'rooms' => [
                    ['id'=>'C01','name'=>'Amphi 200',  'x'=>3,  'y'=>3,  'w'=>60,'h'=>55],
                    ['id'=>'C02','name'=>'Labo Info',  'x'=>67, 'y'=>3,  'w'=>30,'h'=>55],
                    ['id'=>'C03','name'=>'Terrasse',   'x'=>3,  'y'=>62, 'w'=>94,'h'=>35],
                ],
            ],
        ]);

        $batB = $campus->buildings()->create([
            'name'         => 'Bâtiment B — Sciences',
            'floors_count' => 2,
            'model_3d'     => ['width_m' => 35, 'depth_m' => 22, 'floor_height_m' => 4],
        ]);

        $floorB0 = $batB->floors()->create([
            'level' => 0, 'label' => 'Rez-de-chaussée',
            'rooms' => [
                ['id'=>'D01','name'=>'Labo Chimie',    'x'=>3,'y'=>3,'w'=>45,'h'=>48],
                ['id'=>'D02','name'=>'Labo Physique',  'x'=>52,'y'=>3,'w'=>45,'h'=>48],
                ['id'=>'D03','name'=>'Couloir',        'x'=>3,'y'=>55,'w'=>94,'h'=>10],
                ['id'=>'D04','name'=>'Amphithéâtre',   'x'=>3,'y'=>69,'w'=>94,'h'=>28],
            ],
        ]);

        // ======= SITE 2 : Hôpital Central =======
        $hopital = Site::create([
            'name'        => 'Hôpital Central',
            'address'     => 'Rue du Dispensaire, Douala, Cameroun',
            'latitude'    => 4.0511,
            'longitude'   => 9.7679,
            'description' => 'Centre hospitalier avec urgences, consultations et chirurgie.',
        ]);

        $urgences = $hopital->buildings()->create([
            'name'         => 'Bloc Urgences',
            'floors_count' => 2,
            'model_3d'     => ['width_m' => 50, 'depth_m' => 30, 'floor_height_m' => 3.2],
        ]);

        $floorU0 = $urgences->floors()->create([
            'level' => 0, 'label' => 'Rez-de-chaussée',
            'rooms' => [
                ['id'=>'U01','name'=>'Triage',          'x'=>3,'y'=>3,'w'=>25,'h'=>45],
                ['id'=>'U02','name'=>'Salles d\'attente','x'=>32,'y'=>3,'w'=>30,'h'=>45],
                ['id'=>'U03','name'=>'Urgences',        'x'=>66,'y'=>3,'w'=>31,'h'=>45],
                ['id'=>'U04','name'=>'Couloir',         'x'=>3,'y'=>52,'w'=>94,'h'=>10],
                ['id'=>'U05','name'=>'Pharmacie',       'x'=>3,'y'=>66,'w'=>40,'h'=>30],
                ['id'=>'U06','name'=>'Radiologie',      'x'=>47,'y'=>66,'w'=>50,'h'=>30],
            ],
        ]);

        // ======= GÉNÉRATION QR CODES =======
        $service = app(QrCodeService::class);
        $qrs = [];

        $placements = [
            [$campus, $batA, $batA->floors->where('level', 0)->first(),  'Entrée principale',     5,  50],
            [$campus, $batA, $batA->floors->where('level', 0)->first(),  'Cafétéria',             79, 25],
            [$campus, $batA, $batA->floors->where('level', 1)->first(),  'Couloir 1er étage',     50, 58],
            [$campus, $batA, $batA->floors->where('level', 2)->first(),  'Entrée Amphithéâtre',   34, 30],
            [$campus, $batB, $floorB0,                                    'Labo Chimie — entrée',  25, 25],
            [$campus, $batB, $floorB0,                                    'Entrée principale B',   3,  50],
            [$hopital,$urgences,$floorU0,                                 'Accueil Urgences',      5,  50],
            [$hopital,$urgences,$floorU0,                                 'Entrée Pharmacie',      23, 81],
        ];

        foreach ($placements as [$site, $building, $floor, $label, $px, $py]) {
            $result = $service->generate([
                'site_id'        => $site->id,
                'building_id'    => $building->id,
                'floor_id'       => $floor->id,
                'location_label' => $label,
                'pos_x'          => $px,
                'pos_y'          => $py,
            ]);
            $qrs[] = $result['qr_code'];
        }

        // ======= HISTORIQUE DE SCANS SIMULÉ =======
        $devices = ['mobile', 'mobile', 'mobile', 'tablet', 'desktop'];
        foreach ($qrs as $qr) {
            $scanCount = rand(5, 80);
            $qr->update(['scan_count' => $scanCount]);

            for ($i = 0; $i < $scanCount; $i++) {
                $daysAgo = rand(0, 30);
                $device  = $devices[array_rand($devices)];
                ScanLog::create([
                    'qr_code_id'  => $qr->id,
                    'scanned_at'  => Carbon::now()->subDays($daysAgo)->subHours(rand(0, 23))->subMinutes(rand(0, 59)),
                    'user_agent'  => $device === 'mobile' ? 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0) AppleWebKit/605.1' : 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'ip_address'  => '192.168.' . rand(1, 10) . '.' . rand(1, 254),
                    'device_type' => $device,
                ]);
            }

            $qr->update(['last_scanned_at' => ScanLog::where('qr_code_id', $qr->id)->max('scanned_at')]);
        }

        $this->command->info('✓ Démo créée : 2 sites, 3 bâtiments, 5 étages, ' . count($qrs) . ' QR codes, ' . ScanLog::count() . ' scans.');
    }
}