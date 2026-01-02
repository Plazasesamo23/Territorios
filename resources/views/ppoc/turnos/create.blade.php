@extends('layouts.app')

@section('title', isset($turno) ? 'Editar Plantilla de Turno' : (isset($turnoBase) ? 'Duplicar Plantilla de Turno' : 'Nueva Plantilla de Turno'))

@section('content')
@php
    // Si estamos copiando, usar turnoBase como fuente de datos
    $datos = $turno ?? $turnoBase ?? null;
    $esCopia = isset($turnoBase) && !isset($turno);
@endphp
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                @if(isset($turno))
                    &#9998; Editar Plantilla de Turno
                @elseif($esCopia)
                    &#128464; Duplicar Plantilla de Turno
                @else
                    &#10133; Nueva Plantilla de Turno
                @endif
            </h5>
        </div>
        <div class="card-body">
            @if($esCopia)
            <div class="alert alert-info mb-4">
                Copiando desde: <strong>{{ $turnoBase->nombre }}</strong>. Modifica los datos necesarios (especialmente el dia de la semana).
            </div>
            @endif

            <form action="{{ isset($turno) ? route('ppoc.turnos.update', $turno) : route('ppoc.turnos.store') }}" method="POST">
                @csrf
                @if(isset($turno))
                    @method('PUT')
                @endif

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nombre del Turno *</label>
                        <input type="text" name="nombre" class="form-control" value="{{ old('nombre', $esCopia ? $datos->nombre . ' (copia)' : ($datos->nombre ?? '')) }}" required placeholder="Ej: Manana Centro, Tarde Mercado">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Dia de la Semana *</label>
                        <select name="dia_semana" class="form-select" required>
                            @foreach(['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado', 'Domingo'] as $i => $dia)
                                <option value="{{ $i }}" {{ old('dia_semana', $datos->dia_semana ?? '') == $i ? 'selected' : '' }}>{{ $dia }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Numero de Turno *</label>
                        <select name="numero_turno" class="form-select" required>
                            <option value="1" {{ old('numero_turno', $datos->numero_turno ?? 1) == 1 ? 'selected' : '' }}>Turno 1 (Primero del dia)</option>
                            <option value="2" {{ old('numero_turno', $datos->numero_turno ?? '') == 2 ? 'selected' : '' }}>Turno 2 (Segundo del dia)</option>
                            <option value="3" {{ old('numero_turno', $datos->numero_turno ?? '') == 3 ? 'selected' : '' }}>Turno 3</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Hora Inicio *</label>
                        <input type="time" name="hora_inicio" class="form-control" value="{{ old('hora_inicio', isset($datos) ? \Carbon\Carbon::parse($datos->hora_inicio)->format('H:i') : '09:00') }}" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Hora Fin *</label>
                        <input type="time" name="hora_fin" class="form-control" value="{{ old('hora_fin', isset($datos) ? \Carbon\Carbon::parse($datos->hora_fin)->format('H:i') : '11:00') }}" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Capacidad (voluntarios) *</label>
                        <input type="number" name="capacidad" class="form-control" value="{{ old('capacidad', $datos->capacidad ?? 3) }}" min="1" max="10" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Ubicacion</label>
                        <input type="text" name="ubicacion" class="form-control" value="{{ old('ubicacion', $datos->ubicacion ?? '') }}" placeholder="Ej: Plaza Central, Mercado">
                    </div>
                </div>

                <!-- Tipo siempre carrito (oculto) -->
                <input type="hidden" name="tipo" value="carrito">

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Notas</label>
                        <input type="text" name="notas" class="form-control" value="{{ old('notas', $datos->notas ?? '') }}" placeholder="Notas adicionales...">
                    </div>
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input type="hidden" name="activo" value="0">
                        <input type="checkbox" name="activo" value="1" class="form-check-input" id="activo" {{ old('activo', $datos->activo ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="activo">Plantilla activa (se incluira al generar el mes)</label>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        &#128190; {{ isset($turno) ? 'Actualizar' : 'Crear' }} Plantilla
                    </button>
                    <a href="{{ route('ppoc.turnos.index') }}" class="btn btn-secondary">
                        &#10006; Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
