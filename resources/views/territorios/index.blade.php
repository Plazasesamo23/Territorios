@extends('layouts.app')

@section('title', 'Territorios - Gestion de Territorios')

@section('content')
<!-- Navegacion y acciones -->
<div class="page-nav">
    <div class="page-breadcrumbs">
        <a href="{{ route('dashboard') }}" class="breadcrumb-link">Dashboard</a>
        <span class="breadcrumb-sep">></span>
        <span class="breadcrumb-current">Territorios</span>
    </div>

    <div class="page-actions">
        <!-- Dropdown para crear nuevo territorio -->
        <div class="dropdown">
            <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownCrear" onclick="toggleDropdown('dropdownMenuCrear')">
                + Nuevo Territorio
            </button>
            <div class="dropdown-menu" id="dropdownMenuCrear">
                <a href="{{ route('territorios.create', ['tipo' => 'normal']) }}" class="dropdown-item">
                    <span class="dropdown-icon">&#128203;</span> Territorio Normal
                </a>
                <a href="{{ route('territorios.create', ['tipo' => 'campana']) }}" class="dropdown-item">
                    <span class="dropdown-icon">&#128227;</span> Territorio Campana (C-)
                </a>
                <a href="{{ route('territorios.create', ['tipo' => 'negocios']) }}" class="dropdown-item">
                    <span class="dropdown-icon">&#127970;</span> Territorio Negocios (N-)
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Filtros por tipo de territorio -->
<div class="tipo-filtros mb-4">
    <a href="{{ route('territorios.index', array_merge(request()->except('tipo'), ['tipo' => 'todos'])) }}"
       class="tipo-filtro {{ ($tipoFiltro ?? 'todos') === 'todos' ? 'active' : '' }}">
        <span class="tipo-icono">&#128506;</span>
        <span class="tipo-nombre">Todos</span>
        <span class="tipo-count">{{ $conteoTipos['todos'] ?? 0 }}</span>
    </a>
    <a href="{{ route('territorios.index', array_merge(request()->except('tipo'), ['tipo' => 'normal'])) }}"
       class="tipo-filtro {{ ($tipoFiltro ?? '') === 'normal' ? 'active' : '' }}">
        <span class="tipo-icono">&#128203;</span>
        <span class="tipo-nombre">Normales</span>
        <span class="tipo-count">{{ $conteoTipos['normal'] ?? 0 }}</span>
    </a>
    <a href="{{ route('territorios.index', array_merge(request()->except('tipo'), ['tipo' => 'campana'])) }}"
       class="tipo-filtro tipo-campana {{ ($tipoFiltro ?? '') === 'campana' ? 'active' : '' }}">
        <span class="tipo-icono">&#128227;</span>
        <span class="tipo-nombre">Campana</span>
        <span class="tipo-count">{{ $conteoTipos['campana'] ?? 0 }}</span>
    </a>
    <a href="{{ route('territorios.index', array_merge(request()->except('tipo'), ['tipo' => 'negocios'])) }}"
       class="tipo-filtro tipo-negocios {{ ($tipoFiltro ?? '') === 'negocios' ? 'active' : '' }}">
        <span class="tipo-icono">&#127970;</span>
        <span class="tipo-nombre">Negocios</span>
        <span class="tipo-count">{{ $conteoTipos['negocios'] ?? 0 }}</span>
    </a>
</div>

<!-- Nota informativa sobre tipos -->
@if(($tipoFiltro ?? 'todos') === 'campana' || ($tipoFiltro ?? 'todos') === 'negocios')
<div class="alert-info-tipo mb-4">
    <span class="alert-icon">&#9432;</span>
    <span>Los territorios de <strong>{{ ($tipoFiltro ?? '') === 'campana' ? 'Campana' : 'Negocios' }}</strong> NO se incluyen en el reporte S-13.</span>
</div>
@endif

<!-- Buscador -->
<div class="card mb-4">
    <form method="GET" action="{{ route('territorios.index') }}" class="search-form">
        <div class="search-container">
            <input type="text"
                   name="search"
                   value="{{ request('search') }}"
                   placeholder="Buscar territorio por numero, zona, descripcion..."
                   class="search-input">
            <input type="hidden" name="estado" value="{{ request('estado') }}">
            <input type="hidden" name="tipo" value="{{ $tipoFiltro ?? 'todos' }}">
            <button type="submit" class="search-btn">&#128269;</button>
            @if(request('search'))
                <a href="{{ route('territorios.index', ['estado' => request('estado'), 'tipo' => $tipoFiltro ?? 'todos']) }}" class="search-clear">&#10006;</a>
            @endif
        </div>
    </form>
