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

            <!-- Grupo de Predicación -->
            <div class="mb-4">
                <label for="grupo_predicacion_id" style="display: block; font-weight: 600; margin-bottom: 0.5rem;">
                    Grupo de Predicación
                </label>
                <select id="grupo_predicacion_id" name="grupo_predicacion_id"
                        class="grupo-select"
                        style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 8px; background: #fff;">
                    <option value="">-- Seleccionar grupo --</option>
                    @foreach($grupos as $grupo)
                        <option value="{{ $grupo->id }}" {{ old('grupo_predicacion_id') == $grupo->id ? 'selected' : '' }}>
                            Grupo {{ $grupo->numero }}@if($grupo->nombre) - {{ $grupo->nombre }}@endif
                        </option>
                    @endforeach
                </select>
                @error('grupo_predicacion_id')
                    <div style="color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</div>
                @enderror
                <div style="color: #6b7280; font-size: 0.875rem; margin-top: 0.25rem;">
                    Grupo al que pertenece el publicador
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

            <!-- Nombramientos y Clasificacion -->
            <div class="mb-4">
                <label style="display: block; font-weight: 600; margin-bottom: 0.75rem;">
                    Nombramientos y Clasificacion
                </label>
                <div class="nombramientos-box" style="display: flex; flex-direction: column; gap: 0.75rem; padding: 1rem; background: #f9fafb; border-radius: 8px; border: 1px solid #e5e7eb;">
                    <!-- Anciano -->
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" name="es_anciano" value="1" {{ old('es_anciano') ? 'checked' : '' }}
                               style="width: 1rem; height: 1rem;" onchange="handleNombramiento(this, 'anciano')">
                        <span style="display: inline-flex; align-items: center; gap: 0.5rem;">
                            <span style="background: #7c3aed; color: white; padding: 0.15rem 0.5rem; border-radius: 4px; font-size: 0.7rem; font-weight: 700;">ANC</span>
                            <span class="nombramiento-label">Anciano</span>
                        </span>
                    </label>
                    <!-- Siervo Ministerial -->
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" name="es_siervo_ministerial" value="1" {{ old('es_siervo_ministerial') ? 'checked' : '' }}
                               style="width: 1rem; height: 1rem;" onchange="handleNombramiento(this, 'siervo')">
                        <span style="display: inline-flex; align-items: center; gap: 0.5rem;">
                            <span style="background: #0891b2; color: white; padding: 0.15rem 0.5rem; border-radius: 4px; font-size: 0.7rem; font-weight: 700;">SM</span>
                            <span class="nombramiento-label">Siervo Ministerial</span>
                        </span>
                    </label>
                    <!-- Precursor -->
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" name="es_precursor" value="1" {{ old('es_precursor') ? 'checked' : '' }}
                               style="width: 1rem; height: 1rem;">
                        <span style="display: inline-flex; align-items: center; gap: 0.5rem;">
                            <span style="background: #f97316; color: white; padding: 0.15rem 0.5rem; border-radius: 4px; font-size: 0.7rem; font-weight: 700;">PR</span>
                            <span class="nombramiento-label">Precursor Regular</span>
                        </span>
                    </label>
                    <!-- Menor de edad -->
                    <label class="nombramientos-separator" style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; border-top: 1px solid #e5e7eb; padding-top: 0.75rem; margin-top: 0.25rem;">
                        <input type="checkbox" name="es_menor" value="1" {{ old('es_menor') ? 'checked' : '' }}
                               style="width: 1rem; height: 1rem;">
                        <span style="display: inline-flex; align-items: center; gap: 0.5rem;">
                            <span style="background: #eab308; color: #1a1a1a; padding: 0.15rem 0.5rem; border-radius: 4px; font-size: 0.7rem; font-weight: 700;">MEN</span>
                            <span class="nombramiento-label">Menor de edad</span>
                        </span>
                    </label>
                </div>
                <div style="color: #6b7280; font-size: 0.875rem; margin-top: 0.5rem;">
                    Anciano y Siervo Ministerial son mutuamente excluyentes
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

        <div class="card mb-4">
            <div class="card-title">Nombramientos</div>
            <div style="font-size: 0.875rem; line-height: 1.8;">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <span style="background: #7c3aed; color: white; padding: 0.1rem 0.4rem; border-radius: 4px; font-size: 0.65rem; font-weight: 700;">ANC</span>
                    <span class="nombramiento-label">Anciano (Elder)</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <span style="background: #0891b2; color: white; padding: 0.1rem 0.4rem; border-radius: 4px; font-size: 0.65rem; font-weight: 700;">SM</span>
                    <span class="nombramiento-label">Siervo Ministerial</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <span style="background: #f97316; color: white; padding: 0.1rem 0.4rem; border-radius: 4px; font-size: 0.65rem; font-weight: 700;">PR</span>
                    <span class="nombramiento-label">Precursor Regular</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <span style="background: #eab308; color: #1a1a1a; padding: 0.1rem 0.4rem; border-radius: 4px; font-size: 0.65rem; font-weight: 700;">MEN</span>
                    <span class="nombramiento-label">Menor de edad</span>
                </div>
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

<script>
function handleNombramiento(checkbox, tipo) {
    const ancianoCheckbox = document.querySelector('input[name="es_anciano"]');
    const siervoCheckbox = document.querySelector('input[name="es_siervo_ministerial"]');

    if (checkbox.checked) {
        if (tipo === 'anciano') {
            siervoCheckbox.checked = false;
        } else if (tipo === 'siervo') {
            ancianoCheckbox.checked = false;
        }
    }
}
</script>

<style>
/* Dark mode para nombramientos */
[data-theme="dark"] .nombramientos-box {
    background: #1a1a1a !important;
    border-color: #404040 !important;
}
[data-theme="dark"] .nombramientos-separator {
    border-color: #404040 !important;
}
[data-theme="dark"] .nombramiento-label {
    color: #f5f5f5;
}
[data-theme="dark"] .grupo-select {
    background: #262626 !important;
    border-color: #404040 !important;
    color: #e5e5e5 !important;
}
[data-theme="dark"] .grupo-select option {
    background: #262626;
    color: #e5e5e5;
}
</style>
@endsection 