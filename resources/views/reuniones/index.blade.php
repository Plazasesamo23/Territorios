@extends('layouts.app')

@section('title', 'Reuniones - Programas')

@section('content')

<div class="page-md">
    <div class="flex justify-between items-center mb-2">
        <div>
            <h1 class="page-title">Programas VyM</h1>
            <p class="page-subtitle">Vida y Ministerio Cristianos</p>
        </div>
        <a href="{{ route('reuniones.create') }}" class="btn btn-teal">+ Crear programa</a>
    </div>

    @if($programas->isEmpty())
        <div class="empty-state">
            <div class="icon">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
                </svg>
            </div>
            <div class="title">No hay programas creados</div>
            <div class="desc">Crea tu primer programa de reunion semanal</div>
            <a href="{{ route('reuniones.create') }}" class="btn btn-teal mt-2">Crear programa</a>
        </div>
    @else
        <table class="table-flat">
            <thead>
                <tr>
                    <th>Semana</th>
                    <th>Presidente</th>
                    <th>Estado</th>
                    <th>Asignaciones</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($programas as $programa)
                <tr class="clickable-row" data-href="{{ route('reuniones.edit', $programa) }}">
                    <td>
                        <span class="font-medium">{{ $programa->fecha_semana->translatedFormat('d M Y') }}</span>
                        <span class="text-muted text-xs" style="display:block;">{{ $programa->fecha_semana->translatedFormat('l') }}</span>
                    </td>
                    <td>
                        @if($programa->presidente)
                            {{ $programa->presidente->nombre_completo }}
                        @else
                            <span class="text-muted">Sin asignar</span>
                        @endif
                    </td>
                    <td>
                        @if($programa->estado === 'publicado')
                            <span class="badge badge-green">Publicado</span>
                        @else
                            <span class="badge badge-gray">Borrador</span>
                        @endif
                    </td>
                    <td>
                        @php
                            $programa->load('partes');
                            $asignadas = $programa->contarAsignaciones();
                            $total = $programa->totalPartes();
                            $pct = $total > 0 ? round($asignadas / $total * 100) : 0;
                        @endphp
                        <span class="{{ $pct === 100 ? 'text-primary' : '' }}">{{ $asignadas }}/{{ $total }}</span>
                        <span class="text-muted text-xs">({{ $pct }}%)</span>
                    </td>
                    <td class="text-right">
                        <a href="{{ route('reuniones.edit', $programa) }}" class="btn btn-sm btn-ghost">Editar</a>
                        <a href="{{ route('reuniones.show', $programa) }}" class="btn btn-sm btn-ghost">Ver</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-2">
            {{ $programas->links() }}
        </div>
    @endif
</div>

@endsection
