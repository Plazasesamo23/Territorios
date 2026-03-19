@extends('layouts.app')

@section('title', 'Crear programa - Reuniones')

@section('content')

<div class="page-sm">
    <h1 class="page-title">Crear programas</h1>
    <p class="page-subtitle">Se importaran automaticamente los titulos desde jw.org</p>

    <form action="{{ route('reuniones.store') }}" method="POST" class="mt-3">
        @csrf

        <div class="form-group">
            <label class="form-label">Desde la semana del (lunes)</label>
            <input type="date" name="fecha_semana" class="form-input" value="{{ $proximoLunes->format('Y-m-d') }}" required style="max-width: 300px;">
            @error('fecha_semana')
                <span class="text-xs" style="color: #ef4444;">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label">Cantidad de semanas</label>
            <select name="cantidad_semanas" class="form-input" style="max-width: 300px;">
                <option value="4">4 semanas (1 mes)</option>
                <option value="8">8 semanas (2 meses)</option>
                <option value="12">12 semanas (3 meses)</option>
                <option value="16" selected>16 semanas (4 meses)</option>
                <option value="24">24 semanas (6 meses)</option>
            </select>
        </div>

        <div class="config-info-box mb-2">
            Cada programa se creara con los titulos reales del cuadernillo VyM importados directamente de jw.org. Si alguna semana no esta disponible, se creara con partes estandar.
        </div>

        <div class="flex gap-1" style="flex-wrap: wrap;">
            <button type="submit" class="btn btn-teal">Crear e importar de jw.org</button>
            <a href="{{ route('reuniones.index') }}" class="btn btn-ghost">Cancelar</a>
        </div>
    </form>
</div>

@endsection
