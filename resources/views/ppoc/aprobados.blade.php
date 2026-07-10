@extends('layouts.app')

@section('title', 'Publicadores Aprobados PPOC')

@section('content')

<div class="page-nav">
    <div class="page-breadcrumbs">
        <a href="{{ route('dashboard') }}" class="breadcrumb-link">Dashboard</a>
        <span class="breadcrumb-sep">></span>
        <span class="breadcrumb-current">PPOC - Publicadores Aprobados</span>
    </div>
</div>

<!-- Titulo y descripcion -->
<div class="page-header-section">
    <h1 class="page-title">Publicadores Aprobados para PPOC</h1>
    <p class="page-subtitle">Gestiona qué publicadores pueden participar en la predicación pública con carritos (PPOC)</p>
</div>

<!-- Estadisticas -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-number">{{ $stats['total'] }}</div>
        <div class="stat-label">Total Publicadores</div>
    </div>
    <div class="stat-card stat-success">
        <div class="stat-number">{{ $stats['aprobados'] }}</div>
        <div class="stat-label">Aprobados</div>
    </div>
    <div class="stat-card stat-capitan">
        <div class="stat-number">{{ $stats['capitanes'] ?? 0 }}</div>
        <div class="stat-label">Capitanes</div>
    </div>
    <div class="stat-card stat-warning">
        <div class="stat-number">{{ $stats['pendientes'] }}</div>
        <div class="stat-label">No Aprobados</div>
    </div>
</div>

<!-- Lista de publicadores -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Lista de Publicadores</h3>
    </div>

    @if($publicadores->count() > 0)
    <div class="publicadores-list">
        @foreach($publicadores as $publicador)
        <div class="publicador-item {{ $publicador->aprobado_ppoc ? 'aprobado' : '' }}">
            <div class="publicador-info">
                <div class="publicador-avatar">
                    {{ strtoupper(substr($publicador->nombre, 0, 1)) }}{{ strtoupper(substr($publicador->apellidos, 0, 1)) }}
                </div>
                <div class="publicador-details">
                    <span class="publicador-nombre">{{ $publicador->nombre_completo }}</span>
                    @if($publicador->telefono)
                    <span class="publicador-telefono">{{ $publicador->telefono }}</span>
                    @endif
                </div>
            </div>
            <div class="publicador-actions">
                @if($publicador->aprobado_ppoc)
                    <span class="estado-badge {{ $publicador->es_capitan_ppoc ? 'badge-capitan' : 'badge-success' }}">
                        {{ $publicador->es_capitan_ppoc ? 'Capitan' : 'Voluntario' }}
                    </span>
                    @if(!$publicador->es_menor)
                    <form action="{{ route('ppoc.capitanes.toggle', $publicador) }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn-toggle {{ $publicador->es_capitan_ppoc ? 'btn-capitan-remove' : 'btn-capitan' }}">
                            @if($publicador->es_capitan_ppoc)
                                <span>&#x2B07;</span> Quitar Capitan
                            @else
                                <span>&#x2B06;</span> Hacer Capitan
                            @endif
                        </button>
                    </form>
                    @else
                    <span class="badge-menor">Menor de edad</span>
                    @endif
                @else
                    <span class="estado-badge badge-gray">No aprobado</span>
                @endif
                <form action="{{ route('ppoc.aprobados.toggle', $publicador) }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn-toggle {{ $publicador->aprobado_ppoc ? 'btn-remove' : 'btn-approve' }}">
                        @if($publicador->aprobado_ppoc)
                            <span>&#x2716;</span> Remover
                        @else
                            <span>&#x2714;</span> Aprobar
                        @endif
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="empty-state">
        <div class="empty-icon">&#x1F465;</div>
        <h3>No hay publicadores activos</h3>
        <p>Agrega publicadores desde el modulo de Administracion</p>
    </div>
    @endif
</div>

<style>
.page-header-section {
    text-align: center;
    margin-bottom: 2rem;
}

.page-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--text-primary, #1f2937);
    margin-bottom: 0.5rem;
}

