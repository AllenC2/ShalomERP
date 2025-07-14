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
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contrato_id')->nullable(); // Relación con el contrato
            $table->decimal('monto', 10, 2);
            $table->string('observaciones')->nullable();
            $table->date('fecha_pago');
            $table->string('metodo_pago'); // e.g., 'efectivo', 'tarjeta', 'transferencia'
            $table->string('estado')->default('Pendiente'); // e.g., 'pendiente', 'completado', 'cancelado'
            $table->foreign('contrato_id')->references('id')->on('contratos')->onDelete('set null');
            $table->string('documento')->nullable(); // e.g., recibo, factura
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
