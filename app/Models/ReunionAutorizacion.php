<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReunionAutorizacion extends Model
{
    protected $table = 'reuniones_autorizaciones';

    protected $fillable = [
        'publicador_id',
        'congregacion_id',
        'tipo_parte',
    ];

    public function publicador()
    {
        return $this->belongsTo(Publicador::class);
    }
}
