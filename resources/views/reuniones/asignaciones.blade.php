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

    <!-- Toggle de vistas -->
    <div class="vista-toggle">
        <button class="vista-btn active" data-vista="resumen" onclick="cambiarVista('resumen')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                <circle cx="12" cy="12" r="10"/><path d="M12 2a10 10 0 0 1 10 10"/><path d="M12 12L12 2"/>
                <path d="M12 12l7.07 7.07"/>
            </svg>
            Resumen
        </button>
        <button class="vista-btn" data-vista="lista" onclick="cambiarVista('lista')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                <path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/>
            </svg>
            Por Nombre
        </button>
    </div>

    <!-- ========== VISTA RESUMEN ========== -->
    <div id="vista-resumen">

        <!-- Gráficos pastel por nombramiento -->
        <div class="chart-grid">
            @foreach($porNombramiento as $key => $grupo)
            @php
                $pct = round(($grupo['asignaciones'] / $totalAsignaciones) * 100);
                $dash = ($pct / 100) * 251.2;
            @endphp
            <div class="chart-card" style="border-left: 3px solid {{ $grupo['color'] }}">
                <div class="chart-donut">
                    <svg viewBox="0 0 100 100" width="90" height="90">
                        <circle cx="50" cy="50" r="40" fill="none" stroke="rgba(255,255,255,0.06)" stroke-width="8"/>
                        <circle cx="50" cy="50" r="40" fill="none" stroke="{{ $grupo['color'] }}" stroke-width="8"
                            stroke-dasharray="{{ $dash }} {{ 251.2 - $dash }}"
                            stroke-dashoffset="62.8" stroke-linecap="round"
                            style="transition: stroke-dasharray 0.8s ease"/>
                        <text x="50" y="46" text-anchor="middle" fill="{{ $grupo['color'] }}" font-size="20" font-weight="700">{{ $pct }}%</text>
                        <text x="50" y="62" text-anchor="middle" fill="rgba(255,255,255,0.5)" font-size="9">{{ $grupo['asignaciones'] }} asig.</text>
                    </svg>
                </div>
                <div class="chart-info">
                    <span class="chart-label" style="color: {{ $grupo['color'] }}">{{ $grupo['label'] }}</span>
                    <div class="chart-stats">
                        <span><strong>{{ $grupo['personas'] }}</strong> personas</span>
                        <span><strong>{{ $grupo['asignaciones'] }}</strong> asignaciones</span>
                        @if($grupo['personas'] > 0)
                        <span><strong>{{ round($grupo['asignaciones'] / $grupo['personas'], 1) }}</strong> promedio/persona</span>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Tarjetas de publicadores agrupadas -->
        <div class="ra-filtros">
            <button class="filtro-btn active" data-filtro="todos" onclick="filtrarRA('todos')">Todos</button>
            <button class="filtro-btn" data-filtro="anciano" onclick="filtrarRA('anciano')" style="--filtro-color: #6366f1">Ancianos</button>
            <button class="filtro-btn" data-filtro="siervo" onclick="filtrarRA('siervo')" style="--filtro-color: #14b8a6">Siervos</button>
            <button class="filtro-btn" data-filtro="publicador" onclick="filtrarRA('publicador')" style="--filtro-color: #f59e0b">Publicadores</button>
        </div>

        <div class="ra-cards">
            @foreach($estadisticas as $stat)
            @php
                $pub = $stat['publicador'];
                if ($pub->es_anciano) { $tipo = 'anciano'; $color = '#6366f1'; $badge = 'Anciano'; }
                elseif ($pub->es_siervo_ministerial) { $tipo = 'siervo'; $color = '#14b8a6'; $badge = 'Siervo Min.'; }
                else { $tipo = 'publicador'; $color = '#f59e0b'; $badge = 'Publicador'; }
            @endphp
            <a href="{{ route('reuniones.historial', $pub) }}" class="ra-card" data-tipo="{{ $tipo }}">
                <div class="ra-card-left">
                    <div class="ra-avatar" style="background: {{ $color }}18; color: {{ $color }}">
                        {{ strtoupper(mb_substr($pub->nombre, 0, 1)) }}
                    </div>
                    <div class="ra-card-info">
                        <span class="ra-card-name">{{ $pub->nombre_completo }}</span>
                        <span class="ra-card-badge" style="color: {{ $color }}">{{ $badge }}</span>
                    </div>
                </div>
                <div class="ra-card-right">
                    <span class="ra-card-count {{ $stat['total'] === 0 ? 'ra-card-count--zero' : '' }}">{{ $stat['total'] }}</span>
                    <span class="ra-card-date">{{ $stat['ultima'] ? \Carbon\Carbon::parse($stat['ultima'])->translatedFormat('d M') : 'Nunca' }}</span>
                </div>
            </a>
            @endforeach
        </div>
    </div>

    <!-- ========== VISTA POR NOMBRE (lista original) ========== -->
    <div id="vista-lista" style="display: none;">
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
</div>

