@extends('layouts.app')

@section('title', 'Programa VyM - ' . $programa->fecha_semana->format('d/m/Y'))

@section('content')

<div class="page-sm">
    <div class="flex justify-between items-center mb-2 no-print" style="flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 class="page-title">Programa VyM</h1>
            <p class="page-subtitle">Semana del {{ $programa->fecha_semana->translatedFormat('d \d\e F, Y') }}</p>
        </div>
        <div class="flex gap-1" style="flex-wrap: wrap;">
            <button onclick="window.print()" class="btn btn-teal">Imprimir</button>
            <a href="{{ route('reuniones.edit', $programa) }}" class="btn btn-secondary">Editar</a>
            <a href="{{ route('reuniones.index') }}" class="btn btn-ghost">Volver</a>
        </div>
    </div>

    <div class="reunion-programa-imprimible">
        <div class="reunion-print-header">
            <h2>Vida y Ministerio Cristianos</h2>
            <p>Semana del {{ $programa->fecha_semana->translatedFormat('d \d\e F \d\e Y') }}</p>
        </div>

        {{-- ROLES GENERALES --}}
        <table class="reunion-print-table">
            <tr>
                <td class="reunion-print-label">Presidente:</td>
                <td class="reunion-print-value">{{ $programa->presidente?->nombre_completo ?? '---' }}</td>
            </tr>
            <tr>
                <td class="reunion-print-label">Oracion de inicio:</td>
                <td class="reunion-print-value">{{ $programa->oracionInicio?->nombre_completo ?? '---' }}</td>
            </tr>
        </table>

        {{-- TESOROS DE LA BIBLIA --}}
        <div class="reunion-print-seccion reunion-print-tesoros">
            <h3>&#x1F48E; Tesoros de la Biblia</h3>
            <table class="reunion-print-table">
                @foreach($programa->partes->where('seccion', 'tesoros') as $parte)
                <tr>
                    <td class="reunion-print-label">
                        {{ $parte->titulo ?? $parte->nombre_tipo }}
                        <span class="reunion-print-min">({{ $parte->duracion_minutos }} min)</span>
                    </td>
                    <td class="reunion-print-value">{{ $parte->publicador?->nombre_completo ?? '---' }}</td>
                </tr>
                @endforeach
            </table>
        </div>

        {{-- SEAMOS MEJORES MAESTROS --}}
        <div class="reunion-print-seccion reunion-print-maestros">
            <h3>&#x1F33E; Seamos mejores maestros</h3>
            <table class="reunion-print-table">
                @foreach($programa->partes->where('seccion', 'maestros') as $parte)
                <tr>
                    <td class="reunion-print-label">
                        {{ $parte->titulo ?? $parte->nombre_tipo }}
                        <span class="reunion-print-min">({{ $parte->duracion_minutos }} min)</span>
                    </td>
                    <td class="reunion-print-value">
                        {{ $parte->publicador?->nombre_completo ?? '---' }}
                        @if($parte->necesita_ayudante)
                            <span class="reunion-print-ayudante">/ {{ $parte->ayudante?->nombre_completo ?? '---' }}</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </table>
        </div>

        {{-- NUESTRA VIDA CRISTIANA --}}
        <div class="reunion-print-seccion reunion-print-vida">
            <h3>&#x1F411; Nuestra vida cristiana</h3>
            <table class="reunion-print-table">
                @foreach($programa->partes->where('seccion', 'vida_cristiana') as $parte)
                <tr>
                    <td class="reunion-print-label">
                        {{ $parte->titulo ?? $parte->nombre_tipo }}
                        <span class="reunion-print-min">({{ $parte->duracion_minutos }} min)</span>
                    </td>
                    <td class="reunion-print-value">{{ $parte->publicador?->nombre_completo ?? '---' }}</td>
                </tr>
                @endforeach
                <tr>
                    <td class="reunion-print-label">Estudio biblico de congregacion</td>
                    <td class="reunion-print-value">
                        Conductor: {{ $programa->conductorEstudio?->nombre_completo ?? '---' }}
                        <br>Lector: {{ $programa->lectorEstudio?->nombre_completo ?? '---' }}
                    </td>
                </tr>
            </table>
        </div>

        {{-- CIERRE --}}
        <table class="reunion-print-table">
            <tr>
                <td class="reunion-print-label">Oracion final:</td>
                <td class="reunion-print-value">{{ $programa->oracionFinal?->nombre_completo ?? '---' }}</td>
            </tr>
        </table>
    </div>
</div>

@endsection
