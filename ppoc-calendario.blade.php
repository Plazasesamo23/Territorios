@extends('layouts.app')

@section('title', 'PPOC - ' . ucfirst($nombreMes) . ' ' . $year)

@section('content')

<div class="page-nav">
    <div class="page-breadcrumbs">
        @if(auth()->user()->isPpocUser())
            <span class="breadcrumb-current">PPOC - Calendario</span>
        @else
            <a href="{{ route('dashboard') }}" class="breadcrumb-link">Dashboard</a>
            <span class="breadcrumb-sep">></span>
            <span class="breadcrumb-current">PPOC - Calendario</span>
        @endif
    </div>
</div>

<div class="ppoc-page">
    <!-- Header con navegacion de meses -->
    <div class="calendar-header">
        <a href="{{ route('ppoc.calendario', ['year' => $prevMonth->year, 'month' => $prevMonth->month]) }}" class="nav-month-btn">
            &#x276E; {{ $prevMonth->translatedFormat('M') }}
        </a>
        <div class="month-title">
            <h1>{{ ucfirst($nombreMes) }} {{ $year }}</h1>
            <a href="{{ route('ppoc.calendario') }}" class="btn-today">Hoy</a>
        </div>
        <a href="{{ route('ppoc.calendario', ['year' => $nextMonth->year, 'month' => $nextMonth->month]) }}" class="nav-month-btn">
            {{ $nextMonth->translatedFormat('M') }} &#x276F;
        </a>
    </div>

    <!-- Botones de accion -->
    <div class="generate-section">
        <div class="action-buttons">
            <form action="{{ route('ppoc.generar-mes') }}" method="POST" class="generate-form">
                @csrf
                <input type="hidden" name="year" value="{{ $year }}">
                <input type="hidden" name="month" value="{{ $month }}">
                <button type="submit" class="btn-generate" onclick="return confirm('Esto generara los turnos para {{ ucfirst($nombreMes) }} {{ $year }} basado en las plantillas. ¿Continuar?')">
                    &#x2728; Generar Turnos
                </button>
            </form>
            @if($mesGenerado)
            <form action="{{ route('ppoc.asignacion-automatica') }}" method="POST" class="generate-form">
                @csrf
                <input type="hidden" name="year" value="{{ $year }}">
                <input type="hidden" name="month" value="{{ $month }}">
                <button type="submit" class="btn-auto-assign" onclick="return confirm('Esto asignara automaticamente publicadores a los turnos vacios basandose en disponibilidad y equilibrio de turnos. ¿Continuar?')">
                    &#x1F916; Asignar Automatico
                </button>
            </form>
            <form action="{{ route('ppoc.limpiar-mes') }}" method="POST" class="generate-form">
                @csrf
                <input type="hidden" name="year" value="{{ $year }}">
                <input type="hidden" name="month" value="{{ $month }}">
                <button type="submit" class="btn-danger" onclick="return confirm('ATENCION: Esto eliminara TODAS las asignaciones de {{ ucfirst($nombreMes) }} {{ $year }}. Esta accion no se puede deshacer. ¿Continuar?')">
                    &#x1F5D1; Limpiar Mes
                </button>
            </form>
            <a href="{{ route('ppoc.exportar-pdf', ['year' => $year, 'month' => $month]) }}" class="btn-export" target="_blank">
                &#x1F4C4; Exportar PDF
            </a>
            @endif
        </div>
        @if(auth()->user()->canManagePPOC())
        <div class="quick-links">
            <a href="{{ route('ppoc.turnos.index') }}" class="quick-link">&#x1F4CB; Plantillas</a>
            <a href="{{ route('ppoc.aprobados') }}" class="quick-link">&#x2705; Aprobados</a>
            <a href="{{ route('ppoc.disponibilidad.por-turno') }}" class="quick-link">&#x1F4C6; Disponibilidades</a>
        </div>
        @endif
    </div>

    <!-- Layout principal: Calendario + Panel lateral -->
    <div class="ppoc-main-layout">
        <!-- Calendario (lado izquierdo) -->
        <div class="calendar-section">
            <!-- Estadisticas y Alertas del mes -->
            @if($mesGenerado && isset($alertasData))
            <div class="stats-section">
                <div class="stats-cards">
                    <div class="stat-card">
                        <span class="stat-value">{{ $alertasData['stats']['turnos_completos'] ?? 0 }}/{{ $alertasData['stats']['turnos_totales'] ?? 0 }}</span>
                        <span class="stat-label">Turnos completos</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-value">{{ $alertasData['stats']['asignaciones_totales'] ?? 0 }}</span>
                        <span class="stat-label">Asignaciones</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-value">{{ $alertasData['stats']['publicadores_asignados'] ?? 0 }}</span>
                        <span class="stat-label">Publicadores</span>
                    </div>
                </div>

                @if(!empty($alertasData['alertas']))
                <div class="alertas-box">
                    @foreach($alertasData['alertas'] as $alerta)
                    <div class="alerta alerta-{{ $alerta['tipo'] }}">
                        {{ $alerta['mensaje'] }}
                    </div>
                    @endforeach
                </div>
                @endif

                @if(!empty($alertasData['publicadores']))
                <details class="publicadores-stats">
                    <summary>Ver estadisticas por publicador ({{ count($alertasData['publicadores']) }})</summary>
                    <div class="pub-stats-grid">
                        @foreach($alertasData['publicadores'] as $pub)
                        <div class="pub-stat-item estado-{{ $pub['estado'] }}">
                            <span class="pub-name">{{ $pub['nombre'] }}</span>
                            <span class="pub-turnos">{{ $pub['turnos'] }} turnos</span>
                            @if($pub['es_precursor'])
                            <span class="pub-badge precursor">P</span>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </details>
                @endif
            </div>
            @endif

            <!-- Calendario -->
            <div class="calendar-container">
                <div class="calendar-weekdays">
                    <div class="weekday">Lun</div>
                    <div class="weekday">Mar</div>
                    <div class="weekday">Mie</div>
                    <div class="weekday">Jue</div>
                    <div class="weekday">Vie</div>
                    <div class="weekday weekend">Sab</div>
                    <div class="weekday weekend">Dom</div>
                </div>

                <div class="calendar-grid">
                    @foreach($semanas as $semana)
                        @foreach($semana as $dia)
                            @if($dia === null)
                                <div class="calendar-day empty"></div>
                            @else
                                <div class="calendar-day {{ $dia['esHoy'] ? 'today' : '' }} {{ !$dia['esMesActual'] ? 'other-month' : '' }}">
                                    <div class="day-header">
                                        <span class="day-number {{ $dia['esHoy'] ? 'today-badge' : '' }}">{{ $dia['numero'] }}</span>
                                    </div>
                                    <div class="day-content">
                                        @if(isset($dia['turnos']) && count($dia['turnos']) > 0)
                                            @foreach($dia['turnos'] as $turno)
                                                <div class="turno-item {{ $turno->estaCompleto() ? 'completo' : 'incompleto' }}">
                                                    <div class="turno-header">
                                                        <span class="turno-hora">{{ \Carbon\Carbon::parse($turno->hora_inicio)->format('H:i') }}</span>
                                                        @if(auth()->user()->canManagePPOC())
                                                        <form action="{{ route('ppoc.turno-generado.destroy', $turno) }}" method="POST" class="delete-form">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn-delete-turno" title="Eliminar turno" onclick="return confirm('¿Eliminar este turno?')">×</button>
                                                        </form>
                                                        @endif
                                                    </div>
                                                    @if($turno->ubicacion)
                                                        <div class="turno-ubicacion">{{ $turno->ubicacion }}</div>
                                                    @endif
                                                    <div class="turno-asignados">
                                                        @foreach($turno->asignaciones as $asig)
                                                            <div class="asignado {{ $asig->rol }}">
                                                                <span class="asignado-nombre">{{ $asig->publicador->nombre ?? 'N/A' }}</span>
                                                                <div class="asignado-btns">
                                                                    <button type="button" class="btn-cambiar-asig" title="Cambiar" onclick="abrirModalSugerencias({{ $asig->id }})">&#x21C4;</button>
                                                                    <form action="{{ route('ppoc.asignaciones.destroy', $asig) }}" method="POST" class="remove-asig">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit" class="btn-remove-asig" title="Quitar">×</button>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                        @if($turno->tieneEspacioDisponible())
                                                            <button class="btn-add-pub"
                                                                    onclick="abrirModal({{ $turno->id }})"
                                                                    title="Agregar publicador">
                                                                + Agregar
                                                            </button>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Panel lateral de estadisticas (lado derecho) -->
        @if($mesGenerado && isset($alertasData) && isset($alertasData['panel']))
        <div class="stats-panel">
            <div class="panel-header">
                <h3>Resumen del Mes</h3>
            </div>

            <!-- Precursores -->
            <div class="panel-section">
                <div class="section-title">
                    <span class="section-icon">&#x1F451;</span>
                    Precursores
                </div>
                <div class="section-stats">
                    <div class="stat-row">
                        <span class="stat-key">Total:</span>
                        <span class="stat-val">{{ $alertasData['panel']['precursores']['total'] ?? 0 }}</span>
                    </div>
                    <div class="stat-row highlight">
                        <span class="stat-key">Media turnos:</span>
                        <span class="stat-val">{{ $alertasData['panel']['precursores']['media'] ?? 0 }}</span>
                    </div>
                    @if(isset($alertasData['panel']['precursores']['min']) && $alertasData['panel']['precursores']['min'])
                    <div class="stat-row extremo min">
                        <span class="stat-key">&#x2B07; Menos:</span>
                        <span class="stat-val">{{ $alertasData['panel']['precursores']['min']['nombre'] ?? 'N/A' }} ({{ $alertasData['panel']['precursores']['min']['turnos'] ?? 0 }})</span>
                    </div>
                    @endif
                    @if(isset($alertasData['panel']['precursores']['max']) && $alertasData['panel']['precursores']['max'])
                    <div class="stat-row extremo max">
                        <span class="stat-key">&#x2B06; Mas:</span>
                        <span class="stat-val">{{ $alertasData['panel']['precursores']['max']['nombre'] ?? 'N/A' }} ({{ $alertasData['panel']['precursores']['max']['turnos'] ?? 0 }})</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Publicadores -->
            <div class="panel-section">
                <div class="section-title">
                    <span class="section-icon">&#x1F464;</span>
                    Publicadores
                </div>
                <div class="section-stats">
                    <div class="stat-row">
                        <span class="stat-key">Total:</span>
                        <span class="stat-val">{{ $alertasData['panel']['publicadores']['total'] ?? 0 }}</span>
                    </div>
                    <div class="stat-row highlight">
                        <span class="stat-key">Media turnos:</span>
                        <span class="stat-val">{{ $alertasData['panel']['publicadores']['media'] ?? 0 }}</span>
                    </div>
                    @if(isset($alertasData['panel']['publicadores']['min']) && $alertasData['panel']['publicadores']['min'])
                    <div class="stat-row extremo min">
                        <span class="stat-key">&#x2B07; Menos:</span>
                        <span class="stat-val">{{ $alertasData['panel']['publicadores']['min']['nombre'] ?? 'N/A' }} ({{ $alertasData['panel']['publicadores']['min']['turnos'] ?? 0 }})</span>
                    </div>
                    @endif
                    @if(isset($alertasData['panel']['publicadores']['max']) && $alertasData['panel']['publicadores']['max'])
                    <div class="stat-row extremo max">
                        <span class="stat-key">&#x2B06; Mas:</span>
                        <span class="stat-val">{{ $alertasData['panel']['publicadores']['max']['nombre'] ?? 'N/A' }} ({{ $alertasData['panel']['publicadores']['max']['turnos'] ?? 0 }})</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Capitanes -->
            <div class="panel-section">
                <div class="section-title">
                    <span class="section-icon">&#x2693;</span>
                    Capitanes
                </div>
                <div class="section-stats">
                    <div class="stat-row">
                        <span class="stat-key">Total:</span>
                        <span class="stat-val">{{ $alertasData['panel']['capitanes']['total'] ?? 0 }}</span>
                    </div>
                    <div class="stat-row highlight">
                        <span class="stat-key">Media turnos:</span>
                        <span class="stat-val">{{ $alertasData['panel']['capitanes']['media'] ?? 0 }}</span>
                    </div>
                    @if(isset($alertasData['panel']['capitanes']['min']) && $alertasData['panel']['capitanes']['min'])
                    <div class="stat-row extremo min">
                        <span class="stat-key">&#x2B07; Menos:</span>
                        <span class="stat-val">{{ $alertasData['panel']['capitanes']['min']['nombre'] ?? 'N/A' }} ({{ $alertasData['panel']['capitanes']['min']['turnos'] ?? 0 }})</span>
                    </div>
                    @endif
                    @if(isset($alertasData['panel']['capitanes']['max']) && $alertasData['panel']['capitanes']['max'])
                    <div class="stat-row extremo max">
                        <span class="stat-key">&#x2B06; Mas:</span>
                        <span class="stat-val">{{ $alertasData['panel']['capitanes']['max']['nombre'] ?? 'N/A' }} ({{ $alertasData['panel']['capitanes']['max']['turnos'] ?? 0 }})</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Equilibrio visual -->
            <div class="panel-section balance-section">
                <div class="section-title">
                    <span class="section-icon">&#x2696;</span>
                    Equilibrio
                </div>
                @php
                    $mediaPrec = $alertasData['panel']['precursores']['media'] ?? 0;
                    $mediaPub = $alertasData['panel']['publicadores']['media'] ?? 0;
                    $ratio = $mediaPub > 0 ? round($mediaPrec / $mediaPub, 2) : 0;
                    $equilibrado = $ratio >= 0.8 && $ratio <= 1.5;
                @endphp
                <div class="balance-indicator {{ $equilibrado ? 'bueno' : 'revisar' }}">
                    @if($equilibrado)
                        <span class="balance-icon">&#x2705;</span>
                        <span class="balance-text">Equilibrio correcto</span>
                    @else
                        <span class="balance-icon">&#x26A0;</span>
                        <span class="balance-text">Revisar distribucion</span>
                    @endif
                </div>
                <div class="ratio-info">
                    Ratio Prec/Pub: {{ $ratio }}
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Modal para asignar publicador -->
<div id="modal-asignar" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Asignar Publicador</h2>
            <button class="modal-close" onclick="cerrarModal()">×</button>
        </div>
        <form action="{{ route('ppoc.asignaciones.store') }}" method="POST">
            @csrf
            <input type="hidden" name="turno_generado_id" id="turno-generado-id">
            <div class="modal-body">
                <div class="form-group">
                    <label>Publicador</label>
                    <select name="publicador_id" class="form-select" required>
                        <option value="">Seleccionar publicador...</option>
                        @foreach($publicadores as $pub)
                            <option value="{{ $pub->id }}">{{ $pub->nombre }} {{ $pub->apellidos }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Rol</label>
                    <select name="rol" class="form-select" required>
                        <option value="voluntario">Voluntario</option>
                        <option value="capitan">Capitan</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="cerrarModal()">Cancelar</button>
                <button type="submit" class="btn-submit">Asignar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal para sugerencias de reemplazo -->
<div id="modal-sugerencias" class="modal" style="display: none;">
    <div class="modal-content modal-sugerencias">
        <div class="modal-header">
            <h2>Cambiar Publicador</h2>
            <button class="modal-close" onclick="cerrarModalSugerencias()">×</button>
        </div>
        <div class="modal-body">
            <div class="sugerencia-info">
                <p><strong>Turno:</strong> <span id="sug-turno-info"></span></p>
                <p><strong>Actual:</strong> <span id="sug-actual"></span> (<span id="sug-rol"></span>)</p>
            </div>
            <div id="sugerencias-loading" class="loading-spinner">Cargando sugerencias...</div>
            <div id="sugerencias-lista" class="sugerencias-lista"></div>
            <div id="sugerencias-vacio" class="sugerencias-vacio" style="display:none;">
                No hay publicadores disponibles para este turno.
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-cancel" onclick="cerrarModalSugerencias()">Cancelar</button>
        </div>
    </div>
</div>

<form id="form-reemplazar" method="POST" style="display:none;">
    @csrf
    <input type="hidden" name="nuevo_publicador_id" id="nuevo-publicador-id">
</form>

<script>
function abrirModal(turnoId) {
    document.getElementById('turno-generado-id').value = turnoId;
    document.getElementById('modal-asignar').style.display = 'flex';
}

function cerrarModal() {
    document.getElementById('modal-asignar').style.display = 'none';
}

document.getElementById('modal-asignar').addEventListener('click', function(e) {
    if (e.target === this) cerrarModal();
});

// Funciones para modal de sugerencias
let asignacionActualId = null;

function abrirModalSugerencias(asignacionId) {
    asignacionActualId = asignacionId;
    document.getElementById('modal-sugerencias').style.display = 'flex';
    document.getElementById('sugerencias-loading').style.display = 'block';
    document.getElementById('sugerencias-lista').innerHTML = '';
    document.getElementById('sugerencias-vacio').style.display = 'none';

    fetch('/ppoc/asignaciones/' + asignacionId + '/sugerencias')
        .then(response => response.json())
        .then(data => {
            document.getElementById('sugerencias-loading').style.display = 'none';
            document.getElementById('sug-turno-info').textContent = data.turno_fecha + ' ' + data.turno_hora;
            document.getElementById('sug-actual').textContent = data.publicador_actual;
            document.getElementById('sug-rol').textContent = data.rol === 'capitan' ? 'Capitan' : 'Voluntario';

            if (data.sugerencias.length === 0) {
                document.getElementById('sugerencias-vacio').style.display = 'block';
                return;
            }

            let html = '';
            data.sugerencias.forEach(function(pub) {
                let badges = '';
                if (pub.es_capitan) badges += '<span class="sug-badge capitan">C</span>';
                if (pub.es_precursor) badges += '<span class="sug-badge precursor">P</span>';

                html += '<div class="sugerencia-item" onclick="seleccionarReemplazo(' + pub.id + ', \'' + pub.nombre_completo.replace(/'/g, "\\'") + '\')">' +
                    '<div class="sug-nombre">' + pub.nombre_completo + '</div>' +
                    '<div class="sug-meta">' +
                        '<span class="sug-turnos">' + pub.turnos_mes + ' turnos este mes</span>' +
                        badges +
                    '</div>' +
                '</div>';
            });
            document.getElementById('sugerencias-lista').innerHTML = html;
        })
        .catch(error => {
            document.getElementById('sugerencias-loading').style.display = 'none';
            document.getElementById('sugerencias-vacio').textContent = 'Error al cargar sugerencias.';
            document.getElementById('sugerencias-vacio').style.display = 'block';
        });
}

function cerrarModalSugerencias() {
    document.getElementById('modal-sugerencias').style.display = 'none';
    asignacionActualId = null;
}

function seleccionarReemplazo(publicadorId, nombre) {
    if (!confirm('¿Reemplazar por ' + nombre + '?')) return;

    var form = document.getElementById('form-reemplazar');
    form.action = '/ppoc/asignaciones/' + asignacionActualId + '/reemplazar';
    document.getElementById('nuevo-publicador-id').value = publicadorId;
    form.submit();
}

document.getElementById('modal-sugerencias').addEventListener('click', function(e) {
    if (e.target === this) cerrarModalSugerencias();
});
</script>

<style>
.ppoc-page {
    max-width: 1600px;
    margin: 0 auto;
}

/* Layout principal con calendario y panel */
.ppoc-main-layout {
    display: flex;
    gap: 1.5rem;
    align-items: flex-start;
}

.calendar-section {
    flex: 1;
    min-width: 0;
}

/* ========================================
   PANEL LATERAL DE ESTADISTICAS
   ======================================== */
.stats-panel {
    width: 280px;
    flex-shrink: 0;
    background: #171717;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
    overflow: hidden;
    position: sticky;
    top: 1rem;
}

.panel-header {
    background: linear-gradient(135deg, #4a6da7 0%, #2d4266 100%);
    padding: 1rem 1.25rem;
}

.panel-header h3 {
    margin: 0;
    color: white;
    font-size: 1.1rem;
    font-weight: 600;
}

.panel-section {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #262626;
}

.panel-section:last-child {
    border-bottom: none;
}

.section-title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 700;
    color: #f5f5f5;
    margin-bottom: 0.75rem;
    font-size: 0.95rem;
}

.section-icon {
    font-size: 1.1rem;
}

.section-stats {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.stat-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.85rem;
}

.stat-key {
    color: #a3a3a3;
}

.stat-val {
    font-weight: 600;
    color: #e5e5e5;
}

.stat-row.highlight {
    background: #262626;
    padding: 0.5rem 0.75rem;
    border-radius: 6px;
    margin: 0.25rem -0.75rem;
}

.stat-row.highlight .stat-val {
    color: #4a6da7;
    font-size: 1.1rem;
}

.stat-row.extremo {
    padding: 0.35rem 0;
}

.stat-row.extremo.min .stat-key {
    color: #4a6da7;
}

.stat-row.extremo.max .stat-key {
    color: #4a6da7;
}

.stat-row.extremo .stat-val {
    font-size: 0.8rem;
    max-width: 140px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Seccion de equilibrio */
.balance-section {
    background: #0a0a0a;
}

.balance-indicator {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem;
    border-radius: 8px;
    margin-bottom: 0.5rem;
}

.balance-indicator.bueno {
    background: rgba(16, 185, 129, 0.1);
    border: 1px solid rgba(16, 185, 129, 0.3);
}

.balance-indicator.revisar {
    background: rgba(245, 158, 11, 0.1);
    border: 1px solid rgba(245, 158, 11, 0.3);
}

.balance-icon {
    font-size: 1.25rem;
}

.balance-text {
    font-weight: 600;
    font-size: 0.85rem;
    color: #e5e5e5;
}

.ratio-info {
    text-align: center;
    font-size: 0.75rem;
    color: #a3a3a3;
}

/* Header del calendario */
.calendar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding: 1rem;
    background: #171717;
    border-radius: 12px;
    border: 1px solid #262626;
    box-shadow: 0 2px 10px rgba(0,0,0,0.3);
}

.nav-month-btn {
    padding: 0.75rem 1.25rem;
    background: #262626;
    color: #e5e5e5;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.2s;
}

.nav-month-btn:hover {
    background: #4a6da7;
    color: white;
}

.month-title {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.month-title h1 {
    margin: 0;
    font-size: 1.75rem;
    color: #f5f5f5;
}

.btn-today {
    padding: 0.5rem 1rem;
    background: #4a6da7;
    color: white;
    text-decoration: none;
    border-radius: 6px;
    font-size: 0.875rem;
    font-weight: 600;
}

/* Seccion generar */
.generate-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.action-buttons {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.btn-generate {
    padding: 0.875rem 1.5rem;
    background: linear-gradient(135deg, #4a6da7 0%, #3d5a8a 100%);
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
}

.btn-generate:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
}

.btn-auto-assign {
    padding: 0.875rem 1.5rem;
    background: linear-gradient(135deg, #4a6da7 0%, #3d5a8a 100%);
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    box-shadow: 0 4px 15px rgba(139, 92, 246, 0.3);
}

.btn-auto-assign:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(139, 92, 246, 0.4);
}

.btn-danger {
    padding: 0.75rem 1.25rem;
    background: linear-gradient(135deg, #dc2626, #b91c1c);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 0.9rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-danger:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(220, 38, 38, 0.4);
}

.btn-export {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.25rem;
    background: linear-gradient(135deg, #4a6fa5, #3d5a80);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 0.9rem;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.3s ease;
}

.btn-export:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(74, 111, 165, 0.4);
    color: white;
}

.quick-links {
    display: flex;
    gap: 0.75rem;
}

.quick-link {
    padding: 0.75rem 1rem;
    background: #171717;
    color: #e5e5e5;
    text-decoration: none;
    border-radius: 8px;
    font-size: 0.875rem;
    border: 1px solid #262626;
    transition: all 0.2s;
}

.quick-link:hover {
    border-color: #4a6da7;
    color: #4a6da7;
}

/* Estadisticas */
.stats-section {
    background: #171717;
    border-radius: 12px;
    padding: 1rem;
    margin-bottom: 1.5rem;
    border: 1px solid #262626;
}

.stats-cards {
    display: flex;
    gap: 1rem;
    margin-bottom: 1rem;
    flex-wrap: wrap;
}

.stat-card {
    flex: 1;
    min-width: 120px;
    background: #262626;
    border-radius: 8px;
    padding: 1rem;
    text-align: center;
}

.stat-value {
    display: block;
    font-size: 1.5rem;
    font-weight: 700;
    color: #4a6da7;
}

.stat-label {
    display: block;
    font-size: 0.75rem;
    color: #a3a3a3;
    margin-top: 0.25rem;
}

.alertas-box {
    margin-bottom: 1rem;
}

.alerta {
    padding: 0.75rem 1rem;
    border-radius: 8px;
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
}

.alerta-danger {
    background: #e9ecef;
    color: #343a40;
    border: 1px solid #fecaca;
}

.alerta-warning {
    background: #fef3c7;
    color: #3d5a8a;
    border: 1px solid #fde68a;
}

.publicadores-stats {
    margin-top: 1rem;
}

.publicadores-stats summary {
    cursor: pointer;
    padding: 0.75rem;
    background: #262626;
    border-radius: 8px;
    font-weight: 600;
    color: #e5e5e5;
}

.pub-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 0.5rem;
    margin-top: 0.75rem;
    max-height: 300px;
    overflow-y: auto;
    padding: 0.5rem;
}

.pub-stat-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.5rem 0.75rem;
    background: #262626;
    color: #e5e5e5;
    border-radius: 6px;
    font-size: 0.8rem;
    border-left: 3px solid #4a6da7;
}

.pub-stat-item.estado-warning {
    border-left-color: #4a6da7;
    background: #fef3c7;
}

.pub-stat-item.estado-danger {
    border-left-color: #343a40;
    background: #e9ecef;
}

.pub-name {
    font-weight: 500;
    flex: 1;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.pub-turnos {
    font-weight: 600;
    margin-left: 0.5rem;
}

.pub-badge {
    width: 18px;
    height: 18px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.65rem;
    font-weight: 700;
    margin-left: 0.5rem;
}

.pub-badge.precursor {
    background: #4a6da7;
    color: white;
}

/* Calendario */
.calendar-container {
    background: #171717;
    border-radius: 16px;
    overflow-x: auto;
    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
    border: 1px solid #262626;
}

.calendar-weekdays {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    min-width: 700px;
    background: linear-gradient(135deg, #4a6da7 0%, #2d4266 100%);
}

.weekday {
    padding: 1rem;
    text-align: center;
    font-weight: 600;
    color: white;
    font-size: 0.875rem;
}

.weekday.weekend {
    background: rgba(0,0,0,0.1);
}

.calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    min-width: 700px;
}

.calendar-day {
    min-height: 140px;
    border: 1px solid #262626;
    border-top: none;
    padding: 0.5rem;
    background: #171717;
}

.calendar-day.empty {
    background: #0a0a0a;
}

.calendar-day.today {
    background: rgba(249, 115, 22, 0.1);
}

.calendar-day.other-month {
    opacity: 0.5;
}

.day-header {
    margin-bottom: 0.5rem;
}

.day-number {
    font-weight: 600;
    color: #e5e5e5;
}

.day-number.today-badge {
    display: inline-block;
    width: 28px;
    height: 28px;
    line-height: 28px;
    text-align: center;
    background: #4a6da7;
    color: white;
    border-radius: 50%;
}

/* Turnos */
.turno-item {
    background: #262626;
    border-radius: 8px;
    padding: 0.5rem;
    margin-bottom: 0.5rem;
    font-size: 0.75rem;
    border-left: 3px solid #4a6da7;
}

.turno-item.completo {
    border-left-color: #4a6da7;
    background: rgba(16, 185, 129, 0.1);
}

.turno-item.incompleto {
    border-left-color: #4a6da7;
}

.turno-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.turno-hora {
    font-weight: 700;
    color: #f5f5f5;
}

.btn-delete-turno {
    width: 18px;
    height: 18px;
    border: none;
    background: #e9ecef;
    color: #343a40;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    line-height: 1;
}

.btn-delete-turno:hover {
    background: #343a40;
    color: white;
}

.turno-ubicacion {
    color: #a3a3a3;
    font-size: 0.7rem;
    margin-top: 0.25rem;
}

.turno-asignados {
    margin-top: 0.5rem;
}

.asignado {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.25rem 0.5rem;
    background: #404040;
    color: #e5e5e5;
    border-radius: 4px;
    margin-bottom: 0.25rem;
}

.asignado.capitan {
    background: linear-gradient(135deg, #4a6da7 0%, #2d4266 100%);
    color: white;
}

.asignado-nombre {
    font-weight: 500;
}

.asignado-btns {
    display: flex;
    gap: 4px;
    align-items: center;
}

.btn-cambiar-asig {
    width: 18px;
    height: 16px;
    border: none;
    background: rgba(59, 130, 246, 0.2);
    color: #4a6da7;
    border-radius: 3px;
    cursor: pointer;
    font-size: 10px;
    line-height: 1;
    transition: all 0.2s;
}

.btn-cambiar-asig:hover {
    background: #4a6da7;
    color: white;
}

.asignado.capitan .btn-cambiar-asig {
    background: rgba(255,255,255,0.2);
    color: white;
}

.asignado.capitan .btn-cambiar-asig:hover {
    background: rgba(255,255,255,0.4);
}

.btn-remove-asig {
    width: 16px;
    height: 16px;
    border: none;
    background: rgba(0,0,0,0.1);
    color: inherit;
    border-radius: 3px;
    cursor: pointer;
    font-size: 12px;
    line-height: 1;
}

.btn-add-pub {
    width: 100%;
    padding: 0.35rem;
    background: transparent;
    border: 1px dashed #4a6da7;
    color: #4a6da7;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.7rem;
    font-weight: 600;
    transition: all 0.2s;
}

.btn-add-pub:hover {
    background: #4a6da7;
    color: white;
    border-style: solid;
}

/* Modal */
.modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal-content {
    background: #171717;
    border: 1px solid #262626;
    border-radius: 16px;
    width: 90%;
    max-width: 400px;
    overflow: hidden;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.25rem 1.5rem;
    background: linear-gradient(135deg, #4a6da7 0%, #2d4266 100%);
    color: white;
}

.modal-header h2 {
    margin: 0;
    font-size: 1.25rem;
}

.modal-close {
    background: none;
    border: none;
    color: white;
    font-size: 1.5rem;
    cursor: pointer;
}

.modal-body {
    padding: 1.5rem;
}

.form-group {
    margin-bottom: 1rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #e5e5e5;
}

.form-select {
    width: 100%;
    padding: 0.75rem;
    border: 2px solid #404040;
    border-radius: 8px;
    font-size: 1rem;
    background: #262626;
    color: #e5e5e5;
}

.form-select:focus {
    outline: none;
    border-color: #4a6da7;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    padding: 1rem 1.5rem;
    background: #0a0a0a;
}

.btn-cancel {
    padding: 0.75rem 1.25rem;
    background: #262626;
    color: #e5e5e5;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
}

.btn-submit {
    padding: 0.75rem 1.25rem;
    background: linear-gradient(135deg, #4a6da7 0%, #2d4266 100%);
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
}

/* Modal sugerencias */
.modal-sugerencias {
    max-width: 500px;
}

.sugerencia-info {
    background: #262626;
    padding: 0.75rem 1rem;
    border-radius: 8px;
    margin-bottom: 1rem;
}

.sugerencia-info p {
    margin: 0.25rem 0;
    font-size: 0.9rem;
}

.loading-spinner {
    text-align: center;
    padding: 2rem;
    color: #a3a3a3;
}

.sugerencias-lista {
    max-height: 300px;
    overflow-y: auto;
}

.sugerencia-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 1rem;
    border: 1px solid #404040;
    border-radius: 8px;
    margin-bottom: 0.5rem;
    cursor: pointer;
    transition: all 0.2s;
}

.sugerencia-item:hover {
    background: #262626;
    border-color: #4a6da7;
}

.sug-nombre {
    font-weight: 600;
    color: #e5e5e5;
}

.sug-meta {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.sug-turnos {
    font-size: 0.75rem;
    color: #a3a3a3;
}

.sug-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    font-size: 0.65rem;
    font-weight: 700;
}

.sug-badge.capitan {
    background: #4a6da7;
    color: white;
}

.sug-badge.precursor {
    background: #4a6da7;
    color: white;
}

.sugerencias-vacio {
    text-align: center;
    padding: 2rem;
    color: #a3a3a3;
}

/* Responsive */
@media (max-width: 1200px) {
    .ppoc-main-layout {
        flex-direction: column;
    }

    .stats-panel {
        width: 100%;
        position: static;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    }

    .panel-header {
        grid-column: 1 / -1;
    }

    .panel-section {
        border-bottom: none;
        border-right: 1px solid #262626;
    }

    .panel-section:last-child {
        border-right: none;
    }

    .balance-section {
        grid-column: 1 / -1;
    }
}

@media (max-width: 768px) {
    .calendar-header {
        flex-direction: column;
        gap: 1rem;
    }

    .generate-section {
        flex-direction: column;
        align-items: stretch;
    }

    .action-buttons {
        flex-direction: column;
    }

    .quick-links {
        flex-direction: column;
    }

    .calendar-day {
        min-height: 100px;
        padding: 0.25rem;
    }

    .weekday {
        padding: 0.5rem;
        font-size: 0.7rem;
    }

    .turno-item {
        font-size: 0.65rem;
        padding: 0.35rem;
    }

    .stats-cards {
        flex-direction: column;
    }

    .stats-panel {
        grid-template-columns: 1fr;
    }

    .panel-section {
        border-right: none;
        border-bottom: 1px solid #262626;
    }
}

/* Alertas dark */
.alerta-danger {
    background: rgba(220, 38, 38, 0.2);
    border-color: rgba(220, 38, 38, 0.4);
    color: #ced4da;
}

.alerta-warning {
    background: rgba(245, 158, 11, 0.2);
    border-color: rgba(245, 158, 11, 0.4);
    color: #fcd34d;
}

.pub-stat-item.estado-warning {
    background: rgba(245, 158, 11, 0.2);
}

.pub-stat-item.estado-danger {
    background: rgba(220, 38, 38, 0.2);
}

.turno-item.completo {
    background: rgba(34, 197, 94, 0.15);
}

.stat-row.extremo.min .stat-key {
    color: #fbbf24;
}

.stat-row.extremo.max .stat-key {
    color: #34d399;
}
</style>

@endsection
