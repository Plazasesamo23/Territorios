@extends('layouts.app')

@section('title', 'Usuarios')

@section('content')
<div class="page-nav">
    <div class="page-breadcrumbs">
        <a href="{{ route('dashboard') }}" class="breadcrumb-link">Dashboard</a>
        <span class="breadcrumb-sep">></span>
        <span class="breadcrumb-current">Usuarios</span>
        @if(isset($congregacion))
        <span class="breadcrumb-sep">|</span>
        <span style="color:#0d6efd;font-weight:bold;">{{ $congregacion->nombre }}</span>
        @endif
    </div>

    <div class="page-actions">
        <a href="{{ route('usuarios.create') }}" class="btn btn-primary">
            + Nuevo Usuario
        </a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success mb-4">
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="alert alert-danger mb-4">
    {{ session('error') }}
</div>
@endif

<div class="card">
    <div class="card-title">Usuarios de la Congregacion</div>

    @if($usuarios->count() > 0)
    <!-- Vista de tabla para desktop -->
    <div class="usuarios-table-desktop">
        <table class="usuarios-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Creado</th>
                    <th style="width: 180px;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($usuarios as $usuario)
                <tr>
                    <td data-label="Nombre">
                        <strong>{{ $usuario->name }}</strong>
                        @if($usuario->id === auth()->id())
                        <span class="badge badge-info" style="font-size: 10px; margin-left: 5px;">(Tu)</span>
                        @endif
                    </td>
                    <td data-label="Email">{{ $usuario->email }}</td>
                    <td data-label="Rol">
                        @if($usuario->role === 'admin')
                        <span class="badge badge-warning">Administrador</span>
                        @elseif($usuario->role === 'superadmin')
                        <span class="badge badge-danger">Super Admin</span>
                        @else
                        <span class="badge badge-secondary">Usuario</span>
                        @endif
                    </td>
                    <td data-label="Creado">{{ $usuario->created_at->format('d/m/Y') }}</td>
                    <td data-label="Acciones" class="acciones-cell">
                        @if(!$usuario->isSuperAdmin())
                        <a href="{{ route('usuarios.edit', $usuario) }}" class="btn btn-sm btn-secondary">
                            Editar
                        </a>
                        @if($usuario->id !== auth()->id() && !$usuario->isAdmin())
                        <form action="{{ route('usuarios.destroy', $usuario) }}" method="POST" style="display: inline;" onsubmit="return confirm('Estas seguro de eliminar este usuario?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                        </form>
                        @endif
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Vista de cards para movil -->
    <div class="usuarios-cards-mobile">
        @foreach($usuarios as $usuario)
        <div class="usuario-card">
            <div class="usuario-card-header">
                <div class="usuario-nombre">
                    {{ $usuario->name }}
                    @if($usuario->id === auth()->id())
                    <span class="badge badge-info">(Tu)</span>
                    @endif
                </div>
                @if($usuario->role === 'admin')
                <span class="badge badge-warning">Admin</span>
                @elseif($usuario->role === 'superadmin')
                <span class="badge badge-danger">Super</span>
                @else
                <span class="badge badge-secondary">Usuario</span>
                @endif
            </div>
            <div class="usuario-card-body">
                <div class="usuario-info-row">
                    <span class="usuario-label">Email:</span>
                    <span class="usuario-value">{{ $usuario->email }}</span>
                </div>
                <div class="usuario-info-row">
                    <span class="usuario-label">Creado:</span>
                    <span class="usuario-value">{{ $usuario->created_at->format('d/m/Y') }}</span>
                </div>
            </div>
            @if(!$usuario->isSuperAdmin())
            <div class="usuario-card-actions">
                <a href="{{ route('usuarios.edit', $usuario) }}" class="btn btn-sm btn-secondary">
                    Editar
                </a>
                @if($usuario->id !== auth()->id() && !$usuario->isAdmin())
                <form action="{{ route('usuarios.destroy', $usuario) }}" method="POST" style="display: inline;" onsubmit="return confirm('Estas seguro de eliminar este usuario?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                </form>
                @endif
            </div>
            @endif
        </div>
        @endforeach
    </div>
    @else
    <p style="text-align: center; color: #666; padding: 2rem;">
        No hay usuarios en esta congregacion.<br>
        <a href="{{ route('usuarios.create') }}">Crear el primer usuario</a>
    </p>
    @endif
