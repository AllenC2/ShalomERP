@extends('layouts.app')

@section('template_title')
    Rutas
@endsection

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<div class="container py-4" style="max-width: 1600px;">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="page-header">
                <div class="header-content">
                    <div class="header-icon">
                        <i class="fa-solid fa-route"></i>
                    </div>
                    <div class="header-text">
                        <h1 class="page-title">{{ __('Rutas de Visita') }}</h1>
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
                                            'cancelada' => 'btn-outline-danger',
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
                                        <th>Empleado</th>
                                        <th>Fecha</th>
                                        <th>Fecha Límite</th>
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
                                            <td>{{ $ruta->fecha->format('d/m/Y') }}</td>
                                            <td>{{ $ruta->fecha_limite->format('d/m/Y') }}</td>
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
                                                    <button type="button" class="btn btn-outline-warning btn-sm action-btn btn-toggle-cancel" data-ruta-id="{{ $ruta->id }}" title="Cancelar ruta" onclick="event.stopPropagation(); toggleCancelRuta({{ $ruta->id }}, this);">
                                                        <i class="bi bi-stop-fill"></i>
                                                    </button>
                                                    @elseif($ruta->estado === \App\Models\Ruta::ESTADO_CANCELADA)
                                                    <button type="button" class="btn btn-outline-info btn-sm action-btn btn-toggle-cancel" data-ruta-id="{{ $ruta->id }}" title="Reactivar ruta" onclick="event.stopPropagation(); toggleCancelRuta({{ $ruta->id }}, this);">
                                                        <i class="bi bi-play-fill"></i>
                                                    </button>
                                                    @endif
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

