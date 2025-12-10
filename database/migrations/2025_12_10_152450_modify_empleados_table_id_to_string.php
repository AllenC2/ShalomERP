<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // PASO 1: Deshabilitar verificación de foreign keys temporalmente
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        try {
            // PASO 2: Guardar datos existentes de empleados con mapeo de IDs
            $empleados = DB::table('empleados')->get();
            $idMapping = []; // Mapeo: id_viejo => id_nuevo

            // PASO 3: Eliminar foreign key de comisiones hacia empleados
            // Intentar eliminar la foreign key si existe
            try {
                $foreignKeys = DB::select("
                    SELECT CONSTRAINT_NAME
                    FROM information_schema.TABLE_CONSTRAINTS
                    WHERE TABLE_SCHEMA = DATABASE()
                    AND TABLE_NAME = 'comisiones'
                    AND CONSTRAINT_TYPE = 'FOREIGN KEY'
                    AND CONSTRAINT_NAME LIKE '%empleado_id%'
                ");

                if (!empty($foreignKeys)) {
                    foreach ($foreignKeys as $fk) {
                        DB::statement("ALTER TABLE comisiones DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
                    }
                }
            } catch (\Exception $e) {
                // Si falla, continuar sin foreign key
            }

            // PASO 4: Cambiar empleado_id en comisiones a string temporalmente
            Schema::table('comisiones', function (Blueprint $table) {
                $table->string('empleado_id_temp', 50)->nullable()->after('empleado_id');
            });

            // PASO 5: Copiar datos actuales de comisiones al campo temporal
            DB::statement('UPDATE comisiones SET empleado_id_temp = empleado_id WHERE empleado_id IS NOT NULL');

            // PASO 6: Agregar columna temporal para el nuevo ID
            Schema::table('empleados', function (Blueprint $table) {
                $table->string('id_temp', 50)->nullable()->after('id');
            });

            // PASO 7: Generar nuevos IDs en formato string y actualizar mapeo
            foreach ($empleados as $empleado) {
                $nuevoId = 'EMP-' . str_pad($empleado->id, 4, '0', STR_PAD_LEFT);
                $idMapping[$empleado->id] = $nuevoId;

                DB::table('empleados')
                    ->where('id', $empleado->id)
                    ->update(['id_temp' => $nuevoId]);
            }

            // PASO 8: Modificar la columna id - quitar auto_increment y cambiar a VARCHAR
            // Esto debe hacerse en un solo statement para evitar el error de MySQL
            DB::statement('ALTER TABLE empleados
                DROP PRIMARY KEY,
                MODIFY COLUMN id VARCHAR(50) NOT NULL');

            // PASO 9: Copiar los nuevos IDs de id_temp a id
            DB::statement('UPDATE empleados SET id = id_temp');

            // PASO 10: Eliminar columna temporal
            Schema::table('empleados', function (Blueprint $table) {
                $table->dropColumn('id_temp');
            });

            // PASO 11: Restablecer primary key
            Schema::table('empleados', function (Blueprint $table) {
                $table->primary('id');
            });

            // PASO 12: Actualizar referencias en comisiones usando el mapeo
            foreach ($idMapping as $oldId => $newId) {
                DB::table('comisiones')
                    ->where('empleado_id_temp', (string)$oldId)
                    ->update(['empleado_id_temp' => $newId]);
            }

            // PASO 13: Cambiar empleado_id en comisiones a string
            DB::statement('ALTER TABLE comisiones MODIFY COLUMN empleado_id VARCHAR(50)');

            // PASO 14: Copiar datos del campo temporal al campo original
            DB::statement('UPDATE comisiones SET empleado_id = empleado_id_temp WHERE empleado_id_temp IS NOT NULL');

            // PASO 15: Eliminar columna temporal
            Schema::table('comisiones', function (Blueprint $table) {
                $table->dropColumn('empleado_id_temp');
            });

            // PASO 16: Recrear foreign key con el nuevo tipo
            Schema::table('comisiones', function (Blueprint $table) {
                $table->foreign('empleado_id')->references('id')->on('empleados')->onDelete('set null');
            });

        } finally {
            // PASO 17: Rehabilitar verificación de foreign keys
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // IMPORTANTE: El rollback es complejo y puede causar pérdida de datos
        // Se recomienda hacer un backup antes de ejecutar esta migración

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        try {
            // Eliminar foreign key si existe
            try {
                $foreignKeys = DB::select("
                    SELECT CONSTRAINT_NAME
                    FROM information_schema.TABLE_CONSTRAINTS
                    WHERE TABLE_SCHEMA = DATABASE()
                    AND TABLE_NAME = 'comisiones'
                    AND CONSTRAINT_TYPE = 'FOREIGN KEY'
                    AND CONSTRAINT_NAME LIKE '%empleado_id%'
                ");

                if (!empty($foreignKeys)) {
                    foreach ($foreignKeys as $fk) {
                        DB::statement("ALTER TABLE comisiones DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
                    }
                }
            } catch (\Exception $e) {
                // Continuar si falla
            }

            // Cambiar empleado_id en comisiones de vuelta a unsignedBigInteger
            DB::statement('ALTER TABLE comisiones MODIFY COLUMN empleado_id BIGINT UNSIGNED');

            // Cambiar id en empleados de vuelta a bigint autoincremental
            Schema::table('empleados', function (Blueprint $table) {
                $table->dropPrimary();
            });

            DB::statement('ALTER TABLE empleados MODIFY COLUMN id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY');

            // Recrear foreign key
            Schema::table('comisiones', function (Blueprint $table) {
                $table->foreign('empleado_id')->references('id')->on('empleados')->onDelete('set null');
            });

        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }
};
