@extends('layouts.app')

@section('template_title')
    Comisiones del Contrato #{{ $contrato->id }}
@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-10">
            <a href="{{ route('contratos.show', $contrato->id) }}" class="modern-link mb-3 d-inline-block">
                <i class="bi bi-arrow-left me-1"></i>
                {{ __('Regresar') }}
            </a>
            <!-- Header moderno -->
            <div class="page-header">
                <div class="header-content" style="padding-left: 1.5rem;">
                    <div class="header-icon">
                        <i class="bi bi-percent"></i>
                    </div>
                    <div class="header-text">
                        <h1 class="page-title">Comisiones del Contrato {{$contrato->paquete->nombre}}#{{ $contrato->id }}</h1>
                        <p class="page-subtitle">Gestione las comisiones asociadas a este contrato</p>
                    </div>
                </div>
            </div>
            
            <div class="card-body " data-contrato-id="{{ $contrato->id }}">
                    
                    <!-- Gráficos de análisis de comisiones -->
                    <div class="row mb-4">
                        <!-- Tarjeta combinada: Resumen del Contrato y Estado de Comisiones -->
                        <div class="col-md-8">
                            <div class="card bg-white border-0 shadow-sm modern-card">
                                <div class="card-body">
                                    <div class="row">
                                        <!-- Sección izquierda: Resumen del Contrato -->
                                        <div class="col-md-6">
                                            <div class="info-section">
                                                <h6 class="text-muted text-uppercase small fw-bold mb-3">
                                                    <i class="bi bi-file-text me-2"></i>Resumen del Contrato
                                                </h6>

                                                <div class="row g-3">
                                                    <div class="col-12">
                                                        <div class="p-3 bg-success bg-opacity-10 border border-success border-opacity-25 rounded">
                                                            <div class="d-flex align-items-start justify-content-between">
                                                                <div>
                                                                    <div class="mb-1">
                                                                        <span class="fw-bold mb-0" style="font-weight:900; font-size:1.8em;">
                                                                            {{ $contrato->paquete->nombre ?? 'N/A' }}#{{$contrato->id }}
                                                                        </span>
                                                                    </div>
                                                                    <div class="mb-1">
                                                                        <small class="d-block text-muted">
                                                                            Cliente: <span class="fw-bold">{{ $contrato->cliente->nombre ?? 'N/A' }} {{ $contrato->cliente->apellido ?? '' }}</span>
                                                                        </small>
                                                                    </div>
                                                                </div>

                                                                @php
                                                                    $porcentajePagado = $contrato->monto_total > 0 ? ($totalPagosHechos / $contrato->monto_total) * 100 : 0;
                                                                @endphp

                                                                <div class="text-end">
                                                                    <!-- Badge del estado del contrato -->
                                                                    <div class="text-center">
                                                                        <div class="mb-3">
                                                                            <span class="badge fs-6 px-3 py-2 {{ $contrato->estado == 'activo' ? 'bg-success' : ($contrato->estado == 'suspendido' ? 'bg-warning text-dark' : ($contrato->estado == 'cancelado' ? 'bg-danger' : ($contrato->estado == 'finalizado' ? 'bg-primary' : 'bg-secondary'))) }}" 
                                                                                  style="border-radius: 20px; font-weight: 600; letter-spacing: 0.5px;">
                                                                                {{ strtoupper($contrato->estado) }}
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Resumen financiero -->
                                                            <div class="mt-3 p-2 bg-white bg-opacity-50 rounded border">
                                                                <div class="row text-center">
                                                                    <div class="col-12 mb-3">
                                                                        <small class="text-muted fw-bold">Información del Contrato</small>
                                                                    </div>

                                                                    <!-- Primera fila: Fechas de inicio y fin -->
                                                                    <div class="col-6 mb-2">
                                                                        <small class="text-muted d-block">Fecha Inicio</small>
                                                                        <small class="fw-bold text-primary">
                                                                            {{ $contrato->fecha_inicio ? \Carbon\Carbon::parse($contrato->fecha_inicio)->locale('es')->translatedFormat('d M Y') : 'No definida' }}
                                                                        </small>
                                                                    </div>
                                                                    <div class="col-6 mb-2">
                                                                        <small class="text-muted d-block">Fecha Fin</small>
                                                                        <small class="fw-bold text-primary">
                                                                            {{ $contrato->fecha_fin ? \Carbon\Carbon::parse($contrato->fecha_fin)->locale('es')->translatedFormat('d M Y') : 'Indefinida' }}
                                                                        </small>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            @if($contrato->observaciones)
                                                                <div class="mt-3 p-2 bg-info bg-opacity-10 rounded border border-info border-opacity-25">
                                                                    <small class="text-muted fw-bold d-block mb-1">Observaciones:</small>
                                                                    <small class="text-secondary" style="font-style: italic;">
                                                                        {{ Str::limit($contrato->observaciones, 120) }}
                                                                    </small>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Divisor vertical -->
                                        <div class="col-md-6 border-start">
                                            <h6 class="text-muted text-uppercase small fw-bold mb-3">
                                                <i class="bi bi-file-text me-2"></i>Estado de comisiones
                                            </h6>
                                            @php
                                                // Separar comisiones principales (no tipo PARCIALIDAD)
                                                $comisionesPrincipales = $comisiones->where('comision_padre_id', null)->where('tipo_comision', '!=', 'PARCIALIDAD');
                                                $parcialidades = $comisiones->where('comision_padre_id', '!=', null);
                                                
                                                // Para el gráfico: contar solo comisiones principales pendientes + todas las pagadas
                                                $comisionesPendientesPrincipales = $comisionesPrincipales->where('estado', 'Pendiente');
                                                $todasLasPagadas = $comisiones->where('estado', 'Pagada'); // Incluye parcialidades pagadas
                                                $todasLasOtras = $comisiones->whereNotIn('estado', ['Pagada', 'Pendiente']);
                                                
                                                // Contar para el gráfico
                                                $totalCantidadGrafico = $comisionesPendientesPrincipales->count() + $todasLasPagadas->count() + $todasLasOtras->count();
                                                $comisionesPagadasCount = $todasLasPagadas->count();
                                                $comisionesPendientesCount = $comisionesPendientesPrincipales->count(); // Solo principales
                                                $comisionesOtrasCount = $todasLasOtras->count();
                                                
                                                // Calcular montos para información adicional
                                                // Solo parcialidades pagadas (excluir comisiones padre pagadas)
                                                $soloParcialidadesPagadas = $comisiones->where('estado', 'Pagada')->where('comision_padre_id', '!=', null);
                                                $montoPagadas = $soloParcialidadesPagadas->sum('monto');
                                                
                                                // Calcular monto pendiente real: monto de comisiones padre pendientes menos sus parcialidades pagadas
                                                $montoPendientes = 0;
                                                foreach($comisionesPendientesPrincipales as $comisionPendiente) {
                                                    $totalParcialidades = $comisiones->where('comision_padre_id', $comisionPendiente->id)->where('estado', 'Pagada')->sum('monto');
                                                    $montoRestante = $comisionPendiente->monto - $totalParcialidades;
                                                    $montoPendientes += $montoRestante;
                                                }
                                                
                                                $montoOtras = $todasLasOtras->sum('monto');
                                                // Monto total: suma de todas las comisiones principales (sin restar parcialidades y sin incluir PARCIALIDAD)
                                                $montoTotal = $comisiones->where('comision_padre_id', null)->where('tipo_comision', '!=', 'PARCIALIDAD')->sum('monto');
                                                
                                                // Total real para mostrar (solo comisiones padre, no parcialidades)
                                                $totalCantidadReal = $comisiones->where('comision_padre_id', null)->count();
                                                
                                                // Calcular porcentajes basados en CANTIDAD del gráfico
                                                $porcentajePagadas = $totalCantidadGrafico > 0 ? ($comisionesPagadasCount / $totalCantidadGrafico) * 100 : 0;
                                                $porcentajePendientes = $totalCantidadGrafico > 0 ? ($comisionesPendientesCount / $totalCantidadGrafico) * 100 : 0;
                                                $porcentajeOtras = $totalCantidadGrafico > 0 ? ($comisionesOtrasCount / $totalCantidadGrafico) * 100 : 0;
                                                
                                                $estadosComision = [
                                                    'Pagada' => [
                                                        'cantidad' => $comisionesPagadasCount,
                                                        'monto' => $montoPagadas,
                                                        'porcentaje' => $porcentajePagadas,
                                                        'color' => '#28a745'
                                                    ],
                                                    'Pendiente' => [
                                                        'cantidad' => $comisionesPendientesCount,
                                                        'monto' => $montoPendientes,
                                                        'porcentaje' => $porcentajePendientes,
                                                        'color' => '#ffc107'
                                                    ]
                                                ];
                                                
                                                if ($comisionesOtrasCount > 0) {
                                                    $estadosComision['Otros'] = [
                                                        'cantidad' => $comisionesOtrasCount,
                                                        'monto' => $montoOtras,
                                                        'porcentaje' => $porcentajeOtras,
                                                        'color' => '#6c757d'
                                                    ];
                                                }
                                            @endphp
                                            <div class="row">
                                                <!-- Gráfico circular centrado -->
                                                <div class="col-6">
                                                    <div class="text-center mb-3">
                                                        <div style="position: relative; height: 140px; display: inline-block;">
                                                            <canvas id="estadosChart" width="140" height="140"></canvas>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <!-- Información organizada -->
                                                <div class="col-6">
                                                    <div class="row g-2 mb-3">
                                                        @foreach($estadosComision as $estado => $data)
                                                            <div class="col-12">
                                                                <div class="estado-item d-flex align-items-center justify-content-between p-2" style="background-color: rgba({{ $estado == 'Pagada' ? '40, 167, 69' : ($estado == 'Pendiente' ? '255, 193, 7' : '108, 117, 125') }}, 0.08);">
                                                                    <div class="d-flex align-items-center">
                                                                        <span class="badge estado-badge-circle me-2" style="background-color: {{ $data['color'] }}; width: 14px; height: 14px; border-radius: 50%;"></span>
                                                                        <div>
                                                                            <small class="fw-semibold text-dark d-block">{{ $estado }}</small>
                                                                            <small class="text-muted">
                                                                                {{ $data['cantidad'] }} 
                                                                                @if($estado == 'Pendiente')
                                                                                    comisiones
                                                                                @else
                                                                                    parcialidades
                                                                                @endif
                                                                            </small>
                                                                        </div>
                                                                    </div>
                                                                    <div class="text-end">
                                                                        <small class="fw-bold text-dark d-block">${{ number_format($data['monto'], 2) }}</small>
                                                                        <small class="text-muted">{{ round($data['porcentaje']) }}%</small>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>

                                            @if($totalCantidadReal > 0)
                                                <!-- Resumen total -->
                                                <div class="estado-resumen border-top pt-3">
                                                    <div class="row text-center">
                                                        <div class="col-6">
                                                            <small class="text-muted d-block">Comisiones</small>
                                                            <span class="fw-bold text-primary">{{ $totalCantidadReal }}</span>
                                                        </div>
                                                        <div class="col-6">
                                                            <small class="text-muted d-block">Total a pagar</small>
                                                            <span class="fw-bold text-success">${{ number_format($montoTotal, 2) }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tarjeta 3: Form de Parcialidad pagada de la comision -->
                        <div class="col-md-4">
                            <div class="card bg-white border-0 shadow-sm modern-card">
                                <div class="card-header border-0 bg-white">
                                    <h6 class="text-muted text-uppercase small fw-bold mt-2 mb-0">
                                        <i class="bi bi-file-text me-2"></i>Estado de comisiones
                                    </h6>
                                </div>
                                <div class="card-body">
                                    @if($comisionesPadre->count() > 0)
                                        <form id="formParcialidad">
                                            @csrf
                                            
                                            <!-- Selector de comisión padre y monto en la misma fila -->
                                            <div class="row mb-3">
                                                <div class="col-7">
                                                    <label for="comision_padre_id" class="form-label">
                                                        <small class="text-muted">Comisión</small>
                                                    </label>
                                                    <select class="form-select form-select-sm" id="comision_padre_id" name="comision_padre_id" required>
                                                        <option value="">Seleccionar comisión...</option>
                                                        @foreach($comisionesPadre as $comisionPadre)
                                                            @php
                                                                $totalParcialidades = $comisionPadre->parcialidades->sum('monto');
                                                                $montoRestante = $comisionPadre->monto - $totalParcialidades;
                                                            @endphp
                                                            @if($montoRestante > 0)
                                                                <option value="{{ $comisionPadre->id }}" 
                                                                        data-monto-restante="{{ $montoRestante }}"
                                                                        data-empleado="{{ $comisionPadre->empleado->nombre ?? 'N/A' }} {{ $comisionPadre->empleado->apellido ?? '' }}">
                                                                    #{{ $comisionPadre->id }} - {{ $comisionPadre->empleado->nombre ?? 'N/A' }} 
                                                                    (Restante: ${{ number_format($montoRestante, 2) }})
                                                                </option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-5">
                                                    <label for="monto" class="form-label">
                                                        <small class="text-muted">Monto Parcialidad:</small>
                                                    </label>
                                                    <div class="input-group input-group-sm">
                                                        <span class="input-group-text">$</span>
                                                        <input type="number" class="form-control" id="monto" name="monto" 
                                                               step="0.01" min="0.01" placeholder="0.00" required>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Información de la comisión seleccionada -->
                                            <div id="infoComisionPadre" class="mb-3" style="display: none;">
                                                    <small style="display: none;">
                                                        <strong></strong> <span id="empleadoInfo"></span><br>
                                                        <strong></strong> <span id="montoRestanteInfo"></span>
                                                    </small>
                                            </div>

                                            <!-- Observaciones -->
                                            <div class="mb-3">
                                                <label for="observaciones" class="form-label">
                                                    <small class="text-muted">Observaciones:</small>
                                                </label>
                                                <textarea class="form-control form-control-sm" id="observaciones" name="observaciones" 
                                                          rows="2" placeholder="Observaciones adicionales (opcional)"></textarea>
                                            </div>

                                            <!-- Botón de envío -->
                                            <div class="d-grid">
                                                <button type="submit" class="btn btn-primary btn-sm" id="btnCrearParcialidad">
                                                    <i class="bi bi-plus-circle me-1"></i>Registrar Parcialidad
                                                </button>
                                            </div>
                                        </form>
                                    @else
                                        <div class="text-center py-3">
                                            <i class="bi bi-exclamation-circle fs-2 text-muted mb-2"></i>
                                            <p class="text-muted mb-0">No hay comisiones padre disponibles</p>
                                            <small class="text-muted">Las parcialidades se crean desde comisiones principales</small>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 modern-table">
                            <thead class="modern-header">
                                <tr>
                                    <th style="width: 80px;">ID</th>
                                    <th style="width: 200px;">Empleado</th>
                                    <th style="width: 180px;">Tipo</th>
                                    <th style="width: 120px;">Monto</th>
                                    <th style="width: 220px;">Fecha</th>
                                    <th style="width: 100px;">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($comisiones as $comision)
                                    <tr class="modern-row clickable-row" style="cursor: pointer;" onclick="window.location.href='{{ route('comisiones.show', $comision->id) }}'">
                                        <td>
                                            <span class="modern-badge bg-light text-dark">
                                                {{ $comision->id }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($comision->tipo_comision != 'PARCIALIDAD')
                                                    <div class="avatar-circle me-3">
                                                        {{ strtoupper(substr($comision->empleado->nombre ?? 'N', 0, 1)) }}{{ strtoupper(substr($comision->empleado->apellido ?? 'A', 0, 1)) }}
                                                    </div>
                                                
                                                    <div>
                                                        <div class="fw-semibold text-dark">{{ $comision->empleado->nombre ?? 'N/A' }} {{ $comision->empleado->apellido ?? '' }}</div>
                                                        <small class="text-muted">ID: {{ $comision->empleado->id ?? 'N/A' }}</small>
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <span class="modern-badge {{ $comision->tipo_comision == 'PARCIALIDAD' ? 'border border-secondary text-secondary bg-light' : 'bg-info text-white' }}">
                                                {{ $comision->tipo_comision }}
                                                @if($comision->comision_padre_id)
                                                    <small class="text-muted d-block">Parcialidad de la comisión #{{ $comision->comision_padre_id }}</small>
                                                @endif
                                            </span>
                                        </td>
                                        <td>
                                            <div class="contact-info">
                                                <div class="mb-1">
                                                    <i class="bi bi-currency-dollar"></i>
                                                    
                                                    @if($comision->comision_padre_id == null && $comision->parcialidades->count() > 0)
                                                        @php
                                                            $totalParcialidades = $comision->parcialidades->sum('monto');
                                                            $restante = $comision->monto - $totalParcialidades;
                                                        @endphp
                                                        <span class="fw-bold text-success">${{ number_format($restante, 2) }}</span>
                                                        <small class="d-block text-muted">
                                                            de ${{ number_format($comision->monto, 2) }} 
                                                        </small>
                                                    @else
                                                        <span class="fw-bold {{ $comision->tipo_comision == 'PARCIALIDAD' ? 'text-muted' : 'text-success' }}">${{ number_format($comision->monto, 2) }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="contact-info">
                                                <div>
                                                    <i class="bi bi-calendar me-2"></i>
                                                    <span class="{{ $comision->tipo_comision == 'PARCIALIDAD' ? 'text-muted' : 'text-dark' }}">{{ \Carbon\Carbon::parse($comision->fecha_comision)->locale('es')->isoFormat('D [de] MMMM [de] YYYY [a las] HH:mm:ss') }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td onclick="event.stopPropagation();">
                                            <span class="modern-badge estado-badge {{ $comision->estado == 'Pagada' ? 'bg-success text-white' : ($comision->estado == 'Pendiente' ? 'bg-warning text-dark' : 'bg-secondary text-white') }}" 
                                                  style="cursor: pointer;" 
                                                  data-id="{{ $comision->id }}" 
                                                  title="Clic para cambiar estado">
                                                {{ $comision->estado }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <div class="d-flex flex-column align-items-center">
                                                <i class="bi bi-inbox fs-1 text-muted mb-3"></i>
                                                <h5 class="text-muted">No hay comisiones registradas</h5>
                                                <p class="text-muted mb-0">No se encontraron comisiones para este contrato</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
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
    min-width: 45px;
    min-height: 45px;
    border-radius: 50%;
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 0.875rem;
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
    flex-shrink: 0;
}

/* Información de contacto */
.contact-info {
    font-size: 0.875rem;
}

.contact-info i {
    width: 16px;
    font-size: 0.8rem;
    color: #79481D !important;
}

/* Sección de información adicional */
.info-section {
    font-size: 0.875rem;
}

.info-section i {
    width: 16px;
    font-size: 0.8rem;
    color: #79481D !important;
}

/* Badge moderno */
.modern-badge {
    padding: 0.5rem 0.75rem !important;
    border-radius: 8px !important;
    font-weight: 600 !important;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-size: 0.75rem !important;
    white-space: nowrap;
    display: inline-block;
    min-width: fit-content;
}

/* Estilos específicos para la columna de tipo comisión */
.modern-table td:nth-child(3) {
    min-width: 180px !important;
    white-space: normal !important;
}

.modern-table td:nth-child(3) .modern-badge {
    margin-bottom: 2px;
    display: block;
    text-align: center;
}

.modern-table td:nth-child(3) small {
    font-size: 0.7rem !important;
    line-height: 1.2;
    margin-top: 2px;
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

/* Mejoras adicionales */
.table-responsive {
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.card, .modern-card {
    border-radius: 16px !important;
    overflow: hidden;
    transition: all 0.3s ease;
}

.modern-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
}

/* Estilos para actualizaciones en tiempo real */
.estado-badge {
    transition: all 0.3s ease;
    cursor: pointer !important;
}

.estado-badge:hover {
    transform: scale(1.05);
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

/* Asegurar que los badges mantengan su estilo */
.estado-badge.bg-success {
    background-color: #28a745 !important;
    color: white !important;
}

.estado-badge.bg-warning {
    background-color: #ffc107 !important;
    color: #212529 !important;
}

.estado-badge.bg-secondary {
    background-color: #6c757d !important;
    color: white !important;
}

.estado-badge.bg-info {
    background-color: #17a2b8 !important;
    color: white !important;
}

.estado-badge.bg-danger {
    background-color: #dc3545 !important;
    color: white !important;
}

/* Animaciones para actualizaciones de gráficos */
.chart-updating {
    opacity: 0.7;
    transition: opacity 0.3s ease;
}

.chart-updated {
    animation: chartPulse 0.5s ease-in-out;
}

@keyframes chartPulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.02); }
    100% { transform: scale(1); }
}

/* Animaciones para los contadores */
.counter-updating {
    animation: counterUpdate 0.6s ease-in-out;
}

@keyframes counterUpdate {
    0% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.1); opacity: 0.8; }
    100% { transform: scale(1); opacity: 1; }
}

/* Estilos específicos para gráficos */
.progress {
    background-color: #e9ecef;
    border-radius: 10px;
    overflow: hidden;
}

.progress-bar {
    transition: width 0.6s ease;
}

.chart-container {
    position: relative;
    height: 120px;
    margin: 10px 0;
}

/* Estilos para la tarjeta de estados de comisiones */
.estado-item {
    background-color: rgba(248, 249, 250, 0.8);
    border: 1px solid rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    border-radius: 8px;
}

.estado-item:hover {
    background-color: rgba(248, 249, 250, 1);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transform: translateY(-1px);
}

.estado-badge-circle {
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
}

.estado-resumen {
    background-color: rgba(102, 126, 234, 0.05);
    border-radius: 8px;
    padding: 0.75rem;
}

.estado-resumen .text-primary {
    color: #667eea !important;
}

.estado-resumen .text-success {
    color: #28a745 !important;
}

/* Responsividad */
@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        text-align: center;
        gap: 1rem;
    }
    
    .header-content {
        flex-direction: column;
        text-align: center;
    }
    
    .header-icon {
        margin-right: 0;
        margin-bottom: 1rem;
    }
    
    .modern-header th,
    .modern-row td {
        padding: 1rem 0.5rem !important;
    }

    .avatar-circle {
        width: 35px;
        height: 35px;
        min-width: 35px;
        min-height: 35px;
        font-size: 0.75rem;
        flex-shrink: 0;
    }

    .contact-info,
    .info-section {
        font-size: 0.8rem;
    }
    
    .col-md-4 {
        margin-bottom: 1rem;
    }
    
    /* Hacer la tabla más compacta en móviles */
    .table td, .table th {
        font-size: 0.875rem;
        padding: 0.5rem;
    }
    
    /* Ajustar el ancho de la columna de fecha */
    .table td:nth-child(6) {
        white-space: nowrap;
        font-size: 0.75rem;
        min-width: 180px;
    }
    
    /* Asegurar que los contenedores flexibles no deformen el avatar */
    .d-flex.align-items-center {
        flex-wrap: nowrap;
    }
    
    .d-flex.align-items-center > div:first-child {
        flex-shrink: 0;
    }
}

