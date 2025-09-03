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
            $table->unsignedBigInteger('comision_padre_id')->nullable(); // Relación con comisión padre para parcialidades

            $table->datetime('fecha_comision')->default(DB::raw('CURRENT_TIMESTAMP')); // Fecha en que se generó
            $table->string('nombre_paquete'); // Nombre del paquete
            $table->decimal('porcentaje', 5, 2)->default(0); // Porcentaje de la comisión
            $table->string('tipo_comision')->nullable(); // e.g., 'venta', 'renovación', 'referencia'
            $table->decimal('monto', 10, 2)->default(0); // Monto de la comisión

            $table->string('observaciones')->nullable(); // Observaciones adicionales
            $table->string('documento')->nullable(); // e.g., recibo, factura
            $table->string('estado')->default('Pendiente'); // e.g., 'pendiente', 'pagada', 'cancelada'

            $table->foreign('contrato_id')->references('id')->on('contratos')->onDelete('set null');
            $table->foreign('empleado_id')->references('id')->on('empleados')->onDelete('set null');
            $table->foreign('comision_padre_id')->references('id')->on('comisiones')->onDelete('cascade');
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
