@extends('layouts.app')

@section('title', 'Inicio - Gestor de Congregacion')

@section('content')

<div class="dashboard-page">
    <!-- Hero Section -->
    <div class="dashboard-hero">
        <div class="hero-content">
            <h1 class="hero-title">Bienvenido al Gestor de Congregacion</h1>
            <p class="hero-subtitle">{{ $congregacionActiva->nombre ?? 'Tu congregacion' }}</p>
            <div class="hero-date">{{ \Carbon\Carbon::now()->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}</div>
        </div>
    </div>

    <!-- Accesos Rapidos - Cards grandes -->
    <div class="quick-access-grid">
        <!-- Territorios -->
        <a href="{{ route('panel-territorios') }}" class="quick-card territorios">
            <div class="quick-icon">&#x1F5FA;</div>
            <div class="quick-content">
                <h2 class="quick-title">Territorios</h2>
                <p class="quick-desc">Asignar y devolver territorios</p>
            </div>
            <div class="quick-stats">
                <div class="quick-stat">
                    <span class="stat-value">{{ $territoriosActivos ?? 0 }}</span>
                    <span class="stat-label">Activos</span>
                </div>
                <div class="quick-stat">
                    <span class="stat-value stat-green">{{ $territoriosLibres ?? 0 }}</span>
                    <span class="stat-label">Libres</span>
                </div>
            </div>
            <span class="quick-arrow">&#x276F;</span>
        </a>

        <!-- PPOC -->
        @if(Auth::user()->canAccessPPOC())
        <a href="{{ route('ppoc.calendario') }}" class="quick-card ppoc">
            <div class="quick-icon">&#x1F4C5;</div>
            <div class="quick-content">
                <h2 class="quick-title">PPOC</h2>
                <p class="quick-desc">Programa de predicacion organizada</p>
            </div>
            <div class="quick-stats">
                <div class="quick-stat">
                    <span class="stat-value">{{ $turnosEsteMes ?? 0 }}</span>
                    <span class="stat-label">Turnos mes</span>
                </div>
                <div class="quick-stat">
                    <span class="stat-value stat-green">{{ $publicadoresAprobados ?? 0 }}</span>
                    <span class="stat-label">Aprobados</span>
                </div>
            </div>
            <span class="quick-arrow">&#x276F;</span>
        </a>
        @endif

        <!-- Administracion (solo admin) -->
        @if(Auth::user()->isAdmin())
        <a href="{{ route('administracion') }}" class="quick-card admin">
            <div class="quick-icon">&#x2699;</div>
            <div class="quick-content">
                <h2 class="quick-title">Administracion</h2>
                <p class="quick-desc">Gestionar publicadores, usuarios y mas</p>
            </div>
            <div class="quick-stats">
                <div class="quick-stat">
                    <span class="stat-value">{{ $publicadoresActivos ?? 0 }}</span>
                    <span class="stat-label">Publicadores</span>
                </div>
                <div class="quick-stat">
                    <span class="stat-value">{{ $totalTerritorios ?? 0 }}</span>
                    <span class="stat-label">Territorios</span>
                </div>
            </div>
            <span class="quick-arrow">&#x276F;</span>
        </a>
        @endif
    </div>

    <!-- Estadisticas y Alertas -->
    <div class="dashboard-grid">
        <!-- Columna principal -->
        <div class="dashboard-main">
            <!-- Alerta de territorios atrasados -->
            @if(isset($territoriosAtrasados) && $territoriosAtrasados > 0)
            <div class="alert-card warning">
                <div class="alert-icon">&#x26A0;</div>
                <div class="alert-content">
                    <h3 class="alert-title">Territorios que requieren atencion</h3>
                    <p class="alert-desc">Hay {{ $territoriosAtrasados }} territorio(s) que llevan mas tiempo del esperado.</p>
                </div>
                <a href="{{ route('territorios.index', ['estado' => 'atrasado']) }}" class="alert-action">Ver</a>
            </div>
            @endif

            <!-- Resumen de Territorios -->
            <div class="summary-card">
                <div class="summary-header">
                    <h3 class="summary-title">&#x1F5FA; Resumen de Territorios</h3>
                    <a href="{{ route('territorios.index') }}" class="summary-link">Ver todos</a>
                </div>
                <div class="summary-stats">
                    <div class="summary-stat">
                        <div class="stat-circle total">{{ $totalTerritorios ?? 0 }}</div>
                        <span class="stat-name">Total</span>
                    </div>
                    <div class="summary-stat">
                        <div class="stat-circle libre">{{ $territoriosLibres ?? 0 }}</div>
                        <span class="stat-name">Libres</span>
                    </div>
                    <div class="summary-stat">
                        <div class="stat-circle activo">{{ $territoriosActivos ?? 0 }}</div>
                        <span class="stat-name">Activos</span>
                    </div>
                    <div class="summary-stat">
                        <div class="stat-circle archivo">{{ $territoriosArchivo ?? 0 }}</div>
                        <span class="stat-name">En archivo</span>
                    </div>
                    @if(isset($territoriosAtrasados) && $territoriosAtrasados > 0)
                    <div class="summary-stat">
                        <div class="stat-circle atrasado">{{ $territoriosAtrasados }}</div>
                        <span class="stat-name">Atrasados</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Actividad Reciente -->
            <div class="activity-card">
                <h3 class="activity-title">&#x1F4C8; Actividad Reciente</h3>
                @if(isset($registrosActivos) && count($registrosActivos) > 0)
                <div class="activity-list">
                    @foreach($registrosActivos->take(5) as $registro)
                    <div class="activity-item">
                        <div class="activity-avatar">
                            {{ strtoupper(substr($registro->publicador->nombre ?? 'P', 0, 1)) }}
                        </div>
                        <div class="activity-info">
                            <span class="activity-name">{{ $registro->publicador->nombre ?? 'Publicador' }}</span>
                            <span class="activity-detail">Territorio {{ $registro->territorio->numero_completo ?? '#' }}</span>
                        </div>
                        <span class="activity-time">{{ \Carbon\Carbon::parse($registro->fecha_salida)->diffForHumans() }}</span>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="activity-empty">
                    <span class="empty-icon">&#x1F4ED;</span>
                    <p>No hay actividad reciente</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Columna lateral -->
        <div class="dashboard-sidebar">
            <!-- Acciones Rapidas -->
            <div class="actions-card">
                <h3 class="actions-title">&#x26A1; Acciones Rapidas</h3>
                <div class="actions-list">
                    <a href="{{ route('registros.create') }}" class="action-btn-small asignar">
                        <span class="action-icon-small">&#x1F4E4;</span>
                        Asignar Territorio
                    </a>
                    <a href="{{ route('registros.index') }}" class="action-btn-small devolver">
                        <span class="action-icon-small">&#x1F4E5;</span>
                        Devolver Territorio
                    </a>
                    @if(Auth::user()->canAccessPPOC())
                    <a href="{{ route('ppoc.calendario') }}" class="action-btn-small ppoc">
                        <span class="action-icon-small">&#x1F4C5;</span>
                        Ver Calendario PPOC
                    </a>
                    @endif
                    <a href="{{ route('s13.index') }}" class="action-btn-small s13">
                        <span class="action-icon-small">&#x1F4C4;</span>
                        Generar S-13
                    </a>
                </div>
            </div>

            <!-- Info del Sistema -->
            <div class="info-card">
                <h3 class="info-title">&#x2139; Informacion</h3>
                <div class="info-list">
                    <div class="info-item">
                        <span class="info-label">Publicadores activos</span>
                        <span class="info-value">{{ $publicadoresActivos ?? 0 }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Grupos de predicacion</span>
                        <span class="info-value">{{ $totalGrupos ?? 0 }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Ultima actualizacion</span>
                        <span class="info-value">{{ now()->format('H:i') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.dashboard-page {
    max-width: 1400px;
    margin: 0 auto;
    padding: 1rem;
}

/* Hero */
.dashboard-hero {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 50%, #4f46e5 100%);
    border-radius: 20px;
    padding: 2.5rem;
    margin-bottom: 2rem;
    text-align: center;
    color: white;
}

.hero-title {
    font-size: 1.75rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.hero-subtitle {
    font-size: 1.1rem;
    opacity: 0.9;
    margin-bottom: 0.5rem;
}

.hero-date {
    font-size: 0.9rem;
    opacity: 0.8;
    text-transform: capitalize;
}

/* Quick Access Cards */
.quick-access-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.quick-card {
    display: flex;
    align-items: center;
    gap: 1.25rem;
    padding: 1.5rem;
    background: var(--bg-card, #fff);
    border-radius: 16px;
    text-decoration: none;
    color: inherit;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    transition: all 0.3s;
    position: relative;
    overflow: hidden;
}

.quick-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.15);
}

.quick-card.territorios { border-left: 4px solid #3b82f6; }
.quick-card.ppoc { border-left: 4px solid #10b981; }
.quick-card.admin { border-left: 4px solid #8b5cf6; }

.quick-icon {
    font-size: 2.5rem;
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--bg-secondary, #f3f4f6);
    border-radius: 12px;
    flex-shrink: 0;
}

.quick-content {
    flex: 1;
}

.quick-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--text-primary, #1f2937);
    margin-bottom: 0.25rem;
}

.quick-desc {
    font-size: 0.85rem;
    color: var(--text-muted, #6b7280);
}

.quick-stats {
    display: flex;
    gap: 1rem;
}

.quick-stat {
    text-align: center;
}

.stat-value {
    display: block;
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text-primary, #1f2937);
}

.stat-value.stat-green { color: #10b981; }

.stat-label {
    font-size: 0.7rem;
    color: var(--text-muted, #6b7280);
    text-transform: uppercase;
}

.quick-arrow {
    font-size: 1.25rem;
    color: var(--text-muted, #9ca3af);
    transition: transform 0.2s;
}

.quick-card:hover .quick-arrow {
    transform: translateX(4px);
}

/* Dashboard Grid */
.dashboard-grid {
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: 1.5rem;
}

.dashboard-main {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.dashboard-sidebar {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

/* Alert Card */
.alert-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem 1.5rem;
    border-radius: 12px;
    background: #fef3c7;
    border: 1px solid #f59e0b;
}

.alert-card.warning .alert-icon {
    font-size: 1.5rem;
}

.alert-content {
    flex: 1;
}

.alert-title {
    font-weight: 600;
    color: #92400e;
    margin-bottom: 0.15rem;
}

.alert-desc {
    font-size: 0.85rem;
    color: #a16207;
}

.alert-action {
    padding: 0.5rem 1rem;
    background: #f59e0b;
    color: white;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.85rem;
}

/* Summary Card */
.summary-card {
    background: var(--bg-card, #fff);
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

.summary-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.summary-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--text-primary, #1f2937);
}

.summary-link {
    font-size: 0.85rem;
    color: #3b82f6;
    text-decoration: none;
}

.summary-stats {
    display: flex;
    justify-content: space-around;
    flex-wrap: wrap;
    gap: 1rem;
}

.summary-stat {
    text-align: center;
}

.stat-circle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0 auto 0.5rem;
}

.stat-circle.total { background: #e5e7eb; color: #374151; }
.stat-circle.libre { background: #d1fae5; color: #065f46; }
.stat-circle.activo { background: #fef3c7; color: #92400e; }
.stat-circle.archivo { background: #dbeafe; color: #1e40af; }
.stat-circle.atrasado { background: #fee2e2; color: #991b1b; }

.stat-name {
    font-size: 0.8rem;
    color: var(--text-muted, #6b7280);
}

/* Activity Card */
.activity-card {
    background: var(--bg-card, #fff);
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

.activity-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--text-primary, #1f2937);
    margin-bottom: 1rem;
}

.activity-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.activity-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem;
    background: var(--bg-secondary, #f9fafb);
    border-radius: 10px;
}

.activity-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    flex-shrink: 0;
}

.activity-info {
    flex: 1;
    min-width: 0;
}

.activity-name {
    display: block;
    font-weight: 600;
    color: var(--text-primary, #1f2937);
}

.activity-detail {
    display: block;
    font-size: 0.8rem;
    color: var(--text-muted, #6b7280);
}

.activity-time {
    font-size: 0.75rem;
    color: var(--text-muted, #9ca3af);
}

.activity-empty {
    text-align: center;
    padding: 2rem;
    color: var(--text-muted, #6b7280);
}

.empty-icon {
    font-size: 2rem;
    display: block;
    margin-bottom: 0.5rem;
}

/* Actions Card */
.actions-card {
    background: var(--bg-card, #fff);
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

.actions-title {
    font-size: 1rem;
    font-weight: 600;
    color: var(--text-primary, #1f2937);
    margin-bottom: 1rem;
}

.actions-list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.action-btn-small {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 500;
    font-size: 0.9rem;
    transition: all 0.2s;
}

.action-btn-small.asignar {
    background: rgba(16, 185, 129, 0.1);
    color: #065f46;
}
.action-btn-small.devolver {
    background: rgba(59, 130, 246, 0.1);
    color: #1e40af;
}
.action-btn-small.ppoc {
    background: rgba(16, 185, 129, 0.1);
    color: #065f46;
}
.action-btn-small.s13 {
    background: rgba(245, 158, 11, 0.1);
    color: #92400e;
}

.action-btn-small:hover {
    transform: translateX(4px);
}

.action-icon-small {
    font-size: 1.1rem;
}

/* Info Card */
.info-card {
    background: var(--bg-card, #fff);
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

.info-title {
    font-size: 1rem;
    font-weight: 600;
    color: var(--text-primary, #1f2937);
    margin-bottom: 1rem;
}

.info-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid var(--border-color, #e5e7eb);
}

.info-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.info-label {
    font-size: 0.85rem;
    color: var(--text-muted, #6b7280);
}

.info-value {
    font-weight: 600;
    color: var(--text-primary, #1f2937);
}

/* Responsive */
@media (max-width: 1024px) {
    .dashboard-grid {
        grid-template-columns: 1fr;
    }

    .dashboard-sidebar {
        flex-direction: row;
        flex-wrap: wrap;
    }

    .actions-card, .info-card {
        flex: 1;
        min-width: 280px;
    }
}

@media (max-width: 768px) {
    .quick-access-grid {
        grid-template-columns: 1fr;
    }

    .quick-card {
        flex-wrap: wrap;
    }

    .quick-stats {
        width: 100%;
        justify-content: center;
        margin-top: 0.5rem;
    }

    .dashboard-hero {
        padding: 1.5rem;
    }

    .hero-title {
        font-size: 1.35rem;
    }
}

/* ========================================
   DARK THEME
   ======================================== */
[data-theme="dark"] .dashboard-hero {
    background: linear-gradient(135deg, #f97316 0%, #ea580c 50%, #c2410c 100%);
}

[data-theme="dark"] .quick-card {
    background: #171717;
    border-color: #262626;
}

[data-theme="dark"] .quick-card.territorios { border-left-color: #f97316; }
[data-theme="dark"] .quick-card.ppoc { border-left-color: #22c55e; }
[data-theme="dark"] .quick-card.admin { border-left-color: #fb923c; }

[data-theme="dark"] .quick-icon {
    background: #262626;
}

[data-theme="dark"] .quick-title,
[data-theme="dark"] .stat-value,
[data-theme="dark"] .summary-title,
[data-theme="dark"] .activity-title,
[data-theme="dark"] .actions-title,
[data-theme="dark"] .info-title,
[data-theme="dark"] .activity-name,
[data-theme="dark"] .info-value {
    color: #f5f5f5;
}

[data-theme="dark"] .quick-desc,
[data-theme="dark"] .stat-label,
[data-theme="dark"] .stat-name,
[data-theme="dark"] .activity-detail,
[data-theme="dark"] .activity-time,
[data-theme="dark"] .info-label {
    color: #a3a3a3;
}

[data-theme="dark"] .summary-card,
[data-theme="dark"] .activity-card,
[data-theme="dark"] .actions-card,
[data-theme="dark"] .info-card {
    background: #171717;
    border: 1px solid #262626;
}

[data-theme="dark"] .activity-item {
    background: #262626;
}

[data-theme="dark"] .activity-avatar {
    background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
}

[data-theme="dark"] .summary-link {
    color: #f97316;
}

[data-theme="dark"] .stat-circle.total { background: #404040; color: #e5e5e5; }
[data-theme="dark"] .stat-circle.libre { background: rgba(34, 197, 94, 0.2); color: #86efac; }
[data-theme="dark"] .stat-circle.activo { background: rgba(249, 115, 22, 0.2); color: #fdba74; }
[data-theme="dark"] .stat-circle.archivo { background: rgba(59, 130, 246, 0.2); color: #93c5fd; }
[data-theme="dark"] .stat-circle.atrasado { background: rgba(239, 68, 68, 0.2); color: #fca5a5; }

[data-theme="dark"] .action-btn-small.asignar {
    background: rgba(249, 115, 22, 0.15);
    color: #fdba74;
}
[data-theme="dark"] .action-btn-small.devolver {
    background: rgba(59, 130, 246, 0.15);
    color: #93c5fd;
}
[data-theme="dark"] .action-btn-small.ppoc {
    background: rgba(34, 197, 94, 0.15);
    color: #86efac;
}
[data-theme="dark"] .action-btn-small.s13 {
    background: rgba(245, 158, 11, 0.15);
    color: #fcd34d;
}

[data-theme="dark"] .info-item {
    border-bottom-color: #262626;
}

[data-theme="dark"] .alert-card {
    background: rgba(249, 115, 22, 0.15);
    border-color: #f97316;
}

[data-theme="dark"] .alert-title {
    color: #fdba74;
}

[data-theme="dark"] .alert-desc {
    color: #fb923c;
}

[data-theme="dark"] .alert-action {
    background: #f97316;
    color: #0a0a0a;
}
</style>

@endsection
