<?php

namespace App\Services;

use App\Models\Contrato;
use App\Models\Empleado;
use App\Models\Ruta;
use App\Models\RutaParada;
use App\Models\RutaPlantilla;
use App\Models\RutaPlantillaParada;
use Illuminate\Support\Collection;

class OptimizadorOrdenRutaService
{
    /**
     * Ordena IDs de contrato por vecino más cercano (agrupando por cliente).
     * Con punto_casa=final invierte el resultado (casa al final).
     *
     * @param  array<int, int|string>  $contratoIds
     * @return array<int, int>
     */
    public function ordenarContratosPorCercania(array $contratoIds, ?Empleado $empleado = null, string $puntoCasa = 'inicio'): array
    {
        $ids = array_values(array_unique(array_map('intval', $contratoIds)));
        if (count($ids) <= 1) {
            return $ids;
        }

        $contratos = Contrato::with('cliente')
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');

        $items = collect($ids)->map(function (int $id) use ($contratos) {
            $contrato = $contratos->get($id);
            $cliente = $contrato?->cliente;

            return [
                'id' => $id,
                'group_key' => (string) ($contrato?->cliente_id ?: ('c_' . $id)),
                'lat' => $cliente?->latitud !== null ? (float) $cliente->latitud : null,
                'lng' => $cliente?->longitud !== null ? (float) $cliente->longitud : null,
            ];
        });

        $originLat = $empleado?->latitud !== null ? (float) $empleado->latitud : null;
        $originLng = $empleado?->longitud !== null ? (float) $empleado->longitud : null;

        $ordenados = $this->ordenarItemsPorCercania($items, $originLat, $originLng);
        $ordenados = $this->aplicarModoPuntoCasa($ordenados, $puntoCasa);

        return $ordenados->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    /**
     * Reordena las paradas de una ruta por vecino más cercano.
     * En rutas en curso, conserva visitadas/omitidas y solo optimiza pendientes.
     */
    public function optimizarRuta(Ruta $ruta, bool $sincronizarPlantilla = true): int
    {
        $ruta->loadMissing(['paradas.cliente', 'empleado', 'plantilla.paradas']);

        $paradas = $ruta->paradas->sortBy('orden')->values();
        if ($paradas->count() <= 1) {
            return 0;
        }

        $fijas = $paradas->filter(fn (RutaParada $p) => in_array($p->estado, [
            RutaParada::ESTADO_VISITADA,
            RutaParada::ESTADO_OMITIDA,
        ], true))->values();

        $pendientes = $paradas->filter(fn (RutaParada $p) => $p->estado === RutaParada::ESTADO_PENDIENTE)->values();

        if ($pendientes->isEmpty()) {
            return 0;
        }

        $originLat = null;
        $originLng = null;

        if ($fijas->isNotEmpty()) {
            $ultima = $fijas->last();
            $originLat = $ultima->latitud !== null ? (float) $ultima->latitud : null;
            $originLng = $ultima->longitud !== null ? (float) $ultima->longitud : null;
            if (($originLat === null || $originLng === null) && $ultima->cliente) {
                $originLat = $ultima->cliente->latitud !== null ? (float) $ultima->cliente->latitud : null;
                $originLng = $ultima->cliente->longitud !== null ? (float) $ultima->cliente->longitud : null;
            }
        }

        if (($originLat === null || $originLng === null) && $ruta->empleado) {
            $originLat = $ruta->empleado->latitud !== null ? (float) $ruta->empleado->latitud : null;
            $originLng = $ruta->empleado->longitud !== null ? (float) $ruta->empleado->longitud : null;
        }

        $items = $pendientes->map(function (RutaParada $p) {
            $lat = $p->latitud;
            $lng = $p->longitud;
            if (($lat === null || $lng === null) && $p->cliente) {
                $lat = $p->cliente->latitud;
                $lng = $p->cliente->longitud;
            }

            return [
                'id' => $p->id,
                'parada' => $p,
                'group_key' => (string) ($p->cliente_id ?: ('p_' . $p->id)),
                'lat' => $lat !== null ? (float) $lat : null,
                'lng' => $lng !== null ? (float) $lng : null,
            ];
        });

        $pendientesOrdenados = $this->ordenarItemsPorCercania($items, $originLat, $originLng);

        // Solo invertir desde casa: si ya hay visitadas/omitidas, seguir NN desde la última fija.
        if ($fijas->isEmpty()) {
            $pendientesOrdenados = $this->aplicarModoPuntoCasa(
                $pendientesOrdenados,
                $ruta->punto_casa ?? 'inicio'
            );
        }

        $nuevoOrden = $fijas->concat($pendientesOrdenados->pluck('parada'))->values();
        $cambios = 0;

        foreach ($nuevoOrden as $index => $parada) {
            $orden = $index + 1;
            if ((int) $parada->orden !== $orden) {
                $parada->update(['orden' => $orden]);
                $cambios++;
            }
        }

        if ($sincronizarPlantilla && $ruta->plantilla) {
            $this->sincronizarOrdenPlantillaDesdeRuta($ruta->fresh('paradas'), $ruta->plantilla);
        }

        return $cambios;
    }

    public function optimizarPlantilla(RutaPlantilla $plantilla): int
    {
        $plantilla->loadMissing(['paradas.contrato.cliente', 'empleado']);
        $paradas = $plantilla->paradas->sortBy('orden')->values();
        if ($paradas->count() <= 1) {
            return 0;
        }

        $items = $paradas->map(function (RutaPlantillaParada $p) {
            $cliente = $p->contrato?->cliente;

            return [
                'id' => $p->id,
                'parada' => $p,
                'group_key' => (string) ($p->cliente_id ?: ('pp_' . $p->id)),
                'lat' => $cliente?->latitud !== null ? (float) $cliente->latitud : null,
                'lng' => $cliente?->longitud !== null ? (float) $cliente->longitud : null,
            ];
        });

        $originLat = $plantilla->empleado?->latitud !== null ? (float) $plantilla->empleado->latitud : null;
        $originLng = $plantilla->empleado?->longitud !== null ? (float) $plantilla->empleado->longitud : null;

        $ordenados = $this->aplicarModoPuntoCasa(
            $this->ordenarItemsPorCercania($items, $originLat, $originLng),
            $plantilla->punto_casa ?? 'inicio'
        );
        $cambios = 0;

        foreach ($ordenados as $index => $item) {
            $orden = $index + 1;
            /** @var RutaPlantillaParada $parada */
            $parada = $item['parada'];
            if ((int) $parada->orden !== $orden) {
                $parada->update(['orden' => $orden]);
                $cambios++;
            }
        }

        return $cambios;
    }

    /**
     * Nearest-neighbor: agrupa por domicilio/cliente y elige el grupo más cercano al punto actual.
     *
     * @param  Collection<int, array{id:mixed,group_key:string,lat:?float,lng:?float}>  $items
     * @return Collection<int, array>
     */
    public function ordenarItemsPorCercania(Collection $items, ?float $originLat, ?float $originLng): Collection
    {
        if ($items->count() <= 1) {
            return $items->values();
        }

        $grupos = [];
        $ordenGrupos = [];

        foreach ($items as $item) {
            $key = $item['group_key'];
            if (! isset($grupos[$key])) {
                $grupos[$key] = [
                    'key' => $key,
                    'lat' => $item['lat'],
                    'lng' => $item['lng'],
                    'items' => [],
                ];
                $ordenGrupos[] = $key;
            }
            $grupos[$key]['items'][] = $item;
            if ($grupos[$key]['lat'] === null && $item['lat'] !== null) {
                $grupos[$key]['lat'] = $item['lat'];
                $grupos[$key]['lng'] = $item['lng'];
            }
        }

        $conCoords = [];
        $sinCoords = [];
        foreach ($ordenGrupos as $key) {
            $g = $grupos[$key];
            if ($g['lat'] !== null && $g['lng'] !== null) {
                $conCoords[] = $g;
            } else {
                $sinCoords[] = $g;
            }
        }

        if (empty($conCoords)) {
            return $items->values();
        }

        $curLat = $originLat;
        $curLng = $originLng;

        // Sin origen: arrancar en el punto más al NW (estable) o el primero.
        if ($curLat === null || $curLng === null) {
            usort($conCoords, function ($a, $b) {
                $cmp = $a['lat'] <=> $b['lat'];
                return $cmp !== 0 ? $cmp : ($a['lng'] <=> $b['lng']);
            });
            $primero = array_shift($conCoords);
            $resultado = $primero['items'];
            $curLat = $primero['lat'];
            $curLng = $primero['lng'];
        } else {
            $resultado = [];
        }

        while (! empty($conCoords)) {
            $bestIdx = 0;
            $bestDist = INF;
            foreach ($conCoords as $i => $g) {
                $d = $this->distanciaKm($curLat, $curLng, $g['lat'], $g['lng']);
                if ($d < $bestDist) {
                    $bestDist = $d;
                    $bestIdx = $i;
                }
            }
            $next = array_splice($conCoords, $bestIdx, 1)[0];
            foreach ($next['items'] as $item) {
                $resultado[] = $item;
            }
            $curLat = $next['lat'];
            $curLng = $next['lng'];
        }

        foreach ($sinCoords as $g) {
            foreach ($g['items'] as $item) {
                $resultado[] = $item;
            }
        }

        return collect($resultado)->values();
    }

    public function distanciaKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earth = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $earth * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    /**
     * Misma regla que la app del cobrador: NN desde casa; si punto_casa=final, invertir.
     *
     * @param  Collection<int, array>  $items
     * @return Collection<int, array>
     */
    public function aplicarModoPuntoCasa(Collection $items, string $puntoCasa): Collection
    {
        if ($puntoCasa === 'final') {
            return $items->reverse()->values();
        }

        return $items->values();
    }

    protected function sincronizarOrdenPlantillaDesdeRuta(Ruta $ruta, RutaPlantilla $plantilla): void
    {
        $plantilla->loadMissing('paradas');
        $orden = 1;
        $vistos = [];

        foreach ($ruta->paradas->sortBy('orden') as $parada) {
            $pp = $plantilla->paradas->firstWhere('contrato_id', $parada->contrato_id);
            if (! $pp || isset($vistos[$pp->id])) {
                continue;
            }
            if ((int) $pp->orden !== $orden) {
                $pp->update(['orden' => $orden]);
            }
            $vistos[$pp->id] = true;
            $orden++;
        }
    }
}
