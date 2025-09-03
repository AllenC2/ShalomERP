<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Ajuste;

class WhatsAppRecordatorioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Insertar o actualizar el mensaje de recordatorio de WhatsApp por defecto
        Ajuste::updateOrCreate(
            ['nombre' => 'textoRecordatorioWhats'],
            [
                'valor' => 'Hola {nombreCliente}, te recordamos que el pago de tu paquete {nombrePaquete} por {cantidadPagoProximo} será cobrado el día {fechaPago}.',
                'tipo' => 'recordatorio',
                'descripcion' => 'Mensaje que se enviará al cliente como recordatorio de pago por WhatsApp',
                'activo' => true
            ]
        );

        $this->command->info('✅ Mensaje de recordatorio de WhatsApp creado correctamente.');
    }
}
