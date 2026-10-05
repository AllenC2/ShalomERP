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

    public function test_visita_sin_pago(): void
    {
        $this->actingAs($this->user);
        $ctx = $this->contextoRuta();

        $response = $this->postJson(route('visitas.store'), $this->payloadVisita($ctx['parada'], [
            'registrar_pago' => false,
        ]));

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertEquals(1, Visita::count());
        $this->assertEquals(0, Pago::count());
        $this->assertFalse((bool) Visita::first()->recibido);
        $this->assertSame(RutaParada::ESTADO_VISITADA, $ctx['parada']->fresh()->estado);
        $this->assertNotEmpty($response->json('ticket_url'));
    }

    public function test_pago_de_folio_asignado(): void
    {
        $this->actingAs($this->user);
        $ctx = $this->contextoRuta();

        $response = $this->postJson(route('visitas.store'), $this->payloadVisita($ctx['parada'], [
            'recibido' => true,
            'receptor_nombre' => 'María Pérez',
            'receptor_parentesco' => 'titular',
            'registrar_pago' => true,
            'pagos' => [[
                'contrato_id' => $ctx['contrato']->id,
                'monto' => 150,
                'metodo_pago' => 'efectivo',
                'tipo_pago' => 'cuota',
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

        $ticket = $this->get($response->json('ticket_url'));
        $ticket->assertOk();
        $ticket->assertSee('FIRMA DEL EMPLEADO QUE ENTREGA', false);
        $ticket->assertSee('$150.00', false);
        $ticket->assertSee('María Pérez', false);
    }

    public function test_403_si_folio_no_esta_en_la_parada(): void
    {
        $this->actingAs($this->user);
        $ctx = $this->contextoRuta();
        $ajeno = $this->contrato(800);

        $response = $this->postJson(route('visitas.store'), $this->payloadVisita($ctx['parada'], [
            'recibido' => true,
            'receptor_nombre' => 'Recibe',
            'receptor_parentesco' => 'familiar',
            'registrar_pago' => true,
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
            'recibido' => true,
            'receptor_nombre' => 'Recibe',
            'receptor_parentesco' => 'titular',
            'registrar_pago' => true,
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
