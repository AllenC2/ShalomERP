<!-- Formulario Minimalista en 2 Columnas -->
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