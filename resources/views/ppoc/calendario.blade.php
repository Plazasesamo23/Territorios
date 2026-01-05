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
                                                        <form action="{{ route('ppoc.asignaciones.destroy', $asig) }}" method="POST" class="remove-asig">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn-remove-asig" title="Quitar">×</button>
                                                        </form>
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
</script>

<style>
.ppoc-page {
    max-width: 1400px;
    margin: 0 auto;
}

/* Header del calendario */
.calendar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding: 1rem;
    background: var(--bg-card, #fff);
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
}

.nav-month-btn {
    padding: 0.75rem 1.25rem;
    background: var(--bg-secondary, #f3f4f6);
    color: var(--text-primary, #374151);
    text-decoration: none;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.2s;
}

.nav-month-btn:hover {
    background: #3b82f6;
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
    color: var(--text-primary, #1f2937);
}

.btn-today {
    padding: 0.5rem 1rem;
    background: #3b82f6;
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
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
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
    background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
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

.quick-links {
    display: flex;
    gap: 0.75rem;
}

.quick-link {
    padding: 0.75rem 1rem;
    background: var(--bg-card, #fff);
    color: var(--text-primary, #374151);
    text-decoration: none;
    border-radius: 8px;
    font-size: 0.875rem;
    border: 1px solid var(--border-color, #e5e7eb);
    transition: all 0.2s;
}

.quick-link:hover {
    border-color: #3b82f6;
    color: #3b82f6;
}

/* Estadisticas */
.stats-section {
    background: var(--bg-card, #fff);
    border-radius: 12px;
    padding: 1rem;
    margin-bottom: 1.5rem;
    border: 1px solid var(--border-color, #e5e7eb);
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
    background: var(--bg-secondary, #f9fafb);
    border-radius: 8px;
    padding: 1rem;
    text-align: center;
}

.stat-value {
    display: block;
    font-size: 1.5rem;
    font-weight: 700;
    color: #3b82f6;
}

.stat-label {
    display: block;
    font-size: 0.75rem;
    color: var(--text-muted, #6b7280);
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
    background: #fee2e2;
    color: #dc2626;
    border: 1px solid #fecaca;
}

.alerta-warning {
    background: #fef3c7;
    color: #d97706;
    border: 1px solid #fde68a;
}

.publicadores-stats {
    margin-top: 1rem;
}

.publicadores-stats summary {
    cursor: pointer;
    padding: 0.75rem;
    background: var(--bg-secondary, #f9fafb);
    border-radius: 8px;
    font-weight: 600;
    color: var(--text-primary, #374151);
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
    background: var(--bg-secondary, #f9fafb);
    border-radius: 6px;
    font-size: 0.8rem;
    border-left: 3px solid #10b981;
}

.pub-stat-item.estado-warning {
    border-left-color: #f59e0b;
    background: #fef3c7;
}

.pub-stat-item.estado-danger {
    border-left-color: #dc2626;
    background: #fee2e2;
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
    background: #3b82f6;
    color: white;
}

/* Calendario */
.calendar-container {
    background: var(--bg-card, #fff);
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

.calendar-weekdays {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
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
}

.calendar-day {
    min-height: 140px;
    border: 1px solid var(--border-color, #e5e7eb);
    border-top: none;
    padding: 0.5rem;
    background: var(--bg-card, #fff);
}

.calendar-day.empty {
    background: var(--bg-secondary, #f9fafb);
}

.calendar-day.today {
    background: rgba(59, 130, 246, 0.05);
}

.calendar-day.other-month {
    opacity: 0.5;
}

.day-header {
    margin-bottom: 0.5rem;
}

.day-number {
    font-weight: 600;
    color: var(--text-primary, #1f2937);
}

.day-number.today-badge {
    display: inline-block;
    width: 28px;
    height: 28px;
    line-height: 28px;
    text-align: center;
    background: #3b82f6;
    color: white;
    border-radius: 50%;
}

/* Turnos */
.turno-item {
    background: var(--bg-secondary, #f3f4f6);
    border-radius: 8px;
    padding: 0.5rem;
    margin-bottom: 0.5rem;
    font-size: 0.75rem;
    border-left: 3px solid #f59e0b;
}

.turno-item.completo {
    border-left-color: #10b981;
    background: rgba(16, 185, 129, 0.1);
}

.turno-item.incompleto {
    border-left-color: #f59e0b;
}

.turno-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.turno-hora {
    font-weight: 700;
    color: var(--text-primary, #1f2937);
}

.btn-delete-turno {
    width: 18px;
    height: 18px;
    border: none;
    background: #fee2e2;
    color: #dc2626;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    line-height: 1;
}

.btn-delete-turno:hover {
    background: #dc2626;
    color: white;
}

.turno-ubicacion {
    color: var(--text-muted, #6b7280);
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
    background: white;
    border-radius: 4px;
    margin-bottom: 0.25rem;
}

.asignado.capitan {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: white;
}

.asignado-nombre {
    font-weight: 500;
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
    border: 1px dashed #3b82f6;
    color: #3b82f6;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.7rem;
    font-weight: 600;
    transition: all 0.2s;
}

.btn-add-pub:hover {
    background: #3b82f6;
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
    background: var(--bg-card, #fff);
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
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
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
    color: var(--text-primary, #374151);
}

.form-select {
    width: 100%;
    padding: 0.75rem;
    border: 2px solid var(--border-color, #e5e7eb);
    border-radius: 8px;
    font-size: 1rem;
    background: var(--bg-card, #fff);
    color: var(--text-primary, #374151);
}

.form-select:focus {
    outline: none;
    border-color: #3b82f6;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    padding: 1rem 1.5rem;
    background: var(--bg-secondary, #f9fafb);
}

.btn-cancel {
    padding: 0.75rem 1.25rem;
    background: var(--bg-secondary, #e5e7eb);
    color: var(--text-primary, #374151);
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
}

.btn-submit {
    padding: 0.75rem 1.25rem;
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
}

/* Responsive */
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
}

/* ========================================
   DARK THEME
   ======================================== */
[data-theme="dark"] .calendar-header {
    background: #171717;
    border: 1px solid #262626;
}

[data-theme="dark"] .nav-month-btn {
    background: #262626;
    color: #e5e5e5;
}

[data-theme="dark"] .nav-month-btn:hover {
    background: #f97316;
    color: #0a0a0a;
}

[data-theme="dark"] .month-title h1 {
    color: #f5f5f5;
}

[data-theme="dark"] .btn-today {
    background: #f97316;
    color: #0a0a0a;
}

[data-theme="dark"] .btn-generate {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

[data-theme="dark"] .btn-auto-assign {
    background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
    color: #0a0a0a;
}

[data-theme="dark"] .quick-link {
    background: #171717;
    border-color: #262626;
    color: #e5e5e5;
}

[data-theme="dark"] .quick-link:hover {
    border-color: #f97316;
    color: #f97316;
}

[data-theme="dark"] .stats-section {
    background: #171717;
    border-color: #262626;
}

[data-theme="dark"] .stat-card {
    background: #262626;
}

[data-theme="dark"] .stat-value {
    color: #f97316;
}

[data-theme="dark"] .stat-label {
    color: #a3a3a3;
}

[data-theme="dark"] .publicadores-stats summary {
    background: #262626;
    color: #e5e5e5;
}

[data-theme="dark"] .pub-stat-item {
    background: #262626;
    color: #e5e5e5;
}

[data-theme="dark"] .pub-stat-item.estado-warning {
    background: rgba(245, 158, 11, 0.2);
}

[data-theme="dark"] .pub-stat-item.estado-danger {
    background: rgba(220, 38, 38, 0.2);
}

[data-theme="dark"] .calendar-container {
    background: #171717;
    border: 1px solid #262626;
}

[data-theme="dark"] .calendar-weekdays {
    background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
}

[data-theme="dark"] .weekday {
    color: #0a0a0a;
}

[data-theme="dark"] .calendar-day {
    background: #171717;
    border-color: #262626;
}

[data-theme="dark"] .calendar-day.empty {
    background: #0a0a0a;
}

[data-theme="dark"] .calendar-day.today {
    background: rgba(249, 115, 22, 0.1);
}

[data-theme="dark"] .day-number {
    color: #e5e5e5;
}

[data-theme="dark"] .day-number.today-badge {
    background: #f97316;
    color: #0a0a0a;
}

[data-theme="dark"] .turno-item {
    background: #262626;
}

[data-theme="dark"] .turno-item.completo {
    background: rgba(34, 197, 94, 0.15);
}

[data-theme="dark"] .turno-hora {
    color: #f5f5f5;
}

[data-theme="dark"] .turno-ubicacion {
    color: #a3a3a3;
}

[data-theme="dark"] .asignado {
    background: #404040;
    color: #e5e5e5;
}

[data-theme="dark"] .asignado.capitan {
    background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
    color: #0a0a0a;
}

[data-theme="dark"] .btn-add-pub {
    border-color: #f97316;
    color: #f97316;
}

[data-theme="dark"] .btn-add-pub:hover {
    background: #f97316;
    color: #0a0a0a;
}

[data-theme="dark"] .modal-content {
    background: #171717;
    border: 1px solid #262626;
}

[data-theme="dark"] .modal-header {
    background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
    color: #0a0a0a;
}

[data-theme="dark"] .form-group label {
    color: #e5e5e5;
}

[data-theme="dark"] .form-select {
    background: #262626;
    border-color: #404040;
    color: #e5e5e5;
}

[data-theme="dark"] .form-select:focus {
    border-color: #f97316;
}

[data-theme="dark"] .modal-footer {
    background: #0a0a0a;
}

[data-theme="dark"] .btn-cancel {
    background: #262626;
    color: #e5e5e5;
}

[data-theme="dark"] .btn-submit {
    background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
    color: #0a0a0a;
}
</style>

@endsection