/* Media query adicional para pantallas muy pequeñas */
@media (max-width: 480px) {
    .avatar-circle {
        width: 30px;
        height: 30px;
        min-width: 30px;
        min-height: 30px;
        font-size: 0.7rem;
        flex-shrink: 0;
    }
    
    .modern-header th,
    .modern-row td {
        padding: 0.75rem 0.25rem !important;
    }
    
    /* Reducir el padding en los contenedores de empleado */
    .d-flex.align-items-center .me-3 {
        margin-right: 0.5rem !important;
    }
}
</style>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Variables globales para los gráficos
let estadosChart = null;

class ComisionesRealTime {
    constructor() {
        this.contratoId = document.querySelector('[data-contrato-id]').getAttribute('data-contrato-id');
        this.pollingInterval = null;
        this.pollingFrequency = 2000; // Reducido a 2 segundos para mayor inmediatez
        this.lastUpdate = 0;
        this.isVisible = true;
        
        this.init();
    }
    
    init() {
        // this.setupClickHandlers();
        this.startPolling();
        this.setupVisibilityChange();
    }
    
    // setupClickHandlers() {
    //     document.querySelectorAll('.estado-badge').forEach(badge => {
    //         badge.addEventListener('click', (e) => this.handleBadgeClick(e));
    //     });
    // }
    
