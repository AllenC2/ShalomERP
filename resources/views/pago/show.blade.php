@extends('layouts.app')

@section('template_title')
    {{ $pago->name ?? __('Show') . " " . __('Pago') }}
@endsection

@section('content')
<section class="d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="col-md-5">
        <div class="border rounded shadow-sm p-4" style="background: #fff; max-width: 400px; margin: auto; font-family: monospace;">
            <div class="text-center mb-3">
                <span class="fw-bold fs-4">Recibo de Pago</span>
                <div class="small text-muted">#{{ $pago->id }}</div>
            </div>
            <hr>
            <div class="mb-3 text-center">
                <span class="fs-5 fw-bold text-success">
                    ${{ number_format($pago->monto, 2) }} MXN
                </span>
            </div>
            <div class="mb-2">
                <span class="fw-bold">Contrato ID:</span>
                <span class="float-end">{{ $pago->contrato_id }}</span>
            </div>
            <div class="mb-2">
                <span class="fw-bold">Fecha de Pago:</span>
                <span class="float-end">{{ $pago->fecha_pago }}</span>
            </div>
            <div class="mb-2">
                <span class="fw-bold">Método de Pago:</span>
                <span class="float-end">{{ $pago->metodo_pago }}</span>
            </div>
            <div class="mb-2">
                <span class="fw-bold">Estado:</span>
                <span class="float-end">{{ $pago->estado }}</span>
            </div>
            <div class="mb-2">
                <span class="fw-bold">Documento:</span>
                <span class="float-end">{{ $pago->documento }}</span>
            </div>
            <hr>
            <div class="mb-2">
                <span class="fw-bold">Observaciones:</span>
                <div class="border rounded p-2 bg-light small mt-1">{{ $pago->observaciones }}</div>
            </div>
            <hr>
            <div class="text-center">
                <a class="btn btn-outline-secondary btn-sm" href="{{ route('pagos.index') }}">
                    {{ __('Volver') }}
                </a>
            </div>
        </div>
    </div>
</section>
@endsection
