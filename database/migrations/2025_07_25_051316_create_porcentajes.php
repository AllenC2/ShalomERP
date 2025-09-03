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
        Schema::create('porcentajes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('paquete_id')->nullable(); // Relación con el paquete
            $table->decimal('cantidad_porcentaje', 5, 2)->default(0); // Porcentaje del total paquete
            $table->string('tipo_porcentaje')->nullable(); // e.g., 'asesor', 'lider', 'gerente'
            $table->string('observaciones')->nullable(); // Observaciones adicionales
            $table->foreign('paquete_id')->references('id')->on('paquetes')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('porcentajes');
    }
};
