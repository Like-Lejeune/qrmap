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
       // create_qr_codes_table
        Schema::create('qr_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('uuid')->unique();
            $table->foreignId('site_id')->nullable()->constrained();
            $table->foreignId('building_id')->nullable()->constrained();
            $table->foreignId('floor_id')->nullable()->constrained();
            $table->string('location_label')->nullable(); // "Entrée principale", "Couloir B"
            $table->decimal('pos_x', 8, 4)->nullable(); // position sur le plan
            $table->decimal('pos_y', 8, 4)->nullable();
            $table->integer('scan_count')->default(0);
            $table->timestamp('last_scanned_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qr_codes');
    }
};