</div>

<!-- Estadisticas rapidas - Filtros por estado -->
<div class="grid grid-5 mb-4">
    <a href="{{ route('territorios.index', ['search' => request('search'), 'tipo' => $tipoFiltro ?? 'todos']) }}" class="stat-card filter-stat-btn {{ !request('estado') ? 'active' : '' }}">
        <div class="stat-number">{{ $allTerritorios->count() }}</div>
        <div class="stat-label">Total</div>
    </a>
    <a href="{{ route('territorios.index', ['estado' => 'libre', 'search' => request('search'), 'tipo' => $tipoFiltro ?? 'todos']) }}" class="stat-card filter-stat-btn {{ request('estado') == 'libre' ? 'active' : '' }}">
        <div class="stat-number" style="color: #4a6da7;">{{ $estadisticas['libre'] }}</div>
        <div class="stat-label">Libres</div>
    </a>
    <a href="{{ route('territorios.index', ['estado' => 'activo', 'search' => request('search'), 'tipo' => $tipoFiltro ?? 'todos']) }}" class="stat-card filter-stat-btn {{ request('estado') == 'activo' ? 'active' : '' }}">
        <div class="stat-number" style="color: #4a6da7;">{{ $estadisticas['activo'] }}</div>
        <div class="stat-label">Activos</div>
    </a>
    <a href="{{ route('territorios.index', ['estado' => 'atrasado', 'search' => request('search'), 'tipo' => $tipoFiltro ?? 'todos']) }}" class="stat-card filter-stat-btn {{ request('estado') == 'atrasado' ? 'active' : '' }}">
        <div class="stat-number" style="color: #495057;">{{ $estadisticas['atrasado'] }}</div>
        <div class="stat-label">Atrasados</div>
    </a>
    <a href="{{ route('territorios.index', ['estado' => 'archivo', 'search' => request('search'), 'tipo' => $tipoFiltro ?? 'todos']) }}" class="stat-card filter-stat-btn {{ request('estado') == 'archivo' ? 'active' : '' }}">
        <div class="stat-number" style="color: #6b7280;">{{ $estadisticas['archivo'] }}</div>
        <div class="stat-label">En Archivo</div>
    </a>
</div>

<!-- Grid de territorios -->
@if($territorios->count() > 0)
    <div class="territorio-grid">
        @foreach($territorios as $territorio)
        <div class="territorio-card {{ $territorio->tipo !== 'normal' ? 'territorio-especial territorio-' . $territorio->tipo : '' }}">
            <!-- Columna 1: Imagen -->
            <div class="territorio-image-half">
                @if($territorio->tieneImagen())
                    <img src="{{ $territorio->getImagenUrl() }}" referrerpolicy="no-referrer"
                         alt="Territorio {{ $territorio->numero_completo }}"
                         loading="lazy">
                @else
                    <div class="territorio-image-placeholder">
                        @if($territorio->tipo === 'campana')
                            &#128227;
                        @elseif($territorio->tipo === 'negocios')
                            &#127970;
                        @else
                            &#128506;
                        @endif
                    </div>
                @endif
                <!-- Badge de tipo para territorios especiales -->
                @if($territorio->tipo !== 'normal')
                <div class="territorio-tipo-badge {{ $territorio->tipo }}">
                    {{ $territorio->tipo === 'campana' ? 'C' : 'N' }}
                </div>
                @endif
            </div>

            <!-- Columna 2: Contenido principal -->
            <div class="territorio-content">
                <div>
                    <div class="territorio-header-superior">
                        <div class="territorio-numero-destacado {{ $territorio->tipo !== 'normal' ? 'numero-' . $territorio->tipo : '' }}">
                            {{ $territorio->numero_completo }}
                        </div>
                        <div class="territorio-info-superior">
                            <div class="territorio-zona-container">
                                @if($territorio->zona)
                                    <div class="territorio-zona territorio-zona-mobile">{{ $territorio->zona }}</div>
                                @endif
                            </div>
                            <div class="territorio-badge-estado territorio-badge-desktop
                                @if($territorio->estaDisponibleParaAsignar()) badge-green
                                @elseif($territorio->calcularEstado() == 'activo') badge-blue
                                @elseif($territorio->calcularEstado() == 'atrasado') badge-red
                                @else badge-gray
                                @endif">
                                @if($territorio->estaDisponibleParaAsignar()) Disponible
                                @elseif($territorio->calcularEstado() == 'activo') Activo
                                @elseif($territorio->calcularEstado() == 'atrasado') Atrasado
                                @else En Archivo
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="territorio-content-desktop">
                        @if($territorio->zona)
                            <div class="territorio-zona-desktop">{{ $territorio->zona }}</div>
                        @endif
                        @if($territorio->descripcion)
                            <div class="territorio-description">{{ $territorio->descripcion }}</div>
                        @endif
                    </div>
                </div>

                <div class="territorio-actions-grid">
                    <a href="{{ route('territorios.show', $territorio) }}" class="btn-icon">
                        Ver
                    </a>
                    <a href="{{ route('registros.create') }}?territorio_id={{ $territorio->id }}" class="btn-icon">
                        Registrar
                    </a>
                </div>
            </div>

            <!-- Columna 3: Estado en movil -->
            <div class="territorio-badge-estado territorio-badge-mobile
                @if($territorio->estaDisponibleParaAsignar()) badge-green
                @elseif($territorio->calcularEstado() == 'activo') badge-blue
                @elseif($territorio->calcularEstado() == 'atrasado') badge-red
                @else badge-gray
                @endif">
                @if($territorio->estaDisponibleParaAsignar()) Disponible
                @elseif($territorio->calcularEstado() == 'activo') Activo
                @elseif($territorio->calcularEstado() == 'atrasado') Atrasado
                @else Archivo
                @endif
            </div>
        </div>
        @endforeach
    </div>

    <!-- Paginacion -->
    @if($territorios->hasPages())
    <div class="card text-center">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <div class="text-muted">
                Mostrando {{ $territorios->firstItem() }}-{{ $territorios->lastItem() }} de {{ $territorios->total() }} territorios
            </div>
            <div style="display: flex; gap: 0.5rem;">
                @if($territorios->previousPageUrl())
                    <a href="{{ $territorios->appends(request()->query())->previousPageUrl() }}" class="btn btn-secondary">Anterior</a>
                @endif
                <span class="btn" style="background: #e5e7eb; color: #374151;">
                    Pagina {{ $territorios->currentPage() }} de {{ $territorios->lastPage() }}
                </span>
                @if($territorios->nextPageUrl())
                    <a href="{{ $territorios->appends(request()->query())->nextPageUrl() }}" class="btn btn-secondary">Siguiente</a>
                @endif
            </div>
        </div>
    </div>
    @endif
