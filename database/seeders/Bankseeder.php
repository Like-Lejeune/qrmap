<?php
// database/seeders/BankSeeder.php
// Banque Atlantique — Agence Bonanjo (Douala). Tenant : banque@qrmap.test

namespace Database\Seeders;

use App\Models\Destination;
use App\Models\Site;
use App\Services\QrCodeService;
use Database\Seeders\Concerns\SeedsTenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class BankSeeder extends Seeder
{
    use SeedsTenant;

    public function run(): void
    {
        $this->asTenant('banque@qrmap.test', 'Banque Atlantique Bonanjo', function () {
            $this->seedData();
        });
    }

    private function seedData(): void
    {
        Storage::disk('public')->makeDirectory('qrcodes');
        $service = app(QrCodeService::class);

        $site = Site::create([
            'name'        => 'Banque Atlantique — Agence Bonanjo',
            'address'     => 'Avenue Charles de Gaulle, Bonanjo, Douala, Cameroun',
            'latitude'    => 4.0469,
            'longitude'   => 9.6958,
            'description' => 'Agence bancaire principale — guichets, conseil clientèle, coffres et espace entreprises.',
        ]);

        $building = $site->buildings()->create([
            'name'         => 'Agence Bonanjo',
            'floors_count' => 2,
            'model_3d'     => ['width_m' => 32, 'depth_m' => 20, 'floor_height_m' => 3.2],
        ]);

        $rdc = $building->floors()->create([
            'level' => 0,
            'label' => 'Rez-de-chaussée',
            'rooms' => [
                ['id'=>'RDC01','name'=>'Accueil',                'x'=>2,  'y'=>2,  'w'=>24,'h'=>40],
                ['id'=>'RDC02','name'=>'Salle d\'attente',       'x'=>28, 'y'=>2,  'w'=>26,'h'=>40],
                ['id'=>'RDC03','name'=>'Guichets',               'x'=>56, 'y'=>2,  'w'=>26,'h'=>40],
                ['id'=>'RDC04','name'=>'Distributeurs (DAB)',    'x'=>84, 'y'=>2,  'w'=>14,'h'=>40],
                ['id'=>'RDC05','name'=>'Couloir',                'x'=>2,  'y'=>44, 'w'=>96,'h'=>10],
                ['id'=>'RDC06','name'=>'Conseil Particuliers',   'x'=>2,  'y'=>56, 'w'=>30,'h'=>34],
                ['id'=>'RDC07','name'=>'Conseil Entreprises',    'x'=>34, 'y'=>56, 'w'=>30,'h'=>34],
                ['id'=>'RDC08','name'=>'Bureau du Directeur',    'x'=>66, 'y'=>56, 'w'=>20,'h'=>34],
                ['id'=>'RDC09','name'=>'Sortie',                 'x'=>88, 'y'=>56, 'w'=>10,'h'=>34],
            ],
        ]);

        $etage1 = $building->floors()->create([
            'level' => 1,
            'label' => '1er étage',
            'rooms' => [
                ['id'=>'E101','name'=>'Salle des coffres-forts', 'x'=>2,  'y'=>2,  'w'=>30,'h'=>45],
                ['id'=>'E102','name'=>'Bureau de change',        'x'=>34, 'y'=>2,  'w'=>28,'h'=>45],
                ['id'=>'E103','name'=>'Salle de réunion',        'x'=>64, 'y'=>2,  'w'=>34,'h'=>45],
                ['id'=>'E104','name'=>'Couloir',                 'x'=>2,  'y'=>49, 'w'=>96,'h'=>10],
                ['id'=>'E105','name'=>'Bureaux Crédit',          'x'=>2,  'y'=>61, 'w'=>30,'h'=>30],
                ['id'=>'E106','name'=>'Service informatique',    'x'=>34, 'y'=>61, 'w'=>28,'h'=>30],
                ['id'=>'E107','name'=>'Salle de repos',          'x'=>64, 'y'=>61, 'w'=>34,'h'=>30],
            ],
        ]);

        $destData = [
            [$rdc,    'RDC01', 'Accueil',                  'admin',   'Orientation et prise de ticket',          1],
            [$rdc,    'RDC03', 'Guichets',                  'service', 'Dépôts, retraits, opérations courantes',  2],
            [$rdc,    'RDC04', 'Distributeurs (DAB)',       'service', 'Retraits 24h/24',                          3],
            [$rdc,    'RDC06', 'Conseil Particuliers',      'service', 'Comptes, épargne, crédits — sur RDV',     4],
            [$rdc,    'RDC07', 'Conseil Entreprises',       'service', 'Comptes pro et financements — sur RDV',   5],
            [$rdc,    'RDC08', 'Bureau du Directeur',       'admin',   'Sur rendez-vous uniquement',               6],
            [$rdc,    'RDC09', 'Sortie',                    'exit',    'Vers la rue et le parking',                7],
            [$etage1, 'E101',  'Salle des coffres-forts',   'service', '1er étage — Accès sécurisé',              8],
            [$etage1, 'E102',  'Bureau de change',          'service', '1er étage — Devises étrangères',          9],
            [$etage1, 'E103',  'Salle de réunion',          'service', '1er étage — Sur réservation',            10],
        ];

        foreach ($destData as [$floor, $roomId, $label, $cat, $desc, $order]) {
            $floor->destinations()->create([
                'room_id'=>$roomId,'label'=>$label,'category'=>$cat,
                'description'=>$desc,'sort_order'=>$order,'is_active'=>true,
            ]);
        }

        $qrPlacements = [
            ['floor' => $rdc,    'label' => 'Entrée principale — Accueil', 'pos_x' => 14, 'pos_y' => 22],
            ['floor' => $rdc,    'label' => 'Couloir RDC — Conseil clientèle', 'pos_x' => 50, 'pos_y' => 49],
            ['floor' => $rdc,    'label' => 'Espace DAB',                  'pos_x' => 91, 'pos_y' => 22],
            ['floor' => $etage1, 'label' => 'Palier — 1er étage',          'pos_x' => 50, 'pos_y' => 54],
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

        $this->command->info('  ✓ Site : ' . $site->name . ' (tenant: banque@qrmap.test)');
        $this->command->info('  ✓ ' . count($generatedQrs) . ' QR codes générés.');
    }
}