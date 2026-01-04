<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RelacionFamiliar extends Model
{
    protected $table = 'relaciones_familiares';

    protected $fillable = [
        'publicador_id',
        'familiar_id',
        'tipo_relacion',
    ];

    const TIPOS = [
        'conyuge' => 'Cónyuge',
        'progenitor' => 'Padre/Madre',
        'hijo' => 'Hijo/a',
    ];

    public function publicador()
    {
        return $this->belongsTo(Publicador::class, 'publicador_id');
    }

    public function familiar()
    {
        return $this->belongsTo(Publicador::class, 'familiar_id');
    }

    public function getTipoLabelAttribute()
    {
        return self::TIPOS[$this->tipo_relacion] ?? $this->tipo_relacion;
    }

    /**
     * Crear relación bidireccional
     */
    public static function crearRelacion(int $publicadorId, int $familiarId, string $tipo): void
    {
        // Crear relación principal
        self::updateOrCreate(
            ['publicador_id' => $publicadorId, 'familiar_id' => $familiarId],
            ['tipo_relacion' => $tipo]
        );

        // Crear relación inversa
        $tipoInverso = match($tipo) {
            'conyuge' => 'conyuge',
            'progenitor' => 'hijo',
            'hijo' => 'progenitor',
            default => null
        };

        if ($tipoInverso) {
            self::updateOrCreate(
                ['publicador_id' => $familiarId, 'familiar_id' => $publicadorId],
                ['tipo_relacion' => $tipoInverso]
            );
        }
    }

    /**
     * Eliminar relación bidireccional
     */
    public static function eliminarRelacion(int $publicadorId, int $familiarId): int
    {
        return self::where(function ($q) use ($publicadorId, $familiarId) {
            $q->where('publicador_id', $publicadorId)->where('familiar_id', $familiarId);
        })->orWhere(function ($q) use ($publicadorId, $familiarId) {
            $q->where('publicador_id', $familiarId)->where('familiar_id', $publicadorId);
        })->delete();
    }
}
