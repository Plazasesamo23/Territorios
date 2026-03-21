@extends('layouts.app')

@section('title', 'Programa VyM - ' . $programa->fecha_semana->format('d/m/Y'))

@section('content')

<div class="page-sm">
    <div class="rs-toolbar no-print">
        <div>
            <h1 class="page-title">Programa VyM</h1>
            <p class="page-subtitle">Semana del {{ $programa->fecha_semana->translatedFormat('d \d\e F, Y') }}</p>
        </div>
        <div class="rs-toolbar__actions">
            <button onclick="window.print()" class="btn btn-teal">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V2h12v7"></path><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                Imprimir
            </button>
            <a href="{{ route('reuniones.edit', $programa) }}" class="btn btn-secondary">Editar</a>
            <a href="{{ route('reuniones.index') }}" class="btn btn-ghost">Volver</a>
        </div>
    </div>

    <div class="rs-sheet">
        <div class="rs-sheet__header">
            <h2 class="rs-sheet__title">Vida y Ministerio Cristianos</h2>
            <p class="rs-sheet__date">Semana del {{ $programa->fecha_semana->translatedFormat('d \d\e F \d\e Y') }}</p>
        </div>

        {{-- ROLES GENERALES --}}
        <table class="rs-table">
            <tr>
                <td class="rs-table__label">Presidente</td>
                <td class="rs-table__value">{{ $programa->presidente?->nombre_completo ?? '---' }}</td>
            </tr>
            <tr>
                <td class="rs-table__label">Oracion de inicio</td>
                <td class="rs-table__value">{{ $programa->oracionInicio?->nombre_completo ?? '---' }}</td>
            </tr>
        </table>

        {{-- TESOROS DE LA BIBLIA --}}
        <div class="rs-section rs-section--tesoros">
            <h3 class="rs-section__heading">Tesoros de la Biblia</h3>
            <table class="rs-table">
                @foreach($programa->partes->where('seccion', 'tesoros') as $parte)
                <tr>
                    <td class="rs-table__label">
                        {{ $parte->titulo ?? $parte->nombre_tipo }}
                        <span class="rs-table__min">({{ $parte->duracion_minutos }} min)</span>
                    </td>
                    <td class="rs-table__value">{{ $parte->publicador?->nombre_completo ?? '---' }}</td>
                </tr>
                @endforeach
            </table>
        </div>

        {{-- SEAMOS MEJORES MAESTROS --}}
        <div class="rs-section rs-section--maestros">
            <h3 class="rs-section__heading">Seamos mejores maestros</h3>
            <table class="rs-table">
                @foreach($programa->partes->where('seccion', 'maestros') as $parte)
                <tr>
                    <td class="rs-table__label">
                        {{ $parte->titulo ?? $parte->nombre_tipo }}
                        <span class="rs-table__min">({{ $parte->duracion_minutos }} min)</span>
                    </td>
                    <td class="rs-table__value">
                        {{ $parte->publicador?->nombre_completo ?? '---' }}
                        @if($parte->necesita_ayudante)
                            <span class="rs-table__helper">/ {{ $parte->ayudante?->nombre_completo ?? '---' }}</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </table>
        </div>

        {{-- NUESTRA VIDA CRISTIANA --}}
        <div class="rs-section rs-section--vida">
            <h3 class="rs-section__heading">Nuestra vida cristiana</h3>
            <table class="rs-table">
                @foreach($programa->partes->where('seccion', 'vida_cristiana') as $parte)
                <tr>
                    <td class="rs-table__label">
                        {{ $parte->titulo ?? $parte->nombre_tipo }}
                        <span class="rs-table__min">({{ $parte->duracion_minutos }} min)</span>
                    </td>
                    <td class="rs-table__value">{{ $parte->publicador?->nombre_completo ?? '---' }}</td>
                </tr>
                @endforeach
                <tr>
                    <td class="rs-table__label">Estudio biblico de congregacion</td>
                    <td class="rs-table__value">
                        Conductor: {{ $programa->conductorEstudio?->nombre_completo ?? '---' }}
                        <br>Lector: {{ $programa->lectorEstudio?->nombre_completo ?? '---' }}
                    </td>
                </tr>
            </table>
        </div>

        {{-- CIERRE --}}
        <table class="rs-table rs-table--last">
            <tr>
                <td class="rs-table__label">Oracion final</td>
                <td class="rs-table__value">{{ $programa->oracionFinal?->nombre_completo ?? '---' }}</td>
            </tr>
        </table>
    </div>
