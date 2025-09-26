@extends('layouts.app')

@section('title', $publicador->nombre_completo . ' - Publicadores')

@section('content')
<!-- Navegación minimalista -->
<nav class="page-nav">
    <div class="page-breadcrumbs">
        <a href="{{ route('dashboard') }}" class="breadcrumb-link">Dashboard</a>
        <span class="breadcrumb-separator">›</span>
        <a href="{{ route('publicadores.index') }}" class="breadcrumb-link">Publicadores</a>
        <span class="breadcrumb-separator">›</span>
        <span class="breadcrumb-current">{{ $publicador->nombre_completo }}</span>
    </div>
    <div class="page-actions">
        <a href="{{ route('publicadores.registros', $publicador) }}" class="btn btn-primary">
            📋 Registros
        </a>
        <a href="{{ route('publicadores.index') }}" class="btn btn-secondary">
            ← Volver
        </a>
    </div>
</nav>

<!-- Dos opciones principales -->
<div class="grid grid-2 mb-6">
    <!-- Opción 1: Ver/Editar Datos -->
    <div class="card" style="cursor: pointer;" onclick="toggleEditMode()">
        <div class="card-title">👁️ Ver / Editar Datos</div>
        <div class="card-description">Información básica del publicador</div>
        <div style="margin-top: 1rem; padding: 1rem; background: #f8fafc; border-radius: 8px;">
            <div style="font-size: 1.1rem; font-weight: 600; margin-bottom: 0.5rem;">{{ $publicador->nombre_completo }}</div>
            <div style="color: #6b7280; margin-bottom: 0.25rem;">📞 {{ $publicador->telefono }}</div>
            <div style="color: #6b7280;">
                Estado: 
                @if($publicador->activo)
                    <span style="color: #059669; font-weight: 600;">Activo</span>
                @else
                    <span style="color: #dc2626; font-weight: 600;">Inactivo</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Opción 2: Gestión de Registros -->
    <div class="card">
        <div class="card-title">📋 Gestión de Registros</div>
        <div class="card-description">Territorios asignados e historial</div>
        <div style="margin-top: 1rem;">
            @if($publicador->territorio_actual)
                <div style="padding: 1rem; background: #dbeafe; border-radius: 8px; margin-bottom: 1rem;">
                    <div style="font-weight: 600; color: #1e40af; margin-bottom: 0.5rem;">
                        🗺️ Territorio Actual: #{{ $publicador->territorio_actual->numero }}
                    </div>
                    @php
                        $estado = $publicador->territorio_actual->calcularEstado();
                        $dias = $publicador->ultimoRegistroActivo()->fecha_salida->diffInDays(now());
                    @endphp
                    <div style="font-size: 0.875rem; color: #3730a3;">
                        {{ ucfirst($estado) }} • {{ $dias }} días
                    </div>
                </div>
            @else
                <div style="padding: 1rem; background: #dcfce7; border-radius: 8px; margin-bottom: 1rem;">
                    <div style="font-weight: 600; color: #166534;">🆓 Disponible para nueva asignación</div>
                </div>
            @endif
            
            <a href="{{ route('publicadores.registros', $publicador) }}" class="btn btn-primary w-full">
                Ver Todos los Registros
            </a>
        </div>
    </div>
</div>

<!-- Formulario de edición (oculto por defecto) -->
<div id="editForm" style="display: none;">
    <div class="card">
        <div class="card-title">✏️ Editar Publicador</div>
        
        <form method="POST" action="{{ route('publicadores.update', $publicador) }}">
            @csrf
            @method('PUT')
            
            <div class="grid grid-2 gap-4 mb-4">
                <div class="form-group">
                    <label for="nombre">Nombre</label>
                    <input type="text" id="nombre" name="nombre" value="{{ old('nombre', $publicador->nombre) }}" required>
                </div>
                
                <div class="form-group">
                    <label for="apellidos">Apellidos</label>
                    <input type="text" id="apellidos" name="apellidos" value="{{ old('apellidos', $publicador->apellidos) }}" required>
                </div>
            </div>
            
            <div class="form-group mb-4">
                <label for="telefono">Teléfono</label>
                <input type="text" id="telefono" name="telefono" value="{{ old('telefono', $publicador->telefono) }}" required>
            </div>
            
            <div class="form-group mb-4">
                <label for="notas">Notas</label>
                <textarea id="notas" name="notas" rows="3">{{ old('notas', $publicador->notas) }}</textarea>
            </div>
            
            <div class="form-group mb-6">
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input type="checkbox" name="activo" value="1" {{ $publicador->activo ? 'checked' : '' }}>
                    <span>Publicador activo</span>
                </label>
            </div>
            
            <div class="flex gap-3">
                <button type="submit" class="btn btn-primary">
                    💾 Guardar Cambios
                </button>
                <button type="button" onclick="toggleEditMode()" class="btn btn-secondary">
                    ❌ Cancelar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Zona de peligro -->
<div class="card border-red">
    <div class="card-title text-red">⚠️ Zona de Peligro</div>
    <div class="card-description">
        Eliminar este publicador borrará todos sus registros asociados. Esta acción no se puede deshacer.
    </div>
    <form method="POST" action="{{ route('publicadores.destroy', $publicador) }}" onsubmit="return confirm('¿Estás seguro de que quieres eliminar este publicador? Esta acción no se puede deshacer.')" style="margin-top: 1rem;">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger">
            🗑️ Eliminar Publicador
        </button>
    </form>
</div>

<script>
function toggleEditMode() {
    const editForm = document.getElementById('editForm');
    if (editForm.style.display === 'none') {
        editForm.style.display = 'block';
        editForm.scrollIntoView({ behavior: 'smooth' });
    } else {
        editForm.style.display = 'none';
    }
}
</script>

<style>
.w-full {
    width: 100%;
}

.border-red {
    border-color: #fecaca;
}

.text-red {
    color: #dc2626;
}

.btn-danger {
    background: #dc2626;
    color: white;
    border: 1px solid #dc2626;
}

.btn-danger:hover {
    background: #b91c1c;
    border-color: #b91c1c;
}
</style>
@endsection 