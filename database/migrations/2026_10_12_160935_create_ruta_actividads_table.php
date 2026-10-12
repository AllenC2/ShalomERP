<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ruta_actividades', function (Blueprint $table) {
            $table->id();
            $table->string('empleado_id', 50);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('ruta_id')->nullable();
            $table->unsignedBigInteger('ruta_parada_id')->nullable();
            $table->unsignedBigInteger('visita_id')->nullable();
            $table->string('tipo', 40);
            $table->string('titulo');
            $table->text('detalle')->nullable();
            $table->decimal('monto_total', 12, 2)->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->foreign('empleado_id')->references('id')->on('empleados')->cascadeOnDelete();
            $table->foreign('ruta_id')->references('id')->on('rutas')->nullOnDelete();
            $table->index(['empleado_id', 'created_at']);
            $table->index('tipo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ruta_actividades');
    }
};
