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
        .navbar-brand img {
            height: 40px;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }

        .card {
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
        }

        .table {
            width: 100%;
            margin-bottom: 1rem;
            color: #212529;
        }

        .table th,
        .table td {
            padding: 0.5rem;
            border-top: 1px solid #dee2e6;
        }

        /* Lista de Resumen Financiero */
        .financial-summary-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .financial-item {
            display: flex;
            align-items: center;
            background: white;
            border: 1px solid var(--gray-200);
            border-radius: 12px;
            padding: 1rem;
            transition: all 0.2s ease;
        }

        .financial-item:hover {
            border-color: var(--gray-400);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .financial-item-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: var(--gray-100);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            border: 1px solid var(--gray-300);
            flex-shrink: 0;
        }

        .financial-item-icon i {
            font-size: 1.25rem;
            color: var(--gray-900);
        }

        .financial-item-details {
            flex: 1;
        }

        .financial-item-label {
            font-size: 0.9rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 0.1rem;
        }

        .financial-item-subtext {
            font-size: 0.75rem;
            color: var(--gray-600);
        }

        .financial-item-amount {
            font-size: 1.1rem;
            font-weight: 800;
            color: var(--gray-900);
            white-space: nowrap;
        }

        @media print {
            .financial-item {
                border: 1px solid #000 !important;
                padding: 0.5rem !important;
                break-inside: avoid;
            }

            .financial-item-icon {
                border: 1px solid #000 !important;
                background: white !important;
            }
        }
    </style>
    @stack('styles')
</head>

<body>


    <div id="app">
        <nav class="d-print-none navbar navbar-expand-md navbar-light"
            style="background-color: transparent !important; box-shadow: none !important;">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/home') }}">
                    <img src="{{ asset('shalom_logo.svg') }}" alt="Shalom Logo" height="40">
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                    aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
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
                                    <a class="nav-link {{ request()->routeIs('clientes.*') ? 'active' : '' }}"
                                        href="{{ route('clientes.index') }}">{{ __('Clientes') }}</a>
                                </li>
                                <li>
                                    <a class="nav-link {{ request()->routeIs('contratos.*') ? 'active' : '' }}"
                                        href="{{ route('contratos.index') }}">{{ __('Contratos') }}</a>
                                </li>
                            @endif

                            @if(auth()->user() && auth()->user()->role === 'admin')
                                <li>
                                    <a class="nav-link {{ request()->routeIs('ajustes.*') ? 'active' : '' }}"
                                        href="{{ route('ajustes.index') }}">{{ __('Ajustes') }}</a>
                                </li>
                            @endif

                            @if(auth()->user() && auth()->user()->role !== 'admin')
                                <li class="nav-item dropdown">
                                    <a id="navbarDropdown" class="nav-link" href="#" role="button" data-bs-toggle="dropdown"
                                        aria-haspopup="true" aria-expanded="false" v-pre>
                                        {{ isset($currentUser) && $currentUser ? $currentUser->name : (auth()->user() ? auth()->user()->name : 'Usuario') }}
                                    </a>

                                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                        <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault();
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


    @stack('scripts')
    @yield('scripts')
</body>

</html>