@extends('layouts.app')

@section('template_title')
    {{ isset($pago) ? ($pago->name ?? __('Show') . " " . __('Pago')) : __('Pago no encontrado') }}
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
        .text-center.mt-4.mb-3:last-child,
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

@if(!isset($pago) || !$pago)
    <section class="d-flex justify-content-center align-items-center py-4">
        <div class="col-md-8" style="max-width: 800px;">
            <div class="alert alert-danger">
                <h4>Error</h4>
                <p>El pago solicitado no fue encontrado.</p>
                <a href="{{ route('pagos.index') }}" class="btn btn-primary">Volver a la lista de pagos</a>
            </div>
        </div>
    </section>
@else
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
                                    @if($pago->tipo_pago === 'cuota' && $pago->numero_cuota)
                                        Recibo de Cuota #{{ $pago->numero_cuota }} 
                                        <small class="text-muted" style="font-size: 0.7em;">(Folio #{{ str_pad($pago->id ?? 0, 6, '0', STR_PAD_LEFT) }})</small>
                                    @else
                                        Recibo de Pago #{{ str_pad($pago->numero_pago ?? $pago->id ?? 0, 6, '0', STR_PAD_LEFT) }}
                                    @endif
                                </h2>
                                <div class="text-muted" style="font-size: .8rem;">
                                    {{ $pago->fecha_pago ? $pago->fecha_pago->locale('es')->translatedFormat('d \d\e F \d\e Y \a \l\a\s H:i') : 'Fecha no disponible' }}
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
                    <!-- Columna lateral: Datos del cliente y contrato -->
                    <div class="col-sm-4">
                        <h6 class="text-muted text-uppercase small fw-bold mb-3">
                            <i class="bi bi-person-badge me-2"></i>Información del Cliente
                        </h6>
                        @if($pago->contrato && $pago->contrato->cliente)
                            <div class="mb-3 p-0">
                                <div class="card border rounded h-100" style="background: none; border-color: #e3e3e3;">
                                    <div class="card-body p-4">
                                        <!-- Header del cliente -->
                                        @php
                                            $cliente = $pago->contrato->cliente;
                                        @endphp
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="avatar-circle me-3 d-flex align-items-center justify-content-center" style="min-width: 45px; min-height: 45px; width: 45px; height: 45px; background: linear-gradient(135deg, #E1B240 0%, #79481D 100%); border-radius: 50%; border: 2px solid #dee2e6;">
                                                {{ strtoupper(substr($cliente->nombre, 0, 1) . substr($cliente->apellido, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="fw-semibold text-dark">{{ $cliente->nombre }} {{ $cliente->apellido }}</div>
                                            </div>
                                        </div>

                                        <!-- Información de contacto -->
                                        <div class="row g-3 mb-3">
                                            @if($pago->contrato->cliente->email)
                                            <div class="col-md-12">
                                                <div class="d-flex align-items-center gap-2">
                                                    <i class="bi bi-envelope text-primary"></i>
                                                    <span class="text-muted small">{{ $pago->contrato->cliente->email }}</span>
                                                </div>
                                            </div>
                                            @endif

                                            @if($pago->contrato->cliente->telefono)
                                            <div class="col-md-12">
                                                <div class="d-flex align-items-center gap-2">
                                                    <i class="bi bi-telephone text-success"></i>
                                                    <span class="text-muted small">{{ $pago->contrato->cliente->telefono }}</span>
                                                </div>
                                            </div>
                                            @endif

                                            @if($pago->contrato->cliente->domicilio_completo || ($pago->contrato->cliente->calle_y_numero && $pago->contrato->cliente->colonia && $pago->contrato->cliente->municipio))
                                            <div class="col-12">
                                                <div class="d-flex align-items-start gap-2">
                                                    <i class="bi bi-geo-alt text-warning"></i>
                                                    <span class="text-muted small">
                                                        {{ $pago->contrato->cliente->domicilio_completo ?: $pago->contrato->cliente->calle_y_numero . ', ' . $pago->contrato->cliente->colonia . ', ' . $pago->contrato->cliente->municipio }}
                                                    </span>
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="text-muted">Información del cliente no disponible</div>
                        @endif

                        <!-- Documento Adjunto -->
                        <div class="mt-4 d-print-none">
                            <h6 class="text-muted text-uppercase small fw-bold mb-3">
                                <i class="bi bi-paperclip me-2"></i>Documento Adjunto
                            </h6>
                            @if($pago->documento)
                                <!-- Mostrar documento existente -->
                                <div class="card border rounded" style="background: none; border-color: #e3e3e3;">
                                    <div class="card-body p-3 text-center">
                                        @php
                                            $extension = strtolower(pathinfo($pago->documento, PATHINFO_EXTENSION));
                                            $isPDF = $extension === 'pdf';
                                            $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp']);
                                            // Usar Storage::url() para archivos en el disco public
                                            $documentoUrl = \Illuminate\Support\Facades\Storage::url($pago->documento);
                                            $fileName = basename($pago->documento);
                                        @endphp
                                        
                                        <div class="row mb-3">
                                            <!-- Columna de miniatura -->
                                            <div class="col-4 d-flex justify-content-center">
                                                @if($isImage)
                                                    <!-- Miniatura de imagen -->
                                                    <div class="document-thumbnail" style="width: 80px; height: 80px; border-radius: 8px; overflow: hidden; border: 2px solid #dee2e6; cursor: pointer;" onclick="openDocumentModal('{{ $documentoUrl }}', '{{ $fileName }}', 'image')">
                                                        <img src="{{ $documentoUrl }}" alt="Documento adjunto" style="width: 100%; height: 100%; object-fit: cover;">
                                                    </div>
                                                @elseif($isPDF)
                                                    <!-- Icono de PDF -->
                                                    <div class="document-thumbnail d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; border-radius: 8px; background: linear-gradient(135deg, #dc3545 0%, #b02a37 100%); color: white; cursor: pointer; border: 2px solid #dee2e6;" onclick="openDocumentModal('{{ $documentoUrl }}', '{{ $fileName }}', 'pdf')">
                                                        <i class="bi bi-file-earmark-pdf-fill" style="font-size: 32px;"></i>
                                                    </div>
                                                @else
                                                    <!-- Icono genérico de archivo -->
                                                    <div class="document-thumbnail d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; border-radius: 8px; background: linear-gradient(135deg, #6c757d 0%, #495057 100%); color: white; cursor: pointer; border: 2px solid #dee2e6;" onclick="openDocumentModal('{{ $documentoUrl }}', '{{ $fileName }}', 'file')">
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
                                                    @if(\Illuminate\Support\Facades\Storage::disk('public')->exists($pago->documento))
                                                        • {{ formatBytes(\Illuminate\Support\Facades\Storage::disk('public')->size($pago->documento)) }}
                                                    @endif
                                                </div>

                                                <!-- Fecha -->
                                                <div class="text-muted mb-3" style="font-size: 0.7rem;">
                                                    Adjuntado el {{ $pago->created_at->format('d/m/Y H:i') }}
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Botones de acción en horizontal -->
                                        <div class="d-flex justify-content-between align-items-center">
                                            <button class="btn btn-outline-primary btn-sm flex-grow-1 mx-1 d-print-none" onclick="openDocumentModal('{{ $documentoUrl }}', '{{ $fileName }}', '{{ $isImage ? 'image' : ($isPDF ? 'pdf' : 'file') }}')" title="Ver documento">
                                                <i class="bi bi-eye me-1"></i> Ver
                                            </button>
                                            <a href="{{ $documentoUrl }}" class="btn btn-outline-secondary btn-sm flex-grow-1 mx-1 d-print-none" download="{{ $fileName }}" title="Descargar">
                                                <i class="bi bi-download me-1"></i> Descargar
                                            </a>
                                            <button class="btn btn-outline-danger btn-sm flex-grow-1 mx-1 d-print-none" onclick="eliminarDocumento({{ $pago->id }})" title="Eliminar documento">
                                                <i class="bi bi-trash me-1"></i> Eliminar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <!-- Formulario para subir documento -->
                                <div class="card border rounded" style="background: none; border-color: #e3e3e3;">
                                    <div class="card-body p-3">
                                        
                                        <!-- Área de drag & drop -->
                                        <div id="dropZone" class="border border-dashed rounded p-4 text-center" style="border-color: #dee2e6; background: #f8f9fa; cursor: pointer; transition: all 0.3s ease;">
                                            <input type="file" id="documentInput" class="d-none" accept=".pdf,.jpg,.jpeg,.png,.gif,.bmp,.webp">
                                            <div id="dropZoneContent">
                                                <i class="bi bi-plus-circle text-primary mb-2" style="font-size: 2rem;"></i>
                                                <p class="mb-1 fw-semibold">Arrastra un archivo aquí o haz clic para seleccionar</p>
                                                <small class="text-muted">
                                                    Formatos soportados: PDF, Imágenes (JPG, PNG, GIF, BMP, WebP)<br>
                                                    Tamaño máximo: 10 MB
                                                </small>
                                            </div>
                                        </div>
                                        
                                        <!-- Vista previa del archivo -->
                                        <div id="filePreview" class="mt-3 d-none">
                                            <div class="d-flex align-items-center p-3 bg-light rounded">
                                                <div class="d-flex align-items-center">
                                                    <div id="previewThumbnail" class="me-3"></div>
                                                    <div>
                                                        <div id="previewFileName" class="fw-semibold"></div>
                                                        <div id="previewFileInfo" class="text-muted small"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-end mt-2">
                                                <button type="button" class="btn btn-success btn-sm me-2" onclick="uploadDocument()">
                                                    <i class="bi bi-upload me-1"></i>Subir
                                                </button>
                                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="cancelUpload()">
                                                    <i class="bi bi-x"></i>
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <!-- Barra de progreso -->
                                        <div id="uploadProgress" class="mt-3 d-none">
                                            <div class="d-flex align-items-center mb-2">
                                                <small class="text-muted me-2">Subiendo archivo...</small>
                                                <div class="spinner-border spinner-border-sm text-primary" role="status" aria-hidden="true"></div>
                                            </div>
                                            <div class="progress" style="height: 6px;">
                                                <div id="progressBar" class="progress-bar bg-primary" role="progressbar" style="width: 0%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Tarjeta para Firma y Sello -->
                         <div class="mt-4">
                            <h6 class="text-muted text-uppercase small fw-bold mb-3">
                                <i class="bi bi-person-badge me-2"></i>Recibido por
                            </h6>
                         </div>
                        <div class="card border rounded my-2" style="background: none; border-color: #e3e3e3;">
                            <div class="card-body p-2 text-center" style="min-height: 120px;">
                                <div style="height: 60px; border-bottom: 1px dashed #bbb; margin-bottom: 8px;"></div>
                                <div class="small text-muted">Firma y Sello</div>
                            </div>
                            
                        </div>
                        <div class="small text-muted">
                            Recibo emitido el: <br>
                            {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}
                        </div>
                    </div>

                    <!-- Columna principal: Detalles del pago y observaciones -->
                    <div class="col-sm-8">
                        <!-- Detalles del Pago -->
                        <h6 class="text-muted text-uppercase small fw-bold mb-3">
                            <i class="bi bi-cash-stack me-2"></i>Detalles del Pago
                        </h6>
                        @php
                            // Determinar color de fondo según el estado del pago
                            $estado = strtolower($pago->estado);
                            $bgClass = 'bg-success bg-opacity-10 border-success border-opacity-25';
                            if ($estado === 'pendiente') {
                                $bgClass = 'bg-warning bg-opacity-10 border-warning border-opacity-25';
                            } elseif ($estado === 'cancelado' || $estado === 'fallido') {
                                $bgClass = 'bg-danger bg-opacity-10 border-danger border-opacity-25';
                            }
                        @endphp
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <div class="p-3 {{ $bgClass }} border rounded">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <div>
                                            <div class="mb-1">
                                                <span class="fw-bold mb-0" style="font-weight:900; font-size:2em;">${{ number_format($pago->monto, 2) }} MXN</span>
                                            </div>
                                            <div class="mb-1">
                                                <small class="d-block text-muted">
                                                    <i class="bi bi-tag me-1"></i>Tipo de pago: <span class="fw-bold">{{ ucfirst($pago->tipo_pago ?? 'N/A') }}</span>
                                                </small>
                                            </div>
                                            <div class="mb-1">
                                                <small class="d-block text-muted">
                                                    <i class="bi bi-credit-card me-1"></i>Método: 
                                                    @if(strtolower($pago->estado) === 'hecho')
                                                        <div class="d-inline-block position-relative">
                                                            <select id="metodo_pago_select" class="form-select form-select-sm d-inline-block" style="width: auto; font-size: 0.875rem; font-weight: bold; padding-right: 2rem;">
                                                                @foreach(\App\Models\Pago::METODOS_PAGO as $key => $label)
                                                                    <option value="{{ $key }}" {{ $pago->metodo_pago == $key ? 'selected' : '' }}>
                                                                        {{ $label }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            <i class="bi bi-pencil-square position-absolute text-muted" style="right: 0.5rem; top: 50%; transform: translateY(-50%); font-size: 0.7rem; pointer-events: none;"></i>
                                                            <span id="loading_metodo" class="spinner-border spinner-border-sm ms-2 d-none" role="status" aria-hidden="true"></span>
                                                            <span class="d-none d-print-inline fw-bold">{{ \App\Models\Pago::METODOS_PAGO[$pago->metodo_pago] ?? $pago->metodo_pago }}</span>
                                                        </div>
                                                    @else
                                                        <span class="fw-bold text-muted">{{ \App\Models\Pago::METODOS_PAGO[$pago->metodo_pago] ?? $pago->metodo_pago }}</span>
                                                    @endif
                                                </small>
                                            </div>
                                            <div>
                                                <small class="d-block text-muted">
                                                    <i class="bi bi-check-circle me-1"></i>Estado: 
                                                    <span class="badge {{ $pago->estado == 'hecho' ? 'bg-success' : ($pago->estado == 'pendiente' ? 'bg-warning' : 'bg-danger') }}">
                                                        {{ ucfirst($pago->estado) }}
                                                    </span>
                                                </small>
                                            </div>
                                        </div>
                                        @if($pago->contrato)
                                            @php
                                                // Calcular cuotas (solo pagos de tipo "cuota")
                                                $cuotasPagadas = $pago->contrato->pagos->filter(function($pagoContrato) {
                                                    return $pagoContrato->estado == 'hecho' && 
                                                        strtolower($pagoContrato->tipo_pago ?? '') == 'cuota';
                                                })->count();

                                                $totalCuotas = $pago->contrato->numero_cuotas ?? 0;
                                                $porcentajeCuotas = $totalCuotas > 0 ? ($cuotasPagadas / $totalCuotas) * 100 : 0;
                                                $montoRestante = $pago->saldo_restante;
                                                $montoTotalPagado = $pago->contrato->monto_total - $pago->saldo_restante;
                                                $progreso = ($montoTotalPagado / $pago->contrato->monto_total) * 100;
                                            @endphp

                                            <div class="text-end">
                                                <!-- Gráfico circular de progreso -->
                                                <div class="text-center">
                                                    <div class="position-relative d-inline-block">
                                                        <svg width="80" height="80" class="progress-ring">
                                                            <!-- Círculo de fondo -->
                                                            <circle cx="40" cy="40" r="35"
                                                                fill="none"
                                                                stroke="#d1d5db"
                                                                stroke-width="6" />
                                                            <!-- Círculo de progreso -->
                                                            <circle cx="40" cy="40" r="35"
                                                                fill="none"
                                                                stroke="#198754"
                                                                stroke-width="6"
                                                                stroke-linecap="round"
                                                                stroke-dasharray="{{ 2 * pi() * 35 }}"
                                                                stroke-dashoffset="{{ 2 * pi() * 35 - ($progreso / 100) * 2 * pi() * 35 }}"
                                                                transform="rotate(-90 40 40)"
                                                                style="transition: stroke-dashoffset 0.5s ease-in-out" />
                                                        </svg>
                                                        <!-- Texto en el centro del círculo -->
                                                        <div class="position-absolute top-50 start-50 translate-middle text-center">
                                                            <div class="fw-bold text-success" style="font-size: 12px; line-height: 1;">
                                                                {{ round($progreso) }}%
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- Leyenda debajo del gráfico -->
                                                    <div class="mt-2">
                                                        <small class="text-muted d-block" style="font-size: 10px;">
                                                            Progreso del contrato
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    @if($pago->contrato)
                                        @php
                                        $montoInicial = $pago->contrato->monto_inicial ?? 0;
                                        $montoBonificacion = $pago->contrato->monto_bonificacion ?? 0;
                                        $montoFinanciado = $pago->contrato->monto_total - $montoInicial - $montoBonificacion;
                                        $cuotaCalculada = $pago->contrato->numero_cuotas > 0 ? $montoFinanciado / $pago->contrato->numero_cuotas : 0;
                                        @endphp

                                        <!-- Resumen financiero del pago -->
                                        <div class="mt-3 p-2 bg-white bg-opacity-50 rounded border">
                                            <div class="row text-center">
                                                <div class="col-12 mb-2">
                                                    <small class="text-muted fw-bold">Resumen Financiero</small>
                                                </div>

                                                <div class="col-12 mb-2">
                                                    <div class="d-flex justify-content-between align-items-center text-sm">
                                                        <small class="text-muted">Total a pagar:</small>
                                                        <small class="fw-bold">${{ number_format($pago->contrato->monto_total, 2) }}</small>
                                                    </div>
                                                    <div class="d-flex justify-content-between align-items-center text-sm">
                                                        @if(strtolower($pago->estado) == 'pendiente')
                                                            <small class="text-muted">Con este pago irían:</small>
                                                        @elseif(strtolower($pago->estado) == 'hecho')
                                                            <small class="text-success fw-bold">Pagado</small>
                                                        @else
                                                            <small class="text-muted">Con este pago van:</small>
                                                        @endif
                                                        <small class="text-success fw-bold">${{ number_format($montoTotalPagado, 2) }}</small>
                                                    </div>
                                                    <hr class="my-1">
                                                    <div class="d-flex justify-content-between align-items-center text-sm">
                                                        <small class="text-muted fw-bold">Saldo Restante:</small>
                                                        <small class="fw-bold {{ $montoRestante <= 0 ? 'text-success' : 'text-danger' }}">
                                                            ${{ number_format($montoRestante, 2) }}
                                                        </small>
                                                    </div>
                                                </div>

                                                <!-- <div class="col-12">
                                                    <div class="bg-primary bg-opacity-10 rounded p-2">
                                                        <div class="text-center">
                                                            <small class="text-muted">Estado del contrato</small>
                                                        </div>
                                                        <div class="row mt-1">
                                                            <div class="col-6 text-center">
                                                                <small class="text-muted d-block">Progreso</small>
                                                                <strong class="text-primary">{{ number_format($progreso, 1) }}%</strong>
                                                            </div>
                                                            <div class="col-6 text-center">
                                                                <small class="text-muted d-block">Estado</small>
                                                                <strong class="text-primary">{{ ucfirst($pago->contrato->estado ?? 'N/A') }}</strong>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div> -->
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <!-- Observaciones -->
                        @if($pago->observaciones)
                        <div class="mb-4">
                            <div class="border rounded p-3 bg-light small">
                                {{ $pago->observaciones }}
                            </div>
                        </div>
                        @endif

                        <!-- Parcialidades (solo para cuotas) -->
                        @if($pago->tipo_pago === 'cuota' && $pago->parcialidades->count() > 0)
                        <div class="mb-4">
                            <h6 class="text-muted text-uppercase small fw-bold mb-3">
                                <i class="bi bi-layout-split me-2"></i>Parcialidades Aplicadas
                            </h6>
                            <div class="border rounded p-3 bg-light">
                                @php
                                    $totalParcialidades = $pago->parcialidades->where('estado', 'hecho')->sum('monto');
                                    $montoOriginalCuota = $pago->monto_original_cuota;
                                    $montoActualCuota = $pago->monto;
                                    $montoPendiente = max(0, $montoOriginalCuota - $totalParcialidades);
                                @endphp
                                
                                <div class="mb-3">
                                    <div class="row">
                                        <div class="col-sm-3">
                                            <small class="text-muted">Monto original de la cuota:</small>
                                            <div class="fw-bold text-info">${{ number_format($montoOriginalCuota, 2) }}</div>
                                        </div>
                                        <div class="col-sm-3">
                                            <small class="text-muted">Total en parcialidades:</small>
                                            <div class="fw-bold text-success">${{ number_format($totalParcialidades, 2) }}</div>
                                        </div>
                                        <div class="col-sm-3">
                                            <small class="text-muted">Monto actual de la cuota:</small>
                                            <div class="fw-bold text-primary">${{ number_format($montoActualCuota, 2) }}</div>
                                        </div>
                                        <div class="col-sm-3">
                                            <small class="text-muted">Estado:</small>
                                            <div class="fw-bold">
                                                @if($montoPendiente <= 0)
                                                    <span class="badge bg-success">Completo</span>
                                                @else
                                                    <span class="badge bg-warning">Parcial</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @if($montoPendiente > 0)
                                <div class="alert alert-info alert-sm mb-3">
                                    <i class="bi bi-info-circle me-2"></i>
                                    <small>
                                        El monto de la cuota se ha reducido automáticamente de 
                                        <strong>${{ number_format($montoOriginalCuota, 2) }}</strong> a 
                                        <strong>${{ number_format($montoActualCuota, 2) }}</strong> 
                                        debido a las parcialidades aplicadas.
                                    </small>
                                </div>
                                @endif

                                <div class="border-top pt-3">
                                    <small class="text-muted fw-bold d-block mb-2">Detalle de parcialidades:</small>
                                    @foreach($pago->parcialidades->sortBy('created_at') as $parcialidad)
                                        <div class="d-flex justify-content-between align-items-center py-1">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-circle-fill text-success me-2" style="font-size: 0.5rem;"></i>
                                                <small>
                                                    Pago #{{ str_pad($parcialidad->id, 6, '0', STR_PAD_LEFT) }}
                                                    <span class="text-muted">
                                                        - {{ \Carbon\Carbon::parse($parcialidad->fecha_pago)->format('d/m/Y H:i') }}
                                                    </span>
                                                </small>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <span class="badge bg-success me-2">${{ number_format($parcialidad->monto, 2) }}</span>
                                                <span class="badge {{ $parcialidad->estado == 'hecho' ? 'bg-success' : 'bg-warning' }}">
                                                    {{ ucfirst($parcialidad->estado) }}
                                                </span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Información del pago padre (solo para parcialidades) -->
                        @if($pago->tipo_pago === 'parcialidad' && $pago->pagoPadre)
                        <div class="mb-4">
                            <h6 class="text-muted text-uppercase small fw-bold mb-3">
                                <i class="bi bi-link-45deg me-2"></i>Pago Asociado
                            </h6>
                            <div class="border rounded p-3 bg-info bg-opacity-10">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <small class="text-muted">Esta parcialidad está aplicada a:</small>
                                        <div class="fw-bold">
                                            Cuota #{{ str_pad($pago->pagoPadre->id, 6, '0', STR_PAD_LEFT) }}
                                        </div>
                                        <small class="text-muted">{{ $pago->pagoPadre->observaciones }}</small>
                                    </div>
                                    <div class="text-end">
                                        <a href="{{ route('pagos.show', $pago->pagoPadre->id) }}" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye me-1"></i>Ver Cuota
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="text-center mt-4 mb-3 d-print-none">

                    @if(strtolower($pago->estado) === 'hecho')
                        <button class="btn btn-warning me-2 position-relative" id="btnDeshacerPago" 
                                data-pago-id="{{ $pago->id }}"
                                data-tipo-pago="{{ $pago->tipo_pago }}"
                                data-monto="{{ $pago->monto }}"
                                data-numero-cuota="{{ $pago->observaciones }}"
                                title="Deshacer este pago">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Deshacer Pago
                            <span class="spinner-border spinner-border-sm ms-2 d-none" id="loadingDeshacerBtn" role="status" aria-hidden="true"></span>
                        </button>
                    @endif
                    
                    <button class="btn btn-primary me-2" onclick="window.print()">
                        <i class="fas fa-print"></i> Imprimir Recibo
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal de Confirmación para Deshacer Pago -->
<div class="modal fade" id="modalDeshacerPago" tabindex="-1" aria-labelledby="modalDeshacerPagoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="modalDeshacerPagoLabel">
                    <i class="bi bi-exclamation-triangle me-2"></i>Confirmar Deshacer Pago
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <strong>¡Atención!</strong> Esta acción modificará el estado del pago y el saldo del contrato.
                </div>
                
                <!-- Mensaje de estado durante el procesamiento -->
                <div id="processingMessage" class="alert alert-info d-none">
                    <div class="d-flex align-items-center">
                        <div class="spinner-border spinner-border-sm me-3" role="status" aria-hidden="true"></div>
                        <div>
                            <strong>Procesando...</strong>
                            <div class="small" id="processingText">Deshaciendo el pago y actualizando el contrato...</div>
                        </div>
                    </div>
                </div>
                
                <div id="modalContentInfo">
                    <!-- Contenido dinámico según el tipo de pago -->
                </div>

                <div class="mt-3">
                    <h6>Impacto en el contrato:</h6>
                    <div class="bg-light p-3 rounded">
                        <div class="row">
                            <div class="col-md-6">
                                <small class="text-muted">Saldo actual:</small>
                                <div class="fw-bold">${{ number_format($pago->saldo_restante, 2) }}</div>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted">Saldo después de deshacer:</small>
                                <div class="fw-bold text-danger" id="nuevoSaldo"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning" id="confirmarDeshacer">
                    <span id="loadingDeshacerIcon" class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                    <i class="bi bi-arrow-counterclockwise me-1"></i>
                    Confirmar Deshacer
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para visualizar documentos -->
<div class="modal fade" id="documentModal" tabindex="-1" aria-labelledby="documentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="documentModalLabel">
                    <i class="bi bi-file-earmark me-2"></i>
                    <span id="documentTitle">Documento Adjunto</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center" style="min-height: 400px;">
                <div id="documentContent">
                    <!-- El contenido se cargará dinámicamente -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <a id="downloadDocumentBtn" href="#" class="btn btn-primary" download>
                    <i class="bi bi-download me-1"></i>Descargar
                </a>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .btn, .d-print-none {
        display: none !important;
    }
    .shadow-lg {
        box-shadow: none !important;
    }
    body {
        font-size: 12px;
    }
    
    /* En modo impresión, ocultar el select y mostrar solo el texto */
    #metodo_pago_select, .bi-pencil-square, #loading_metodo {
        display: none !important;
    }
    
    /* Estilos para documento adjunto en impresión */
    .document-thumbnail {
        border: 1px solid #000 !important;
        box-shadow: none !important;
    }
    
    .document-thumbnail img {
        filter: grayscale(100%);
    }
}

/* Estilos para los detalles del pago */
.icon-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.payment-detail-item {
    transition: transform 0.2s ease;
}

.payment-detail-item:hover {
    transform: translateX(5px);
}

.financial-summary {
    backdrop-filter: blur(10px);
    transition: all 0.3s ease;
}

.financial-summary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

/* Animación para la barra de progreso */
.progress-bar-animated {
    animation: progress-bar-stripes 1s linear infinite;
}

@keyframes progress-bar-stripes {
    0% {
        background-position: 1rem 0;
    }
    100% {
        background-position: 0 0;
    }
}

.card {
    transition: all 0.3s ease;
}

.bg-gradient {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
}

/* Estilos para la tarjeta de cliente moderna */
.hover-card {
    transition: all 0.3s ease;
    border: 1px solid rgba(0,0,0,0.05) !important;
}

.hover-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
}

.client-avatar {
    transition: transform 0.3s ease;
}

.hover-card:hover .client-avatar {
    transform: scale(1.05);
}

.client-stat {
    transition: all 0.3s ease;
}

.client-stat:hover {
    transform: translateY(-2px);
}

/* Estilos para el select de método de pago */
#metodo_pago_select {
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    padding: 0.25rem 0.5rem;
    background-color: #fff;
    color: #495057;
    transition: all 0.15s ease-in-out;
    cursor: pointer;
    appearance: none;
    background-image: none;
}

#metodo_pago_select:hover {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.1rem rgba(13, 110, 253, 0.25);
}

#metodo_pago_select:hover + .bi-pencil-square {
    color: #0d6efd !important;
}

