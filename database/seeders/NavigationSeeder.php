<?php
// database/seeders/NavigationSeeder.php
//
// Cas pratique : Hôpital Central de Yaoundé
// Multi-tenant : rattaché au compte « hopital@qrmap.test ».
// Lance avec : php artisan db:seed --class=NavigationSeeder

namespace Database\Seeders;

use App\Models\Building;
use App\Models\Destination;
use App\Models\Floor;
use App\Models\QrCode;
use App\Models\Site;
use App\Services\QrCodeService;
use Database\Seeders\Concerns\SeedsTenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class NavigationSeeder extends Seeder
{
    use SeedsTenant;

    public function run(): void
    {
        $this->asTenant('hopital@qrmap.test', 'Hôpital Central de Yaoundé', function () {
            $this->seedData();
        });
    }

    private function seedData(): void
    {
        Storage::disk('public')->makeDirectory('qrcodes');
        $service = app(QrCodeService::class);

        $site = Site::create([
            'name'        => 'Hôpital Central de Yaoundé',
            'address'     => 'Rue Henri Dunant, Yaoundé, Cameroun',
            'latitude'    => 3.8674,
            'longitude'   => 11.5171,
            'description' => 'Centre hospitalier de référence. Urgences 24h/24. Accueil ~1 200 patients/jour.',
        ]);

        $building = $site->buildings()->create([
            'name'         => 'Bloc Principal',
            'floors_count' => 3,
            'model_3d'     => ['width_m' => 55, 'depth_m' => 35, 'floor_height_m' => 3.5],
        ]);

        $ascenseur = ['id' => 'ASC', 'name' => 'Ascenseurs', 'x' => 44, 'y' => 46, 'w' => 12, 'h' => 12];

        $rdc = $building->floors()->create([
            'level' => 0,
            'label' => 'Rez-de-chaussée',
            'rooms' => [
                ['id'=>'RDC01','name'=>'Accueil',           'x'=>2,  'y'=>2,  'w'=>22,'h'=>40],
                ['id'=>'RDC02','name'=>'Urgences',          'x'=>27, 'y'=>2,  'w'=>26,'h'=>40],
                ['id'=>'RDC03','name'=>'Bureau admission',   'x'=>56, 'y'=>2,  'w'=>20,'h'=>40],
                ['id'=>'RDC04','name'=>'Salle d\'attente',  'x'=>79, 'y'=>2,  'w'=>19,'h'=>40],
                ['id'=>'RDC05','name'=>'Couloir principal',  'x'=>2,  'y'=>46, 'w'=>96,'h'=>12],
                $ascenseur,
                ['id'=>'RDC06','name'=>'Pharmacie',         'x'=>2,  'y'=>62, 'w'=>28,'h'=>34],
                ['id'=>'RDC07','name'=>'Radiologie',        'x'=>34, 'y'=>62, 'w'=>28,'h'=>34],
                ['id'=>'RDC08','name'=>'Laboratoire RDC',   'x'=>66, 'y'=>62, 'w'=>18,'h'=>34],
                ['id'=>'RDC09','name'=>'Sortie principale', 'x'=>87, 'y'=>62, 'w'=>11,'h'=>34],
            ],
        ]);

        $etage1 = $building->floors()->create([
            'level' => 1,
            'label' => '1er étage',
            'rooms' => [
                ['id'=>'E101','name'=>'Cardiologie',       'x'=>2,  'y'=>2,  'w'=>30,'h'=>42],
                ['id'=>'E102','name'=>'Pédiatrie',         'x'=>35, 'y'=>2,  'w'=>28,'h'=>42],
                ['id'=>'E103','name'=>'Bloc opératoire',   'x'=>66, 'y'=>2,  'w'=>32,'h'=>42],
                ['id'=>'E104','name'=>'Couloir',           'x'=>2,  'y'=>48, 'w'=>96,'h'=>12],
                $ascenseur,
                ['id'=>'E105','name'=>'Soins intensifs',   'x'=>2,  'y'=>64, 'w'=>40,'h'=>32],
                ['id'=>'E106','name'=>'Poste infirmier',   'x'=>60, 'y'=>64, 'w'=>13,'h'=>32],
                ['id'=>'E107','name'=>'Salle de réveil',   'x'=>76, 'y'=>64, 'w'=>22,'h'=>32],
            ],
        ]);

        $etage2 = $building->floors()->create([
            'level' => 2,
            'label' => '2ème étage',
            'rooms' => [
                ['id'=>'E201','name'=>'Consultations A',   'x'=>2,  'y'=>2,  'w'=>28,'h'=>44],
                ['id'=>'E202','name'=>'Consultations B',   'x'=>33, 'y'=>2,  'w'=>28,'h'=>44],
                ['id'=>'E203','name'=>'Direction médicale','x'=>64, 'y'=>2,  'w'=>34,'h'=>44],
                ['id'=>'E204','name'=>'Couloir',           'x'=>2,  'y'=>50, 'w'=>96,'h'=>12],
                $ascenseur,
                ['id'=>'E205','name'=>'Salle conférence',  'x'=>2,  'y'=>66, 'w'=>40,'h'=>30],
                ['id'=>'E206','name'=>'Archives médicales','x'=>60, 'y'=>66, 'w'=>16,'h'=>30],
                ['id'=>'E207','name'=>'Secrétariat',       'x'=>80, 'y'=>66, 'w'=>18,'h'=>30],
            ],
        ]);

        $destData = [
            [$rdc,    'RDC01', 'Accueil',              'admin',   'Orientation & renseignements',    1],
            [$rdc,    'RDC02', 'Urgences',             'medical', 'Ouvert 24h/24 — Priorité rouge',  2],
            [$rdc,    'RDC06', 'Pharmacie',            'medical', 'Délivrance ordonnances',          3],
            [$rdc,    'RDC07', 'Radiologie',           'medical', 'Rx, Scanner, IRM — sur RDV',      4],
            [$rdc,    'RDC08', 'Laboratoire',          'medical', 'Analyses biologiques',            5],
            [$rdc,    'RDC09', 'Sortie principale',    'exit',    'Vers le parking et la rue',       6],
            [$rdc,    'RDC03', 'Bureau admission',     'admin',   'Formalités administratives',      7],
            [$etage1, 'E101',  'Cardiologie',          'medical', '1er étage — Pr. Mbarga',          8],
            [$etage1, 'E102',  'Pédiatrie',            'medical', '1er étage — Dr. Nkoa',            9],
            [$etage1, 'E103',  'Bloc opératoire',      'medical', 'Accès restreint — personnel auth.', 10],
            [$etage1, 'E105',  'Soins intensifs',      'medical', 'Accès famille sur autorisation',  11],
            [$etage2, 'E201',  'Consultations A',      'medical', '2ème étage — Généralistes',       12],
            [$etage2, 'E202',  'Consultations B',      'medical', '2ème étage — Spécialistes',       13],
            [$etage2, 'E203',  'Direction médicale',   'admin',   '2ème étage — Sur RDV uniquement', 14],
            [$etage2, 'E205',  'Salle de conférence',  'service', '2ème étage — Réservation requise',15],
        ];

        foreach ($destData as [$floor, $roomId, $label, $cat, $desc, $order]) {
            $floor->destinations()->create([
                'room_id'=>$roomId,'label'=>$label,'category'=>$cat,
                'description'=>$desc,'sort_order'=>$order,'is_active'=>true,
            ]);
        }

        $qrPlacements = [
            ['floor' => $rdc,    'label' => 'Entrée principale — Accueil RDC', 'pos_x' => 13, 'pos_y' => 22],
            ['floor' => $rdc,    'label' => 'Couloir RDC — Zone centrale',     'pos_x' => 50, 'pos_y' => 52],
            ['floor' => $rdc,    'label' => 'Entrée Pharmacie',                'pos_x' => 16, 'pos_y' => 79],
            ['floor' => $rdc,    'label' => 'Entrée Urgences',                 'pos_x' => 40, 'pos_y' => 22],
            ['floor' => $etage1, 'label' => 'Couloir 1er étage — Cardiologie', 'pos_x' => 17, 'pos_y' => 54],
            ['floor' => $etage1, 'label' => 'Accès Bloc opératoire',           'pos_x' => 82, 'pos_y' => 22],
            ['floor' => $etage2, 'label' => 'Couloir 2ème étage — Consultations', 'pos_x' => 18, 'pos_y' => 56],
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

        $this->command->info('  ✓ Site : ' . $site->name . ' (tenant: hopital@qrmap.test)');
        $this->command->info('  ✓ ' . count($generatedQrs) . ' QR codes générés.');
        foreach ($generatedQrs as $qr) {
            $this->command->line('  → /scan/' . $qr->uuid . '  (' . $qr->location_label . ')');
        }
    }
}