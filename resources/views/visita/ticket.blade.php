<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket de visita #{{ $visita->id }}</title>
    <style>
        html, body {
            margin: 0;
            padding: 0;
            background: white;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
            line-height: 1.2;
            color: #000;
            width: 100%;
            max-width: 100%;
            overflow-x: hidden;
            -webkit-text-size-adjust: 100%;
        }
        .ticket {
            width: 100%;
            max-width: 58mm;
            padding: 4mm 4mm 8mm;
            margin: 0 auto;
            box-sizing: border-box;
            background: white;
        }
        @media screen {
            .ticket { max-width: min(58mm, 100%); }
        }
        .logo-ticket {
            max-width: 40mm;
            height: auto;
            display: block;
            margin: 0 auto 2mm auto;
        }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .fw-bold { font-weight: bold; }
        .mb-1 { margin-bottom: 2px; }
        .mb-2 { margin-bottom: 5px; }
        .mb-3 { margin-bottom: 10px; }
        .mt-1 { margin-top: 2px; }
        .mt-2 { margin-top: 5px; }
        .mt-3 { margin-top: 10px; }
        .mt-4 { margin-top: 16px; }
        .ticket-header-info {
            font-size: 10px;
            text-align: center;
            margin-bottom: 5px;
        }
        .ticket-header {
            text-align: center;
            border-bottom: 1px dashed #000;
            padding-bottom: 2mm;
            margin-bottom: 2mm;
        }
        .ticket-header h2 {
            font-family: 'Courier New', Courier, monospace;
            font-size: 14px;
            font-weight: bold;
            margin: 0 0 2px 0;
            color: #000;
        }
        .ticket-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1mm;
            gap: 4px;
        }
        .ticket-label { font-weight: normal; }
        .ticket-value { font-weight: bold; text-align: right; }
        .ticket-divider {
            border-bottom: 1px dashed #000;
            margin: 2mm 0;
        }
        .ticket-footer {
            text-align: center;
            margin-top: 4mm;
            font-size: 10px;
        }
        .aviso-carta {
            text-align: left;
            font-size: 10px;
            line-height: 1.35;
            margin: 4px 0 8px;
        }
        .aviso-carta p { margin: 0 0 6px; }
        .monto-grande {
            font-family: 'Courier New', Courier, monospace;
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            margin: 10px 0;
        }
        @media print {
            body { width: 58mm; }
            .ticket { width: 58mm; max-width: 58mm; padding: 0; margin: 0; }
            .d-print-none { display: none !important; }
            @page { margin: 0; size: 58mm auto; }
        }
    </style>
