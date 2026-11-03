<?php
// database/migrations/2026_07_18_000000_add_end_point_to_navigation_paths.php
//
// Ajoute deux colonnes à navigation_paths :
//  - end_point : point d'arrivée précis (porte) au format JSON {x, y}.
//                NULL = viser le centre de la pièce (comportement historique).
//  - smooth    : lissage du tracé (si la colonne n'existe pas déjà).
//
// Le point d'arrivée modifié par l'admin est ainsi PERSISTÉ et relu au recalcul,
// au lieu d'être ignoré au profit du centre de la pièce.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('navigation_paths', function (Blueprint $table) {
            if (!Schema::hasColumn('navigation_paths', 'end_point')) {
                // {x: float, y: float} en % — porte / point d'accès de la pièce
                $table->json('end_point')->nullable()->after('waypoints');
            }
            if (!Schema::hasColumn('navigation_paths', 'smooth')) {
                $table->boolean('smooth')->default(false)->after('end_point');
            }
        });
    }

    public function down(): void
    {
        Schema::table('navigation_paths', function (Blueprint $table) {
            if (Schema::hasColumn('navigation_paths', 'end_point')) {
                $table->dropColumn('end_point');
            }
            // On ne retire pas 'smooth' au rollback si d'autres migrations en dépendent.
        });
    }
};