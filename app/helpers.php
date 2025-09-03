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
