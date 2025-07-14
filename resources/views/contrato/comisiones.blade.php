@extends('layouts.app')

@section('template_title')
    Comisiones del Contrato #{{ $contrato->id }}
@endsection

@section('content')
<section class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Comisiones del Contrato {{$contrato->paquete->nombre}}#{{ $contrato->id }}</h5>
                    <a class="btn btn-light btn-sm" href="{{ route('contratos.show', $contrato->id) }}">
                        <i class="bi bi-arrow-left"></i> Regresar al contrato
                    </a>
                </div>
                <div class="card-body bg-white">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-primary">
                                <tr>
                                    <th>ID</th>
                                    <th>Empleado</th>
                                    <th>Tipo Comisión</th>
                                    <th>Monto</th>
                                    <th>Observaciones</th>
                                    <th>Fecha Comisión</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($comisiones as $comision)
                                    <tr>
                                        <td>{{ $comision->id }}</td>
                                        <td>{{ $comision->empleado->nombre ?? 'N/A' }} {{ $comision->empleado->apellido ?? '' }}</td>
                                        <td>{{ $comision->tipo_comision }}</td>
                                        <td>${{ number_format($comision->monto, 2) }}</td>
                                        <td>{{ $comision->observaciones }}</td>
                                        <td>{{ \Carbon\Carbon::parse($comision->fecha_comision)->locale('es')->isoFormat('D [de] MMMM [de] YYYY') }}</td>
                                        <td>
                                            <span class="badge {{ $comision->estado == 'Activo' ? 'bg-success' : 'bg-secondary' }}">
                                                {{ $comision->estado }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('comisiones.show', $comision->id) }}" class="btn btn-outline-primary btn-sm">
                                                <i class="bi bi-eye"></i> Ver comisión
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">No hay comisiones registradas para este contrato.</td>
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
