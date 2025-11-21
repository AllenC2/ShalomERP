<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Ajuste;

class RegistroPublicoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Ajuste::updateOrCreate(
            ['nombre' => 'registro_publico_activo'],
            [
                'valor' => 'false',
                'tipo' => 'boolean',
                'descripcion' => 'Permite el registro público de nuevos usuarios sin autenticación de administrador',
                'activo' => true
            ]
        );
    }
}
