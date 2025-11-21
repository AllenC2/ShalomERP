<?php

if (!function_exists('formatBytes')) {
    /**
     * Convierte bytes a una representación legible (KB, MB, GB, etc.)
     *
     * @param int $bytes Cantidad de bytes
     * @param int $precision Número de decimales
     * @return string Tamaño formateado
     */
    function formatBytes($bytes, $precision = 2)
    {
        if ($bytes == 0) {
            return '0 Bytes';
        }
        
        $k = 1024;
        $dm = $precision < 0 ? 0 : $precision;
        $sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        
        $i = floor(log($bytes) / log($k));
        
        return round($bytes / pow($k, $i), $dm) . ' ' . $sizes[$i];
    }
}

if (!function_exists('infoEmpresa')) {
    /**
     * Obtiene la información de la empresa configurada en ajustes
     *
     * @param string|null $campo Campo específico a obtener (ej: 'razon_social')
     * @return array|string|null Información completa o campo específico
     */
    function infoEmpresa($campo = null)
    {
        try {
            $info = \App\Models\Ajuste::obtenerInfoEmpresa();
            
            if ($campo) {
                return $info[$campo] ?? null;
            }
            
            return $info;
        } catch (Exception $e) {
            // Valores por defecto si hay error
            $infoDefault = [
                'razon_social' => 'RazonSocial',
                'rfc' => 'RFC0000000',
                'calle_numero' => 'Av. Principal #123',
                'colonia' => 'Col. COLONIA',
                'ciudad' => 'Guadalajara',
                'estado' => 'Jalisco',
                'pais' => 'México',
                'codigo_postal' => '00000',
                'telefono' => '(000) 123-4567',
                'email' => 'contacto@funerariashalom.com',
            ];
            
            if ($campo) {
                return $infoDefault[$campo] ?? null;
            }
            
            return $infoDefault;
        }
    }
}

if (!function_exists('formatearDireccionEmpresa')) {
    /**
     * Formatea la dirección completa de la empresa para mostrar en recibos
     *
     * @param bool $conSaltos Si incluir saltos de línea HTML
     * @return string Dirección formateada
     */
    function formatearDireccionEmpresa($conSaltos = true)
    {
        $info = infoEmpresa();
        $separador = $conSaltos ? '<br>' : ', ';
        
        $direccion = [];
        
        // Calle y número, colonia
        if ($info['calle_numero'] && $info['colonia']) {
            $direccion[] = $info['calle_numero'] . ', ' . $info['colonia'];
        } else {
            $direccion[] = 'Av. Principal #123, Col. Centro';
        }
        
        // Ciudad, estado, país y código postal
        if ($info['ciudad'] && $info['estado']) {
            $ciudadEstado = $info['ciudad'] . ', ' . $info['estado'] . ', ' . ($info['pais'] ?: 'México');
            if ($info['codigo_postal']) {
                $ciudadEstado .= ' C.P. ' . $info['codigo_postal'];
            } else {
                $ciudadEstado .= ' C.P. 97000';
            }
            $direccion[] = $ciudadEstado;
        } else {
            $direccion[] = 'Mérida, Yucatán, México C.P. 97000';
        }
        
        return implode($separador, $direccion);
    }
}

