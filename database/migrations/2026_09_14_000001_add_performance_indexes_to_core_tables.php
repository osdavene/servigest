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
        Schema::table('ordenes_trabajo', function (Blueprint $table) {
            $table->index(['taller_id', 'estado'], 'idx_ot_taller_estado');
            $table->index(['taller_id', 'tecnico_asignado_id'], 'idx_ot_taller_tecnico');
            $table->index(['taller_id', 'cliente_id'], 'idx_ot_taller_cliente');
            $table->index(['taller_id', 'equipo_id'], 'idx_ot_taller_equipo');
            $table->index(['taller_id', 'created_at'], 'idx_ot_taller_created');
            $table->index('token_publico_pdf', 'idx_ot_token_publico');
        });

        Schema::table('equipos', function (Blueprint $table) {
            $table->index(['taller_id', 'cliente_id'], 'idx_eq_taller_cliente');
            $table->index(['taller_id', 'categoria_id'], 'idx_eq_taller_categoria');
            $table->index(['taller_id', 'fecha_proximo_mantenimiento'], 'idx_eq_taller_mantenimiento');
        });

        Schema::table('clientes', function (Blueprint $table) {
            $table->index(['taller_id', 'identificacion'], 'idx_cli_taller_identificacion');
            $table->index(['taller_id', 'telefono'], 'idx_cli_taller_telefono');
        });

        Schema::table('usuarios', function (Blueprint $table) {
            $table->index(['taller_id', 'rol'], 'idx_usr_taller_rol');
        });

        Schema::table('evidencias_fotograficas', function (Blueprint $table) {
            $table->index(['taller_id', 'orden_trabajo_id', 'etiqueta'], 'idx_evi_taller_ot_etiqueta');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ordenes_trabajo', function (Blueprint $table) {
            $table->dropIndex('idx_ot_taller_estado');
            $table->dropIndex('idx_ot_taller_tecnico');
            $table->dropIndex('idx_ot_taller_cliente');
            $table->dropIndex('idx_ot_taller_equipo');
            $table->dropIndex('idx_ot_taller_created');
            $table->dropIndex('idx_ot_token_publico');
        });

        Schema::table('equipos', function (Blueprint $table) {
            $table->dropIndex('idx_eq_taller_cliente');
            $table->dropIndex('idx_eq_taller_categoria');
            $table->dropIndex('idx_eq_taller_mantenimiento');
        });

        Schema::table('clientes', function (Blueprint $table) {
            $table->dropIndex('idx_cli_taller_identificacion');
            $table->dropIndex('idx_cli_taller_telefono');
        });

        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropIndex('idx_usr_taller_rol');
        });

        Schema::table('evidencias_fotograficas', function (Blueprint $table) {
            $table->dropIndex('idx_evi_taller_ot_etiqueta');
        });
    }
};
