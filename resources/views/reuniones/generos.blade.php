@extends('layouts.app')

@section('title', 'Asignar generos - Reuniones')

@section('content')

<div class="page-sm">
    <div class="rg-header">
        <h1 class="page-title">Asignar generos</h1>
        <p class="page-subtitle">Necesario para la auto-asignacion de reuniones VyM</p>
    </div>

    @php
        $sinGenero = $publicadores->whereNull('genero')->count();
        $total = $publicadores->count();
    @endphp

    @if($sinGenero > 0)
    <div class="rg-alert rg-alert--warn">
        <svg class="rg-alert__icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
        <span>{{ $sinGenero }} de {{ $total }} publicadores no tienen genero asignado.</span>
    </div>
    @else
    <div class="rg-alert rg-alert--ok">
        <svg class="rg-alert__icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
        <span>Todos los publicadores tienen genero asignado.</span>
    </div>
    @endif

    <form action="{{ route('reuniones.generos.guardar') }}" method="POST">
        @csrf

        <div class="table-responsive">
        <table class="table-flat">
            <thead>
                <tr>
                    <th>Publicador</th>
                    <th class="hide-mobile">Nombramiento</th>
                    <th class="rg-col-genero">Genero</th>
                </tr>
            </thead>
            <tbody>
                @foreach($publicadores as $pub)
                <tr class="{{ is_null($pub->genero) ? 'rg-row--missing' : '' }}">
                    <td class="font-medium">{{ $pub->nombre_completo }}</td>
                    <td class="hide-mobile">
                        <span class="rg-role">
                            @if($pub->es_anciano) Anciano
                            @elseif($pub->es_siervo_ministerial) Siervo ministerial
                            @elseif($pub->es_precursor) Precursor
                            @elseif($pub->es_menor) Menor
                            @else Publicador
                            @endif
                        </span>
                    </td>
                    <td>
                        <select name="generos[{{ $pub->id }}]" class="form-input rg-select">
                            <option value="">-- --</option>
                            <option value="M" {{ $pub->genero === 'M' ? 'selected' : '' }}>Hermano</option>
                            <option value="F" {{ $pub->genero === 'F' ? 'selected' : '' }}>Hermana</option>
                        </select>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>

        <div class="rg-sticky-bar">
            <button type="submit" class="btn btn-teal">Guardar generos</button>
            <a href="{{ route('reuniones.index') }}" class="btn btn-ghost">Volver</a>
        </div>
    </form>
</div>

<style>
.rg-header {
    margin-bottom: 1.25rem;
}

.rg-alert {
    display: flex;
    align-items: center;
    gap: 0.625rem;
    padding: 0.75rem 1rem;
    border-radius: var(--radius);
    font-size: 0.85rem;
    margin-bottom: 1.5rem;
}

.rg-alert__icon {
    flex-shrink: 0;
}

.rg-alert--warn {
    background: rgba(245, 158, 11, 0.1);
    border: 1px solid rgba(245, 158, 11, 0.25);
    color: #fbbf24;
}

.rg-alert--ok {
    background: rgba(34, 197, 94, 0.08);
    border: 1px solid rgba(34, 197, 94, 0.2);
    color: #4ade80;
}

.rg-col-genero {
    width: 150px;
}

.rg-role {
    font-size: 0.8rem;
    color: var(--text-muted);
}

.rg-select {
    padding: 0.375rem 0.5rem;
    font-size: 0.85rem;
}

.rg-row--missing {
    background: rgba(245, 158, 11, 0.04);
}

.rg-sticky-bar {
    display: flex;
    gap: 0.5rem;
    position: sticky;
    bottom: 0;
    padding: 1rem 0;
    background: var(--bg);
}
</style>

@endsection
