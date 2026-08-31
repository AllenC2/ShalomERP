<!-- Formulario Minimalista en 2 Columnas -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<div class="minimal-form">
    <div class="form-container">
        
        <div class="form-layout">
            <!-- Columna Izquierda -->
            <div class="left-column">
                
                <!-- Información Personal -->
                <div class="form-section">
                    <h6 class="section-title">Información Personal</h6>

                    <div class="form-group">
                        <label for="id" class="form-label">ID del Empleado <span class="text-danger">*</span></label>
                        <input type="text" name="id" class="form-control @error('id') is-invalid @enderror"
                               value="{{ old('id', $empleado?->id) }}" id="id" placeholder="Ej: EMP-001"
                               {{ isset($empleado) && $empleado->id ? '' : '' }}>
                        <small class="form-text text-muted">Ingrese un identificador único para el empleado</small>
                        @error('id')<div class="error-text">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror"
                                   value="{{ old('nombre', $empleado?->nombre) }}" id="nombre" placeholder="Nombre del empleado">
                            @error('nombre')<div class="error-text">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-group">
                            <label for="apellido" class="form-label">Apellido <span class="text-danger">*</span></label>
                            <input type="text" name="apellido" class="form-control @error('apellido') is-invalid @enderror"
                                   value="{{ old('apellido', $empleado?->apellido) }}" id="apellido" placeholder="Apellido del empleado">
                            @error('apellido')<div class="error-text">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="form-label">Correo Electrónico <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                               value="{{ old('email', $empleado?->user?->email) }}" id="email" placeholder="empleado@empresa.com">
                        @error('email')<div class="error-text">{{ $message }}</div>@enderror
                    </div>

                    @if(!isset($empleado) || !$empleado->id)
                    <!-- Campo de contraseña solo para crear nuevos empleados -->
                    <div class="form-group">
                        <label for="password" class="form-label">Contraseña <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" 
                               id="password" placeholder="Contraseña del empleado">
                        @error('password')<div class="error-text">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation" class="form-label">Confirmar Contraseña <span class="text-danger">*</span></label>
                        <input type="password" name="password_confirmation" class="form-control" 
                               id="password_confirmation" placeholder="Confirmar contraseña">
                    </div>
                    @endif
                </div>
            </div>

            <!-- Columna Derecha -->
            <div class="right-column">
                
                <!-- Información de Contacto -->
                <div class="form-section">
                    <h6 class="section-title">Información de Contacto</h6>
                    
                    <div class="form-group">
                        <label for="telefono" class="form-label">Teléfono <span class="text-danger">*</span></label>
                        <input type="tel" name="telefono" class="form-control @error('telefono') is-invalid @enderror" 
                               value="{{ old('telefono', $empleado?->telefono) }}" id="telefono" placeholder="Número de teléfono">
                        @error('telefono')<div class="error-text">{{ $message }}</div>@enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="domicilio" class="form-label">Domicilio <span class="text-danger">*</span></label>
                        <textarea name="domicilio" class="form-control @error('domicilio') is-invalid @enderror" 
                                  id="domicilio" rows="3" placeholder="Dirección completa del empleado">{{ old('domicilio', $empleado?->domicilio) }}</textarea>
                        @error('domicilio')<div class="error-text">{{ $message }}</div>@enderror
                    </div>

                    <!-- Ubicación -->
                    <div class="form-section" style="padding: 24px 0 0 0; border-bottom: none;">
                        <h6 class="section-title">Ubicación</h6>
                        <input type="hidden" name="latitud" id="inputLatitud" value="{{ old('latitud', $empleado?->latitud) }}">
                        <input type="hidden" name="longitud" id="inputLongitud" value="{{ old('longitud', $empleado?->longitud) }}">
                        <div id="empleadoMapContainer" style="height: 200px; border-radius: 10px; overflow: hidden; background: #e9ecef; position: relative;">
                            <div id="empleadoMap" style="height: 100%; width: 100%;"></div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <small class="text-muted" id="empleadoCoordsDisplay">{{ $empleado?->latitud ? number_format($empleado->latitud, 6) . ', ' . number_format($empleado->longitud, 6) : 'Sin coordenadas' }}</small>
                            <button type="button" class="btn btn-sm fw-bold px-3 text-white" id="btnOpenEmpleadoRelocate" style="background: linear-gradient(135deg, #E1B240 0%, #79481D 100%); border: none; border-radius: 20px; font-size: 0.75rem;">
                                <i class="bi bi-crosshair me-1"></i>Cambiar punto
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botones -->
        <div class="form-actions d-flex justify-content-end">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-circle me-2"></i>{{ isset($empleado) ? 'Actualizar' : 'Guardar' }} Empleado
            </button>
        </div>
    </div>
