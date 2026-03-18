<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToCongregacion;

class ReunionPrograma extends Model
{
    use BelongsToCongregacion;

    protected $table = 'reuniones_programas';

    protected $fillable = [
        'congregacion_id',
        'fecha_semana',
        'presidente_id',
        'oracion_inicio_id',
        'oracion_final_id',
        'conductor_estudio_id',
        'lector_estudio_id',
        'estado',
        'notas',
    ];

    protected $casts = [
        'fecha_semana' => 'date',
    ];

    public function presidente()
    {
        return $this->belongsTo(Publicador::class, 'presidente_id');
    }

    public function oracionInicio()
    {
        return $this->belongsTo(Publicador::class, 'oracion_inicio_id');
    }

    public function oracionFinal()
    {
        return $this->belongsTo(Publicador::class, 'oracion_final_id');
    }

    public function conductorEstudio()
    {
        return $this->belongsTo(Publicador::class, 'conductor_estudio_id');
    }

    public function lectorEstudio()
    {
        return $this->belongsTo(Publicador::class, 'lector_estudio_id');
    }

    public function partes()
    {
        return $this->hasMany(ReunionParte::class, 'programa_id')->orderBy('orden');
    }

    public function historial()
    {
        return $this->hasMany(ReunionHistorial::class, 'programa_id');
    }

    public function partesSeccion(string $seccion)
    {
        return $this->partes()->where('seccion', $seccion)->get();
    }

    public function estaCompleto(): bool
    {
        if (!$this->presidente_id || !$this->oracion_inicio_id || !$this->oracion_final_id) {
            return false;
        }
        if (!$this->conductor_estudio_id || !$this->lector_estudio_id) {
            return false;
        }
        foreach ($this->partes as $parte) {
            if (!$parte->publicador_id) {
                return false;
            }
            if ($parte->necesita_ayudante && !$parte->ayudante_id) {
                return false;
            }
        }
        return true;
    }

    public function contarAsignaciones(): int
    {
        $count = 0;
        if ($this->presidente_id) $count++;
        if ($this->oracion_inicio_id) $count++;
        if ($this->oracion_final_id) $count++;
        if ($this->conductor_estudio_id) $count++;
        if ($this->lector_estudio_id) $count++;
        foreach ($this->partes as $parte) {
            if ($parte->publicador_id) $count++;
            if ($parte->ayudante_id) $count++;
        }
        return $count;
    }

    public function totalPartes(): int
    {
        $count = 5; // presidente, oracion_inicio, oracion_final, conductor, lector
        foreach ($this->partes as $parte) {
            $count++; // principal
            if ($parte->necesita_ayudante) $count++; // ayudante
        }
        return $count;
    }
}