#metodo_pago_select:focus {
    border-color: #86b7fe;
    outline: 0;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

#metodo_pago_select:disabled {
    background-color: #e9ecef;
    cursor: not-allowed;
    opacity: 0.65;
}

/* Icono de edición */
.bi-pencil-square {
    transition: color 0.15s ease-in-out;
    opacity: 0.7;
}

/* Estilos para el botón de deshacer pago */
#btnDeshacerPago {
    transition: all 0.3s ease;
}

#btnDeshacerPago:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(255, 193, 7, 0.4);
}

#btnDeshacerPago:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

#loadingDeshacerBtn {
    width: 1rem;
    height: 1rem;
}

/* Estilos para botones en estado de carga */
.btn-loading {
    position: relative;
    pointer-events: none;
    opacity: 0.7;
}

.btn-loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 16px;
    height: 16px;
    margin: -8px 0 0 -8px;
    border: 2px solid transparent;
    border-top-color: currentColor;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

/* Estilos para el modal de confirmación */
.modal-content {
    border: none;
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
}

.modal-header.bg-warning {
    border-radius: 12px 12px 0 0;
}

.modal-footer {
    border-radius: 0 0 12px 12px;
}

/* Estilos para prevenir sobreposición */
.text-center.mt-4.mb-3 {
    padding: 20px 0;
    clear: both;
    position: relative;
    z-index: 1;
}

