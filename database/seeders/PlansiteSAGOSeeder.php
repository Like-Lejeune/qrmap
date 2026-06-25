<?php
// database/seeders/PlansiteSAGOSeeder.php
// Plan site SAGO. Tenant : sago@qrmap.test

namespace Database\Seeders;

use App\Models\Destination;
use App\Models\Site;
use App\Services\QrCodeService;
use Database\Seeders\Concerns\SeedsTenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class PlansiteSAGOSeeder extends Seeder
{
    use SeedsTenant;

    public function run(): void
    {
        $this->asTenant('sago@qrmap.test', 'SAGO', function () {
            $this->seedData();
        });
    }

    private function seedData(): void
    {
        Storage::disk('public')->makeDirectory('qrcodes');
        $service = app(QrCodeService::class);

        $site = Site::create([
            'name'        => 'Plan site SAGO',
            'address'     => 'Esplanade de l\'hotel de ville',
            'latitude'    => 3.11555,
            'longitude'   => 115.225,
            'description' => '',
        ]);

        $building = $site->buildings()->create([
            'name'         => 'Esplanade',
            'floors_count' => 1,
            'model_3d'     => ['width_m' => 40, 'depth_m' => 25, 'floor_height_m' => 3.3],
        ]);

        $floor0 = $building->floors()->create([
            'level' => 0,
            'label' => 'Plan',
            'rooms' => [
                ['id'=>'F0R1','name'=>'MINPOSTEL','x'=>2.9,'y'=>4,'w'=>26,'h'=>12],
                ['id'=>'F0ASC','name'=>'Escalier','x'=>82,'y'=>2,'w'=>12,'h'=>40],
                ['id'=>'F0R4','name'=>'couloir','x'=>3,'y'=>19,'w'=>93,'h'=>7],
                ['id'=>'F0R5','name'=>'Couloir','x'=>89.5,'y'=>30.5,'w'=>5,'h'=>46],
                ['id'=>'F0R6','name'=>'salle de conference','x'=>2,'y'=>29,'w'=>38,'h'=>24],
                ['id'=>'F0R7','name'=>'Salle de concert','x'=>42,'y'=>30,'w'=>47,'h'=>23],
                ['id'=>'F0R8','name'=>'Nouvelle pièce','x'=>42,'y'=>55,'w'=>47,'h'=>42],
                ['id'=>'F0R9','name'=>'Nouvelle pièce','x'=>95,'y'=>76.5,'w'=>3.5,'h'=>18.6],
            ],
        ]);

        $destData = [
            [$floor0, 'F0R1', 'MINPOSTEL', 'service', '', 1],
            [$floor0, 'F0ASC', 'Escalier', 'service', '', 2],
            [$floor0, 'F0R6', 'salle de conference', 'service', '', 3],
            [$floor0, 'F0R7', 'Salle de concert', 'service', '', 4],
            [$floor0, 'F0R8', 'Nouvelle pièce', 'service', '', 5],
        ];

        foreach ($destData as [$floor, $roomId, $label, $cat, $desc, $order]) {
            $floor->destinations()->create([
                'room_id'=>$roomId,'label'=>$label,'category'=>$cat,
                'description'=>$desc,'sort_order'=>$order,'is_active'=>true,
            ]);
        }

        $qrPlacements = [
            ['floor'=>$floor0, 'label'=>'Nouveau QR', 'pos_x'=>5, 'pos_y'=>23],
            ['floor'=>$floor0, 'label'=>'Nouveau QR', 'pos_x'=>93, 'pos_y'=>90],
        ];

        foreach ($qrPlacements as $p) {
            $service->generate([
                'site_id'=>$site->id,'building_id'=>$building->id,'floor_id'=>$p['floor']->id,
                'location_label'=>$p['label'],'pos_x'=>$p['pos_x'],'pos_y'=>$p['pos_y'],
            ]);
        }

        $this->command->info('  ✓ Plan site SAGO (tenant: sago@qrmap.test) — '.$building->floors()->count().' étage(s).');
    }
}