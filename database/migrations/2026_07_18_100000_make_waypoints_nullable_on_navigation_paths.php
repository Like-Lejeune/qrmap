<?php
// database/migrations/2026_07_18_100000_make_waypoints_nullable_on_navigation_paths.php
//
// Contexte : on peut désormais enregistrer UNIQUEMENT le point d'arrivée
// (end_point) sans figer de tracé complet — dans ce cas waypoints = null et
// l'A* recalcule vers la porte. Or la colonne waypoints était NOT NULL, d'où :
//   SQLSTATE[23000]: Column 'waypoints' cannot be null
//
// Cette migration la rend nullable.
//
// ⚠️ Nécessite doctrine/dbal pour ->change() sur certaines versions de Laravel :
//     composer require doctrine/dbal
//   (Laravel 11+ n'en a plus besoin.) Si tu ne peux pas l'installer, utilise
//   la variante SQL brute en commentaire plus bas.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Voie standard (Laravel 11+, ou Laravel 10 avec doctrine/dbal) :
        Schema::table('navigation_paths', function (Blueprint $table) {
            $table->json('waypoints')->nullable()->change();
        });

        // ── Variante sans doctrine/dbal (décommente si ->change() échoue) ──
        // DB::statement('ALTER TABLE `navigation_paths` MODIFY `waypoints` JSON NULL');
    }

    public function down(): void
    {
        // Attention : le rollback échouera s'il reste des lignes waypoints = null.
        // On les nettoie d'abord pour éviter un plantage.
        DB::table('navigation_paths')->whereNull('waypoints')->delete();

        Schema::table('navigation_paths', function (Blueprint $table) {
            $table->json('waypoints')->nullable(false)->change();
        });

        // Variante sans doctrine/dbal :
        // DB::statement('ALTER TABLE `navigation_paths` MODIFY `waypoints` JSON NOT NULL');
    }
};