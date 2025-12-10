@extends('layouts.app')

@section('template_title')
    Contratos
@endsection

@section('content')
<div class="container py-4" style="max-width: 1600px;">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Header moderno -->
            <div class="page-header">
                <div class="header-content">
                    <div class="header-icon">
                        <i class="bi bi-file-earmark-text"></i>
                    </div>
                    <div class="header-text">
                        <h1 class="page-title">{{ __('Contratos') }}</h1>
                        <p class="page-subtitle">Gestione y consulte la información de los contratos</p>
                    </div>
                </div>
                <div class="header-actions">
                    <a href="{{ route('contratos.create') }}" class="btn text-secondary border-secondary custom-hover-btn">
                        <i class="bi bi-plus-lg me-1"></i>
                        {{ __('Nuevo Contrato') }}
                    </a>

                </div>
            </div>
            <div class="">
                <!-- Formulario de búsqueda -->
                <div class="pb-3">
                    <form method="GET" action="{{ route('contratos.index') }}" id="searchForm" class="row g-2 align-items-center">
                        
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" name="search" id="searchInput" class="bg-white form-control border-start-0" placeholder="Buscar cliente..." value="{{ request('search') }}" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-auto">
                            <button type="button" id="solo_activos" class="btn toggle-btn {{ request('solo_activos', '1') ? 'active' : '' }}" data-value="{{ request('solo_activos', '1') ? '1' : '0' }}">
                                <i class="bi bi-toggle-{{ request('solo_activos', '1') ? 'on' : 'off' }} me-2"></i>
                                Solo Activos
                            </button>
                        </div>
                        <div class="col-auto">
                            <div class="spinner-border spinner-border-sm text-primary d-none" id="loadingSpinner" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                        </div>
                        @if(request('search'))
                        <div class="col-auto">
                            <button type="button" id="clearSearch" class="btn btn-outline-secondary btn-sm">
                                <i class="fa fa-times"></i> Limpiar
                            </button>
                        </div>
                        @endif
                    </form>
                </div>
                @if ($message = Session::get('success'))
                    <div class="alert alert-success m-4">
                        <p class="mb-0">{{ $message }}</p>
                    </div>
                @endif

                <div class="card-body p-0">
                    <div id="contractsTableContainer">
                        <div class="table-responsive" id="tabla-contratos">
                            <table class="table table-hover align-middle mb-0 modern-table">
                                <thead class="modern-header">
                                    <tr>
                                        <th scope="col" class="ps-4">ID</th>
                                        <th scope="col">Cliente</th>
                                        <th scope="col">Paquete</th>
                                        <th scope="col">Progreso</th>
                                        <th scope="col" class="pe-4">Estado de pagos</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($contratos as $contrato)
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
                                                        <div class="fw-semibold text-dark mb-1 d-flex align-items-center">
                                                        <span class="status-dot me-2 {{ 
                                                            $contrato->estado == 'activo' ? 'status-dot-success' : 
                                                            ($contrato->estado == 'suspendido' ? 'status-dot-warning' : 
                                                            ($contrato->estado == 'cancelado' ? 'status-dot-danger' : 
                                                            ($contrato->estado == 'finalizado' ? 'status-dot-primary' : 'status-dot-secondary'))) 
                                                        }}"></span>
                                                        <small class="text-muted">{{$contrato->paquete->nombre}}#{{ $contrato->id }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="package-info">
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
                                                                {{ $estadoPagos['cuotas_vencidas'] }} cuota{{ $estadoPagos['cuotas_vencidas'] > 1 ? 's' : '' }} retrasada{{ $estadoPagos['cuotas_vencidas'] > 1 ? 's' : '' }}
                                                            </div>
                                                            <small>
                                                                Total: ${{ number_format($estadoPagos['monto_vencido'], 2) }}
                                                                @if($estadoPagos['dias_retraso'])
                                                                    <br>{{ intval($estadoPagos['dias_retraso']) }} día{{ intval($estadoPagos['dias_retraso']) > 1 ? 's' : '' }} de retraso
                                                                @endif
                                                            </small>
                                                        </div>
                                                    @elseif($estadoPagos['tiene_en_tolerancia'])
                                                        <div class="alert alert-warning p-2 mb-2" style="font-size: 0.85rem;">
                                                            <div class="fw-bold">
                                                                <i class="bi bi-clock me-1"></i>
                                                                {{ $estadoPagos['cuotas_en_tolerancia'] }} cuota{{ $estadoPagos['cuotas_en_tolerancia'] > 1 ? 's' : '' }} en período de gracia
                                                            </div>
                                                            <div style="font-size: 0.75rem;">
                                                                Total: ${{ number_format($estadoPagos['monto_en_tolerancia'], 2) }}
                                                                <br>Tolerancia: {{ $estadoPagos['tolerancia_dias'] }} día{{ $estadoPagos['tolerancia_dias'] > 1 ? 's' : '' }}
                                                            </div>
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
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-center mt-4">
                            {!! $contratos->withQueryString()->links('vendor.pagination.custom') !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

<style>
    /* Estilos modernos para la tabla */
    .modern-table {
        border: none !important;
        box-shadow: none !important;
    }

    .modern-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
        color: white !important;
        border: none !important;
    }

    .modern-header th {
        border: none !important;
        padding: 1.2rem 1rem !important;
        font-weight: 600 !important;
        font-size: 0.875rem !important;
        text-transform: uppercase !important;
        letter-spacing: 0.5px !important;
        position: relative;
    }

    .modern-header th:not(:last-child)::after {
        content: '';
        position: absolute;
        right: 0;
        top: 25%;
        height: 50%;
        width: 1px;
        background: rgba(255, 255, 255, 0.2);
    }

    .modern-row {
        border: none !important;
        transition: all 0.3s ease !important;
        background: white !important;
    }

    .modern-row:hover {
        background: linear-gradient(135deg, #f8f9ff 0%, #f0f4ff 100%) !important;
        transform: translateY(-2px) !important;
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.15) !important;
    }

    .modern-row td {
        border: none !important;
        padding: 1.5rem 1rem !important;
        vertical-align: middle !important;
        border-bottom: 1px solid #f1f3f5 !important;
    }

    /* Avatar circular */
    .avatar-circle {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        font-size: 0.875rem;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }

    /* Información del paquete */
    .package-info {
        font-size: 0.875rem;
    }

    .package-info i {
        width: 16px;
        font-size: 0.8rem;
        color: #79481D !important;
    }

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

    /* Información del progreso */
    .progress-info {
        font-size: 0.875rem;
    }

    .progress-info i {
        width: 16px;
        font-size: 0.8rem;
        color: #79481D !important;
    }

    /* Información de estado de pagos */
    .payment-status-info {
        font-size: 0.875rem;
        min-width: 200px;
    }

    .payment-status-info .alert {
        border-radius: 8px !important;
        margin-bottom: 0.5rem !important;
        padding: 0.5rem !important;
        border: none !important;
    }

    .payment-status-info .alert-danger {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%) !important;
        color: #991b1b !important;
    }

    .payment-status-info .alert-warning {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%) !important;
        color: #92400e !important;
    }

    .payment-status-info .alert-info {
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%) !important;
        color: #1e40af !important;
    }

    .payment-status-info .alert-primary {
        background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%) !important;
        color: #3730a3 !important;
    }

    .payment-status-info .alert-success {
        background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%) !important;
        color: #166534 !important;
    }

    .payment-status-info i {
        width: 16px;
        font-size: 0.8rem;
    }

    /* Badge moderno */
    .modern-badge {
        padding: 0.5rem 0.75rem !important;
        border-radius: 8px !important;
        font-weight: 600 !important;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 0.75rem !important;
    }

    /* Filas clickeables */
    .clickable-row {
        cursor: pointer;
    }

    .clickable-row:hover {
        background: linear-gradient(135deg, #f8f9ff 0%, #f0f4ff 100%) !important;
    }

    /* Botones de acción modernos */
    .action-btn {
        border-radius: 8px !important;
        margin: 0 2px !important;
        transition: all 0.3s ease !important;
        border-width: 1.5px !important;
        width: 35px;
        height: 35px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .action-btn:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
    }

    .btn-outline-primary.action-btn:hover {
        background: #667eea !important;
        border-color: #667eea !important;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4) !important;
    }

    .btn-outline-success.action-btn:hover {
        background: #28a745 !important;
        border-color: #28a745 !important;
        box-shadow: 0 4px 12px rgba(40, 167, 69, 0.4) !important;
    }

    .btn-outline-danger.action-btn:hover {
        background: #dc3545 !important;
        border-color: #dc3545 !important;
        box-shadow: 0 4px 12px rgba(220, 53, 69, 0.4) !important;
    }

    /* Badge moderno para ID */
    .badge.bg-light {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
        border: 1px solid #dee2e6 !important;
        font-weight: 600 !important;
        padding: 0.5rem 0.75rem !important;
        border-radius: 8px !important;
    }

    /* Efectos de carga suave */
    .modern-row {
        animation: fadeInUp 0.5s ease forwards;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Responsive */
    @media (max-width: 768px) {
        .modern-header th,
        .modern-row td {
            padding: 1rem 0.5rem !important;
        }

        .avatar-circle {
            width: 35px;
            height: 35px;
            font-size: 0.75rem;
        }

        .package-info,
        .progress-info,
        .payment-status-info {
            font-size: 0.8rem;
        }

        .payment-status-info {
            min-width: 150px;
        }

        .payment-status-info .alert {
            padding: 0.25rem !important;
            font-size: 0.75rem !important;
        }

        /* En móvil, ocultar la columna de cuotas pendientes en pantallas muy pequeñas */
        @media (max-width: 576px) {
            .payment-status-info {
                display: none;
            }
            
            .modern-header th:nth-child(5),
            .modern-row td:nth-child(5) {
                display: none;
            }
        }
    }

    /* Mejoras adicionales */
    .table-responsive {
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    .card {
        border-radius: 16px !important;
        overflow: hidden;
    }

    /* Header page styles */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        padding: 1.5rem 0;
    }

    .header-content {
        display: flex;
        align-items: center;
    }

    .header-icon {
        background: linear-gradient(135deg, #E1B240 0%, #79481D 100%);
        color: white;
        width: 60px;
        height: 60px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1.5rem;
        font-size: 1.5rem;
        box-shadow: 0 10px 30px rgba(225, 178, 64, 0.3);
    }

    .page-title {
        font-size: 2rem;
        font-weight: 700;
        color: #2d3748;
        margin: 0;
        line-height: 1.2;
    }

    .page-subtitle {
        color: #718096;
        font-size: 1rem;
        margin: 0;
        margin-top: 0.25rem;
    }

    .header-actions .btn {
        background: white;
        color: #667eea;
        border: 2px solid #e2e8f0;
        padding: 0.75rem 1.5rem;
        border-radius: 12px;
        font-weight: 600;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    .header-actions .btn:hover {
        background: #667eea;
        color: white;
        border-color: #667eea;
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.25);
    }

    /* Estilos para el botón toggle */
    .toggle-btn {
        background: #f8f9fa;
        color: #6c757d;
        border: 2px solid #e9ecef;
        border-radius: 25px;
        padding: 0.5rem 1rem;
        font-weight: 600;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .toggle-btn:hover {
        background: #e9ecef;
        color: #495057;
        border-color: #dee2e6;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .toggle-btn.active {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
        border-color: #28a745;
        box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
    }

    .toggle-btn.active:hover {
        background: linear-gradient(135deg, #218838 0%, #1dbd92 100%);
        border-color: #1e7e34;
        transform: translateY(-1px);
        box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
    }

    .toggle-btn i {
        transition: all 0.3s ease;
        font-size: 1.1rem;
    }

    .toggle-btn.active i {
        color: white;
    }

    /* Paginador Minimalista */
    .pagination-minimal {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.25rem;
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .page-item-minimal {
        display: inline-block;
    }

    .page-link-minimal {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 36px;
        height: 36px;
        padding: 0 0.5rem;
        font-size: 0.875rem;
        font-weight: 500;
        color: #64748b;
        background: transparent;
        border: none;
        border-radius: 6px;
        text-decoration: none;
        transition: all 0.15s ease;
        cursor: pointer;
    }

    .page-link-minimal:hover {
        color: #79481D;
        background: #f8fafc;
    }

    .page-item-minimal.active .page-link-minimal {
        color: white;
        background: linear-gradient(135deg, #E1B240 0%, #79481D 100%);
        font-weight: 600;
    }

    .page-item-minimal.disabled .page-link-minimal {
        color: #cbd5e0;
        cursor: not-allowed;
        opacity: 0.5;
    }

    .page-item-minimal.disabled .page-link-minimal:hover {
        background: transparent;
        color: #cbd5e0;
    }

    /* Iconos de flechas */
    .page-link-minimal i {
        font-size: 0.75rem;
    }
</style>




@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const soloActivosToggle = document.getElementById('solo_activos');
    const clearSearchBtn = document.getElementById('clearSearch');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const contractsContainer = document.getElementById('contractsTableContainer');
    
    let searchTimeout;
    let currentRequest;

    // Función para realizar la búsqueda
    function performSearch(immediate = false) {
        clearTimeout(searchTimeout);
        
        const delay = immediate ? 0 : 300; // Sin delay para checkbox, con delay para input
        
        searchTimeout = setTimeout(() => {
            // Cancelar request anterior si existe
            if (currentRequest) {
                currentRequest.abort();
            }

            const searchValue = searchInput.value.trim();
            const soloActivos = soloActivosToggle.getAttribute('data-value');
            
            // Mostrar spinner de carga
            loadingSpinner.classList.remove('d-none');
            
            // Crear URL con parámetros
            const url = new URL('{{ route("contratos.index") }}', window.location.origin);
            if (searchValue) {
                url.searchParams.set('search', searchValue);
            }
            url.searchParams.set('solo_activos', soloActivos);
            
            // Realizar request AJAX
            currentRequest = fetch(url.toString(), {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html',
                }
            })
            .then(response => response.text())
            .then(data => {
                // Crear un elemento temporal para parsear la respuesta
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = data;
                
                // Buscar si hay una vista parcial (solo tabla) o vista completa
                const partialTable = tempDiv.querySelector('.table-responsive');
                const fullContainer = tempDiv.querySelector('#contractsTableContainer');
                
                if (partialTable && !fullContainer) {
                    // Es una respuesta parcial (AJAX), reemplazar todo el contenedor
                    contractsContainer.innerHTML = data;
                } else if (fullContainer) {
                    // Es una respuesta completa, extraer solo el contenido del contenedor
                    contractsContainer.innerHTML = fullContainer.innerHTML;
                } else {
                    // Fallback: reemplazar con la respuesta completa
                    contractsContainer.innerHTML = data;
                }
                
                // Actualizar la URL del navegador sin recargar la página
                window.history.replaceState({}, '', url.toString());
                
                // Actualizar botón de limpiar
                updateClearButton(searchValue);
                
                // Agregar event listeners a los nuevos enlaces de paginación y filas
                addPaginationListeners();
                initializeRowEvents();
            })
            .catch(error => {
                if (error.name !== 'AbortError') {
                    console.error('Error en la búsqueda:', error);
                }
            })
            .finally(() => {
                loadingSpinner.classList.add('d-none');
                currentRequest = null;
            });
        }, delay); // Usar el delay variable
    }

    // Función para actualizar el botón de limpiar
    function updateClearButton(searchValue) {
        const clearButtonContainer = clearSearchBtn?.parentElement;
        if (searchValue && searchValue.length > 0) {
            if (!clearSearchBtn) {
                // Crear botón de limpiar si no existe
                const formRow = document.querySelector('#searchForm .row');
                const newCol = document.createElement('div');
                newCol.className = 'col-auto';
                newCol.innerHTML = `
                    <button type="button" id="clearSearch" class="btn btn-outline-secondary btn-sm">
                        <i class="fa fa-times"></i> Limpiar
                    </button>
                `;
                formRow.appendChild(newCol);
                
                // Agregar event listener al nuevo botón
                newCol.querySelector('#clearSearch').addEventListener('click', clearSearch);
            }
        } else {
            if (clearButtonContainer) {
                clearButtonContainer.remove();
            }
        }
    }

    // Función para limpiar búsqueda
    function clearSearch() {
        searchInput.value = '';
        // Activar el toggle (Solo activos en true)
        soloActivosToggle.setAttribute('data-value', '1');
        soloActivosToggle.classList.add('active');
        soloActivosToggle.innerHTML = '<i class="bi bi-toggle-on me-2"></i>Solo Activos';
        performSearch(true); // Inmediato al limpiar
    }

    // Función para inicializar eventos de las filas
    function initializeRowEvents() {
        // Click en fila para ir al show
        document.querySelectorAll('.clickable-row').forEach(row => {
            row.addEventListener('click', function(e) {
                // No redirigir si se hace click en los botones de acciones
                if (!e.target.closest('td[onclick*="stopPropagation"]')) {
                    window.location.href = this.getAttribute('data-href');
                }
            });
        });
    }

    // Función para agregar listeners a enlaces de paginación
    function addPaginationListeners() {
        const paginationLinks = contractsContainer.querySelectorAll('.pagination a');
        paginationLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const url = new URL(this.href);
                
                // Mantener parámetros de búsqueda actuales
                const searchValue = searchInput.value.trim();
                const soloActivos = soloActivosToggle.getAttribute('data-value');
                
                if (searchValue) {
                    url.searchParams.set('search', searchValue);
                }
                url.searchParams.set('solo_activos', soloActivos);
                
                // Mostrar spinner
                loadingSpinner.classList.remove('d-none');
                
                // Realizar petición AJAX
                fetch(url.toString(), {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html',
                    }
                })
                .then(response => response.text())
                .then(data => {
                    // Crear un elemento temporal para parsear la respuesta
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = data;
                    
                    // Buscar si hay una vista parcial (solo tabla) o vista completa
                    const partialTable = tempDiv.querySelector('.table-responsive');
                    const fullContainer = tempDiv.querySelector('#contractsTableContainer');
                    
                    if (partialTable && !fullContainer) {
                        // Es una respuesta parcial (AJAX), reemplazar todo el contenedor
                        contractsContainer.innerHTML = data;
                    } else if (fullContainer) {
                        // Es una respuesta completa, extraer solo el contenido del contenedor
                        contractsContainer.innerHTML = fullContainer.innerHTML;
                    } else {
                        // Fallback: reemplazar con la respuesta completa
                        contractsContainer.innerHTML = data;
                    }
                    
                    // Actualizar URL
                    window.history.replaceState({}, '', url.toString());
                    
                    // Re-agregar listeners
                    addPaginationListeners();
                    initializeRowEvents();
                })
                .catch(error => {
                    console.error('Error en la paginación:', error);
                })
                .finally(() => {
                    loadingSpinner.classList.add('d-none');
                });
            });
        });
    }

    // Event listeners
    searchInput.addEventListener('input', () => performSearch(false)); // Con delay para input
    
    // Event listener para el botón toggle
    soloActivosToggle.addEventListener('click', function() {
        const currentValue = this.getAttribute('data-value');
        const newValue = currentValue === '1' ? '0' : '1';
        
        // Actualizar el estado del botón
        this.setAttribute('data-value', newValue);
        
        if (newValue === '1') {
            this.classList.add('active');
            this.innerHTML = '<i class="bi bi-toggle-on me-2"></i>Solo Activos';
        } else {
            this.classList.remove('active');
            this.innerHTML = '<i class="bi bi-toggle-off me-2"></i>Solo Activos';
        }
        
        performSearch(true); // Sin delay para el toggle
    });
    
    if (clearSearchBtn) {
        clearSearchBtn.addEventListener('click', clearSearch);
    }

    // Configurar listeners iniciales para paginación y filas
    addPaginationListeners();
    initializeRowEvents();

    // Manejar navegación del navegador (botones atrás/adelante)
    window.addEventListener('popstate', function() {
        location.reload();
    });
});
</script>
@endsection
