<?php
// database/seeders/AmusementParkSeeder.php
// Parc d'Attractions Mefou Land (Yaoundé). Tenant : parc@qrmap.test

namespace Database\Seeders;

use App\Models\Destination;
use App\Models\Site;
use App\Services\QrCodeService;
use Database\Seeders\Concerns\SeedsTenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class AmusementParkSeeder extends Seeder
{
    use SeedsTenant;

    public function run(): void
    {
        $this->asTenant('parc@qrmap.test', 'Parc Mefou Land', function () {
            $this->seedData();
        });
    }

    private function seedData(): void
    {
        Storage::disk('public')->makeDirectory('qrcodes');
        $service = app(QrCodeService::class);

        $site = Site::create([
            'name'        => 'Parc d\'Attractions Mefou Land',
            'address'     => 'Route de Mfou, Yaoundé, Cameroun',
            'latitude'    => 3.8019,
            'longitude'   => 11.6342,
            'description' => 'Parc d\'attractions familial — manèges, grande roue, espace aquatique et restauration.',
        ]);

        $building = $site->buildings()->create([
            'name'         => 'Parc Principal',
            'floors_count' => 1,
            'model_3d'     => ['width_m' => 120, 'depth_m' => 90, 'floor_height_m' => 5],
        ]);

        $parc = $building->floors()->create([
            'level' => 0,
            'label' => 'Plan du parc',
            'rooms' => [
                ['id'=>'PK01','name'=>'Entrée & Billetterie',     'x'=>2,  'y'=>2,  'w'=>20,'h'=>22],
                ['id'=>'PK02','name'=>'Boutique Souvenirs',       'x'=>24, 'y'=>2,  'w'=>16,'h'=>22],
                ['id'=>'PK03','name'=>'Grande Roue',              'x'=>42, 'y'=>2,  'w'=>26,'h'=>30],
                ['id'=>'PK04','name'=>'Montagnes Russes',         'x'=>70, 'y'=>2,  'w'=>28,'h'=>40],
                ['id'=>'PK05','name'=>'Allée centrale',           'x'=>2,  'y'=>26, 'w'=>96,'h'=>10],
                ['id'=>'PK06','name'=>'Manège Enfants',           'x'=>2,  'y'=>38, 'w'=>22,'h'=>26],
                ['id'=>'PK07','name'=>'Carrousel',                'x'=>26, 'y'=>38, 'w'=>22,'h'=>26],
                ['id'=>'PK08','name'=>'Maison Hantée',            'x'=>50, 'y'=>34, 'w'=>22,'h'=>30],
                ['id'=>'PK09','name'=>'Espace Aquatique',         'x'=>74, 'y'=>44, 'w'=>24,'h'=>28],
                ['id'=>'PK10','name'=>'Allée secondaire',         'x'=>2,  'y'=>66, 'w'=>96,'h'=>8],
                ['id'=>'PK11','name'=>'Restauration — Snacks',    'x'=>2,  'y'=>76, 'w'=>26,'h'=>22],
                ['id'=>'PK12','name'=>'Aire de pique-nique',      'x'=>30, 'y'=>76, 'w'=>26,'h'=>22],
                ['id'=>'PK13','name'=>'Premiers secours',         'x'=>58, 'y'=>76, 'w'=>16,'h'=>22],
                ['id'=>'PK14','name'=>'Toilettes',                'x'=>76, 'y'=>76, 'w'=>10,'h'=>22],
                ['id'=>'PK15','name'=>'Sortie',                   'x'=>88, 'y'=>76, 'w'=>10,'h'=>22],
            ],
        ]);

        $destData = [
            [$parc, 'PK01', 'Entrée & Billetterie',     'admin',   'Achat des billets et plans du parc',          1],
            [$parc, 'PK02', 'Boutique Souvenirs',       'shop',    'Peluches, casquettes, souvenirs',             2],
            [$parc, 'PK03', 'Grande Roue',              'service', 'Attraction familiale — 15m de hauteur',       3],
            [$parc, 'PK04', 'Montagnes Russes',         'service', 'Sensations fortes — taille min. 1m40',        4],
            [$parc, 'PK06', 'Manège Enfants',           'service', 'Pour les 2-8 ans',                            5],
            [$parc, 'PK07', 'Carrousel',                'service', 'Manège classique pour toute la famille',      6],
            [$parc, 'PK08', 'Maison Hantée',            'service', 'Déconseillé aux moins de 10 ans',             7],
            [$parc, 'PK09', 'Espace Aquatique',         'service', 'Piscines et toboggans — serviette requise',   8],
            [$parc, 'PK11', 'Restauration — Snacks',    'service', 'Sandwichs, glaces et boissons',               9],
            [$parc, 'PK12', 'Aire de pique-nique',      'service', 'Tables ombragées — accès libre',             10],
            [$parc, 'PK13', 'Premiers secours',         'medical', 'Poste de secours — personnel sur place',     11],
            [$parc, 'PK14', 'Toilettes',                'service', 'Toilettes publiques',                        12],
            [$parc, 'PK15', 'Sortie',                   'exit',    'Vers le parking visiteurs',                  13],
        ];

        foreach ($destData as [$floor, $roomId, $label, $cat, $desc, $order]) {
            $floor->destinations()->create([
                'room_id'=>$roomId,'label'=>$label,'category'=>$cat,
                'description'=>$desc,'sort_order'=>$order,'is_active'=>true,
            ]);
        }

        $qrPlacements = [
            ['floor' => $parc, 'label' => 'Entrée principale — Billetterie', 'pos_x' => 12, 'pos_y' => 13],
            ['floor' => $parc, 'label' => 'Allée centrale — Carrefour Grande Roue', 'pos_x' => 50, 'pos_y' => 31],
            ['floor' => $parc, 'label' => 'Près de la Maison Hantée',        'pos_x' => 61, 'pos_y' => 49],
            ['floor' => $parc, 'label' => 'Allée secondaire — Restauration', 'pos_x' => 30, 'pos_y' => 70],
            ['floor' => $parc, 'label' => 'Sortie — Parking visiteurs',      'pos_x' => 92, 'pos_y' => 87],
        ];

        $generatedQrs = [];
        foreach ($qrPlacements as $p) {
            $result = $service->generate([
                'site_id'        => $site->id,
                'building_id'    => $building->id,
                'floor_id'       => $p['floor']->id,
                'location_label' => $p['label'],
                'pos_x'          => $p['pos_x'],
                'pos_y'          => $p['pos_y'],
            ]);
            $generatedQrs[] = $result['qr_code'];
        }

        $this->command->info('  ✓ Site : ' . $site->name . ' (tenant: parc@qrmap.test)');
        $this->command->info('  ✓ ' . count($generatedQrs) . ' QR codes générés.');
    }
}