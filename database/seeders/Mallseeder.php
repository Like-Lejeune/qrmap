<?php
// database/seeders/MallSeeder.php
//
// Cas pratique : Centre Commercial Akwa Palace (Douala)
// Site + Bâtiment principal (3 niveaux) + Destinations + QR codes placés
// Lance avec : php artisan db:seed --class=MallSeeder

namespace Database\Seeders;

use App\Models\Destination;
use App\Services\QrCodeService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class MallSeeder extends Seeder
{
    public function run(): void
    {
        Storage::disk('public')->makeDirectory('qrcodes');
        $service = app(QrCodeService::class);

        // ══════════════════════════════════════════════════════════════
        // SITE : Centre Commercial Akwa Palace
        // ══════════════════════════════════════════════════════════════
        $site = \App\Models\Site::create([
            'name'        => 'Centre Commercial Akwa Palace',
            'address'     => 'Rue Joss, Akwa, Douala, Cameroun',
            'latitude'    => 4.0518,
            'longitude'   => 9.6987,
            'description' => 'Centre commercial de 3 niveaux — boutiques, hypermarché, cinéma et restauration.',
        ]);

        // ══════════════════════════════════════════════════════════════
        // BÂTIMENT : Galerie Principale (3 niveaux : Parking, RDC, Étage)
        // ══════════════════════════════════════════════════════════════
        $building = $site->buildings()->create([
            'name'         => 'Galerie Principale',
            'floors_count' => 3,
            'model_3d'     => [
                'width_m'        => 70,
                'depth_m'        => 45,
                'floor_height_m' => 4,
            ],
        ]);

        // ══════════════════════════════════════════════════════════════
        // NIVEAU 0 — Parking & Services
        // ══════════════════════════════════════════════════════════════
        $parking = $building->floors()->create([
            'level' => 0,
            'label' => 'Niveau Parking',
            'rooms' => [
                ['id'=>'P001','name'=>'Parking A',          'x'=>2,  'y'=>2,  'w'=>46,'h'=>60],
                ['id'=>'P002','name'=>'Parking B',          'x'=>52, 'y'=>2,  'w'=>46,'h'=>60],
                ['id'=>'P003','name'=>'Rampe d\'accès',     'x'=>2,  'y'=>64, 'w'=>20,'h'=>34],
                ['id'=>'P004','name'=>'Local technique',    'x'=>24, 'y'=>64, 'w'=>24,'h'=>34],
                ['id'=>'P005','name'=>'Ascenseurs / Escaliers','x'=>50,'y'=>64,'w'=>18,'h'=>34],
                ['id'=>'P006','name'=>'Sortie véhicules',   'x'=>70, 'y'=>64, 'w'=>28,'h'=>34],
            ],
        ]);

        // ══════════════════════════════════════════════════════════════
        // NIVEAU 1 — Rez-de-chaussée : Boutiques & Hypermarché
        // ══════════════════════════════════════════════════════════════
        $rdc = $building->floors()->create([
            'level' => 1,
            'label' => 'Rez-de-chaussée',
            'rooms' => [
                ['id'=>'RDC01','name'=>'Hall d\'entrée',      'x'=>2,  'y'=>2,  'w'=>30,'h'=>20],
                ['id'=>'RDC02','name'=>'Hypermarché',         'x'=>34, 'y'=>2,  'w'=>40,'h'=>50],
                ['id'=>'RDC03','name'=>'Pharmacie',           'x'=>76, 'y'=>2,  'w'=>22,'h'=>20],
                ['id'=>'RDC04','name'=>'Boutique Mode A',     'x'=>2,  'y'=>24, 'w'=>30,'h'=>26],
                ['id'=>'RDC05','name'=>'Banque / Agence',     'x'=>76, 'y'=>24, 'w'=>22,'h'=>28],
                ['id'=>'RDC06','name'=>'Galerie marchande',   'x'=>2,  'y'=>54, 'w'=>96,'h'=>10],
                ['id'=>'RDC07','name'=>'Boutique Téléphonie', 'x'=>2,  'y'=>66, 'w'=>22,'h'=>30],
                ['id'=>'RDC08','name'=>'Food Court',          'x'=>26, 'y'=>66, 'w'=>48,'h'=>30],
                ['id'=>'RDC09','name'=>'Toilettes',           'x'=>76, 'y'=>66, 'w'=>10,'h'=>14],
                ['id'=>'RDC10','name'=>'Ascenseurs / Escaliers','x'=>76,'y'=>82,'w'=>10,'h'=>14],
                ['id'=>'RDC11','name'=>'Sortie vers Parking', 'x'=>88, 'y'=>66, 'w'=>10,'h'=>30],
            ],
        ]);

        // ══════════════════════════════════════════════════════════════
        // NIVEAU 2 — Étage : Cinéma, Mode & Loisirs
        // ══════════════════════════════════════════════════════════════
        $etage1 = $building->floors()->create([
            'level' => 2,
            'label' => '1er étage',
            'rooms' => [
                ['id'=>'E101','name'=>'Cinéma — Salle 1',    'x'=>2,  'y'=>2,  'w'=>30,'h'=>40],
                ['id'=>'E102','name'=>'Cinéma — Salle 2',    'x'=>34, 'y'=>2,  'w'=>30,'h'=>40],
                ['id'=>'E103','name'=>'Hall Cinéma & Snacks','x'=>66, 'y'=>2,  'w'=>32,'h'=>40],
                ['id'=>'E104','name'=>'Galerie',             'x'=>2,  'y'=>44, 'w'=>96,'h'=>10],
                ['id'=>'E105','name'=>'Boutique Mode B',     'x'=>2,  'y'=>56, 'w'=>26,'h'=>32],
                ['id'=>'E106','name'=>'Espace Jeux Enfants', 'x'=>30, 'y'=>56, 'w'=>26,'h'=>32],
                ['id'=>'E107','name'=>'Salon de coiffure',   'x'=>58, 'y'=>56, 'w'=>20,'h'=>32],
                ['id'=>'E108','name'=>'Toilettes',           'x'=>80, 'y'=>56, 'w'=>8, 'h'=>14],
                ['id'=>'E109','name'=>'Ascenseurs / Escaliers','x'=>80,'y'=>72,'w'=>8, 'h'=>16],
                ['id'=>'E110','name'=>'Terrasse',            'x'=>90, 'y'=>56, 'w'=>8, 'h'=>32],
            ],
        ]);

        // ══════════════════════════════════════════════════════════════
        // DESTINATIONS navigables
        // ══════════════════════════════════════════════════════════════
        $destData = [
            // Parking
            [$parking, 'P006', 'Sortie véhicules',       'exit',    'Vers la voie publique',                    1],
            // RDC
            [$rdc,     'RDC01', 'Hall d\'entrée',         'exit',    'Entrée principale piétons',                2],
            [$rdc,     'RDC02', 'Hypermarché',            'shop',    'Alimentation, électroménager, textile',    3],
            [$rdc,     'RDC03', 'Pharmacie',              'medical', 'Médicaments et parapharmacie',             4],
            [$rdc,     'RDC04', 'Boutique Mode A',        'shop',    'Prêt-à-porter homme & femme',              5],
            [$rdc,     'RDC05', 'Banque / Agence',        'service', 'Distributeur automatique disponible',      6],
            [$rdc,     'RDC07', 'Boutique Téléphonie',    'shop',    'Smartphones et accessoires',               7],
            [$rdc,     'RDC08', 'Food Court',             'service', 'Restauration rapide — 8 enseignes',        8],
            [$rdc,     'RDC09', 'Toilettes RDC',          'service', 'Toilettes publiques',                      9],
            // Étage 1
            [$etage1,  'E101',  'Cinéma — Salle 1',       'service', '1er étage — Séances dès 14h',             10],
            [$etage1,  'E102',  'Cinéma — Salle 2',       'service', '1er étage — Séances dès 14h',             11],
            [$etage1,  'E105',  'Boutique Mode B',        'shop',    '1er étage — Vêtements enfants',           12],
            [$etage1,  'E106',  'Espace Jeux Enfants',    'service', '1er étage — Accès payant',                13],
            [$etage1,  'E107',  'Salon de coiffure',      'service', '1er étage — Sur rendez-vous',             14],
            [$etage1,  'E108',  'Toilettes Étage',        'service', '1er étage',                                15],
            [$etage1,  'E110',  'Terrasse',               'service', '1er étage — Vue sur la ville',            16],
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
            ['floor' => $parking, 'label' => 'Entrée Parking — Niveau Parking', 'pos_x' => 60, 'pos_y' => 80],
            ['floor' => $rdc,     'label' => 'Hall d\'entrée — RDC',            'pos_x' => 17, 'pos_y' => 12],
            ['floor' => $rdc,     'label' => 'Galerie marchande — RDC',         'pos_x' => 50, 'pos_y' => 59],
            ['floor' => $rdc,     'label' => 'Entrée Food Court',               'pos_x' => 50, 'pos_y' => 80],
            ['floor' => $etage1,  'label' => 'Hall Cinéma — 1er étage',         'pos_x' => 82, 'pos_y' => 22],
            ['floor' => $etage1,  'label' => 'Palier ascenseurs — 1er étage',   'pos_x' => 84, 'pos_y' => 80],
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