/* Espaciado adicional para el contenedor principal */
.border.shadow-lg.p-0 {
    margin-bottom: 30px;
}

/* Asegurar que los botones tengan suficiente espacio */
@media screen {
    .btn {
        margin: 5px;
    }
}

/* Estilos para la miniatura de documentos */
.document-thumbnail {
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.document-thumbnail:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

.document-thumbnail img {
    transition: transform 0.3s ease;
}

.document-thumbnail:hover img {
    transform: scale(1.05);
}

/* Estilos para el modal de documentos */
#documentModal .modal-content {
    border-radius: 12px;
    border: none;
    box-shadow: 0 10px 40px rgba(0,0,0,0.15);
}

#documentModal .modal-body {
    background: #f8f9fa;
}

#documentContent img {
    max-width: 100%;
    max-height: 70vh;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

#documentContent iframe {
    width: 100%;
    height: 70vh;
    border: none;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.file-icon {
    font-size: 80px;
    color: #6c757d;
    margin-bottom: 20px;
}

/* Animación de carga */
.loading-spinner {
    display: inline-block;
    width: 40px;
    height: 40px;
    border: 4px solid #f3f3f3;
    border-radius: 50%;
    border-top: 4px solid #007bff;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Estilos para la zona de drag & drop */
#dropZone:hover {
    border-color: #007bff !important;
    background: #e3f2fd !important;
}

