@extends('layouts.app')

@section('title', 'Registros Archivados - Gestión de Territorios')

@section('content')
<!-- Navegación minimalista -->
<nav class="page-nav">
    <div class="page-breadcrumbs">
        <a href="{{ route('dashboard') }}" class="breadcrumb-link">Inicio</a>
        <span class="breadcrumb-separator">›</span>
        <a href="{{ route('registros.index') }}" class="breadcrumb-link">Asignaciones</a>
        <span class="breadcrumb-separator">›</span>
        <span class="breadcrumb-current">Archivadas</span>
    </div>
    <div class="page-actions">
        <a href="{{ route('registros.index') }}" class="btn btn-primary">
            ← Asignaciones Activas
        </a>
        <a href="{{ route('registros.create') }}" class="btn btn-secondary">
            ➕ Asignar
        </a>
    </div>
</nav>

<!-- Estadísticas compactas -->
<div class="grid grid-4 mb-4">
    <div class="stat-card">
        <div class="stat-number" style="color: #3d5a8a;">{{ $estadisticas['total_archivados'] }}</div>
        <div class="stat-label">Total Archivados</div>
    </div>
    <div class="stat-card">
        <div class="stat-number" style="color: #4a6da7;">{{ $estadisticas['promedio_dias'] }}</div>
        <div class="stat-label">Promedio Días</div>
    </div>
    <div class="stat-card">
        <div class="stat-number" style="color: #4a6da7;">{{ $estadisticas['min_dias'] }}</div>
        <div class="stat-label">Mínimo</div>
    </div>
    <div class="stat-card">
        <div class="stat-number" style="color: #495057;">{{ $estadisticas['max_dias'] }}</div>
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
        <div class="archivados-header" style="display: grid; grid-template-columns: 110px 110px 1fr 90px 130px 120px; gap: 1rem; padding: 0.75rem 1rem; background: #1a1d21; border-bottom: 2px solid #2d3339; font-weight: 600; font-size: 0.875rem; color: #9ca3af;">
            <div>FECHA SALIDA</div>
            <div>TERRITORIO</div>
            <div>PUBLICADOR</div>
            <div>DÍAS</div>
            <div>DEVOLUCIÓN</div>
            <div>ESTADO</div>
        </div>

        <!-- Filas de datos -->
        @foreach($registrosArchivados as $index => $registro)
            <a href="{{ route('registros.show', $registro) }}" class="archivado-row"
               style="display: grid; grid-template-columns: 110px 110px 1fr 90px 130px 120px; gap: 1rem; padding: 0.75rem 1rem; border-bottom: 1px solid #2d3339; text-decoration: none; color: inherit; {{ $index % 2 == 0 ? 'background: #171717;' : 'background: #1f2024;' }}"
               onmouseover="this.style.backgroundColor='#262626'"
               onmouseout="this.style.backgroundColor='{{ $index % 2 == 0 ? '#171717' : '#1f2024' }}'">

                <!-- Fecha Salida -->
                <div class="col-salida" data-label="Salida" style="font-size: 0.875rem; color: #9ca3af;">
                    {{ $registro->fecha_salida->format('d/m/Y') }}
                </div>

                <!-- Territorio -->
                <div class="col-territorio" style="text-align: center;">
                    <span style="font-size: 1.5rem; font-weight: 700; color: #e5e7eb;">{{ $registro->territorio->numero_completo }}</span>
                </div>

                <!-- Publicador -->
                <div class="col-publicador">
                    <span style="font-weight: 500;" class="{{ $registro->publicador->es_precursor ? 'text-precursor' : '' }}">
                        {{ $registro->publicador->nombre_completo }}
                        @if($registro->publicador->es_precursor)
                        <span class="precursor-badge-sm">PR</span>
                        @endif
                    </span>
                    <div style="font-size: 0.75rem; color: #9ca3af; margin-top: 0.125rem;">{{ $registro->publicador->telefono }}</div>
                </div>

                <!-- Días -->
                <div class="col-dias" data-label="Días" style="text-align: center;">
                    <span style="font-weight: 600; color: #3d5a8a;">{{ round($registro->duracion_dias) }}</span>
                </div>

                <!-- Fecha Devolución -->
                <div class="col-devolucion" data-label="Devuelto" style="text-align: center;">
                    <span style="font-size: 0.875rem; color: #3d5a8a;">{{ $registro->fecha_entrada->format('d/m/Y') }}</span>
                </div>

                <!-- Estado -->
                <div class="col-estado" style="text-align: center;">
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

/* Responsive para pantallas pequeñas: cada registro es una tarjeta legible */
@media (max-width: 768px) {
    .archivados-header { display: none !important; }

    .archivado-row {
        display: grid !important;
        grid-template-columns: auto 1fr !important;
        grid-template-areas:
            "territorio publicador"
            "fechas fechas"
            "dias estado" !important;
        gap: 0.25rem 0.75rem !important;
        align-items: center;
        padding: 0.75rem 1rem !important;
    }
    .archivado-row .col-territorio { grid-area: territorio; text-align: left !important; }
    .archivado-row .col-publicador { grid-area: publicador; }
    .archivado-row .col-salida { grid-area: fechas; }
    .archivado-row .col-devolucion { grid-area: fechas; text-align: right !important; }
    .archivado-row .col-dias { grid-area: dias; text-align: left !important; }
    .archivado-row .col-estado { grid-area: estado; text-align: right !important; }

    .archivado-row [data-label]::before {
        content: attr(data-label) ": ";
        color: #9ca3af;
        font-size: 0.75rem;
    }
    .archivado-row .col-salida { font-size: 0.85rem; }
}

/* Precursor styles */
.text-precursor {
    color: #3d5a8a !important;
}

.precursor-badge-sm {
    display: inline-block;
    padding: 0.1rem 0.35rem;
    background: linear-gradient(135deg, #4a6da7 0%, #3d5a8a 100%);
    color: white;
    font-size: 0.6rem;
    font-weight: 700;
    border-radius: 4px;
    margin-left: 0.4rem;
    vertical-align: middle;
}
</style>
@endsection 