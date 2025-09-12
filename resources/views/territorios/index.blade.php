@extends('layouts.app')

@section('title', 'Territorios - Gestión de Territorios')

@section('content')
<!-- Navegación y acciones en una sola línea -->
<div class="page-nav">
    <div class="page-breadcrumbs">
        <a href="{{ route('dashboard') }}" class="breadcrumb-link">Dashboard</a>
        <span class="breadcrumb-sep">›</span>
        <span class="breadcrumb-current">Territorios</span>
    </div>
    
    <div class="page-actions">
        <a href="{{ route('territorios.create') }}" class="btn btn-primary">
            ➕ Nuevo Territorio
        </a>
    </div>
</div>

<!-- Estadísticas rápidas - Funcionan como filtros -->
<div class="grid grid-5 mb-4">
    <a href="{{ route('territorios.index') }}" class="stat-card filter-stat-btn {{ !request('estado') ? 'active' : '' }}">
        <div class="stat-number">{{ $allTerritorios->count() }}</div>
        <div class="stat-label">Total</div>
    </a>
    <a href="{{ route('territorios.index', ['estado' => 'libre']) }}" class="stat-card filter-stat-btn {{ request('estado') == 'libre' ? 'active' : '' }}">
        <div class="stat-number" style="color: #10b981;">{{ $estadisticas['libre'] }}</div>
        <div class="stat-label">Libres</div>
    </a>
    <a href="{{ route('territorios.index', ['estado' => 'activo']) }}" class="stat-card filter-stat-btn {{ request('estado') == 'activo' ? 'active' : '' }}">
        <div class="stat-number" style="color: #3b82f6;">{{ $estadisticas['activo'] }}</div>
        <div class="stat-label">Activos</div>
    </a>
    <a href="{{ route('territorios.index', ['estado' => 'atrasado']) }}" class="stat-card filter-stat-btn {{ request('estado') == 'atrasado' ? 'active' : '' }}">
        <div class="stat-number" style="color: #ef4444;">{{ $estadisticas['atrasado'] }}</div>
        <div class="stat-label">Atrasados</div>
    </a>
    <a href="{{ route('territorios.index', ['estado' => 'archivo']) }}" class="stat-card filter-stat-btn {{ request('estado') == 'archivo' ? 'active' : '' }}">
        <div class="stat-number" style="color: #6b7280;">{{ $estadisticas['archivo'] }}</div>
        <div class="stat-label">En Archivo</div>
    </a>
</div>



