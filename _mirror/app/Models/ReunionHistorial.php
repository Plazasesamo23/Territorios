<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReunionHistorial extends Model
{
    protected $table = 'reuniones_historial';

    public $timestamps = false;

    protected $fillable = [
        'congregacion_id',
        'publicador_id',
        'programa_id',
        'fecha_semana',
        'tipo_parte',
        'rol',
        'es_emergencia',
    ];

    protected $casts = [
        'fecha_semana' => 'date',
        'created_at' => 'datetime',
        'es_emergencia' => 'boolean',
    ];

    public function publicador()
    {
        return $this->belongsTo(Publicador::class, 'publicador_id');
    }

    public function programa()
    {
        return $this->belongsTo(ReunionPrograma::class, 'programa_id');
    }
}