    // async handleBadgeClick(event) {
    //     const badge = event.target;
    //     const comisionId = badge.getAttribute('data-id');
    //     const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
    //     // Guardar estado original
    //     const estadoOriginal = badge.textContent;
    //     const claseOriginal = badge.className;
        
    //     // Cambiar a estado de loading
    //     badge.style.cursor = 'wait';
    //     badge.textContent = 'Cambiando...';
    //     badge.className = 'modern-badge estado-badge bg-info text-white';
        
    //     try {
    //         const response = await fetch(`/comisiones/${comisionId}/toggle-estado`, {
    //             method: 'PATCH',
    //             headers: {
    //                 'Content-Type': 'application/json',
    //                 'X-CSRF-TOKEN': csrfToken,
    //                 'Accept': 'application/json'
    //             }
    //         });
            
    //         if (!response.ok) {
    //             throw new Error('Error en la respuesta del servidor');
    //         }
            
    //         const data = await response.json();
            
    //         if (data.success) {
    //             // Actualizar inmediatamente el badge
    //             this.updateBadge(badge, data.nuevo_estado, data.badge_class);
                
    //             // Actualizar la fecha de comisión en la tabla si se proporciona
    //             if (data.fecha_comision) {
    //                 this.updateFechaComision(badge, data.fecha_comision);
    //             }
                
