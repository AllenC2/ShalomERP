@extends('layouts.app')

@section('template_title')
    {{ $contrato->name ?? __('Show') . " " . __('Contrato') }}
@endsection

@section('content')
    <section class="content container-fluid">
    
     <div class="container py-2">
        <!-- Header del contrato -->
        <div class="contract-header">
            <a class="btn btn-outline-primary btn-sm mb-2" href="{{ route('contratos.index') }}">
                <i class="bi bi-arrow-left me-1"></i> {{ __('Regresar') }}
            </a>
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="display-6 fw-bold">Contrato {{$contrato->paquete->nombre}}#{{$contrato->id}}</h1>
                </div>
                <div class="col-md-4 text-md-end">
                    <span class="badge status-badge 
                        {{ 
                            $contrato->estado == 'Activo' ? 'bg-primary' : 
                            ($contrato->estado == 'Finalizado' ? 'bg-success' : 
                            ($contrato->estado == 'Cancelado' ? 'bg-danger' : 'bg-secondary')) 
                        }}">
                        {{ strtoupper($contrato->estado) }}
                    </span>
                    <p class="text-muted mb-0 mt-2">
                        Creado: {{ $contrato->created_at->format('d') }} de {{ ucfirst($contrato->created_at->locale('es')->monthName) }} de {{ $contrato->created_at->format('Y') }}
                    </p>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Columna izquierda - Detalles del contrato -->
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0"><i class="bi bi-file-earmark-text me-2"></i>Detalles del Contrato</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Cliente:</strong> <a href="#">{{$contrato->cliente->nombre}} {{$contrato->cliente->apellido}}</a></p>
                                <p><strong>Responsable:</strong> {{$contrato->empleado->nombre}} {{$contrato->empleado->apellido}}</p>
                                @php
                                    $fechaInicio = \Carbon\Carbon::parse($contrato->fecha_inicio);
                                @endphp
                                <p><strong>Fecha Inicio:</strong> {{ $fechaInicio->format('d') }} de {{ ucfirst($fechaInicio->locale('es')->monthName) }} de {{ $fechaInicio->format('Y') }}</p>
                                @if($contrato->fecha_fin)
                                    <p><strong>Fecha Fin:</strong> {{$contrato->fecha_fin}}</p>
                                @else
                                    <p><strong>Fecha Fin:</strong> <span class="text-muted">Sin definir</span></p>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <p><strong>Monto Total:</strong> ${{ number_format($contrato->monto_total, 2) }}</p>
                                @if($contrato->monto_inicial)
                                    <p><strong>Monto Inicial:</strong> ${{ number_format($contrato->monto_inicial, 2) }}</p>
                                @else
                                    <p><strong>Monto Inicial:</strong> <span class="text-muted">Sin monto inicial</span></p>
                                @endif
                                <p><strong>Forma de Pago:</strong> 
                                    {{$contrato->plazo_tipo}} 
                                    ({{$contrato->plazo_cantidad}} pagos de ${{ number_format(($contrato->monto_total - ($contrato->monto_inicial ?? 0)) / max($contrato->plazo_cantidad, 1), 2) }})
                                </p>
                                @if($contrato->documento)
                                    <p><strong>Documento:</strong> <a href="{{ asset('storage/' . $contrato->documento) }}" target="_blank" class="text-decoration-none">{{ $contrato->documento }}</a></p>
                                @else
                                    <p><strong>Documento:</strong> <span class="text-muted">No disponible</span></p>
                                @endif
                            </div>
                        </div>
                        
                        <hr>
                        
                        <h6 class="fw-bold mb-3">Paquete contratado: {{$contrato->paquete->nombre}}</h6>
                        <p>{{$contrato->paquete->descripcion}}</p>
                        
                        <h6 class="fw-bold mb-3">Observaciones</h6>
                        <p>{{$contrato->observaciones}}</p>
                    </div>
                </div>

                <!-- Historial de pagos -->
                <div class="card">
                    <div class="card-header bg-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0"><i class="bi bi-credit-card me-2"></i>Historial de Pagos</h5>

                        </div>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            @forelse($pagos_contrato->sortBy('fecha_pago') as $pago)
                                <div class="timeline-item mb-4">
                                    <a href="{{ route('pagos.edit', $pago->id) }}" class="text-decoration-none">
                                        @php
                                            $fechaPago = \Carbon\Carbon::parse($pago->fecha_pago)->locale('es');
                                            $diaSemana = ucfirst($fechaPago->dayName);
                                            $mesNombre = ucfirst($fechaPago->monthName);
                                            $fechaFormateada = $fechaPago->format('d') . ' de ' . $mesNombre . ' de ' . $fechaPago->format('Y');
                                            $esRetrasado = $pago->estado == 'Pendiente' && $fechaPago->isPast();
                                        @endphp
                                        <div class="card payment-card border-start border-3 
                                            {{ $pago->estado == 'Hecho' ? 'border-success' : ($esRetrasado ? 'border-danger' : ($pago->estado == 'Pendiente' ? 'border-warning' : 'border-light')) }}">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between">
                                                    <div>
                                                        <h6 class="fw-bold">Folio #{{ $pago->id }} - {{ $pago->observaciones ?? 'Extraordinario' }}</h6>
                                                        <p class="mb-1 text-muted">
                                                            {{ $diaSemana }}, {{ $fechaFormateada }} - 
                                                            {{ $esRetrasado ? 'Retrasado' : ucfirst($pago->estado) }}
                                                        </p>
                                                    </div>
                                                    <div class="text-end">
                                                        <span class="badge bg-{{ $pago->estado == 'Hecho' ? 'success' : ($esRetrasado ? 'danger' : ($pago->estado == 'Pendiente' ? 'warning text-dark' : 'secondary')) }}">
                                                            {{ $esRetrasado ? 'Retrasado' : ucfirst($pago->estado) }}
                                                        </span>
                                                        <p class="fw-bold mb-0 mt-1">${{ number_format($pago->monto, 2) }}</p>
                                                    </div>
                                                </div>
                                                <p class="mb-0 mt-2">
                                                    <small>
                                                        Método: {{ $pago->metodo_pago ?? 'N/A' }}
                                                    </small>
                                                </p>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            @empty
                                <div class="timeline-item">
                                    <div class="card payment-card border-start border-3 border-light">
                                        <div class="card-body">
                                            <p class="mb-0 text-muted">No hay pagos registrados para este contrato.</p>
                                        </div>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Columna derecha - Resumen y acciones -->
            <div class="col-lg-4">
                <!-- Resumen financiero -->
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0"><i class="bi bi-graph-up me-2"></i>Resumen Financiero</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <p class="mb-1">Monto Total del Contrato</p>
                            <h4 class="fw-bold">${{ number_format($contrato->monto_total, 2) }}</h4>
                        </div>
                        
                        <div class="progress mb-3" style="height: 10px;">
                            @php
                                $pagado = $pagos_contrato->where('estado', 'Hecho')->sum('monto');
                                $porcentajePagado = $contrato->monto_total > 0 
                                    ? min(100, ($pagado / $contrato->monto_total) * 100)
                                    : 0;
                            @endphp
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ number_format($porcentajePagado, 2) }}%;" aria-valuenow="{{ number_format($porcentajePagado, 2) }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        
                        <div class="row">
                            <div class="col-6">
                                <p class="mb-1 text-success">Pagado</p>
                                <h5 class="fw-bold">${{ number_format($pagado, 2) }}</h5>
                            </div>
                            <div class="col-6 text-end">
                                <p class="mb-1 text-warning">Pendiente</p>
                                <h5 class="fw-bold">${{ number_format($contrato->monto_total - $pagado, 2) }}</h5>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="mb-2">
                            <p class="mb-1"><small>Próximo pago:</small></p>
                            @php
                                $proximoPago = $pagos_contrato->where('estado', 'Pendiente')
                                    ->where('fecha_pago', '>=', now())
                                    ->sortBy('fecha_pago')
                                    ->first();
                            @endphp
                            @if($proximoPago)
                                <p class="fw-bold mb-0">
                                    ${{ number_format($proximoPago->monto, 2) }} 
                                    <span class="text-muted">
                                        ({{ \Carbon\Carbon::parse($proximoPago->fecha_pago)->format('d') }} de {{ ucfirst(\Carbon\Carbon::parse($proximoPago->fecha_pago)->locale('es')->monthName) }} de {{ \Carbon\Carbon::parse($proximoPago->fecha_pago)->format('Y') }})
                                    </span>
                                </p>
                            @else
                                <p class="fw-bold mb-0 text-muted">No hay pagos pendientes próximos.</p>
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- Acciones rápidas -->
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0"><i class="bi bi-lightning me-2"></i>Acciones Rápidas</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <div class="d-flex gap-2">
                                <a href="{{ route('comisiones.create', $contrato) }}" class="btn btn-outline-success btn-sm flex-fill">
                                    <i class="bi bi-cash-coin me-2"></i>Agregar comisión
                                </a>
                                <a href="{{ route('contratos.comisiones', $contrato->id) }}" class="btn btn-outline-success btn-sm flex-fill">
                                    <i class="bi bi-list-ul me-2"></i>Revisar comisiones
                                </a>
                            </div>
                            <a href="mailto:{{ $contrato->cliente->email }}" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-envelope me-2"></i>Enviar recordatorio
                            </a>
                            <a href="{{ route('contratos.edit', $contrato->id) }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-pencil me-2"></i>Editar contrato
                            </a>
                        
                            <button class="btn btn-outline-danger btn-sm" onclick="event.preventDefault(); document.getElementById('cancelar-contrato-form').submit();">
                                <i class="bi bi-trash me-2"></i>Cancelar contrato
                            </button>
                            <form id="cancelar-contrato-form" action="{{ route('contratos.cancel', $contrato->id) }}" method="POST" style="display: none;">
                                @csrf
                                @method('PATCH')
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Documentos relacionados -->
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0"><i class="bi bi-folder me-2"></i>Documento del Contrato</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            @if($contrato->documento)
                                <a href="{{ asset('storage/' . $contrato->documento) }}" target="_blank" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-filetype-pdf me-2 text-danger"></i> {{ $contrato->documento }}</span>
                                    <small class="text-muted">{{ $contrato->created_at->format('d/m/Y') }}</small>
                                </a>
                            @else
                                <span class="list-group-item text-muted">No hay documentos disponibles.</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    </section>
@endsection
