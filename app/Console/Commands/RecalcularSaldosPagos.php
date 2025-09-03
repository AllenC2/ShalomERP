<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Contrato;
use App\Models\Pago;

class RecalcularSaldosPagos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pagos:recalcular-saldos {--dry-run : Solo mostrar los cambios sin aplicarlos}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalcula el saldo restante de todos los pagos basándose en el monto total del contrato';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info('🔍 MODO DRY-RUN: Solo se mostrarán los cambios, no se aplicarán');
        }
        
        $this->info('🔄 Iniciando recálculo de saldos de pagos...');
        
        // Obtener todos los contratos que tienen pagos
        $contratos = Contrato::has('pagos')->with(['pagos' => function($query) {
            $query->orderBy('fecha_pago', 'asc')->orderBy('created_at', 'asc');
        }])->get();
        
        $totalContratos = $contratos->count();
        $totalPagosActualizados = 0;
        
        $this->info("📊 Se encontraron {$totalContratos} contratos con pagos");
        
        $progressBar = $this->output->createProgressBar($totalContratos);
        $progressBar->start();
        
        foreach ($contratos as $contrato) {
            $pagosDelContrato = $contrato->pagos;
            $saldoAcumulado = 0;
            
            foreach ($pagosDelContrato as $pago) {
                if ($pago->estado === 'Hecho') {
                    $saldoAcumulado += $pago->monto;
                }
                
                $nuevoSaldo = max(0, $contrato->monto_total - $saldoAcumulado);
                
                if ($pago->saldo_restante != $nuevoSaldo) {
                    if ($dryRun) {
                        $this->line("\n💰 Pago ID {$pago->id} (Contrato #{$contrato->id}):");
                        $this->line("   - Saldo actual: {$pago->saldo_restante}");
                        $this->line("   - Saldo nuevo: {$nuevoSaldo}");
                        $this->line("   - Monto pago: {$pago->monto} ({$pago->estado})");
                    } else {
                        $pago->update(['saldo_restante' => $nuevoSaldo]);
                    }
                    $totalPagosActualizados++;
                }
            }
            
            $progressBar->advance();
        }
        
        $progressBar->finish();
        
        if ($dryRun) {
            $this->info("\n\n✅ DRY-RUN completado:");
            $this->info("📋 {$totalPagosActualizados} pagos necesitan ser actualizados");
            $this->info("💡 Ejecuta el comando sin --dry-run para aplicar los cambios");
        } else {
            $this->info("\n\n✅ Recálculo completado:");
            $this->info("📋 {$totalPagosActualizados} pagos actualizados exitosamente");
        }
        
        return Command::SUCCESS;
    }
}
