@extends('layouts.app')

@section('template_title')
    {{ $plantilla->nombre ?: ('Ruta #' . $plantilla->id) }}
@endsection

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@php
    $freqIcon = match ($plantilla->frecuencia) {
        \App\Models\RutaPlantilla::FRECUENCIA_DIARIA => 'bi-sun',
        \App\Models\RutaPlantilla::FRECUENCIA_SEMANAL => 'bi-calendar-week',
        \App\Models\RutaPlantilla::FRECUENCIA_MENSUAL => 'bi-calendar-month',
        default => 'bi-calendar3',
    };
    $puntoCasa = $plantilla->punto_casa ?? 'inicio';
    $puntoCasaLabel = match ($puntoCasa) {
        'final' => 'Final',
        'ambos' => 'Inicio y fin',
        default => 'Inicial',
    };
    $empNombre = trim(($plantilla->empleado->nombre ?? '') . ' ' . ($plantilla->empleado->apellido ?? ''));

    // Agrupar paradas por cliente/domicilio para el mapa
    $gruposParadas = [];
    $grupoOrden = [];
    foreach ($plantilla->paradas as $parada) {
        $cliente = $parada->cliente ?? $parada->contrato?->cliente;
        $clienteId = $parada->cliente_id ?? $cliente?->id ?? ('p_' . $parada->id);
        $direccion = $cliente?->domicilio_completo
            ?? $parada->contrato?->cliente?->domicilio_completo
            ?? 'Sin dirección';
        $key = $clienteId . '_' . md5($direccion);
        if (!isset($gruposParadas[$key])) {
            $gruposParadas[$key] = [
                'cliente' => $cliente,
                'direccion' => $direccion,
                'latitud' => $cliente?->latitud,
                'longitud' => $cliente?->longitud,
                'paradas' => collect(),
            ];
            $grupoOrden[] = $key;
        }
        $gruposParadas[$key]['paradas']->push($parada);
    }
@endphp

