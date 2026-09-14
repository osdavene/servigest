<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Agregar deleted_at (Borrado Lógico) a todas las tablas del sistema
        Schema::table('talleres', function (Blueprint $table) {
            if (!Schema::hasColumn('talleres', 'plan_licencia_id')) {
                $table->foreignId('plan_licencia_id')->nullable()->constrained('planes_licencia')->nullOnDelete();
            }
            if (!Schema::hasColumn('talleres', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        Schema::table('usuarios', function (Blueprint $table) {
            if (!Schema::hasColumn('usuarios', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        Schema::table('clientes', function (Blueprint $table) {
            if (!Schema::hasColumn('clientes', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        Schema::table('categorias', function (Blueprint $table) {
            if (!Schema::hasColumn('categorias', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        Schema::table('equipos', function (Blueprint $table) {
            if (!Schema::hasColumn('equipos', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        Schema::table('ordenes_trabajo', function (Blueprint $table) {
            if (!Schema::hasColumn('ordenes_trabajo', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        Schema::table('evidencias_fotograficas', function (Blueprint $table) {
            if (!Schema::hasColumn('evidencias_fotograficas', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        Schema::table('planes_licencia', function (Blueprint $table) {
            if (!Schema::hasColumn('planes_licencia', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        Schema::table('talleres', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropConstrainedForeignId('plan_licencia_id');
        });
        Schema::table('usuarios', fn (Blueprint $table) => $table->dropSoftDeletes());
        Schema::table('clientes', fn (Blueprint $table) => $table->dropSoftDeletes());
        Schema::table('categorias', fn (Blueprint $table) => $table->dropSoftDeletes());
        Schema::table('equipos', fn (Blueprint $table) => $table->dropSoftDeletes());
        Schema::table('ordenes_trabajo', fn (Blueprint $table) => $table->dropSoftDeletes());
        Schema::table('evidencias_fotograficas', fn (Blueprint $table) => $table->dropSoftDeletes());
        Schema::table('planes_licencia', fn (Blueprint $table) => $table->dropSoftDeletes());
    }
};
