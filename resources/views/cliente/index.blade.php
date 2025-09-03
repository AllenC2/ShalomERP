@extends('layouts.app')

@section('template_title')
    Clientes
@endsection

@section('content')
    <div class="container py-4" style="max-width: 1600px;">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- Header moderno -->
                <div class="page-header">
                    <div class="header-content">
                        <div class="header-icon">
                            <i class="bi bi-people"></i>
                        </div>
                        <div class="header-text">
                            <h1 class="page-title">{{ __('Clientes') }}</h1>
                            <p class="page-subtitle">Gestione y consulte la información de sus clientes</p>
                        </div>
                    </div>
                    <div class="header-actions">
                        <a href="{{ route('clientes.create') }}" class="btn border-secondary">
                            <i class="bi bi-plus-lg me-1"></i>
                            {{ __('Nuevo Cliente') }}
                        </a>
                    </div>
                </div>
                <div class="">
                    <!-- Buscador -->
                    <div class="pb-3">
                        <div class="col-md-6">
                            <form id="form-busqueda" method="GET" action="{{ route('clientes.index') }}" class="row g-2 align-items-center">
                                <div class="col-md-8">
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0">
                                            <i class="bi bi-search"></i>
                                        </span>
                                        <input type="text" id="input-busqueda" name="busqueda" value="{{ request('busqueda') }}" class="bg-white form-control border-start-0" placeholder="Buscar">
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    @if ($message = Session::get('success'))
                        <div class="alert alert-success m-4">
                            <p class="mb-0">{{ $message }}</p>
                        </div>
                    @endif

                    <div class="card-body p-0">
                        <div class="table-responsive" id="tabla-clientes">
                            <table class="table table-hover align-middle mb-0 modern-table">
                                <thead class="modern-header">
                                    <tr>
                                        <th scope="col" class="ps-4">#</th>
                                        <th scope="col">Cliente</th>
                                        <th scope="col">Contacto</th>
                                        <th scope="col">Contratos activos</th>
                                        <th scope="col" class="text-center pe-4">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($clientes as $cliente)
                                        <tr class="modern-row clickable-row" data-href="{{ route('clientes.show', $cliente->id) }}">
                                            <td class="ps-4">
                                                <span class="badge bg-light text-dark fw-normal">{{ ++$i }}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-circle me-3 d-flex align-items-center justify-content-center" style="min-width: 45px; min-height: 45px; width: 45px; height: 45px; background: linear-gradient(135deg, #E1B240 0%, #79481D 100%); border-radius: 50%;">
                                                        {{ strtoupper(substr($cliente->nombre, 0, 1) . substr($cliente->apellido, 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <div class="fw-semibold text-dark">{{ $cliente->nombre }} {{ $cliente->apellido }}</div>
                                                        <small class="text-muted">Cliente desde {{ \Carbon\Carbon::parse($cliente->created_at)->translatedFormat('d \d\e F \d\e Y') }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="contact-info">
                                                    <div class="mb-1">
                                                        <i class="bi bi-envelope me-2"></i>
                                                        <span class="text-dark">{{ $cliente->email }}</span>
                                                    </div>
                                                    <div>
                                                        <i class="bi bi-telephone me-2"></i>
                                                        <span class="text-dark">{{ $cliente->telefono }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="info-section">
                                                    <div class="mb-2">
                                                        <div class="d-flex align-items-center mb-1">
                                                            <i class="bi bi-file-earmark-text me-2"></i>
                                                            <span class="fw-semibold text-dark">{{ $cliente->contratos_activos_count ?? 0 }}</span>
                                                            <span class="text-muted ms-1">{{ ($cliente->contratos_activos_count ?? 0) == 1 ? 'contrato activo' : 'contratos activos' }}</span>
                                                        </div>
                                                    </div>
                                                   
                                                </div>
                                            </td>
                                            <td class="text-center pe-4" onclick="event.stopPropagation();">
                                                <div class="btn-group" role="group">
                                                    <a class="btn btn-outline-success btn-sm action-btn" href="{{ route('clientes.edit', $cliente->id) }}" title="Editar">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-outline-danger btn-sm action-btn" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#confirmDeleteModal"
                                                            data-cliente-id="{{ $cliente->id }}"
                                                            data-cliente-nombre="{{ $cliente->nombre }} {{ $cliente->apellido }}"
                                                            title="Eliminar">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-center mt-3">
                            {!! $clientes->withQueryString()->links() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmación de Eliminación -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-danger text-white border-0">
                    <h5 class="modal-title" id="confirmDeleteModalLabel">
                        <i class="bi bi-exclamation-triangle me-2"></i>Confirmar Eliminación
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="text-center mb-3">
                        <div class="alert alert-warning border-0" role="alert">
                            <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>
                            <strong>¡Atención!</strong> Esta acción no se puede deshacer.
                        </div>
                    </div>
                    <p class="mb-3">¿Está seguro de que desea eliminar al cliente:</p>
                    <div class="text-center">
                        <h6 class="fw-bold text-dark" id="clienteNombre"></h6>
                    </div>
                    <p class="text-muted small mt-3">
                        Al eliminar este cliente, también se eliminarán todos sus datos relacionados.
                    </p>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i>Cancelar
                    </button>
                    <form id="deleteForm" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-1"></i>Eliminar Cliente
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery CDN -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 0.875rem;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
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

        /* Información de dirección */
        .address-info {
            font-size: 0.875rem;
            display: flex;
            align-items: flex-start;
        }

        .address-info i {
            margin-top: 2px;
            font-size: 0.8rem;
            color: #79481D !important;
        }

        /* Sección de información adicional */
        .info-section {
            font-size: 0.875rem;
        }

        .info-section i {
            width: 16px;
            font-size: 0.8rem;
            color: #79481D !important;
        }

        /* Filas clickeables */
        .clickable-row {
            cursor: pointer;
        }

        .clickable-row:hover {
            background: linear-gradient(135deg, #f8f9ff 0%, #f0f4ff 100%) !important;
        }

        /* Botones de acción modernos */
        .action-btn {
            border-radius: 8px !important;
            margin: 0 2px !important;
            transition: all 0.3s ease !important;
            border-width: 1.5px !important;
            width: 35px;
            height: 35px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .action-btn:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
        }

        .btn-outline-primary.action-btn:hover {
            background: #667eea !important;
            border-color: #667eea !important;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4) !important;
        }

        .btn-outline-success.action-btn:hover {
            background: #28a745 !important;
            border-color: #28a745 !important;
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.4) !important;
        }

        .btn-outline-danger.action-btn:hover {
            background: #dc3545 !important;
            border-color: #dc3545 !important;
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.4) !important;
        }

        /* Badge moderno */
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
            .address-info {
                font-size: 0.8rem;
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
    </style>

    <script>
    $(document).ready(function() {
        // Funcionalidad de búsqueda AJAX
        $('#input-busqueda').on('keyup', function() {
            var busqueda = $(this).val();
            $.ajax({
                url: "{{ route('clientes.index') }}",
                type: 'GET',
                data: { busqueda: busqueda },
                success: function(data) {
                    // Extrae solo la tabla de la respuesta
                    var html = $(data).find('#tabla-clientes').html();
                    $('#tabla-clientes').html(html);
                    
                    // Reinicializar eventos después de la actualización AJAX
                    initializeRowEvents();
                }
            });
        });

        // Función para inicializar eventos de las filas
        function initializeRowEvents() {
            // Click en fila para ir al show
            $('.clickable-row').off('click').on('click', function(e) {
                // No redirigir si se hace click en los botones de acciones
                if (!$(e.target).closest('td[onclick*="stopPropagation"]').length) {
                    window.location.href = $(this).data('href');
                }
            });

            // Configurar modal de eliminación
            $('[data-bs-toggle="modal"][data-bs-target="#confirmDeleteModal"]').off('click').on('click', function() {
                var clienteId = $(this).data('cliente-id');
                var clienteNombre = $(this).data('cliente-nombre');
                
                $('#clienteNombre').text(clienteNombre);
                $('#deleteForm').attr('action', '{{ route("clientes.destroy", ":id") }}'.replace(':id', clienteId));
            });
        }

        // Inicializar eventos al cargar la página
        initializeRowEvents();
    });
    </script>
@endsection
