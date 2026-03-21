@extends('layouts.app')

@section('title', 'Asignaciones - Reuniones')

@section('content')

<div class="page-md">
    <div class="ra-header">
        <div>
            <h1 class="page-title">Asignaciones</h1>
            <p class="page-subtitle">Vista general de asignaciones (ultimos 3 meses)</p>
        </div>
    </div>

    <div class="table-responsive">
    <table class="table-flat">
        <thead>
            <tr>
                <th>Publicador</th>
                <th class="hide-mobile">Genero</th>
                <th class="hide-mobile">Nombramiento</th>
                <th>Asignaciones</th>
                <th>Ultima</th>
            </tr>
        </thead>
        <tbody>
            @foreach($estadisticas as $stat)
            <tr class="clickable-row" data-href="{{ route('reuniones.historial', $stat['publicador']) }}">
                <td>
                    <a href="{{ route('reuniones.historial', $stat['publicador']) }}" class="ra-name">{{ $stat['publicador']->nombre_completo }}</a>
                </td>
                <td class="hide-mobile">
                    @if($stat['publicador']->genero === 'M')
                        <span class="ra-tag">Hermano</span>
                    @else
                        <span class="ra-tag">Hermana</span>
                    @endif
                </td>
                <td class="hide-mobile">
                    <span class="ra-role">
                        @if($stat['publicador']->es_anciano) Anciano
                        @elseif($stat['publicador']->es_siervo_ministerial) Siervo ministerial
                        @elseif($stat['publicador']->es_precursor) Precursor
                        @elseif($stat['publicador']->es_menor) Menor
                        @else Publicador
                        @endif
                    </span>
                </td>
                <td>
                    <span class="ra-count {{ $stat['total'] === 0 ? 'ra-count--zero' : '' }}">{{ $stat['total'] }}</span>
                </td>
                <td>
                    <span class="ra-date">{{ $stat['ultima'] ? \Carbon\Carbon::parse($stat['ultima'])->translatedFormat('d M Y') : 'Nunca' }}</span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
</div>

<style>
.ra-header {
    margin-bottom: 1.5rem;
}

.ra-name {
    color: var(--text);
    text-decoration: none;
    font-weight: 500;
}

.ra-name:hover {
    color: var(--primary);
}

.ra-tag {
    font-size: 0.75rem;
    color: var(--text-muted);
}

.ra-role {
    font-size: 0.8rem;
    color: var(--text-muted);
}

.ra-count {
    font-weight: 600;
    font-size: 0.9rem;
    color: var(--text);
}

.ra-count--zero {
    color: var(--text-light);
}

.ra-date {
    font-size: 0.8rem;
    color: var(--text-muted);
}
</style>

@endsection
