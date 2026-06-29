@extends('layouts.app')

@section('title', 'Mi Perfil')

@section('content')
<div class="page-header">
    <div class="page-header-content">
        <h1>👤 Mi Perfil</h1>
        <p>Gestiona tu información personal y contraseña</p>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 1.5rem;">
    {{-- Información del usuario --}}
    <div class="card">
        <div class="card-header">
            <h3>📋 Información Personal</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('perfil.nombre') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="name">Nombre</label>
                    <input type="text" id="name" name="name" value="{{ old('name', Auth::user()->name) }}"
                           class="form-control @error('name') is-invalid @enderror" required>
                    @error('name')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Correo Electrónico</label>
                    <input type="email" value="{{ Auth::user()->email }}" class="form-control" disabled
                           style="background: #1f2024; color: #9ca3af; cursor: not-allowed;">
                    <small style="color: #9ca3af;">El correo no se puede cambiar</small>
                </div>

                <div class="form-group">
                    <label>Rol</label>
                    <input type="text" value="{{ Auth::user()->rol_nombre }}" class="form-control" disabled
                           style="background: #1f2024; color: #9ca3af; cursor: not-allowed;">
                </div>

                @if(Auth::user()->congregacion)
                <div class="form-group">
                    <label>Congregación</label>
                    <input type="text" value="{{ Auth::user()->congregacion->nombre }}" class="form-control" disabled
                           style="background: #1f2024; color: #9ca3af; cursor: not-allowed;">
                </div>
                @endif

                <button type="submit" class="btn btn-primary">
                    💾 Guardar Nombre
                </button>
            </form>
        </div>
    </div>

    {{-- Cambiar contraseña --}}
    <div class="card">
        <div class="card-header">
            <h3>🔐 Cambiar Contraseña</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('perfil.password') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="password">Nueva Contraseña</label>
                    <input type="password" id="password" name="password"
                           class="form-control @error('password') is-invalid @enderror" required>
                    @error('password')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirmar Nueva Contraseña</label>
                    <input type="password" id="password_confirmation" name="password_confirmation"
                           class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary">
                    🔑 Cambiar Contraseña
                </button>
            </form>
        </div>
    </div>
</div>

<style>
    .form-group {
        margin-bottom: 1rem;
    }
    .form-group label {
        display: block;
        font-weight: 500;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
    }
    .form-control {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid #2d3339;
        border-radius: 0.5rem;
        font-size: 1rem;
        background: #151719;
        color: #f1f3f5;
        transition: border-color 0.2s;
    }
    .form-control:focus {
        outline: none;
        border-color: #6b8fc7;
        box-shadow: 0 0 0 3px rgba(107, 143, 199, 0.12);
    }
    .form-control.is-invalid {
        border-color: #ff6b6b;
    }
    .error-message {
        color: #ff8a80;
        font-size: 0.75rem;
        margin-top: 0.25rem;
        display: block;
    }
</style>
@endsection
