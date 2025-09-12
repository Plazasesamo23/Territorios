@extends('layouts.app')

@section('title', 'Publicadores - Gestión de Territorios')

@section('content')
<!-- Navegación minimalista -->
<nav class="page-nav">
    <div class="page-breadcrumbs">
        <a href="{{ route('dashboard') }}" class="breadcrumb-link">Dashboard</a>
        <span class="breadcrumb-sep">›</span>
        <span class="breadcrumb-current">Publicadores</span>
    </div>
    <div class="page-actions">
        <a href="{{ route('publicadores.create') }}" class="btn btn-primary">
            ➕ Nuevo Publicador
        </a>
    </div>
</nav>

<!-- Lista minimalista tipo tabla -->
<div class="card">
    <div class="card-title">
        Publicadores
        <span class="badge badge-blue">{{ $publicadores->count() }}</span>
    </div>

    @if($publicadores->count() > 0)
        <!-- Header de tabla -->
        <div class="table-header table-5-col">
            <div>NOMBRE</div>
            <div>APELLIDOS</div>
            <div>TELÉFONO</div>
            <div>ESTADO</div>
        </div>

        <!-- Filas de datos -->
        @foreach($publicadores as $publicador)
            <a href="{{ route('publicadores.show', $publicador) }}" class="table-row table-5-col">
                <!-- Nombre -->
                <div>
                    <span class="font-bold text-dark">{{ $publicador->nombre }}</span>
                </div>
                
                <!-- Apellidos -->
                <div>
                    <span class="table-cell-bold text-muted">{{ $publicador->apellidos }}</span>
                </div>
                
                <!-- Teléfono -->
                <div>
                    <span class="table-cell-small">{{ $publicador->telefono }}</span>
                </div>
                
                <!-- Estado -->
                <div class="table-cell-center">
                    @if($publicador->activo)
                        <span class="status-active">ACTIVO</span>
                    @else
                        <span class="status-inactive">INACTIVO</span>
                    @endif
                </div>
            </a>
        @endforeach

    @else
        <!-- Estado vacío -->
        <div class="text-center py-8">
            <div class="text-4xl mb-4 text-gray">👥</div>
            <h3 class="card-subtitle mb-2 text-muted">No hay publicadores registrados</h3>
            <p class="text-muted mb-4">Comienza agregando publicadores para gestionar territorios.</p>
            <a href="{{ route('publicadores.create') }}" class="btn btn-primary">
                Crear Primer Publicador
            </a>
        </div>
    @endif
</div>
@endsection 