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

        <div class="form-group mb-4" style="background: #f8f9fa; padding: 1rem; border-radius: 8px;">
            <label class="form-label">Rol Actual</label>
            <p style="margin: 0; color: #666;">
                @if($usuario->role === 'admin')
                <span class="badge badge-warning">Administrador</span>
                @else
                <span class="badge badge-secondary">Usuario</span>
                @endif
            </p>
        </div>

        <div class="form-actions">
            <a href="{{ route('usuarios.index') }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
        </div>
    </form>
</div>
@endsection
