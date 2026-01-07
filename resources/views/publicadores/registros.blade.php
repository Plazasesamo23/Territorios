@extends('layouts.app')

@section('title', 'Registros de ' . $publicador->nombre_completo)

@section('content')
<!-- Navegacion -->
<nav class="page-nav">
    <div class="page-breadcrumbs">
        <a href="{{ route('dashboard') }}" class="breadcrumb-link">Dashboard</a>
        <span class="breadcrumb-separator">></span>
        <a href="{{ route('publicadores.index') }}" class="breadcrumb-link">Publicadores</a>
        <span class="breadcrumb-separator">></span>
        <span class="breadcrumb-current">{{ $publicador->nombre_completo }}</span>
    </div>
    <div class="page-actions">
        <a href="{{ route('publicadores.show', $publicador) }}" class="btn btn-secondary">
            Ver Publicador
        </a>
        <a href="{{ route('publicadores.index') }}" class="btn btn-outline">
            Volver
        </a>
    </div>
</nav>

<!-- Cabecera del publicador -->
<div class="publicador-header">
    <div class="publicador-avatar">
        {{ strtoupper(substr($publicador->nombre, 0, 1)) }}{{ strtoupper(substr($publicador->apellidos, 0, 1)) }}
    </div>
    <div class="publicador-info">
        <h1 class="publicador-nombre {{ $publicador->es_precursor ? 'text-precursor' : '' }}">
            {{ $publicador->nombre_completo }}
            @if($publicador->es_precursor)
            <span class="precursor-badge">PR</span>
            @endif
        </h1>
        <div class="publicador-meta">
            <span class="meta-item">Tel: {{ $publicador->telefono }}</span>
            @if($publicador->activo)
                <span class="badge badge-green">Activo</span>
            @else
                <span class="badge badge-gray">Inactivo</span>
            @endif
        </div>
    </div>
</div>

