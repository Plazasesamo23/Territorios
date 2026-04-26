<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\BelongsToCongregacion;

class Tarea extends Model
{
    use BelongsToCongregacion;

    protected $table = 'tareas';

    protected $fillable = [
        'congregacion_id',
        'titulo',
        'descripcion',
        'departamento',
        'asignado_a',
        'creado_por',
        'visibilidad',
        'estado',
        'prioridad',
        'fecha_limite',
        'orden',
        'completada_at',
        'completada_por',
    ];

    protected $casts = [
        'fecha_limite' => 'date',
        'completada_at' => 'datetime',
        'orden' => 'integer',
    ];

    public const DEPARTAMENTOS = [
        'territorios' => 'Territorios',
        'ppoc' => 'PPOC',
        'reuniones' => 'Reuniones VyM',
        'administracion' => 'Administracion',
        'general' => 'General',
    ];

    public const ESTADOS = [
        'pendiente' => 'Pendiente',
        'en_curso' => 'En curso',
        'bloqueada' => 'Bloqueada',
        'hecha' => 'Hecha',
    ];

    public const PRIORIDADES = [
        'baja' => 'Baja',
        'media' => 'Media',
        'alta' => 'Alta',
    ];

    public function asignado()
    {
        return $this->belongsTo(User::class, 'asignado_a');
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function completador()
    {
        return $this->belongsTo(User::class, 'completada_por');
    }

    /**
     * Usuarios que han anclado esta tarea (relacion many-to-many via tarea_anclajes)
     */
    public function ancladaPor()
    {
        return $this->belongsToMany(User::class, 'tarea_anclajes', 'tarea_id', 'user_id')->withTimestamps();
    }

    public function estaAncladaPor(int $userId): bool
    {
        return $this->ancladaPor()->where('users.id', $userId)->exists();
    }

    public function scopeVisiblePara(Builder $q, User $user): Builder
    {
        return $q->where(function ($sub) use ($user) {
            $sub->where('visibilidad', 'publica')
                ->orWhere('asignado_a', $user->id)
                ->orWhere('creado_por', $user->id);
        });
    }

    public function scopeDelDepartamento(Builder $q, string $depto): Builder
    {
        return $q->where('departamento', $depto);
    }

    public function scopePendientes(Builder $q): Builder
    {
        return $q->whereIn('estado', ['pendiente', 'en_curso', 'bloqueada']);
    }

    public function scopeAsignadasA(Builder $q, int $userId): Builder
    {
        return $q->where('asignado_a', $userId);
    }

    public function getEstaVencidaAttribute(): bool
    {
        if (!$this->fecha_limite || $this->estado === 'hecha') return false;
        return $this->fecha_limite->isPast();
    }

    public function getDepartamentoNombreAttribute(): string
    {
        return self::DEPARTAMENTOS[$this->departamento] ?? $this->departamento;
    }
}
