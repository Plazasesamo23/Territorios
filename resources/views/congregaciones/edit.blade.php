@extends('layouts.app')

@section('title', 'Editar Congregación')

@section('content')
<div class="page-header">
    <div class="page-header-content">
        <h1>🏛️ Editar Congregación</h1>
        <p>{{ $congregacion->nombre }}</p>
    </div>
</div>

<div class="card" style="max-width: 600px;">
    <div class="card-body">
        <form action="{{ route('congregaciones.update', $congregacion) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="nombre">Nombre de la Congregación *</label>
                <input type="text" id="nombre" name="nombre" value="{{ old('nombre', $congregacion->nombre) }}"
                       class="form-control @error('nombre') is-invalid @enderror"
                       placeholder="Ej: Centro - Santa Coloma" required>
                @error('nombre')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="codigo">Código Único *</label>
                <input type="text" id="codigo" name="codigo" value="{{ old('codigo', $congregacion->codigo) }}"
                       class="form-control @error('codigo') is-invalid @enderror"
                       placeholder="Ej: centro-sc" required>
                <small style="color: #6b7280;">Identificador único, sin espacios ni caracteres especiales</small>
                @error('codigo')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div style="background: #f0fdf4; border: 1px solid #8aa8d6; border-radius: 0.5rem; padding: 1rem; margin: 1rem 0;">
                <h4 style="margin: 0 0 1rem 0; color: #166534;">Credenciales de Acceso</h4>

                <div class="form-group">
                    <label for="usuario">Usuario para iniciar sesión *</label>
                    <input type="text" id="usuario" name="usuario" value="{{ old('usuario', $congregacion->usuario) }}"
                           class="form-control @error('usuario') is-invalid @enderror"
                           placeholder="Ej: Centro Santa Coloma" required>
                    <small style="color: #6b7280;">Nombre con el que la congregación iniciará sesión</small>
                    @error('usuario')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label for="password_plain">Contraseña *</label>
                    <input type="text" id="password_plain" name="password_plain" value="{{ old('password_plain', $congregacion->password_plain) }}"
                           class="form-control @error('password_plain') is-invalid @enderror"
                           placeholder="Ej: centro123" required>
                    @error('password_plain')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label for="ciudad">Ciudad</label>
                <input type="text" id="ciudad" name="ciudad" value="{{ old('ciudad', $congregacion->ciudad) }}"
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
                          placeholder="Notas adicionales sobre la congregación...">{{ old('descripcion', $congregacion->descripcion) }}</textarea>
                @error('descripcion')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input type="checkbox" name="activa" value="1" {{ old('activa', $congregacion->activa) ? 'checked' : '' }}>
                    <span>Congregación activa</span>
                </label>
            </div>

            <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                <button type="submit" class="btn btn-primary">
                    💾 Guardar Cambios
                </button>
                <a href="{{ route('congregaciones.index') }}" class="btn btn-outline">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card" style="max-width: 600px; margin-top: 1.5rem;">
    <div class="card-header">
        <h3>📊 Estadísticas</h3>
    </div>
    <div class="card-body">
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
            <div style="text-align: center; padding: 1rem; background: #f3f4f6; border-radius: 0.5rem;">
                <div style="font-size: 2rem; font-weight: bold; color: #4a6da7;">{{ $congregacion->territorios()->count() }}</div>
                <div style="font-size: 0.875rem; color: #6b7280;">Territorios</div>
            </div>
            <div style="text-align: center; padding: 1rem; background: #f3f4f6; border-radius: 0.5rem;">
                <div style="font-size: 2rem; font-weight: bold; color: #3d5a8a;">{{ $congregacion->publicadores()->count() }}</div>
                <div style="font-size: 0.875rem; color: #6b7280;">Publicadores</div>
            </div>
            <div style="text-align: center; padding: 1rem; background: #f3f4f6; border-radius: 0.5rem;">
                <div style="font-size: 2rem; font-weight: bold; color: #3d5a8a;">{{ $congregacion->users()->count() }}</div>
                <div style="font-size: 0.875rem; color: #6b7280;">Usuarios</div>
            </div>
        </div>
    </div>
</div>
@endsection
