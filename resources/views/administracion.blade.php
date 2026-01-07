@extends('layouts.app')

@section('title', 'Administracion')

@section('content')

<div class="admin-page">
    <h1 class="page-title">Administracion</h1>
    <p class="page-subtitle">Gestiona todos los aspectos de tu congregacion</p>

    <div class="admin-grid">
        <!-- Territorios -->
        <div class="admin-section">
            <div class="section-header">Territorios</div>
            <a href="{{ route('territorios.create', ['tipo' => 'normal']) }}" class="admin-link">Anadir Territorio</a>
            <a href="{{ route('territorios.create', ['tipo' => 'campana']) }}" class="admin-link">Territorio Campana</a>
            <a href="{{ route('territorios.create', ['tipo' => 'negocios']) }}" class="admin-link">Territorio Negocios</a>
            <a href="{{ route('registros.archivados') }}" class="admin-link">Registros Archivados</a>
            <a href="{{ route('creador-territorios.index') }}" class="admin-link">Creador de Territorios</a>
        </div>

        <!-- Publicadores -->
        <div class="admin-section">
            <div class="section-header">Publicadores</div>
            <a href="{{ route('publicadores.index') }}" class="admin-link">Ver Publicadores</a>
            <a href="{{ route('publicadores.create') }}" class="admin-link">Crear Publicador</a>
            <a href="{{ route('grupos-predicacion.index') }}" class="admin-link">Grupos de Predicacion</a>
        </div>

        <!-- Usuarios -->
        <div class="admin-section">
            <div class="section-header">Usuarios</div>
            <a href="{{ route('usuarios.index') }}" class="admin-link">Gestionar Usuarios</a>
            <a href="{{ route('usuarios.create') }}" class="admin-link">Crear Usuario</a>
        </div>

        <!-- PPOC -->
        @if(Auth::user()->canAccessPPOC())
        <div class="admin-section">
            <div class="section-header">PPOC</div>
            <a href="{{ route('ppoc.turnos.index') }}" class="admin-link">Turnos Predeterminados</a>
            <a href="{{ route('ppoc.turnos.create') }}" class="admin-link">Crear Turno</a>
            <a href="{{ route('ppoc.aprobados') }}" class="admin-link">Publicadores Aprobados</a>
            <a href="{{ route('ppoc.disponibilidad.por-turno') }}" class="admin-link">Disponibilidades</a>
        </div>
        @endif

        <!-- S-13 -->
        <div class="admin-section">
            <div class="section-header">S-13</div>
            <a href="{{ route('s13.index') }}" class="admin-link">Ver S-13</a>
            <a href="{{ route('s13.importar') }}" class="admin-link">Importar S-13</a>
        </div>

        <!-- Sistema -->
        <div class="admin-section">
            <div class="section-header">Sistema</div>
            <a href="{{ route('configuracion') }}" class="admin-link">Configuracion</a>
            <a href="{{ route('cambiar-usuario.index') }}" class="admin-link">Cambiar Usuario</a>
        </div>
    </div>
</div>

<style>
.admin-page {
    max-width: 1200px;
    margin: 0 auto;
}

.admin-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
}

.admin-section {
    background: var(--bg-white);
    border-radius: var(--radius);
    padding: 1rem;
}

.section-header {
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 0.75rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid var(--border);
}

.admin-link {
    display: block;
    padding: 0.625rem 0;
    color: var(--text);
    text-decoration: none;
    font-size: 0.9rem;
    border-bottom: 1px solid var(--border);
    transition: color 0.15s;
}

.admin-link:last-child {
    border-bottom: none;
}

.admin-link:hover {
    color: var(--primary);
}

@media (max-width: 768px) {
    .admin-grid {
        grid-template-columns: 1fr;
    }
}
</style>

@endsection
