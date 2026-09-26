<?php

namespace App\Http\Controllers;

use App\Models\Contrato;
use App\Models\Ruta;
use App\Models\RutaParada;
use App\Models\Visita;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class VisitaController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'contrato_id' => 'required_without:ruta_parada_ids|nullable|exists:contratos,id',
            'comentarios' => 'nullable|string',
            'ubicacion_evidencia' => ['required', 'string', 'regex:/^POINT\(\s*-?\d+(\.\d+)?\s+-?\d+(\.\d+)?\s*\)$/i'],
            'ruta_parada_id' => 'nullable|exists:ruta_paradas,id',
            'ruta_parada_ids' => 'nullable|array',
            'ruta_parada_ids.*' => 'exists:ruta_paradas,id',
        ]);

        $paradaIds = collect($request->input('ruta_parada_ids', []))
            ->when($request->ruta_parada_id, fn ($ids) => $ids->push($request->ruta_parada_id))
            ->filter()
            ->unique()
            ->values();

        $paradas = $paradaIds->isNotEmpty()
            ? RutaParada::with('ruta')->whereIn('id', $paradaIds)->get()
            : collect();

        if ($paradas->isEmpty() && ! $request->contrato_id) {
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => 'Selecciona un domicilio para registrar la visita.'], 422)
                : back()->withErrors(['ruta_parada_ids' => 'Selecciona un domicilio para registrar la visita.']);
        }

        $this->autorizarParadas($paradas);

        DB::transaction(function () use ($paradas, $request) {
            if ($paradas->isEmpty()) {
                $this->crearVisita($request->contrato_id, null, $request);
                return;
            }

            foreach ($paradas as $parada) {
                $this->crearVisita($parada->contrato_id, $parada, $request);
            }

            $this->refrescarEstadoRuta($paradas->first());
        });

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Visita registrada con éxito.',
            ]);
        }

        return back()->with('success', 'Visita registrada con éxito.');
    }

    protected function autorizarParadas($paradas): void
    {
        $user = Auth::user();
        if (! $user || $user->role !== 'empleado' || $paradas->isEmpty()) {
            return;
        }

        $empleadoId = $user->empleado?->id;
        foreach ($paradas as $parada) {
            if (! $empleadoId || (int) $parada->ruta?->empleado_id !== (int) $empleadoId) {
                abort(403, 'No puedes registrar visitas de una ruta que no te corresponde.');
            }
        }
    }

    protected function crearVisita(int $contratoId, ?RutaParada $parada, Request $request): Visita
    {
        $contrato = Contrato::findOrFail($contratoId);

        $visita = new Visita();
        $visita->contrato_id = $contrato->id;
        $visita->user_id = Auth::id();
        $visita->comentarios = $request->comentarios;
        $visita->adeudo_momento = $contrato->saldo_pendiente;
        $visita->ruta_parada_id = $parada?->id;
        $this->asignarUbicacion($visita, $request->ubicacion_evidencia);
        $visita->save();

        if ($parada && $parada->estado === RutaParada::ESTADO_PENDIENTE) {
            $parada->update(['estado' => RutaParada::ESTADO_VISITADA]);
        }

        return $visita;
    }

    protected function refrescarEstadoRuta(?RutaParada $parada): void
    {
        if (! $parada?->ruta) {
            return;
        }

        $ruta = $parada->ruta;
        $pendientes = $ruta->paradas()->where('estado', RutaParada::ESTADO_PENDIENTE)->exists();

        if (! $pendientes && in_array($ruta->estado, [Ruta::ESTADO_PLANEADA, Ruta::ESTADO_EN_CURSO], true)) {
            $ruta->update(['estado' => Ruta::ESTADO_COMPLETADA]);
        } elseif ($ruta->estado === Ruta::ESTADO_PLANEADA) {
            $ruta->update(['estado' => Ruta::ESTADO_EN_CURSO]);
        }
    }

    protected function asignarUbicacion(Visita $visita, string $punto): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            $visita->ubicacion_evidencia = DB::raw("ST_GeomFromText('" . $punto . "')");
            return;
        }

        $visita->ubicacion_evidencia = $punto;
    }
}
