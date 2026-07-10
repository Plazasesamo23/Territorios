<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Registro extends Model
{
    protected $table = 'registros';

    protected $fillable = [
        'territorio_id',
        'publicador_id',
        'fecha_salida',
        'fecha_entrada',
        'entrada_prevista',
        'notas'
    ];

    protected $casts = [
        'fecha_salida' => 'date',
        'fecha_entrada' => 'date',
        'entrada_prevista' => 'date',
    ];

    /**
     * Relación: Un registro pertenece a un territorio
     */
    public function territorio()
    {
        return $this->belongsTo(Territorio::class);
    }

    /**
     * Relación: Un registro pertenece a un publicador
     */
    public function publicador()
    {
        return $this->belongsTo(Publicador::class);
    }

    /**
     * Verificar si el registro está activo (sin fecha de entrada)
     */
    public function esActivo()
    {
        return is_null($this->fecha_entrada);
    }

    /**
     * Calcular días transcurridos desde la salida
     */
    public function diasTranscurridos()
    {
        return $this->fecha_salida->diffInDays(now());
    }
}
