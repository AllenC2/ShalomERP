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
        Schema::create('contratos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cliente_id');
            $table->unsignedBigInteger('empleado_id')->nullable();
            $table->unsignedBigInteger('paquete_id')->nullable(); // Paquete asociado al contrato

            $table->date('fecha_inicio');
            $table->date('fecha_fin');

            $table->decimal('monto_inicial', 10, 2)->default(0);
            $table->decimal('monto_bonificacion', 10, 2)->default(0);
            $table->decimal('monto_total', 10, 2)->default(0);

            $table->integer('numero_cuotas')->default(12); // e.g., 3,6,12 meses o 12,24,32 semanas
            $table->integer('frecuencia_cuotas')->default(7); // e.g., cada 7,15,30 días
            $table->decimal('monto_cuota', 10, 2)->default(0); // Monto de cada cuota

            $table->string('observaciones')->nullable();
            $table->string('documento')->nullable(); // e.g., contrato firmado, acuerdo
            $table->enum('estado', ['activo', 'cancelado', 'finalizado', 'suspendido'])->default('activo');

            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('cascade');
            $table->foreign('empleado_id')->references('id')->on('empleados')->onDelete('set null');
            $table->foreign('paquete_id')->references('id')->on('paquetes')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contratos');
    }
};
