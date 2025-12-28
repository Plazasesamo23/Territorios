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

        <div class="form-group mb-4" style="background: #f8f9fa; padding: 1rem; border-radius: 8px;">
            <label class="form-label">Rol del Usuario</label>
            <p style="margin: 0; color: #666;">
                <span class="badge badge-secondary">Usuario</span><br>
                <small>El nuevo usuario tendrá permisos limitados: ver territorios, editar publicadores, asignar territorios y generar S-13.</small>
            </p>
        </div>

        <div class="form-actions">
            <a href="{{ route('usuarios.index') }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Crear Usuario</button>
        </div>
    </form>
</div>
@endsection
