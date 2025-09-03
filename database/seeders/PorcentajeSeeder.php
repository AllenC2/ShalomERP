<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Porcentaje;
use App\Models\Paquete;

class PorcentajeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Definir los porcentajes específicos para cada paquete
        $porcentajesPorPaquete = [
            'ETERNIDAD' => [
                'asesor' => 3.03,
                'lider' => 0.61,
                'gerencia' => 1.52
            ],
            'SERENIDAD' => [
                'asesor' => 7.00,
                'lider' => 0.53,
                'gerencia' => 1.33
            ],
            'MEMORIA' => [
                'asesor' => 7.00,
                'lider' => 0.42,
                'gerencia' => 1.05
            ],
            'DESCANSO' => [
                'asesor' => 7.00,
                'lider' => 0.39,
                'gerencia' => 0.98
            ],
            'LEGADO' => [
                'asesor' => 7.00,
                'lider' => 0.52,
                'gerencia' => 0.86
            ],
            'TRANQUILIDAD' => [
                'asesor' => 7.00,
                'lider' => 0.49,
                'gerencia' => 0.82
            ],
            'PAZ' => [
                'asesor' => 7.00,
                'lider' => 0.44,
                'gerencia' => 0.74
            ],
            'SANTIDAD' => [
                'asesor' => 7.00,
                'lider' => 0.47,
                'gerencia' => 0.59
            ],
            'RECUERDO' => [
                'asesor' => 7.00,
                'lider' => 0.47,
                'gerencia' => 0.59
            ],
            'PROPIEDAD PANTEÓN' => [
                'asesor' => 7.00,
                'lider' => 0.49,
                'gerencia' => 0.49
            ],
            'CREMACIÓN DIRECTA' => [
                'asesor' => 7.00,
                'lider' => 0.72,
                'gerencia' => 1.81
            ],
            'INHUMACIÓN DIRECTA' => [
                'asesor' => 7.00,
                'lider' => 0.72,
                'gerencia' => 1.81
            ]
        ];

        // Obtener todos los paquetes
        $paquetes = Paquete::all();

        foreach ($paquetes as $paquete) {
            // Verificar si el paquete tiene porcentajes definidos
            if (isset($porcentajesPorPaquete[$paquete->nombre])) {
                $porcentajes = $porcentajesPorPaquete[$paquete->nombre];
                
                // Crear porcentajes para cada tipo
                $tiposPorcentaje = [
                    [
                        'paquete_id' => $paquete->id,
                        'cantidad_porcentaje' => $porcentajes['asesor'],
                        'tipo_porcentaje' => 'asesor',
                        'observaciones' => 'Porcentaje para asesores de ventas'
                    ],
                    [
                        'paquete_id' => $paquete->id,
                        'cantidad_porcentaje' => $porcentajes['lider'],
                        'tipo_porcentaje' => 'lider',
                        'observaciones' => 'Porcentaje para líderes de equipo'
                    ],
                    [
                        'paquete_id' => $paquete->id,
                        'cantidad_porcentaje' => $porcentajes['gerencia'],
                        'tipo_porcentaje' => 'gerencia',
                        'observaciones' => 'Porcentaje para gerencia'
                    ]
                ];

                foreach ($tiposPorcentaje as $porcentaje) {
                    Porcentaje::create($porcentaje);
                }
            }
        }
    }
}
