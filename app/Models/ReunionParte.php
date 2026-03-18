<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReunionParte extends Model
{
    protected $table = 'reuniones_partes';

    protected $fillable = [
        'programa_id',
        'seccion',
        'tipo',
        'titulo',
        'duracion_minutos',
        'orden',
        'publicador_id',
        'ayudante_id',
        'necesita_ayudante',
    ];

    protected $casts = [
        'duracion_minutos' => 'integer',
        'orden' => 'integer',
        'necesita_ayudante' => 'boolean',
    ];

    public function programa()
    {
        return $this->belongsTo(ReunionPrograma::class, 'programa_id');
    }

    public function publicador()
    {
        return $this->belongsTo(Publicador::class, 'publicador_id');
    }

    public function ayudante()
    {
        return $this->belongsTo(Publicador::class, 'ayudante_id');
    }

    public function getNombreSeccionAttribute(): string
    {
        return match ($this->seccion) {
            'tesoros' => 'Tesoros de la Biblia',
            'maestros' => 'Seamos mejores maestros',
            'vida_cristiana' => 'Nuestra vida cristiana',
            default => $this->seccion,
        };
    }

    public function getNombreTipoAttribute(): string
    {
        return match ($this->tipo) {
            'discurso_tesoros' => 'Discurso',
            'perlas' => 'Perlas escondidas',
            'lectura' => 'Lectura de la Biblia',
            'empiece_conversaciones' => 'Empiece conversaciones',
            'haga_revisitas' => 'Haga revisitas',
            'haga_discipulos' => 'Haga discipulos',
            'explique_creencias' => 'Explique sus creencias',
            'discurso_vida' => 'Discurso',
            default => $this->tipo,
        };
    }
}
