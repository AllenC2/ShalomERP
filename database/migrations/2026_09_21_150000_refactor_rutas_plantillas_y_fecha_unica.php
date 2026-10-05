<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('ruta_plantillas')) {
            Schema::create('ruta_plantillas', function (Blueprint $table) {
                $table->id();
                $table->string('nombre');
                $table->string('empleado_id', 50);
                $table->foreign('empleado_id')->references('id')->on('empleados')->onDelete('cascade');
                $table->string('frecuencia', 20);
                $table->unsignedTinyInteger('dia_semana')->nullable();
                $table->unsignedTinyInteger('dia_mes')->nullable();
                $table->string('punto_casa', 20)->default('inicio');
                $table->boolean('activa')->default(true);
                $table->unsignedBigInteger('user_id')->nullable();
                $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('ruta_plantilla_paradas')) {
            Schema::create('ruta_plantilla_paradas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('plantilla_id')->constrained('ruta_plantillas')->onDelete('cascade');
                $table->integer('orden')->default(0);
                $table->unsignedBigInteger('contrato_id');
                $table->foreign('contrato_id')->references('id')->on('contratos')->onDelete('cascade');
                $table->unsignedBigInteger('cliente_id');
                $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('cascade');
                $table->timestamps();
            });
        }

        Schema::table('rutas', function (Blueprint $table) {
            if (!Schema::hasColumn('rutas', 'plantilla_id')) {
                $table->foreignId('plantilla_id')->nullable()->after('id')->constrained('ruta_plantillas')->onDelete('cascade');
            }
            if (!Schema::hasColumn('rutas', 'nombre')) {
                $table->string('nombre')->nullable()->after('plantilla_id');
            }
        });

        $rutas = DB::table('rutas')->whereNull('plantilla_id')->orderBy('id')->get();
        foreach ($rutas as $ruta) {
            $nombre = 'Ruta #' . $ruta->id;
            $plantillaId = DB::table('ruta_plantillas')->insertGetId([
                'nombre' => $nombre,
                'empleado_id' => $ruta->empleado_id,
                'frecuencia' => 'diaria',
                'dia_semana' => null,
                'dia_mes' => null,
                'punto_casa' => $ruta->punto_casa ?? 'inicio',
                'activa' => false,
                'user_id' => $ruta->user_id,
                'created_at' => $ruta->created_at,
                'updated_at' => $ruta->updated_at,
            ]);

            $paradas = DB::table('ruta_paradas')->where('ruta_id', $ruta->id)->orderBy('orden')->get();
            foreach ($paradas as $parada) {
                DB::table('ruta_plantilla_paradas')->insert([
                    'plantilla_id' => $plantillaId,
                    'orden' => $parada->orden,
                    'contrato_id' => $parada->contrato_id,
                    'cliente_id' => $parada->cliente_id,
                    'created_at' => $parada->created_at,
                    'updated_at' => $parada->updated_at,
                ]);
            }

            DB::table('rutas')->where('id', $ruta->id)->update([
                'plantilla_id' => $plantillaId,
                'nombre' => $nombre,
            ]);
        }

        $hasUnique = collect(Schema::getIndexes('rutas'))->contains(function ($index) {
            return ($index['name'] ?? '') === 'rutas_plantilla_id_fecha_unique';
        });
        if (!$hasUnique) {
            Schema::table('rutas', function (Blueprint $table) {
                $table->unique(['plantilla_id', 'fecha']);
            });
        }

        Schema::table('rutas', function (Blueprint $table) {
            if (Schema::hasColumn('rutas', 'fecha_limite')) {
                $table->dropColumn('fecha_limite');
            }
        });

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE rutas MODIFY COLUMN estado ENUM('planeada','en_curso','completada','detenida','vencida','incompleta') NOT NULL DEFAULT 'planeada'");
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE rutas MODIFY COLUMN estado ENUM('planeada','en_curso','completada','detenida') NOT NULL DEFAULT 'planeada'");
        }

        Schema::table('rutas', function (Blueprint $table) {
            $table->date('fecha_limite')->nullable()->after('fecha');
        });

        DB::table('rutas')->update(['fecha_limite' => DB::raw('fecha')]);

        Schema::table('rutas', function (Blueprint $table) {
            $table->dropForeign(['plantilla_id']);
            $table->dropColumn(['plantilla_id', 'nombre']);
        });

        Schema::dropIfExists('ruta_plantilla_paradas');
        Schema::dropIfExists('ruta_plantillas');
    }
};
