@extends('layouts.app')

@section('title', 'Reuniones - Programas')

@section('content')

<div class="page-md">
    <div class="ri-header">
        <div>
            <h1 class="page-title">Programas VyM</h1>
            <p class="page-subtitle">Vida y Ministerio Cristianos</p>
        </div>
        <a href="{{ route('reuniones.create') }}" class="btn btn-teal">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            Crear semanas
        </a>
    </div>

    @php
        $hoy = \Carbon\Carbon::now();
        $lunesActual = $hoy->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
    @endphp

    @if($programas->count() === 0)
    <div class="empty-state">
        <div class="icon">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
            </svg>
        </div>
        <div class="title">No hay programas creados</div>
        <div class="desc">Crea las primeras semanas para empezar a gestionar la reunion de Vida y Ministerio.</div>
        <a href="{{ route('reuniones.create') }}" class="btn btn-teal" style="margin-top: 1rem;">Crear semanas</a>
    </div>
    @endif

    <div class="ri-list">
        @foreach($programas as $programa)
        @php
            $fechaLunes = $programa->fecha_semana;
            $fechaDomingo = $fechaLunes->copy()->addDays(6);
            $esSemanaActual = $fechaLunes->eq($lunesActual);
            $esPasada = $fechaLunes->lt($lunesActual);
            $esFutura = $fechaLunes->gt($lunesActual);

            $programa->load('partes');
            $asignadas = $programa->contarAsignaciones();
            $total = $programa->totalPartes();
            $pct = $total > 0 ? round($asignadas / $total * 100) : 0;

            $mesInicio = $fechaLunes->translatedFormat('M');
            $mesFin = $fechaDomingo->translatedFormat('M');
            $rangoFecha = $fechaLunes->day . ($mesInicio !== $mesFin ? ' ' . $mesInicio : '') . '-' . $fechaDomingo->day . ' ' . $mesFin;
        @endphp

        <a href="{{ route('reuniones.edit', $programa) }}"
           class="ri-card {{ $esSemanaActual ? 'ri-card--current' : '' }} {{ $esPasada ? 'ri-card--past' : '' }}">

            <div class="ri-card__accent {{ $esSemanaActual ? 'ri-card__accent--current' : ($esPasada ? 'ri-card__accent--past' : 'ri-card__accent--future') }}"></div>

            <div class="ri-card__body">
                <div class="ri-card__date">
                    <span class="ri-card__month">{{ $fechaLunes->translatedFormat('M Y') }}</span>
                    <span class="ri-card__range">{{ $rangoFecha }}</span>
                    @if($esSemanaActual)
                        <span class="ri-card__now">Esta semana</span>
                    @endif
                </div>

                <div class="ri-card__meta">
                    <div class="ri-card__status">
                        @if($programa->estado === 'publicado')
                            <span class="badge badge-green">Publicado</span>
                        @else
                            <span class="badge badge-gray">Borrador</span>
                        @endif
                    </div>
                    <div class="ri-card__pres">
                        @if($programa->presidente)
                            <span class="ri-card__pres-label">Presidente</span>
                            <span class="ri-card__pres-name">{{ $programa->presidente->nombre_completo }}</span>
                        @else
                            <span class="ri-card__pres-label">Sin presidente</span>
                        @endif
                    </div>
                </div>

                <div class="ri-card__progress">
                    <div class="ri-card__bar">
                        <div class="ri-card__fill {{ $pct === 100 ? 'ri-card__fill--done' : '' }}" style="width: {{ $pct }}%"></div>
                    </div>
                    <span class="ri-card__count {{ $pct === 100 ? 'ri-card__count--done' : '' }}" title="{{ $asignadas }} de {{ $total }} partes asignadas">{{ $asignadas }}/{{ $total }}</span>
                </div>
            </div>

            <div class="ri-card__arrow">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
            </div>
        </a>
        @endforeach
    </div>

    <div class="ri-pagination">
        {{ $programas->links() }}
    </div>
</div>

<style>
/* ---- Index: Header ---- */
.ri-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

