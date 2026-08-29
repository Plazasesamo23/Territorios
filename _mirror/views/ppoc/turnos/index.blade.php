@extends('layouts.app')

@section('title', 'Plantillas de Turnos PPOC')

@section('content')
<div class="page-nav">
    <div class="page-breadcrumbs">
        <span class="breadcrumb-current">Plantillas de Turnos Semanales</span>
    </div>
    <div class="page-actions">
        <a href="{{ route('ppoc.calendario') }}" class="btn btn-secondary">Ver Calendario</a>
        <a href="{{ route('ppoc.turnos.create') }}" class="btn btn-primary">+ Nueva Plantilla</a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success mb-4">{{ session('success') }}</div>
@endif

<div class="card mb-4">
    <p class="text-muted" style="margin: 0;">
        Las plantillas definen los turnos que se repiten cada semana. Al generar el calendario de un mes,
        estos turnos se crearán automáticamente para cada día correspondiente.
    </p>
</div>

@php
    $diasSemana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
@endphp

@foreach($diasSemana as $index => $diaNombre)
    @php $turnosDia = $turnos->where('dia_semana', $index); @endphp
    @if($turnosDia->count() > 0)
    <div class="turnos-dia-seccion">
        <h3 class="turnos-dia-titulo">{{ $diaNombre }}</h3>
        <div class="turnos-grid">
            @foreach($turnosDia as $turno)
            <div class="turno-plantilla-card {{ $turno->activo ? '' : 'turno-inactivo' }}">
                <div class="turno-plantilla-info">
                    <div class="turno-plantilla-nombre">
                        <strong>{{ $turno->nombre }}</strong>
                        <span class="badge badge-blue">Turno {{ $turno->numero_turno }}</span>
                        @if(!$turno->activo)
                            <span class="badge badge-gray">Inactivo</span>
                        @endif
                    </div>
                    <div class="turno-plantilla-meta">
                        🕐 {{ \Carbon\Carbon::parse($turno->hora_inicio)->format('H:i') }} - {{ \Carbon\Carbon::parse($turno->hora_fin)->format('H:i') }}
                        &nbsp;·&nbsp; 👥 {{ $turno->capacidad }} voluntarios
                        @if($turno->ubicacion)
                            <br>📍 {{ $turno->ubicacion }}
                        @endif
                    </div>
                </div>
                <div class="turno-plantilla-acciones">
                    <a href="{{ route('ppoc.turnos.edit', $turno) }}" class="btn btn-secondary btn-sm">Editar</a>
                    <a href="{{ route('ppoc.turnos.create') }}?copiar={{ $turno->id }}" class="btn btn-ghost btn-sm">Duplicar</a>
                    <form action="{{ route('ppoc.turnos.destroy', $turno) }}" method="POST" style="margin:0; display:inline;" onsubmit="return confirm('¿Eliminar esta plantilla de turno?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
@endforeach

@if($turnos->count() === 0)
    <div class="empty-state">
        <div class="icon">🕐</div>
        <div class="title">No hay plantillas de turnos</div>
        <div class="description">Crea la primera plantilla para poder generar el calendario mensual.</div>
        <a href="{{ route('ppoc.turnos.create') }}" class="btn btn-primary mt-2">+ Crear la primera</a>
    </div>
@endif

<style>
.turnos-dia-seccion { margin-bottom: 1.5rem; }
.turnos-dia-titulo {
    font-size: 1rem;
    font-weight: 600;
    color: var(--text);
    border-bottom: 1px solid var(--border);
    padding-bottom: 0.5rem;
    margin-bottom: 0.75rem;
}
.turnos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 0.75rem;
}
.turno-plantilla-card {
    background: var(--bg-white);
    border: 1px solid var(--border);
    border-radius: var(--radius-card);
    padding: 0.85rem 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
}
.turno-plantilla-card.turno-inactivo { opacity: 0.55; }
.turno-plantilla-nombre { display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; }
.turno-plantilla-meta { color: var(--text-muted); font-size: 0.85rem; line-height: 1.6; }
.turno-plantilla-acciones { display: flex; gap: 0.4rem; flex-wrap: wrap; }
@media (max-width: 480px) {
    .turnos-grid { grid-template-columns: 1fr; }
}
</style>
@endsection