    //             // Actualizar los gráficos INMEDIATAMENTE
    //             this.updateChartsFromDOM();
                
    //             // Actualizar también la información del lado derecho del gráfico
    //             this.updateChartInfo();
                
    //             // Mostrar confirmación visual
    //             badge.style.transform = 'scale(1.2)';
    //             badge.style.transition = 'all 0.3s ease';
    //             setTimeout(() => {
    //                 badge.style.transform = 'scale(1)';
    //             }, 300);
                
    //             // Forzar una actualización inmediata para sincronizar con otros usuarios
    //             setTimeout(() => this.checkForUpdates(), 200);
    //         } else {
    //             throw new Error(data.error || 'Error desconocido');
    //         }
    //     } catch (error) {
    //         console.error('Error:', error);
    //         this.handleError(badge, estadoOriginal, claseOriginal, error.message);
    //     } finally {
    //         badge.style.cursor = 'pointer';
    //     }
    // }
    
    updateBadge(badge, estado, badgeClass) {
        badge.textContent = estado;
        // Si no se proporciona badgeClass, determinarlo basado en el estado
        if (!badgeClass) {
            badgeClass = this.getBadgeClass(estado);
        }
        badge.className = `modern-badge estado-badge ${badgeClass}`;
    }
    
    getBadgeClass(estado) {
        switch(estado) {
            case 'Pagada':
                return 'bg-success text-white';
            case 'Pendiente':
                return 'bg-warning text-dark';
            default:
                return 'bg-secondary text-white';
        }
    }
    