</head>
<body>
    @php
        $titular = $visita->contrato?->cliente;
        $nombreCliente = $titular ? trim(($titular->nombre ?? '').' '.($titular->apellido ?? '')) : 'cliente';
        $folios = $visitas->pluck('contrato_id')->filter()->unique()->values();
        $foliosTxt = $folios->map(fn ($id) => '#'.$id)->implode(', ');
        $fechaVisita = $visita->created_at ?? now();
        $tienePagos = $visitas->contains(fn ($v) => $v->pagos->isNotEmpty());
        $pagosTodos = $visitas->flatMap->pagos;
        $montoTotal = $pagosTodos->sum(fn ($p) => (float) $p->monto);
        $primerPago = $pagosTodos->first();
        $metodos = $pagosTodos
            ->pluck('metodo_pago')
            ->unique()
            ->map(fn ($m) => \App\Models\Pago::METODOS_PAGO[$m] ?? $m)
            ->implode(', ');
        $folioTicket = str_pad((string) ($primerPago->id ?? $visita->id), 6, '0', STR_PAD_LEFT);
        $razonSocial = $empresa['razon_social'] ?? 'Funeraria Shalom';
    @endphp
    <div class="ticket">
        <div class="ticket-header-info">
            Emitido: {{ $fechaVisita->format('d/m/y H:i') }}<br>
            Por: {{ $empleadoNombre ?? 'Sistema' }}
        </div>

        <img src="{{ asset('shalom_logo.svg') }}" alt="Logo" class="logo-ticket">

        <div class="text-center mt-1 mb-2" style="font-size: 8px;">
            {{ $razonSocial }}<br>
            {{ $empresa['calle_numero'] ?? '' }} {{ $empresa['colonia'] ?? '' }}<br>
            {{ $empresa['ciudad'] ?? '' }}, {{ $empresa['estado'] ?? '' }} CP: {{ $empresa['codigo_postal'] ?? '' }}<br>
            @if(!empty($empresa['telefono']))Tel: {{ $empresa['telefono'] }}@endif
        </div>

        <div class="ticket-divider"></div>

        @if(!$visita->recibido && !$tienePagos)
            <div class="ticket-header">
                <h2>AVISO DE VISITA</h2>
                <div style="font-size: 10px;">Folio: {{ $foliosTxt }}</div>
            </div>

            <div class="aviso-carta">
                <p class="fw-bold">Estimado(a) {{ $nombreCliente }}:</p>
                <p>
                    Hoy realizamos una visita a su domicilio para darle seguimiento a su plan de previsión Folio: {{ $foliosTxt }},
                    con el fin de mantener sus beneficios al corriente.
                </p>
                <p>En esta ocasión no fue posible localizarle.</p>
                <p>
                    Para su comodidad, le pedimos comunicarse al 339 1338 3886 / (33) 1607 9490,
                    y así programar el mejor horario para atenderle.
                </p>
                <p>
                    Su plan es una protección importante para usted y su familia,
                    y en Funeraria Shalom estamos para servirle.
                </p>
                <p class="fw-bold mb-1">Datos de la visita</p>
                <p class="mb-1">Cobrador: {{ $empleadoNombre }}</p>
                <p class="mb-1">Fecha: {{ $fechaVisita->format('d/m/Y') }}</p>
                <p class="mb-1">Hora: {{ $fechaVisita->format('H:i') }}</p>
                <p class="mt-3 mb-1">Atentamente</p>
                <p class="fw-bold">{{ $razonSocial }}</p>
            </div>

            <div class="ticket-footer">
                <div style="border-bottom: 1px solid #000; width: 80%; margin: 15px auto 5px auto;"></div>
                <div class="fw-bold">FIRMA DEL EMPLEADO QUE VISITA</div>
                <div>{{ $empleadoNombre }}</div>
            </div>
        @else
            <div class="ticket-header">
                <h2>RECIBO DE PAGO</h2>
                <div style="font-size: 10px;">Folio: #{{ $folioTicket }}</div>
            </div>

            <div class="monto-grande">${{ number_format($montoTotal, 2) }}</div>

            @if($metodos !== '')
                <div class="ticket-row">
                    <span class="ticket-label">Método de pago:</span>
                    <span class="ticket-value">{{ $metodos }}</span>
                </div>
            @endif

            <div class="ticket-row">
                <span class="ticket-label">Fecha:</span>
                <span class="ticket-value">{{ $fechaVisita->format('d/m/Y') }}</span>
            </div>

            @if($titular)
                <div class="ticket-divider"></div>
                <div class="ticket-row">
                    <span class="ticket-label">ID Cliente:</span>
                    <span class="ticket-value">{{ $titular->id }}</span>
                </div>
                <div class="ticket-row">
                    <span class="ticket-label">Nombre:</span>
                    <span class="ticket-value" style="max-width: 60%;">
                        {{ \Illuminate\Support\Str::limit($nombreCliente, 20) }}
                    </span>
                </div>
            @endif

            @foreach($visitas as $v)
                @php $pagoV = $v->pagos->first(); @endphp
                <div class="ticket-divider"></div>
                <div class="ticket-row">
                    <span class="ticket-label">Contrato:</span>
                    <span class="ticket-value">
                        {{ \Illuminate\Support\Str::limit($v->contrato?->paquete?->nombre ?? 'N/A', 14) }}#{{ $v->contrato_id }}
                    </span>
                </div>
                @if($pagoV)
                    <div class="ticket-row">
                        <span class="ticket-label">Abono:</span>
                        <span class="ticket-value">${{ number_format($pagoV->monto, 2) }}</span>
                    </div>
                    @if($v->contrato)
                        @php
                            $pagosContrato = $v->contrato->relationLoaded('pagos')
                                ? $v->contrato->pagos
                                : $v->contrato->pagos()->get();
                            $montoTotalPagado = calcularMontoPagadoContrato($pagosContrato);
                            $montoRestante = (float) $v->contrato->monto_total - (float) $montoTotalPagado;
                        @endphp
                        <div class="ticket-row">
                            <span class="ticket-label">Total Contrato:</span>
                            <span class="ticket-value">${{ number_format($v->contrato->monto_total, 2) }}</span>
                        </div>
                        <div class="ticket-row">
                            <span class="ticket-label">Abonado:</span>
                            <span class="ticket-value">${{ number_format($montoTotalPagado, 2) }}</span>
                        </div>
                        <div class="ticket-row fw-bold" style="border-top: 1px dashed #000; padding-top: 1mm; margin-top: 1mm;">
                            <span class="ticket-label">Resta:</span>
                            <span class="ticket-value">${{ number_format($montoRestante, 2) }}</span>
                        </div>
                    @endif
                    @if(!empty($pagoV->observaciones))
                        <div class="text-center small mt-1" style="font-size: 10px;">
                            "{{ \Illuminate\Support\Str::limit($pagoV->observaciones, 50) }}"
                        </div>
                    @endif
                @else
                    <div class="ticket-row">
                        <span class="ticket-label">Abono:</span>
                        <span class="ticket-value">Sin pago</span>
                    </div>
                @endif
            @endforeach

            <div class="ticket-footer">
                <br>
                <div style="border-bottom: 1px solid #000; width: 80%; margin: 0 auto 5px auto;"></div>
                <div class="fw-bold">FIRMA DE RECIBIDO</div>
                <div>{{ $empleadoNombre }}</div>
                <br>
                ¡GRACIAS POR SU PAGO!
            </div>
        @endif
    </div>
</body>
</html>
