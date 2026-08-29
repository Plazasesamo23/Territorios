@extends('layouts.app')

@section('title', isset($turno) ? 'Editar Plantilla de Turno' : 'Nueva Plantilla de Turno')

@section('content')
<div class="page-nav">
    <div class="page-breadcrumbs">
        <a href="{{ route('ppoc.turnos.index') }}" class="breadcrumb-link">Plantillas de Turnos</a>
        <span class="breadcrumb-sep">›</span>
        <span class="breadcrumb-current">{{ isset($turno) ? 'Editar' : 'Nueva' }}</span>
    </div>
</div>

@if($errors->any())
<div class="alert alert-error mb-4">
    @foreach($errors->all() as $error)
        <div>{{ $error }}</div>
    @endforeach
</div>
@endif

<div class="card" style="max-width: 720px;">
    <div class="card-title">{{ isset($turno) ? 'Editar Plantilla de Turno' : 'Nueva Plantilla de Turno' }}</div>

    <form action="{{ isset($turno) ? route('ppoc.turnos.update', $turno) : route('ppoc.turnos.store') }}" method="POST">
        @csrf
        @if(isset($turno))
            @method('PUT')
        @endif

        <div class="form-group mb-4">
            <label for="nombre" class="form-label">Nombre del Turno *</label>
            <input type="text" id="nombre" name="nombre" class="form-input" value="{{ old('nombre', $turno->nombre ?? '') }}" required placeholder="Ej: Mañana Centro, Tarde Mercado">
        </div>

        <div class="turno-form-grid">
            <div class="form-group mb-4">
                <label for="dia_semana" class="form-label">Día de la Semana *</label>
                <select id="dia_semana" name="dia_semana" class="form-input" required>
                    @foreach(['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'] as $i => $dia)
                        <option value="{{ $i }}" {{ old('dia_semana', $turno->dia_semana ?? '') == $i ? 'selected' : '' }}>{{ $dia }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group mb-4">
                <label for="numero_turno" class="form-label">Orden del Turno *</label>
                <select id="numero_turno" name="numero_turno" class="form-input" required>
                    <option value="1" {{ old('numero_turno', $turno->numero_turno ?? 1) == 1 ? 'selected' : '' }}>Turno 1 (primero del día)</option>
                    <option value="2" {{ old('numero_turno', $turno->numero_turno ?? '') == 2 ? 'selected' : '' }}>Turno 2 (segundo del día)</option>
                </select>
            </div>
        </div>

        <div class="turno-form-grid">
            <div class="form-group mb-4">
                <label for="hora_inicio" class="form-label">Hora Inicio *</label>
                <input type="time" id="hora_inicio" name="hora_inicio" class="form-input" value="{{ old('hora_inicio', isset($turno) ? \Carbon\Carbon::parse($turno->hora_inicio)->format('H:i') : '09:00') }}" required>
            </div>
            <div class="form-group mb-4">
                <label for="hora_fin" class="form-label">Hora Fin *</label>
                <input type="time" id="hora_fin" name="hora_fin" class="form-input" value="{{ old('hora_fin', isset($turno) ? \Carbon\Carbon::parse($turno->hora_fin)->format('H:i') : '11:00') }}" required>
            </div>
        </div>

        <div class="turno-form-grid">
            <div class="form-group mb-4">
                <label for="capacidad" class="form-label">Capacidad (voluntarios) *</label>
                <input type="number" id="capacidad" name="capacidad" class="form-input" value="{{ old('capacidad', $turno->capacidad ?? 3) }}" min="1" max="10" required>
            </div>
            <div class="form-group mb-4">
                <label for="tipo" class="form-label">Tipo</label>
                <select id="tipo" name="tipo" class="form-input">
                    <option value="predicacion" {{ old('tipo', $turno->tipo ?? '') == 'predicacion' ? 'selected' : '' }}>Predicación</option>
                    <option value="carrito" {{ old('tipo', $turno->tipo ?? '') == 'carrito' ? 'selected' : '' }}>Carrito</option>
                </select>
            </div>
        </div>

        <div class="form-group mb-4">
            <label for="ubicacion" class="form-label">Ubicación</label>
            <input type="text" id="ubicacion" name="ubicacion" class="form-input" value="{{ old('ubicacion', $turno->ubicacion ?? '') }}" placeholder="Ej: Plaza Central, Mercado Municipal">
        </div>

        <div class="form-group mb-4">
            <label for="notas" class="form-label">Notas</label>
            <input type="text" id="notas" name="notas" class="form-input" value="{{ old('notas', $turno->notas ?? '') }}" placeholder="Notas adicionales...">
        </div>

        <div class="form-group mb-4">
            <label style="display: flex; align-items: center; gap: 0.6rem; cursor: pointer; min-height: 44px;">
                <input type="hidden" name="activo" value="0">
                <input type="checkbox" name="activo" value="1" style="width: 20px; height: 20px;" {{ old('activo', $turno->activo ?? true) ? 'checked' : '' }}>
                <span>Plantilla activa (se incluirá al generar el mes)</span>
            </label>
        </div>

        <div class="form-actions">
            <a href="{{ route('ppoc.turnos.index') }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">{{ isset($turno) ? 'Guardar cambios' : 'Crear plantilla' }}</button>
        </div>
    </form>
</div>

<style>
.turno-form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}
@media (max-width: 560px) {
    .turno-form-grid { grid-template-columns: 1fr; }
}
</style>
@endsection
