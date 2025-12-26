@extends('layouts.app')

@section('title', 'Nueva Congregación')

@section('content')
<div class="page-header">
    <div class="page-header-content">
        <h1>🏛️ Nueva Congregación</h1>
        <p>Registra una nueva congregación en el sistema</p>
    </div>
</div>

<div class="card" style="max-width: 600px;">
    <div class="card-body">
        <form action="{{ route('congregaciones.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="nombre">Nombre de la Congregación *</label>
                <input type="text" id="nombre" name="nombre" value="{{ old('nombre') }}"
                       class="form-control @error('nombre') is-invalid @enderror"
                       placeholder="Ej: Centro - Santa Coloma" required>
                @error('nombre')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="codigo">Código Único *</label>
                <input type="text" id="codigo" name="codigo" value="{{ old('codigo') }}"
                       class="form-control @error('codigo') is-invalid @enderror"
                       placeholder="Ej: centro-sc" required>
                <small style="color: #6b7280;">Identificador único, sin espacios ni caracteres especiales</small>
                @error('codigo')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div style="background: #f0fdf4; border: 1px solid #86efac; border-radius: 0.5rem; padding: 1rem; margin: 1rem 0;">
                <h4 style="margin: 0 0 1rem 0; color: #166534;">Credenciales de Acceso</h4>

                <div class="form-group">
                    <label for="usuario">Usuario para iniciar sesión *</label>
                    <input type="text" id="usuario" name="usuario" value="{{ old('usuario') }}"
                           class="form-control @error('usuario') is-invalid @enderror"
                           placeholder="Ej: Centro Santa Coloma" required>
                    <small style="color: #6b7280;">Nombre con el que la congregación iniciará sesión</small>
                    @error('usuario')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label for="password_plain">Contraseña *</label>
                    <input type="text" id="password_plain" name="password_plain" value="{{ old('password_plain') }}"
                           class="form-control @error('password_plain') is-invalid @enderror"
                           placeholder="Ej: centro123" required>
                    @error('password_plain')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label for="ciudad">Ciudad</label>
                <input type="text" id="ciudad" name="ciudad" value="{{ old('ciudad') }}"
                       class="form-control @error('ciudad') is-invalid @enderror"
                       placeholder="Ej: Santa Coloma de Gramenet">
                @error('ciudad')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="descripcion">Descripción</label>
                <textarea id="descripcion" name="descripcion" rows="3"
                          class="form-control @error('descripcion') is-invalid @enderror"
                          placeholder="Notas adicionales sobre la congregación...">{{ old('descripcion') }}</textarea>
                @error('descripcion')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                <button type="submit" class="btn btn-primary">
                    💾 Guardar Congregación
                </button>
                <a href="{{ route('congregaciones.index') }}" class="btn btn-outline">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
