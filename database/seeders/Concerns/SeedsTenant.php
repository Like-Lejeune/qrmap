<?php
// database/seeders/Concerns/SeedsTenant.php

namespace Database\Seeders\Concerns;

use App\Models\User;
use App\Support\Tenancy;
use Illuminate\Support\Facades\Auth;

/**
 * Donne aux seeders la capacité de créer des données pour un tenant donné,
 * de façon compatible avec les deux modes :
 *
 *  - MULTI  : un compte tenant dédié par démo. En s'authentifiant, le trait
 *             HasTenant remplit automatiquement user_id sur Site et QrCode.
 *  - SINGLE : tout est rattaché au propriétaire unique de l'instance
 *             (premier utilisateur), créé si nécessaire.
 *
 * Usage dans un seeder :
 *
 *   use Database\Seeders\Concerns\SeedsTenant;
 *   class XxxSeeder extends Seeder {
 *       use SeedsTenant;
 *       public function run(): void {
 *           $this->asTenant('hopital@qrmap.test', 'Hôpital Central', function () {
 *               // ... création Site / Building / Floors / Destinations / QR ...
 *           });
 *       }
 *   }
 */
trait SeedsTenant
{
    /**
     * Exécute le callback en étant authentifié comme le tenant propriétaire.
     * Garantit la déconnexion à la fin (même en cas d'exception).
     */
    protected function asTenant(string $email, string $name, callable $callback): void
    {
        $owner = $this->resolveTenantOwner($email, $name);

        Auth::login($owner);   // HasTenant → user_id = ce tenant
        try {
            $callback($owner);
        } finally {
            Auth::logout();
        }
    }

    /**
     * Résout le propriétaire des données selon le mode.
     */
    protected function resolveTenantOwner(string $email, string $name): User
    {
        if (Tenancy::isSingle()) {
            // Instance dédiée : un seul propriétaire pour toute la base.
            return User::orderBy('id')->first()
                ?? User::create([
                    'name'     => $name,
                    'email'    => $email,
                    'password' => bcrypt('password'),
                ]);
        }

        // SaaS multi : un compte tenant dédié par démo (réutilisé si déjà créé).
        return User::firstOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => bcrypt('password')]
        );
    }
}