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
        'es_capitan_ppoc',
        'es_coordinador_cuerpo',
        'es_secretario',
        'es_sup_servicio',
        'es_sup_vym',
        'es_sup_atalaya',
        'es_coord_mantenimiento',
        'es_consejero_auxiliar',
        'es_siervo_cuentas',
        'es_siervo_territorios',
        'es_siervo_multimedia',
        'es_siervo_limpieza',
        'es_siervo_publicaciones',
        'es_aux_cuentas',
        'es_aux_territorios',
        'es_aux_multimedia',
        'es_aux_limpieza',
        'es_aux_publicaciones',
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
        'es_coordinador_cuerpo' => 'boolean',
        'es_secretario' => 'boolean',
        'es_sup_servicio' => 'boolean',
        'es_sup_vym' => 'boolean',
        'es_sup_atalaya' => 'boolean',
        'es_coord_mantenimiento' => 'boolean',
        'es_consejero_auxiliar' => 'boolean',
        'es_siervo_cuentas' => 'boolean',
        'es_siervo_territorios' => 'boolean',
        'es_siervo_multimedia' => 'boolean',
        'es_siervo_limpieza' => 'boolean',
        'es_siervo_publicaciones' => 'boolean',
        'es_aux_cuentas' => 'boolean',
        'es_aux_territorios' => 'boolean',
        'es_aux_multimedia' => 'boolean',
        'es_aux_limpieza' => 'boolean',
        'es_aux_publicaciones' => 'boolean',
    ];

    /**
     * Nombramientos del cuerpo de ancianos.
     * Solo aplican a publicadores con es_anciano=true.
     */
    public const NOMBRAMIENTOS_CUERPO = [
        'es_coordinador_cuerpo' => 'Coordinador del cuerpo',
        'es_secretario' => 'Secretario',
        'es_sup_servicio' => 'Sup. de Servicio',
        'es_sup_vym' => 'Sup. de Vida y Ministerio',
        'es_sup_atalaya' => 'Sup. de la Atalaya',
        'es_coord_mantenimiento' => 'Coord. de Mantenimiento',
        'es_consejero_auxiliar' => 'Consejero auxiliar',
    ];

    /**
     * Devuelve los nombramientos del cuerpo activos en este publicador.
     * Si no es anciano, devuelve array vacio.
     */
    public function getNombramientosCuerpoActivosAttribute(): array
    {
        if (!$this->es_anciano) return [];
        $activos = [];
        foreach (self::NOMBRAMIENTOS_CUERPO as $campo => $etiqueta) {
            if ($this->{$campo}) $activos[$campo] = $etiqueta;
        }
        return $activos;
    }

    /**
     * Cargos de siervo ministerial. 5 categorias con titular unico (entre todos los SM
     * de la congregacion solo uno puede ser titular) y auxiliares ilimitados (en la practica 1-3).
     * Solo aplican a publicadores con es_siervo_ministerial=true.
     */
    public const CARGOS_SM_TITULARES = [
        'es_siervo_cuentas' => 'Siervo de cuentas',
        'es_siervo_territorios' => 'Siervo de territorios',
        'es_siervo_multimedia' => 'Siervo de multimedia',
        'es_siervo_limpieza' => 'Siervo de limpieza',
        'es_siervo_publicaciones' => 'Siervo de publicaciones',
    ];

    public const CARGOS_SM_AUXILIARES = [
        'es_aux_cuentas' => 'Aux. de cuentas',
        'es_aux_territorios' => 'Aux. de territorios',
        'es_aux_multimedia' => 'Aux. de multimedia',
        'es_aux_limpieza' => 'Aux. de limpieza',
        'es_aux_publicaciones' => 'Aux. de publicaciones',
    ];

    /**
     * 5 categorias con sus dos campos (titular, aux) para iterar en formularios.
     */
    public const CARGOS_SM_CATEGORIAS = [
        'cuentas' => ['etiqueta' => 'Cuentas', 'titular' => 'es_siervo_cuentas', 'aux' => 'es_aux_cuentas'],
        'territorios' => ['etiqueta' => 'Territorios', 'titular' => 'es_siervo_territorios', 'aux' => 'es_aux_territorios'],
        'multimedia' => ['etiqueta' => 'Multimedia y plataforma', 'titular' => 'es_siervo_multimedia', 'aux' => 'es_aux_multimedia'],
        'limpieza' => ['etiqueta' => 'Limpieza', 'titular' => 'es_siervo_limpieza', 'aux' => 'es_aux_limpieza'],
        'publicaciones' => ['etiqueta' => 'Publicaciones', 'titular' => 'es_siervo_publicaciones', 'aux' => 'es_aux_publicaciones'],
    ];

    /**
     * Devuelve los cargos SM activos. Si no es SM devuelve array vacio.
     */
    public function getCargosSmActivosAttribute(): array
    {
        if (!$this->es_siervo_ministerial) return [];
        $activos = [];
        foreach (self::CARGOS_SM_TITULARES as $campo => $etiqueta) {
            if ($this->{$campo}) $activos[$campo] = $etiqueta;
        }
        foreach (self::CARGOS_SM_AUXILIARES as $campo => $etiqueta) {
            if ($this->{$campo}) $activos[$campo] = $etiqueta;
        }
        return $activos;
    }

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
