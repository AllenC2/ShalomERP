<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket de Comisiones</title>
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
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td { padding: 1px 0; font-size: 10px; word-wrap: break-word; vertical-align: top; }
        .col-fecha { width: 25%; }
        .col-desc { width: 40%; }
        .col-monto { width: 35%; text-align: right; }
        
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
        <div class="text-center mb-2 border-bottom">
            <h2 class="fw-bold mb-1 mt-1" style="font-size: 14px; margin: 0;">{{ strtoupper(infoEmpresa('razon_social')) }}</h2>
            @if(infoEmpresa('rfc'))<div class="mb-1">RFC: {{ infoEmpresa('rfc') }}</div>@endif
            @if(infoEmpresa('telefono'))<div class="mb-1">Tel: {{ infoEmpresa('telefono') }}</div>@endif
        </div>
        
        <div class="text-center mb-2 border-bottom">
            <div class="fw-bold">RECIBO DE COMISIONES</div>
            <div>{{ $fechaInicio->format('d/m/y') }} al {{ $fechaFin->format('d/m/y') }}</div>
            <div class="mt-1">Impreso: {{ \Carbon\Carbon::now()->format('d/m/y H:i') }}</div>
        </div>

        <div class="mb-2 border-bottom">
            <div class="fw-bold">ASESOR:</div>
            <div class="text-uppercase">{{ $empleado->nombre }} {{ $empleado->apellido }}</div>
        </div>

        @if($comisionesPagadas->count() > 0)
        <div class="mb-1 fw-bold text-center border-bottom">PAGADAS / HECHAS</div>
        <table class="mb-2">
            <thead>
                <tr class="border-bottom">
                    <th class="col-fecha text-left">Fec</th>
                    <th class="col-desc text-left">Ref/Tipo</th>
                    <th class="col-monto">Monto</th>
                </tr>
            </thead>
            <tbody>
                @foreach($comisionesPagadas as $comision)
                <tr>
                    <td class="col-fecha">{{ \Carbon\Carbon::parse($comision->fecha_comision)->format('d/m') }}</td>
                    <td class="col-desc">
                        #{{ $comision->contrato_id }}<br>
                        <small>{{ substr(ucfirst($comision->tipo_comision), 0, 10) }}</small>
                    </td>
                    <td class="col-monto fw-bold">${{ number_format($comision->monto, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="text-right fw-bold border-top mb-3" style="font-size: 12px;">
            TOTAL PAGADO: ${{ number_format($totalPagadas, 2) }}
        </div>
        @else
        <div class="text-center mb-3 border-bottom">NO HAY COMISIONES PAGADAS</div>
        @endif

        @if($incluirPendientes)
            @if($comisionesPendientes->count() > 0)
            <div class="mb-1 fw-bold text-center border-bottom">PENDIENTES</div>
            <table class="mb-2">
                <thead>
                    <tr class="border-bottom">
                        <th class="col-fecha text-left">Fec</th>
                        <th class="col-desc text-left">Ref/Tipo</th>
                        <th class="col-monto">Monto</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($comisionesPendientes as $comision)
                    <tr>
                        <td class="col-fecha">{{ \Carbon\Carbon::parse($comision->fecha_comision)->format('d/m') }}</td>
                        <td class="col-desc">
                            #{{ $comision->contrato_id }}<br>
                            <small>{{ substr(ucfirst($comision->tipo_comision), 0, 10) }}</small>
                        </td>
                        <td class="col-monto fw-bold">${{ number_format($comision->monto_restante_calculado, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="text-right fw-bold border-top mb-3" style="font-size: 12px;">
                TOTAL PENDIENTE: ${{ number_format($totalPendientes, 2) }}
            </div>
            @else
            <div class="text-center mb-3 border-bottom">NO HAY COMISIONES PENDIENTES</div>
            @endif
        @endif

        <div class="text-center mt-3 border-bottom" style="height: 30px;">
            <br>
        </div>
        <div class="text-center mb-3">
            Firma Asesor
        </div>

        <div class="text-center mb-3">
            *** FIN DE RECIBO ***
        </div>
        
        <div class="d-print-none mt-3">
            <button class="btn" onclick="window.print()">IMPRIMIR</button>
            <a href="{{ url()->previous() }}" class="btn" style="margin-top: 5px; background: #666;">REGRESAR</a>
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