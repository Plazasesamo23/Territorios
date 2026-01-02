@extends('layouts.app')

@section('title', isset($turno) ? 'Editar Plantilla de Turno' : 'Nueva Plantilla de Turno')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-{{ isset($turno) ? 'edit' : 'plus' }}"></i>
                {{ isset($turno) ? 'Editar Plantilla de Turno' : 'Nueva Plantilla de Turno' }}
            </h5>
        </div>
        <div class="card-body">
            <form action="{{ isset($turno) ? route('ppoc.turnos.update', $turno) : route('ppoc.turnos.store') }}" method="POST">
                @csrf
                @if(isset($turno))
                    @method('PUT')
                @endif

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nombre del Turno *</label>
                        <input type="text" name="nombre" class="form-control" value="{{ old('nombre', $turno->nombre ?? '') }}" required placeholder="Ej: Manana Centro, Tarde Mercado">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Dia de la Semana *</label>
                        <select name="dia_semana" class="form-select" required>
                            @foreach(['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado', 'Domingo'] as $i => $dia)
                                <option value="{{ $i }}" {{ old('dia_semana', $turno->dia_semana ?? '') == $i ? 'selected' : '' }}>{{ $dia }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Numero de Turno *</label>
                        <select name="numero_turno" class="form-select" required>
                            <option value="1" {{ old('numero_turno', $turno->numero_turno ?? 1) == 1 ? 'selected' : '' }}>Turno 1 (Primero del dia)</option>
                            <option value="2" {{ old('numero_turno', $turno->numero_turno ?? '') == 2 ? 'selected' : '' }}>Turno 2 (Segundo del dia)</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Hora Inicio *</label>
                        <input type="time" name="hora_inicio" class="form-control" value="{{ old('hora_inicio', isset($turno) ? \Carbon\Carbon::parse($turno->hora_inicio)->format('H:i') : '09:00') }}" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Hora Fin *</label>
                        <input type="time" name="hora_fin" class="form-control" value="{{ old('hora_fin', isset($turno) ? \Carbon\Carbon::parse($turno->hora_fin)->format('H:i') : '11:00') }}" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Capacidad (voluntarios) *</label>
                        <input type="number" name="capacidad" class="form-control" value="{{ old('capacidad', $turno->capacidad ?? 3) }}" min="1" max="10" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Tipo</label>
                        <select name="tipo" class="form-select">
                            <option value="predicacion" {{ old('tipo', $turno->tipo ?? '') == 'predicacion' ? 'selected' : '' }}>Predicacion</option>
                            <option value="carrito" {{ old('tipo', $turno->tipo ?? '') == 'carrito' ? 'selected' : '' }}>Carrito</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Ubicacion</label>
                        <input type="text" name="ubicacion" class="form-control" value="{{ old('ubicacion', $turno->ubicacion ?? '') }}" placeholder="Ej: Plaza Central, Mercado Municipal">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Notas</label>
                        <input type="text" name="notas" class="form-control" value="{{ old('notas', $turno->notas ?? '') }}" placeholder="Notas adicionales...">
                    </div>
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input type="hidden" name="activo" value="0">
                        <input type="checkbox" name="activo" value="1" class="form-check-input" id="activo" {{ old('activo', $turno->activo ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="activo">Plantilla activa (se incluira al generar el mes)</label>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> {{ isset($turno) ? 'Actualizar' : 'Crear' }} Plantilla
                    </button>
                    <a href="{{ route('ppoc.turnos.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection