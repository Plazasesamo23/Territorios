@extends('layouts.app')

@section('title', 'Administracion')

@section('content')

<div class="admin-page">
    <div class="admin-header">
        <h1 class="admin-title">Panel de Administracion</h1>
        <p class="admin-subtitle">Gestiona todos los aspectos de tu congregacion</p>
    </div>

    <div class="admin-grid">
        <!-- Seccion Territorios -->
        <div class="admin-section">
            <div class="section-header territorios">
                <span class="section-icon">&#x1F5FA;</span>
                <h2 class="section-title">Territorios</h2>
            </div>
            <div class="section-content">
                <a href="{{ route('territorios.create', ['tipo' => 'normal']) }}" class="admin-link">
                    <span class="link-icon">&#x2795;</span>
                    <div class="link-content">
                        <span class="link-title">Añadir Territorio</span>
                        <span class="link-desc">Agregar nuevo territorio normal</span>
                    </div>
                    <span class="link-arrow">&#x276F;</span>
                </a>
                <a href="{{ route('territorios.create', ['tipo' => 'campana']) }}" class="admin-link">
                    <span class="link-icon">&#x1F4E2;</span>
                    <div class="link-content">
                        <span class="link-title">Crear Territorio Campana</span>
                        <span class="link-desc">Agregar territorio tipo campana (C-)</span>
                    </div>
                    <span class="link-arrow">&#x276F;</span>
                </a>
                <a href="{{ route('territorios.create', ['tipo' => 'negocios']) }}" class="admin-link">
                    <span class="link-icon">&#x1F3E2;</span>
                    <div class="link-content">
                        <span class="link-title">Crear Territorio Negocios</span>
                        <span class="link-desc">Agregar territorio tipo negocios (N-)</span>
                    </div>
                    <span class="link-arrow">&#x276F;</span>
                </a>
                <a href="{{ route('registros.archivados') }}" class="admin-link">
                    <span class="link-icon">&#x1F4DA;</span>
                    <div class="link-content">
                        <span class="link-title">Registros Archivados</span>
                        <span class="link-desc">Ver historial de asignaciones</span>
                    </div>
                    <span class="link-arrow">&#x276F;</span>
                </a>
                <a href="{{ route('creador-territorios.index') }}" class="admin-link">
                    <span class="link-icon">&#x1F3A8;</span>
                    <div class="link-content">
                        <span class="link-title">Creador de Territorios <span class="beta-badge">BETA</span></span>
                        <span class="link-desc">Editor visual para crear mapas</span>
                    </div>
                    <span class="link-arrow">&#x276F;</span>
                </a>
            </div>
        </div>

        <!-- Seccion Publicadores -->
        <div class="admin-section">
            <div class="section-header publicadores">
                <span class="section-icon">&#x1F465;</span>
                <h2 class="section-title">Publicadores</h2>
            </div>
            <div class="section-content">
                <a href="{{ route('publicadores.index') }}" class="admin-link">
                    <span class="link-icon">&#x1F4CB;</span>
                    <div class="link-content">
                        <span class="link-title">Ver Publicadores</span>
                        <span class="link-desc">Lista completa de publicadores</span>
                    </div>
                    <span class="link-arrow">&#x276F;</span>
                </a>
                <a href="{{ route('publicadores.create') }}" class="admin-link">
                    <span class="link-icon">&#x2795;</span>
                    <div class="link-content">
                        <span class="link-title">Crear Publicador</span>
                        <span class="link-desc">Agregar nuevo publicador</span>
                    </div>
                    <span class="link-arrow">&#x276F;</span>
                </a>
                <a href="{{ route('grupos-predicacion.index') }}" class="admin-link">
                    <span class="link-icon">&#x1F46A;</span>
                    <div class="link-content">
                        <span class="link-title">Grupos de Predicacion</span>
                        <span class="link-desc">Organizar publicadores en grupos</span>
                    </div>
                    <span class="link-arrow">&#x276F;</span>
                </a>
            </div>
        </div>

        <!-- Seccion Usuarios -->
        <div class="admin-section">
            <div class="section-header usuarios">
                <span class="section-icon">&#x1F464;</span>
                <h2 class="section-title">Usuarios</h2>
            </div>
            <div class="section-content">
                <a href="{{ route('usuarios.index') }}" class="admin-link">
                    <span class="link-icon">&#x1F4CB;</span>
                    <div class="link-content">
                        <span class="link-title">Gestionar Usuarios</span>
                        <span class="link-desc">Ver y editar usuarios del sistema</span>
                    </div>
                    <span class="link-arrow">&#x276F;</span>
                </a>
                <a href="{{ route('usuarios.create') }}" class="admin-link">
                    <span class="link-icon">&#x2795;</span>
                    <div class="link-content">
                        <span class="link-title">Crear Usuario</span>
                        <span class="link-desc">Agregar nuevo usuario</span>
                    </div>
                    <span class="link-arrow">&#x276F;</span>
                </a>
            </div>
        </div>

        <!-- Seccion PPOC -->
        @if(Auth::user()->canAccessPPOC())
        <div class="admin-section">
            <div class="section-header ppoc">
                <span class="section-icon">&#x1F4C5;</span>
                <h2 class="section-title">PPOC</h2>
            </div>
            <div class="section-content">
                <a href="{{ route('ppoc.turnos.index') }}" class="admin-link">
                    <span class="link-icon">&#x1F4CB;</span>
                    <div class="link-content">
                        <span class="link-title">Turnos Predeterminados</span>
                        <span class="link-desc">Configurar plantillas de turnos</span>
                    </div>
                    <span class="link-arrow">&#x276F;</span>
                </a>
                <a href="{{ route('ppoc.turnos.create') }}" class="admin-link">
                    <span class="link-icon">&#x2795;</span>
                    <div class="link-content">
                        <span class="link-title">Crear Turno</span>
                        <span class="link-desc">Agregar nueva plantilla de turno</span>
                    </div>
                    <span class="link-arrow">&#x276F;</span>
                </a>
                <a href="{{ route('ppoc.aprobados') }}" class="admin-link">
                    <span class="link-icon">&#x2705;</span>
                    <div class="link-content">
                        <span class="link-title">Publicadores Aprobados</span>
                        <span class="link-desc">Gestionar quienes pueden participar</span>
                    </div>
                    <span class="link-arrow">&#x276F;</span>
                </a>
                <a href="{{ route('ppoc.disponibilidad.por-turno') }}" class="admin-link">
                    <span class="link-icon">&#x1F4C6;</span>
                    <div class="link-content">
                        <span class="link-title">Disponibilidades</span>
                        <span class="link-desc">Ver disponibilidad de publicadores por turno</span>
                    </div>
                    <span class="link-arrow">&#x276F;</span>
                </a>
            </div>
        </div>
        @endif

        <!-- Seccion S-13 -->
        <div class="admin-section">
            <div class="section-header reportes">
                <span class="section-icon">&#x1F4C4;</span>
                <h2 class="section-title">S-13</h2>
            </div>
            <div class="section-content">
                <a href="{{ route('s13.index') }}" class="admin-link">
                    <span class="link-icon">&#x1F4CB;</span>
                    <div class="link-content">
                        <span class="link-title">Ver S-13</span>
                        <span class="link-desc">Generar reporte oficial de territorios</span>
                    </div>
                    <span class="link-arrow">&#x276F;</span>
                </a>
                <a href="{{ route('s13.importar') }}" class="admin-link">
                    <span class="link-icon">&#x1F4E5;</span>
                    <div class="link-content">
                        <span class="link-title">Importar S-13</span>
                        <span class="link-desc">Importar registros desde PDF</span>
                    </div>
                    <span class="link-arrow">&#x276F;</span>
                </a>
            </div>
        </div>

        <!-- Seccion Sistema -->
        <div class="admin-section">
            <div class="section-header sistema">
                <span class="section-icon">&#x2699;</span>
                <h2 class="section-title">Sistema</h2>
            </div>
            <div class="section-content">
                <a href="{{ route('configuracion') }}" class="admin-link">
                    <span class="link-icon">&#x2699;</span>
                    <div class="link-content">
                        <span class="link-title">Configuracion</span>
                        <span class="link-desc">Ajustes generales de la congregacion</span>
                    </div>
                    <span class="link-arrow">&#x276F;</span>
                </a>
                <a href="{{ route('cambiar-usuario.index') }}" class="admin-link">
                    <span class="link-icon">&#x1F465;</span>
                    <div class="link-content">
                        <span class="link-title">Cambiar Usuario</span>
                        <span class="link-desc">Acceder como otro usuario de la congregacion</span>
                    </div>
                    <span class="link-arrow">&#x276F;</span>
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.admin-page {
    max-width: 1200px;
    margin: 0 auto;
    padding: 1rem;
}

