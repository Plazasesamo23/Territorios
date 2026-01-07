@extends('layouts.app')

@section('title', 'Inicio - Gestor de Congregacion')

@section('content')

<div class="dashboard">
    <!-- Header -->
    <div class="dash-header">
        <div>
            <h1>Bienvenido</h1>
            <p class="subtitle">{{ $congregacionActiva->nombre ?? 'Tu congregacion' }} · {{ \Carbon\Carbon::now()->locale('es')->isoFormat('dddd, D [de] MMMM') }}</p>
        </div>
    </div>

    <!-- Alerta -->
    @if(isset($territoriosAtrasados) && $territoriosAtrasados > 0)
    <a href="{{ route('territorios.index', ['estado' => 'atrasado']) }}" class="alert-inline">
        ⚠ {{ $territoriosAtrasados }} territorio(s) requieren atencion →
    </a>
    @endif

    <!-- Stats globales -->
    <div class="stats-grid">
        <div class="stat-box">
            <span class="stat-number">{{ $totalTerritorios ?? 0 }}</span>
            <span class="stat-label">Territorios</span>
        </div>
        <div class="stat-box">
            <span class="stat-number">{{ $publicadoresActivos ?? 0 }}</span>
            <span class="stat-label">Publicadores</span>
        </div>
        @if(Auth::user()->canAccessPPOC())
        <div class="stat-box">
            <span class="stat-number">{{ $turnosEsteMes ?? 0 }}</span>
            <span class="stat-label">Turnos PPOC</span>
        </div>
        <div class="stat-box">
            <span class="stat-number">{{ $publicadoresAprobados ?? 0 }}</span>
            <span class="stat-label">Aprobados</span>
        </div>
        @endif
    </div>

    <!-- Accesos directos -->
    <div class="section-title">Accesos rapidos</div>
    <div class="shortcuts">
        <a href="{{ route('panel-territorios') }}" class="shortcut">
            <span class="sc-icon">🗺</span>
            <span class="sc-text">Territorios</span>
        </a>
        <a href="{{ route('registros.create') }}" class="shortcut">
            <span class="sc-icon">📤</span>
            <span class="sc-text">Asignar</span>
        </a>
        <a href="{{ route('registros.index') }}" class="shortcut">
            <span class="sc-icon">📥</span>
            <span class="sc-text">Devolver</span>
        </a>
        @if(Auth::user()->canAccessPPOC())
        <a href="{{ route('ppoc.calendario') }}" class="shortcut">
            <span class="sc-icon">📅</span>
            <span class="sc-text">PPOC</span>
        </a>
        @endif
        <a href="{{ route('s13.index') }}" class="shortcut">
            <span class="sc-icon">📄</span>
            <span class="sc-text">S-13</span>
        </a>
        @if(Auth::user()->isAdmin())
        <a href="{{ route('administracion') }}" class="shortcut">
            <span class="sc-icon">⚙</span>
            <span class="sc-text">Admin</span>
        </a>
        @endif
    </div>

    <!-- Actividad reciente -->
    @if(isset($registrosActivos) && count($registrosActivos) > 0)
    <div class="section-title">Actividad reciente</div>
    <div class="activity">
        @foreach($registrosActivos->take(6) as $registro)
        <div class="activity-item">
            <div class="av">{{ strtoupper(substr($registro->publicador->nombre ?? 'P', 0, 1)) }}</div>
            <div class="info">
                <span class="name">{{ $registro->publicador->nombre ?? 'Publicador' }}</span>
                <span class="terr">Territorio {{ $registro->territorio->numero_completo ?? '#' }}</span>
            </div>
            <span class="time">{{ \Carbon\Carbon::parse($registro->fecha_salida)->diffForHumans(null, true) }}</span>
        </div>
        @endforeach
    </div>
    @endif

    <!-- Info adicional -->
    <div class="section-title">Informacion</div>
    <div class="info-grid">
        <div class="info-row">
            <span>Grupos de predicacion</span>
            <strong>{{ $totalGrupos ?? 0 }}</strong>
        </div>
        <div class="info-row">
            <span>Territorios en archivo</span>
            <strong>{{ $territoriosArchivo ?? 0 }}</strong>
        </div>
        <div class="info-row">
            <span>Ultima actualizacion</span>
            <strong>{{ now()->format('H:i') }}</strong>
        </div>
    </div>
</div>

<style>
/* ========================================
   DASHBOARD FLAT - Sin cards, plano, limpio
   ======================================== */
