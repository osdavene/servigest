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
        Schema::create('categorias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('taller_id')->constrained('talleres')->cascadeOnDelete();
            $table->string('nombre', 100);
            $table->string('descripcion', 255)->nullable();
            $table->boolean('requiere_mantenimiento_preventivo')->default(false);
            $table->integer('intervalo_mantenimiento_dias')->nullable()->comment('Días estándar para mantenimiento');
            $table->timestamps();

            $table->unique(['taller_id', 'nombre']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categorias');
    }
};