#dropZone.drag-over {
    border-color: #28a745 !important;
    background: #d4edda !important;
    transform: scale(1.02);
}

#dropZone.drag-over .bi-plus-circle {
    color: #28a745 !important;
    transform: scale(1.1);
}

/* Miniaturas en previsualización */
.preview-thumbnail {
    width: 50px;
    height: 50px;
    border-radius: 6px;
    overflow: hidden;
    border: 2px solid #dee2e6;
    display: flex;
    align-items: center;
    justify-content: center;
}

.preview-thumbnail img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.preview-thumbnail.pdf {
    background: linear-gradient(135deg, #dc3545 0%, #b02a37 100%);
    color: white;
}

.preview-thumbnail.file {
    background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
    color: white;
}

/* Animaciones de carga */
.upload-success {
    animation: successPulse 0.6s ease-in-out;
}

@keyframes successPulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); background-color: #d4edda; }
    100% { transform: scale(1); }
}

/* Estilos responsive para documentos */
@media (max-width: 768px) {
    .document-thumbnail {
        width: 50px !important;
        height: 50px !important;
    }
    
    .document-thumbnail i {
        font-size: 20px !important;
    }
    
    #dropZone {
        padding: 2rem 1rem !important;
    }
    
    #dropZone i {
        font-size: 1.5rem !important;
    }
    
    #documentModal .modal-dialog {
        margin: 10px;
    }
    
    #documentContent img {
        max-height: 50vh;
    }
    
    #documentContent iframe {
        height: 50vh;
    }
    
    .preview-thumbnail {
        width: 40px !important;
        height: 40px !important;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const metodoPagoSelect = document.getElementById('metodo_pago_select');
    const loadingSpinner = document.getElementById('loading_metodo');
    
    // Solo configurar el evento si el select existe (estado = "hecho")
    if (metodoPagoSelect && loadingSpinner) {
        let originalValue = metodoPagoSelect.value;
        
        metodoPagoSelect.addEventListener('change', function() {
            const nuevoMetodo = this.value;
            const originalText = this.options[this.selectedIndex].text;
            const originalOption = Array.from(this.options).find(opt => opt.value === originalValue);
            
            // Mostrar loading
            loadingSpinner.classList.remove('d-none');
            metodoPagoSelect.disabled = true;
            
            // Realizar petición AJAX
            fetch(`{{ route('pagos.updateMetodoPago', $pago->id) }}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    metodo_pago: nuevoMetodo
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Actualizar valor original
                    originalValue = nuevoMetodo;
                    
                    // Mostrar mensaje de éxito
                    showAlert('success', data.message);
                    
                    // Actualizar el span para impresión
                    const printSpan = metodoPagoSelect.parentElement.querySelector('.d-print-inline');
                    if (printSpan) {
                        printSpan.textContent = data.metodo_pago_label;
                    }
                } else {
                    // Revertir el select al valor original
                    metodoPagoSelect.value = originalValue;
                    
                    // Mostrar mensaje de error
                    showAlert('error', data.message || 'Error al actualizar el método de pago');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                
                // Revertir el select al valor original
                metodoPagoSelect.value = originalValue;
                
                // Mostrar mensaje de error más específico
                let errorMessage = 'Error de conexión al actualizar el método de pago';
                if (error.message.includes('422')) {
                    errorMessage = 'Método de pago no válido';
                } else if (error.message.includes('404')) {
                    errorMessage = 'Pago no encontrado';
                } else if (error.message.includes('403')) {
                    errorMessage = 'No tienes permisos para realizar esta acción';
                }
                
                showAlert('error', errorMessage);
            })
            .finally(() => {
                // Ocultar loading
                loadingSpinner.classList.add('d-none');
                metodoPagoSelect.disabled = false;
            });
        });
    }
    
    function showAlert(type, message) {
        // Remover alertas existentes
        const existingAlerts = document.querySelectorAll('.alert.position-fixed');
        existingAlerts.forEach(alert => alert.remove());
        
        // Crear alerta
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
        alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; max-width: 400px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);';
        
        alertDiv.innerHTML = `
            <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        // Agregar al documento
        document.body.appendChild(alertDiv);
        
        // Auto-remover después de 5 segundos
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.classList.remove('show');
                setTimeout(() => {
                    if (alertDiv.parentNode) {
                        alertDiv.remove();
                    }
                }, 150);
            }
        }, 5000);
    }

    // Funcionalidad para deshacer pagos
    const btnDeshacerPago = document.getElementById('btnDeshacerPago');
    const modalDeshacerPago = new bootstrap.Modal(document.getElementById('modalDeshacerPago'));
    const modalContentInfo = document.getElementById('modalContentInfo');
    const nuevoSaldoElement = document.getElementById('nuevoSaldo');
    const confirmarDeshacerBtn = document.getElementById('confirmarDeshacer');
    const loadingDeshacerIcon = document.getElementById('loadingDeshacerIcon');
    const loadingDeshacerBtn = document.getElementById('loadingDeshacerBtn');
    const processingMessage = document.getElementById('processingMessage');
    const processingText = document.getElementById('processingText');

    if (btnDeshacerPago) {
        btnDeshacerPago.addEventListener('click', function() {
            const tipoPago = this.dataset.tipoPago;
            const monto = parseFloat(this.dataset.monto);
            const saldoActual = {{ $pago->saldo_restante }};
            const pagoId = this.dataset.pagoId;
            
            // Configurar contenido del modal según el tipo de pago
            let contenidoInfo = '';
            let nuevoSaldo = saldoActual;
            
            switch (tipoPago.toLowerCase()) {
                case 'inicial':
                    contenidoInfo = `
                        <h6><i class="bi bi-info-circle text-primary me-2"></i>Pago Inicial</h6>
                        <p>Se realizarán las siguientes acciones:</p>
                        <ul class="list-unstyled">
                            <li><i class="bi bi-check text-success me-2"></i>El monto de $${monto.toLocaleString()} será agregado al saldo restante</li>
                            <li><i class="bi bi-check text-success me-2"></i>El monto del pago se cambiará a $0.00</li>
                            <li><i class="bi bi-check text-success me-2"></i>El estado se mantendrá como "Hecho" pero no contará en la suma</li>
                            <li><i class="bi bi-check text-success me-2"></i>Se agregará una observación sobre la cancelación</li>
                        </ul>
                    `;
                    nuevoSaldo = saldoActual + monto;
                    break;
                    
                case 'bonificacion':
                case 'bonificación':
                    contenidoInfo = `
                        <h6><i class="bi bi-info-circle text-primary me-2"></i>Pago de Bonificación</h6>
                        <p>Se realizarán las siguientes acciones:</p>
                        <ul class="list-unstyled">
                            <li><i class="bi bi-check text-success me-2"></i>El monto de $${monto.toLocaleString()} será agregado al saldo restante</li>
                            <li><i class="bi bi-check text-success me-2"></i>El monto del pago se cambiará a $0.00</li>
                            <li><i class="bi bi-check text-success me-2"></i>El estado se mantendrá como "Hecho" pero no contará en la suma</li>
                            <li><i class="bi bi-check text-success me-2"></i>Se agregará una observación sobre la cancelación</li>
                        </ul>
                    `;
                    nuevoSaldo = saldoActual + monto;
                    break;
                    
                case 'cuota':
                    contenidoInfo = `
                        <h6><i class="bi bi-info-circle text-primary me-2"></i>Pago de Cuota</h6>
                        <p>Se realizarán las siguientes acciones:</p>
                        <ul class="list-unstyled">
                            <li><i class="bi bi-check text-success me-2"></i>El monto se reiniciará al valor de la cuota del contrato</li>
                            <li><i class="bi bi-check text-success me-2"></i>Las observaciones se reiniciarán</li>
                            <li><i class="bi bi-check text-success me-2"></i>El estado cambiará a "Pendiente"</li>
                            <li><i class="bi bi-check text-success me-2"></i>El saldo restante se ajustará automáticamente</li>
                        </ul>
                    `;
                    nuevoSaldo = saldoActual + monto;
                    break;
                    
                case 'parcialidad':
                    contenidoInfo = `
                        <h6><i class="bi bi-exclamation-triangle text-warning me-2"></i>Pago de Parcialidad</h6>
                        <div class="alert alert-danger">
                            <strong>¡IMPORTANTE!</strong> Este registro será eliminado completamente.
                        </div>
                        <p>Se realizarán las siguientes acciones:</p>
                        <ul class="list-unstyled">
                            <li><i class="bi bi-x text-danger me-2"></i>El registro del pago será eliminado permanentemente</li>
                            <li><i class="bi bi-check text-success me-2"></i>El saldo restante se ajustará automáticamente</li>
                        </ul>
                    `;
                    nuevoSaldo = saldoActual + monto;
                    break;
                    
                default:
                    contenidoInfo = `
                        <h6><i class="bi bi-question-circle text-muted me-2"></i>Tipo de Pago: ${tipoPago}</h6>
                        <p>Se realizará el proceso estándar de deshacimiento.</p>
                    `;
                    nuevoSaldo = saldoActual + monto;
            }
            
            modalContentInfo.innerHTML = contenidoInfo;
            nuevoSaldoElement.textContent = '$' + nuevoSaldo.toLocaleString();
            
            // Almacenar el ID del pago en el botón de confirmación
            confirmarDeshacerBtn.dataset.pagoId = pagoId;
            
            modalDeshacerPago.show();
        });
        
        confirmarDeshacerBtn.addEventListener('click', function() {
            const pagoId = this.dataset.pagoId;
            
            // Mostrar loading y mensaje de procesamiento
            loadingDeshacerIcon.classList.remove('d-none');
            this.disabled = true;
            processingMessage.classList.remove('d-none');
            processingText.textContent = 'Deshaciendo el pago y actualizando el contrato...';
            
            // Realizar petición AJAX
            fetch(`/pagos/${pagoId}/deshacer`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Actualizar mensaje de procesamiento
                    processingText.textContent = 'Pago deshecho exitosamente. Redirigiendo al contrato...';
                    
                    showAlert('success', data.message + ' Redirigiendo al contrato...');
                    
                    // Mostrar loading en el botón principal también
                    if (loadingDeshacerBtn) {
                        loadingDeshacerBtn.classList.remove('d-none');
                        btnDeshacerPago.disabled = true;
                    }
                    
                    // Redirigir al contrato después de un breve delay
                    setTimeout(() => {
                        @if($pago->contrato_id)
                            window.location.href = '{{ route("contratos.show", $pago->contrato_id) }}';
                        @else
                            window.location.href = '{{ route("pagos.index") }}';
                        @endif
                    }, 1500);
                } else {
                    showAlert('error', data.message || 'Error al deshacer el pago');
                    // Ocultar mensaje de procesamiento en caso de error
                    processingMessage.classList.add('d-none');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                
                let errorMessage = 'Error de conexión al deshacer el pago';
                if (error.message.includes('422')) {
                    errorMessage = 'Datos no válidos para deshacer el pago';
                } else if (error.message.includes('404')) {
                    errorMessage = 'Pago no encontrado';
                } else if (error.message.includes('403')) {
                    errorMessage = 'No tienes permisos para realizar esta acción';
                }
                
                showAlert('error', errorMessage);
                // Ocultar mensaje de procesamiento en caso de error
                processingMessage.classList.add('d-none');
            })
            .finally(() => {
                // Solo restaurar el estado de los botones si no fue exitoso (no redirigimos)
                if (!document.querySelector('.alert-success')) {
                    loadingDeshacerIcon.classList.add('d-none');
                    this.disabled = false;
                }
            });
        });
    }
    
    // Resetear el modal cuando se cierre
    document.getElementById('modalDeshacerPago').addEventListener('hidden.bs.modal', function () {
        if (processingMessage) {
            processingMessage.classList.add('d-none');
        }
        if (confirmarDeshacerBtn) {
            confirmarDeshacerBtn.disabled = false;
        }
        if (loadingDeshacerIcon) {
            loadingDeshacerIcon.classList.add('d-none');
        }
    });
});

