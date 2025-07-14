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
            $table->date('fecha_fin')->nullable();
            $table->decimal('monto_total', 10, 2);
            $table->decimal('monto_inicial', 10, 2)->nullable();
            $table->string('plazo_tipo')->nullable(); // e.g., 'mensual', 'semanal'
            $table->integer('plazo_cantidad')->nullable(); // e.g., 3,6,12 meses o 12,24,32 semanas
            $table->integer('plazo_frencuencia')->nullable(); // e.g., los dias 15,30 o juves viernes
            $table->string('observaciones')->nullable();
            $table->string('documento')->nullable(); // e.g., contrato firmado, acuerdo
            $table->string('estado')->default('activo'); // e.g., 'activo', 'cancelado', 'finalizado'
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
