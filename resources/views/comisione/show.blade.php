@extends('layouts.app')

@section('template_title')
    {{ $comisione->name ?? __('Show') . " " . __('Comisione') }}
@endsection

@section('content')
<section class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="border rounded bg-white shadow-sm p-4" style="max-width: 400px; margin: auto; font-family: monospace;">
                <div class="text-center mb-3">
                    <h5 class="fw-bold mb-1">Recibo de Comisión</h5>
                    <small class="text-muted">#{{ $comisione->id }}</small>
                </div>
                <hr>
                <div class="mb-2">
                    <div class="d-flex justify-content-between">
                        <span>Folio:</span>
                        <span>{{ $comisione->id }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Contrato Id:</span>
                        <span>{{ $comisione->contrato->paquete->nombre }}#{{$comisione->contrato->id}}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Empleado Id:</span>
                        <span>{{ $comisione->empleado->nombre }} {{$comisione->empleado->apellido}}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Fecha Comisión:</span>
                        <span>{{ \Carbon\Carbon::parse($comisione->fecha_comision)->locale('es_MX')->translatedFormat('d \d\e F \d\e Y') }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Tipo Comisión:</span>
                        <span>{{ $comisione->tipo_comision }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Monto:</span>
                        <span>${{ number_format($comisione->monto, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Estado:</span>
                        <span class="badge {{ $comisione->estado == 'Activo' ? 'bg-success' : 'bg-secondary' }}">
                            {{ $comisione->estado }}
                        </span>
                    </div>
                </div>
                @if($comisione->observaciones)
                <hr>
                <div class="mb-2">
                    <strong>Observaciones:</strong>
                    <div class="text-muted small">{{ $comisione->observaciones }}</div>
                </div>
                @endif
                @if($comisione->documento)
                <div class="mb-2">
                    <strong>Documento:</strong>
                    <div class="text-muted small">{{ $comisione->documento }}</div>
                </div>
                @endif
                <hr>
                <div class="text-center">
                    <a class="btn btn-outline-primary btn-sm" href="javascript:history.back()">
                        <i class="bi bi-arrow-left"></i> {{ __('Regresar') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
