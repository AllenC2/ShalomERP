<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SHALOM ERP - Bienvenido</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body {
            min-height: 100vh;
            background: #f4f6f8;
            font-family: 'Inter', Arial, sans-serif;
        }
        .main-card {
            border-radius: 1rem;
            box-shadow: 0 2px 16px rgba(0,0,0,0.07);
            background: #fff;
        }
        .navbar {
            border-radius: 1rem;
            margin-bottom: 2rem;
            background: #a47026 !important;
        }
        .navbar-brand {
            color: #fff !important;
            font-weight: 700;
            letter-spacing: 1px;
        }
        .nav-link {
            color: #fff !important;
            font-weight: 500;
        }
        .nav-link.active, .nav-link:hover {
            color: #fff !important;
        }
        .funeral-logo {
            width: 80px;
            margin-bottom: 1.5rem;

        }
        .list-group-item {
            border: none;
            background: transparent;
            padding-left: 0;
        }
        .btn-primary {
            background: #a47026;
            border: none;
        }
        .btn-primary:hover {
            background: #343a40;
        }
        .system-title {
            font-size: 2rem;
            font-weight: 700;
            color: #212529;
        }
        .system-desc {
            color: #6c757d;
        }
    </style>
</head>
<body class="d-flex flex-column justify-content-center align-items-center">
    <nav class="navbar navbar-expand-lg shadow-sm px-4 py-2 w-100" style="max-width: 600px;">
        <div class="container-fluid d-flex justify-content-center">
            <span class="navbar-brand mx-auto">
            SHALOM ERP
            </span>
        </div>
    </nav>
    <main class="main-card p-4 w-100" style="max-width: 600px;">
        <div class="text-center">
            <img src="{{ asset('shalom_ico.svg') }}" alt="Funeraria Logo" class="funeral-logo">
            <h1 class="system-title mb-2">Bienvenido a SHALOM ERP</h1>
            <p class="system-desc mb-4">Sistema funeral con agenda de clientes, gestión de pagos, cobranza y comisiones. Accede a tus datos ahora mismo.</p>
        </div>
   
        <div class="d-flex justify-content-center gap-3">
            @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/home') }}" class="btn btn-primary px-4">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Entrar al sistema
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-primary px-4">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Iniciar sesión
                            </a>
            
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="btn btn-secondary px-4">
                                    <i class="bi bi-person-plus me-2"></i>Registrarme
                                </a>
                            @endif
                        @endauth
                    @endif
            
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
