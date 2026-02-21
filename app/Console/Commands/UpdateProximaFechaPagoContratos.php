<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Contrato;

class UpdateProximaFechaPagoContratos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'contratos:update-proxima-fecha';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualiza el campo proxima_fecha_pago para todos los contratos existentes basados en su cálculo de siguiente pago.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Actualizando próxima fecha de pago de contratos...');

        $contratos = Contrato::all();
        $bar = $this->output->createProgressBar(count($contratos));
        $bar->start();

        foreach ($contratos as $contrato) {
            $siguientePago = $contrato->siguiente_pago_calculado; // Usa el mutador existente
            if ($siguientePago) {
                // Guarda solo la fecha
                $contrato->proxima_fecha_pago = $siguientePago->fecha_pago;
            } else {
                // Si el contrato está al día y no hay siguientes pagos, o está finalizado
                $contrato->proxima_fecha_pago = null;
            }
            // Guarda sin disparar eventos para no modificar updated_at u otros hooks si no es necesario
            $contrato->saveQuietly();
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Actualización completada.');
    }
}
