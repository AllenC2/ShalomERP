<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cliente;
use App\Models\Empleado;

class FakeDomiciliosGuadalajaraSeeder extends Seeder
{
    public function run(): void
    {
        // Clientes — domicilios falsos en Guadalajara
        $clientes = [
            1 => [
                'calle_y_numero' => 'Av. Vallarta #1523',
                'cruces' => 'Entre López Cotilla y Pedro Moreno',
                'colonia' => 'Colonia Americana',
                'municipio' => 'Guadalajara',
                'estado' => 'Jalisco',
                'codigo_postal' => '44160',
                'latitud' => null,
                'longitud' => null,
            ],
            2 => [
                'calle_y_numero' => 'Calle Morelos #892',
                'cruces' => 'Entre Independencia y Hidalgo',
                'colonia' => 'Centro Histórico',
                'municipio' => 'Guadalajara',
                'estado' => 'Jalisco',
                'codigo_postal' => '44100',
                'latitud' => null,
                'longitud' => null,
            ],
            3 => [
                'calle_y_numero' => 'Av. Chapultepec #345',
                'cruces' => 'Entre Marsella y Londres',
                'colonia' => 'Colonia Americana',
                'municipio' => 'Guadalajara',
                'estado' => 'Jalisco',
                'codigo_postal' => '44160',
                'latitud' => null,
                'longitud' => null,
            ],
            4 => [
                'calle_y_numero' => 'Calle Libertad #678',
                'cruces' => 'Entre Juárez y Corona',
                'colonia' => 'Centro Histórico',
                'municipio' => 'Guadalajara',
                'estado' => 'Jalisco',
                'codigo_postal' => '44100',
                'latitud' => null,
                'longitud' => null,
            ],
            5 => [
                'calle_y_numero' => 'Av. México #2340',
                'cruces' => 'Entre Alemania y España',
                'colonia' => 'Colonia Moderna',
                'municipio' => 'Guadalajara',
                'estado' => 'Jalisco',
                'codigo_postal' => '44190',
                'latitud' => null,
                'longitud' => null,
            ],
            6 => [
                'calle_y_numero' => 'Calle Pedro Loza #512',
                'cruces' => 'Entre Degollado y Galeana',
                'colonia' => 'Centro Histórico',
                'municipio' => 'Guadalajara',
                'estado' => 'Jalisco',
                'codigo_postal' => '44100',
                'latitud' => null,
                'longitud' => null,
            ],
        ];

        foreach ($clientes as $id => $data) {
            $cliente = Cliente::find($id);
            if ($cliente) {
                $cliente->update($data);
            }
        }

        // Empleados — domicilios falsos en Guadalajara
        $empleados = [
            'EMP-0001' => [
                'domicilio' => 'Av. Patria #1250, Col. Jardines Universidad, Guadalajara, Jalisco',
                'latitud' => null,
                'longitud' => null,
            ],
            'EMP-0002' => [
                'domicilio' => 'Calle Marsella #345, Col. Americana, Guadalajara, Jalisco',
                'latitud' => null,
                'longitud' => null,
            ],
            'EMP-0003' => [
                'domicilio' => 'Av. López Mateos Sur #2800, Col. Vallarta Poniente, Guadalajara, Jalisco',
                'latitud' => null,
                'longitud' => null,
            ],
            'EMP-0004' => [
                'domicilio' => 'Calle Buenos Aires #178, Col. Centro, Guadalajara, Jalisco',
                'latitud' => null,
                'longitud' => null,
            ],
            'EMP-0005' => [
                'domicilio' => 'Av. Niños Héroes #1523, Col. Independencia, Guadalajara, Jalisco',
                'latitud' => null,
                'longitud' => null,
            ],
            'EMP-0006' => [
                'domicilio' => 'Calle Reforma #456, Col. Lafayette, Guadalajara, Jalisco',
                'latitud' => null,
                'longitud' => null,
            ],
            'marianis1' => [
                'domicilio' => 'Av. Revolución #789, Col. Arcos Vallarta, Guadalajara, Jalisco',
                'latitud' => null,
                'longitud' => null,
            ],
        ];

        foreach ($empleados as $id => $data) {
            $empleado = Empleado::find($id);
            if ($empleado) {
                $empleado->update($data);
            }
        }

        // Limpiar coordenadas de rutas_paradas existentes (ya no son válidas)
        \DB::table('ruta_paradas')->update(['latitud' => null, 'longitud' => null]);

        $this->command->info('Domicilios actualizados a direcciones falsas de Guadalajara.');
        $this->command->info('Clientes: ' . Cliente::where('municipio', 'Guadalajara')->count());
        $this->command->info('Empleados: ' . Empleado::where('domicilio', 'like', '%Guadalajara%')->count());
    }
}
