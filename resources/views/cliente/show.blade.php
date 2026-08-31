@extends('layouts.app')

@section('template_title')
    {{ $cliente->nombre ?? __('Show') . ' ' . __('Cliente') }}
@endsection

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        .hover-card {
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .hover-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }

        .client-avatar {
            position: relative;
        }

        .client-stat {
            transition: all 0.2s ease;
        }

        .hover-card:hover .client-stat {
            transform: scale(1.05);
        }

        .payment-card {
            transition: all 0.3s ease;
        }

        .payment-card:hover {
            transform: translateX(5px);
            box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.1) !important;
        }

        .status-badge {
            font-size: 0.875rem;
            padding: 0.5rem 1rem;
        }

        .timeline-item {
            position: relative;
        }

        .timeline-item:not(:last-child)::after {
            content: '';
            position: absolute;
            left: 1.5rem;
            top: 100%;
            width: 2px;
            height: 20px;
            background: #e9ecef;
        }

        /* Estilos para el header premium */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2.5rem;
            padding: 2rem;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.4);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 6px;
            height: 100%;
            background: linear-gradient(to bottom, #007bff, #00c6ff);
        }

        .header-content {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .page-title {
            font-size: 1.75rem;
            font-weight: 800;
            margin: 0;
            color: #1a2a3a;
            letter-spacing: -0.5px;
        }

        .page-subtitle {
            margin: 0.25rem 0 0;
            color: #6c757d;
            font-size: 1rem;
            opacity: 0.8;
        }

        .header-actions .status-badge {
            font-weight: 700;
            letter-spacing: 1px;
            padding: 0.6rem 1.2rem;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1.5rem;
                padding: 1.5rem;
            }
            .header-actions {
                width: 100%;
                text-align: left !important;
            }
            .text-md-end {
                text-align: left !important;
            }
        }

        .modern-link {
            color: #007bff;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s ease;
        }

        .modern-link:hover {
            color: #0056b3;
        }

        .contract-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .15) !important;
        }

        /* Estilos para filas clicables */
        .clickable-row {
            transition: all 0.2s ease;
        }

        .clickable-row:hover {
            background-color: rgba(0, 123, 255, 0.05) !important;
            transform: translateX(2px);
        }

        .clickable-row:hover td {
            border-left: 3px solid #007bff;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        function copyAddress() {
            const address = @json($cliente->domicilio_completo ?? 'Domicilio no disponible');

            navigator.clipboard.writeText(address).then(function() {
                // Cambiar el botón temporalmente para mostrar confirmación
                const btn = document.getElementById('copyAddressBtn');
                const originalContent = btn.innerHTML;

                btn.innerHTML = '<i class="bi bi-check me-1"></i>Copiado';
                btn.classList.remove('btn-outline-secondary');
                btn.classList.add('btn-success');

                setTimeout(function() {
                    btn.innerHTML = originalContent;
                    btn.classList.remove('btn-success');
                    btn.classList.add('btn-outline-secondary');
                }, 2000);
            }).catch(function(err) {
                console.error('Error al copiar: ', err);
                alert('No se pudo copiar la dirección');
            });
        }

        // Animar el chevron del acordeón
        document.addEventListener('DOMContentLoaded', function() {
            const historialCollapse = document.getElementById('historialFinanciero');
            const chevronIcon = document.getElementById('chevronIcon');

            historialCollapse.addEventListener('show.bs.collapse', function() {
                chevronIcon.classList.remove('bi-chevron-down');
                chevronIcon.classList.add('bi-chevron-up');
            });

            historialCollapse.addEventListener('hide.bs.collapse', function() {
                chevronIcon.classList.remove('bi-chevron-up');
                chevronIcon.classList.add('bi-chevron-down');
            });
        });

        // ==================== MAPA DEL CLIENTE ====================
        var clienteMap = null;
        var relocateMap = null;
        var relocateDebounce = null;
        var clienteLat = {{ $cliente->latitud ?? 'null' }};
        var clienteLng = {{ $cliente->longitud ?? 'null' }};

        document.addEventListener('DOMContentLoaded', function() {
            if (clienteLat && clienteLng && document.getElementById('clienteMap')) {
                clienteMap = L.map('clienteMap', { zoomControl: false, attributionControl: false, scrollWheelZoom: false, dragging: false }).setView([clienteLat, clienteLng], 16);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OSM', maxZoom: 19
                }).addTo(clienteMap);
                L.marker([clienteLat, clienteLng], {
                    icon: L.divIcon({
                        className: 'custom-marker',
                        html: '<div style="background:#79481D;width:28px;height:28px;border-radius:50%;border:3px solid white;box-shadow:0 2px 8px rgba(0,0,0,0.3);display:flex;align-items:center;justify-content:center;"><i class="bi bi-geo-alt-fill" style="color:white;font-size:12px;"></i></div>',
                        iconSize: [28, 28], iconAnchor: [14, 28]
                    })
                }).addTo(clienteMap);
                setTimeout(function() { clienteMap.invalidateSize(); }, 300);
            }

            // Boton reubicar
            var btnRelocate = document.getElementById('btnOpenRelocate');
            if (btnRelocate) {
                btnRelocate.addEventListener('click', function() {
                    var lat = clienteLat || 20.67;
                    var lng = clienteLng || -103.36;
                    document.getElementById('relocateSearchInput').value = '';
                    document.getElementById('relocateAddress').textContent = (clienteLat && clienteLng) ? 'Moviendo el mapa...' : 'Busca o mueve el mapa para asignar ubicación';
                    document.getElementById('relocateCoords').textContent = lat.toFixed(6) + ', ' + lng.toFixed(6);
                    var modal = new bootstrap.Modal(document.getElementById('relocateModal'));
                    modal.show();
                    setTimeout(function() { initRelocateMap(lat, lng); }, 400);
                });
            }

            // Boton guardar
            var btnConfirm = document.getElementById('btnConfirmRelocate');
            if (btnConfirm) {
                btnConfirm.addEventListener('click', confirmRelocate);
            }

            // Buscar
            var searchBtn = document.getElementById('relocateSearchBtn');
            if (searchBtn) searchBtn.addEventListener('click', searchRelocate);
            var searchInput = document.getElementById('relocateSearchInput');
            if (searchInput) {
                searchInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') { e.preventDefault(); searchRelocate(); }
                });
            }

            // Al cerrar modal, destruir mapa
            var relocateModalEl = document.getElementById('relocateModal');
            if (relocateModalEl) {
                relocateModalEl.addEventListener('hidden.bs.modal', function() {
                    if (relocateMap) { relocateMap.remove(); relocateMap = null; }
                });
            }
        });

        function initRelocateMap(lat, lng) {
            var mapEl = document.getElementById('relocateMap');
            if (!mapEl) return;
            if (relocateMap) {
                relocateMap.setView([lat, lng], 17);
                relocateMap.invalidateSize();
                return;
            }
            relocateMap = L.map('relocateMap', { zoomControl: false, attributionControl: false }).setView([lat, lng], 17);
            L.control.zoom({ position: 'topright' }).addTo(relocateMap);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OSM', maxZoom: 19
            }).addTo(relocateMap);

            relocateMap.on('moveend', function() {
                var center = relocateMap.getCenter();
                document.getElementById('relocateCoords').textContent = center.lat.toFixed(6) + ', ' + center.lng.toFixed(6);
                clearTimeout(relocateDebounce);
                relocateDebounce = setTimeout(function() {
                    reverseGeocodeRelocate(center.lat, center.lng);
                }, 600);
            });

            reverseGeocodeRelocate(lat, lng);
        }

        function reverseGeocodeRelocate(lat, lng) {
            document.getElementById('relocateAddress').textContent = 'Buscando dirección...';
            fetch('https://nominatim.openstreetmap.org/reverse?lat=' + lat + '&lon=' + lng + '&format=json&zoom=18&addressdetails=1&accept-language=es', {
                headers: { 'User-Agent': 'ShalomERP/1.0' }
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                document.getElementById('relocateAddress').textContent = (data && data.display_name) ? data.display_name : 'Dirección no encontrada';
            })
            .catch(function() { document.getElementById('relocateAddress').textContent = 'Error al buscar dirección'; });
        }

        function searchRelocate() {
            var query = document.getElementById('relocateSearchInput').value.trim();
            if (!query) return;
            document.getElementById('relocateAddress').textContent = 'Buscando...';
            fetch('https://nominatim.openstreetmap.org/search?q=' + encodeURIComponent(query) + '&format=json&limit=1&countrycodes=mx&accept-language=es', {
                headers: { 'User-Agent': 'ShalomERP/1.0' }
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data && data.length > 0) {
                    var lat = parseFloat(data[0].lat);
                    var lng = parseFloat(data[0].lon);
                    relocateMap.setView([lat, lng], 17, { animate: true });
                    document.getElementById('relocateAddress').textContent = data[0].display_name;
                    document.getElementById('relocateCoords').textContent = lat.toFixed(6) + ', ' + lng.toFixed(6);
                } else {
                    document.getElementById('relocateAddress').textContent = 'No se encontró la dirección';
                }
            })
            .catch(function() { document.getElementById('relocateAddress').textContent = 'Error al buscar'; });
        }

        function confirmRelocate() {
            if (!relocateMap) return;
            var center = relocateMap.getCenter();
            var lat = center.lat;
            var lng = center.lng;
            var csrf = document.querySelector('meta[name="csrf-token"]');
            var btn = document.getElementById('btnConfirmRelocate');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

            var xhr = new XMLHttpRequest();
            xhr.open('POST', '{{ route("rutas.reubicarCliente") }}', true);
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.setRequestHeader('Accept', 'application/json');
            xhr.setRequestHeader('X-CSRF-TOKEN', csrf ? csrf.content : '{{ csrf_token() }}');
            xhr.onload = function() {
                if (xhr.status === 200) {
                    var response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        clienteLat = lat;
                        clienteLng = lng;
                        location.reload();
                        return;
                    }
                }
                alert('Error al reubicar.');
                btn.disabled = false;
                btn.textContent = 'Guardar';
            };
            xhr.onerror = function() {
                alert('Error al reubicar.');
                btn.disabled = false;
                btn.textContent = 'Guardar';
            };
            xhr.send(JSON.stringify({ cliente_id: {{ $cliente->id }}, lat: lat, lng: lng }));
        }
    </script>
