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
        Schema::create('ordenes_trabajo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('taller_id')->constrained('talleres')->cascadeOnDelete();
            $table->string('codigo_orden', 30)->comment('Ej: ORD-2026-0001');
            $table->foreignId('equipo_id')->constrained('equipos')->restrictOnDelete();
            $table->foreignId('cliente_id')->constrained('clientes')->restrictOnDelete();
            $table->foreignId('tecnico_asignado_id')->nullable()->constrained('usuarios')->nullOnDelete();
            
            $table->enum('tipo_ubicacion', ['servicio_en_domicilio', 'ingresado_al_taller'])->default('ingresado_al_taller');
            $table->enum('estado', ['pendiente', 'en_proceso', 'finalizado', 'entregado', 'cancelado'])->default('pendiente');
            
            // Diagnóstico y Procedimiento
            $table->text('problema_reportado');
            $table->text('diagnostico')->nullable();
            $table->text('procedimiento_realizado')->nullable();
            $table->text('repuestos_usados')->nullable();
            $table->decimal('costo_mano_obra', 12, 2)->default(0);
            $table->decimal('costo_repuestos', 12, 2)->default(0);
            $table->decimal('costo_total', 12, 2)->default(0);

            // Firma Digital del Cliente (Canvas HTML5)
            $table->string('ruta_firma_cliente', 255)->nullable()->comment('Ruta relativa en storage');
            $table->string('nombre_firmante', 150)->nullable();
            $table->dateTime('fecha_firma')->nullable();

            // Seguridad y Enlaces Públicos
            $table->uuid('token_publico_pdf')->unique();
            $table->dateTime('fecha_ingreso');
            $table->dateTime('fecha_promesa')->nullable();
            $table->dateTime('fecha_finalizacion')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['taller_id', 'codigo_orden']);
            $table->index(['taller_id', 'estado']);
            $table->index(['taller_id', 'tecnico_asignado_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ordenes_trabajo');
    }
};
