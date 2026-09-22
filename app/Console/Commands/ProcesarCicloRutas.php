<?php

namespace App\Console\Commands;

use App\Services\GeneradorRutasService;
use Illuminate\Console\Command;

class ProcesarCicloRutas extends Command
{
    protected $signature = 'rutas:procesar-ciclo';

    protected $description = 'Genera ejecuciones del día para plantillas activas y cierra rutas pasadas como vencidas o incompletas';

    public function handle(GeneradorRutasService $generador): int
    {
        $cerradas = $generador->cerrarEjecucionesPasadas();
        $creadas = $generador->generarInstanciasDelDia();

        $this->info("Rutas cerradas: {$cerradas}. Ejecuciones creadas: {$creadas}.");

        return self::SUCCESS;
    }
}
