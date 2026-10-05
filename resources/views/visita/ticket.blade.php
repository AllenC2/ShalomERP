<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket de visita #{{ $visita->id }}</title>
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
        .ticket { width: 58mm; max-width: 58mm; padding: 2mm; margin: 0 auto; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .fw-bold { font-weight: bold; }
        .mb-1 { margin-bottom: 2px; }
        .mb-2 { margin-bottom: 5px; }
        .mb-3 { margin-bottom: 10px; }
        .mt-2 { margin-top: 5px; }
        .mt-3 { margin-top: 10px; }
        .mt-4 { margin-top: 16px; }
        .ticket-row { display: flex; justify-content: space-between; margin-bottom: 1mm; gap: 4px; }
        .ticket-label { font-weight: normal; }
        .ticket-value { font-weight: bold; text-align: right; }
        .ticket-divider { border-bottom: 1px dashed #000; margin: 2mm 0; }
        .aviso-carta {
            text-align: left;
            font-size: 10px;
            line-height: 1.35;
            margin: 4px 0 8px;
        }
        .aviso-carta p { margin: 0 0 6px; }
        @media print {
            body { width: 58mm; }
            .ticket { width: 58mm; padding: 0; margin: 0; }
            .d-print-none { display: none !important; }
            @page { margin: 0; size: 58mm auto; }
        }
        .btn {
            display: block; width: 100%; padding: 10px; background: #000; color: #fff;
            text-align: center; border: none; font-size: 14px; cursor: pointer;
            text-decoration: none; box-sizing: border-box; font-family: monospace;
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
    @endphp
    <div class="ticket">
        <div class="text-center mb-2" style="font-size: 10px;">
            Emitido: {{ $fechaVisita->format('d/m/y H:i') }}<br>
            Por: {{ $empleadoNombre ?? 'Sistema' }}
        </div>

        <div class="text-center mt-1 mb-2" style="font-size: 10px;">
            <div class="fw-bold" style="font-size: 12px;">{{ $empresa['razon_social'] ?? 'Funeraria Shalom' }}</div>
            {{ $empresa['calle_numero'] ?? '' }} {{ $empresa['colonia'] ?? '' }}<br>
            {{ $empresa['ciudad'] ?? '' }}, {{ $empresa['estado'] ?? '' }} CP: {{ $empresa['codigo_postal'] ?? '' }}<br>
            @if(!empty($empresa['telefono']))Tel: {{ $empresa['telefono'] }}@endif
        </div>

        <div class="ticket-divider"></div>

        @if(!$visita->recibido)
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
                <p class="fw-bold">Funeraria Shalom</p>
            </div>
        @else
            <div class="text-center fw-bold mb-2" style="font-size: 13px;">TICKET DE VISITA</div>
            <div class="ticket-row">
                <span class="ticket-label">Recibió:</span>
                <span class="ticket-value">{{ $visita->receptor_nombre }}</span>
            </div>
            <div class="ticket-row">
                <span class="ticket-label">Parentesco:</span>
                <span class="ticket-value">{{ \App\Models\Visita::PARENTESCOS[$visita->receptor_parentesco] ?? $visita->receptor_parentesco }}</span>
            </div>

            <div class="ticket-row">
                <span class="ticket-label">En domicilio:</span>
                <span class="ticket-value">{{ $visita->en_domicilio === null ? 'N/D' : ($visita->en_domicilio ? 'Sí' : 'No') }}</span>
            </div>

            @if($titular)
                <div class="ticket-divider"></div>
                <div class="ticket-row">
                    <span class="ticket-label">Titular:</span>
                    <span class="ticket-value">{{ \Illuminate\Support\Str::limit($nombreCliente, 22) }}</span>
                </div>
            @endif

            @foreach($visitas as $v)
                <div class="ticket-divider"></div>
                <div class="ticket-row">
                    <span class="ticket-label">Folio:</span>
                    <span class="ticket-value">#{{ $v->contrato_id }}</span>
                </div>
                @if($v->contrato?->paquete)
                    <div class="ticket-row">
                        <span class="ticket-label">Paquete:</span>
                        <span class="ticket-value">{{ \Illuminate\Support\Str::limit($v->contrato->paquete->nombre, 18) }}</span>
                    </div>
                @endif
                @php $pagoV = $v->pagos->first(); @endphp
                @if($pagoV)
                    <div class="ticket-row">
                        <span class="ticket-label">Pago/abono:</span>
                        <span class="ticket-value">${{ number_format($pagoV->monto, 2) }}</span>
                    </div>
                    <div class="ticket-row">
                        <span class="ticket-label">Método:</span>
                        <span class="ticket-value">{{ \App\Models\Pago::METODOS_PAGO[$pagoV->metodo_pago] ?? $pagoV->metodo_pago }}</span>
                    </div>
                @else
                    <div class="ticket-row">
                        <span class="ticket-label">Pago/abono:</span>
                        <span class="ticket-value">Sin pago</span>
                    </div>
                @endif
            @endforeach

            <div class="ticket-divider"></div>
            <div class="text-center mt-4">
                <div class="mb-1" style="border-bottom: 1px solid #000; width: 80%; margin: 18px auto 5px auto;"></div>
                <div class="small fw-bold">FIRMA DEL EMPLEADO QUE ENTREGA</div>
                <div class="small">{{ $empleadoNombre }}</div>
            </div>
        @endif

        <div class="d-print-none mt-3">
            <button class="btn" type="button" onclick="window.print()">IMPRIMIR</button>
        </div>
    </div>
</body>
</html>