.dashboard {
    max-width: 1200px;
    margin: 0 auto;
}

/* Header */
.dash-header {
    margin-bottom: 2rem;
}

.dash-header h1 {
    font-size: 1.75rem;
    font-weight: 700;
    color: #212529;
    margin: 0 0 0.25rem 0;
}

[data-theme="dark"] .dash-header h1 {
    color: #f1f3f5;
}

.dash-header .subtitle {
    font-size: 0.9rem;
    color: #6c757d;
    margin: 0;
    text-transform: capitalize;
}

[data-theme="dark"] .dash-header .subtitle {
    color: #8b939c;
}

/* Alert inline */
.alert-inline {
    display: inline-block;
    padding: 0.625rem 1rem;
    background: #4a6da7;
    color: white;
    border-radius: 6px;
    text-decoration: none;
    font-size: 0.875rem;
    margin-bottom: 1.5rem;
}

[data-theme="dark"] .alert-inline {
    background: #6b8fc7;
    color: #121416;
}

/* Stats grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2.5rem;
}

.stat-box {
    text-align: center;
}

.stat-number {
    display: block;
    font-size: 2.25rem;
    font-weight: 700;
    color: #212529;
    line-height: 1;
}

[data-theme="dark"] .stat-number {
    color: #f1f3f5;
}

.stat-number.primary {
    color: #4a6da7;
}

[data-theme="dark"] .stat-number.primary {
    color: #6b8fc7;
}

.stat-label {
    display: block;
    font-size: 0.75rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    margin-top: 0.375rem;
}

[data-theme="dark"] .stat-label {
    color: #8b939c;
}

/* Section title */
.section-title {
    font-size: 0.7rem;
    font-weight: 600;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    margin-bottom: 0.75rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #e9ecef;
}

[data-theme="dark"] .section-title {
    color: #8b939c;
    border-bottom-color: #2d3339;
}

/* Shortcuts */
.shortcuts {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-bottom: 2.5rem;
}

.shortcut {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1rem;
    background: #f8f9fa;
    border-radius: 6px;
    text-decoration: none;
    color: #212529;
    font-size: 0.875rem;
    font-weight: 500;
    transition: background 0.15s;
}

[data-theme="dark"] .shortcut {
    background: #1a1d21;
    color: #f1f3f5;
}

.shortcut:hover {
    background: #e9ecef;
}

[data-theme="dark"] .shortcut:hover {
    background: #22262b;
}

.sc-icon {
    font-size: 1.125rem;
}

/* Activity */
.activity {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 0.5rem;
    margin-bottom: 2.5rem;
}

.activity-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 0;
    border-bottom: 1px solid #e9ecef;
}

[data-theme="dark"] .activity-item {
    border-bottom-color: #2d3339;
}

.activity-item .av {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #4a6da7;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.875rem;
    flex-shrink: 0;
}

[data-theme="dark"] .activity-item .av {
    background: #6b8fc7;
    color: #121416;
}

.activity-item .info {
    flex: 1;
    min-width: 0;
}

.activity-item .name {
    display: block;
    font-size: 0.9rem;
    font-weight: 500;
    color: #212529;
}

[data-theme="dark"] .activity-item .name {
    color: #f1f3f5;
}

.activity-item .terr {
    display: block;
    font-size: 0.75rem;
    color: #6c757d;
}

[data-theme="dark"] .activity-item .terr {
    color: #8b939c;
}

.activity-item .time {
    font-size: 0.7rem;
    color: #adb5bd;
    flex-shrink: 0;
}

[data-theme="dark"] .activity-item .time {
    color: #5c656e;
}

/* Info grid */
.info-grid {
    max-width: 400px;
}

.info-row {
    display: flex;
    justify-content: space-between;
    padding: 0.625rem 0;
    border-bottom: 1px solid #e9ecef;
    font-size: 0.875rem;
}

[data-theme="dark"] .info-row {
    border-bottom-color: #2d3339;
}

.info-row span {
    color: #6c757d;
}

[data-theme="dark"] .info-row span {
    color: #8b939c;
}

.info-row strong {
    color: #212529;
}

[data-theme="dark"] .info-row strong {
    color: #f1f3f5;
}

/* Responsive */
@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
    }

    .stat-number {
        font-size: 1.75rem;
    }

    .shortcuts {
        gap: 0.5rem;
    }

    .shortcut {
        padding: 0.5rem 0.75rem;
        font-size: 0.8rem;
    }

    .activity {
        grid-template-columns: 1fr;
    }
}
</style>

@endsection