@else
    <div class="card text-center" style="padding: 3rem;">
        <div style="font-size: 4rem; margin-bottom: 1rem;">&#128506;</div>
        <h3 style="font-size: 1.5rem; margin-bottom: 0.5rem; color: #1f2937;">No hay territorios disponibles</h3>
        <p class="text-muted mb-4">
            @if(($tipoFiltro ?? 'todos') !== 'todos')
                No hay territorios de tipo "{{ ucfirst($tipoFiltro ?? '') }}" en esta congregacion.
            @else
                Comienza agregando el primer territorio a tu sistema.
            @endif
        </p>
        <a href="{{ route('territorios.create', ['tipo' => ($tipoFiltro ?? 'todos') !== 'todos' ? $tipoFiltro : 'normal']) }}" class="btn btn-primary">
            + Crear Territorio
        </a>
    </div>
@endif

<script>
function toggleDropdown(menuId) {
    const menu = document.getElementById(menuId);
    const allMenus = document.querySelectorAll('.dropdown-menu');

    allMenus.forEach(m => {
        if (m.id !== menuId) m.classList.remove('show');
    });

    menu.classList.toggle('show');
}

document.addEventListener('click', function(e) {
    if (!e.target.closest('.dropdown')) {
        document.querySelectorAll('.dropdown-menu').forEach(m => m.classList.remove('show'));
    }
});
</script>

