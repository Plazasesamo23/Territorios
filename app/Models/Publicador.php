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
        'genero',
        'telefono',
        'notas',
        'activo',
        'aprobado_ppoc',
        'es_precursor',
        'es_superintendente',
        'es_auxiliar',
        'orden_grupo',
        'es_anciano',
        'es_siervo_ministerial',
        'es_menor',
        'es_capitan_ppoc'
    ];

    protected $casts = [
        'activo' => 'boolean',
        'aprobado_ppoc' => 'boolean',
        'es_precursor' => 'boolean',
        'es_superintendente' => 'boolean',
        'es_auxiliar' => 'boolean',
        'es_anciano' => 'boolean',
        'es_siervo_ministerial' => 'boolean',
        'es_menor' => 'boolean',
        'es_capitan_ppoc' => 'boolean',
        'orden_grupo' => 'integer',
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

    /**
     * Disponibilidades PPOC
     */
    public function disponibilidadesPpoc()
    {
        return $this->hasMany(DisponibilidadPpoc::class);
    }

    /**
     * Historial de reuniones
     */
    public function reunionHistorial()
    {
        return $this->hasMany(ReunionHistorial::class);
    }

    /**
     * Es hermano (varon)
     */
    public function esHermano(): bool
    {
        return $this->genero === 'M';
    }

    /**
     * Puede hacer una parte especifica de la reunion VyM
     */
    public function puedeHacerParte(string $tipoParte): bool
    {
        if (!$this->activo || !$this->genero) {
            return false;
        }

        return match ($tipoParte) {
            'presidente' => $this->es_anciano,
            'oracion_inicio', 'oracion_final' => $this->esHermano(),
            'discurso_tesoros', 'perlas' => $this->esHermano() && ($this->es_anciano || $this->es_siervo_ministerial),
            'lectura' => $this->esHermano(),
            'empiece_conversaciones', 'haga_revisitas', 'haga_discipulos', 'explique_creencias' => true,
            'discurso_vida' => $this->esHermano() && ($this->es_anciano || $this->es_siervo_ministerial),
            'conductor_estudio' => $this->es_anciano,
            'lector_estudio' => $this->esHermano(),
            'ayudante' => true,
            default => false,
        };
    }
}
