@extends('layouts.app')

@section('title', 'Reuniones - Programas')

@section('content')

<div class="page-md">
    <div class="flex justify-between items-center mb-2" style="flex-wrap: wrap; gap: 1rem;">
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

    <div class="semanas-grid">
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

            // Mes para mostrar
            $mesInicio = $fechaLunes->translatedFormat('M');
            $mesFin = $fechaDomingo->translatedFormat('M');
            $rangoFecha = $fechaLunes->day . ($mesInicio !== $mesFin ? ' ' . $mesInicio : '') . '-' . $fechaDomingo->day . ' ' . $mesFin;
        @endphp

        <a href="{{ route('reuniones.edit', $programa) }}"
           class="semana-card {{ $esSemanaActual ? 'semana-actual' : '' }} {{ $esPasada ? 'semana-pasada' : '' }}">

            {{-- Indicador lateral --}}
            <div class="semana-indicator {{ $esSemanaActual ? 'indicator-actual' : ($esPasada ? 'indicator-pasada' : 'indicator-futura') }}"></div>

            <div class="semana-content">
                {{-- Cabecera: fecha tipo calendario --}}
                <div class="semana-fecha">
                    <div class="semana-mes">{{ $fechaLunes->translatedFormat('M Y') }}</div>
                    <div class="semana-rango">{{ $rangoFecha }}</div>
                    @if($esSemanaActual)
                        <span class="semana-badge-actual">Esta semana</span>
                    @endif
                </div>

                {{-- Info del programa --}}
                <div class="semana-info">
                    <div class="semana-estado">
                        @if($programa->estado === 'publicado')
                            <span class="badge badge-green">Publicado</span>
                        @else
                            <span class="badge badge-gray">Borrador</span>
                        @endif
                    </div>

                    <div class="semana-presidente">
                        @if($programa->presidente)
                            <span class="text-xs text-muted">Presidente:</span>
                            <span class="text-sm">{{ $programa->presidente->nombre_completo }}</span>
                        @else
                            <span class="text-muted text-xs">Sin presidente</span>
                        @endif
                    </div>
                </div>

                {{-- Barra de progreso --}}
                <div class="semana-progreso">
                    <div class="progreso-bar">
                        <div class="progreso-fill {{ $pct === 100 ? 'progreso-completo' : '' }}" style="width: {{ $pct }}%"></div>
                    </div>
                    <span class="progreso-texto {{ $pct === 100 ? 'text-primary' : 'text-muted' }}">{{ $asignadas }}/{{ $total }}</span>
                </div>
            </div>

            {{-- Flecha --}}
            <div class="semana-arrow">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
            </div>
        </a>
        @endforeach
    </div>

    <div class="mt-2">
        {{ $programas->links() }}
    </div>
</div>

<style>
.semanas-grid {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.semana-card {
    display: flex;
    align-items: center;
    gap: 0;
    background: var(--bg-white);
    border: 1px solid var(--border);
    border-radius: 10px;
    text-decoration: none;
    color: var(--text);
    transition: all 0.2s;
    overflow: hidden;
}

.semana-card:hover {
    border-color: var(--primary);
    transform: translateX(2px);
    color: var(--text);
}

/* Semana actual - destacada */
.semana-actual {
    border-color: #14b8a6;
    background: linear-gradient(135deg, rgba(20,184,166,0.08) 0%, var(--bg-white) 100%);
    box-shadow: 0 0 0 1px rgba(20,184,166,0.3);
}

.semana-actual:hover {
    border-color: #14b8a6;
    box-shadow: 0 4px 16px rgba(20,184,166,0.2);
}

/* Semana pasada - atenuada */
.semana-pasada {
    opacity: 0.6;
}

.semana-pasada:hover {
    opacity: 0.85;
}

/* Indicador lateral de color */
.semana-indicator {
    width: 5px;
    align-self: stretch;
    flex-shrink: 0;
}

.indicator-actual { background: #14b8a6; }
.indicator-pasada { background: var(--border); }
.indicator-futura { background: var(--primary); }

/* Contenido */
.semana-content {
    flex: 1;
    display: grid;
    grid-template-columns: 200px 1fr auto;
    align-items: center;
    gap: 1rem;
    padding: 1rem 1rem 1rem 0.75rem;
    min-width: 0;
}

/* Fecha */
.semana-fecha {
    display: flex;
    flex-direction: column;
    gap: 0.125rem;
}

.semana-mes {
    font-size: 0.65rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--text-muted);
    font-weight: 600;
}

.semana-rango {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--text);
    text-transform: capitalize;
}

.semana-actual .semana-rango {
    color: #14b8a6;
}

.semana-badge-actual {
    display: inline-block;
    margin-top: 0.25rem;
    padding: 0.125rem 0.5rem;
    background: #14b8a6;
    color: #121416;
    border-radius: 10px;
    font-size: 0.65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    width: fit-content;
}

/* Info */
.semana-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    min-width: 0;
}

.semana-presidente {
    display: flex;
    flex-direction: column;
}

/* Progreso */
.semana-progreso {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    min-width: 100px;
}

.progreso-bar {
    flex: 1;
    height: 4px;
    background: var(--border);
    border-radius: 2px;
    overflow: hidden;
}

.progreso-fill {
    height: 100%;
    background: var(--primary);
    border-radius: 2px;
    transition: width 0.3s;
}

.progreso-completo {
    background: #14b8a6;
}

.progreso-texto {
    font-size: 0.75rem;
    font-weight: 600;
    white-space: nowrap;
}

/* Flecha */
.semana-arrow {
    padding: 0 0.75rem;
    color: var(--text-light);
    flex-shrink: 0;
}

.semana-card:hover .semana-arrow {
    color: var(--primary);
    transform: translateX(2px);
}

.semana-actual:hover .semana-arrow {
    color: #14b8a6;
}

/* Responsive */
@media (max-width: 768px) {
    .semana-content {
        grid-template-columns: 1fr;
        gap: 0.5rem;
        padding: 0.75rem 0.75rem 0.75rem 0.6rem;
    }

    .semana-fecha {
        flex-direction: row;
        align-items: center;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .semana-mes { display: none; }

    .semana-rango {
        font-size: 1rem;
    }

    .semana-info {
        flex-direction: row;
        align-items: center;
        gap: 0.75rem;
    }

    .semana-presidente {
        flex-direction: row;
        gap: 0.25rem;
    }

    .semana-progreso {
        min-width: 80px;
    }

    .semana-arrow { display: none; }
}

@media (max-width: 480px) {
    .semana-info {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>

@endsection
