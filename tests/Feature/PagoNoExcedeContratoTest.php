<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Contrato;
use App\Models\Pago;
use App\Models\Paquete;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PagoNoExcedeContratoTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'admin']);
    }

    public function test_rechaza_pago_que_supera_el_total_del_contrato(): void
    {
        $this->actingAs($this->user);
        $contrato = $this->contrato(1000);

        Pago::create($this->datosPago($contrato, 800));

        $response = $this->post(route('pagos.store'), $this->datosPago($contrato, 300));

        $response->assertSessionHasErrors('monto');
        $this->assertEquals(1, $contrato->pagos()->count());
        $this->assertEquals(800, (float) $contrato->fresh()->total_pagado);
    }

    public function test_acepta_pago_que_cubre_exactamente_el_saldo(): void
    {
        $this->actingAs($this->user);
        $contrato = $this->contrato(1000);

        Pago::create($this->datosPago($contrato, 800));

        $response = $this->post(route('pagos.store'), $this->datosPago($contrato, 200));

        $response->assertSessionDoesntHaveErrors('monto');
        $response->assertRedirect();
        $this->assertEquals(1000, (float) $contrato->fresh()->total_pagado);
    }

    public function test_rechaza_edicion_que_hace_superar_el_total(): void
    {
        $this->actingAs($this->user);
        $contrato = $this->contrato(1000);
        $pago = Pago::create($this->datosPago($contrato, 400));
        Pago::create($this->datosPago($contrato, 400));

        $response = $this->put(route('pagos.update', $pago), array_merge(
            $this->datosPago($contrato, 700),
            ['tipo_pago' => 'cuota']
        ));

        $response->assertSessionHasErrors('monto');
        $this->assertEquals(400, (float) $pago->fresh()->monto);
    }

    public function test_el_formulario_muestra_el_saldo_disponible(): void
    {
        $this->actingAs($this->user);
        $contrato = $this->contrato(1000);
        Pago::create($this->datosPago($contrato, 250));

        $response = $this->get(route('pagos.create', ['contrato_id' => $contrato->id]));

        $response->assertOk();
        $response->assertSee('Saldo disponible del contrato: $750.00', false);
    }

    public function test_acepta_edicion_dentro_del_total(): void
    {
        $this->actingAs($this->user);
        $contrato = $this->contrato(1000);
        $pago = Pago::create($this->datosPago($contrato, 400));
        Pago::create($this->datosPago($contrato, 400));

        $response = $this->put(route('pagos.update', $pago), array_merge(
            $this->datosPago($contrato, 500),
            ['tipo_pago' => 'cuota']
        ));

        $response->assertSessionDoesntHaveErrors('monto');
        $this->assertEquals(500, (float) $pago->fresh()->monto);
        $this->assertEquals(900, (float) $contrato->fresh()->total_pagado);
    }

    private function contrato(float $montoTotal): Contrato
    {
        $cliente = Cliente::create([
            'nombre' => 'Test',
            'apellido' => 'Client',
            'email' => fake()->unique()->safeEmail(),
            'telefono' => '1234567890',
            'calle_y_numero' => 'Main St 123',
            'colonia' => 'Downtown',
            'municipio' => 'Cityville',
            'domicilio_completo' => 'Main St 123, Downtown, Cityville',
        ]);

        $paquete = Paquete::create([
            'nombre' => 'Test Package',
            'descripcion' => 'A test package description',
            'precio' => $montoTotal,
        ]);

        return Contrato::create([
            'cliente_id' => $cliente->id,
            'paquete_id' => $paquete->id,
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin' => now()->addYear()->toDateString(),
            'monto_total' => $montoTotal,
            'estado' => 'activo',
        ]);
    }

    private function datosPago(Contrato $contrato, float $monto): array
    {
        return [
            'contrato_id' => $contrato->id,
            'fecha_pago' => now()->format('Y-m-d\TH:i'),
            'monto' => $monto,
            'metodo_pago' => 'efectivo',
            'estado' => 'hecho',
            'tipo_pago' => 'parcialidad',
        ];
    }
}
