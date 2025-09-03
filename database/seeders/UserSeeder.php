<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear usuario administrador
        User::create([
            'name' => 'Administrador',
            'email' => 'admin@shalom.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Crear usuario empleado de ejemplo
        User::create([
            'name' => 'Empleado 1',
            'email' => 'empleado@shalom.com',
            'password' => Hash::make('password'),
            'role' => 'empleado',
        ]);

        // Crear usuario cliente (si necesitas)
        User::create([
            'name' => 'Cliente',
            'email' => 'cliente@shalom.com',
            'password' => Hash::make('password'),
            'role' => 'cliente',
        ]);
    }
}
