<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket de Pago #{{ $pago->id }}</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
            color: black;
            background: white;
            margin: 0;
            padding: 0;
            width: 58mm;
        }
        .ticket {
            width: 58mm;
            max-width: 58mm;
            padding: 2mm;
            margin: 0 auto;
        }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .fw-bold { font-weight: bold; }
        .text-uppercase { text-transform: uppercase; }
        .border-bottom { border-bottom: 1px dashed black; margin-bottom: 3px; padding-bottom: 3px; }
        .border-top { border-top: 1px dashed black; margin-top: 3px; padding-top: 3px; }
        .mb-1 { margin-bottom: 2px; }
        .mb-2 { margin-bottom: 5px; }
        .mb-3 { margin-bottom: 10px; }
        .mt-1 { margin-top: 2px; }
        .mt-2 { margin-top: 5px; }
        .mt-3 { margin-top: 10px; }
        
        .ticket-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1mm;
        }
        .ticket-label {
            font-weight: normal;
        }
        .ticket-value {
            font-weight: bold;
            text-align: right;
        }
        .ticket-divider {
            border-bottom: 1px dashed #000;
            margin: 2mm 0;
        }
        
        @media print {
            body { width: 58mm; }
            .ticket { width: 58mm; padding: 0; margin: 0; }
            .d-print-none { display: none !important; }
            @page { margin: 0; size: 58mm auto; }
        }
        .btn {
            display: block; width: 100%; padding: 10px; background: #000; color: #fff; text-align: center; border: none; font-size: 14px; cursor: pointer; text-decoration: none; box-sizing: border-box; font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="ticket">
        <div class="small text-center mb-2" style="font-size: 10px;">
            Emitido: {{ \Carbon\Carbon::now()->format('d/m/y H:i') }}<br>
            Por: {{ auth()->user()->name ?? 'Sistema' }}
        </div>

        <div class="text-center mt-1 mb-2" style="font-size: 10px;">
            <div class="fw-bold" style="font-size: 12px;">{{ $empresa['razon_social'] ?? '' }}</div>
            {{ $empresa['calle_numero'] ?? '' }} {{ $empresa['colonia'] ?? '' }}<br>
            {{ $empresa['ciudad'] ?? '' }}, {{ $empresa['estado'] ?? '' }} CP: {{ $empresa['codigo_postal'] ?? '' }}<br>
            @if(isset($empresa['telefono']) && $empresa['telefono'])Tel: {{ $empresa['telefono'] }}@endif
        </div>
        
        <div class="ticket-divider"></div>

        <div class="text-center mb-2 border-bottom pb-2">
            <h2 class="fw-bold mb-1 mt-1" style="font-size: 14px; margin: 0;">
                @if($pago->tipo_pago === 'cuota' && $pago->numero_cuota)
                    PAGO CUOTA #{{ $pago->numero_cuota }}
                @else
                    RECIBO DE PAGO
                @endif
            </h2>
            <div style="font-size: 10px;">Folio: #{{ str_pad($pago->numero_pago ?? $pago->id ?? 0, 6, '0', STR_PAD_LEFT) }}</div>
        </div>

        <h4 class="text-center fw-bold my-3" style="font-family: 'Courier New'; font-size: 16px; margin: 10px 0;">
            ${{ number_format($pago->monto, 2) }}
        </h4>

        <div class="ticket-row">
            <span class="ticket-label">Método de pago:</span>
            <span class="ticket-value">{{ \App\Models\Pago::METODOS_PAGO[$pago->metodo_pago] ?? $pago->metodo_pago }}</span>
        </div>

        <div class="ticket-row">
            <span class="ticket-label">Fecha:</span>
            <span class="ticket-value">{{ $pago->created_at->format('d/m/Y') }}</span>
        </div>

        @if($cliente)
            <div class="ticket-divider"></div>
            <div class="ticket-row">
                <span class="ticket-label">ID Cliente:</span>
                <span class="ticket-value">{{ $cliente->id }}</span>
            </div>
            <div class="ticket-row">
                <span class="ticket-label">Nombre:</span>
                <span class="ticket-value" style="text-align: right; max-width: 60%;">
                    {{ Str::limit($cliente->nombre . ' ' . $cliente->apellido, 20) }}
                </span>
            </div>
            @if($pago->contrato)
                <div class="ticket-row">
                    <span class="ticket-label">Contrato:</span>
                    <span class="ticket-value">{{ $pago->contrato->paquete->nombre ?? 'N/A' }}#{{ $pago->contrato->id }}</span>
                </div>
            @endif
        @endif

        <div class="ticket-divider"></div>

        @if($pago->contrato)
            @php
                $montoTotalPagado = calcularMontoPagadoContrato($pago->contrato->pagos);
                $montoRestante = $pago->contrato->monto_total - $montoTotalPagado;
            @endphp
            <div class="ticket-row">
                <span class="ticket-label">Total Contrato:</span>
                <span class="ticket-value">${{ number_format($pago->contrato->monto_total, 2) }}</span>
            </div>
            <div class="ticket-row">
                <span class="ticket-label">Abonado:</span>
                <span class="ticket-value">${{ number_format($montoTotalPagado, 2) }}</span>
            </div>
            <div class="ticket-row fw-bold mt-1 pt-1" style="border-top: 1px dashed #000;">
                <span class="ticket-label">Resta:</span>
                <span class="ticket-value">${{ number_format($montoRestante, 2) }}</span>
            </div>
        @endif

        <div class="ticket-divider"></div>

        @if($pago->observaciones)
            <div style="display: flex; flex-direction: column; margin-top: 2mm;">
                <span class="ticket-label fw-bold mb-1">Notas/Concepto:</span>
                <span class="ticket-value text-left" style="font-size: 10px; font-weight: normal; text-align: left;">{{ $pago->observaciones }}</span>
            </div>
            <div class="ticket-divider"></div>
        @endif

        <div class="text-center mt-4">
            <div class="mb-3" style="border-bottom: 1px solid #000; width: 80%; margin: 15px auto 5px auto;"></div>
            <div class="small fw-bold">FIRMA DE RECIBIDO</div>
        </div>

        <div class="text-center mt-2 mb-3">
            ¡GRACIAS POR SU PAGO!
        </div>
        <div class="d-print-none mt-3">
            <button class="btn" onclick="window.print()">IMPRIMIR</button>
            <button class="btn" style="margin-top: 5px; background: #666;" onclick="window.close()">CERRAR</button>
        </div>
    </div>
    <script>
        // Imprimir automáticamente al cargar
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>
