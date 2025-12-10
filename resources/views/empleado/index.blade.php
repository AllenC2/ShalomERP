@extends('layouts.app')

@section('template_title')
    Empleados
@endsection

@section('content')
<div class="container py-4" style="max-width: 1600px;">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Header moderno -->
            <div class="page-header">
                <div class="header-content">
                    <div class="header-icon">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div class="header-text">
                        <h1 class="page-title">{{ __('Empleados') }}</h1>
                        <p class="page-subtitle">Gestione y consulte la información de los empleados</p>
                    </div>
                </div>
                <div class="header-actions">
                    <a href="{{ route('empleados.create') }}" class="btn text-secondary border-secondary custom-hover-btn">
                        <i class="bi bi-plus-lg me-1"></i>
                        {{ __('Nuevo Empleado') }}
                    </a>
                    <style>
                        .custom-hover-btn:hover {
                            background: linear-gradient(135deg, #E1B240 0%, #79481D 100%) !important;
                            color: #fff !important;
                            border-color: #E1B240 !important;
                        }
                    </style>
                </div>
            </div>
            <div class="">
                @if ($message = Session::get('success'))
                    <div class="alert alert-success m-4">
                        <p class="mb-0">{{ $message }}</p>
                    </div>
                @endif

                <div class="card-body p-0">
                    <div id="empleadosTableContainer">
                        <div class="table-responsive" id="tabla-empleados">
                            <table class="table table-hover align-middle mb-0 modern-table">
                                <thead class="modern-header">
                                    <tr>
                                        <th scope="col" class="ps-4">ID</th>
                                        <th scope="col">Empleado</th>
                                        <th scope="col">Información de Contacto</th>
                                        <th scope="col">Estado</th>
                                        <th scope="col" class="pe-4">Comisiones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($empleados as $empleado)
                                        <tr class="modern-row clickable-row" data-href="{{ route('empleados.show', $empleado->id) }}">
                                            <td class="ps-4">
                                                <span class="badge bg-light text-dark fw-normal">{{ $empleado->id }}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-circle me-3 d-flex align-items-center justify-content-center" style="min-width: 45px; min-height: 45px; width: 45px; height: 45px; background: linear-gradient(135deg, #E1B240 0%, #79481D 100%); border-radius: 50%;">
                                                        {{ strtoupper(substr($empleado->nombre, 0, 1) . substr($empleado->apellido, 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <div class="fw-semibold text-dark">{{ $empleado->nombre }} {{ $empleado->apellido }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="contact-info">
                                                    <div class="fw-semibold">{{ $empleado->user->email }}</div>
                                                    <small class="text-muted d-block">
                                                        <i class="bi bi-telephone me-1"></i>
                                                        {{ $empleado->telefono }}
                                                    </small>
                                                    <small class="text-muted d-block">
                                                        <i class="bi bi-geo-alt me-1"></i>
                                                        {{ $empleado->domicilio }}
                                                    </small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge {{ ($empleado->estado ?? 'activo') === 'activo' ? 'bg-success' : 'bg-danger' }}">
                                                    {{ ucfirst($empleado->estado ?? 'Activo') }}
                                                </span>
                                            </td>
                                            <td class="pe-4">
                                                <div class="commissions-info">
                                                    @php
                                                        $comisionesPagadas = $empleado->comisiones->where('estado', 'Pagada')->count();
                                                        $comisionesPendientes = $empleado->comisiones->where('estado', 'Pendiente')->count();
                                                        $totalComisiones = $empleado->comisiones->count();
                                                    @endphp
                                                    
                                                    @if($totalComisiones > 0)
                                                        <div class="d-flex align-items-center mb-1">
                                                            <span class="badge bg-success me-2">{{ $comisionesPagadas }}</span>
                                                            <span class="fw-semibold text-success">Pagadas</span>
                                                        </div>
                                                        <div class="d-flex align-items-center">
                                                            <span class="badge bg-warning me-2">{{ $comisionesPendientes }}</span>
                                                            <span class="fw-semibold text-warning">Pendientes</span>
                                                        </div>
                                                    @else
                                                        <div class="text-muted text-center">
                                                            <i class="bi bi-dash-circle me-1"></i>
                                                            Sin comisiones
                                                        </div>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-center mt-3">
                            {!! $empleados->withQueryString()->links() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

<style>
    /* Estilos modernos para la tabla */
    .modern-table {
        border: none !important;
        box-shadow: none !important;
    }

    .modern-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
        color: white !important;
        border: none !important;
    }

    .modern-header th {
        border: none !important;
        padding: 1.2rem 1rem !important;
        font-weight: 600 !important;
        font-size: 0.875rem !important;
        text-transform: uppercase !important;
        letter-spacing: 0.5px !important;
        position: relative;
    }

    .modern-header th:not(:last-child)::after {
        content: '';
        position: absolute;
        right: 0;
        top: 25%;
        height: 50%;
        width: 1px;
        background: rgba(255, 255, 255, 0.2);
    }

    .modern-row {
        border: none !important;
        transition: all 0.3s ease !important;
        background: white !important;
    }

    .modern-row:hover {
        background: linear-gradient(135deg, #f8f9ff 0%, #f0f4ff 100%) !important;
        transform: translateY(-2px) !important;
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.15) !important;
    }

    .modern-row td {
        border: none !important;
        padding: 1.5rem 1rem !important;
        vertical-align: middle !important;
        border-bottom: 1px solid #f1f3f5 !important;
    }

    /* Avatar circular */
    .avatar-circle {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        background: linear-gradient(135deg, #E1B240 0%, #79481D 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        font-size: 0.875rem;
        box-shadow: 0 4px 12px rgba(225, 178, 64, 0.3);
    }

    /* Información de contacto */
    .contact-info {
        font-size: 0.875rem;
    }

    .contact-info i {
        width: 16px;
        font-size: 0.8rem;
        color: #79481D !important;
    }

    /* Información de comisiones */
    .commissions-info {
        font-size: 0.875rem;
    }

    .commissions-info .badge {
        font-size: 0.75rem;
        padding: 0.35rem 0.6rem;
        border-radius: 6px;
        font-weight: 600;
    }

    .commissions-info i {
        width: 16px;
        font-size: 0.8rem;
    }

    /* Filas clickeables */
    .clickable-row {
        cursor: pointer;
    }

    .clickable-row:hover {
        background: linear-gradient(135deg, #f8f9ff 0%, #f0f4ff 100%) !important;
    }

    /* Badge moderno para ID */
    .badge.bg-light {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
        border: 1px solid #dee2e6 !important;
        font-weight: 600 !important;
        padding: 0.5rem 0.75rem !important;
        border-radius: 8px !important;
    }

    /* Efectos de carga suave */
    .modern-row {
        animation: fadeInUp 0.5s ease forwards;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Responsive */
    @media (max-width: 768px) {
        .modern-header th,
        .modern-row td {
            padding: 1rem 0.5rem !important;
        }

        .avatar-circle {
            width: 35px;
            height: 35px;
            font-size: 0.75rem;
        }

        .contact-info,
        .commissions-info {
            font-size: 0.8rem;
        }

        /* En móvil, ocultar la columna de comisiones en pantallas muy pequeñas */
        @media (max-width: 576px) {
            .modern-header th:nth-child(4),
            .modern-row td:nth-child(4) {
                display: none;
            }
        }
    }

    /* Mejoras adicionales */
    .table-responsive {
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    .card {
        border-radius: 16px !important;
        overflow: hidden;
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

    .header-icon {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        width: 60px;
        height: 60px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1.5rem;
        font-size: 1.5rem;
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
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

    .header-actions .btn {
        background: white;
        color: #667eea;
        border: 2px solid #e2e8f0;
        padding: 0.75rem 1.5rem;
        border-radius: 12px;
        font-weight: 600;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    .header-actions .btn:hover {
        background: #667eea;
        color: white;
        border-color: #667eea;
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.25);
    }

    /* Responsive del header */
    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }

        .header-content {
            flex-direction: column;
            align-items: flex-start;
            text-align: left;
        }

        .header-icon {
            margin-right: 0;
            margin-bottom: 1rem;
        }

        .page-title {
            font-size: 1.5rem;
        }

        .page-subtitle {
            font-size: 0.875rem;
        }

        .header-actions {
            width: 100%;
        }

        .header-actions .btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Función para inicializar eventos de las filas clickeables
    function initializeRowEvents() {
        // Click en fila para ir al show
        document.querySelectorAll('.clickable-row').forEach(row => {
            row.addEventListener('click', function(e) {
                // Redirigir al show del empleado
                window.location.href = this.getAttribute('data-href');
            });
        });
    }

    // Inicializar eventos al cargar la página
    initializeRowEvents();
});
</script>
@endsection
