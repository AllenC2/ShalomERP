<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visitas', function (Blueprint $table) {
            if (! Schema::hasColumn('visitas', 'lote')) {
                $table->uuid('lote')->nullable()->index()->after('ruta_parada_id');
            }
            if (! Schema::hasColumn('visitas', 'en_domicilio')) {
                $table->boolean('en_domicilio')->nullable()->after('lote');
            }
            if (! Schema::hasColumn('visitas', 'recibido')) {
                $table->boolean('recibido')->default(false)->after('en_domicilio');
            }
            if (! Schema::hasColumn('visitas', 'receptor_nombre')) {
                $table->string('receptor_nombre')->nullable()->after('recibido');
            }
            if (! Schema::hasColumn('visitas', 'receptor_parentesco')) {
                $table->string('receptor_parentesco', 40)->nullable()->after('receptor_nombre');
            }
        });

        if (! Schema::hasColumn('pagos', 'visita_id')) {
            // MySQL STRICT + default 0000-00-00 en fecha_pago rompe ALTER TABLE.
            if (Schema::getConnection()->getDriverName() === 'mysql') {
                DB::statement("UPDATE pagos SET fecha_pago = NOW() WHERE fecha_pago IS NULL OR fecha_pago = '0000-00-00 00:00:00'");
                DB::statement('ALTER TABLE pagos MODIFY fecha_pago DATETIME NULL DEFAULT NULL');
                DB::statement('ALTER TABLE pagos ADD visita_id BIGINT UNSIGNED NULL AFTER contrato_id');
                DB::statement('ALTER TABLE pagos ADD CONSTRAINT pagos_visita_id_foreign FOREIGN KEY (visita_id) REFERENCES visitas(id) ON DELETE SET NULL');
            } else {
                Schema::table('pagos', function (Blueprint $table) {
                    $table->foreignId('visita_id')->nullable()->after('contrato_id')->constrained('visitas')->nullOnDelete();
                });
            }
        }

        if (Schema::hasTable('empleados') && ! Schema::hasColumn('empleados', 'user_id')) {
            Schema::table('empleados', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('pagos', 'visita_id')) {
            if (Schema::getConnection()->getDriverName() === 'mysql') {
                try {
                    DB::statement('ALTER TABLE pagos DROP FOREIGN KEY pagos_visita_id_foreign');
                } catch (\Throwable $e) {
                    // ignore if constraint name differs
                }
                Schema::table('pagos', function (Blueprint $table) {
                    $table->dropColumn('visita_id');
                });
            } else {
                Schema::table('pagos', function (Blueprint $table) {
                    $table->dropConstrainedForeignId('visita_id');
                });
            }
        }

        Schema::table('visitas', function (Blueprint $table) {
            foreach (['receptor_parentesco', 'receptor_nombre', 'recibido', 'en_domicilio', 'lote'] as $col) {
                if (Schema::hasColumn('visitas', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
