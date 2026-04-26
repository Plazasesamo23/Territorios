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
     * Verificar si el usuario es usuario de PPOC (solo ver calendario y cambiar turnos)
     */
    public function isPpocUser(): bool
    {
        return $this->role === 'ppoc';
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
            'ppoc' => 'Gestor de PPOC',
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
        if ($this->isAdmin()) {
            return true;
        }
        if ($this->isPpocUser()) {
            return true;
        }
        return (bool) $this->puede_acceder_ppoc;
    }

    /**
     * Verificar si puede gestionar completamente PPOC (crear turnos, ver disponibilidad, etc)
     */
    public function canManagePPOC(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Verificar si puede generar el mes en PPOC
     */
    public function canGeneratePPOC(): bool
    {
        return $this->isAdmin() || $this->isPpocUser();
    }

    /**
     * Verificar si puede cambiar asignaciones en PPOC
     */
    public function canAssignPPOC(): bool
    {
        return $this->isAdmin() || $this->isPpocUser();
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
        return !$this->isTerritoriosUser() && !$this->isPpocUser();
    }

    /**
     * Departamentos a los que pertenece el usuario por su rol.
     * Define qué tareas públicas puede ver/gestionar de cada modulo.
     */
    public function departamentosPropios(): array
    {
        if ($this->isAdmin()) {
            return ['territorios', 'ppoc', 'reuniones', 'administracion', 'general'];
        }
        $deptos = ['general'];
        if ($this->isTerritoriosUser()) $deptos[] = 'territorios';
        if ($this->isPpocUser()) $deptos[] = 'ppoc';
        return $deptos;
    }

    /**
     * Puede crear una tarea publica en el departamento dado.
     * Admin puede en todos. Roles de departamento solo en el suyo.
     */
    public function canCrearTareaPublica(string $departamento): bool
    {
        if ($this->isAdmin()) return true;
        return in_array($departamento, $this->departamentosPropios(), true)
            && $departamento !== 'general';
    }

    /**
     * Puede ver tareas publicas del departamento.
     */
    public function canVerTareasDepartamento(string $departamento): bool
    {
        if ($this->isAdmin()) return true;
        return in_array($departamento, $this->departamentosPropios(), true);
    }

    /**
     * Puede editar/marcar hecha una tarea.
     * El asignado, el creador o un admin.
     */
    public function canEditarTarea(\App\Models\Tarea $tarea): bool
    {
        if ($this->isAdmin()) return true;
        return $tarea->asignado_a === $this->id || $tarea->creado_por === $this->id;
    }

    /**
     * Tareas pendientes del usuario (asignadas o creadas por el).
     */
    public function tareasAsignadas()
    {
        return $this->hasMany(\App\Models\Tarea::class, 'asignado_a');
    }

    /**
     * Tareas que el usuario ha anclado para tener a mano.
     */
    public function tareasAncladas()
    {
        return $this->belongsToMany(\App\Models\Tarea::class, 'tarea_anclajes', 'user_id', 'tarea_id')->withTimestamps();
    }
}
