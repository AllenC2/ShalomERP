@extends('layouts.app')

@section('template_title')
    Rutas
@endsection

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>

<div class="container py-4" style="max-width: 1600px;">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="page-header">
                <div class="header-content">
                    <div class="header-icon">
                        <i class="fa-solid fa-route"></i>
                    </div>
                    <div class="header-text">
                        <h1 class="page-title">{{ __('Rutas de Cobranza') }}</h1>
                        <p class="page-subtitle">Planificación y seguimiento de rutas de cobro</p>
                    </div>
                </div>
                @if(auth()->user()->role === 'admin')
                <div class="header-actions">
                    <button type="button" class="btn" data-bs-toggle="modal" data-bs-target="#nuevaRutaModal">
                        <i class="bi bi-plus-lg me-1"></i>
                        {{ __('Nueva Ruta') }}
                    </button>
                </div>
                @endif
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="">
                <!-- Filtros -->
                <div class="pb-3">
                    <div class="row g-2 align-items-center">
                        @if(auth()->user()->role === 'admin')
                        <div class="col-md-4">
                            <div class="dropdown" id="searchDropdown">
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0">
                                        <i class="bi bi-search"></i>
                                    </span>
                                    <input type="text" id="searchEmpleado" class="bg-white form-control border-start-0" placeholder="Buscar empleado..." autocomplete="off"
                                        value="{{ $empleadoFiltro ? ($empleados->firstWhere('id', $empleadoFiltro)->nombre . ' ' . $empleados->firstWhere('id', $empleadoFiltro)->apellido) : '' }}"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                    <button class="btn btn-outline-secondary border-start-0" type="button" id="clearSearch" title="Limpiar" style="display: {{ $empleadoFiltro ? 'block' : 'none' }};">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </div>
                                <ul class="dropdown-menu w-100 shadow-sm" id="empleadosList" style="max-height: 250px; overflow-y: auto;">
                                    <li><a class="dropdown-item empleado-item" href="#" data-id="">
                                        <i class="bi bi-people me-2"></i>Todos los empleados
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    @foreach($empleados as $emp)
                                        <li><a class="dropdown-item empleado-item {{ $empleadoFiltro == $emp->id ? 'active' : '' }}" href="#" data-id="{{ $emp->id }}">
                                            <i class="bi bi-person me-2"></i>{{ $emp->nombre }} {{ $emp->apellido }}
                                        </a></li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        @else
                        <div class="col-md-4"></div>
                        @endif
                        <div class="col-md-8">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="text-muted small fw-semibold me-1">Estado:</span>
                                @foreach(\App\Models\Ruta::getEstadosValidos() as $key => $label)
                                    @php
                                        $isChecked = in_array($key, $estadosFiltro);
                                        $btnClass = match($key) {
                                            'planeada' => 'btn-outline-info',
                                            'en_curso' => 'btn-outline-warning',
                                            'completada' => 'btn-outline-success',
                                            'detenida' => 'btn-outline-danger',
                                            default => 'btn-outline-secondary',
                                        };
                                        $activeClass = $isChecked ? str_replace('outline-', '', $btnClass) : '';
                                    @endphp
                                    <label class="btn btn-sm {{ $isChecked ? $activeClass : $btnClass }} rounded-pill px-3 py-1" style="cursor: pointer; font-size: 0.8rem; transition: all 0.2s;">
                                        <input type="checkbox" name="estados[]" value="{{ $key }}" class="d-none estado-check" {{ $isChecked ? 'checked' : '' }}>
                                        {{ $label }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                @if ($message = Session::get('success'))
                    <div class="alert alert-success m-4">
                        <p class="mb-0">{{ $message }}</p>
                    </div>
                @endif

                <div class="card-body p-0">
                    @if($rutas->isEmpty())
                        <div class="text-center py-5">
                            <i class="fa-solid fa-route text-muted" style="font-size: 3rem;"></i>
                            <h5 class="mt-3 text-muted">No hay rutas registradas</h5>
                            <p class="text-muted">Crea una nueva ruta para comenzar.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 modern-table">
                                <thead class="modern-header">
                                    <tr>
                                        <th class="ps-4">ID</th>
                                        <th>Nombre</th>
                                        <th>Empleado</th>
                                        <th>Progreso</th>
                                        <th>Estado</th>
                                        <th class="text-center pe-4">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($rutas as $ruta)
                                        <tr class="modern-row clickable-row" data-href="{{ route('rutas.show', $ruta->id) }}">
                                            <td class="ps-4">
                                                <span class="badge bg-light text-dark fw-normal">#{{ $ruta->id }}</span>
                                            </td>
                                            <td>
                                                <div class="fw-semibold text-dark">{{ $ruta->nombre ?: ('Ruta #' . $ruta->id) }}</div>
                                                @if($ruta->plantilla)
                                                    <small class="text-muted">{{ $ruta->plantilla->etiquetaFrecuencia() }}</small>
                                                @endif
                                                <div><small class="text-muted">{{ $ruta->fecha instanceof \Carbon\Carbon ? $ruta->fecha->format('d/m/Y') : \Carbon\Carbon::parse($ruta->fecha)->format('d/m/Y') }}</small></div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-circle me-3 d-flex align-items-center justify-content-center" style="min-width: 40px; min-height: 40px; width: 40px; height: 40px; background: linear-gradient(135deg, #E1B240 0%, #79481D 100%); border-radius: 50%;">
                                                        {{ strtoupper(substr($ruta->empleado->nombre ?? '?', 0, 1) . substr($ruta->empleado->apellido ?? '?', 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <div class="fw-semibold text-dark">{{ $ruta->empleado->nombre ?? '' }} {{ $ruta->empleado->apellido ?? '' }}</div>
                                                        <small class="text-muted">{{ $ruta->empleado->id ?? '' }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @php
                                                    $total = $ruta->paradas->count();
                                                    $completadas = $ruta->paradas->where('estado', 'visitada')->count();
                                                    $porcentaje = $total > 0 ? round(($completadas / $total) * 100) : 0;
                                                @endphp
                                                <div class="d-flex align-items-center">
                                                    <div class="progress flex-grow-1 me-2" style="height: 8px; border-radius: 4px;">
                                                        <div class="progress-bar" role="progressbar" style="width: {{ $porcentaje }}%; background: linear-gradient(135deg, #E1B240 0%, #79481D 100%); border-radius: 4px;"></div>
                                                    </div>
                                                    <small class="text-muted">{{ $completadas }}/{{ $total }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge {{ $ruta->estado_badge }}">{{ $ruta->estado_label }}</span>
                                            </td>
                                            <td class="text-center pe-4" onclick="event.stopPropagation();">
                                                <div class="btn-group" role="group">
                                                    @if(auth()->user()->role === 'admin')
                                                    @if($ruta->estado !== \App\Models\Ruta::ESTADO_COMPLETADA)
                                                    <button type="button" class="btn btn-outline-success btn-sm action-btn btn-editar-ruta" data-ruta-id="{{ $ruta->id }}" title="Editar" onclick="event.stopPropagation(); openEditModal({{ $ruta->id }});">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    @endif
                                                    @if($ruta->estado === \App\Models\Ruta::ESTADO_PLANEADA || $ruta->estado === \App\Models\Ruta::ESTADO_EN_CURSO)
                                                    <button type="button" class="btn btn-outline-warning btn-sm action-btn btn-toggle-detener" data-ruta-id="{{ $ruta->id }}" title="Detener ruta" onclick="event.stopPropagation(); toggleDetenerRuta({{ $ruta->id }}, this);">
                                                        <i class="bi bi-stop-fill"></i>
                                                    </button>
                                                    @elseif($ruta->estado === \App\Models\Ruta::ESTADO_DETENIDA)
                                                    <button type="button" class="btn btn-outline-info btn-sm action-btn btn-toggle-detener" data-ruta-id="{{ $ruta->id }}" title="Reactivar ruta" onclick="event.stopPropagation(); toggleDetenerRuta({{ $ruta->id }}, this);">
                                                        <i class="bi bi-play-fill"></i>
                                                    </button>
                                                    @endif
                                                    <button type="button"
                                                        class="btn btn-outline-danger btn-sm action-btn"
                                                        title="Eliminar ruta"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#eliminarRutaModal"
                                                        data-ruta-id="{{ $ruta->id }}"
                                                        data-ruta-nombre="{{ $ruta->nombre ?: ('Ruta #' . $ruta->id) }}"
                                                        onclick="event.stopPropagation();">
                                                        Eliminar
                                                    </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-center mt-4">
                            {!! $rutas->withQueryString()->links('vendor.pagination.custom') !!}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@if(auth()->user()->role === 'admin')
<!-- Modal Eliminar Ruta -->
<div class="modal fade" id="eliminarRutaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-trash text-danger me-2"></i>Eliminar ruta
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-3">
                <p class="mb-2">¿Seguro que deseas eliminar <strong id="eliminarRutaNombre">esta ruta</strong>?</p>
                <p class="text-muted small mb-0">El borrado es lógico: la ruta quedará registrada en la base de datos, pero ya no se mostrará en el listado ni para el cobrador.</p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <form id="eliminarRutaForm" method="POST" action="#">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i>Sí, eliminar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nueva Ruta (multi-paso) -->
<div class="modal fade" id="nuevaRutaModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <!-- Header -->
            <div class="modal-header border-bottom py-3 px-4" style="flex-direction: row; justify-content: space-between !important;">
                <div class="d-flex align-items-center gap-3">
                    <div style="background: linear-gradient(135deg, #E1B240 0%, #79481D 100%); color: white; width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; box-shadow: 0 6px 12px rgba(225, 178, 64, 0.3);">
                        <i class="fa-solid fa-route"></i>
                    </div>
                    <h5 class="modal-title fw-bold mb-0" style="font-size: 1.3rem; color: #1f2937;">Nueva Ruta</h5>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center gap-2" id="stepIndicator">
                        <span class="step-item active" data-step="1">
                            <span class="step-num active">1</span>
                            <span class="step-label fw-semibold small" style="color: #79481D;">Datos</span>
                        </span>
                        <span class="text-muted">&rsaquo;</span>
                        <span class="step-item" data-step="2">
                            <span class="step-num">2</span>
                            <span class="step-label text-muted small">Contratos</span>
                        </span>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="location.reload()"></button>
                </div>
            </div>

            <form method="POST" action="{{ route('rutas.store') }}" id="nuevaRutaForm">
                @csrf
                <input type="hidden" name="empleado_id" id="formEmpleadoId">
                <input type="hidden" name="nombre" id="formNombre">
                <input type="hidden" name="fecha" id="formFecha">
                <input type="hidden" name="frecuencia" id="formFrecuencia" value="diaria">
                <input type="hidden" name="notas" id="formNotas">
                <input type="hidden" name="punto_casa" id="formPuntoCasa" value="inicio">

                <div class="modal-body p-0">
                    <!-- Paso 1 -->
                    <div id="step1" class="p-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-person me-1"></i> Empleado
                            </label>
                            <div class="empleado-combobox position-relative" id="empleadoCombobox">
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                                    <input type="text" id="selectEmpleadoSearch" class="form-control" placeholder="Buscar empleado por nombre o ID..." autocomplete="off" role="combobox" aria-expanded="false" aria-autocomplete="list">
                                </div>
                                <ul class="empleado-combobox-list list-unstyled mb-0 shadow-sm" id="selectEmpleadoList" hidden>
                                    @foreach($empleados as $empleado)
                                        <li>
                                            <button type="button" class="empleado-combobox-item" data-id="{{ $empleado->id }}" data-label="{{ $empleado->nombre }} {{ $empleado->apellido }} ({{ $empleado->id }})">
                                                <i class="bi bi-person me-2"></i>{{ $empleado->nombre }} {{ $empleado->apellido }}
                                                <small class="text-muted">({{ $empleado->id }})</small>
                                            </button>
                                        </li>
                                    @endforeach
                                    <li class="empleado-combobox-empty px-3 py-2 text-muted small" style="display: none;">Sin resultados</li>
                                </ul>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="inputNombreRuta">
                                <i class="bi bi-tag me-1"></i> Nombre de la ruta
                            </label>
                            <input type="text" id="inputNombreRuta" class="form-control form-control-lg" placeholder="Ej. Zona Norte lunes" maxlength="120">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-arrow-repeat me-1"></i> Frecuencia
                            </label>
                            <div class="seg-tabs" id="frecuenciaTabs" role="tablist">
                                <button type="button" class="seg-tab active" data-value="diaria" role="tab" aria-selected="true">Diaria</button>
                                <button type="button" class="seg-tab" data-value="semanal" role="tab" aria-selected="false">Semanal</button>
                                <button type="button" class="seg-tab" data-value="mensual" role="tab" aria-selected="false">Mensual</button>
                            </div>
                            <small class="text-muted d-block mt-2" id="frecuenciaHint"></small>
                        </div>

                        <div class="mb-3 rango-calendario">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-calendar-event me-1"></i> Primera fecha
                            </label>
                            <div class="mb-2 text-end">
                                <span class="fw-semibold small" id="inputFechaRangoLabel" style="color: #79481D;"></span>
                            </div>
                            <input type="text" id="inputFechaRango" class="d-none" tabindex="-1" aria-hidden="true">
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-lg px-4 text-white fw-bold" style="background: linear-gradient(135deg, #E1B240 0%, #79481D 100%); border: none;" id="btnSiguiente" disabled>
                                Siguiente <i class="bi bi-arrow-right ms-1"></i>
                            </button>
                        </div>

                        <div id="step1Error" class="alert alert-danger d-none mt-3 py-2"></div>
                    </div>

                    <!-- Paso 1.2: Geocodificando -->
                    <div id="stepGeocode" class="d-none p-4 text-center">
                        <div class="py-5">
                            <div class="mb-4">
                                <div style="background: linear-gradient(135deg, #E1B240 0%, #79481D 100%); color: white; width: 56px; height: 56px; border-radius: 14px; display: inline-flex; align-items: center; justify-content: center; font-size: 1.4rem; box-shadow: 0 6px 16px rgba(225, 178, 64, 0.3);">
                                    <i class="bi bi-geo-alt-fill" id="geocodeStepIcon"></i>
                                </div>
                            </div>
                            <h5 class="fw-bold mb-2" style="color: #1c1c1e;" id="geocodeStepTitle">Geocodificando domicilios</h5>
                            <p class="text-muted small mb-0" id="geocodeStepSubtitle">Buscando coordenadas en el mapa...</p>

                            <div class="progress mt-4 mx-auto" style="max-width: 400px; height: 8px; border-radius: 4px; background: rgba(120,120,128,0.12);">
                                <div class="progress-bar" role="progressbar" id="geocodeStepBar" style="width: 0%; background: linear-gradient(135deg, #E1B240 0%, #79481D 100%); border-radius: 4px; transition: width 0.3s ease;"></div>
                            </div>
                            <div class="d-flex justify-content-between mt-2 mx-auto" style="max-width: 400px;">
                                <small class="text-muted" id="geocodeStepCount">0 / 0</small>
                                <small class="text-muted" id="geocodeStepStatus"></small>
                            </div>

                            <div class="mt-3 mx-auto" style="max-width: 400px;" id="geocodeStepErrors"></div>
                        </div>
                    </div>

                    <!-- Paso 2 -->
                    <div id="step2" class="d-none">
                        <div class="row g-0 modal-paso-mapa">
                            <!-- Mapa -->
                            <div class="col-6 position-relative modal-paso-mapa-col">
                                <div id="modalMap" style="height: 100%; width: 100%; background: #e9ecef;"></div>
                            </div>

                            <!-- Lista de contratos -->
                            <div class="col-6 d-flex flex-column modal-paso-mapa-col">
                                <div class="px-3 py-2 border-bottom d-flex justify-content-between align-items-center bg-white flex-shrink-0">
                                    <div>
                                        <span class="fw-bold small" style="color: #79481D;" id="step2Empleado"></span>
                                        <span class="text-muted small ms-2" id="step2Fecha"></span>
                                    </div>
                                </div>

                                <div class="px-3 py-2 border-bottom bg-white flex-shrink-0">
                                    <label class="form-label small fw-semibold mb-1">
                                        <i class="bi bi-search me-1"></i> Agregar contrato (folio o titular)
                                    </label>
                                    <div class="empleado-combobox position-relative" id="contratoFolioCombobox">
                                        <input type="text" id="buscarContratoFolio" class="form-control form-control-sm" placeholder="Folio o nombre del titular..." autocomplete="off">
                                        <ul class="empleado-combobox-list list-unstyled mb-0 shadow-sm" id="buscarContratoLista" hidden>
                                            <li class="px-3 py-2 text-muted small" id="buscarContratoEmpty">Sin resultados</li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="px-3 py-2 border-bottom bg-white flex-shrink-0" id="puntoCasaWrap">
                                    <label class="form-label small fw-semibold mb-1">
                                        <i class="bi bi-house-door me-1"></i> Domicilio del empleado
                                    </label>
                                    <div class="seg-tabs" id="puntoCasaTabs" role="tablist">
                                        <button type="button" class="seg-tab active" data-value="inicio" role="tab" aria-selected="true">Inicial</button>
                                        <button type="button" class="seg-tab" data-value="final" role="tab" aria-selected="false">Final</button>
                                        <button type="button" class="seg-tab" data-value="ambos" role="tab" aria-selected="false">Ambos</button>
                                    </div>
                                </div>

                                <!-- Geocode alert -->
                                <div id="geocodeAlert" class="alert alert-warning d-none mx-3 mt-2 mb-0 py-2 d-flex align-items-center justify-content-between rounded-3 flex-shrink-0">
                                    <span><i class="bi bi-geo-alt-fill me-1"></i><span id="geocodeCount"></span> sin coordenadas</span>
                                    <button type="button" class="btn btn-sm btn-dark py-0" onclick="geocodificarPendientes()"><i class="bi bi-globe me-1"></i>Geocodificar</button>
                                </div>
                                <div id="geocodeProgress" class="alert alert-info d-none mx-3 mt-2 mb-0 py-2 rounded-3 flex-shrink-0">
                                    <div class="d-flex align-items-center">
                                        <div class="spinner-border spinner-border-sm me-2"></div>
                                        <span id="geocodeProgressText">Geocodificando...</span>
                                    </div>
                                    <div class="progress mt-1" style="height: 4px;"><div class="progress-bar bg-success" id="geocodeBar" style="width: 0%"></div></div>
                                </div>

                                <div id="listaContratos" class="list-group list-group-flush modal-paso-mapa-lista"></div>

                                <div class="px-3 py-2 border-top bg-white flex-shrink-0">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="text-muted small" id="selectedCount">0 seleccionado(s)</span>
                                            <textarea id="inputNotas" class="form-control form-control-sm mt-1" rows="1" placeholder="Notas de la ruta..." style="display: none;"></textarea>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-sm btn-outline-secondary" id="btnVolver">
                                                <i class="bi bi-arrow-left me-1"></i> Volver
                                            </button>
                                            <button type="submit" class="btn btn-sm px-4 text-white fw-bold" style="background: linear-gradient(135deg, #E1B240 0%, #79481D 100%); border: none;">
                                                <i class="bi bi-check-lg me-1"></i> Crear Ruta
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Modal Editar Ruta (multi-paso) -->
@if(auth()->user()->role === 'admin')
<div class="modal fade" id="editarRutaModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <!-- Header -->
            <div class="modal-header border-bottom py-3 px-4" style="flex-direction: row; justify-content: space-between !important;">
                <div class="d-flex align-items-center gap-3">
                    <div style="background: linear-gradient(135deg, #E1B240 0%, #79481D 100%); color: white; width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; box-shadow: 0 6px 12px rgba(225, 178, 64, 0.3);">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </div>
                    <h5 class="modal-title fw-bold mb-0" style="font-size: 1.3rem; color: #1f2937;">Editar Ruta <span id="editRutaId"></span></h5>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center gap-2" id="editStepIndicator">
                        <span class="step-item active" data-step="1">
                            <span class="step-num active">1</span>
                            <span class="step-label fw-semibold small" style="color: #79481D;">Datos</span>
                        </span>
                        <span class="text-muted">&rsaquo;</span>
                        <span class="step-item" data-step="2">
                            <span class="step-num">2</span>
                            <span class="step-label text-muted small">Paradas</span>
                        </span>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="location.reload()"></button>
                </div>
            </div>

            <form id="editarRutaForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="ruta_id" id="editRutaIdInput">

                <div class="modal-body p-0">
                    <!-- Paso 1: Datos generales -->
                    <div id="editStep1" class="p-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-person me-1"></i> Empleado
                            </label>
                            <div class="empleado-combobox position-relative" id="editEmpleadoCombobox">
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                                    <input type="text" id="editEmpleadoSearch" class="form-control" placeholder="Buscar empleado por nombre o ID..." autocomplete="off" role="combobox" aria-expanded="false" aria-autocomplete="list">
                                </div>
                                <ul class="empleado-combobox-list list-unstyled mb-0 shadow-sm" id="editEmpleadoList" hidden>
                                    @foreach($empleados as $empleado)
                                        <li>
                                            <button type="button" class="empleado-combobox-item" data-id="{{ $empleado->id }}" data-label="{{ $empleado->nombre }} {{ $empleado->apellido }} ({{ $empleado->id }})">
                                                <i class="bi bi-person me-2"></i>{{ $empleado->nombre }} {{ $empleado->apellido }}
                                                <small class="text-muted">({{ $empleado->id }})</small>
                                            </button>
                                        </li>
                                    @endforeach
                                    <li class="empleado-combobox-empty px-3 py-2 text-muted small" style="display: none;">Sin resultados</li>
                                </ul>
                            </div>
                            <small class="text-muted">Un empleado puede tener varias rutas; esta ruta queda con un solo empleado.</small>
                        </div>

                        <div class="mb-3 rango-calendario">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-calendar-event me-1"></i> Fecha de esta ejecución
                            </label>
                            <div class="mb-2 text-end">
                                <span class="fw-semibold small" id="editInputFechaRangoLabel" style="color: #79481D;"></span>
                            </div>
                            <input type="text" id="editInputFechaRango" class="d-none" tabindex="-1" aria-hidden="true">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="editInputNombre">
                                <i class="bi bi-tag me-1"></i> Nombre
                            </label>
                            <input type="text" id="editInputNombre" class="form-control form-control-lg" maxlength="120">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-sticky me-1"></i> Notas
                            </label>
                            <input type="text" id="editInputNotas" class="form-control form-control-lg" placeholder="Observaciones...">
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-lg px-4 text-white fw-bold" style="background: linear-gradient(135deg, #E1B240 0%, #79481D 100%); border: none;" id="editBtnSiguiente">
                                Siguiente <i class="bi bi-arrow-right ms-1"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Paso 2: Mapa + paradas -->
                    <div id="editStep2" class="d-none">
                        <div class="row g-0 modal-paso-mapa">
                            <!-- Mapa -->
                            <div class="col-6 position-relative modal-paso-mapa-col">
                                <div id="editModalMap" style="height: 100%; width: 100%; background: #e9ecef;"></div>
                            </div>

                            <!-- Lista de paradas -->
                            <div class="col-6 d-flex flex-column modal-paso-mapa-col">
                                <div class="px-3 py-2 border-bottom d-flex justify-content-between align-items-center bg-white flex-shrink-0">
                                    <div>
                                        <span class="fw-bold small" style="color: #79481D;" id="editStep2Empleado"></span>
                                        <span class="text-muted small ms-2" id="editStep2Fecha"></span>
                                    </div>
                                </div>

                                <div class="px-3 py-2 border-bottom bg-white flex-shrink-0" id="editPuntoCasaWrap">
                                    <label class="form-label small fw-semibold mb-1">
                                        <i class="bi bi-house-door me-1"></i> Domicilio del empleado
                                    </label>
                                    <div class="seg-tabs" id="editPuntoCasaTabs" role="tablist">
                                        <button type="button" class="seg-tab active" data-value="inicio" role="tab" aria-selected="true">Inicial</button>
                                        <button type="button" class="seg-tab" data-value="final" role="tab" aria-selected="false">Final</button>
                                        <button type="button" class="seg-tab" data-value="ambos" role="tab" aria-selected="false">Ambos</button>
                                    </div>
                                </div>

                                <div id="editListaParadas" class="list-group list-group-flush modal-paso-mapa-lista"></div>

                                <div class="px-3 py-2 border-top bg-white flex-shrink-0">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted small" id="editParadasCount">0 parada(s)</span>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-sm btn-outline-secondary" id="editBtnVolver">
                                                <i class="bi bi-arrow-left me-1"></i> Volver
                                            </button>
                                            <button type="button" class="btn btn-sm px-4 text-white fw-bold" style="background: linear-gradient(135deg, #E1B240 0%, #79481D 100%); border: none;" id="editBtnGuardar">
                                                <i class="bi bi-check-lg me-1"></i> Guardar Cambios
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(document).ready(function() {
    $('.clickable-row').on('click', function(e) {
        if (!$(e.target).closest('td[onclick*="stopPropagation"]').length && !$(e.target).closest('a, button, form').length) {
            window.location.href = $(this).data('href');
        }
    });

    // Filtros de estado por checkboxes
    $('.estado-check').on('change', function() {
        var params = new URLSearchParams(window.location.search);
        var selected = [];
        $('.estado-check:checked').each(function() {
            selected.push($(this).val());
        });
        params.delete('estados[]');
        selected.forEach(function(v) { params.append('estados[]', v); });
        params.delete('page');
        window.location.href = window.location.pathname + '?' + params.toString();
    });

    // Buscador de empleados con dropdown
    var $searchInput = $('#searchEmpleado');
    var $empleadosList = $('#empleadosList');
    var $clearBtn = $('#clearSearch');

    $searchInput.on('focus', function() {
        $empleadosList.addClass('show');
    });

    $searchInput.on('input', function() {
        var query = $(this).val().toLowerCase();
        $empleadosList.find('.empleado-item').each(function() {
            var text = $(this).text().toLowerCase();
            var isDefault = $(this).data('id') === '';
            $(this).closest('li').toggle(isDefault || text.includes(query));
        });
        if (!$empleadosList.hasClass('show')) $empleadosList.addClass('show');
    });

    $searchInput.on('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            var $visible = $empleadosList.find('.empleado-item:visible').first();
            if ($visible.length) $visible.click();
        }
    });

    $(document).on('click', function(e) {
        if (!$(e.target).closest('#searchDropdown').length) {
            $empleadosList.removeClass('show');
        }
    });

    $empleadosList.on('click', '.empleado-item', function(e) {
        e.preventDefault();
        var empId = $(this).data('id');
        var empName = empId ? $(this).text().trim() : '';
        $searchInput.val(empName);
        $empleadosList.removeClass('show');

        var params = new URLSearchParams(window.location.search);
        if (empId) {
            params.set('empleado_id', empId);
            $clearBtn.show();
        } else {
            params.delete('empleado_id');
            $clearBtn.hide();
        }
        params.delete('page');
        window.location.href = window.location.pathname + '?' + params.toString();
    });

    $clearBtn.on('click', function() {
        $searchInput.val('');
        var params = new URLSearchParams(window.location.search);
        params.delete('empleado_id');
        params.delete('page');
        window.location.href = window.location.pathname + '?' + params.toString();
    });

    var modalMap = null;
    var markerLayer = null;
    var routeLine = null;
    var domicilios = [];
    var domMarkers = [];
    var homeMarker = null;
    var empleadoHome = null;

    function distanciaKm(lat1, lng1, lat2, lng2) {
        var r = 6371;
        var dLat = (lat2 - lat1) * Math.PI / 180;
        var dLng = (lng2 - lng1) * Math.PI / 180;
        var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
            Math.sin(dLng / 2) * Math.sin(dLng / 2);
        return r * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    }

    function ordenarPorCercania(items, originLat, originLng) {
        var con = [];
        var sin = [];
        items.forEach(function(item) {
            var lat = parseFloat(item.latitud);
            var lng = parseFloat(item.longitud);
            if (lat && lng) {
                item.latitud = lat;
                item.longitud = lng;
                con.push(item);
            } else {
                sin.push(item);
            }
        });
        if (!originLat || !originLng || !con.length) return items;
        var ordenadas = [];
        var curLat = originLat;
        var curLng = originLng;
        while (con.length) {
            var best = 0;
            var bestD = Infinity;
            for (var i = 0; i < con.length; i++) {
                var d = distanciaKm(curLat, curLng, con[i].latitud, con[i].longitud);
                if (d < bestD) {
                    bestD = d;
                    best = i;
                }
            }
            var next = con.splice(best, 1)[0];
            ordenadas.push(next);
            curLat = next.latitud;
            curLng = next.longitud;
        }
        return ordenadas.concat(sin);
    }

    function etiquetaPuntoCasa(modo) {
        if (modo === 'final') return 'Punto final';
        if (modo === 'ambos') return 'Inicio y fin';
        return 'Punto de inicio';
    }

    function getPuntoCasaModo(tabsSelector) {
        return $(tabsSelector).find('.seg-tab.active').data('value') || 'inicio';
    }

    function setPuntoCasaModo(tabsSelector, modo) {
        var value = modo || 'inicio';
        $(tabsSelector).find('.seg-tab').each(function() {
            var on = $(this).data('value') === value;
            $(this).toggleClass('active', on).attr('aria-selected', on ? 'true' : 'false');
        });
    }

    function aplicarHomeARuta(coords, home, modo) {
        if (!home) return coords;
        var latlng = [home.lat, home.lng];
        if (modo === 'inicio' || modo === 'ambos') coords.unshift(latlng);
        if (modo === 'final' || modo === 'ambos') coords.push(latlng);
        return coords;
    }

    function initModalMap() {
        if (modalMap) {
            modalMap.invalidateSize();
            return;
        }
        modalMap = L.map('modalMap').setView([20.67, -103.36], 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OSM', maxZoom: 19
        }).addTo(modalMap);
        markerLayer = L.layerGroup().addTo(modalMap);
    }

    function clearMapOverlays() {
        if (markerLayer) markerLayer.clearLayers();
        if (routeLine && modalMap) { modalMap.removeLayer(routeLine); routeLine = null; }
        if (homeMarker && modalMap) { modalMap.removeLayer(homeMarker); homeMarker = null; }
        domMarkers = [];
    }

    var contratosAgregados = [];
    var contratoSearchTimer = null;

    function startGeocodificacion(empId) {
        irAPaso2(empId);
    }

    function irAPaso2(empId) {
        var csrf = $('meta[name="csrf-token"]').attr('content');
        $.ajax({
            url: '{{ route("rutas.empleadoDatos") }}',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            data: { empleado_id: empId },
            success: function(response) {
                $('#stepGeocode').addClass('d-none');
                $('#step2').removeClass('d-none');
                setRutaModalAncho('#nuevaRutaModal', true);
                setTimeout(function() {
                    initModalMap();
                    renderContratos(contratosAgregados, response.empleado || null);
                    $('#buscarContratoFolio').trigger('focus');
                }, 100);
            },
            error: function() {
                $('#stepGeocode').addClass('d-none');
                $('#step2').removeClass('d-none');
                setRutaModalAncho('#nuevaRutaModal', true);
                setTimeout(function() {
                    initModalMap();
                    renderContratos(contratosAgregados, null);
                }, 100);
            }
        });
    }

    var $empSearch = $('#selectEmpleadoSearch');
    var $empList = $('#selectEmpleadoList');
    var $empEmpty = $empList.find('.empleado-combobox-empty');
    var selectedEmpleadoId = '';
    var selectedEmpleadoLabel = '';
    var selectedFrecuencia = 'diaria';
    var diasSemana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];

    function formatIsoDate(date) {
        if (!date) return '';
        return flatpickr.formatDate(date, 'Y-m-d');
    }

    function formatDisplayDate(date) {
        if (!date) return '';
        return flatpickr.formatDate(date, 'd/m/Y');
    }

    function getFechaIso(picker) {
        if (!picker || !picker.selectedDates.length) return '';
        return formatIsoDate(picker.selectedDates[0]);
    }

    var defaultFechaInicio = new Date();
    var fpLocale = Object.assign({}, flatpickr.l10ns.es);

    function updateFechaLabel(picker, labelSelector) {
        $(labelSelector).text(picker && picker.selectedDates.length ? formatDisplayDate(picker.selectedDates[0]) : '');
    }

    function createFechaPicker(selector, defaultDate, onChange) {
        var el = document.querySelector(selector);
        if (!el) return null;
        return flatpickr(el, {
            mode: 'single',
            inline: true,
            locale: fpLocale,
            dateFormat: 'Y-m-d',
            defaultDate: defaultDate || null,
            showMonths: 1,
            disableMobile: true,
            monthSelectorType: 'static',
            onChange: onChange || null
        });
    }

    function frecuenciaHintText(picker, frecuencia) {
        if (!picker || !picker.selectedDates.length) return 'Elige la primera fecha de la ruta.';
        var date = picker.selectedDates[0];
        var isoDay = date.getDay() === 0 ? 7 : date.getDay();
        if (frecuencia === 'semanal') return 'Se repetirá cada ' + diasSemana[isoDay - 1] + '.';
        if (frecuencia === 'mensual') return 'Se repetirá el día ' + date.getDate() + ' de cada mes.';
        return 'Se generará una ejecución cada día.';
    }

    function updateBtnSiguiente() {
        updateFechaLabel(pickerNuevaRuta, '#inputFechaRangoLabel');
        $('#frecuenciaHint').text(frecuenciaHintText(pickerNuevaRuta, selectedFrecuencia));
        var nombre = ($('#inputNombreRuta').val() || '').trim();
        $('#btnSiguiente').prop('disabled', !selectedEmpleadoId || !getFechaIso(pickerNuevaRuta) || !nombre);
    }

    var pickerNuevaRuta = createFechaPicker('#inputFechaRango', defaultFechaInicio, function() {
        updateBtnSiguiente();
    });
    var pickerEditarRuta = createFechaPicker('#editInputFechaRango', null, function() {
        updateFechaLabel(pickerEditarRuta, '#editInputFechaRangoLabel');
    });
    updateBtnSiguiente();
    updateFechaLabel(pickerEditarRuta, '#editInputFechaRangoLabel');

    function setRutaModalAncho(modalSelector, wide) {
        $(modalSelector).find('.modal-dialog').toggleClass('modal-mapa', !!wide);
        if (wide) {
            setTimeout(function() {
                if (modalSelector === '#nuevaRutaModal' && modalMap) modalMap.invalidateSize();
                if (modalSelector === '#editarRutaModal' && editMap) editMap.invalidateSize();
            }, 220);
        }
    }

    function fixCalendarioAncho(picker) {
        if (!picker || !picker.calendarContainer) return;
        picker.calendarContainer.classList.remove('multiMonth');
        picker.redraw();
        picker.calendarContainer.style.width = '307.875px';
    }

    $('#nuevaRutaModal').on('shown.bs.modal', function() {
        fixCalendarioAncho(pickerNuevaRuta);
    });
    $('#editarRutaModal').on('shown.bs.modal', function() {
        fixCalendarioAncho(pickerEditarRuta);
    });

    $('#btnSiguiente').on('click', function() {
        var empId = selectedEmpleadoId;
        var fecha = getFechaIso(pickerNuevaRuta);
        var nombre = ($('#inputNombreRuta').val() || '').trim();
        if (!empId || !fecha || !nombre) return;

        $('#formEmpleadoId').val(empId);
        $('#formNombre').val(nombre);
        $('#formFecha').val(fecha);
        $('#formFrecuencia').val(selectedFrecuencia);

        var empText = $('#selectEmpleadoSearch').val().trim();
        $('#step2Empleado').text(nombre);
        $('#step2Fecha').text(empText + ' · ' + formatDisplayDate(pickerNuevaRuta.selectedDates[0]) + ' · ' + selectedFrecuencia);

        $('#step1').addClass('d-none');
        $('#stepGeocode').addClass('d-none');
        $('#step2').removeClass('d-none');
        updateStepIndicator(2);
        irAPaso2(empId);
    });

    $('#btnVolver').on('click', function() {
        $('#step2').addClass('d-none');
        $('#step1').removeClass('d-none');
        setRutaModalAncho('#nuevaRutaModal', false);
        updateStepIndicator(1);
    });

    function setEmpleadoSeleccionado(id, label) {
        selectedEmpleadoId = id || '';
        if (label !== undefined) {
            selectedEmpleadoLabel = label;
            $empSearch.val(label);
        }
        $('#btnSiguiente').prop('disabled', !selectedEmpleadoId);
        updateBtnSiguiente();
    }

    $('#inputNombreRuta').on('input', updateBtnSiguiente);

    $('#frecuenciaTabs').on('click', '.seg-tab', function() {
        selectedFrecuencia = $(this).data('value');
        setPuntoCasaModo('#frecuenciaTabs', selectedFrecuencia);
        $('#formFrecuencia').val(selectedFrecuencia);
        updateBtnSiguiente();
    });

    function filterEmpleadoList(query) {
        var q = (query || '').toLowerCase().trim();
        var visible = 0;
        $empList.find('.empleado-combobox-item').each(function() {
            var $item = $(this);
            var text = ($item.attr('data-label') || $item.text()).toString().toLowerCase();
            var match = !q || text.indexOf(q) !== -1;
            $item.closest('li').toggle(match);
            if (match) visible++;
        });
        $empEmpty.toggle(visible === 0);
    }

    function openEmpleadoList() {
        filterEmpleadoList($empSearch.val());
        $empList.prop('hidden', false);
        $empSearch.attr('aria-expanded', 'true');
    }

    function closeEmpleadoList() {
        $empList.prop('hidden', true);
        $empSearch.attr('aria-expanded', 'false');
    }

    $empSearch.on('focus', function() {
        $(this).select();
        if (selectedEmpleadoId && $(this).val() === selectedEmpleadoLabel) {
            filterEmpleadoList('');
            $empList.prop('hidden', false);
            $empSearch.attr('aria-expanded', 'true');
        } else {
            openEmpleadoList();
        }
    });

    $empSearch.on('input', function() {
        if ($(this).val() !== selectedEmpleadoLabel) {
            setEmpleadoSeleccionado('', undefined);
        }
        openEmpleadoList();
    });

    $empSearch.on('keydown', function(e) {
        if (e.key === 'Escape') {
            closeEmpleadoList();
            return;
        }
        if (e.key === 'Enter') {
            e.preventDefault();
            var $first = $empList.find('.empleado-combobox-item:visible').first();
            if ($first.length) $first.trigger('click');
        }
    });

    $empList.on('click', '.empleado-combobox-item', function() {
        var id = $(this).attr('data-id');
        var label = $(this).attr('data-label');
        setEmpleadoSeleccionado(id, label);
        closeEmpleadoList();
    });

    $(document).on('click', function(e) {
        if (!$(e.target).closest('#empleadoCombobox').length) {
            closeEmpleadoList();
        }
    });

    function updateStepIndicator(step) {
        $('.step-item').each(function() {
            var s = $(this).data('step');
            var num = $(this).find('.step-num');
            var label = $(this).find('.step-label');
            if (s <= step) {
                num.addClass('active');
                label.removeClass('text-muted').addClass('fw-semibold').css('color', '#79481D');
            } else {
                num.removeClass('active');
                label.addClass('text-muted').removeClass('fw-semibold').css('color', '');
            }
        });
    }

    function idsAgregados() {
        return contratosAgregados.map(function(c) { return String(c.id); });
    }

    function agregarContratoARuta(contrato) {
        if (idsAgregados().indexOf(String(contrato.id)) !== -1) return;
        contratosAgregados.push(contrato);
        renderContratos(contratosAgregados, empleadoHome ? {
            latitud: empleadoHome.lat,
            longitud: empleadoHome.lng,
            domicilio: empleadoHome.domicilio || ''
        } : null);
        if (!contrato.tiene_coordenadas && contrato.cliente_id) {
            geocodificarContratoAgregado(contrato);
        }
    }

    function quitarContratoDeRuta(contratoId) {
        contratosAgregados = contratosAgregados.filter(function(c) { return String(c.id) !== String(contratoId); });
        renderContratos(contratosAgregados, empleadoHome ? {
            latitud: empleadoHome.lat,
            longitud: empleadoHome.lng,
            domicilio: empleadoHome.domicilio || ''
        } : null);
    }

    function geocodificarContratoAgregado(contrato) {
        var csrf = document.querySelector('meta[name="csrf-token"]');
        fetch('{{ route("rutas.geocodificarCliente") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf ? csrf.content : '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: JSON.stringify({ cliente_id: contrato.cliente_id })
        }).then(function(r) { return r.json(); })
        .then(function(d) {
            if (!d.success) return;
            contratosAgregados.forEach(function(c) {
                if (String(c.cliente_id) === String(contrato.cliente_id)) {
                    c.latitud = d.lat;
                    c.longitud = d.lng;
                    c.tiene_coordenadas = true;
                }
            });
            renderContratos(contratosAgregados, empleadoHome ? {
                latitud: empleadoHome.lat,
                longitud: empleadoHome.lng,
                domicilio: empleadoHome.domicilio || ''
            } : null);
        }).catch(function() {});
    }

    function cerrarBusquedaContrato() {
        $('#buscarContratoLista').prop('hidden', true);
    }

    function renderResultadosContrato(contratos) {
        var $list = $('#buscarContratoLista');
        $list.find('.resultado-contrato').remove();
        var agregados = idsAgregados();
        var visibles = 0;
        contratos.forEach(function(c) {
            if (String(c.estado || 'activo').toLowerCase() !== 'activo') return;
            if (agregados.indexOf(String(c.id)) !== -1) return;
            visibles++;
            var $li = $('<li class="resultado-contrato"></li>');
            var $btn = $('<button type="button" class="empleado-combobox-item"></button>');
            if (c.ocupado) $btn.prop('disabled', true).css('opacity', '0.55');
            $btn.append('<div class="fw-semibold small">Folio #' + c.id + (c.ocupado ? ' <span class="text-danger">en ruta activa</span>' : '') + '</div>');
            $btn.append('<div class="text-muted" style="font-size:0.75rem;">' + (c.cliente_nombre || '') + ' — ' + (c.domicilio || '') + '</div>');
            $btn.on('click', function() {
                if (c.ocupado) return;
                agregarContratoARuta(c);
                $('#buscarContratoFolio').val('');
                cerrarBusquedaContrato();
            });
            $li.append($btn);
            $list.append($li);
        });
        $('#buscarContratoEmpty').toggle(visibles === 0).text(visibles === 0 ? 'Sin resultados' : '');
        $list.prop('hidden', false);
    }

    $('#buscarContratoFolio').on('input', function() {
        var q = $(this).val().trim();
        clearTimeout(contratoSearchTimer);
        if (!q) {
            cerrarBusquedaContrato();
            return;
        }
        contratoSearchTimer = setTimeout(function() {
            $.ajax({
                url: '{{ route("rutas.buscarContratos") }}',
                method: 'GET',
                data: { q: q },
                success: function(response) {
                    if (response.success) renderResultadosContrato(response.contratos || []);
                }
            });
        }, 250);
    });

    $('#buscarContratoFolio').on('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            $('#buscarContratoLista .empleado-combobox-item:not(:disabled)').first().trigger('click');
        }
        if (e.key === 'Escape') cerrarBusquedaContrato();
    });

    $(document).on('click', function(e) {
        if (!$(e.target).closest('#contratoFolioCombobox').length) {
            cerrarBusquedaContrato();
        }
    });

    window.loadContratos = function(empId) {
        irAPaso2(empId);
    };

    function groupByDomicilio(contratos) {
        var map = {};
        var order = [];
        contratos.forEach(function(c) {
            var key = c.cliente_id;
            if (!map[key]) {
                map[key] = {
                    cliente_id: c.cliente_id,
                    cliente_nombre: c.cliente_nombre,
                    cliente_telefono: c.cliente_telefono,
                    domicilio: c.domicilio,
                    latitud: c.latitud,
                    longitud: c.longitud,
                    tiene_coordenadas: c.tiene_coordenadas,
                    contratos: []
                };
                order.push(key);
            }
            map[key].contratos.push(c);
        });
        return order.map(function(k) { return map[k]; });
    }

    function renderContratos(contratos, empleado) {
        var container = $('#listaContratos');
        container.empty();
        clearMapOverlays();
        domicilios = [];

        if (empleado && empleado.latitud && empleado.longitud && modalMap) {
            var modoCasa = getPuntoCasaModo('#puntoCasaTabs');
            homeMarker = L.marker([empleado.latitud, empleado.longitud], {
                icon: L.divIcon({
                    className: 'custom-marker',
                    html: '<div style="background:#28a745;width:34px;height:34px;border-radius:50%;border:2px solid white;box-shadow:0 2px 6px rgba(0,0,0,0.3);display:flex;align-items:center;justify-content:center;"><i class="fa-solid fa-house" style="color:white;font-size:15px;"></i></div>',
                    iconSize: [34, 34], iconAnchor: [17, 17]
                }),
                zIndexOffset: 1000
            }).addTo(modalMap);
            homeMarker.bindPopup('<strong>' + etiquetaPuntoCasa(modoCasa) + '</strong><br><small>' + (empleado.domicilio || 'Empleado') + '</small>');
        }

        if (contratos.length === 0) {
            container.html('<div class="text-center py-5 text-muted"><i class="bi bi-search" style="font-size: 2rem;"></i><p class="mt-2 small mb-0">Busca un contrato por folio y agrégalo a la ruta.</p></div>');
            $('#geocodeAlert').addClass('d-none');
            actualizarRutaModal();
            return;
        }

        domicilios = groupByDomicilio(contratos);
        if (empleado && empleado.latitud && empleado.longitud) {
            empleadoHome = { lat: parseFloat(empleado.latitud), lng: parseFloat(empleado.longitud), domicilio: empleado.domicilio || '' };
            domicilios = ordenarPorCercania(domicilios, empleadoHome.lat, empleadoHome.lng);
        } else {
            empleadoHome = null;
        }
        $('#puntoCasaTabs .seg-tab').prop('disabled', !empleadoHome);
        var sinCoords = 0;
        var domIdx = 0;

        domicilios.forEach(function(dom) {
            domIdx++;
            var hasCoords = dom.tiene_coordenadas && dom.latitud && dom.longitud;
            var checked = 'checked';
            var opacity = '1';
            if (!hasCoords) sinCoords++;

            var multiBadge = dom.contratos.length > 1
                ? ' <span class="badge bg-secondary rounded-pill ms-1" style="font-size:0.65rem;">' + dom.contratos.length + ' contratos</span>'
                : '';

            var html = '<div class="list-group-item px-3 py-2 domicilio-item" data-domidx="' + (domIdx - 1) + '" data-cliente-id="' + dom.cliente_id + '" data-lat="' + (dom.latitud || '') + '" data-lng="' + (dom.longitud || '') + '" style="opacity: ' + opacity + '; transition: all 0.2s; border-left: 3px solid transparent;">';
            html += '<div class="d-flex align-items-start gap-2">';
            html += '<div class="d-flex flex-column align-items-center gap-1 pt-1"><i class="bi bi-grip-vertical text-muted" style="cursor: grab; font-size: 1rem;"></i>';
            html += '<input type="checkbox" class="form-check-input domicilio-check check-shalom" ' + checked + ' aria-label="Seleccionar domicilio de ' + dom.cliente_nombre.replace(/"/g, '&quot;') + '"></div>';
            html += '<div class="flex-grow-1 min-width-0">';
            html += '<div class="d-flex justify-content-between align-items-center"><div class="fw-semibold small">' + dom.cliente_nombre + multiBadge + '</div><span class="badge bg-light text-dark rounded-pill orden-badge" style="font-size:0.65rem;">#' + domIdx + '</span></div>';
            html += '<div class="text-muted" style="font-size: 0.75rem;"><i class="bi bi-geo-alt me-1"></i>' + dom.domicilio + '</div>';

            dom.contratos.forEach(function(c) {
                html += '<div class="d-flex gap-2 mt-1 align-items-center contrato-sub" data-id="' + c.id + '" style="font-size: 0.75rem; padding-left: 4px; border-left: 2px solid #e9ecef;">';
                html += '<input type="checkbox" class="form-check-input contrato-check check-shalom" value="' + c.id + '" ' + checked + ' aria-label="Seleccionar contrato ' + c.id + '">';
                html += '<span class="text-muted">Folio #' + c.id + '</span>';
                html += '<span><strong>$' + c.cuota + '</strong> cuota</span>';
                if (c.abono_promedio > 0) html += '<span class="text-success"><strong>$' + c.abono_promedio.toFixed(2) + '</strong> prom</span>';
                if (c.saldo_raw > 0) html += '<span class="text-danger"><strong>$' + c.saldo + '</strong> saldo</span>';
                if (c.proxima_fecha_pago) html += '<span class="' + (c.pago_atrasado ? 'text-danger fw-bold' : '') + '">' + c.proxima_fecha_pago + '</span>';
                html += '<button type="button" class="btn btn-link btn-sm p-0 ms-auto text-danger quitar-contrato" data-id="' + c.id + '" title="Quitar de la ruta"><i class="bi bi-x-lg"></i></button>';
                html += '</div>';
            });

            if (!hasCoords) html += '<small class="text-warning" id="sin-coord-' + dom.cliente_id + '"><i class="bi bi-exclamation-triangle"></i> Sin coordenadas</small>';
            html += '</div></div></div>';

            container.append(html);

            if (hasCoords) {
                var m = L.marker([dom.latitud, dom.longitud], {
                    icon: makeIcon('#79481D', 30, String(domIdx)),
                    zIndexOffset: 500
                });
                var popupHtml = '<div style="min-width:170px"><strong style="color:#79481D">' + dom.cliente_nombre + '</strong><br><small>' + dom.domicilio + '</small>';
                dom.contratos.forEach(function(c) {
                    popupHtml += '<br><small class="text-muted">C#' + c.id + ' — $' + c.cuota + ' cuota | $' + c.saldo + ' saldo</small>';
                });
                popupHtml += '</div>';
                m.bindPopup(popupHtml);
                domMarkers.push({ marker: m, idx: domIdx - 1 });
                if (checked) markerLayer.addLayer(m);
            }
        });

        var pts = [];
        if (empleadoHome) pts.push([empleadoHome.lat, empleadoHome.lng]);
        domicilios.forEach(function(dom, i) {
            if (dom.tiene_coordenadas && dom.latitud && dom.longitud) {
                var item = container.find('.domicilio-item').eq(i);
                if (item.find('.contrato-check:checked').length > 0) pts.push([dom.latitud, dom.longitud]);
            }
        });
        if (pts.length > 0 && modalMap) modalMap.fitBounds(pts, { padding: [30, 30] });

        if (sinCoords > 0) {
            $('#geocodeCount').text(sinCoords);
            $('#geocodeAlert').removeClass('d-none');
        } else {
            $('#geocodeAlert').addClass('d-none');
        }

        var el = document.getElementById('listaContratos');
        if (el) {
            Sortable.create(el, {
                handle: '.bi-grip-vertical',
                animation: 150,
                ghostClass: 'sortable-ghost',
                onEnd: function() { actualizarRutaModal(); }
            });
        }

        actualizarRutaModal();
    }

    function makeIcon(bg, sz, txt) {
        return L.divIcon({
            className: 'custom-marker',
            html: '<div style="background:' + bg + ';width:' + sz + 'px;height:' + sz + 'px;border-radius:50%;border:2px solid white;box-shadow:0 2px 6px rgba(0,0,0,0.3);display:flex;align-items:center;justify-content:center;"><span style="color:white;font-size:' + Math.round(sz * 0.4) + 'px;font-weight:bold;">' + txt + '</span></div>',
            iconSize: [sz, sz], iconAnchor: [sz / 2, sz / 2]
        });
    }

    function syncSelectContratosTabs() {
        return;
    }

    function actualizarRutaModal() {
        if (routeLine && modalMap) { modalMap.removeLayer(routeLine); routeLine = null; }
        var coords = [];
        var totalContratos = 0;
        var visIdx = 0;
        var modoCasa = getPuntoCasaModo('#puntoCasaTabs');
        $('#formPuntoCasa').val(modoCasa);

        $('#listaContratos .domicilio-item').each(function() {
            var lat = $(this).data('lat');
            var lng = $(this).data('lng');
            var domidx = $(this).data('domidx');
            var checkedSubs = $(this).find('.contrato-check:checked');
            var hasChecked = checkedSubs.length > 0;

            if (hasChecked && lat && lng) {
                visIdx++;
                coords.push([parseFloat(lat), parseFloat(lng)]);
            }
            totalContratos += checkedSubs.length;

            var entry = domMarkers.find(function(d) { return d.idx === domidx; });
            if (entry && markerLayer) {
                if (hasChecked) {
                    entry.marker.setIcon(makeIcon('#79481D', 30, String(visIdx)));
                    if (!markerLayer.hasLayer(entry.marker)) markerLayer.addLayer(entry.marker);
                    $(this).find('.orden-badge').text('#' + visIdx).show();
                } else {
                    if (markerLayer.hasLayer(entry.marker)) markerLayer.removeLayer(entry.marker);
                    $(this).find('.orden-badge').hide();
                }
            }
        });

        coords = aplicarHomeARuta(coords, empleadoHome, modoCasa);

        if (coords.length > 1 && modalMap) {
            routeLine = L.polyline(coords, { color: '#79481D', weight: 3, opacity: 0.7, dashArray: '10,8' }).addTo(modalMap);
        }

        if (homeMarker) {
            homeMarker.setPopupContent('<strong>' + etiquetaPuntoCasa(modoCasa) + '</strong><br><small>Domicilio del empleado</small>');
        }

        var paradasCount = visIdx;
        $('#selectedCount').text(totalContratos + ' contrato(s) en ' + paradasCount + ' domicilio(s)');
        syncSelectContratosTabs();
    }

    function syncDomicilioCheck(domicilioItem) {
        var all = $(domicilioItem).find('.contrato-check');
        var checked = $(domicilioItem).find('.contrato-check:checked');
        var domCb = $(domicilioItem).find('.domicilio-check');
        if (checked.length === 0) {
            domCb.prop('checked', false).prop('indeterminate', false);
            $(domicilioItem).css('opacity', '0.5');
        } else if (checked.length === all.length) {
            domCb.prop('checked', true).prop('indeterminate', false);
            $(domicilioItem).css('opacity', '1');
        } else {
            domCb.prop('checked', false).prop('indeterminate', true);
            $(domicilioItem).css('opacity', '1');
        }
    }

    $(document).on('click', '.quitar-contrato', function(e) {
        e.preventDefault();
        e.stopPropagation();
        quitarContratoDeRuta($(this).data('id'));
    });

    $(document).on('change', '.domicilio-check', function() {
        var item = $(this).closest('.domicilio-item');
        item.find('.contrato-check').prop('checked', this.checked);
        item.css('opacity', this.checked ? '1' : '0.5');
        actualizarRutaModal();
    });

    $(document).on('change', '.contrato-check', function() {
        var item = $(this).closest('.domicilio-item');
        syncDomicilioCheck(item);
        actualizarRutaModal();
    });

    $('#puntoCasaTabs').on('click', '.seg-tab', function() {
        if ($(this).prop('disabled')) return;
        setPuntoCasaModo('#puntoCasaTabs', $(this).data('value'));
        $('#formPuntoCasa').val(getPuntoCasaModo('#puntoCasaTabs'));
        actualizarRutaModal();
    });

    $('#nuevaRutaForm').on('submit', function() {
        $('#formNombre').val(($('#inputNombreRuta').val() || '').trim());
        $('#formFrecuencia').val(selectedFrecuencia);
        $(this).find('input[name="contratos[]"]').remove();
        $('#listaContratos .contrato-check:checked').each(function() {
            $('#nuevaRutaForm').append('<input type="hidden" name="contratos[]" value="' + $(this).val() + '">');
        });
        $('#formNotas').val($('#inputNotas').val());
    });

    $('#nuevaRutaModal').on('hidden.bs.modal', function() {
        $('#step1').removeClass('d-none');
        $('#stepGeocode').addClass('d-none');
        $('#step2').addClass('d-none');
        updateStepIndicator(1);
        selectedEmpleadoLabel = '';
        setEmpleadoSeleccionado('', '');
        if (pickerNuevaRuta) pickerNuevaRuta.setDate(defaultFechaInicio, false);
        $('#formEmpleadoId').val('');
        $('#formNombre').val('');
        $('#formFecha').val('');
        $('#formFrecuencia').val('diaria');
        $('#inputNombreRuta').val('');
        selectedFrecuencia = 'diaria';
        setPuntoCasaModo('#frecuenciaTabs', 'diaria');
        setPuntoCasaModo('#puntoCasaTabs', 'inicio');
        $('#formPuntoCasa').val('inicio');
        empleadoHome = null;
        contratosAgregados = [];
        $('#buscarContratoFolio').val('');
        cerrarBusquedaContrato();
        closeEmpleadoList();
        setRutaModalAncho('#nuevaRutaModal', false);
        $('#listaContratos').empty();
        clearMapOverlays();
        domicilios = [];
    });

    // ==================== EDITAR RUTA ====================
    var editMap = null;
    var editMarkerLayer = null;
    var editRouteLine = null;
    var editHomeMarker = null;
    var editRutaData = null;
    var selectedEditEmpleadoId = '';
    var selectedEditEmpleadoLabel = '';
    var $editEmpSearch = $('#editEmpleadoSearch');
    var $editEmpList = $('#editEmpleadoList');
    var $editEmpEmpty = $editEmpList.find('.empleado-combobox-empty');

    function initEditMap() {
        if (editMap) {
            editMap.invalidateSize();
            return;
        }
        editMap = L.map('editModalMap').setView([20.67, -103.36], 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OSM', maxZoom: 19
        }).addTo(editMap);
        editMarkerLayer = L.layerGroup().addTo(editMap);
    }

    function clearEditMap() {
        if (editMarkerLayer) editMarkerLayer.clearLayers();
        if (editRouteLine && editMap) { editMap.removeLayer(editRouteLine); editRouteLine = null; }
        if (editHomeMarker && editMap) { editMap.removeLayer(editHomeMarker); editHomeMarker = null; }
    }

    function updateEditStepIndicator(step) {
        $('#editStepIndicator .step-item').each(function() {
            var s = $(this).data('step');
            var num = $(this).find('.step-num');
            var label = $(this).find('.step-label');
            if (s <= step) {
                num.addClass('active');
                label.removeClass('text-muted').addClass('fw-semibold').css('color', '#79481D');
            } else {
                num.removeClass('active');
                label.addClass('text-muted').removeClass('fw-semibold').css('color', '');
            }
        });
    }

    // Abrir modal de edición
    window.openEditModal = function(rutaId) {
        loadRutaData(rutaId);
    };

    $('#eliminarRutaModal').on('show.bs.modal', function(e) {
        var btn = $(e.relatedTarget);
        var rutaId = btn.data('ruta-id');
        var nombre = btn.data('ruta-nombre') || ('Ruta #' + rutaId);
        $('#eliminarRutaNombre').text(nombre);
        $('#eliminarRutaForm').attr('action', '{{ url('/rutas') }}/' + rutaId);
    });

    // Toggle detener/reactivar
    window.toggleDetenerRuta = function(rutaId, btn) {
        var csrf = $('meta[name="csrf-token"]').attr('content');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

        $.ajax({
            url: '/rutas/' + rutaId + '/toggle-detener',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            success: function(response) {
                if (response.success) {
                    location.reload();
                }
            },
            error: function(xhr) {
                btn.disabled = false;
                var msg = 'Error al cambiar estado.';
                if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                alert(msg);
                location.reload();
            }
        });
    };

    function loadRutaData(rutaId) {
        $.ajax({
            url: '/rutas/' + rutaId + '/datos',
            method: 'GET',
            headers: { 'Accept': 'application/json' },
            success: function(response) {
                if (response.success) {
                    editRutaData = response;
                    populateEditStep1(response.ruta);
                    $('#editarRutaModal').modal('show');
                }
            },
            error: function() {
                alert('Error al cargar los datos de la ruta.');
            }
        });
    }

    function populateEditStep1(ruta) {
        $('#editRutaId').text('#' + ruta.id);
        $('#editRutaIdInput').val(ruta.id);
        var $match = $editEmpList.find('.empleado-combobox-item[data-id="' + ruta.empleado_id + '"]');
        var label = $match.attr('data-label') || (ruta.empleado_nombre || '');
        setEditEmpleadoSeleccionado(ruta.empleado_id, label);
        $('#editInputNombre').val(ruta.nombre || '');
        if (pickerEditarRuta) pickerEditarRuta.setDate(ruta.fecha, true);
        $('#editInputNotas').val(ruta.notas || '');
        setPuntoCasaModo('#editPuntoCasaTabs', ruta.punto_casa || 'inicio');
    }

    function setEditEmpleadoSeleccionado(id, label) {
        selectedEditEmpleadoId = id || '';
        if (label !== undefined) {
            selectedEditEmpleadoLabel = label;
            $editEmpSearch.val(label);
        }
    }

    function filterEditEmpleadoList(query) {
        var q = (query || '').toLowerCase().trim();
        var visible = 0;
        $editEmpList.find('.empleado-combobox-item').each(function() {
            var $item = $(this);
            var text = ($item.attr('data-label') || $item.text()).toString().toLowerCase();
            var match = !q || text.indexOf(q) !== -1;
            $item.closest('li').toggle(match);
            if (match) visible++;
        });
        $editEmpEmpty.toggle(visible === 0);
    }

    function openEditEmpleadoList() {
        filterEditEmpleadoList($editEmpSearch.val() === selectedEditEmpleadoLabel ? '' : $editEmpSearch.val());
        $editEmpList.prop('hidden', false);
        $editEmpSearch.attr('aria-expanded', 'true');
    }

    function closeEditEmpleadoList() {
        $editEmpList.prop('hidden', true);
        $editEmpSearch.attr('aria-expanded', 'false');
    }

    $editEmpSearch.on('focus', function() {
        $(this).select();
        openEditEmpleadoList();
    });

    $editEmpSearch.on('input', function() {
        if ($(this).val() !== selectedEditEmpleadoLabel) {
            setEditEmpleadoSeleccionado('', undefined);
        }
        openEditEmpleadoList();
    });

    $editEmpSearch.on('keydown', function(e) {
        if (e.key === 'Escape') {
            closeEditEmpleadoList();
            return;
        }
        if (e.key === 'Enter') {
            e.preventDefault();
            var $first = $editEmpList.find('.empleado-combobox-item:visible').first();
            if ($first.length) $first.trigger('click');
        }
    });

    $editEmpList.on('click', '.empleado-combobox-item', function() {
        setEditEmpleadoSeleccionado($(this).attr('data-id'), $(this).attr('data-label'));
        closeEditEmpleadoList();
    });

    $(document).on('click', function(e) {
        if (!$(e.target).closest('#editEmpleadoCombobox').length) {
            closeEditEmpleadoList();
        }
    });

    function aplicarEmpleadoEnEdicion(done) {
        if (!editRutaData || !editRutaData.ruta) {
            done();
            return;
        }
        var ruta = editRutaData.ruta;
        if (!selectedEditEmpleadoId) {
            alert('Selecciona un empleado para esta ruta.');
            return;
        }
        if (String(ruta.empleado_id) === String(selectedEditEmpleadoId) && ruta.empleado_lat) {
            done();
            return;
        }
        var csrf = $('meta[name="csrf-token"]').attr('content');
        $.ajax({
            url: '{{ route("rutas.empleadoDatos") }}',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            data: { empleado_id: selectedEditEmpleadoId },
            success: function(response) {
                ruta.empleado_id = selectedEditEmpleadoId;
                ruta.empleado_nombre = selectedEditEmpleadoLabel;
                if (response.empleado) {
                    ruta.empleado_lat = response.empleado.latitud;
                    ruta.empleado_lng = response.empleado.longitud;
                }
                done();
            },
            error: function() {
                ruta.empleado_id = selectedEditEmpleadoId;
                ruta.empleado_nombre = selectedEditEmpleadoLabel;
                done();
            }
        });
    }

    $('#editBtnSiguiente').on('click', function() {
        if (!editRutaData || !editRutaData.ruta) return;
        var fecha = getFechaIso(pickerEditarRuta);
        if (!fecha) return;
        if (!selectedEditEmpleadoId) {
            alert('Selecciona un empleado para esta ruta.');
            return;
        }

        aplicarEmpleadoEnEdicion(function() {
            var ruta = editRutaData.ruta;
            $('#editStep1').addClass('d-none');
            $('#editStep2').removeClass('d-none');
            setRutaModalAncho('#editarRutaModal', true);
            updateEditStepIndicator(2);

            $('#editStep2Empleado').text(selectedEditEmpleadoLabel);
            $('#editStep2Fecha').text(formatDisplayDate(pickerEditarRuta.selectedDates[0]));

            setTimeout(function() {
                initEditMap();
                renderEditParadas(editRutaData.paradas, ruta);
            }, 200);
        });
    });

    $('#editBtnVolver').on('click', function() {
        $('#editStep2').addClass('d-none');
        $('#editStep1').removeClass('d-none');
        setRutaModalAncho('#editarRutaModal', false);
        updateEditStepIndicator(1);
    });

    function renderEditParadas(paradas, ruta) {
        var container = $('#editListaParadas');
        container.empty();
        clearEditMap();
        $('#editPuntoCasaTabs .seg-tab').prop('disabled', !(ruta.empleado_lat && ruta.empleado_lng));

        // Home marker
        if (ruta.empleado_lat && ruta.empleado_lng && editMap) {
            editHomeMarker = L.marker([ruta.empleado_lat, ruta.empleado_lng], {
                icon: L.divIcon({
                    className: 'custom-marker',
                    html: '<div style="background:#28a745;width:34px;height:34px;border-radius:50%;border:2px solid white;box-shadow:0 2px 6px rgba(0,0,0,0.3);display:flex;align-items:center;justify-content:center;"><i class="fa-solid fa-house" style="color:white;font-size:15px;"></i></div>',
                    iconSize: [34, 34], iconAnchor: [17, 17]
                }),
                zIndexOffset: 1000
            }).addTo(editMap);
            editHomeMarker.bindPopup('<strong>' + etiquetaPuntoCasa(getPuntoCasaModo('#editPuntoCasaTabs')) + '</strong><br><small>Domicilio del empleado</small>');
        }

        var pts = [];
        if (ruta.empleado_lat && ruta.empleado_lng) pts.push([ruta.empleado_lat, ruta.empleado_lng]);

        paradas.forEach(function(p, idx) {
            var estadoBadge = p.estado === 'visitada' ? 'bg-success' : (p.estado === 'omitida' ? 'bg-danger' : 'bg-secondary');

            var html = '<div class="list-group-item px-3 py-2 edit-parada-item" data-id="' + p.id + '" data-lat="' + (p.latitud || '') + '" data-lng="' + (p.longitud || '') + '">';
            html += '<div class="d-flex align-items-start gap-2">';
            html += '<div class="d-flex flex-column align-items-center gap-1 pt-1"><i class="bi bi-grip-vertical text-muted" style="cursor: grab; font-size: 1rem;"></i>';
            html += '<span class="badge bg-light text-dark rounded-pill edit-orden-badge" style="font-size:0.65rem;">#' + (idx + 1) + '</span></div>';
            html += '<div class="flex-grow-1 min-width-0">';
            html += '<div class="d-flex justify-content-between align-items-center"><div class="fw-semibold small">' + p.cliente_nombre + '</div>';
            html += '<span class="badge ' + estadoBadge + ' edit-estado-badge" data-estado="' + p.estado + '" style="cursor:pointer;" title="Click para cambiar">' + p.estado.charAt(0).toUpperCase() + p.estado.slice(1) + '</span></div>';
            html += '<div class="text-muted" style="font-size: 0.75rem;"><i class="bi bi-geo-alt me-1"></i>' + p.domicilio + '</div>';
            html += '</div></div></div>';

            container.append(html);

            if (p.latitud && p.longitud && editMap) {
                pts.push([p.latitud, p.longitud]);
                var color = p.estado === 'visitada' ? '#28a745' : (p.estado === 'omitida' ? '#dc3545' : '#79481D');
                var m = L.marker([p.latitud, p.longitud], {
                    icon: makeIcon(color, 30, String(idx + 1)),
                    zIndexOffset: 500
                });
                m.bindPopup('<div style="min-width:170px"><strong>' + p.cliente_nombre + '</strong><br><small>' + p.domicilio + '</small><br><span class="badge ' + estadoBadge + '">' + p.estado + '</span></div>');
                editMarkerLayer.addLayer(m);
            }
        });

        if (pts.length > 0 && editMap) editMap.fitBounds(pts, { padding: [30, 30] });

        // Sortable
        var el = document.getElementById('editListaParadas');
        if (el) {
            Sortable.create(el, {
                handle: '.bi-grip-vertical',
                animation: 150,
                ghostClass: 'sortable-ghost',
                onEnd: function() { actualizarEditOrden(); }
            });
        }

        $('#editParadasCount').text(paradas.length + ' parada(s)');
        actualizarEditRutaLine();
    }

    function actualizarEditOrden() {
        var items = $('#editListaParadas .edit-parada-item');
        items.each(function(idx) {
            $(this).find('.edit-orden-badge').text('#' + (idx + 1));
        });
        actualizarEditRutaLine();
    }

    function actualizarEditRutaLine() {
        if (editRouteLine && editMap) { editMap.removeLayer(editRouteLine); editRouteLine = null; }
        var coords = [];
        var modoCasa = getPuntoCasaModo('#editPuntoCasaTabs');
        var home = null;
        if (editRutaData && editRutaData.ruta.empleado_lat && editRutaData.ruta.empleado_lng) {
            home = {
                lat: parseFloat(editRutaData.ruta.empleado_lat),
                lng: parseFloat(editRutaData.ruta.empleado_lng)
            };
        }

        $('#editListaParadas .edit-parada-item').each(function() {
            var lat = $(this).data('lat');
            var lng = $(this).data('lng');
            if (lat && lng) coords.push([parseFloat(lat), parseFloat(lng)]);
        });

        coords = aplicarHomeARuta(coords, home, modoCasa);

        if (coords.length > 1 && editMap) {
            editRouteLine = L.polyline(coords, { color: '#79481D', weight: 3, opacity: 0.7, dashArray: '10,8' }).addTo(editMap);
        }

        if (editHomeMarker) {
            editHomeMarker.setPopupContent('<strong>' + etiquetaPuntoCasa(modoCasa) + '</strong><br><small>Domicilio del empleado</small>');
        }
    }

    $('#editPuntoCasaTabs').on('click', '.seg-tab', function() {
        if ($(this).prop('disabled')) return;
        setPuntoCasaModo('#editPuntoCasaTabs', $(this).data('value'));
        actualizarEditRutaLine();
    });

    // Cambiar estado de parada
    $(document).on('click', '.edit-estado-badge', function() {
        var badge = $(this);
        var estados = ['pendiente', 'visitada', 'omitida'];
        var actual = badge.data('estado');
        var siguiente = estados[(estados.indexOf(actual) + 1) % estados.length];
        var colores = { pendiente: 'bg-secondary', visitada: 'bg-success', omitida: 'bg-danger' };

        badge.data('estado', siguiente);
        badge.removeClass('bg-secondary bg-success bg-danger').addClass(colores[siguiente]);
        badge.text(siguiente.charAt(0).toUpperCase() + siguiente.slice(1));
    });

    // Guardar cambios
        $('#editBtnGuardar').on('click', function() {
        if (!selectedEditEmpleadoId) {
            alert('Selecciona un empleado para esta ruta.');
            return;
        }
        var rutaId = $('#editRutaIdInput').val();
        var paradasOrden = [];
        $('#editListaParadas .edit-parada-item').each(function() {
            paradasOrden.push($(this).data('id'));
        });

        var csrf = $('meta[name="csrf-token"]').attr('content');

        $.ajax({
            url: '/rutas/' + rutaId + '/ajax',
            method: 'PUT',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
            data: JSON.stringify({
                fecha: getFechaIso(pickerEditarRuta),
                nombre: $('#editInputNombre').val(),
                empleado_id: selectedEditEmpleadoId,
                notas: $('#editInputNotas').val(),
                punto_casa: getPuntoCasaModo('#editPuntoCasaTabs'),
                paradas: paradasOrden
            }),
            success: function(response) {
                if (response.success) {
                    location.reload();
                }
            },
            error: function(xhr) {
                var msg = 'Error al guardar.';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    msg = Object.values(xhr.responseJSON.errors).flat().join('\n');
                }
                alert(msg);
            }
        });
    });

    $('#editarRutaModal').on('hidden.bs.modal', function() {
        $('#editStep1').removeClass('d-none');
        $('#editStep2').addClass('d-none');
        setRutaModalAncho('#editarRutaModal', false);
        updateEditStepIndicator(1);
        $('#editListaParadas').empty();
        clearEditMap();
        editRutaData = null;
        setEditEmpleadoSeleccionado('', '');
        closeEditEmpleadoList();
    });
});

function geocodificarPendientes() {
    var btn = document.querySelector('#geocodeAlert .btn');
    var progress = document.getElementById('geocodeProgress');
    var bar = document.getElementById('geocodeBar');
    var text = document.getElementById('geocodeProgressText');
    if (!btn || !progress) return;

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    progress.classList.remove('d-none');

    var ids = [];
    document.querySelectorAll('[id^="sin-coord-"]').forEach(function(el) { ids.push(el.id.replace('sin-coord-', '')); });
    if (ids.length === 0) { btn.disabled = false; btn.innerHTML = '<i class="bi bi-globe me-1"></i>Geocodificar'; progress.classList.add('d-none'); return; }

    var total = ids.length, proc = 0, ok = 0, fail = 0;
    var csrf = document.querySelector('meta[name="csrf-token"]');

    function next() {
        if (ids.length === 0) {
            bar.style.width = '100%';
            text.textContent = ok + ' encontrados, ' + fail + ' sin resultado.';
            if (ok > 0) {
                setTimeout(function() {
                    contratosAgregados.forEach(function(c) {
                        var el = document.getElementById('sin-coord-' + c.cliente_id);
                        if (!el) {
                            c.tiene_coordenadas = true;
                        }
                    });
                    renderContratos(contratosAgregados, empleadoHome ? {
                        latitud: empleadoHome.lat,
                        longitud: empleadoHome.lng,
                        domicilio: empleadoHome.domicilio || ''
                    } : null);
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-globe me-1"></i>Geocodificar';
                    progress.classList.add('d-none');
                }, 400);
            }
            else { btn.disabled = false; btn.innerHTML = '<i class="bi bi-globe me-1"></i>Reintentar'; progress.classList.add('d-none'); }
            return;
        }
        var cid = ids.shift(); proc++;
        bar.style.width = Math.round(proc / total * 100) + '%';
        text.textContent = proc + ' de ' + total + '...';

        fetch('{{ route("rutas.geocodificarCliente") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf ? csrf.content : '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: JSON.stringify({ cliente_id: cid })
        }).then(function(r) { return r.json().then(function(d) { return { s: r.status, d: d }; }); })
        .then(function(r) {
            var el = document.getElementById('sin-coord-' + cid);
            if (r.d.success) {
                ok++;
                contratosAgregados.forEach(function(c) {
                    if (String(c.cliente_id) === String(cid)) {
                        c.latitud = r.d.lat;
                        c.longitud = r.d.lng;
                        c.tiene_coordenadas = true;
                    }
                });
                if (el) {
                    if (r.d.approximate) {
                        el.innerHTML = '<i class="bi bi-info-circle"></i> ' + (r.d.message || 'Aprox');
                        el.className = 'text-info small';
                    } else el.remove();
                }
            }
            else { fail++; if (el) { el.innerHTML = '<i class="bi bi-x-circle"></i> ' + (r.d.message || 'No encontrado'); el.className = 'text-danger small'; } }
            setTimeout(next, 2000);
        }).catch(function() { fail++; setTimeout(next, 2000); });
    }
    next();
}
</script>

<style>
    .page-header {
        background: white;
        border-radius: 20px;
        padding: 2rem;
        margin-bottom: 2rem;
        border: 1px solid rgba(255, 255, 255, 0.8);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .header-content { display: flex; align-items: center; }
    .page-title { font-size: 2rem; font-weight: 700; color: #2d3748; margin: 0; }
    .page-subtitle { color: #718096; font-size: 1rem; margin: 0; margin-top: 0.25rem; }
    .header-actions .btn {
        background: white;
        color: #79481D;
        border: 2px solid #e2e8f0;
        padding: 0.75rem 1.5rem;
        border-radius: 12px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    .header-actions .btn:hover {
        background: linear-gradient(135deg, #E1B240 0%, #79481D 100%);
        color: white;
        border-color: white;
        transform: translateY(-2px);
    }
    .modern-table { border: none !important; }
    .modern-header {
        background: linear-gradient(135deg, #79481D 0%, #E1B240 100%) !important;
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
    }
    .modern-row { border: none !important; transition: all 0.3s ease !important; background: white !important; }
    .modern-row:hover { background: linear-gradient(135deg, #f8f9ff 0%, #f0f4ff 100%) !important; transform: translateY(-2px) !important; box-shadow: 0 8px 25px rgba(121, 72, 29, 0.1) !important; }
    .modern-row td { border: none !important; padding: 1.2rem 1rem !important; vertical-align: middle !important; border-bottom: 1px solid #f1f3f5 !important; }
    .clickable-row { cursor: pointer; }
    .action-btn {
        border-radius: 8px !important;
        margin: 0 2px !important;
        width: 35px;
        height: 35px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .action-btn:hover { transform: translateY(-2px) !important; }
    .table-responsive { border-radius: 12px; overflow: hidden; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1); }
    .avatar-circle { color: white; font-weight: 600; font-size: 0.875rem; }

    #nuevaRutaModal .modal-dialog,
    #editarRutaModal .modal-dialog {
        max-width: 520px;
        width: calc(100% - 2rem);
        transition: max-width 0.2s ease;
    }
    #nuevaRutaModal .modal-dialog.modal-mapa,
    #editarRutaModal .modal-dialog.modal-mapa {
        max-width: min(1140px, calc(100vw - 2rem));
        width: min(1140px, calc(100vw - 2rem));
    }
    #nuevaRutaModal .modal-dialog.modal-mapa .modal-content,
    #editarRutaModal .modal-dialog.modal-mapa .modal-content {
        overflow: hidden;
        max-height: calc(100vh - 3rem);
        display: flex;
        flex-direction: column;
    }
    #nuevaRutaModal .modal-dialog.modal-mapa #nuevaRutaForm,
    #editarRutaModal .modal-dialog.modal-mapa #editarRutaForm {
        display: flex;
        flex-direction: column;
        flex: 1 1 auto;
        min-height: 0;
        min-width: 0;
        overflow: hidden;
    }
    #nuevaRutaModal .modal-dialog.modal-mapa .modal-body,
    #editarRutaModal .modal-dialog.modal-mapa .modal-body {
        overflow: hidden;
        flex: 1 1 auto;
        min-height: 0;
        min-width: 0;
        display: flex;
        flex-direction: column;
    }
    #step2,
    #editStep2 {
        flex: 1 1 auto;
        min-height: 0;
        min-width: 0;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    .modal-paso-mapa {
        flex: 1 1 auto;
        min-height: 0;
        min-width: 0;
        height: 65vh;
        max-height: 65vh;
        overflow: hidden;
        margin-left: 0;
        margin-right: 0;
    }
    .modal-paso-mapa-col {
        min-height: 0;
        min-width: 0;
        height: 100%;
        overflow: hidden;
    }
    .modal-paso-mapa-lista {
        flex: 1 1 0;
        min-height: 0;
        min-width: 0;
        overflow-x: hidden;
        overflow-y: auto;
        overscroll-behavior: contain;
    }
    .seg-tabs {
        display: flex;
        width: 100%;
        padding: 3px;
        gap: 3px;
        background: #f3f4f6;
        border-radius: 10px;
    }
    #selectContratosTabs {
        width: auto;
        flex: 0 0 auto;
        min-width: 148px;
    }
    .seg-tab {
        flex: 1 1 0;
        border: none;
        background: transparent;
        color: #6b7280;
        font-size: 0.8rem;
        font-weight: 600;
        line-height: 1.2;
        padding: 0.4rem 0.35rem;
        border-radius: 8px;
        transition: background 0.15s ease, color 0.15s ease, box-shadow 0.15s ease;
    }
    .seg-tab:hover:not(:disabled):not(.active) {
        background: rgba(255,255,255,0.7);
        color: #79481D;
    }
    .seg-tab.active {
        background: #fff;
        color: #79481D;
        box-shadow: 0 1px 3px rgba(31, 41, 55, 0.12);
    }
    .seg-tab:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    #step1,
    #editStep1 { overflow: visible; }
    #nuevaRutaModal .modal-content,
    #editarRutaModal .modal-content { overflow: visible; }
    .empleado-combobox-list {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        z-index: 1080;
        max-height: 250px;
        overflow-y: auto;
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
        margin-top: 4px;
    }
    .empleado-combobox-item {
        display: block;
        width: 100%;
        text-align: left;
        background: none;
        border: none;
        padding: 0.55rem 0.9rem;
        font-size: 0.95rem;
    }
    .empleado-combobox-item:hover,
    .empleado-combobox-item:focus {
        background: #f8f9ff;
    }

    .rango-calendario {
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    .rango-calendario > .form-label,
    .rango-calendario > .mb-2 {
        width: 100%;
    }
    .rango-calendario .flatpickr-input {
        display: none !important;
    }
    .rango-calendario .flatpickr-calendar,
    .rango-calendario .flatpickr-calendar.inline {
        z-index: 1 !important;
        display: inline-block !important;
        width: 307.875px !important;
        max-width: 100%;
        box-shadow: none;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        margin: 0 auto;
        top: auto !important;
        left: auto !important;
        right: auto !important;
        position: relative !important;
    }
    .flatpickr-day.selected,
    .flatpickr-day.startRange,
    .flatpickr-day.endRange,
    .flatpickr-day.selected:hover,
    .flatpickr-day.startRange:hover,
    .flatpickr-day.endRange:hover {
        background: #79481D;
        border-color: #79481D;
    }
    .flatpickr-day.inRange,
    .flatpickr-day.prevMonthDay.inRange,
    .flatpickr-day.nextMonthDay.inRange {
        background: #f5ead8;
        box-shadow: none;
        border-color: transparent;
        color: #79481D;
    }
    .flatpickr-months .flatpickr-month,
    .flatpickr-current-month .flatpickr-monthDropdown-months,
    .flatpickr-weekday {
        color: #79481D;
    }
    .flatpickr-months .flatpickr-prev-month:hover svg,
    .flatpickr-months .flatpickr-next-month:hover svg {
        fill: #79481D;
    }

    /* Step indicator */
    .step-num {
        display: inline-flex; align-items: center; justify-content: center;
        width: 20px; height: 20px; border-radius: 50%; font-size: 0.7rem; font-weight: 700;
        background: #e9ecef; color: #6b7280; margin-right: 4px;
    }
    .step-num.active { background: #79481D; color: white; }

    /* Sortable */
    .sortable-ghost { opacity: 0.4; background: #fff3cd !important; }
    .domicilio-item:hover { background: #f8f9ff; border-left-color: #79481D !important; }
    .contrato-sub { transition: background 0.15s; border-radius: 4px; padding: 2px 0; }
    .contrato-sub:hover { background: #f0f4ff; }
    .check-shalom {
        appearance: none;
        -webkit-appearance: none;
        width: 22px;
        height: 22px;
        min-width: 22px;
        min-height: 22px;
        margin: 0;
        flex-shrink: 0;
        cursor: pointer;
        border: 2px solid #E1B240;
        border-radius: 6px;
        background-color: #fff;
        background-image: none !important;
        box-shadow: none;
        vertical-align: middle;
        transition: background-color 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .check-shalom:hover {
        border-color: #d4a22e;
        background-color: #fff8e8;
    }
    .check-shalom:checked {
        background-color: #E1B240;
        border-color: #E1B240;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath fill='none' stroke='%23fff' stroke-linecap='round' stroke-linejoin='round' stroke-width='2.4' d='M3.2 8.3l3.1 3.1 6.5-6.8'/%3E%3C/svg%3E") !important;
        background-size: 14px 14px;
        background-position: center;
        background-repeat: no-repeat;
    }
    .check-shalom:indeterminate {
        background-color: #E1B240;
        border-color: #E1B240;
        background-image: none !important;
        box-shadow: inset 0 0 0 5px #fff;
    }
    .check-shalom:focus {
        box-shadow: none;
        outline: none;
    }
    .check-shalom:focus-visible {
        outline: 3px solid #79481D;
        outline-offset: 2px;
    }
    .custom-marker { background: transparent !important; border: none !important; }
    .leaflet-popup-content-wrapper { border-radius: 12px !important; }
    .leaflet-popup-content { margin: 10px 14px !important; font-size: 0.85rem !important; }
    .min-width-0 { min-width: 0; }

    .estado-check + * { cursor: pointer; }
    label:has(.estado-check:checked) { font-weight: 600; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }

    @media (max-width: 768px) {
        .page-header { flex-direction: column; gap: 20px; text-align: center; }
        .header-content { flex-direction: column; gap: 16px; }
    }
</style>
@endsection
