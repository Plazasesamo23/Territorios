@extends('layouts.app')

@section('title', 'Administración')

@section('content')

<div class="admin-page">
    <h1 class="page-title">Administración</h1>
    <p class="page-subtitle">Gestiona todos los aspectos de tu congregación</p>

    <div class="admin-grid">
        <!-- Territorios -->
        <div class="admin-section">
            <div class="section-header">Territorios</div>
            <a href="{{ route('territorios.index') }}" class="admin-link">Ver Territorios</a>
            <a href="{{ route('registros.index') }}" class="admin-link">Asignaciones Activas</a>
            <a href="{{ route('creador-territorios.index') }}" class="admin-link">Creador de Territorios</a>
        </div>

        <!-- Publicadores -->
        <div class="admin-section">
            <div class="section-header">Publicadores</div>
            <a href="{{ route('publicadores.index') }}" class="admin-link">Ver Publicadores</a>
            <a href="{{ route('grupos-predicacion.index') }}" class="admin-link">Grupos de Predicación</a>
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
            <a href="{{ route('s13.importar') }}" class="admin-link">Añadir registros a mano (S-13)</a>
        </div>

        <!-- Sistema -->
        <div class="admin-section">
            <div class="section-header">Sistema</div>
            <a href="{{ route('configuracion') }}" class="admin-link">Configuración</a>
            <a href="{{ route('cambiar-usuario.index') }}" class="admin-link">Cambiar Usuario</a>
        </div>
    </div>
</div>

@endsection