<!-- Modal Nueva Ruta (multi-paso) -->
@if(auth()->user()->role === 'admin')
<div class="modal fade" id="nuevaRutaModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
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
                            <span class="step-label fw-semibold small" style="color: #79481D;">Empleado</span>
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
                <input type="hidden" name="fecha" id="formFecha">
                <input type="hidden" name="fecha_limite" id="formFechaLimite">
                <input type="hidden" name="notas" id="formNotas">

                <div class="modal-body p-0">
                    <!-- Paso 1 -->
                    <div id="step1" class="p-4">
                        <!-- Fila 1: Empleado -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-person me-1"></i> Empleado
                            </label>
                            <select id="selectEmpleado" class="form-select form-select-lg" required>
                                <option value="">Seleccionar empleado...</option>
                                @foreach($empleados as $empleado)
                                    <option value="{{ $empleado->id }}">
                                        {{ $empleado->nombre }} {{ $empleado->apellido }} ({{ $empleado->id }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Fila 2: Fechas -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-calendar me-1"></i> Fecha de la Ruta
                                </label>
                                <input type="date" id="inputFecha" class="form-control form-control-lg" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-calendar-check me-1"></i> Fecha Límite
                                </label>
                                <input type="date" id="inputFechaLimite" class="form-control form-control-lg" value="{{ date('Y-m-d', strtotime('+7 days')) }}" required>
                            </div>
                        </div>

                        <!-- Siguiente -->
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
                        <div class="row g-0" style="height: 65vh;">
                            <!-- Mapa -->
                            <div class="col-lg-6 position-relative">
                                <div id="modalMap" style="height: 100%; width: 100%; background: #e9ecef;"></div>
                            </div>

                            <!-- Lista de contratos -->
                            <div class="col-lg-6 d-flex flex-column">
                                <div class="px-3 py-2 border-bottom d-flex justify-content-between align-items-center bg-white">
                                    <div>
                                        <span class="fw-bold small" style="color: #79481D;" id="step2Empleado"></span>
                                        <span class="text-muted small ms-2" id="step2Fecha"></span>
                                    </div>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-secondary py-0" id="btnSelectAll">Todos</button>
                                        <button type="button" class="btn btn-outline-secondary py-0" id="btnDeselectAll">Ninguno</button>
                                    </div>
                                </div>

                                <!-- Geocode alert -->
                                <div id="geocodeAlert" class="alert alert-warning d-none mx-3 mt-2 mb-0 py-2 d-flex align-items-center justify-content-between rounded-3">
                                    <span><i class="bi bi-geo-alt-fill me-1"></i><span id="geocodeCount"></span> sin coordenadas</span>
                                    <button type="button" class="btn btn-sm btn-dark py-0" onclick="geocodificarPendientes()"><i class="bi bi-globe me-1"></i>Geocodificar</button>
                                </div>
                                <div id="geocodeProgress" class="alert alert-info d-none mx-3 mt-2 mb-0 py-2 rounded-3">
                                    <div class="d-flex align-items-center">
                                        <div class="spinner-border spinner-border-sm me-2"></div>
                                        <span id="geocodeProgressText">Geocodificando...</span>
                                    </div>
                                    <div class="progress mt-1" style="height: 4px;"><div class="progress-bar bg-success" id="geocodeBar" style="width: 0%"></div></div>
                                </div>

                                <div id="listaContratos" class="list-group list-group-flush flex-grow-1 overflow-auto" style="max-height: 100%;"></div>

                                <div class="px-3 py-2 border-top bg-white">
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
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
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
                            <input type="text" id="editEmpleadoNombre" class="form-control form-control-lg" disabled>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-calendar me-1"></i> Fecha de la Ruta
                                </label>
                                <input type="date" id="editInputFecha" class="form-control form-control-lg" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-calendar-check me-1"></i> Fecha Límite
                                </label>
                                <input type="date" id="editInputFechaLimite" class="form-control form-control-lg" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-flag me-1"></i> Estado
                                </label>
                                <select id="editSelectEstado" class="form-select form-select-lg">
                                    @foreach(\App\Models\Ruta::getEstadosValidos() as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-sticky me-1"></i> Notas
                                </label>
                                <input type="text" id="editInputNotas" class="form-control form-control-lg" placeholder="Observaciones...">
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-lg px-4 text-white fw-bold" style="background: linear-gradient(135deg, #E1B240 0%, #79481D 100%); border: none;" id="editBtnSiguiente">
                                Siguiente <i class="bi bi-arrow-right ms-1"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Paso 2: Mapa + paradas -->
                    <div id="editStep2" class="d-none">
                        <div class="row g-0" style="height: 65vh;">
                            <!-- Mapa -->
                            <div class="col-lg-6 position-relative">
                                <div id="editModalMap" style="height: 100%; width: 100%; background: #e9ecef;"></div>
                            </div>

                            <!-- Lista de paradas -->
                            <div class="col-lg-6 d-flex flex-column">
                                <div class="px-3 py-2 border-bottom d-flex justify-content-between align-items-center bg-white">
                                    <div>
                                        <span class="fw-bold small" style="color: #79481D;" id="editStep2Empleado"></span>
                                        <span class="text-muted small ms-2" id="editStep2Fecha"></span>
                                    </div>
                                </div>

                                <div id="editListaParadas" class="list-group list-group-flush flex-grow-1 overflow-auto" style="max-height: 100%;"></div>

                                <div class="px-3 py-2 border-top bg-white">
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

    function startGeocodificacion(empId) {
        var csrf = $('meta[name="csrf-token"]').attr('content');
        $('#geocodeStepBar').css('width', '0%');
        $('#geocodeStepCount').text('');
        $('#geocodeStepStatus').text('Obteniendo contratos...');
        $('#geocodeStepErrors').html('');

        $.ajax({
            url: '{{ route("rutas.contratosEmpleado") }}',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            data: { empleado_id: empId },
            success: function(response) {
                if (response.success) geocodificarStep(response.contratos, empId);
            },
            error: function() {
                $('#geocodeStepStatus').html('<span class="text-danger">Error al cargar contratos</span>');
            }
        });
    }

    function geocodificarStep(contratos, empId) {
        var ids = [];
        var seen = {};
        contratos.forEach(function(c) {
            if (c.cliente_id && !seen[c.cliente_id] && !c.tiene_coordenadas) { seen[c.cliente_id] = true; ids.push(c.cliente_id); }
        });

        if (ids.length === 0) { irAPaso2(empId); return; }

        var total = ids.length, proc = 0, ok = 0, fail = 0;
        var csrf = document.querySelector('meta[name="csrf-token"]');
        var errorsHtml = '';

        function next() {
            if (ids.length === 0) {
                $('#geocodeStepBar').css('width', '100%');
                $('#geocodeStepCount').text(ok + ' / ' + total);
                $('#geocodeStepStatus').html('<span class="' + (fail > 0 ? 'text-warning' : 'text-success') + ' fw-bold">' + ok + ' actualizados' + (fail > 0 ? ', ' + fail + ' sin resultado' : '') + '</span>');
                if (errorsHtml) $('#geocodeStepErrors').html(errorsHtml);
                setTimeout(function() { irAPaso2(empId); }, 1000);
                return;
            }
            var cid = ids.shift(); proc++;
            $('#geocodeStepBar').css('width', Math.round((proc - 1) / total * 100) + '%');
            $('#geocodeStepCount').text((proc - 1) + ' / ' + total);
            $('#geocodeStepStatus').text('Geocodificando ' + proc + ' de ' + total + '...');

            fetch('{{ route("rutas.geocodificarCliente") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf ? csrf.content : '{{ csrf_token() }}', 'Accept': 'application/json' },
                body: JSON.stringify({ cliente_id: cid })
            }).then(function(r) { return r.json(); })
            .then(function(d) {
                if (d.success) { ok++; }
                else { fail++; errorsHtml += '<div class="text-danger small"><i class="bi bi-x-circle"></i> ' + (d.message || 'Error') + '</div>'; }
                setTimeout(next, 1800);
            }).catch(function() { fail++; setTimeout(next, 1800); });
        }
        next();
    }

    function irAPaso2(empId) {
        var csrf = $('meta[name="csrf-token"]').attr('content');
        $.ajax({
            url: '{{ route("rutas.contratosEmpleado") }}',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            data: { empleado_id: empId },
            success: function(response) {
                if (response.success) {
                    $('#stepGeocode').addClass('d-none');
                    $('#step2').removeClass('d-none');
                    setTimeout(function() {
                        initModalMap();
                        renderContratos(response.contratos, response.empleado);
                    }, 100);
                }
            }
        });
    }

    $('#btnSiguiente').on('click', function() {
        var empId = $('#selectEmpleado').val();
        var fecha = $('#inputFecha').val();
        var fechaLim = $('#inputFechaLimite').val();
        if (!empId || !fecha || !fechaLim) return;

        $('#formEmpleadoId').val(empId);
        $('#formFecha').val(fecha);
        $('#formFechaLimite').val(fechaLim);

        var empText = $('#selectEmpleado option:selected').text().trim();
        $('#step2Empleado').text(empText);
        $('#step2Fecha').text(fecha.split('-').reverse().join('/'));

        $('#step1').addClass('d-none');
        $('#stepGeocode').removeClass('d-none');
        updateStepIndicator(2);

        startGeocodificacion(empId);
    });

    $('#btnVolver').on('click', function() {
        $('#step2').addClass('d-none');
        $('#step1').removeClass('d-none');
        updateStepIndicator(1);
    });

    $('#selectEmpleado').on('change', function() {
        $('#btnSiguiente').prop('disabled', !$(this).val());
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

    window.loadContratos = function(empId) {
        var csrf = $('meta[name="csrf-token"]').attr('content');
        $.ajax({
            url: '{{ route("rutas.contratosEmpleado") }}',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            data: { empleado_id: empId },
            success: function(response) {
                if (response.success) renderContratos(response.contratos, response.empleado);
            },
            error: function() {
                $('#listaContratos').html('<div class="text-center py-5 text-muted">Error al cargar contratos.</div>');
            }
        });
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
            homeMarker = L.marker([empleado.latitud, empleado.longitud], {
                icon: L.divIcon({
                    className: 'custom-marker',
                    html: '<div style="background:#28a745;width:34px;height:34px;border-radius:50%;border:2px solid white;box-shadow:0 2px 6px rgba(0,0,0,0.3);display:flex;align-items:center;justify-content:center;"><i class="fa-solid fa-house" style="color:white;font-size:15px;"></i></div>',
                    iconSize: [34, 34], iconAnchor: [17, 17]
                }),
                zIndexOffset: 1000
            }).addTo(modalMap);
            homeMarker.bindPopup('<strong>Punto de inicio</strong><br><small>Empleado</small>');
        }

        if (contratos.length === 0) {
            container.html('<div class="text-center py-5 text-muted"><i class="bi bi-exclamation-circle" style="font-size: 2rem;"></i><p class="mt-2 small">Este empleado no tiene contratos activos.</p></div>');
            return;
        }

        domicilios = groupByDomicilio(contratos);
        var sinCoords = 0;
        var domIdx = 0;

        domicilios.forEach(function(dom) {
            domIdx++;
            var hasCoords = dom.tiene_coordenadas && dom.latitud && dom.longitud;
            var checked = hasCoords ? 'checked' : '';
            var opacity = hasCoords ? '1' : '0.5';
            if (!hasCoords) sinCoords++;

            var multiBadge = dom.contratos.length > 1
                ? ' <span class="badge bg-secondary rounded-pill ms-1" style="font-size:0.65rem;">' + dom.contratos.length + ' contratos</span>'
                : '';

            var html = '<div class="list-group-item px-3 py-2 domicilio-item" data-domidx="' + (domIdx - 1) + '" data-cliente-id="' + dom.cliente_id + '" data-lat="' + (dom.latitud || '') + '" data-lng="' + (dom.longitud || '') + '" style="opacity: ' + opacity + '; transition: all 0.2s; border-left: 3px solid transparent;">';
            html += '<div class="d-flex align-items-start gap-2">';
            html += '<div class="d-flex flex-column align-items-center gap-1 pt-1"><i class="bi bi-grip-vertical text-muted" style="cursor: grab; font-size: 1rem;"></i>';
            html += '<input type="checkbox" class="form-check-input domicilio-check" ' + checked + '></div>';
            html += '<div class="flex-grow-1 min-width-0">';
            html += '<div class="d-flex justify-content-between align-items-center"><div class="fw-semibold small">' + dom.cliente_nombre + multiBadge + '</div><span class="badge bg-light text-dark rounded-pill orden-badge" style="font-size:0.65rem;">#' + domIdx + '</span></div>';
            html += '<div class="text-muted" style="font-size: 0.75rem;"><i class="bi bi-geo-alt me-1"></i>' + dom.domicilio + '</div>';

            dom.contratos.forEach(function(c) {
                html += '<div class="d-flex gap-2 mt-1 align-items-center contrato-sub" data-id="' + c.id + '" style="font-size: 0.75rem; padding-left: 4px; border-left: 2px solid #e9ecef;">';
                html += '<input type="checkbox" class="form-check-input contrato-check" value="' + c.id + '" ' + checked + ' style="margin: 0;">';
                html += '<span class="text-muted">C#' + c.id + '</span>';
                html += '<span><strong>$' + c.cuota + '</strong> cuota</span>';
                if (c.abono_promedio > 0) html += '<span class="text-success"><strong>$' + c.abono_promedio.toFixed(2) + '</strong> prom</span>';
                if (c.saldo_raw > 0) html += '<span class="text-danger"><strong>$' + c.saldo + '</strong> saldo</span>';
                if (c.proxima_fecha_pago) html += '<span class="' + (c.pago_atrasado ? 'text-danger fw-bold' : '') + '">' + c.proxima_fecha_pago + '</span>';
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
        if (empleado && empleado.latitud && empleado.longitud) pts.push([empleado.latitud, empleado.longitud]);
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

    function actualizarRutaModal() {
        if (routeLine && modalMap) { modalMap.removeLayer(routeLine); }
        var coords = [];
        var totalContratos = 0;
        var visIdx = 0;

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

        if (coords.length > 1 && modalMap) {
            routeLine = L.polyline(coords, { color: '#79481D', weight: 3, opacity: 0.7, dashArray: '10,8' }).addTo(modalMap);
        }

        $('#selectedCount').text(totalContratos + ' contrato(s) en ' + coords.length + ' domicilio(s)');
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

    $('#btnSelectAll').on('click', function() {
        $('.contrato-check').prop('checked', true);
        $('.domicilio-check').prop('checked', true).prop('indeterminate', false);
        $('.domicilio-item').css('opacity', '1');
        actualizarRutaModal();
    });
    $('#btnDeselectAll').on('click', function() {
        $('.contrato-check').prop('checked', false);
        $('.domicilio-check').prop('checked', false).prop('indeterminate', false);
        $('.domicilio-item').css('opacity', '0.5');
        actualizarRutaModal();
    });

    $('#nuevaRutaForm').on('submit', function() {
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
        $('#selectEmpleado').val('');
        $('#btnSiguiente').prop('disabled', true);
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

    // Toggle cancel/uncancel
    window.toggleCancelRuta = function(rutaId, btn) {
        var csrf = $('meta[name="csrf-token"]').attr('content');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

        $.ajax({
            url: '/rutas/' + rutaId + '/toggle-cancel',
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
        $('#editEmpleadoNombre').val(ruta.empleado_nombre);
        $('#editInputFecha').val(ruta.fecha);
        $('#editInputFechaLimite').val(ruta.fecha_limite);
        $('#editSelectEstado').val(ruta.estado);
        $('#editInputNotas').val(ruta.notas || '');
    }

    $('#editBtnSiguiente').on('click', function() {
        var ruta = editRutaData.ruta;
        $('#editStep1').addClass('d-none');
        $('#editStep2').removeClass('d-none');
        updateEditStepIndicator(2);

        $('#editStep2Empleado').text(ruta.empleado_nombre);
        $('#editStep2Fecha').text(ruta.fecha.split('-').reverse().join('/'));

        setTimeout(function() {
            initEditMap();
            renderEditParadas(editRutaData.paradas, ruta);
        }, 200);
    });

    $('#editBtnVolver').on('click', function() {
        $('#editStep2').addClass('d-none');
        $('#editStep1').removeClass('d-none');
        updateEditStepIndicator(1);
    });

    function renderEditParadas(paradas, ruta) {
        var container = $('#editListaParadas');
        container.empty();
        clearEditMap();

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
            editHomeMarker.bindPopup('<strong>Punto de inicio</strong><br><small>Empleado</small>');
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
        if (editRouteLine && editMap) { editMap.removeLayer(editRouteLine); }
        var coords = [];

        if (editRutaData && editRutaData.ruta.empleado_lat && editRutaData.ruta.empleado_lng) {
            coords.push([editRutaData.ruta.empleado_lat, editRutaData.ruta.empleado_lng]);
        }

        $('#editListaParadas .edit-parada-item').each(function() {
            var lat = $(this).data('lat');
            var lng = $(this).data('lng');
            if (lat && lng) coords.push([parseFloat(lat), parseFloat(lng)]);
        });

        if (coords.length > 1 && editMap) {
            editRouteLine = L.polyline(coords, { color: '#79481D', weight: 3, opacity: 0.7, dashArray: '10,8' }).addTo(editMap);
        }
    }

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
                fecha: $('#editInputFecha').val(),
                fecha_limite: $('#editInputFechaLimite').val(),
                estado: $('#editSelectEstado').val(),
                notas: $('#editInputNotas').val(),
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
        updateEditStepIndicator(1);
        $('#editListaParadas').empty();
        clearEditMap();
        editRutaData = null;
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
            if (ok > 0) setTimeout(function() { location.reload(); }, 2000);
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
            if (r.d.success) { ok++; if (el) { if (r.d.approximate) { el.innerHTML = '<i class="bi bi-info-circle"></i> ' + (r.d.message || 'Aprox'); el.className = 'text-info small'; } else el.remove(); } }
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
    .contrato-check { width: 14px; height: 14px; cursor: pointer; flex-shrink: 0; }
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
