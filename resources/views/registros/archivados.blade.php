@extends('layouts.app')

@section('title', 'Registros Archivados - Gestión de Territorios')

@section('content')
<!-- Navegación minimalista -->
<nav class="page-nav">
    <div class="page-breadcrumbs">
        <a href="{{ route('dashboard') }}" class="breadcrumb-link">Dashboard</a>
        <span class="breadcrumb-separator">›</span>
        <a href="{{ route('registros.index') }}" class="breadcrumb-link">Registros</a>
        <span class="breadcrumb-separator">›</span>
        <span class="breadcrumb-current">Archivados</span>
    </div>
    <div class="page-actions">
        <a href="{{ route('registros.index') }}" class="btn btn-primary">
            ← Registros Activos
        </a>
        <a href="{{ route('registros.create') }}" class="btn btn-secondary">
            ➕ Nuevo Registro
        </a>
    </div>
</nav>

<!-- Estadísticas compactas -->
<div class="grid grid-4 mb-4">
    <div class="stat-card">
        <div class="stat-number" style="color: #059669;">{{ $estadisticas['total_archivados'] }}</div>
        <div class="stat-label">Total Archivados</div>
    </div>
    <div class="stat-card">
        <div class="stat-number" style="color: #f59e0b;">{{ $estadisticas['promedio_dias'] }}</div>
        <div class="stat-label">Promedio Días</div>
    </div>
    <div class="stat-card">
        <div class="stat-number" style="color: #10b981;">{{ $estadisticas['min_dias'] }}</div>
        <div class="stat-label">Mínimo</div>
    </div>
    <div class="stat-card">
        <div class="stat-number" style="color: #ef4444;">{{ $estadisticas['max_dias'] }}</div>
        <div class="stat-label">Máximo</div>
    </div>
</div>

<!-- Lista minimalista tipo tabla -->
<div class="card">
    <div class="card-title">
        Registros Archivados
        <span class="badge badge-green">{{ $registrosArchivados->count() }}</span>
    </div>

    @if($registrosArchivados->count() > 0)
        <!-- Header de tabla -->
        <div style="display: grid; grid-template-columns: 120px 120px 1fr 1fr 120px 120px 120px; gap: 1rem; padding: 0.75rem 1rem; background: #f8fafc; border-bottom: 2px solid #e5e7eb; font-weight: 600; font-size: 0.875rem; color: #6b7280;">
            <div>FECHA SALIDA</div>
            <div>TERRITORIO</div>
            <div>PUBLICADOR</div>
            <div>DÍAS</div>
            <div>DEVOLUCIÓN</div>
            <div>ESTADO</div>
        </div>

        <!-- Filas de datos -->
        @foreach($registrosArchivados as $index => $registro)
            <a href="{{ route('registros.show', $registro) }}" 
               style="display: grid; grid-template-columns: 120px 120px 1fr 1fr 120px 120px 120px; gap: 1rem; padding: 0.75rem 1rem; border-bottom: 1px solid #e5e7eb; text-decoration: none; color: inherit; {{ $index % 2 == 0 ? 'background: #ffffff;' : 'background: #f9fafb;' }}"
               onmouseover="this.style.backgroundColor='#f1f5f9'"
               onmouseout="this.style.backgroundColor='{{ $index % 2 == 0 ? '#ffffff' : '#f9fafb' }}'">
                
                <!-- Fecha Salida -->
                <div style="font-size: 0.875rem; color: #6b7280;">
                    {{ $registro->fecha_salida->format('d/m/Y') }}
                </div>
                
                <!-- Territorio -->
                <div style="text-align: center;">
                    <span style="font-size: 1.5rem; font-weight: 700; color: #6b7280;">{{ $registro->territorio->numero }}</span>
                </div>
                
                <!-- Publicador -->
                <div>
                    <span style="font-weight: 500;" class="{{ $registro->publicador->es_precursor ? 'text-precursor' : '' }}">
                        {{ $registro->publicador->nombre_completo }}
                        @if($registro->publicador->es_precursor)
                        <span class="precursor-badge-sm">PR</span>
                        @endif
                    </span>
                    <div style="font-size: 0.75rem; color: #9ca3af; margin-top: 0.125rem;">{{ $registro->publicador->telefono }}</div>
                </div>
                
                <!-- Días -->
                <div style="text-align: center;">
                    <span style="font-weight: 600; color: #059669;">{{ round($registro->duracion_dias) }}</span>
                </div>
                
                <!-- Fecha Devolución -->
                <div style="text-align: center;">
                    <span style="font-size: 0.875rem; color: #059669;">{{ $registro->fecha_entrada->format('d/m/Y') }}</span>
                </div>
                
                <!-- Estado -->
                <div style="text-align: center;">
                    <span style="background: #dcfce7; color: #166534; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem; font-weight: 600;">COMPLETADO</span>
                </div>
            </a>
        @endforeach

    @else
        <!-- Estado vacío -->
        <div class="text-center" style="padding: 3rem;">
            <div style="font-size: 3rem; margin-bottom: 1rem; color: #d1d5db;">📚</div>
            <h3 style="font-size: 1.25rem; margin-bottom: 0.5rem; color: #6b7280;">No hay registros archivados</h3>
            <p style="color: #9ca3af; margin-bottom: 1.5rem;">Los registros aparecerán aquí cuando se devuelvan territorios.</p>
            <a href="{{ route('registros.index') }}" class="btn btn-primary">
                Ver Registros Activos
            </a>
        </div>
    @endif
</div>

<style>
/* Mejoras para hover en filas */
.card a:hover {
    transform: none !important;
    box-shadow: none !important;
}

/* Responsive para pantallas pequeñas */
@media (max-width: 768px) {
    .card > a {
        display: block !important;
        padding: 1rem !important;
    }
    
    .card > div:first-of-type {
        display: none; /* Ocultar header en móvil */
    }
    
    .card a > div {
        margin-bottom: 0.5rem;
    }
    
    .card a > div:last-child {
        margin-bottom: 0;
    }
}

/* Precursor styles */
.text-precursor {
    color: #16a34a !important;
}

.precursor-badge-sm {
    display: inline-block;
    padding: 0.1rem 0.35rem;
    background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
    color: white;
    font-size: 0.6rem;
    font-weight: 700;
    border-radius: 4px;
    margin-left: 0.4rem;
    vertical-align: middle;
}
</style>
@endsection 