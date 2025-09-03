<style>
    /* Status dots para el estado del contrato */
    .status-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
        flex-shrink: 0;
        box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.8);
    }

    .status-dot-success {
        background: #28a745;
        box-shadow: 0 0 0 2px rgba(40, 167, 69, 0.2), 0 0 6px rgba(40, 167, 69, 0.4);
    }

    .status-dot-warning {
        background: #ffc107;
        box-shadow: 0 0 0 2px rgba(255, 193, 7, 0.2), 0 0 6px rgba(255, 193, 7, 0.4);
    }

    .status-dot-danger {
        background: #dc3545;
        box-shadow: 0 0 0 2px rgba(220, 53, 69, 0.2), 0 0 6px rgba(220, 53, 69, 0.4);
    }

    .status-dot-primary {
        background: #007bff;
        box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.2), 0 0 6px rgba(0, 123, 255, 0.4);
    }

    .status-dot-secondary {
        background: #6c757d;
        box-shadow: 0 0 0 2px rgba(108, 117, 125, 0.2), 0 0 6px rgba(108, 117, 125, 0.4);
    }
</style>

<div class="table-responsive" id="tabla-contratos">
    <table class="table table-hover align-middle mb-0 modern-table">
        <thead class="modern-header">
            <tr>
                <th scope="col" class="ps-4">ID</th>
                <th scope="col">Cliente</th>
                <th scope="col">Paquete</th>
                <th scope="col">Progreso</th>
                <th scope="col" class="pe-4">Cuotas Pendientes</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($contratos as $contrato)
                <tr class="modern-row clickable-row" data-href="{{ route('contratos.show', $contrato->id) }}">
                    <td class="ps-4">
                        <span class="badge bg-light text-dark fw-normal">{{ $contrato->id }}</span>
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar-circle me-3 d-flex align-items-center justify-content-center" style="min-width: 45px; min-height: 45px; width: 45px; height: 45px; background: linear-gradient(135deg, #E1B240 0%, #79481D 100%); border-radius: 50%;">
                                {{ strtoupper(substr($contrato->cliente->nombre, 0, 1) . substr($contrato->cliente->apellido, 0, 1)) }}
                            </div>
                            <div>
                                <div class="fw-semibold text-dark">{{ $contrato->cliente->nombre }} {{ $contrato->cliente->apellido }}</div>
                                <span class="status-dot me-2 {{ 
                                    $contrato->estado == 'activo' ? 'status-dot-success' : 
                                    ($contrato->estado == 'suspendido' ? 'status-dot-warning' : 
                                    ($contrato->estado == 'cancelado' ? 'status-dot-danger' : 
                                    ($contrato->estado == 'finalizado' ? 'status-dot-primary' : 'status-dot-secondary'))) 
                                }}"></span>
                                <small class="text-muted">
                                    {{$contrato->paquete->nombre}}#{{ $contrato->id }}
                                </small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="package-info">
                            <div class="fw-semibold text-dark mb-1 d-flex align-items-center">
                                {{ $contrato->paquete->nombre }}
                            </div>
                            <small class="text-muted">
                                <i class="bi bi-currency-dollar me-1"></i>
                                ${{ number_format($contrato->paquete->precio, 2) }}
                            </small>
                        </div>
                    </td>
                    <td>
                        @php
                            // Calcular cuotas pagadas (solo pagos de tipo "cuota")
                            $cuotasPagadas = $contrato->pagos->filter(function($pago) {
                                return $pago->estado == 'hecho' && 
                                       strtolower($pago->tipo_pago ?? '') == 'cuota';
                            })->count();
                            $totalCuotas = $contrato->numero_cuotas ?? 0;
                        @endphp
                        <div class="progress-info">
                            <div class="d-flex align-items-center" style="min-width:140px;">
                                <span class="fw-bold me-2" style="min-width:40px;">{{ $contrato->porcentaje_pagado }}%</span>
                                <div class="progress flex-grow-1" style="height: 12px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $contrato->porcentaje_pagado }}%;" aria-valuenow="{{ $contrato->porcentaje_pagado }}" aria-valuemin="0" aria-valuemax="100">
                                    </div>
                                </div>
                            </div>
                            <small class="text-muted mb-2">
                                Cuotas pagadas: {{ $cuotasPagadas }} de {{ $totalCuotas }}
                            </small>
                        </div>
                    </td>
                    <td>
                        @php
                            $estadoPagos = $contrato->estado_pagos;
                        @endphp
                        <div class="payment-status-info">
                            @if($estadoPagos['tiene_vencidas'])
                                <div class="alert alert-danger p-2 mb-2" style="font-size: 0.85rem;">
                                    <div class="fw-bold">
                                        <i class="bi bi-exclamation-triangle me-1"></i>
                                        {{ $estadoPagos['cuotas_vencidas'] }} cuota{{ $estadoPagos['cuotas_vencidas'] > 1 ? 's' : '' }} vencida{{ $estadoPagos['cuotas_vencidas'] > 1 ? 's' : '' }}
                                    </div>
                                    <small>
                                        Total: ${{ number_format($estadoPagos['monto_vencido'], 2) }}
                                        @if($estadoPagos['dias_retraso'])
                                            <br>{{ intval($estadoPagos['dias_retraso']) }} día{{ intval($estadoPagos['dias_retraso']) > 1 ? 's' : '' }} de retraso
                                        @endif
                                    </small>
                                </div>
                            @elseif($estadoPagos['proxima_cuota'])
                                <div class="alert alert-success p-2 mb-2" style="font-size: 0.85rem;">
                                    <div class="fw-bold">
                                        <i class="bi bi-check-circle me-1"></i>
                                        Todas las cuotas al día
                                    </div>
                                    <small>
                                        Próximo pago: ${{ number_format($estadoPagos['proxima_cuota']['monto'], 2) }}
                                        <br>
                                        {{ \Carbon\Carbon::parse($estadoPagos['proxima_cuota']['fecha'])->translatedFormat('d \\d\\e F \\d\\e Y') }}
                                    </small>
                                </div>
                            @elseif($contrato->estado == 'finalizado')
                                <div class="alert alert-primary p-2 mb-2" style="font-size: 0.85rem;">
                                    <div class="fw-bold">
                                        <i class="bi bi-check-circle-fill me-1"></i>
                                        Contrato finalizado
                                    </div>
                                    <small>
                                        Todas las cuotas pagadas
                                        <br>
                                        Sin pagos pendientes
                                    </small>
                                </div>
                            @else
                                <div class="alert alert-info p-2 mb-2" style="font-size: 0.85rem;">
                                    <div class="fw-bold">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Todas las cuotas pagadas
                                    </div>
                                    <small>
                                        Contrato {{ $contrato->estado }}
                                        <br>
                                        Sin pagos pendientes
                                    </small>
                                </div>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center py-4">
                        <div class="text-muted">
                            <i class="bi bi-search fs-1 d-block mb-2"></i>
                            No se encontraron contratos que coincidan con los criterios de búsqueda.
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="d-flex justify-content-center mt-3">
    {!! $contratos->withQueryString()->links() !!}
</div>
