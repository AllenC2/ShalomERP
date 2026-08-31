<?php

namespace App\Services;

use App\Models\Contrato;
use App\Models\Empleado;
use App\Models\Ruta;
use App\Models\RutaParada;
use Illuminate\Support\Facades\DB;

class GeneradorRutasService
{
    protected GeocodingService $geocoding;

    public function __construct(GeocodingService $geocoding)
    {
        $this->geocoding = $geocoding;
    }

    public function generar(array $contratoIds, string $empleadoId, string $fecha, string $fechaLimite, ?string $notas = null, ?int $userId = null, bool $respetarOrden = true): Ruta
    {
        return DB::transaction(function () use ($contratoIds, $empleadoId, $fecha, $fechaLimite, $notas, $userId, $respetarOrden) {
            $empleado = Empleado::findOrFail($empleadoId);

            if (!$empleado->tiene_coordenadas && $empleado->domicilio) {
                $coords = $this->geocoding->geocode($empleado->domicilio);
                if ($coords) {
                    $empleado->update([
                        'latitud' => $coords['lat'],
                        'longitud' => $coords['lng'],
                    ]);
                }
            }

            $ruta = Ruta::create([
                'empleado_id' => $empleadoId,
                'fecha' => $fecha,
                'fecha_limite' => $fechaLimite,
                'estado' => Ruta::ESTADO_PLANEADA,
                'notas' => $notas,
                'user_id' => $userId,
            ]);

            $paradasData = [];
            foreach ($contratoIds as $index => $contratoId) {
                $contrato = Contrato::with('cliente')->findOrFail($contratoId);
                $cliente = $contrato->cliente;

                if (!$cliente->tiene_coordenadas) {
                    $coords = $this->geocoding->geocode($cliente->domicilio_completo);
                    if ($coords) {
                        $cliente->update([
                            'latitud' => $coords['lat'],
                            'longitud' => $coords['lng'],
                        ]);
                    }
                }

                $paradasData[] = [
                    'ruta_id' => $ruta->id,
                    'orden' => $index + 1,
                    'contrato_id' => $contrato->id,
                    'cliente_id' => $cliente->id,
                    'direccion_destino' => $cliente->domicilio_completo,
                    'latitud' => $cliente->latitud,
                    'longitud' => $cliente->longitud,
                    'estado' => RutaParada::ESTADO_PENDIENTE,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            $paradasOrdenadas = $respetarOrden
                ? $paradasData
                : $this->ordenarPorCercania($paradasData, $empleado);

            foreach ($paradasOrdenadas as $orden => $parada) {
                $parada['orden'] = $orden + 1;
                RutaParada::create($parada);
            }

            return $ruta->load('paradas.contrato.cliente', 'empleado');
        });
    }

    protected function ordenarPorCercania(array $paradas, Empleado $empleado): array
    {
        $puntoInicio = $this->obtenerPuntoInicio($empleado);

        if (!$puntoInicio) {
            return $paradas;
        }

        $conCoordenadas = [];
        $sinCoordenadas = [];

        foreach ($paradas as $parada) {
            if ($parada['latitud'] && $parada['longitud']) {
                $conCoordenadas[] = $parada;
            } else {
                $sinCoordenadas[] = $parada;
            }
        }

        if (empty($conCoordenadas)) {
            return $paradas;
        }

        $ordenadas = [];
        $actual = $puntoInicio;

        while (!empty($conCoordenadas)) {
            $indiceMasCercano = null;
            $distanciaMinima = PHP_FLOAT_MAX;

            foreach ($conCoordenadas as $index => $parada) {
                $distancia = $this->distanciaHaversine(
                    $actual['lat'], $actual['lng'],
                    $parada['latitud'], $parada['longitud']
                );

                if ($distancia < $distanciaMinima) {
                    $distanciaMinima = $distancia;
                    $indiceMasCercano = $index;
                }
            }

            $paradaCercana = $conCoordenadas[$indiceMasCercano];
            $ordenadas[] = $paradaCercana;
            $actual = ['lat' => $paradaCercana['latitud'], 'lng' => $paradaCercana['longitud']];
            unset($conCoordenadas[$indiceMasCercano]);
            $conCoordenadas = array_values($conCoordenadas);
        }

        return array_merge($ordenadas, $sinCoordenadas);
    }

    protected function obtenerPuntoInicio(Empleado $empleado): ?array
    {
        if ($empleado->latitud && $empleado->longitud) {
            return ['lat' => $empleado->latitud, 'lng' => $empleado->longitud];
        }

        return null;
    }

    protected function distanciaHaversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $radioTierra = 6371;

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $radioTierra * $c;
    }
}
