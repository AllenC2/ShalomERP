<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ruta extends Model
{
    protected $perPage = 20;

    const ESTADO_PLANEADA = 'planeada';
    const ESTADO_EN_CURSO = 'en_curso';
    const ESTADO_COMPLETADA = 'completada';
    const ESTADO_CANCELADA = 'cancelada';

    public static function getEstadosValidos()
    {
        return [
            self::ESTADO_PLANEADA => 'Planeada',
            self::ESTADO_EN_CURSO => 'En Curso',
            self::ESTADO_COMPLETADA => 'Completada',
            self::ESTADO_CANCELADA => 'Cancelada',
        ];
    }

    protected $fillable = [
        'empleado_id',
        'fecha',
        'fecha_limite',
        'estado',
        'notas',
        'punto_casa',
        'user_id',
    ];

    protected $casts = [
        'fecha' => 'date',
        'fecha_limite' => 'date',
    ];

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function paradas()
    {
        return $this->hasMany(RutaParada::class, 'ruta_id')->orderBy('orden');
    }

    public function getParadasCompletadasCountAttribute()
    {
        return $this->paradas()->where('estado', 'visitada')->count();
    }

    public function getParadasTotalesCountAttribute()
    {
        return $this->paradas()->count();
    }

    public function getProgresoAttribute()
    {
        $total = $this->paradas_totales_count;
        if ($total === 0) return 0;
        return round(($this->paradas_completadas_count / $total) * 100);
    }

    public function getEstadoBadgeAttribute()
    {
        return match ($this->estado) {
            self::ESTADO_PLANEADA => 'bg-info',
            self::ESTADO_EN_CURSO => 'bg-warning',
            self::ESTADO_COMPLETADA => 'bg-success',
            self::ESTADO_CANCELADA => 'bg-danger',
            default => 'bg-secondary',
        };
    }

    public function getEstadoLabelAttribute()
    {
        return self::getEstadosValidos()[$this->estado] ?? $this->estado;
    }
}
