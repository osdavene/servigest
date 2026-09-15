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
        // 1. Agregar campos de sesión activa y último login a usuarios
        Schema::table('usuarios', function (Blueprint $table) {
            $table->string('current_session_id', 255)->nullable()->after('esta_activo');
            $table->timestamp('ultimo_login_at')->nullable()->after('current_session_id');
            $table->string('ultimo_login_ip', 45)->nullable()->after('ultimo_login_at');
        });

        // 2. Crear tabla de bitácora y auditoría de accesos
        Schema::create('registros_acceso', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->foreignId('taller_id')->nullable()->constrained('talleres')->nullOnDelete();
            $table->string('email_ingresado', 150)->index();
            $table->string('ip_address', 45);
            $table->string('dispositivo', 100)->nullable();
            $table->string('navegador', 100)->nullable();
            $table->string('sistema_operativo', 100)->nullable();
            $table->string('pais', 100)->nullable();
            $table->string('ciudad', 100)->nullable();
            $table->string('estado', 50)->default('exitoso'); // exitoso, fallido, cerrado_por_otra_sesion, inactividad, logout
            $table->string('session_id', 255)->nullable()->index();
            $table->timestamp('fecha_ingreso')->useCurrent();
            $table->timestamp('fecha_cierre')->nullable();
            $table->timestamps();

            $table->index(['taller_id', 'fecha_ingreso']);
            $table->index(['usuario_id', 'fecha_ingreso']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registros_acceso');

        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropColumn(['current_session_id', 'ultimo_login_at', 'ultimo_login_ip']);
        });
    }
};
