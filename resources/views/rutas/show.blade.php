@extends('layouts.app')

@section('template_title')
    Ruta #{{ $ruta->id }}
@endsection

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<div class="container-fluid py-3 px-4" style="max-width: 1600px;">
    <a href="{{ route('rutas.index') }}" class="d-inline-block mb-3 text-decoration-none" style="color: #79481D;">
        <i class="bi bi-arrow-left me-1"></i> Regresar
    </a>

    @if ($message = Session::get('success'))
        <div class="alert alert-success mb-3 py-2">
            <p class="mb-0">{{ $message }}</p>
        </div>
    @endif

    <!-- Header compacto -->
    <div class="page-header mb-3">
        <div class="header-content">
            <div class="header-icon">
                <i class="fa-solid fa-route"></i>
            </div>
            <div class="header-text">
                <h1 class="page-title">Ruta #{{ $ruta->id }}</h1>
                <p class="page-subtitle">
                    {{ $ruta->empleado->nombre }} {{ $ruta->empleado->apellido }}
                    &mdash; {{ $ruta->fecha->format('d/m/Y') }}
                    &mdash; <span class="badge {{ $ruta->estado_badge }}">{{ $ruta->estado_label }}</span>
                </p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            @if($ruta->notas)
                <span class="text-muted small" title="{{ $ruta->notas }}"><i class="bi bi-chat-text me-1"></i>Notas</span>
            @endif
            @if(auth()->user()->role === 'admin')
                <a href="{{ route('rutas.edit', $ruta->id) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-pencil me-1"></i> Editar
                </a>
                <form method="POST" action="{{ route('rutas.destroy', $ruta->id) }}" class="d-inline" onsubmit="return confirm('¿Eliminar esta ruta del sistema? Esta acción no se puede deshacer.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-trash me-1"></i> Eliminar
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Progreso compacto -->
    <!-- Layout: mapa + lista -->
    @php
        // Agrupar paradas por cliente_id + direccion_destino
        $gruposParadas = [];
        $grupoOrden = [];
        foreach ($ruta->paradas as $parada) {
            $key = $parada->cliente_id . '_' . md5($parada->direccion_destino);
            if (!isset($gruposParadas[$key])) {
                $gruposParadas[$key] = [
                    'cliente' => $parada->cliente,
                    'direccion' => $parada->direccion_destino,
                    'latitud' => $parada->latitud,
                    'longitud' => $parada->longitud,
                    'paradas' => collect(),
                ];
                $grupoOrden[] = $key;
            }
            $gruposParadas[$key]['paradas']->push($parada);
        }

        // Progreso basado en domicilios únicos, no en contratos individuales
        $total = count($gruposParadas);
        $completadas = 0;
        $omitidas = 0;
        foreach ($gruposParadas as $grupo) {
            $paradasGrupo = $grupo['paradas'];
            $totalGrupo = $paradasGrupo->count();
            $completadasGrupo = $paradasGrupo->where('estado', 'visitada')->count();
            $omitidasGrupo = $paradasGrupo->where('estado', 'omitida')->count();
            if ($completadasGrupo === $totalGrupo) $completadas++;
            elseif ($omitidasGrupo === $totalGrupo) $omitidas++;
        }
        $porcentaje = $total > 0 ? round(($completadas / $total) * 100) : 0;
    @endphp
    <div class="row g-3">
        <!-- Mapa -->
        <div class="col-lg-6 col-xl-7">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden sticky-map">
                <div id="map" style="height: calc(100vh - 260px); min-height: 450px; width: 100%; background: #e9ecef;"></div>
            </div>
        </div>

        <!-- Lista de paradas -->
        <div class="col-lg-6 col-xl-5">
            <!-- Tarjeta de progreso -->
            <div class="card border-0 shadow-sm rounded-4 mb-3">
                <div class="card-body py-2 px-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="fw-bold small" style="color: #79481D;">
                            <i class="bi bi-geo-alt me-1"></i> Domicilios ({{ $total }})
                        </span>
                        <span class="small fw-bold" style="color: #79481D;">{{ $completadas }}/{{ $total }} visitados @if($omitidas > 0)<span class="text-danger">({{ $omitidas }} omitido(s))</span>@endif</span>
                    </div>
                    <div class="progress" style="height: 8px; border-radius: 4px;">
                        <div class="progress-bar" role="progressbar" style="width: {{ $porcentaje }}%; background: linear-gradient(135deg, #E1B240 0%, #79481D 100%); border-radius: 4px;">{{ $porcentaje }}%</div>
                    </div>
                </div>
            </div>

            <!-- Tarjeta de lista -->
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-0">
                    @if($ruta->paradas->isEmpty())
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-geo-alt" style="font-size: 2rem;"></i>
                            <p class="mt-2 small">No hay paradas.</p>
                        </div>
                    @else
                        <div id="lista-paradas" class="list-group list-group-flush" style="max-height: calc(100vh - 310px); overflow-y: auto;">
                            @php $domIdx = 0; @endphp
                            @foreach($grupoOrden as $key)
                                @php
                                    $grupo = $gruposParadas[$key];
                                    $domIdx++;
                                    $paradasGrupo = $grupo['paradas'];
                                    $totalGrupo = $paradasGrupo->count();
                                    $completadasGrupo = $paradasGrupo->where('estado', 'visitada')->count();
                                    $omitidasGrupo = $paradasGrupo->where('estado', 'omitida')->count();
                                    $primerOrden = $paradasGrupo->first()->orden;
                                    $multiBadge = $totalGrupo > 1 ? ' <span class="badge bg-secondary rounded-pill ms-1" style="font-size:0.6rem;">' . $totalGrupo . ' contratos</span>' : '';
                                @endphp
                                <div class="list-group-item parada-item px-3 py-2" data-parada-id="{{ $paradasGrupo->first()->id }}" data-lat="{{ $grupo['latitud'] }}" data-lng="{{ $grupo['longitud'] }}">
                                    <div class="d-flex align-items-start gap-2">
                                        <!-- Número -->
                                        <div class="d-flex align-items-center justify-content-center rounded-circle fw-bold text-white flex-shrink-0"
                                             style="width: 32px; height: 32px; background: {{ $completadasGrupo === $totalGrupo ? '#28a745' : ($omitidasGrupo === $totalGrupo ? '#dc3545' : 'linear-gradient(135deg, #E1B240 0%, #79481D 100%)') }}; font-size: 0.8rem;">
                                            {{ $primerOrden }}
                                        </div>

                                        <!-- Contenido -->
                                        <div class="flex-grow-1 min-width-0">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <div class="fw-semibold small">{{ $grupo['cliente']->nombre }} {{ $grupo['cliente']->apellido }}{!! $multiBadge !!}</div>
                                                    <div class="text-muted" style="font-size: 0.75rem;">{{ $grupo['direccion'] }}</div>
                                                </div>
                                                <div class="d-flex align-items-center gap-1 flex-shrink-0">
                                                    @if($totalGrupo > 1)
                                                        <span class="badge bg-light text-dark" style="font-size: 0.65rem;">{{ $completadasGrupo }}/{{ $totalGrupo }}</span>
                                                    @else
                                                        <span class="badge {{ $paradasGrupo->first()->estado_badge }}" style="font-size: 0.7rem;">{{ $paradasGrupo->first()->estado_label }}</span>
                                                    @endif

                                                    <!-- Dropdown acciones -->
                                                    <div class="dropdown">
                                                        <button class="btn btn-sm btn-light border-0 p-1" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="border-radius: 6px; line-height: 1;">
                                                            <i class="bi bi-three-dots" style="font-size: 1rem; color: #6b7280;"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end shadow" style="min-width: 180px; border-radius: 10px; border: 1px solid #e5e7eb;">
                                                            @if($grupo['latitud'] && $grupo['longitud'])
                                                                <li>
                                                                    <button type="button" class="dropdown-item py-2 centrar-mapa-btn" data-lat="{{ $grupo['latitud'] }}" data-lng="{{ $grupo['longitud'] }}">
                                                                        <i class="bi bi-map text-primary me-2"></i> Ver en mapa
                                                                    </button>
                                                                </li>
                                                                <li><hr class="dropdown-divider my-1"></li>
                                                            @endif

                                                            @foreach($paradasGrupo as $parada)
                                                                <li class="px-2 py-1">
                                                                    <div class="d-flex align-items-center justify-content-between">
                                                                        <span class="small fw-semibold">C#{{ $parada->contrato_id }}</span>
                                                                        <span class="badge {{ $parada->estado_badge }}" style="font-size: 0.6rem;">{{ $parada->estado_label }}</span>
                                                                    </div>
                                                                    <div class="d-flex gap-1 mt-1">
                                                                        @if($parada->estado === 'pendiente')
                                                                            <form method="POST" action="{{ route('rutas.actualizarEstadoParada', [$ruta->id, $parada->id]) }}" class="d-inline">
                                                                                @csrf
                                                                                <input type="hidden" name="estado" value="visitada">
                                                                                <button type="submit" class="btn btn-sm btn-outline-success py-0" style="font-size: 0.65rem;" title="Marcar visitada">
                                                                                    <i class="bi bi-check-lg"></i>
                                                                                </button>
                                                                            </form>
                                                                            <form method="POST" action="{{ route('rutas.actualizarEstadoParada', [$ruta->id, $parada->id]) }}" class="d-inline">
                                                                                @csrf
                                                                                <input type="hidden" name="estado" value="omitida">
                                                                                <button type="submit" class="btn btn-sm btn-outline-danger py-0" style="font-size: 0.65rem;" title="Omitir">
                                                                                    <i class="bi bi-x-lg"></i>
                                                                                </button>
                                                                            </form>
                                                                        @else
                                                                            <form method="POST" action="{{ route('rutas.actualizarEstadoParada', [$ruta->id, $parada->id]) }}" class="d-inline">
                                                                                @csrf
                                                                                <input type="hidden" name="estado" value="pendiente">
                                                                                <button type="submit" class="btn btn-sm btn-outline-secondary py-0" style="font-size: 0.65rem;" title="Revertir">
                                                                                    <i class="bi bi-arrow-counterclockwise"></i>
                                                                                </button>
                                                                            </form>
                                                                        @endif
                                                                        @if($parada->estado !== 'visitada')
                                                                            <button type="button" class="btn btn-sm btn-outline-warning py-0" style="font-size: 0.65rem;" data-bs-toggle="modal" data-bs-target="#visitaModal"
                                                                                data-contrato-id="{{ $parada->contrato_id }}"
                                                                                data-parada-id="{{ $parada->id }}"
                                                                                data-cliente="{{ $grupo['cliente']->nombre }} {{ $grupo['cliente']->apellido }}" title="Registrar visita">
                                                                                <i class="bi bi-camera"></i>
                                                                            </button>
                                                                        @endif
                                                                    </div>
                                                                </li>
                                                                @if(!$loop->last)
                                                                    <li><hr class="dropdown-divider my-1"></li>
                                                                @endif
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                            @if($totalGrupo === 1)
                                                @php $parada = $paradasGrupo->first(); @endphp
                                                <div class="d-flex gap-3 mt-1 flex-wrap" style="font-size: 0.75rem;">
                                                    <span><strong>${{ number_format($parada->contrato->monto_cuota_real, 2) }}</strong> cuota</span>
                                                    @php
                                                        $pagosHechos = $parada->contrato->pagos->where('estado', 'hecho');
                                                        $abonoPromedio = $pagosHechos->count() > 0 ? $pagosHechos->avg('monto') : 0;
                                                    @endphp
                                                    @if($abonoPromedio > 0)
                                                        <span class="text-success"><strong>${{ number_format($abonoPromedio, 2) }}</strong> prom</span>
                                                    @endif
                                                    @if($parada->contrato->saldo_pendiente > 0)
                                                        <span class="text-danger"><strong>${{ number_format($parada->contrato->saldo_pendiente, 2) }}</strong> saldo</span>
                                                    @endif
                                                    @if($parada->notas)
                                                        <span class="text-muted"><i class="bi bi-chat-dots"></i> {{ $parada->notas }}</span>
                                                    @endif
                                                </div>
                                            @else
                                                <div class="mt-1" style="font-size: 0.72rem;">
                                                    @foreach($paradasGrupo as $parada)
                                                        <div class="d-flex align-items-center gap-2 {{ !$loop->last ? 'mb-1' : '' }}" style="padding-left: 4px; border-left: 2px solid {{ $parada->estado === 'visitada' ? '#28a745' : ($parada->estado === 'omitida' ? '#dc3545' : '#e9ecef') }};">
                                                            <span class="text-muted">C#{{ $parada->contrato_id }}</span>
                                                            <span><strong>${{ number_format($parada->contrato->monto_cuota_real, 2) }}</strong></span>
                                                            @if($parada->contrato->saldo_pendiente > 0)
                                                                <span class="text-danger">${{ number_format($parada->contrato->saldo_pendiente, 2) }}</span>
                                                            @endif
                                                            <span class="badge {{ $parada->estado_badge }}" style="font-size: 0.55rem;">{{ $parada->estado_label }}</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Registrar Visita -->
