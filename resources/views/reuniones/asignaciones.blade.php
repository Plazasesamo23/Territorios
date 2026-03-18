@extends('layouts.app')

@section('title', 'Asignaciones - Reuniones')

@section('content')

<div class="page-md">
    <div class="flex justify-between items-center mb-2">
        <div>
            <h1 class="page-title">Asignaciones</h1>
            <p class="page-subtitle">Vista general de asignaciones (ultimos 3 meses)</p>
        </div>
    </div>

    <table class="table-flat">
        <thead>
            <tr>
                <th>Publicador</th>
                <th>Genero</th>
                <th>Nombramiento</th>
                <th>Asignaciones</th>
                <th>Ultima asignacion</th>
            </tr>
        </thead>
        <tbody>
            @foreach($estadisticas as $stat)
            <tr class="clickable-row" data-href="{{ route('reuniones.historial', $stat['publicador']) }}">
                <td class="font-medium"><a href="{{ route('reuniones.historial', $stat['publicador']) }}" style="color: var(--text); text-decoration: none;">{{ $stat['publicador']->nombre_completo }}</a></td>
                <td>
                    @if($stat['publicador']->genero === 'M')
                        <span class="text-muted text-xs">Hermano</span>
                    @else
                        <span class="text-muted text-xs">Hermana</span>
                    @endif
                </td>
                <td class="text-muted text-sm">
                    @if($stat['publicador']->es_anciano) Anciano
                    @elseif($stat['publicador']->es_siervo_ministerial) Siervo ministerial
                    @elseif($stat['publicador']->es_precursor) Precursor
                    @elseif($stat['publicador']->es_menor) Menor
                    @else Publicador
                    @endif
                </td>
                <td>
                    <span class="{{ $stat['total'] === 0 ? 'text-muted' : '' }}">{{ $stat['total'] }}</span>
                </td>
                <td class="text-muted text-sm">
                    {{ $stat['ultima'] ? \Carbon\Carbon::parse($stat['ultima'])->translatedFormat('d M Y') : 'Nunca' }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endsection
