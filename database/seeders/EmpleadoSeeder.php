<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Empleado;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class EmpleadoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $empleados = [
            [
                'nombre' => 'Luis Fernando',
                'apellido' => 'Morales Vega',
                'email' => 'luis.morales@shalom.com',
                'telefono' => '5551122334',
                'domicilio' => 'Av. Revolución #890, Col. San Ángel, Álvaro Obregón, CDMX'
            ],
            [
                'nombre' => 'Sandra',
                'apellido' => 'Castillo Ruiz',
                'email' => 'sandra.castillo@shalom.com',
                'telefono' => '5555566778',
                'domicilio' => 'Calle Puebla #567, Col. Roma Sur, Cuauhtémoc, CDMX'
            ],
            [
                'nombre' => 'Diego Armando',
                'apellido' => 'Flores Mendoza',
                'email' => 'diego.flores@shalom.com',
                'telefono' => '5559988776',
                'domicilio' => 'Av. Patriotismo #234, Col. San Pedro de los Pinos, Benito Juárez, CDMX'
            ],
            [
                'nombre' => 'Fernanda',
                'apellido' => 'Gutiérrez Pérez',
                'email' => 'fernanda.gutierrez@shalom.com',
                'telefono' => '5553344556',
                'domicilio' => 'Calle Durango #456, Col. Roma Norte, Cuauhtémoc, CDMX'
            ],
            [
                'nombre' => 'Miguel Ángel',
                'apellido' => 'Ramírez Corona',
                'email' => 'miguel.ramirez@shalom.com',
                'telefono' => '5557788990',
                'domicilio' => 'Av. División del Norte #678, Col. Narvarte, Benito Juárez, CDMX'
            ],
            [
                'nombre' => 'Claudia',
                'apellido' => 'Torres Aguilar',
                'email' => 'claudia.torres@shalom.com',
                'telefono' => '5551234890',
                'domicilio' => 'Calle Orizaba #321, Col. Roma Norte, Cuauhtémoc, CDMX'
            ]
        ];

        foreach ($empleados as $empleadoData) {
            // Crear el usuario primero
            $user = User::create([
                'name' => $empleadoData['nombre'] . ' ' . $empleadoData['apellido'],
                'email' => $empleadoData['email'],
                'password' => Hash::make('password123'),
                'role' => 'empleado'
            ]);

            // Crear el empleado vinculado al usuario
            Empleado::create([
                'nombre' => $empleadoData['nombre'],
                'apellido' => $empleadoData['apellido'],
                'user_id' => $user->id,
                'telefono' => $empleadoData['telefono'],
                'domicilio' => $empleadoData['domicilio'],
            ]);
        }
    }
}
