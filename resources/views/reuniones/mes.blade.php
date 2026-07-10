@extends('layouts.app')

@section('title', 'VMC-' . $mesYYYYMM)

@section('content')

<div class="rs-container">
    <div class="rs-toolbar no-print">
        <div>
            <h1 class="page-title">Programa VyM — {{ $mesNombre }}</h1>
            <p class="page-subtitle">{{ count($programas) }} {{ count($programas) === 1 ? 'semana' : 'semanas' }}. Se imprime una por hoja.</p>
        </div>
        <div class="rs-toolbar__actions">
            <button onclick="window.print();" class="btn btn-teal">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V2h12v7"></path><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                Imprimir mes
            </button>
            <a href="{{ route('reuniones.index') }}" class="btn btn-ghost">Volver</a>
        </div>
    </div>

    @php
        function formatHoraMes($min) {
            $h = intdiv($min, 60);
            $m = $min % 60;
            return sprintf('%d:%02d', $h, $m);
        }
        $congregacionNombre = isset($congregacionActiva) ? $congregacionActiva->nombre : '';
    @endphp

    @foreach($programas as $indice => $programa)
    @php
        $minActual = 19 * 60 + 30;
        $partesTesorosCol = $programa->partes->where('seccion', 'tesoros')->sortBy('orden');
        $partesMaestrosCol = $programa->partes->where('seccion', 'maestros')->sortBy('orden');
        $partesVidaCol = $programa->partes->where('seccion', 'vida_cristiana')->sortBy('orden');
        $numParte = 1;
    @endphp

    <div class="rs-sheet {{ $indice > 0 ? 'rs-sheet--nextpage' : '' }}">
        <div class="rs-head">
            <div class="rs-head__left">
                <h2 class="rs-head__title">Programa para la reunion Vida y Ministerio Cristianos {{ $mesNombre }}</h2>
            </div>
            <div class="rs-head__right">{{ $congregacionNombre }}</div>
        </div>

        <div class="rs-datebar">
            {{ $programa->fecha_semana->format('d/m/Y') }}
        </div>

        <div class="rs-row">
            <span class="rs-time">{{ formatHoraMes($minActual) }}</span>
            <span class="rs-desc">Cancion y oracion</span>
            <span class="rs-name"><strong>Oracion inicio</strong> {{ $programa->oracionInicio?->nombre_completo ?? 'Sin asignar' }}</span>
        </div>
        @php $minActual += 5; @endphp

        <div class="rs-row">
            <span class="rs-time">{{ formatHoraMes($minActual) }}</span>
            <span class="rs-desc">Palabras de introduccion (1 min.)</span>
            <span class="rs-name"><strong>Presidente</strong> {{ $programa->presidente?->nombre_completo ?? 'Sin asignar' }}</span>
        </div>
        @php $minActual += 1; @endphp

        <div class="rs-section-bar rs-section-bar--tesoros">
            <span>TESOROS DE LA BIBLIA</span>
        </div>

        @foreach($partesTesorosCol as $parte)
        <div class="rs-row">
            <span class="rs-time">{{ formatHoraMes($minActual) }}</span>
            <span class="rs-desc">{{ $numParte }}. {{ $parte->titulo ?? $parte->nombre_tipo }} ({{ $parte->duracion_minutos }} min.)</span>
            <span class="rs-name">{{ $parte->publicador?->nombre_completo ?? '' }}</span>
        </div>
        @php $minActual += $parte->duracion_minutos; $numParte++; @endphp
        @endforeach

        <div class="rs-section-bar rs-section-bar--maestros">
            <span>SEAMOS MEJORES MAESTROS</span>
        </div>

        @foreach($partesMaestrosCol as $parte)
        <div class="rs-row">
            <span class="rs-time">{{ formatHoraMes($minActual) }}</span>
            <span class="rs-desc">{{ $numParte }}. {{ $parte->titulo ?? $parte->nombre_tipo }} ({{ $parte->duracion_minutos }} min.)</span>
            <span class="rs-name">
                @if($parte->publicador)
                    {{ $parte->publicador->nombre_completo }}
                    @if($parte->necesita_ayudante && $parte->ayudante)
                        &amp; {{ $parte->ayudante->nombre_completo }}
                    @endif
                @endif
            </span>
        </div>
        @php $minActual += $parte->duracion_minutos; $numParte++; @endphp
        @endforeach

        <div class="rs-section-bar rs-section-bar--vida">
            <span>NUESTRA VIDA CRISTIANA</span>
        </div>

        @foreach($partesVidaCol as $parte)
        <div class="rs-row">
            <span class="rs-time">{{ formatHoraMes($minActual) }}</span>
            <span class="rs-desc">{{ $numParte }}. {{ $parte->titulo ?? $parte->nombre_tipo }} ({{ $parte->duracion_minutos }} min.)</span>
            <span class="rs-name">{{ $parte->publicador?->nombre_completo ?? '' }}</span>
        </div>
        @php $minActual += $parte->duracion_minutos; $numParte++; @endphp
        @endforeach

        <div class="rs-row">
            <span class="rs-time">{{ formatHoraMes($minActual) }}</span>
            <span class="rs-desc">{{ $numParte }}. Estudio biblico (30 min.)</span>
            <span class="rs-name">
                <strong>Conductor</strong> {{ $programa->conductorEstudio?->nombre_completo ?? '' }}
                &nbsp;&nbsp;<strong>Lector</strong> {{ $programa->lectorEstudio?->nombre_completo ?? '' }}
            </span>
        </div>
        @php $minActual += 30; $numParte++; @endphp

        <div class="rs-row rs-row--cierre">
            <span class="rs-time">{{ formatHoraMes($minActual) }}</span>
            <span class="rs-desc">Palabras de conclusion (3 min.)</span>
            <span class="rs-name"></span>
        </div>
        @php $minActual += 4; @endphp

        <div class="rs-row rs-row--cierre">
            <span class="rs-time">{{ formatHoraMes($minActual) }}</span>
            <span class="rs-desc">Cancion y oracion</span>
            <span class="rs-name"><strong>Oracion final</strong> {{ $programa->oracionFinal?->nombre_completo ?? 'Sin asignar' }}</span>
        </div>

        <div class="rs-footer">Impreso {{ now()->format('d-m-Y') }}</div>
    </div>
    @endforeach
