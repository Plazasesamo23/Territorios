@extends('layouts.app')

@section('title', 'S13 - Gestión de Territorios')

@section('content')
<!-- Navegación y acciones en una sola línea -->
<div class="page-nav">
    <div class="page-breadcrumbs">
        <a href="{{ route('dashboard') }}" class="breadcrumb-link">Dashboard</a>
        <span class="breadcrumb-sep">›</span>
        <span class="breadcrumb-current">S13</span>
    </div>
    
    <div class="page-actions">
        <button class="btn btn-primary">
            📊 Generar Reporte
        </button>
    </div>
</div>

<!-- Contenido principal -->
<div class="card text-center py-8">
    <div class="text-4xl mb-4">📊</div>
    <h3 class="page-title mb-2">Reporte S13</h3>
    <p class="text-muted mb-4">Esta sección está en desarrollo. Aquí podrás generar los reportes S13 mensuales.</p>
    <div class="flex flex-center gap-2">
        <a href="{{ route('dashboard') }}" class="btn btn-secondary">
            ↩️ Volver al Dashboard
        </a>
        <button class="btn btn-primary" disabled>
            📊 Generar Reporte (Próximamente)
        </button>
    </div>
</div>

<!-- Estadísticas básicas para S13 -->
<div class="grid grid-4 mb-6">
    <div class="stat-card">
        <div class="stat-number">{{ \App\Models\Territorio::count() }}</div>
        <div class="stat-label">Total Territorios</div>
    </div>
    <div class="stat-card">
        <div class="stat-number stat-number-green">{{ \App\Models\Territorio::get()->filter(fn($t) => $t->calcularEstado() === 'libre')->count() }}</div>
        <div class="stat-label">Libres</div>
    </div>
    <div class="stat-card">
        <div class="stat-number stat-number-yellow">{{ \App\Models\Registro::whereNull('fecha_entrada')->count() }}</div>
        <div class="stat-label">Asignados</div>
    </div>
    <div class="stat-card">
        <div class="stat-number stat-number-purple">{{ \App\Models\Publicador::where('activo', true)->count() }}</div>
        <div class="stat-label">Publicadores</div>
    </div>
</div>
@endsection 