    updateFechaComision(badge, nuevaFecha) {
        // Encontrar la fila que contiene este badge
        const row = badge.closest('tr');
        if (row) {
            // La fecha está en la columna 4 (índice 4, "Fecha Comisión")
            const fechaCell = row.children[4];
            if (fechaCell) {
                // Buscar el span dentro de la celda que contiene la fecha
                const fechaSpan = fechaCell.querySelector('.text-dark');
                if (fechaSpan) {
                    // Agregar animación de actualización
                    fechaSpan.classList.add('counter-updating');
                    fechaSpan.textContent = nuevaFecha;
                    
                    // Remover la animación después de un tiempo
                    setTimeout(() => {
                        fechaSpan.classList.remove('counter-updating');
                    }, 600);
                }
            }
        }
    }
    
    handleError(badge, estadoOriginal, claseOriginal, errorMessage) {
        // Restaurar estado original
        badge.textContent = estadoOriginal;
        badge.className = claseOriginal;
        
        // Mostrar error temporal
        badge.textContent = '✗ Error';
        badge.className = 'modern-badge estado-badge bg-danger text-white';
        setTimeout(() => {
            badge.textContent = estadoOriginal;
            badge.className = claseOriginal;
        }, 2000);
    }
    
    async checkForUpdates() {
        if (!this.isVisible) return;
        
        try {
            const response = await fetch(`/contratos/${this.contratoId}/comisiones/estados`);
            
            if (!response.ok) {
                throw new Error('Error al obtener actualizaciones');
            }
            
            const data = await response.json();
            
            if (data.success) {
                this.processUpdates(data.estados);
                this.lastUpdate = data.timestamp;
                
                // Actualizar gráficos si hubo cambios
                this.updateChartsAfterStateChange();
            }
        } catch (error) {
            console.error('Error en polling:', error);
        }
    }
    
    processUpdates(estadosServidor) {
        let huboCambios = false;
        
        Object.keys(estadosServidor).forEach(comisionId => {
            const badge = document.querySelector(`[data-id="${comisionId}"]`);
            if (badge) {
                const estadoServidor = estadosServidor[comisionId];
                const estadoActual = badge.textContent.replace('✗ Error', '').trim();
                
                // Solo actualizar si el estado cambió
                if (estadoActual !== estadoServidor.estado) {
                    huboCambios = true;
                    this.updateBadge(badge, estadoServidor.estado, estadoServidor.badge_class);
                    
                    // Actualizar fecha si se proporciona
                    if (estadoServidor.fecha_comision) {
                        this.updateFechaComision(badge, estadoServidor.fecha_comision);
                    }
                    
                    // Mostrar animación de cambio sutil para cambios remotos
                    badge.style.transform = 'scale(1.05)';
                    badge.style.transition = 'transform 0.2s ease';
                    badge.style.boxShadow = '0 0 10px rgba(102, 126, 234, 0.5)';
                    setTimeout(() => {
                        badge.style.transform = 'scale(1)';
                        badge.style.boxShadow = '';
                    }, 400);
                }
            }
        });
        
        // Si hubo cambios, actualizar gráficos inmediatamente
        if (huboCambios) {
            this.updateChartsFromDOM();
            this.updateChartInfo();
            
            // Mostrar indicador visual de actualización
            this.showUpdateIndicator();
        }
    }
    
