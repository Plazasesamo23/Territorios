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
        'nombre',
        'apellidos',
        'telefono',
        'notas',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    /**
     * Relación: Un publicador tiene muchos registros
     */
    public function registros()
    {
        return $this->hasMany(Registro::class);
    }

    /**
     * Obtener el último registro activo del publicador
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
