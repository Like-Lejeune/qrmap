<?php
// database/seeders/DatabaseSeeder.php

namespace Database\Seeders;

use App\Models\Building;
use App\Models\Destination;
use App\Models\Floor;
use App\Models\QrCode;
use App\Models\ScanLog;
use App\Models\Site;
use App\Models\User;
use App\Support\Tenancy;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // $this->resetData();

        // En mode single, on s'assure qu'un propriétaire existe AVANT les seeders :
        // tous les sites lui seront rattachés.
        if (Tenancy::isSingle()) {
            User::firstOrCreate(
                ['email' => 'admin@qrmap.test'],
                ['name' => 'Administrateur', 'password' => bcrypt('password')]
            );
            $this->command->info('Mode SINGLE : toutes les données seront rattachées au propriétaire unique.');
        } else {
            $this->command->info('Mode MULTI : chaque démo crée son propre compte tenant (mot de passe : password).');
        }

        $this->call([
            PlansiteSAGOSeeder::class,
            DemoSeeder::class,
            NavigationSeeder::class,
            HotelSeeder::class,
            MallSeeder::class,
            BankSeeder::class,
            AmusementParkSeeder::class,
        ]);

        $this->printTenantSummary();
    }

    /**
     * Affiche les comptes tenants créés (utile pour se connecter et tester l'isolation).
     */
    private function printTenantSummary(): void
    {
        if (Tenancy::isSingle()) {
            return;
        }

        $this->command->info('');
        $this->command->info('  ── Comptes tenants (mode multi) ─────────────────────');
        User::orderBy('id')->get()->each(function (User $u) {
            $count = Site::withoutTenant()->where('user_id', $u->id)->count();
            $this->command->line(sprintf('  • %-28s %d site(s)', $u->email, $count));
        });
        $this->command->info('  Mot de passe pour tous : password');
        $this->command->info('  ─────────────────────────────────────────────────────');
    }

    /**
     * Vide les tables QRMap avant réinjection (dev uniquement).
     * Ne touche PAS la table users (on conserve les comptes tenants).
     */
    private function resetData(): void
    {
        $this->command->info('Nettoyage des données existantes...');

        Storage::disk('public')->deleteDirectory('qrcodes');
        Storage::disk('public')->makeDirectory('qrcodes');

        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');
        } else {
            Schema::disableForeignKeyConstraints();
        }

        foreach ([
            ScanLog::class,
            QrCode::class,
            Destination::class,
            Floor::class,
            Building::class,
            Site::class,
        ] as $model) {
            $table = (new $model)->getTable();
            DB::table($table)->truncate();
        }

        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON');
        } else {
            Schema::enableForeignKeyConstraints();
        }

        $this->command->info('✓ Tables vidées (users conservés).');
        $this->command->info('');
    }
}