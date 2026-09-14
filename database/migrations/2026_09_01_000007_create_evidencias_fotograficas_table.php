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
        Schema::create('evidencias_fotograficas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('taller_id')->constrained('talleres')->cascadeOnDelete();
            $table->foreignId('orden_trabajo_id')->constrained('ordenes_trabajo')->cascadeOnDelete();
            $table->string('ruta_imagen', 255);
            $table->enum('etiqueta', ['antes', 'durante', 'falla_en_placa', 'despues', 'otra'])->default('otra');
            $table->string('descripcion', 255)->nullable();
            $table->timestamps();

            $table->index(['taller_id', 'orden_trabajo_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evidencias_fotograficas');
    }
};