// Funciones para manejar documentos
function openDocumentModal(url, fileName, type) {
    const modal = new bootstrap.Modal(document.getElementById('documentModal'));
    const documentTitle = document.getElementById('documentTitle');
    const documentContent = document.getElementById('documentContent');
    const downloadBtn = document.getElementById('downloadDocumentBtn');
    
    // Configurar título y botón de descarga
    documentTitle.textContent = fileName;
    downloadBtn.href = url;
    downloadBtn.download = fileName;
    
    // Mostrar spinner de carga
    documentContent.innerHTML = '<div class="loading-spinner"></div><p class="mt-3 text-muted">Cargando documento...</p>';
    
    // Abrir modal
    modal.show();
    
    // Cargar contenido según el tipo
    setTimeout(() => {
        switch(type) {
            case 'image':
                documentContent.innerHTML = `
                    <img src="${url}" alt="${fileName}" onload="this.style.opacity=1" style="opacity:0; transition: opacity 0.3s ease;">
                `;
                break;
                
            case 'pdf':
                documentContent.innerHTML = `
                    <iframe src="${url}" title="${fileName}" onload="this.style.opacity=1" style="opacity:0; transition: opacity 0.3s ease;">
                        <p>Su navegador no soporta la visualización de PDFs. 
                           <a href="${url}" target="_blank">Haga clic aquí para descargar el archivo</a>.
                        </p>
                    </iframe>
                `;
                break;
                
            default:
                documentContent.innerHTML = `
                    <div class="text-center py-5">
                        <i class="bi bi-file-earmark-fill file-icon"></i>
                        <h5 class="mb-3">${fileName}</h5>
                        <p class="text-muted mb-4">Este tipo de archivo no se puede visualizar en el navegador.</p>
                        <a href="${url}" class="btn btn-primary btn-lg" download="${fileName}">
                            <i class="bi bi-download me-2"></i>Descargar Archivo
                        </a>
                    </div>
                `;
        }
    }, 500);
}

