<!-- Formulario Minimalista en 2 Columnas -->
<div class="minimal-form">
    <div class="form-container">
        
        <div class="form-layout">
            <!-- Columna Izquierda -->
            <div class="left-column">
                
                <!-- Información Personal -->
                <div class="form-section">
                    <h6 class="section-title">Información Personal</h6>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror"
                                   value="{{ old('nombre', $cliente?->nombre) }}" id="nombre" placeholder="Nombre">
                            @error('nombre')<div class="error-text">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-group">
                            <label for="apellido" class="form-label">Apellido <span class="text-danger">*</span></label>
                            <input type="text" name="apellido" class="form-control @error('apellido') is-invalid @enderror"
                                   value="{{ old('apellido', $cliente?->apellido) }}" id="apellido" placeholder="Apellido">
                            @error('apellido')<div class="error-text">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email" class="form-label">Correo Electrónico</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                                   value="{{ old('email', $cliente?->email) }}" id="email" placeholder="email@ejemplo.com">
                            @error('email')<div class="error-text">{{ $message }}</div>@enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="tel" name="telefono" class="form-control @error('telefono') is-invalid @enderror" 
                                   value="{{ old('telefono', $cliente?->telefono) }}" id="telefono" placeholder="Teléfono">
                            @error('telefono')<div class="error-text">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Columna Derecha -->
            <div class="right-column">
                
                <!-- Información de Domicilio -->
                <div class="form-section">
                    <h6 class="section-title">Información de Domicilio</h6>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="calle_y_numero" class="form-label">Calle y Número <span class="text-danger">*</span></label>
                            <input type="text" name="calle_y_numero" class="form-control @error('calle_y_numero') is-invalid @enderror" 
                                   value="{{ old('calle_y_numero', $cliente?->calle_y_numero) }}" id="calle_y_numero" placeholder="Calle y Número">
                            @error('calle_y_numero')<div class="error-text">{{ $message }}</div>@enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="codigo_postal" class="form-label">C.P.</label>
                            <input type="text" name="codigo_postal" class="form-control @error('codigo_postal') is-invalid @enderror" 
                                   value="{{ old('codigo_postal', $cliente?->codigo_postal) }}" id="codigo_postal" placeholder="C.P.">
                            @error('codigo_postal')<div class="error-text">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="cruces" class="form-label">Entre Calles / Referencias</label>
                        <input type="text" name="cruces" class="form-control @error('cruces') is-invalid @enderror" 
                               value="{{ old('cruces', $cliente?->cruces) }}" id="cruces" placeholder="Referencias">
                        @error('cruces')<div class="error-text">{{ $message }}</div>@enderror
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="colonia" class="form-label">Colonia <span class="text-danger">*</span></label>
                            <input type="text" name="colonia" class="form-control @error('colonia') is-invalid @enderror" 
                                   value="{{ old('colonia', $cliente?->colonia) }}" id="colonia" placeholder="Colonia">
                            @error('colonia')<div class="error-text">{{ $message }}</div>@enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="municipio" class="form-label">Municipio <span class="text-danger">*</span></label>
                            <input type="text" name="municipio" class="form-control @error('municipio') is-invalid @enderror" 
                                   value="{{ old('municipio', $cliente?->municipio) }}" id="municipio" placeholder="Municipio">
                            @error('municipio')<div class="error-text">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="estado" class="form-label">Estado</label>
                        <input type="text" name="estado" class="form-control @error('estado') is-invalid @enderror" 
                               value="{{ old('estado', $cliente?->estado) }}" id="estado" placeholder="Estado">
                        @error('estado')<div class="error-text">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Campo oculto para el domicilio completo -->
        <input type="hidden" name="domicilio_completo" id="domicilio_completo" value="{{ old('domicilio_completo', $cliente?->domicilio_completo) }}">

        <!-- Botones -->
        <div class="form-actions">
            <a href="{{ url()->previous() }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-circle me-2"></i>Guardar Cliente
            </button>
        </div>
    </div>
</div>

<script>
// Script para actualizar el domicilio completo automáticamente
document.addEventListener('DOMContentLoaded', function() {
    const campos = ['calle_y_numero', 'cruces', 'colonia', 'municipio', 'estado', 'codigo_postal'];
    const domicilioInput = document.getElementById('domicilio_completo');
    
    campos.forEach(campo => {
        const input = document.getElementById(campo);
        if (input) {
            input.addEventListener('input', actualizarDomicilio);
        }
    });
    
    // Actualizar al cargar la página si hay datos
    actualizarDomicilio();
    
    function actualizarDomicilio() {
        const partes = [];
        
        const calleNumero = document.getElementById('calle_y_numero').value.trim();
        if (calleNumero) partes.push(calleNumero);
        
        const cruces = document.getElementById('cruces').value.trim();
        if (cruces) partes.push(`Entre: ${cruces}`);
        
        const colonia = document.getElementById('colonia').value.trim();
        if (colonia) partes.push(colonia);
        
        const municipio = document.getElementById('municipio').value.trim();
        if (municipio) partes.push(municipio);
        
        const estado = document.getElementById('estado').value.trim();
        if (estado) partes.push(estado);
        
        const cp = document.getElementById('codigo_postal').value.trim();
        if (cp) partes.push(`CP: ${cp}`);
        
        const domicilioCompleto = partes.join(', ');
        domicilioInput.value = domicilioCompleto;
    }
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

    .text-danger {
        color: #da3633;
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

    /* Altura equilibrada para las columnas - removido para permitir altura natural */
    .left-column .form-section:last-child,
    .right-column .form-section:last-child {
        /* flex-grow: 1; - removido para evitar estiramiento innecesario */
    }

    /* Mejora visual para separación de secciones */
    .right-column .form-section:first-child {
        border-top: none;
    }
</style>