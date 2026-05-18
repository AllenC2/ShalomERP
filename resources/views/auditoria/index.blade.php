@extends('layouts.app')

@section('template_title')
    Bitácora de Auditoría
@endsection

@section('content')
<div class="bg-light min-vh-100 py-4">
    <div class="container-fluid px-4">
        <!-- Header con estilo premium de ShalomERP -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 pb-3 border-bottom">
            <div>
                <h1 class="display-6 fw-bold mb-1" style="color: #79481D; font-family: 'Outfit', sans-serif;">
                    <i class="bi bi-shield-check me-2"></i>Logs de Auditoría
                </h1>
                <p class="text-muted mb-0">Bitácora centralizada de control de cambios y actividades forenses del sistema ShalomERP.</p>
            </div>
            <div class="mt-3 mt-md-0">
                <a href="{{ route('ajustes.index') }}" class="btn btn-outline-secondary px-4 py-2 rounded-pill fw-bold shadow-sm transition-all hover-translate">
                    <i class="bi bi-arrow-left me-2"></i>Volver a Ajustes
                </a>
            </div>
        </div>

        <div class="row g-4">
            <!-- Columna de Filtros -->
            <div class="col-xl-3 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="card-title mb-0 fw-bold" style="color: #79481D;">
                            <i class="bi bi-funnel me-2"></i>Filtrar Registros
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="{{ route('auditoria.index') }}" id="filter-form">
                            <!-- Filtro por Tabla -->
                            <div class="mb-3">
                                <label for="tabla" class="form-label small fw-bold text-muted text-uppercase">Tabla / Módulo</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-table"></i></span>
                                    <input type="text" class="form-control bg-light border-start-0" id="tabla" name="tabla" 
                                           value="{{ request('tabla') }}" placeholder="Ej: contratos, pagos...">
                                </div>
                            </div>

                            <!-- Filtro por Acción -->
                            <div class="mb-3">
                                <label for="accion" class="form-label small fw-bold text-muted text-uppercase">Acción realizada</label>
                                <select class="form-select bg-light" id="accion" name="accion">
                                    <option value="">Todas las acciones</option>
                                    <option value="INSERT" {{ request('accion') === 'INSERT' ? 'selected' : '' }}>INSERT (Creación)</option>
                                    <option value="UPDATE" {{ request('accion') === 'UPDATE' ? 'selected' : '' }}>UPDATE (Modificación)</option>
                                    <option value="DELETE" {{ request('accion') === 'DELETE' ? 'selected' : '' }}>DELETE (Eliminación)</option>
                                </select>
                            </div>

                            <!-- Filtro por Operador -->
                            <div class="mb-3">
                                <label for="usuario" class="form-label small fw-bold text-muted text-uppercase">Operador (Usuario)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-person"></i></span>
                                    <input type="text" class="form-control bg-light border-start-0" id="usuario" name="usuario" 
                                           value="{{ request('usuario') }}" placeholder="Nombre o correo...">
                                </div>
                            </div>

                            <!-- Filtro por IP -->
                            <div class="mb-3">
                                <label for="ip" class="form-label small fw-bold text-muted text-uppercase">Dirección IP</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-laptop"></i></span>
                                    <input type="text" class="form-control bg-light border-start-0" id="ip" name="ip" 
                                           value="{{ request('ip') }}" placeholder="Ej: 127.0.0.1">
                                </div>
                            </div>

                            <!-- Rango de Fechas -->
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted text-uppercase d-block">Periodo de Tiempo</label>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <input type="date" class="form-control bg-light px-2" id="fecha_desde" name="fecha_desde" 
                                               value="{{ request('fecha_desde') }}" placeholder="Desde">
                                    </div>
                                    <div class="col-6">
                                        <input type="date" class="form-control bg-light px-2" id="fecha_hasta" name="fecha_hasta" 
                                               value="{{ request('fecha_hasta') }}" placeholder="Hasta">
                                    </div>
                                </div>
                            </div>

                            <!-- Botones de Acción de Filtro -->
                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" class="btn text-white fw-bold py-2 rounded-3 shadow-sm" style="background: linear-gradient(135deg, #E1B240 0%, #79481D 100%);">
                                    <i class="bi bi-search me-2"></i>Aplicar Filtros
                                </button>
                                <a href="{{ route('auditoria.index') }}" class="btn btn-light py-2 rounded-3 border fw-semibold">
                                    <i class="bi bi-x-circle me-2"></i>Limpiar Filtros
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Tabla de Logs -->
            <div class="col-xl-9 col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 fw-bold" style="color: #79481D;">
                            <i class="bi bi-list-columns me-2"></i>Historial de Actividades
                        </h5>
                        <span class="badge text-dark bg-light border px-3 py-2 rounded-pill fw-semibold">
                            Total: {{ $logs->total() }} registros
                        </span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="min-width: 800px;">
                            <thead class="table-light">
                                <tr>
                                    <th class="px-4 py-3 text-muted text-uppercase fs-7" style="width: 80px;">ID</th>
                                    <th class="py-3 text-muted text-uppercase fs-7" style="width: 140px;">Acción</th>
                                    <th class="py-3 text-muted text-uppercase fs-7" style="width: 160px;">Módulo / Tabla</th>
                                    <th class="py-3 text-muted text-uppercase fs-7" style="width: 120px;">Registro ID</th>
                                    <th class="py-3 text-muted text-uppercase fs-7">Operador</th>
                                    <th class="py-3 text-muted text-uppercase fs-7" style="width: 140px;">Dirección IP</th>
                                    <th class="py-3 text-muted text-uppercase fs-7" style="width: 180px;">Fecha y Hora</th>
                                    <th class="pe-4 py-3 text-end" style="width: 100px;">Detalles</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($logs as $log)
                                    <tr>
                                        <td class="px-4 fw-bold text-secondary">#{{ $log->id }}</td>
                                        <td>
                                            @if($log->accion === 'INSERT')
                                                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill fw-bold">
                                                    <i class="bi bi-plus-circle-fill me-1"></i>INSERT
                                                </span>
                                            @elseif($log->accion === 'UPDATE')
                                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-2 rounded-pill fw-bold">
                                                    <i class="bi bi-pencil-square me-1"></i>UPDATE
                                                </span>
                                            @elseif($log->accion === 'DELETE')
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 rounded-pill fw-bold">
                                                    <i class="bi bi-trash-fill me-1"></i>DELETE
                                                </span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-2 rounded-pill fw-bold">
                                                    {{ $log->accion }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="text-dark fw-semibold text-uppercase font-monospace bg-light px-2 py-1 rounded border small">
                                                {{ $log->tabla_nombre }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-dark-subtle text-dark border px-2.5 py-1.5 rounded-3 fw-bold small">
                                                ID: {{ $log->registro_id }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle text-white d-flex align-items-center justify-content-center me-2 font-monospace fw-bold" 
                                                     style="width: 32px; height: 32px; font-size: 0.8rem; background: linear-gradient(135deg, #E1B240 0%, #79481D 100%);">
                                                    {{ collect(explode(' ', $log->usuario->name ?? 'S'))->map(fn($w) => mb_substr($w, 0, 1))->take(1)->join('') }}
                                                </div>
                                                <div>
                                                    <div class="fw-semibold text-dark">{{ $log->usuario->name ?? 'Sistema / CLI' }}</div>
                                                    <div class="text-muted small" style="font-size: 0.75rem;">{{ $log->usuario->email ?? 'Automated Action' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-secondary small font-monospace"><i class="bi bi-pc-display me-1"></i>{{ $log->ip_direccion ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            <div class="text-dark fw-semibold small">{{ $log->created_at->translatedFormat('d M Y, h:i A') }}</div>
                                            <div class="text-muted font-monospace" style="font-size: 0.75rem;">{{ $log->created_at->diffForHumans() }}</div>
                                        </td>
                                        <td class="pe-4 text-end">
                                            <button type="button" class="btn btn-outline-info btn-sm rounded-3 px-3 py-1.5 fw-bold btn-show-details" data-id="{{ $log->id }}" data-bs-toggle="modal" data-bs-target="#detalleLogModal">
                                                <i class="bi bi-eye-fill"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5">
                                            <div class="text-muted mb-3"><i class="bi bi-shield-slash fs-1 text-secondary opacity-50"></i></div>
                                            <h6 class="fw-bold text-secondary">No se encontraron registros de auditoría</h6>
                                            <p class="text-muted small">Intenta modificando los filtros de búsqueda.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <!-- Paginador -->
                    @if($logs->hasPages())
                        <div class="card-footer bg-white border-top py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="small text-muted font-monospace">
                                    Mostrando {{ $logs->firstItem() ?? 0 }} a {{ $logs->lastItem() ?? 0 }} de {{ $logs->total() }} registros
                                </div>
                                <div>
                                    {!! $logs->links('pagination::bootstrap-5') !!}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detalle de Log de Auditoría -->
<div class="modal fade" id="detalleLogModal" tabindex="-1" aria-labelledby="detalleLogModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #79481D 0%, #3a220e 100%);">
                <h5 class="modal-title fw-bold" id="detalleLogModalLabel">
                    <i class="bi bi-file-earmark-diff me-2"></i>Detalle de Cambios de Registro
                </h5>
                <button type="button" class="btn-close btn-close-white opacity-75" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-light" id="modal-content-area">
                <!-- El contenido dinámico cargado por AJAX se inyecta aquí -->
                <div class="text-center py-5" id="modal-spinner">
                    <div class="spinner-border text-info" role="status" style="width: 3rem; height: 3rem;">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="text-muted mt-3 font-monospace">Consultando bitácora forense estructurada...</p>
                </div>
            </div>
            <div class="modal-footer bg-white border-top-0 d-flex justify-content-between">
                <span class="text-muted small font-monospace" id="modal-metadata-id">Log ID: N/A</span>
                <button type="button" class="btn btn-secondary px-4 py-2 rounded-pill fw-bold shadow-sm" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Estilos personalizados para animaciones y estética premium */
    .transition-all {
        transition: all 0.25s ease-in-out;
    }
    
    .hover-translate:hover {
        transform: translateY(-2px);
    }
    
    .table-hover tbody tr:hover {
        background-color: rgba(225, 178, 64, 0.04) !important;
        transition: background-color 0.15s ease;
    }
    
    .font-monospace {
        font-family: 'Courier New', Courier, monospace !important;
    }
    
    .fs-7 {
        font-size: 0.75rem !important;
        letter-spacing: 0.5px;
    }
    
    /* Badges de soporte sutiles y estéticos */
    .bg-success-subtle {
        background-color: rgba(25, 135, 84, 0.12) !important;
    }
    .bg-warning-subtle {
        background-color: rgba(255, 193, 7, 0.12) !important;
    }
    .bg-danger-subtle {
        background-color: rgba(220, 53, 69, 0.12) !important;
    }
    .bg-secondary-subtle {
        background-color: rgba(108, 117, 125, 0.12) !important;
    }
    .bg-dark-subtle {
        background-color: rgba(33, 37, 41, 0.08) !important;
    }
    
    /* Inputs modernos */
    .form-control:focus, .form-select:focus {
        border-color: #E1B240;
        box-shadow: 0 0 0 0.25rem rgba(225, 178, 64, 0.2);
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modalSpinner = document.getElementById('modal-spinner');
        const modalBody = document.getElementById('modal-content-area');
        const modalMetadataId = document.getElementById('modal-metadata-id');

        document.querySelectorAll('.btn-show-details').forEach(button => {
            button.addEventListener('click', function () {
                const logId = this.getAttribute('data-id');
                
                // Mostrar Spinner de carga
                modalBody.innerHTML = modalSpinner.outerHTML;
                modalMetadataId.innerText = 'Cargando Log #' + logId + '...';

                // Realizar llamada AJAX
                fetch('{{ url("auditoria") }}/' + logId)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(log => {
                        modalMetadataId.innerText = 'Log ID: #' + log.id + ' | Operador: ' + (log.usuario ? log.usuario.name : 'Sistema');
                        
                        let htmlContent = '';

                        // Tarjeta de Resumen del Log
                        htmlContent += `
                            <div class="card border-0 shadow-sm rounded-3 p-3 mb-4 bg-white">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="small text-muted fw-bold text-uppercase">Información del Evento</div>
                                        <div class="mt-2">
                                            <strong>Acción:</strong> 
                                            <span class="badge ${log.accion === 'INSERT' ? 'bg-success' : (log.accion === 'UPDATE' ? 'bg-warning text-dark' : 'bg-danger')} px-2.5 py-1.5 rounded-pill fw-bold text-uppercase small ms-1">${log.accion}</span>
                                        </div>
                                        <div class="mt-2"><strong>Módulo / Tabla:</strong> <span class="font-monospace text-uppercase small bg-light px-2 py-0.5 border rounded">${log.tabla_nombre}</span></div>
                                        <div class="mt-2"><strong>Registro Afectado:</strong> <span class="badge bg-secondary px-2 py-1">ID: ${log.registro_id}</span></div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="small text-muted fw-bold text-uppercase">Contexto de Seguridad</div>
                                        <div class="mt-2"><strong>Operador:</strong> ${log.usuario ? log.usuario.name : 'Sistema / CLI'}</div>
                                        <div class="mt-2"><strong>Dirección IP:</strong> <span class="font-monospace small text-primary bg-light border px-2 py-0.5 rounded">${log.ip_direccion || 'N/A'}</span></div>
                                        <div class="mt-2"><strong>Marca de Tiempo:</strong> ${new Date(log.created_at).toLocaleString('es-MX')}</div>
                                    </div>
                                </div>
                            </div>
                        `;

                        // Render de los cambios según la acción (INSERT, UPDATE, DELETE)
                        if (log.accion === 'INSERT') {
                            htmlContent += `
                                <div class="alert alert-success border-0 shadow-sm rounded-3 d-flex align-items-center mb-0">
                                    <i class="bi bi-check-circle-fill fs-3 me-3 text-success"></i>
                                    <div>
                                        <h6 class="fw-bold mb-1 text-success">Registro Creado Correctamente</h6>
                                        <p class="mb-0 small text-secondary">Por políticas estrictas de optimización de almacenamiento en disco, los estados iniciales de inserción no se duplican en la base de datos de logs.</p>
                                    </div>
                                </div>
                            `;
                        } else if (log.accion === 'DELETE') {
                            // Render forense completo de registro eliminado
                            let tableRows = '';
                            const estadoAnterior = log.estado_anterior || {};
                            
                            for (const [key, value] of Object.entries(estadoAnterior)) {
                                tableRows += `
                                    <tr>
                                        <td class="fw-bold text-secondary font-monospace" style="width: 30%;">${key}</td>
                                        <td class="text-danger bg-danger-subtle text-decoration-line-through font-monospace small px-2 py-1.5 rounded">${value !== null ? value : '<em class="opacity-50">null</em>'}</td>
                                    </tr>
                                `;
                            }

                            htmlContent += `
                                <div class="card border-0 shadow-sm rounded-3 bg-white mb-3">
                                    <div class="card-header bg-white border-bottom-0 py-3">
                                        <h6 class="card-title mb-0 fw-bold text-danger">
                                            <i class="bi bi-shield-fill-exclamation me-1"></i>Instantánea de Recuperación Forense (Valores eliminados)
                                        </h6>
                                    </div>
                                    <div class="table-responsive px-3 pb-3">
                                        <table class="table table-bordered table-sm align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Atributo</th>
                                                    <th>Último Valor Registrado (Eliminado)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                ${tableRows || '<tr><td colspan="2" class="text-center text-muted py-3">Ningún campo disponible</td></tr>'}
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="alert alert-danger border-0 d-flex align-items-center mb-0" style="background-color: rgba(220, 53, 69, 0.08);">
                                    <i class="bi bi-info-circle-fill fs-4 me-3 text-danger"></i>
                                    <div class="small text-secondary">
                                        Esta instantánea forense contiene todo el estado del registro justo antes de su remoción física. <strong>Campos de texto libre o archivos masivos configurados han sido excluidos para optimizar almacenamiento.</strong>
                                    </div>
                                </div>
                            `;
                        } else if (log.accion === 'UPDATE') {
                            // Render side-by-side de campos modificados
                            let tableRows = '';
                            const estadoAnterior = log.estado_anterior || {};
                            const estadoNuevo = log.estado_nuevo || {};
                            
                            // Iteramos sobre las claves modificadas
                            for (const [key, newValue] of Object.entries(estadoNuevo)) {
                                const oldValue = estadoAnterior[key];
                                tableRows += `
                                    <tr>
                                        <td class="fw-bold text-secondary font-monospace" style="width: 25%;">${key}</td>
                                        <td class="bg-danger-subtle text-danger text-decoration-line-through font-monospace small px-2 py-1.5 rounded-pill" style="width: 37.5%;">
                                            ${oldValue !== null && oldValue !== undefined ? oldValue : '<em class="opacity-50">null</em>'}
                                        </td>
                                        <td class="bg-success-subtle text-success fw-semibold font-monospace small px-2 py-1.5 rounded-pill" style="width: 37.5%;">
                                            ${newValue !== null && newValue !== undefined ? newValue : '<em class="opacity-50">null</em>'}
                                        </td>
                                    </tr>
                                `;
                            }

                            htmlContent += `
                                <div class="card border-0 shadow-sm rounded-3 bg-white">
                                    <div class="card-header bg-white border-bottom py-3">
                                        <h6 class="card-title mb-0 fw-bold" style="color: #79481D;">
                                            <i class="bi bi-arrow-left-right me-1"></i>Comparación Diferencial de Atributos Modificados
                                        </h6>
                                    </div>
                                    <div class="table-responsive p-3">
                                        <table class="table table-bordered table-sm align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Campo</th>
                                                    <th>Estado Anterior (Antes)</th>
                                                    <th>Estado Nuevo (Después)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                ${tableRows || '<tr><td colspan="3" class="text-center text-muted py-3">Ningún cambio de atributos detectado en este log.</td></tr>'}
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            `;
                        }

                        modalBody.innerHTML = htmlContent;
                    })
                    .catch(error => {
                        console.error('Error al consultar logs:', error);
                        modalBody.innerHTML = `
                            <div class="alert alert-danger border-0 d-flex align-items-center">
                                <i class="bi bi-x-circle-fill fs-3 me-3 text-danger"></i>
                                <div>
                                    <h6 class="fw-bold mb-1 text-danger">Error de Conexión</h6>
                                    <p class="mb-0 small text-secondary">No se pudo recuperar el registro forense. Por favor, intente nuevamente.</p>
                                </div>
                            </div>
                        `;
                    });
            });
        });
    });
</script>
@endpush
@endsection
