@extends('layouts.app')

@section('title', 'Crear programa - Reuniones')

@section('content')

<div class="page-sm">
    <h1 class="page-title">Crear programas</h1>
    <p class="page-subtitle">Se generaran las partes estandar de VyM automaticamente</p>

    <form action="{{ route('reuniones.store') }}" method="POST" class="mt-3">
        @csrf

        <div class="form-group">
            <label class="form-label">Fecha de inicio (lunes de la semana)</label>
            <input type="date" name="fecha_semana" class="form-input" value="{{ $proximoLunes->format('Y-m-d') }}" required style="max-width: 300px;">
            @error('fecha_semana')
                <span class="text-xs" style="color: #ef4444;">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label">Cantidad de semanas a generar</label>
            <select name="cantidad_semanas" class="form-input" style="max-width: 300px;">
                <option value="1">1 semana</option>
                <option value="2">2 semanas</option>
                <option value="4" selected>4 semanas (1 mes)</option>
                <option value="8">8 semanas (2 meses)</option>
            </select>
        </div>

        <div class="config-info-box mb-2">
            Cada programa incluira automaticamente: Discurso Tesoros, Perlas escondidas, Lectura biblica, 3 partes de estudiantes (con ayudante), Discurso Vida Cristiana.
        </div>

        <div class="flex gap-1">
            <button type="submit" class="btn btn-teal">Crear programas</button>
            <a href="{{ route('reuniones.index') }}" class="btn btn-ghost">Cancelar</a>
        </div>
    </form>
</div>

@endsection