<!-- Sistema de Filtros -->
<div class="card mb-4 filtros-card">
    <div class="card-title">Filtrar Estadisticas</div>
    <form method="GET" action="{{ route('publicadores.registros', $publicador) }}" id="filtroForm">
        <div class="filtros-container">
            <!-- Tipo de filtro -->
            <div class="filtro-tipo">
                <label class="filtro-radio {{ $filtroActivo === 'todos' ? 'active' : '' }}">
                    <input type="radio" name="filtro" value="todos" {{ $filtroActivo === 'todos' ? 'checked' : '' }} onchange="toggleFiltros()">
                    <span class="radio-label">Todo el Historico</span>
                </label>
                <label class="filtro-radio {{ $filtroActivo === 'ano_servicio' ? 'active' : '' }}">
                    <input type="radio" name="filtro" value="ano_servicio" {{ $filtroActivo === 'ano_servicio' ? 'checked' : '' }} onchange="toggleFiltros()">
                    <span class="radio-label">Ano de Servicio</span>
                </label>
                <label class="filtro-radio {{ $filtroActivo === 'fechas' ? 'active' : '' }}">
                    <input type="radio" name="filtro" value="fechas" {{ $filtroActivo === 'fechas' ? 'checked' : '' }} onchange="toggleFiltros()">
                    <span class="radio-label">Fechas Personalizadas</span>
                </label>
            </div>

            <!-- Selector de ano de servicio -->
            <div class="filtro-opciones" id="filtroAnoServicio" style="{{ $filtroActivo === 'ano_servicio' ? '' : 'display:none;' }}">
                <div class="filtro-grupo">
                    <label>Selecciona el ano de servicio:</label>
                    <select name="ano_servicio" class="form-select" onchange="document.getElementById('filtroForm').submit()">
                        @foreach($anosServicio as $ano)
                            <option value="{{ $ano }}" {{ $anoServicioSeleccionado === $ano ? 'selected' : '' }}>
                                {{ $ano }} (Sep {{ explode('-', $ano)[0] }} - Ago {{ explode('-', $ano)[1] }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Selector de fechas personalizadas -->
            <div class="filtro-opciones" id="filtroFechas" style="{{ $filtroActivo === 'fechas' ? '' : 'display:none;' }}">
                <div class="filtro-fechas-grid">
                    <div class="filtro-grupo">
                        <label>Desde:</label>
                        <input type="date" name="fecha_inicio" class="form-input" value="{{ $fechaInicio }}">
                    </div>
                    <div class="filtro-grupo">
                        <label>Hasta:</label>
                        <input type="date" name="fecha_fin" class="form-input" value="{{ $fechaFin }}">
                    </div>
                    <button type="submit" class="btn btn-primary">Aplicar</button>
                </div>
            </div>
        </div>
    </form>

    @if($estadisticas['tiene_filtro'] ?? false)
    <div class="filtro-activo-info">
        <span class="filtro-badge">Filtro activo:</span>
        <span class="filtro-periodo">{{ $estadisticas['periodo'] }}</span>
        <a href="{{ route('publicadores.registros', $publicador) }}" class="filtro-limpiar">Limpiar filtro</a>
    </div>
    @endif
</div>

<!-- Panel de Estadisticas Visual -->
<div class="stats-dashboard {{ ($estadisticas['tiene_filtro'] ?? false) ? 'stats-filtrado' : '' }}">
    <div class="stats-header">
        <h2>{{ ($estadisticas['tiene_filtro'] ?? false) ? 'Estadisticas del Periodo' : 'Resumen de Actividad' }}</h2>
        <p>{{ $estadisticas['periodo'] ?? 'Historico completo' }}</p>
    </div>

    <div class="stats-grid">
        <!-- Territorios Completados -->
        <div class="stat-box stat-completed">
            <div class="stat-icon">&#128203;</div>
            <div class="stat-content">
                <div class="stat-value">{{ $estadisticas['territorios_completados'] }}</div>
                <div class="stat-label">Territorios Completados</div>
            </div>
            <div class="stat-footer">
                @if($estadisticas['territorios_completados'] > 0)
                    <span class="trend-up">Buen trabajo!</span>
                @else
                    <span class="trend-neutral">Sin completar en este periodo</span>
                @endif
            </div>
        </div>

        <!-- Promedio de Dias -->
        <div class="stat-box stat-average">
            <div class="stat-icon">&#128337;</div>
            <div class="stat-content">
                <div class="stat-value">{{ $estadisticas['promedio_dias'] }}</div>
                <div class="stat-label">Dias Promedio</div>
            </div>
            <div class="stat-footer">
                @if($estadisticas['promedio_dias'] > 0 && $estadisticas['promedio_dias'] <= 60)
                    <span class="trend-up">Excelente ritmo</span>
                @elseif($estadisticas['promedio_dias'] > 60 && $estadisticas['promedio_dias'] <= 90)
                    <span class="trend-neutral">Ritmo normal</span>
                @elseif($estadisticas['promedio_dias'] > 90)
                    <span class="trend-down">Puede mejorar</span>
                @else
                    <span class="trend-neutral">-</span>
                @endif
            </div>
        </div>

        <!-- Total Dias Trabajados -->
        <div class="stat-box stat-total">
            <div class="stat-icon">&#128197;</div>
            <div class="stat-content">
                <div class="stat-value">{{ $estadisticas['total_dias_trabajados'] }}</div>
                <div class="stat-label">Dias de Servicio</div>
            </div>
            <div class="stat-footer">
                @if($estadisticas['total_dias_trabajados'] >= 180)
                    <span class="trend-up">+6 meses</span>
                @elseif($estadisticas['total_dias_trabajados'] > 0)
                    <span class="trend-neutral">Acumulando</span>
                @else
                    <span class="trend-neutral">-</span>
                @endif
            </div>
        </div>

        <!-- Total Asignaciones -->
        <div class="stat-box stat-records">
            <div class="stat-icon">&#128209;</div>
            <div class="stat-content">
                <div class="stat-value">{{ $estadisticas['total_territorios'] }}</div>
                <div class="stat-label">Asignaciones</div>
            </div>
            <div class="stat-footer">
                <span class="trend-neutral">En el periodo</span>
            </div>
        </div>
    </div>

    @if($estadisticas['tiene_filtro'] ?? false)
    <!-- Comparativa con el total -->
    <div class="stats-comparativa">
        <div class="comparativa-titulo">Comparativa con el historico total</div>
        <div class="comparativa-grid">
            <div class="comparativa-item">
                <span class="comparativa-label">Total territorios:</span>
                <span class="comparativa-valor">{{ $estadisticasGlobales['territorios_completados'] }}</span>
            </div>
            <div class="comparativa-item">
                <span class="comparativa-label">Promedio global:</span>
                <span class="comparativa-valor">{{ $estadisticasGlobales['promedio_dias'] }} dias</span>
            </div>
            <div class="comparativa-item">
                <span class="comparativa-label">Total dias:</span>
                <span class="comparativa-valor">{{ $estadisticasGlobales['total_dias_trabajados'] }}</span>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Territorio Actual -->
<div class="card mb-4">
    <div class="card-title">Territorio Actual</div>
    @if($estadisticas['territorio_actual'])
        @php
            $registro = $estadisticas['territorio_actual'];
            $estado = $registro->territorio->calcularEstado();
            $dias = $registro->fecha_salida->diffInDays(now());
            $porcentaje = min(100, ($dias / 120) * 100);
        @endphp
        <div class="territorio-actual-card">
            <div class="territorio-actual-info">
                <div class="territorio-numero-grande">#{{ $registro->territorio->numero }}</div>
                @if($registro->territorio->nombre)
                    <div class="territorio-nombre-actual">{{ $registro->territorio->nombre }}</div>
                @endif

                <div class="progreso-container">
                    <div class="progreso-label">
                        <span>Tiempo transcurrido</span>
                        <span class="progreso-dias">{{ $dias }} dias</span>
                    </div>
                    <div class="progreso-barra">
                        <div class="progreso-fill {{ $estado === 'atrasado' ? 'progreso-danger' : ($dias > 60 ? 'progreso-warning' : 'progreso-success') }}" style="width: {{ $porcentaje }}%"></div>
                    </div>
                    <div class="progreso-escala">
                        <span>0</span>
                        <span>60 dias</span>
                        <span>120 dias</span>
                    </div>
                </div>

                <div class="territorio-actual-meta">
                    <div class="meta-row">
                        <span class="meta-label">Asignado:</span>
                        <span class="meta-value">{{ $registro->fecha_salida->format('d/m/Y') }}</span>
                    </div>
                    @if($registro->entrada_prevista)
                    <div class="meta-row">
                        <span class="meta-label">Devolucion prevista:</span>
                        <span class="meta-value">{{ $registro->entrada_prevista->format('d/m/Y') }}</span>
                    </div>
                    @endif
                    <div class="meta-row">
                        <span class="meta-label">Estado:</span>
                        @if($estado === 'activo')
                            <span class="badge badge-blue">Activo</span>
                        @elseif($estado === 'atrasado')
                            <span class="badge badge-red">Atrasado</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="territorio-actual-acciones">
                <a href="{{ route('territorios.show', $registro->territorio) }}" class="btn btn-primary">
                    Ver Territorio
                </a>
                @php
                    $congregacion = \App\Models\Congregacion::find(session('congregacion_activa_id'));
                    $mensajeWhatsapp = $congregacion ? $congregacion->getMensajeWhatsappFormateado($publicador, $registro->territorio) : "Hola " . $publicador->nombre;
                @endphp
                <a href="https://wa.me/{{ str_replace(['+', ' ', '-'], '', $publicador->telefono) }}?text={{ urlencode($mensajeWhatsapp) }}"
                   target="_blank" class="btn btn-success">
                    WhatsApp
                </a>
                <form action="{{ route('registros.entrada', $registro) }}" method="POST" class="inline"
                      onsubmit="return confirm('Confirmas que el territorio #{{ $registro->territorio->numero }} ha sido devuelto?')">
                    @csrf
                    <button type="submit" class="btn btn-secondary">
                        Marcar Devuelto
                    </button>
                </form>
            </div>
        </div>
    @else
        <div class="territorio-sin-asignar">
            <div class="sin-asignar-icon">&#128077;</div>
            <div class="sin-asignar-texto">
                <strong>Disponible para nueva asignacion</strong>
                <p>Este publicador no tiene territorio asignado actualmente</p>
            </div>
            @if($publicador->activo)
                <a href="{{ route('registros.create') }}?publicador={{ $publicador->id }}" class="btn btn-primary">
                    Asignar Territorio
                </a>
            @endif
        </div>
    @endif
</div>

<!-- Historial de Territorios (Filtrado) -->
@php
    $registrosCompletados = $registros->where('fecha_entrada', '!=', null);
@endphp

@if($registrosCompletados->count() > 0)
<div class="card">
    <div class="card-title">
        Territorios del Periodo
        <span class="badge badge-blue">{{ $registrosCompletados->count() }}</span>
    </div>
    <div class="card-description">
        {{ $estadisticas['periodo'] ?? 'Historico completo' }}
    </div>

    <div class="historial-lista">
        @foreach($registrosCompletados as $registro)
            @php
                $diasTrabajados = $registro->fecha_salida->diffInDays($registro->fecha_entrada);
            @endphp
            <div class="historial-item">
                <div class="historial-territorio">
                    <span class="historial-numero">#{{ $registro->territorio->numero }}</span>
                    @if($registro->territorio->nombre)
                        <span class="historial-nombre">{{ $registro->territorio->nombre }}</span>
                    @endif
                </div>
                <div class="historial-fechas">
                    <span class="historial-fecha">{{ $registro->fecha_salida->format('d/m/Y') }}</span>
                    <span class="historial-flecha">-></span>
                    <span class="historial-fecha">{{ $registro->fecha_entrada->format('d/m/Y') }}</span>
                </div>
                <div class="historial-duracion">
                    <span class="duracion-numero">{{ $diasTrabajados }}</span>
                    <span class="duracion-label">dias</span>
                </div>
                <div class="historial-estado">
                    <span class="badge badge-green">Completado</span>
                </div>
                <a href="{{ route('territorios.show', $registro->territorio) }}" class="historial-ver">
                    Ver
                </a>
            </div>
        @endforeach
    </div>
</div>
@else
<div class="card">
    <div class="card-title">Territorios del Periodo</div>
    <div class="empty-state">
        <div class="empty-icon">&#128269;</div>
        <div class="empty-text">
            <strong>Sin territorios completados</strong>
            <p>No hay territorios completados en el periodo seleccionado</p>
        </div>
    </div>
</div>
@endif

<script>
function toggleFiltros() {
    const filtroSeleccionado = document.querySelector('input[name="filtro"]:checked').value;
    const filtroAno = document.getElementById('filtroAnoServicio');
    const filtroFechas = document.getElementById('filtroFechas');

    // Actualizar clases activas
    document.querySelectorAll('.filtro-radio').forEach(el => el.classList.remove('active'));
    document.querySelector('input[name="filtro"]:checked').closest('.filtro-radio').classList.add('active');

    // Mostrar/ocultar opciones
    filtroAno.style.display = filtroSeleccionado === 'ano_servicio' ? '' : 'none';
    filtroFechas.style.display = filtroSeleccionado === 'fechas' ? '' : 'none';

    // Si es "todos", enviar el formulario
    if (filtroSeleccionado === 'todos') {
        document.getElementById('filtroForm').submit();
    }
}
</script>

<style>
/* Header del publicador */
.publicador-header {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    margin-bottom: 2rem;
    padding: 1.5rem;
    background: var(--bg-card, #fff);
    border-radius: 12px;
    box-shadow: var(--shadow-sm);
}
.publicador-avatar {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: 700;
    flex-shrink: 0;
}
.publicador-nombre {
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0 0 0.5rem 0;
    color: var(--text-primary);
}
.publicador-meta {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}
.meta-item {
    color: var(--text-secondary);
    font-size: 0.9rem;
}

/* Filtros */
.filtros-card {
    border: 2px solid var(--color-primary);
}
.filtros-container {
    margin-top: 1rem;
}
.filtro-tipo {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
    margin-bottom: 1rem;
}
.filtro-radio {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.25rem;
    background: var(--color-gray-100);
    border: 2px solid transparent;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
}
.filtro-radio:hover {
    background: var(--color-gray-200);
}
.filtro-radio.active {
    background: linear-gradient(135deg, #f4f7fb 0%, #e8eef6 100%);
    border-color: var(--color-primary);
}
.filtro-radio input {
    display: none;
}
.radio-label {
    font-weight: 500;
    font-size: 0.9rem;
}
.filtro-opciones {
    padding: 1rem;
    background: var(--color-gray-50);
    border-radius: 8px;
    margin-top: 1rem;
}
.filtro-grupo label {
    display: block;
    font-size: 0.875rem;
    color: var(--text-secondary);
    margin-bottom: 0.5rem;
}
.filtro-fechas-grid {
    display: flex;
    gap: 1rem;
    align-items: flex-end;
    flex-wrap: wrap;
}
.filtro-fechas-grid .filtro-grupo {
    flex: 1;
    min-width: 150px;
}
.filtro-activo-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-top: 1rem;
    padding: 0.75rem 1rem;
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    border-radius: 8px;
    flex-wrap: wrap;
}
.filtro-badge {
    font-weight: 600;
    color: #92400e;
    font-size: 0.875rem;
}
.filtro-periodo {
    color: #78350f;
    font-size: 0.9rem;
}
.filtro-limpiar {
    margin-left: auto;
    color: #b45309;
    text-decoration: underline;
    font-size: 0.875rem;
}

/* Dashboard de estadisticas */
.stats-dashboard {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 16px;
    padding: 2rem;
    margin-bottom: 2rem;
    color: white;
}
.stats-dashboard.stats-filtrado {
    background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
}
.stats-header {
    text-align: center;
    margin-bottom: 1.5rem;
}
.stats-header h2 {
    margin: 0 0 0.5rem 0;
    font-size: 1.5rem;
}
.stats-header p {
    margin: 0;
    opacity: 0.8;
    font-size: 0.9rem;
}
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
}
.stat-box {
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(10px);
    border-radius: 12px;
    padding: 1.25rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    transition: transform 0.2s, background 0.2s;
}
.stat-box:hover {
    background: rgba(255,255,255,0.25);
    transform: translateY(-3px);
}
.stat-icon {
    font-size: 2rem;
    margin-bottom: 0.75rem;
}
.stat-value {
    font-size: 2.5rem;
    font-weight: 700;
    line-height: 1;
    margin-bottom: 0.25rem;
}
.stat-label {
    font-size: 0.8rem;
    opacity: 0.9;
    margin-bottom: 0.75rem;
}
.stat-footer {
    font-size: 0.75rem;
    padding-top: 0.75rem;
    border-top: 1px solid rgba(255,255,255,0.2);
    width: 100%;
}
.trend-up { color: #a7f3d0; }
.trend-neutral { opacity: 0.7; }
.trend-down { color: #fecaca; }

/* Comparativa */
.stats-comparativa {
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid rgba(255,255,255,0.2);
}
.comparativa-titulo {
    text-align: center;
    font-size: 0.8rem;
    opacity: 0.8;
    margin-bottom: 1rem;
    text-transform: uppercase;
    letter-spacing: 1px;
}
.comparativa-grid {
    display: flex;
    justify-content: center;
    gap: 2rem;
    flex-wrap: wrap;
}
.comparativa-item {
    text-align: center;
}
.comparativa-label {
    display: block;
    font-size: 0.75rem;
    opacity: 0.7;
}
.comparativa-valor {
    font-size: 1.1rem;
    font-weight: 600;
}

/* Territorio actual */
.territorio-actual-card {
    display: flex;
    gap: 2rem;
    align-items: flex-start;
}
.territorio-actual-info {
    flex: 1;
}
.territorio-numero-grande {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--color-primary);
    margin-bottom: 0.25rem;
}
.territorio-nombre-actual {
    color: var(--text-secondary);
    font-size: 1rem;
    margin-bottom: 1rem;
}
.progreso-container {
    margin: 1.5rem 0;
}
.progreso-label {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
}
.progreso-dias {
    font-weight: 600;
    color: var(--text-primary);
}
.progreso-barra {
    height: 12px;
    background: #e5e7eb;
    border-radius: 6px;
    overflow: hidden;
}
.progreso-fill {
    height: 100%;
    border-radius: 6px;
    transition: width 0.5s ease;
}
.progreso-success { background: linear-gradient(90deg, #4a6da7, #34d399); }
.progreso-warning { background: linear-gradient(90deg, #4a6da7, #fbbf24); }
.progreso-danger { background: linear-gradient(90deg, #495057, #f87171); }
.progreso-escala {
    display: flex;
    justify-content: space-between;
    margin-top: 0.25rem;
    font-size: 0.7rem;
    color: var(--text-muted);
}
.territorio-actual-meta {
    margin-top: 1rem;
}
.meta-row {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px dashed var(--border-light);
    font-size: 0.875rem;
}
.meta-label {
    color: var(--text-muted);
}
.meta-value {
    font-weight: 500;
}
.territorio-actual-acciones {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}
.territorio-actual-acciones .btn {
    white-space: nowrap;
}

/* Sin territorio */
.territorio-sin-asignar {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    padding: 1.5rem;
    background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
    border-radius: 12px;
}
.sin-asignar-icon {
    font-size: 3rem;
}
.sin-asignar-texto {
    flex: 1;
}
.sin-asignar-texto strong {
    display: block;
    color: #166534;
    font-size: 1.1rem;
    margin-bottom: 0.25rem;
}
.sin-asignar-texto p {
    margin: 0;
    color: #15803d;
    font-size: 0.875rem;
}

/* Empty state */
.empty-state {
    text-align: center;
    padding: 3rem 1.5rem;
}
.empty-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
}
.empty-text strong {
    display: block;
    font-size: 1.1rem;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
}
.empty-text p {
    margin: 0;
    color: var(--text-muted);
}

/* Historial */
.historial-lista {
    margin-top: 1rem;
}
.historial-item {
    display: grid;
    grid-template-columns: 1fr 1fr auto auto auto;
    gap: 1rem;
    align-items: center;
    padding: 1rem;
    border-bottom: 1px solid var(--border-light);
    transition: background 0.2s;
}
.historial-item:hover {
    background: var(--color-gray-50);
}
.historial-item:last-child {
    border-bottom: none;
}
.historial-numero {
    font-weight: 700;
    color: var(--color-primary);
    font-size: 1.1rem;
}
.historial-nombre {
    color: var(--text-secondary);
    font-size: 0.875rem;
    margin-left: 0.5rem;
}
.historial-fechas {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: var(--text-secondary);
}
.historial-flecha {
    color: var(--text-muted);
}
.historial-duracion {
    text-align: center;
    background: var(--color-gray-100);
    padding: 0.5rem 1rem;
    border-radius: 8px;
}
.duracion-numero {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--text-primary);
    display: block;
}
.duracion-label {
    font-size: 0.7rem;
    color: var(--text-muted);
}
.historial-ver {
    padding: 0.5rem 1rem;
    background: var(--color-gray-100);
    color: var(--color-primary);
    border-radius: 6px;
    text-decoration: none;
    font-weight: 500;
    font-size: 0.875rem;
    transition: all 0.2s;
}
.historial-ver:hover {
    background: var(--color-primary);
    color: white;
}

/* Responsive */
@media (max-width: 1024px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
@media (max-width: 768px) {
    .publicador-header {
        flex-direction: column;
        text-align: center;
    }
    .publicador-meta {
        justify-content: center;
    }
    .filtro-tipo {
        flex-direction: column;
    }
    .filtro-fechas-grid {
        flex-direction: column;
    }
    .filtro-fechas-grid .btn {
        width: 100%;
    }
    .stats-dashboard {
        padding: 1.5rem;
    }
    .stats-grid {
        grid-template-columns: 1fr;
    }
    .stat-box {
        flex-direction: row;
        text-align: left;
        gap: 1rem;
    }
    .stat-icon {
        margin-bottom: 0;
    }
    .stat-content {
        flex: 1;
    }
    .stat-value {
        font-size: 1.75rem;
    }
    .stat-footer {
        display: none;
    }
    .comparativa-grid {
        flex-direction: column;
        gap: 0.75rem;
    }
    .territorio-actual-card {
        flex-direction: column;
    }
    .territorio-actual-acciones {
        flex-direction: row;
        flex-wrap: wrap;
        width: 100%;
    }
    .territorio-actual-acciones .btn {
        flex: 1;
        min-width: 120px;
    }
    .territorio-sin-asignar {
        flex-direction: column;
        text-align: center;
    }
    .historial-item {
        grid-template-columns: 1fr;
        gap: 0.5rem;
    }
    .historial-territorio,
    .historial-fechas,
    .historial-duracion,
    .historial-estado {
        justify-content: space-between;
        display: flex;
    }
    .historial-ver {
        width: 100%;
        text-align: center;
    }
}
</style>
@endsection
