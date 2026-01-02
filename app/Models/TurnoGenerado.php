<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToCongregacion;

class TurnoGenerado extends Model
{
    use BelongsToCongregacion;

    protected $table = 'turnos_generados';

    protected $fillable = [
        'turno_id',
        'congregacion_id',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'capacidad',
        'ubicacion',
        'notas',
        'estado',
    ];

    protected $casts = [
        'fecha' => 'date',
        'capacidad' => 'integer',
    ];

    public const ESTADOS = [
        'abierto' => 'Abierto',
        'completo' => 'Completo',
        'cancelado' => 'Cancelado',
    ];

    /**
     * Relación con la plantilla de turno
     */
    public function turno()
    {
        return $this->belongsTo(Turno::class);
    }

    /**
     * Relación con la congregación
     */
    public function congregacion()
    {
        return $this->belongsTo(Congregacion::class);
    }

    /**
     * Relación con las asignaciones
     */
    public function asignaciones()
    {
        return $this->hasMany(TurnoAsignacion::class, 'turno_generado_id');
    }

    /**
     * Obtener horario formateado
     */
    public function getHorarioAttribute(): string
    {
        return substr($this->hora_inicio, 0, 5) . ' - ' . substr($this->hora_fin, 0, 5);
    }

    /**
     * Verificar si hay espacio disponible
     */
    public function tieneEspacioDisponible(): bool
    {
        return $this->asignaciones()->count() < $this->capacidad;
    }

    /**
     * Obtener espacios disponibles
     */
    public function getEspaciosDisponiblesAttribute(): int
    {
        return max(0, $this->capacidad - $this->asignaciones()->count());
    }

    /**
     * Verificar si está completo
     */
    public function estaCompleto(): bool
    {
        return !$this->tieneEspacioDisponible();
    }

    /**
     * Scope para filtrar por congregación
     */
    public function scopeDeCongregacion($query, $congregacionId)
    {
        return $query->where('congregacion_id', $congregacionId);
    }

    /**
     * Scope para filtrar por mes
     */
    public function scopeDelMes($query, $year, $month)
    {
        return $query->whereYear('fecha', $year)->whereMonth('fecha', $month);
    }

    /**
     * Scope para filtrar por fecha
     */
    public function scopeDeFecha($query, $fecha)
    {
        return $query->where('fecha', $fecha);
    }
}