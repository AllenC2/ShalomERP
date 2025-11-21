<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ajuste;

class AjustesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Valores por defecto para la empresa
        $infoEmpresa = [
            'razon_social' => '',
            'rfc' => '',
            'calle_numero' => '',
            'colonia' => '',
            'ciudad' => '',
            'estado' => '',
            'pais' => 'México',
            'codigo_postal' => '',
            'telefono' => '',
            'email' => '',
        ];

        try {
            // Intentar obtener los datos existentes de la base de datos
            $ajustes = Ajuste::where('nombre', 'like', 'empresa_%')
                           ->where('activo', true)
                           ->get();

            foreach ($ajustes as $ajuste) {
                $key = str_replace('empresa_', '', $ajuste->nombre);
                if (array_key_exists($key, $infoEmpresa)) {
                    $infoEmpresa[$key] = $ajuste->valor;
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error al obtener información de empresa: ' . $e->getMessage());
        }

        // Obtener mensaje de recordatorio de WhatsApp
        $mensajeRecordatorio = '';
        try {
            $ajusteRecordatorio = Ajuste::where('nombre', 'textoRecordatorioWhats')
                                       ->where('activo', true)
                                       ->first();
            
            $mensajeRecordatorio = $ajusteRecordatorio ? $ajusteRecordatorio->valor : 'Hola {nombreCliente}, te recordamos que el pago de tu paquete {nombrePaquete} por {cantidadPagoProximo} será cobrado el día {fechaPago}.';
        } catch (\Exception $e) {
            \Log::error('Error al obtener mensaje de recordatorio: ' . $e->getMessage());
            $mensajeRecordatorio = 'Hola {nombreCliente}, te recordamos que el pago de tu paquete {nombrePaquete} por {cantidadPagoProximo} será cobrado el día {fechaPago}.';
        }

        // Obtener tolerancia de pagos
        $toleranciaPagos = 0;
        try {
            $ajusteToleranciaPagos = Ajuste::where('nombre', 'tolerancia_pagos_dias')
                                          ->where('activo', true)
                                          ->first();
            
            $toleranciaPagos = $ajusteToleranciaPagos ? (int)$ajusteToleranciaPagos->valor : 0;
        } catch (\Exception $e) {
            \Log::error('Error al obtener tolerancia de pagos: ' . $e->getMessage());
            $toleranciaPagos = 0;
        }

        // Obtener configuración de registro público
        $registroPublico = false;
        try {
            $ajusteRegistroPublico = Ajuste::where('nombre', 'registro_publico_activo')
                                          ->where('activo', true)
                                          ->first();
            
            $registroPublico = $ajusteRegistroPublico ? filter_var($ajusteRegistroPublico->valor, FILTER_VALIDATE_BOOLEAN) : false;
        } catch (\Exception $e) {
            \Log::error('Error al obtener configuración de registro público: ' . $e->getMessage());
            $registroPublico = false;
        }
        
        return view('ajustes.index', compact('infoEmpresa', 'mensajeRecordatorio', 'toleranciaPagos', 'registroPublico'));
    }

    /**
     * Actualizar información de la empresa
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function actualizarEmpresa(Request $request)
    {
        // Log para debugging
        \Log::info('Método actualizarEmpresa llamado', ['data' => $request->all()]);

        $request->validate([
            'razon_social' => 'required|string|max:255',
            'rfc' => 'required|string|min:12|max:13',
            'calle_numero' => 'required|string|max:255',
            'colonia' => 'required|string|max:255',
            'ciudad' => 'required|string|max:255',
            'estado' => 'required|string|max:255',
            'pais' => 'required|string|max:255',
            'codigo_postal' => 'required|string|min:5|max:5',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
        ]);

        try {
            // Array de configuraciones de empresa
            $configuracionesEmpresa = [
                'empresa_razon_social' => [
                    'valor' => $request->razon_social,
                    'tipo' => 'string',
                    'descripcion' => 'Razón social de la empresa que aparece en recibos'
                ],
                'empresa_rfc' => [
                    'valor' => strtoupper($request->rfc),
                    'tipo' => 'string',
                    'descripcion' => 'RFC de la empresa'
                ],
                'empresa_calle_numero' => [
                    'valor' => $request->calle_numero,
                    'tipo' => 'string',
                    'descripcion' => 'Dirección: Calle y número'
                ],
                'empresa_colonia' => [
                    'valor' => $request->colonia,
                    'tipo' => 'string',
                    'descripcion' => 'Dirección: Colonia'
                ],
                'empresa_ciudad' => [
                    'valor' => $request->ciudad,
                    'tipo' => 'string',
                    'descripcion' => 'Dirección: Ciudad'
                ],
                'empresa_estado' => [
                    'valor' => $request->estado,
                    'tipo' => 'string',
                    'descripcion' => 'Dirección: Estado'
                ],
                'empresa_pais' => [
                    'valor' => $request->pais,
                    'tipo' => 'string',
                    'descripcion' => 'Dirección: País'
                ],
                'empresa_codigo_postal' => [
                    'valor' => $request->codigo_postal,
                    'tipo' => 'string',
                    'descripcion' => 'Código postal de la empresa'
                ],
                'empresa_telefono' => [
                    'valor' => $request->telefono ?? '',
                    'tipo' => 'string',
                    'descripcion' => 'Número de teléfono de la empresa'
                ],
                'empresa_email' => [
                    'valor' => $request->email ?? '',
                    'tipo' => 'string',
                    'descripcion' => 'Email corporativo de la empresa'
                ]
            ];

            // Guardar o actualizar cada configuración
            foreach ($configuracionesEmpresa as $nombre => $config) {
                \Log::info('Guardando ajuste', ['nombre' => $nombre, 'config' => $config]);
                
                Ajuste::updateOrCreate(
                    ['nombre' => $nombre],
                    [
                        'valor' => $config['valor'],
                        'tipo' => $config['tipo'],
                        'descripcion' => $config['descripcion'],
                        'activo' => true
                    ]
                );
            }

            \Log::info('Información de empresa guardada exitosamente');

            return redirect()->route('ajustes.index')
                           ->with('success', 'Información de la empresa actualizada correctamente. Esta información aparecerá en todos los recibos.');

        } catch (\Exception $e) {
            \Log::error('Error al guardar información de empresa', ['error' => $e->getMessage()]);
            
            return redirect()->route('ajustes.index')
                           ->with('error', 'Error al guardar la información de la empresa: ' . $e->getMessage());
        }
    }

    /**
     * Actualizar mensaje de recordatorio de WhatsApp
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function actualizarRecordatorioWhatsApp(Request $request)
    {
        $request->validate([
            'mensaje_recordatorio' => 'required|string|max:250',
        ], [
            'mensaje_recordatorio.required' => 'El mensaje de recordatorio es obligatorio.',
            'mensaje_recordatorio.max' => 'El mensaje no puede exceder 250 caracteres.',
        ]);

        try {
            Ajuste::updateOrCreate(
                ['nombre' => 'textoRecordatorioWhats'],
                [
                    'valor' => $request->mensaje_recordatorio,
                    'tipo' => 'recordatorio',
                    'descripcion' => 'Mensaje que se enviará al cliente como recordatorio de pago por WhatsApp',
                    'activo' => true
                ]
            );

            return redirect()->route('ajustes.index')
                           ->with('success', 'Mensaje de recordatorio de WhatsApp actualizado correctamente.');

        } catch (\Exception $e) {
            \Log::error('Error al guardar mensaje de recordatorio WhatsApp', ['error' => $e->getMessage()]);
            
            return redirect()->route('ajustes.index')
                           ->with('error', 'Error al guardar el mensaje de recordatorio: ' . $e->getMessage());
        }
    }

    /**
     * Actualizar tolerancia de pagos
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function actualizarToleranciaPagos(Request $request)
    {
        $request->validate([
            'tolerancia_dias' => 'required|integer|min:0|max:365',
        ], [
            'tolerancia_dias.required' => 'La tolerancia en días es obligatoria.',
            'tolerancia_dias.integer' => 'La tolerancia debe ser un número entero.',
            'tolerancia_dias.min' => 'La tolerancia no puede ser menor a 0 días.',
            'tolerancia_dias.max' => 'La tolerancia no puede ser mayor a 365 días.',
        ]);

        try {
            Ajuste::updateOrCreate(
                ['nombre' => 'tolerancia_pagos_dias'],
                [
                    'valor' => $request->tolerancia_dias,
                    'tipo' => 'numero',
                    'descripcion' => 'Número de días de tolerancia antes de que un pago se considere retrasado',
                    'activo' => true
                ]
            );

            return redirect()->route('ajustes.index')
                           ->with('success', 'Tolerancia de pagos actualizada correctamente. Los pagos se mostrarán como retrasados después de ' . $request->tolerancia_dias . ' días de su fecha de vencimiento.');

        } catch (\Exception $e) {
            \Log::error('Error al guardar tolerancia de pagos', ['error' => $e->getMessage()]);
            
            return redirect()->route('ajustes.index')
                           ->with('error', 'Error al guardar la tolerancia de pagos: ' . $e->getMessage());
        }
    }

    /**
     * Actualizar configuración de registro público
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function actualizarRegistroPublico(Request $request)
    {
        $request->validate([
            'registro_publico' => 'required|boolean',
        ]);

        try {
            $activado = filter_var($request->registro_publico, FILTER_VALIDATE_BOOLEAN);

            Ajuste::updateOrCreate(
                ['nombre' => 'registro_publico_activo'],
                [
                    'valor' => $activado ? 'true' : 'false',
                    'tipo' => 'boolean',
                    'descripcion' => 'Permite el registro público de nuevos usuarios sin autenticación de administrador',
                    'activo' => true
                ]
            );

            $mensaje = $activado 
                ? 'Registro público activado. Ahora cualquier persona puede registrarse en el sistema.'
                : 'Registro público desactivado. Solo los administradores pueden crear nuevos usuarios.';

            return redirect()->route('ajustes.index')
                           ->with('success', $mensaje);

        } catch (\Exception $e) {
            \Log::error('Error al actualizar configuración de registro público', ['error' => $e->getMessage()]);
            
            return redirect()->route('ajustes.index')
                           ->with('error', 'Error al actualizar la configuración de registro público: ' . $e->getMessage());
        }
    }
}