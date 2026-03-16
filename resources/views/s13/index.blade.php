@extends('layouts.app')

@section('title', 'S13 - Gestión de Territorios')

@section('content')
@if(!Auth::user()->canGenerateS13())
<div class="card" style="text-align: center; padding: 3rem;">
    <div style="font-size: 4rem; margin-bottom: 1rem;">&#128274;</div>
    <h3>Acceso Restringido</h3>
    <p class="text-muted">No tienes permisos para generar el reporte S-13.</p>
    <p class="text-muted">Contacta con el administrador de tu congregacion si necesitas acceso.</p>
    <a href="{{ route('territorios.index') }}" class="btn btn-primary">Volver a Territorios</a>
</div>
@else
<!-- Navegación -->
<div class="page-nav">
    <div class="page-breadcrumbs">
        <a href="{{ route('dashboard') }}" class="breadcrumb-link">Dashboard</a>
        <span class="breadcrumb-sep">›</span>
        <span class="breadcrumb-current">S13</span>
        @if(isset($congregacionActiva))
        <span class="breadcrumb-sep">|</span>
        <span class="text-primary font-medium">{{ $congregacionActiva->nombre }}</span>
        @endif
    </div>
</div>

<!-- Configuración del reporte -->
<div class="card mb-4">
    <div class="card-title">📊 Configuración del Reporte S13</div>
    <form method="GET" action="{{ route('s13.generar-pdf') }}" id="form-s13" target="_blank">
        <div class="form-group mb-4">
            <label for="año" class="form-label">Año de Servicio:</label>
            <select name="año" id="año" class="form-input" style="width: 200px;">
                @for($i = 2020; $i <= now()->year + 2; $i++)
                    <option value="{{ $i }}" {{ $i == $estadisticas['año_actual'] ? 'selected' : '' }}>
                        {{ $i }}-{{ $i + 1 }}
                    </option>
                @endfor
            </select>
            <div class="text-small text-muted mt-1">
                El año de servicio va de septiembre a agosto del año siguiente
            </div>
        </div>

        <div class="form-actions">
            <button type="button" onclick="vistaPrevia()" class="btn btn-secondary">
                👁️ Vista Previa
            </button>
            <button type="submit" class="btn btn-primary">
                📄 Generar PDF
            </button>
        </div>
    </form>
</div>

<!-- Información del reporte -->
<div class="card mb-4">
    <div class="card-title">📋 Información del Reporte</div>
    <div class="grid grid-2">
        <div>
            <h4 class="mb-1">Contenido del PDF:</h4>
            <ul style="margin: 0; padding-left: 1.5rem; color: var(--text-muted);">
                <li>{{ $estadisticas['total_territorios'] }} territorios divididos en {{ ceil($estadisticas['total_territorios'] / 20) }} páginas</li>
                <li>20 territorios por página</li>
                <li>Registros del año de servicio seleccionado</li>
                <li>Formato oficial S13</li>
            </ul>
        </div>
        <div>
            <h4 class="mb-1">Datos incluidos:</h4>
            <ul style="margin: 0; padding-left: 1.5rem; color: var(--text-muted);">
                <li>Fecha de asignación</li>
                <li>Nombre del publicador</li>
                <li>Fecha de devolución</li>
                <li>Hasta 4 asignaciones por territorio</li>
            </ul>
        </div>
    </div>
</div>

<!-- Estadísticas básicas para S13 -->
<div class="grid grid-4 mb-6">
    <div class="stat-card">
        <div class="stat-number">{{ $estadisticas['total_territorios'] }}</div>
        <div class="stat-label">Total Territorios</div>
    </div>
    <div class="stat-card">
        <div class="stat-number stat-number-green">{{ $estadisticas['territorios_libres'] }}</div>
        <div class="stat-label">Libres</div>
    </div>
    <div class="stat-card">
        <div class="stat-number stat-number-yellow">{{ $estadisticas['territorios_asignados'] }}</div>
        <div class="stat-label">Asignados</div>
    </div>
    <div class="stat-card">
        <div class="stat-number stat-number-purple">{{ $estadisticas['total_registros'] }}</div>
        <div class="stat-label">Total Registros</div>
    </div>
</div>

<script>
function vistaPrevia() {
    const año = document.getElementById('año').value;
    const url = "{{ route('s13.vista-previa') }}?año=" + año;
    window.open(url, '_blank');
}
</script>
@endif
@endsection
