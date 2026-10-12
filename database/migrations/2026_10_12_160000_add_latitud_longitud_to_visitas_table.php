<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visitas', function (Blueprint $table) {
            if (! Schema::hasColumn('visitas', 'latitud')) {
                $table->decimal('latitud', 10, 7)->nullable();
            }
            if (! Schema::hasColumn('visitas', 'longitud')) {
                $table->decimal('longitud', 10, 7)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('visitas', function (Blueprint $table) {
            if (Schema::hasColumn('visitas', 'longitud')) {
                $table->dropColumn('longitud');
            }
            if (Schema::hasColumn('visitas', 'latitud')) {
                $table->dropColumn('latitud');
            }
        });
    }
};
