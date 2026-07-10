<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DisponibilidadPpoc extends Model
{
    use HasFactory;

    protected $table = 'disponibilidad_ppoc';

    protected $fillable = [
        'publicador_id',
        'turno_id',
    ];

    public function publicador(): BelongsTo
    {
        return $this->belongsTo(Publicador::class);
    }

    public function turno(): BelongsTo
    {
        return $this->belongsTo(Turno::class);
    }
}
