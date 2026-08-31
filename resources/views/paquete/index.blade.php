@extends('layouts.app')

@section('template_title')
    Paquetes
@endsection

@section('content')
    <div class="container py-4" style="max-width: 1600px;">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- Header moderno -->
                <div class="page-header bg-white  px-4">
                    <div class="header-content ">
                        <div class="header-icon">
                            <i class="bi bi-box-seam"></i>
                        </div>
                        <div class="header-text">
                            <h1 class="page-title">{{ __('Paquetes') }}</h1>
                            <p class="page-subtitle">Gestione y consulte la información de los paquetes</p>
                        </div>
                    </div>
                    <div class="header-actions">
                        <button type="button" class="btn me-2" data-bs-toggle="modal" data-bs-target="#prioridadesModal" style="background: white; color: #6c757d; border: 2px solid #e2e8f0;">
                            <i class="bi bi-sort-down me-2"></i>{{ __('Configurar Prioridades') }}
                        </button>
                        <a href="{{ route('paquetes.create') }}" class="btn">
                            <i class="bi bi-plus-lg me-2"></i>{{ __('Crear Nuevo') }}
                        </a>
                    </div>
                </div>
                <div class="">

                    @if ($message = Session::get('success'))
                    <div class="alert alert-success mb-4">
                        <p class="mb-0">{{ $message }}</p>
                    </div>
                    @endif

                    @if($paquetes->count() > 0)
                        <div class="row g-4">
                            @foreach ($paquetes as $paquete)
                                <div class="col-12 col-lg-6 col-xl-4">
                                    <div class="card shadow-sm h-100 border-0" style="cursor: pointer;" onclick="window.location='{{ route('paquetes.show', $paquete->id) }}';">
                                        <div class="card-body p-0">
                                            <div class="row g-0 h-100">
                                                <!-- Columna izquierda: Información del paquete -->
                                                <div class="col-7 d-flex flex-column p-3">
                                                    <div class="flex-grow-1">
                                                        <h6 class="card-title fw-bold mb-2" style="font-size: 1.6em;"><strong><span style="font-weight:900;">{{ $paquete->nombre }}</span></strong></h6>
                                                        
                                                        <!-- Información adicional: contratos -->
                                                        <div class="mb-3">
                                                            <div class="d-flex align-items-center">
                                                                <i class="bi bi-file-earmark-text me-2" style="color: #79481D; font-size: 0.9em;"></i>
                                                                <span class="text-muted" style="font-size: 0.8em;">
                                                                    <strong>{{ $paquete->contratos_count }}</strong> 
                                                                    {{ $paquete->contratos_count == 1 ? 'contrato' : 'contratos' }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                        
                                                        <p class="card-text text-muted mb-3" style="font-size: 0.9em; line-height: 1.4;">
                                                            {{ $paquete->descripcion }}
                                                        </p>
                                                        <div class="mb-3">
                                                            <span class="badge px-3 py-2" style="font-size: 0.85em; border: 2px solid; border-color: #6c757d; border-radius: 12px; color: #6c757d;">
                                                                <i class="bi bi-currency-dollar me-1"></i>{{ number_format($paquete->precio, 2) }} MXN
                                                            </span>
                                                        </div>

                                                    </div>
                                                    
                                                    <!-- Botones de acción -->
                                                    <div class="d-flex gap-1 mt-auto">
                                                        <a href="{{ route('paquetes.edit', $paquete->id) }}" class="btn btn-outline-success btn-sm flex-fill" title="Editar">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                        <form action="{{ route('paquetes.destroy', $paquete->id) }}" method="POST" class="flex-fill">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-outline-danger btn-sm w-100" title="Eliminar"
                                                                onclick="event.preventDefault(); if(confirm('¿Seguro que deseas eliminar este paquete?')) this.closest('form').submit();">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>

                                                <!-- Columna derecha: Comisiones -->
                                                <div class="col-5 d-flex flex-column" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-left: 1px solid #e9ecef;">
                                                    <div class="p-3 flex-grow-1">
                                                        <div class="d-flex align-items-center mb-3">
                                                            <i class="bi bi-percent me-2" style="color: #79481D;" class=""></i>
                                                            <h6 class="mb-0 fw-semibold text-dark" style="font-size: 0.85em;">Comisiones</h6>
                                                        </div>
                                                        
                                                        @if($paquete->porcentajes && $paquete->porcentajes->count() > 0)
                                                            <div class="porcentajes-container">
                                                                @foreach($paquete->porcentajes->sortBy('orden') as $porcentaje)
                                                                    <div class="porcentaje-item mb-2 p-2 rounded shadow-sm" 
                                                                        style="background: white; border-left: 3px solid {{ $porcentaje->tipo_porcentaje == 'vendedor' ? '#2196f3' : ($porcentaje->tipo_porcentaje == 'supervisor' ? '#ff9800' : 'linear-gradient(135deg, #E1B240 0%, #79481D 100%)') }};">
                                                                        <div class="d-flex justify-content-between align-items-center">
                                                                            <div class="d-flex align-items-center">
                                                                                <i class="bi bi-{{ $porcentaje->tipo_porcentaje == 'vendedor' ? 'person-badge' : ($porcentaje->tipo_porcentaje == 'supervisor' ? 'person-gear' : 'people') }} me-1" 
                                                                                style="color: {{ $porcentaje->tipo_porcentaje == 'vendedor' ? '#2196f3' : ($porcentaje->tipo_porcentaje == 'supervisor' ? '#ff9800' : 'linear-gradient(135deg, #E1B240 0%, #79481D 100%)') }}; font-size: 0.8em;"></i>
                                                                                <span class="fw-medium" style="font-size: 0.75em; color: #495057;">
                                                                                    {{ ucfirst($porcentaje->tipo_porcentaje) }}
                                                                                </span>
                                                                            </div>
                                                                            <span class="badge rounded-pill" 
                                                                                style="background: {{ $porcentaje->tipo_porcentaje == 'vendedor' ? '#2196f3' : ($porcentaje->tipo_porcentaje == 'supervisor' ? '#ff9800' : 'linear-gradient(135deg, #E1B240 0%, #79481D 100%)') }}; font-size: 0.7em;">
                                                                                @if($porcentaje->modo_comision === 'monto')
                                                                                    ${{ number_format($porcentaje->monto_fijo, 0) }}
                                                                                @else
                                                                                    {{ $porcentaje->cantidad_porcentaje }}%
                                                                                @endif
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @else
                                                            <div class="text-center py-3">
                                                                <i class="bi bi-exclamation-triangle text-warning mb-2" style="font-size: 1.5em;"></i>
                                                                <div class="text-warning" style="font-size: 0.75em;">
                                                                    <strong>Sin comisiones</strong><br>
                                                                    <small class="text-muted">No configurado</small>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                    <div class="text-center py-5">
                        <div class="mb-4">
                            <i class="bi bi-box-seam" style="font-size: 4rem; color: #6c757d;"></i>
                        </div>
                        <h4 class="text-muted mb-3">No hay paquetes disponibles</h4>
                        <p class="text-muted mb-4">Aún no se han creado paquetes en el sistema.</p>
                        <a href="{{ route('paquetes.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-lg me-2"></i>Crear mi primer paquete
                        </a>
                    </div>
                    @endif

                    <div class="mt-4">
                        {!! $paquetes->withQueryString()->links() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal Prioridades Globales -->
    <div class="modal fade" id="prioridadesModal" tabindex="-1" aria-labelledby="prioridadesModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold" id="prioridadesModalLabel"><i class="bi bi-sort-down-alt me-2 text-primary"></i>Prioridad de Comisiones en General</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-4">
                        Arrastra los tipos de comisión para establecer el orden de prioridad para <strong>todos los paquetes</strong> al mismo tiempo. Los paquetes que no tengan configurado alguno de estos porcentajes simplemente lo ignorarán y seguirán con el siguiente en la lista.
                    </p>
                    <form id="formPrioridadesGlobales" action="{{ route('paquetes.actualizar_prioridades') }}" method="POST">
                        @csrf
                        <div id="lista-prioridades-globales" class="list-group">
                            @if(isset($tiposPorcentajeUnicos) && $tiposPorcentajeUnicos->count() > 0)
                                @foreach($tiposPorcentajeUnicos as $tipo)
                                    <div class="list-group-item d-flex align-items-center bg-light border mb-2 rounded shadow-sm" style="cursor: grab;">
                                        <i class="bi bi-grip-vertical text-muted me-3 fs-5 drag-handle"></i>
                                        <div class="fw-semibold text-dark">{{ strtoupper($tipo) }}</div>
                                        <input type="hidden" name="tipos_porcentaje[]" value="{{ $tipo }}">
                                    </div>
                                @endforeach
                            @else
                                <div class="text-center py-4">
                                    <span class="text-muted">No hay tipos de comisión registrados en los paquetes actuales.</span>
                                </div>
                            @endif
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    @if(isset($tiposPorcentajeUnicos) && $tiposPorcentajeUnicos->count() > 0)
                        <button type="submit" form="formPrioridadesGlobales" class="btn text-white px-4" style="background: linear-gradient(135deg, #E1B240 0%, #79481D 100%); border: none;">
                            <i class="bi bi-save me-2"></i>Cambiar Prioridades para Todos
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

<style>
    .page-header {
        background: white;
        border-radius: 20px;
        padding: 2rem;
        margin-bottom: 2rem;
        border: 1px solid rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(10px);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .header-content {
        display: flex;
        align-items: center;
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
        color: #79481D;
        border: 2px solid #e2e8f0;
        padding: 0.75rem 1.5rem;
        border-radius: 12px;
        font-weight: 600;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    .header-actions .btn:hover {
        background: linear-gradient(135deg, #E1B240 0%, #79481D 100%);
        color: white;
        border-color: white;
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.25);
    }
</style>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const el = document.getElementById('lista-prioridades-globales');
        if (el && typeof Sortable !== 'undefined') {
            new Sortable(el, {
                animation: 150,
                ghostClass: 'bg-white',
                handle: '.drag-handle',
                onStart: function (evt) {
                    evt.item.style.opacity = '0.7';
                },
                onEnd: function (evt) {
                    evt.item.style.opacity = '1';
                }
            });
        }
    });
</script>
@endpush
@endsection
