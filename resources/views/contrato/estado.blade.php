@extends('layouts.app')

@section('template_title')
    @if(($filtrarPorPeriodo ?? false) && $periodoSeleccionado)
        Estado de Cuenta - Período {{ $periodoSeleccionado['numero'] }} - Contrato #{{ $contrato->id }}
    @else
        Estado de Cuenta - Contrato #{{ $contrato->id }}
    @endif
@endsection

@section('content')


    <section class="d-flex justify-content-center align-items-center py-4">
        <div class="col-md-8" style="max-width: 800px;">
            <div class="d-flex gap-2 mb-3 d-print-none">
                <a href="{{ route('contratos.show', $contrato->id) }}" class="modern-link d-inline-block">
                    <i class="bi bi-arrow-left me-1"></i>
                    {{ __('Regresar al Contrato') }}
                </a>


                @if($filtrarPorPeriodo ?? false)
                    <a href="{{ route('contratos.estado', $contrato->id) }}" class="modern-link d-inline-block">
                        <i class="bi bi-list-ul me-1"></i>
                        Ver Estado Completo
                    </a>
                @endif
            </div>
            <!-- Estado de cuenta imprimible -->

            <div class="p-4" style=" margin: auto; font-family: 'Arial', sans-serif;" id="estado-cuenta">
                <!-- Header del documento -->
                <div class="text-dark pb-2">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="d-flex flex-column h-100">
                                <div class="align-items-center mb-3 ">
                                    <img src="{{ asset('shalom_logo.svg') }}" alt="Logo Shalom" style="height: 50px;"
                                        class="me-3 mb-2">
                                    <div class="mb-5">
                                        <p class="text-muted" style="font-size: 0.6em; line-height: 1em;">
                                            {{ $empresa['nombre']}}<br>
                                            {{ $empresa['calle_numero'] }}
                                            {{ $empresa['colonia'] }}
                                            <br>{{ $empresa['municipio'] }} {{ $empresa['estado'] }}
                                            C.P. {{ $empresa['codigo_postal'] }} <br>
                                        </p>
                                    </div>
                                    <div>
                                        <h5 class="mb-0">{{ $contrato->cliente->nombre }}
                                            {{ $contrato->cliente->apellido }}
                                        </h5>
                                        <h6>
                                            {{ $contrato->cliente->calle_y_numero ?: 'Sin domicilio' }}<br>
                                            {{ $contrato->cliente->colonia ?: '' }},
                                            {{ $contrato->cliente->municipio }} <br>
                                            {{ $contrato->cliente->estado ?: '' }}<br>
                                            C.P.
                                            {{ $contrato->cliente->codigo_postal ?: '--' }}
                                        </h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 text-end">
                            <h5 class="mb-2 fw-bold">ESTADO DE CUENTA</h5>

                            <p class="text-muted mb-0">
                                {{$contrato->paquete->nombre}} #{{ $contrato->id }}
                                @if(($filtrarPorPeriodo ?? false) && $periodoSeleccionado)
                                    - Período {{ $periodoSeleccionado['numero'] }}
                                @endif
                            </p>

                            @if(($filtrarPorPeriodo ?? false) && $periodoSeleccionado)
                                <p class="small mb-1">
                                    <strong>Del:</strong>
                                    {{ $periodoSeleccionado['fecha_inicio']->translatedFormat('d \d\e F \d\e Y') }}
                                </p>
                                <p class="small mb-2">
                                    <strong>Al:</strong>
                                    {{ $periodoSeleccionado['fecha_fin']->translatedFormat('d \d\e F \d\e Y') }}
                                </p>
                            @endif

                            <p>
                                @if($filtrarPorPeriodo ?? false)
                                    Total a pagar:
                                    ${{ number_format($pagosRealizados->sum('monto') + $pagosPendientes->sum('monto') + ($parcialidadesPeriodo ?? collect())->sum('monto'), 2) }}
                                @else
                                    Total de contrato: ${{ number_format($contrato->monto_total, 2) }}
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Resumen financiero -->
                <div class="resumen-financiero mb-3">

                    <div class="row g-4 align-items-start">
                        <!-- Detalles y Progreso (4 columnas) -->
                        <div class="col-sm-4">
                            <div class="progress-section mb-0 h-100">
                                <div class="mb-3">
                                    <h6 class="fw-bold mb-2"><i class="bi bi-file-earmark-text-fill me-2"></i>Resumen
                                    </h6>

                                    <div class="small text-muted">
                                        <div class="mb-1">
                                            <strong>Paquete:</strong> {{ $contrato->paquete->nombre }}
                                        </div>
                                        <div class="mb-1">
                                            <strong>Inicio:</strong>
                                            {{ \Carbon\Carbon::parse($contrato->fecha_inicio)->translatedFormat('d M Y') }}
                                        </div>
                                        <div class="mb-1">
                                            <strong>Fin:</strong>
                                            {{ \Carbon\Carbon::parse($contrato->fecha_fin)->translatedFormat('d M Y') }}
                                        </div>
                                        @if(($filtrarPorPeriodo ?? false) && $periodoSeleccionado)
                                            <div
                                                class="mb-1 mt-2 p-2 bg-light rounded border border-secondary border-opacity-10">
                                                <strong>
                                                    @if(is_numeric($periodoSeleccionado['numero']))
                                                        {{ numeroOrdinal($periodoSeleccionado['numero']) }} Periodo
                                                    @else
                                                        Período {{ $periodoSeleccionado['numero'] }}
                                                    @endif
                                                </strong>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="progress-header">
                                    @if(isset($filtrarPorPeriodo) && $filtrarPorPeriodo && isset($montoCuotaPeriodo))
                                        <span class="progress-label small">Progreso </span>
                                    @else
                                        <span class="progress-label small">Progreso </span>
                                    @endif
                                    <span
                                        class="progress-percentage small">{{ number_format($porcentajePagado, 1) }}%</span>
                                </div>
                                <div class="progress progress-modern mb-2">
                                    <div class="progress-bar {{ $porcentajePagado >= 100 ? 'bg-gradient-success' : 'bg-gradient-warning' }}"
                                        role="progressbar" style="width: {{ min(100, $porcentajePagado) }}%"
                                        aria-valuenow="{{ $porcentajePagado }}" aria-valuemin="0" aria-valuemax="100">
                                    </div>
                                </div>
                                @if(isset($filtrarPorPeriodo) && $filtrarPorPeriodo && isset($montoCuotaPeriodo))
                                    <small class="text-muted d-block" style="font-size: 0.7rem;">
                                        Pagado: ${{ number_format($totalPagado, 2) }} /
                                        ${{ number_format($montoCuotaPeriodo, 2) }}
                                    </small>
                                @endif
                            </div>
                        </div>

                        <!-- Lista Financiera (8 columnas) -->
                        <div class="col-sm-8">
                            <div class="financial-summary-list mb-0">
                                <!-- Monto Total -->
                                <div class="financial-item py-2">
                                    <div class="financial-item-icon" style="width: 36px; height: 36px;">
                                        <i class="bi bi-currency-dollar" style="font-size: 1.1rem;"></i>
                                    </div>
                                    <div class="financial-item-details">
                                        <div class="financial-item-label">Monto Total</div>
                                        <div class="financial-item-subtext">Valor del contrato</div>
                                    </div>
                                    <div class="financial-item-amount">
                                        ${{ number_format($contrato->monto_total, 2) }}
                                    </div>
                                </div>

                                <!-- Total Pagado -->
                                <div class="financial-item py-2">
                                    <div class="financial-item-icon" style="width: 36px; height: 36px;">
                                        <i class="bi bi-check-circle" style="font-size: 1.1rem;"></i>
                                    </div>
                                    <div class="financial-item-details">
                                        <div class="financial-item-label">Total Pagado</div>
                                        <div class="financial-item-subtext">
                                            @if(($filtrarPorPeriodo ?? false) && ($parcialidadesPeriodo ?? collect())->count() > 0)
                                                Incluye parc.
                                            @else
                                                {{ number_format($porcentajePagado, 1) }}% del total
                                            @endif
                                        </div>
                                    </div>
                                    <div class="financial-item-amount">
                                        ${{ number_format($totalPagado, 2) }}
                                    </div>
                                </div>

                                <!-- Saldo Pendiente -->
                                <div class="financial-item py-2">
                                    <div class="financial-item-icon" style="width: 36px; height: 36px;">
                                        <i class="bi bi-clock-history" style="font-size: 1.1rem;"></i>
                                    </div>
                                    <div class="financial-item-details">
                                        <div class="financial-item-label">Saldo Pendiente</div>
                                        <div class="financial-item-subtext">
                                            {{ number_format(100 - $porcentajePagado, 1) }}% restante
                                        </div>
                                    </div>
                                    <div class="financial-item-amount">
                                        ${{ number_format($saldoPendiente, 2) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mensaje informativo para período filtrado sin pagos -->
                @if(($filtrarPorPeriodo ?? false) && $periodoSeleccionado && $pagosRealizados->count() == 0 && $pagosPendientes->count() == 0 && ($parcialidadesPeriodo ?? collect())->count() == 0)
                    <div class="alert alert-info">
                        <h5><i class="bi bi-info-circle me-2"></i>Período sin actividad</h5>
                        <p class="mb-2">
                            No se encontraron pagos (realizados, pendientes o parcialidades) para el
                            <strong>Período {{ $periodoSeleccionado['numero'] }}</strong>
                            ({{ $periodoSeleccionado['fecha_inicio']->translatedFormat('d \d\e F') }} -
                            {{ $periodoSeleccionado['fecha_fin']->translatedFormat('d \d\e F \d\e Y') }}).
                        </p>
                        <a href="{{ route('contratos.estado', $contrato->id) }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-list-ul me-1"></i>Ver estado completo del contrato
                        </a>
                    </div>
                @endif

                <!-- Historial de pagos realizados -->
                @if($pagosRealizados->count() > 0)
                    <div class="historial-pagos mb-3">
                        <div class="table-container">
                            <div class="table-responsive">
                                <table class="table table-modern">
                                    <thead>
                                        <tr>
                                            <th><i class="bi bi-calendar3 me-2"></i>Fecha</th>
                                            <th><i class="bi bi-currency-dollar me-2"></i>Monto</th>
                                            <th><i class="bi bi-credit-card me-2"></i>Método</th>
                                            <th class="no-print"><i class="bi bi-chat-text me-2"></i>Observaciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($pagosRealizados as $index => $pago)
                                            <tr class="table-row-hover">
                                                <td>
                                                    <div class="date-cell">
                                                        <strong>{{ \Carbon\Carbon::parse($pago->fecha_pago)->format('d/m/Y') }}</strong>
                                                    </div>
                                                </td>

                                                <td>
                                                    <div class="monto-cell success">
                                                        <span class="monto-principal">${{ number_format($pago->monto, 2) }}</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="metodo-cell">
                                                        <i
                                                            class="bi bi-{{ $pago->metodo_pago === 'efectivo' ? 'cash' : ($pago->metodo_pago === 'tarjeta' ? 'credit-card' : 'bank') }} me-2"></i>
                                                        {{ $pago->metodo_pago ?: 'No especificado' }}
                                                    </div>
                                                </td>

                                                <td class="no-print">
                                                    <div class="observaciones-cell">
                                                        {{ Str::limit($pago->observaciones ?: 'Sin observaciones', 50) }}
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Mensaje informativo cuando solo hay parcialidades -->
                @if(($filtrarPorPeriodo ?? false) && $periodoSeleccionado && $pagosRealizados->count() == 0 && $pagosPendientes->count() == 0 && ($parcialidadesPeriodo ?? collect())->count() > 0)
                    <div class="alert alert-warning mb-3">
                        <h5><i class="bi bi-info-circle me-2"></i>Período con parcialidades únicamente</h5>
                        <p class="mb-2">
                            Durante el <strong>Período {{ $periodoSeleccionado['numero'] }}</strong>
                            ({{ $periodoSeleccionado['fecha_inicio']->translatedFormat('d \d\e F') }} -
                            {{ $periodoSeleccionado['fecha_fin']->translatedFormat('d \d\e F \d\e Y') }})
                            no se completaron cuotas, pero <strong>sí se registraron
                                {{ ($parcialidadesPeriodo ?? collect())->count() }} parcialidad(es)</strong>
                            por un total de
                            <strong>${{ number_format(($parcialidadesPeriodo ?? collect())->sum('monto'), 2) }}</strong>.
                        </p>
                    </div>
                @endif

                <!-- Parcialidades del período (solo cuando se filtra por período) -->
                @if(($filtrarPorPeriodo ?? false) && ($parcialidadesPeriodo ?? collect())->count() > 0)
                    <div class="parcialidades-periodo mb-3">
                        <div class="section-header">
                            <div class="section-header-content">
                                <div class="section-icon">
                                    <i class="bi bi-pie-chart"></i>
                                </div>
                                <h4 class="section-title">Parcialidades Registradas en el Período</h4>
                            </div>
                        </div>
                        <div class="table-container">
                            <div class="table-responsive">
                                <table class="table table-modern">
                                    <thead>
                                        <tr>
                                            <th><i class="bi bi-calendar3 me-2"></i>Fecha</th>
                                            <th><i class="bi bi-currency-dollar me-2"></i>Monto</th>
                                            <th><i class="bi bi-credit-card me-2"></i>Método</th>
                                            <th class="no-print"><i class="bi bi-chat-text me-2"></i>Observaciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($parcialidadesPeriodo as $parcialidad)
                                            <tr class="table-row-hover">
                                                <td>
                                                    <div class="date-cell">
                                                        <strong>{{ \Carbon\Carbon::parse($parcialidad->fecha_pago)->format('d/m/Y') }}</strong>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="monto-cell success">
                                                        <span
                                                            class="monto-principal">${{ number_format($parcialidad->monto, 2) }}</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="metodo-cell">
                                                        <i
                                                            class="bi bi-{{ $parcialidad->metodo_pago === 'efectivo' ? 'cash' : ($parcialidad->metodo_pago === 'tarjeta' ? 'credit-card' : 'bank') }} me-2"></i>
                                                        {{ $parcialidad->metodo_pago ?: 'No especificado' }}
                                                    </div>
                                                </td>

                                                <td class="no-print">
                                                    <div class="observaciones-cell">
                                                        {{ Str::limit($parcialidad->observaciones ?: 'Sin observaciones', 50) }}
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Resumen de parcialidades -->
                        <div class="mt-3">
                            <div class="alert alert-success">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <strong><i class="bi bi-info-circle me-2"></i>Resumen de Parcialidades</strong><br>
                                        <small>Se registraron {{ $parcialidadesPeriodo->count() }} parcialidad(es) durante
                                            este
                                            período.</small>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <strong>Total: ${{ number_format($parcialidadesPeriodo->sum('monto'), 2) }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Cuotas pendientes -->
                @if($pagosPendientes->count() > 0)
                    <div class="cuotas-pendientes mb-3">
                        <div class="section-header">
                            <div class="section-header-content">
                                <div class="section-icon">
                                    <i class="bi bi-hourglass-split"></i>
                                </div>
                                <h4 class="section-title">Cuotas Pendientes</h4>
                            </div>
                        </div>
                        <div class="table-container">
                            <div class="table-responsive">
                                <table class="table table-modern">
                                    <thead>
                                        <tr>
                                            <th><i class="bi bi-calendar-event me-2"></i>Vencimiento</th>
                                            <th><i class="bi bi-currency-dollar me-2"></i>Monto Original</th>
                                            <th><i class="bi bi-exclamation-circle me-2"></i>Pendiente</th>
                                            <th><i class="bi bi-flag me-2"></i>Estado</th>
                                            <th><i class="bi bi-clock me-2"></i>Días</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($pagosPendientes as $pago)
                                            @php
                                                $fechaVencimiento = \Carbon\Carbon::parse($pago->fecha_pago);
                                                $diasDiferencia = $fechaVencimiento->diffInDays(now(), false);
                                                $esVencida = $fechaVencimiento->isPast();
                                                $tolerancia = \App\Models\Ajuste::obtenerToleranciaPagos();
                                                $enTolerancia = $esVencida && $diasDiferencia <= $tolerancia;
                                                $realmenteVencida = $esVencida && $diasDiferencia > $tolerancia;

                                                $estadoClass = $realmenteVencida ? 'vencida' : ($enTolerancia ? 'tolerancia' : ($fechaVencimiento->isToday() ? 'hoy' : 'pendiente'));
                                            @endphp
                                            <tr class="table-row-hover cuota-{{ $estadoClass }}">
                                                <td>
                                                    <div class="fecha-cell">
                                                        <strong>{{ $fechaVencimiento->format('d/m/Y') }}</strong>
                                                        <small
                                                            class="text-muted d-block">{{ $fechaVencimiento->format('l') }}</small>
                                                    </div>
                                                </td>

                                                <td>
                                                    <div class="monto-cell">
                                                        <span class="monto-original">${{ number_format($pago->monto, 2) }}</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="monto-cell pendiente">
                                                        <span
                                                            class="monto-pendiente">${{ number_format($pago->monto_pendiente, 2) }}</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="estado-cell">
                                                        @if($realmenteVencida)
                                                            <span class="estado-badge estado-vencida">
                                                                <i class="bi bi-exclamation-triangle me-1"></i>Vencida
                                                            </span>
                                                        @elseif($enTolerancia)
                                                            <span class="estado-badge estado-tolerancia">
                                                                <i class="bi bi-clock me-1"></i>En Tolerancia
                                                            </span>
                                                        @elseif($fechaVencimiento->isToday())
                                                            <span class="estado-badge estado-hoy">
                                                                <i class="bi bi-calendar-check me-1"></i>Vence Hoy
                                                            </span>
                                                        @else
                                                            <span class="estado-badge estado-pendiente">
                                                                <i class="bi bi-hourglass me-1"></i>Pendiente
                                                            </span>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="dias-cell">
                                                        @if($esVencida)
                                                            <span class="dias-vencida">
                                                                <i class="bi bi-arrow-down me-1"></i>
                                                                {{ intval($diasDiferencia) }} día(s)
                                                            </span>
                                                        @elseif($fechaVencimiento->isToday())
                                                            <span class="dias-hoy">
                                                                <i class="bi bi-calendar-day me-1"></i>
                                                                Hoy
                                                            </span>
                                                        @else
                                                            <span class="dias-futuro">
                                                                <i class="bi bi-arrow-up me-1"></i>
                                                                {{ intval($diasDiferencia) }} día(s)
                                                            </span>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Observaciones del contrato -->
                @if($contrato->observaciones)
                    <div class="observaciones mb-2">
                        <h5 class="section-title">Observaciones del Contrato</h5>
                        <div class="alert alert-info">
                            {{ $contrato->observaciones }}
                        </div>
                    </div>
                @endif

                <!-- Footer del documento -->
                <div class="documento-footer">
                    <div class="footer-content">
                        <div class="footer-main">
                            <div class="footer-logo">
                                <h5 class="empresa-footer-nombre" style="font-size: 1rem;">
                                    {{ $empresa['nombre'] ?? 'Nombre de la Empresa' }}
                                </h5>
                                @if($empresa['telefono'] ?? false)
                                    <p class="m-0">
                                        <i class="bi bi-telephone-fill me-2"></i>{{ $empresa['telefono'] }}
                                    </p>
                                @endif
                                @if($empresa['email'] ?? false)
                                    <p class="m-0">
                                        <i class="bi bi-envelope-fill me-2"></i>{{ $empresa['email'] }}
                                    </p>
                                @endif
                            </div>
                            <div class="footer-info">
                                <div class="footer-section">

                                </div>
                                @if($empresa['website'] ?? false)
                                    <div class="footer-section">
                                        <h6 class="footer-section-title">Web</h6>
                                        <p class="footer-item">
                                            <i class="bi bi-globe me-2"></i>{{ $empresa['website'] }}
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="row align-items-center">
                            <div class="col-sm-6">
                                <p class="footer-text mb-0">
                                    Documento generado automáticamente
                                </p>
                            </div>
                            <div class="col-sm-6 text-sm-end">
                                <p class="footer-text mb-0">
                                    {{ now()->format('d/m/Y H:i:s') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="content container-fluid d-print-none">
        <div class="container py-2">
            <!-- Botón de imprimir -->
            <div class="text-center mb-3 ">
                <button onclick="window.print()" class="btn btn-primary">
                    <i class="bi bi-printer me-2"></i>Imprimir Estado de Cuenta
                </button>
            </div>
        </div>
    </section>


@endsection

@push('styles')
    <style>
        /* Variables CSS para consistencia */
        :root {
            --primary-color: #000000;
            --success-color: #000000;
            --warning-color: #000000;
            --danger-color: #000000;
            --info-color: #000000;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
        }

        /* Utility overrides for monochrome */
        .text-success,
        .text-warning,
        .text-danger,
        .text-info {
            color: var(--gray-900) !important;
        }

        .bg-gradient-warning {
            background: var(--gray-900) !important;
        }

        .btn-primary {
            background-color: var(--gray-900) !important;
            border-color: var(--gray-900) !important;
        }

        .btn-outline-primary {
            color: var(--gray-900) !important;
            border-color: var(--gray-900) !important;
        }

        .btn-outline-primary:hover {
            background-color: var(--gray-900) !important;
            color: white !important;
        }

        .alert-info,
        .alert-success,
        .alert-warning,
        .alert-danger {
            background-color: var(--gray-50) !important;
            border-color: var(--gray-200) !important;
            color: var(--gray-900) !important;
            border-left: 4px solid var(--gray-900) !important;
        }

        /* Líneas separadoras de sección */
        .section-divider {
            border: none;
            border-top: 2px solid var(--gray-200);
            margin: 2rem 0;
            opacity: 0.6;
        }

        @media print {
            .section-divider {
                border-top: 1px solid #ccc;
                margin: 1.5rem 0;
            }
        }

        /* Enlace de regresar */
        .modern-link {
            display: inline-flex;
            align-items: center;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 10px;
            transition: all 0.2s ease;
            background: white;
            border: 1px solid var(--gray-200);
        }

        .modern-link:hover {
            background: var(--primary-color);
            color: white;
            transform: translateX(-5px);
        }

        /* Estilos para el contenedor principal - Como la vista de pago */
        .border.shadow-lg {
            background: #ffffff !important;
            padding: 2rem;
            margin: 1rem auto;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15) !important;
            border: 1px solid #e0e0e0 !important;
            font-family: 'Arial', 'Helvetica', sans-serif;
            transition: all 0.3s ease;
        }

        .border.shadow-lg:hover {
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2) !important;
        }

        /* Header del documento estilo pago */
        .bg-light {
            background: #f8f9fa !important;
        }



        /* Tarjetas simples como en pago */
        .card {
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-2px);
        }

        /* Sección financiera moderna */
        .resumen-financiero {
            margin-bottom: 3rem;
        }

        .section-header {
            margin-bottom: 2rem;
        }

        .section-header-content {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1.5rem;
            background: linear-gradient(135deg, white, var(--gray-50));
            border-radius: 15px;
            border: 1px solid var(--gray-200);
        }

        .section-icon {
            width: 50px;
            height: 50px;
            background: var(--gray-100);
            border: 1px solid var(--gray-800);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gray-900);
            font-size: 1.5rem;
        }

        .section-title {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--gray-800);
            letter-spacing: -0.025em;
        }

        /* Barra de progreso moderna */
        .progress-section {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            border: 1px solid var(--gray-200);
        }

        .progress-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .progress-label {
            font-weight: 600;
            color: var(--gray-700);
        }

        .progress-percentage {
            font-weight: 700;
            font-size: 1.25rem;
            color: var(--gray-900);
        }

        .progress-modern {
            height: 12px;
            border-radius: 10px;
            background: var(--gray-200);
            overflow: hidden;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .progress-bar.bg-gradient-success {
            background: var(--gray-900);
            box-shadow: none;
            transition: width 0.6s ease;
        }

        /* Cards financieras */
        .financial-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--gray-100);
            transition: all 0.3s ease;
            height: 100%;
        }

        .financial-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .financial-card-body {
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .financial-icon {
            width: 60px;
            height: 60px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.75rem;
            flex-shrink: 0;
        }

        .total-card .financial-icon {
            background: var(--gray-900);
            color: white;
        }

        .success-card .financial-icon {
            background: white;
            color: var(--gray-900);
            border: 2px solid var(--gray-900);
        }

        .warning-card .financial-icon {
            background: white;
            color: var(--gray-900);
            border: 2px solid var(--gray-900);
        }

        .danger-card .financial-icon {
            background: var(--gray-900);
            color: white;
        }

        .info-card .financial-icon {
            background: white;
            color: var(--gray-900);
            border: 2px solid var(--gray-900);
        }

        .financial-content {
            flex: 1;
        }

        .financial-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--gray-600);
            margin-bottom: 0.25rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .financial-amount {
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--gray-800);
            margin: 0;
            line-height: 1;
        }

        .financial-card-footer {
            padding: 0.75rem 1.5rem;
            background: var(--gray-50);
            border-top: 1px solid var(--gray-100);
        }

        /* Contenedores de tablas modernas */
        .table-container {
            background-color: transparent;
            border-radius: 0;
            box-shadow: none;
            margin-bottom: 2rem;
            overflow: hidden;
            border: none;
        }

        .table-modern {
            margin-bottom: 0;
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background-color: transparent;
        }

        .table-modern th {
            background-color: transparent;
            color: var(--gray-900);
            font-weight: 700;
            padding: 0.5rem 0.5rem;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            border-bottom: 1px solid var(--gray-500);
            border-top: none;
        }

        .table-modern th i {
            opacity: 0.8;
        }

        .table-modern td {
            padding: 0.15rem 0.5rem;
            border: none;
            border-bottom: 1px solid var(--gray-100);
            vertical-align: middle;

        }

        .table-row-hover {
            transition: all 0.2s ease;
        }

        .table-row-hover:hover {
            background: var(--gray-50) !important;
            transform: scale(1.01);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        /* Estilos para celdas de tabla */
        .date-cell,
        .fecha-cell {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .date-cell strong,
        .fecha-cell strong {
            font-weight: 700;
            color: var(--gray-800);
            font-size: 0.95rem;
        }

        .date-cell small,
        .fecha-cell small {
            color: var(--gray-500);
            font-size: 0.8rem;
            margin-top: 0.125rem;
        }

        .monto-cell {
            display: flex;
            align-items: center;
            font-weight: 700;
        }

        .monto-cell.success .monto-principal {
            color: var(--gray-900);
            font-size: 1.1rem;
        }

        .monto-cell.pendiente .monto-pendiente {
            color: var(--gray-900);
            font-size: 1.1rem;
        }

        .monto-original {
            color: var(--gray-600);
            font-size: 1rem;
        }

        .saldo-cell .saldo-monto {
            color: var(--gray-900);
            font-weight: 600;
        }

        .metodo-cell {
            display: flex;
            align-items: center;
            font-weight: 500;
            color: var(--gray-700);
        }

        .metodo-cell i {
            color: var(--gray-900);
        }

        .observaciones-cell {
            font-size: 0.875rem;
            color: var(--gray-600);
            line-height: 1.4;
        }

        /* Badges de tipo */
        .tipo-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.375rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }

        .tipo-cuota {
            background: var(--gray-900);
            color: white;
        }

        .tipo-enganche {
            background: white;
            color: var(--gray-900);
            border: 1px solid var(--gray-900);
        }

        .tipo-pago-extra {
            background: white;
            color: var(--gray-900);
            border: 1px dashed var(--gray-900);
        }

        /* Estados de cuotas */
        .cuota-vencida {
            border-left: 4px solid var(--gray-900);
            background: var(--gray-50);
        }

        .cuota-tolerancia {
            border-left: 4px solid var(--gray-600);
            background: var(--gray-50);
        }

        .cuota-hoy {
            border-left: 4px solid var(--gray-800);
            background: var(--gray-100);
        }

        .cuota-pendiente {
            border-left: 4px solid var(--gray-300);
        }

        /* Estados badges */
        .estado-cell {
            display: flex;
            justify-content: center;
        }

        .estado-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 0.875rem;
            border-radius: 25px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.025em;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .estado-vencida {
            background: var(--gray-900);
            color: white;
        }

        .estado-tolerancia {
            background: white;
            color: var(--gray-900);
            border: 1px solid var(--gray-900);
        }

        .estado-hoy {
            background: white;
            color: var(--gray-900);
            border: 1px dashed var(--gray-900);
        }

        .estado-pendiente {
            background: var(--gray-100);
            color: var(--gray-600);
            border: 1px solid var(--gray-300);
        }

        /* Celdas de días */
        .dias-cell {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .dias-vencida {
            color: var(--gray-900);
            font-weight: 700;
        }

        .dias-hoy {
            color: var(--gray-900);
            font-weight: 700;
        }

        .dias-futuro {
            color: var(--gray-600);
            font-weight: 500;
        }

        /* Badges generales */
        .badge {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.375rem 0.75rem;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }

        /* Footer profesional */
        .documento-footer {
            margin-top: 4rem;
            background: transparent;
            overflow: hidden;
        }



        .footer-main {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 2rem;
            align-items: start;
            margin-bottom: 2rem;
        }

        .footer-logo .empresa-footer-nombre {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--gray-800);
            margin-bottom: 0.5rem;

            .footer-logo .empresa-footer-nombre {
                font-size: 1.5rem;
                font-weight: 800;
                color: var(--gray-900);
                margin-bottom: 0.5rem;
            }

            .empresa-footer-slogan {
                color: var(--gray-600);
                font-style: italic;
                margin: 0;
                font-size: 0.95rem;
            }

            .footer-info {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 1.5rem;
            }

            .footer-section-title {
                font-size: 0.9rem;
                font-weight: 700;
                color: var(--gray-800);
                text-transform: uppercase;
                letter-spacing: 0.05em;
                margin-bottom: 0.75rem;
                padding-bottom: 0.25rem;
                border-bottom: 2px solid var(--gray-900);
                display: inline-block;
            }

            .footer-item {
                display: flex;
                align-items: center;
                margin-bottom: 0.5rem;
                font-size: 0.9rem;
                color: var(--gray-600);
                font-weight: 500;
            }

            .footer-item i {
                color: var(--gray-900);
                font-size: 0.8rem;
            }

            .footer-divider {
                height: 1px;
                background: linear-gradient(90deg, transparent, var(--gray-300), transparent);
                margin: 1.5rem 0;
            }

            .footer-bottom {
                padding-top: 1rem;
            }

            .footer-text {
                font-size: 0.875rem;
                color: var(--gray-600);
                font-weight: 500;
                display: flex;
                align-items: center;
            }

            .footer-text i {
                color: var(--gray-900);
                font-size: 0.8rem;
            }

            /* Estilos para impresión - Tamaño Carta */
            @media print {
                * {
                    -webkit-print-color-adjust: exact !important;
                    color-adjust: exact !important;
                    print-color-adjust: exact !important;
                }

                @page {
                    size: letter;
                    /* 8.5in x 11in */
                    margin: 0.5in 0.75in;
                    /* Margenes optimizados para carta */
                }

                body {
                    font-size: 9px;
                    line-height: 1.3;
                    color: #000;
                    font-family: 'Segoe UI', Arial, sans-serif;
                }

                .container {
                    max-width: none !important;
                    margin: 0 !important;
                    padding: 0 !important;
                    width: 100% !important;
                }

                .contract-header,
                .btn,
                .no-print,
                .d-print-none {
                    display: none !important;
                }

                .border.shadow-lg {
                    background: white !important;
                    box-shadow: none !important;
                    border: none !important;
                    margin: 0 !important;
                    padding: 0.5in !important;
                    /* Márgenes estándar para carta */
                    border-radius: 0 !important;
                    height: auto !important;
                    max-width: none !important;
                }

                /* Ajuste del contenedor de página */
                section.d-flex {
                    display: block !important;
                    padding: 0 !important;
                    margin: 0 !important;
                }

                .col-md-8 {
                    max-width: none !important;
                    padding: 0 !important;
                    margin: 0 !important;
                }

                .estado-cuenta-container::before {
                    height: 1px !important;
                    background: #000 !important;
                }

                /* Header del documento en impresión */
                .bg-light {
                    background: none !important;
                    border-bottom: 1px solid #000 !important;
                    padding: 0.5rem !important;
                }

                /* Título compacto */
                .documento-titulo {
                    margin-bottom: 0.75rem !important;
                }

                .titulo-principal {
                    font-size: 1rem !important;
                    color: #000 !important;
                    margin-bottom: 0.25rem !important;
                    font-weight: 900 !important;
                }

                .titulo-container {
                    background: none !important;
                    border: 1px solid #000 !important;
                    padding: 0.5rem !important;
                    border-radius: 0 !important;
                }

                .titulo-container::before {
                    display: none !important;
                }

                .titulo-detalles .badge {
                    font-size: 0.7rem !important;
                    padding: 0.2rem 0.4rem !important;
                }

                .fecha-emision {
                    font-size: 0.7rem !important;
                    margin-top: 0.25rem !important;
                }

                .titulo-container::before {
                    background: #000 !important;
                }

                /* Información del cliente y contrato compacta */
                .row.g-4 {
                    margin-bottom: 0.5rem !important;
                }

                .info-card {
                    background: white !important;
                    box-shadow: none !important;
                    border: 1px solid #000 !important;
                    margin-bottom: 0.25rem !important;
                    border-radius: 0 !important;
                    page-break-inside: avoid !important;
                }

                .info-card .card-header {
                    background: #f0f0f0 !important;
                    border-bottom: 1px solid #000 !important;
                    padding: 0.3rem 0.5rem !important;
                }

                .card-header-content {
                    gap: 0.5rem !important;
                }

                .card-icon {
                    background: #000 !important;
                    border-radius: 3px !important;
                    width: 20px !important;
                    height: 20px !important;
                    font-size: 0.8rem !important;
                }

                .card-title {
                    font-size: 0.8rem !important;
                    color: #000 !important;
                    font-weight: 700 !important;
                }

                .info-card .card-body {
                    padding: 0.4rem !important;
                }

                .info-items {
                    gap: 0.25rem !important;
                }

                .info-item-modern {
                    background: none !important;
                    border: none !important;
                    margin-bottom: 0.15rem !important;
                    padding: 0.25rem !important;
                    border-radius: 0 !important;
                    border-bottom: 1px dotted #ccc !important;
                }

                .info-item-modern:last-child {
                    border-bottom: none !important;
                }

                .item-icon {
                    background: #000 !important;
                    width: 15px !important;
                    height: 15px !important;
                    border-radius: 2px !important;
                    font-size: 0.6rem !important;
                }

                .item-content {
                    gap: 0.1rem !important;
                }

                .item-label {
                    font-size: 0.65rem !important;
                    color: #666 !important;
                    font-weight: 600 !important;
                }

                .item-value {
                    font-size: 0.75rem !important;
                    color: #000 !important;
                    font-weight: 600 !important;
                }

                .status-badge {
                    background: #6c757d !important;
                    color: white !important;
                    border-radius: 10px !important;
                    padding: 0.2rem 0.5rem !important;
                }

                /* Sección financiera compacta */
                .resumen-financiero {
                    margin-bottom: 0.75rem !important;
                }

                .section-header {
                    margin-bottom: 0.5rem !important;
                }

                .section-header-content {
                    background: #f0f0f0 !important;
                    border: 1px solid #000 !important;
                    padding: 0.4rem !important;
                    border-radius: 0 !important;
                }

                .section-icon {
                    background: #000 !important;
                    width: 12px !important;
                    height: 12px !important;
                    border-radius: 3px !important;
                    font-size: 0.8rem !important;
                }

                .section-title {
                    font-size: 0.8rem !important;
                    color: #000 !important;
                    font-weight: 700 !important;
                }

                .progress-section {
                    background: white !important;
                    border: 1px solid #000 !important;
                    box-shadow: none !important;
                    padding: 0.4rem !important;
                    border-radius: 0 !important;
                    margin-bottom: 0.3rem !important;
                }

                .progress-header {
                    margin-bottom: 0.25rem !important;
                }

                .progress-label {
                    font-size: 0.7rem !important;
                }

                .progress-percentage {
                    font-size: 0.8rem !important;
                }

                .progress-modern {
                    background: #e0e0e0 !important;
                    height: 5px !important;
                    border-radius: 0 !important;
                    border: 1px solid #000 !important;
                }

                .progress-bar.bg-gradient-success {
                    background: #000 !important;
                }

                /* Tarjetas financieras en grid compacto */
                .row.g-4 {
                    gap: 0.25rem !important;
                }

                .financial-card {
                    background: white !important;
                    box-shadow: none !important;
                    border: 1px solid #000 !important;
                    margin-bottom: 0.2rem !important;
                    border-radius: 0 !important;
                    page-break-inside: avoid !important;
                }

                .financial-card-body {
                    padding: 0.4rem !important;
                }

                .financial-icon {
                    background: #000 !important;
                    width: 20px !important;
                    height: 20px !important;
                    border-radius: 3px !important;
                    font-size: 0.8rem !important;
                }

                .financial-label {
                    font-size: 0.65rem !important;
                    color: #666 !important;
                    font-weight: 600 !important;
                }

                .financial-amount {
                    font-size: 0.85rem !important;
                    color: #000 !important;
                    font-weight: 700 !important;
                }

                .financial-card-footer {
                    background: none !important;
                    border-top: 1px dotted #666 !important;
                    padding: 0.2rem 0.4rem !important;
                    font-size: 0.6rem !important;
                }

                /* Tablas optimizadas para carta */
                .historial-pagos,
                .cuotas-pendientes {
                    margin-bottom: 0.5rem !important;
                    page-break-inside: avoid !important;
                }

                .table-container {
                    border-radius: 0 !important;
                    box-shadow: none !important;
                    border: 1px solid #000 !important;
                }

                .table-modern {
                    font-size: 7px !important;
                    border-radius: 0 !important;
                    box-shadow: none !important;
                    margin: 0 !important;
                }

                .table-modern th {
                    background: #f0f0f0 !important;
                    color: #000 !important;
                    border: 1px solid #000 !important;
                    padding: 0.3rem 0.15rem !important;
                    font-size: 0.65rem !important;
                    font-weight: 700 !important;
                }

                .table-modern th i {
                    display: none !important;
                }

                .table-modern td {
                    padding: 0.2rem 0.1rem !important;
                    border: 1px solid #ccc !important;
                    font-size: 0.7rem !important;
                    line-height: 1.2 !important;
                }

                .table-row-hover:hover {
                    transform: none !important;
                    box-shadow: none !important;
                }

                /* Celdas de tabla compactas */
                .date-cell,
                .fecha-cell {
                    text-align: center !important;
                }

                .date-cell strong,
                .fecha-cell strong {
                    font-size: 0.7rem !important;
                    display: block !important;
                }

                .date-cell small,
                .fecha-cell small {
                    font-size: 0.6rem !important;
                    display: none !important;
                    /* Ocultar hora para ahorrar espacio */
                }

                .monto-cell,
                .saldo-cell {
                    text-align: right !important;
                }

                .monto-principal,
                .monto-pendiente,
                .monto-original,
                .saldo-monto {
                    font-size: 0.7rem !important;
                    font-weight: 600 !important;
                }

                .tipo-badge,
                .estado-badge {
                    background-color: #000 !important;
                    color: white !important;
                    font-size: 0.55rem !important;
                    padding: 0.1rem 0.3rem !important;
                    border-radius: 5px !important;
                }

                .tipo-badge i,
                .estado-badge i {
                    display: none !important;
                }

                .metodo-cell {
                    font-size: 0.65rem !important;
                }

                .metodo-cell i {
                    display: none !important;
                }

                .observaciones-cell {
                    font-size: 0.6rem !important;
                    max-width: 100px !important;
                    word-wrap: break-word !important;
                }

                .dias-cell {
                    text-align: center !important;
                    font-size: 0.6rem !important;
                }

                .dias-vencida,
                .dias-hoy,
                .dias-futuro {
                    font-size: 0.6rem !important;
                }

                .dias-cell i {
                    display: none !important;
                }

                /* Estados de cuotas simplificados */
                .cuota-vencida {
                    background: #f0f0f0 !important;
                    border-left: 2px solid #000 !important;
                }

                .cuota-tolerancia {
                    background: #f5f5f5 !important;
                    border-left: 2px solid #666 !important;
                }

                .cuota-hoy {
                    background: #f8f8f8 !important;
                    border-left: 2px solid #333 !important;
                }

                /* Footer compacto */
                .documento-footer {
                    margin-top: 0.5rem !important;
                    border-radius: 0 !important;
                    border-top: 1px solid #000 !important;
                    page-break-inside: avoid !important;
                }



                .footer-main {
                    display: flex !important;
                    justify-content: space-between !important;
                    align-items: center !important;
                    margin-bottom: 0.25rem !important;
                }

                .footer-logo .empresa-footer-nombre {
                    font-size: 0.8rem !important;
                    color: #000 !important;
                    background: none !important;
                    -webkit-text-fill-color: #000 !important;
                    margin-bottom: 0 !important;
                    font-weight: 700 !important;
                }

                .empresa-footer-slogan {
                    font-size: 0.6rem !important;
                    color: #666 !important;
                    margin: 0 !important;
                }

                .footer-info {
                    display: flex !important;
                    gap: 1rem !important;
                    margin-top: 0 !important;
                    font-size: 0.6rem !important;
                }

                .footer-section {
                    margin: 0 !important;
                }

                .footer-section-title {
                    display: none !important;
                    /* Ocultar títulos para ahorrar espacio */
                }

                .footer-item {
                    font-size: 0.6rem !important;
                    color: #000 !important;
                    margin-bottom: 0 !important;
                    display: inline !important;
                }

                .footer-item i {
                    color: #000 !important;
                    margin-right: 0.25rem !important;
                }

                .footer-divider {
                    background: #000 !important;
                    margin: 0.25rem 0 !important;
                    height: 0.5px !important;
                }

                .footer-bottom {
                    padding-top: 0.25rem !important;
                }

                .footer-bottom .row {
                    align-items: center !important;
                }

                .footer-text {
                    font-size: 0.6rem !important;
                    color: #666 !important;
                    margin: 0 !important;
                }

                .footer-text i {
                    color: #000 !important;
                    font-size: 0.6rem !important;
                }

                /* Evitar saltos de página en elementos críticos */
                .resumen-financiero,
                .info-card,
                .financial-card,
                .progress-section {
                    page-break-inside: avoid !important;
                }

                /* Control de saltos de página para tablas */
                .table-container {
                    page-break-inside: auto !important;
                }

                .table-modern thead {
                    page-break-after: avoid !important;
                }

                .table-row-hover {
                    page-break-inside: avoid !important;
                }

                /* Ocultar elementos no esenciales para impresión */
                .no-print {
                    display: none !important;
                }

                /* Optimización de espacios */
                h1,
                h2,
                h3,
                h4,
                h5,
                h6 {
                    page-break-after: avoid !important;
                    margin-bottom: 0.25rem !important;
                }

                p {
                    margin-bottom: 0.25rem !important;
                }

                /* Evitar saltos de página en elementos importantes */
                .resumen-financiero,
                .info-card,
                .historial-pagos,
                .cuotas-pendientes {
                    page-break-inside: avoid;
                }

                .financial-card {
                    page-break-inside: avoid;
                }

                /* Forzar nueva página antes de secciones importantes si es necesario */
                .cuotas-pendientes {
                    page-break-before: auto;
                }

                h1,
                h2,
                h3,
                h4,
                h5,
                h6 {
                    page-break-after: avoid;
                }
            }

            /* Visualización de cuotas - Estilo minimalista */
            .cuotas-visualizacion {
                margin-top: 0.75rem;
            }

            .cuotas-container {
                background: #f8fafc;
                border: 1px solid #e2e8f0;
                border-radius: 8px;
                padding: 1rem;
            }

            .cuotas-grid {
                display: flex;
                flex-wrap: wrap;
                gap: 2px;
                margin-bottom: 0.75rem;
                align-items: center;
                justify-content: flex-start;
            }

            .cuota-item {
                transition: all 0.2s ease;
            }

            .cuota-item:hover {
                transform: scale(1.2);
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
                z-index: 10;
                position: relative;
            }

            .cuota-cuadrito:hover {
                transform: scale(1.1);
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
                z-index: 10;
            }

            /* Estados de las cuotas */
            .cuotas-horizontal .cuota-cuadrito.cuota-pagada {
                background: var(--gray-900) !important;
                border: 1px solid black !important;
            }

            .cuotas-horizontal .cuota-cuadrito.cuota-hoy {
                background: white !important;
                border: 1px dashed black !important;
                animation: pulse-hoy 2s infinite;
            }

            .cuotas-horizontal .cuota-cuadrito.cuota-pendiente {
                background: var(--gray-200) !important;
                border: 1px solid var(--gray-400) !important;
            }

            .cuotas-horizontal .cuota-cuadrito.cuota-tolerancia {
                background: white !important;
                border: 1px solid var(--gray-600) !important;
                animation: pulse-tolerancia 1.5s infinite;
            }

            .cuotas-horizontal .cuota-cuadrito.cuota-vencida {
                background: black !important;
                border: 1px solid black !important;
                position: relative;
            }

            .cuotas-horizontal .cuota-cuadrito.cuota-futura {
                background: white !important;
                border: 1px solid var(--gray-300) !important;
            }

            /* Animaciones para estados críticos */
            @keyframes pulse-hoy {

                0%,
                100% {
                    box-shadow: 0 0 0 0 rgba(0, 0, 0, 0.7);
                }

                50% {
                    box-shadow: 0 0 0 4px rgba(0, 0, 0, 0);
                }
            }

            @keyframes pulse-tolerancia {

                0%,
                100% {
                    box-shadow: 0 0 0 0 rgba(100, 100, 100, 0.7);
                }

                50% {
                    box-shadow: 0 0 0 4px rgba(100, 100, 100, 0);
                }
            }

            @keyframes pulse-vencida {

                0%,
                100% {
                    transform: scale(1);
                }

                50% {
                    transform: scale(0.9);
                }
            }

            /* Leyenda de cuotas - Estilo minimalista */
            .cuotas-leyenda {
                display: flex;
                flex-wrap: wrap;
                gap: 0.875rem;
                align-items: center;
                margin-top: 0.75rem;
                padding-top: 0.5rem;
                border-top: 1px solid #e2e8f0;
            }

            .leyenda-item {
                display: flex;
                align-items: center;
                gap: 0.375rem;
                font-size: 0.75rem;
                color: #64748b;
                font-weight: 500;
            }

            .cuota-cuadrito.leyenda {
                width: 16px;
                height: 16px;
                font-size: 0;
                animation: none !important;
                cursor: default;
            }

            .cuota-cuadrito.leyenda:hover {
                transform: none !important;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            }

            /* Colores neutros minimalistas para la leyenda */
            .cuotas-leyenda .cuota-cuadrito.leyenda.cuota-pagada {
                background: var(--gray-900) !important;
                border: 1px solid black !important;
            }

            .cuotas-leyenda .cuota-cuadrito.leyenda.cuota-hoy {
                background: white !important;
                border: 1px dashed black !important;
            }

            .cuotas-leyenda .cuota-cuadrito.leyenda.cuota-pendiente {
                background: var(--gray-200) !important;
                border: 1px solid var(--gray-400) !important;
            }

            .cuotas-leyenda .cuota-cuadrito.leyenda.cuota-tolerancia {
                background: white !important;
                border: 1px solid var(--gray-600) !important;
            }

            .cuotas-leyenda .cuota-cuadrito.leyenda.cuota-vencida {
                background: black !important;
                border: 1px solid black !important;
            }

            /* Responsive para cuadritos */
            @media (max-width: 480px) {
                .cuotas-horizontal {
                    gap: 0.2rem;
                }

                .cuota-cuadrito {
                    width: 16px;
                    height: 16px;
                }

                .cuotas-leyenda {
                    gap: 0.5rem;
                }

                .leyenda-item {
                    font-size: 0.7rem;
                }
            }

            /* Estilos para impresión de cuotas */
            @media print {
                .cuotas-visualizacion {
                    margin-top: 0.5rem !important;
                }

                .cuotas-horizontal {
                    gap: 0.1rem !important;
                }

                .cuota-cuadrito {
                    width: 12px !important;
                    height: 12px !important;
                    border-radius: 2px !important;
                    animation: none !important;
                    box-shadow: none !important;
                    border: 1px solid #000 !important;
                }

                .cuota-cuadrito:hover {
                    transform: none !important;
                }

                .cuota-pagada {
                    background: #000 !important;
                    color: white !important;
                }

                .cuota-hoy,
                .cuota-pendiente,
                .cuota-tolerancia {
                    background: #666 !important;
                    color: white !important;
                }

                .cuota-vencida {
                    background: #333 !important;
                    color: white !important;
                }

                .cuota-futura {
                    background: #ccc !important;
                    color: #666 !important;
                }

                .cuotas-leyenda {
                    gap: 0.25rem !important;
                    font-size: 0.6rem !important;
                    margin-top: 0.25rem !important;
                }

                .leyenda-item {
                    font-size: 0.6rem !important;
                }

                .cuota-cuadrito.leyenda {
                    width: 10px !important;
                    height: 10px !important;
                }
            }

            /* Responsive */
            @media (max-width: 768px) {
                .paper-sheet {
                    margin: 0.5rem;
                    padding: 1.5rem;
                    transform: none;
                    max-width: none;
                    border-radius: 8px;
                }

                .paper-sheet::after {
                    display: none;
                }

                .paper-sheet {
                    background-image: none !important;
                }

                .contract-header {
                    padding: 1rem;
                    margin-bottom: 1rem;
                }

                .header-content {
                    flex-direction: column;
                    text-align: center;
                    gap: 0.5rem;
                }

                .page-title {
                    font-size: 1.25rem;
                }

                .col-md-10 {
                    padding: 0 0.5rem;
                }

                .resumen-financiero .col-md-3 {
                    margin-bottom: 1rem;
                }

                .footer-main {
                    grid-template-columns: 1fr !important;
                    gap: 1rem !important;
                    text-align: center;
                }

                .footer-info {
                    justify-content: center !important;
                }
            }

            @media (max-width: 576px) {
                .paper-sheet {
                    padding: 1rem;
                }

                .empresa-nombre {
                    font-size: 1.5rem !important;
                }

                .titulo-principal {
                    font-size: 1.5rem !important;
                }
            }
    </style>
@endpush

@push('scripts')
    <script>
        // Función mejorada de impresión
        function imprimirEstadoCuenta() {
            // Ocultar elementos no necesarios para la impresión
            const elementsToHide = document.querySelectorAll('.contract-header, .btn, .no-print');
            elementsToHide.forEach(el => el.style.display = 'none');

            // Imprimir
            window.print();

            // Restaurar elementos después de la impresión
            setTimeout(() => {
                elementsToHide.forEach(el => el.style.display = '');
            }, 1000);
        }

        // Escuchar eventos de teclado para impresión rápida
        document.addEventListener('keydown', function (e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
                e.preventDefault();
                imprimirEstadoCuenta();
            }
        });
    </script>
@endpush