@endpush

@section('content')
    <section class="content container-fluid">
        <div class="container py-2">
            <!-- Header del cliente -->
            <div class="contract-header">
                <a href="{{ route('clientes.index') }}" class="modern-link mb-3 d-inline-block">
                    <i class="bi bi-arrow-left me-1"></i>
                    {{ __('Regresar') }}
                </a>
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <!-- Header moderno -->
                        <div class="page-header">
                            <div class="header-content">
                                <div class="header-icon">
                                    <i class="bi bi-person-fill"></i>
                                </div>
                                <div class="header-text">
                                    <h1 class="page-title">{{ $cliente->nombre }} {{ $cliente->apellido }}</h1>
                                    <p class="page-subtitle">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Expediente de cliente y gestión de contratos
                                    </p>
                                </div>
                            </div>
                            <div class="header-actions">
                                <div class="text-md-end">
                                    <span class="badge status-badge bg-primary bg-gradient text-white">
                                        <i class="bi bi-shield-check me-1"></i> CLIENTE ACTIVO
                                    </span>
                                    <p class="text-muted mb-0 mt-3 small">
                                        <i class="bi bi-calendar-check me-1"></i> Miembro desde: 
                                        <span class="fw-bold text-dark">{{ $cliente->created_at->translatedFormat('d M, Y') }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                @php
                    $totalPagos = 0;
                    $pagosPendientes = 0;
                    $totalContratos = count($cliente_contratos);
                    $contratosActivos = $cliente_contratos->where('estado', 'activo')->count();

                    foreach ($cliente_contratos as $contrato) {
                        if ($contrato->pagos) {
                            foreach ($contrato->pagos as $pago) {
                                if ($pago->estado === 'hecho') {
                                    $totalPagos += $pago->monto;
                                } elseif ($pago->estado === 'pendiente') {
                                    $pagosPendientes += $pago->monto;
                                }
                            }
                        }
                    }

                    $porcentajeActivos = $totalContratos > 0 ? ($contratosActivos / $totalContratos) * 100 : 0;
                @endphp
                <div class="col-md-8">
                    <!-- // Detalles del cliente -->
                    <div class="card mb-4">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="card-title mb-0"><i class="bi bi-person-circle me-2 text-primary"></i>Detalles del
                                Cliente</h5>
                        </div>
                        <div class="card-body">
                            <!-- Información Principal -->
                            <div class="row mb-4">
                                <!-- Información Personal -->
                                <div class="col-md-6">
                                    <div class="info-section">
                                        <div class="card border-0 shadow-sm h-100 hover-card" style="background: linear-gradient(135deg, #e3f2fd 0%, #f8f9fa 100%);">
                                            <div class="card-body p-4">
                                                <!-- Header del cliente -->
                                                <div class="d-flex align-items-center justify-content-between mb-3">
                                                    <div class="d-flex align-items-center">
                                                        <div class="client-avatar me-3">
                                                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; font-size: 1.5em; font-weight: bold;">
                                                                {{ strtoupper(substr($cliente->nombre, 0, 1) . substr($cliente->apellido, 0, 1)) }}
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <h5 class="fw-bold mb-1 text-dark">
                                                                {{ $cliente->nombre }} {{ $cliente->apellido }}
                                                            </h5>
                                                            <div class="d-flex gap-2 align-items-center">
                                                                <span class="badge bg-light text-dark border">Cliente</span>
                                                                @if($cliente->tipo)
                                                                <span class="badge bg-info text-dark">{{ ucfirst($cliente->tipo) }}</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Información de contacto -->
                                                <div class="row g-3 mb-3">
                                                    @if($cliente->email)
                                                    <div class="col-12">
                                                        <div class="d-flex align-items-center gap-2">
                                                            <i class="bi bi-envelope text-primary"></i>
                                                            <a href="mailto:{{ $cliente->email }}" class="text-decoration-none text-muted small">{{ $cliente->email }}</a>
                                                        </div>
                                                    </div>
                                                    @endif

                                                    @if($cliente->telefono)
                                                    <div class="col-12">
                                                        <div class="d-flex align-items-center gap-2">
                                                            <i class="bi bi-telephone text-success"></i>
                                                            <a href="tel:{{ $cliente->telefono }}" class="text-decoration-none text-muted small">{{ $cliente->telefono }}</a>
                                                        </div>
                                                    </div>
                                                    @endif
                                                </div>

                                                <!-- Estadísticas del cliente -->
                                                <div class="border-top pt-3">
                                                    <div class="row text-center">
                                                        <div class="col-4">
                                                            <div class="client-stat">
                                                                <i class="bi bi-calendar-plus text-info d-block fs-5 mb-1"></i>
                                                                <small class="text-muted d-block">Cliente desde</small>
                                                                    <small class="fw-bold">
                                                                        {{ $cliente->created_at->translatedFormat('F Y') }}
                                                                    </small>
                                                            </div>
                                                        </div>
                                                        <div class="col-4">
                                                            <div class="client-stat">
                                                                <i class="bi bi-file-earmark-text text-success d-block fs-5 mb-1"></i>
                                                                <small class="text-muted d-block">Contratos</small>
                                                                <small class="fw-bold">{{ $totalContratos }}</small>
                                                            </div>
                                                        </div>
                                                        <div class="col-4">
                                                            <div class="client-stat">
                                                                <i class="bi bi-check-circle text-primary d-block fs-5 mb-1"></i>
                                                                <small class="text-muted d-block">Activos</small>
                                                                <small class="fw-bold">{{ $contratosActivos }}</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Indicador de tiempo como cliente -->
                                                @php
                                                $tiempoCliente = $cliente->created_at->diffForHumans(null, true);
                                                @endphp
                                                <div class="mt-3">
                                                    <div class="border border-muted rounded p-2 text-center">
                                                        <small class="text-muted">
                                                            <i class="bi bi-star-fill text-warning me-1"></i>
                                                            @if($tiempoCliente)
                                                            Cliente registrado hace {{ $tiempoCliente }}
                                                            @else
                                                            Cliente recién registrado
                                                            @endif
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Información de Domicilio -->
                                <div class="col-md-6">
                                    <div class="info-section">
                                        <div class="card border-0 shadow-sm h-100 hover-card" style="background: linear-gradient(135deg, #fff8e1 0%, #f8f9fa 100%);">
                                            <div class="card-body p-4">
                                                <!-- Header del domicilio -->
                                                <div class="d-flex align-items-center justify-content-between mb-3">
                                                    <div class="d-flex align-items-center">
                                                        <div class="client-avatar me-3">
                                                            <div class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                                                <i class="bi bi-geo-alt-fill fs-4"></i>
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <h5 class="fw-bold mb-1 text-dark">
                                                                Información de Domicilio
                                                            </h5>
                                                            <div class="d-flex gap-2 align-items-center">
                                                                <span class="badge bg-light text-dark border">Dirección</span>
                                                                @if($cliente->municipio)
                                                                <span class="badge bg-warning text-dark">{{ $cliente->municipio }}</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Información de dirección -->
                                                <div class="mb-3">
                                                    <div class="d-flex align-items-start gap-2">
                                                        <i class="bi bi-geo-alt text-primary mt-1"></i>
                                                        <span class="text-muted small">
                                                            {{ $cliente->domicilio_completo ?? 'Domicilio no disponible' }}
                                                        </span>
                                                    </div>
                                                </div>

                                                <!-- Datos de ubicación -->
                                                <div class="border-top pt-3">
                                                    <div class="row text-center">
                                                        @if($cliente->codigo_postal)
                                                        <div class="col-6">
                                                            <div class="client-stat">
                                                                <i class="bi bi-mailbox text-info d-block fs-5 mb-1"></i>
                                                                <small class="text-muted d-block">C.P.</small>
                                                                <small class="fw-bold">{{ $cliente->codigo_postal }}</small>
                                                            </div>
                                                        </div>
                                                        @endif
                                                        @if($cliente->municipio)
                                                        <div class="col-6">
                                                            <div class="client-stat">
                                                                <i class="bi bi-map text-success d-block fs-5 mb-1"></i>
                                                                <small class="text-muted d-block">Municipio</small>
                                                                <small class="fw-bold">{{ Str::limit($cliente->municipio, 10) }}</small>
                                                            </div>
                                                        </div>
                                                        @endif
                                                    </div>
                                                </div>

                                                <!-- Botón para ver en Maps -->
                                                @php
                                                $direccionCompleta = collect([
                                                    $cliente->calle_y_numero,
                                                    $cliente->colonia,
                                                    $cliente->municipio,
                                                    $cliente->estado,
                                                    $cliente->codigo_postal,
                                                ])
                                                    ->filter()
                                                    ->implode(', ');

                                                $googleMapsUrl =
                                                    'https://www.google.com/maps/search/' .
                                                    urlencode($direccionCompleta);
                                                @endphp
                                                <div class="mt-3">
                                                    <div class="border border-muted rounded p-2 text-center">
                                                        <a href="{{ $googleMapsUrl }}" target="_blank" class="text-decoration-none">
                                                            <small class="text-muted">
                                                                <i class="bi bi-geo-alt-fill text-warning me-1"></i>
                                                                Ver ubicación en Google Maps
                                                            </small>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Historial Financiero -->
                    <div class="card">
                        <div class="card-header text-white border-0 rounded-top"
                            style="background-image: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                            <button class="btn text-white p-0 w-100 text-start border-0" type="button"
                                data-bs-toggle="collapse" data-bs-target="#historialFinanciero" aria-expanded="false"
                                aria-controls="historialFinanciero" style="background: transparent;">
                                <div class="d-flex justify-content-between align-items-center w-100">
                                    <h6 class="mb-0">
                                        <i class="bi bi-graph-up me-2"></i>Historial Financiero Detallado
                                    </h6>
                                    <i class="bi bi-chevron-down" id="chevronIcon"></i>
                                </div>
                            </button>
                        </div>
                        <div class="collapse" id="historialFinanciero">
                            <div class="card-body">
                                @php
                                    $ultimosPagos = collect();

                                    foreach ($cliente_contratos as $contrato) {
                                        if ($contrato->pagos) {
                                            foreach ($contrato->pagos as $pago) {
                                                // Solo agregar pagos con estado "hecho" al historial
                                                if ($pago->estado === 'hecho') {
                                                    $ultimosPagos->push($pago);
                                                }
                                            }
                                        }
                                    }

                                    $ultimosPagos = $ultimosPagos->sortByDesc('fecha_pago')->take(5);
                                @endphp
                                <!-- Resumen del historial financiero -->
                                <div class="row mb-4">
                                    <div class="col-md-4 mb-3 mb-md-0">
                                        <div class="card border-0 h-100"
                                            style="background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%); min-height: 80px;">
                                            <div
                                                class="card-body py-2 px-3 d-flex align-items-center justify-content-center">
                                                <div class="row w-100 align-items-center">
                                                    <div class="col-2 text-end">
                                                        <i class="bi bi-check-circle-fill text-success fs-4"></i>
                                                    </div>
                                                    <div class="col-10">
                                                        <h6 class="text-success mb-0 fw-bold" style="font-size: 1.1rem;">
                                                            ${{ number_format($totalPagos, 2) }}
                                                        </h6>
                                                        <small class="text-success fw-medium">Total</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3 mb-md-0">
                                        <div class="card border-0 h-100"
                                            style="background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%); min-height: 80px;">
                                            <div
                                                class="card-body py-2 px-3 d-flex align-items-center justify-content-center">
                                                <div class="row w-100 align-items-center">
                                                    <div class="col-2 text-center">
                                                        <i class="bi bi-clock-fill text-warning fs-4"></i>
                                                    </div>
                                                    <div class="col-10">
                                                        <h6 class="text-warning mb-0 fw-bold" style="font-size: 1.1rem;">
                                                            ${{ number_format($pagosPendientes, 2) }}
                                                        </h6>
                                                        <small class="text-warning fw-medium">Saldo pendiente</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card border-0 h-100"
                                            style="background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%); min-height: 80px;">
                                            <div
                                                class="card-body py-2 px-3 d-flex align-items-center justify-content-center">
                                                <div class="row w-100 align-items-center">
                                                    <div class="col-4 text-center">
                                                        <i class="bi bi-file-earmark-text-fill text-info fs-4"></i>
                                                    </div>
                                                    <div class="col-8">
                                                        <h6 class="text-info mb-0 fw-bold" style="font-size: 1.1rem;">
                                                            {{ count($cliente_contratos) }}
                                                        </h6>
                                                        <small class="text-info fw-medium">Contratos</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Últimos Pagos -->
                                @if ($ultimosPagos->count() > 0)
                                    <div class="card border-0" style="background-color: #f8fafc;">
                                        <div class="card-body p-0">
                                            <div class="table-responsive">
                                                <table class="table table-hover mb-0">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th class="border-0 fw-medium" style="width: 8%;">ID</th>
                                                            <th class="border-0 fw-medium" style="width: 18%;">Contrato</th>
                                                            <th class="border-0 fw-medium" style="width: 14%;">Tipo</th>
                                                            <th class="border-0 fw-medium" style="width: 16%;">Monto</th>
                                                            <th class="border-0 fw-medium" style="width: 14%;">Método</th>
                                                            <th class="border-0 fw-medium" style="width: 16%;">Fecha</th>
                                                            <th class="border-0 fw-medium text-center" style="width: 14%;">Estado</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($ultimosPagos as $pago)
                                                            <tr class="clickable-row" style="cursor: pointer;" onclick="window.location='{{ route('pagos.show', $pago->id) }}'">
                                                                <td class="border-0 py-3">
                                                                    <span class="badge bg-light text-primary fw-bold">#{{ $pago->id }}</span>
                                                                </td>
                                                                <td class="border-0 py-3">
                                                                    <div class="d-flex align-items-center">
                                                                        <i class="bi bi-file-earmark-text me-2 text-muted"></i>
                                                                        <span class="text-dark">
                                                                            {{ $pago->contrato->paquete->nombre ?? 'N/A' }} #{{ $pago->contrato_id }}
                                                                        </span>
                                                                    </div>
                                                                </td>
                                                                <td class="border-0 py-3">
                                                                    @php
                                                                        $tipoPagoColors = [
                                                                            'cuota' => 'bg-primary',
                                                                            'parcialidad' => 'bg-warning',
                                                                            'inicial' => 'bg-info',
                                                                            'bonificación' => 'bg-info'
                                                                        ];
                                                                        $colorClass = $tipoPagoColors[$pago->tipo_pago] ?? 'bg-secondary';
                                                                    @endphp
                                                                    <span class="badge {{ $colorClass }}">
                                                                        {{ \App\Models\Pago::TIPOS_PAGO[$pago->tipo_pago] ?? ucfirst($pago->tipo_pago) }}
                                                                    </span>
                                                                </td>
                                                                <td class="border-0 py-3">
                                                                    <span class="fw-bold text-success">${{ number_format($pago->monto, 2) }}</span>
                                                                </td>
                                                                <td class="border-0 py-3">
                                                                    <span class="badge bg-light text-dark">{{ $pago->metodo_pago }}</span>
                                                                </td>
                                                                <td class="border-0 py-3">
                                                                    <div class="d-flex align-items-center">
                                                                        <i class="bi bi-calendar3 me-2 text-muted"></i>
                                                                        <span class="text-dark small">
                                                                            {{ $pago->fecha_pago->isoFormat('D MMM YYYY HH:mm') }}
                                                                        </span>
                                                                    </div>
                                                                </td>
                                                                <td class="border-0 py-3 text-center">
                                                                    <span class="badge bg-success">
                                                                        <i class="bi bi-check-circle me-1"></i>{{ ucfirst($pago->estado) }}
                                                                    </span>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="card border-0 bg-white">
                                        <div class="card-body text-center py-5">
                                            <div class="mb-3">
                                                <i class="bi bi-receipt text-muted"
                                                    style="font-size: 3rem; opacity: 0.5;"></i>
                                            </div>
                                            <h6 class="text-muted mb-2">Sin pagos realizados</h6>
                                            <p class="text-muted small mb-0">No hay historial de pagos completados</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    
                </div>
                <div class="col-md-4">
                    <!-- Tarjeta de Ubicación -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-0 pb-2 d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold mb-0" style="color: #79481D;">
                                <i class="bi bi-geo-alt me-2"></i>Ubicación
                            </h6>
                            @if($cliente->tiene_coordenadas)
                                <button type="button" class="btn btn-sm fw-bold px-3 text-white" id="btnOpenRelocate" style="background: linear-gradient(135deg, #E1B240 0%, #79481D 100%); border: none; border-radius: 20px; font-size: 0.75rem;">
                                    <i class="bi bi-crosshair me-1"></i>Reubicar
                                </button>
                            @endif
                        </div>
                        <div class="card-body p-0">
                            @if($cliente->tiene_coordenadas)
                                <div id="clienteMap" style="height: 220px; width: 100%; border-radius: 0 0 12px 12px;"></div>
                                <div class="px-3 py-2 border-top">
                                    <small class="text-muted">
                                        <i class="bi bi-geo-alt me-1"></i>{{ $cliente->domicilio_completo }}
                                    </small>
                                    <br>
                                    <small class="text-muted" id="clienteCoordsDisplay">{{ $cliente->latitud }}, {{ $cliente->longitud }}</small>
                                </div>
                            @else
                                <div class="text-center py-4 px-3">
                                    <i class="bi bi-geo-alt" style="font-size: 2rem; color: #E1B240; opacity: 0.5;"></i>
                                    <p class="text-muted small mb-2 mt-2">Sin coordenadas registradas</p>
                                    <button type="button" class="btn btn-sm fw-bold px-3 text-white" id="btnOpenRelocate" style="background: linear-gradient(135deg, #E1B240 0%, #79481D 100%); border: none; border-radius: 20px; font-size: 0.75rem;">
                                        <i class="bi bi-crosshair me-1"></i>Asignar ubicación
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Historial de contratos -->
                    <div class="card mb-4">
                        <div class="card-header bg-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0"><i class="bi bi-file-text me-2"></i>Contratos del Cliente</h5>
                                <a href="{{ route('contratos.create', ['cliente_id' => $cliente->id]) }}"
                                    class="btn btn-primary btn-sm">
                                    <i class="bi bi-plus-circle me-1"></i>Nuevo Contrato
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-6">
                                    <div class="alert alert-info text-center py-2 mb-2">
                                        <strong>{{ count($cliente_contratos) }}</strong><br>
                                        <small>Contratos totales</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="alert alert-success text-center py-2 mb-2">
                                        <strong>{{ $cliente_contratos->where('estado', 'activo')->count() }}</strong><br>
                                        <small>Contratos activos</small>
                                    </div>
                                </div>
                            </div>
                            <div class="timeline">
                                @forelse($cliente_contratos as $contrato)
                                    <div class="timeline-item mb-4">
                                        <a href="{{ route('contratos.show', $contrato->id) }}"
                                            class="text-decoration-none">
                                            <div class="card shadow-sm payment-card border-0"
                                                style="border-left: 4px solid 
                                            {{ $contrato->estado === 'activo'
                                                ? '#198754'
                                                : ($contrato->estado === 'suspendido'
                                                    ? '#ffc107'
                                                    : ($contrato->estado === 'cancelado'
                                                        ? '#dc3545'
                                                        : '#6c757d')) }} !important;">
                                                <div class="card-body">
                                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                                        <div class="d-flex align-items-center">
                                                            <div class="me-3">
                                                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
                                                                    style="width: 40px; height: 40px;">
                                                                    <i class="bi bi-file-text"></i>
                                                                </div>
                                                            </div>
                                                            <div>
                                                                <h6 class="mb-0 fw-bold text-dark">
                                                                    {{ $contrato->paquete->nombre }}#{{ $contrato->id }}
                                                                </h6>
                                                                <small class="text-muted">
                                                                    <i class="bi bi-calendar-event me-1"></i>
                                                                    {{ \Carbon\Carbon::parse($contrato->fecha_inicio)->isoFormat('D MMM YYYY') }}
                                                                </small>
                                                            </div>
                                                        </div>
                                                        <div class="text-end">
                                                            @php
                                                                switch ($contrato->estado) {
                                                                    case 'activo':
                                                                        $badgeClass = 'bg-success';
                                                                        break;
                                                                    case 'suspendido':
                                                                        $badgeClass = 'bg-warning text-dark';
                                                                        break;
                                                                    case 'cancelado':
                                                                        $badgeClass = 'bg-danger';
                                                                        break;
                                                                    default:
                                                                        $badgeClass = 'bg-secondary';
                                                                }
                                                            @endphp
                                                            <span class="badge {{ $badgeClass }} mb-1">
                                                                {{ strtoupper($contrato->estado) }}
                                                            </span>
                                                            <div class="text-muted small">
                                                                ${{ number_format($contrato->monto_total, 2) }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                @empty
                                    <div class="text-center py-5">
                                        <div class="mb-3">
                                            <i class="bi bi-file-text text-muted"
                                                style="font-size: 3rem; opacity: 0.5;"></i>
                                        </div>
                                        <h6 class="text-muted mb-2">Sin contratos</h6>
                                        <p class="text-muted small mb-0">No hay contratos asociados a este cliente</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

            </div>

        </div>
    </section>

    <!-- Modal Reubicar Punto -->
    <div class="modal fade" id="relocateModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-fullscreen-sm-down modal-lg modal-dialog-centered">
            <div class="modal-content border-0 overflow-hidden" style="border-radius: 16px;">
                <div class="modal-header py-2 px-3 border-bottom" style="background: white;">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    <h6 class="modal-title fw-bold mb-0" style="color: #1c1c1e;">Reubicar punto</h6>
                    <button type="button" class="btn btn-sm fw-bold px-3 text-white" id="btnConfirmRelocate" style="background: linear-gradient(135deg, #E1B240 0%, #79481D 100%); border: none; border-radius: 20px;">
                        Guardar
                    </button>
                </div>
                <div class="px-3 py-2 border-bottom bg-white">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" id="relocateSearchInput" class="form-control border-start-0" placeholder="Buscar dirección..." autocomplete="off">
                        <button class="btn btn-outline-secondary" type="button" id="relocateSearchBtn">
                            <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                </div>
                <div id="relocateMap" style="flex: 1; width: 100%; min-height: 300px; background: #e9ecef; position: relative;">
                    <div id="relocatePin" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -100%); z-index: 1000; pointer-events: none;">
                        <div style="width: 32px; height: 32px; background: #79481D; border-radius: 50% 50% 50% 0; transform: rotate(-45deg); border: 3px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.3);"></div>
                        <div style="position: absolute; bottom: -6px; left: 50%; transform: translateX(-50%); width: 12px; height: 12px; background: rgba(0,0,0,0.15); border-radius: 50%; filter: blur(3px);"></div>
                    </div>
                </div>
                <div class="px-3 py-2 border-top bg-white">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-geo-alt-fill" style="color: #79481D;"></i>
                        <small class="text-muted flex-grow-1" id="relocateAddress" style="font-size: 0.78rem;">Moviendo el mapa...</small>
                        <span class="badge bg-light text-dark" id="relocateCoords" style="font-size: 0.65rem;"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
