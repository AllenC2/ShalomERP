<!-- Formulario Minimalista en 2 Columnas -->
<div class="minimal-form">
    <div class="form-container">

        <div class="form-layout">
            <!-- Columna Izquierda -->
            <div class="left-column">

                <!-- Información Básica -->
                <div class="form-section">
                    <h6 class="section-title">Información del Contrato</h6>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="contrato_id" class="form-label">ID del Contrato</label>
                            <input type="text" name="id" class="form-control @error('id') is-invalid @enderror"
                                value="{{ old('id', isset($contrato) && !$contrato->exists ? '' : $contrato?->id) }}"
                                id="contrato_id" maxlength="6" pattern="[0-9]{1,6}" placeholder="Ingrese ID..." required>
                            <div id="id_status" class="id-status-message"></div>
                            @error('id')<div class="error-text">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-group">
                            <label for="fecha_inicio" class="form-label">Fecha de Inicio</label>
                            <input type="date" name="fecha_inicio"
                                class="form-control @error('fecha_inicio') is-invalid @enderror"
                                value="{{ old('fecha_inicio', $contrato?->fecha_inicio ?? now()->format('Y-m-d')) }}"
                                id="fecha_inicio" required>
                            @error('fecha_inicio')<div class="error-text">{{ $message }}</div>@enderror
                            <input type="hidden" name="fecha_fin" id="fecha_fin_hidden"
                                value="{{ old('fecha_fin', $contrato?->fecha_fin) }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="cliente_id" class="form-label">Cliente Titular</label>
                        <div class="searchable-select-container">
                            <div class="searchable-select" id="cliente-select">
                                <div class="search-input-container">
                                    <input type="text"
                                        class="form-control search-input @error('cliente_id') is-invalid @enderror"
                                        id="cliente_search" placeholder="Buscar cliente..." autocomplete="off">
                                    <div class="search-icon">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2">
                                            <circle cx="11" cy="11" r="8"></circle>
                                            <path d="m21 21-4.35-4.35"></path>
                                        </svg>
                                    </div>
                                    <div class="dropdown-arrow">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2">
                                            <polyline points="6,9 12,15 18,9"></polyline>
                                        </svg>
                                    </div>
                                </div>
                                <div class="dropdown-list" id="cliente-dropdown">
                                    <div class="dropdown-option" data-value="">
                                        <span class="option-text">Seleccionar cliente</span>
                                    </div>
                                    @foreach ($clientes as $id => $cliente)
                                        <div class="dropdown-option" data-value="{{ $id }}" {{ old('cliente_id', $contrato?->cliente_id) == $id ? 'data-selected="true"' : '' }}>
                                            <span class="option-text">{{ $cliente }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <input type="hidden" name="cliente_id" id="cliente_id"
                                value="{{ old('cliente_id', $contrato?->cliente_id) }}" required>
                        </div>
                        @error('cliente_id')<div class="error-text">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="paquete_id" class="form-label">Paquete</label>
                            @if(isset($contrato) && $contrato->exists)
                                <input type="text" class="form-control bg-light text-muted" value="{{ $paquetes[$contrato->paquete_id] ?? 'N/A' }}" readonly style="cursor: not-allowed;" onclick="mostrarModalPaqueteBloqueado()">
                                <input type="hidden" name="paquete_id" value="{{ $contrato->paquete_id }}">
                            @else
                                <select name="paquete_id" class="form-control @error('paquete_id') is-invalid @enderror"
                                    id="paquete_id" required>
                                    <option value="">Seleccionar paquete</option>
                                    @foreach ($paquetes as $id => $name)
                                        <option value="{{ $id }}" {{ old('paquete_id', $contrato?->paquete_id) == $id ? 'selected' : '' }}>
                                            {{ $name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('paquete_id')<div class="error-text">{{ $message }}</div>@enderror
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="monto_total" class="form-label">Monto Total</label>
                            <input type="text" name="monto_total"
                                class="form-control @error('monto_total') is-invalid @enderror"
                                value="{{ old('monto_total', isset($contrato) ? '$' . number_format($contrato->monto_total, 2, '.', ',') : '') }}"
                                id="monto_total" placeholder="$0.00">
                            @error('monto_total')<div class="error-text">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    @if(!isset($contrato) || !$contrato->exists)
                    <!-- Ruta Molde (opcional, solo creación) -->
                    <div class="form-group" id="ruta_molde_section">
                        <label for="ruta_plantilla_search" class="form-label">Asignar a ruta existente</label>
                        <div class="searchable-select-container">
                            <div class="searchable-select" id="ruta-molde-select">
                                <div class="search-input-container">
                                    <input type="text"
                                        class="form-control search-input @error('ruta_plantilla_id') is-invalid @enderror"
                                        id="ruta_plantilla_search" placeholder="Buscar ruta molde..." autocomplete="off">
                                    <div class="search-icon">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2">
                                            <circle cx="11" cy="11" r="8"></circle>
                                            <path d="m21 21-4.35-4.35"></path>
                                        </svg>
                                    </div>
                                    <div class="dropdown-arrow">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2">
                                            <polyline points="6,9 12,15 18,9"></polyline>
                                        </svg>
                                    </div>
                                </div>
                                <div class="dropdown-list dropdown-list-rich" id="ruta-molde-dropdown">
                                    <div class="dropdown-option" data-value="" data-label="Sin asignar"
                                        {{ old('ruta_plantilla_id') ? '' : 'data-selected="true"' }}>
                                        <span class="option-text">Sin asignar</span>
                                    </div>
                                    @foreach(($rutasMolde ?? []) as $plantilla)
                                        @php
                                            $nombreRuta = $plantilla->nombre ?: ('Ruta #' . $plantilla->id);
                                            $empNombre = trim(($plantilla->empleado->nombre ?? '') . ' ' . ($plantilla->empleado->apellido ?? ''));
                                            $paradasCount = (int) ($plantilla->paradas_count ?? 0);
                                            $paradasLabel = $paradasCount . ' parada' . ($paradasCount === 1 ? '' : 's');
                                            $frecuencia = $plantilla->etiquetaFrecuencia();
                                            $labelCorto = $nombreRuta . ' · ' . $paradasLabel;
                                            $searchText = trim($nombreRuta . ' ' . $frecuencia . ' ' . $paradasLabel . ' ' . $empNombre);
                                        @endphp
                                        <div class="dropdown-option ruta-molde-option"
                                            data-value="{{ $plantilla->id }}"
                                            data-label="{{ $labelCorto }}"
                                            data-search="{{ $searchText }}"
                                            {{ old('ruta_plantilla_id') == $plantilla->id ? 'data-selected="true"' : '' }}>
                                            <span class="option-text visually-hidden">{{ $searchText }}</span>
                                            <div class="ruta-molde-option-top">
                                                <span class="ruta-molde-option-name">{{ $nombreRuta }}</span>
                                                <span class="ruta-molde-option-badge">{{ $paradasLabel }}</span>
                                            </div>
                                            <div class="ruta-molde-option-meta">
                                                <span><i class="bi bi-calendar3"></i> {{ $frecuencia }}</span>
                                                @if($empNombre !== '')
                                                    <span><i class="bi bi-person"></i> {{ $empNombre }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <input type="hidden" name="ruta_plantilla_id" id="ruta_plantilla_id"
                                value="{{ old('ruta_plantilla_id') }}">
                        </div>
                        @error('ruta_plantilla_id')<div class="error-text">{{ $message }}</div>@enderror
                    </div>
                    @endif

                    <!-- Comisiones -->
                    <div id="comisiones_section" style="display: none;">
                        <h6 class="section-title">Comisiones</h6>
                        <div id="comisiones_container"></div>
                    </div>

                    <div class="form-row observaciones-documento-row">
                        <div class="form-group">
                            <label for="observaciones" class="form-label">Observaciones</label>
                            <textarea name="observaciones" class="form-control @error('observaciones') is-invalid @enderror"
                                id="observaciones" rows="6"
                                placeholder="Observaciones adicionales del contrato">{{ old('observaciones', $contrato?->observaciones) }}</textarea>
                            @error('observaciones')<div class="error-text">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Documento del contrato</label>
                            <div class="upload-container">

                        <label for="documento" class="upload-area" id="upload-area">
                            <div class="upload-content" id="upload-content">
                                <svg class="upload-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                    <polyline points="7,10 12,15 17,10" />
                                    <line x1="12" y1="15" x2="12" y2="3" />
                                </svg>
                                <span class="upload-text">Subir contrato en PDF</span>
                                <span class="upload-hint">Arrastra aquí o haz clic</span>
                            </div>
                        </label>

                        <input type="file" name="documento"
                            class="upload-input @error('documento') is-invalid @enderror" id="documento" accept=".pdf"
                            onchange="previewPDF(this)">
                        @error('documento')<div class="error-text">{{ $message }}</div>@enderror

                        <!-- Preview -->
                        <div id="pdf-preview" class="file-preview" style="display: none; cursor: pointer;"
                            onclick="triggerFileInput()">
                            <div class="preview-header">
                                <span>Vista previa (haz clic para cambiar)</span>
                            </div>
                            <div class="pdf-container"
                                style="height: 600px; background: #f8f9fa; display: flex; align-items: center; justify-content: center;">
                                <embed id="pdf-embed" src="" type="application/pdf" width="100%" height="100%"
                                    style="border: none;">
                                <div id="pdf-fallback" style="display: none; text-align: center; color: #6c757d;">
                                    <p>Vista previa no disponible</p>
                                    <a id="pdf-download-link" href="#" target="_blank" class="btn-link">Abrir PDF</a>
                                </div>
                            </div>
                            <div class="preview-info">
                                <small id="pdf-info"></small>
                            </div>
                        </div>

                        <!-- Documento existente -->
                        @if(isset($contrato) && $contrato->documento && $contrato->documento !== 'No')
                            <div id="existing-pdf" class="file-preview" style="cursor: pointer;"
                                onclick="triggerFileInput()">
                                <div class="preview-header">
                                    <span>Documento actual (haz clic para cambiar)</span>
                                </div>
                                <embed src="{{ asset('storage/' . $contrato->documento) }}" type="application/pdf"
                                    width="100%" height="600px">
                                <div class="preview-actions">
                                    <a href="{{ asset('storage/' . $contrato->documento) }}" target="_blank"
                                        class="btn-link" onclick="event.stopPropagation();">
                                        Ver completo
                                    </a>
                                </div>
                            </div>
                        @endif

                        <script>
                            // Variable para rastrear si hay un documento existente
                            const hasExistingDocument = document.getElementById('existing-pdf') !== null;

                            // Inicializar el estado del upload area
                            window.addEventListener('DOMContentLoaded', function () {
                                if (hasExistingDocument) {
                                    const uploadContent = document.getElementById('upload-content');
                                    const uploadLabel = document.querySelector('label[for="documento"]');
                                    if (uploadContent) {
                                        uploadContent.style.display = 'none';
                                    }
                                    if (uploadLabel) {
                                        uploadLabel.style.display = 'none';
                                    }
                                }
                            });

                            function triggerFileInput() {
                                document.getElementById('documento').click();
                            }

                            function restoreExistingDocument() {
                                const previewDiv = document.getElementById('pdf-preview');
                                const existingPdf = document.getElementById('existing-pdf');
                                const uploadContent = document.getElementById('upload-content');
                                const uploadLabel = document.querySelector('label[for="documento"]');

                                // Si hay un documento existente, mantenerlo visible
                                if (existingPdf) {
                                    existingPdf.style.display = 'block';
                                    if (previewDiv) previewDiv.style.display = 'none';
                                    if (uploadContent) uploadContent.style.display = 'none';
                                    if (uploadLabel) {
                                        uploadLabel.style.display = 'none';
                                    }
                                } else {
                                    // Si no hay documento existente, mostrar el área de upload
                                    if (previewDiv) previewDiv.style.display = 'none';
                                    if (uploadContent) uploadContent.style.display = '';
                                    if (uploadLabel) {
                                        uploadLabel.style.display = 'flex';
                                        uploadLabel.style.borderColor = '#d1d9e0';
                                        uploadLabel.style.background = '#f6f8fa';
                                    }
                                }
                            }

                            function previewPDF(input) {
                                const previewDiv = document.getElementById('pdf-preview');
                                const pdfEmbed = document.getElementById('pdf-embed');
                                const pdfInfo = document.getElementById('pdf-info');
                                const existingPdf = document.getElementById('existing-pdf');
                                const uploadContent = document.getElementById('upload-content');
                                const uploadLabel = document.querySelector('label[for="documento"]');

                                if (input.files && input.files[0]) {
                                    const file = input.files[0];

                                    // Validar que sea un PDF
                                    if (file.type !== 'application/pdf') {
                                        showNotification('Por favor selecciona solo archivos PDF', 'error');
                                        input.value = '';
                                        // Mantener el estado anterior
                                        restoreExistingDocument();
                                        return;
                                    }

                                    // Validar tamaño (máximo 10MB)
                                    if (file.size > 10 * 1024 * 1024) {
                                        showNotification('El archivo es demasiado grande. Máximo 10MB permitido', 'error');
                                        input.value = '';
                                        // Mantener el estado anterior
                                        restoreExistingDocument();
                                        return;
                                    }

                                    // Crear URL temporal para el archivo
                                    const fileUrl = URL.createObjectURL(file);

                                    // Mostrar información del archivo
                                    const fileSize = (file.size / 1024 / 1024).toFixed(2);
                                    if (pdfInfo) {
                                        pdfInfo.innerHTML = `<strong>${file.name}</strong> (${fileSize} MB)`;
                                    }

                                    // Ocultar upload area y documento existente
                                    if (uploadLabel) uploadLabel.style.display = 'none';
                                    if (uploadContent) uploadContent.style.display = 'none';
                                    if (existingPdf) existingPdf.style.display = 'none';

                                    // Mostrar previsualización
                                    if (pdfEmbed) {
                                        pdfEmbed.src = fileUrl;
                                    }
                                    if (previewDiv) {
                                        previewDiv.style.display = 'block';
                                        previewDiv.style.opacity = '1';
                                        previewDiv.style.transform = 'none';
                                    }

                                    showNotification('Archivo cargado correctamente', 'success');
                                } else {
                                    // Si no hay archivo, restaurar estado anterior
                                    restoreExistingDocument();
                                }
                            }

                            // Función para mostrar notificaciones mejorada
                            function showNotification(message, type = 'info') {
                                // Crear elemento de notificación si no existe
                                let notification = document.getElementById('notification');
                                if (!notification) {
                                    notification = document.createElement('div');
                                    notification.id = 'notification';
                                    notification.style.cssText = `
                                    position: fixed;
                                    top: 20px;
                                    right: 20px;
                                    padding: 16px 20px;
                                    border-radius: 12px;
                                    color: white;
                                    font-weight: 500;
                                    z-index: 9999;
                                    transform: translateX(400px);
                                    transition: all 0.3s ease;
                                    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
                                `;
                                    document.body.appendChild(notification);
                                }

                                // Establecer color según el tipo
                                const colors = {
                                    success: '#10b981',
                                    error: '#ef4444',
                                    info: '#6366f1'
                                };

                                notification.style.background = colors[type] || colors.info;
                                notification.textContent = message;

                                // Mostrar notificación
                                setTimeout(() => {
                                    notification.style.transform = 'translateX(0)';
                                }, 100);

                                // Ocultar después de 3 segundos
                                setTimeout(() => {
                                    notification.style.transform = 'translateX(400px)';
                                }, 3000);
                            }
                        </script>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Columna Derecha -->
            <div class="right-column">

                <!-- Configuración Financiera / Plan de Pagos -->
                <div class="form-section">
                    <h6 class="section-title">Configuración Financiera</h6>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="monto_inicial" class="form-label">Pago Inicial</label>
                            <input type="text" name="monto_inicial"
                                class="form-control @error('monto_inicial') is-invalid @enderror"
                                value="{{ old('monto_inicial', isset($contrato) && $contrato->monto_inicial > 0 ? '$' . number_format($contrato->monto_inicial, 2, '.', ',') : '') }}"
                                id="monto_inicial" placeholder="$0.00">
                            @error('monto_inicial')<div class="error-text">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-group">
                            <label for="monto_bonificacion" class="form-label">Bonificación</label>
                            <input type="text" name="monto_bonificacion"
                                class="form-control @error('monto_bonificacion') is-invalid @enderror"
                                value="{{ old('monto_bonificacion', isset($contrato) && $contrato->monto_bonificacion > 0 ? '$' . number_format($contrato->monto_bonificacion, 2, '.', ',') : '') }}"
                                id="monto_bonificacion" placeholder="$0.00">
                            @error('monto_bonificacion')<div class="error-text">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-group">
                            <label for="monto_cuota" class="form-label">Monto del Abono</label>
                            <input type="text" name="monto_cuota"
                                class="form-control @error('monto_cuota') is-invalid @enderror"
                                value="{{ old('monto_cuota', isset($contrato) && $contrato->monto_cuota > 0 ? '$' . number_format($contrato->monto_cuota, 2, '.', ',') : '') }}"
                                id="monto_cuota" placeholder="$0.00" required>
                            @error('monto_cuota')<div class="error-text">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-group">
                            <label for="frecuencia_cuotas" class="form-label">Frecuencia (Días)</label>
                            <input type="number" name="frecuencia_cuotas" list="frecuencia_opciones"
                                class="form-control @error('frecuencia_cuotas') is-invalid @enderror"
                                value="{{ old('frecuencia_cuotas', $contrato?->frecuencia_cuotas) }}"
                                id="frecuencia_cuotas" placeholder="Escribir o seleccionar" min="1" required>
                            <datalist id="frecuencia_opciones">
                                <option value="7">Semanal (7 días)</option>
                                <option value="14">Catorcenal (14 días)</option>
                                <option value="15">Quincenal (15 días)</option>
                                <option value="30">Mensual (30 días)</option>
                            </datalist>
                            @error('frecuencia_cuotas')<div class="error-text">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-group">
                            <label for="duracion_meses" class="form-label">Plan Proyectado (Meses)</label>
                            @php
                                $duracionActual = isset($contrato) && $contrato->numero_cuotas > 0 && $contrato->frecuencia_cuotas > 0
                                    ? round(($contrato->numero_cuotas * $contrato->frecuencia_cuotas) / 30, 1) . ' meses'
                                    : '';
                            @endphp
                            <input type="text" name="duracion_meses" id="duracion_meses"
                                class="form-control @error('duracion_meses') is-invalid @enderror"
                                value="{{ old('duracion_meses', $duracionActual) }}" placeholder="0" readonly>
                            @error('duracion_meses')<div class="error-text">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-group">
                            <label for="numero_cuotas" class="form-label">Número de Cuotas</label>
                            <input type="number" name="numero_cuotas"
                                class="form-control @error('numero_cuotas') is-invalid @enderror"
                                value="{{ old('numero_cuotas', $contrato?->numero_cuotas) }}" id="numero_cuotas"
                                placeholder="0" min="1" required readonly>
                            @error('numero_cuotas')<div class="error-text">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <!-- Resumen en Dos Columnas -->
                    <div class="payment-summary" id="pagos_card" style="display:none;">
                        <div class="payment-summary-grid">
                            <div class="summary-column">
                                <h6 class="summary-title">Resumen Financiero</h6>
                                <div id="total_restante" class="summary-breakdown"></div>
                            </div>
                            <div class="summary-column">
                                <h6 class="summary-title">Plan de Pagos</h6>
                                <div id="pagos_info" class="summary-text"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Proyección de Cuotas Temporales -->
                    <div class="payment-projection mt-3" id="projection_card" style="display:none; background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 16px;">
                        <h6 class="summary-title" style="font-size: 0.9rem; font-weight: 600; color: #1f2937; margin-bottom: 12px;">Proyección de Cuotas <small class="text-muted fw-normal">(Ajenas a Inicial y Bonificación)</small></h6>
                        <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
                            <table class="table table-sm table-hover mb-0" style="font-size: 0.85rem;">
                                <thead style="position: sticky; top: 0; background: white; z-index: 1;">
                                    <tr>
                                        <th style="border-bottom: 2px solid #f3f4f6;"># Cuota</th>
                                        <th style="border-bottom: 2px solid #f3f4f6;">Monto</th>
                                        <th style="border-bottom: 2px solid #f3f4f6;">Fecha Estimada</th>
                                    </tr>
                                </thead>
                                <tbody id="projection_body">
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-2 text-muted" style="font-size: 0.75rem;">
                            <i class="bi bi-info-circle me-1"></i> El Pago Inicial y Bonificación descuentan el saldo, pero su pago no forma parte de este calendario.
                        </div>
                    </div>
                    
                    <!-- Estado del Contrato (oculto) -->
                    <div class="form-group" style="display: none;">
                        <label for="estado" class="form-label">Estado del Contrato</label>
                        <select name="estado" class="form-control @error('estado') is-invalid @enderror" id="estado">
                            @php
                                $estados = \App\Models\Contrato::getEstadosValidos();
                                $estadoActual = old('estado', $contrato?->estado ?? 'activo');
                            @endphp
                            @foreach ($estados as $valor => $etiqueta)
                                <option value="{{ $valor }}" {{ $estadoActual == $valor ? 'selected' : '' }}>
                                    {{ $etiqueta }}
                                </option>
                            @endforeach
                        </select>
                        @error('estado')<div class="error-text">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Botones -->
        <div class="form-actions">
            <a href="{{ url()->previous() }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary"
                style="background: linear-gradient(90deg, #E1B240 0%, #79481D 100%); border: none;">
                Registrar Contrato
            </button>
        </div>
    </div>
</div>
</div>

@if(isset($contrato) && $contrato->exists)
<!-- Modal para paquete bloqueado -->
<div class="modal fade" id="paqueteBloqueadoModal" tabindex="-1" aria-labelledby="paqueteBloqueadoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning text-dark border-0">
                <h5 class="modal-title" id="paqueteBloqueadoModalLabel">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Cambio de Paquete No Permitido
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <div class="mb-3">
                    <i class="bi bi-shield-lock text-warning" style="font-size: 3rem;"></i>
                </div>
                <h6 class="fw-bold mb-3">No es posible cambiar el paquete de un contrato existente.</h6>
                <p class="text-muted mb-3">Modificar el paquete puede corromper los datos financieros, el historial de pagos y las comisiones ya generadas.</p>
                <div class="p-3 bg-light rounded text-start">
                    <p class="mb-0 text-dark"><i class="bi bi-lightbulb text-primary me-2"></i><strong>Sugerencia:</strong> Si el cliente requiere un paquete diferente, debe cancelar o finalizar este contrato y crear uno <strong>nuevo</strong>.</p>
                </div>
            </div>
            <div class="modal-footer border-0 justify-content-center pb-4">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Entendido</button>
            </div>
        </div>
    </div>
</div>

<script>
function mostrarModalPaqueteBloqueado() {
    const modalEl = document.getElementById('paqueteBloqueadoModal');
    if(modalEl) {
        const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        modal.show();
    }
}
</script>
@endif

<script>
    // Comisiones de pago inicial (ajustes activos comision_fija_*)
    @php
        $comisionesPagoInicialConfig = \App\Models\Ajuste::where('nombre', 'like', 'comision_fija_%')
            ->where('activo', true)
            ->get()
            ->map(function ($ajuste) {
                $data = is_array($ajuste->valor) ? $ajuste->valor : json_decode($ajuste->valor, true);
                $esJson = is_array($data);
                return [
                    'tipo' => str_replace('comision_fija_', '', $ajuste->nombre),
                    'monto' => floatval($esJson && isset($data['monto']) ? $data['monto'] : $ajuste->valor),
                    'tipo_valor' => $esJson && isset($data['tipo_valor']) ? $data['tipo_valor'] : 'fijo',
                ];
            })
            ->values();
    @endphp
    const comisionesPagoInicialConfig = @json($comisionesPagoInicialConfig);
    const esCreacionContrato = @json(!isset($contrato) || !$contrato->exists);

    function obtenerComisionesAdelantadas(montoTotal) {
        if (!esCreacionContrato) {
            return [];
        }

        const adelantadas = [];
        document.querySelectorAll('.adelantar-comision-checkbox:checked').forEach((checkbox) => {
            const modo = checkbox.dataset.modo || 'porcentaje';
            const tipo = checkbox.dataset.tipo || 'Comisión';
            let montoCalculado = 0;

            if (modo === 'monto') {
                montoCalculado = parseFloat(checkbox.dataset.montoFijo) || 0;
            } else {
                const porcentaje = parseFloat(checkbox.dataset.cantidad) || 0;
                montoCalculado = (montoTotal * porcentaje) / 100;
            }

            montoCalculado = Math.round(montoCalculado * 100) / 100;
            adelantadas.push({
                tipo: tipo,
                monto: montoCalculado,
                tipo_valor: 'adelanto',
                config_monto: null
            });
        });

        return adelantadas;
    }

    function calcularComisionesPagoInicial(montoInicial, montoTotal) {
        if (!montoInicial || montoInicial <= 0) {
            return [];
        }

        let totalAsignado = 0;
        let tipoRestante = null;
        const comisiones = [];

        if (Array.isArray(comisionesPagoInicialConfig)) {
            comisionesPagoInicialConfig.forEach((config) => {
                if (config.tipo_valor === 'restante') {
                    tipoRestante = config.tipo;
                    return;
                }

                let montoCalculado = 0;
                if (config.tipo_valor === 'porcentaje') {
                    montoCalculado = (montoInicial * config.monto) / 100;
                } else {
                    montoCalculado = config.monto;
                }

                montoCalculado = Math.round(montoCalculado * 100) / 100;
                totalAsignado += montoCalculado;

                comisiones.push({
                    tipo: config.tipo,
                    monto: montoCalculado,
                    tipo_valor: config.tipo_valor,
                    config_monto: config.monto
                });
            });
        }

        const adelantadas = obtenerComisionesAdelantadas(montoTotal);
        adelantadas.forEach((adelanto) => {
            totalAsignado += adelanto.monto;
            comisiones.push(adelanto);
        });

        if (tipoRestante) {
            const montoRestante = Math.max(0, Math.round((montoInicial - totalAsignado) * 100) / 100);
            comisiones.push({
                tipo: tipoRestante,
                monto: montoRestante,
                tipo_valor: 'restante',
                config_monto: null
            });
        }

        return comisiones;
    }

    function construirHtmlComisionesPagoInicial(montoInicial, montoTotal) {
        const comisiones = calcularComisionesPagoInicial(montoInicial, montoTotal);
        if (!comisiones.length) {
            return '';
        }

        let html = '';
        comisiones.forEach((c) => {
            let detalle = '';
            if (c.tipo_valor === 'porcentaje') {
                detalle = ` (${c.config_monto}%)`;
            } else if (c.tipo_valor === 'restante') {
                detalle = ' (restante)';
            } else if (c.tipo_valor === 'adelanto') {
                detalle = ' (adelanto)';
            }
            html += `<div class="summary-comision"><span>↳ ${c.tipo}${detalle}</span><span>-${formatearMoneda(c.monto)}</span></div>`;
        });
        return html;
    }

    function calcularPagos() {
        const montoTotalStr = document.getElementById('monto_total').value.replace(/[$,]/g, '');
        const montoTotal = parseFloat(montoTotalStr) || 0;
        const montoInicialStr = document.getElementById('monto_inicial').value.replace(/[$,]/g, '');
        const montoInicial = parseFloat(montoInicialStr) || 0;
        const montoBonificacionStr = document.getElementById('monto_bonificacion').value.replace(/[$,]/g, '');
        const montoBonificacion = parseFloat(montoBonificacionStr) || 0;

        const saldoPendiente = Math.max(montoTotal - montoInicial - montoBonificacion, 0);

        const montoCuotaInput = document.getElementById('monto_cuota');
        const freqInput = document.getElementById('frecuencia_cuotas');
        const numCuotasInput = document.getElementById('numero_cuotas');
        const duracionMesesInput = document.getElementById('duracion_meses');

        const montoCuotaStr = montoCuotaInput.value.replace(/[$,]/g, '');
        const montoCuotaIngresado = parseFloat(montoCuotaStr) || 0;
        const frecuenciaCuotas = parseInt(freqInput.value) || 0;

        if (saldoPendiente <= 0 || !montoTotal) {
            document.getElementById('pagos_card').style.display = 'none';
            document.getElementById('projection_card').style.display = 'none';
            numCuotasInput.value = '';
            if (duracionMesesInput) duracionMesesInput.value = '';
            return;
        }

        let numeroCuotas = 0;
        if (montoCuotaIngresado > 0) {
            numeroCuotas = Math.ceil(saldoPendiente / montoCuotaIngresado);
            if (numeroCuotas <= 0) numeroCuotas = 1;
            numCuotasInput.value = numeroCuotas;

            if (frecuenciaCuotas > 0 && duracionMesesInput) {
                let meses = (numeroCuotas * frecuenciaCuotas) / 30;
                duracionMesesInput.value = (Math.round(meses * 10) / 10) + ' meses';
            }
        } else {
            numCuotasInput.value = '';
            if (duracionMesesInput) duracionMesesInput.value = '';
        }

        if (!numeroCuotas || !montoCuotaIngresado) {
            document.getElementById('pagos_card').style.display = 'none';
            document.getElementById('projection_card').style.display = 'none';
            return;
        }

        const montoPorCuotaBase = montoCuotaIngresado;
        const totalCuotasBase = montoPorCuotaBase * (numeroCuotas - 1);
        const montoUltimaCuota = Math.round((saldoPendiente - totalCuotasBase) * 100) / 100;

        const fechaInicio = document.getElementById('fecha_inicio').value;
        let fechaFin = '';
        let fechaFinISO = '';
        if (fechaInicio && numeroCuotas > 0 && frecuenciaCuotas > 0) {
            const fecha = new Date(fechaInicio);
            fecha.setDate(fecha.getDate() + (frecuenciaCuotas * numeroCuotas));
            fechaFin = formatearFechaConMes(fecha);
            fechaFinISO = fecha.toISOString().split('T')[0];
            document.getElementById('fecha_fin_hidden').value = fechaFinISO;
        }

        let infoDetalle = '';
        if (numeroCuotas > 1 && Math.abs(montoUltimaCuota - montoPorCuotaBase) > 0.01) {
            infoDetalle = `
                    <small>
                        ${numeroCuotas - 1} cuotas de ${formatearMoneda(montoPorCuotaBase)} + 
                        1 cuota final de ${formatearMoneda(montoUltimaCuota)}
                    </small><br>
                `;
        } else if (numeroCuotas > 1) {
            infoDetalle = `
                    <small>
                        ${numeroCuotas} cuotas de ${formatearMoneda(montoPorCuotaBase)}
                    </small><br>
                `;
        }

        document.getElementById('pagos_info').innerHTML = `
                <strong>${numeroCuotas}</strong> parcialidades<br>
                ${infoDetalle}
                <small>Frecuencia: ${frecuenciaCuotas} días</small><br>
                ${fechaFin ? `<small>Finalización estimada: <strong>${fechaFin}</strong></small>` : ''}
            `;

        const comisionesInicialHtml = construirHtmlComisionesPagoInicial(montoInicial, montoTotal);

        document.getElementById('total_restante').innerHTML = `
                <div><span>Costo total</span><span>${formatearMoneda(montoTotal)}</span></div>
                <div><span>Inicial</span><span>-${formatearMoneda(montoInicial)}</span></div>
                ${comisionesInicialHtml}
                <div><span>Bonificación</span><span>-${formatearMoneda(montoBonificacion)}</span></div>
                <div class="summary-restante"><span><strong>Restante a liquidar</strong></span><span><strong>${formatearMoneda(saldoPendiente)}</strong></span></div>
            `;

        // Generar proyección detallada
        if (fechaInicio) {
            let trs = '';
            let currentDate = new Date(fechaInicio);
            
            // Añadir timezone offset para evitar que la fecha cambie por diferencia horaria local vs UTC
            currentDate.setMinutes(currentDate.getMinutes() + currentDate.getTimezoneOffset());
            
            for (let i = 1; i <= numeroCuotas; i++) {
                currentDate.setDate(currentDate.getDate() + frecuenciaCuotas);
                let montoCuotaActual = (i === numeroCuotas) ? montoUltimaCuota : montoPorCuotaBase;
                let fechaFormateada = formatearFechaConMes(currentDate);
                trs += `
                    <tr>
                        <td class="fw-bold">Cuota ${i}</td>
                        <td class="text-success fw-bold">${formatearMoneda(montoCuotaActual)}</td>
                        <td class="text-muted">${fechaFormateada}</td>
                    </tr>
                `;
            }
            document.getElementById('projection_body').innerHTML = trs;
            document.getElementById('projection_card').style.display = 'block';
        } else {
            document.getElementById('projection_card').style.display = 'none';
        }

        document.getElementById('pagos_card').style.display = 'block';
    }

    document.getElementById('monto_cuota').addEventListener('blur', function () {
        formatearCampoMoneda(this);
    });
    document.getElementById('monto_cuota').addEventListener('input', calcularPagos);
    document.getElementById('monto_cuota').addEventListener('focus', function () {
        if (this.value) {
            const valorLimpio = this.value.replace(/[$,]/g, '');
            if (!isNaN(parseFloat(valorLimpio))) {
                this.value = valorLimpio;
            }
        }
    });

    document.getElementById('frecuencia_cuotas').addEventListener('change', calcularPagos);
    document.getElementById('frecuencia_cuotas').addEventListener('input', calcularPagos);

    // Aplicar formateo solo al salir del campo (blur) para permitir edición libre
    document.getElementById('monto_inicial').addEventListener('blur', function () {
        formatearCampoMoneda(this);
    });
    document.getElementById('monto_inicial').addEventListener('input', calcularPagos);

    document.getElementById('monto_bonificacion').addEventListener('blur', function () {
        formatearCampoMoneda(this);
    });
    document.getElementById('monto_bonificacion').addEventListener('input', calcularPagos);

    // Permitir escribir libremente, formatear al perder foco
    document.getElementById('monto_inicial').addEventListener('focus', function () {
        if (this.value) {
            // Quitar formato para editar libremente
            const valorLimpio = this.value.replace(/[$,]/g, '');
            if (!isNaN(parseFloat(valorLimpio))) {
                this.value = valorLimpio;
            }
        }
    });

    document.getElementById('monto_bonificacion').addEventListener('focus', function () {
        if (this.value) {
            // Quitar formato para editar libremente
            const valorLimpio = this.value.replace(/[$,]/g, '');
            if (!isNaN(parseFloat(valorLimpio))) {
                this.value = valorLimpio;
            }
        }
    });

    document.getElementById('monto_total').addEventListener('input', calcularPagos);
    document.getElementById('monto_total').addEventListener('blur', function () {
        formatearCampoMoneda(this);
    });
    document.getElementById('monto_total').addEventListener('focus', function () {
        if (this.value) {
            // Quitar formato para editar libremente
            const valorLimpio = this.value.replace(/[$,]/g, '');
            if (!isNaN(parseFloat(valorLimpio))) {
                this.value = valorLimpio;
            }
        }
    });
    document.getElementById('fecha_inicio').addEventListener('change', calcularPagos);

    // Función para formatear campos de moneda
    function formatearCampoMoneda(input) {
        let value = input.value.replace(/[^0-9.]/g, ''); // Remover todo excepto números y punto

        // Si está vacío, dejarlo vacío (no forzar 0)
        if (value === '' || value === '.') {
            input.value = '';
            return;
        }

        // Asegurar solo un punto decimal
        const parts = value.split('.');
        if (parts.length > 2) {
            value = parts[0] + '.' + parts.slice(1).join('');
        }

        // Limitar a 2 decimales
        if (parts[1] && parts[1].length > 2) {
            value = parts[0] + '.' + parts[1].substring(0, 2);
        }

        // Formatear con símbolo de dólar y miles si hay valor
        const valorNumerico = parseFloat(value);
        if (!isNaN(valorNumerico) && valorNumerico >= 0) {
            input.value = formatearMoneda(valorNumerico);
        } else {
            input.value = '';
        }
    }

    // Función helper para formatear moneda con miles
    function formatearMoneda(valor) {
        if (valor === null || valor === undefined || isNaN(valor)) {
            return '$0.00';
        }
        return '$' + parseFloat(valor).toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    // Función helper para formatear fecha con mes en letras
    function formatearFechaConMes(fecha) {
        const meses = [
            'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
            'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'
        ];

        const dia = fecha.getDate();
        const mes = meses[fecha.getMonth()];
        const año = fecha.getFullYear();

        return `${dia} de ${mes} de ${año}`;
    }

    // Aplicar formato inicial a los campos de moneda al cargar la página (solo si tienen valor)
    window.addEventListener('DOMContentLoaded', function () {
        const camposMoneda = ['monto_inicial', 'monto_bonificacion', 'monto_cuota', 'monto_total'];
        camposMoneda.forEach(function (campoId) {
            const campo = document.getElementById(campoId);
            if (campo && campo.value && campo.value.trim() !== '') {
                // Solo formatear si ya tiene un valor (edición)
                if (!campo.value.startsWith('$')) {
                    const valor = parseFloat(campo.value.replace(/[^0-9.]/g, ''));
                    if (!isNaN(valor) && valor > 0) {
                        campo.value = formatearMoneda(valor);
                    } else {
                        campo.value = '';
                    }
                }
            }
            // Si está vacío, dejarlo vacío (aparecerá el placeholder)
        });
        calcularPagos();
    });
</script>

<!-- Validación del formulario -->
<script>
    // Validación en tiempo real para campos obligatorios
    function setupFormValidation() {
        const requiredFields = [
            'cliente_search', // Cambiado de cliente_id a cliente_search
            'contrato_id',
            'paquete_id',
            'fecha_inicio',
            'monto_cuota',
            'frecuencia_cuotas'
        ];

        requiredFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                // Validar al perder el foco
                field.addEventListener('blur', function () {
                    validateField(this);
                });

                // Limpiar error al cambiar el valor
                field.addEventListener('input', function () {
                    clearFieldError(this);
                });

                field.addEventListener('change', function () {
                    clearFieldError(this);
                });
            }
        });

        // Validar formulario antes de enviar
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function (e) {
                let hasErrors = false;

                // Limpiar campos de moneda antes de enviar
                const camposMoneda = ['monto_inicial', 'monto_bonificacion', 'monto_cuota'];
                camposMoneda.forEach(function (campoId) {
                    const campo = document.getElementById(campoId);
                    if (campo && campo.value) {
                        // Remover formato de moneda para envío
                        const valorLimpio = campo.value.replace(/[$,]/g, '');
                        if (valorLimpio && !isNaN(parseFloat(valorLimpio))) {
                            campo.value = parseFloat(valorLimpio);
                        }
                    }
                });

                // Validar campo de cliente usando el input oculto
                const clienteField = document.getElementById('cliente_id');
                const clienteSearchField = document.getElementById('cliente_search');
                if (clienteField && !validateField(clienteSearchField)) {
                    hasErrors = true;
                }

                // Validar otros campos
                ['contrato_id', 'paquete_id', 'fecha_inicio', 'monto_cuota', 'frecuencia_cuotas'].forEach(fieldId => {
                    const field = document.getElementById(fieldId);
                    if (field && !validateField(field)) {
                        hasErrors = true;
                    }
                });

                // Validar que el ID esté disponible (solo para contratos nuevos)
                const idField = document.getElementById('contrato_id');
                const idStatus = document.getElementById('id_status');
                if (idField && !idField.readOnly && idStatus.classList.contains('unavailable')) {
                    hasErrors = true;
                    idField.classList.add('is-invalid');
                    showNotification('El ID del contrato ya está en uso. Por favor ingrese un ID diferente.', 'error');
                }

                // Validar selects (buscadores) de comisiones
                const comisionSearchInputs = document.querySelectorAll('#comisiones_container .comision-empleado-search');
                comisionSearchInputs.forEach(searchInput => {
                    if (!validateField(searchInput)) {
                        hasErrors = true;
                    }
                });

                if (hasErrors) {
                    e.preventDefault();
                    showNotification('Por favor complete todos los campos obligatorios', 'error');

                    // Scroll al primer campo con error
                    const firstError = document.querySelector('.form-control.is-invalid');
                    if (firstError) {
                        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        firstError.focus();
                    }
                }
            });
        }
    }

    function validateField(field) {
        // Si es el campo de búsqueda de cliente, validar el campo oculto
        if (field.id === 'cliente_search') {
            const hiddenField = document.getElementById('cliente_id');
            const value = hiddenField ? hiddenField.value.trim() : '';
            let isValid = true;
            let errorMessage = '';

            if (!value) {
                isValid = false;
                errorMessage = 'Debe seleccionar un cliente';
            }

            // Aplicar estilos de validación al campo visible
            if (isValid) {
                field.classList.remove('is-invalid');
                removeCustomError(field);
            } else {
                field.classList.add('is-invalid');
                showCustomError(field, errorMessage);
            }

            return isValid;
        }

        // Buscador de empleado en comisiones
        if (field.classList && field.classList.contains('comision-empleado-search')) {
            const hiddenField = field.dataset.hiddenId
                ? document.getElementById(field.dataset.hiddenId)
                : null;
            const value = hiddenField ? hiddenField.value.trim() : '';
            let isValid = true;
            let errorMessage = '';

            if (!value) {
                isValid = false;
                errorMessage = 'Debe seleccionar un empleado para esta comisión';
            }

            if (isValid) {
                field.classList.remove('is-invalid');
                removeCustomError(field);
            } else {
                field.classList.add('is-invalid');
                showCustomError(field, errorMessage);
            }

            return isValid;
        }

        const value = field.value.trim();
        const fieldName = field.getAttribute('name');
        let isValid = true;
        let errorMessage = '';

        // Validar campos obligatorios
        if (field.hasAttribute('required') && !value) {
            isValid = false;
            errorMessage = getRequiredFieldMessage(fieldName);
        }
        // Validar ID del contrato
        else if (fieldName === 'id' && value) {
            if (!/^[0-9]{1,6}$/.test(value)) {
                isValid = false;
                errorMessage = 'El ID debe contener solo números y máximo 6 dígitos';
            } else if (!field.readOnly) {
                // Verificar disponibilidad solo si no es readonly (contratos nuevos)
                const idStatus = document.getElementById('id_status');
                if (idStatus && idStatus.classList.contains('unavailable')) {
                    isValid = false;
                    errorMessage = 'Este ID ya está en uso';
                }
            }
        }
        // Validar campos numéricos
        else if (field.type === 'number' && value) {
            const numValue = parseFloat(value);
            const min = field.getAttribute('min');

            if (isNaN(numValue)) {
                isValid = false;
                errorMessage = 'Debe ser un número válido';
            } else if (min && numValue < parseFloat(min)) {
                isValid = false;
                errorMessage = `Debe ser mayor o igual a ${min}`;
            }
        }

        // Aplicar estilos de validación
        if (isValid) {
            field.classList.remove('is-invalid');
            removeCustomError(field);
        } else {
            field.classList.add('is-invalid');
            showCustomError(field, errorMessage);
        }

        return isValid;
    }

    function getErrorAnchor(field) {
        // En selects con búsqueda, el mensaje va debajo del contenedor completo
        return field.closest('.searchable-select-container')
            || field.closest('.form-group')
            || field.parentNode;
    }

    function clearFieldError(field) {
        field.classList.remove('is-invalid');
        removeCustomError(field);
    }

    function showCustomError(field, message) {
        removeCustomError(field);

        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-text custom-error';
        errorDiv.textContent = message;

        getErrorAnchor(field).appendChild(errorDiv);
    }

    function removeCustomError(field) {
        const anchor = getErrorAnchor(field);
        const existingError = anchor.querySelector(':scope > .custom-error')
            || anchor.querySelector('.custom-error');
        if (existingError) {
            existingError.remove();
        }
    }

    function getRequiredFieldMessage(fieldName) {
        const messages = {
            'cliente_id': 'Debe seleccionar un cliente',
            'id': 'El ID del contrato es obligatorio',
            'paquete_id': 'Debe seleccionar un paquete',
            'fecha_inicio': 'La fecha de inicio es obligatoria',
            'duracion_meses': 'Debe seleccionar una duración',
            'numero_cuotas': 'El número de cuotas es obligatorio',
            'frecuencia_cuotas': 'La frecuencia de cuotas es obligatoria'
        };

        // Si es un campo de comisión, mostrar mensaje específico
        if (fieldName && fieldName.includes('comisiones[')) {
            return 'Debe seleccionar un empleado para esta comisión';
        }

        return messages[fieldName] || 'Este campo es obligatorio';
    }

    // Inicializar validación cuando se carga la página
    document.addEventListener('DOMContentLoaded', function () {
        setupFormValidation();
        setupIdValidation();
    });

    // Función para configurar la validación del ID en tiempo real
    function setupIdValidation() {
        const idInput = document.getElementById('contrato_id');
        const idStatus = document.getElementById('id_status');
        let debounceTimeout;

        if (!idInput || !idStatus) return;

        // Si es readonly (editando), no hacer validación
        if (idInput.readOnly) return;

        idInput.addEventListener('input', function () {
            const value = this.value.trim();

            // Limpiar timeout anterior
            clearTimeout(debounceTimeout);

            // Limpiar clases de estado
            idStatus.className = 'id-status-message';

            // Validar formato
            if (!value) {
                idStatus.textContent = '';
                idStatus.classList.remove('show');
                return;
            }

            if (!/^[0-9]{1,6}$/.test(value)) {
                idStatus.textContent = 'El ID debe contener solo números y máximo 6 dígitos';
                idStatus.classList.add('show', 'unavailable');
                return;
            }

            // Mostrar estado de verificación
            idStatus.textContent = 'Verificando disponibilidad...';
            idStatus.classList.add('show', 'checking');

            // Verificar disponibilidad con debounce
            debounceTimeout = setTimeout(() => {
                checkIdAvailability(value, idStatus);
            }, 500);
        });

        // Verificar disponibilidad al cargar si ya hay un valor
        if (idInput.value.trim() && /^[0-9]{1,6}$/.test(idInput.value.trim())) {
            checkIdAvailability(idInput.value.trim(), idStatus);
        }
    }

    // Función para verificar disponibilidad del ID
    function checkIdAvailability(id, statusElement) {
        const currentContratoId = '{{ $contrato?->id ?? "" }}';

        fetch('/ajax/check-contrato-id', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify({
                id: id,
                exclude_id: currentContratoId
            })
        })
            .then(response => response.json())
            .then(data => {
                statusElement.className = 'id-status-message show';

                if (data.available) {
                    statusElement.textContent = '✓ ID disponible';
                    statusElement.classList.add('available');
                } else {
                    statusElement.textContent = '✗ ID ya está en uso';
                    statusElement.classList.add('unavailable');
                }
            })
            .catch(error => {
                console.error('Error al verificar ID:', error);
                statusElement.className = 'id-status-message show unavailable';
                statusElement.textContent = 'Error al verificar disponibilidad';
            });
    }
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
        min-height: 100vh;
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
        position: relative;
    }

    /* Indicador visual para campos obligatorios */
    .form-control[required]+.form-label::after,
    select[required]+.form-label::after,
    input[required]~.form-label::after {
        content: " *";
        color: #da3633;
        font-weight: bold;
    }

    /* Estilo alternativo: agregar asterisco a labels de campos requeridos */
    .form-label[for="cliente_id"]::after,
    .form-label[for="cliente_search"]::after,
    .form-label[for="contrato_id"]::after,
    .form-label[for="paquete_id"]::after,
    .form-label[for="fecha_inicio"]::after,
    .form-label[for="frecuencia_cuotas"]::after,
    .form-label[for="monto_cuota"]::after {
        content: " *";
        color: #da3633;
        font-weight: bold;
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

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    .form-row.three-cols {
        grid-template-columns: 1fr 1fr 1fr;
    }

    /* Responsive para form-row */
    @media (max-width: 576px) {

        .form-row,
        .form-row.three-cols {
            grid-template-columns: 1fr;
        }
    }

    /* Resumen de pagos en dos columnas */
    .payment-summary {
        background: #f6f8fa;
        border: 1px solid #d1d9e0;
        border-radius: 6px;
        padding: 0;
        margin-top: 16px;
        overflow: hidden;
    }

    .payment-summary-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0;
    }

    .summary-column {
        padding: 16px;
        position: relative;
    }

    .summary-column:first-child {
        border-right: 1px solid #d1d9e0;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    }

    .summary-column:last-child {
        background: linear-gradient(135deg, #fefefe 0%, #f6f8fa 100%);
    }

    .summary-title {
        font-size: 13px;
        font-weight: 600;
        color: #374151;
        margin: 0 0 12px 0;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #e5e7eb;
        padding-bottom: 6px;
    }

    .summary-text {
        font-size: 14px;
        color: #24292f;
        line-height: 1.5;
    }

    .summary-text strong {
        color: #0969da;
        font-weight: 600;
    }

    .summary-breakdown {
        font-size: 13px;
        color: #374151;
        display: grid;
        gap: 8px;
    }

    .summary-breakdown > div {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 6px 0;
        border-bottom: 1px solid rgba(209, 217, 224, 0.5);
    }

    .summary-breakdown > div.summary-restante {
        font-weight: 600;
        color: #0969da;
        margin-top: 4px;
        padding-top: 10px;
        opacity: 1;
    }

    .summary-breakdown > div:not(.summary-restante) {
        opacity: 0.8;
    }

    .summary-breakdown > div.summary-comision {
        opacity: 1;
        font-size: 12px;
        color: #6b7280;
        padding: 3px 0 3px 16px;
        border-bottom: 1px dashed rgba(209, 217, 224, 0.6);
        gap: 8px;
    }

    .summary-breakdown > div.summary-comision span:last-child {
        color: #6d28d9;
        font-weight: 500;
        white-space: nowrap;
    }

    /* Responsive para el resumen de pagos */
    @media (max-width: 768px) {
        .payment-summary-grid {
            grid-template-columns: 1fr;
        }

        .summary-column:first-child {
            border-right: none;
            border-bottom: 1px solid #d1d9e0;
        }

        .summary-column {
            padding: 14px;
        }
    }

    /* Upload minimalista */
    .observaciones-documento-row {
        align-items: start;
    }

    .observaciones-documento-row #observaciones {
        min-height: 140px;
        height: 100%;
        resize: vertical;
    }

    .observaciones-documento-row .upload-area {
        min-height: 140px;
        padding: 24px 16px;
    }

    .upload-container {
        position: relative;
    }

    .upload-area {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px 20px;
        border: 2px dashed #d1d9e0;
        border-radius: 6px;
        background: #f6f8fa;
        cursor: pointer;
        transition: all 0.2s;
    }

    .upload-area:hover {
        border-color: #0969da;
        background: #ddf4ff;
    }

    .upload-area[style*="display: none"] {
        display: none !important;
    }

    .upload-content {
        text-align: center;
    }

    .upload-icon {
        width: 32px;
        height: 32px;
        color: #656d76;
        margin-bottom: 8px;
    }

    .upload-text {
        display: block;
        font-size: 14px;
        font-weight: 500;
        color: #24292f;
        margin-bottom: 4px;
    }

    .upload-hint {
        font-size: 12px;
        color: #656d76;
    }

    .upload-input {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
    }

    .file-preview {
        margin-top: 16px;
        border: 1px solid #d1d9e0;
        border-radius: 6px;
        overflow: hidden;
        transition: border-color 0.2s ease;
    }

    .file-preview:hover {
        border-color: #0969da;
    }

    .file-preview[style*="cursor: pointer"]:hover .preview-header {
        background: #ddf4ff;
        color: #0969da;
    }

    .preview-header {
        background: #f6f8fa;
        padding: 8px 12px;
        font-size: 12px;
        font-weight: 500;
        color: #656d76;
        border-bottom: 1px solid #d1d9e0;
    }

    .preview-info {
        padding: 8px 12px;
        font-size: 12px;
        color: #656d76;
    }

    .preview-actions {
        padding: 8px 12px;
        background: #f6f8fa;
        border-top: 1px solid #d1d9e0;
    }

    .btn-link {
        color: #0969da;
        text-decoration: none;
        font-size: 12px;
    }

    .btn-link:hover {
        text-decoration: underline;
    }

    /* Botones minimalistas */
    .form-actions {
        grid-column: 1 / -1;
        padding: 24px 32px;

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
        display: inline-block;
    }

    .btn-secondary {
        color: #24292f;
        background: #ffffff;
        border-color: #d1d9e0;
    }

    .btn-secondary:hover {
        background: #f3f4f6;
        border-color: #afb8c1;
    }

    .btn-primary {
        color: #ffffff;
        background: #0969da;
        border-color: #0969da;
    }

    .btn-primary:hover {
        background: #0860ca;
        border-color: #0860ca;
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

    .searchable-select-container > .error-text,
    .searchable-select-container > .custom-error {
        display: block;
        width: 100%;
        flex-basis: 100%;
        margin-top: 6px;
    }

    /* Estilos para el mensaje de estado del ID */
    .id-status-message {
        font-size: 12px;
        margin-top: 4px;
        padding: 4px 8px;
        border-radius: 4px;
        font-weight: 500;
        opacity: 0;
        transition: opacity 0.2s ease;
    }

    .id-status-message.show {
        opacity: 1;
    }

    .id-status-message.available {
        color: #10b981;
        background-color: rgba(16, 185, 129, 0.1);
        border: 1px solid rgba(16, 185, 129, 0.2);
    }

    .id-status-message.unavailable {
        color: #da3633;
        background-color: rgba(218, 54, 51, 0.1);
        border: 1px solid rgba(218, 54, 51, 0.2);
    }

    .id-status-message.checking {
        color: #656d76;
        background-color: rgba(101, 109, 118, 0.1);
        border: 1px solid rgba(101, 109, 118, 0.2);
    }

    #comisiones_section {
        margin-top: 8px;
        margin-bottom: 8px;
    }

    /* Comisiones en dos columnas de tarjetas */
    #comisiones_container {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }

    #comisiones_container .form-group,
    #comisiones_container .comision-card {
        background: #f6f8fa;
        border: 1px solid #d1d9e0;
        border-radius: 10px;
        padding: 14px 16px;
        margin-bottom: 0;
        display: flex;
        flex-direction: column;
        align-items: stretch;
        gap: 12px;
        min-width: 0;
    }

    .comision-card-header {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .comision-adelantar-container {
        display: flex;
        align-items: center;
        padding-top: 4px;
        border-top: 1px solid rgba(209, 217, 224, 0.7);
    }

    .comision-adelantar-label {
        font-size: 12px;
        color: #6b7280;
        cursor: pointer;
        user-select: none;
    }

    .adelantar-comision-checkbox:checked + .comision-adelantar-label {
        color: #6d28d9;
        font-weight: 600;
    }

    @media (max-width: 768px) {
        #comisiones_container {
            grid-template-columns: 1fr;
        }
    }

    /* Estilos para el acordeón */
    .accordion-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        cursor: pointer;
        user-select: none;
        padding: 8px 0;
        border-radius: 4px;
        transition: background-color 0.2s ease;
    }

    .accordion-header:hover {
        background-color: rgba(9, 105, 218, 0.05);
        margin: 0 -8px;
        padding: 8px;
    }

    .accordion-header .section-title {
        margin: 0;
        border-bottom: none;
        padding-bottom: 0;
        flex: 1;
    }

    .accordion-toggle {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        border-radius: 4px;
        transition: all 0.2s ease;
        color: #656d76;
    }

    .accordion-toggle:hover {
        background-color: rgba(9, 105, 218, 0.1);
        color: #0969da;
    }

    .accordion-icon {
        transition: transform 0.2s ease;
    }

    .accordion-header.open .accordion-icon {
        transform: rotate(180deg);
    }

    .accordion-content {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease, opacity 0.2s ease;
        opacity: 0;
    }

    .accordion-content.open {
        max-height: 500px;
        opacity: 1;
        padding-top: 16px;
    }

    .comision-info {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 2px;
        min-width: 0;
    }

    .comision-title {
        font-size: 14px;
        font-weight: 600;
        color: #24292f;
        margin: 0;
        position: relative;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Indicador de campo obligatorio para comisiones */
    .comision-title::after {
        content: " *";
        color: #da3633;
        font-weight: bold;
    }

    .comision-percentage {
        font-size: 12px;
        color: #656d76;
        margin: 0;
    }

    .comision-select-container {
        width: 100%;
        max-width: 100%;
        min-width: 0;
    }

    #comisiones_container .form-control {
        margin-bottom: 0;
        width: 100%;
    }



    /* Estilos para el select con búsqueda */
    .searchable-select {
        position: relative;
    }

    .search-input-container {
        position: relative;
        display: flex;
        align-items: center;
    }

    .search-input {
        width: 100%;
        padding-right: 60px;
        cursor: pointer;
    }

    .search-input:focus {
        cursor: text;
    }

    .search-icon {
        position: absolute;
        right: 32px;
        top: 50%;
        transform: translateY(-50%);
        color: #656d76;
        pointer-events: none;
        z-index: 2;
    }

    .dropdown-arrow {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #656d76;
        pointer-events: none;
        transition: transform 0.2s ease;
        z-index: 2;
    }

    .searchable-select.open .dropdown-arrow {
        transform: translateY(-50%) rotate(180deg);
    }

    .searchable-select-container {
        position: relative;
        z-index: 1;
    }

    .searchable-select-container:has(.searchable-select.open),
    .comision-card:has(.searchable-select.open) {
        z-index: 50;
        position: relative;
    }

    .dropdown-list {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: #ffffff;
        border: 1px solid #d1d9e0;
        border-radius: 6px;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.16);
        max-height: 200px;
        overflow-y: auto;
        z-index: 1000;
        display: none;
        margin-top: 2px;
    }

    .dropdown-list.dropdown-fixed {
        position: fixed;
        right: auto;
        margin-top: 0;
        z-index: 4000;
    }

    .dropdown-list.dropdown-up {
        box-shadow: 0 -8px 24px rgba(15, 23, 42, 0.16);
    }

    #comisiones_container,
    #comisiones_container .comision-card,
    #comisiones_section {
        overflow: visible;
    }

    .dropdown-list-rich {
        max-height: 280px;
    }

    .ruta-molde-option {
        padding: 10px 12px;
    }

    .ruta-molde-option-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 4px;
    }

    .ruta-molde-option-name {
        font-size: 14px;
        font-weight: 600;
        color: #24292f;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .ruta-molde-option-badge {
        flex-shrink: 0;
        font-size: 11px;
        font-weight: 600;
        color: #6d28d9;
        background: rgba(109, 40, 217, 0.08);
        border: 1px solid rgba(109, 40, 217, 0.18);
        border-radius: 999px;
        padding: 2px 8px;
        white-space: nowrap;
    }

    .ruta-molde-option-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        font-size: 12px;
        color: #656d76;
    }

    .ruta-molde-option-meta i {
        margin-right: 3px;
    }

    .ruta-molde-option.highlighted .ruta-molde-option-name,
    .ruta-molde-option.selected .ruta-molde-option-name {
        color: #0969da;
    }

    .visually-hidden {
        position: absolute !important;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border: 0;
    }

    .dropdown-list.show {
        display: block;
        animation: dropdownSlide 0.15s ease-out;
    }

    @keyframes dropdownSlide {
        from {
            opacity: 0;
            transform: translateY(-8px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .dropdown-option {
        padding: 10px 12px;
        cursor: pointer;
        border-bottom: 1px solid #f6f8fa;
        transition: background-color 0.1s ease;
    }

    .dropdown-option:last-child {
        border-bottom: none;
    }

    .dropdown-option:hover {
        background-color: #f6f8fa;
    }

    .dropdown-option.selected {
        background-color: #ddf4ff;
        color: #0969da;
        font-weight: 500;
    }

    .dropdown-option.highlighted {
        background-color: #f6f8fa;
    }

    .option-text {
        font-size: 14px;
        line-height: 20px;
    }

    .dropdown-option[data-value=""] .option-text {
        color: #656d76;
        font-style: italic;
    }

    /* Estilos cuando está activo/enfocado */
    .searchable-select.focused .search-input {
        border-color: #0969da;
        box-shadow: 0 0 0 3px rgba(9, 105, 218, 0.1);
    }

    /* Scroll personalizado para el dropdown */
    .dropdown-list::-webkit-scrollbar {
        width: 6px;
    }

    .dropdown-list::-webkit-scrollbar-track {
        background: #f6f8fa;
        border-radius: 3px;
    }

    .dropdown-list::-webkit-scrollbar-thumb {
        background: #d1d9e0;
        border-radius: 3px;
    }

    .dropdown-list::-webkit-scrollbar-thumb:hover {
        background: #afb8c1;
    }

    /* Responsive para searchable select */
    @media (max-width: 768px) {
        .dropdown-list {
            max-height: 160px;
        }
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

    /* Altura equilibrada para las columnas */
    .left-column .form-section:last-child,
    .right-column .form-section:last-child {
        flex-grow: 1;
    }

    /* Mejora visual para separación de secciones */
    .right-column .form-section:first-child {
        border-top: none;
    }

    /* Sticky para el resumen de pagos */
    @media (min-width: 969px) {
        .payment-summary {
            position: sticky;
            top: 20px;
        }
    }
</style>
<script>
    // Precios de paquetes desde backend
    const paquetesPrecios = @json(App\Models\Paquete::pluck('precio', 'id'));

    document.getElementById('paquete_id').addEventListener('change', function () {
        const paqueteId = this.value;
        const montoTotalInput = document.getElementById('monto_total');
        if (paquetesPrecios[paqueteId]) {
            const precio = parseFloat(paquetesPrecios[paqueteId]);
            montoTotalInput.value = formatearMoneda(precio);
        } else {
            montoTotalInput.value = '';
        }

        // Cargar porcentajes del paquete seleccionado
        if (paqueteId) {
            cargarPorcentajes(paqueteId);
        } else {
            document.getElementById('comisiones_section').style.display = 'none';
        }

        // Recalcular pagos después de actualizar el monto
        calcularPagos();
    });

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function cargarPorcentajes(paqueteId) {
        fetch(`/ajax/porcentajes/${paqueteId}`)
            .then(response => response.json())
            .then(data => {
                const container = document.getElementById('comisiones_container');
                container.innerHTML = '';

                if (data.porcentajes.length > 0) {
                    data.porcentajes.forEach((porcentaje, index) => {
                        const div = document.createElement('div');
                        div.className = 'form-group comision-card';

                        // Buscar comisión existente para este tipo de porcentaje
                        const comisionExistente = @json(isset($comisionesExistentes) ? $comisionesExistentes : (object) []);
                        const empleadoSeleccionado = comisionExistente[porcentaje.tipo_porcentaje] ?
                            comisionExistente[porcentaje.tipo_porcentaje].empleado_id : '';

                        const adelantarCheckboxHtml = esCreacionContrato ? `
                            <div class="comision-adelantar-container">
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input adelantar-comision-checkbox"
                                        type="checkbox"
                                        role="switch"
                                        name="adelantar_comisiones[${porcentaje.id}]"
                                        id="adelantar_comision_${porcentaje.id}"
                                        value="1"
                                        data-tipo="${porcentaje.tipo_porcentaje}"
                                        data-modo="${porcentaje.modo_comision || 'porcentaje'}"
                                        data-monto-fijo="${porcentaje.monto_fijo || 0}"
                                        data-cantidad="${porcentaje.cantidad_porcentaje || 0}">
                                    <label class="form-check-label comision-adelantar-label" for="adelantar_comision_${porcentaje.id}">
                                        Adelantar su comisión
                                    </label>
                                </div>
                            </div>
                        ` : '';

                        const empleadosOptionsHtml = Object.entries(data.empleados).map(([id, nombre]) => {
                            const nombreSeguro = escapeHtml(nombre);
                            const selectedAttr = String(id) === String(empleadoSeleccionado) ? 'data-selected="true"' : '';
                            return `
                                <div class="dropdown-option" data-value="${escapeHtml(id)}" data-label="${nombreSeguro}" ${selectedAttr}>
                                    <span class="option-text">${nombreSeguro}</span>
                                </div>
                            `;
                        }).join('');

                        div.innerHTML = `
                            <div class="comision-card-header">
                                <div class="d-flex align-items-center justify-content-center bg-secondary text-white rounded-circle fw-bold shadow-sm" style="min-width: 28px; height: 28px; font-size: 0.85rem;">
                                    ${porcentaje.orden || (index + 1)}
                                </div>
                                <div class="comision-info">
                                    <div class="comision-title">${escapeHtml(porcentaje.tipo_porcentaje)}</div>
                                    <div class="comision-percentage">${porcentaje.modo_comision === 'monto' ? '$' + parseFloat(porcentaje.monto_fijo).toFixed(2) : porcentaje.cantidad_porcentaje + '%'}</div>
                                </div>
                            </div>
                            <div class="comision-select-container">
                                <div class="searchable-select-container">
                                    <div class="searchable-select" id="comision-select-${porcentaje.id}">
                                        <div class="search-input-container">
                                            <input type="text"
                                                class="form-control search-input comision-empleado-search"
                                                id="comision_search_${porcentaje.id}"
                                                data-hidden-id="comision_${porcentaje.id}"
                                                placeholder="Buscar empleado..."
                                                autocomplete="off">
                                            <div class="search-icon">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                                    stroke="currentColor" stroke-width="2">
                                                    <circle cx="11" cy="11" r="8"></circle>
                                                    <path d="m21 21-4.35-4.35"></path>
                                                </svg>
                                            </div>
                                            <div class="dropdown-arrow">
                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                                                    stroke="currentColor" stroke-width="2">
                                                    <polyline points="6,9 12,15 18,9"></polyline>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="dropdown-list" id="comision-dropdown-${porcentaje.id}">
                                            <div class="dropdown-option" data-value="" data-label="Asignar"
                                                ${empleadoSeleccionado ? '' : 'data-selected="true"'}>
                                                <span class="option-text">Asignar</span>
                                            </div>
                                            ${empleadosOptionsHtml}
                                        </div>
                                    </div>
                                    <input type="hidden"
                                        name="comisiones[${porcentaje.id}]"
                                        id="comision_${porcentaje.id}"
                                        value="${empleadoSeleccionado ? escapeHtml(empleadoSeleccionado) : ''}"
                                        required>
                                </div>
                            </div>
                            ${adelantarCheckboxHtml}
                            <input type="hidden" name="porcentaje_ids[]" value="${porcentaje.id}">
                        `;
                        container.appendChild(div);
                    });

                    // Inicializar buscadores y validación de comisiones
                    data.porcentajes.forEach(porcentaje => {
                        initSearchableSelect({
                            selectId: `comision-select-${porcentaje.id}`,
                            searchId: `comision_search_${porcentaje.id}`,
                            dropdownId: `comision-dropdown-${porcentaje.id}`,
                            hiddenId: `comision_${porcentaje.id}`,
                            validateAs: true
                        });

                        const searchInput = document.getElementById(`comision_search_${porcentaje.id}`);
                        if (searchInput) {
                            searchInput.addEventListener('blur', function () {
                                setTimeout(() => validateField(this), 180);
                            });
                            searchInput.addEventListener('input', function () {
                                clearFieldError(this);
                            });
                        }

                        if (esCreacionContrato) {
                            const adelantarCheckbox = document.getElementById(`adelantar_comision_${porcentaje.id}`);
                            if (adelantarCheckbox) {
                                adelantarCheckbox.addEventListener('change', calcularPagos);
                            }
                        }
                    });

                    // Mostrar sección con animación
                    const section = document.getElementById('comisiones_section');
                    section.style.display = 'block';
                    section.style.opacity = '0';
                    section.style.transform = 'translateY(20px)';

                    setTimeout(() => {
                        section.style.transition = 'all 0.3s ease';
                        section.style.opacity = '1';
                        section.style.transform = 'translateY(0)';
                    }, 50);
                } else {
                    document.getElementById('comisiones_section').style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Error al cargar porcentajes:', error);
                document.getElementById('comisiones_section').style.display = 'none';
            });
    }

    // Cargar porcentajes al cargar la página si ya hay un paquete seleccionado
    window.addEventListener('DOMContentLoaded', function () {
        const paqueteSelect = document.getElementById('paquete_id');
        if (paqueteSelect.value) {
            cargarPorcentajes(paqueteSelect.value);
        }
        // Calcular pagos al cargar la página
        calcularPagos();
    });

    // Función mejorada para previsualizar PDF
    function previewPDF(input) {
        const previewDiv = document.getElementById('pdf-preview');
        const pdfEmbed = document.getElementById('pdf-embed');
        const pdfInfo = document.getElementById('pdf-info');
        const existingPdf = document.getElementById('existing-pdf');
        const uploadLabel = document.querySelector('label[for="documento"]');

        if (input.files && input.files[0]) {
            const file = input.files[0];

            // Validar que sea un PDF
            if (file.type !== 'application/pdf') {
                showNotification('Por favor selecciona solo archivos PDF', 'error');
                input.value = '';
                restoreExistingDocument();
                return;
            }

            // Validar tamaño (máximo 10MB)
            if (file.size > 10 * 1024 * 1024) {
                showNotification('El archivo es demasiado grande. Máximo 10MB permitido', 'error');
                input.value = '';
                restoreExistingDocument();
                return;
            }

            // Crear URL temporal para el archivo
            const fileUrl = URL.createObjectURL(file);

            // Mostrar información del archivo
            const fileSize = (file.size / 1024 / 1024).toFixed(2);
            if (pdfInfo) {
                pdfInfo.innerHTML = `
                    <strong>${file.name}</strong> (${fileSize} MB)
                `;
            }

            // Cambiar estilo del upload area
            if (uploadLabel) {
                uploadLabel.style.borderColor = '#0969da';
                uploadLabel.style.background = '#ddf4ff';
                uploadLabel.style.display = 'none';
            }

            // Mostrar previsualización con animación
            if (pdfEmbed) {
                pdfEmbed.src = fileUrl;

                // Configurar fallback si el embed no funciona
                const pdfFallback = document.getElementById('pdf-fallback');
                const pdfDownloadLink = document.getElementById('pdf-download-link');

                pdfEmbed.onload = function () {
                    if (pdfFallback) pdfFallback.style.display = 'none';
                    pdfEmbed.style.display = 'block';
                    URL.revokeObjectURL(fileUrl);
                };

                pdfEmbed.onerror = function () {
                    pdfEmbed.style.display = 'none';
                    if (pdfFallback) {
                        pdfFallback.style.display = 'block';
                        if (pdfDownloadLink) {
                            pdfDownloadLink.href = fileUrl;
                            pdfDownloadLink.textContent = `Abrir ${file.name}`;
                        }
                    }
                };

                // Timeout fallback para navegadores que no soporten embed
                setTimeout(() => {
                    if (pdfEmbed.style.display !== 'none' && !pdfEmbed.offsetHeight) {
                        pdfEmbed.style.display = 'none';
                        if (pdfFallback) {
                            pdfFallback.style.display = 'block';
                            if (pdfDownloadLink) {
                                pdfDownloadLink.href = fileUrl;
                                pdfDownloadLink.textContent = `Abrir ${file.name}`;
                            }
                        }
                    }
                }, 1000);
            }

            if (previewDiv) {
                previewDiv.style.display = 'block';
                previewDiv.style.opacity = '0';
                previewDiv.style.transform = 'translateY(20px)';

                setTimeout(() => {
                    previewDiv.style.transition = 'all 0.3s ease';
                    previewDiv.style.opacity = '1';
                    previewDiv.style.transform = 'translateY(0)';
                }, 50);
            }

            // Ocultar documento existente si hay uno
            if (existingPdf) {
                existingPdf.style.display = 'none';
            }

            showNotification('Archivo cargado correctamente', 'success');
        } else {
            // Si no hay archivo, restaurar estado anterior
            restoreExistingDocument();
        }
    }

    // Función para mostrar notificaciones
    function showNotification(message, type = 'info') {
        // Crear elemento de notificación si no existe
        let notification = document.getElementById('notification');
        if (!notification) {
            notification = document.createElement('div');
            notification.id = 'notification';
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 16px 20px;
                border-radius: 12px;
                color: white;
                font-weight: 500;
                z-index: 9999;
                transform: translateX(400px);
                transition: all 0.3s ease;
                box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            `;
            document.body.appendChild(notification);
        }

        // Establecer color según el tipo
        const colors = {
            success: '#10b981',
            error: '#ef4444',
            info: '#6366f1'
        };

        notification.style.background = colors[type] || colors.info;
        notification.textContent = message;

        // Mostrar notificación
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 100);

        // Ocultar después de 3 segundos
        setTimeout(() => {
            notification.style.transform = 'translateX(400px)';
        }, 3000);
    }

    // Select con búsqueda reutilizable (cliente, ruta molde, etc.)
    function initSearchableSelect({ selectId, searchId, dropdownId, hiddenId, clearSearchWhenEmpty = true, validateAs }) {
        const searchableSelect = document.getElementById(selectId);
        const searchInput = document.getElementById(searchId);
        const dropdown = document.getElementById(dropdownId);
        const hiddenInput = document.getElementById(hiddenId);

        if (!searchableSelect || !searchInput || !dropdown || !hiddenInput) {
            return;
        }

        const options = dropdown.querySelectorAll('.dropdown-option');
        let currentHighlight = -1;
        let isOpen = false;

        const preselected = dropdown.querySelector('[data-selected="true"]') ||
            (hiddenInput.value ? dropdown.querySelector(`[data-value="${hiddenInput.value}"]`) : null);

        function getOptionLabel(option) {
            return option.dataset.label
                || option.querySelector('.option-text')?.textContent?.trim()
                || '';
        }

        function getOptionSearchText(option) {
            return (
                option.dataset.search
                || option.querySelector('.option-text')?.textContent
                || getOptionLabel(option)
            ).toLowerCase();
        }

        if (preselected) {
            hiddenInput.value = preselected.dataset.value || '';
            // No mostrar en el input el texto de opciones vacías ("Asignar", "Sin asignar", etc.)
            searchInput.value = hiddenInput.value ? getOptionLabel(preselected) : '';
            preselected.classList.add('selected');
        }

        function positionDropdown() {
            const rect = searchInput.getBoundingClientRect();
            const maxHeight = dropdown.classList.contains('dropdown-list-rich') ? 280 : 200;
            const spaceBelow = window.innerHeight - rect.bottom - 8;
            const spaceAbove = rect.top - 8;
            const openUp = spaceBelow < 140 && spaceAbove > spaceBelow;

            dropdown.classList.add('dropdown-fixed');
            dropdown.style.left = `${Math.max(8, rect.left)}px`;
            dropdown.style.width = `${rect.width}px`;
            dropdown.style.maxHeight = `${Math.min(maxHeight, openUp ? spaceAbove : spaceBelow)}px`;

            if (openUp) {
                dropdown.classList.add('dropdown-up');
                dropdown.style.top = 'auto';
                dropdown.style.bottom = `${window.innerHeight - rect.top + 2}px`;
            } else {
                dropdown.classList.remove('dropdown-up');
                dropdown.style.top = `${rect.bottom + 2}px`;
                dropdown.style.bottom = 'auto';
            }
        }

        function resetDropdownPosition() {
            dropdown.classList.remove('dropdown-fixed', 'dropdown-up');
            dropdown.style.left = '';
            dropdown.style.width = '';
            dropdown.style.top = '';
            dropdown.style.bottom = '';
            dropdown.style.maxHeight = '';
        }

        function handleViewportChange() {
            if (isOpen) {
                positionDropdown();
            }
        }

        function openDropdown() {
            if (!isOpen) {
                dropdown.classList.add('show');
                searchableSelect.classList.add('open', 'focused');
                isOpen = true;
                currentHighlight = -1;
                // Al abrir, mostrar todas las opciones (el filtro aplica al escribir)
                filterOptions(true);
                positionDropdown();
                window.addEventListener('scroll', handleViewportChange, true);
                window.addEventListener('resize', handleViewportChange);
            }
        }

        function closeDropdown() {
            if (isOpen) {
                dropdown.classList.remove('show');
                searchableSelect.classList.remove('open', 'focused');
                isOpen = false;
                currentHighlight = -1;
                resetDropdownPosition();
                window.removeEventListener('scroll', handleViewportChange, true);
                window.removeEventListener('resize', handleViewportChange);

                if (!hiddenInput.value) {
                    if (clearSearchWhenEmpty) {
                        searchInput.value = '';
                    }
                } else {
                    const selectedOption = dropdown.querySelector(`[data-value="${CSS.escape(hiddenInput.value)}"]`)
                        || dropdown.querySelector(`[data-value="${hiddenInput.value}"]`);
                    if (selectedOption) {
                        searchInput.value = getOptionLabel(selectedOption);
                    }
                }
            }
        }

        function filterOptions(showAll = false) {
            let searchTerm = searchInput.value.toLowerCase().trim();

            // Si el texto del input es solo la etiqueta seleccionada (o no hay selección),
            // no filtrar: mostrar el listado completo.
            if (showAll) {
                if (!hiddenInput.value) {
                    searchTerm = '';
                } else {
                    const selectedOption = dropdown.querySelector(`[data-value="${CSS.escape(hiddenInput.value)}"]`)
                        || Array.from(options).find(opt => opt.dataset.value === hiddenInput.value);
                    const selectedLabel = selectedOption ? getOptionLabel(selectedOption).toLowerCase() : '';
                    if (searchTerm === selectedLabel) {
                        searchTerm = '';
                    }
                }
            }

            const visibleOptions = [];

            options.forEach(option => {
                const text = getOptionSearchText(option);
                const isEmptyOption = option.dataset.value === '';
                const isVisible = searchTerm === ''
                    || text.includes(searchTerm)
                    || (isEmptyOption && searchTerm === '');

                option.style.display = isVisible ? 'block' : 'none';
                if (isVisible) {
                    visibleOptions.push(option);
                }
            });

            return visibleOptions;
        }

        function selectOption(option) {
            options.forEach(opt => opt.classList.remove('selected'));
            option.classList.add('selected');
            hiddenInput.value = option.dataset.value || '';
            searchInput.value = hiddenInput.value ? getOptionLabel(option) : '';
            closeDropdown();

            if (typeof clearFieldError === 'function') {
                clearFieldError(searchInput);
            }

            if (validateAs && typeof validateField === 'function') {
                setTimeout(() => validateField(searchInput), 100);
            }
        }

        function navigateOptions(direction) {
            const visibleOptions = Array.from(options).filter(opt => opt.style.display !== 'none');
            if (visibleOptions.length === 0) return;

            options.forEach(opt => opt.classList.remove('highlighted'));

            if (direction === 'down') {
                currentHighlight = Math.min(currentHighlight + 1, visibleOptions.length - 1);
            } else if (direction === 'up') {
                currentHighlight = Math.max(currentHighlight - 1, 0);
            }

            if (currentHighlight >= 0 && currentHighlight < visibleOptions.length) {
                visibleOptions[currentHighlight].classList.add('highlighted');
                visibleOptions[currentHighlight].scrollIntoView({ block: 'nearest' });
            }
        }

        searchInput.addEventListener('click', function (e) {
            e.stopPropagation();
            openDropdown();
        });

        searchInput.addEventListener('input', function () {
            if (!isOpen) {
                openDropdown();
            }

            const selectedOption = hiddenInput.value
                ? (dropdown.querySelector(`[data-value="${CSS.escape(hiddenInput.value)}"]`)
                    || Array.from(options).find(opt => opt.dataset.value === hiddenInput.value))
                : null;
            const selectedText = selectedOption ? getOptionLabel(selectedOption) : null;

            if (!this.value || (hiddenInput.value && this.value !== selectedText)) {
                hiddenInput.value = '';
                options.forEach(opt => opt.classList.remove('selected'));
            }

            filterOptions(false);
            positionDropdown();
        });

        searchInput.addEventListener('keydown', function (e) {
            if (!isOpen && (e.key === 'ArrowDown' || e.key === 'ArrowUp' || e.key === 'Enter')) {
                e.preventDefault();
                openDropdown();
                return;
            }

            switch (e.key) {
                case 'ArrowDown':
                    e.preventDefault();
                    navigateOptions('down');
                    break;
                case 'ArrowUp':
                    e.preventDefault();
                    navigateOptions('up');
                    break;
                case 'Enter':
                    e.preventDefault();
                    const highlighted = dropdown.querySelector('.highlighted');
                    if (highlighted) {
                        selectOption(highlighted);
                    }
                    break;
                case 'Escape':
                    e.preventDefault();
                    closeDropdown();
                    break;
            }
        });

        searchInput.addEventListener('blur', function () {
            setTimeout(() => {
                if (!searchableSelect.contains(document.activeElement)) {
                    closeDropdown();
                }
            }, 150);
        });

        options.forEach(option => {
            option.addEventListener('mousedown', function (e) {
                e.preventDefault();
            });

            option.addEventListener('click', function (e) {
                e.stopPropagation();
                selectOption(this);
            });

            option.addEventListener('mouseenter', function () {
                options.forEach(opt => opt.classList.remove('highlighted'));
                this.classList.add('highlighted');
                currentHighlight = Array.from(options).filter(opt => opt.style.display !== 'none').indexOf(this);
            });
        });

        document.addEventListener('click', function (e) {
            if (!searchableSelect.contains(e.target)) {
                closeDropdown();
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        initSearchableSelect({
            selectId: 'cliente-select',
            searchId: 'cliente_search',
            dropdownId: 'cliente-dropdown',
            hiddenId: 'cliente_id',
            validateAs: true
        });

        initSearchableSelect({
            selectId: 'ruta-molde-select',
            searchId: 'ruta_plantilla_search',
            dropdownId: 'ruta-molde-dropdown',
            hiddenId: 'ruta_plantilla_id'
        });
    });


</script>