<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $rows = Schema::hasTable('comisiones') ? DB::table('comisiones')->get() : collect();

            Schema::dropIfExists('comisiones');
            Schema::create('comisiones', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('contrato_id')->nullable();
                $table->string('empleado_id', 50)->nullable();
                $table->unsignedBigInteger('comision_padre_id')->nullable();
                $table->unsignedBigInteger('pago_id')->nullable();
                $table->dateTime('fecha_comision')->nullable();
                $table->string('nombre_paquete')->nullable();
                $table->decimal('porcentaje', 5, 2)->default(0);
                $table->string('tipo_comision')->nullable();
                $table->decimal('monto', 10, 2)->default(0);
                $table->string('observaciones')->nullable();
                $table->string('documento')->nullable();
                $table->string('estado')->default('Pendiente');
                $table->integer('orden')->default(0);
                $table->timestamps();
            });

            $columnas = Schema::getColumnListing('comisiones');
            foreach ($rows as $row) {
                $data = array_intersect_key((array) $row, array_flip($columnas));
                unset($data['id']);
                if ($data !== []) {
                    DB::table('comisiones')->insert($data);
                }
            }

            return;
        }

        Schema::table('comisiones', function (Blueprint $table) {
            if (!Schema::hasColumn('comisiones', 'pago_id')) {
                $table->unsignedBigInteger('pago_id')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('comisiones', function (Blueprint $table) {
            if (Schema::hasColumn('comisiones', 'pago_id')) {
                $table->dropColumn('pago_id');
            }
        });
    }
};
