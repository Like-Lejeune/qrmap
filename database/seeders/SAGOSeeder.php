<?php

namespace Database\Seeders;

use App\Models\Destination;
use App\Models\Site;
use App\Services\QrCodeService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class SAGOSeeder extends Seeder
{
    public function run(): void
    {
        Storage::disk('public')->makeDirectory('qrcodes');
        $service = app(QrCodeService::class);

        $site = Site::create([
            'name'        => 'SAGO',
            'address'     => 'Yaoundé Hotle de ville',
            'latitude'    => null,
            'longitude'   => null,
            'description' => '',
        ]);

        $building = $site->buildings()->create([
            'name'         => 'SAGO esplanade',
            'floors_count' => 1,
            'model_3d'     => ['width_m' => 40, 'depth_m' => 25, 'floor_height_m' => 3.3],
        ]);

        $floor0 = $building->floors()->create([
            'level' => 0,
            'label' => 'Rez-de-chaussée',
            'rooms' => [
                ['id'=>'F0R1','name'=>'Accueil','x'=>2,'y'=>1,'w'=>22,'h'=>23],
                ['id'=>'F0R2','name'=>'Salle de conférence','x'=>2,'y'=>40,'w'=>29,'h'=>59],
                ['id'=>'F0R3','name'=>'Salle de concert','x'=>33,'y'=>40,'w'=>21,'h'=>59],
                ['id'=>'F0R4','name'=>'Salle de fete','x'=>56,'y'=>40,'w'=>24,'h'=>56],
                ['id'=>'F0R5','name'=>'Allée','x'=>3,'y'=>26,'w'=>96,'h'=>12],
                ['id'=>'F0R6','name'=>'Salle de reunion','x'=>83,'y'=>41,'w'=>16,'h'=>56],
                ['id'=>'F0R7','name'=>'Chambres de séjour','x'=>25,'y'=>1,'w'=>74,'h'=>23],
                ['id'=>'F0ASC','name'=>'Ascenseurs','x'=>91,'y'=>28,'w'=>7,'h'=>9],
            ],
        ]);

        $destData = [
            [$floor0, 'F0R1', 'Accueil', 'service', '', 1],
            [$floor0, 'F0R2', 'Salle de conférence', 'service', '', 2],
            [$floor0, 'F0R3', 'Salle de concert', 'service', '', 3],
            [$floor0, 'F0R4', 'Salle de fete', 'service', '', 4],
            [$floor0, 'F0R6', 'Salle de reunion', 'service', '', 5],
            [$floor0, 'F0R7', 'Chambres de séjour', 'service', '', 6],
            [$floor0, 'F0ASC', 'Ascenseurs', 'service', 'Liaison etage', 7],
        ];

        foreach ($destData as [$floor, $roomId, $label, $cat, $desc, $order]) {
            $floor->destinations()->create([
                'room_id'=>$roomId,'label'=>$label,'category'=>$cat,'description'=>$desc,'sort_order'=>$order,'is_active'=>true,
            ]);
        }

        $qrPlacements = [
            ['floor'=>$floor0, 'label'=>'Accueil QR', 'pos_x'=>4, 'pos_y'=>5],
            ['floor'=>$floor0, 'label'=>'Ascenseur QR', 'pos_x'=>97, 'pos_y'=>39],
        ];

        foreach ($qrPlacements as $p) {
            $service->generate([
                'site_id'=>$site->id,'building_id'=>$building->id,'floor_id'=>$p['floor']->id,
                'location_label'=>$p['label'],'pos_x'=>$p['pos_x'],'pos_y'=>$p['pos_y'],
            ]);
        }

        $this->command->info('✓ SAGO — '.$building->floors()->count().' étages.');
    }
}
