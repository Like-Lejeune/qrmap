<?php
// database/migrations/2026_06_24_000000_add_segments_to_navigation_paths.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('navigation_paths', function (Blueprint $table) {
            // Tracé multi-étage : tableau de tronçons
            // [{floor_id, role, smooth, waypoints:[{x,y}]}, ...]
            // NULL = tracé mono-étage (on utilise waypoints/smooth comme avant).
            if (!Schema::hasColumn('navigation_paths', 'segments')) {
                $table->json('segments')->nullable()->after('smooth');
            }
        });
    }

    public function down(): void
    {
        Schema::table('navigation_paths', function (Blueprint $table) {
            if (Schema::hasColumn('navigation_paths', 'segments')) {
                $table->dropColumn('segments');
            }
        });
    }
};