<!-- Grid de territorios -->
@if($territorios->count() > 0)
    <div class="territorio-grid">
        @foreach($territorios as $territorio)
        <div class="territorio-card">
            <!-- Columna 1: Imagen -->
            <div class="territorio-image-half">
                @if($territorio->tieneImagen())
                    <img src="{{ $territorio->getImagenUrl() }}" 
                         alt="Territorio {{ $territorio->numero }}" 
                         loading="lazy">
                @else
                    <div class="territorio-image-placeholder">
                        🗺️
                    </div>
                @endif
            </div>

            <!-- Columna 2: Contenido principal -->
            <div class="territorio-content">
                <!-- Header reorganizado: Desktop vs Móvil -->
                <div>
                    <!-- Línea 1: Número + Estado (Desktop) / Número + Nombre (Móvil) -->
                    <div class="territorio-header-superior">
                        <div class="territorio-numero-destacado">{{ $territorio->numero }}</div>
                        <div class="territorio-info-superior">
                            <!-- Nombre: visible en móvil, oculto en desktop -->
                            <div class="territorio-nombre-container">
                                @if($territorio->nombre)
                                    <div class="territorio-nombre territorio-nombre-mobile">{{ $territorio->nombre }}</div>
                                @endif
                            </div>
                            <!-- Estado: visible en desktop, oculto en móvil (se mueve a columna 3) -->
                            <div class="territorio-badge-estado territorio-badge-desktop
                                @if($territorio->calcularEstado() == 'libre') badge-green
                                @elseif($territorio->calcularEstado() == 'activo') badge-blue
                                @elseif($territorio->calcularEstado() == 'atrasado') badge-red
                                @else badge-gray
                                @endif">
                                @if($territorio->calcularEstado() == 'libre') Libre
                                @elseif($territorio->calcularEstado() == 'activo') Activo
                                @elseif($territorio->calcularEstado() == 'atrasado') Atrasado
                                @else En Archivo
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Línea 2: Nombre y Descripción (Desktop) -->
                    <div class="territorio-content-desktop">
                        @if($territorio->nombre)
                            <div class="territorio-nombre-desktop">{{ $territorio->nombre }}</div>
                        @endif
                        @if($territorio->descripcion)
                            <div class="territorio-description">{{ $territorio->descripcion }}</div>
                        @endif
                    </div>
                </div>

                <!-- Acciones en grid -->
                <div class="territorio-actions-grid">
                    <button type="button" onclick="window.location.href='{{ route('territorios.show', $territorio) }}'" class="btn-icon">
                        👁️ Ver
                    </button>
                    
                    <button type="button" onclick="window.location.href='{{ route('registros.create') }}?territorio_id={{ $territorio->id }}'" class="btn-icon">
                        📋 Registrar
                    </button>
                </div>
            </div>

            <!-- Columna 3: Estado en móvil -->
            <div class="territorio-badge-estado territorio-badge-mobile
                @if($territorio->calcularEstado() == 'libre') badge-green
                @elseif($territorio->calcularEstado() == 'activo') badge-blue
                @elseif($territorio->calcularEstado() == 'atrasado') badge-red
                @else badge-gray
                @endif">
                @if($territorio->calcularEstado() == 'libre') Libre
                @elseif($territorio->calcularEstado() == 'activo') Activo
                @elseif($territorio->calcularEstado() == 'atrasado') Atrasado
                @else En Archivo
                @endif
            </div>
        </div>
        @endforeach
    </div>

    <!-- Paginación simple -->
    @if($territorios->hasPages())
    <div class="card text-center">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <div class="text-muted">
                Mostrando {{ $territorios->firstItem() }}-{{ $territorios->lastItem() }} de {{ $territorios->total() }} territorios
            </div>
            
            <div style="display: flex; gap: 0.5rem;">
                @if($territorios->previousPageUrl())
                    <a href="{{ $territorios->previousPageUrl() }}" class="btn btn-secondary">← Anterior</a>
                @endif
                
                <span class="btn" style="background: #e5e7eb; color: #374151;">
                    Página {{ $territorios->currentPage() }} de {{ $territorios->lastPage() }}
                </span>
                
                @if($territorios->nextPageUrl())
                    <a href="{{ $territorios->nextPageUrl() }}" class="btn btn-secondary">Siguiente →</a>
                @endif
            </div>
        </div>
    </div>
    @endif
@else
    <!-- Estado vacío -->
    <div class="card text-center" style="padding: 3rem;">
        <div style="font-size: 4rem; margin-bottom: 1rem;">🗺️</div>
        <h3 style="font-size: 1.5rem; margin-bottom: 0.5rem; color: #1f2937;">No hay territorios disponibles</h3>
        <p class="text-muted mb-4">Comienza agregando el primer territorio a tu sistema.</p>
        <a href="{{ route('territorios.create') }}" class="btn btn-primary">
            ➕ Crear Primer Territorio
        </a>
    </div>
@endif

<script>
// Navegación simplificada con 2 acciones principales:
// - "Ver": Lleva a la página completa del territorio (editar, eliminar, historial, etc.)
// - "Registrar": Lleva a la página de gestión de registros (asignar, devolver, WhatsApp, etc.)
</script>

<style>
/* Grid responsive para territorios */
.territorio-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

/* Estructura del header reorganizado */
.territorio-header-superior {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 0.5rem;
}

.territorio-info-superior {
    flex: 1;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    min-width: 0; /* Para truncate */
}

.territorio-nombre-container {
    flex: 1;
    min-width: 0; /* Para truncate */
}

