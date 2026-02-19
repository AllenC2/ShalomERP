<!-- Formulario Minimalista en 2 Columnas -->
<div class="minimal-form">
    <form method="POST" action="{{ $pago->exists ? route('pagos.update', $pago->id) : route('pagos.store') }}"
        enctype="multipart/form-data" class="form-container">
        @csrf
        @if($pago->exists)
            @method('PATCH')
        @endif

        <!-- Información del contrato (si existe) -->
        @if($pago->exists && $pago->contrato)
            <div class="contract-info-section">
                <div class="contract-card">
                    <div class="contract-header">
                        <div class="contract-icon">
                            <i class="bi bi-file-earmark-text"></i>
                        </div>
                        <div class="contract-details">
                            <h6 class="contract-title">Editando pago del contrato {{$pago->contrato->paquete->nombre}}
                                #{{$pago->contrato->id}}</h6>
                            <p class="contract-client">Cliente: {{$pago->contrato->cliente->nombre}}
                                {{$pago->contrato->cliente->apellido}}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @elseif(isset($contrato) && $contrato)
            <div class="contract-info-section">
                <div class="contract-card">
                    <div class="contract-header">
                        <div class="contract-icon">
                            <i class="bi bi-person-fill"></i>
                        </div>
                        <div class="contract-details">
                            <h6 class="contract-title">{{$contrato->paquete->nombre}} #{{$contrato->id}}</h6>
                            <p class="contract-client">{{$contrato->cliente->nombre}} {{$contrato->cliente->apellido}}</p>
                            <small class="contract-date">
                                <i class="bi bi-calendar-event me-1"></i>
                                Contrato desde
                                {{ \Carbon\Carbon::parse($contrato->fecha_inicio)->translatedFormat('d \d\e F \d\e Y') }}
                            </small>
                        </div>
                        <div class="contract-amount">
                            @php
                                $montoInicial = $contrato->monto_inicial ?? 0;
                                $montoBonificacion = $contrato->monto_bonificacion ?? 0;
                                $montoFinanciado = $contrato->monto_total - $montoInicial - $montoBonificacion;
                                $cuotaCalculada = $contrato->numero_cuotas > 0 ? $montoFinanciado / $contrato->numero_cuotas : 0;
                            @endphp
                            <div class="amount-label">Cuota regular</div>
                            <div class="amount-value">${{ number_format($cuotaCalculada, 2) }}</div>
                            <div class="amount-installments">{{ $contrato->numero_cuotas }} cuotas</div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="form-layout">
            <!-- Columna Izquierda -->
            <div class="left-column">

                <!-- Información Básica del Pago -->
                <div class="form-section">
                    <h6 class="section-title">Información del Pago</h6>

                    <!-- Campos ocultos -->
                    <input type="hidden" name="contrato_id"
                        value="{{ old('contrato_id', $contrato_id ?? $pago?->contrato_id) }}" id="contrato_id">
                    <input type="hidden" name="estado" value="hecho">

                    <div class="form-row">
                        <div class="form-group">
                            <label for="fecha_pago" class="form-label">Fecha y Hora del Pago <span
                                    class="text-danger">*</span></label>
                            <input type="datetime-local" name="fecha_pago"
                                class="form-control @error('fecha_pago') is-invalid @enderror"
                                value="{{ old('fecha_pago', $pago?->fecha_pago?->format('Y-m-d\TH:i') ?? now()->format('Y-m-d\TH:i')) }}"
                                id="fecha_pago">
                            @error('fecha_pago')<div class="error-text">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-group">
                            <label for="monto" class="form-label">Monto del Pago <span
                                    class="text-danger">*</span></label>
                            @php
                                $maxMonto = null;
                                $valorPorDefecto = '';

                                if (!$pago->exists && isset($contrato) && $contrato) {
                                    // Para nuevos pagos desde contrato (parcialidades)
                                    if (isset($proximoPagoPendiente) && $proximoPagoPendiente) {
                                        // Usar el monto restante (después de parcialidades) como valor por defecto y máximo
                                        $montoRestante = $proximoPagoPendiente->monto_restante ?? $proximoPagoPendiente->monto;
                                        $valorPorDefecto = $montoRestante > 0 ? number_format($montoRestante, 2, '.', '') : '';
                                        $maxMonto = $montoRestante;
                                    } else {
                                        // Si no hay pagos pendientes, usar la cuota sugerida
                                        $montoInicial = $contrato->monto_inicial ?? 0;
                                        $montoBonificacion = $contrato->monto_bonificacion ?? 0;
                                        $montoFinanciado = $contrato->monto_total - $montoInicial - $montoBonificacion;
                                        $cuotaSugerida = $contrato->numero_cuotas > 0 ? $montoFinanciado / $contrato->numero_cuotas : 0;
                                        $valorPorDefecto = $cuotaSugerida > 0 ? number_format($cuotaSugerida, 2, '.', '') : '';
                                        $maxMonto = $cuotaSugerida;
                                    }
                                } else {
                                    // Para edición de pagos existentes o pagos sin contrato
                                    $valorPorDefecto = old('monto', $pago?->monto ?? (isset($montoSugerido) ? number_format($montoSugerido, 2, '.', '') : ''));
                                }
                            @endphp
                            <input type="text" name="monto" class="form-control @error('monto') is-invalid @enderror"
                                value="{{ old('monto', $valorPorDefecto) }}" id="monto" placeholder="$0.00">
                            @error('monto')<div class="error-text">{{ $message }}</div>@enderror

                            <!-- Información de parcialidades aplicadas -->
                            @if(!$pago->exists && isset($proximoPagoPendiente) && $proximoPagoPendiente && isset($proximoPagoPendiente->parcialidades_aplicadas) && $proximoPagoPendiente->parcialidades_aplicadas > 0)
                                <div class="mt-2 p-2 bg-info bg-opacity-10 border border-info border-opacity-25 rounded">
                                    <small class="text-info">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Esta cuota ya tiene
                                        ${{ number_format($proximoPagoPendiente->parcialidades_aplicadas, 2) }} pagados en
                                        parcialidades.
                                        <br>
                                        <strong>Monto restante:
                                            ${{ number_format($proximoPagoPendiente->monto_restante ?? 0, 2) }}</strong>
                                    </small>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="form-group">
                        @if(!$pago->exists && isset($contrato) && $contrato)
                            <!-- Nuevo pago desde contrato: solo parcialidad -->
                            <select hidden name="tipo_pago" id="tipo_pago"
                                class="form-control @error('tipo_pago') is-invalid @enderror">
                                <option value="parcialidad" selected>Parcialidad</option>
                            </select>
                        @else
                            <!-- Edición de pago existente o pago sin contrato: opciones completas -->
                            <label for="tipo_pago" class="form-label">Tipo de Pago <span
                                    class="text-danger">*</span></label>
                            <select name="tipo_pago" id="tipo_pago"
                                class="form-control @error('tipo_pago') is-invalid @enderror">
                                <option value="cuota" {{ old('tipo_pago', $pago?->tipo_pago ?? 'cuota') == 'cuota' ? 'selected' : '' }}>Cuota Regular</option>
                                <option value="parcialidad" {{ old('tipo_pago', $pago?->tipo_pago) == 'parcialidad' ? 'selected' : '' }}>Parcialidad</option>
                                <option value="inicial" {{ old('tipo_pago', $pago?->tipo_pago) == 'inicial' ? 'selected' : '' }}>Pago Inicial</option>
                                <option value="bonificación" {{ old('tipo_pago', $pago?->tipo_pago) == 'bonificación' ? 'selected' : '' }}>Bonificación</option>
                            </select>
                        @endif
                        @error('tipo_pago')<div class="error-text">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Método de Pago <span class="text-danger">*</span></label>
                        <div class="radio-group @error('metodo_pago') is-invalid @enderror">
                            <div class="radio-option">
                                <input type="radio" name="metodo_pago" id="metodo_efectivo" value="efectivo" {{ old('metodo_pago', $pago?->metodo_pago) == 'efectivo' ? 'checked' : '' }}>
                                <label for="metodo_efectivo" class="radio-label">
                                    <i class="bi bi-cash-coin"></i>
                                    <span>Efectivo</span>
                                </label>
                            </div>
                            <div class="radio-option">
                                <input type="radio" name="metodo_pago" id="metodo_transferencia"
                                    value="transferencia bancaria" {{ old('metodo_pago', $pago?->metodo_pago) == 'transferencia bancaria' ? 'checked' : '' }}>
                                <label for="metodo_transferencia" class="radio-label">
                                    <i class="bi bi-bank"></i>
                                    <span>Transferencia Bancaria</span>
                                </label>
                            </div>
                            <div class="radio-option">
                                <input type="radio" name="metodo_pago" id="metodo_tarjeta"
                                    value="tarjeta credito/debito" {{ old('metodo_pago', $pago?->metodo_pago) == 'tarjeta credito/debito' ? 'checked' : '' }}>
                                <label for="metodo_tarjeta" class="radio-label">
                                    <i class="bi bi-credit-card"></i>
                                    <span>Tarjeta Crédito/Débito</span>
                                </label>
                            </div>
                            <div class="radio-option">
                                <input type="radio" name="metodo_pago" id="metodo_cheque" value="cheque" {{ old('metodo_pago', $pago?->metodo_pago) == 'cheque' ? 'checked' : '' }}>
                                <label for="metodo_cheque" class="radio-label">
                                    <i class="bi bi-journal-check"></i>
                                    <span>Cheque</span>
                                </label>
                            </div>
                            <div class="radio-option">
                                <input type="radio" name="metodo_pago" id="metodo_otro" value="otro" {{ old('metodo_pago', $pago?->metodo_pago) == 'otro' ? 'checked' : '' }}>
                                <label for="metodo_otro" class="radio-label">
                                    <i class="bi bi-three-dots"></i>
                                    <span>Otro</span>
                                </label>
                            </div>
                        </div>
                        @error('metodo_pago')<div class="error-text">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <!-- Columna Derecha -->
            <div class="right-column">

                <!-- Observaciones -->
                <div class="form-section">
                    <h6 class="section-title">Observaciones</h6>

                    <div class="form-group">
                        <label for="observaciones" class="form-label">Detalles adicionales</label>
                        <textarea name="observaciones" class="form-control @error('observaciones') is-invalid @enderror"
                            id="observaciones" rows="4"
                            placeholder="Observaciones del pago...">{{ old('observaciones', $pago?->observaciones) }}</textarea>
                        @error('observaciones')<div class="error-text">{{ $message }}</div>@enderror
                    </div>
                </div>

                <!-- Comprobante de pago -->
                <div class="form-section">
                    <h6 class="section-title">Comprobante de pago</h6>
                    <div class="upload-container">
                        <label for="documento" class="upload-area">
                            <div class="upload-content">
                                <svg class="upload-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                    <polyline points="7,10 12,15 17,10" />
                                    <line x1="12" y1="15" x2="12" y2="3" />
                                </svg>
                                <span class="upload-text">Subir comprobante</span>
                                <span class="upload-hint">PDF, JPG, PNG, WEBP (máx. 5MB)</span>
                            </div>
                        </label>
                        <input type="file" name="documento"
                            class="upload-input @error('documento') is-invalid @enderror" id="documento"
                            accept=".pdf,.jpg,.jpeg,.png,.webp" onchange="previewDocument(this)">
                        @error('documento')<div class="error-text">{{ $message }}</div>@enderror

                        <!-- Preview del documento actual si existe -->
                        @if($pago->exists && $pago->documento && $pago->documento !== 'No')
                            <div id="currentDocument" class="file-preview">
                                <div class="preview-header">
                                    <span>Documento actual</span>
                                    <div class="preview-actions">
                                        <a href="{{ asset('storage/' . $pago->documento) }}" target="_blank"
                                            class="preview-btn">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ asset('storage/' . $pago->documento) }}" download class="preview-btn">
                                            <i class="bi bi-download"></i>
                                        </a>
                                    </div>
                                </div>
                                <div class="preview-content">
                                    @php
                                        $extension = pathinfo($pago->documento, PATHINFO_EXTENSION);
                                        $isPdf = strtolower($extension) === 'pdf';
                                    @endphp
                                    @if($isPdf)
                                        <div class="pdf-icon">
                                            <i class="bi bi-file-earmark-pdf"></i>
                                        </div>
                                    @else
                                        <img src="{{ asset('storage/' . $pago->documento) }}" alt="Documento"
                                            class="preview-image">
                                    @endif
                                </div>
                                <div class="preview-info">
                                    <small>{{ basename($pago->documento) }}</small>
                                </div>
                            </div>
                        @endif

                        <!-- Preview del nuevo documento -->
                        <div id="documentPreview" class="file-preview" style="display: none;">
                            <div class="preview-header">
                                <span>Nuevo documento</span>
                                <div class="preview-actions">
                                    <button type="button" class="preview-btn" onclick="changeDocument()">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button" class="preview-btn" onclick="clearDocumentPreview()">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="preview-content">
                                <i id="previewIcon" class="bi bi-file-earmark"></i>
                                <img id="previewThumbnail" src="" alt="Vista previa" class="preview-image"
                                    style="display: none;">
                                <div id="previewPdfThumbnail" class="pdf-icon" style="display: none;">
                                    <i class="bi bi-file-earmark-pdf"></i>
                                </div>
                            </div>
                            <div class="preview-info">
                                <small id="previewName"></small>
                                <small id="previewSize"></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botones -->
        <div class="form-actions">
            <div class="action-left">
                @if($pago->exists && $pago->contrato)
                    <a href="{{ route('contratos.show', $pago->contrato->id) }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Volver al Contrato
                    </a>
                @elseif(isset($contrato) && $contrato)
                    <a href="{{ route('contratos.show', $contrato->id) }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Volver al Contrato
                    </a>
                @else
                    <a href="{{ route('pagos.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Volver a Pagos
                    </a>
                @endif
            </div>
            <div class="action-right">
                <button type="reset" class="btn btn-outline">
                    <i class="bi bi-arrow-clockwise me-2"></i>Limpiar
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle me-2"></i>{{ $pago->exists ? 'Actualizar Pago' : 'Registrar Pago' }}
                </button>
            </div>
        </div>
    </form>