if (!function_exists('generarMensajeWhatsApp')) {
    /**
     * Genera un mensaje personalizado de WhatsApp para recordatorio de pago
     *
     * @param object $contrato Objeto del contrato
     * @param object $proximoPago Objeto del próximo pago (opcional)
     * @return string Mensaje personalizado
     */
    function generarMensajeWhatsApp($contrato, $proximoPago = null)
    {
        try {
            // Obtener el template del mensaje desde ajustes
            $mensajeTemplate = \App\Models\Ajuste::obtener('textoRecordatorioWhats', 
                'Hola {nombreCliente}, te recordamos que el pago de tu paquete {nombrePaquete} por {cantidadPagoProximo} será cobrado el día {fechaPago}.'
            );

            // Si no hay próximo pago, buscar el siguiente pendiente
            if (!$proximoPago) {
                $proximoPago = $contrato->pagos()
                    ->where('estado', 'pendiente')
                    ->where('fecha_pago', '>=', now())
                    ->orderBy('fecha_pago')
                    ->first();
            }

            // Variables para reemplazar
            $variables = [
                '{nombreCliente}' => $contrato->cliente->nombre ?? 'Cliente',
                '{nombrePaquete}' => $contrato->paquete->nombre ?? 'Paquete',
                '{cantidadPagoProximo}' => $proximoPago ? '$' . number_format($proximoPago->monto, 2) : '$0.00',
                '{fechaPago}' => $proximoPago ? 
                    \Carbon\Carbon::parse($proximoPago->fecha_pago)->locale('es')->isoFormat('D [de] MMMM [de] YYYY') : 
                    'próximamente'
            ];

            // Reemplazar variables en el mensaje
            $mensaje = $mensajeTemplate;
            foreach ($variables as $variable => $valor) {
                $mensaje = str_replace($variable, $valor, $mensaje);
            }

            return $mensaje;

        } catch (Exception $e) {
            // Mensaje por defecto en caso de error
            \Log::error('Error al generar mensaje de WhatsApp: ' . $e->getMessage());
            
            $nombreCliente = $contrato->cliente->nombre ?? 'Cliente';
            $contratoId = $contrato->id ?? '000';
            
            return "Hola {$nombreCliente}, te recordamos tu próximo pago de contrato #{$contratoId}.";
        }
    }
}

if (!function_exists('toleranciaPagos')) {
    /**
     * Obtiene la tolerancia de pagos configurada en ajustes
     *
     * @return int Días de tolerancia antes de considerar un pago retrasado
     */
    function toleranciaPagos()
    {
        try {
            return \App\Models\Ajuste::obtenerToleranciaPagos();
        } catch (Exception $e) {
            \Log::error('Error al obtener tolerancia de pagos: ' . $e->getMessage());
            return 0; // Sin tolerancia por defecto
        }
    }
}

