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
        Schema::table('visitas', function (Blueprint $table) {
            if (!Schema::hasColumn('visitas', 'ruta_parada_id')) {
                $table->unsignedBigInteger('ruta_parada_id')->nullable()->after('user_id');
            }
            $table->foreign('ruta_parada_id')->references('id')->on('ruta_paradas')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visitas', function (Blueprint $table) {
            $table->dropForeign(['ruta_parada_id']);
            $table->dropColumn('ruta_parada_id');
        });
    }
};