</div>
<script>


    // Scripts de funcionalidad del formulario
    document.addEventListener('DOMContentLoaded', function () {
        const tipoSelect = document.getElementById('tipo_pago');
        const montoInput = document.getElementById('monto');
        const observacionesInput = document.getElementById('observaciones');

        // Helper para formatear moneda
        function formatearMoneda(valor) {
            if (valor === null || valor === undefined || isNaN(valor)) return '';
            return '$' + parseFloat(valor).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        // Actualizar observaciones basadas en el tipo de pago
        if (tipoSelect && observacionesInput) {
            tipoSelect.addEventListener('change', function () {
                const tipo = this.value;
                let observacion = observacionesInput.value.trim();

                // Solo actualizar si no hay observaciones personalizadas o son las por defecto
                const defaultObs = [
                    '',
                    'Cuota regular del contrato',
                    'Pago parcial del contrato',
                    'Pago inicial del contrato',
                    'Bonificación aplicada al contrato'
                ];

                if (defaultObs.includes(observacion)) {
                    switch (tipo) {
                        case 'cuota': observacionesInput.value = 'Cuota regular del contrato'; break;
                        case 'parcialidad': observacionesInput.value = 'Pago parcial del contrato'; break;
                        case 'inicial': observacionesInput.value = 'Pago inicial del contrato'; break;
                        case 'bonificación': observacionesInput.value = 'Bonificación aplicada al contrato'; break;
                    }
                }
            });
        }

        // Formateo de monto
        if (montoInput) {
            montoInput.addEventListener('focus', function () {
                if (this.value) {
                    this.value = this.value.replace(/[$,]/g, '');
                }
            });

            montoInput.addEventListener('blur', function () {
                const val = this.value.replace(/[^0-9.]/g, '');
                if (val) {
                    this.value = formatearMoneda(val);
                }
            });

            // Formatear valor inicial si existe
            if (montoInput.value) {
                const val = montoInput.value.replace(/[^0-9.]/g, '');
                if (val) montoInput.value = formatearMoneda(val);
            }
        }

        // Validación de envío básico
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function (e) {
                if (montoInput) {
                    const monto = parseFloat(montoInput.value.replace(/[$,]/g, ''));
                    if (!monto || monto <= 0 || isNaN(monto)) {
                        e.preventDefault();
                        alert('Por favor ingresa un monto válido.');
                        montoInput.focus();
                        return;
                    }
                    montoInput.value = monto.toFixed(2); // Enviar limpio
                }

                const metodo = document.querySelector('input[name="metodo_pago"]:checked');
                if (!metodo) {
                    e.preventDefault();
                    alert('Selecciona un método de pago.');
                    return;
                }
            });
        }
    });

    // Función para previsualizar documento
    function previewDocument(input) {
        const file = input.files[0];
        const preview = document.getElementById('documentPreview');
        const previewIcon = document.getElementById('previewIcon');
        const previewThumbnail = document.getElementById('previewThumbnail');
        const previewPdfThumbnail = document.getElementById('previewPdfThumbnail');
        const previewName = document.getElementById('previewName');
        const previewSize = document.getElementById('previewSize');

        if (!file) {
            preview.style.display = 'none';
            return;
        }

        // Validar tamaño (máximo 5MB)
        const maxSize = 5 * 1024 * 1024; // 5MB en bytes
        if (file.size > maxSize) {
            alert('El archivo es demasiado grande. El tamaño máximo permitido es 5MB.');
            input.value = '';
            preview.style.display = 'none';
            return;
        }

        // Validar tipo de archivo
        const allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            alert('Tipo de archivo no permitido. Solo se permiten archivos PDF, JPG, JPEG, PNG y WEBP.');
            input.value = '';
            preview.style.display = 'none';
            return;
        }

        // Mostrar información del archivo
        const fileName = file.name;
        const fileSize = formatFileSize(file.size);
        const extension = fileName.split('.').pop().toLowerCase();

        // Ocultar todos los elementos de vista previa primero
        previewIcon.style.display = 'none';
        previewThumbnail.style.display = 'none';
        previewPdfThumbnail.style.display = 'none';

        // Generar miniatura según el tipo de archivo
        if (extension === 'pdf') {
            previewPdfThumbnail.style.display = 'block';
        } else if (['jpg', 'jpeg', 'png', 'webp'].includes(extension)) {
            const reader = new FileReader();
            reader.onload = function (e) {
                previewThumbnail.src = e.target.result;
                previewThumbnail.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            previewIcon.style.display = 'block';
        }

        previewName.textContent = fileName;
        previewSize.textContent = fileSize;
        preview.style.display = 'block';
    }

    // Función para cambiar documento
    function changeDocument() {
        const documentInput = document.getElementById('documento');
        documentInput.click();
    }

    // Función para limpiar la previsualización
    function clearDocumentPreview() {
        const input = document.getElementById('documento');
        const preview = document.getElementById('documentPreview');
        const previewThumbnail = document.getElementById('previewThumbnail');

        input.value = '';
        preview.style.display = 'none';
        previewThumbnail.src = '';
    }

    // Función para formatear el tamaño del archivo
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';

        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));

        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }


