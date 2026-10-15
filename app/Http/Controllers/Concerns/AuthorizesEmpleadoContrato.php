<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Contrato;
use App\Models\RutaParada;
use Illuminate\Support\Facades\Auth;

trait AuthorizesEmpleadoContrato
{
    /**
     * Admins: full access. Empleados: only contracts linked via comisión or ruta asignada.
     */
    protected function authorizeContratoAccess(Contrato $contrato): void
    {
        $user = Auth::user();
        if (! $user) {
            abort(401);
        }

        if ($user->role === 'admin') {
            return;
        }

        if ($user->role !== 'empleado') {
            abort(404);
        }

        $empleadoId = $user->empleado?->id;
        if (! $empleadoId) {
            abort(404);
        }

        $viaComision = $contrato->comisiones()
            ->where('empleado_id', $empleadoId)
            ->exists();

        $viaRuta = RutaParada::query()
            ->where('contrato_id', $contrato->id)
            ->whereHas('ruta', fn ($q) => $q->where('empleado_id', $empleadoId))
            ->exists();

        if (! $viaComision && ! $viaRuta) {
            abort(404);
        }
    }
}
