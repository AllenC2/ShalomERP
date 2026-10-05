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
        Schema::create('rutas', function (Blueprint $table) {
            $table->id();
            $table->string('empleado_id', 50);
            $table->foreign('empleado_id')->references('id')->on('empleados')->onDelete('cascade');
            $table->date('fecha');
            $table->date('fecha_limite');
            $table->enum('estado', ['planeada', 'en_curso', 'completada', 'detenida'])->default('planeada');
            $table->text('notas')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rutas');
    }
};
