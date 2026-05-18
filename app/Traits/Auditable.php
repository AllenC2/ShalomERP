<?php

namespace App\Traits;

use App\Models\LogAuditoria;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait Auditable
{
    // Propiedades temporales en memoria para guardar los estados de cambios del ciclo de vida
    // Evita que Eloquent los trate como atributos dinámicos de columnas de base de datos
    public $auditTempOldState;
    public $auditTempNewState;

    /**
     * Boot del Trait de Auditoría. Registra eventos Eloquent automáticamente.
     */
    public static function bootAuditable()
    {
        // 1. INSERT: Registro minimalista sin duplicar datos en disco
        static::created(function ($model) {
            $model->writeAuditLog('INSERT', null, null);
        });

        // 2. UPDATE - Fase de Captura: Obtenemos modificaciones antes de guardar en BD
        static::updating(function ($model) {
            // Excluir campos configurados en el modelo o por defecto ['observaciones', 'documento']
            $excludeFields = $model->dontAudit ?? ['observaciones', 'documento'];
            
            $dirty = $model->getDirty();
            $old = [];
            $new = [];

            foreach ($dirty as $key => $value) {
                if (in_array($key, $excludeFields)) {
                    continue;
                }

                $original = $model->getOriginal($key);
                
                // Solo loggear si el valor realmente cambió
                if ($original !== $value) {
                    $old[$key] = $original;
                    $new[$key] = $value;
                }
            }

            // Guardamos temporalmente en memoria del objeto para escribir tras el éxito
            if (!empty($new)) {
                $model->auditTempOldState = $old;
                $model->auditTempNewState = $new;
            }
        });

        // 2. UPDATE - Fase de Escritura: Escribimos sólo si la actualización en base de datos fue exitosa
        static::updated(function ($model) {
            if (isset($model->auditTempNewState) && !empty($model->auditTempNewState)) {
                $model->writeAuditLog('UPDATE', $model->auditTempOldState, $model->auditTempNewState);
                
                // Limpiar memoria
                unset($model->auditTempOldState);
                unset($model->auditTempNewState);
            }
        });

        // 3. DELETE: Respaldo forense completo (excluyendo masivos) antes de la eliminación física
        static::deleting(function ($model) {
            $excludeFields = $model->dontAudit ?? ['observaciones', 'documento'];
            $state = $model->getOriginal();

            foreach ($excludeFields as $field) {
                unset($state[$field]);
            }

            $model->writeAuditLog('DELETE', $state, null);
        });
    }

    /**
     * Escribe el registro en la tabla centralizada de auditoría.
     */
    protected function writeAuditLog(string $action, ?array $oldState, ?array $newState): void
    {
        try {
            LogAuditoria::create([
                'tabla_nombre' => $this->getTable(),
                'registro_id'  => $this->getKey(),
                'accion'       => $action,
                'estado_anterior' => $oldState,
                'estado_nuevo'    => $newState,
                'usuario_id'   => Auth::id(), // Captura automática de usuario en sesión backend
                'ip_direccion' => Request::ip(), // Captura automática de dirección IP
            ]);
        } catch (\Exception $e) {
            // Loggear la excepción en el log local del sistema para evitar que un fallo de auditoría
            // rompa la transacción de negocio principal
            \Illuminate\Support\Facades\Log::error("Fallo al escribir log de auditoría: " . $e->getMessage());
        }
    }
}
