<!-- Formulario Minimalista en 2 Columnas -->
<div class="minimal-form">
    <div class="form-container">
        
        <div class="form-layout">
            <!-- Columna Izquierda -->
            <div class="left-column">
                
                <!-- Información Básica -->
                <div class="form-section">
                    <h6 class="section-title">Información del Contrato</h6>
                    
                    <div class="form-group">
                        <label for="cliente_id" class="form-label">Cliente Titular</label>
                        <div class="searchable-select-container">
                            <div class="searchable-select" id="cliente-select">
                                <div class="search-input-container">
                                    <input type="text" 
                                           class="form-control search-input @error('cliente_id') is-invalid @enderror" 
                                           id="cliente_search" 
                                           placeholder="Buscar cliente..."
                                           autocomplete="off">
                                    <div class="search-icon">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="11" cy="11" r="8"></circle>
                                            <path d="m21 21-4.35-4.35"></path>
                                        </svg>
                                    </div>
                                    <div class="dropdown-arrow">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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
                            <input type="hidden" name="cliente_id" id="cliente_id" value="{{ old('cliente_id', $contrato?->cliente_id) }}" required>
                        </div>
                        @error('cliente_id')<div class="error-text">{{ $message }}</div>@enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="contrato_id" class="form-label">ID del Contrato</label>
                        <input type="text" name="id" class="form-control @error('id') is-invalid @enderror" 
                               value="{{ old('id', isset($contrato) && !$contrato->exists ? '' : $contrato?->id) }}" 
                               id="contrato_id" maxlength="6" pattern="[0-9]{1,6}" 
                               placeholder="Ingrese ID..." {{ isset($contrato) && $contrato->exists ? 'readonly' : 'required' }}>
                        <div id="id_status" class="id-status-message"></div>
                        @error('id')<div class="error-text">{{ $message }}</div>@enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="paquete_id" class="form-label">Paquete</label>
                        <select name="paquete_id" class="form-control @error('paquete_id') is-invalid @enderror" id="paquete_id" required>
                            <option value="">Seleccionar paquete</option>
                            @foreach ($paquetes as $id => $name)
                                <option value="{{ $id }}" {{ old('paquete_id', $contrato?->paquete_id) == $id ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                        @error('paquete_id')<div class="error-text">{{ $message }}</div>@enderror
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="fecha_inicio" class="form-label">Fecha de Inicio</label>
                            <input type="date" name="fecha_inicio" class="form-control @error('fecha_inicio') is-invalid @enderror"
                                   value="{{ old('fecha_inicio', $contrato?->fecha_inicio ?? now()->format('Y-m-d')) }}"
                                   id="fecha_inicio" required>
                            @error('fecha_inicio')<div class="error-text">{{ $message }}</div>@enderror
                            <input type="hidden" name="fecha_fin" id="fecha_fin_hidden" value="{{ old('fecha_fin', $contrato?->fecha_fin) }}">
                        </div>
                        
                        <div class="form-group">
                            <label for="monto_total" class="form-label">Monto Total</label>
                            <input type="text" name="monto_total" class="form-control @error('monto_total') is-invalid @enderror" 
                                   value="{{ old('monto_total', isset($contrato) ? '$' . number_format($contrato->monto_total, 2, '.', ',') : '') }}" 
                                   id="monto_total" disabled placeholder="$0.00">
                            @error('monto_total')<div class="error-text">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="observaciones" class="form-label">Observaciones</label>
                        <textarea name="observaciones" class="form-control @error('observaciones') is-invalid @enderror" 
                               id="observaciones" rows="3" placeholder="Observaciones adicionales del contrato">{{ old('observaciones', $contrato?->observaciones) }}</textarea>
                        @error('observaciones')<div class="error-text">{{ $message }}</div>@enderror
                    </div>
                </div>

                <!-- Documentación -->
                <div class="form-section">
                    <h6 class="section-title">Documento del contrato</h6>
                    
                    <div class="upload-container">

                        <label for="documento" class="upload-area" id="upload-area">
                            <div class="upload-content" id="upload-content">
                                <svg class="upload-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                    <polyline points="7,10 12,15 17,10"/>
                                    <line x1="12" y1="15" x2="12" y2="3"/>
                                </svg>
                                <span class="upload-text">Subir contrato en PDF</span>
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
                        @if(isset($contrato) && $contrato->documento && $contrato->documento !== 'No')
                        <div id="existing-pdf" class="file-preview" style="cursor: pointer;" onclick="triggerFileInput()">
                            <div class="preview-header">
                                <span>Documento actual (haz clic para cambiar)</span>
                            </div>
                            <embed src="{{ asset('storage/' . $contrato->documento) }}" type="application/pdf" width="100%" height="600px">
                            <div class="preview-actions">
                                <a href="{{ asset('storage/' . $contrato->documento) }}" target="_blank" class="btn-link" onclick="event.stopPropagation();">
                                    Ver completo
                                </a>
                            </div>
                        </div>
                        @endif

                        <script>
                        // Variable para rastrear si hay un documento existente
                        const hasExistingDocument = document.getElementById('existing-pdf') !== null;
                        
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

            <!-- Columna Derecha -->
            <div class="right-column">
                
                <!-- Configuración Financiera -->
                <div class="form-section">
                    <h6 class="section-title">Configuración Financiera</h6>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="monto_inicial" class="form-label">Pago Inicial</label>
                            <input type="text" name="monto_inicial" class="form-control @error('monto_inicial') is-invalid @enderror" 
                                   value="{{ old('monto_inicial', isset($contrato) ? '$' . number_format($contrato->monto_inicial, 2, '.', ',') : '') }}" 
                                   id="monto_inicial" placeholder="$0.00">
                            @error('monto_inicial')<div class="error-text">{{ $message }}</div>@enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="monto_bonificacion" class="form-label">Bonificación</label>
                            <input type="text" name="monto_bonificacion" class="form-control @error('monto_bonificacion') is-invalid @enderror" 
                                   value="{{ old('monto_bonificacion', isset($contrato) ? '$' . number_format($contrato->monto_bonificacion, 2, '.', ',') : '') }}" 
                                   id="monto_bonificacion" placeholder="$0.00">
                            @error('monto_bonificacion')<div class="error-text">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
                
                <!-- Plan de Pagos -->
                <div class="form-section">
                    <h6 class="section-title">Plan de Pagos</h6>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="numero_cuotas" class="form-label">Número de Cuotas</label>
                            <input type="number" name="numero_cuotas" class="form-control @error('numero_cuotas') is-invalid @enderror" 
                                   value="{{ old('numero_cuotas', $contrato?->numero_cuotas) }}" id="numero_cuotas" 
                                   placeholder="Escriba el numero de cuotas" min="1" required>
                            @error('numero_cuotas')<div class="error-text">{{ $message }}</div>@enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="frecuencia_cuotas" class="form-label">Frecuencia (Días)</label>
                            <input type="number" name="frecuencia_cuotas" class="form-control @error('frecuencia_cuotas') is-invalid @enderror" 
                                   value="{{ old('frecuencia_cuotas', $contrato?->frecuencia_cuotas ?? 'Cada cuantos dias') }}" id="frecuencia_cuotas" 
                                   placeholder="Cada cuantos dias" min="1" required>
                            @error('frecuencia_cuotas')<div class="error-text">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="monto_cuota" class="form-label">Monto por Cuota</label>
                        <input type="text" name="monto_cuota" class="form-control @error('monto_cuota') is-invalid @enderror" 
                               value="{{ old('monto_cuota', isset($contrato) ? '$' . number_format($contrato->monto_cuota, 2, '.', ',') : '') }}" 
                               id="monto_cuota" readonly placeholder="$0.00">
                        @error('monto_cuota')<div class="error-text">{{ $message }}</div>@enderror
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

                <!-- Comisiones -->
                <div class="form-section" id="comisiones_section" style="display: none;">
                    <div class="accordion-header open" onclick="toggleAccordion('comisiones_accordion')">
                        <h6 class="section-title">Comisiones</h6>
                        <div class="accordion-toggle">
                            <svg class="accordion-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6,9 12,15 18,9"></polyline>
                            </svg>
                        </div>
                    </div>
                    <div class="accordion-content open" id="comisiones_accordion">
                        <div id="comisiones_container"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botones -->
        <div class="form-actions">
            <a href="{{ url()->previous() }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary" style="background: linear-gradient(90deg, #E1B240 0%, #79481D 100%); border: none;">
                Registrar Contrato
            </button>
        </div>
    </div>
</div>
    </div>
    
    <script>
        function calcularPagos() {
            const numeroCuotas = parseInt(document.getElementById('numero_cuotas').value) || 0;
            const montoTotalStr = document.getElementById('monto_total').value.replace(/[$,]/g, '');
            const montoTotal = parseFloat(montoTotalStr) || 0;
            const montoInicialStr = document.getElementById('monto_inicial').value.replace(/[$,]/g, '');
            const montoInicial = parseFloat(montoInicialStr) || 0;
            const montoBonificacionStr = document.getElementById('monto_bonificacion').value.replace(/[$,]/g, '');
            const montoBonificacion = parseFloat(montoBonificacionStr) || 0;
            const frecuenciaCuotas = parseInt(document.getElementById('frecuencia_cuotas').value) || 7;

            if (!numeroCuotas || !montoTotal) {
                document.getElementById('pagos_card').style.display = 'none';
                return;
            }

            const saldoPendiente = montoTotal - montoInicial - montoBonificacion;
            
            const montoPorCuotaBase = numeroCuotas > 0 ? Math.floor((saldoPendiente / numeroCuotas) * 100) / 100 : 0;
            const totalCuotasBase = montoPorCuotaBase * numeroCuotas;
            const ajusteUltimaCuota = Math.round((saldoPendiente - totalCuotasBase) * 100) / 100;
            const montoUltimaCuota = montoPorCuotaBase + ajusteUltimaCuota;

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

            document.getElementById('monto_cuota').value = formatearMoneda(montoPorCuotaBase);

            let infoDetalle = '';
            if (Math.abs(ajusteUltimaCuota) > 0.01) {
                infoDetalle = `
                    <small>
                        ${numeroCuotas - 1} cuotas de ${formatearMoneda(montoPorCuotaBase)} + 
                        1 cuota final de ${formatearMoneda(montoUltimaCuota)}
                    </small><br>
                `;
            }

            document.getElementById('pagos_info').innerHTML = `
                <strong>${numeroCuotas}</strong> cuotas de <strong>${formatearMoneda(montoPorCuotaBase)}</strong><br>
                ${infoDetalle}
                <small>Cada ${frecuenciaCuotas} días</small><br>
                ${fechaFin ? `<small>Finalización: <strong>${fechaFin}</strong></small>` : ''}
            `;
            
            document.getElementById('total_restante').innerHTML = `
                <div>Costo total: ${formatearMoneda(montoTotal)}</div>
                <div>Inicial: -${formatearMoneda(montoInicial)}</div>
                <div>Bonificación: -${formatearMoneda(montoBonificacion)}</div>
                <div><strong>Restante: ${formatearMoneda(saldoPendiente)}</strong></div>
            `;
            
            document.getElementById('pagos_card').style.display = 'block';
        }

        document.getElementById('numero_cuotas').addEventListener('input', calcularPagos);
        document.getElementById('frecuencia_cuotas').addEventListener('input', calcularPagos);
        document.getElementById('monto_inicial').addEventListener('input', function() {
            formatearCampoMoneda(this);
            calcularPagos();
        });
        document.getElementById('monto_bonificacion').addEventListener('input', function() {
            formatearCampoMoneda(this);
            calcularPagos();
        });
        document.getElementById('monto_total').addEventListener('input', calcularPagos);
        document.getElementById('fecha_inicio').addEventListener('change', calcularPagos);

        // Función para formatear campos de moneda
        function formatearCampoMoneda(input) {
            let value = input.value.replace(/[^0-9.]/g, ''); // Remover todo excepto números y punto
            
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
            if (value && parseFloat(value) >= 0) {
                input.value = formatearMoneda(parseFloat(value));
            } else if (value === '') {
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

        // Aplicar formato inicial a los campos de moneda al cargar la página
        window.addEventListener('DOMContentLoaded', function() {
            const camposMoneda = ['monto_inicial', 'monto_bonificacion'];
            camposMoneda.forEach(function(campoId) {
                const campo = document.getElementById(campoId);
                if (campo && campo.value && !campo.value.startsWith('$')) {
                    const valor = parseFloat(campo.value.replace(/[^0-9.]/g, ''));
                    if (!isNaN(valor)) {
                        campo.value = formatearMoneda(valor);
                    }
                }
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
                'numero_cuotas',
                'frecuencia_cuotas'
            ];

            requiredFields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field) {
                    // Validar al perder el foco
                    field.addEventListener('blur', function() {
                        validateField(this);
                    });

                    // Limpiar error al cambiar el valor
                    field.addEventListener('input', function() {
                        clearFieldError(this);
                    });

                    field.addEventListener('change', function() {
                        clearFieldError(this);
                    });
                }
            });

            // Validar formulario antes de enviar
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    let hasErrors = false;
                    
                    // Limpiar campos de moneda antes de enviar
                    const camposMoneda = ['monto_inicial', 'monto_bonificacion', 'monto_cuota'];
                    camposMoneda.forEach(function(campoId) {
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
                    ['contrato_id', 'paquete_id', 'fecha_inicio', 'numero_cuotas', 'frecuencia_cuotas'].forEach(fieldId => {
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

                    // Validar selects de comisiones
                    const comisionSelects = document.querySelectorAll('#comisiones_container select[required]');
                    comisionSelects.forEach(select => {
                        if (!validateField(select)) {
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

        function clearFieldError(field) {
            field.classList.remove('is-invalid');
            removeCustomError(field);
            
            // Si es el campo de búsqueda de cliente, también limpiar error del contenedor
            if (field.id === 'cliente_search') {
                const container = field.closest('.searchable-select-container');
                if (container) {
                    const existingError = container.parentNode.querySelector('.custom-error');
                    if (existingError) {
                        existingError.remove();
                    }
                }
            }
        }

        function showCustomError(field, message) {
            removeCustomError(field);
            
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-text custom-error';
            errorDiv.textContent = message;
            
            field.parentNode.appendChild(errorDiv);
        }

        function removeCustomError(field) {
            const existingError = field.parentNode.querySelector('.custom-error');
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
        document.addEventListener('DOMContentLoaded', function() {
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

            idInput.addEventListener('input', function() {
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
    .form-control[required] + .form-label::after,
    select[required] + .form-label::after,
    input[required] ~ .form-label::after {
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
    .form-label[for="numero_cuotas"]::after,
    .form-label[for="frecuencia_cuotas"]::after {
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

    /* Comisiones minimalistas con layout mejorado */
    #comisiones_container .form-group {
        background: #f6f8fa;
        border: 1px solid #d1d9e0;
        border-radius: 6px;
        padding: 16px;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
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
    }

    .comision-title {
        font-size: 14px;
        font-weight: 500;
        color: #24292f;
        margin: 0;
        position: relative;
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
        flex: 1;
        max-width: 200px;
    }

    #comisiones_container .form-control {
        margin-bottom: 0;
        width: 100%;
    }

    /* Responsive para comisiones */
    @media (max-width: 768px) {
        #comisiones_container .form-group {
            flex-direction: column;
            align-items: stretch;
            gap: 12px;
        }
        
        .comision-select-container {
            max-width: 100%;
        }
        
        .comision-info {
            text-align: center;
        }
    }

    /* Estilos para el select con búsqueda */
    .searchable-select-container {
        position: relative;
    }

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

    .dropdown-list {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: #ffffff;
        border: 1px solid #d1d9e0;
        border-radius: 6px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        max-height: 200px;
        overflow-y: auto;
        z-index: 1000;
        display: none;
        margin-top: 2px;
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
    
    document.getElementById('paquete_id').addEventListener('change', function() {
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
    
    function cargarPorcentajes(paqueteId) {
        fetch(`/ajax/porcentajes/${paqueteId}`)
            .then(response => response.json())
            .then(data => {
                const container = document.getElementById('comisiones_container');
                container.innerHTML = '';
                
                if (data.porcentajes.length > 0) {
                    data.porcentajes.forEach(porcentaje => {
                        const div = document.createElement('div');
                        div.className = 'form-group';
                        
                        // Buscar comisión existente para este tipo de porcentaje
                        const comisionExistente = @json(isset($comisionesExistentes) ? $comisionesExistentes : (object)[]);
                        const empleadoSeleccionado = comisionExistente[porcentaje.tipo_porcentaje] ? 
                            comisionExistente[porcentaje.tipo_porcentaje].empleado_id : '';
                        
                        div.innerHTML = `
                            <div class="comision-info">
                                <div class="comision-title">${porcentaje.tipo_porcentaje}</div>
                                <div class="comision-percentage">${porcentaje.cantidad_porcentaje}% de comisión</div>
                            </div>
                            <div class="comision-select-container">
                                <select name="comisiones[${porcentaje.id}]" class="form-control" id="comision_${porcentaje.id}" required>
                                    <option value="">Seleccionar empleado</option>
                                    ${Object.entries(data.empleados).map(([id, nombre]) => 
                                        `<option value="${id}" ${id == empleadoSeleccionado ? 'selected' : ''}>${nombre}</option>`
                                    ).join('')}
                                </select>
                            </div>
                            <input type="hidden" name="porcentaje_ids[]" value="${porcentaje.id}">
                        `;
                        container.appendChild(div);
                    });
                    
                    // Agregar validación en tiempo real a los nuevos selects de comisiones
                    data.porcentajes.forEach(porcentaje => {
                        const select = document.getElementById(`comision_${porcentaje.id}`);
                        if (select) {
                            // Validar al perder el foco
                            select.addEventListener('blur', function() {
                                validateField(this);
                            });

                            // Limpiar error al cambiar el valor
                            select.addEventListener('change', function() {
                                clearFieldError(this);
                            });
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
    window.addEventListener('DOMContentLoaded', function() {
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
                
                pdfEmbed.onload = function() {
                    if (pdfFallback) pdfFallback.style.display = 'none';
                    pdfEmbed.style.display = 'block';
                    URL.revokeObjectURL(fileUrl);
                };
                
                pdfEmbed.onerror = function() {
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

    // Funcionalidad del select con búsqueda para clientes
    document.addEventListener('DOMContentLoaded', function() {
        const searchableSelect = document.getElementById('cliente-select');
        const searchInput = document.getElementById('cliente_search');
        const dropdown = document.getElementById('cliente-dropdown');
        const hiddenInput = document.getElementById('cliente_id');
        const options = dropdown.querySelectorAll('.dropdown-option');
        
        let currentHighlight = -1;
        let isOpen = false;

        // Inicializar con valor seleccionado
        if (hiddenInput.value) {
            const selectedOption = dropdown.querySelector(`[data-value="${hiddenInput.value}"]`);
            if (selectedOption) {
                searchInput.value = selectedOption.querySelector('.option-text').textContent;
                selectedOption.classList.add('selected');
            }
        }

        // Función para abrir dropdown
        function openDropdown() {
            if (!isOpen) {
                dropdown.classList.add('show');
                searchableSelect.classList.add('open', 'focused');
                isOpen = true;
                currentHighlight = -1;
                filterOptions();
            }
        }

        // Función para cerrar dropdown
        function closeDropdown() {
            if (isOpen) {
                dropdown.classList.remove('show');
                searchableSelect.classList.remove('open', 'focused');
                isOpen = false;
                currentHighlight = -1;
                
                // Si no hay valor seleccionado, limpiar el input
                if (!hiddenInput.value) {
                    searchInput.value = '';
                }
            }
        }

        // Función para filtrar opciones
        function filterOptions() {
            const searchTerm = searchInput.value.toLowerCase();
            let visibleOptions = [];
            
            options.forEach(option => {
                const text = option.querySelector('.option-text').textContent.toLowerCase();
                const isVisible = text.includes(searchTerm) || option.dataset.value === '';
                
                option.style.display = isVisible ? 'block' : 'none';
                if (isVisible) {
                    visibleOptions.push(option);
                }
            });
            
            return visibleOptions;
        }

        // Función para seleccionar opción
        function selectOption(option) {
            // Limpiar selección anterior
            options.forEach(opt => opt.classList.remove('selected'));
            
            // Seleccionar nueva opción
            option.classList.add('selected');
            hiddenInput.value = option.dataset.value;
            searchInput.value = option.querySelector('.option-text').textContent;
            
            closeDropdown();
            
            // Validar el campo después de la selección
            if (typeof validateField === 'function') {
                setTimeout(() => validateField(hiddenInput), 100);
            }
        }

        // Función para navegar con teclado
        function navigateOptions(direction) {
            const visibleOptions = Array.from(options).filter(opt => opt.style.display !== 'none');
            
            if (visibleOptions.length === 0) return;
            
            // Limpiar highlight anterior
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

        // Event listeners
        searchInput.addEventListener('click', function(e) {
            e.stopPropagation();
            openDropdown();
        });

        searchInput.addEventListener('input', function() {
            if (!isOpen) openDropdown();
            
            // Si el input está vacío o no coincide con la opción seleccionada, limpiar selección
            if (!this.value || (hiddenInput.value && 
                this.value !== dropdown.querySelector(`[data-value="${hiddenInput.value}"] .option-text`).textContent)) {
                hiddenInput.value = '';
                options.forEach(opt => opt.classList.remove('selected'));
            }
            
            filterOptions();
        });

        searchInput.addEventListener('keydown', function(e) {
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

        searchInput.addEventListener('blur', function() {
            // Delay para permitir que el click en una opción funcione
            setTimeout(() => {
                if (!searchableSelect.contains(document.activeElement)) {
                    closeDropdown();
                }
            }, 150);
        });

        // Click en opciones del dropdown
        options.forEach(option => {
            option.addEventListener('mousedown', function(e) {
                e.preventDefault(); // Prevenir blur del input
            });
            
            option.addEventListener('click', function(e) {
                e.stopPropagation();
                selectOption(this);
            });
            
            option.addEventListener('mouseenter', function() {
                options.forEach(opt => opt.classList.remove('highlighted'));
                this.classList.add('highlighted');
                currentHighlight = Array.from(options).filter(opt => opt.style.display !== 'none').indexOf(this);
            });
        });

        // Cerrar dropdown al hacer click fuera
        document.addEventListener('click', function(e) {
            if (!searchableSelect.contains(e.target)) {
                closeDropdown();
            }
        });

        // Actualizar la validación del campo oculto en la función de validación existente
        const originalValidateField = window.validateField;
        if (typeof originalValidateField === 'function') {
            window.validateField = function(field) {
                // Si es el campo de cliente, validar el input oculto
                if (field.id === 'cliente_search') {
                    return originalValidateField(hiddenInput);
                }
                return originalValidateField(field);
            };
        }
    });

    // Función para manejar el acordeón
    function toggleAccordion(accordionId) {
        const content = document.getElementById(accordionId);
        const header = content.previousElementSibling;
        
        if (content.classList.contains('open')) {
            // Cerrar acordeón
            content.classList.remove('open');
            header.classList.remove('open');
        } else {
            // Abrir acordeón
            content.classList.add('open');
            header.classList.add('open');
        }
    }
</script>