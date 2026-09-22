<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class RutaPlantilla extends Model
{
    const FRECUENCIA_DIARIA = 'diaria';
    const FRECUENCIA_SEMANAL = 'semanal';
    const FRECUENCIA_MENSUAL = 'mensual';

    protected $fillable = [
        'nombre',
        'empleado_id',
        'frecuencia',
        'dia_semana',
        'dia_mes',
        'punto_casa',
        'activa',
        'user_id',
    ];

    protected $casts = [
        'activa' => 'boolean',
        'dia_semana' => 'integer',
        'dia_mes' => 'integer',
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
        return $this->hasMany(RutaPlantillaParada::class, 'plantilla_id')->orderBy('orden');
    }

    public function ejecuciones()
    {
        return $this->hasMany(Ruta::class, 'plantilla_id');
    }

    public function correspondeA(Carbon $fecha): bool
    {
        return match ($this->frecuencia) {
            self::FRECUENCIA_DIARIA => true,
            self::FRECUENCIA_SEMANAL => (int) $fecha->dayOfWeekIso === (int) $this->dia_semana,
            self::FRECUENCIA_MENSUAL => (int) $fecha->day === min((int) $this->dia_mes, $fecha->daysInMonth),
            default => false,
        };
    }

    public function etiquetaFrecuencia(): string
    {
        return match ($this->frecuencia) {
            self::FRECUENCIA_DIARIA => 'Diaria',
            self::FRECUENCIA_SEMANAL => 'Semanal (' . $this->nombreDiaSemana() . ')',
            self::FRECUENCIA_MENSUAL => 'Mensual (día ' . $this->dia_mes . ')',
            default => $this->frecuencia,
        };
    }

    public function nombreDiaSemana(): string
    {
        return [
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado',
            7 => 'Domingo',
        ][(int) $this->dia_semana] ?? '';
    }
}
