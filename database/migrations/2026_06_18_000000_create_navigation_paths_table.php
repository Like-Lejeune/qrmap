<?php
// database/migrations/2026_06_18_000000_create_navigation_paths_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('navigation_paths', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qr_code_id')->constrained()->cascadeOnDelete();
            $table->foreignId('destination_id')->constrained()->cascadeOnDelete();
            $table->json('waypoints');               // [{x,y}, ...] en %
            $table->boolean('smooth')->default(false);
            $table->timestamps();

            // Un seul tracé par couple (origine QR, destination)
            $table->unique(['qr_code_id', 'destination_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('navigation_paths');
    }
};