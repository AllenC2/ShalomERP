<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Ajuste;

class AjustesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Configuraciones de ejemplo para la empresa
        $ajustesEmpresa = [
            [
                'nombre' => 'empresa_razon_social',
                'valor' => 'Shalom Servicios Empresariales S.A. de C.V.',
                'tipo' => 'string',
                'descripcion' => 'Razón social de la empresa que aparece en recibos',
                'activo' => true
            ],
            [
                'nombre' => 'empresa_rfc',
                'valor' => 'SSE123456789',
                'tipo' => 'string',
                'descripcion' => 'RFC de la empresa',
                'activo' => true
            ],
            [
                'nombre' => 'empresa_calle_numero',
                'valor' => '',
                'tipo' => 'string',
                'descripcion' => 'Dirección: Calle y número',
                'activo' => true
            ],
            [
                'nombre' => 'empresa_colonia',
                'valor' => '',
                'tipo' => 'string',
                'descripcion' => 'Dirección: Colonia',
                'activo' => true
            ],
            [
                'nombre' => 'empresa_ciudad',
                'valor' => '',
                'tipo' => 'string',
                'descripcion' => 'Dirección: Ciudad',
                'activo' => true
            ],
            [
                'nombre' => 'empresa_estado',
                'valor' => '',
                'tipo' => 'string',
                'descripcion' => 'Dirección: Estado',
                'activo' => true
            ],
            [
                'nombre' => 'empresa_pais',
                'valor' => 'México',
                'tipo' => 'string',
                'descripcion' => 'Dirección: País',
                'activo' => true
            ],
            [
                'nombre' => 'empresa_codigo_postal',
                'valor' => '',
                'tipo' => 'string',
                'descripcion' => 'Código postal de la empresa',
                'activo' => true
            ],
            [
                'nombre' => 'empresa_telefono',
                'valor' => '',
                'tipo' => 'string',
                'descripcion' => 'Número de teléfono de la empresa',
                'activo' => true
            ],
            [
                'nombre' => 'empresa_email',
                'valor' => '',
                'tipo' => 'string',
                'descripcion' => 'Email corporativo de la empresa',
                'activo' => true
            ]
        ];

        foreach ($ajustesEmpresa as $ajuste) {
            Ajuste::updateOrCreate(
                ['nombre' => $ajuste['nombre']],
                $ajuste
            );
        }
    }
}
