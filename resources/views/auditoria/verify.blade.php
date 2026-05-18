@extends('layouts.app')

@section('template_title')
    Verificación de Seguridad Requerida
@endsection

@section('content')
<div class="bg-light min-vh-100 d-flex align-items-center justify-content-center py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <!-- Tarjeta de Desafío de Seguridad Estilo Premium -->
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden" style="background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.25);">
                    <!-- Línea decorativa dorada de ShalomERP -->
                    <div style="height: 6px; background: linear-gradient(90deg, #E1B240 0%, #79481D 100%);"></div>
                    
                    <div class="card-body p-5">
                        <!-- Icono de Seguridad Animado -->
                        <div class="text-center mb-4">
                            <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3 shadow" 
                                 style="width: 80px; height: 80px; background: linear-gradient(135deg, #79481D 0%, #3a220e 100%); color: #E1B240; font-size: 2.2rem; animation: pulse-shadow 2s infinite;">
                                <i class="bi bi-shield-lock-fill"></i>
                            </div>
                            <h3 class="fw-bold mb-1" style="color: #79481D; font-family: 'Outfit', sans-serif;">Doble Factor de Seguridad</h3>
                            <p class="text-muted small">Acceso exclusivo a la Bitácora Forense de Auditoría</p>
                        </div>

                        <!-- Alerta de Advertencia de Datos Sensibles -->
                        <div class="alert alert-warning border-0 small d-flex align-items-start py-3 mb-4 rounded-3" style="background-color: rgba(225, 178, 64, 0.08);">
                            <i class="bi bi-exclamation-triangle-fill fs-5 me-2 mt-0.5" style="color: #79481D;"></i>
                            <div>
                                <strong style="color: #79481D;">Zona Altamente Restringida:</strong> 
                                Esta sección contiene deltas forenses y logs detallados del sistema. Requiere verificación de clave secundaria.
                            </div>
                        </div>

                        <!-- Formulario de Verificación -->
                        <form method="POST" action="{{ route('auditoria.verify.submit') }}">
                            @csrf

                            <!-- Campo de Clave de Seguridad -->
                            <div class="mb-4">
                                <label for="password" class="form-label small fw-bold text-muted text-uppercase">Clave de Seguridad Especial</label>
                                <div class="input-group rounded-3 overflow-hidden border">
                                    <span class="input-group-text bg-white border-0 text-muted"><i class="bi bi-key-fill"></i></span>
                                    <input type="password" class="form-control border-0 bg-white" id="password" name="password" 
                                           placeholder="Ingresa la clave secundaria..." required autofocus>
                                    <button class="btn btn-white border-0 text-muted px-3" type="button" id="togglePassword">
                                        <i class="bi bi-eye" id="toggleIcon"></i>
                                    </button>
                                </div>
                                @error('password')
                                    <div class="text-danger small mt-2 fw-semibold">
                                        <i class="bi bi-x-circle-fill me-1"></i>{{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <!-- Botón de Envío -->
                            <div class="d-grid mb-3">
                                <button type="submit" class="btn text-white fw-bold py-2.5 rounded-3 shadow" 
                                        style="background: linear-gradient(135deg, #E1B240 0%, #79481D 100%); transition: all 0.25s ease;"
                                        onmouseover="this.style.transform='translateY(-1px)'" 
                                        onmouseout="this.style.transform='translateY(0)'">
                                    <i class="bi bi-unlock-fill me-2"></i>Verificar y Acceder
                                </button>
                            </div>

                            <!-- Enlace de Cancelación -->
                            <div class="text-center">
                                <a href="{{ route('ajustes.index') }}" class="text-muted small text-decoration-none hover-underline fw-semibold">
                                    <i class="bi bi-x-circle me-1"></i>Cancelar y Volver
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Efecto pulse de sombra para el icono de seguridad */
    @keyframes pulse-shadow {
        0% {
            box-shadow: 0 0 0 0 rgba(121, 72, 29, 0.4);
        }
        70% {
            box-shadow: 0 0 0 15px rgba(121, 72, 29, 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(121, 72, 29, 0);
        }
    }
    
    .form-control:focus {
        box-shadow: none !important;
    }
    
    .input-group:focus-within {
        border-color: #E1B240 !important;
        box-shadow: 0 0 0 0.25rem rgba(225, 178, 64, 0.2) !important;
    }
    
    .hover-underline:hover {
        text-decoration: underline !important;
        color: #79481D !important;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const passwordInput = document.getElementById('password');
        const toggleButton = document.getElementById('togglePassword');
        const toggleIcon = document.getElementById('toggleIcon');

        // Toggle Mostrar/Ocultar Contraseña
        toggleButton.addEventListener('click', function () {
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('bi-eye');
                toggleIcon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('bi-eye-slash');
                toggleIcon.classList.add('bi-eye');
            }
        });
    });
</script>
@endpush
@endsection
