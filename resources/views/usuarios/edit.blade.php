@extends('layouts.app')

@section('title', 'Editar Usuario')

@section('content')
<div class="page-nav">
    <div class="page-breadcrumbs">
        <a href="{{ route('dashboard') }}" class="breadcrumb-link">Dashboard</a>
        <span class="breadcrumb-sep">›</span>
        <a href="{{ route('usuarios.index') }}" class="breadcrumb-link">Usuarios</a>
        <span class="breadcrumb-sep">›</span>
        <span class="breadcrumb-current">Editar Usuario</span>
    </div>
</div>

<div class="card" style="max-width: 600px;">
    <div class="card-title">Editar Usuario: {{ $usuario->name }}</div>

    <form action="{{ route('usuarios.update', $usuario) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group mb-4">
            <label for="name" class="form-label">Nombre de Usuario *</label>
            <input type="text" name="name" id="name" class="form-input @error('name') is-invalid @enderror" value="{{ old('name', $usuario->name) }}" required autofocus>
            @error('name')
            <div class="text-danger" style="font-size: 12px; margin-top: 4px;">{{ $message }}</div>
            @enderror
            <div class="text-small text-muted mt-1">Este es el nombre para iniciar sesión</div>
        </div>

        <div class="form-group mb-4">
            <label for="password" class="form-label">Nueva Contraseña</label>
            <input type="password" name="password" id="password" class="form-input @error('password') is-invalid @enderror">
            @error('password')
            <div class="text-danger" style="font-size: 12px; margin-top: 4px;">{{ $message }}</div>
            @enderror
            <div class="text-small text-muted mt-1">Dejar en blanco para mantener la contraseña actual</div>
        </div>

        <div class="form-group mb-4">
            <label for="password_confirmation" class="form-label">Confirmar Nueva Contraseña</label>
            <input type="password" name="password_confirmation" id="password_confirmation" class="form-input">
        </div>

        <div class="form-group mb-4">
            <label for="role" class="form-label">Rol del Usuario *</label>
            @if($usuario->role === 'admin' || $usuario->role === 'superadmin')
            <div style="background: #f8f9fa; padding: 1rem; border-radius: 8px;">
                <span class="badge badge-warning">{{ ucfirst($usuario->role) }}</span>
                <p style="margin: 0.5rem 0 0 0; font-size: 0.85rem; color: #666;">El rol de administrador no puede cambiarse desde aquí</p>
            </div>
            @else
            <select name="role" id="role" class="form-input @error('role') is-invalid @enderror" required>
                <option value="user" {{ old('role', $usuario->role) == 'user' ? 'selected' : '' }}>Usuario (Territorios básico)</option>
                <option value="territorios" {{ old('role', $usuario->role) == 'territorios' ? 'selected' : '' }}>Usuario Territorios</option>
                <option value="ppoc" {{ old('role', $usuario->role) == 'ppoc' ? 'selected' : '' }}>Usuario PPOC</option>
            </select>
            @error('role')
            <div class="text-danger" style="font-size: 12px; margin-top: 4px;">{{ $message }}</div>
            @enderror
            <div class="text-small text-muted mt-1">
                <strong>Usuario:</strong> Permisos básicos en territorios<br>
                <strong>Usuario Territorios:</strong> Acceso directo al panel de territorios<br>
                <strong>Usuario PPOC:</strong> Acceso directo al calendario PPOC
            </div>
            @endif
        </div>

        
        {{-- Permisos especiales (solo para usuarios normales) --}}
        @if($usuario->role === 'user')
        <div class="form-group mb-4" style="background: linear-gradient(135deg, #fdf4ff 0%, #fae8ff 100%); padding: 1rem; border-radius: 8px; border: 1px solid #e879f9;">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <label class="form-label" style="margin-bottom: 0.25rem;">Acceso al modulo PPOC</label>
                    <p style="margin: 0; font-size: 0.85rem; color: #6b7280;">
                        Permite acceder al Programa de Predicacion
                    </p>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox" name="puede_acceder_ppoc" value="1" {{ old('puede_acceder_ppoc', $usuario->puede_acceder_ppoc) ? 'checked' : '' }}>
                    <span class="toggle-slider toggle-purple"></span>
                </label>
            </div>
        </div>
        {{-- Permiso S-13 --}}
        <div class="form-group mb-4" style="background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); padding: 1rem; border-radius: 8px; border: 1px solid #bfdbfe;">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <label class="form-label" style="margin-bottom: 0.25rem;">Permiso para generar S-13</label>
                    <p style="margin: 0; font-size: 0.85rem; color: #6b7280;">
                        Permite a este usuario generar el reporte S-13
                    </p>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox" name="puede_generar_s13" value="1" {{ old('puede_generar_s13', $usuario->puede_generar_s13) ? 'checked' : '' }}>
                    <span class="toggle-slider"></span>
                </label>
            </div>
        </div>
        @endif

        <div class="form-actions">
            <a href="{{ route('usuarios.index') }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
        </div>
    </form>
</div>

<style>
.toggle-switch {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 26px;
}
.toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}
.toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #cbd5e1;
    transition: 0.3s;
    border-radius: 26px;
}
.toggle-slider:before {
    position: absolute;
    content: "";
    height: 20px;
    width: 20px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: 0.3s;
    border-radius: 50%;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}
.toggle-slider.toggle-purple { background-color: #d946ef; }
.toggle-switch input:checked + .toggle-slider.toggle-purple { background-color: #a855f7; }
.toggle-switch input:checked + .toggle-slider {
    background-color: #3b82f6;
}
.toggle-switch input:checked + .toggle-slider:before {
    transform: translateX(24px);
}
</style>
@endsection
