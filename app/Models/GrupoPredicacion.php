<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToCongregacion;

class GrupoPredicacion extends Model
{
    use BelongsToCongregacion;

    protected $table = 'grupos_predicacion';

    protected $fillable = [
        'congregacion_id',
        'numero',
        'nombre',
    ];

    public function publicadores()
    {
        return $this->hasMany(Publicador::class, 'grupo_predicacion_id');
    }

    public function superintendente()
    {
        return $this->publicadores()->where('es_superintendente', true)->first();
    }

    public function auxiliar()
    {
        return $this->publicadores()->where('es_auxiliar', true)->first();
    }
}
