<!-- Formulario Minimalista en 2 Columnas -->
<div class="">
    <div class="">
        
        <div class="form-layout">
            <!-- Columna Izquierda -->
            <div class="left-column">
                
                <!-- Información del Paquete -->
                <div class="form-section">
                    <h6 class="section-title">Información del Paquete</h6>
                    
                    <div class="form-group">
                        <label for="nombre" class="form-label">Nombre</label>
                        <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror" 
                               value="{{ old('nombre', $paquete?->nombre) }}" id="nombre" 
                               placeholder="Nombre del paquete" required>
                        @error('nombre')<div class="error-text">{{ $message }}</div>@enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="precio" class="form-label">Precio</label>
                        <input type="text" name="precio" class="form-control @error('precio') is-invalid @enderror" 
                               value="{{ old('precio', isset($paquete) ? '$' . number_format($paquete->precio, 2, '.', ',') : '') }}" 
                               id="precio" placeholder="$0.00" required>
                        @error('precio')<div class="error-text">{{ $message }}</div>@enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea name="descripcion" class="form-control @error('descripcion') is-invalid @enderror" 
                               id="descripcion" rows="4" placeholder="Descripción del paquete" required>{{ old('descripcion', $paquete?->descripcion) }}</textarea>
                        @error('descripcion')<div class="error-text">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <!-- Columna Derecha -->
            <div class="right-column">
                
                <!-- Porcentajes -->
                <div class="form-section">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h6 class="section-title mb-0">Porcentajes de Comisión</h6>
                            <div class="mt-1">
                                <small class="text-muted">Utilidad restante: </small>
                                <small id="monto-restante-valor" class="fw-bold text-success">$0.00</small>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-success" id="addPorcentaje">
                            <i class="fas fa-plus me-1"></i> Agregar Porcentaje
                        </button>
                    </div>
                    
                    <div id="porcentajes-container" class="form-row">
                        @php
                            $porcentajesOld = old('porcentajes');
                            if ($porcentajesOld) {
                                // Convertir a objetos para uniformidad
                                $porcentajesData = collect($porcentajesOld)->map(function($p) { return (object)$p; });
                            } else {
                                $porcentajesData = isset($paquete) ? $paquete->porcentajes : collect();
                            }
                        @endphp

                        @if($porcentajesData->count() > 0)
                            @foreach($porcentajesData as $index => $porcentaje)
                                <div class="porcentaje-row">
                                    <div class="porcentaje-content">
                                        <div class="form-group">
                                            <label class="form-label">Tipo de Comisión</label>
                                            <input type="text" name="porcentajes[{{ $index }}][tipo_porcentaje]" 
                                                   class="form-control @error('porcentajes.'.$index.'.tipo_porcentaje') is-invalid @enderror" 
                                                   value="{{ $porcentaje->tipo_porcentaje }}" 
                                                   placeholder="Ej: Vendedor, Supervisor, etc.">
                                            @error('porcentajes.'.$index.'.tipo_porcentaje')<div class="error-text">{{ $message }}</div>@enderror
                                        </div>

                                        <div class="form-group">
                                            <label class="form-label">Cantidad</label>
                                            <div class="input-group">
                                                <input type="hidden" name="porcentajes[{{ $index }}][modo_comision]" class="modo-comision-input" value="{{ $porcentaje->modo_comision ?? 'porcentaje' }}">
                                                <button type="button" class="btn btn-outline-secondary toggle-modo-btn" title="Click para cambiar entre % y $">
                                                    <i class="fas {{ (($porcentaje->modo_comision ?? 'porcentaje') == 'porcentaje') ? 'fa-percentage' : 'fa-dollar-sign' }}"></i>
                                                </button>
                                                
                                                {{-- Input de Porcentaje --}}
                                                <input type="number" step="0.01" name="porcentajes[{{ $index }}][cantidad_porcentaje]" 
                                                       class="form-control valor-porcentaje-input {{ (($porcentaje->modo_comision ?? 'porcentaje') == 'monto') ? 'd-none' : '' }}" 
                                                       value="{{ $porcentaje->cantidad_porcentaje ?? '' }}" 
                                                       placeholder="0.00 %" min="0" max="100">
                                                
                                                {{-- Input de Monto Fijo --}}
                                                <input type="number" step="0.01" name="porcentajes[{{ $index }}][monto_fijo]" 
                                                       class="form-control valor-monto-input {{ (($porcentaje->modo_comision ?? 'porcentaje') == 'porcentaje') ? 'd-none' : '' }}" 
                                                       value="{{ $porcentaje->monto_fijo ?? '' }}" 
                                                       placeholder="$ 0.00">
                                            </div>
                                            @error('porcentajes.'.$index.'.cantidad_porcentaje')<div class="error-text">{{ $message }}</div>@enderror
                                            @error('porcentajes.'.$index.'.monto_fijo')<div class="error-text">{{ $message }}</div>@enderror
                                        </div>

                                        <div class="form-group">
                                            <label class="form-label">Observaciones</label>
                                            <input type="text" name="porcentajes[{{ $index }}][observaciones]" 
                                                   class="form-control @error('porcentajes.'.$index.'.observaciones') is-invalid @enderror" 
                                                   value="{{ $porcentaje->observaciones ?? '' }}" 
                                                   placeholder="Observaciones opcionales">
                                            @error('porcentajes.'.$index.'.observaciones')<div class="error-text">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                    <div class="porcentaje-actions">
                                        <button type="button" class="btn btn-danger remove-porcentaje">
                                            <i class="fas fa-trash me-1"></i> Eliminar Porcentaje
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="empty-state" id="empty-porcentajes-message">
                                <div class="empty-icon">
                                    <i class="fas fa-percentage"></i>
                                </div>
                                <p class="empty-text">No hay porcentajes configurados</p>
                                <small class="empty-hint">Agrega porcentajes de comisión haciendo clic en "Agregar Porcentaje"</small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Botones -->
        <div class="d-flex justify-content-end">
            <a href="{{ url()->previous() }}" class="btn btn-secondary me-4">Cancelar</a>
            <button type="submit" class="btn btn-primary" style="background: linear-gradient(90deg, #E1B240 0%, #79481D 100%); border: none;">
            <i class="fas fa-check me-1"></i> Guardar Paquete
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let porcentajeIndex = {{ isset($paquete) && $paquete->porcentajes ? $paquete->porcentajes->count() : 0 }};

    // Formatear campo de precio
    const precioInput = document.getElementById('precio');
    if (precioInput) {
        precioInput.addEventListener('blur', function() {
            formatearCampoMoneda(this);
            actualizarMontoRestante();
        });
        
        precioInput.addEventListener('focus', function() {
            // Al entrar, quitar formato para facilitar edición
            let value = this.value.replace(/[$,]/g, '');
            if (value && !isNaN(parseFloat(value))) {
                this.value = value;
            }
        });

        precioInput.addEventListener('input', function() {
            actualizarMontoRestante();
        });
        
        // Aplicar formato inicial si hay valor
        if (precioInput.value) {
            const valorLimpio = precioInput.value.replace(/[$,]/g, '');
            const valor = parseFloat(valorLimpio);
            if (!isNaN(valor)) {
                precioInput.value = formatearMoneda(valor);
            }
        }
    }

    function actualizarMontoRestante() {
        const precioField = document.getElementById('precio');
        if (!precioField) return;

        let precioBase = parseFloat(precioField.value.replace(/[$,]/g, '')) || 0;
        let totalComisiones = 0;

        const rows = document.querySelectorAll('.porcentaje-row');
        rows.forEach(row => {
            const modo = row.querySelector('.modo-comision-input').value;
            const porcentajeInput = row.querySelector('.valor-porcentaje-input');
            const montoInput = row.querySelector('.valor-monto-input');

            if (modo === 'porcentaje') {
                let p = parseFloat(porcentajeInput.value) || 0;
                totalComisiones += (precioBase * p) / 100;
            } else {
                let m = parseFloat(montoInput.value) || 0;
                totalComisiones += m;
            }
        });

        const restante = precioBase - totalComisiones;
        const restanteElement = document.getElementById('monto-restante-valor');

        if (restanteElement) {
            restanteElement.textContent = formatearMoneda(restante);
            
            if (restante < 0) {
                restanteElement.classList.remove('text-success');
                restanteElement.classList.add('text-danger');
            } else {
                restanteElement.classList.remove('text-danger');
                restanteElement.classList.add('text-success');
            }
        }
    }

    // Inicializar cálculo
    setTimeout(actualizarMontoRestante, 500);

    // Agregar nuevo porcentaje
    document.getElementById('addPorcentaje').addEventListener('click', function() {
        const container = document.getElementById('porcentajes-container');
        
        // Remover mensaje de vacío si existe
        const emptyMessage = document.getElementById('empty-porcentajes-message');
        if (emptyMessage) {
            emptyMessage.remove();
        }
        
        const newRow = document.createElement('div');
        newRow.className = 'porcentaje-row';
        newRow.innerHTML = `
            <div class="porcentaje-content">
                <div class="form-group">
                    <label class="form-label">Tipo de Comisión</label>
                    <input type="text" name="porcentajes[${porcentajeIndex}][tipo_porcentaje]" 
                           class="form-control" placeholder="Ej: Vendedor, Supervisor, etc.">
                </div>
                <div class="form-group">
                    <label class="form-label">Monto</label>
                    <div class="input-group">
                        <input type="hidden" name="porcentajes[${porcentajeIndex}][modo_comision]" class="modo-comision-input" value="porcentaje">
                        <button type="button" class="btn btn-outline-secondary toggle-modo-btn" title="Click para cambiar entre % y $">
                            <i class="fas fa-percentage"></i>
                        </button>
                        <input type="number" step="0.01" name="porcentajes[${porcentajeIndex}][cantidad_porcentaje]" 
                               class="form-control valor-porcentaje-input" placeholder="0.00" min="0" max="100">
                        <input type="number" step="0.01" name="porcentajes[${porcentajeIndex}][monto_fijo]" 
                               class="form-control valor-monto-input d-none" placeholder="0.00">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Observaciones</label>
                    <input type="text" name="porcentajes[${porcentajeIndex}][observaciones]" 
                           class="form-control" placeholder="Observaciones opcionales">
                </div>
            </div>
            <div class="porcentaje-actions">
                <button type="button" class="btn btn-danger remove-porcentaje">
                    <i class="fas fa-trash me-1"></i> Eliminar Porcentaje
                </button>
            </div>
        `;
        container.appendChild(newRow);
        porcentajeIndex++;
        
        // Agregar animación
        newRow.style.opacity = '0';
        newRow.style.transform = 'translateY(20px)';
        setTimeout(() => {
            newRow.style.transition = 'all 0.3s ease';
            newRow.style.opacity = '1';
            newRow.style.transform = 'translateY(0)';
        }, 50);
    });

    // Escuchar cambios en los inputs de comisiones para recalcular
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('valor-porcentaje-input') || e.target.classList.contains('valor-monto-input')) {
            actualizarMontoRestante();
        }
    });

    // Manejar el toggle de modo (porcentaje vs monto)
    document.addEventListener('click', function(e) {
        const toggleBtn = e.target.closest('.toggle-modo-btn');
        if (toggleBtn) {
            const row = toggleBtn.closest('.porcentaje-row');
            const hiddenInput = row.querySelector('.modo-comision-input');
            const icon = toggleBtn.querySelector('i');
            const porcentajeInput = row.querySelector('.valor-porcentaje-input');
            const montoInput = row.querySelector('.valor-monto-input');
            
            if (hiddenInput.value === 'porcentaje') {
                // Cambiar a monto
                hiddenInput.value = 'monto';
                icon.className = 'fas fa-dollar-sign';
                porcentajeInput.classList.add('d-none');
                montoInput.classList.remove('d-none');
                porcentajeInput.value = ''; // Limpiar el otro valor
            } else {
                // Cambiar a porcentaje
                hiddenInput.value = 'porcentaje';
                icon.className = 'fas fa-percentage';
                porcentajeInput.classList.remove('d-none');
                montoInput.classList.add('d-none');
                montoInput.value = ''; // Limpiar el otro valor
            }
            actualizarMontoRestante();
        }

        if (e.target.classList.contains('remove-porcentaje') || e.target.closest('.remove-porcentaje')) {
            const row = e.target.closest('.porcentaje-row');
            const container = document.getElementById('porcentajes-container');
            
            // Animación de salida
            row.style.transition = 'all 0.3s ease';
            row.style.opacity = '0';
            row.style.transform = 'translateX(-100%)';
            
            setTimeout(() => {
                row.remove();
                actualizarMontoRestante();
                
                // Si no quedan porcentajes, mostrar el estado vacío
                if (container.children.length === 0) {
                    const emptyState = document.createElement('div');
                    emptyState.className = 'empty-state';
                    emptyState.id = 'empty-porcentajes-message';
                    emptyState.innerHTML = `
                        <div class="empty-icon">
                            <i class="fas fa-percentage"></i>
                        </div>
                        <p class="empty-text">No hay porcentajes configurados</p>
                        <small class="empty-hint">Agrega porcentajes de comisión haciendo clic en "Agregar Porcentaje"</small>
                    `;
                    container.appendChild(emptyState);
                }
            }, 300);
        }
    });
    
    // Validación del formulario
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            // Verificar utilidad restante
            const precioField = document.getElementById('precio');
            let precioBase = parseFloat(precioField.value.replace(/[$,]/g, '')) || 0;
            let totalComisiones = 0;

            const rows = document.querySelectorAll('.porcentaje-row');
            rows.forEach(row => {
                const modo = row.querySelector('.modo-comision-input').value;
                if (modo === 'porcentaje') {
                    let p = parseFloat(row.querySelector('.valor-porcentaje-input').value) || 0;
                    totalComisiones += (precioBase * p) / 100;
                } else {
                    let m = parseFloat(row.querySelector('.valor-monto-input').value) || 0;
                    totalComisiones += m;
                }
            });

            if (totalComisiones > precioBase) {
                e.preventDefault();
                alert('Error: Las comisiones totales ($' + totalComisiones.toFixed(2) + ') no pueden ser mayores al precio del paquete ($' + precioBase.toFixed(2) + ').');
                return false;
            }

            // Limpiar formato de moneda antes de enviar
            if (precioField && precioField.value) {
                const valorLimpio = precioField.value.replace(/[$,]/g, '');
                if (valorLimpio && !isNaN(parseFloat(valorLimpio))) {
                    precioField.value = parseFloat(valorLimpio);
                }
            }
        });
    }
});

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
        min-height: 70vh;
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
        flex-grow: 1;
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
    .form-label[for="nombre"]::after,
    .form-label[for="precio"]::after,
    .form-label[for="descripcion"]::after {
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

    textarea.form-control {
        resize: vertical;
        min-height: 80px;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
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

    /* Estilos para porcentajes */
    .porcentaje-row {
        background: #f6f8fa;
        border: 1px solid #d1d9e0;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 16px;
        transition: all 0.3s ease;
    }

    .porcentaje-row:hover {
        border-color: #afb8c1;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    .porcentaje-content {
        margin-bottom: 16px;
    }

    .porcentaje-actions {
        padding-top: 12px;
        border-top: 1px solid #d1d9e0;
    }

    .remove-porcentaje {
        width: 100%;
        padding: 8px 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        background: #dc3545;
        border: 1px solid #dc3545;
        color: white;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .remove-porcentaje:hover {
        background: #c82333;
        border-color: #c82333;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(220, 53, 69, 0.2);
    }

    /* Estado vacío */
    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: #656d76;
        background: #f8fafc;
        border: 2px dashed #d1d9e0;
        border-radius: 8px;
    }

    .empty-icon {
        font-size: 48px;
        color: #afb8c1;
        margin-bottom: 16px;
    }

    .empty-text {
        font-size: 16px;
        font-weight: 500;
        margin: 0 0 8px 0;
        color: #374151;
    }

    .empty-hint {
        font-size: 14px;
        color: #6b7280;
    }

    /* Botones */
    .form-actions {
        grid-column: 1 / -1;
        padding: 24px 32px;
        display: flex;
        justify-content: space-between;
        gap: 12px;
        border-top: 1px solid #e1e5e9;
        background: #f8fafc;
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

    .btn-primary {
        color: #ffffff;
        background: #0969da;
        border-color: #0969da;
    }

    .btn-primary:hover {
        background: #0860ca;
        border-color: #0860ca;
    }

    .btn-success {
        color: #ffffff;
        background: #22c55e;
        border-color: #22c55e;
        font-size: 12px;
        padding: 6px 12px;
    }

    .btn-success:hover {
        background: #16a34a;
        border-color: #16a34a;
    }

    .btn-danger {
        color: #ffffff;
        background: #dc3545;
        border-color: #dc3545;
    }

    .btn-danger:hover {
        background: #c82333;
        border-color: #c82333;
    }

    .btn-sm {
        font-size: 12px;
        padding: 6px 12px;
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
        
        .form-row {
            grid-template-columns: 1fr;
        }
        
        .porcentaje-content {
            margin-bottom: 16px;
        }
    }

    /* Mejoras visuales adicionales */
    .d-flex {
        display: flex;
    }

    .justify-content-between {
        justify-content: space-between;
    }

    .align-items-center {
        align-items: center;
    }

    .mb-0 {
        margin-bottom: 0;
    }

    .mb-4 {
        margin-bottom: 24px;
    }

    .me-1 {
        margin-right: 8px;
    }

    /* Animaciones */
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .porcentaje-row {
        animation: slideIn 0.3s ease;
    }
</style>