<?php
// database/migrations/2024_01_01_000006_create_destinations_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('destinations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('floor_id')->constrained()->cascadeOnDelete();

            // Référence l'id d'une pièce dans le JSON rooms du Floor
            $table->string('room_id', 30);

            // Nom affiché à l'utilisateur lors du scan
            $table->string('label');

            // Catégorie pour l'icône et le filtrage
            $table->enum('category', [
                'medical',    // Urgences, Pharmacie, Bloc opératoire
                'admin',      // Bureau, Accueil, Direction
                'service',    // Cafétéria, WC, Ascenseur
                'exit',       // Sortie, Escaliers, Sortie de secours
                'shop',
                'other',
            ])->default('other');

            // Description courte affichée sous le label
            $table->string('description')->nullable();

            // Ordre d'affichage dans la liste
            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Index pour la requête la plus fréquente : toutes les destinations d'un étage
            $table->index(['floor_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('destinations');
    }
};