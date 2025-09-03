<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Paquete;

class PaqueteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $paquetes = [
            [
                'nombre' => 'ETERNIDAD',
                'descripcion' => 'Paquete funerario ETERNIDAD con servicios completos para honrar la memoria de sus seres queridos.',
                'precio' => 16500.00
            ],
            [
                'nombre' => 'SERENIDAD',
                'descripcion' => 'Paquete funerario SERENIDAD diseñado para brindar paz y tranquilidad en momentos difíciles.',
                'precio' => 18800.00
            ],
            [
                'nombre' => 'MEMORIA',
                'descripcion' => 'Paquete funerario MEMORIA para preservar el recuerdo de manera digna y respetuosa.',
                'precio' => 23800.00
            ],
            [
                'nombre' => 'DESCANSO',
                'descripcion' => 'Paquete funerario DESCANSO que ofrece servicios integrales para el descanso eterno.',
                'precio' => 25500.00
            ],
            [
                'nombre' => 'LEGADO',
                'descripcion' => 'Paquete funerario LEGADO para honrar el legado y la vida de sus seres queridos.',
                'precio' => 29000.00
            ],
            [
                'nombre' => 'TRANQUILIDAD',
                'descripcion' => 'Paquete funerario TRANQUILIDAD con servicios premium para momentos de duelo.',
                'precio' => 30500.00
            ],
            [
                'nombre' => 'PAZ',
                'descripcion' => 'Paquete funerario PAZ que brinda servicios completos con la máxima calidad y respeto.',
                'precio' => 34000.00
            ],
            [
                'nombre' => 'SANTIDAD',
                'descripcion' => 'Paquete funerario SANTIDAD con servicios especializados de alta calidad.',
                'precio' => 42500.00
            ],
            [
                'nombre' => 'RECUERDO',
                'descripcion' => 'Paquete funerario RECUERDO para preservar los momentos más importantes.',
                'precio' => 42500.00
            ],
            [
                'nombre' => 'PROPIEDAD PANTEÓN',
                'descripcion' => 'Paquete funerario PROPIEDAD PANTEÓN que incluye servicios exclusivos de panteón.',
                'precio' => 51000.00
            ],
            [
                'nombre' => 'CREMACIÓN DIRECTA',
                'descripcion' => 'Servicio de CREMACIÓN DIRECTA con atención profesional y respetuosa.',
                'precio' => 13800.00
            ],
            [
                'nombre' => 'INHUMACIÓN DIRECTA',
                'descripcion' => 'Servicio de INHUMACIÓN DIRECTA con servicios básicos y dignos.',
                'precio' => 13800.00
            ]
        ];

        foreach ($paquetes as $paquete) {
            Paquete::create($paquete);
        }
    }
}
