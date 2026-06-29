@extends('layouts.app')

@section('title', 'Reuniones - Programas')

@section('content')

<div class="page-md">
    <div class="ri-header">
        <div>
            <h1 class="page-title">Programas VyM</h1>
            <p class="page-subtitle">Vida y Ministerio Cristianos</p>
        </div>
    </div>

    @php
        $hoy = \Carbon\Carbon::now();
        $lunesActual = $hoy->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
        $proxDomingo = $proximoLunes->copy()->addDays(6);
        $proxRango = $proximoLunes->day . '-' . $proxDomingo->day . ' ' . $proxDomingo->translatedFormat('M Y');
    @endphp

    <div class="ri-add">
        <form action="{{ route('reuniones.agregar-proxima') }}" method="POST" class="ri-add__form">
            @csrf
            <button type="submit" class="ri-add__btn">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                <span class="ri-add__text">
                    <span class="ri-add__label">Añadir proxima semana</span>
                    <span class="ri-add__hint">{{ $proxRango }} · en blanco</span>
                </span>
            </button>
        </form>
        <a href="{{ route('reuniones.create') }}" class="ri-add__more">o crear varias a la vez</a>
    </div>

    @if($programas->count() === 0)
    <div class="empty-state">
        <div class="icon">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
            </svg>
        </div>
        <div class="title">Aun no hay programas</div>
        <div class="desc">
            @if($totalPasadas > 0 && !$incluirPasadas)
                Hay {{ $totalPasadas }} {{ $totalPasadas === 1 ? 'semana pasada' : 'semanas pasadas' }} ocultas. <a href="{{ route('reuniones.index', ['pasadas' => 1]) }}">Verlas</a>.
            @else
                Pulsa el boton de arriba para crear la primera semana en blanco.
            @endif
        </div>
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
            $huecos = $total - $asignadas;
            $titulosOk = $programa->titulosImportados();
            $completo = $programa->estaCompleto();
            $publicado = $programa->estado === 'publicado';

            $mesInicio = $fechaLunes->translatedFormat('M');
            $mesFin = $fechaDomingo->translatedFormat('M');
            $rangoFecha = $fechaLunes->day . ($mesInicio !== $mesFin ? ' ' . $mesInicio : '') . '-' . $fechaDomingo->day . ' ' . $mesFin;

            // Determinar badge de estado principal
            if ($publicado) {
                $badgeClase = 'ri-badge--ok';
                $badgeTexto = '✓ Publicado';
            } elseif ($completo) {
                $badgeClase = 'ri-badge--ready';
                $badgeTexto = 'Lista para publicar';
            } elseif (!$titulosOk) {
                $badgeClase = 'ri-badge--warn';
                $badgeTexto = 'Sin titulos';
            } elseif (!$programa->presidente_id) {
                $badgeClase = 'ri-badge--warn';
                $badgeTexto = 'Falta presidente';
            } elseif ($huecos > 0) {
                $badgeClase = 'ri-badge--soft';
                $badgeTexto = "Faltan {$huecos} " . ($huecos === 1 ? 'asignacion' : 'asignaciones');
            } else {
                $badgeClase = 'ri-badge--soft';
                $badgeTexto = 'Borrador';
            }
        @endphp

        <div class="ri-card {{ $esSemanaActual ? 'ri-card--current' : '' }} {{ $esPasada ? 'ri-card--past' : '' }}">

            <div class="ri-card__accent {{ $esSemanaActual ? 'ri-card__accent--current' : ($esPasada ? 'ri-card__accent--past' : 'ri-card__accent--future') }}"></div>

            <a href="{{ route('reuniones.edit', $programa) }}" class="ri-card__main">
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
                            <span class="ri-badge {{ $badgeClase }}">{{ $badgeTexto }}</span>
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
            </a>

            <div class="ri-card__actions">
                @if($publicado)
                    <a href="{{ route('reuniones.edit', $programa) }}" class="ri-act" title="Editar esta semana">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                        <span>Editar</span>
                    </a>
                @elseif(!$titulosOk)
                    <form action="{{ route('reuniones.importar-desde-listado', $programa) }}" method="POST" class="ri-act__form">
                        @csrf
                        <button type="submit" class="ri-act ri-act--primary" title="Traer titulos de jw.org">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                            <span>Traer titulos</span>
                        </button>
                    </form>
                @elseif($huecos > 0)
                    <form action="{{ route('reuniones.auto-asignar', $programa) }}" method="POST" class="ri-act__form">
                        @csrf
                        <button type="submit" class="ri-act ri-act--primary" title="Rellenar huecos automaticamente">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                            <span>Rellenar huecos</span>
                        </button>
                    </form>
                @elseif($completo)
                    <form action="{{ route('reuniones.publicar', $programa) }}" method="POST" class="ri-act__form" onsubmit="return confirm('¿Publicar esta semana? Podras editarla despues si hace falta.');">
                        @csrf
                        <button type="submit" class="ri-act ri-act--publish" title="Publicar">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            <span>Publicar</span>
                        </button>
                    </form>
                @endif
            </div>

            <a href="{{ route('reuniones.edit', $programa) }}" class="ri-card__arrow" aria-label="Abrir editor">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
            </a>
        </div>
        @endforeach
    </div>

    <div class="ri-pagination">
        {{ $programas->links() }}
    </div>

    @if($totalPasadas > 0)
    <div class="ri-pasadas">
        @if($incluirPasadas)
            <a href="{{ route('reuniones.index') }}" class="ri-pasadas__link">Ocultar semanas anteriores</a>
        @else
            <a href="{{ route('reuniones.index', ['pasadas' => 1]) }}" class="ri-pasadas__link">
                Ver {{ $totalPasadas }} {{ $totalPasadas === 1 ? 'semana anterior' : 'semanas anteriores' }}
            </a>
        @endif
    </div>
    @endif
