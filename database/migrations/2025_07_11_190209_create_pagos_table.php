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
            $table->unsignedBigInteger('pago_padre_id')->nullable(); // Relación con pago padre (para parcialidades)
            $table->enum('tipo_pago', ['cuota', 'parcialidad', 'inicial', 'bonificación']); // Solo valores permitidos
            $table->enum('metodo_pago', ['efectivo', 'transferencia bancaria', 'tarjeta credito/debito', 'cheque', 'otro']); 
            $table->decimal('monto', 10, 2);
            $table->dateTime('fecha_pago')->default(DB::raw('CURRENT_TIMESTAMP')); // Fecha y hora del pago
            $table->integer('numero_cuota')->nullable(); // Número de cuota (1, 2, 3, etc.) - solo para pagos tipo 'cuota'
            $table->string('referencia')->nullable(); // e.g., número de transacción, cheque
            $table->decimal('saldo_restante', 10, 2)->default(0); // Saldo restante después del pago
            $table->string('documento')->nullable(); // e.g., recibo, factura
            $table->string('observaciones')->nullable();
            $table->enum('estado', ['pendiente', 'hecho', 'retrasado'])->default('pendiente'); // Solo valores permitidos
            
            // Relaciones foráneas
            $table->foreign('contrato_id')->references('id')->on('contratos')->onDelete('set null');
            $table->foreign('pago_padre_id')->references('id')->on('pagos')->onDelete('cascade'); // Si se elimina el pago padre, se eliminan las parcialidades
            
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
