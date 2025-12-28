<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use App\Models\Congregacion;

trait BelongsToCongregacion
{
    protected static function bootBelongsToCongregacion(): void
    {
        // Scope global para filtrar por congregación automáticamente
        static::addGlobalScope('congregacion', function (Builder $builder) {
            $congregacionId = session('congregacion_activa_id');
            // SIEMPRE filtrar por congregación - si no hay sesión, usar 0 (no devuelve nada)
            $builder->where((new static)->getTable() . '.congregacion_id', $congregacionId ?? 0);
        });

        // Al crear, asignar congregación automáticamente
        static::creating(function ($model) {
            if (!$model->congregacion_id) {
                $model->congregacion_id = session('congregacion_activa_id');
            }
        });
    }

    public function congregacion()
    {
        return $this->belongsTo(Congregacion::class);
    }

    /**
     * Scope para obtener registros sin filtro de congregación
     */
    public function scopeWithoutCongregacionScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope('congregacion');
    }

    /**
     * Scope para filtrar por una congregación específica
     */
    public function scopeForCongregacion(Builder $query, int $congregacionId): Builder
    {
        return $query->withoutGlobalScope('congregacion')
            ->where('congregacion_id', $congregacionId);
    }
}
