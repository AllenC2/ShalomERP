<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Cliente;
use App\Models\Paquete;
use App\Models\Contrato;
use App\Models\Pago;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PaymentRefactorTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    // Given the agent context, I will try to use a transaction or cleanup in the test, but strictly speaking I should use RefreshDatabase if I was in a CI env. 
    // I'll use a specific test database if configured, or just be careful. 
    // For this agent session, I'll assume standard testing practices.

    protected function setUp(): void
    {
        parent::setUp();
        // Create user with admin role to pass middleware
        $this->user = User::factory()->create(['role' => 'admin']);
    }

    public function test_simplified_payment_flow()
    {
        $this->actingAs($this->user);

        // 1. Setup Data
        // Manually create records as factories might not exist
        $cliente = Cliente::create([
            'nombre' => 'Test',
            'apellido' => 'Client',
            'email' => 'test@client.com',
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
            // Add other required fields if necessary
        ]);

        // 2. Create Contract
        $contrato = Contrato::create([
            'cliente_id' => $cliente->id,
            'paquete_id' => $paquete->id,
            'fecha_inicio' => now(),
            'monto_total' => 1000,
            'estado' => 'activo',
            'user_id' => $this->user->id,
        ]);

        // Verify initial state
        $estadoCuenta = $contrato->estado_cuenta;
        $this->assertEquals(1000, $estadoCuenta['total']);
        $this->assertEquals(0, $estadoCuenta['pagado']);
        $this->assertEquals(1000, $estadoCuenta['pendiente']);

        // 3. Add a Payment
        $response = $this->post(route('pagos.store'), [
            'contrato_id' => $contrato->id,
            'fecha_pago' => now()->format('Y-m-d\TH:i'),
            'monto' => 200,
            'metodo_pago' => 'efectivo',
            'estado' => 'hecho',
            'observaciones' => 'First payment',
        ]);

        $response->assertRedirect(route('contratos.show', $contrato->id));

        $contrato->refresh();
        $estadoCuenta = $contrato->estado_cuenta;

        $this->assertEquals(200, $estadoCuenta['pagado']);
        $this->assertEquals(800, $estadoCuenta['pendiente']);
        $this->assertEquals(20, $estadoCuenta['porcentaje']);

        // 4. Add another Payment
        $this->post(route('pagos.store'), [
            'contrato_id' => $contrato->id,
            'fecha_pago' => now()->format('Y-m-d\TH:i'),
            'monto' => 300,
            'metodo_pago' => 'transferencia bancaria',
            'estado' => 'hecho',
        ]);

        $contrato->refresh();
        $estadoCuenta = $contrato->estado_cuenta;

        $this->assertEquals(500, $estadoCuenta['pagado']);
        $this->assertEquals(500, $estadoCuenta['pendiente']);

        // 5. Update a Payment
        $pago = $contrato->pagos()->first();
        $this->put(route('pagos.update', $pago->id), [
            'contrato_id' => $contrato->id,
            'fecha_pago' => now()->format('Y-m-d\TH:i'),
            'monto' => 250, // Changed from 200 to 250
            'metodo_pago' => 'efectivo',
            'estado' => 'hecho',
        ]);

        $contrato->refresh();
        $estadoCuenta = $contrato->estado_cuenta;
        // Total should be 250 + 300 = 550
        $this->assertEquals(550, $estadoCuenta['pagado']);

        // 6. Delete a Payment
        $this->delete(route('pagos.destroy', $pago->id));

        $contrato->refresh();
        $estadoCuenta = $contrato->estado_cuenta;
        // Total should be 300
        $this->assertEquals(300, $estadoCuenta['pagado']);
    }
}
