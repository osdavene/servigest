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
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('taller_id')->constrained('talleres')->cascadeOnDelete();
            $table->string('nombre_completo', 150);
            $table->string('identificacion', 50)->nullable();
            $table->string('telefono', 30)->comment('Número con código de país, ej: 573001234567');
            $table->string('telefono_secundario', 30)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('direccion', 255);
            $table->string('barrio', 100)->nullable();
            $table->string('ciudad', 100)->nullable();
            $table->decimal('latitud', 10, 8)->nullable();
            $table->decimal('longitud', 11, 8)->nullable();
            $table->text('notas_adicionales')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['taller_id', 'telefono']);
            $table->index(['taller_id', 'nombre_completo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