.admin-header {
    text-align: center;
    margin-bottom: 2.5rem;
}

.admin-title {
    font-size: 2rem;
    font-weight: 700;
    color: var(--text-primary, #1f2937);
    margin-bottom: 0.5rem;
}

.admin-subtitle {
    font-size: 1.1rem;
    color: var(--text-muted, #6b7280);
}

.admin-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 1.5rem;
}

.admin-section {
    background: var(--bg-card, #fff);
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    transition: transform 0.3s, box-shadow 0.3s;
}

.admin-section:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.12);
}

.section-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1.25rem 1.5rem;
    color: white;
}

.section-header.territorios {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
}

.section-header.publicadores {
    background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);
}

.section-header.usuarios {
    background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
}

.section-header.ppoc {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.section-header.reportes {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}

.section-header.sistema {
    background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
}

.section-icon {
    font-size: 1.75rem;
}

.section-title {
    font-size: 1.25rem;
    font-weight: 600;
    margin: 0;
}

.section-content {
    padding: 0.5rem;
}

.admin-link {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem 1.25rem;
    text-decoration: none;
    color: var(--text-primary, #374151);
    border-radius: 10px;
    transition: all 0.2s;
}

.admin-link:hover {
    background: var(--bg-hover, #f3f4f6);
}

.link-icon {
    font-size: 1.5rem;
    width: 2.5rem;
    height: 2.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--bg-secondary, #f3f4f6);
    border-radius: 10px;
    flex-shrink: 0;
}

.link-content {
    flex: 1;
    min-width: 0;
}

.beta-badge {
    display: inline-block;
    padding: 0.15rem 0.4rem;
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: white;
    font-size: 0.6rem;
    font-weight: 700;
    border-radius: 4px;
    margin-left: 0.5rem;
    vertical-align: middle;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.link-title {
    display: block;
    font-weight: 600;
    font-size: 0.95rem;
    color: var(--text-primary, #1f2937);
    margin-bottom: 0.15rem;
}

.link-desc {
    display: block;
    font-size: 0.8rem;
    color: var(--text-muted, #6b7280);
}

.link-arrow {
    color: var(--text-muted, #9ca3af);
    font-size: 1rem;
    transition: transform 0.2s;
}

.admin-link:hover .link-arrow {
    transform: translateX(4px);
    color: var(--text-primary, #374151);
}

/* Responsive */
@media (max-width: 768px) {
    .admin-grid {
        grid-template-columns: 1fr;
    }

    .admin-title {
        font-size: 1.5rem;
    }

    .admin-subtitle {
        font-size: 1rem;
    }

    .section-header {
        padding: 1rem 1.25rem;
    }

    .section-icon {
        font-size: 1.5rem;
    }

    .section-title {
        font-size: 1.1rem;
    }

    .admin-link {
        padding: 0.875rem 1rem;
    }

    .link-icon {
        width: 2.25rem;
        height: 2.25rem;
        font-size: 1.25rem;
    }
}

/* ========================================
   DARK THEME - Colores naranja
   ======================================== */
[data-theme="dark"] .admin-section {
    background: #171717;
    border: 1px solid #262626;
}

[data-theme="dark"] .admin-link:hover {
    background: rgba(249, 115, 22, 0.1);
}

[data-theme="dark"] .link-icon {
    background: #262626;
}

[data-theme="dark"] .admin-title {
    color: #f5f5f5;
}

[data-theme="dark"] .admin-subtitle {
    color: #a3a3a3;
}

[data-theme="dark"] .link-title {
    color: #e5e5e5;
}

[data-theme="dark"] .link-desc {
    color: #a3a3a3;
}

[data-theme="dark"] .link-arrow {
    color: #525252;
}

[data-theme="dark"] .admin-link:hover .link-arrow {
    color: #f97316;
}

[data-theme="dark"] .admin-link:hover .link-icon {
    background: #f97316;
}

/* Cabezales en tema oscuro - variaciones de naranja */
[data-theme="dark"] .section-header.territorios {
    background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
}

[data-theme="dark"] .section-header.publicadores {
    background: linear-gradient(135deg, #fb923c 0%, #f97316 100%);
}

[data-theme="dark"] .section-header.usuarios {
    background: linear-gradient(135deg, #fdba74 0%, #fb923c 100%);
}

[data-theme="dark"] .section-header.ppoc {
    background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%);
}

[data-theme="dark"] .section-header.reportes {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}

[data-theme="dark"] .section-header.sistema {
    background: linear-gradient(135deg, #78716c 0%, #57534e 100%);
}
</style>

@endsection