<div class="container-fluid py-3 px-4" style="max-width: 1600px;">
    <a href="{{ route('rutas.index') }}" class="d-inline-block mb-3 text-decoration-none" style="color: #79481D;">
        <i class="bi bi-arrow-left me-1"></i> Regresar
    </a>

    @if ($message = Session::get('success'))
        <div class="alert alert-success mb-3 py-2">
            <p class="mb-0">{{ $message }}</p>
        </div>
    @endif

    <div class="row g-3">
        <div class="col-lg-6 col-xl-5">
            <div class="page-header mb-3">
                <div class="header-identity">
                    <div class="header-icon">
                        <i class="fa-solid fa-route"></i>
                    </div>
                    <div class="header-text min-width-0">
                        <h1 class="page-title">{{ $plantilla->nombre ?: ('Ruta #' . $plantilla->id) }}</h1>
                        <p class="page-subtitle">
                            <i class="bi {{ $freqIcon }}"></i>
                            <span>{{ $plantilla->etiquetaFrecuencia() }}</span>
                            <span class="badge {{ $plantilla->activa ? 'bg-success' : 'bg-secondary' }}">
                                {{ $plantilla->activa ? 'Activa' : 'Inactiva' }}
                            </span>
                        </p>
                    </div>
                </div>
                <div class="header-actions">
                    <button type="button" class="btn btn-header-action btn-header-ejecuciones" data-bs-toggle="modal" data-bs-target="#ejecucionesModal" title="Ejecuciones">
                        <i class="bi bi-calendar3"></i>
                        <span class="btn-header-label">Ejecuciones</span>
                        <span class="badge">{{ $plantilla->ejecuciones->count() }}</span>
                    </button>
                    @if(auth()->user()->role === 'admin')
                        <form method="POST" action="{{ route('rutas.plantillas.toggleActiva', $plantilla) }}" class="header-action-form">
                            @csrf
                            <button type="submit"
                                class="btn btn-header-action {{ $plantilla->activa ? 'btn-header-desactivar' : 'btn-header-activar' }}"
                                title="{{ $plantilla->activa ? 'Desactivar' : 'Activar' }}">
                                @if($plantilla->activa)
                                    <i class="bi bi-pause-circle"></i>
                                    <span class="btn-header-label">Desactivar</span>
                                @else
                                    <i class="bi bi-play-circle"></i>
                                    <span class="btn-header-label">Activar</span>
                                @endif
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            @if(auth()->user()->role === 'admin')
                <div class="panel-card panel-empleado mb-3">
                    <form method="POST" action="{{ route('rutas.plantillas.actualizarEmpleado', $plantilla) }}" id="formEmpleadoPlantilla" class="empleado-assign-form">
                        @csrf
                        <label class="empleado-assign-label" for="plantillaEmpleadoSearch">
                            <i class="bi bi-person"></i> Empleado asignado
                        </label>
                        <div class="empleado-combobox position-relative" id="plantillaEmpleadoCombobox">
                            <div class="empleado-assign-input">
                                <i class="bi bi-search empleado-assign-search-icon"></i>
                                <input type="text"
                                    id="plantillaEmpleadoSearch"
                                    class="form-control"
                                    placeholder="Buscar empleado por nombre o ID..."
                                    autocomplete="off"
                                    role="combobox"
                                    aria-expanded="false"
                                    value="{{ $empNombre !== '' ? $empNombre . ' (' . $plantilla->empleado_id . ')' : '' }}">
                                <input type="hidden" name="empleado_id" id="plantillaEmpleadoId" value="{{ $plantilla->empleado_id }}">
                            </div>
                            <ul class="empleado-combobox-list list-unstyled mb-0 shadow-sm" id="plantillaEmpleadoList" hidden>
                                @foreach($empleados as $empleado)
                                    <li>
                                        <button type="button"
                                            class="empleado-combobox-item {{ $plantilla->empleado_id == $empleado->id ? 'is-selected' : '' }}"
                                            data-id="{{ $empleado->id }}"
                                            data-label="{{ $empleado->nombre }} {{ $empleado->apellido }} ({{ $empleado->id }})">
                                            <i class="bi bi-person me-2"></i>{{ $empleado->nombre }} {{ $empleado->apellido }}
                                            <small class="text-muted">({{ $empleado->id }})</small>
                                        </button>
                                    </li>
                                @endforeach
                                <li class="empleado-combobox-empty px-3 py-2 text-muted small" style="display: none;">Sin resultados</li>
                            </ul>
                        </div>
                    </form>
                </div>
            @else
                <div class="panel-card panel-empleado mb-3">
                    <div class="empleado-assign-label">
                        <i class="bi bi-person"></i> Empleado asignado
                    </div>
                    <div class="empleado-assign-readonly">
                        <i class="bi bi-person"></i>
                        <span>{{ $empNombre !== '' ? $empNombre : 'Sin asignar' }}</span>
                    </div>
                </div>
            @endif

            <div class="card border-0 shadow-sm rounded-4 paradas-molde-card">
                <div class="card-body p-0">
                    <div class="px-3 py-2 border-bottom d-flex justify-content-between align-items-center paradas-molde-head">
                        <span class="fw-bold small" style="color: #79481D;">
                            <i class="bi bi-list-ol me-1"></i> Paradas del molde
                        </span>
                        <span class="text-muted small">{{ $plantilla->paradas->count() }} en total</span>
                    </div>

                    @if($plantilla->paradas->isEmpty())
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-geo-alt" style="font-size: 2rem;"></i>
                            <p class="mt-2 small mb-0">Este molde no tiene paradas.</p>
                        </div>
                    @else
                        <div id="lista-paradas" class="list-group list-group-flush" style="max-height: calc(100vh - 420px); overflow-y: auto;">
                            @foreach($grupoOrden as $key)
                                @php
                                    $grupo = $gruposParadas[$key];
                                    $paradasGrupo = $grupo['paradas'];
                                    $primerOrden = $paradasGrupo->first()->orden;
                                    $cliente = $grupo['cliente'];
                                    $nombreCliente = $cliente
                                        ? trim(($cliente->nombre ?? '') . ' ' . ($cliente->apellido ?? ''))
                                        : 'Cliente';
                                    $multiBadge = $paradasGrupo->count() > 1
                                        ? ' <span class="badge bg-secondary rounded-pill ms-1" style="font-size:0.6rem;">' . $paradasGrupo->count() . ' contratos</span>'
                                        : '';
                                @endphp
                                <div class="list-group-item parada-item px-3 py-2"
                                     data-lat="{{ $grupo['latitud'] }}"
                                     data-lng="{{ $grupo['longitud'] }}">
                                    <div class="d-flex align-items-start gap-2">
                                        <div class="parada-orden">{{ $primerOrden }}</div>
                                        <div class="flex-grow-1 min-width-0">
                                            <div class="d-flex justify-content-between align-items-start gap-2">
                                                <div>
                                                    <div class="fw-semibold small">{{ $nombreCliente }}{!! $multiBadge !!}</div>
                                                    <div class="text-muted" style="font-size: 0.75rem;">{{ $grupo['direccion'] }}</div>
                                                </div>
                                                @if($grupo['latitud'] && $grupo['longitud'])
                                                    <button type="button"
                                                        class="btn btn-sm btn-light border-0 p-1 centrar-mapa-btn flex-shrink-0"
                                                        data-lat="{{ $grupo['latitud'] }}"
                                                        data-lng="{{ $grupo['longitud'] }}"
                                                        title="Ver en mapa">
                                                        <i class="bi bi-map" style="color: #79481D;"></i>
                                                    </button>
                                                @endif
                                            </div>
                                            @foreach($paradasGrupo as $parada)
                                                <div class="mt-1">
                                                    <span class="badge bg-light text-dark fw-normal" style="font-size: 0.68rem;">
                                                        Contrato #{{ $parada->contrato_id }}
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-6 col-xl-7">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden sticky-map">
                <div id="map" style="height: calc(100vh - 140px); min-height: 420px; width: 100%; background: #e9ecef;"></div>
            </div>
        </div>
    </div>