.territorio-nombre {
    font-weight: 600;
    color: #374151;
    font-size: 1rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Desktop: nombre en línea superior oculto, contenido en línea 2 */
.territorio-nombre-mobile {
    display: none;
}

.territorio-content-desktop {
    display: block;
}

.territorio-nombre-desktop {
    font-weight: 600;
    color: #374151;
    font-size: 1rem;
    margin-bottom: 0.25rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.territorio-description {
    color: #6b7280;
    font-size: 0.85rem;
    line-height: 1.4;
    margin-bottom: 0.75rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Estados: Desktop vs Mobile */
.territorio-badge-mobile {
    display: none;
}

.territorio-badge-desktop {
    display: inline-flex;
}

@media (max-width: 768px) {
    .territorio-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    /* MEJORA MÓVIL: 45/55 ratio - Imagen más grande */
    .territorio-card {
        flex-direction: row;
        min-height: 130px;
        display: grid;
        grid-template-columns: 140px 1fr auto; /* 45% imagen + contenido + estado */
        gap: 0.75rem;
        align-items: center;
    }
    
    .territorio-image-half {
        width: 140px;
        height: 110px;
        padding: 0.25rem;
        flex-shrink: 0;
    }
    
    .territorio-image-half img {
        border: 1px solid #fff;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
    }
    
    .territorio-content {
        width: auto;
        padding: 0.75rem 0;
        min-width: 0; /* Para text truncate */
    }
    
    /* Header reorganizado para móvil */
    .territorio-header-superior {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.25rem;
        margin-bottom: 0.25rem;
    }
    
    .territorio-info-superior {
        width: 100%;
        flex-direction: column;
        align-items: flex-start;
        gap: 0.25rem;
        justify-content: flex-start;
    }
    
    .territorio-nombre-container {
        width: 100%;
    }
    
    /* CÍRCULO PROTEGIDO: Tamaño fijo sin deformación */
    .territorio-numero-destacado {
        width: 2.25rem !important;
        height: 2.25rem !important;
        font-size: 1rem !important;
        font-weight: 800 !important;
        flex-shrink: 0; /* CLAVE: No se achata */
        border-radius: 50% !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
    
    /* Móvil: ocultar contenido desktop y mostrar móvil */
    .territorio-content-desktop {
        display: none !important;
    }
    
    .territorio-nombre-mobile {
        display: block !important;
        font-size: 0.85rem;
        font-weight: 600;
        max-width: 150px;
        line-height: 1.2;
    }
    
    /* Descripción visible pero compacta */
    .territorio-description {
        font-size: 0.75rem;
        line-height: 1.3;
        color: #9ca3af;
        -webkit-line-clamp: 1; /* Solo 1 línea en móvil */
        margin-top: 0.25rem;
        margin-bottom: 0.5rem;
    }
    
    /* Alternar visibilidad de estados */
    .territorio-badge-desktop {
        display: none !important;
    }
    
    .territorio-badge-mobile {
        display: flex !important;
        writing-mode: vertical-lr;
        text-orientation: mixed;
        padding: 0.5rem 0.3rem;
        font-size: 0.65rem;
        font-weight: 600;
        border-width: 1px;
        align-self: stretch;
        align-items: center;
        justify-content: center;
        min-width: 50px;
    }
    
    .territorio-actions-grid {
        grid-template-columns: 1fr 1fr;
        gap: 0.4rem;
        margin-top: 0.5rem;
    }
    
    .btn-icon {
        padding: 0.4rem 0.6rem;
        font-size: 0.7rem;
        border-radius: 6px;
    }
}

@media (max-width: 480px) {
    /* MÓVIL PEQUEÑO: 35/65 ratio - Imagen más visible */
    .territorio-card {
        grid-template-columns: 110px 1fr auto; /* 35% imagen + contenido + estado */
        gap: 0.5rem;
        min-height: 120px;
    }
    
    .territorio-image-half {
        width: 110px;
        height: 90px;
    }
    
    /* Círculo ligeramente más pequeño pero protegido */
    .territorio-numero-destacado {
        width: 2rem !important;
        height: 2rem !important;
        font-size: 0.9rem !important;
    }
    
    .territorio-nombre-mobile {
        font-size: 0.8rem !important;
        max-width: 120px !important;
    }
    
    .territorio-description {
        font-size: 0.7rem;
    }
    
    .territorio-badge-mobile {
        font-size: 0.6rem !important;
        min-width: 45px !important;
        padding: 0.4rem 0.25rem !important;
    }
    
    /* Para pantallas muy pequeñas: botones verticales */
    .territorio-actions-grid {
        grid-template-columns: 1fr;
        gap: 0.3rem;
    }
    
    .btn-icon {
        justify-content: center;
    }
}

/* Badges específicos para estados */
.badge-blue {
    background: #dbeafe;
    color: #1e40af;
}

.badge-green {
    background: #dcfce7;
    color: #166534;
}

.badge-yellow {
    background: #fef3c7;
    color: #92400e;
}

.badge-red {
    background: #fee2e2;
    color: #991b1b;
}

.badge-gray {
    background: #f3f4f6;
    color: #374151;
}
</style>
@endsection 