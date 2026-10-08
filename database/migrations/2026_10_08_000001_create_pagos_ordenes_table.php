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
        Schema::create('pagos_ordenes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('taller_id')->constrained('talleres')->cascadeOnDelete();
            $table->foreignId('orden_trabajo_id')->constrained('ordenes_trabajo')->cascadeOnDelete();
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();
            
            $table->decimal('monto', 12, 2)->comment('Valor abonado o pagado en pesos');
            $table->string('metodo_pago', 50)->default('efectivo')->comment('efectivo, nequi, daviplata, transferencia, tarjeta, otro');
            $table->string('referencia', 100)->nullable()->comment('Número de comprobante Nequi/transferencia o voucher');
            $table->string('notas', 255)->nullable()->comment('Detalle opcional: Anticipo repuesto, liquidación final, etc.');
            $table->dateTime('fecha_pago')->useCurrent();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['taller_id', 'orden_trabajo_id']);
            $table->index(['taller_id', 'fecha_pago']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos_ordenes');
    }
};
