<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contrato_id')->nullable(); // Relación con el contrato
            $table->enum('metodo_pago', ['efectivo', 'transferencia bancaria', 'tarjeta credito/debito', 'cheque', 'otro']);
            $table->decimal('monto', 10, 2);
            $table->dateTime('fecha_pago')->default(DB::raw('CURRENT_TIMESTAMP')); // Fecha y hora del pago
            $table->string('referencia')->nullable(); // e.g., número de transacción, cheque
            $table->string('documento')->nullable(); // e.g., recibo, factura
            $table->string('observaciones')->nullable();
            $table->enum('estado', ['pendiente', 'hecho', 'retrasado'])->default('hecho'); // Default to 'hecho' as per refactor suggestion, though keeping enum options

            // Relaciones foráneas
            $table->foreign('contrato_id')->references('id')->on('contratos')->onDelete('set null');

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