<style>
/* Filtros por tipo */
.tipo-filtros {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}
.tipo-filtro {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.25rem;
    background: var(--bg-white);
    border: 2px solid var(--border);
    border-radius: 10px;
    text-decoration: none;
    color: var(--text);
    transition: all 0.2s;
    font-weight: 500;
}
.tipo-filtro:hover {
    border-color: var(--primary);
    background: #f8fafc;
}
.tipo-filtro.active {
    border-color: var(--primary);
    background: linear-gradient(135deg, #f4f7fb 0%, #e8eef6 100%);
    color: var(--primary);
}
.tipo-filtro.tipo-campana.active {
    border-color: #4a6da7;
    background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
    color: #b45309;
}
.tipo-filtro.tipo-negocios.active {
    border-color: #4a6da7;
    background: linear-gradient(135deg, #f4f7fb 0%, #e8eef6 100%);
    color: #2d4266;
}
.tipo-icono {
    font-size: 1.25rem;
}
.tipo-nombre {
    font-size: 0.9rem;
}
.tipo-count {
    background: var(--border);
    padding: 0.15rem 0.5rem;
    border-radius: 10px;
    font-size: 0.75rem;
    font-weight: 600;
}
.tipo-filtro.active .tipo-count {
    background: rgba(255,255,255,0.8);
}

/* Alerta informativa */
.alert-info-tipo {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    border: 1px solid #fbbf24;
    border-radius: 8px;
    color: #92400e;
    font-size: 0.875rem;
}
.alert-icon {
    font-size: 1.25rem;
}

/* Dropdown */
.dropdown {
    position: relative;
    display: inline-block;
}
.dropdown-toggle {
    cursor: pointer;
}
.dropdown-menu {
    display: none;
    position: absolute;
    right: 0;
    top: 100%;
    margin-top: 0.5rem;
    background: var(--bg-white);
    border: 1px solid var(--border);
    border-radius: 8px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.15);
    min-width: 220px;
    z-index: 100;
    overflow: hidden;
}
.dropdown-menu.show {
    display: block;
}
.dropdown-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    color: var(--text);
    text-decoration: none;
    transition: background 0.2s;
}
.dropdown-item:hover {
    background: var(--bg-hover);
}
.dropdown-icon {
    font-size: 1.25rem;
}

/* Territorios especiales */
.territorio-especial {
    border-left: 4px solid;
}
.territorio-campana {
    border-left-color: #4a6da7;
}
.territorio-negocios {
    border-left-color: #4a6da7;
}
.territorio-tipo-badge {
    position: absolute;
    top: 0.5rem;
    left: 0.5rem;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.7rem;
    font-weight: 700;
    color: white;
}
.territorio-tipo-badge.campana {
    background: #4a6da7;
}
.territorio-tipo-badge.negocios {
    background: #4a6da7;
}
.numero-campana {
    background: linear-gradient(135deg, #4a6da7 0%, #3d5a8a 100%) !important;
}
.numero-negocios {
    background: linear-gradient(135deg, #4a6da7 0%, #3d5a8a 100%) !important;
}
.territorio-image-half {
    position: relative;
}

/* Grid responsive */
.territorio-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}
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
    min-width: 0;
}
.territorio-nombre-container {
    flex: 1;
    min-width: 0;
}
.territorio-nombre {
    font-weight: 600;
    color: #374151;
    font-size: 1rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
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
.territorio-badge-mobile {
    display: none;
}
.territorio-badge-desktop {
    display: inline-flex;
}

@media (max-width: 768px) {
    .tipo-filtros {
        overflow-x: auto;
        flex-wrap: nowrap;
        padding-bottom: 0.5rem;
    }
    .tipo-filtro {
        flex-shrink: 0;
        padding: 0.5rem 0.75rem;
    }
    .tipo-nombre {
        font-size: 0.8rem;
    }
    .territorio-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    .territorio-card {
        display: grid;
        grid-template-columns: 140px 1fr auto;
        gap: 0.75rem;
        align-items: center;
        min-height: 130px;
    }
    .territorio-image-half {
        width: 140px;
        height: 110px;
        padding: 0.25rem;
    }
    .territorio-content {
        padding: 0.75rem 0;
    }
    .territorio-header-superior {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.25rem;
    }
    .territorio-info-superior {
        width: 100%;
        flex-direction: column;
        align-items: flex-start;
    }
    .territorio-numero-destacado {
        width: 2.5rem !important;
        height: 2.5rem !important;
        font-size: 0.85rem !important;
        flex-shrink: 0;
    }
    .territorio-content-desktop {
        display: none !important;
    }
    .territorio-nombre-mobile {
        display: block !important;
        font-size: 0.85rem;
    }
    .territorio-badge-desktop {
        display: none !important;
    }
    .territorio-badge-mobile {
        display: flex !important;
        writing-mode: vertical-lr;
        padding: 0.5rem 0.3rem;
        font-size: 0.65rem;
        font-weight: 600;
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
    }
}

/* Badges */
.badge-blue { background: #e8eef6; color: #2d4266; }
.badge-green { background: #dcfce7; color: #166534; }
.badge-yellow { background: #fef3c7; color: #92400e; }
.badge-red { background: #e9ecef; color: #212529; }
.badge-gray { background: #f3f4f6; color: #374151; }

/* Buscador */
.search-container {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.search-input {
    flex: 1;
    padding: 0.75rem 1rem;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 1rem;
}
.search-input:focus {
    outline: none;
    border-color: #4a6da7;
}
.search-btn {
    padding: 0.75rem 1rem;
    background: #4a6da7;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
}
.search-clear {
    padding: 0.75rem 1rem;
    background: #495057;
    color: white;
    text-decoration: none;
    border-radius: 8px;
}
</style>
@endsection
