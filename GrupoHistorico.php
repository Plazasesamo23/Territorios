<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GrupoHistorico extends Model
{
    protected $table = 'grupos_historico';
    public $timestamps = false;

    protected $fillable = [
        'congregacion_id',
        'ano_servicio',
        'grupo_numero',
        'publicador_id',
        'rol'
    ];

    public function publicador()
    {
        return $this->belongsTo(Publicador::class);
    }

    public function congregacion()
    {
        return $this->belongsTo(Congregacion::class);
    }

    // Obtener años disponibles
    public static function anosDisponibles($congregacionId)
    {
        return self::where('congregacion_id', $congregacionId)
            ->select('ano_servicio')
            ->distinct()
            ->orderBy('ano_servicio', 'desc')
            ->pluck('ano_servicio');
    }
}