// Función helper para formatear bytes (si no existe)
function formatBytes(bytes, decimals = 2) {
    if (bytes === 0) return '0 Bytes';
    
    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
    
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    
    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
}

// Variables globales para el manejo de archivos
let selectedFile = null;
const pagoId = {{ $pago->id }};

// Configurar eventos de drag & drop
document.addEventListener('DOMContentLoaded', function() {
    const dropZone = document.getElementById('dropZone');
    const documentInput = document.getElementById('documentInput');
    
    if (dropZone && documentInput) {
        // Eventos de drag & drop
        dropZone.addEventListener('dragover', handleDragOver);
        dropZone.addEventListener('dragleave', handleDragLeave);
        dropZone.addEventListener('drop', handleDrop);
        dropZone.addEventListener('click', () => documentInput.click());
        
        // Evento de selección de archivo
        documentInput.addEventListener('change', handleFileSelect);
    }
});

function handleDragOver(e) {
    e.preventDefault();
    e.stopPropagation();
    e.currentTarget.classList.add('drag-over');
}

function handleDragLeave(e) {
    e.preventDefault();
    e.stopPropagation();
    e.currentTarget.classList.remove('drag-over');
}

function handleDrop(e) {
    e.preventDefault();
    e.stopPropagation();
    e.currentTarget.classList.remove('drag-over');
    
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        processFile(files[0]);
    }
}

