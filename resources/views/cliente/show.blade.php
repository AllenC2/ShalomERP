@extends('layouts.app')

@section('template_title')
    {{ $cliente->name ?? __('Show') . " " . __('Cliente') }}
@endsection

@section('content')
<section class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center bg-primary text-white">
                    <h5 class="mb-0">{{ __('Tarjeta de') }} Cliente</h5>
                    <a class="btn btn-outline text-white btn-sm" href="{{ route('clientes.index') }}">
                        <i class="bi bi-arrow-left"></i> {{ __('Volver') }}
                    </a>
                </div>
                <div class="card-body bg-white">
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Nombre:</div>
                        <div class="col-sm-8">{{ $cliente->nombre }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Apellido:</div>
                        <div class="col-sm-8">{{ $cliente->apellido }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Email:</div>
                        <div class="col-sm-8">{{ $cliente->email }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Teléfono:</div>
                        <div class="col-sm-8">{{ $cliente->telefono }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Domicilio:</div>
                        <div class="col-sm-8">{{ $cliente->domicilio }}</div>
                    </div>
                </div>
            </div>

            {{-- Tabla de contratos --}}
            <div class="card mt-4 shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0">Contratos del Cliente</h6>
                </div>
                <div class="card-body bg-white p-0">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Paquete</th>
                                <th>Fecha de inicio</th>
                                <th>Fecha de fin</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cliente_contratos as $contrato)
                                <tr>
                                    <td>{{ $contrato->id }}</td>
                                    <td>{{ $contrato->paquete->nombre }}</td>
                                    <td>{{ \Carbon\Carbon::parse($contrato->fecha_inicio)->locale('es')->isoFormat('D [de] MMMM [de] YYYY') }}</td>
                                    <td>{{ $contrato->fecha_fin }}</td>
                                    <td>
                                        @php
                                            switch ($contrato->estado) {
                                                case 'Activo':
                                                    $badgeClass = 'bg-primary';
                                                    break;
                                                case 'Retraso':
                                                    $badgeClass = 'bg-warning text-dark';
                                                    break;
                                                case 'Cancelado':
                                                    $badgeClass = 'bg-danger';
                                                    break;
                                                default:
                                                    $badgeClass = 'bg-secondary';
                                            }
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">{{ ucfirst($contrato->estado) }}</span>
                                        
                                    </td>
                                    <td class="text-start">
                                        <a href="{{ route('contratos.show', $contrato->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> Abrir
                                        </a>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No hay contratos asociados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            {{-- Fin tabla de contratos --}}

        </div>
    </div>
</section>
@endsection
