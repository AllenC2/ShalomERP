@extends('layouts.app')

@section('content')
@php
    // Zona horaria de México
    date_default_timezone_set('America/Mexico_City');
    $hour = date('H');
    if ($hour >= 6 && $hour < 12) {
        $greeting = 'Buenos días';
    } elseif ($hour >= 12 && $hour < 19) {
        $greeting = 'Buenas tardes';
    } else {
        $greeting = 'Buenas noches';
    }
@endphp

<style>
.agenda-card {
    transition: all 0.3s ease;
    min-height: 300px;
}


.agenda-card.today {
    background: linear-gradient(135deg, #e3f2fd 0%, #f8f9fa 100%);
}

.pago-item {
    transition: all 0.2s ease;
    cursor: pointer;
}

.pago-item:hover {
    background-color: #f8f9fa !important;
    transform: translateX(2px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.pago-link {
    transition: all 0.2s ease;
}

.pago-link:hover .pago-item {
    transform: translateX(4px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.agenda-nav-btn {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #E1B240 0%, #79481D 100%);
    border: 2px solid #E1B240;
    color: white;
    transition: all 0.3s ease;
}

.agenda-nav-btn:hover {
    background: linear-gradient(135deg, #79481D 0%, #E1B240 100%);
    border-color: #79481D;
    color: white;
    transform: scale(1.1);
    box-shadow: 0 4px 12px rgba(225, 178, 64, 0.4);
}

.agenda-nav-btn.btn-primary {
    background: linear-gradient(135deg, #79481D 0%, #E1B240 100%);
    border-color: #79481D;
}

.agenda-nav-btn.btn-primary:hover {
    background: linear-gradient(135deg, #E1B240 0%, #79481D 100%);
    border-color: #E1B240;
}

.agenda-header {
    background: linear-gradient(135deg, #E1B240 0%, #79481D 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

/* Estilos para el mini calendario */
.mini-calendario {
    background: transparent;
    border: 1px solid rgba(222, 226, 230, 0.3);
    border-radius: 8px;
    box-shadow: none;
    position: relative;
    width: 100%;
    max-width: 300px;
}

.calendario-header {
    background: linear-gradient(135deg, #E1B240 0%, #79481D 100%);
    color: white;
    padding: 10px;
    border-radius: 8px 8px 0 0;
    text-align: center;
    font-weight: bold;
}

.calendario-nav {
    background: none;
    border: none;
    color: white;
    font-size: 1.2rem;
    cursor: pointer;
    padding: 5px 8px;
    border-radius: 4px;
    transition: all 0.2s ease;
}

.calendario-nav:hover {
    background: rgba(255,255,255,0.2);
}

.calendario-dias-semana {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 1px;
    background: transparent;
    padding: 8px 5px;
    font-size: 0.7rem;
    font-weight: bold;
    text-align: center;
    color: #6c757d;
}

.calendario-dias {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 1px;
    background: transparent;
    padding: 5px;
}

.calendario-dia {
    aspect-ratio: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.8rem;
    transition: all 0.2s ease;
    position: relative;
    color: #495057;
}

.calendario-dia:hover {
    background: rgba(227, 242, 253, 0.3);
    border-color: rgba(227, 242, 253, 0.5);
}

.calendario-dia.hoy {
    background: linear-gradient(135deg, #E1B240 0%, #79481D 100%);
    color: white;
    font-weight: bold;
}

.calendario-dia.seleccionado {
    background: #2196f3;
    color: white;
    font-weight: bold;
}

.calendario-dia.otro-mes {
    color: #adb5bd;
    background: rgba(248, 249, 250, 0.1);
}

.calendario-dia.con-pagos::after {
    content: '';
    position: absolute;
    bottom: 2px;
    left: 50%;
    transform: translateX(-50%);
    width: 4px;
    height: 4px;
    background: #E1B240;
    border-radius: 50%;
}

.calendario-dia.hoy.con-pagos::after {
    background: white;
}

/* Estilos para paginación */
#controlesPaginacion {
    background: rgba(248, 249, 250, 0.3);
    border-radius: 8px;
    padding: 12px;
    margin-top: 15px;
}

#controlesPaginacion .btn-outline-secondary {
    border-color: #79481D;
    color: #79481D;
    background: transparent;
    font-size: 0.8rem;
    padding: 0.375rem 0.75rem;
    transition: all 0.2s ease;
}

#controlesPaginacion .btn-outline-secondary:hover:not(:disabled) {
    background: linear-gradient(135deg, #E1B240 0%, #79481D 100%);
    border-color: #E1B240;
    color: white;
}

#controlesPaginacion .btn-outline-secondary:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

#itemsPorPagina {
    border-color: #79481D;
    font-size: 0.8rem;
    padding: 0.25rem 0.5rem;
    min-width: 70px;
}

#itemsPorPagina:focus {
    border-color: #E1B240;
    box-shadow: 0 0 0 0.2rem rgba(225, 178, 64, 0.25);
}

#infoPaginacion {
    background: rgba(121, 72, 29, 0.1);
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.75rem;
}

/* Estilos para el badge de rango de fechas */
#rangoFechas {
    transition: all 0.3s ease;
    border: 1px solid rgba(121, 72, 29, 0.2);
    font-weight: 500;
    letter-spacing: 0.02em;
}

#rangoFechas:hover {
    background: rgba(121, 72, 29, 0.15) !important;
    border-color: rgba(121, 72, 29, 0.3);
    transform: translateY(-1px);
}

@media (max-width: 768px) {
    .agenda-card {
        min-height: 150px;
    }
    
    .col {
        min-width: 120px;
    }
    
    .mini-calendario {
        max-width: 250px;
    }
    
    /* Ajustes responsive para paginación */
    #controlesPaginacion {
        flex-direction: column;
        gap: 10px;
        align-items: stretch !important;
    }
    
    #controlesPaginacion .btn-group {
        justify-content: center;
    }
    
    #controlesPaginacion .d-flex {
        justify-content: center;
    }
    
    #infoPaginacion {
        text-align: center;
        margin-top: 5px;
    }
}
</style>

{{-- Rainbow background container --}}
<div class="rainbow-background">
    @for ($i = 1; $i <= 25; $i++)
        <div class="rainbow"></div>
    @endfor
    
    <div class="h"></div>
    <div class="v"></div>
</div>

<div class="container" style="position: relative; z-index: 10;">
    <div class="mb-4 text-start">
        <h3 class="display-2 fw-bold" style="line-height: 0.8; opacity: 0.6; letter-spacing: -2px;">¡{{ $greeting }}, 
            <br>
        {{ Auth::user()->name }}!</h3>
    </div>
    
    {{-- Agenda Minimalista - Solo para administradores --}}
    @if(Auth::user()->role === 'admin')
    <div class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex justify-content-start mt-3 mb-3">
                <div class="text-start">
                    <h4 class="fw-bold agenda-header mb-0 me-3">
                        <i class="bi bi-calendar me-2"></i>Agenda de Pagos
                    </h4>
                    <div>
                        @if($currentDayOffset == 0)

                            <span class="badge" style="background: linear-gradient(135deg, #79481D 0%, #E1B240 100%); color: white;">
                                Hoy
                            </span>

                        @endif
                        <small class="text-muted">
                            {{ $agendaDia['fecha']->translatedFormat('l, d \d\e F \d\e Y') }}
                        </small>
                    </div>
                       
                </div>
            </div>
            <div class="btn-group" role="group">
                <a href="{{ route('home', ['day' => $currentDayOffset - 1]) }}" 
                   class="btn btn-outline-primary agenda-nav-btn" 
                   title="Día anterior"
                   style="background: none; color: #79481D;">
                    <i class="bi bi-chevron-left"></i>
                </a>
                @if($currentDayOffset != 0)
                    <a href="{{ route('home') }}" 
                       class="btn btn-outline-primary agenda-nav-btn"
                       title="Hoy"
                       style="background: none; color: #79481D;">
                        <i class="bi bi-house-fill"></i>
                    </a>
                @endif
                <a href="{{ route('home', ['day' => $currentDayOffset + 1]) }}" 
                   class="btn btn-outline-primary agenda-nav-btn"
                   title="Día siguiente"
                   style="background: none; color: #79481D;">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </div>
        </div>
        
        
        <div class="row justify-content-center">
            <div class="col-lg-12">
                <div class="agenda-card {{ $agendaDia['fecha']->isToday() ? 'today' : '' }}" style="background: transparent;">
                    <div class="">
                        <div class="row">
                            {{-- Columna izquierda: Información del día y contadores --}}
                            <div class="col-md-3">
                                {{-- Cabecera del día --}}
                                <div class="text-start mb-4">
                                    {{-- Mini calendario siempre visible --}}
                                    <div id="miniCalendario" class="mini-calendario w-100">
                                        <!-- El calendario se generará dinámicamente con JavaScript -->
                                    </div>
                                </div>

                                {{-- Resumen de pagos --}}
                                @if($agendaDia['pagos_pendientes']->count() > 0 || $agendaDia['pagos_hechos']->count() > 0)
                                    <div class="border-top pt-3">
                                        @if($agendaDia['pagos_pendientes']->count() > 0)
                                            <div class="mb-3">
                                                <span class="badge badge-lg w-100" style="background: linear-gradient(135deg, #E1B240 0%, #79481D 100%); color: white; font-size: 0.8rem; padding: 10px;">
                                                    <i class="bi bi-clock me-1"></i>{{ $agendaDia['pagos_pendientes']->count() }} pendientes
                                                </span>
                                            </div>
                                        @endif
                                        @if($agendaDia['pagos_hechos']->count() > 0)
                                            <div class="mb-3">
                                                <span class="badge bg-success badge-lg w-100" style="font-size: 0.8rem; padding: 10px;">
                                                    <i class="bi bi-check-circle me-1"></i>{{ $agendaDia['pagos_hechos']->count() }} completados
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            {{-- Columna derecha: Lista de pagos --}}
                            <div class="col-md-9">
                                @if($agendaDia['pagos_pendientes']->count() > 0 || $agendaDia['pagos_hechos']->count() > 0)
                                    <div class="row g-3">
                                        {{-- Pagos pendientes --}}
                                        @foreach($agendaDia['pagos_pendientes'] as $pago)
                                            <div class="col-md-6 col-lg-3">
                                                <a href="{{ route('contratos.show', $pago->contrato->id) }}" class="text-decoration-none pago-link">
                                                    <div class="pago-item p-3 bg-white rounded border-start border-4 h-100" 
                                                         style="border-left-color: #E1B240 !important;"
                                                         title="PENDIENTE - Cliente: {{ $pago->contrato->cliente->nombre ?? 'Sin cliente' }} - Contrato #{{ $pago->contrato->id ?? 'N/A' }} - Click para ver detalles">
                                                        <div class="fw-bold text-truncate mb-2">
                                                            <i class="bi bi-person-fill me-2" style="color: #E1B240;"></i>
                                                            {{ $pago->contrato->cliente->nombre ?? 'Sin cliente' }}
                                                        </div>
                                                        <div class="fw-bold h5 mb-2" style="color: #E1B240;">
                                                            <i class="bi bi-currency-dollar"></i>{{ number_format($pago->monto, 0) }}
                                                        </div>
                                                        @if($pago->numero_cuota)
                                                            <div class="text-muted small">
                                                                <i class="bi bi-list-ol me-1"></i>Cuota #{{ $pago->numero_cuota }}
                                                            </div>
                                                        @endif
                                                        <div class="text-muted small">
                                                            <i class="bi bi-file-text me-1"></i>Contrato #{{ $pago->contrato->id }}
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        @endforeach
                                        
                                        {{-- Pagos hechos --}}
                                        @foreach($agendaDia['pagos_hechos'] as $pago)
                                            <div class="col-md-6 col-lg-3">
                                                <a href="{{ route('contratos.show', $pago->contrato->id) }}" class="text-decoration-none pago-link">
                                                    <div class="pago-item p-3 bg-white rounded border-start border-4 border-success h-100"
                                                         title="COMPLETADO - Cliente: {{ $pago->contrato->cliente->nombre ?? 'Sin cliente' }} - Contrato #{{ $pago->contrato->id ?? 'N/A' }} - Click para ver detalles">
                                                        <div class="fw-bold text-truncate mb-2">
                                                            <i class="bi bi-person-check-fill me-2 text-success"></i>
                                                            {{ $pago->contrato->cliente->nombre ?? 'Sin cliente' }}
                                                        </div>
                                                        <div class="text-success fw-bold h5 mb-2">
                                                            <i class="bi bi-check-circle me-2"></i>${{ number_format($pago->monto, 0) }}
                                                        </div>
                                                        @if($pago->numero_cuota)
                                                            <div class="text-muted small">
                                                                <i class="bi bi-list-ol me-1"></i>Cuota #{{ $pago->numero_cuota }}
                                                            </div>
                                                        @endif
                                                        <div class="text-muted small">
                                                            <i class="bi bi-file-text me-1"></i>Contrato #{{ $pago->contrato->id }}
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center text-muted py-5">
                                        <i class="bi bi-calendar-x display-1" style="color: #E1B240;"></i>
                                        <div class="mt-3">
                                            <h4 class="fw-bold">Sin pagos programados</h4>
                                            <p class="mb-0">No hay pagos programados para este día.</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        
    </div>
    @endif
    
    {{-- Sección para Empleados - Solo para usuarios con rol empleado --}}
    @if(Auth::user()->role !== 'admin' && ($empleadoContratos->count() > 0 || $empleadoAgenda->count() > 0 || $empleadoPagosVencidos->count() > 0))
    <div class="mb-5">        
        <div class="row g-4">
            {{-- Columna 1: Contratos Asignados --}}
            <div class="col-lg-4">
                <div class="card bg-transparent border-0 h-100">
                    <div class="card-header bg-transparent border-0 pb-2">
                        <h5 class="fw-bold mb-0" style="color: #79481D;">
                            <i class="bi bi-file-text me-2"></i>Mis Contratos Asignados
                        </h5>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted"><span id="contadorContratos">{{ $empleadoContratos->count() }}</span> contratos activos</small>
                        </div>
                        <div class="mt-2">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-search text-muted"></i>
                                </span>
                                <input type="text" id="buscadorContratos" class="form-control border-start-0" 
                                       placeholder="Buscar por cliente, contrato o domicilio..."
                                       style="box-shadow: none;">
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($empleadoContratos->count() > 0)
                            <div id="listaContratos" class="list-group list-group-flush">
                                @foreach($empleadoContratos as $contrato)
                                    <a href="{{ route('contratos.show', $contrato->id) }}" 
                                       class="bg-transparent list-group-item list-group-item-action border-0 border-bottom border-muted px-0 py-3 contrato-item" 
                                       style="border-bottom-width: 1px !important;"
                                       data-cliente="{{ strtolower(($contrato->cliente->nombre ?? '') . ' ' . ($contrato->cliente->apellido ?? '')) }}"
                                       data-contrato-id="{{ $contrato->id }}"
                                       data-domicilio="{{ strtolower($contrato->cliente->domicilio_completo ?? '') }}">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <div class="fw-bold text-truncate" style="color: #79481D;">
                                                    <i class="bi bi-box me-1"></i>
                                                    {{ $contrato->paquete->nombre ?? 'Sin paquete' }}#{{ $contrato->id ?? 'Sin ID' }}
                                                </div>
                                                <div class="small text-muted mb-1">
                                                    <i class="bi bi-person-fill me-1"></i>
                                                    {{ $contrato->cliente->nombre ?? 'Sin cliente' }} {{ $contrato->cliente->apellido ?? '' }}
                                                </div>
                                                <div class="small text-muted mb-1">
                                                    <i class="bi bi-geo-alt me-1"></i>
                                                    {{ Str::limit($contrato->cliente->colonia ?? 'Sin domicilio', 40) }}, {{ Str::limit($contrato->cliente->municipio ?? '', 40) }}
                                                </div>
                                                <div class="small text-muted">
                                                    <i class="bi bi-calendar me-1"></i>
                                                    @php
                                                        $proximoPago = $contrato->pagos()
                                                            ->where('estado', 'pendiente')
                                                            ->orderBy('fecha_pago', 'asc')
                                                            ->first();
                                                    @endphp
                                                    Próximo pago: 
                                                    @if($proximoPago)
                                                        {{ \Carbon\Carbon::parse($proximoPago->fecha_pago)->translatedFormat('d \d\e F \d\e Y') }}
                                                    @else
                                                        Sin cuotas proximas
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-success small">
                                                    @if($proximoPago)
                                                        ${{ number_format($proximoPago->monto, 0) }}
                                                    @else
                                                        NA
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                            <div id="sinResultados" class="text-center text-muted py-4" style="display: none;">
                                <i class="bi bi-search fs-1" style="color: #E1B240;"></i>
                                <div class="mt-3">
                                    <h6 class="fw-bold">No se encontraron contratos</h6>
                                    <p class="mb-0">Intenta con otros términos de búsqueda.</p>
                                </div>
                            </div>
                            
                            <div class="pt-2 mt-2 d-flex justify-content-center ">
                                <small class="text-muted" id="infoPaginacion">
                                    Página <span id="paginaActual">1</span> de <span id="totalPaginas">1</span>
                                </small>
                            </div>
                            {{-- Controles de paginación --}}
                            <div id="controlesPaginacion" class="d-flex justify-content-between align-items-center p-0">
                                <div class="btn-group" role="group">
                                    <button id="btnAnterior" class="btn btn-outline-secondary btn-sm" onclick="cambiarPagina(-1)" disabled>
                                        <i class="bi bi-chevron-left"></i> Anterior
                                    </button>
                                    <button id="btnSiguiente" class="btn btn-outline-secondary btn-sm" onclick="cambiarPagina(1)">
                                        Siguiente <i class="bi bi-chevron-right"></i>
                                    </button>
                                </div>
                                <div class="d-flex align-items-center">
                                    <small class="text-muted me-2">Mostrar:</small>
                                    <select id="itemsPorPagina" class="form-select form-select-sm" style="width: auto;" onchange="cambiarItemsPorPagina()">
                                        <option value="5">5</option>
                                        <option value="10" selected>10</option>
                                        <option value="15">15</option>
                                        <option value="20">20</option>
                                    </select>
                                </div>
                            </div>
                        @else
                            <div class="text-center text-muted py-5">
                                <i class="bi bi-briefcase fs-1" style="color: #E1B240;"></i>
                                <div class="mt-3">
                                    <h6 class="fw-bold">No tienes contratos asignados</h6>
                                    <p class="mb-0">Los contratos aparecerán aquí cuando te sean asignados.</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
            {{-- Columna 2: Agenda Personal de 7 días --}}
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 pb-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="fw-bold mb-0" style="color: #79481D;">
                                    <i class="bi bi-calendar-week me-2"></i>Mi Agenda
                                </h5>
                                <div class="mt-1">
                                    <span class="badge" id="rangoFechas" style="background: rgba(121, 72, 29, 0.1); color: #79481D; font-size: 0.7rem;">
                                        <i class="bi bi-calendar-range me-1"></i>
                                        @php
                                            $fechaInicio = $empleadoAgenda->first()['fecha'] ?? now();
                                            $fechaFin = $empleadoAgenda->last()['fecha'] ?? now()->addDays(6);
                                        @endphp
                                        {{ $fechaInicio->format('d M Y') }} - {{ $fechaFin->format('d M Y') }}
                                    </span>
                                </div>
                            </div>
                            <div class="btn-group" role="group">
                                <button class="btn agenda-nav-btn" onclick="cambiarSemana(-1)" title="Semana anterior" style="background: none; color: #79481D; width: 35px; height: 35px;">
                                    <i class="bi bi-chevron-left"></i>
                                </button>
                                <button class="btn agenda-nav-btn" onclick="cambiarSemana(0)" title="Esta semana" style="background: none; color: #79481D; width: 35px; height: 35px;">
                                    <i class="bi bi-house-fill"></i>
                                </button>
                                <button class="btn agenda-nav-btn" onclick="cambiarSemana(1)" title="Semana siguiente" style="background: none; color: #79481D; width: 35px; height: 35px;">
                                    <i class="bi bi-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-2">
                        @if($empleadoAgenda->count() > 0)
                            @foreach($empleadoAgenda as $dia)
                                <div class=" rounded p-3 mb-2 {{ $dia['fecha']->isToday() ? 'bg-light border-primary' : '' }}">
                                    {{-- Cabecera del día --}}
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div>
                                            <div class="fw-bold small text-uppercase text-muted">
                                                @if($dia['fecha']->isToday())
                                                    <i class="bi bi-circle-fill text-primary me-1" style="font-size: .5rem;"></i>
                                                @endif
                                                {{ $dia['dia_nombre'] }}
                                            </div>
                                            <div class="h6 mb-0 fw-bold" style="color: #79481D;">
                                                {{ $dia['dia_numero'] }} {{ $dia['mes'] }}
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            @if($dia['pagos_pendientes']->count() > 0)
                                                <span class="badge badge-sm me-1" style="background: linear-gradient(135deg, #E1B240 0%, #79481D 100%); color: white; font-size: 0.6rem;">
                                                    {{ $dia['pagos_pendientes']->count() }} pendientes
                                                </span>
                                            @endif
                                            @if($dia['pagos_hechos']->count() > 0)
                                                <span class="badge bg-success badge-sm" style="font-size: 0.6rem;">
                                                    {{ $dia['pagos_hechos']->count() }} hechos
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    {{-- Lista de pagos --}}
                                    @if($dia['pagos_pendientes']->count() > 0 || $dia['pagos_hechos']->count() > 0)
                                        <div class="small">
                                            <div class="row g-2">
                                                {{-- Pagos pendientes --}}
                                                @foreach($dia['pagos_pendientes'] as $pago)
                                                    <div class="col-6 col-md-3">
                                                        <a href="{{ route('contratos.show', $pago->contrato->id) }}" class="text-decoration-none">
                                                            <div class="py-2 px-2 bg-white rounded border-start border-3 mb-2 h-100"
                                                                 style="border-left-color: #E1B240 !important;">
                                                                <div class="fw-bold text-truncate" style="color: #79481D;">
                                                                    <i class="bi bi-person-fill me-1" style="color: #E1B240;"></i>
                                                                    {{ Str::limit($pago->contrato->cliente->nombre ?? 'Sin cliente', 15) }}
                                                                </div>
                                                                <div class="fw-bold" style="color: #E1B240;">
                                                                    ${{ number_format($pago->monto, 0) }}
                                                                </div>
                                                            </div>
                                                        </a>
                                                    </div>
                                                @endforeach

                                                {{-- Pagos hechos --}}
                                                @foreach($dia['pagos_hechos'] as $pago)
                                                    <div class="col-6 col-md-3">
                                                        <a href="{{ route('contratos.show', $pago->contrato->id) }}" class="text-decoration-none">
                                                            <div class="py-2 px-2 bg-white rounded border-start border-3 border-success mb-2 h-100">
                                                                <div class="fw-bold text-truncate text-success">
                                                                    <i class="bi bi-person-check-fill me-1"></i>
                                                                    {{ Str::limit($pago->contrato->cliente->nombre ?? 'Sin cliente', 15) }}
                                                                </div>
                                                                <div class="text-success fw-bold">
                                                                    <i class="bi bi-check-circle me-1"></i>${{ number_format($pago->monto, 0) }}
                                                                </div>
                                                            </div>
                                                        </a>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @else
                                        <div class="text-center text-muted small">
                                            <i class="bi bi-calendar-x" style="color: #E1B240;"></i>
                                            Sin pagos programados
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        @else
                            <div class="text-center text-muted py-5">
                                <i class="bi bi-calendar-x fs-1" style="color: #E1B240;"></i>
                                <div class="mt-3">
                                    <h6 class="fw-bold">No hay pagos programados</h6>
                                    <p class="mb-0">Los pagos de tus contratos aparecerán aquí.</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
    
    {{-- Sección de Pagos Vencidos - Solo para empleados --}}
    @if(Auth::user()->role !== 'admin' && $empleadoPagosVencidos->count() > 0)
    <div class="mb-5">
        <div class="d-flex justify-content-start mt-3 mb-4">
            <div class="text-start">
                <h4 class="fw-bold mb-0 me-3" style="color: #dc3545;">
                    <i class="bi bi-exclamation-triangle me-2"></i>Pagos Vencidos
                </h4>
                <small class="text-muted">
                    {{ $empleadoPagosVencidos->count() }} pagos que exceden el período de tolerancia
                </small>
                <div class="mt-1">
                    <span class="badge bg-danger">
                        <i class="bi bi-clock-history me-1"></i>Requieren atención inmediata
                    </span>
                    @if(toleranciaPagos() > 0)
                        <span class="badge bg-warning text-dark ms-1">
                            <i class="bi bi-info-circle me-1"></i>Tolerancia: {{ toleranciaPagos() }} días
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-start border-4 border-danger" style="border-top: 0; border-right: 0; border-bottom: 0;">
            <div class="card-header bg-light border-0 pb-2">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-danger">
                        <i class="bi bi-calendar-x me-2"></i>Pagos Atrasados
                    </h5>
                    <span class="badge bg-danger">{{ $empleadoPagosVencidos->count() }} pendientes</span>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach($empleadoPagosVencidos as $pago)
                        <div class="col-lg-4 col-md-6">
                            <a href="{{ route('contratos.show', $pago->contrato->id) }}" class="text-decoration-none">
                                <div class="card border-0 bg-light h-100 pago-item" style="border-left: 4px solid #dc3545 !important;">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div class="flex-grow-1">
                                                <div class="fw-bold text-truncate text-danger mb-1">
                                                    <i class="bi bi-person-fill me-1"></i>
                                                    {{ $pago->contrato->cliente->nombre ?? 'Sin cliente' }} {{ $pago->contrato->cliente->apellido ?? '' }}
                                                </div>
                                                <div class="small text-muted mb-1">
                                                    <i class="bi bi-box me-1"></i>
                                                    Contrato #{{ $pago->contrato->id }} - {{ $pago->contrato->paquete->nombre ?? 'Sin paquete' }}
                                                </div>
                                                @if($pago->numero_cuota)
                                                    <div class="small text-muted mb-2">
                                                        <i class="bi bi-list-ol me-1"></i>Cuota #{{ $pago->numero_cuota }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="fw-bold h5 mb-0 text-danger">
                                                <i class="bi bi-currency-dollar"></i>{{ number_format($pago->monto, 0) }}
                                            </div>
                                            <div class="text-end">
                                                <div class="small text-danger fw-bold">
                                                    <i class="bi bi-calendar-x me-1"></i>
                                                    Venció: {{ \Carbon\Carbon::parse($pago->fecha_pago)->format('d/m/Y') }}
                                                </div>
                                                <div class="small text-muted">
                                                    @php
                                                        $diasRetraso = diasDeRetraso($pago->fecha_pago, $pago->estado);
                                                    @endphp
                                                    @if($diasRetraso > 0)
                                                        <span class="text-danger fw-bold">
                                                            <i class="bi bi-exclamation-triangle me-1"></i>
                                                            {{ $diasRetraso }} día{{ $diasRetraso != 1 ? 's' : '' }} de retraso
                                                        </span>
                                                    @else
                                                        <span class="text-warning">
                                                            <i class="bi bi-clock me-1"></i>
                                                            En período de tolerancia
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
                
                @if($empleadoPagosVencidos->count() > 6)
                    <div class="text-center mt-3">
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            Mostrando los pagos vencidos más recientes. 
                            <a href="#" class="text-primary">Ver todos los pagos vencidos</a>
                        </small>
                    </div>
                @endif
            </div>
        </div>
    </div>
    @endif
    
    {{-- Script para tooltips y calendario --}}
    <script>
        // Variables globales para el calendario (solo si es admin)
        @if(Auth::user()->role === 'admin')
        let fechaActual = new Date(@json($agendaDia['fecha']->format('Y')), @json($agendaDia['fecha']->format('n') - 1), @json($agendaDia['fecha']->format('j')));
        let mesCalendario = new Date(fechaActual);
        
        // Datos de pagos por fecha (se pasarían desde el controlador)
        const diasConPagos = @json($diasConPagos ?? []);
        @endif
        
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM cargado');
            
            // Inicializar tooltips de Bootstrap si están disponibles
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
            if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            }
            
            // Generar calendario solo si existe (para administradores)
            if (document.getElementById('miniCalendario')) {
                console.log('Generando calendario...');
                generarCalendario();
            }
            
            // Inicializar buscador de contratos solo si existe (para empleados)
            if (document.getElementById('buscadorContratos')) {
                console.log('Inicializando buscador...');
                inicializarBuscadorContratos();
            }
        });

        function generarCalendario() {
            const calendario = document.getElementById('miniCalendario');
            if (!calendario) {
                console.log('No se encontró el elemento miniCalendario');
                return;
            }
            
            const meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
                          'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
            const diasSemana = ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'];
            
            const primerDiaMes = new Date(mesCalendario.getFullYear(), mesCalendario.getMonth(), 1);
            const ultimoDiaMes = new Date(mesCalendario.getFullYear(), mesCalendario.getMonth() + 1, 0);
            const primerDiaSemana = primerDiaMes.getDay();
            
            let html = `
                <div class="calendario-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <button class="calendario-nav" onclick="cambiarMes(-1)">‹</button>
                        <span>${meses[mesCalendario.getMonth()]} de ${mesCalendario.getFullYear()}</span>
                        <button class="calendario-nav" onclick="cambiarMes(1)">›</button>
                    </div>
                </div>
                <div class="calendario-dias-semana">
                    ${diasSemana.map(dia => `<div>${dia}</div>`).join('')}
                </div>
                <div class="calendario-dias">
            `;
            
            // Días del mes anterior
            for (let i = primerDiaSemana; i > 0; i--) {
                const dia = new Date(mesCalendario.getFullYear(), mesCalendario.getMonth(), 1 - i);
                html += `<button class="calendario-dia otro-mes" onclick="seleccionarFecha('${formatearFecha(dia)}')">${dia.getDate()}</button>`;
            }
            
            // Días del mes actual
            for (let dia = 1; dia <= ultimoDiaMes.getDate(); dia++) {
                const fechaDia = new Date(mesCalendario.getFullYear(), mesCalendario.getMonth(), dia);
                const fechaFormateada = formatearFecha(fechaDia);
                const esHoy = esMismaFecha(fechaDia, new Date());
                const esSeleccionado = esMismaFecha(fechaDia, fechaActual);
                const tienePagos = diasConPagos.includes(fechaFormateada);
                
                let clases = ['calendario-dia'];
                if (esHoy) clases.push('hoy');
                if (esSeleccionado) clases.push('seleccionado');
                if (tienePagos) clases.push('con-pagos');
                
                html += `<button class="${clases.join(' ')}" onclick="seleccionarFecha('${fechaFormateada}')">${dia}</button>`;
            }
            
            // Días del mes siguiente para completar la grilla
            const diasRestantes = 42 - (primerDiaSemana + ultimoDiaMes.getDate());
            for (let dia = 1; dia <= diasRestantes; dia++) {
                const fechaDia = new Date(mesCalendario.getFullYear(), mesCalendario.getMonth() + 1, dia);
                html += `<button class="calendario-dia otro-mes" onclick="seleccionarFecha('${formatearFecha(fechaDia)}')">${dia}</button>`;
            }
            
            html += '</div>';
            calendario.innerHTML = html;
        }

        function cambiarMes(direccion) {
            if (typeof mesCalendario !== 'undefined') {
                mesCalendario.setMonth(mesCalendario.getMonth() + direccion);
                generarCalendario();
            }
        }

        function seleccionarFecha(fecha) {
            // Calcular la diferencia en días desde hoy
            const hoy = new Date();
            const fechaSeleccionada = new Date(fecha);
            const diferenciaTiempo = fechaSeleccionada.getTime() - hoy.getTime();
            const diferenciaDias = Math.ceil(diferenciaTiempo / (1000 * 3600 * 24));
            
            // Redirigir a la URL con el parámetro day
            window.location.href = `{{ route('home') }}?day=${diferenciaDias}`;
        }

        function formatearFecha(fecha) {
            return fecha.toISOString().split('T')[0];
        }

        function esMismaFecha(fecha1, fecha2) {
            return fecha1.toDateString() === fecha2.toDateString();
        }

        // Variables globales para paginación
        let paginaActualContratos = 1;
        let itemsPorPaginaContratos = 10;
        let contratosFiltrados = [];

        // Funcionalidad del buscador de contratos con paginación
        function inicializarBuscadorContratos() {
            const buscador = document.getElementById('buscadorContratos');
            if (!buscador) {
                console.log('No se encontró el elemento buscadorContratos');
                return;
            }

            const contratos = document.querySelectorAll('.contrato-item');
            const contador = document.getElementById('contadorContratos');
            const sinResultados = document.getElementById('sinResultados');
            const totalContratos = contratos.length;
            
            // Inicializar array de contratos
            contratosFiltrados = Array.from(contratos);
            
            console.log('Buscador inicializado. Contratos encontrados:', totalContratos);

            // Inicializar paginación
            actualizarPaginacion();

            buscador.addEventListener('input', function(e) {
                const termino = e.target.value.toLowerCase().trim();
                contratosFiltrados = [];

                console.log('Buscando:', termino);

                contratos.forEach((contrato, index) => {
                    const cliente = (contrato.getAttribute('data-cliente') || '').toLowerCase();
                    const contratoId = (contrato.getAttribute('data-contrato-id') || '').toLowerCase();
                    const domicilio = (contrato.getAttribute('data-domicilio') || '').toLowerCase();

                    // Buscar en: nombre del cliente, ID del contrato, y domicilio
                    const coincideCliente = cliente.includes(termino);
                    const coincideId = contratoId.includes(termino);
                    const coincideDomicilio = domicilio.includes(termino);
                    const coincide = coincideCliente || coincideId || coincideDomicilio;

                    if (termino === '' || coincide) {
                        contratosFiltrados.push(contrato);
                    }
                });

                // Reiniciar paginación cuando se busca
                paginaActualContratos = 1;
                actualizarPaginacion();

                console.log('Contratos filtrados:', contratosFiltrados.length);
            });

            // Limpiar búsqueda con Escape
            buscador.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    this.value = '';
                    this.dispatchEvent(new Event('input'));
                }
            });
        }

        function actualizarPaginacion() {
            const totalContratos = contratosFiltrados.length;
            const totalPaginas = Math.ceil(totalContratos / itemsPorPaginaContratos);
            const inicio = (paginaActualContratos - 1) * itemsPorPaginaContratos;
            const fin = inicio + itemsPorPaginaContratos;

            // Actualizar contadores
            const contador = document.getElementById('contadorContratos');
            const paginaActualSpan = document.getElementById('paginaActual');
            const totalPaginasSpan = document.getElementById('totalPaginas');
            const sinResultados = document.getElementById('sinResultados');
            const controlesPaginacion = document.getElementById('controlesPaginacion');

            if (contador) contador.textContent = totalContratos;
            if (paginaActualSpan) paginaActualSpan.textContent = totalPaginas > 0 ? paginaActualContratos : 0;
            if (totalPaginasSpan) totalPaginasSpan.textContent = totalPaginas;

            // Mostrar/ocultar todos los contratos
            document.querySelectorAll('.contrato-item').forEach(contrato => {
                contrato.style.display = 'none';
            });

            // Mostrar solo los contratos de la página actual
            contratosFiltrados.slice(inicio, fin).forEach(contrato => {
                contrato.style.display = 'block';
            });

            // Mostrar/ocultar mensaje sin resultados
            if (sinResultados) {
                sinResultados.style.display = totalContratos === 0 ? 'block' : 'none';
            }

            // Mostrar/ocultar controles de paginación
            if (controlesPaginacion) {
                controlesPaginacion.style.display = totalContratos > itemsPorPaginaContratos ? 'flex' : 'none';
            }

            // Actualizar botones
            const btnAnterior = document.getElementById('btnAnterior');
            const btnSiguiente = document.getElementById('btnSiguiente');

            if (btnAnterior) {
                btnAnterior.disabled = paginaActualContratos <= 1;
            }

            if (btnSiguiente) {
                btnSiguiente.disabled = paginaActualContratos >= totalPaginas;
            }
        }

        function cambiarPagina(direccion) {
            const totalPaginas = Math.ceil(contratosFiltrados.length / itemsPorPaginaContratos);
            
            if (direccion === -1 && paginaActualContratos > 1) {
                paginaActualContratos--;
            } else if (direccion === 1 && paginaActualContratos < totalPaginas) {
                paginaActualContratos++;
            }

            actualizarPaginacion();
        }

        function cambiarItemsPorPagina() {
            const select = document.getElementById('itemsPorPagina');
            if (select) {
                itemsPorPaginaContratos = parseInt(select.value);
                paginaActualContratos = 1; // Reiniciar a la primera página
                actualizarPaginacion();
            }
        }

        // Funcionalidad de navegación por semanas
        let semanaOffset = 0;
        const userRole = @json(Auth::user()->role);

        // Función auxiliar para actualizar el rango de fechas
        function actualizarRangoFechas(agendaDatos = null) {
            if (userRole === 'admin') return;
            
            // Buscar el elemento con mayor precisión
            let rangoFechas = document.getElementById('rangoFechas');
            
            // Si no lo encuentra, intentar múltiples formas
            if (!rangoFechas) {
                rangoFechas = document.querySelector('#rangoFechas');
                if (!rangoFechas) {
                    rangoFechas = document.querySelector('span[id="rangoFechas"]');
                }
                if (!rangoFechas) {
                    rangoFechas = document.querySelector('.badge[id="rangoFechas"]');
                }
            }
            
            if (!rangoFechas) {
                console.log('No se encontró elemento rangoFechas después de múltiples intentos');
                return;
            }
            
            let fechaInicio, fechaFin;
            
            if (agendaDatos && agendaDatos.length > 0) {
                // Usar fechas de los datos del backend
                fechaInicio = new Date(agendaDatos[0].fecha);
                fechaFin = new Date(agendaDatos[agendaDatos.length - 1].fecha);
                console.log('Usando fechas del backend:', agendaDatos[0].fecha, 'a', agendaDatos[agendaDatos.length - 1].fecha);
            } else {
                // Calcular rango basado en semanaOffset
                const hoy = new Date();
                fechaInicio = new Date(hoy);
                fechaInicio.setDate(hoy.getDate() + (semanaOffset * 7));
                fechaFin = new Date(fechaInicio);
                fechaFin.setDate(fechaInicio.getDate() + 6);
                console.log('Calculando fechas con offset:', semanaOffset);
            }
            
            const fechaInicioStr = fechaInicio.toLocaleDateString('es-ES', { day: 'numeric', month: 'short', year: 'numeric' });
            const fechaFinStr = fechaFin.toLocaleDateString('es-ES', { day: 'numeric', month: 'short', year: 'numeric' });
            
            const nuevoRango = `<i class="bi bi-calendar-range me-1"></i>${fechaInicioStr} - ${fechaFinStr}`;
            
            // Verificar que el elemento sigue existiendo antes de actualizarlo
            if (rangoFechas && rangoFechas.parentNode) {
                rangoFechas.innerHTML = nuevoRango;
                console.log('Rango actualizado exitosamente:', nuevoRango);
            } else {
                console.log('El elemento rangoFechas ya no existe en el DOM');
            }
        }

        function cambiarSemana(direccion) {
            // Solo ejecutar si el usuario no es admin
            if (userRole === 'admin') {
                console.log('Usuario es admin, navegación por semanas no disponible');
                return;
            }
            
            if (direccion === 0) {
                // Volver a la semana actual
                semanaOffset = 0;
            } else {
                // Cambiar semana (-1 = anterior, 1 = siguiente)
                semanaOffset += direccion;
            }
            
            actualizarAgendaSemana();
        }

        function actualizarAgendaSemana() {
            // Solo ejecutar si el usuario no es admin
            if (userRole === 'admin') {
                console.log('Usuario es admin, no actualizar agenda de semana');
                return;
            }
            
            // Actualizar texto descriptivo
            const textoSemana = document.getElementById('textoSemana');
            
            if (textoSemana) {
                if (semanaOffset === 0) {
                    textoSemana.textContent = 'Pagos programados para los próximos 7 días';
                } else if (semanaOffset > 0) {
                    textoSemana.textContent = `Pagos programados para la semana ${semanaOffset + 1}`;
                } else {
                    textoSemana.textContent = `Pagos programados para ${Math.abs(semanaOffset)} semana${Math.abs(semanaOffset) > 1 ? 's' : ''} atrás`;
                }
            }
            
            // Actualizar el rango de fechas inmediatamente (antes de la petición AJAX)
            actualizarRangoFechas();

            // Hacer petición AJAX para obtener los datos de la nueva semana
            const url = `{{ route('home') }}?week=${semanaOffset}&ajax=1`;
            
            fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                console.log('Datos recibidos del servidor:', data);
                if (data.success) {
                    actualizarVistaAgenda(data.empleadoAgenda);
                } else {
                    console.error('Error al cargar la agenda:', data.message);
                }
            })
            .catch(error => {
                console.error('Error en la petición:', error);
                // Fallback: recargar la página con el parámetro week
                window.location.href = `{{ route('home') }}?week=${semanaOffset}`;
            });
        }

        function actualizarVistaAgenda(agendaDatos) {
            // Solo ejecutar si el usuario no es admin (la sección de empleados existe)
            if (userRole === 'admin') {
                console.log('Usuario es admin, no hay sección de empleados para actualizar');
                return;
            }
            
            const agendaContainer = document.querySelector('.col-lg-8 .card-body');
            
            // Buscar el elemento rangoFechas de múltiples formas
            let rangoFechas = document.getElementById('rangoFechas');
            if (!rangoFechas) {
                rangoFechas = document.querySelector('#rangoFechas');
            }
            if (!rangoFechas) {
                rangoFechas = document.querySelector('span[id="rangoFechas"]');
            }
            
            console.log('Actualizando vista agenda con:', agendaDatos);
            console.log('Elemento rangoFechas encontrado:', rangoFechas);
            
            // Actualizar el rango de fechas usando la función auxiliar
            actualizarRangoFechas(agendaDatos);
            
            if (!agendaContainer) {
                console.error('No se encontró el contenedor de agenda');
                return;
            }
            
            if (!agendaDatos || agendaDatos.length === 0) {
                // Mostrar mensaje de sin pagos
                agendaContainer.innerHTML = `
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-calendar-x fs-1" style="color: #E1B240;"></i>
                        <div class="mt-3">
                            <h6 class="fw-bold">No hay pagos programados</h6>
                            <p class="mb-0">No hay pagos programados para esta semana.</p>
                        </div>
                    </div>
                `;
                return;
            }

            let html = '';
            agendaDatos.forEach(dia => {
                const esHoy = dia.fecha === new Date().toISOString().split('T')[0];
                
                html += `
                    <div class="rounded p-3 mb-2 ${esHoy ? 'bg-light border-primary' : ''}">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <div class="fw-bold small text-uppercase text-muted">
                                    ${esHoy ? '<i class="bi bi-circle-fill text-primary me-1" style="font-size: .5rem;"></i>' : ''}
                                    ${dia.dia_nombre}
                                </div>
                                <div class="h6 mb-0 fw-bold" style="color: #79481D;">
                                    ${dia.dia_numero} ${dia.mes}
                                </div>
                            </div>
                            <div class="text-end">
                `;
                
                if (dia.pagos_pendientes_count > 0) {
                    html += `
                        <span class="badge badge-sm me-1" style="background: linear-gradient(135deg, #E1B240 0%, #79481D 100%); color: white; font-size: 0.6rem;">
                            ${dia.pagos_pendientes_count} pendientes
                        </span>
                    `;
                }
                
                if (dia.pagos_hechos_count > 0) {
                    html += `
                        <span class="badge bg-success badge-sm" style="font-size: 0.6rem;">
                            ${dia.pagos_hechos_count} hechos
                        </span>
                    `;
                }
                
                html += `
                            </div>
                        </div>
                `;
                
                if (dia.pagos && dia.pagos.length > 0) {
                    html += `
                        <div class="small">
                            <div class="row g-2">
                    `;
                    
                    dia.pagos.forEach(pago => {
                        const esPendiente = pago.estado === 'pendiente';
                        html += `
                            <div class="col-6 col-md-3">
                                <a href="/contratos/${pago.contrato_id}" class="text-decoration-none">
                                    <div class="py-2 px-2 bg-white rounded border-start border-3 mb-2 h-100" 
                                         style="border-left-color: ${esPendiente ? '#E1B240' : '#28a745'} !important;">
                                        <div class="fw-bold text-truncate ${esPendiente ? '' : 'text-success'}">
                                            <i class="bi bi-person${esPendiente ? '' : '-check'}-fill me-1" style="color: ${esPendiente ? '#E1B240' : '#28a745'};"></i>
                                            ${pago.cliente_nombre.length > 15 ? pago.cliente_nombre.substring(0, 15) + '...' : pago.cliente_nombre}
                                        </div>
                                        <div class="fw-bold" style="color: ${esPendiente ? '#E1B240' : '#28a745'};">
                                            ${esPendiente ? '' : '<i class="bi bi-check-circle me-1"></i>'}$${new Intl.NumberFormat().format(pago.monto)}
                                        </div>
                                    </div>
                                </a>
                            </div>
                        `;
                    });
                    
                    html += `
                            </div>
                        </div>
                    `;
                } else {
                    html += `
                        <div class="text-center text-muted small">
                            <i class="bi bi-calendar-x" style="color: #E1B240;"></i>
                            Sin pagos programados
                        </div>
                    `;
                }
                
                html += `</div>`;
            });

            agendaContainer.innerHTML = html;
            
            // Asegurar que el rango de fechas se actualice después de modificar el contenido
            setTimeout(() => {
                actualizarRangoFechas(agendaDatos);
            }, 50);
        }


    </script>
    
</div>
@endsection
