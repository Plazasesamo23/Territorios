@extends('layouts.app')

@section('title', 'Crear Usuario')

@section('content')
<div class="page-nav">
    <div class="page-breadcrumbs">
        <a href="{{ route('dashboard') }}" class="breadcrumb-link">Dashboard</a>
        <span class="breadcrumb-sep">›</span>
        <a href="{{ route('usuarios.index') }}" class="breadcrumb-link">Usuarios</a>
        <span class="breadcrumb-sep">›</span>
        <span class="breadcrumb-current">Crear Usuario</span>
    </div>
</div>

<div class="card" style="max-width: 600px;">
    <div class="card-title">Nuevo Usuario</div>

    <form action="{{ route('usuarios.store') }}" method="POST">
        @csrf

        <div class="form-group mb-4">
            <label for="name" class="form-label">Nombre de Usuario *</label>
            <input type="text" name="name" id="name" class="form-input @error('name') is-invalid @enderror" value="{{ old('name') }}" required autofocus placeholder="Ej: Juan, Maria, Pedro...">
            @error('name')
            <div class="text-danger" style="font-size: 12px; margin-top: 4px;">{{ $message }}</div>
            @enderror
            <div class="text-small text-muted mt-1">Este será el nombre para iniciar sesión</div>
        </div>

        <div class="form-group mb-4">
            <label for="password" class="form-label">Contraseña *</label>
            <input type="password" name="password" id="password" class="form-input @error('password') is-invalid @enderror" required>
            @error('password')
            <div class="text-danger" style="font-size: 12px; margin-top: 4px;">{{ $message }}</div>
            @enderror
            <div class="text-small text-muted mt-1">Mínimo 6 caracteres</div>
        </div>

        <div class="form-group mb-4">
            <label for="password_confirmation" class="form-label">Confirmar Contraseña *</label>
            <input type="password" name="password_confirmation" id="password_confirmation" class="form-input" required>
        </div>

        <div class="form-group mb-4">
            <label for="role" class="form-label">Rol del Usuario *</label>
            <select name="role" id="role" class="form-input @error('role') is-invalid @enderror" required>
                <option value="user" {{ old('role') == 'user' ? 'selected' : '' }}>Usuario (Territorios básico)</option>
                <option value="territorios" {{ old('role') == 'territorios' ? 'selected' : '' }}>Usuario Territorios</option>
                <option value="ppoc" {{ old('role') == 'ppoc' ? 'selected' : '' }}>Usuario PPOC</option>
            </select>
            @error('role')
            <div class="text-danger" style="font-size: 12px; margin-top: 4px;">{{ $message }}</div>
            @enderror
            <div class="text-small text-muted mt-1">
                <strong>Usuario:</strong> Permisos básicos en territorios<br>
                <strong>Usuario Territorios:</strong> Acceso directo al panel de territorios<br>
                <strong>Usuario PPOC:</strong> Acceso directo al calendario PPOC
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('usuarios.index') }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Crear Usuario</button>
        </div>
    </form>
</div>
@endsection