<div class="modal fade" id="visitaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0" style="background: linear-gradient(135deg, #E1B240 0%, #79481D 100%); color: white;">
                <h5 class="modal-title">
                    <i class="bi bi-camera me-2"></i>Registrar Visita
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('visitas.store') }}" id="visitaForm">
                @csrf
                <input type="hidden" name="contrato_id" id="visita_contrato_id">
                <input type="hidden" name="user_id" value="{{ auth()->id() }}">
                <input type="hidden" name="ruta_parada_id" id="visita_parada_id">
                <input type="hidden" name="ubicacion_evidencia" id="ubicacion_evidencia">
                <div class="modal-body p-4">
                    <p class="mb-3">Cliente: <strong id="visita_cliente_nombre"></strong></p>
                    <div id="locationStatus" class="alert alert-info d-none mb-3"></div>
                    <div id="locationAlert" class="alert alert-danger d-none mb-3">
                        <span id="locationAlertText"></span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Comentarios</label>
                        <textarea name="comentarios" class="form-control" rows="3" placeholder="Observaciones de la visita..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn text-white fw-bold" style="background: linear-gradient(135deg, #E1B240 0%, #79481D 100%);" id="btnGuardarVisita" disabled>
                        <i class="bi bi-check-lg me-1"></i> Guardar Visita
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mapa
    var map = L.map('map').setView([20.67, -103.36], 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OSM', maxZoom: 19 }).addTo(map);
    setTimeout(function() { map.invalidateSize(); }, 300);
    setTimeout(function() { map.invalidateSize(); }, 800);

    var points = [];
    var routeCoords = [];

    function makeIcon(bg, sz, txt) {
        return L.divIcon({
            className: 'custom-marker',
            html: '<div style="background:' + bg + ';width:' + sz + 'px;height:' + sz + 'px;border-radius:50%;border:2px solid white;box-shadow:0 2px 6px rgba(0,0,0,0.3);display:flex;align-items:center;justify-content:center;"><span style="color:white;font-size:' + Math.round(sz * 0.4) + 'px;font-weight:bold;">' + txt + '</span></div>',
            iconSize: [sz, sz], iconAnchor: [sz / 2, sz / 2]
        });
    }

    // Empleado
    @if($ruta->empleado->latitud && $ruta->empleado->longitud)
        var empM = L.marker([{{ $ruta->empleado->latitud }}, {{ $ruta->empleado->longitud }}], { icon: makeIcon('#28a745', 34, 'H'), zIndexOffset: 1000 }).addTo(map);
        empM.bindPopup('<div style="min-width:140px"><strong style="color:#28a745">Inicio</strong><br><strong>{{ addslashes($ruta->empleado->nombre) }} {{ addslashes($ruta->empleado->apellido) }}</strong><br><small>{{ addslashes($ruta->empleado->domicilio ?? "") }}</small></div>');
        points.push([{{ $ruta->empleado->latitud }}, {{ $ruta->empleado->longitud }}]);
        routeCoords.push([{{ $ruta->empleado->latitud }}, {{ $ruta->empleado->longitud }}]);
    @endif

    // Paradas (un marcador por domicilio único)
    @php
        $marcadoresMostrados = [];
        $marcadorIdx = 0;
    @endphp
    @foreach($grupoOrden as $key)
        @php
            $grupo = $gruposParadas[$key];
            $lat = $grupo['latitud'];
            $lng = $grupo['longitud'];
            $coordKey = round($lat, 6) . ',' . round($lng, 6);
            if (!$lat || !$lng || isset($marcadoresMostrados[$coordKey])) continue;
            $marcadoresMostrados[$coordKey] = true;
            $marcadorIdx++;
            $paradasGrupo = $grupo['paradas'];
            $completadasGrupo = $paradasGrupo->where('estado', 'visitada')->count();
            $omitidasGrupo = $paradasGrupo->where('estado', 'omitida')->count();
            $totalGrupo = $paradasGrupo->count();
            $color = $completadasGrupo === $totalGrupo ? '#28a745' : ($omitidasGrupo === $totalGrupo ? '#dc3545' : '#79481D');
            $nombre = addslashes($grupo['cliente']->nombre . ' ' . $grupo['cliente']->apellido);
            $direccion = addslashes($grupo['direccion']);
            $popupExtra = $totalGrupo > 1 ? '<br><small class="text-muted">' . $totalGrupo . ' contratos</small>' : '';
        @endphp
        (function() {
            var m = L.marker([{{ $lat }}, {{ $lng }}], { icon: makeIcon('{{ $color }}', 28, '{{ $marcadorIdx }}'), zIndexOffset: 500 }).addTo(map);
            m.bindPopup('<div style="min-width:170px"><strong style="color:#79481D">#{{ $marcadorIdx }} — {{ $nombre }}</strong><br><small>{{ $direccion }}</small>{{ $popupExtra }}<br><span class="badge {{ $completadasGrupo === $totalGrupo ? 'bg-success' : ($omitidasGrupo === $totalGrupo ? 'bg-danger' : 'bg-secondary') }}">{{ $completadasGrupo }}/{{ $totalGrupo }} completadas</span></div>');
            points.push([{{ $lat }}, {{ $lng }}]);
            routeCoords.push([{{ $lat }}, {{ $lng }}]);
        })();
    @endforeach

    if (routeCoords.length > 1) {
        L.polyline(routeCoords, { color: '#79481D', weight: 3, opacity: 0.7, dashArray: '10,8' }).addTo(map);
    }
    if (points.length > 0) map.fitBounds(points, { padding: [30, 30] });

    // Centrar mapa
    document.querySelectorAll('.centrar-mapa-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var lat = parseFloat(this.getAttribute('data-lat'));
            var lng = parseFloat(this.getAttribute('data-lng'));
            if (lat && lng) { map.setView([lat, lng], 16); }
        });
    });

    // Resaltar parada al hover
    document.querySelectorAll('.parada-item').forEach(function(item) {
        item.addEventListener('mouseenter', function() {
            var lat = parseFloat(this.getAttribute('data-lat'));
            var lng = parseFloat(this.getAttribute('data-lng'));
            if (lat && lng) { this.style.borderLeft = '3px solid #79481D'; }
        });
        item.addEventListener('mouseleave', function() {
            this.style.borderLeft = '3px solid transparent';
        });
    });

    // Modal visita
    var visitaModal = document.getElementById('visitaModal');
    visitaModal.addEventListener('show.bs.modal', function(event) {
        var button = event.relatedTarget;
        document.getElementById('visita_contrato_id').value = button.getAttribute('data-contrato-id');
        document.getElementById('visita_parada_id').value = button.getAttribute('data-parada-id');
        document.getElementById('visita_cliente_nombre').textContent = button.getAttribute('data-cliente');
        document.getElementById('btnGuardarVisita').disabled = true;
        document.getElementById('ubicacion_evidencia').value = '';
        document.getElementById('locationStatus').classList.add('d-none');
        document.getElementById('locationAlert').classList.add('d-none');

        if ("geolocation" in navigator) {
            var locationStatus = document.getElementById('locationStatus');
            locationStatus.classList.remove('d-none');
            locationStatus.innerHTML = '<div class="spinner-border spinner-border-sm me-2" role="status"></div>Obteniendo ubicación...';

            navigator.geolocation.getCurrentPosition(function(position) {
                var lat = position.coords.latitude;
                var lng = position.coords.longitude;
                document.getElementById('ubicacion_evidencia').value = 'POINT(' + lng + ' ' + lat + ')';
                document.getElementById('btnGuardarVisita').disabled = false;
                locationStatus.classList.remove('alert-info');
                locationStatus.classList.add('alert-success');
                locationStatus.innerHTML = '<i class="bi bi-check-circle me-2"></i>Ubicación: ' + lat.toFixed(6) + ', ' + lng.toFixed(6);
            }, function(error) {
                locationStatus.classList.add('d-none');
                document.getElementById('locationAlert').classList.remove('d-none');
                document.getElementById('locationAlertText').textContent = 'No se pudo obtener la ubicación.';
            }, { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 });
        }
    });
});
</script>