/* ---- Index: Card list ---- */
.ri-list {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.ri-card {
    display: flex;
    align-items: center;
    background: var(--bg-white);
    border: 1px solid var(--border);
    border-radius: 8px;
    text-decoration: none;
    color: var(--text);
    transition: border-color 0.15s, background 0.15s;
    overflow: hidden;
}

.ri-card:hover {
    border-color: var(--primary);
    background: var(--bg-hover);
    color: var(--text);
}

/* Current week */
.ri-card--current {
    border-color: var(--color-tesoros-text);
    background: rgba(20, 184, 166, 0.06);
}

.ri-card--current:hover {
    border-color: var(--color-tesoros-text);
    background: rgba(20, 184, 166, 0.1);
}

/* Past week */
.ri-card--past {
    opacity: 0.55;
}

.ri-card--past:hover {
    opacity: 0.8;
}

/* Accent bar */
.ri-card__accent {
    width: 4px;
    align-self: stretch;
    flex-shrink: 0;
}

.ri-card__accent--current { background: var(--color-tesoros-text); }
.ri-card__accent--past    { background: var(--border); }
.ri-card__accent--future  { background: var(--primary); }

/* Card body grid */
.ri-card__body {
    flex: 1;
    display: grid;
    grid-template-columns: 190px 1fr 120px;
    align-items: center;
    gap: 1.25rem;
    padding: 0.875rem 1rem 0.875rem 0.875rem;
    min-width: 0;
}

/* Date column */
.ri-card__date {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.ri-card__month {
    font-size: 0.625rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--text-muted);
    font-weight: 600;
}

.ri-card__range {
    font-size: 1.05rem;
    font-weight: 700;
    text-transform: capitalize;
    color: var(--text);
    line-height: 1.3;
}

.ri-card--current .ri-card__range {
    color: var(--color-tesoros-text);
}

.ri-card__now {
    display: inline-block;
    margin-top: 4px;
    padding: 2px 8px;
    background: var(--color-tesoros-text);
    color: #0d0f11;
    border-radius: 10px;
    font-size: 0.6rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    width: fit-content;
}

/* Meta column */
.ri-card__meta {
    display: flex;
    flex-direction: column;
    gap: 4px;
    min-width: 0;
}

.ri-card__pres {
    display: flex;
    flex-direction: column;
}

.ri-card__pres-label {
    font-size: 0.65rem;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.03em;
}

.ri-card__pres-name {
    font-size: 0.85rem;
    color: var(--text-secondary);
}

/* Progress column */
.ri-card__progress {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.ri-card__bar {
    flex: 1;
    height: 4px;
    background: var(--border);
    border-radius: 2px;
    overflow: hidden;
}

.ri-card__fill {
    height: 100%;
    background: var(--primary);
    border-radius: 2px;
    transition: width 0.3s;
}

.ri-card__fill--done {
    background: var(--color-tesoros-text);
}

.ri-card__count {
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--text-muted);
    white-space: nowrap;
}

.ri-card__count--done {
    color: var(--color-tesoros-text);
}

/* Arrow */
.ri-card__arrow {
    padding: 0 0.75rem;
    color: var(--text-light);
    flex-shrink: 0;
    transition: transform 0.15s;
}

.ri-card:hover .ri-card__arrow {
    color: var(--primary);
    transform: translateX(2px);
}

.ri-card--current:hover .ri-card__arrow {
    color: var(--color-tesoros-text);
}

/* Pagination */
.ri-pagination {
    margin-top: 1.5rem;
}

/* ---- Responsive ---- */
@media (max-width: 768px) {
    .ri-card__body {
        grid-template-columns: 1fr;
        gap: 0.5rem;
        padding: 0.75rem;
    }

    .ri-card__date {
        flex-direction: row;
        align-items: center;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .ri-card__month {
        display: none;
    }

    .ri-card__range {
        font-size: 0.95rem;
    }

    .ri-card__meta {
        flex-direction: row;
        align-items: center;
        gap: 0.75rem;
    }

    .ri-card__pres {
        flex-direction: row;
        gap: 0.25rem;
    }

    .ri-card__progress {
        min-width: 80px;
    }

    .ri-card__arrow {
        display: none;
    }
}

@media (max-width: 480px) {
    .ri-card__meta {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>

@endsection