</div>

<style>
/* ---- Show: Toolbar ---- */
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

/* ---- Show: Sheet (print container) ---- */
.rs-sheet {
    background: var(--bg-white);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 2rem;
}

.rs-sheet__header {
    text-align: center;
    margin-bottom: 1.75rem;
    padding-bottom: 1.25rem;
    border-bottom: 1px solid var(--border);
}

.rs-sheet__title {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--text);
    margin: 0 0 0.25rem 0;
}

.rs-sheet__date {
    font-size: 0.875rem;
    color: var(--text-muted);
    margin: 0;
}

/* ---- Show: Section headers ---- */
.rs-section {
    margin-top: 1.5rem;
}

.rs-section__heading {
    font-size: 0.8rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    margin: 0 0 0.75rem 0;
    padding: 0.5rem 0 0.5rem 0.75rem;
    border-left: 4px solid transparent;
}

.rs-section--tesoros .rs-section__heading {
    color: var(--color-tesoros-text);
    border-left-color: var(--color-tesoros-text);
}

.rs-section--maestros .rs-section__heading {
    color: var(--color-maestros-text);
    border-left-color: var(--color-maestros-text);
}

.rs-section--vida .rs-section__heading {
    color: var(--color-vida-text);
    border-left-color: var(--color-vida-text);
}

/* ---- Show: Tables ---- */
.rs-table {
    width: 100%;
    border-collapse: collapse;
}

.rs-table tr {
    border-bottom: 1px solid var(--border);
}

.rs-table--last tr:last-child {
    border-bottom: none;
}

.rs-table__label {
    padding: 0.625rem 0.5rem;
    font-size: 0.85rem;
    color: var(--text-secondary);
    width: 50%;
    vertical-align: top;
}

.rs-table__value {
    padding: 0.625rem 0.5rem;
    font-size: 0.85rem;
    font-weight: 500;
    color: var(--text);
    vertical-align: top;
}

.rs-table__min {
    font-size: 0.7rem;
    color: var(--text-muted);
    font-weight: 400;
}

.rs-table__helper {
    color: var(--text-muted);
    font-weight: 400;
}

/* ---- Print styles ---- */
@media print {
    body {
        background: #fff !important;
        color: #000 !important;
    }

    .no-print,
    .header,
    .footer,
    .nav {
        display: none !important;
    }

    .main {
        padding: 0 !important;
        min-height: auto !important;
        background: #fff !important;
    }

    .rs-sheet {
        background: #fff;
        border: none;
        border-radius: 0;
        padding: 0;
        box-shadow: none;
    }

    .rs-sheet__header {
        border-bottom-color: #ccc;
    }

    .rs-sheet__title {
        color: #000;
    }

    .rs-sheet__date {
        color: #555;
    }

    .rs-section__heading {
        color: #333 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .rs-section--tesoros .rs-section__heading {
        border-left-color: #0f766e !important;
    }

    .rs-section--maestros .rs-section__heading {
        border-left-color: #b45309 !important;
    }

    .rs-section--vida .rs-section__heading {
        border-left-color: #991b1b !important;
    }

    .rs-table tr {
        border-bottom-color: #ddd;
    }

    .rs-table__label {
        color: #333;
    }

    .rs-table__value {
        color: #000;
    }

    .rs-table__min {
        color: #777;
    }

    .rs-table__helper {
        color: #777;
    }
}

/* ---- Responsive ---- */
@media (max-width: 640px) {
    .rs-sheet {
        padding: 1.25rem;
    }

    .rs-table__label,
    .rs-table__value {
        display: block;
        width: 100%;
        padding: 0.25rem 0.5rem;
    }

    .rs-table__label {
        padding-top: 0.625rem;
        padding-bottom: 0;
        font-weight: 500;
    }

    .rs-table__value {
        padding-bottom: 0.625rem;
    }

    .rs-table tr {
        display: block;
    }
}
</style>

@endsection
