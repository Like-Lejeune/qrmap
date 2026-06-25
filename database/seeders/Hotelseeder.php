<?php
// database/seeders/HotelSeeder.php
//
// Cas pratique : Hôtel Mont Cameroun (Douala)
// Site + Bâtiment principal (5 niveaux) + Destinations + QR codes placés
// Lance avec : php artisan db:seed --class=HotelSeeder

namespace Database\Seeders;

use App\Models\Destination;
use App\Services\QrCodeService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class HotelSeeder extends Seeder
{
    public function run(): void
    {
        Storage::disk('public')->makeDirectory('qrcodes');
        $service = app(QrCodeService::class);

        // ══════════════════════════════════════════════════════════════
        // SITE : Hôtel Mont Cameroun
        // ══════════════════════════════════════════════════════════════
        $site = \App\Models\Site::create([
            'name'        => 'Hôtel Mont Cameroun',
            'address'     => 'Boulevard de la Liberté, Douala, Cameroun',
            'latitude'    => 4.0483,
            'longitude'   => 9.7043,
            'description' => 'Hôtel 4 étoiles — 60 chambres, restaurant panoramique, spa et centre d\'affaires.',
        ]);

        // ══════════════════════════════════════════════════════════════
        // BÂTIMENT : Bâtiment Principal (5 niveaux : RDC + 3 étages + rooftop)
        // ══════════════════════════════════════════════════════════════
        $building = $site->buildings()->create([
            'name'         => 'Bâtiment Principal',
            'floors_count' => 5,
            'model_3d'     => [
                'width_m'        => 42,
                'depth_m'        => 26,
                'floor_height_m' => 3.3,
            ],
        ]);

        // ══════════════════════════════════════════════════════════════
        // ÉTAGE 0 — Rez-de-chaussée
        // Réception · Restaurant · Bar · Boutique · Centre d'affaires
        // ══════════════════════════════════════════════════════════════
        $rdc = $building->floors()->create([
            'level' => 0,
            'label' => 'Rez-de-chaussée',
            'rooms' => [
                ['id'=>'RDC01','name'=>'Réception',            'x'=>2,  'y'=>2,  'w'=>24,'h'=>40],
                ['id'=>'RDC02','name'=>'Lobby & Lounge',       'x'=>28, 'y'=>2,  'w'=>26,'h'=>40],
                ['id'=>'RDC03','name'=>'Restaurant',           'x'=>56, 'y'=>2,  'w'=>24,'h'=>40],
                ['id'=>'RDC04','name'=>'Boutique',             'x'=>82, 'y'=>2,  'w'=>16,'h'=>40],
                ['id'=>'RDC05','name'=>'Couloir principal',    'x'=>2,  'y'=>44, 'w'=>96,'h'=>10],
                ['id'=>'RDC06','name'=>'Bar',                  'x'=>2,  'y'=>56, 'w'=>26,'h'=>40],
                ['id'=>'RDC07','name'=>'Centre d\'affaires',   'x'=>30, 'y'=>56, 'w'=>26,'h'=>40],
                ['id'=>'RDC08','name'=>'Salle de conférence',  'x'=>58, 'y'=>56, 'w'=>26,'h'=>40],
                ['id'=>'RDC09','name'=>'Sortie / Parking',     'x'=>86, 'y'=>56, 'w'=>12,'h'=>40],
            ],
        ]);

        // ══════════════════════════════════════════════════════════════
        // ÉTAGE 1 — Chambres Standard (101-112) + Ascenseurs
        // ══════════════════════════════════════════════════════════════
        $etage1 = $building->floors()->create([
            'level' => 1,
            'label' => '1er étage — Chambres Standard',
            'rooms' => [
                ['id'=>'E101','name'=>'Chambres 101-106',  'x'=>2,  'y'=>2,  'w'=>45,'h'=>40],
                ['id'=>'E102','name'=>'Chambres 107-112',  'x'=>53, 'y'=>2,  'w'=>45,'h'=>40],
                ['id'=>'E103','name'=>'Couloir',           'x'=>2,  'y'=>44, 'w'=>96,'h'=>14],
                ['id'=>'E104','name'=>'Ascenseurs',        'x'=>42, 'y'=>60, 'w'=>16,'h'=>20],
                ['id'=>'E105','name'=>'Office d\'étage',   'x'=>2,  'y'=>60, 'w'=>36,'h'=>36],
                ['id'=>'E106','name'=>'Lingerie',          'x'=>62, 'y'=>60, 'w'=>36,'h'=>36],
            ],
        ]);

        // ══════════════════════════════════════════════════════════════
        // ÉTAGE 2 — Chambres Supérieures (201-212) + Salle de fitness
        // ══════════════════════════════════════════════════════════════
        $etage2 = $building->floors()->create([
            'level' => 2,
            'label' => '2e étage — Chambres Supérieures',
            'rooms' => [
                ['id'=>'F201','name'=>'Chambres 201-206',  'x'=>2,  'y'=>2,  'w'=>45,'h'=>40],
                ['id'=>'F202','name'=>'Chambres 207-212',  'x'=>53, 'y'=>2,  'w'=>45,'h'=>40],
                ['id'=>'F203','name'=>'Couloir',           'x'=>2,  'y'=>44, 'w'=>96,'h'=>14],
                ['id'=>'F204','name'=>'Ascenseurs',        'x'=>42, 'y'=>60, 'w'=>16,'h'=>20],
                ['id'=>'F205','name'=>'Salle de fitness',  'x'=>2,  'y'=>60, 'w'=>36,'h'=>36],
                ['id'=>'F206','name'=>'Salle de massage',  'x'=>62, 'y'=>60, 'w'=>36,'h'=>36],
            ],
        ]);

        // ══════════════════════════════════════════════════════════════
        // ÉTAGE 3 — Suites & Spa
        // ══════════════════════════════════════════════════════════════
        $etage3 = $building->floors()->create([
            'level' => 3,
            'label' => '3e étage — Suites & Spa',
            'rooms' => [
                ['id'=>'G301','name'=>'Suites Junior',     'x'=>2,  'y'=>2,  'w'=>45,'h'=>40],
                ['id'=>'G302','name'=>'Suites Présidentielles','x'=>53,'y'=>2, 'w'=>45,'h'=>40],
                ['id'=>'G303','name'=>'Couloir',           'x'=>2,  'y'=>44, 'w'=>96,'h'=>14],
                ['id'=>'G304','name'=>'Ascenseurs',        'x'=>42, 'y'=>60, 'w'=>16,'h'=>20],
                ['id'=>'G305','name'=>'Spa & Bien-être',   'x'=>2,  'y'=>60, 'w'=>36,'h'=>36],
                ['id'=>'G306','name'=>'Sauna & Hammam',    'x'=>62, 'y'=>60, 'w'=>36,'h'=>36],
            ],
        ]);

        // ══════════════════════════════════════════════════════════════
        // ÉTAGE 4 — Rooftop : Piscine & Restaurant panoramique
        // ══════════════════════════════════════════════════════════════
        $rooftop = $building->floors()->create([
            'level' => 4,
            'label' => 'Rooftop — Piscine & Panorama',
            'rooms' => [
                ['id'=>'H401','name'=>'Piscine',                  'x'=>2,  'y'=>2,  'w'=>50,'h'=>55],
                ['id'=>'H402','name'=>'Restaurant Panoramique',   'x'=>56, 'y'=>2,  'w'=>42,'h'=>55],
                ['id'=>'H403','name'=>'Terrasse',                 'x'=>2,  'y'=>60, 'w'=>96,'h'=>20],
                ['id'=>'H404','name'=>'Ascenseurs',               'x'=>42, 'y'=>83, 'w'=>16,'h'=>14],
            ],
        ]);

        // ══════════════════════════════════════════════════════════════
        // DESTINATIONS navigables
        // ══════════════════════════════════════════════════════════════
        $destData = [
            // RDC
            [$rdc,     'RDC01', 'Réception',                 'admin',   'Check-in / check-out — ouvert 24h/24',     1],
            [$rdc,     'RDC03', 'Restaurant',                 'service', 'Petit-déjeuner, déjeuner et dîner',         2],
            [$rdc,     'RDC06', 'Bar',                        'service', 'Bar & lounge — ouvert dès 11h',             3],
            [$rdc,     'RDC04', 'Boutique',                   'shop',    'Souvenirs et produits locaux',              4],
            [$rdc,     'RDC07', 'Centre d\'affaires',         'service', 'Bureautique, impression, visioconférence',  5],
            [$rdc,     'RDC08', 'Salle de conférence',        'service', 'Capacité 80 personnes — sur réservation',   6],
            [$rdc,     'RDC09', 'Sortie / Parking',           'exit',    'Accès parking et dépose-minute',            7],
            // Étage 1
            [$etage1,  'E105',  'Office d\'étage',            'service', '1er étage — Service de chambre',            8],
            // Étage 2
            [$etage2,  'F205',  'Salle de fitness',           'service', '2e étage — Ouvert 6h-22h',                   9],
            [$etage2,  'F206',  'Salle de massage',           'service', '2e étage — Sur réservation',                10],
            // Étage 3
            [$etage3,  'G305',  'Spa & Bien-être',            'service', '3e étage — Soins corps et visage',          11],
            [$etage3,  'G306',  'Sauna & Hammam',             'service', '3e étage — Accès libre pour les résidents', 12],
            // Rooftop
            [$rooftop, 'H401',  'Piscine',                    'service', 'Rooftop — Ouvert 7h-19h',                    13],
            [$rooftop, 'H402',  'Restaurant Panoramique',     'service', 'Rooftop — Vue sur la mer, réservation conseillée', 14],
        ];

        foreach ($destData as [$floor, $roomId, $label, $cat, $desc, $order]) {
            $floor->destinations()->create([
                'room_id'     => $roomId,
                'label'       => $label,
                'category'    => $cat,
                'description' => $desc,
                'sort_order'  => $order,
                'is_active'   => true,
            ]);
        }

        // ══════════════════════════════════════════════════════════════
        // QR CODES — placés physiquement dans le bâtiment
        // ══════════════════════════════════════════════════════════════
        $qrPlacements = [
            ['floor' => $rdc,     'label' => 'Entrée principale — Réception', 'pos_x' => 14, 'pos_y' => 22],
            ['floor' => $rdc,     'label' => 'Couloir RDC — Centre d\'affaires', 'pos_x' => 50, 'pos_y' => 49],
            ['floor' => $etage1,  'label' => 'Palier ascenseurs — 1er étage', 'pos_x' => 50, 'pos_y' => 70],
            ['floor' => $etage2,  'label' => 'Palier ascenseurs — 2e étage', 'pos_x' => 50, 'pos_y' => 70],
            ['floor' => $etage3,  'label' => 'Entrée Spa — 3e étage',        'pos_x' => 20, 'pos_y' => 78],
            ['floor' => $rooftop, 'label' => 'Rooftop — Accès piscine',      'pos_x' => 50, 'pos_y' => 90],
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

        // ══════════════════════════════════════════════════════════════
        // RÉSUMÉ
        // ══════════════════════════════════════════════════════════════
        $this->command->info('');
        $this->command->info('  ✓ Site       : ' . $site->name);
        $this->command->info('  ✓ Bâtiment   : ' . $building->name . ' (' . $building->floors_count . ' niveaux)');
        $this->command->info('  ✓ Étages     : ' . $building->floors()->count() . ' configurés');
        $this->command->info('  ✓ Destinations: ' . Destination::whereIn('floor_id', $building->floors->pluck('id'))->count() . ' navigables');
        $this->command->info('  ✓ QR codes   : ' . count($generatedQrs) . ' générés et placés');
        $this->command->info('');
        $this->command->info('  Pour tester le scan, ouvrez :');
        foreach ($generatedQrs as $qr) {
            $this->command->line('  → /scan/' . $qr->uuid . '  (' . $qr->location_label . ')');
        }
        $this->command->info('');
    }
}