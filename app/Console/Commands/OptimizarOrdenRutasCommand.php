<?php

namespace App\Console\Commands;

use App\Models\Ruta;
use App\Models\RutaPlantilla;
use App\Services\OptimizadorOrdenRutaService;
use Illuminate\Console\Command;

class OptimizarOrdenRutasCommand extends Command
{
    protected $signature = 'rutas:optimizar-orden
                            {--solo-plantillas : Solo reordena plantillas}
                            {--solo-rutas : Solo reordena instancias de ruta}';

    protected $description = 'Reordena paradas por vecino más cercano (casa del cobrador → siguiente más cercano)';

    public function handle(OptimizadorOrdenRutaService $optimizador): int
    {
        $soloPlantillas = (bool) $this->option('solo-plantillas');
        $soloRutas = (bool) $this->option('solo-rutas');

        $plantillasActualizadas = 0;
        $rutasActualizadas = 0;

        if (! $soloRutas) {
            $plantillas = RutaPlantilla::with(['paradas.contrato.cliente', 'empleado'])
                ->where('activa', true)
                ->get();

            foreach ($plantillas as $plantilla) {
                if ($optimizador->optimizarPlantilla($plantilla) > 0) {
                    $plantillasActualizadas++;
                }
            }
        }

        if (! $soloPlantillas) {
            $rutas = Ruta::with(['paradas.cliente', 'empleado', 'plantilla.paradas'])
                ->whereIn('estado', [Ruta::ESTADO_PLANEADA, Ruta::ESTADO_EN_CURSO, Ruta::ESTADO_DETENIDA])
                ->get();

            foreach ($rutas as $ruta) {
                if ($optimizador->optimizarRuta($ruta, sincronizarPlantilla: false) > 0) {
                    $rutasActualizadas++;
                }
            }
        }

        $this->info("Plantillas reordenadas: {$plantillasActualizadas}. Rutas reordenadas: {$rutasActualizadas}.");

        return self::SUCCESS;
    }
}
