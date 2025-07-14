@extends('layouts.app')

@section('template_title')
    {{ $empleado->name ?? __('Show') . " " . __('Empleado') }}
@endsection

@section('content')
<section class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-white border-bottom-0 d-flex justify-content-between align-items-center rounded-top-4">
                    <h5 class="mb-0 fw-bold text-primary">
                        <i class="bi bi-person-badge me-2"></i>{{ __('Ficha de Empleado') }}
                    </h5>
                    <a class="btn btn-outline-primary btn-sm" href="{{ route('empleados.index') }}">
                        <i class="bi bi-arrow-left"></i> {{ __('Volver') }}
                    </a>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-4">
                        <div class="bg-primary text-white rounded-circle d-flex justify-content-center align-items-center" style="width:60px; height:60px; font-size:2rem;">
                            {{ strtoupper(substr($empleado->nombre,0,1)) }}{{ strtoupper(substr($empleado->apellido,0,1)) }}
                        </div>
                        <div class="ms-3">
                            <h4 class="mb-0">{{ $empleado->nombre }} {{ $empleado->apellido }}</h4>
                            <small class="text-muted">{{ $empleado->email }}</small>
                        </div>
                    </div>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item px-0">
                            <strong><i class="bi bi-telephone me-2"></i>Teléfono:</strong>
                            <span class="ms-2">{{ $empleado->telefono }}</span>
                        </li>
                        <li class="list-group-item px-0">
                            <strong><i class="bi bi-house-door me-2"></i>Domicilio:</strong>
                            <span class="ms-2">{{ $empleado->domicilio }}</span>
                        </li>
                    </ul>
                </div>
                <!-- Tabla de comisiones -->
                <div class="m-3">
                    <h5 class="fw-bold text-secondary mb-3">
                        <i class="bi bi-cash-coin me-2"></i>Comisiones del Empleado
                    </h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Fecha</th>
                                    <th>Tipo</th>
                                    <th>Monto</th>
                                    <th>Estado</th>
                                    <th>Observaciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($comisiones as $comision)
                                    <tr>
                                        <td>{{ $comision->id }}</td>
                                        <td>{{ \Carbon\Carbon::parse($comision->fecha_comision)->locale('es_MX')->isoFormat('D [de] MMMM [de] YYYY') }}</td>
                                        <td>{{ $comision->tipo_comision }}</td>
                                        <td>${{ number_format($comision->monto, 2) }}</td>
                                        <td>{{ $comision->estado }}</td>
                                        <td>{{ $comision->observaciones }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">No hay comisiones registradas para este empleado.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
