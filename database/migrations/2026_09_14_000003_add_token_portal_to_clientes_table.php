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
        Schema::table('clientes', function (Blueprint $table) {
            $table->string('token_portal', 64)->nullable()->unique()->after('notas_adicionales');
            $table->index(['taller_id', 'token_portal'], 'idx_cli_taller_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropIndex('idx_cli_taller_token');
            $table->dropColumn('token_portal');
        });
    }
};
