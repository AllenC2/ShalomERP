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
            'comentarios' => 'nullable|string',
            'ubicacion_evidencia' => 'required|string',
            'ruta_parada_id' => 'nullable|exists:ruta_paradas,id',
        ]);

        $contrato = \App\Models\Contrato::findOrFail($request->contrato_id);

        $visita = new Visita();
        $visita->contrato_id = $request->contrato_id;
        $visita->user_id = $request->user_id;
        $visita->comentarios = $request->comentarios;
        $visita->adeudo_momento = $contrato->saldo_pendiente;
        $visita->ruta_parada_id = $request->ruta_parada_id;
        
        if ($request->ubicacion_evidencia) {
            $visita->ubicacion_evidencia = DB::raw("ST_GeomFromText('{$request->ubicacion_evidencia}')");
        }
        
        $visita->save();

        if ($request->ruta_parada_id) {
            $parada = \App\Models\RutaParada::find($request->ruta_parada_id);
            if ($parada && $parada->estado === 'pendiente') {
                $parada->update(['estado' => 'visitada']);
            }
        }

        return back()->with('success', 'Visita registrada con éxito.');
    }
}