</div>

<div class="card mt-4">
    <div class="card-title">Informacion sobre Roles</div>
    <div class="roles-grid">
        <div class="rol-card rol-admin">
            <div class="rol-header">
                <span class="rol-icon">&#128081;</span>
                <span class="badge badge-warning">Administrador</span>
            </div>
            <ul class="rol-permisos">
                <li>Gestion completa de territorios</li>
                <li>Gestion completa de publicadores</li>
                <li>Crear y gestionar usuarios</li>
                <li>Configuracion de la congregacion</li>
            </ul>
        </div>
        <div class="rol-card rol-user">
            <div class="rol-header">
                <span class="rol-icon">&#128100;</span>
                <span class="badge badge-secondary">Usuario</span>
            </div>
            <ul class="rol-permisos">
                <li>Ver territorios</li>
                <li>Ver y editar publicadores</li>
                <li>Asignar/devolver territorios</li>
                <li>Generar reporte S-13</li>
            </ul>
        </div>
    </div>
</div>

<style>
/* Tabla de usuarios */
.usuarios-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1rem;
}
.usuarios-table th,
.usuarios-table td {
    padding: 0.875rem 1rem;
    text-align: left;
    border-bottom: 1px solid var(--border-color, #e5e7eb);
}
.usuarios-table th {
    background: var(--color-gray-50, #f9fafb);
    font-weight: 600;
    color: var(--text-secondary, #6b7280);
    font-size: 0.875rem;
}
.usuarios-table tbody tr:hover {
    background: var(--color-gray-50, #f9fafb);
}
.acciones-cell {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

/* Cards movil - oculto por defecto */
.usuarios-cards-mobile {
    display: none;
}
.usuario-card {
    background: var(--bg-card, #fff);
    border: 1px solid var(--border-color, #e5e7eb);
    border-radius: 12px;
    padding: 1rem;
    margin-bottom: 1rem;
}
.usuario-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.75rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid var(--border-light, #f3f4f6);
}
.usuario-nombre {
    font-weight: 600;
    font-size: 1.1rem;
    color: var(--text-primary, #333);
}
.usuario-card-body {
    margin-bottom: 0.75rem;
}
.usuario-info-row {
    display: flex;
    justify-content: space-between;
    padding: 0.375rem 0;
    font-size: 0.875rem;
}
.usuario-label {
    color: var(--text-muted, #9ca3af);
}
.usuario-value {
    color: var(--text-primary, #333);
    font-weight: 500;
}
.usuario-card-actions {
    display: flex;
    gap: 0.5rem;
    padding-top: 0.75rem;
    border-top: 1px solid var(--border-light, #f3f4f6);
}
.usuario-card-actions .btn {
    flex: 1;
    justify-content: center;
}

/* Grid de roles */
.roles-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
    margin-top: 1rem;
}
.rol-card {
    background: var(--color-gray-50, #f9fafb);
    border-radius: 12px;
    padding: 1.25rem;
    border: 2px solid transparent;
}
.rol-admin {
    border-color: #fbbf24;
    background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
}
.rol-user {
    border-color: #9ca3af;
    background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
}
.rol-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1rem;
}
.rol-icon {
    font-size: 1.5rem;
}
.rol-permisos {
    margin: 0;
    padding: 0;
    list-style: none;
}
.rol-permisos li {
    padding: 0.5rem 0;
    font-size: 0.875rem;
    color: var(--text-secondary, #6b7280);
    border-bottom: 1px dashed var(--border-light, #e5e7eb);
    padding-left: 0.5rem;
}
.rol-permisos li:last-child {
    border-bottom: none;
}
.rol-admin .rol-permisos li::before {
    content: "OK ";
    color: #10b981;
    font-weight: bold;
}
.rol-user .rol-permisos li::before {
    content: "- ";
    color: #6b7280;
}

/* Responsive */
@media (max-width: 768px) {
    .usuarios-table-desktop {
        display: none;
    }
    .usuarios-cards-mobile {
        display: block;
        margin-top: 1rem;
    }
    .roles-grid {
        grid-template-columns: 1fr;
    }
}
</style>
@endsection
