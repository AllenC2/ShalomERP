@extends('layouts.app')

@section('template_title')
    Recibo Consolidado de Comisiones
@endsection

@section('content')
    <style>
        @media print {

            /* Evitar saltos de página en elementos específicos */
            .bg-light,
            .border,
            .shadow-lg,
            .p-4,
            .pb-2 {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            /* Controlar saltos de página entre secciones */
            .bg-light.text-dark.p-4.pb-2 {
                page-break-after: avoid !important;
                break-after: avoid !important;
            }

            /* Mantener elementos juntos */
            .row {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            /* Evitar saltos de página innecesarios */
            * {
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
            }

            /* Asegurar que el contenido principal no se rompa */
            .border.shadow-lg {
                box-shadow: none !important;
                page-break-inside: avoid !important;
            }

            /* Optimizar espaciado para impresión */
            .p-4 {
                padding: 1rem !important;
            }

            .pb-2 {
                padding-bottom: 0.5rem !important;
            }

            /* Evitar salto de página específico después del header */
            .bg-light.text-dark.p-4.pb-2+.p-4 {
                page-break-before: avoid !important;
                break-before: avoid !important;
            }

            /* Evitar páginas en blanco al final */
            section:last-child,
            .text-center.p-4.d-print-none:last-child,
            .modal:last-child {
                page-break-after: avoid !important;
                break-after: avoid !important;
            }

            /* Ocultar completamente elementos que no son necesarios en impresión */
            .modal,
            script,
            .d-print-none,
            .btn {
                display: none !important;
                height: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            /* Optimizar márgenes para impresión */
            body,
            html {
                margin: 0 !important;
                padding: 0 !important;
            }

            section {
                margin: 0 !important;
                padding: 1rem !important;
            }

            .py-4 {
                padding-top: 0 !important;
                padding-bottom: 0 !important;
            }

            /* Control estricto del final del documento */
            @page {
                margin: 1cm;
                size: letter;
            }

            /* Eliminar cualquier contenido después del recibo principal */
            .modal,
            .modal+*,
            script,
            script+* {
                display: none !important;
                position: absolute !important;
                left: -9999px !important;
                top: -9999px !important;
                width: 0 !important;
                height: 0 !important;
                overflow: hidden !important;
            }

            /* Asegurar que el documento termine limpiamente */
            section:last-of-type {
                page-break-after: auto !important;
            }

            .shadow-lg {
                box-shadow: none !important;
            }

            .border.shadow-lg {
                box-shadow: none !important;
                border: 1px solid #dee2e6 !important;
            }

            .col-md-10 {
                max-width: 100% !important;
                width: 100% !important;
            }

            section.d-flex {
                margin: 0 !important;
                padding: 0 !important;
            }
        }
    </style>

    <section class="d-flex justify-content-center align-items-center py-4">
        <div class="col-md-10" style="max-width: 1000px;">
            <a href="{{ url()->previous() }}" class="modern-link mb-3 d-inline-block d-print-none">
                <i class="bi bi-arrow-left me-1"></i>
                {{ __('Regresar') }}
            </a>

            <div class="border shadow-lg p-0" style="background: #fff; margin: auto; font-family: 'Arial', sans-serif;">

                <!-- Header del Recibo -->
                <div class="bg-light text-dark p-4 pb-2">
                    <div class="row">
                        <div class="col-sm-8">
                            <div class="d-flex flex-column h-100" style="height: 100%;">
                                <div class="d-flex align-items-center" style="margin-bottom: auto;">
                                    <img src="{{ asset('shalom_logo.svg') }}" alt="Shalom Logo"
                                        style="height: 50px; margin-right: 15px;">
                                </div>
                                <div class="mb-2 mt-auto">
                                    <h2 class="fw-bold mb-0" style="font-size: 1.5rem; color: #2d3748;">
                                        Recibo de Comisiones
                                    </h2>
                                    <div class="text-muted" style="font-size: .8rem;">
                                        Período: {{ $fechaInicio->translatedFormat('d \d\e F \d\e Y') }} al
                                        {{ $fechaFin->translatedFormat('d \d\e F \d\e Y') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4 text-end">
                            <div class="small">
                                <strong>{{ infoEmpresa('razon_social') }}</strong><br>
                                {!! formatearDireccionEmpresa() !!}<br>
                                RFC: {{ infoEmpresa('rfc') }}<br>
                                @if(infoEmpresa('telefono'))
                                    Tel: {{ infoEmpresa('telefono') }}<br>
                                @endif
                                @if(infoEmpresa('email'))
                                    Email: {{ infoEmpresa('email') }}
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Body of Receipt -->
                <div class="p-4">
                    <div class="row mb-4">
                        <!-- Employee Info -->
                        <div class="col-sm-4">
                            <h6 class="text-muted text-uppercase small fw-bold mb-3">
                                <i class="bi bi-person-badge me-2"></i>Información del Asesor
                            </h6>
                            <div class="mb-3 p-0">
                                <div class="card border rounded h-100" style="background: none; border-color: #e3e3e3;">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="avatar-circle me-3 d-flex align-items-center justify-content-center"
                                                style="min-width: 45px; min-height: 45px; width: 45px; height: 45px; background: linear-gradient(135deg, #E1B240 0%, #79481D 100%); border-radius: 50%; color: white; border: 2px solid #dee2e6;">
                                                {{ strtoupper(substr($empleado->nombre, 0, 1) . substr($empleado->apellido, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="fw-semibold text-dark">{{ $empleado->nombre }}
                                                    {{ $empleado->apellido }}</div>
                                                <small class="text-muted">{{ $empleado->puesto ?? 'Asesor' }}</small>
                                            </div>
                                        </div>
                                        @if($empleado->email)
                                            <div class="small text-muted mb-1"><i
                                                    class="bi bi-envelope me-1"></i>{{ $empleado->email }}</div>
                                        @endif
                                        @if($empleado->telefono)
                                            <div class="small text-muted"><i
                                                    class="bi bi-telephone me-1"></i>{{ $empleado->telefono }}</div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Tarjeta para Firma y Sello -->
                            <div class="mt-4">
                                <h6 class="text-muted text-uppercase small fw-bold mb-3">
                                    <i class="bi bi-person-badge me-2"></i>Recibido
                                </h6>
                            </div>
                            <div class="card border rounded my-2" style="background: none; border-color: #e3e3e3;">
                                <div class="card-body p-2 text-center" style="min-height: 120px;">
                                    <div style="height: 60px; border-bottom: 1px dashed #bbb; margin-bottom: 8px;"></div>
                                    <div class="small text-muted">Firma de recibido</div>
                                </div>
                            </div>
                            <div class="small text-muted">
                                Recibo emitido el: <br>
                                {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}
                            </div>
                        </div>

                        <!-- Commissions Details -->
                        <div class="col-sm-8">
                            <!-- Comisiones Pagadas -->
                            <h6 class="text-muted text-uppercase small fw-bold mb-3">
                                <i class="bi bi-list-check me-2"></i>Comisiones Pagadas / Hechas
                                ({{ $comisionesPagadas->count() }})
                            </h6>

                            @if($comisionesPagadas->count() > 0)
                                <div class="table-responsive border rounded mb-4">
                                    <table class="table table-sm table-striped mb-0" style="font-size: 0.85rem;">
                                        <thead class="bg-light text-dark">
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Contrato / Cliente</th>
                                                <th>Tipo</th>
                                                <th class="text-end">Monto</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($comisionesPagadas as $comision)
                                                <tr>
                                                    <td>{{ \Carbon\Carbon::parse($comision->fecha_comision)->format('d/m/Y') }}</td>
                                                    <td>
                                                        @if($comision->contrato && $comision->contrato->cliente)
                                                            #{{ $comision->contrato_id }} - {{ $comision->contrato->cliente->nombre }}
                                                            {{ $comision->contrato->cliente->apellido }}
                                                        @elseif($comision->contrato)
                                                            #{{ $comision->contrato_id }} - N/A
                                                        @else
                                                            N/A
                                                        @endif
                                                    </td>
                                                    <td>{{ ucfirst($comision->tipo_comision) }}</td>
                                                    <td class="text-end fw-bold">${{ number_format($comision->monto, 2) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr class="table-light">
                                                <td colspan="3" class="text-end fw-bold">TOTAL PAGADO:</td>
                                                <td class="text-end fw-bold" style="font-size: 1.1rem; color: #2d3748;">
                                                    ${{ number_format($totalPagadas, 2) }} MXN
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            @else
                                <div class="alert alert-info py-2 text-center small mb-4">
                                    No se encontraron comisiones pagadas para este asesor en el período seleccionado.
                                </div>
                            @endif

                            <!-- Comisiones Pendientes (Opcional) -->
                            @if($incluirPendientes)
                                <h6 class="text-muted text-uppercase small fw-bold mb-3 mt-4">
                                    <i class="bi bi-clock-history me-2"></i>Comisiones Pendientes
                                    ({{ $comisionesPendientes->count() }})
                                </h6>

                                @if($comisionesPendientes->count() > 0)
                                    <div class="table-responsive border rounded mb-4" style="opacity: 0.85;">
                                        <table class="table table-sm table-striped mb-0" style="font-size: 0.85rem;">
                                            <thead class="bg-light text-dark">
                                                <tr>
                                                    <th>Fecha</th>
                                                    <th>Contrato / Cliente</th>
                                                    <th>Tipo</th>
                                                    <th class="text-end">Monto Estimado</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($comisionesPendientes as $comision)
                                                    <tr>
                                                        <td>{{ \Carbon\Carbon::parse($comision->fecha_comision)->format('d/m/Y') }}</td>
                                                        <td>
                                                            @if($comision->contrato && $comision->contrato->cliente)
                                                                #{{ $comision->contrato_id }} - {{ $comision->contrato->cliente->nombre }}
                                                                {{ $comision->contrato->cliente->apellido }}
                                                            @elseif($comision->contrato)
                                                                #{{ $comision->contrato_id }} - N/A
                                                            @else
                                                                N/A
                                                            @endif
                                                        </td>
                                                        <td>{{ ucfirst($comision->tipo_comision) }}</td>
                                                        <td class="text-end fw-bold text-muted">
                                                            ${{ number_format($comision->monto_restante_calculado, 2) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                                <tr class="table-light">
                                                    <td colspan="3" class="text-end fw-bold text-muted">TOTAL PENDIENTE:</td>
                                                    <td class="text-end fw-bold text-muted" style="font-size: 1.1rem;">
                                                        ${{ number_format($totalPendientes, 2) }} MXN
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                @else
                                    <div class="alert alert-light border py-2 text-center small text-muted mb-4">
                                        No hay comisiones pendientes en este período.
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Botón de impresión -->
                <div class="text-center p-4 d-print-none border-top" style="background-color: #f8f9fa;">
                    <button onclick="window.print()" class="btn btn-primary btn-lg px-4 py-2" {{ ($comisionesPagadas->count() + ($incluirPendientes ? $comisionesPendientes->count() : 0)) == 0 ? 'disabled' : '' }}>
                        <i class="bi bi-printer me-2"></i>
                        Imprimir Recibo
                    </button>
                </div>
            </div>
        </div>
    </section>
@endsection