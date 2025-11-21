@extends('layouts.app')

@section('template_title')
    {{ $comisione->name ?? __('Show') . " " . __('Comisione') }}
@endsection

@section('content')
<style>
@media print {
    /* Evitar saltos de página en elementos específicos */
    .bg-light,
    .border,
    .shadow-lg,
    .p-4,
    .pb-2 {
        page-break-inside: avoid !important;
        break-inside: avoid !important;
    }
    
    /* Controlar saltos de página entre secciones */
    .bg-light.text-dark.p-4.pb-2 {
        page-break-after: avoid !important;
        break-after: avoid !important;
    }
    
    /* Mantener elementos juntos */
    .row {
        page-break-inside: avoid !important;
        break-inside: avoid !important;
    }
    
    /* Evitar saltos de página innecesarios */
    * {
        -webkit-print-color-adjust: exact !important;
        color-adjust: exact !important;
    }
    
    /* Asegurar que el contenido principal no se rompa */
    .border.shadow-lg {
        box-shadow: none !important;
        page-break-inside: avoid !important;
    }
    
    /* Optimizar espaciado para impresión */
    .p-4 {
        padding: 1rem !important;
    }
    
    .pb-2 {
        padding-bottom: 0.5rem !important;
    }
    
    /* Evitar salto de página específico después del header */
    .bg-light.text-dark.p-4.pb-2 + .p-4 {
        page-break-before: avoid !important;
        break-before: avoid !important;
    }
    
    /* Evitar páginas en blanco al final */
    section:last-child,
    .text-center.p-4.d-print-none:last-child,
    .modal:last-child {
        page-break-after: avoid !important;
        break-after: avoid !important;
    }
    
    /* Limitar altura del contenido para evitar desbordamiento */
    .col-md-8 {
        page-break-after: avoid !important;
    }
    
    /* Ocultar completamente elementos que no son necesarios en impresión */
    .modal,
    script,
    .d-print-none,
    .btn {
        display: none !important;
        height: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    
    /* Optimizar márgenes para impresión */
    body, html {
        margin: 0 !important;
        padding: 0 !important;
    }
    
    section {
        margin: 0 !important;
        padding: 1rem !important;
    }
    
    .py-4 {
        padding-top: 0 !important;
        padding-bottom: 0 !important;
    }
    
    /* Evitar saltos de página al final del documento */
    .border.shadow-lg.p-0:last-child {
        page-break-after: avoid !important;
        margin-bottom: 0 !important;
    }
    
    /* Ajustar altura de elementos altos que pueden causar páginas extras */
    .mt-4, .mb-3, .py-4 {
        margin: 0.5rem 0 !important;
        padding: 0.5rem 0 !important;
    }
    
    /* Control estricto del final del documento */
    @page {
        margin: 1cm;
        size: letter;
    }
    
    /* Eliminar cualquier contenido después del recibo principal */
    .modal,
    .modal + *,
    script,
    script + * {
        display: none !important;
        position: absolute !important;
        left: -9999px !important;
        top: -9999px !important;
        width: 0 !important;
        height: 0 !important;
        overflow: hidden !important;
    }
    
    /* Asegurar que el documento termine limpiamente */
    section:last-of-type {
        page-break-after: auto !important;
    }
    
    /* Eliminar espacios al final */
    *:last-child {
        margin-bottom: 0 !important;
        padding-bottom: 0 !important;
    }
}
</style>

<style>
@media print {
    .shadow-lg {
        box-shadow: none !important;
    }
    .border.shadow-lg {
        box-shadow: none !important;
        border: 1px solid #dee2e6 !important;
    }
    .col-md-8 {
        max-width: 100% !important;
        width: 100% !important;
    }
    section.d-flex {
        margin: 0 !important;
        padding: 0 !important;
    }
    .py-4 {
        padding-top: 0 !important;
        padding-bottom: 0 !important;
    }
}
</style>

<section class="d-flex justify-content-center align-items-center py-4">
    <div class="col-md-8" style="max-width: 800px;">
        <a href="{{ url()->previous() }}" class="modern-link mb-3 d-inline-block d-print-none">
            <i class="bi bi-arrow-left me-1"></i>
            {{ __('Regresar') }}
        </a>
        <div class="border shadow-lg p-0" style="background: #fff; margin: auto; font-family: 'Arial', sans-serif;">
            
            <!-- Header del Recibo -->
            <div class="bg-light text-dark p-4 pb-2">
                <div class="row">
                    <div class="col-sm-8">
                        <div class="d-flex flex-column h-100" style="height: 100%;">
                            <div class="d-flex align-items-center" style="margin-bottom: auto;">
                                <img src="{{ asset('shalom_logo.svg') }}" alt="Shalom Logo" style="height: 50px; margin-right: 15px;">
                            </div>
                            <div class="mb-2 mt-auto">
                                <h2 class="fw-bold mb-0" style="font-size: 1.5rem; color: #2d3748;">
                                    Recibo de Comisión #{{ str_pad($comisione->id ?? 0, 6, '0', STR_PAD_LEFT) }}
                                </h2>
                                <div class="text-muted" style="font-size: .8rem;">
                                    {{ $comisione->fecha_comision ? \Carbon\Carbon::parse($comisione->fecha_comision)->translatedFormat('d \d\e F \d\e Y') : 'Fecha no disponible' }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4 text-end">
                        <div class="small">
                            <strong>{{ infoEmpresa('razon_social') }}</strong><br>
                            {!! formatearDireccionEmpresa() !!}<br>
                            RFC: {{ infoEmpresa('rfc') }}<br>
                            @if(infoEmpresa('telefono'))
                                Tel: {{ infoEmpresa('telefono') }}<br>
                            @endif
                            @if(infoEmpresa('email'))
                                Email: {{ infoEmpresa('email') }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Layout reorganizado -->
            <div class="p-4">
                <div class="row mb-4">
                    <!-- Columna lateral: Datos del empleado y contrato -->
                    <div class="col-sm-4">
                        <h6 class="text-muted text-uppercase small fw-bold mb-3">
                            <i class="bi bi-person-badge me-2"></i>Información del Empleado
                        </h6>
                        @if($comisione->empleado)
                            <div class="mb-3 p-0">
                                <div class="card border rounded h-100" style="background: none; border-color: #e3e3e3;">
                                    <div class="card-body p-4">
                                        <!-- Header del empleado -->
                                        @php
                                            $empleado = $comisione->empleado;
                                        @endphp
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="avatar-circle me-3 d-flex align-items-center justify-content-center" style="min-width: 45px; min-height: 45px; width: 45px; height: 45px; background: linear-gradient(135deg, #E1B240 0%, #79481D 100%); border-radius: 50%; border: 2px solid #dee2e6;">
                                                {{ strtoupper(substr($empleado->nombre, 0, 1) . substr($empleado->apellido, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="fw-semibold text-dark">{{ $empleado->nombre }} {{ $empleado->apellido }}</div>
                                            </div>
                                        </div>

                                        <!-- Información de contacto del empleado -->
                                        <div class="row g-3 mb-3">
                                            @if($empleado->email)
                                            <div class="col-md-12">
                                                <div class="d-flex align-items-center gap-2">
                                                    <i class="bi bi-envelope text-primary"></i>
                                                    <span class="text-muted small">{{ $empleado->email }}</span>
                                                </div>
                                            </div>
                                            @endif

                                            @if($empleado->telefono)
                                            <div class="col-md-12">
                                                <div class="d-flex align-items-center gap-2">
                                                    <i class="bi bi-telephone text-success"></i>
                                                    <span class="text-muted small">{{ $empleado->telefono }}</span>
                                                </div>
                                            </div>
                                            @endif

                                            @if($empleado->puesto)
                                            <div class="col-md-12">
                                                <div class="d-flex align-items-center gap-2">
                                                    <i class="bi bi-briefcase text-info"></i>
                                                    <span class="text-muted small">{{ $empleado->puesto }}</span>
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="text-muted">Información del empleado no disponible</div>
                        @endif

                        <!-- Documento Adjunto -->
                        @if($comisione->documento && $comisione->documento !== 'No')
                        <div id="documento-section" class="mt-4 d-print-none">
                            <h6 class="text-muted text-uppercase small fw-bold mb-3">
                                <i class="bi bi-paperclip me-2"></i>Documento Adjunto
                            </h6>
                                <div class="card border rounded" style="background: none; border-color: #e3e3e3;">
                                <div class="card-body p-3 text-center" style="cursor: pointer;" onclick="abrirSelectorDocumento({{ $comisione->id }})" title="Haz clic para cambiar el documento">
                                    @php
                                        $extension = strtolower(pathinfo($comisione->documento, PATHINFO_EXTENSION));
                                        $isPDF = $extension === 'pdf';
                                        $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp']);
                                        // Usar Storage::url() para archivos en el disco public
                                        $documentoUrl = \Illuminate\Support\Facades\Storage::url($comisione->documento);
                                        $fileName = basename($comisione->documento);
                                    @endphp
                                    
                                    <div class="position-relative">                                    <div class="row mb-3">
                                        <!-- Columna de miniatura -->
                                        <div class="col-4 d-flex justify-content-center">
                                            @if($isImage)
                                                <!-- Miniatura de imagen -->
                                                <div class="document-thumbnail" style="width: 80px; height: 80px; border-radius: 8px; overflow: hidden; border: 2px solid #dee2e6; cursor: pointer;" onclick="event.stopPropagation(); openDocumentModal('{{ $documentoUrl }}', '{{ $fileName }}', 'image')">
                                                    <img src="{{ $documentoUrl }}" alt="Documento adjunto" style="width: 100%; height: 100%; object-fit: cover;">
                                                </div>
                                            @elseif($isPDF)
                                                <!-- Previsualización de PDF -->
                                                <div class="document-thumbnail" style="width: 80px; height: 80px; border-radius: 8px; overflow: hidden; border: 2px solid #dee2e6; cursor: pointer; position: relative;" onclick="event.stopPropagation(); openDocumentModal('{{ $documentoUrl }}', '{{ $fileName }}', 'pdf')">
                                                    <iframe src="{{ $documentoUrl }}#toolbar=0&navpanes=0&scrollbar=0" style="width: 200%; height: 200%; transform: scale(0.5); transform-origin: 0 0; pointer-events: none; border: none;"></iframe>
                                                    <!-- Overlay para indicar que es PDF -->
                                                    <div class="position-absolute top-0 end-0 bg-danger text-white px-1 py-0" style="font-size: 0.6rem; border-radius: 0 6px 0 4px;">
                                                        PDF
                                                    </div>
                                                </div>
                                            @else
                                                <!-- Icono genérico de archivo -->
                                                <div class="document-thumbnail d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; border-radius: 8px; background: linear-gradient(135deg, #6c757d 0%, #495057 100%); color: white; cursor: pointer; border: 2px solid #dee2e6;" onclick="event.stopPropagation(); openDocumentModal('{{ $documentoUrl }}', '{{ $fileName }}', 'file')">
                                                    <i class="bi bi-file-earmark-fill" style="font-size: 32px;"></i>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Columna de detalles -->
                                        <div class="col-8 text-start">
                                            <!-- Nombre del archivo -->
                                            <div class="fw-semibold text-dark mb-2">{{ $fileName }}</div>

                                            <!-- Formato y tamaño -->
                                            <div class="text-muted mb-2" style="font-size: 0.75rem;">
                                                <i class="bi bi-file-earmark me-1"></i>
                                                {{ strtoupper($extension) }}
                                                @if(\Illuminate\Support\Facades\Storage::disk('public')->exists($comisione->documento))
                                                    • {{ formatBytes(\Illuminate\Support\Facades\Storage::disk('public')->size($comisione->documento)) }}
                                                @endif
                                            </div>

                                            <!-- Fecha -->
                                            <div class="text-muted mb-3" style="font-size: 0.7rem;">
                                                Adjuntado el {{ $comisione->created_at->format('d/m/Y H:i') }}
                                            </div>
                                        </div>
                                    </div>
                                    </div>
                                    
                                    <!-- Botones de acción en horizontal -->
                                    <div class="d-flex justify-content-between align-items-center">
                                        <button class="btn btn-outline-primary btn-sm flex-grow-1 mx-1 d-print-none" onclick="event.stopPropagation(); openDocumentModal('{{ $documentoUrl }}', '{{ $fileName }}', '{{ $isImage ? 'image' : ($isPDF ? 'pdf' : 'file') }}')" title="Ver documento">
                                            <i class="bi bi-eye me-1"></i> Ver
                                        </button>
                                        <a href="{{ $documentoUrl }}" class="btn btn-outline-secondary btn-sm flex-grow-1 mx-1 d-print-none" download="{{ $fileName }}" title="Descargar" onclick="event.stopPropagation();">
                                            <i class="bi bi-download me-1"></i> Descargar
                                        </a>
                                        <button class="btn btn-outline-danger btn-sm flex-grow-1 mx-1 d-print-none" onclick="event.stopPropagation(); eliminarDocumento({{ $comisione->id }})" title="Eliminar documento">
                                            <i class="bi bi-trash me-1"></i> Eliminar
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Input file oculto para seleccionar nuevo documento -->
                            <input type="file" id="documentoInput" accept=".pdf,.jpg,.jpeg,.png,.gif,.bmp,.webp" style="display: none;" onchange="subirNuevoDocumento({{ $comisione->id }}, this)">
                        </div>
                        @else
                            <!-- Mensaje cuando no hay documento -->
                            <div class="mt-4 d-print-none">
                                <h6 class="text-muted text-uppercase small fw-bold mb-3">
                                    <i class="bi bi-paperclip me-2"></i>Documento Adjunto
                                </h6>
                                <div class="card border rounded" style="background: none; border-color: #e3e3e3;">
                                    <div class="card-body p-3 text-center" style="cursor: pointer;" onclick="abrirSelectorDocumentoNuevo({{ $comisione->id }})" title="Haz clic para adjuntar un documento">
                                        <div class="position-relative">
                                            
                                            <div class="text-muted">
                                                <i class="bi bi-file-earmark-plus" style="font-size: 3rem; color: #6c757d;"></i>
                                                <p class="mb-2 mt-3 fw-semibold">No hay documento adjunto</p>
                                                <p class="mb-0 small text-muted">Haz clic aquí para subir un documento</p>
                                                <small class="text-muted d-block mt-2">
                                                    Formatos: PDF, JPG, PNG, GIF, BMP, WebP (máx. 10MB)
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Input file oculto para seleccionar documento nuevo -->
                                <input type="file" id="documentoInputNuevo" accept=".pdf,.jpg,.jpeg,.png,.gif,.bmp,.webp" style="display: none;" onchange="subirDocumentoNuevo({{ $comisione->id }}, this)">
                            </div>
                        @endif

                        <!-- Tarjeta para Firma y Sello -->
                        <div class="mt-4">
                            <h6 class="text-muted text-uppercase small fw-bold mb-3">
                                <i class="bi bi-person-badge me-2"></i>Recibido
                            </h6>
                        </div>
                        <div class="card border rounded my-2" style="background: none; border-color: #e3e3e3;">
                            <div class="card-body p-2 text-center" style="min-height: 120px;">
                                <div style="height: 60px; border-bottom: 1px dashed #bbb; margin-bottom: 8px;"></div>
                                <div class="small text-muted">Firma de recibido</div>
                            </div>
                        </div>
                        <div class="small text-muted">
                            Recibo emitido el: <br>
                            {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}
                        </div>
                    </div>

                    <!-- Columna principal: Detalles de la comisión -->
                    <div class="col-sm-8">
                        <!-- Detalles de la Comisión -->
                        <h6 class="text-muted text-uppercase small fw-bold mb-3">
                            <i class="bi bi-cash-stack me-2"></i>Detalles de la Comisión
                        </h6>
                        @php
                            // Determinar color de fondo según el estado de la comisión
                            $estado = strtolower($comisione->estado);
                            $bgClass = 'bg-success bg-opacity-10 border-success border-opacity-25';
                            if ($estado === 'pendiente') {
                                $bgClass = 'bg-warning bg-opacity-10 border-warning border-opacity-25';
                            } elseif ($estado === 'cancelado' || $estado === 'inactivo') {
                                $bgClass = 'bg-danger bg-opacity-10 border-danger border-opacity-25';
                            }
                        @endphp
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <div class="p-3 {{ $bgClass }} border rounded">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <div>
                                            <div class="mb-1">
                                                <span class="fw-bold mb-0" style="font-weight:900; font-size:2em;">${{ number_format($comisione->monto, 2) }} MXN</span>
                                            </div>
                                            <div class="mb-1">
                                                <small class="d-block text-muted">
                                                    <i class="bi bi-tag me-1"></i>Tipo de comisión: <span class="fw-bold">{{ ucfirst($comisione->tipo_comision ?? 'N/A') }}</span>
                                                </small>
                                            </div>
                                            <div class="mb-1">
                                                <small class="d-block text-muted">
                                                    <i class="bi bi-calendar me-1"></i>Fecha: 
                                                    <span class="fw-bold">{{ \Carbon\Carbon::parse($comisione->fecha_comision)->translatedFormat('d \d\e F \d\e Y') }}</span>
                                                </small>
                                            </div>
                                            <div>
                                                <small class="d-block text-muted">
                                                    <i class="bi bi-check-circle me-1"></i>Estado: 
                                                    <span class="badge {{ $comisione->estado == 'Pagada' ? 'bg-success' : ($comisione->estado == 'Pendiente' ? 'bg-warning' : 'bg-danger') }}">
                                                        {{ ucfirst($comisione->estado) }}
                                                    </span>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Información del Contrato y Parcialidades -->
                        @if($comisione->contrato)
                        <div class="mb-4">
                            <div class="row g-3">
                                <!-- Columna izquierda: Información del Contrato -->
                                <div class="col-md-6">
                                    <a href="{{ route('contratos.show', $comisione->contrato->id) }}" class="text-decoration-none" title="Ver contrato">
                                    <div class="card border rounded h-100" style="background: none; border-color: #e3e3e3;">
                                        <div class="card-body p-3">
                                            <h6 class="text-muted small fw-bold mb-3">
                                                <i class="bi bi-cash-stack me-1"></i>Información del Contrato
                                            </h6>
                                            
                                            @if($comisione->contrato)
                                                    @if($comisione->contrato->cliente)
                                                    <div class="mb-2">
                                                        <strong>Cliente:</strong><br> {{ $comisione->contrato->cliente->nombre }} {{ $comisione->contrato->cliente->apellido }}
                                                    </div>
                                                    @endif
                                                    <div class="mb-2">
                                                        <strong>Contrato:</strong><br> {{ $comisione->contrato->paquete->nombre ?? 'N/A' }}#{{ $comisione->contrato->id }}
                                                    </div>
                                                    @if($comisione->contrato->fecha_inicio)
                                                    <div class="mb-2">
                                                        <strong>Fecha inicio:</strong><br> {{ \Carbon\Carbon::parse($comisione->contrato->fecha_inicio)->format('d/m/Y') }}
                                                    </div>
                                                    @endif
                                                    @endif
                                        </div>
                                    </div>
                                    </a>
                                </div>
                                
                                <!-- Columna derecha: Resumen de Parcialidades -->
                                <div class="col-md-6">
                                    <div class="card border rounded h-100" style="background: none; border-color: #e3e3e3;">
                                        <div class="card-body p-3">
                                            <h6 class="text-muted small fw-bold mb-3">
                                                <i class="bi bi-cash-stack me-1"></i>Parcialidades
                                            </h6>
                                            
                                            @php
                                                // Obtener parcialidades de esta comisión
                                                $parcialidades = \App\Models\Comisione::where('comision_padre_id', $comisione->id)
                                                    ->orderBy('fecha_comision', 'desc')
                                                    ->get();
                                                $totalParcialidades = $parcialidades->sum('monto');
                                                $montoPendiente = $comisione->monto - $totalParcialidades;
                                            @endphp
                                            
                                            @if($parcialidades->count() > 0)
                                                <div class="small">
                                                    <div class="d-flex justify-content-between mb-1">
                                                        <span class="text-muted">Total parcialidades:</span>
                                                        <span class="fw-bold">${{ number_format($totalParcialidades, 2) }}</span>
                                                    </div>
                                                    <div class="d-flex justify-content-between mb-2">
                                                        <span class="text-muted">Monto pendiente:</span>
                                                        <span class="fw-bold text-{{ $montoPendiente > 0 ? 'warning' : 'success' }}">
                                                            ${{ number_format($montoPendiente, 2) }}
                                                        </span>
                                                    </div>
                                                    <div class="mb-2">
                                                        <small class="text-muted">{{ $parcialidades->count() }} parcialidad(es) registrada(s)</small>
                                                    </div>
                                                    
                                                    <!-- Progress bar -->
                                                    @php
                                                        $porcentajePagado = $comisione->monto > 0 ? ($totalParcialidades / $comisione->monto) * 100 : 0;
                                                    @endphp
                                                    <div class="progress mb-2" style="height: 6px;">
                                                        <div class="progress-bar bg-{{ $porcentajePagado >= 100 ? 'success' : 'primary' }}" 
                                                             role="progressbar" 
                                                             style="width: {{ min($porcentajePagado, 100) }}%"
                                                             aria-valuenow="{{ $porcentajePagado }}" 
                                                             aria-valuemin="0" 
                                                             aria-valuemax="100">
                                                        </div>
                                                    </div>
                                                    <small class="text-muted">{{ number_format($porcentajePagado, 1) }}% pagado</small>
                                                </div>
                                            @else
                                                <div class="text-center text-muted small">
                                                    <i class="bi bi-info-circle mb-2 d-block" style="font-size: 1.5rem;"></i>
                                                    <p class="mb-0">No hay parcialidades registradas</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Observaciones -->
                        @if($comisione->observaciones)
                        <div class="mb-4">
                            <h6 class="text-muted text-uppercase small fw-bold mb-3">
                                <i class="bi bi-chat-text me-2"></i>Observaciones
                            </h6>
                            <div class="card border rounded" style="background: none; border-color: #e3e3e3;">
                                <div class="card-body p-3">
                                    <p class="mb-0 text-muted">{{ $comisione->observaciones }}</p>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Botón de impresión -->
            <div class="text-center p-4 d-print-none border-top" style="background-color: #f8f9fa;">
                <button onclick="window.print()" class="btn btn-primary btn-lg px-4 py-2 me-2">
                    <i class="bi bi-printer me-2"></i>
                    Imprimir Recibo
                </button>
                
                @if($comisione->tipo_comision === 'PARCIALIDAD')
                    <button onclick="confirmarEliminacionParcialidad({{ $comisione->id }})" class="btn btn-danger btn-lg px-4 py-2">
                        <i class="bi bi-trash me-2"></i>
                        Eliminar Parcialidad
                    </button>
                @endif
            </div>
        </div>
    </div>
</section>

<!-- Modal para visualizar documentos -->
<div class="modal fade" id="documentModal" tabindex="-1" aria-labelledby="documentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="documentModalLabel">Vista previa del documento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center" id="documentModalBody">
                <!-- El contenido se cargará dinámicamente -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <a id="downloadButton" href="#" class="btn btn-primary" download>
                    <i class="bi bi-download me-1"></i>Descargar
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// Función para formatear bytes (si no existe)
if (typeof formatBytes === 'undefined') {
    function formatBytes(bytes, decimals = 2) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    }
}

// Función para abrir el modal de documento
function openDocumentModal(url, fileName, type) {
    const modal = new bootstrap.Modal(document.getElementById('documentModal'));
    const modalBody = document.getElementById('documentModalBody');
    const modalLabel = document.getElementById('documentModalLabel');
    const downloadButton = document.getElementById('downloadButton');
    
    // Configurar título y botón de descarga
    modalLabel.textContent = fileName;
    downloadButton.href = url;
    downloadButton.download = fileName;
    
    // Limpiar contenido anterior
    modalBody.innerHTML = '';
    
    if (type === 'image') {
        // Mostrar imagen
        modalBody.innerHTML = `
            <img src="${url}" alt="${fileName}" class="img-fluid" style="max-height: 500px; object-fit: contain;">
        `;
    } else if (type === 'pdf') {
        // Mostrar PDF en iframe
        modalBody.innerHTML = `
            <iframe src="${url}" style="width: 100%; height: 500px; border: none;"></iframe>
        `;
    } else {
        // Mostrar información del archivo
        modalBody.innerHTML = `
            <div class="text-center">
                <i class="bi bi-file-earmark-fill text-muted" style="font-size: 4rem;"></i>
                <h5 class="mt-3">${fileName}</h5>
                <p class="text-muted">No se puede mostrar una vista previa de este tipo de archivo.</p>
                <p class="text-muted">Haz clic en "Descargar" para abrir el archivo.</p>
            </div>
        `;
    }
    
    modal.show();
}

// Función para eliminar documento
function eliminarDocumento(comisionId) {
    console.log('Función eliminarDocumento llamada con ID:', comisionId);
    
    if (confirm('¿Estás seguro de que deseas eliminar este documento? Esta acción no se puede deshacer.')) {
        console.log('Usuario confirmó la eliminación');
        
        // Obtener el token CSRF
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (!csrfToken) {
            console.error('Token CSRF no encontrado');
            alert('Error: Token CSRF no encontrado');
            return;
        }
        
        console.log('Token CSRF encontrado:', csrfToken.getAttribute('content'));
        console.log('Enviando petición DELETE a:', `/comisiones/${comisionId}/delete-documento`);
        
        // Realizar la petición AJAX
        fetch(`/comisiones/${comisionId}/delete-documento`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
        })
        .then(response => {
            console.log('Respuesta HTTP recibida:', response.status, response.statusText);
            
            // Leer el texto de la respuesta
            return response.text().then(text => {
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}, respuesta: ${text}`);
                }
                
                try {
                    return JSON.parse(text);
                } catch (e) {
                    throw new Error(`Error al parsear JSON: ${e.message}, respuesta: ${text}`);
                }
            });
        })
        .then(data => {
            console.log('Datos JSON parseados:', data);
            if (data.success) {
                alert('Documento eliminado correctamente');
                location.reload();
            } else {
                alert('Error al eliminar el documento: ' + (data.message || 'Error desconocido'));
            }
        })
        .catch(error => {
            console.error('Error completo:', error);
            alert('Error al eliminar el documento: ' + error.message);
        });
    } else {
        console.log('Usuario canceló la eliminación');
    }
}

// Función para abrir el selector de documentos
function abrirSelectorDocumento(comisionId) {
    console.log('Abriendo selector de documento para comisión:', comisionId);
    const input = document.getElementById('documentoInput');
    input.click();
}

// Función para abrir el selector de documentos cuando no hay documento
function abrirSelectorDocumentoNuevo(comisionId) {
    console.log('Abriendo selector de documento nuevo para comisión:', comisionId);
    const input = document.getElementById('documentoInputNuevo');
    input.click();
}

// Función para subir nuevo documento
function subirNuevoDocumento(comisionId, input) {
    const file = input.files[0];
    
    if (!file) {
        console.log('No se seleccionó ningún archivo');
        return;
    }
    
    console.log('Archivo seleccionado:', file.name, 'Tamaño:', file.size, 'Tipo:', file.type);
    
    // Validar tipo de archivo
    const allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/bmp', 'image/webp'];
    if (!allowedTypes.includes(file.type)) {
        alert('Tipo de archivo no permitido. Solo se permiten: PDF, JPG, PNG, GIF, BMP, WebP');
        input.value = '';
        return;
    }
    
    // Validar tamaño (10MB máximo)
    const maxSize = 10 * 1024 * 1024; // 10MB
    if (file.size > maxSize) {
        alert('El archivo es demasiado grande. Tamaño máximo permitido: 10MB');
        input.value = '';
        return;
    }
    
    // Confirmar reemplazo
    if (!confirm(`¿Estás seguro de que deseas reemplazar el documento actual con "${file.name}"?`)) {
        input.value = '';
        return;
    }
    
    // Mostrar loading en la tarjeta
    const documentSection = document.querySelector('#documento-section .card-body');
    const originalContent = documentSection.innerHTML;
    
    documentSection.innerHTML = `
        <div class="text-center p-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Subiendo...</span>
            </div>
            <p class="mt-2 mb-0">Subiendo nuevo documento...</p>
            <div class="progress mt-2" style="height: 6px;">
                <div class="progress-bar bg-primary" role="progressbar" style="width: 0%" id="uploadProgress"></div>
            </div>
        </div>
    `;
    
    // Crear FormData
    const formData = new FormData();
    formData.append('documento', file);
    
    // Obtener token CSRF
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken) {
        documentSection.innerHTML = originalContent;
        alert('Error: Token CSRF no encontrado');
        input.value = '';
        return;
    }
    
    // Crear XMLHttpRequest para mostrar progreso
    const xhr = new XMLHttpRequest();
    
    // Manejar progreso de subida
    xhr.upload.addEventListener('progress', function(e) {
        if (e.lengthComputable) {
            const percentComplete = (e.loaded / e.total) * 100;
            const progressBar = document.getElementById('uploadProgress');
            if (progressBar) {
                progressBar.style.width = percentComplete + '%';
            }
        }
    });
    
    // Manejar respuesta
    xhr.addEventListener('load', function() {
        console.log('Respuesta de subida recibida:', xhr.status, xhr.responseText);
        
        try {
            const data = JSON.parse(xhr.responseText);
            
            if (xhr.status === 200 && data.success) {
                alert('Documento subido correctamente');
                location.reload();
            } else {
                documentSection.innerHTML = originalContent;
                alert('Error al subir el documento: ' + (data.message || 'Error desconocido'));
            }
        } catch (e) {
            console.error('Error al parsear respuesta:', e, xhr.responseText);
            documentSection.innerHTML = originalContent;
            alert('Error al procesar la respuesta del servidor');
        }
        
        input.value = '';
    });
    
    // Manejar errores
    xhr.addEventListener('error', function() {
        console.error('Error de red en la subida');
        documentSection.innerHTML = originalContent;
        alert('Error de red al subir el documento');
        input.value = '';
    });
    
    // Enviar petición
    xhr.open('POST', `/comisiones/${comisionId}/upload-documento`);
    xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken.getAttribute('content'));
    xhr.send(formData);
}

// Función para subir documento nuevo (cuando no hay documento)
function subirDocumentoNuevo(comisionId, input) {
    const file = input.files[0];
    
    if (!file) {
        console.log('No se seleccionó ningún archivo');
        return;
    }
    
    console.log('Archivo seleccionado para nueva subida:', file.name, 'Tamaño:', file.size, 'Tipo:', file.type);
    
    // Validar tipo de archivo
    const allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/bmp', 'image/webp'];
    if (!allowedTypes.includes(file.type)) {
        alert('Tipo de archivo no permitido. Solo se permiten: PDF, JPG, PNG, GIF, BMP, WebP');
        input.value = '';
        return;
    }
    
    // Validar tamaño (10MB máximo)
    const maxSize = 10 * 1024 * 1024; // 10MB
    if (file.size > maxSize) {
        alert('El archivo es demasiado grande. Tamaño máximo permitido: 10MB');
        input.value = '';
        return;
    }
    
    // Confirmar subida
    if (!confirm(`¿Estás seguro de que deseas adjuntar el documento "${file.name}"?`)) {
        input.value = '';
        return;
    }
    
    // Buscar la sección del documento vacío
    const documentSection = document.querySelector('.mt-4.d-print-none .card-body');
    const originalContent = documentSection.innerHTML;
    
    // Mostrar loading en la tarjeta
    documentSection.innerHTML = `
        <div class="text-center p-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Subiendo...</span>
            </div>
            <p class="mt-2 mb-0">Subiendo documento...</p>
            <p class="small text-muted mb-2">${file.name}</p>
            <div class="progress mt-2" style="height: 6px;">
                <div class="progress-bar bg-primary" role="progressbar" style="width: 0%" id="uploadProgressNuevo"></div>
            </div>
        </div>
    `;
    
    // Crear FormData
    const formData = new FormData();
    formData.append('documento', file);
    
    // Obtener token CSRF
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken) {
        documentSection.innerHTML = originalContent;
        alert('Error: Token CSRF no encontrado');
        input.value = '';
        return;
    }
    
    // Crear XMLHttpRequest para mostrar progreso
    const xhr = new XMLHttpRequest();
    
    // Manejar progreso de subida
    xhr.upload.addEventListener('progress', function(e) {
        if (e.lengthComputable) {
            const percentComplete = (e.loaded / e.total) * 100;
            const progressBar = document.getElementById('uploadProgressNuevo');
            if (progressBar) {
                progressBar.style.width = percentComplete + '%';
            }
        }
    });
    
    // Manejar respuesta
    xhr.addEventListener('load', function() {
        console.log('Respuesta de subida nueva recibida:', xhr.status, xhr.responseText);
        
        try {
            const data = JSON.parse(xhr.responseText);
            
            if (xhr.status === 200 && data.success) {
                alert('Documento subido correctamente');
                location.reload();
            } else {
                documentSection.innerHTML = originalContent;
                alert('Error al subir el documento: ' + (data.message || 'Error desconocido'));
            }
        } catch (e) {
            console.error('Error al parsear respuesta nueva:', e, xhr.responseText);
            documentSection.innerHTML = originalContent;
            alert('Error al procesar la respuesta del servidor');
        }
        
        input.value = '';
    });
    
    // Manejar errores
    xhr.addEventListener('error', function() {
        console.error('Error de red en la subida nueva');
        documentSection.innerHTML = originalContent;
        alert('Error de red al subir el documento');
        input.value = '';
    });
    
    // Enviar petición
    xhr.open('POST', `/comisiones/${comisionId}/upload-documento`);
    xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken.getAttribute('content'));
    xhr.send(formData);
}

// Función para abrir selector de archivo cuando no hay documento
function abrirSelectorDocumentoNuevo(comisionId) {
    console.log('Abriendo selector para documento nuevo en comisión:', comisionId);
    const input = document.getElementById('documentoInputNuevo');
    if (input) {
        input.click();
    } else {
        console.error('No se encontró el input de archivo para documento nuevo');
    }
}

// Función para confirmar y eliminar parcialidad
function confirmarEliminacionParcialidad(comisionId) {
    if (confirm('¿Estás seguro de que deseas eliminar esta parcialidad?\n\nEsta acción no se puede deshacer y el monto volverá a estar disponible en la comisión padre.')) {
        eliminarParcialidad(comisionId);
    }
}

// Función para eliminar parcialidad
function eliminarParcialidad(comisionId) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    
    if (!csrfToken) {
        alert('Error: Token CSRF no encontrado');
        return;
    }
    
    // Mostrar indicador de carga
    const btnEliminar = document.querySelector(`button[onclick="confirmarEliminacionParcialidad(${comisionId})"]`);
    if (btnEliminar) {
        btnEliminar.disabled = true;
        btnEliminar.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Eliminando...';
    }
    
    fetch(`/comisiones/${comisionId}/eliminar-parcialidad`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Parcialidad eliminada exitosamente');
            // Redirigir a la vista de comisiones del contrato
            if (data.redirect_url) {
                window.location.href = data.redirect_url;
            } else {
                window.location.reload();
            }
        } else {
            throw new Error(data.message || 'Error al eliminar la parcialidad');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al eliminar la parcialidad: ' + error.message);
        
        // Restaurar botón en caso de error
        if (btnEliminar) {
            btnEliminar.disabled = false;
            btnEliminar.innerHTML = '<i class="bi bi-trash me-2"></i>Eliminar Parcialidad';
        }
    });
}
</script>
@endsection
