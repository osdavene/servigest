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
        Schema::create('talleres', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_comercial', 150);
            $table->string('identificacion_fiscal', 50)->nullable()->comment('NIT / RFC / CIF / RUC');
            $table->string('telefono', 30);
            $table->string('email', 150)->unique();
            $table->string('direccion', 255)->nullable();
            $table->string('ciudad', 100)->nullable();
            $table->string('logo_ruta', 255)->nullable();
            $table->string('logo_url', 255)->nullable();
            $table->enum('estado_suscripcion', ['activo', 'suspendido', 'periodo_prueba'])->default('periodo_prueba');
            $table->date('fecha_vencimiento_suscripcion')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('talleres');
    }
};
