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
        Schema::table('porcentajes', function (Blueprint $table) {
            $table->string('modo_comision')->default('porcentaje')->after('paquete_id'); // 'porcentaje' o 'monto'
            $table->decimal('monto_fijo', 12, 2)->nullable()->after('cantidad_porcentaje');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('porcentajes', function (Blueprint $table) {
            $table->dropColumn(['modo_comision', 'monto_fijo']);
        });
    }
};