function handleFileSelect(e) {
    const files = e.target.files;
    if (files.length > 0) {
        processFile(files[0]);
    }
}

function processFile(file) {
    // Validar tipo de archivo
    const allowedTypes = [
        'application/pdf',
        'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/bmp', 'image/webp',
        'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    ];
    
    if (!allowedTypes.includes(file.type)) {
        showAlert('error', 'Tipo de archivo no permitido. Por favor, selecciona un archivo PDF, imagen, Word o Excel.');
        return;
    }
    
    // Validar tamaño (10MB máximo)
    if (file.size > 10 * 1024 * 1024) {
        showAlert('error', 'El archivo es demasiado grande. El tamaño máximo permitido es 10 MB.');
        return;
    }
    
    selectedFile = file;
    showFilePreview(file);
}

function showFilePreview(file) {
    const preview = document.getElementById('filePreview');
    const dropZone = document.getElementById('dropZone');
    const thumbnail = document.getElementById('previewThumbnail');
    const fileName = document.getElementById('previewFileName');
    const fileInfo = document.getElementById('previewFileInfo');
    
    // Limpiar miniatura anterior
    thumbnail.innerHTML = '';
    
    // Determinar tipo de archivo
    const extension = file.name.split('.').pop().toLowerCase();
    const isImage = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'].includes(extension);
    const isPDF = extension === 'pdf';
    
    // Crear miniatura
    if (isImage) {
        const reader = new FileReader();
        reader.onload = function(e) {
            thumbnail.innerHTML = `
                <div class="preview-thumbnail">
                    <img src="${e.target.result}" alt="Vista previa">
                </div>
            `;
        };
        reader.readAsDataURL(file);
    } else if (isPDF) {
        thumbnail.innerHTML = `
            <div class="preview-thumbnail pdf">
                <i class="bi bi-file-earmark-pdf-fill" style="font-size: 20px;"></i>
            </div>
        `;
    } else {
        thumbnail.innerHTML = `
            <div class="preview-thumbnail file">
                <i class="bi bi-file-earmark-fill" style="font-size: 20px;"></i>
            </div>
        `;
    }
    
    // Mostrar información del archivo
    fileName.textContent = file.name;
    fileInfo.innerHTML = `
        <i class="bi bi-file-earmark me-1"></i>
        ${extension.toUpperCase()} • ${formatBytes(file.size)}
    `;
    
    // Ocultar área de drag & drop y mostrar vista previa
    dropZone.classList.add('d-none');
    preview.classList.remove('d-none');
}

function cancelUpload() {
    selectedFile = null;
    const preview = document.getElementById('filePreview');
    const dropZone = document.getElementById('dropZone');
    
    // Ocultar vista previa y mostrar área de drag & drop
    preview.classList.add('d-none');
    dropZone.classList.remove('d-none');
    document.getElementById('documentInput').value = '';
}

function uploadDocument() {
    if (!selectedFile) {
        showAlert('error', 'No hay archivo seleccionado');
        return;
    }
    
    const formData = new FormData();
    formData.append('documento', selectedFile);
    formData.append('_token', '{{ csrf_token() }}');
    
    // Mostrar progreso y deshabilitar botón
    const uploadProgress = document.getElementById('uploadProgress');
    const progressBar = document.getElementById('progressBar');
    const uploadBtn = document.querySelector('button[onclick="uploadDocument()"]');
    
    uploadProgress.classList.remove('d-none');
    progressBar.style.width = '0%';
    
    if (uploadBtn) {
        uploadBtn.disabled = true;
        uploadBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Subiendo...';
    }
    
    // Simular progreso
    let progress = 0;
    const progressInterval = setInterval(() => {
        progress += Math.random() * 15;
        if (progress > 90) progress = 90;
        progressBar.style.width = progress + '%';
    }, 200);
    
    // Realizar upload
    fetch(`/pagos/${pagoId}/upload-document`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        // Completar barra de progreso
        clearInterval(progressInterval);
        progressBar.style.width = '100%';
        
        if (data.success) {
            // Mostrar mensaje de éxito
            showAlert('success', data.message || 'Documento subido exitosamente');
            
            // Cambiar botón a estado de éxito
            if (uploadBtn) {
                uploadBtn.innerHTML = '<i class="bi bi-check-circle me-1"></i>¡Subido!';
                uploadBtn.classList.remove('btn-success');
                uploadBtn.classList.add('btn-success');
            }
            
            // Recargar la página después de mostrar el éxito
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            throw new Error(data.message || 'Error al subir el archivo');
        }
    })
    .catch(error => {
        console.error('Error al subir documento:', error);
        clearInterval(progressInterval);
        
        // Mostrar error específico
        let errorMessage = 'Error de conexión al subir el archivo';
        if (error.message.includes('422')) {
            errorMessage = 'El archivo no es válido o es demasiado grande';
        } else if (error.message.includes('413')) {
            errorMessage = 'El archivo es demasiado grande';
        } else if (error.message.includes('404')) {
            errorMessage = 'Pago no encontrado';
        } else if (error.message.includes('403')) {
            errorMessage = 'No tienes permisos para subir archivos';
        } else if (error.message) {
            errorMessage = error.message;
        }
        
        showAlert('error', errorMessage);
        
        // En caso de error, restaurar el estado inicial
        const preview = document.getElementById('filePreview');
        const dropZone = document.getElementById('dropZone');
        preview.classList.add('d-none');
        dropZone.classList.remove('d-none');
        selectedFile = null;
        document.getElementById('documentInput').value = '';
    })
    .finally(() => {
        // Ocultar progreso y restaurar botón
        setTimeout(() => {
            uploadProgress.classList.add('d-none');
            progressBar.style.width = '0%';
            
            if (uploadBtn) {
                uploadBtn.disabled = false;
                uploadBtn.innerHTML = '<i class="bi bi-upload me-1"></i>Subir';
                uploadBtn.classList.remove('btn-success');
            }
        }, 2000);
    });
}