.page-subtitle {
    font-size: 1rem;
    color: var(--text-muted, #6b7280);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: var(--bg-card, #fff);
    padding: 1.5rem;
    border-radius: 12px;
    text-align: center;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
}

.stat-card.stat-success {
    background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
}

.stat-card.stat-warning {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
}

.stat-card.stat-capitan {
    background: linear-gradient(135deg, #e8eef6 0%, #bfdbfe 100%);
}

.stat-number {
    font-size: 2rem;
    font-weight: 700;
    color: var(--text-primary, #1f2937);
}

.stat-label {
    font-size: 0.875rem;
    color: var(--text-muted, #6b7280);
    margin-top: 0.25rem;
}

.card-header {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--border-color, #e5e7eb);
}

.card-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--text-primary, #1f2937);
    margin: 0;
}

.publicadores-list {
    padding: 0.5rem;
}

.publicador-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--border-color, #e5e7eb);
    transition: background 0.2s;
}

.publicador-item:last-child {
    border-bottom: none;
}

.publicador-item:hover {
    background: var(--bg-hover, #f9fafb);
}

.publicador-item.aprobado {
    background: rgba(16, 185, 129, 0.05);
}

.publicador-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.publicador-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: linear-gradient(135deg, #4a6da7 0%, #2d4266 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1rem;
}

.publicador-item.aprobado .publicador-avatar {
    background: linear-gradient(135deg, #4a6da7 0%, #3d5a8a 100%);
}

.publicador-details {
    display: flex;
    flex-direction: column;
}

.publicador-nombre {
    font-weight: 600;
    color: var(--text-primary, #1f2937);
    font-size: 1rem;
}

.publicador-telefono {
    font-size: 0.85rem;
    color: var(--text-muted, #6b7280);
}

.publicador-actions {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.estado-badge {
    padding: 0.35rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}

.badge-success {
    background: #dcfce7;
    color: #166534;
}

.badge-gray {
    background: #f3f4f6;
    color: #6b7280;
}

.badge-capitan {
    background: #e8eef6;
    color: #2d4266;
}

.badge-menor {
    padding: 0.35rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    background: #fef3c7;
    color: #92400e;
}

.btn-toggle {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 8px;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-approve {
    background: linear-gradient(135deg, #4a6da7 0%, #3d5a8a 100%);
    color: white;
}

.btn-approve:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
}

.btn-remove {
    background: linear-gradient(135deg, #495057 0%, #343a40 100%);
    color: white;
}

.btn-remove:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
}

.btn-capitan {
    background: linear-gradient(135deg, #4a6da7 0%, #2d4266 100%);
    color: white;
}

.btn-capitan:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
}

.btn-capitan-remove {
    background: linear-gradient(135deg, #4a6da7 0%, #3d5a8a 100%);
    color: white;
}

.btn-capitan-remove:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4);
}

.empty-state {
    text-align: center;
    padding: 3rem;
}

.empty-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
}

.empty-state h3 {
    font-size: 1.25rem;
    color: var(--text-primary, #1f2937);
    margin-bottom: 0.5rem;
}

.empty-state p {
    color: var(--text-muted, #6b7280);
}

/* Responsive */
@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }

    .publicador-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }

    .publicador-actions {
        width: 100%;
        justify-content: space-between;
    }
}

@media (max-width: 480px) {
    .page-title {
        font-size: 1.5rem;
    }

    .publicador-actions {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.75rem;
    }

    .btn-toggle {
        width: 100%;
        justify-content: center;
    }
}

/* ========================================
   DARK THEME - Colores naranja consistentes
   ======================================== */
[data-theme="dark"] .stat-card {
    background: #171717;
    border: 1px solid #262626;
}

[data-theme="dark"] .stat-card.stat-success {
    background: rgba(34, 197, 94, 0.15);
    border-color: #4a6da7;
}

[data-theme="dark"] .stat-card.stat-warning {
    background: rgba(249, 115, 22, 0.15);
    border-color: #4a6da7;
}

[data-theme="dark"] .stat-card.stat-capitan {
    background: rgba(59, 130, 246, 0.15);
    border-color: #4a6da7;
}

[data-theme="dark"] .publicador-item {
    border-bottom-color: #262626;
}

[data-theme="dark"] .publicador-item:hover {
    background: rgba(249, 115, 22, 0.05);
}

[data-theme="dark"] .publicador-item.aprobado {
    background: rgba(34, 197, 94, 0.08);
}

[data-theme="dark"] .publicador-avatar {
    background: linear-gradient(135deg, #4a6da7 0%, #3d5a8a 100%);
    color: #0a0a0a;
}

[data-theme="dark"] .publicador-item.aprobado .publicador-avatar {
    background: linear-gradient(135deg, #4a6da7 0%, #3d5a8a 100%);
    color: #0a0a0a;
}

[data-theme="dark"] .publicador-nombre {
    color: #e5e5e5;
}

[data-theme="dark"] .publicador-telefono {
    color: #a3a3a3;
}

[data-theme="dark"] .badge-success {
    background: rgba(34, 197, 94, 0.2);
    color: #8aa8d6;
}

[data-theme="dark"] .badge-gray {
    background: rgba(107, 114, 128, 0.2);
    color: #a3a3a3;
}

[data-theme="dark"] .badge-capitan {
    background: rgba(59, 130, 246, 0.2);
    color: #8aa8d6;
}

[data-theme="dark"] .badge-menor {
    background: rgba(245, 158, 11, 0.2);
    color: #fcd34d;
}

[data-theme="dark"] .btn-capitan {
    background: linear-gradient(135deg, #4a6da7 0%, #2d4266 100%);
}

[data-theme="dark"] .btn-capitan-remove {
    background: linear-gradient(135deg, #4a6da7 0%, #3d5a8a 100%);
}

[data-theme="dark"] .btn-approve {
    background: linear-gradient(135deg, #4a6da7 0%, #3d5a8a 100%);
}

[data-theme="dark"] .btn-approve:hover {
    box-shadow: 0 4px 12px rgba(34, 197, 94, 0.4);
}

[data-theme="dark"] .page-title {
    color: #f5f5f5;
}

[data-theme="dark"] .page-subtitle {
    color: #a3a3a3;
}

[data-theme="dark"] .stat-number {
    color: #f5f5f5;
}

[data-theme="dark"] .stat-label {
    color: #a3a3a3;
}

[data-theme="dark"] .card {
    background: #171717;
    border: 1px solid #262626;
}

[data-theme="dark"] .section-header {
    background: linear-gradient(135deg, #4a6da7 0%, #3d5a8a 100%);
    color: #0a0a0a;
}

[data-theme="dark"] .section-title {
    color: #0a0a0a;
}

[data-theme="dark"] .section-count {
    background: rgba(0, 0, 0, 0.2);
    color: #0a0a0a;
}
</style>

@endsection
