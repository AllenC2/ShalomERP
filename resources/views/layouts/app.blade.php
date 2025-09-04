<!doctype html>
<html lang="es-MX">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('shalom_ico.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    <!-- Iconos de Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/sass/rainbow.scss', 'resources/js/app.js'])
    
    <!-- Estilos de emergencia en caso de fallo de Vite -->
    <style>
        .navbar-brand img { height: 40px; }
        .btn-primary { background-color: #007bff; border-color: #007bff; }
        .card { border: 1px solid #dee2e6; border-radius: 0.375rem; }
        .table { width: 100%; margin-bottom: 1rem; color: #212529; }
        .table th, .table td { padding: 0.5rem; border-top: 1px solid #dee2e6; }
        
        /* Pantalla de bloqueo para móviles */
        .mobile-block {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #E1B240 0%, #79481D 100%);
            z-index: 9999;
            justify-content: center;
            align-items: center;
            color: white;
            text-align: center;
            padding: 20px;
            box-sizing: border-box;
        }
        
        .mobile-block-content {
            max-width: 400px;
            padding: 40px 20px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .mobile-block-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            color: #fff;
        }
        
        .mobile-block h2 {
            font-size: 1.8rem;
            margin-bottom: 15px;
            font-weight: 600;
        }
        
        .mobile-block p {
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 0;
            opacity: 0.9;
        }
        
        /* Mostrar el bloqueo en dispositivos móviles */
        @media screen and (max-width: 768px) {
            .mobile-block {
                display: flex !important;
            }
            
            #app {
                display: none !important;
            }
        }
        
        /* También bloquear en tablets en orientación vertical */
        @media screen and (max-width: 1024px) and (orientation: portrait) {
            .mobile-block {
                display: flex !important;
            }
            
            #app {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <!-- Pantalla de bloqueo para dispositivos móviles -->
    <div class="mobile-block">
        <div class="mobile-block-content">
            <div class="mobile-block-icon">
                <i class="fas fa-desktop"></i>
            </div>
            <h2>Acceso Restringido</h2>
            <p>Esta aplicación está diseñada para ser utilizada únicamente desde una computadora de escritorio o laptop.</p>
            <p style="margin-top: 15px; font-size: 0.95rem;">Por favor, accede desde un dispositivo con pantalla más grande para una mejor experiencia.</p>
        </div>
    </div>

    <div id="app">
        <nav class="d-print-none navbar navbar-expand-md navbar-light" style="background-color: transparent !important; box-shadow: none !important;">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/home') }}">
                    <img src="{{ asset('shalom_logo.svg') }}" alt="Shalom Logo" height="40">
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto">

                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto">
                        <!-- Authentication Links -->
                        @if(auth()->guest() && (!isset($isAuthenticated) || !$isAuthenticated))
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                                </li>
                            @endif
                        @else
                            @if(auth()->user() && auth()->user()->role === 'admin')
                            <li>
                                <a class="nav-link {{ request()->routeIs('clientes.*') ? 'active' : '' }}" href="{{ route('clientes.index') }}">{{ __('Clientes') }}</a>
                            </li>
                            <li>
                                <a class="nav-link {{ request()->routeIs('contratos.*') ? 'active' : '' }}" href="{{ route('contratos.index') }}">{{ __('Contratos') }}</a>
                            </li>
                            @endif

                            @if(auth()->user() && auth()->user()->role === 'admin')
                                <li>
                                    <a class="nav-link {{ request()->routeIs('ajustes.*') ? 'active' : '' }}" href="{{ route('ajustes.index') }}">{{ __('Ajustes') }}</a>
                                </li>
                            @endif

                            @if(auth()->user() && auth()->user()->role !== 'admin')
                                <li class="nav-item dropdown">
                                    <a id="navbarDropdown" class="nav-link" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                        {{ isset($currentUser) && $currentUser ? $currentUser->name : (auth()->user() ? auth()->user()->name : 'Usuario') }}
                                    </a>

                                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                        <a class="dropdown-item" href="{{ route('logout') }}"
                                           onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                            {{ __('Cerrar sesión') }}
                                        </a>

                                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                            @csrf
                                        </form>
                                    </div>
                                </li>
                            @endif
                        @endif
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            @yield('content')
        </main>
    </div>
    
    <!-- Script para detección adicional de dispositivos móviles -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Detección adicional de dispositivos móviles usando User Agent
            const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
            const isSmallScreen = window.innerWidth <= 768;
            const isTabletPortrait = window.innerWidth <= 1024 && window.innerHeight > window.innerWidth;
            
            if (isMobile || isSmallScreen || isTabletPortrait) {
                document.querySelector('.mobile-block').style.display = 'flex';
                document.querySelector('#app').style.display = 'none';
            }
            
            // Reactivar detección al cambiar el tamaño de ventana
            window.addEventListener('resize', function() {
                const currentWidth = window.innerWidth;
                const currentHeight = window.innerHeight;
                const isCurrentlySmall = currentWidth <= 768;
                const isCurrentlyTabletPortrait = currentWidth <= 1024 && currentHeight > currentWidth;
                
                if (isMobile || isCurrentlySmall || isCurrentlyTabletPortrait) {
                    document.querySelector('.mobile-block').style.display = 'flex';
                    document.querySelector('#app').style.display = 'none';
                } else {
                    document.querySelector('.mobile-block').style.display = 'none';
                    document.querySelector('#app').style.display = 'block';
                }
            });
        });
    </script>
    
    @yield('scripts')
</body>
</html>
