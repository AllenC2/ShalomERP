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
        Schema::create('comisiones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contrato_id')->nullable(); // Relación con el contrato
            $table->unsignedBigInteger('empleado_id')->nullable(); // Empleado asociado a la comisión
            $table->date('fecha_comision')->nullable(); // Fecha en que se generó
            $table->string('tipo_comision')->nullable(); // e.g., 'venta', 'renovación', 'referencia'
            $table->decimal('monto', 10, 2);
            $table->string('observaciones')->nullable(); // Observaciones adicionales
            $table->string('documento')->nullable(); // e.g., recibo, factura
            $table->string('estado')->default('Pendiente'); // e.g., 'pendiente', 'pagada', 'cancelada'
            $table->foreign('contrato_id')->references('id')->on('contratos')->onDelete('set null');
            $table->foreign('empleado_id')->references('id')->on('empleados')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comisiones');
    }
};
