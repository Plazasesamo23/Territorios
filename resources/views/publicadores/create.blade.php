@extends('layouts.app')

@section('title', 'Nuevo Publicador - Gestión de Territorios')

@section('content')
<!-- Navegación minimalista -->
<nav class="page-nav">
    <div class="page-breadcrumbs">
        <a href="{{ route('dashboard') }}" class="breadcrumb-link">Dashboard</a>
        <span class="breadcrumb-separator">›</span>
        <a href="{{ route('publicadores.index') }}" class="breadcrumb-link">Publicadores</a>
        <span class="breadcrumb-separator">›</span>
        <span class="breadcrumb-current">Nuevo</span>
    </div>
    <div class="page-actions">
        <a href="{{ route('publicadores.index') }}" class="btn btn-secondary">
            ← Volver
        </a>
    </div>
</nav>

<div class="grid grid-2">
    <!-- Formulario Principal -->
    <div class="card">
        <div class="card-title">Nuevo Publicador</div>
        <div class="card-description">Registra un nuevo publicador para asignar territorios.</div>

        <form action="{{ route('publicadores.store') }}" method="POST">
            @csrf

            <!-- Nombre -->
            <div class="mb-4">
                <label for="nombre" style="display: block; font-weight: 600; margin-bottom: 0.5rem;">
                    Nombre *
                </label>
                <input type="text" id="nombre" name="nombre" required value="{{ old('nombre') }}"
                       style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 8px;"
                       placeholder="Nombre del publicador">
                @error('nombre')
                    <div style="color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</div>
                @enderror
            </div>

            <!-- Apellidos -->
            <div class="mb-4">
                <label for="apellidos" style="display: block; font-weight: 600; margin-bottom: 0.5rem;">
                    Apellidos
                </label>
                <input type="text" id="apellidos" name="apellidos" value="{{ old('apellidos') }}"
                       style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 8px;"
                       placeholder="Apellidos del publicador (opcional)">
                @error('apellidos')
                    <div style="color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</div>
                @enderror
            </div>

            <!-- Teléfono -->
            <div class="mb-4">
                <label for="telefono" style="display: block; font-weight: 600; margin-bottom: 0.5rem;">
                    Teléfono *
                </label>
                <input type="tel" id="telefono" name="telefono" required value="{{ old('telefono') }}"
                       style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 8px;"
                       placeholder="+34 612 345 678">
                @error('telefono')
                    <div style="color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</div>
                @enderror
                <div style="color: #6b7280; font-size: 0.875rem; margin-top: 0.25rem;">
                    Incluye código de país para WhatsApp
                </div>
            </div>

            <!-- Estado activo -->
            <div class="mb-4">
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input type="checkbox" name="activo" value="1" {{ old('activo', true) ? 'checked' : '' }}
                           style="width: 1rem; height: 1rem;">
                    <span style="font-weight: 600;">Publicador activo</span>
                </label>
                <div style="color: #6b7280; font-size: 0.875rem; margin-top: 0.25rem;">
                    Solo los publicadores activos pueden recibir territorios
                </div>
            </div>

            <!-- Notas adicionales -->
            <div class="mb-4">
                <label for="notas" style="display: block; font-weight: 600; margin-bottom: 0.5rem;">
                    Notas adicionales
                </label>
                <textarea id="notas" name="notas" rows="3"
                          style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 8px;"
                          placeholder="Información adicional sobre el publicador...">{{ old('notas') }}</textarea>
                @error('notas')
                    <div style="color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</div>
                @enderror
            </div>

            <!-- Botones de acción -->
            <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2rem;">
                <a href="{{ route('publicadores.index') }}" class="btn btn-secondary">
                    Cancelar
                </a>
                <button type="submit" class="btn btn-primary">
                    Crear Publicador
                </button>
            </div>
        </form>
    </div>

    <!-- Panel de información -->
    <div>
        <div class="card mb-4">
            <div class="card-title">¿Qué información necesito?</div>
            <div style="font-size: 0.875rem; line-height: 1.5;">
                <div><strong>Nombre:</strong> Nombre del publicador (requerido)</div>
                <div><strong>Apellidos:</strong> Apellidos del publicador (opcional)</div>
                <div><strong>Teléfono:</strong> Con código de país (+34 para España)</div>
                <div><strong>Estado:</strong> Solo activos pueden recibir territorios</div>
                <div><strong>Notas:</strong> Información adicional opcional</div>
            </div>
        </div>

        <div class="card">
            <div class="card-title">Después de crear</div>
            <div style="font-size: 0.875rem; line-height: 1.5;">
                <div>• El publicador aparecerá en la lista</div>
                <div>• Podrás asignarle territorios si está activo</div>
                <div>• Se pueden enviar mensajes de WhatsApp</div>
                <div>• Puedes editar la información después</div>
            </div>
        </div>
    </div>
</div>
@endsection 