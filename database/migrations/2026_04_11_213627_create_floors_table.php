<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
       // create_floors_table
        Schema::create('floors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('building_id')->constrained()->cascadeOnDelete();
            $table->integer('level'); // 0 = RDC, 1, 2...
            $table->string('label')->nullable(); // "Rez-de-chaussée", "1er étage"
            $table->json('plan_2d')->nullable(); // SVG ou GeoJSON du niveau
            $table->json('rooms')->nullable(); // liste des pièces avec coords
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('floors');
    }
};
