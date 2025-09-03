<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Crear Cuenta - Shalom ERP</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/sass/rainbow.scss', 'resources/js/app.js'])
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <style>
        .register-card {
            /* Efecto Crystal/Glass Morphism similar a Welcome */
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px !important;
            box-shadow: 
                0 8px 32px rgba(0, 0, 0, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.3),
                inset 0 -1px 0 rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .register-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, 
                rgba(255, 255, 255, 0.1) 0%, 
                rgba(255, 255, 255, 0.05) 50%, 
                rgba(255, 255, 255, 0.1) 100%);
            pointer-events: none;
            z-index: 1;
        }

        .register-card .card-body {
            position: relative;
            z-index: 2;
        }

        .register-card:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
            box-shadow: 
                0 12px 40px rgba(0, 0, 0, 0.15),
                inset 0 1px 0 rgba(255, 255, 255, 0.4),
                inset 0 -1px 0 rgba(255, 255, 255, 0.2);
        }

        .register-icon {
            transition: all 0.3s ease;
        }

        .register-card:hover .register-icon {
            transform: scale(1.05);
        }

        .register-input {
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 12px !important;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(5px);
            color: #2d3748;
        }

        .register-input:focus {
            border-color: #79481D;
            box-shadow: 0 0 0 0.25rem rgba(121, 72, 29, 0.1);
            background: rgba(255, 255, 255, 0.95);
            transform: translateY(-1px);
        }

        .register-input:hover {
            border-color: rgba(121, 72, 29, 0.5);
            background: rgba(255, 255, 255, 0.9);
        }

        .register-btn {
            background: linear-gradient(135deg, #79481D 0%, #8B5A2B 100%);
            border: none;
            border-radius: 12px !important;
            color: white;
            font-weight: bold;
            padding: 0.75rem 1.5rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 8px rgba(121, 72, 29, 0.3);
        }

        .register-btn:hover {
            background: linear-gradient(135deg, #8B5A2B 0%, #79481D 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(121, 72, 29, 0.4);
            color: white;
        }

        .register-btn:active {
            transform: translateY(0);
            box-shadow: 0 2px 4px rgba(121, 72, 29, 0.3);
        }

        .modern-link {
            transition: all 0.3s ease;
            position: relative;
        }

        .modern-link:hover {
            color: #8B5A2B !important;
            text-decoration: underline !important;
        }

        /* Animación de entrada */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .register-card {
            animation: fadeInUp 0.6s ease-out;
        }

        /* Efectos de validation feedback */
        .invalid-feedback {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #991b1b;
            border-radius: 8px;
            padding: 0.5rem 0.75rem;
            margin-top: 0.5rem;
            border-left: 4px solid #dc3545;
            font-size: 0.875rem;
            font-weight: 500;
        }

        /* Efectos para las características destacadas */
        .bg-success.bg-opacity-10 {
            transition: all 0.3s ease;
        }

        .register-card:hover .bg-success.bg-opacity-10 {
            background: rgba(25, 135, 84, 0.15) !important;
            transform: scale(1.02);
        }

        .bg-success.bg-opacity-10 i {
            transition: all 0.3s ease;
        }

        .register-card:hover .bg-success.bg-opacity-10 i {
            transform: scale(1.1);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .row .col-md-6 {
                margin-bottom: 1rem !important;
            }
        }

        @media (max-width: 576px) {
            .register-card {
                margin: 1rem;
            }
            
            .card-body {
                padding: 2rem 1.5rem !important;
            }
            
            .register-icon i {
                font-size: 2.5rem !important;
            }

            .row .col-md-6 {
                padding-left: 15px;
                padding-right: 15px;
            }
        }
    </style>
</head>
<body>
    {{-- Rainbow background container --}}
    <div class="rainbow-background">
        @for ($i = 1; $i <= 25; $i++)
            <div class="rainbow"></div>
        @endfor
        
        <div class="h"></div>
        <div class="v"></div>
    </div>
    
    <div class="container d-flex align-items-center justify-content-center min-vh-100" style="position: relative; z-index: 10;">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4 register-card">
            <div class="card-body p-5">
                <!-- Header con icono -->
                <div class="text-center mb-4">
                    <div class="register-icon mb-3">
                        <i class="bi bi-person-plus-fill" style="color: #79481D; font-size: 3.5rem;"></i>
                    </div>
                    <h3 class="fw-bold text-dark mb-2">{{ __('Crear cuenta') }}</h3>
                    <p class="text-muted mb-0">Únete al sistema Shalom ERP</p>
                </div>

                <form method="POST" action="{{ route('register') }}">
                    @csrf

                    <div class="mb-4">
                        <label for="name" class="form-label text-muted fw-bold small text-uppercase">
                            <i class="bi bi-person me-2" style="color: #79481D;"></i>{{ __('Nombre completo') }}
                        </label>
                        <input id="name" type="text" class="form-control form-control-lg register-input @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autofocus>
                        @error('name')
                            <div class="invalid-feedback">
                                <i class="bi bi-exclamation-triangle me-1"></i>{{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="email" class="form-label text-muted fw-bold small text-uppercase">
                            <i class="bi bi-envelope me-2" style="color: #79481D;"></i>{{ __('Correo electrónico') }}
                        </label>
                        <input id="email" type="email" class="form-control form-control-lg register-input @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required>
                        @error('email')
                            <div class="invalid-feedback">
                                <i class="bi bi-exclamation-triangle me-1"></i>{{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label for="password" class="form-label text-muted fw-bold small text-uppercase">
                                <i class="bi bi-lock me-2" style="color: #79481D;"></i>{{ __('Contraseña') }}
                            </label>
                            <input id="password" type="password" class="form-control form-control-lg register-input @error('password') is-invalid @enderror" name="password" required>
                            @error('password')
                                <div class="invalid-feedback">
                                    <i class="bi bi-exclamation-triangle me-1"></i>{{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-4">
                            <label for="password-confirm" class="form-label text-muted fw-bold small text-uppercase">
                                <i class="bi bi-shield-check me-2" style="color: #79481D;"></i>{{ __('Confirmar contraseña') }}
                            </label>
                            <input id="password-confirm" type="password" class="form-control form-control-lg register-input" name="password_confirmation" required>
                        </div>
                    </div>

                    <!-- Información adicional -->
                    <div class="mb-4">
                        <div class="p-3 bg-success bg-opacity-10 border border-success border-opacity-25 rounded">
                            <div class="row text-center">
                                <div class="col-4">
                                    <i class="bi bi-shield-check text-success d-block fs-4 mb-1"></i>
                                    <small class="text-muted d-block fw-bold">Seguro</small>
                                </div>
                                <div class="col-4">
                                    <i class="bi bi-lightning-charge text-warning d-block fs-4 mb-1"></i>
                                    <small class="text-muted d-block fw-bold">Rápido</small>
                                </div>
                                <div class="col-4">
                                    <i class="bi bi-people text-primary d-block fs-4 mb-1"></i>
                                    <small class="text-muted d-block fw-bold">Colaborativo</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-lg register-btn">
                            <i class="bi bi-person-check me-2"></i>{{ __('Crear mi cuenta') }}
                        </button>
                    </div>
                </form>

                <!-- Links adicionales -->
                <div class="mt-4">
                    <div class="border-top pt-3 text-center">
                        <a href="{{ route('login') }}" class="modern-link text-decoration-none fw-bold" style="color: #79481D;">
                            <i class="bi bi-arrow-left me-1"></i>¿Ya tienes cuenta? Inicia sesión
                        </a>
                    </div>
                </div>

                <!-- Footer del card -->
                <div class="text-center mt-3">
                    <small class="text-muted">
                        <i class="bi bi-info-circle me-1" style="color: #79481D;"></i>
                        Al registrarte aceptas nuestros términos de servicio
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
