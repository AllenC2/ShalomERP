<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogAuditoria extends Model
{
    protected $table = 'logs_auditoria';

    // Deshabilitamos timestamps automáticos de Laravel porque usamos created_at de BD
    public $timestamps = false;

    protected $fillable = [
        'tabla_nombre',
        'registro_id',
        'accion',
        'estado_anterior',
        'estado_nuevo',
        'usuario_id',
        'ip_direccion',
    ];

    protected $casts = [
        'estado_anterior' => 'array',
        'estado_nuevo' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Relación con el usuario que ejecutó la acción.
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
