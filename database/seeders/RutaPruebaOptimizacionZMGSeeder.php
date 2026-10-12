<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\Contrato;
use App\Models\Empleado;
use App\Models\Paquete;
use App\Models\RutaPlantilla;
use App\Models\User;
use App\Services\GeneradorRutasService;
use App\Services\OptimizadorOrdenRutaService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Crea 12 domicilios en la ZMG con coordenadas conocidas y una ruta de prueba
 * para validar el ordenamiento por vecino más cercano.
 *
 * Uso: php artisan db:seed --class=RutaPruebaOptimizacionZMGSeeder
 */
class RutaPruebaOptimizacionZMGSeeder extends Seeder
{
    public function run(): void
    {
        $empleado = Empleado::find('marianis1')
            ?? Empleado::where('nombre', 'like', '%Mariana%')->first();
        if (! $empleado) {
            $this->command?->error('No existe el empleado Mariana (marianis1).');
            return;
        }

        // Asegura domicilio/coords en Arcos Vallarta si faltan.
        if (! $empleado->domicilio || ! $empleado->latitud || ! $empleado->longitud) {
            $empleado->update([
                'domicilio' => 'Av. Revolución #789, Col. Arcos Vallarta, Guadalajara, Jalisco',
                'latitud' => 20.65633783,
                'longitud' => -103.3915,
            ]);
        }

        $paqueteId = Paquete::query()->value('id') ?? 1;
        $adminId = User::where('role', 'admin')->value('id');

        // Domicilios reales de la ZMG con coords aproximadas.
        // Orden intencional "malo" (lejos → cerca → saltos) para que el optimizador reordene.
        $domicilios = [
            [
                'nombre' => 'Test ZMG',
                'apellido' => '01 Tonala Centro',
                'colonia' => 'Centro',
                'municipio' => 'Tonalá',
                'calle_y_numero' => 'Calle Juárez #100',
                'latitud' => 20.6242,
                'longitud' => -103.2343,
            ],
            [
                'nombre' => 'Test ZMG',
                'apellido' => '02 Zapopan Basilica',
                'colonia' => 'Centro',
                'municipio' => 'Zapopan',
                'calle_y_numero' => 'Av. Hidalgo #50',
                'latitud' => 20.7214,
                'longitud' => -103.3910,
            ],
            [
                'nombre' => 'Test ZMG',
                'apellido' => '03 Tlaquepaque Centro',
                'colonia' => 'Centro',
                'municipio' => 'San Pedro Tlaquepaque',
                'calle_y_numero' => 'Calle Independencia #200',
                'latitud' => 20.6407,
                'longitud' => -103.3116,
            ],
            [
                'nombre' => 'Test ZMG',
                'apellido' => '04 Andares',
                'colonia' => 'Puerta de Hierro',
                'municipio' => 'Zapopan',
                'calle_y_numero' => 'Blvd. Puerta de Hierro #4965',
                'latitud' => 20.7080,
                'longitud' => -103.4120,
            ],
            [
                'nombre' => 'Test ZMG',
                'apellido' => '05 Centro GDL',
                'colonia' => 'Centro Histórico',
                'municipio' => 'Guadalajara',
                'calle_y_numero' => 'Av. Hidalgo #400',
                'latitud' => 20.6767,
                'longitud' => -103.3470,
            ],
            [
                'nombre' => 'Test ZMG',
                'apellido' => '06 Plaza del Sol',
                'colonia' => 'Ciudad del Sol',
                'municipio' => 'Zapopan',
                'calle_y_numero' => 'Av. López Mateos Sur #2375',
                'latitud' => 20.6500,
                'longitud' => -103.3990,
            ],
            [
                'nombre' => 'Test ZMG',
                'apellido' => '07 Minerva',
                'colonia' => 'Minerva',
                'municipio' => 'Guadalajara',
                'calle_y_numero' => 'Av. López Mateos Nte #100',
                'latitud' => 20.6745,
                'longitud' => -103.3870,
            ],
            [
                'nombre' => 'Test ZMG',
                'apellido' => '08 Oblatos',
                'colonia' => 'Oblatos',
                'municipio' => 'Guadalajara',
                'calle_y_numero' => 'Calle Circunvalación Oblatos #1200',
                'latitud' => 20.6960,
                'longitud' => -103.3100,
            ],
            [
                'nombre' => 'Test ZMG',
                'apellido' => '09 Chapalita',
                'colonia' => 'Chapalita',
                'municipio' => 'Guadalajara',
                'calle_y_numero' => 'Av. Guadalupe #1500',
                'latitud' => 20.6660,
                'longitud' => -103.4010,
            ],
            [
                'nombre' => 'Test ZMG',
                'apellido' => '10 Providencia',
                'colonia' => 'Providencia',
                'municipio' => 'Guadalajara',
                'calle_y_numero' => 'Av. Americas #1500',
                'latitud' => 20.6980,
                'longitud' => -103.3800,
            ],
            [
                'nombre' => 'Test ZMG',
                'apellido' => '11 Estadio Jalisco',
                'colonia' => 'Independencia',
                'municipio' => 'Guadalajara',
                'calle_y_numero' => 'Calle 7 Colón #2375',
                'latitud' => 20.7050,
                'longitud' => -103.3280,
            ],
            [
                'nombre' => 'Test ZMG',
                'apellido' => '12 Patria Sur',
                'colonia' => 'Ciudad del Sol',
                'municipio' => 'Zapopan',
                'calle_y_numero' => 'Av. Patria #1800',
                'latitud' => 20.6350,
                'longitud' => -103.3800,
            ],
        ];

        $contratoIds = [];
        $ordenIngreso = [];

        DB::transaction(function () use ($domicilios, $paqueteId, &$contratoIds, &$ordenIngreso) {
            // Limpia corridas previas de esta prueba.
            Cliente::where('email', 'like', 'test.zmg.%@shalom.test')->each(function (Cliente $cliente) {
                $contratoIdsOld = $cliente->contratos()->pluck('id');
                if ($contratoIdsOld->isNotEmpty()) {
                    DB::table('ruta_paradas')->whereIn('contrato_id', $contratoIdsOld)->delete();
                    DB::table('ruta_plantilla_paradas')->whereIn('contrato_id', $contratoIdsOld)->delete();
                    Contrato::whereIn('id', $contratoIdsOld)->delete();
                }
                $cliente->delete();
            });

            RutaPlantilla::where('nombre', 'Prueba Optimización ZMG')->each(function (RutaPlantilla $p) {
                $p->ejecuciones()->each(function ($ruta) {
                    $ruta->paradas()->delete();
                    $ruta->delete();
                });
                $p->paradas()->delete();
                $p->delete();
            });

            foreach ($domicilios as $i => $d) {
                $n = $i + 1;
                $cliente = Cliente::create([
                    'nombre' => $d['nombre'],
                    'apellido' => $d['apellido'],
                    'email' => sprintf('test.zmg.%02d@shalom.test', $n),
                    'telefono' => sprintf('33300000%02d', $n),
                    'calle_y_numero' => $d['calle_y_numero'],
                    'cruces' => null,
                    'colonia' => $d['colonia'],
                    'municipio' => $d['municipio'],
                    'estado' => 'Jalisco',
                    'codigo_postal' => '45000',
                    'latitud' => $d['latitud'],
                    'longitud' => $d['longitud'],
                ]);

                $contrato = Contrato::create([
                    'cliente_id' => $cliente->id,
                    'paquete_id' => $paqueteId,
                    'fecha_inicio' => Carbon::today()->toDateString(),
                    'fecha_fin' => Carbon::today()->addYear()->toDateString(),
                    'monto_total' => 12000,
                    'monto_inicial' => 0,
                    'monto_bonificacion' => 0,
                    'numero_cuotas' => 12,
                    'frecuencia_cuotas' => 30,
                    'monto_cuota' => 1000,
                    'estado' => Contrato::ESTADO_ACTIVO,
                    'observaciones' => 'Contrato de prueba ZMG para optimización de orden',
                ]);

                $contratoIds[] = $contrato->id;
                $ordenIngreso[] = [
                    'contrato_id' => $contrato->id,
                    'label' => $d['apellido'],
                    'lat' => $d['latitud'],
                    'lng' => $d['longitud'],
                ];
            }
        });

        $this->command?->info('Cobradora '.$empleado->id.' ('.$empleado->nombre.'): '.$empleado->latitud.', '.$empleado->longitud);
        $this->command?->warn('Orden de ingreso (desordenado a propósito):');
        foreach ($ordenIngreso as $idx => $item) {
            $this->command?->line(sprintf(
                '  %2d) contrato #%d  %s  (%.4f, %.4f)',
                $idx + 1,
                $item['contrato_id'],
                $item['label'],
                $item['lat'],
                $item['lng']
            ));
        }

        /** @var GeneradorRutasService $generador */
        $generador = app(GeneradorRutasService::class);

        $ruta = $generador->crearPlantillaEInstancia([
            'nombre' => 'Prueba Optimización ZMG',
            'empleado_id' => $empleado->id,
            'frecuencia' => RutaPlantilla::FRECUENCIA_DIARIA,
            'fecha' => Carbon::today()->toDateString(),
            'punto_casa' => 'inicio',
            'contratos' => $contratoIds,
            'notas' => 'Ruta de prueba: 12 domicilios ZMG para validar nearest-neighbor',
            'user_id' => $adminId,
        ]);

        $ruta->load(['paradas.cliente', 'empleado']);
        $optimizador = app(OptimizadorOrdenRutaService::class);
        $originLat = (float) $empleado->latitud;
        $originLng = (float) $empleado->longitud;

        $this->command?->newLine();
        $this->command?->info("Ruta #{$ruta->id} creada — orden final (casa → vecino más cercano):");

        $prevLat = $originLat;
        $prevLng = $originLng;
        $distTotal = 0.0;

        foreach ($ruta->paradas->sortBy('orden') as $parada) {
            $lat = (float) ($parada->latitud ?? $parada->cliente?->latitud);
            $lng = (float) ($parada->longitud ?? $parada->cliente?->longitud);
            $d = $optimizador->distanciaKm($prevLat, $prevLng, $lat, $lng);
            $distTotal += $d;
            $this->command?->line(sprintf(
                '  #%d  contrato #%d  %s  +%.2f km desde anterior',
                $parada->orden,
                $parada->contrato_id,
                $parada->cliente?->apellido ?? '—',
                $d
            ));
            $prevLat = $lat;
            $prevLng = $lng;
        }

        $this->command?->newLine();
        $this->command?->info(sprintf('Distancia acumulada NN: %.2f km', $distTotal));
        $this->command?->info('Ábrela en /rutas/'.$ruta->id.' o inicia sesión como Mariana (mariana@gmail.com).');
    }
}
