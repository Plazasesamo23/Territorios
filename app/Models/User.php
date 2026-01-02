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
        'puede_generar_s13',
        'puede_acceder_ppoc',
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
     * Verificar si el usuario es usuario de territorios (solo asignar/devolver)
     */
    public function isTerritoriosUser(): bool
    {
        return $this->role === 'territorios';
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
            'territorios' => 'Gestor de Territorios',
            'user' => 'Usuario',
            default => 'Usuario'
        };
    }

    /**
     * Verificar si puede generar S-13
     */
    public function canGenerateS13(): bool
    {
        // Admins y superadmins siempre pueden
        if ($this->isAdmin()) {
            return true;
        }
        // Usuarios normales solo si tienen el permiso
        return (bool) $this->puede_generar_s13;
    }

    /**
     * Verificar si puede editar territorios (crear, editar, eliminar)
     */
    public function canEditTerritorios(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Verificar si puede asignar/devolver territorios
     */
    public function canAssignTerritorios(): bool
    {
        return $this->isAdmin() || $this->isTerritoriosUser();
    }

    /**
     * Verificar si puede ver territorios
     */
    public function canViewTerritorios(): bool
    {
        return true; // Todos pueden ver
    }

    /**
     * Verificar si puede editar publicadores (crear, editar, eliminar)
     */
    public function canEditPublicadores(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Verificar si puede ver publicadores
     */
    public function canViewPublicadores(): bool
    {
        return true; // Todos pueden ver
    }

    /**
     * Verificar si puede ver estadísticas de publicadores
     */
    public function canViewPublicadorStats(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Verificar si puede acceder al módulo PPOC
     */
    public function canAccessPPOC(): bool
    {
        // Admins y superadmins siempre pueden
        if ($this->isAdmin()) {
            return true;
        }
        // Usuarios normales solo si tienen el permiso
        return (bool) $this->puede_acceder_ppoc;
    }

    /**
     * Verificar si puede acceder a configuración
     */
    public function canAccessConfig(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Verificar si puede acceder al dashboard normal
     */
    public function canAccessDashboard(): bool
    {
        return !$this->isTerritoriosUser();
    }
}
