<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Comisione;
use App\Models\Contrato;
use App\Models\Empleado;
use App\Models\Paquete;
use App\Models\Pago;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ComisionesFechaCorteTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($this->user);
    }

    public function test_parcialidad_usa_fecha_del_abono_no_now(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-29 10:00:00', 'America/Mexico_City'));

        $empleado = $this->crearEmpleado();
        $contrato = $this->crearContratoConComision($empleado, 200);

        $this->post(route('pagos.store'), $this->payloadPago($contrato->id, '2026-07-27T15:00', 100))
            ->assertRedirect();

        $parcialidad = Comisione::where('tipo_comision', 'PARCIALIDAD')->first();
        $this->assertNotNull($parcialidad);
        $this->assertTrue(
            Carbon::parse($parcialidad->fecha_comision)->isSameDay(Carbon::parse('2026-07-27'))
        );
        $this->assertFalse(
            Carbon::parse($parcialidad->fecha_comision)->isSameDay(Carbon::parse('2026-07-29'))
        );

        $padre = Comisione::whereNull('comision_padre_id')->where('tipo_comision', 'vendedor')->first();
        $this->assertSame('Pendiente', $padre->estado);
        $this->assertTrue(
            Carbon::parse($padre->fecha_comision)->isSameDay(Carbon::parse($contrato->fecha_inicio))
        );

        Carbon::setTestNow();
    }

    public function test_abono_lunes_entra_en_corte_que_cierra_martes_no_en_el_que_abre_miercoles(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-29 10:00:00', 'America/Mexico_City'));

        $empleado = $this->crearEmpleado();
        $contrato = $this->crearContratoConComision($empleado, 200);

        $this->post(route('pagos.store'), $this->payloadPago($contrato->id, '2026-07-27T15:00', 100));

        $reciboSemanaCorrecta = $this->get(route('comisiones.reciboConsolidado', [
            'empleado_id' => $empleado->id,
            'fecha_inicio' => '2026-07-22',
            'fecha_fin' => '2026-07-28',
        ]));
        $reciboSemanaCorrecta->assertOk();
        $reciboSemanaCorrecta->assertSee('100.00', false);

        $reciboSemanaSiguiente = $this->get(route('comisiones.reciboConsolidado', [
            'empleado_id' => $empleado->id,
            'fecha_inicio' => '2026-07-29',
            'fecha_fin' => '2026-08-04',
        ]));
        $reciboSemanaSiguiente->assertOk();
        $reciboSemanaSiguiente->assertSee('No se encontraron comisiones pagadas', false);

        Carbon::setTestNow();
    }

    public function test_recibo_incluye_abono_del_30_en_rango_22_jul_a_4_ago(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-10 09:00:00', 'America/Mexico_City'));

        $empleado = $this->crearEmpleado();
        $contrato = $this->crearContratoConComision($empleado, 200);

        $this->post(route('pagos.store'), $this->payloadPago($contrato->id, '2026-07-30T12:00', 80));

        $recibo = $this->get(route('comisiones.reciboConsolidado', [
            'empleado_id' => $empleado->id,
            'fecha_inicio' => '2026-07-22',
            'fecha_fin' => '2026-08-04',
        ]));
        $recibo->assertOk();
        $recibo->assertSee('80.00', false);
        $recibo->assertSee((string) $contrato->id, false);

        Carbon::setTestNow();
    }

    public function test_recibo_no_duplica_padre_con_parcialidades(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-29 10:00:00', 'America/Mexico_City'));

        $empleado = $this->crearEmpleado();
        $contrato = $this->crearContratoConComision($empleado, 100);

        $this->post(route('pagos.store'), $this->payloadPago($contrato->id, '2026-07-27T15:00', 100));

        $padre = Comisione::whereNull('comision_padre_id')->where('tipo_comision', 'vendedor')->first();
        $this->assertSame('Pagada', $padre->estado);

        $recibo = $this->get(route('comisiones.reciboConsolidado', [
            'empleado_id' => $empleado->id,
            'fecha_inicio' => '2026-07-22',
            'fecha_fin' => '2026-08-04',
        ]));
        $recibo->assertOk();
        $recibo->assertSee('100.00', false);
        $recibo->assertDontSee('200.00', false);

        Carbon::setTestNow();
    }

    public function test_toggle_estado_no_pisa_fecha_comision(): void
    {
        $empleado = $this->crearEmpleado();
        $contrato = $this->crearContratoConComision($empleado, 50);
        $padre = Comisione::where('contrato_id', $contrato->id)->first();
        $padre->update(['estado' => 'Pagada']);
        $fechaOriginal = Carbon::parse($padre->fecha_comision)->toDateTimeString();

        Carbon::setTestNow(Carbon::parse('2026-08-15 12:00:00'));
        $this->patch(route('comisiones.toggleEstado', $padre->id))->assertOk();
        $padre->refresh();
        $this->assertSame('Pendiente', $padre->estado);
        $this->assertSame($fechaOriginal, Carbon::parse($padre->fecha_comision)->toDateTimeString());
        Carbon::setTestNow();
    }

    public function test_comando_corrige_fecha_de_parcialidad_por_ventana_created_at(): void
    {
        $empleado = $this->crearEmpleado();
        $contrato = $this->crearContratoConComision($empleado, 200);
        $captura = Carbon::parse('2026-07-29 10:00:00');
        $abono = Carbon::parse('2026-07-27 15:00:00');

        $pago = Pago::create([
            'contrato_id' => $contrato->id,
            'tipo_pago' => 'cuota',
            'metodo_pago' => 'efectivo',
            'monto' => 100,
            'fecha_pago' => $abono,
            'estado' => 'hecho',
            'created_by' => $this->user->id,
        ]);
        $pago->created_at = $captura;
        $pago->save();

        $padre = Comisione::where('contrato_id', $contrato->id)->first();
        $parcialidad = Comisione::create([
            'contrato_id' => $contrato->id,
            'empleado_id' => $empleado->id,
            'comision_padre_id' => $padre->id,
            'fecha_comision' => $captura,
            'nombre_paquete' => 'Test Package',
            'porcentaje' => 0,
            'tipo_comision' => 'PARCIALIDAD',
            'monto' => 100,
            'estado' => 'Pagada',
            'orden' => 0,
        ]);
        $parcialidad->created_at = $captura;
        $parcialidad->save();

        $this->artisan('comisiones:corregir-fechas', ['--execute' => true])
            ->assertSuccessful();

        $parcialidad->refresh();
        $this->assertTrue(Carbon::parse($parcialidad->fecha_comision)->isSameDay($abono));
        $padre->refresh();
        $this->assertTrue(
            Carbon::parse($padre->fecha_comision)->isSameDay(Carbon::parse($contrato->fecha_inicio))
        );
    }

    private function crearEmpleado(): Empleado
    {
        $attrs = [
            'id' => 'EMP-TEST1',
            'nombre' => 'Ana',
            'apellido' => 'Asesor',
            'estado' => 'activo',
        ];
        $cols = Schema::getColumnListing('empleados');
        if (in_array('email', $cols, true)) {
            $attrs['email'] = 'ana.asesor@test.com';
        }
        if (in_array('user_id', $cols, true)) {
            $attrs['user_id'] = $this->user->id;
        }

        $empleado = new Empleado();
        $empleado->forceFill($attrs);
        $empleado->save();

        return $empleado;
    }

    private function crearContratoConComision(Empleado $empleado, float $montoComision): Contrato
    {
        $cliente = Cliente::create([
            'nombre' => 'Test',
            'apellido' => 'Client',
            'email' => 'cliente.comision.'.uniqid().'@test.com',
            'telefono' => '1234567890',
            'calle_y_numero' => 'Main St 123',
            'colonia' => 'Downtown',
            'municipio' => 'Cityville',
            'domicilio_completo' => 'Main St 123, Downtown, Cityville',
        ]);

        $paquete = Paquete::create([
            'nombre' => 'Test Package',
            'descripcion' => 'A test package description',
            'precio' => 1000,
        ]);

        $contrato = Contrato::create([
            'cliente_id' => $cliente->id,
            'paquete_id' => $paquete->id,
            'fecha_inicio' => '2026-06-01',
            'monto_total' => 1000,
            'monto_inicial' => 0,
            'estado' => 'activo',
            'numero_cuotas' => 10,
            'frecuencia_cuotas' => 7,
            'monto_cuota' => 100,
        ]);

        Comisione::create([
            'contrato_id' => $contrato->id,
            'empleado_id' => $empleado->id,
            'fecha_comision' => $contrato->fecha_inicio,
            'nombre_paquete' => $paquete->nombre,
            'porcentaje' => 20,
            'tipo_comision' => 'vendedor',
            'monto' => $montoComision,
            'observaciones' => 'Comisión de prueba',
            'documento' => 'No',
            'estado' => 'Pendiente',
            'orden' => 1,
        ]);

        return $contrato;
    }

    private function payloadPago(int $contratoId, string $fechaPago, float $monto): array
    {
        return [
            'contrato_id' => $contratoId,
            'fecha_pago' => $fechaPago,
            'monto' => $monto,
            'metodo_pago' => 'efectivo',
            'estado' => 'hecho',
            'tipo_pago' => 'cuota',
        ];
    }
}