<style>
    .page-header {
        background: white;
        border-radius: 16px;
        padding: 1.25rem 1.5rem;
        border: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .header-content { display: flex; align-items: center; gap: 16px; }

    .page-title { font-size: 1.4rem; font-weight: 700; color: #1f2937; margin: 0; }
    .page-subtitle { font-size: 0.85rem; color: #6b7280; margin: 0; }

    #map { border-radius: 0 0 16px 16px; }
    .custom-marker { background: transparent !important; border: none !important; }
    .leaflet-popup-content-wrapper { border-radius: 12px !important; box-shadow: 0 4px 16px rgba(0,0,0,0.15) !important; }
    .leaflet-popup-content { margin: 10px 14px !important; font-family: 'Nunito', sans-serif !important; font-size: 0.85rem !important; }

    .sticky-map { position: sticky; top: 70px; }

    .parada-item {
        transition: all 0.2s ease;
        border-left: 3px solid transparent;
    }
    .parada-item:hover {
        background: #f8f9ff;
        border-left-color: #79481D;
    }
    .parada-item .dropdown .btn:hover {
        background: #e9ecef;
    }
    .parada-item .dropdown .btn:focus,
    .parada-item .dropdown .btn.show {
        background: #dee2e6;
    }
    .parada-item .dropdown-menu {
        padding: 0.25rem 0;
    }
    .parada-item .dropdown-item {
        font-size: 0.85rem;
        padding: 0.5rem 1rem;
        border-radius: 0;
    }
    .parada-item .dropdown-item:hover {
        background: #f0ebe3;
        color: #79481D;
    }

    .centrar-mapa-btn {
        background: none;
        border: none;
        width: 100%;
        text-align: left;
        padding: 0;
        font-size: 0.85rem;
    }
    .centrar-mapa-btn:hover {
        color: #79481D !important;
    }

    .min-width-0 { min-width: 0; }

    @media (max-width: 991.98px) {
        .sticky-map { position: static; }
        #map { height: 350px !important; min-height: 300px !important; }
        #lista-paradas { max-height: none !important; }
        .page-header { flex-direction: column; gap: 12px; text-align: center; }
    }
</style>
@endsection
