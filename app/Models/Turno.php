<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToCongregacion;

class Turno extends Model
{
    use BelongsToCongregacion;

    protected $table = 'turnos';

    protected $fillable = [
        'congregacion_id',
        'nombre',
        'dia_semana',
        'numero_turno',
        'hora_inicio',
        'hora_fin',
        'ubicacion',
        'tipo',
        'capacidad',
        'notas',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'dia_semana' => 'integer',
        'numero_turno' => 'integer',
        'capacidad' => 'integer',
    ];

    /**
     * Nombres de los días de la semana (formato europeo: 0=Lunes, 6=Domingo)
     */
    public const DIAS_SEMANA = [
        0 => 'Lunes',
        1 => 'Martes',
        2 => 'Miércoles',
        3 => 'Jueves',
        4 => 'Viernes',
        5 => 'Sábado',
        6 => 'Domingo',
    ];

    /**
     * Tipos de turno disponibles
     */
    public const TIPOS = [
        'predicacion' => 'Predicación',
        'carrito' => 'Carrito',
        'telefonica' => 'Predicación Telefónica',
        'cartas' => 'Cartas',
        'informal' => 'Predicación Informal',
    ];

    /**
     * Relación con la congregación
     */
    public function congregacion()
    {
        return $this->belongsTo(Congregacion::class);
    }

    /**
     * Relación con los turnos generados
     */
    public function turnosGenerados()
    {
        return $this->hasMany(TurnoGenerado::class);
    }

    /**
     * Obtener el nombre del día de la semana
     */
    public function getDiaNombreAttribute(): string
    {
        return self::DIAS_SEMANA[$this->dia_semana] ?? 'Desconocido';
    }

    /**
     * Obtener el nombre del tipo de turno
     */
    public function getTipoNombreAttribute(): string
    {
        return self::TIPOS[$this->tipo] ?? $this->tipo;
    }

    /**
     * Obtener hora formateada
     */
    public function getHorarioAttribute(): string
    {
        return substr($this->hora_inicio, 0, 5) . ' - ' . substr($this->hora_fin, 0, 5);
    }

    /**
     * Obtener descripción completa del turno
     */
    public function getDescripcionAttribute(): string
    {
        return $this->dia_nombre . ' - Turno ' . $this->numero_turno . ' (' . $this->horario . ')';
    }

    /**
     * Obtener fechas del mes donde aplica este turno
     * Usamos dayOfWeekIso (1=Lunes, 7=Domingo) y restamos 1 para formato 0=Lunes
     */
    public function getFechasDelMes(int $year, int $month): array
    {
        $fechas = [];
        $primerDia = \Carbon\Carbon::create($year, $month, 1);
        $ultimoDia = $primerDia->copy()->endOfMonth();

        $actual = $primerDia->copy();
        while ($actual <= $ultimoDia) {
            // dayOfWeekIso: 1=Lunes, 7=Domingo
            // Convertimos a 0=Lunes, 6=Domingo
            $diaActual = $actual->dayOfWeekIso - 1;
            if ($diaActual === $this->dia_semana) {
                $fechas[] = $actual->copy();
            }
            $actual->addDay();
        }

        return $fechas;
    }

    /**
     * Generar turnos para un mes específico
     */
    public function generarParaMes(int $year, int $month): int
    {
        $fechas = $this->getFechasDelMes($year, $month);
        $creados = 0;

        foreach ($fechas as $fecha) {
            // Verificar si ya existe
            $existe = TurnoGenerado::where('turno_id', $this->id)
                ->where('fecha', $fecha->format('Y-m-d'))
                ->exists();

            if (!$existe) {
                TurnoGenerado::create([
                    'turno_id' => $this->id,
                    'congregacion_id' => $this->congregacion_id,
                    'fecha' => $fecha->format('Y-m-d'),
                    'hora_inicio' => $this->hora_inicio,
                    'hora_fin' => $this->hora_fin,
                    'capacidad' => $this->capacidad,
                    'ubicacion' => $this->ubicacion,
                    'estado' => 'abierto',
                ]);
                $creados++;
            }
        }

        return $creados;
    }
}