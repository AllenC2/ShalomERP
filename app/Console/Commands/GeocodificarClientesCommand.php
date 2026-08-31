<?php

namespace App\Console\Commands;

use App\Models\Cliente;
use App\Models\Empleado;
use App\Services\GeocodingService;
use Illuminate\Console\Command;

class GeocodificarClientesCommand extends Command
{
    protected $signature = 'app:geocodificar {--tipo=clientes : Tipo a geocodificar: clientes o empleados} {--limit=50 : Límite de registros a procesar}';
    protected $description = 'Geocodifica direcciones de clientes y empleados usando Nominatim';

    public function handle(GeocodingService $geocoding)
    {
        $tipo = $this->option('tipo');
        $limit = (int) $this->option('limit');

        if ($tipo === 'clientes') {
            $this->geocodificarClientes($geocoding, $limit);
        } elseif ($tipo === 'empleados') {
            $this->geocodificarEmpleados($geocoding, $limit);
        } else {
            $this->error('Tipo no válido. Usa: clientes o empleados');
            return 1;
        }

        return 0;
    }

    protected function geocodificarClientes(GeocodingService $geocoding, int $limit)
    {
        $clientes = Cliente::whereNull('latitud')
            ->whereNotNull('domicilio_completo')
            ->where('domicilio_completo', '!=', '')
            ->limit($limit)
            ->get();

        $this->info("Geocodificando {$clientes->count()} clientes...");

        $bar = $this->output->createProgressBar($clientes->count());
        $bar->start();

        $exitosos = 0;
        $fallidos = 0;

        foreach ($clientes as $cliente) {
            $coords = $geocoding->geocode($cliente->domicilio_completo);

            if ($coords) {
                $cliente->update([
                    'latitud' => $coords['lat'],
                    'longitud' => $coords['lng'],
                ]);
                $exitosos++;
            } else {
                $fallidos++;
                $this->newLine();
                $this->warn("  Sin resultado: [{$cliente->id}] {$cliente->domicilio_completo}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Completado: {$exitosos} exitosos, {$fallidos} fallidos.");
    }

    protected function geocodificarEmpleados(GeocodingService $geocoding, int $limit)
    {
        $empleados = Empleado::whereNull('latitud')
            ->whereNotNull('domicilio')
            ->where('domicilio', '!=', '')
            ->limit($limit)
            ->get();

        $this->info("Geocodificando {$empleados->count()} empleados...");

        $bar = $this->output->createProgressBar($empleados->count());
        $bar->start();

        $exitosos = 0;
        $fallidos = 0;

        foreach ($empleados as $empleado) {
            $coords = $geocoding->geocode($empleado->domicilio);

            if ($coords) {
                $empleado->update([
                    'latitud' => $coords['lat'],
                    'longitud' => $coords['lng'],
                ]);
                $exitosos++;
            } else {
                $fallidos++;
                $this->newLine();
                $this->warn("  Sin resultado: [{$empleado->id}] {$empleado->domicilio}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Completado: {$exitosos} exitosos, {$fallidos} fallidos.");
    }
}