</div>

<!-- Modal Ejecuciones -->
<div class="modal fade" id="ejecucionesModal" tabindex="-1" aria-labelledby="ejecucionesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0 align-items-start">
                <div class="pe-2">
                    <h5 class="modal-title fw-bold mb-1" id="ejecucionesModalLabel">
                        <i class="bi bi-calendar3 me-2" style="color: #79481D;"></i>Ejecuciones de Ruta
                    </h5>
                    <div class="text-muted small">
                        <span class="fw-semibold text-dark">{{ $plantilla->nombre ?: ('Ruta #' . $plantilla->id) }}</span>
                        <span class="mx-1">·</span>
                        <i class="bi {{ $freqIcon }}"></i> {{ $plantilla->etiquetaFrecuencia() }}
                        <span class="mx-1">·</span>
                        <i class="bi bi-person"></i> {{ $empNombre !== '' ? $empNombre : 'Sin asignar' }}
                    </div>
                    <div class="text-muted small mt-1">
                        <i class="bi bi-geo-alt"></i> {{ $plantilla->paradas->count() }} parada{{ $plantilla->paradas->count() === 1 ? '' : 's' }}
                        <span class="mx-1">·</span>
                        <i class="bi bi-house-door"></i> {{ $puntoCasaLabel }}
                        <span class="mx-1">·</span>
                        <span class="badge {{ $plantilla->activa ? 'bg-success' : 'bg-secondary' }}" style="font-size: 0.65rem;">
                            {{ $plantilla->activa ? 'Activa' : 'Inactiva' }}
                        </span>
                        <span class="mx-1">·</span>
                        {{ $plantilla->ejecuciones->count() }} ejecución(es)
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body pt-3 px-0 pb-0">

                @if($plantilla->ejecuciones->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-calendar-x" style="font-size: 2rem;"></i>
                        <p class="mt-2 small mb-0">Aún no hay rutas ejecutadas de este molde.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 ejecuciones-table">
                            <thead>
                                <tr>
                                    <th class="ps-3">Fecha</th>
                                    <th>Empleado</th>
                                    <th>Progreso</th>
                                    <th>Estado</th>
                                    <th class="pe-3 text-end">Ver</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($plantilla->ejecuciones as $ejecucion)
                                    @php
                                        $empEjec = trim(($ejecucion->empleado->nombre ?? '') . ' ' . ($ejecucion->empleado->apellido ?? ''));
                                        $totalParadas = (int) $ejecucion->paradas_count;
                                        $visitadas = (int) $ejecucion->paradas_visitadas_count;
                                        $porcentaje = $totalParadas > 0 ? round(($visitadas / $totalParadas) * 100) : 0;
                                        if ($ejecucion->fecha) {
                                            $f = $ejecucion->fecha->copy()->locale('es');
                                            $fechaLabel = ucfirst($f->isoFormat('dddd')) . ' ' . $f->day . ' de ' . ucfirst($f->isoFormat('MMMM')) . ' de ' . $f->year;
                                        } else {
                                            $fechaLabel = '—';
                                        }
                                    @endphp
                                    <tr class="ejecucion-row" role="button" tabindex="0"
                                        onclick="window.location='{{ route('rutas.show', $ejecucion->id) }}'">
                                        <td class="ps-3">
                                            <div class="fw-semibold">{{ $fechaLabel }}</div>
                                            <small class="text-muted">#{{ $ejecucion->id }}</small>
                                        </td>
                                        <td>
                                            <span class="small">{{ $empEjec !== '' ? $empEjec : 'Sin asignar' }}</span>
                                        </td>
                                        <td style="min-width: 140px;">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="progress flex-grow-1" style="height: 8px; border-radius: 4px;">
                                                    <div class="progress-bar" role="progressbar"
                                                         style="width: {{ $porcentaje }}%; background: linear-gradient(135deg, #E1B240 0%, #79481D 100%); border-radius: 4px;"></div>
                                                </div>
                                                <small class="text-muted flex-shrink-0">{{ $visitadas }}/{{ $totalParadas }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge {{ $ejecucion->estado_badge }}">{{ $ejecucion->estado_label }}</span>
                                        </td>
                                        <td class="pe-3 text-end">
                                            <i class="bi bi-chevron-right text-muted"></i>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var map = L.map('map').setView([20.67, -103.36], 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OSM',
        maxZoom: 19
    }).addTo(map);
    setTimeout(function() { map.invalidateSize(); }, 300);
    setTimeout(function() { map.invalidateSize(); }, 800);

    var points = [];
    var routeCoords = [];

    function makeIcon(bg, sz, txt) {
        return L.divIcon({
            className: 'custom-marker',
            html: '<div style="background:' + bg + ';width:' + sz + 'px;height:' + sz + 'px;border-radius:50%;border:2px solid white;box-shadow:0 2px 6px rgba(0,0,0,0.3);display:flex;align-items:center;justify-content:center;"><span style="color:white;font-size:' + Math.round(sz * 0.4) + 'px;font-weight:bold;">' + txt + '</span></div>',
            iconSize: [sz, sz],
            iconAnchor: [sz / 2, sz / 2]
        });
    }

    var homeLatLng = null;
    @if($plantilla->empleado && $plantilla->empleado->latitud && $plantilla->empleado->longitud)
        homeLatLng = [{{ $plantilla->empleado->latitud }}, {{ $plantilla->empleado->longitud }}];
        var casaLabel = @json($puntoCasaLabel);
        var empM = L.marker(homeLatLng, { icon: makeIcon('#28a745', 34, 'H'), zIndexOffset: 1000 }).addTo(map);
        empM.bindPopup('<div style="min-width:140px"><strong style="color:#28a745">' + casaLabel + '</strong><br><strong>{{ addslashes($empNombre) }}</strong><br><small>{{ addslashes($plantilla->empleado->domicilio ?? '') }}</small></div>');
        points.push(homeLatLng);
    @endif

    @php $marcadorIdx = 0; @endphp
    @foreach($grupoOrden as $key)
        @php
            $grupo = $gruposParadas[$key];
            $lat = $grupo['latitud'];
            $lng = $grupo['longitud'];
            if (!$lat || !$lng) {
                continue;
            }
            $marcadorIdx++;
            $nombre = addslashes(trim(($grupo['cliente']?->nombre ?? '') . ' ' . ($grupo['cliente']?->apellido ?? '')) ?: 'Cliente');
            $direccion = addslashes($grupo['direccion']);
            $totalGrupo = $grupo['paradas']->count();
            $popupExtra = $totalGrupo > 1 ? '<br><small>' . $totalGrupo . ' contratos</small>' : '';
            $ordenLabel = $grupo['paradas']->first()->orden;
        @endphp
        (function() {
            var m = L.marker([{{ $lat }}, {{ $lng }}], {
                icon: makeIcon('#79481D', 28, '{{ $marcadorIdx }}'),
                zIndexOffset: 500
            }).addTo(map);
            m.bindPopup('<div style="min-width:170px"><strong style="color:#79481D">#{{ $ordenLabel }} — {{ $nombre }}</strong><br><small>{{ $direccion }}</small>{{ $popupExtra }}</div>');
            points.push([{{ $lat }}, {{ $lng }}]);
            routeCoords.push([{{ $lat }}, {{ $lng }}]);
        })();
    @endforeach

    if (homeLatLng) {
        var puntoCasaRuta = @json($puntoCasa);
        if (puntoCasaRuta === 'inicio' || puntoCasaRuta === 'ambos') routeCoords.unshift(homeLatLng);
        if (puntoCasaRuta === 'final' || puntoCasaRuta === 'ambos') routeCoords.push(homeLatLng);
    }

    if (routeCoords.length > 1) {
        L.polyline(routeCoords, { color: '#79481D', weight: 3, opacity: 0.7, dashArray: '10,8' }).addTo(map);
    }
    if (points.length > 0) {
        map.fitBounds(points, { padding: [30, 30] });
    }

    document.querySelectorAll('.centrar-mapa-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var lat = parseFloat(this.getAttribute('data-lat'));
            var lng = parseFloat(this.getAttribute('data-lng'));
            if (lat && lng) map.setView([lat, lng], 16);
        });
    });

    document.querySelectorAll('.parada-item').forEach(function(item) {
        item.addEventListener('mouseenter', function() {
            this.style.borderLeft = '3px solid #79481D';
        });
        item.addEventListener('mouseleave', function() {
            this.style.borderLeft = '3px solid transparent';
        });
    });

    // Combobox empleado asignado
    var empSearch = document.getElementById('plantillaEmpleadoSearch');
    var empList = document.getElementById('plantillaEmpleadoList');
    var empIdInput = document.getElementById('plantillaEmpleadoId');
    var empForm = document.getElementById('formEmpleadoPlantilla');
    var empEmpty = empList ? empList.querySelector('.empleado-combobox-empty') : null;
    var currentEmpId = empIdInput ? empIdInput.value : '';

    function openEmpList() {
        if (!empList || !empSearch) return;
        empList.hidden = false;
        empSearch.setAttribute('aria-expanded', 'true');
    }

    function closeEmpList() {
        if (!empList || !empSearch) return;
        empList.hidden = true;
        empSearch.setAttribute('aria-expanded', 'false');
    }

    function filterEmpList(query) {
        if (!empList) return;
        query = (query || '').toLowerCase().trim();
        var visible = 0;
        empList.querySelectorAll('.empleado-combobox-item').forEach(function(btn) {
            var text = btn.textContent.toLowerCase();
            var show = !query || text.indexOf(query) !== -1;
            btn.closest('li').style.display = show ? '' : 'none';
            if (show) visible++;
        });
        if (empEmpty) empEmpty.style.display = visible === 0 ? '' : 'none';
    }

    if (empSearch && empList && empForm) {
        empSearch.addEventListener('focus', function() {
            filterEmpList(empSearch.value);
            openEmpList();
        });

        empSearch.addEventListener('input', function() {
            filterEmpList(empSearch.value);
            openEmpList();
        });

        empSearch.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeEmpList();
                return;
            }
            if (e.key === 'Enter') {
                e.preventDefault();
                var first = empList.querySelector('.empleado-combobox-item:not([style*="display: none"])');
                // visible items have display '' on li
                var candidates = Array.prototype.filter.call(
                    empList.querySelectorAll('.empleado-combobox-item'),
                    function(btn) { return btn.closest('li').style.display !== 'none'; }
                );
                if (candidates[0]) candidates[0].click();
            }
        });

        empList.addEventListener('click', function(e) {
            var btn = e.target.closest('.empleado-combobox-item');
            if (!btn) return;
            var id = btn.getAttribute('data-id');
            var label = btn.getAttribute('data-label');
            empSearch.value = label;
            empIdInput.value = id;
            empList.querySelectorAll('.empleado-combobox-item').forEach(function(b) {
                b.classList.toggle('is-selected', b === btn);
            });
            closeEmpList();
            if (String(id) !== String(currentEmpId)) {
                empForm.submit();
            }
        });

        document.addEventListener('click', function(e) {
            var wrap = document.getElementById('plantillaEmpleadoCombobox');
            if (wrap && !wrap.contains(e.target)) closeEmpList();
        });
    }
});
</script>

