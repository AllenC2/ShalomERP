<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Visita;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VisitaController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'contrato_id' => 'required|exists:contratos,id',
            'user_id' => 'required|exists:users,id',
            'comentarios' => 'required|string',
            'ubicacion_evidencia' => 'nullable|string',
        ]);

        $visita = new Visita();
        $visita->contrato_id = $request->contrato_id;
        $visita->user_id = $request->user_id;
        $visita->comentarios = $request->comentarios;
        
        if ($request->ubicacion_evidencia) {
            // Usar DB::raw para el punto geográfico si se envía (formato POINT(lng lat))
            $visita->ubicacion_evidencia = DB::raw("ST_GeomFromText('{$request->ubicacion_evidencia}')");
        }
        
        $visita->save();

        return back()->with('success', 'Visita registrada con éxito.');
    }
}
