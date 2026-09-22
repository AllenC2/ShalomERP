<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RutaPlantillaParada extends Model
{
    protected $fillable = [
        'plantilla_id',
        'orden',
        'contrato_id',
        'cliente_id',
    ];

    public function plantilla()
    {
        return $this->belongsTo(RutaPlantilla::class, 'plantilla_id');
    }

    public function contrato()
    {
        return $this->belongsTo(Contrato::class, 'contrato_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }
}