<style>
    .page-header,
    .panel-card {
        background: white;
        border-radius: 16px;
        padding: 1rem 1.15rem;
        border: 1px solid #e5e7eb;
    }
    .page-header {
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }
    .header-identity {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
        flex: 1 1 0;
    }
    .header-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 0.4rem;
        flex: 0 0 auto;
        margin-left: auto;
    }
    .header-action-form {
        display: inline-flex;
        margin: 0;
    }
    .header-icon {
        width: 42px; height: 42px; border-radius: 12px;
        background: linear-gradient(135deg, rgba(225, 178, 64, 0.22) 0%, rgba(121, 72, 29, 0.16) 100%);
        color: #79481D;
        display: flex; align-items: center; justify-content: center;
        font-size: 1rem; flex-shrink: 0;
    }
    .page-title {
        font-size: 1.15rem;
        font-weight: 700;
        color: #1f2937;
        margin: 0 0 0.2rem;
        line-height: 1.25;
        letter-spacing: -0.02em;
    }
    .page-subtitle {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.35rem 0.5rem;
        font-size: 0.78rem;
        color: #6b7280;
        margin: 0;
    }
    .page-subtitle .badge {
        font-size: 0.65rem;
        font-weight: 600;
        vertical-align: middle;
    }
    .btn-header-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.35rem;
        height: 34px;
        padding: 0 0.7rem;
        border-radius: 10px;
        font-size: 0.78rem;
        font-weight: 600;
        border: 1px solid transparent;
        white-space: nowrap;
        line-height: 1;
    }
    .btn-header-action .badge {
        font-size: 0.65rem;
        font-weight: 700;
        padding: 0.2em 0.45em;
        border-radius: 999px;
    }
    .btn-header-ejecuciones {
        background: rgba(121, 72, 29, 0.08);
        color: #79481D;
        border-color: rgba(121, 72, 29, 0.18);
    }
    .btn-header-ejecuciones .badge {
        background: #79481D;
        color: #fff;
    }
    .btn-header-ejecuciones:hover {
        background: rgba(121, 72, 29, 0.14);
        color: #79481D;
    }
    .btn-header-desactivar {
        background: #fff;
        color: #6b7280;
        border-color: #e5e7eb;
    }
    .btn-header-desactivar:hover {
        background: #f9fafb;
        color: #374151;
        border-color: #d1d5db;
    }
    .btn-header-activar {
        background: rgba(25, 135, 84, 0.08);
        color: #198754;
        border-color: rgba(25, 135, 84, 0.22);
    }
    .btn-header-activar:hover {
        background: rgba(25, 135, 84, 0.14);
        color: #146c43;
    }

    .empleado-assign-form { margin: 0; }
    .empleado-assign-label {
        display: flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.72rem;
        font-weight: 600;
        color: #6b7280;
        margin-bottom: 0.4rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    .empleado-assign-input {
        position: relative;
        display: flex;
        align-items: center;
    }
    .empleado-assign-search-icon {
        position: absolute;
        left: 0.75rem;
        color: #9ca3af;
        font-size: 0.85rem;
        pointer-events: none;
        z-index: 1;
    }
    .empleado-assign-input .form-control {
        padding-left: 2.1rem;
        height: 38px;
        border-radius: 10px;
        border-color: #e5e7eb;
        font-size: 0.88rem;
        background: #fafafa;
    }
    .empleado-assign-input .form-control:focus {
        background: #fff;
        border-color: rgba(121, 72, 29, 0.45);
        box-shadow: 0 0 0 3px rgba(121, 72, 29, 0.1);
    }
    .empleado-assign-readonly {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.88rem;
        font-weight: 600;
        color: #374151;
    }
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
        border-radius: 10px;
        margin-top: 4px;
    }
    .empleado-combobox-item {
        display: block;
        width: 100%;
        text-align: left;
        background: none;
        border: none;
        padding: 0.5rem 0.85rem;
        font-size: 0.88rem;
    }
    .empleado-combobox-item:hover,
    .empleado-combobox-item:focus,
    .empleado-combobox-item.is-selected {
        background: #f8f7f5;
    }

    @media (max-width: 420px) {
        .btn-header-label { display: none; }
        .btn-header-action { width: 34px; padding: 0; }
        .btn-header-ejecuciones .badge {
            position: absolute;
            top: -4px;
            right: -4px;
        }
        .btn-header-ejecuciones { position: relative; }
    }

    .parada-orden {
        width: 32px; height: 32px; border-radius: 50%;
        background: linear-gradient(135deg, #E1B240 0%, #79481D 100%);
        color: #fff; font-weight: 700; font-size: 0.8rem;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .min-width-0 { min-width: 0; }

    #map { border-radius: 0 0 16px 16px; }
    .custom-marker { background: transparent !important; border: none !important; }
    .leaflet-popup-content-wrapper { border-radius: 12px !important; box-shadow: 0 4px 16px rgba(0,0,0,0.15) !important; }
    .leaflet-popup-content { margin: 10px 14px !important; font-size: 0.85rem !important; }

    .sticky-map { position: sticky; top: 70px; }

    .paradas-molde-card {
        background: #f8f7f5;
        border: 1px solid #eee8df !important;
        overflow: hidden;
    }
    .paradas-molde-head {
        background: #f3f0eb;
        border-bottom-color: #eee8df !important;
    }
    #lista-paradas {
        background: #f8f7f5;
    }
    #lista-paradas .parada-item {
        background: transparent;
        border-color: #eee8df;
        transition: all 0.2s ease;
        border-left: 3px solid transparent;
    }
    #lista-paradas .parada-item:hover {
        background: #efeae3;
        border-left-color: #79481D;
    }

    .ejecuciones-table thead th {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        color: #6b7280;
        font-weight: 600;
        border-bottom-color: #eee8df;
        background: #faf9f7;
        padding-top: 0.65rem;
        padding-bottom: 0.65rem;
    }
    .ejecuciones-table td {
        font-size: 0.875rem;
        border-bottom-color: #f1f3f5;
    }
    .ejecucion-row { cursor: pointer; transition: background 0.15s ease; }
    .ejecucion-row:hover { background: #f8f7f5; }

    @media (max-width: 991.98px) {
        .sticky-map { position: static; }
        #map { height: 350px !important; min-height: 300px !important; }
        #lista-paradas { max-height: none !important; }
    }
</style>
@endsection