    // Función para mostrar un indicador visual de actualización
    showUpdateIndicator() {
        // Buscar si ya existe un indicador
        let indicator = document.getElementById('update-indicator');
        
        if (!indicator) {
            // Crear el indicador
            indicator = document.createElement('div');
            indicator.id = 'update-indicator';
            indicator.innerHTML = '<i class="bi bi-arrow-clockwise me-1"></i>Datos actualizados';
            indicator.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: linear-gradient(135deg, #28a745, #20c997);
                color: white;
                padding: 8px 16px;
                border-radius: 25px;
                font-size: 0.875rem;
                font-weight: 600;
                box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
                z-index: 9999;
                opacity: 0;
                transform: translateY(-20px);
                transition: all 0.3s ease;
            `;
            document.body.appendChild(indicator);
        }
        
        // Mostrar el indicador
        setTimeout(() => {
            indicator.style.opacity = '1';
            indicator.style.transform = 'translateY(0)';
        }, 10);
        
        // Ocultar después de 2 segundos
        setTimeout(() => {
            indicator.style.opacity = '0';
            indicator.style.transform = 'translateY(-20px)';
            setTimeout(() => {
                if (indicator.parentNode) {
                    indicator.parentNode.removeChild(indicator);
                }
            }, 300);
        }, 2000);
    }
    
    startPolling() {
        this.checkForUpdates(); // Primera verificación inmediata
        this.pollingInterval = setInterval(() => {
            this.checkForUpdates();
        }, this.pollingFrequency);
    }
    
    stopPolling() {
        if (this.pollingInterval) {
            clearInterval(this.pollingInterval);
            this.pollingInterval = null;
        }
    }
    
    setupVisibilityChange() {
        document.addEventListener('visibilitychange', () => {
            this.isVisible = !document.hidden;
            
            if (this.isVisible) {
                this.startPolling();
            } else {
                this.stopPolling();
            }
        });
    }
    
    // Nuevo método para actualizar gráficos tras cambios de estado
    async updateChartsAfterStateChange() {
        try {
            // Obtener datos actualizados del servidor
            const response = await fetch(`/contratos/${this.contratoId}/comisiones/chart-data`);
            
            if (!response.ok) {
                // Si no existe la ruta, calcular datos desde el DOM
                this.updateChartsFromDOM();
                return;
            }
            
            const data = await response.json();
            
            if (data.success) {
                // Actualizar gráfico de progreso
                this.updateProgressChart(data.progreso);
                
                // Actualizar gráfico de estados
                if (estadosChart) {
                    this.updateEstadosChart(data.estados);
                }
            }
        } catch (error) {
            console.log('Actualizando gráficos desde DOM...');
            this.updateChartsFromDOM();
        }
    }
    
    // Actualizar gráficos calculando desde el DOM actual
    updateChartsFromDOM() {
        // Obtener todos los badges de estado actuales
        const badges = document.querySelectorAll('.estado-badge');
        let totalPagadas = 0;
        let totalPendientes = 0;
        let totalOtros = 0;
        let estadosCount = {
            'Pagada': 0,
            'Pendiente': 0,
            'Otros': 0
        };
        
        badges.forEach(badge => {
            const estado = badge.textContent.replace('✗ Error', '').trim();
            const row = badge.closest('tr');
            
            if (row) {
                // Verificar si es una parcialidad
                const tipoComisionCell = row.children[2];
                const esParcialidad = tipoComisionCell && tipoComisionCell.textContent.includes('PARCIALIDAD');
                
                const montoText = row.children[3].textContent.replace('$', '').replace(',', '');
                const monto = parseFloat(montoText) || 0;
                
                if (estado === 'Pagada') {
                    // Solo sumar parcialidades pagadas, no comisiones padre pagadas
                    if (esParcialidad) {
                        totalPagadas += monto;
                    }
                    estadosCount['Pagada']++; // Contar todas las pagadas (principales y parcialidades)
                } else if (estado === 'Pendiente') {
                    totalPendientes += monto;
                    // Solo contar pendientes si NO es parcialidad
                    if (!esParcialidad) {
                        estadosCount['Pendiente']++;
                    }
                } else {
                    totalOtros += monto;
                    estadosCount['Otros']++;
                }
            }
        });
        
        // Actualizar barra de progreso basada en montos
        const total = totalPagadas + totalPendientes + totalOtros;
        const porcentajePagado = total > 0 ? (totalPagadas / total) * 100 : 0;
        const porcentajePendiente = total > 0 ? (totalPendientes / total) * 100 : 0;
        
        this.updateProgressBars(porcentajePagado, porcentajePendiente, totalPagadas, totalPendientes, total);
        
        // Actualizar gráfico de estados basado en CANTIDAD filtrada
        if (estadosChart) {
            const labels = [];
            const data = [];
            const colors = [];
            
            if (estadosCount['Pagada'] > 0) {
                labels.push('Pagada');
                data.push(estadosCount['Pagada']);
                colors.push('#28a745');
            }
            
            if (estadosCount['Pendiente'] > 0) {
                labels.push('Pendiente');
                data.push(estadosCount['Pendiente']);
                colors.push('#ffc107');
            }
            
            if (estadosCount['Otros'] > 0) {
                labels.push('Otros');
                data.push(estadosCount['Otros']);
                colors.push('#6c757d');
            }
            
            estadosChart.data.labels = labels;
            estadosChart.data.datasets[0].data = data;
            estadosChart.data.datasets[0].backgroundColor = colors;
            estadosChart.update('active');
            
            // Agregar animación al contenedor del gráfico
            const chartContainer = document.getElementById('estadosChart').parentElement;
            chartContainer.classList.add('chart-updated');
            setTimeout(() => {
                chartContainer.classList.remove('chart-updated');
            }, 500);
        }
    }
    
    // Nueva función para actualizar la información del gráfico en tiempo real
    updateChartInfo() {
        // Obtener todos los badges de estado actuales
        const badges = document.querySelectorAll('.estado-badge');
        let estadosCount = {
            'Pagada': 0,
            'Pendiente': 0,
            'Otros': 0
        };
        let estadosMontos = {
            'Pagada': 0,
            'Pendiente': 0,
            'Otros': 0
        };
        
        badges.forEach(badge => {
            const estado = badge.textContent.replace('✗ Error', '').trim();
            const row = badge.closest('tr');
            
            if (row) {
                // Verificar si es una parcialidad
                const tipoComisionCell = row.children[2];
                const esParcialidad = tipoComisionCell && tipoComisionCell.textContent.includes('PARCIALIDAD');
                
                const montoText = row.children[3].textContent.replace('$', '').replace(',', '');
                const monto = parseFloat(montoText) || 0;
                
                if (estado === 'Pagada') {
                    estadosCount['Pagada']++;
                    // Solo sumar parcialidades pagadas, no comisiones padre pagadas
                    if (esParcialidad) {
                        estadosMontos['Pagada'] += monto;
                    }
                } else if (estado === 'Pendiente') {
                    estadosMontos['Pendiente'] += monto;
                    // Solo contar pendientes si NO es parcialidad
                    if (!esParcialidad) {
                        estadosCount['Pendiente']++;
                    }
                } else {
                    estadosCount['Otros']++;
                    estadosMontos['Otros'] += monto;
                }
            }
        });
        
        // Calcular totales
        const totalCantidad = estadosCount['Pagada'] + estadosCount['Pendiente'] + estadosCount['Otros'];
        const totalMonto = estadosMontos['Pagada'] + estadosMontos['Pendiente'] + estadosMontos['Otros'];
        
        // Actualizar la información del lado derecho del gráfico
        Object.keys(estadosCount).forEach(estado => {
            if (estadosCount[estado] > 0) {
                const porcentaje = totalCantidad > 0 ? (estadosCount[estado] / totalCantidad) * 100 : 0;
                
                // Buscar el elemento correspondiente en el DOM
                const estadoElements = document.querySelectorAll('.ps-2 .d-flex');
                estadoElements.forEach(element => {
                    const estadoText = element.querySelector('small').textContent.trim();
                    if (estadoText === estado) {
                        const cantidadElement = element.querySelector('.text-end .text-muted');
                        const montoElement = element.querySelector('.text-end .fw-bold');
                        
                        if (cantidadElement && montoElement) {
                            // Agregar animación
                            cantidadElement.classList.add('counter-updating');
                            montoElement.classList.add('counter-updating');
                            
                            // Actualizar textos
                            const tipoTexto = estado === 'Pendiente' ? 'comisiones principales' : 'parcialidades';
                            cantidadElement.textContent = `${estadosCount[estado]} ${tipoTexto} (${Math.round(porcentaje)}%)`;
                            montoElement.textContent = `$${estadosMontos[estado].toLocaleString('es-ES', { minimumFractionDigits: 2 })}`;
                            
                            // Remover animación
                            setTimeout(() => {
                                cantidadElement.classList.remove('counter-updating');
                                montoElement.classList.remove('counter-updating');
                            }, 600);
                        }
                    }
                });
            }
        });
        
        // Actualizar totales
        const totalParcialidadesElement = document.querySelector('.text-center .text-muted');
        const totalMontoElement = document.querySelector('.text-center .fw-bold');
        
        if (totalParcialidadesElement && totalMontoElement) {
            totalParcialidadesElement.classList.add('counter-updating');
            totalMontoElement.classList.add('counter-updating');
            
            const totalRealParcialidades = badges.length;
            totalParcialidadesElement.textContent = `Total Parcialidades: ${totalRealParcialidades}`;
            totalMontoElement.textContent = `Total Monto: $${totalMonto.toLocaleString('es-ES', { minimumFractionDigits: 2 })}`;
            
            setTimeout(() => {
                totalParcialidadesElement.classList.remove('counter-updating');
                totalMontoElement.classList.remove('counter-updating');
            }, 600);
        }
    }
    
    updateProgressBars(porcentajePagado, porcentajePendiente, totalPagadas, totalPendientes, total) {
        // Agregar clase de animación
        const progressContainer = document.querySelector('.progress');
        if (progressContainer) {
            progressContainer.classList.add('chart-updating');
        }
        
        // Actualizar barras de progreso
        const progressBars = document.querySelectorAll('.progress-bar');
        if (progressBars.length >= 2) {
            // Animar cambio de width
            setTimeout(() => {
                progressBars[0].style.width = porcentajePagado + '%';
                progressBars[0].textContent = Math.round(porcentajePagado) + '%';
                progressBars[0].title = `Pagadas: $${totalPagadas.toLocaleString('es-ES', { minimumFractionDigits: 2 })}`;
                
                progressBars[1].style.width = porcentajePendiente + '%';
                progressBars[1].textContent = Math.round(porcentajePendiente) + '%';
                progressBars[1].title = `Pendientes: $${totalPendientes.toLocaleString('es-ES', { minimumFractionDigits: 2 })}`;
            }, 100);
        }
        
        // Actualizar textos de totales con animación
        const totalElement = document.querySelector('.text-muted');
        if (totalElement && totalElement.textContent.includes('Total Comisiones')) {
            totalElement.classList.add('counter-updating');
            totalElement.textContent = `Total Comisiones: $${total.toLocaleString('es-ES', { minimumFractionDigits: 2 })}`;
            
            setTimeout(() => {
                totalElement.classList.remove('counter-updating');
            }, 600);
        }
        
        // Actualizar montos en las columnas con animación
        const pagadasElement = document.querySelector('.text-success strong');
        const pendientesElement = document.querySelector('.text-warning strong');
        
        if (pagadasElement) {
            pagadasElement.classList.add('counter-updating');
            pagadasElement.textContent = `$${totalPagadas.toLocaleString('es-ES', { minimumFractionDigits: 2 })}`;
            
            setTimeout(() => {
                pagadasElement.classList.remove('counter-updating');
            }, 600);
        }
        
        if (pendientesElement) {
            pendientesElement.classList.add('counter-updating');
            pendientesElement.textContent = `$${totalPendientes.toLocaleString('es-ES', { minimumFractionDigits: 2 })}`;
            
            setTimeout(() => {
                pendientesElement.classList.remove('counter-updating');
            }, 600);
        }
        
        // Remover clase de actualización
        setTimeout(() => {
            if (progressContainer) {
                progressContainer.classList.remove('chart-updating');
                progressContainer.classList.add('chart-updated');
                
                setTimeout(() => {
                    progressContainer.classList.remove('chart-updated');
                }, 500);
            }
        }, 600);
    }
    
    updateEstadosChart(estadosData) {
        if (!estadosChart) return;
        
        const labels = Object.keys(estadosData);
        const data = labels.map(label => estadosData[label].cantidad);
        const colors = labels.map(label => {
            switch(label) {
                case 'Pagada': return '#28a745';
                case 'Pendiente': return '#ffc107';
                default: return '#6c757d';
            }
        });
        const chartContainer = document.getElementById('estadosChart').parentElement;
        
        // Agregar animación
        chartContainer.classList.add('chart-updating');
        
        setTimeout(() => {
            estadosChart.data.labels = labels;
            estadosChart.data.datasets[0].data = data;
            estadosChart.data.datasets[0].backgroundColor = colors;
            estadosChart.update('active');
            
            chartContainer.classList.remove('chart-updating');
            chartContainer.classList.add('chart-updated');
            
            setTimeout(() => {
                chartContainer.classList.remove('chart-updated');
            }, 500);
        }, 200);
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    window.comisionesRealTime = new ComisionesRealTime();
    initializeCharts();
    initializeTooltips();
    initializeParcialidadForm();
});

// Función para inicializar tooltips
function initializeTooltips() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

// Función para inicializar los gráficos
function initializeCharts() {
    initEstadosChart();
}

// Gráfico de estados de comisión (Dona)
function initEstadosChart() {
    const ctx = document.getElementById('estadosChart');
    if (!ctx) return;
    
    const estadosData = @json($estadosComision);
    const labels = Object.keys(estadosData);
    const data = labels.map(label => estadosData[label].cantidad); // Cambio: volver a usar cantidad (parcialidades)
    const colors = labels.map(label => estadosData[label].color);
    
    // Verificar si hay datos
    if (labels.length === 0) {
        ctx.getContext('2d').font = '14px Arial';
        ctx.getContext('2d').textAlign = 'center';
        ctx.getContext('2d').fillText('Sin datos', ctx.width/2, ctx.height/2);
        return;
    }
    
    estadosChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: colors,
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: {
                duration: 800,
                easing: 'easeInOutQuart'
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                            // Cambio: mostrar número de parcialidades en lugar de monto
                            const descripcion = label === 'Pendiente' ? 'comisiones principales' : 'parcialidades';
                            return `${label}: ${value} ${descripcion} (${percentage}%)`;
                        }
                    }
                }
            },
            cutout: '60%'
        }
    });
}

// Función para inicializar el formulario de parcialidades
function initializeParcialidadForm() {
    const formParcialidad = document.getElementById('formParcialidad');
    const selectComisionPadre = document.getElementById('comision_padre_id');
    const infoComisionPadre = document.getElementById('infoComisionPadre');
    const empleadoInfo = document.getElementById('empleadoInfo');
    const montoRestanteInfo = document.getElementById('montoRestanteInfo');
    const inputMonto = document.getElementById('monto');

    if (!formParcialidad) return;

    // Manejar cambio en el selector de comisión padre
    selectComisionPadre.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        
        if (this.value) {
            const montoRestante = parseFloat(selectedOption.getAttribute('data-monto-restante'));
            const empleado = selectedOption.getAttribute('data-empleado');
            
            empleadoInfo.textContent = empleado;
            montoRestanteInfo.textContent = montoRestante.toLocaleString('es-ES', { minimumFractionDigits: 2 });
            
            // Establecer el máximo para el input de monto con el valor exacto
            inputMonto.setAttribute('max', montoRestante.toFixed(2));
            
            // Establecer automáticamente el monto máximo restante exacto
            inputMonto.value = montoRestante.toFixed(2);
            
            infoComisionPadre.style.display = 'block';
        } else {
            infoComisionPadre.style.display = 'none';
            inputMonto.removeAttribute('max');
            inputMonto.value = '';
        }
    });

    // Manejar envío del formulario
    formParcialidad.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const btnSubmit = document.getElementById('btnCrearParcialidad');
        const originalText = btnSubmit.innerHTML;
        
        // Deshabilitar botón y mostrar loading
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Creando...';
        
        fetch('{{ route("contratos.crearParcialidad") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Mostrar mensaje de éxito
                showNotification('Parcialidad creada exitosamente', 'success');
                
                // Limpiar formulario
                formParcialidad.reset();
                infoComisionPadre.style.display = 'none';
                inputMonto.removeAttribute('max');
                
                // Recargar la página para mostrar la nueva parcialidad
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                showNotification(data.message || 'Error al crear la parcialidad', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error al procesar la solicitud', 'error');
        })
        .finally(() => {
            // Restaurar botón
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = originalText;
        });
    });
}

// Función para mostrar notificaciones
function showNotification(message, type = 'info') {
    // Crear el elemento de notificación
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'error' ? 'danger' : type === 'success' ? 'success' : 'info'} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    // Agregar al DOM
    document.body.appendChild(notification);
    
    // Auto-remover después de 5 segundos
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

// Limpiar al cerrar la página
window.addEventListener('beforeunload', () => {
    if (window.comisionesRealTime) {
        window.comisionesRealTime.stopPolling();
    }
});
</script>
@endsection
