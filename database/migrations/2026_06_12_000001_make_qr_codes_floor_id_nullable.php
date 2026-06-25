<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('qr_codes', function (Blueprint $table) {
            $table->foreignId('floor_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Réaffecte un floor_id par défaut avant de remettre NOT NULL si besoin
        Schema::table('qr_codes', function (Blueprint $table) {
            $table->foreignId('floor_id')->nullable(false)->change();
        });
    }
};