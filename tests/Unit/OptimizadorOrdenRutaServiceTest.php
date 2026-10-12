<?php

namespace Tests\Unit;

use App\Services\OptimizadorOrdenRutaService;
use Tests\TestCase;

class OptimizadorOrdenRutaServiceTest extends TestCase
{
    public function test_elige_el_vecino_mas_cercano_desde_el_origen(): void
    {
        $service = new OptimizadorOrdenRutaService();

        // Origen en (0,0). A cerca, C media, B lejos.
        $items = collect([
            ['id' => 'b', 'group_key' => 'b', 'lat' => 0.20, 'lng' => 0.20],
            ['id' => 'c', 'group_key' => 'c', 'lat' => 0.05, 'lng' => 0.05],
            ['id' => 'a', 'group_key' => 'a', 'lat' => 0.01, 'lng' => 0.01],
        ]);

        $orden = $service->ordenarItemsPorCercania($items, 0.0, 0.0)->pluck('id')->all();

        $this->assertSame(['a', 'c', 'b'], $orden);
    }

    public function test_mantiene_juntos_los_contratos_del_mismo_cliente(): void
    {
        $service = new OptimizadorOrdenRutaService();

        $items = collect([
            ['id' => 1, 'group_key' => 'cliA', 'lat' => 0.10, 'lng' => 0.10],
            ['id' => 2, 'group_key' => 'cliB', 'lat' => 0.01, 'lng' => 0.01],
            ['id' => 3, 'group_key' => 'cliA', 'lat' => 0.10, 'lng' => 0.10],
        ]);

        $orden = $service->ordenarItemsPorCercania($items, 0.0, 0.0)->pluck('id')->all();

        $this->assertSame([2, 1, 3], $orden);
    }

    public function test_punto_casa_final_invierte_el_orden_nn(): void
    {
        $service = new OptimizadorOrdenRutaService();

        $items = collect([
            ['id' => 'b', 'group_key' => 'b', 'lat' => 0.20, 'lng' => 0.20],
            ['id' => 'c', 'group_key' => 'c', 'lat' => 0.05, 'lng' => 0.05],
            ['id' => 'a', 'group_key' => 'a', 'lat' => 0.01, 'lng' => 0.01],
        ]);

        $nn = $service->ordenarItemsPorCercania($items, 0.0, 0.0);
        $final = $service->aplicarModoPuntoCasa($nn, 'final')->pluck('id')->all();
        $inicio = $service->aplicarModoPuntoCasa($nn, 'inicio')->pluck('id')->all();

        $this->assertSame(['b', 'c', 'a'], $final);
        $this->assertSame(['a', 'c', 'b'], $inicio);
    }
}
