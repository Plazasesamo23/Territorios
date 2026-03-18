@extends('layouts.app')

@section('title', 'Asignar generos - Reuniones')

@section('content')

<div class="page-sm">
    <h1 class="page-title">Asignar generos</h1>
    <p class="page-subtitle">Necesario para la auto-asignacion de reuniones VyM</p>

    @php
        $sinGenero = $publicadores->whereNull('genero')->count();
        $total = $publicadores->count();
    @endphp

    @if($sinGenero > 0)
    <div class="alert-info-tipo mt-2 mb-2">
        <span class="alert-icon">!</span>
        <span>{{ $sinGenero }} de {{ $total }} publicadores no tienen genero asignado.</span>
    </div>
    @else
    <div class="config-info-box mt-2 mb-2">
        Todos los publicadores tienen genero asignado.
    </div>
    @endif

    <form action="{{ route('reuniones.generos.guardar') }}" method="POST">
        @csrf

        <table class="table-flat">
            <thead>
                <tr>
                    <th>Publicador</th>
                    <th>Nombramiento</th>
                    <th style="width: 160px;">Genero</th>
                </tr>
            </thead>
            <tbody>
                @foreach($publicadores as $pub)
                <tr>
                    <td class="font-medium">{{ $pub->nombre_completo }}</td>
                    <td class="text-muted text-sm">
                        @if($pub->es_anciano) Anciano
                        @elseif($pub->es_siervo_ministerial) Siervo ministerial
                        @elseif($pub->es_precursor) Precursor
                        @elseif($pub->es_menor) Menor
                        @else Publicador
                        @endif
                    </td>
                    <td>
                        <select name="generos[{{ $pub->id }}]" class="form-input" style="padding: 0.375rem 0.5rem; font-size: 0.85rem;">
                            <option value="">-- --</option>
                            <option value="M" {{ $pub->genero === 'M' ? 'selected' : '' }}>Hermano</option>
                            <option value="F" {{ $pub->genero === 'F' ? 'selected' : '' }}>Hermana</option>
                        </select>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="flex gap-1 mt-2" style="position: sticky; bottom: 0; padding: 1rem 0; background: var(--bg);">
            <button type="submit" class="btn btn-teal">Guardar generos</button>
            <a href="{{ route('reuniones.index') }}" class="btn btn-ghost">Volver</a>
        </div>
    </form>
</div>

@endsection
