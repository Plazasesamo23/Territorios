<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToCongregacion;

class Publicador extends Model
{
    use BelongsToCongregacion;

    protected $table = 'publicadores';

    protected $fillable = [
        'congregacion_id',
        'grupo_predicacion_id',
        'nombre',
        'apellidos',
        'telefono',
        'notas',
        'activo',
        'aprobado_ppoc',
        'es_precursor',
        'es_superintendente',
        'es_auxiliar',
        'orden_grupo'
    ];

    protected $casts = [
        'activo' => 'boolean',
        'aprobado_ppoc' => 'boolean',
        'es_precursor' => 'boolean',
        'es_superintendente' => 'boolean',
        'es_auxiliar',
        'orden_grupo' => 'boolean',
    ];

    /**
     * Relacion con grupo de predicacion
     */
    public function grupoPredicacion()
    {
        return $this->belongsTo(GrupoPredicacion::class, 'grupo_predicacion_id');
    }

    /**
     * Relacion: Un publicador tiene muchos registros
     */
    public function registros()
    {
        return $this->hasMany(Registro::class);
    }

    /**
     * Obtener el ultimo registro activo del publicador
     */
    public function ultimoRegistroActivo()
    {
        return $this->registros()->whereNull('fecha_entrada')->latest('fecha_salida')->first();
    }

    /**
     * Obtener el nombre completo
     */
    public function getNombreCompletoAttribute()
    {
        return trim($this->nombre . ' ' . $this->apellidos);
    }
}