function eliminarDocumento(pagoId) {
    // Crear modal de confirmación personalizado
    const confirmResult = confirm('¿Estás seguro de que deseas eliminar este documento? Esta acción no se puede deshacer.');
    
    if (confirmResult) {
        // Obtener el botón de eliminar para mostrar loading
        const deleteBtn = document.querySelector(`button[onclick="eliminarDocumento(${pagoId})"]`);
        const originalBtnContent = deleteBtn ? deleteBtn.innerHTML : '';
        
        // Mostrar loading en el botón
        if (deleteBtn) {
            deleteBtn.disabled = true;
            deleteBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Eliminando...';
            deleteBtn.classList.add('btn-loading');
        }
        
        // Realizar petición de eliminación
        fetch(`/pagos/${pagoId}/delete-documento`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Cambiar botón a estado de éxito
                if (deleteBtn) {
                    deleteBtn.innerHTML = '<i class="bi bi-check-circle me-1"></i> ¡Eliminado!';
                    deleteBtn.classList.remove('btn-outline-danger');
                    deleteBtn.classList.add('btn-success');
                }
                
                // Mostrar mensaje de éxito
                showAlert('success', data.message || 'Documento eliminado exitosamente');
                
                // Recargar la página después de mostrar el éxito
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                throw new Error(data.message || 'Error al eliminar el documento');
            }
        })
        .catch(error => {
            console.error('Error al eliminar documento:', error);
            
            // Mostrar error específico
            let errorMessage = 'Error de conexión al eliminar el documento';
            if (error.message.includes('422')) {
                errorMessage = 'No se puede eliminar el documento';
            } else if (error.message.includes('404')) {
                errorMessage = 'Documento o pago no encontrado';
            } else if (error.message.includes('403')) {
                errorMessage = 'No tienes permisos para eliminar este documento';
            } else if (error.message) {
                errorMessage = error.message;
            }
            
            showAlert('error', errorMessage);
            
            // Restaurar botón en caso de error
            if (deleteBtn) {
                deleteBtn.disabled = false;
                deleteBtn.innerHTML = originalBtnContent;
                deleteBtn.classList.remove('btn-loading', 'btn-success');
                deleteBtn.classList.add('btn-outline-danger');
            }
        });
    }
}
</script>

@endif
@endsection
