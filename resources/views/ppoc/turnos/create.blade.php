@extends('layouts.app')

@section('title', isset($turno) ? 'Editar Plantilla de Turno' : (isset($turnoBase) ? 'Duplicar Plantilla de Turno' : 'Nueva Plantilla de Turno'))

@section('content')
@php
    $datos = $turno ?? $turnoBase ?? null;
    $esCopia = isset($turnoBase) && !isset($turno);
@endphp

<div class="turno-form-container">
    <!-- Header -->
    <div class="form-header">
        <div class="header-icon">
            @if(isset($turno))
                <span>&#9998;</span>
            @elseif($esCopia)
                <span>&#128464;</span>
            @else
                <span>&#128197;</span>
            @endif
        </div>
        <div class="header-text">
            <h1>
                @if(isset($turno))
                    Editar Plantilla
                @elseif($esCopia)
                    Duplicar Plantilla
                @else
                    Nueva Plantilla de Turno
                @endif
            </h1>
            <p>Configura los detalles del turno de predicacion publica</p>
        </div>
    </div>

    @if($esCopia)
    <div class="info-banner">
        <span class="info-icon">&#9432;</span>
        <span>Copiando desde: <strong>{{ $turnoBase->nombre }}</strong> - Modifica los datos necesarios</span>
    </div>
    @endif

    <form action="{{ isset($turno) ? route('ppoc.turnos.update', $turno) : route('ppoc.turnos.store') }}" method="POST" class="turno-form">
        @csrf
        @if(isset($turno))
            @method('PUT')
        @endif
        <input type="hidden" name="tipo" value="carrito">

        <!-- Seccion: Identificacion -->
        <div class="form-section">
            <div class="section-header">
                <span class="section-icon">&#127991;</span>
                <h2>Identificacion</h2>
            </div>
            <div class="section-content">
                <div class="input-group-modern">
                    <label for="nombre">Nombre del Turno</label>
                    <input
                        type="text"
                        id="nombre"
                        name="nombre"
                        value="{{ old('nombre', $esCopia ? $datos->nombre . ' (copia)' : ($datos->nombre ?? '')) }}"
                        required
                        placeholder="Ej: Manana Centro, Tarde Mercado"
                        class="input-modern"
                    >
                </div>
                <div class="input-row">
                    <div class="input-group-modern">
                        <label for="dia_semana">Dia de la Semana</label>
                        <select name="dia_semana" id="dia_semana" class="input-modern" required>
                            @foreach(['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado', 'Domingo'] as $i => $dia)
                                <option value="{{ $i }}" {{ old('dia_semana', $datos->dia_semana ?? '') == $i ? 'selected' : '' }}>{{ $dia }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="input-group-modern">
                        <label for="numero_turno">Orden del Turno</label>
                        <select name="numero_turno" id="numero_turno" class="input-modern" required>
                            <option value="1" {{ old('numero_turno', $datos->numero_turno ?? 1) == 1 ? 'selected' : '' }}>1er turno del dia</option>
                            <option value="2" {{ old('numero_turno', $datos->numero_turno ?? '') == 2 ? 'selected' : '' }}>2do turno del dia</option>
                            <option value="3" {{ old('numero_turno', $datos->numero_turno ?? '') == 3 ? 'selected' : '' }}>3er turno del dia</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Seccion: Horario -->
        <div class="form-section">
            <div class="section-header">
                <span class="section-icon">&#128336;</span>
                <h2>Horario</h2>
            </div>
            <div class="section-content">
                <div class="time-picker-row">
                    <div class="time-picker-group">
                        <label>Inicio</label>
                        <input
                            type="time"
                            name="hora_inicio"
                            value="{{ old('hora_inicio', isset($datos) ? \Carbon\Carbon::parse($datos->hora_inicio)->format('H:i') : '09:00') }}"
                            required
                            class="time-input"
                        >
                    </div>
                    <div class="time-separator">
                        <span>&#10132;</span>
                    </div>
                    <div class="time-picker-group">
                        <label>Fin</label>
                        <input
                            type="time"
                            name="hora_fin"
                            value="{{ old('hora_fin', isset($datos) ? \Carbon\Carbon::parse($datos->hora_fin)->format('H:i') : '11:00') }}"
                            required
                            class="time-input"
                        >
                    </div>
                </div>
            </div>
        </div>

        <!-- Seccion: Configuracion -->
        <div class="form-section">
            <div class="section-header">
                <span class="section-icon">&#9881;</span>
                <h2>Configuracion</h2>
            </div>
            <div class="section-content">
                <div class="input-row">
                    <div class="input-group-modern">
                        <label for="capacidad">Capacidad</label>
                        <div class="capacity-input">
                            <input
                                type="number"
                                id="capacidad"
                                name="capacidad"
                                value="{{ old('capacidad', $datos->capacidad ?? 3) }}"
                                min="1"
                                max="10"
                                required
                                class="input-modern capacity-number"
                            >
                            <span class="capacity-label">voluntarios</span>
                        </div>
                    </div>
                    <div class="input-group-modern">
                        <label for="ubicacion">Ubicacion</label>
                        <input
                            type="text"
                            id="ubicacion"
                            name="ubicacion"
                            value="{{ old('ubicacion', $datos->ubicacion ?? '') }}"
                            placeholder="Ej: Plaza Central"
                            class="input-modern"
                        >
                    </div>
                </div>
                <div class="input-group-modern">
                    <label for="notas">Notas adicionales</label>
                    <input
                        type="text"
                        id="notas"
                        name="notas"
                        value="{{ old('notas', $datos->notas ?? '') }}"
                        placeholder="Informacion extra sobre este turno..."
                        class="input-modern"
                    >
                </div>
            </div>
        </div>

        <!-- Toggle Activo -->
        <div class="toggle-section">
            <label class="toggle-switch">
                <input type="hidden" name="activo" value="0">
                <input type="checkbox" name="activo" value="1" {{ old('activo', $datos->activo ?? true) ? 'checked' : '' }}>
                <span class="toggle-slider"></span>
            </label>
            <div class="toggle-text">
                <span class="toggle-title">Plantilla activa</span>
                <span class="toggle-desc">Se incluira automaticamente al generar el calendario mensual</span>
            </div>
        </div>

        <!-- Botones -->
        <div class="form-actions">
            <a href="{{ route('ppoc.turnos.index') }}" class="btn-cancel">
                <span>&#10006;</span> Cancelar
            </a>
            <button type="submit" class="btn-submit">
                <span>&#10003;</span> {{ isset($turno) ? 'Guardar Cambios' : 'Crear Plantilla' }}
            </button>
        </div>
    </form>
</div>

<style>
.turno-form-container {
    max-width: 700px;
    margin: 0 auto;
    padding: 1rem;
}

/* Header */
.form-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.5rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid var(--border, #e5e7eb);
}

.header-icon {
    width: 56px;
    height: 56px;
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
}

.header-text h1 {
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0 0 0.25rem 0;
    color: var(--text, #1f2937);
}

.header-text p {
    font-size: 0.9rem;
    color: var(--text-muted, #6b7280);
    margin: 0;
}

/* Info Banner */
.info-banner {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem;
    background: linear-gradient(135deg, #dbeafe 0%, #e0e7ff 100%);
    border-radius: 12px;
    margin-bottom: 1.5rem;
    font-size: 0.9rem;
    color: #1e40af;
}

[data-theme="dark"] .info-banner {
    background: linear-gradient(135deg, #1e3a5f 0%, #312e81 100%);
    color: #93c5fd;
}

.info-icon {
    font-size: 1.25rem;
}

/* Form Sections */
.form-section {
    background: var(--bg-white, #ffffff);
    border-radius: 16px;
    margin-bottom: 1rem;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    border: 1px solid var(--border, #e5e7eb);
}

[data-theme="dark"] .form-section {
    background: var(--bg-card, #1f2937);
    border-color: var(--border, #374151);
}

.section-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem 1.25rem;
    background: var(--bg-hover, #f9fafb);
    border-bottom: 1px solid var(--border, #e5e7eb);
}

[data-theme="dark"] .section-header {
    background: var(--bg-hover, #111827);
}

.section-icon {
    font-size: 1.25rem;
}

.section-header h2 {
    font-size: 0.95rem;
    font-weight: 600;
    margin: 0;
    color: var(--text, #1f2937);
}

.section-content {
    padding: 1.25rem;
}

/* Inputs */
.input-group-modern {
    margin-bottom: 1rem;
}

.input-group-modern:last-child {
    margin-bottom: 0;
}

.input-group-modern label {
    display: block;
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--text-muted, #6b7280);
    margin-bottom: 0.5rem;
    text-transform: uppercase;
    letter-spacing: 0.025em;
}

.input-modern {
    width: 100%;
    padding: 0.875rem 1rem;
    border: 2px solid var(--border, #e5e7eb);
    border-radius: 10px;
    font-size: 1rem;
    background: var(--bg-white, #ffffff);
    color: var(--text, #1f2937);
    transition: all 0.2s ease;
}

[data-theme="dark"] .input-modern {
    background: var(--bg-input, #111827);
    border-color: var(--border, #374151);
    color: var(--text, #f9fafb);
}

.input-modern:focus {
    outline: none;
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
}

.input-modern::placeholder {
    color: var(--text-light, #9ca3af);
}

.input-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

/* Time Picker */
.time-picker-row {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
}

.time-picker-group {
    text-align: center;
}

.time-picker-group label {
    display: block;
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--text-muted, #6b7280);
    margin-bottom: 0.5rem;
    text-transform: uppercase;
}

.time-input {
    padding: 1rem 1.25rem;
    border: 2px solid var(--border, #e5e7eb);
    border-radius: 12px;
    font-size: 1.5rem;
    font-weight: 600;
    text-align: center;
    background: var(--bg-white, #ffffff);
    color: var(--text, #1f2937);
    transition: all 0.2s ease;
    width: 140px;
}

[data-theme="dark"] .time-input {
    background: var(--bg-input, #111827);
    border-color: var(--border, #374151);
    color: var(--text, #f9fafb);
}

.time-input:focus {
    outline: none;
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
}

.time-separator {
    font-size: 1.5rem;
    color: var(--text-muted, #6b7280);
    padding-top: 1.5rem;
}

/* Capacity Input */
.capacity-input {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.capacity-number {
    width: 80px;
    text-align: center;
    font-weight: 600;
}

.capacity-label {
    font-size: 0.9rem;
    color: var(--text-muted, #6b7280);
}

/* Toggle Switch */
.toggle-section {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.25rem;
    background: var(--bg-white, #ffffff);
    border-radius: 16px;
    margin-bottom: 1.5rem;
    border: 1px solid var(--border, #e5e7eb);
}

[data-theme="dark"] .toggle-section {
    background: var(--bg-card, #1f2937);
    border-color: var(--border, #374151);
}

.toggle-switch {
    position: relative;
    width: 52px;
    height: 28px;
    flex-shrink: 0;
}

.toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: #d1d5db;
    border-radius: 28px;
    transition: 0.3s;
}

.toggle-slider:before {
    position: absolute;
    content: "";
    height: 22px;
    width: 22px;
    left: 3px;
    bottom: 3px;
    background: white;
    border-radius: 50%;
    transition: 0.3s;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.toggle-switch input:checked + .toggle-slider {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.toggle-switch input:checked + .toggle-slider:before {
    transform: translateX(24px);
}

.toggle-text {
    flex: 1;
}

.toggle-title {
    display: block;
    font-weight: 600;
    color: var(--text, #1f2937);
    margin-bottom: 0.125rem;
}

.toggle-desc {
    display: block;
    font-size: 0.8rem;
    color: var(--text-muted, #6b7280);
}

/* Form Actions */
.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
}

.btn-cancel, .btn-submit {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.875rem 1.5rem;
    border-radius: 12px;
    font-size: 1rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s ease;
    border: none;
    cursor: pointer;
}

.btn-cancel {
    background: var(--bg-hover, #f3f4f6);
    color: var(--text-muted, #6b7280);
}

[data-theme="dark"] .btn-cancel {
    background: var(--bg-hover, #374151);
    color: var(--text, #d1d5db);
}

.btn-cancel:hover {
    background: #e5e7eb;
    color: var(--text, #374151);
}

.btn-submit {
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
}

.btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(99, 102, 241, 0.4);
}

/* Responsive */
@media (max-width: 640px) {
    .turno-form-container {
        padding: 0.75rem;
    }

    .form-header {
        flex-direction: column;
        text-align: center;
    }

    .header-text h1 {
        font-size: 1.25rem;
    }

    .input-row {
        grid-template-columns: 1fr;
    }

    .time-picker-row {
        flex-direction: column;
        gap: 0.5rem;
    }

    .time-separator {
        transform: rotate(90deg);
        padding: 0;
    }

    .time-input {
        width: 100%;
        max-width: 200px;
    }

    .form-actions {
        flex-direction: column-reverse;
    }

    .btn-cancel, .btn-submit {
        width: 100%;
        justify-content: center;
        padding: 1rem;
    }

    .toggle-section {
        flex-direction: row;
    }
}
</style>

@endsection