</div>

<!-- Modal Reubicar Empleado -->
<div class="modal fade" id="empleadoRelocateModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-fullscreen-sm-down modal-lg modal-dialog-centered">
        <div class="modal-content border-0 overflow-hidden" style="border-radius: 16px;">
            <div class="modal-header py-2 px-3 border-bottom" style="background: white;">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <h6 class="modal-title fw-bold mb-0" style="color: #1c1c1e;">Reubicar empleado</h6>
                <button type="button" class="btn btn-sm fw-bold px-3 text-white" id="btnConfirmEmpleadoRelocate" style="background: linear-gradient(135deg, #E1B240 0%, #79481D 100%); border: none; border-radius: 20px;">
                    Guardar
                </button>
            </div>
            <div class="px-3 py-2 border-bottom bg-white">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" id="empleadoRelocateSearch" class="form-control border-start-0" placeholder="Buscar dirección..." autocomplete="off">
                    <button class="btn btn-outline-secondary" type="button" id="empleadoRelocateSearchBtn">
                        <i class="bi bi-arrow-right"></i>
                    </button>
                </div>
            </div>
            <div id="empleadoRelocateMap" style="flex: 1; width: 100%; min-height: 300px; background: #e9ecef; position: relative;">
                <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -100%); z-index: 1000; pointer-events: none;">
                    <div style="width: 32px; height: 32px; background: #79481D; border-radius: 50% 50% 50% 0; transform: rotate(-45deg); border: 3px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.3);"></div>
                    <div style="position: absolute; bottom: -6px; left: 50%; transform: translateX(-50%); width: 12px; height: 12px; background: rgba(0,0,0,0.15); border-radius: 50%; filter: blur(3px);"></div>
                </div>
            </div>
            <div class="px-3 py-2 border-top bg-white">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-geo-alt-fill" style="color: #79481D;"></i>
                    <small class="text-muted flex-grow-1" id="empleadoRelocateAddress" style="font-size: 0.78rem;">Moviendo el mapa...</small>
                    <span class="badge bg-light text-dark" id="empleadoRelocateCoords" style="font-size: 0.65rem;"></span>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var empMap = null;
    var relocateMap = null;
    var relocateDebounce = null;
    var latInput = document.getElementById('inputLatitud');
    var lngInput = document.getElementById('inputLongitud');
    var initLat = parseFloat(latInput.value) || null;
    var initLng = parseFloat(lngInput.value) || null;

    // Init mini map
    if (initLat && initLng && document.getElementById('empleadoMap')) {
        empMap = L.map('empleadoMap', { zoomControl: false, attributionControl: false, scrollWheelZoom: false, dragging: false }).setView([initLat, initLng], 16);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OSM', maxZoom: 19 }).addTo(empMap);
        L.marker([initLat, initLng], {
            icon: L.divIcon({
                className: 'custom-marker',
                html: '<div style="background:#79481D;width:28px;height:28px;border-radius:50%;border:3px solid white;box-shadow:0 2px 8px rgba(0,0,0,0.3);display:flex;align-items:center;justify-content:center;"><i class="bi bi-geo-alt-fill" style="color:white;font-size:12px;"></i></div>',
                iconSize: [28, 28], iconAnchor: [14, 28]
            })
        }).addTo(empMap);
        setTimeout(function() { empMap.invalidateSize(); }, 300);
    }

    // Open relocate modal
    document.getElementById('btnOpenEmpleadoRelocate').addEventListener('click', function() {
        var lat = parseFloat(latInput.value) || 20.67;
        var lng = parseFloat(lngInput.value) || -103.36;
        document.getElementById('empleadoRelocateSearch').value = '';
        document.getElementById('empleadoRelocateAddress').textContent = (initLat && initLng) ? 'Moviendo el mapa...' : 'Busca o mueve el mapa para asignar ubicación';
        document.getElementById('empleadoRelocateCoords').textContent = lat.toFixed(6) + ', ' + lng.toFixed(6);
        var modal = new bootstrap.Modal(document.getElementById('empleadoRelocateModal'));
        modal.show();
        setTimeout(function() { initRelocateMap(lat, lng); }, 400);
    });

    function initRelocateMap(lat, lng) {
        if (relocateMap) {
            relocateMap.setView([lat, lng], 17);
            relocateMap.invalidateSize();
            return;
        }
        relocateMap = L.map('empleadoRelocateMap', { zoomControl: false, attributionControl: false }).setView([lat, lng], 17);
        L.control.zoom({ position: 'topright' }).addTo(relocateMap);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OSM', maxZoom: 19 }).addTo(relocateMap);
        relocateMap.on('moveend', function() {
            var center = relocateMap.getCenter();
            document.getElementById('empleadoRelocateCoords').textContent = center.lat.toFixed(6) + ', ' + center.lng.toFixed(6);
            clearTimeout(relocateDebounce);
            relocateDebounce = setTimeout(function() { reverseGeo(center.lat, center.lng); }, 600);
        });
        reverseGeo(lat, lng);
    }

    function reverseGeo(lat, lng) {
        document.getElementById('empleadoRelocateAddress').textContent = 'Buscando dirección...';
        fetch('https://nominatim.openstreetmap.org/reverse?lat=' + lat + '&lon=' + lng + '&format=json&zoom=18&accept-language=es', {
            headers: { 'User-Agent': 'ShalomERP/1.0' }
        })
        .then(function(r) { return r.json(); })
        .then(function(d) { document.getElementById('empleadoRelocateAddress').textContent = (d && d.display_name) ? d.display_name : 'Dirección no encontrada'; })
        .catch(function() { document.getElementById('empleadoRelocateAddress').textContent = 'Error al buscar'; });
    }

    document.getElementById('empleadoRelocateSearchBtn').addEventListener('click', searchRelocate);
    document.getElementById('empleadoRelocateSearch').addEventListener('keydown', function(e) { if (e.key === 'Enter') { e.preventDefault(); searchRelocate(); } });

    function searchRelocate() {
        var q = document.getElementById('empleadoRelocateSearch').value.trim();
        if (!q) return;
        document.getElementById('empleadoRelocateAddress').textContent = 'Buscando...';
        fetch('https://nominatim.openstreetmap.org/search?q=' + encodeURIComponent(q) + '&format=json&limit=1&countrycodes=mx&accept-language=es', {
            headers: { 'User-Agent': 'ShalomERP/1.0' }
        })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d && d.length > 0) {
                var lat = parseFloat(d[0].lat), lng = parseFloat(d[0].lon);
                relocateMap.setView([lat, lng], 17, { animate: true });
                document.getElementById('empleadoRelocateAddress').textContent = d[0].display_name;
                document.getElementById('empleadoRelocateCoords').textContent = lat.toFixed(6) + ', ' + lng.toFixed(6);
            } else {
                document.getElementById('empleadoRelocateAddress').textContent = 'No se encontró';
            }
        })
        .catch(function() { document.getElementById('empleadoRelocateAddress').textContent = 'Error al buscar'; });
    }

    // Confirm: set hidden inputs and close
    document.getElementById('btnConfirmEmpleadoRelocate').addEventListener('click', function() {
        if (!relocateMap) return;
        var center = relocateMap.getCenter();
        latInput.value = center.lat.toFixed(8);
        lngInput.value = center.lng.toFixed(8);
        document.getElementById('empleadoCoordsDisplay').textContent = center.lat.toFixed(6) + ', ' + center.lng.toFixed(6);

        // Update mini map
        if (empMap) {
            empMap.setView([center.lat, center.lng], 16);
        } else {
            empMap = L.map('empleadoMap', { zoomControl: false, attributionControl: false, scrollWheelZoom: false, dragging: false }).setView([center.lat, center.lng], 16);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OSM', maxZoom: 19 }).addTo(empMap);
        }
        // Clear and re-add marker
        empMap.eachLayer(function(l) { if (l instanceof L.Marker) empMap.removeLayer(l); });
        L.marker([center.lat, center.lng], {
            icon: L.divIcon({
                className: 'custom-marker',
                html: '<div style="background:#79481D;width:28px;height:28px;border-radius:50%;border:3px solid white;box-shadow:0 2px 8px rgba(0,0,0,0.3);display:flex;align-items:center;justify-content:center;"><i class="bi bi-geo-alt-fill" style="color:white;font-size:12px;"></i></div>',
                iconSize: [28, 28], iconAnchor: [14, 28]
            })
        }).addTo(empMap);
        setTimeout(function() { empMap.invalidateSize(); }, 200);

        var modal = bootstrap.Modal.getInstance(document.getElementById('empleadoRelocateModal'));
        modal.hide();
    });

    document.getElementById('empleadoRelocateModal').addEventListener('hidden.bs.modal', function() {
        if (relocateMap) { relocateMap.remove(); relocateMap = null; }
    });
});
</script>

