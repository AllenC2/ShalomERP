@extends('layouts.app')

@section('template_title')
    {{ __('Revisión de Comisiones tras Eliminación de Pago') }}
@endsection

@section('content')
<div class="modern-container">
    <div class="page-wrapper">
        <a href="{{ route('contratos.show', $contrato->id) }}" class="modern-link mb-3 d-inline-block">
            <i class="bi bi-arrow-left me-1"></i>
            {{ __('Volver al Contrato') }}
        </a>

        <div class="page-header bg-white p-4 rounded shadow-sm mb-4">
            <h2 class="mb-2"><i class="bi bi-exclamation-triangle-fill text-warning me-2"></i> Revisión Manual de Comisiones</h2>
            <p class="text-muted mb-0">Se eliminó un pago recientemente. Verifica si las comisiones pagadas exceden el saldo disponible del contrato y ajústalas manualmente.</p>
        </div>

        @if($diferencia > 0.009)
            <div class="alert alert-danger d-flex align-items-center mb-4 shadow-sm" role="alert">
                <i class="bi bi-exclamation-circle-fill fs-3 me-3"></i>
                <div>
                    <h5 class="alert-heading mb-1 fw-bold">¡Atención! Comisiones pagadas exceden el saldo disponible</h5>
                    <p class="mb-0">
                        Hay un excedente de <strong>${{ number_format($diferencia, 2) }}</strong> pagado en parcialidades. 
                        Debes reducir el monto de una o más parcialidades para cuadrar los saldos.
                    </p>
                </div>
            </div>
        @else
            <div class="alert alert-success d-flex align-items-center mb-4 shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill fs-3 me-3"></i>
                <div>
                    <h5 class="alert-heading mb-1 fw-bold">Saldos de comisiones correctos</h5>
                    <p class="mb-0">Las comisiones pagadas actuales no exceden el saldo disponible tras la eliminación del pago. No se requiere acción inmediata, aunque puedes realizar ajustes si lo deseas.</p>
                </div>
            </div>
        @endif

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-white border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <h6 class="text-muted text-uppercase mb-2">Saldo Disponible</h6>
                        <h3 class="fw-bold mb-0 text-primary">${{ number_format($saldoDisponible, 2) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-white border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <h6 class="text-muted text-uppercase mb-2">Total Pagado (Parcialidades)</h6>
                        <h3 class="fw-bold mb-0 {{ $diferencia > 0.009 ? 'text-danger' : 'text-success' }}">
                            ${{ number_format($totalPagado, 2) }}
                        </h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-white border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <h6 class="text-muted text-uppercase mb-2">Diferencia a Revertir</h6>
                        <h3 class="fw-bold mb-0 {{ $diferencia > 0.009 ? 'text-danger' : 'text-secondary' }}">
                            ${{ number_format(max($diferencia, 0), 2) }}
                        </h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="card bg-white border-0 shadow-sm mb-5">
            <div class="card-header bg-white border-0 pt-4 pb-0">
                <h5 class="fw-bold"><i class="bi bi-list-check me-2 text-primary"></i>Listado de Parcialidades</h5>
                <p class="text-muted small">Modifica el monto de las parcialidades para revertir comisiones. Si indicas <strong>0</strong>, la parcialidad será eliminada.</p>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Comisión Principal</th>
                                <th>Empleado</th>
                                <th>Monto Total Comisión</th>
                                <th>Parcialidades Pagadas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($comisiones as $comision)
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark">{{ strtoupper($comision->tipo_comision) }}</div>
                                        <span class="badge {{ $comision->estado == 'Pagada' ? 'bg-success' : 'bg-warning text-dark' }}">{{ $comision->estado }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle me-2 bg-primary text-white" style="width: 32px; height: 32px; font-size: 0.8rem; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                                                {{ strtoupper(substr($comision->empleado->nombre ?? 'N', 0, 1) . substr($comision->empleado->apellido ?? 'A', 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="fw-semibold">{{ $comision->empleado->nombre ?? 'N/A' }} {{ $comision->empleado->apellido ?? '' }}</div>
                                                <small class="text-muted">ID: {{ $comision->empleado_id }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="fw-bold text-dark">
                                        ${{ number_format($comision->monto, 2) }}
                                    </td>
                                    <td>
                                        @if($comision->parcialidades->count() > 0)
                                            <ul class="list-group list-group-flush border-0">
                                                @foreach($comision->parcialidades as $parcialidad)
                                                    <li class="list-group-item bg-transparent px-0 border-bottom">
                                                        <form action="{{ route('pagos.procesar_reversion_comisiones', $contrato->id) }}" method="POST" class="d-flex align-items-center">
                                                            @csrf
                                                            <input type="hidden" name="parcialidad_id" value="{{ $parcialidad->id }}">
                                                            <div class="me-3">
                                                                <small class="text-muted d-block">{{ $parcialidad->created_at->format('d/m/Y H:i') }}</small>
                                                                <span class="badge bg-info">ID: {{ $parcialidad->id }}</span>
                                                            </div>
                                                            <div class="input-group input-group-sm me-2" style="width: 130px;">
                                                                <span class="input-group-text">$</span>
                                                                <input type="number" class="form-control" name="monto" value="{{ $parcialidad->monto }}" step="0.01" min="0" required>
                                                            </div>
                                                            <button type="submit" class="btn btn-sm btn-outline-primary" title="Actualizar Monto">
                                                                <i class="bi bi-save"></i> Modificar
                                                            </button>
                                                        </form>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <span class="text-muted fst-italic">Sin parcialidades pagadas</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">No hay comisiones registradas para este contrato.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
