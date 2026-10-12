<?php

namespace App\Console\Commands;

use App\Services\GeneradorRutasService;
use Illuminate\Console\Command;

class ProcesarCicloRutas extends Command
{
    protected $signature = 'rutas:procesar-ciclo';

    protected $description = 'Genera ejecuciones del día para plantillas activas';

    public function handle(GeneradorRutasService $generador): int
    {
        $creadas = $generador->generarInstanciasDelDia();

        $this->info("Ejecuciones creadas: {$creadas}.");

        return self::SUCCESS;
    }
}
