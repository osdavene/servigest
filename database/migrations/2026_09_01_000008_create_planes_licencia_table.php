<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planes_licencia', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->integer('dias_duracion'); // Duración en días (ej: 30, 90, 180, 365)
            $table->decimal('precio', 12, 2)->default(0); // Precio en moneda local
            $table->integer('limite_usuarios')->nullable(); // null = ilimitado
            $table->text('descripcion')->nullable();
            $table->boolean('es_prueba')->default(false); // Para identificar el plan demo
            $table->boolean('esta_activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planes_licencia');
    }
};
