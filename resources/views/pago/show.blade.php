@extends('layouts.app')

@section('template_title')
    {{ isset($pago) ? ($pago->name ?? __('Show') . " " . __('Pago')) : __('Pago no encontrado') }}
@endsection

@section('content')
    <style>
        /* ===== ESTILOS DEL TICKET (VISUALIZACIÓN Y IMPRESIÓN) ===== */
        .ticket-visual {
            width: 58mm !important;
            max-width: 58mm !important;
            margin: 0 auto !important;
            padding: 4mm !important;
            background: white;
            font-size: 11px !important;
            font-family: 'Courier New', monospace !important;
            line-height: 1.2 !important;
            color: #000 !important;
        }

        /* ELEMENTOS DEL TICKET */
        .ticket-visual img.logo-ticket {
            max-width: 40mm !important;
            height: auto !important;
            display: block !important;
            margin: 0 auto 2mm auto !important;
        }

        .ticket-visual h2,
        .ticket-visual h3,
        .ticket-visual h4,
        .ticket-visual h5,
        .ticket-visual h6 {
            font-family: 'Courier New', monospace !important;
            color: #000 !important;
            margin: 0 0 1mm 0 !important;
        }

        .ticket-visual .ticket-header {
            text-align: center !important;
            border-bottom: 1px dashed #000 !important;
            padding-bottom: 2mm !important;
            margin-bottom: 2mm !important;
        }

        .ticket-visual .ticket-body {
            /* Sin padding extra */
        }

        .ticket-visual .ticket-row {
            display: flex !important;
            justify-content: space-between !important;
            margin-bottom: 1mm !important;
        }

        .ticket-visual .ticket-label {
            font-weight: normal !important;
        }

        .ticket-visual .ticket-value {
            font-weight: bold !important;
            text-align: right !important;
        }

        .ticket-visual .ticket-divider {
            border-bottom: 1px dashed #000 !important;
            margin: 2mm 0 !important;
        }

        .ticket-visual .ticket-footer {
            text-align: center !important;
            margin-top: 4mm !important;
            font-size: 10px !important;
        }

        /* OCULTAR ELEMENTOS QUE NO SON DEL TICKET */
        .ticket-visual .d-print-none {
            display: none !important;
        }

        /* ESTILOS DE IMPRESIÓN */
        @media print {
            @page {
                size: 58mm auto !important;
                margin: 0mm !important;
            }

            body {
                visibility: hidden !important;
                background: white !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .ticket-visual {
                visibility: visible !important;
                position: absolute !important;
                left: 0 !important;
                top: 0 !important;
                width: 58mm !important;
                max-width: 58mm !important;
                padding: 2mm !important;
                margin: 0 !important;
                box-shadow: none !important;
                display: block !important;
            }

            .ticket-visual * {
                visibility: visible !important;
            }

            .admin-column,
            .d-print-none {
                display: none !important;
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
            <section class="d-flex justify-content-center py-4 bg-light">
                <div class="ticket-container-wrapper d-flex flex-wrap justify-content-center gap-4 p-3"
                    style="max-width: 1200px; width: 100%;">

                    @php
                        // Pre-calculation of variables
                        $cliente = $pago->contrato ? $pago->contrato->cliente : null;
                        $empresa = \App\Models\Ajuste::obtenerInfoEmpresa();

                        if ($pago->contrato) {
                            $cuotasPagadas = $pago->contrato->pagos->filter(function ($pagoContrato) {
                                return $pagoContrato->estado == 'hecho' &&
                                    strtolower($pagoContrato->tipo_pago ?? '') == 'cuota';
                            })->count();

                            $totalCuotas = $pago->contrato->numero_cuotas ?? 0;
                            $porcentajeCuotas = $totalCuotas > 0 ? ($cuotasPagadas / $totalCuotas) * 100 : 0;

                            // Use helper to calculate actual paid amount, consistent with Contract View
                            $montoTotalPagado = calcularMontoPagadoContrato($pago->contrato->pagos);
                            $montoRestante = $pago->contrato->monto_total - $montoTotalPagado;

                            $progreso = ($montoTotalPagado / $pago->contrato->monto_total) * 100;
                        } else {
                            $montoRestante = 0;
                            $montoTotalPagado = 0;
                            $progreso = 0;
                        }
                    @endphp

                    <!-- COLUMNA IZQUIERDA: TICKET (58mm) -->
                    <div>
                        <div class="mb-2 d-print-none text-center">
                            <small class="text-muted"><i class="bi bi-printer me-1"></i>Vista Previa del Ticket</small>
                        </div>
                        <div class="ticket-visual">
                            <!-- Header Info -->
                            <div class="small ticket-header-info text-center">
                                Emitido: {{ \Carbon\Carbon::now()->format('d/m/y H:i') }}<br>
                                Por: {{ auth()->user()->name ?? 'Sistema' }}
                            </div>

                            <!-- Logo -->
                            <img src="{{ asset('shalom_logo.svg') }}" alt="Logo" class="logo-ticket">

                            <div class="small mt-1 text-center" style="font-size: 8px;">
                                {{ $empresa['razon_social'] }}<br>
                                {{ $empresa['calle_numero'] }} {{ $empresa['colonia'] }}<br>
                                {{ $empresa['ciudad'] }}, {{ $empresa['estado'] }} CP: {{ $empresa['codigo_postal'] }}<br>
                                Tel: {{ $empresa['telefono'] }}
                            </div>
                            <div class="mt-2 ticket-divider"></div>
                            <!-- Title -->
                            <div class="ticket-header">
                                <h6 class="fw-bold fs-6 mb-1">
                                    @if($pago->tipo_pago === 'cuota' && $pago->numero_cuota)
                                        PAGO CUOTA #{{ $pago->numero_cuota }}
                                    @else
                                        RECIBO DE PAGO
                                    @endif
                                </h6>
                                <div class="small">Folio: #{{ str_pad($pago->numero_pago ?? $pago->id ?? 0, 6, '0', STR_PAD_LEFT) }}
                                </div>
                            </div>

                            <!-- Body -->
                            <div class="ticket-body">
                                <!-- Amount -->
                                <h4 class="text-center fw-bold my-3" style="font-family: 'Courier New'; font-size: 16px;">
                                    ${{ number_format($pago->monto, 2) }}
                                </h4>

                                <div class="ticket-row">

                                </div>
                                <!--  -->
                                <div class="ticket-row">
                                    <span class="ticket-label">Método de pago:</span>
                                    <span id="ticket-metodo-pago-value"
                                        class="ticket-value">{{ \App\Models\Pago::METODOS_PAGO[$pago->metodo_pago] ?? $pago->metodo_pago }}</span>
                                </div>

                                <!-- Info Rows -->
                                <div class="ticket-row">
                                    <span class="ticket-label">Fecha:</span>
                                    <span class="ticket-value">{{ $pago->created_at->format('d/m/Y') }}</span>
                                </div>


                                <!-- Cliente Info en Ticket (Breve) -->
                                @if($cliente)
                                    <div class="ticket-divider"></div>
                                    <div class="ticket-row">
                                        <span class="ticket-label">Cliente:</span>
                                        <span class="ticket-value" style="text-align: right; max-width: 60%;">
                                            {{ Str::limit($cliente->nombre . ' ' . $cliente->apellido, 20) }}
                                        </span>
                                    </div>
                                @endif

                                <div class="ticket-divider"></div>

                                <!-- Financial Summary -->
                                @if($pago->contrato)
                                    <div class="ticket-row">
                                        <span class="ticket-label">Total Contrato:</span>
                                        <span class="ticket-value">${{ number_format($pago->contrato->monto_total, 2) }}</span>
                                    </div>
                                    <div class="ticket-row">
                                        <span class="ticket-label">Abonado:</span>
                                        <span class="ticket-value">${{ number_format($montoTotalPagado, 2) }}</span>
                                    </div>
                                    <div class="ticket-row">
                                        <span class="ticket-label">Restante:</span>
                                        <span class="ticket-value">${{ number_format($montoRestante, 2) }}</span>
                                    </div>
                                @endif

                                @if($pago->observaciones)
                                    <div class="ticket-divider"></div>
                                    <div class="text-center small mt-1">
                                        "{{ Str::limit($pago->observaciones, 50) }}"
                                    </div>
                                @endif
                            </div>

                            <!-- Footer -->
                            <div class="ticket-footer">
                                <br>
                                <div style="border-bottom: 1px solid #000; width: 80%; margin: 0 auto 5px auto;"></div>
                                <div class="small">FIRMA DE RECIBIDO</div>
                                <br>
                                ¡GRACIAS POR SU PAGO!
                            </div>
                        </div>
                    </div>


                    <!-- COLUMNA DERECHA: ADMIN & INFO -->
                    <div class="admin-column d-print-none d-flex flex-column gap-3"
                        style="flex: 1; min-width: 300px; max-width: 500px;">

                        <!-- Navegación -->
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-arrow-left me-1"></i> Regresar
                            </a>
                            <button class="btn btn-secondary" onclick="window.print()">
                                <i class="fas fa-print me-1"></i> Imprimir Ticket
                            </button>
                        </div>

                        <!-- Panel de Cliente -->
                        @if($cliente)
                            <div class="card border shadow-sm">
                                <div class="card-header bg-white fw-bold">
                                    <i class="bi bi-person-circle me-2 text-primary"></i>Información del Cliente
                                </div>
                                <div class="card-body">
                                    <h5 class="card-title">{{ $cliente->nombre }} {{ $cliente->apellido }}</h5>
                                    <p class="card-text small text-muted">
                                        @if($cliente->email) <i class="bi bi-envelope me-1"></i> {{ $cliente->email }}<br> @endif
                                        @if($cliente->telefono) <i class="bi bi-telephone me-1"></i> {{ $cliente->telefono }}<br> @endif
                                    </p>
                                    @if($pago->contrato)
                                        <a href="{{ route('contratos.show', $pago->contrato->id) }}"
                                            class="btn btn-sm btn-outline-primary w-100">
                                            <i class="bi bi-file-text me-1"></i> Ver Contrato
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <form action="{{ route('pagos.update', $pago->id) }}" method="POST" enctype="multipart/form-data"
                            id="form-update-pago">
                            @csrf
                            @method('PATCH')

                            <!-- Campos ocultos requeridos por validación -->
                            <input type="hidden" name="contrato_id" value="{{ $pago->contrato_id }}">
                            <input type="hidden" name="monto" value="{{ $pago->monto }}">
                            <input type="hidden" name="fecha_pago"
                                value="{{ $pago->fecha_pago ? $pago->fecha_pago->format('Y-m-d\TH:i') : $pago->created_at->format('Y-m-d\TH:i') }}">
                            <input type="hidden" name="estado" value="{{ $pago->estado }}">
                            @if($pago->observaciones)
                                <input type="hidden" name="observaciones" value="{{ $pago->observaciones }}">
                            @endif

                            <!-- Panel de Documentos -->
                            <div class="card border shadow-sm mb-3">
                                <div class="card-header bg-white fw-bold">
                                    <i class="bi bi-paperclip me-2 text-secondary"></i>Comprobante / Documento
                                </div>
                                <div class="card-body">
                                    @if($pago->documento)
                                        @php
                                            $extension = strtolower(pathinfo($pago->documento, PATHINFO_EXTENSION));
                                            $documentoUrl = \Illuminate\Support\Facades\Storage::url($pago->documento);
                                            $fileName = basename($pago->documento);
                                            $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp']);
                                        @endphp
                                        <div class="d-flex align-items-center p-2 border rounded mb-3">
                                            <div class="me-3">
                                                @if($isImage)
                                                    <img src="{{ $documentoUrl }}" alt="Preview" class="rounded border"
                                                        style="width: 40px; height: 40px; object-fit: cover;">
                                                @elseif($extension === 'pdf')
                                                    <div class="d-flex align-items-center justify-content-center bg-danger text-white rounded"
                                                        style="width: 40px; height: 40px;"><i class="bi bi-file-earmark-pdf"></i></div>
                                                @elseif(in_array($extension, ['doc', 'docx']))
                                                    <div class="d-flex align-items-center justify-content-center bg-primary text-white rounded"
                                                        style="width: 40px; height: 40px;"><i class="bi bi-file-earmark-word"></i></div>
                                                @elseif(in_array($extension, ['xls', 'xlsx']))
                                                    <div class="d-flex align-items-center justify-content-center bg-success text-white rounded"
                                                        style="width: 40px; height: 40px;"><i class="bi bi-file-earmark-excel"></i></div>
                                                @else
                                                    <div class="d-flex align-items-center justify-content-center bg-secondary text-white rounded"
                                                        style="width: 40px; height: 40px;"><i class="bi bi-file-earmark"></i></div>
                                                @endif
                                            </div>
                                            <div class="flex-grow-1 overflow-hidden">
                                                <div class="fw-bold text-truncate" title="{{ $fileName }}">{{ $fileName }}</div>
                                                <div class="small text-muted">{{ strtoupper($extension) }}</div>
                                            </div>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-sm btn-outline-primary flex-grow-1"
                                                onclick="openDocumentModal('{{ $documentoUrl }}', '{{ $fileName }}', '{{ $isImage ? 'image' : ($extension == 'pdf' ? 'pdf' : 'file') }}')">
                                                <i class="bi bi-eye"></i> Ver
                                            </button>
                                            <a href="{{ $documentoUrl }}" class="btn btn-sm btn-outline-secondary flex-grow-1" download>
                                                <i class="bi bi-download"></i> Descargar
                                            </a>
                                            <!-- Usamos type="button" para evitar submit -->
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                onclick="eliminarDocumento({{ $pago->id }})">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    @else
                                        <div id="dropZone" class="border border-dashed rounded p-3 text-center bg-light"
                                            style="cursor: pointer;" onclick="document.getElementById('documentInput').click()">
                                            <!-- Agregamos name="documento" al input -->
                                            <input type="file" id="documentInput" name="documento" class="d-none"
                                                accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx" onclick="event.stopPropagation()">
                                            <i class="bi bi-cloud-upload fs-3 text-muted"></i>
                                            <p class="small text-muted mb-0">Clic o arrastra para subir comprobante</p>
                                        </div>

                                        <!-- Vista previa del archivo seleccionado -->
                                        <div id="filePreview" class="d-none mt-2">
                                            <div class="d-flex justify-content-between align-items-center p-2 bg-white border rounded">
                                                <small id="previewFileName" class="text-truncate" style="max-width: 150px;"></small>
                                                <div>
                                                    <!-- Botón para cancelar la selección (limpiar input) -->
                                                    <button type="button" class="btn btn-xs btn-outline-secondary py-0"
                                                        onclick="cancelUpload()">X</button>
                                                </div>
                                            </div>
                                            <div id="previewThumbnail" class="d-none"></div>
                                            <div id="previewFileInfo" class="d-none"></div>
                                            <div class="text-success small mt-1"><i class="bi bi-check-circle"></i> Archivo listo para
                                                guardar</div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Método de Pago -->
                            @if(strtolower($pago->estado) === 'hecho')
                                <div class="card border shadow-sm mb-3">
                                    <div class="card-header bg-white fw-bold">
                                        <i class="bi bi-credit-card me-2 text-dark"></i>Método de Pago
                                    </div>
                                    <div class="card-body">
                                        <div class="d-flex gap-2">
                                            <!-- Agregamos name="metodo_pago" -->
                                            <select id="metodo_pago_select" name="metodo_pago" class="form-select form-select-sm">
                                                @foreach(\App\Models\Pago::METODOS_PAGO as $key => $label)
                                                    <option value="{{ $key }}" {{ $pago->metodo_pago == $key ? 'selected' : '' }}>
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <input type="hidden" name="metodo_pago" value="{{ $pago->metodo_pago }}">
                            @endif

                            <!-- Acciones Peligrosas -->
                            @if(strtolower($pago->estado) === 'hecho')
                                <div class="card border border-seconda ry shadow-sm mb-3">
                                    <div class="card-body">
                                        <h6 class="text-warning fw-bold"><i class="bi bi-exclamation-triangle me-1"></i>Correcciones
                                        </h6>
                                        <p class="small text-muted mb-2" style="font-size: 12px;">Si hubo un error, puedes deshacer este
                                            pago. Esto recalculará el
                                            saldo del contrato.</p>
                                        <!-- AÑADIDO: type="button" para evitar submit del formulario -->
                                        <button type="button" class="btn border-warning btn-sm w-100" id="btnDeshacerPago"
                                            data-pago-id="{{ $pago->id }}" data-tipo-pago="{{ $pago->tipo_pago }}"
                                            data-monto="{{ $pago->monto }}">
                                            <i class="bi bi-arrow-counterclockwise"></i> Deshacer Pago
                                        </button>
                                    </div>
                                </div>
                            @endif

                            <div>
                                <button type="submit" class="btn btn-primary w-100">
                                    Guardar cambios
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </section>

            <!-- Modal de Confirmación para Deshacer Pago -->
            <div class="modal fade" id="modalDeshacerPago" tabindex="-1" aria-labelledby="modalDeshacerPagoLabel"
                aria-hidden="true">
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
                                        <div class="small" id="processingText">Deshaciendo el pago y actualizando el contrato...
                                        </div>
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
                                <span id="loadingDeshacerIcon" class="spinner-border spinner-border-sm me-2 d-none"
                                    role="status"></span>
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

                    .btn,
                    .d-print-none {
                        display: none !important;
                    }

                    .shadow-lg {
                        box-shadow: none !important;
                    }

                    body {
                        font-size: 12px;
                    }

                    /* En modo impresión, ocultar el select y mostrar solo el texto */
                    #metodo_pago_select,
                    .bi-pencil-square,
                    #loading_metodo {
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
                    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
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
                    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
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
                    border: 1px solid rgba(0, 0, 0, 0.05) !important;
                }

                .hover-card:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15) !important;
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

                #metodo_pago_select:hover+.bi-pencil-square {
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
                    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
                }

                .document-thumbnail:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
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
                    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
                }

                #documentModal .modal-body {
                    background: #f8f9fa;
                }

                #documentContent img {
                    max-width: 100%;
                    max-height: 70vh;
                    border-radius: 8px;
                    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
                }

                #documentContent iframe {
                    width: 100%;
                    height: 70vh;
                    border: none;
                    border-radius: 8px;
                    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
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
                    0% {
                        transform: rotate(0deg);
                    }

                    100% {
                        transform: rotate(360deg);
                    }
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
                    0% {
                        transform: scale(1);
                    }

                    50% {
                        transform: scale(1.05);
                        background-color: #d4edda;
                    }

                    100% {
                        transform: scale(1);
                    }
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
                document.addEventListener('DOMContentLoaded', function () {
                    // Listener para metodo_pago_select removido. Se maneja por submit del form.

                    // Funcionalidad para deshacer pagos

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
                        btnDeshacerPago.addEventListener('click', function () {
                            const tipoPago = this.dataset.tipoPago;
                            const monto = parseFloat(this.dataset.monto);
                            const saldoActual = {{ $pago->saldo_restante ?? 0 }};
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

                        confirmarDeshacerBtn.addEventListener('click', function () {
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
                        switch (type) {
                            case 'image':
                                documentContent.innerHTML = `
                                                    <div class="text-center">
                                                        <img src="${url}" alt="${fileName}" class="img-fluid rounded shadow-sm" style="max-height: 70vh;">
                                                    </div>
                                                `;
                                break;

                            case 'pdf':
                                documentContent.innerHTML = `
                                            <iframe src="${url}" title="${fileName}" style="width:100%; height:70vh; border:none;">
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
                document.addEventListener('DOMContentLoaded', function () {
                    const dropZone = document.getElementById('dropZone');
                    const documentInput = document.getElementById('documentInput');

                    if (dropZone && documentInput) {
                        // Eventos de drag & drop
                        dropZone.addEventListener('dragover', handleDragOver);
                        dropZone.addEventListener('dragleave', handleDragLeave);
                        dropZone.addEventListener('drop', handleDrop);

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
                        processFile(files[0], true); // true = actualizar input
                    }
                }

                function handleFileSelect(e) {
                    const files = e.target.files;
                    if (files.length > 0) {
                        processFile(files[0], false); // false = no update input (ya viene del input)
                    }
                }

                function processFile(file, updateInput = false) {
                    if (!file) return;

                    // Validar tipo de archivo
                    // Tipos MIME permitidos
                    const allowedTypes = [
                        'application/pdf',
                        'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/bmp', 'image/webp',
                        'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                    ];

                    // Check extension as fallback if type is empty or generic
                    const extension = file.name.split('.').pop().toLowerCase();
                    const allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'doc', 'docx', 'xls', 'xlsx'];

                    if (!allowedTypes.includes(file.type) && !allowedExtensions.includes(extension)) {
                        showAlert('error', 'Tipo de archivo no permitido. Por favor, selecciona un archivo PDF, imagen, Word o Excel.');
                        return;
                    }

                    // Validar tamaño (10MB máximo)
                    if (file.size > 10 * 1024 * 1024) {
                        showAlert('error', 'El archivo es demasiado grande. El tamaño máximo permitido es 10 MB.');
                        return;
                    }

                    selectedFile = file;

                    // Actualizar input solo si viene de drag & drop
                    if (updateInput) {
                        try {
                            const dataTransfer = new DataTransfer();
                            dataTransfer.items.add(file);
                            document.getElementById('documentInput').files = dataTransfer.files;
                        } catch (e) {
                            console.error('Error al asignar archivo al input:', e);
                        }
                    }

                    showFilePreview(file);
                }

                function showFilePreview(file) {
                    try {
                        const preview = document.getElementById('filePreview');
                        const dropZone = document.getElementById('dropZone');

                        if (!preview || !dropZone) {
                            console.error('CRITICAL: Elements not found', { preview, dropZone });
                            return;
                        }

                        // Generar contenido HTML basado en el diseño solicitado
                        const extension = file.name.split('.').pop().toLowerCase();
                        const isImage = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'].includes(extension);

                        let thumbnailHtml = '';

                        if (isImage) {
                            const objectUrl = URL.createObjectURL(file);
                            thumbnailHtml = `<img src="${objectUrl}" alt="Preview" class="rounded" style="width: 40px; height: 40px; object-fit: cover;">`;
                        } else if (extension === 'pdf') {
                            thumbnailHtml = `<div class="d-flex align-items-center justify-content-center bg-danger text-white rounded" style="width: 40px; height: 40px;"><i class="bi bi-file-earmark-pdf"></i></div>`;
                        } else if (['doc', 'docx'].includes(extension)) {
                            thumbnailHtml = `<div class="d-flex align-items-center justify-content-center bg-primary text-white rounded" style="width: 40px; height: 40px;"><i class="bi bi-file-earmark-word"></i></div>`;
                        } else if (['xls', 'xlsx'].includes(extension)) {
                            thumbnailHtml = `<div class="d-flex align-items-center justify-content-center bg-success text-white rounded" style="width: 40px; height: 40px;"><i class="bi bi-file-earmark-excel"></i></div>`;
                        } else {
                            thumbnailHtml = `<div class="d-flex align-items-center justify-content-center bg-secondary text-white rounded" style="width: 40px; height: 40px;"><i class="bi bi-file-earmark"></i></div>`;
                        }

                        // Obtener fecha actual formateada
                        const now = new Date();
                        const dateStr = now.toLocaleDateString() + ' ' + now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                        const fileSize = formatBytes(file.size);

                        preview.innerHTML = `
                                                                                            <div class="d-flex align-items-center p-2 border rounded bg-white">
                                                                                                <div class="me-3">
                                                                                                    ${thumbnailHtml}
                                                                                                </div>
                                                                                                <div class="flex-grow-1 overflow-hidden">
                                                                                                    <div class="d-flex justify-content-between align-items-center">
                                                                                                        <span class="fw-bold text-truncate" title="${file.name}">${file.name}</span>
                                                                                                        <a href="#" onclick="replaceDocument(event)" class="text-primary small text-decoration-none ms-2">Reemplazar</a>
                                                                                                    </div>
                                                                                                    <div class="small text-muted d-flex gap-3">
                                                                                                        <span>${fileSize}</span>
                                                                                                        <span>${dateStr}</span>
                                                                                                    </div>
                                                                                                </div>
                                                                                                <div class="ms-2">
                                                                                                    <button type="button" class="btn btn-sm text-secondary" onclick="cancelUpload()">
                                                                                                        <i class="bi bi-x-lg"></i>
                                                                                                    </button>
                                                                                                </div>
                                                                                            </div>
                                                                                        `;

                        // Ocultar área de drag & drop y mostrar vista previa
                        dropZone.classList.add('d-none');
                        preview.classList.remove('d-none');
                    } catch (error) {
                        console.error('Error in showFilePreview:', error);
                        alert('Error showing preview: ' + error.message);
                    }
                }

                function replaceDocument(e) {
                    if (e) e.preventDefault();
                    document.getElementById('documentInput').click();
                }

                function cancelUpload() {
                    selectedFile = null;
                    const preview = document.getElementById('filePreview');
                    const dropZone = document.getElementById('dropZone');

                    // Ocultar vista previa y mostrar área de drag & drop
                    preview.classList.add('d-none');
                    dropZone.classList.remove('d-none');
                    document.getElementById('documentInput').value = '';
                    preview.innerHTML = ''; // Limpiar contenido previo
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


                function showAlert(type, message) {
                    // Remover alertas existentes
                    const existingAlerts = document.querySelectorAll('.alert.position-fixed');
                    existingAlerts.forEach(alert => alert.remove());

                    // Crear alerta
                    const alertDiv = document.createElement('div');
                    alertDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
                    alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; max-width: 400px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);';

                    alertDiv.innerHTML = `
                                                                                <div class="d-flex align-items-center">
                                                                                    <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
                                                                                    <div>${message}</div>
                                                                                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                                                                                </div>
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
            </script>

        @endif
@endsection