<style>
.ra-header { margin-bottom: 1.25rem; }

.ra-name { color: var(--text); text-decoration: none; font-weight: 500; }
.ra-name:hover { color: var(--primary); }
.ra-tag { font-size: 0.75rem; color: var(--text-muted); }
.ra-role { font-size: 0.8rem; color: var(--text-muted); }
.ra-count { font-weight: 600; font-size: 0.9rem; color: var(--text); }
.ra-count--zero { color: var(--text-light); }
.ra-date { font-size: 0.8rem; color: var(--text-muted); }

/* Filtros nombramiento */
.ra-filtros {
    display: flex;
    gap: 0.375rem;
    margin-bottom: 0.75rem;
    flex-wrap: wrap;
}

/* Cards de publicadores */
.ra-cards {
    display: flex;
    flex-direction: column;
    gap: 0.375rem;
}

.ra-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.625rem 0.875rem;
    background: var(--bg-surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-card);
    text-decoration: none;
    color: var(--text);
    transition: border-color 0.15s ease;
}

.ra-card:hover { border-color: var(--border-medium); }

.ra-card-left {
    display: flex;
    align-items: center;
    gap: 0.625rem;
    min-width: 0;
    flex: 1;
}

.ra-avatar {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: 700;
    flex-shrink: 0;
}

.ra-card-info {
    display: flex;
    flex-direction: column;
    min-width: 0;
}

.ra-card-name {
    font-size: 0.8125rem;
    font-weight: 500;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.ra-card-badge {
    font-size: 0.625rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.02em;
}

.ra-card-right {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    flex-shrink: 0;
    margin-left: 0.5rem;
}

.ra-card-count {
    font-size: 1.125rem;
    font-weight: 700;
    line-height: 1;
    color: var(--text);
}

.ra-card-count--zero { color: var(--text-disabled, #555); }

.ra-card-date {
    font-size: 0.625rem;
    color: var(--text-muted);
    margin-top: 0.125rem;
}
</style>

@push('scripts')
<script>
function cambiarVista(vista) {
    document.getElementById('vista-resumen').style.display = vista === 'resumen' ? 'block' : 'none';
    document.getElementById('vista-lista').style.display = vista === 'lista' ? 'block' : 'none';
    document.querySelectorAll('.vista-btn').forEach(function(btn) {
        btn.classList.toggle('active', btn.dataset.vista === vista);
    });
}

function filtrarRA(tipo) {
    document.querySelectorAll('.ra-filtros .filtro-btn').forEach(function(btn) {
        btn.classList.toggle('active', btn.dataset.filtro === tipo);
    });
    document.querySelectorAll('.ra-card').forEach(function(card) {
        card.style.display = (tipo === 'todos' || card.dataset.tipo === tipo) ? '' : 'none';
    });
}
</script>
@endpush

@endsection