if (!function_exists('pagoEstaRetrasado')) {
    /**
     * Determina si un pago está retrasado basado en la tolerancia configurada
     *
     * @param string|\Carbon\Carbon $fechaPago Fecha de vencimiento del pago
     * @param string $estado Estado actual del pago
     * @return bool True si el pago está retrasado
     */
    function pagoEstaRetrasado($fechaPago, $estado = 'pendiente')
    {
        try {
            // Solo los pagos pendientes pueden estar retrasados
            if ($estado !== 'pendiente') {
                return false;
            }

            $fechaPago = \Carbon\Carbon::parse($fechaPago);
            $tolerancia = toleranciaPagos();
            $fechaLimite = \Carbon\Carbon::now()->subDays($tolerancia);
            
            return $fechaPago->isBefore($fechaLimite);
        } catch (Exception $e) {
            \Log::error('Error al verificar si pago está retrasado: ' . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('diasDeRetraso')) {
    /**
     * Calcula los días de retraso de un pago considerando la tolerancia
     *
     * @param string|\Carbon\Carbon $fechaPago Fecha de vencimiento del pago
     * @param string $estado Estado actual del pago
     * @return int Días de retraso (0 si no está retrasado)
     */
    function diasDeRetraso($fechaPago, $estado = 'pendiente')
    {
        try {
            if (!pagoEstaRetrasado($fechaPago, $estado)) {
                return 0;
            }

            $fechaPago = \Carbon\Carbon::parse($fechaPago);
            $tolerancia = toleranciaPagos();
            $fechaLimite = $fechaPago->copy()->addDays($tolerancia);
            
            return max(0, \Carbon\Carbon::now()->diffInDays($fechaLimite));
        } catch (Exception $e) {
            \Log::error('Error al calcular días de retraso: ' . $e->getMessage());
            return 0;
        }
    }
}

if (!function_exists('diasDeGraciaRestantes')) {
    /**
     * Calcula los días de gracia restantes para un pago en tolerancia
     *
     * @param string|\Carbon\Carbon $fechaPago Fecha de vencimiento del pago
     * @param string $estado Estado actual del pago
     * @return int Días de gracia restantes (0 si no está en gracia o ya está retrasado)
     */
    function diasDeGraciaRestantes($fechaPago, $estado = 'pendiente')
    {
        try {
            // Solo los pagos pendientes pueden estar en gracia
            if ($estado !== 'pendiente') {
                return 0;
            }

            $fechaPago = \Carbon\Carbon::parse($fechaPago);
            $tolerancia = toleranciaPagos();
            
            // Si no hay tolerancia configurada, no hay días de gracia
            if ($tolerancia <= 0) {
                return 0;
            }

            $fechaLimite = $fechaPago->copy()->addDays($tolerancia);
            $ahora = \Carbon\Carbon::now();
            
            // Si ya pasó la fecha límite, está retrasado (no en gracia)
            if ($ahora->isAfter($fechaLimite)) {
                return 0;
            }
            
            // Si aún no vence, no está en gracia
            if ($ahora->isBefore($fechaPago)) {
                return 0;
            }
            
            // Está en período de gracia, calcular días restantes (entero)
            return max(0, intval($ahora->diffInDays($fechaLimite, false)));
        } catch (Exception $e) {
            \Log::error('Error al calcular días de gracia restantes: ' . $e->getMessage());
            return 0;
        }
    }
}

if (!function_exists('calcularMontoPagadoContrato')) {
    /**
     * Calcula el monto total pagado de un contrato evitando conteo duplicado
     * entre cuotas completadas por parcialidades y las parcialidades mismas.
     *
     * @param \Illuminate\Support\Collection $pagos_contrato Colección de pagos del contrato
     * @param bool $soloHechos Si true, solo considera pagos con estado 'hecho'
     * @return float Monto total pagado sin duplicados
     */
    function calcularMontoPagadoContrato($pagos_contrato, $soloHechos = true)
    {
        try {
            $montoPagado = 0;
            $pagosConsiderar = $soloHechos ? $pagos_contrato->where('estado', 'hecho') : $pagos_contrato;
            
            foreach ($pagosConsiderar as $pago) {
                if ($pago->tipo_pago === 'parcialidad') {
                    // Las parcialidades siempre se suman
                    $montoPagado += $pago->monto;
                } elseif ($pago->tipo_pago === 'cuota') {
                    // Para cuotas, solo sumar si NO tienen parcialidades que las completen
                    $tieneParcialidades = $pagos_contrato
                        ->where('tipo_pago', 'parcialidad')
                        ->where('estado', 'hecho')
                        ->where('pago_padre_id', $pago->id)
                        ->count() > 0;
                    
                    if (!$tieneParcialidades) {
                        // Cuota pagada directamente, sin parcialidades
                        $montoPagado += $pago->monto;
                    }
                    // Si tiene parcialidades, NO sumar la cuota (se suma por las parcialidades)
                } else {
                    // Pagos especiales (inicial, bonificación, etc.)
                    $montoPagado += $pago->monto;
                }
            }
            
            return $montoPagado;
        } catch (Exception $e) {
            \Log::error('Error al calcular monto pagado del contrato: ' . $e->getMessage());
            return 0;
        }
    }
}

if (!function_exists('numeroOrdinal')) {
    /**
     * Convierte un número a su forma ordinal en español
     *
     * @param int $numero El número a convertir
     * @return string El número con su terminación ordinal (1ra, 2da, 3ra, etc.)
     */
    function numeroOrdinal($numero)
    {
        // Casos especiales del 1 al 10
        if ($numero == 1) return '1er';
        if ($numero == 2) return '2do';
        if ($numero == 3) return '3er';
        if ($numero == 4) return '4to';
        if ($numero == 5) return '5to';
        if ($numero == 6) return '6to';
        if ($numero == 7) return '7mo';
        if ($numero == 8) return '8vo';
        if ($numero == 9) return '9no';
        if ($numero == 10) return '10mo';

        // Del 11 al 20 usan 'va'
        if ($numero >= 11 && $numero <= 20) {
            return $numero . 'va';
        }
        
        // Casos especiales mayores a 20
        if ($numero == 21) return '21ro';
        if ($numero == 22) return '22do';
        if ($numero == 23) return '23ro';

        // Para números mayores, usar el último dígito
        $ultimoDigito = $numero % 10;

        if ($ultimoDigito == 1) return $numero . 'ro';
        if ($ultimoDigito == 2) return $numero . 'do';
        if ($ultimoDigito == 3) return $numero . 'er';
        if ($ultimoDigito == 7) return $numero . 'mo';
        if ($ultimoDigito == 8) return $numero . 'vo';
        if ($ultimoDigito == 9) return $numero . 'no';
        if ($ultimoDigito == 0) return $numero . 'mo';
        
        // Para 4, 5, 6 usar 'ta'
        return $numero . 'to';
    }
}