<style>
    /* Diseño Minimalista en 2 Columnas */
    .minimal-form {
        max-width: 1200px;
        margin: 0 auto;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }

    .form-container {
        background: #ffffff;
        border-radius: 8px;
        border: 1px solid #e1e5e9;
        overflow: hidden;
    }

    /* Layout de 2 columnas */
    .form-layout {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0;
    }

    .left-column,
    .right-column {
        display: flex;
        flex-direction: column;
    }

    .left-column {
        border-right: 1px solid #e1e5e9;
    }

    .form-section {
        padding: 32px;
        border-bottom: 1px solid #f6f8fa;
        height: fit-content;
    }

    .form-section:last-child {
        border-bottom: none;
    }

    .section-title {
        font-size: 18px;
        font-weight: 600;
        color: #24292f;
        margin: 0 0 24px 0;
        border-bottom: 1px solid #d1d9e0;
        padding-bottom: 8px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        display: block;
        font-size: 14px;
        font-weight: 500;
        color: #656d76;
        margin-bottom: 6px;
    }

    .form-control {
        width: 100%;
        padding: 8px 12px;
        font-size: 14px;
        line-height: 20px;
        color: #24292f;
        background-color: #ffffff;
        border: 1px solid #d1d9e0;
        border-radius: 6px;
        transition: border-color 0.15s ease-in-out;
    }

    .form-control:focus {
        border-color: #0969da;
        outline: none;
        box-shadow: 0 0 0 3px rgba(9, 105, 218, 0.1);
    }

    .form-control:disabled {
        background-color: #f6f8fa;
        color: #656d76;
    }

    .form-control[rows] {
        resize: vertical;
        min-height: 80px;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    /* Responsive para form-row */
    @media (max-width: 576px) {
        .form-row {
            grid-template-columns: 1fr;
        }
    }

    /* Botones minimalistas */
    .form-actions {
        grid-column: 1 / -1;
        padding: 24px 32px;
        background: #f6f8fa;
        border-top: 1px solid #e1e5e9;
        display: flex;
        justify-content: space-between;
        gap: 12px;
    }

    .btn {
        padding: 8px 16px;
        font-size: 14px;
        font-weight: 500;
        border-radius: 6px;
        border: 1px solid;
        cursor: pointer;
        transition: all 0.15s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .btn-secondary {
        color: #24292f;
        background: #ffffff;
        border-color: #d1d9e0;
    }

    .btn-secondary:hover {
        background: #f3f4f6;
        border-color: #afb8c1;
        text-decoration: none;
        color: #24292f;
    }

    .btn-primary {
        color: #ffffff;
        background: #0969da;
        border-color: #0969da;
    }

    .btn-primary:hover {
        background: #0860ca;
        border-color: #0860ca;
        color: #ffffff;
    }

    /* Estados de error */
    .is-invalid {
        border-color: #da3633;
    }

    .error-text {
        font-size: 12px;
        color: #da3633;
        margin-top: 4px;
    }

    .text-danger {
        color: #da3633;
    }

    .form-text {
        font-size: 12px;
        margin-top: 4px;
        display: block;
    }

    .text-muted {
        color: #656d76;
    }

    /* Responsive Design */
    @media (max-width: 968px) {
        .form-layout {
            grid-template-columns: 1fr;
        }
        
        .left-column {
            border-right: none;
            border-bottom: 1px solid #e1e5e9;
        }
        
        .form-section {
            padding: 24px 20px;
        }
    }

    @media (max-width: 768px) {
        .minimal-form {
            padding: 16px;
        }
        
        .form-section {
            padding: 20px 16px;
        }
        
        .form-actions {
            padding: 20px;
            flex-direction: column;
        }
        
        .btn {
            width: 100%;
            text-align: center;
        }
    }

    /* Mejora visual para separación de secciones */
    .right-column .form-section:first-child {
        border-top: none;
    }
</style>