<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('rutas')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            // Incluir ambos valores temporalmente para poder migrar filas existentes.
            DB::statement("ALTER TABLE rutas MODIFY COLUMN estado ENUM('planeada','en_curso','completada','cancelada','detenida','vencida','incompleta') NOT NULL DEFAULT 'planeada'");
            DB::table('rutas')->where('estado', 'cancelada')->update(['estado' => 'detenida']);
            DB::statement("ALTER TABLE rutas MODIFY COLUMN estado ENUM('planeada','en_curso','completada','detenida','vencida','incompleta') NOT NULL DEFAULT 'planeada'");

            return;
        }

        DB::table('rutas')->where('estado', 'cancelada')->update(['estado' => 'detenida']);
    }

    public function down(): void
    {
        if (! Schema::hasTable('rutas')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE rutas MODIFY COLUMN estado ENUM('planeada','en_curso','completada','cancelada','detenida','vencida','incompleta') NOT NULL DEFAULT 'planeada'");
            DB::table('rutas')->where('estado', 'detenida')->update(['estado' => 'cancelada']);
            DB::statement("ALTER TABLE rutas MODIFY COLUMN estado ENUM('planeada','en_curso','completada','cancelada','vencida','incompleta') NOT NULL DEFAULT 'planeada'");

            return;
        }

        DB::table('rutas')->where('estado', 'detenida')->update(['estado' => 'cancelada']);
    }
};