</script>

<style>
    /* Diseño Minimalista en 2 Columnas - Estilo Pagos */
    .minimal-form {
        max-width: 1200px;
        margin: 0 auto;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }

    .form-container {
        overflow: hidden;
    }

    /* Información del contrato */
    .contract-info-section {
        padding: 24px 32px;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-bottom: 1px solid #e1e5e9;
    }

    .contract-card {
        background: white;
        border-radius: 12px;
        border: 1px solid #d1d9e0;
        overflow: hidden;
    }

    .contract-header {
        display: flex;
        align-items: center;
        padding: 20px;
        gap: 16px;
    }

    .contract-icon {
        width: 48px;
        height: 48px;
        background: linear-gradient(135deg, #E1B240 0%, #79481D 100%);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
    }

    .contract-details {
        flex-grow: 1;
    }

    .contract-title {
        font-size: 16px;
        font-weight: 600;
        color: #24292f;
        margin: 0 0 4px 0;
    }

    .contract-client {
        font-size: 14px;
        font-weight: 500;
        color: #0969da;
        margin: 0 0 4px 0;
    }

    .contract-date {
        font-size: 12px;
        color: #656d76;
        margin: 0;
    }

    .contract-amount {
        text-align: right;
    }

    .amount-label {
        font-size: 12px;
        color: #656d76;
        margin-bottom: 2px;
    }

    .amount-value {
        font-size: 18px;
        font-weight: 700;
        color: #0969da;
        margin-bottom: 2px;
    }

    .amount-installments {
        font-size: 12px;
        color: #656d76;
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

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }



    /* Hints */
    .form-hint {
        margin-top: 4px;
    }

    .hint-button {
        background: none;
        border: none;
        color: #0969da;
        text-decoration: underline;
        cursor: pointer;
        font-size: 12px;
        margin-left: 8px;
    }

    .hint-button:hover {
        color: #0860ca;
    }

    /* Estilos para texto informativo */
    .text-info {
        color: #0c5460;
    }

    .text-primary {
        color: #0969da;
    }

    /* Upload styles */
    .upload-container {
        position: relative;
    }

    .upload-area {
        display: block;
        width: 100%;
        padding: 32px;
        border: 2px dashed #d1d9e0;
        border-radius: 8px;
        background: #f6f8fa;
        cursor: pointer;
        transition: all 0.3s ease;
        text-align: center;
    }

    .upload-area:hover {
        border-color: #0969da;
        background: #f3f4f6;
    }

    .upload-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
    }

    .upload-icon {
        width: 48px;
        height: 48px;
        color: #656d76;
    }

    .upload-text {
        font-size: 16px;
        font-weight: 500;
        color: #24292f;
    }

    .upload-hint {
        font-size: 14px;
        color: #656d76;
    }

    .upload-input {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border: 0;
    }

    /* File preview */
    .file-preview {
        margin-top: 16px;
        border: 1px solid #d1d9e0;
        border-radius: 8px;
        background: white;
        overflow: hidden;
    }

    .preview-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 16px;
        background: #f6f8fa;
        border-bottom: 1px solid #d1d9e0;
        font-size: 14px;
        font-weight: 500;
        color: #24292f;
    }

    .preview-actions {
        display: flex;
        gap: 4px;
    }

    .preview-btn {
        width: 32px;
        height: 32px;
        border: 1px solid #d1d9e0;
        border-radius: 6px;
        background: white;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #656d76;
        text-decoration: none;
        font-size: 14px;
        transition: all 0.15s;
    }

    .preview-btn:hover {
        border-color: #0969da;
        color: #0969da;
        background: #f3f4f6;
    }

    .preview-content {
        padding: 16px;
        text-align: center;
    }

    .preview-image {
        max-width: 100%;
        max-height: 120px;
        border-radius: 6px;
    }

    .pdf-icon {
        font-size: 48px;
        color: #dc3545;
    }

    .preview-info {
        padding: 8px 16px;
        background: #f6f8fa;
        border-top: 1px solid #d1d9e0;
        font-size: 12px;
        color: #656d76;
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    /* Botones minimalistas */
    .form-actions {
        grid-column: 1 / -1;
        padding: 24px 32px;
        background: #f6f8fa;
        border-top: 1px solid #e1e5e9;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .action-right {
        display: flex;
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
    }

    .btn-outline {
        color: #656d76;
        background: #ffffff;
        border-color: #d1d9e0;
    }

    .btn-outline:hover {
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

    .text-danger {
        color: #da3633;
    }

    /* Radio Group Styles */
    .radio-group {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 12px;
        margin-top: 8px;
    }

    .radio-group.is-invalid {
        border: 1px solid #da3633;
        border-radius: 6px;
        padding: 8px;
        background: #ffeaea;
    }

    .radio-option {
        position: relative;
    }

    .radio-option input[type="radio"] {
        position: absolute;
        opacity: 0;
        width: 0;
        height: 0;
    }

    .radio-label {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 12px 16px;
        border: 1px solid #d1d9e0;
        border-radius: 6px;
        background: #ffffff;
        cursor: pointer;
        transition: all 0.15s ease;
        font-size: 14px;
        font-weight: 500;
        color: #24292f;
        margin: 0;
    }

    .radio-label:hover {
        border-color: #0969da;
        background: #f3f4f6;
    }

    .radio-option input[type="radio"]:checked+.radio-label {
        border-color: #0969da;
        background: #dbeafe;
        color: #0969da;
    }

    .radio-option input[type="radio"]:focus+.radio-label {
        outline: none;
        box-shadow: 0 0 0 3px rgba(9, 105, 218, 0.1);
    }

    .radio-label i {
        font-size: 16px;
        min-width: 16px;
    }

    .radio-option input[type="radio"]:checked+.radio-label i {
        color: #0969da;
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

        .contract-info-section {
            padding: 20px;
        }

        .contract-header {
            padding: 16px;
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
            gap: 16px;
            align-items: stretch;
        }

        .action-right {
            justify-content: space-between;
        }

        .btn {
            flex: 1;
            text-align: center;
        }

        .contract-header {
            flex-direction: column;
            text-align: center;
            gap: 12px;
        }

        .contract-amount {
            text-align: center;
        }
    }

    @media (max-width: 576px) {
        .form-row {
            grid-template-columns: 1fr;
        }

        .radio-group {
            grid-template-columns: 1fr;
        }
    }
</style>