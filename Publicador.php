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
        'excluido_reuniones',
        'puede_dirigir_estudio',
        'puede_leer_estudio',
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
        'excluido_reuniones' => 'boolean',
        'puede_dirigir_estudio' => 'boolean',
        'puede_leer_estudio' => 'boolean',
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
     * Conyuge (matrimonio)
     */
    public function conyuge()
    {
        return $this->hasOne(\App\Models\RelacionFamiliar::class, 'publicador_id')
            ->where('tipo_relacion', 'conyuge');
    }

    /**
     * Obtener el ID del conyuge
     */
    public function getConyugeId(): ?int
    {
        $rel = \Illuminate\Support\Facades\DB::table('relaciones_familiares')
            ->where('publicador_id', $this->id)
            ->where('tipo_relacion', 'conyuge')
            ->first();
        return $rel ? (int)$rel->familiar_id : null;
    }

    /**
     * Autorizaciones de reunion
     */
    public function reunionAutorizaciones()
    {
        return $this->hasMany(ReunionAutorizacion::class);
    }

    /**
     * Puede hacer una parte especifica de la reunion VyM
     * Consulta la tabla reuniones_autorizaciones
     */
    public function puedeHacerParte(string $tipoParte): bool
    {
        if (!$this->activo || !$this->genero || $this->excluido_reuniones) {
            return false;
        }

        // Mapear tipos de parte a tipos de autorizacion
        $tipoAuth = match ($tipoParte) {
            'presidente' => 'presidente',
            'oracion_inicio', 'oracion_final' => 'oracion',
            'discurso_tesoros' => 'tesoros',
            'perlas' => 'perlas',
            'lectura' => 'lectura',
            'empiece_conversaciones', 'haga_revisitas', 'haga_discipulos', 'explique_creencias' => 'maestros',
            'discurso_maestros' => 'discurso_maestros',
            'discurso_vida' => 'discurso_vida',
            'necesidades' => 'necesidades',
            'conductor_estudio' => 'conductor_estudio',
            'lector_estudio' => 'lector_estudio',
            'ayudante' => 'maestros',
            default => null,
        };

        if (!$tipoAuth) return false;

        return ReunionAutorizacion::where('publicador_id', $this->id)
            ->where('tipo_parte', $tipoAuth)
            ->exists();
    }
}
