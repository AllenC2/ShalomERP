<!-- Formulario Minimalista en 2 Columnas -->
<div class="minimal-form">


<div class="form-container"
        
        <div class="form-layout">
            <!-- Columna Izquierda -->
            <div class="left-column">
                
                <!-- Información Básica -->
                <div class="form-section">
                    <h6 class="section-title">Información de la Comisión</h6>
                    
                    
                    
                    <div class="form-row">
                        <div class="form-group">
                        <label for="contrato_id" class="form-label">
                            Contrato
                        </label>
                        <select name="contrato_id" class="form-control @error('contrato_id') is-invalid @enderror" id="contrato_id" {{ isset($comisione) && $comisione->id ? 'disabled' : '' }}>
                            <option value="">Seleccionar contrato</option>
                            @if(isset($contratos))
                                @foreach ($contratos as $id => $contrato)
                                    <option value="{{ $id }}" {{ old('contrato_id', $comisione?->contrato_id) == $id ? 'selected' : '' }}>
                                        {{ $contrato }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        @if(isset($comisione) && $comisione->id)
                            <input type="hidden" name="contrato_id" value="{{ $comisione->contrato_id }}">
                        @endif
                        @error('contrato_id')<div class="error-text">{{ $message }}</div>@enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="empleado_id" class="form-label">Empleado</label>
                        <select name="empleado_id" class="form-control @error('empleado_id') is-invalid @enderror" id="empleado_id">
                            <option value="">Seleccionar empleado</option>
                            @if(isset($empleados))
                                @foreach ($empleados as $id => $empleado)
                                    <option value="{{ $id }}" {{ old('empleado_id', $comisione?->empleado_id) == $id ? 'selected' : '' }}>
                                        {{ $empleado }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        @error('empleado_id')<div class="error-text">{{ $message }}</div>@enderror
                    </div>

                        <div class="form-group">
                            <label for="fecha_comision" class="form-label">Fecha de Comisión</label>
                            <input type="date" name="fecha_comision" class="form-control @error('fecha_comision') is-invalid @enderror"
                                   value="{{ old('fecha_comision', $comisione?->fecha_comision ? $comisione->fecha_comision->format('Y-m-d') : now()->format('Y-m-d')) }}"
                                   id="fecha_comision">
                            @error('fecha_comision')<div class="error-text">{{ $message }}</div>@enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="tipo_comision" class="form-label">Tipo de Comisión</label>
                            <select name="tipo_comision" class="form-control @error('tipo_comision') is-invalid @enderror" id="tipo_comision">
                                <option value="">Seleccionar tipo</option>
                                <option value="asesor" {{ old('tipo_comision', $comisione?->tipo_comision) == 'asesor' ? 'selected' : '' }}>Asesor</option>
                                <option value="lider" {{ old('tipo_comision', $comisione?->tipo_comision) == 'lider' ? 'selected' : '' }}>Líder</option>
                                <option value="coordinador" {{ old('tipo_comision', $comisione?->tipo_comision) == 'coordinador' ? 'selected' : '' }}>Coordinador</option>
                                <option value="gerente" {{ old('tipo_comision', $comisione?->tipo_comision) == 'gerente' ? 'selected' : '' }}>Gerente</option>
                                <option value="gerencia" {{ old('tipo_comision', $comisione?->tipo_comision) == 'gerencia' ? 'selected' : '' }}>Gerencia</option>
                                <option value="administrador" {{ old('tipo_comision', $comisione?->tipo_comision) == 'administrador' ? 'selected' : '' }}>Administrador</option>
                            </select>
                            @error('tipo_comision')<div class="error-text">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="nombre_paquete" class="form-label">Nombre del Paquete</label>
                            <input type="text" name="nombre_paquete" class="form-control @error('nombre_paquete') is-invalid @enderror" 
                                   value="{{ old('nombre_paquete', $comisione?->nombre_paquete) }}" 
                                   id="nombre_paquete" placeholder="Nombre del paquete">
                            @error('nombre_paquete')<div class="error-text">{{ $message }}</div>@enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="porcentaje" class="form-label">Porcentaje (%)</label>
                            <input type="number" name="porcentaje" class="form-control @error('porcentaje') is-invalid @enderror" 
                                   value="{{ old('porcentaje', $comisione?->porcentaje) }}" 
                                   id="porcentaje" placeholder="0.00" step="0.01" min="0" max="100">
                            @error('porcentaje')<div class="error-text">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="monto" class="form-label">Monto</label>
                            <input type="text" name="monto" class="form-control @error('monto') is-invalid @enderror" 
                                   value="{{ old('monto', isset($comisione) && $comisione->monto ? '$' . number_format($comisione->monto, 2, '.', ',') : '') }}" 
                                   id="monto" placeholder="$0.00">
                            @error('monto')<div class="error-text">{{ $message }}</div>@enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="estado" class="form-label">Estado</label>
                            <select name="estado" class="form-control @error('estado') is-invalid @enderror" id="estado" required>
                                <option value="">Seleccionar estado</option>
                                <option value="Pendiente" {{ old('estado', $comisione?->estado) == 'Pendiente' ? 'selected' : '' }}>Pendiente</option>
                                <option value="Pagada" {{ old('estado', $comisione?->estado) == 'Pagada' ? 'selected' : '' }}>Pagada</option>
                                <option value="Cancelada" {{ old('estado', $comisione?->estado) == 'Cancelada' ? 'selected' : '' }}>Cancelada</option>
                            </select>
                            @error('estado')<div class="error-text">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="observaciones" class="form-label">Observaciones</label>
                        <textarea name="observaciones" class="form-control @error('observaciones') is-invalid @enderror" 
                               id="observaciones" rows="3" placeholder="Observaciones adicionales de la comisión">{{ old('observaciones', $comisione?->observaciones) }}</textarea>
                        @error('observaciones')<div class="error-text">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <!-- Columna Derecha -->
            <div class="right-column">
                
                <!-- Documentación -->
                <div class="form-section">
                    <h6 class="section-title">Documento de la comisión</h6>
                    
                    <div class="upload-container">

                        <label for="documento" class="upload-area" id="upload-area">
                            <div class="upload-content" id="upload-content">
                                <svg class="upload-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                    <polyline points="7,10 12,15 17,10"/>
                                    <line x1="12" y1="15" x2="12" y2="3"/>
                                </svg>
                                <span class="upload-text">Subir documento en PDF</span>
                                <span class="upload-hint">Arrastra aquí o haz clic</span>
                            </div>
                        </label>
                        
                        <input type="file" name="documento" class="upload-input @error('documento') is-invalid @enderror" 
                               id="documento" accept=".pdf" onchange="previewPDF(this)">
                        @error('documento')<div class="error-text">{{ $message }}</div>@enderror

                        <!-- Preview -->
                        <div id="pdf-preview" class="file-preview" style="display: none; cursor: pointer;" onclick="triggerFileInput()">
                            <div class="preview-header">
                                <span>Vista previa (haz clic para cambiar)</span>
                            </div>
                            <div class="pdf-container" style="height: 600px; background: #f8f9fa; display: flex; align-items: center; justify-content: center;">
                                <embed id="pdf-embed" src="" type="application/pdf" width="100%" height="100%" style="border: none;">
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
                        @if(isset($comisione) && $comisione->documento && $comisione->documento !== 'No')
                        <div id="existing-pdf" class="file-preview" style="cursor: pointer;" onclick="triggerFileInput()">
                            <div class="preview-header">
                                <span>Documento actual (haz clic para cambiar)</span>
                            </div>
                            <embed src="{{ asset('storage/' . $comisione->documento) }}" type="application/pdf" width="100%" height="600px">
                            <div class="preview-actions">
                                <a href="{{ asset('storage/' . $comisione->documento) }}" target="_blank" class="btn-link" onclick="event.stopPropagation();">
                                    Ver completo
                                </a>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Resumen de la Comisión -->
                <div class="form-section">
                    <h6 class="section-title">Resumen de la Comisión</h6>
                    
                    <div class="commission-summary">
                        <div class="commission-summary-grid">
                            <div class="summary-column">
                                <div class="summary-title">Información General</div>
                                <div class="summary-text">
                                    <div class="summary-breakdown">
                                        <div>
                                            <span>Tipo:</span>
                                            <span id="summary-tipo">-</span>
                                        </div>
                                        <div>
                                            <span>Estado:</span>
                                            <span id="summary-estado">-</span>
                                        </div>
                                        <div>
                                            <span>Empleado:</span>
                                            <span id="summary-empleado">-</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="summary-column">
                                <div class="summary-title">Cálculo</div>
                                <div class="summary-text">
                                    <div class="summary-breakdown">
                                        <div>
                                            <span>Porcentaje:</span>
                                            <span id="summary-porcentaje">0%</span>
                                        </div>
                                        <div>
                                            <span>Paquete:</span>
                                            <span id="summary-paquete">-</span>
                                        </div>
                                        <div>
                                            <span><strong>Monto Total:</strong></span>
                                            <span id="summary-monto"><strong>$0.00</strong></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Botones de acción -->
        <div class="form-actions">
            <a href="{{ route('comisiones.index') }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">
                {{ isset($comisione) && $comisione->id ? 'Actualizar Comisión' : 'Crear Comisión' }}
            </button>
        </div>
    </div>
</div>

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
    .form-label[for="monto"]::after,
    .form-label[for="estado"]::after {
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

    /* Responsive para form-row */
    @media (max-width: 576px) {
        .form-row {
            grid-template-columns: 1fr;
        }
    }

    /* Resumen de comisión en dos columnas */
    .commission-summary {
        background: #f6f8fa;
        border: 1px solid #d1d9e0;
        border-radius: 6px;
        padding: 0;
        margin-top: 16px;
        overflow: hidden;
    }

    .commission-summary-grid {
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

    .summary-breakdown div {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 6px 0;
        border-bottom: 1px solid rgba(209, 217, 224, 0.5);
    }

    .summary-breakdown div:last-child {
        font-weight: 600;
        color: #0969da;
        margin-top: 4px;
        padding-top: 10px;
    }

    .summary-breakdown div:not(:last-child) {
        opacity: 0.8;
    }

    /* Responsive para el resumen de comisión */
    @media (max-width: 768px) {
        .commission-summary-grid {
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

    /* Campo bloqueado */
    .locked-indicator {
        color: #ffc107;
        font-size: 0.75rem;
        font-weight: 500;
        margin-left: 8px;
    }

    select:disabled {
        background-color: #f8f9fa !important;
        border-color: #dee2e6 !important;
        opacity: 0.75;
    }

    .form-text.text-muted {
        color: #6c757d !important;
        font-size: 0.8rem;
        margin-top: 4px;
    }
</style>

<script>
    // Variables globales para el funcionamiento del formulario
    let hasExistingDocument = document.getElementById('existing-pdf') !== null;
    
    // Inicializar el estado del upload area
    window.addEventListener('DOMContentLoaded', function() {
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
        
        // Inicializar el resumen
        updateSummary();
        
        // Agregar event listeners para actualizar el resumen
        document.getElementById('tipo_comision').addEventListener('change', updateSummary);
        document.getElementById('estado').addEventListener('change', updateSummary);
        document.getElementById('empleado_id').addEventListener('change', updateSummary);
        document.getElementById('porcentaje').addEventListener('input', updateSummary);
        document.getElementById('nombre_paquete').addEventListener('input', updateSummary);
        document.getElementById('monto').addEventListener('input', updateSummary);
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
            
            if (file.type === 'application/pdf') {
                const url = URL.createObjectURL(file);
                
                // Ocultar upload area y documento existente
                if (uploadLabel) uploadLabel.style.display = 'none';
                if (existingPdf) existingPdf.style.display = 'none';
                
                // Mostrar preview
                previewDiv.style.display = 'block';
                pdfEmbed.src = url;
                pdfInfo.textContent = `${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)`;
                
                // Limpiar URL cuando se quite el archivo
                input.addEventListener('change', function() {
                    if (!this.files || !this.files[0]) {
                        URL.revokeObjectURL(url);
                        restoreExistingDocument();
                    }
                });
            }
        } else {
            restoreExistingDocument();
        }
    }

    // Función para formatear moneda
    function formatearMoneda(valor) {
        return '$' + parseFloat(valor).toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    // Función para limpiar el formato de moneda y obtener el número
    function limpiarMoneda(texto) {
        return parseFloat(texto.replace(/[$,]/g, '')) || 0;
    }

    // Formatear el campo de monto mientras se escribe
    document.getElementById('monto').addEventListener('input', function() {
        let valor = this.value.replace(/[$,]/g, '');
        if (valor && !isNaN(valor)) {
            this.value = formatearMoneda(valor);
        }
        updateSummary();
    });

    // Función para actualizar el resumen
    function updateSummary() {
        const tipoComision = document.getElementById('tipo_comision').value || '-';
        const estado = document.getElementById('estado').value || '-';
        const empleadoSelect = document.getElementById('empleado_id');
        const empleado = empleadoSelect.selectedOptions[0]?.text || '-';
        const porcentaje = document.getElementById('porcentaje').value || '0';
        const paquete = document.getElementById('nombre_paquete').value || '-';
        const monto = document.getElementById('monto').value || '$0.00';

        document.getElementById('summary-tipo').textContent = tipoComision;
        document.getElementById('summary-estado').textContent = estado;
        document.getElementById('summary-empleado').textContent = empleado;
        document.getElementById('summary-porcentaje').textContent = porcentaje + '%';
        document.getElementById('summary-paquete').textContent = paquete;
        document.getElementById('summary-monto').textContent = monto;
    }

    // Función para mostrar notificaciones
    function showNotification(message, type = 'info') {
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
