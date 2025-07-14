<?php

namespace App\Http\Controllers;


use App\Models\Contrato;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // Obtener los últimos 7 días
        $dias = collect();
        for ($i = 6; $i >= 0; $i--) {
            $dias->push(now()->subDays($i)->format('Y-m-d'));
        }

        // Consultar la cantidad de contratos por día
        $contratosPorDia = $dias->map(function ($fecha) {
            return 
                [
                    'fecha' => $fecha,
                    'cantidad' => \App\Models\Contrato::whereDate('created_at', $fecha)->count()
                ];
        });

        // Solo los valores para el gráfico
        $cantidades = $contratosPorDia->pluck('cantidad');
        $labels = $dias->map(function($fecha) {
            return \Carbon\Carbon::parse($fecha)->isoFormat('ddd'); // Ej: Lun, Mar, etc.
        });

        return view('home', [
            'contratosLabels' => $labels,
            'contratosData' => $cantidades
        ]);
    }
}