</div>

<style>
.rs-container {
    max-width: 820px;
    margin: 0 auto;
    padding: 0 1rem;
}

.rs-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.rs-toolbar__actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.rs-sheet {
    background: #fff;
    border-radius: 8px;
    padding: 2rem 2.5rem;
    color: #222;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    margin-bottom: 1rem;
}

.rs-sheet--nextpage {
    page-break-before: always;
    break-before: page;
}

.rs-head {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1.25rem;
    gap: 1rem;
}
.rs-head__title {
    font-size: 1.2rem;
    font-weight: 700;
    color: #1a1a1a;
    margin: 0;
    line-height: 1.3;
}
.rs-head__right {
    font-size: 1.1rem;
    font-weight: 700;
    color: #1a1a1a;
    text-align: right;
    white-space: nowrap;
}

.rs-datebar {
    background: #0f766e;
    color: #fff;
    font-weight: 700;
    font-size: 0.95rem;
    padding: 0.5rem 0.75rem;
    margin-bottom: 0;
    border-radius: 4px 4px 0 0;
}

.rs-row {
    display: flex;
    align-items: baseline;
    border-bottom: 1px solid #e5e7eb;
    padding: 0.5rem 0;
    gap: 0.5rem;
}
.rs-row--cierre { background: rgba(0,0,0,0.02); }
.rs-time {
    width: 42px;
    flex-shrink: 0;
    font-size: 0.75rem;
    font-weight: 700;
    color: #555;
    text-align: right;
    padding-right: 0.5rem;
}
.rs-desc {
    flex: 1;
    font-size: 0.85rem;
    color: #333;
    min-width: 0;
}
.rs-name {
    font-size: 0.85rem;
    color: #222;
    text-align: right;
    white-space: nowrap;
}
.rs-name strong {
    color: #555;
    font-weight: 600;
    margin-right: 0.25rem;
}

.rs-section-bar {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 0.75rem;
    margin-top: 0.25rem;
    font-size: 0.8rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    color: #fff;
}
.rs-section-icon { font-size: 0.9rem; }
.rs-section-bar--tesoros  { background: #0f766e; }
.rs-section-bar--maestros { background: #b45309; }
.rs-section-bar--vida     { background: #991b1b; }

.rs-footer {
    text-align: right;
    font-size: 0.7rem;
    color: #999;
    font-style: italic;
    margin-top: 1.5rem;
}

@media print {
    body, html { background: #fff !important; }
    .no-print, .header, .footer, .submenu, .main > .container > *:not(.rs-container) {
        display: none !important;
    }
    .main {
        padding: 0 !important;
        min-height: auto !important;
        background: #fff !important;
    }
    .rs-container {
        max-width: 100%;
        padding: 0;
    }
    .rs-sheet {
        border: none;
        border-radius: 0;
        padding: 0.5cm;
        box-shadow: none;
        margin-bottom: 0;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    .rs-sheet--nextpage {
        page-break-before: always;
        break-before: page;
    }
    .rs-datebar, .rs-section-bar {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    .rs-row { page-break-inside: avoid; }

    @page {
        margin: 1cm;
        margin-top: 0.5cm;
        margin-bottom: 0.5cm;
        @top-left { content: none; }
        @top-right { content: none; }
        @bottom-left { content: none; }
        @bottom-right { content: none; }
    }
}

@media (max-width: 640px) {
    .rs-sheet { padding: 1rem; }
    .rs-head { flex-direction: column; }
    .rs-head__right { text-align: left; }
    .rs-name { white-space: normal; text-align: right; min-width: 0; }
    .rs-row { flex-wrap: wrap; }
    .rs-time { width: 36px; font-size: 0.7rem; }
}
</style>

@endsection
