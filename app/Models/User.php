<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'congregacion_id',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Relación con la congregación
     */
    public function congregacion(): BelongsTo
    {
        return $this->belongsTo(Congregacion::class);
    }

    /**
     * Verificar si el usuario es superadmin
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'superadmin';
    }

    /**
     * Verificar si el usuario es admin (de su congregación o superadmin)
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin' || $this->isSuperAdmin();
    }

    /**
     * Verificar si el usuario es un usuario normal
     */
    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    /**
     * Verificar si puede acceder a una congregación específica
     */
    public function canAccessCongregacion(int $congregacionId): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }
        return $this->congregacion_id === $congregacionId;
    }

    /**
     * Obtener el nombre del rol en español
     */
    public function getRolNombreAttribute(): string
    {
        return match($this->role) {
            'superadmin' => 'Super Administrador',
            'admin' => 'Administrador',
            'user' => 'Usuario',
            default => 'Usuario'
        };
    }
}