</div>

<style>
/* ---- Index: Header ---- */
.ri-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1rem;
}

/* ---- Index: Add proxima semana ---- */
.ri-add {
    display: flex;
    flex-direction: column;
    align-items: stretch;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
}

.ri-add__form { margin: 0; }

.ri-add__btn {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    padding: 1rem 1.25rem;
    background: var(--color-tesoros-text);
    color: #0d0f11;
    border: none;
    border-radius: 10px;
    font-size: 1rem;
    font-weight: 700;
    cursor: pointer;
    transition: transform 0.1s, filter 0.15s;
}

.ri-add__btn:hover { filter: brightness(1.1); }
.ri-add__btn:active { transform: scale(0.99); }

.ri-add__text {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    line-height: 1.2;
}

.ri-add__label { font-size: 1.05rem; font-weight: 700; }

.ri-add__hint {
    font-size: 0.75rem;
    font-weight: 500;
    opacity: 0.7;
    text-transform: lowercase;
}

.ri-add__more {
    align-self: center;
    font-size: 0.85rem;
    color: var(--text-muted);
    text-decoration: underline;
    text-underline-offset: 3px;
}

.ri-add__more:hover { color: var(--primary); }

/* ---- Index: Card list ---- */
.ri-list {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.ri-card {
    display: flex;
    align-items: stretch;
    background: var(--bg-white);
    border: 1px solid var(--border);
    border-radius: 8px;
    color: var(--text);
    transition: border-color 0.15s, background 0.15s;
    overflow: hidden;
}

.ri-card:hover {
    border-color: var(--primary);
    background: var(--bg-hover);
}

.ri-card--current {
    border-color: var(--color-tesoros-text);
    background: rgba(20, 184, 166, 0.06);
}

.ri-card--current:hover {
    border-color: var(--color-tesoros-text);
    background: rgba(20, 184, 166, 0.1);
}

.ri-card--past { opacity: 0.6; }
.ri-card--past:hover { opacity: 0.85; }

/* Accent bar */
.ri-card__accent {
    width: 4px;
    flex-shrink: 0;
}

.ri-card__accent--current { background: var(--color-tesoros-text); }
.ri-card__accent--past    { background: var(--border); }
.ri-card__accent--future  { background: var(--primary); }

/* Main clickable area */
.ri-card__main {
    flex: 1;
    display: flex;
    align-items: center;
    text-decoration: none;
    color: inherit;
    min-width: 0;
}

.ri-card__main:hover { color: inherit; }

/* Card body grid */
.ri-card__body {
    flex: 1;
    display: grid;
    grid-template-columns: 170px 1fr 110px;
    align-items: center;
    gap: 1rem;
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

.ri-card--current .ri-card__range { color: var(--color-tesoros-text); }

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
    gap: 6px;
    min-width: 0;
}

.ri-card__pres { display: flex; flex-direction: column; }

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

/* Badges dinamicos */
.ri-badge {
    display: inline-flex;
    align-items: center;
    padding: 3px 9px;
    border-radius: 10px;
    font-size: 0.72rem;
    font-weight: 600;
    letter-spacing: 0.01em;
    white-space: nowrap;
    width: fit-content;
}

.ri-badge--ok {
    background: rgba(34, 197, 94, 0.15);
    color: #4ade80;
    border: 1px solid rgba(34, 197, 94, 0.3);
}

.ri-badge--ready {
    background: rgba(20, 184, 166, 0.15);
    color: var(--color-tesoros-text);
    border: 1px solid rgba(20, 184, 166, 0.35);
}

.ri-badge--warn {
    background: rgba(249, 115, 22, 0.15);
    color: #fb923c;
    border: 1px solid rgba(249, 115, 22, 0.3);
}

.ri-badge--soft {
    background: var(--bg-hover);
    color: var(--text-secondary);
    border: 1px solid var(--border);
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

.ri-card__fill--done { background: var(--color-tesoros-text); }

.ri-card__count {
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--text-muted);
    white-space: nowrap;
}

.ri-card__count--done { color: var(--color-tesoros-text); }

/* Actions column */
.ri-card__actions {
    display: flex;
    align-items: center;
    padding: 0 0.75rem;
    border-left: 1px solid var(--border);
    flex-shrink: 0;
}

.ri-act__form { margin: 0; }

.ri-act {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.5rem 0.75rem;
    background: transparent;
    border: 1px solid var(--border);
    border-radius: 6px;
    color: var(--text-secondary);
    font-size: 0.78rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s;
    min-height: 36px;
}

.ri-act:hover {
    border-color: var(--primary);
    color: var(--primary);
}

.ri-act--primary {
    background: var(--primary);
    color: #fff;
    border-color: var(--primary);
}

.ri-act--primary:hover { filter: brightness(1.1); color: #fff; }

.ri-act--publish {
    background: rgba(34, 197, 94, 0.15);
    color: #4ade80;
    border-color: rgba(34, 197, 94, 0.4);
}

.ri-act--publish:hover {
    background: rgba(34, 197, 94, 0.25);
    color: #4ade80;
}

/* Arrow */
.ri-card__arrow {
    display: flex;
    align-items: center;
    padding: 0 0.75rem;
    color: var(--text-light);
    flex-shrink: 0;
    transition: transform 0.15s, color 0.15s;
    text-decoration: none;
}

.ri-card:hover .ri-card__arrow {
    color: var(--primary);
    transform: translateX(2px);
}

.ri-card--current:hover .ri-card__arrow { color: var(--color-tesoros-text); }

/* Pagination */
.ri-pagination { margin-top: 1.5rem; }

/* Ver pasadas */
.ri-pasadas {
    text-align: center;
    margin-top: 1rem;
}

.ri-pasadas__link {
    display: inline-block;
    padding: 0.5rem 1rem;
    font-size: 0.85rem;
    color: var(--text-muted);
    text-decoration: underline;
    text-underline-offset: 3px;
}

.ri-pasadas__link:hover { color: var(--primary); }

/* ---- Responsive ---- */
@media (max-width: 768px) {
    .ri-card {
        flex-wrap: wrap;
    }

    .ri-card__accent {
        width: 100%;
        height: 3px;
    }

    .ri-card__main {
        width: 100%;
    }

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

    .ri-card__month { display: none; }

    .ri-card__range { font-size: 1rem; }

    .ri-card__meta {
        flex-direction: row;
        align-items: center;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .ri-card__pres {
        flex-direction: row;
        gap: 0.25rem;
    }

    .ri-card__progress { min-width: 80px; }

    .ri-card__actions {
        width: 100%;
        padding: 0 0.75rem 0.75rem 0.75rem;
        border-left: none;
        border-top: 1px solid var(--border);
        padding-top: 0.5rem;
    }

    .ri-act__form { width: 100%; }

    .ri-act {
        width: 100%;
        justify-content: center;
        min-height: 40px;
    }

    .ri-card__arrow { display: none; }
}

@media (max-width: 480px) {
    .ri-card__meta {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>

@endsection
