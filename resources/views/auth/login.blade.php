<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Iniciar Sesión - Shalom ERP</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/sass/rainbow.scss', 'resources/js/app.js'])
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <style>
        .login-card {
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

        .login-card::before {
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

        .login-card .card-body {
            position: relative;
            z-index: 2;
        }

        .login-card:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
            box-shadow: 
                0 12px 40px rgba(0, 0, 0, 0.15),
                inset 0 1px 0 rgba(255, 255, 255, 0.4),
                inset 0 -1px 0 rgba(255, 255, 255, 0.2);
        }

        /* Logo Styles */
        .logo-container {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-logo {
            max-width: 120px;
            height: auto;
            filter: drop-shadow(0 4px 8px rgba(121, 72, 29, 0.2));
            transition: all 0.3s ease;
        }

        .login-logo:hover {
            transform: scale(1.05);
            filter: drop-shadow(0 6px 12px rgba(121, 72, 29, 0.3));
        }

        .login-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: #2d3748;
            margin: 0;
            line-height: 1.2;
        }

        .login-subtitle {
            font-size: 0.95rem;
            margin: 0;
            color: #718096 !important;
        }

        /* Header page styles */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding: 1.5rem 0;
        }

        .header-content {
            display: flex;
            align-items: center;
        }

        .header-content.justify-content-center {
            justify-content: center;
        }

        .header-icon {
            background: linear-gradient(135deg, #79481D 0%, #8B5A2B 100%);
            color: white;
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1.5rem;
            font-size: 1.5rem;
            box-shadow: 0 10px 30px rgba(121, 72, 29, 0.3);
            transition: all 0.3s ease;
        }

        .login-card:hover .header-icon {
            transform: scale(1.05);
        }

        .page-title {
            font-size: 2rem;
            font-weight: 700;
            color: #2d3748;
            margin: 0;
            line-height: 1.2;
        }

        .page-subtitle {
            color: #718096;
            font-size: 1rem;
            margin: 0;
            margin-top: 0.25rem;
        }

        .login-input {
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 12px !important;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(5px);
            color: #2d3748;
        }

        .login-input:focus {
            border-color: #79481D;
            box-shadow: 0 0 0 0.25rem rgba(121, 72, 29, 0.1);
            background: rgba(255, 255, 255, 0.95);
            transform: translateY(-1px);
        }

        .login-input:hover {
            border-color: rgba(121, 72, 29, 0.5);
            background: rgba(255, 255, 255, 0.9);
        }

        .login-btn {
            background: linear-gradient(135deg, #79481D 0%, #8B5A2B 100%);
            border: none;
            border-radius: 12px !important;
            color: white;
            font-weight: bold;
            padding: 0.75rem 1.5rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 8px rgba(121, 72, 29, 0.3);
        }

        .login-btn:hover {
            background: linear-gradient(135deg, #8B5A2B 0%, #79481D 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(121, 72, 29, 0.4);
            color: white;
        }

        .login-btn:active {
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

        .form-check-input:checked {
            background-color: #79481D;
            border-color: #79481D;
        }

        .form-check-input:focus {
            border-color: #79481D;
            box-shadow: 0 0 0 0.25rem rgba(121, 72, 29, 0.1);
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

        .login-card {
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

        /* Responsive adjustments */
        @media (max-width: 576px) {
            .login-card {
                margin: 1rem;
            }
            
            .card-body {
                padding: 2rem 1.5rem !important;
            }

            .login-logo {
                max-width: 100px;
            }

            .login-title {
                font-size: 1.5rem !important;
            }

            .login-subtitle {
                font-size: 0.9rem !important;
            }
            
            .header-icon {
                font-size: 1.2rem !important;
                width: 50px !important;
                height: 50px !important;
                margin-right: 1rem !important;
            }

            .page-title {
                font-size: 1.5rem !important;
            }

            .header-content {
                flex-direction: column;
                text-align: center;
            }

            .header-icon {
                margin-right: 0 !important;
                margin-bottom: 1rem !important;
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
    <div class="col-md-5">
        <!-- Logo Section -->
        <div class="logo-container mb-4 flex-column text-center">
            <h1 class="login-title mb-1">Iniciar Sesión</h1>
            <p class="login-subtitle">Bienvenido, por favor ingresa tus credenciales</p>
        </div>
        <div class="card border-0 shadow-sm rounded-4 login-card">    
            <div class="card-body">
                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="mb-4">
                        <label for="email" class="form-label text-muted fw-bold small text-uppercase">
                            <i class="bi bi-envelope me-2" style="color: #79481D;"></i>{{ __('Correo Electrónico') }}
                        </label>
                        <input id="email" type="email" class="form-control form-control-lg login-input @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                        @error('email')
                            <div class="invalid-feedback">
                                <i class="bi bi-exclamation-triangle me-1"></i>{{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label text-muted fw-bold small text-uppercase">
                            <i class="bi bi-lock me-2" style="color: #79481D;"></i>{{ __('Contraseña') }}
                        </label>
                        <input id="password" type="password" class="form-control form-control-lg login-input @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">
                        @error('password')
                            <div class="invalid-feedback">
                                <i class="bi bi-exclamation-triangle me-1"></i>{{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label text-muted fw-bold small" for="remember">
                                {{ __('Recordar cuenta') }}
                            </label>
                        </div>
                        @if (Route::has('password.request'))
                            <a class="modern-link text-decoration-none fw-bold small" href="{{ route('password.request') }}" style="color: #79481D;">
                                {{ __('¿Olvidaste tu contraseña?') }}
                            </a>
                        @endif
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-lg login-btn">
                            <i class="bi bi-box-arrow-in-right me-2"></i>{{ __('Iniciar Sesión') }}
                        </button>
                    </div>
                </form>

                <!-- Footer del card -->
                <div class="text-center mt-4">
                    <div class="border-top pt-3">
                        <small class="text-muted">
                            <i class="bi bi-shield-check me-1" style="color: #79481D;"></i>
                            Acceso seguro y protegido
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
