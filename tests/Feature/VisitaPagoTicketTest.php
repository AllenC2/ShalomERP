<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Contrato;
use App\Models\Empleado;
use App\Models\Pago;
use App\Models\Paquete;
use App\Models\Ruta;
use App\Models\RutaParada;
use App\Models\User;
use App\Models\Visita;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class VisitaPagoTicketTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Empleado $empleado;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'empleado']);
        $this->empleado = $this->crearEmpleado($this->user);
    }

    public function test_empleado_requiere_pago(): void
    {
        $this->actingAs($this->user);
        $ctx = $this->contextoRuta();

        $response = $this->postJson(route('visitas.store'), $this->payloadVisita($ctx['parada'], [
            'registrar_pago' => false,
        ]));

        $response->assertStatus(422);
        $this->assertEquals(0, Visita::count());
        $this->assertEquals(0, Pago::count());
    }

    public function test_ausente_registra_visita_sin_pago_y_ticket_con_firma(): void
    {
        $this->actingAs($this->user);
        $ctx = $this->contextoRuta();

        $response = $this->postJson(route('visitas.store'), $this->payloadVisita($ctx['parada'], [
            'ausente' => true,
            'recibido' => false,
            'registrar_pago' => false,
        ]));

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertEquals(1, Visita::count());
        $this->assertEquals(0, Pago::count());

        $visita = Visita::first();
        $this->assertFalse((bool) $visita->recibido);
        $this->assertEquals(RutaParada::ESTADO_VISITADA, $ctx['parada']->fresh()->estado);
        $this->assertEqualsWithDelta(19.43, (float) $visita->latitud, 0.0001);
        $this->assertEqualsWithDelta(-99.13, (float) $visita->longitud, 0.0001);

        $ticket = $this->get($response->json('ticket_url'));
        $ticket->assertOk();
        $ticket->assertSee('no fue posible localizarle', false);
        $ticket->assertSee('FIRMA DEL EMPLEADO QUE VISITA', false);
        $ticket->assertSee($this->user->name, false);
        $ticket->assertDontSee('TICKET DE PAGO', false);
    }

    public function test_datos_de_ruta_incluyen_ticket_url_de_parada_visitada(): void
    {
        $this->actingAs($this->user);
        $ctx = $this->contextoRuta();

        $this->postJson(route('visitas.store'), $this->payloadVisita($ctx['parada'], [
            'ausente' => true,
            'recibido' => false,
            'registrar_pago' => false,
        ]))->assertOk();

        $visita = Visita::first();
        $datos = $this->getJson(route('rutas.getRutaData', $ctx['ruta']->id));
        $datos->assertOk();
        $parada = collect($datos->json('paradas'))->firstWhere('id', $ctx['parada']->id);
        $this->assertNotNull($parada);
        $this->assertSame(route('visitas.ticket', $visita), $parada['ticket_url']);
        $this->assertSame($visita->id, $parada['visita_id']);
        $this->assertIsArray($parada['registros_ubicacion'] ?? null);
        $this->assertCount(1, $parada['registros_ubicacion']);
        $this->assertEqualsWithDelta(19.43, (float) $parada['registros_ubicacion'][0]['latitud'], 0.0001);
        $this->assertEqualsWithDelta(-99.13, (float) $parada['registros_ubicacion'][0]['longitud'], 0.0001);
        $this->assertFalse($parada['registros_ubicacion'][0]['tiene_pago']);
    }

    public function test_deshacer_visita_ausente_revierte_parada_y_ruta(): void
    {
        $this->actingAs($this->user);
        $ctx = $this->contextoRuta();

        $store = $this->postJson(route('visitas.store'), $this->payloadVisita($ctx['parada'], [
            'ausente' => true,
            'recibido' => false,
            'registrar_pago' => false,
        ]));
        $store->assertOk();
        $visitaId = $store->json('visita_id');
        $this->assertEquals(RutaParada::ESTADO_VISITADA, $ctx['parada']->fresh()->estado);
        $this->assertEquals(Ruta::ESTADO_COMPLETADA, $ctx['ruta']->fresh()->estado);

        $undo = $this->postJson(route('visitas.deshacer', $visitaId));
        $undo->assertOk()->assertJson(['success' => true]);

        $this->assertEquals(0, Visita::count());
        $this->assertEquals(RutaParada::ESTADO_PENDIENTE, $ctx['parada']->fresh()->estado);
        $this->assertEquals(Ruta::ESTADO_PLANEADA, $ctx['ruta']->fresh()->estado);
    }

    public function test_deshacer_visita_con_pago_anula_el_pago(): void
    {
        $this->actingAs($this->user);
        $ctx = $this->contextoRuta();

        $store = $this->postJson(route('visitas.store'), $this->payloadVisita($ctx['parada'], [
            'pagos' => [[
                'contrato_id' => $ctx['contrato']->id,
                'monto' => 150,
                'metodo_pago' => 'efectivo',
                'tipo_pago' => 'parcialidad',
            ]],
        ]));
        $store->assertOk();
        $this->assertEquals(1, Pago::count());

        $undo = $this->postJson(route('visitas.deshacer', $store->json('visita_id')));
        $undo->assertOk();

        $this->assertEquals(0, Visita::count());
        $this->assertEquals(0, Pago::count());
        $this->assertEquals(RutaParada::ESTADO_PENDIENTE, $ctx['parada']->fresh()->estado);
    }

    public function test_pago_de_folio_asignado(): void
    {
        $this->actingAs($this->user);
        $ctx = $this->contextoRuta();

        $response = $this->postJson(route('visitas.store'), $this->payloadVisita($ctx['parada'], [
            'pagos' => [[
                'contrato_id' => $ctx['contrato']->id,
                'monto' => 150,
                'metodo_pago' => 'efectivo',
                'tipo_pago' => 'parcialidad',
            ]],
        ]));

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertEquals(1, Pago::count());
        $pago = Pago::first();
        $this->assertEquals(150, (float) $pago->monto);
        $this->assertEquals('hecho', $pago->estado);
        $this->assertEquals($this->user->id, $pago->created_by);
        $this->assertNotNull($pago->visita_id);
        $this->assertEquals($ctx['contrato']->id, $pago->contrato_id);
        $this->assertFalse((bool) Visita::first()->recibido);

        $ticket = $this->get($response->json('ticket_url'));
        $ticket->assertOk();
        $ticket->assertSee('FIRMA DEL EMPLEADO QUE ENTREGA', false);
        $ticket->assertSee('$150.00', false);
        $ticket->assertSee('TICKET DE PAGO', false);
        $ticket->assertDontSee('María Pérez', false);
    }

    public function test_403_si_folio_no_esta_en_la_parada(): void
    {
        $this->actingAs($this->user);
        $ctx = $this->contextoRuta();
        $ajeno = $this->contrato(800);

        $response = $this->postJson(route('visitas.store'), $this->payloadVisita($ctx['parada'], [
            'pagos' => [[
                'contrato_id' => $ajeno->id,
                'monto' => 100,
                'metodo_pago' => 'efectivo',
                'tipo_pago' => 'parcialidad',
            ]],
        ]));

        $response->assertForbidden();
        $this->assertEquals(0, Visita::count());
        $this->assertEquals(0, Pago::count());
    }

    public function test_422_si_el_monto_supera_el_contrato(): void
    {
        $this->actingAs($this->user);
        $ctx = $this->contextoRuta(1000);

        Pago::create([
            'contrato_id' => $ctx['contrato']->id,
            'tipo_pago' => 'parcialidad',
            'metodo_pago' => 'efectivo',
            'monto' => 800,
            'fecha_pago' => now(),
            'estado' => 'hecho',
            'created_by' => $this->user->id,
        ]);

        $response = $this->postJson(route('visitas.store'), $this->payloadVisita($ctx['parada'], [
            'pagos' => [[
                'contrato_id' => $ctx['contrato']->id,
                'monto' => 300,
                'metodo_pago' => 'efectivo',
                'tipo_pago' => 'parcialidad',
            ]],
        ]));

        $response->assertStatus(422);
        $this->assertEquals(0, Visita::count());
        $this->assertEquals(1, Pago::count());
    }

    private function contextoRuta(float $montoTotal = 1000): array
    {
        $contrato = $this->contrato($montoTotal);
        $ruta = Ruta::create([
            'empleado_id' => $this->empleado->id,
            'nombre' => 'Ruta test',
            'fecha' => now()->toDateString(),
            'estado' => Ruta::ESTADO_PLANEADA,
            'user_id' => $this->user->id,
        ]);
        $parada = RutaParada::create([
            'ruta_id' => $ruta->id,
            'orden' => 1,
            'contrato_id' => $contrato->id,
            'cliente_id' => $contrato->cliente_id,
            'direccion_destino' => 'Main St 123',
            'latitud' => 19.43,
            'longitud' => -99.13,
            'estado' => RutaParada::ESTADO_PENDIENTE,
        ]);

        return compact('contrato', 'ruta', 'parada');
    }

    private function payloadVisita(RutaParada $parada, array $extra = []): array
    {
        return array_merge([
            'ruta_parada_ids' => [$parada->id],
            'ubicacion_evidencia' => 'POINT(-99.13 19.43)',
            'en_domicilio' => true,
            'recibido' => false,
        ], $extra);
    }

    private function crearEmpleado(User $user): Empleado
    {
        $attrs = [
            'id' => 'EMP-VST01',
            'nombre' => 'Cobra',
            'apellido' => 'Dor',
            'estado' => 'activo',
        ];
        $cols = Schema::getColumnListing('empleados');
        if (in_array('email', $cols, true)) {
            $attrs['email'] = 'cobrador.'.uniqid().'@test.com';
        }
        if (in_array('user_id', $cols, true)) {
            $attrs['user_id'] = $user->id;
        }

        $empleado = new Empleado();
        $empleado->forceFill($attrs);
        $empleado->save();

        return $empleado;
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
            'monto_inicial' => 0,
            'estado' => 'activo',
            'numero_cuotas' => 10,
            'frecuencia_cuotas' => 7,
            'monto_cuota' => 100,
        ]);
    }
}
