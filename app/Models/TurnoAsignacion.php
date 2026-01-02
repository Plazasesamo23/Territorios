<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TurnoAsignacion extends Model
{
    protected $table = 'turno_asignaciones';

    protected $fillable = [
        'turno_id',
        'turno_generado_id',
        'publicador_id',
        'fecha',
        'rol',
        'estado',
        'notas',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public const ROLES = [
        'capitan' => 'Capitán',
        'voluntario' => 'Voluntario',
    ];

    public const ESTADOS = [
        'pendiente' => 'Pendiente',
        'confirmado' => 'Confirmado',
        'completado' => 'Completado',
        'cancelado' => 'Cancelado',
    ];

    /**
     * Relación con el turno plantilla
     */
    public function turno()
    {
        return $this->belongsTo(Turno::class);
    }

    /**
     * Relación con el turno generado
     */
    public function turnoGenerado()
    {
        return $this->belongsTo(TurnoGenerado::class, 'turno_generado_id');
    }

    /**
     * Relación con el publicador
     */
    public function publicador()
    {
        return $this->belongsTo(Publicador::class);
    }

    /**
     * Obtener nombre del rol
     */
    public function getRolNombreAttribute(): string
    {
        return self::ROLES[$this->rol] ?? $this->rol;
    }

    /**
     * Obtener nombre del estado
     */
    public function getEstadoNombreAttribute(): string
    {
        return self::ESTADOS[$this->estado] ?? $this->estado;
    }

    /**
     * Scope para filtrar por congregación
     */
    public function scopeDeCongregacion($query, $congregacionId)
    {
        return $query->whereHas('turnoGenerado', function($q) use ($congregacionId) {
            $q->where('congregacion_id', $congregacionId);
        });
    }

    /**
     * Scope para filtrar por mes
     */
    public function scopeDelMes($query, $year, $month)
    {
        return $query->whereYear('fecha', $year)->whereMonth('fecha', $month);
    }
}