<?php
// database/migrations/2026_06_22_000000_add_user_id_to_tenant_tables.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        foreach (['sites', 'qr_codes'] as $table) {
            if (!Schema::hasColumn($table, 'user_id')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->foreignId('user_id')->nullable()->after('id')
                      ->constrained()->nullOnDelete();
                });
            }
        }

        // Backfill : rattache toutes les données existantes au premier utilisateur.
        $firstUserId = DB::table('users')->orderBy('id')->value('id');
        if ($firstUserId) {
            DB::table('sites')->whereNull('user_id')->update(['user_id' => $firstUserId]);
            DB::table('qr_codes')->whereNull('user_id')->update(['user_id' => $firstUserId]);
        }
    }

    public function down(): void
    {
        foreach (['sites', 'qr_codes'] as $table) {
            if (Schema::hasColumn($table, 'user_id')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropConstrainedForeignId('user_id');
                });
            }
        }
    }
};