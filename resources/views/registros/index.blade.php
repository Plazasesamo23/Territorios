@extends('layouts.app')

@section('title', 'Registros - Gestión de Territorios')

@section('content')
<!-- Navegación minimalista -->
<nav class="page-nav">
    <div class="page-breadcrumbs">
        <a href="{{ route('dashboard') }}" class="breadcrumb-link">Dashboard</a>
        <span class="breadcrumb-sep">›</span>
        <span class="breadcrumb-current">Registros</span>
    </div>
    <div class="page-actions">
        <a href="{{ route('registros.create') }}" class="btn btn-primary">
            ➕ Nuevo Registro
        </a>
        <a href="{{ route('registros.archivados') }}" class="btn btn-secondary">
            📚 Ver Archivados
        </a>
    </div>
</nav>

<!-- Estadísticas compactas -->
<div class="grid grid-4 mb-4">
    <div class="stat-card">
        <div class="stat-number stat-number-purple">{{ $estadisticas['activos_total'] }}</div>
        <div class="stat-label">Activos</div>
    </div>
    <div class="stat-card">
        <div class="stat-number text-red">{{ $estadisticas['atrasados'] }}</div>
        <div class="stat-label">Atrasados</div>
    </div>
    <div class="stat-card">
        <div class="stat-number stat-number-purple">{{ $estadisticas['territorios_libres'] }}</div>
        <div class="stat-label">Libres</div>
    </div>
    <div class="stat-card">
        <div class="stat-number stat-number-yellow">{{ round($estadisticas['promedio_dias']) }}</div>
        <div class="stat-label">Promedio</div>
    </div>
</div>

@php
    // Ordenar registros activos según criterios especificados
    $registrosOrdenados = $registrosActivos->sortBy(function($registro) {
        $estado = $registro->territorio->calcularEstado();
        $fechaSalida = $registro->fecha_salida->timestamp;
        
        // Prioridad: 1=Atrasados, 2=Activos
        if ($estado === 'atrasado') {
            return '1_' . (9999999999 - $fechaSalida); // Más recientes primero
        } elseif ($estado === 'activo') {
            return '2_' . (9999999999 - $fechaSalida); // Más recientes primero
        } else {
            return '3_' . $fechaSalida; // Más antiguos primero para otros estados
        }
    });
@endphp

<!-- Lista minimalista tipo tabla -->
<div class="card">
    <div class="card-title">
        Registros Activos
        <span class="badge badge-blue">{{ $registrosActivos->count() }}</span>
    </div>

    @if($registrosActivos->count() > 0)
        <!-- Header de tabla -->
        <div class="table-header table-7-col">
            <div>FECHA SALIDA</div>
            <div>TERRITORIO</div>
            <div>PUBLICADOR</div>
            <div>DÍAS</div>
            <div>DEVOLUCIÓN</div>
            <div>ESTADO</div>
        </div>

        <!-- Filas de datos -->
        @foreach($registrosOrdenados as $index => $registro)
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
                    <span style="font-size: 1.5rem; font-weight: 700; color: #3b82f6;">{{ $registro->territorio->numero }}</span>
                </div>
                
                <!-- Publicador -->
                <div>
                    <span style="font-weight: 500;">{{ $registro->publicador->nombre_completo }}</span>
                    <div style="font-size: 0.75rem; color: #9ca3af; margin-top: 0.125rem;">{{ $registro->publicador->telefono }}</div>
                </div>
                
                <!-- Días -->
                <div style="text-align: center;">
                    <span style="font-weight: 600; color: #374151;">{{ round($registro->fecha_salida->diffInDays(now())) }}</span>
                </div>
                
                <!-- Fecha Devolución -->
                <div style="text-align: center;">
                    @if($registro->fecha_entrada)
                        <span style="font-size: 0.875rem; color: #059669;">{{ $registro->fecha_entrada->format('d/m/Y') }}</span>
                    @else
                        <span style="font-size: 0.875rem; color: #ef4444; font-weight: 600;">PENDIENTE</span>
                    @endif
                </div>
                
                <!-- Estado -->
                <div style="text-align: center;">
                    @php $estado = $registro->territorio->calcularEstado(); @endphp
                    @if($estado === 'activo')
                        <span style="background: #dbeafe; color: #1e40af; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem; font-weight: 600;">ACTIVO</span>
                    @elseif($estado === 'atrasado')
                        <span style="background: #fef2f2; color: #dc2626; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem; font-weight: 600;">ATRASADO</span>
                    @endif
                </div>
            </a>
        @endforeach

    @else
        <!-- Estado vacío -->
        <div class="text-center" style="padding: 3rem;">
            <div style="font-size: 3rem; margin-bottom: 1rem; color: #d1d5db;">📋</div>
            <h3 style="font-size: 1.25rem; margin-bottom: 0.5rem; color: #6b7280;">No hay registros activos</h3>
            <p style="color: #9ca3af; margin-bottom: 1.5rem;">Todos los territorios están libres o archivados.</p>
            <a href="{{ route('registros.create') }}" class="btn btn-primary">
                Crear Primer Registro
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
</style>
@endsection 