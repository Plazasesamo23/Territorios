@extends('layouts.app')

@section('title', 'Disponibilidad por Publicador')

@section('content')
<div class="page-nav">
    <div class="page-breadcrumbs">
        <a href="{{ route('dashboard') }}" class="breadcrumb-link">Dashboard</a>
        <span class="breadcrumb-sep">></span>
        <a href="{{ route('ppoc.calendario') }}" class="breadcrumb-link">PPOC</a>
        <span class="breadcrumb-sep">></span>
        <span class="breadcrumb-current">Disponibilidad por Publicador</span>
    </div>
    <div class="page-actions">
        <a href="{{ route('ppoc.disponibilidad.por-turno') }}" class="btn btn-secondary btn-sm">
            Ver por Turno
        </a>
    </div>
</div>

<div class="card">
    <div class="card-title">Disponibilidad por Publicador</div>

    @if($publicadores->count() === 0)
    <div class="empty-state">
        <p>No hay publicadores aprobados para PPOC.</p>
        <a href="{{ route('ppoc.aprobados') }}">Aprobar publicadores</a>
    </div>
    @else
    <div class="publicadores-grid">
        @foreach($publicadores as $pub)
        <div class="publicador-card {{ $pub->disponibilidadesPpoc->count() > 0 ? 'tiene-disponibilidad' : 'sin-disponibilidad' }}">
            <div class="publicador-header">
                <span class="publicador-nombre">{{ $pub->nombre_completo }}</span>
                <span class="turnos-count">{{ $pub->disponibilidadesPpoc->count() }} turnos</span>
            </div>
            <div class="publicador-turnos">
                @if($pub->disponibilidadesPpoc->count() > 0)
                    @php
                        $turnosOrdenados = $pub->disponibilidadesPpoc->sortBy(function($d) {
                            return $d->turno->dia_semana * 100 + substr($d->turno->hora_inicio, 0, 2);
                        });
                    @endphp
                    @foreach($turnosOrdenados as $disp)
                    <div class="turno-badge">
                        <span class="turno-dia">{{ $disp->turno->dia_nombre }}</span>
                        <span class="turno-hora">{{ $disp->turno->horario }}</span>
                    </div>
                    @endforeach
                @else
                <div class="no-turnos">
                    No ha indicado disponibilidad
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>

<style>
/* Grid de publicadores */
.publicadores-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}

/* Tarjeta de publicador */
.publicador-card {
    background: var(--bg-card, #fff);
    border: 2px solid var(--border-color, #e5e7eb);
    border-radius: 12px;
    padding: 1rem;
    transition: transform 0.2s, box-shadow 0.2s;
}
.publicador-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.publicador-card.tiene-disponibilidad {
    border-color: #4a6da7;
}
.publicador-card.sin-disponibilidad {
    border-color: #9ca3af;
    opacity: 0.7;
}
[data-theme="dark"] .publicador-card {
    background: #1a1a1a;
    border-color: #404040;
}
[data-theme="dark"] .publicador-card.tiene-disponibilidad {
    border-color: #4a6da7;
}
[data-theme="dark"] .publicador-card.sin-disponibilidad {
    border-color: #525252;
}

.publicador-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.75rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid var(--border-color, #e5e7eb);
}
[data-theme="dark"] .publicador-header {
    border-bottom-color: #404040;
}
.publicador-nombre {
    font-weight: 600;
    color: var(--text-primary, #111827);
}
[data-theme="dark"] .publicador-nombre {
    color: #f5f5f5;
}
.turnos-count {
    font-size: 0.75rem;
    padding: 0.125rem 0.5rem;
    background: #4a6da7;
    color: white;
    border-radius: 10px;
    font-weight: 600;
}
.publicador-card.sin-disponibilidad .turnos-count {
    background: #9ca3af;
}

/* Turnos del publicador */
.publicador-turnos {
    display: flex;
    flex-wrap: wrap;
    gap: 0.375rem;
}
.turno-badge {
    display: flex;
    flex-direction: column;
    font-size: 0.75rem;
    padding: 0.375rem 0.5rem;
    background: #f3f4f6;
    border-radius: 6px;
    text-align: center;
}
[data-theme="dark"] .turno-badge {
    background: #262626;
}
.turno-dia {
    font-weight: 600;
    color: #4a6da7;
}
.turno-hora {
    color: #6b7280;
}
[data-theme="dark"] .turno-hora {
    color: #a3a3a3;
}
.no-turnos {
    font-size: 0.85rem;
    color: #9ca3af;
    font-style: italic;
}

/* Empty state */
.empty-state {
    text-align: center;
    padding: 2rem;
    color: #6b7280;
}
.empty-state a {
    color: #4a6da7;
}

/* Responsive */
@media (max-width: 768px) {
    .publicadores-grid {
        grid-template-columns: 1fr;
    }
}
</style>
@endsection
