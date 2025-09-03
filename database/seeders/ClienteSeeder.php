<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Cliente;

class ClienteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clientes = [
            [
                'nombre' => 'Juan Carlos',
                'apellido' => 'García López',
                'email' => 'juan.garcia@email.com',
                'telefono' => '5551234567',
                'calle_y_numero' => 'Av. Reforma #123',
                'cruces' => 'Entre Insurgentes y Patriotismo',
                'colonia' => 'Roma Norte',
                'municipio' => 'Cuauhtémoc',
                'estado' => 'Ciudad de México',
                'codigo_postal' => '06700',
                'domicilio_completo' => 'Av. Reforma #123, Entre Insurgentes y Patriotismo, Roma Norte, Cuauhtémoc, Ciudad de México, C.P. 06700'
            ],
            [
                'nombre' => 'María Elena',
                'apellido' => 'Rodríguez Martínez',
                'email' => 'maria.rodriguez@email.com',
                'telefono' => '5559876543',
                'calle_y_numero' => 'Calle Madero #456',
                'cruces' => 'Entre Bolívar y 16 de Septiembre',
                'colonia' => 'Centro Histórico',
                'municipio' => 'Cuauhtémoc',
                'estado' => 'Ciudad de México',
                'codigo_postal' => '06000',
                'domicilio_completo' => 'Calle Madero #456, Entre Bolívar y 16 de Septiembre, Centro Histórico, Cuauhtémoc, Ciudad de México, C.P. 06000'
            ],
            [
                'nombre' => 'Carlos Alberto',
                'apellido' => 'Hernández Silva',
                'email' => 'carlos.hernandez@email.com',
                'telefono' => '5555678901',
                'calle_y_numero' => 'Av. Universidad #789',
                'cruces' => 'Entre Copilco y Miguel Ángel de Quevedo',
                'colonia' => 'Copilco Universidad',
                'municipio' => 'Coyoacán',
                'estado' => 'Ciudad de México',
                'codigo_postal' => '04360',
                'domicilio_completo' => 'Av. Universidad #789, Entre Copilco y Miguel Ángel de Quevedo, Copilco Universidad, Coyoacán, Ciudad de México, C.P. 04360'
            ],
            [
                'nombre' => 'Ana Patricia',
                'apellido' => 'López Jiménez',
                'email' => 'ana.lopez@email.com',
                'telefono' => '5552345678',
                'calle_y_numero' => 'Av. Insurgentes Sur #321',
                'cruces' => 'Entre Félix Cuevas y Municipio Libre',
                'colonia' => 'Del Valle',
                'municipio' => 'Benito Juárez',
                'estado' => 'Ciudad de México',
                'codigo_postal' => '03100',
                'domicilio_completo' => 'Av. Insurgentes Sur #321, Entre Félix Cuevas y Municipio Libre, Del Valle, Benito Juárez, Ciudad de México, C.P. 03100'
            ],
            [
                'nombre' => 'Roberto',
                'apellido' => 'Sánchez Torres',
                'email' => 'roberto.sanchez@email.com',
                'telefono' => '5558765432',
                'calle_y_numero' => 'Calle Amsterdam #654',
                'cruces' => 'Entre Génova y Londres',
                'colonia' => 'Condesa',
                'municipio' => 'Cuauhtémoc',
                'estado' => 'Ciudad de México',
                'codigo_postal' => '06140',
                'domicilio_completo' => 'Calle Amsterdam #654, Entre Génova y Londres, Condesa, Cuauhtémoc, Ciudad de México, C.P. 06140'
            ]
        ];

        foreach ($clientes as $cliente) {
            Cliente::create($cliente);
        }
    }
}
