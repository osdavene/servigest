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
        Schema::create('equipos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('taller_id')->constrained('talleres')->cascadeOnDelete();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('categoria_id')->constrained('categorias')->restrictOnDelete();
            $table->string('marca', 80);
            $table->string('modelo', 100);
            $table->string('numero_serie', 100)->nullable();
            $table->text('observaciones_fisicas')->nullable()->comment('Golpes, rayones, faltantes');
            $table->date('fecha_ultimo_servicio')->nullable();
            $table->date('fecha_proximo_mantenimiento')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['taller_id', 'cliente_id']);
            $table->index(['taller_id', 'numero_serie']);
            $table->index(['taller_id', 'fecha_proximo_mantenimiento']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipos');
    }
};
