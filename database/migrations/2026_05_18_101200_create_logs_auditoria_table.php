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
        Schema::create('logs_auditoria', function (Blueprint $table) {
            $table->id();
            $table->string('tabla_nombre');
            $table->unsignedBigInteger('registro_id');
            $table->string('accion'); // 'INSERT', 'UPDATE', 'DELETE'
            $table->json('estado_anterior')->nullable();
            $table->json('estado_nuevo')->nullable();
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->string('ip_direccion', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();

            // Índices optimizados para búsquedas combinadas e individuales
            $table->index(['tabla_nombre', 'registro_id']);
            $table->index('usuario_id');
            
            // Relación forense con usuarios (sin restricción rígida de borrado)
            $table->foreign('usuario_id')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logs_auditoria');
    }
};
