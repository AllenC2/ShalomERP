@extends('layouts.app')

@section('template_title')
    Comisiones
@endsection

@section('content')
    <div class="container py-4" style="max-width: 1600px;">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- Header moderno -->
                <div class="page-header">
                    <div class="header-content" style="padding-left: 1.5rem;">
                        <div class="header-icon">
                            <i class="bi bi-percent"></i>
                        </div>
                        <div class="header-text">
                            <h1 class="page-title">{{ __('Comisiones') }}</h1>
                            <p class="page-subtitle">Gestione y consulte la información de las comisiones</p>
                        </div>
                    </div>
                </div>
                
                 <!-- Advertencia sobre modificación de comisiones -->
                <div class="alert alert-warning border-0 mb-4" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-exclamation-triangle-fill text-warning me-3 fs-5"></i>
                        <div>
                            <h6 class="alert-heading mb-1">Advertencia Importante</h6>
                            <p class="mb-0 small">
                                <strong>Tenga precaución al modificar registros de comisiones.</strong> 
                                Los cambios pueden afectar los cálculos financieros, reportes contables y pagos del personal. 
                                Asegúrese de verificar la información antes de realizar cualquier modificación desde esta pagina.
                            </p>
                        </div>
                    </div>
                </div>
                

                <!-- Tarjeta principal -->
                <div class="">

                    @if ($message = Session::get('success'))
                        <div class="alert alert-success m-4">
                            <p class="mb-0">{{ $message }}</p>
                        </div>
                    @endif

                    <div class="card-body p-0">
                        <div class="table-responsive" id="tabla-comisiones">
                            <table class="table table-hover align-middle mb-0 modern-table">
                                <thead class="modern-header">
                                    <tr>
                                        <th scope="col">Contrato</th>
                                        <th scope="col">Tipo</th>
                                        <th scope="col">Empleado</th>
                                        <th scope="col">Observaciones</th>
                                        <th scope="col">Monto</th>
                                        <th scope="col">Estado</th>
                                        <th scope="col" class="text-center pe-4">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($comisiones as $comisione)
                                        <tr class="modern-row clickable-row" data-href="{{ route('comisiones.show', $comisione->id) }}">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <div class="fw-semibold text-dark">{{ optional(optional($comisione->contrato)->paquete)->nombre ?? 'N/A' }}</div>
                                                        <small class="text-muted">Contrato #{{ $comisione->contrato_id }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="modern-badge bg-info text-white">
                                                    {{ $comisione->tipo_comision }}
                                                </span>
                                            </td>
                                            <td>
                                                <div>
                                                    <div class="fw-semibold text-dark">{{ $comisione->empleado->nombre }} {{ $comisione->empleado->apellido }}</div>
                                                    <small class="text-muted">ID: {{ $comisione->empleado->id }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="info-section">
                                                    @if($comisione->observaciones)
                                                        <span class="text-truncate d-inline-block" style="max-width: 250px;" 
                                                              data-bs-toggle="tooltip" title="{{ $comisione->observaciones }}">
                                                            {{ $comisione->observaciones }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted fst-italic">Sin observaciones</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <div class="contact-info">
                                                    <div class="mb-1">
                                                        <i class="bi bi-currency-dollar me-2"></i>
                                                        <span class="fw-bold text-success">${{ number_format($comisione->monto, 2) }}</span>
                                                    </div>
                                                    <div>
                                                        <i class="bi bi-calendar me-2"></i>
                                                        <span class="text-dark">{{ \Carbon\Carbon::parse($comisione->fecha_comision)->format('d/m/Y') }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="modern-badge
                                                    @if($comisione->estado == 'Entregada' || $comisione->estado == 'Pagada') bg-success text-white
                                                    @else bg-warning text-dark
                                                    @endif">
                                                    @if($comisione->estado == 'Entregada' || $comisione->estado == 'Pagada')
                                                        Pagada
                                                    @else
                                                        Pendiente
                                                    @endif
                                                </span>
                                            </td>
                                            <td class="text-center pe-4" onclick="event.stopPropagation();">
                                                <div class="btn-group" role="group">
                                                    <a class="btn btn-outline-success btn-sm action-btn" href="{{ route('comisiones.edit', $comisione->id) }}" title="Editar">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-outline-danger btn-sm action-btn" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#confirmDeleteModal"
                                                            data-comision-id="{{ $comisione->id }}"
                                                            data-comision-contrato="{{ $comisione->contrato_id }}"
                                                            title="Eliminar">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-5">
                                                <div class="d-flex flex-column align-items-center">
                                                    <i class="bi bi-inbox fs-1 text-muted mb-3"></i>
                                                    <h5 class="text-muted">No hay comisiones registradas</h5>
                                                    <p class="text-muted mb-3">Comienza creando una nueva comisión</p>
                                                    <a href="{{ route('comisiones.create') }}" class="btn btn-primary">
                                                        <i class="bi bi-plus-circle me-1"></i>Crear Primera Comisión
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                            @if($comisiones->isEmpty())
                                <div class="text-center text-muted py-4">
                                    <i class="bi bi-info-circle"></i> No hay comisiones registradas.
                                </div>
                            @endif
                        </div>
                        
                        <!-- Paginación moderna -->
                        <div class="d-flex justify-content-between align-items-center mt-4 px-3">
                            <div class="pagination-info">
                                <span class="text-muted">
                                    Mostrando {{ $comisiones->firstItem() ?? 0 }} - {{ $comisiones->lastItem() ?? 0 }} de {{ $comisiones->total() }} resultados
                                </span>
                            </div>
                            <div class="pagination-wrapper">
                                {!! $comisiones->withQueryString()->links('custom.pagination') !!}
                            </div>
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
                    <p class="mb-3">¿Está seguro de que desea eliminar la comisión del contrato:</p>
                    <div class="text-center">
                        <h6 class="fw-bold text-dark" id="comisionContrato"></h6>
                    </div>
                    <p class="text-muted small mt-3">
                        Al eliminar esta comisión, también se eliminarán todos sus datos relacionados.
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
                            <i class="bi bi-trash me-1"></i>Eliminar Comisión
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery CDN -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>    <style>
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
            background: linear-gradient(135deg, #E1B240 0%, #79481D 100%);
            color: white;
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1.5rem;
            font-size: 1.5rem;
            box-shadow: 0 10px 30px rgba(225, 178, 64, 0.3);
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
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 0.875rem;
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
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

        /* Sección de información adicional */
        .info-section {
            font-size: 0.875rem;
        }

        .info-section i {
            width: 16px;
            font-size: 0.8rem;
            color: #79481D !important;
        }

        /* Badge moderno */
        .modern-badge {
            padding: 0.5rem 0.75rem !important;
            border-radius: 8px !important;
            font-weight: 600 !important;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.75rem !important;
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
            .info-section {
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

        /* Estilos para paginación moderna */
        .pagination-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .pagination-info {
            font-size: 0.875rem;
            color: #6c757d;
            font-weight: 500;
        }

        /* Estilos personalizados para los enlaces de paginación */
        .pagination {
            margin: 0;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            background: white;
        }

        .pagination .page-item {
            margin: 0;
        }

        .pagination .page-item .page-link {
            border: none;
            padding: 0.75rem 1rem;
            color: #6c757d;
            background: transparent;
            transition: all 0.3s ease;
            font-weight: 500;
            position: relative;
            z-index: 1;
        }

        .pagination .page-item:first-child .page-link {
            border-top-left-radius: 12px;
            border-bottom-left-radius: 12px;
        }

        .pagination .page-item:last-child .page-link {
            border-top-right-radius: 12px;
            border-bottom-right-radius: 12px;
        }

        .pagination .page-item .page-link:hover {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
            z-index: 2;
        }

        .pagination .page-item.active .page-link {
            background: linear-gradient(135deg, #E1B240 0%, #79481D 100%);
            border-color: #E1B240;
            color: white;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(225, 178, 64, 0.4);
        }

        .pagination .page-item.disabled .page-link {
            color: #adb5bd;
            background: #f8f9fa;
            cursor: not-allowed;
        }

        .pagination .page-item.disabled .page-link:hover {
            background: #f8f9fa;
            transform: none;
            box-shadow: none;
        }

        /* Indicadores de navegación */
        .pagination .page-item .page-link[aria-label*="Anterior"] {
            font-weight: bold;
        }

        .pagination .page-item .page-link[aria-label*="Siguiente"] {
            font-weight: bold;
        }

        /* Efecto especial para números de página */
        .pagination .page-item:not(.active):not(.disabled) .page-link:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: -1;
        }

        .pagination .page-item:not(.active):not(.disabled) .page-link:hover:before {
            opacity: 1;
        }

        /* Responsive para paginación */
        @media (max-width: 576px) {
            .pagination-info {
                font-size: 0.75rem;
                margin-bottom: 1rem;
            }
            
            .d-flex.justify-content-between.align-items-center {
                flex-direction: column;
                gap: 1rem;
            }

            .pagination .page-item .page-link {
                padding: 0.5rem 0.75rem;
                font-size: 0.875rem;
            }
        }

        /* Estilos específicos para comisiones */
        .bg-gradient-primary {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        }
        
        .avatar-sm {
            width: 32px;
            height: 32px;
            font-size: 14px;
        }
        
        .card {
            transition: all 0.3s ease;
        }
        
        .table tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.05);
        }
        
        .dropdown-toggle::after {
            display: none;
        }
    </style>

    <script>
    $(document).ready(function() {
        // Funcionalidad de búsqueda AJAX
        $('#search, #estado, #tipo, #fecha_desde, #fecha_hasta').on('change keyup', function() {
            var formData = $('form').serialize();
            $.ajax({
                url: "{{ route('comisiones.index') }}",
                type: 'GET',
                data: formData,
                success: function(data) {
                    // Extrae la tabla y paginación de la respuesta
                    var newHtml = $(data);
                    var tableHtml = newHtml.find('#tabla-comisiones').html();
                    var paginationHtml = newHtml.find('.pagination-wrapper').parent().html();
                    
                    $('#tabla-comisiones').html(tableHtml);
                    $('.pagination-wrapper').parent().html(paginationHtml);
                    
                    // Reinicializar eventos después de la actualización AJAX
                    initializeRowEvents();
                    initializePaginationEvents();
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
                var comisionId = $(this).data('comision-id');
                var comisionContrato = $(this).data('comision-contrato');
                
                $('#comisionContrato').text('Contrato #' + comisionContrato);
                $('#deleteForm').attr('action', '{{ route("comisiones.destroy", ":id") }}'.replace(':id', comisionId));
            });
        }

        // Función para inicializar eventos de paginación
        function initializePaginationEvents() {
            $('.pagination a').off('click').on('click', function(e) {
                e.preventDefault();
                var url = $(this).attr('href');
                var formData = $('form').serialize();
                
                // Combinar parámetros de búsqueda con URL de paginación
                var separator = url.includes('?') ? '&' : '?';
                var fullUrl = url + separator + formData;
                
                $.ajax({
                    url: fullUrl,
                    type: 'GET',
                    success: function(data) {
                        var newHtml = $(data);
                        var tableHtml = newHtml.find('#tabla-comisiones').html();
                        var paginationHtml = newHtml.find('.pagination-wrapper').parent().html();
                        
                        $('#tabla-comisiones').html(tableHtml);
                        $('.pagination-wrapper').parent().html(paginationHtml);
                        
                        // Reinicializar eventos
                        initializeRowEvents();
                        initializePaginationEvents();
                        
                        // Scroll suave al top de la tabla
                        $('html, body').animate({
                            scrollTop: $('.table-responsive').offset().top - 100
                        }, 300);
                    }
                });
            });
        }

        // Inicializar eventos al cargar la página
        initializeRowEvents();
        initializePaginationEvents();

        // Inicializar tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
    </script>
@endsection
