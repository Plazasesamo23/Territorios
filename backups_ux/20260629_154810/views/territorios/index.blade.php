@extends('layouts.app')

@section('title', 'Territorios - Gestion de Territorios')

@section('content')
<!-- Navegacion y acciones -->
<div class="page-nav">
    <div class="page-breadcrumbs">
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
    <a href="{{ route('territorios.index', ['search' => request('search'), 'tipo' => $tipoFiltro ?? 'todos']) }}" class="stat-card {{ !request('estado') ? 'active' : '' }}">
        <div class="stat-number">{{ $allTerritorios->count() }}</div>
        <div class="stat-label">Total</div>
    </a>
    <a href="{{ route('territorios.index', ['estado' => 'libre', 'search' => request('search'), 'tipo' => $tipoFiltro ?? 'todos']) }}" class="stat-card {{ request('estado') == 'libre' ? 'active' : '' }}">
        <div class="stat-number text-primary">{{ $estadisticas['libre'] }}</div>
        <div class="stat-label">Libres</div>
    </a>
    <a href="{{ route('territorios.index', ['estado' => 'activo', 'search' => request('search'), 'tipo' => $tipoFiltro ?? 'todos']) }}" class="stat-card {{ request('estado') == 'activo' ? 'active' : '' }}">
        <div class="stat-number text-primary">{{ $estadisticas['activo'] }}</div>
        <div class="stat-label">Activos</div>
    </a>
    <a href="{{ route('territorios.index', ['estado' => 'atrasado', 'search' => request('search'), 'tipo' => $tipoFiltro ?? 'todos']) }}" class="stat-card {{ request('estado') == 'atrasado' ? 'active' : '' }}">
        <div class="stat-number text-muted">{{ $estadisticas['atrasado'] }}</div>
        <div class="stat-label">Atrasados</div>
    </a>
    <a href="{{ route('territorios.index', ['estado' => 'archivo', 'search' => request('search'), 'tipo' => $tipoFiltro ?? 'todos']) }}" class="stat-card {{ request('estado') == 'archivo' ? 'active' : '' }}">
        <div class="stat-number text-muted">{{ $estadisticas['archivo'] }}</div>
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
                <span class="btn btn-page-current">
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
    <div class="empty-state">
        <div class="icon">&#128506;</div>
        <div class="title">No hay territorios disponibles</div>
        <div class="desc">
            @if(($tipoFiltro ?? 'todos') !== 'todos')
                No hay territorios de tipo "{{ ucfirst($tipoFiltro ?? '') }}" en esta congregacion.
            @else
                Usa el boton "+ Nuevo Territorio" de arriba para empezar.
            @endif
        </div>
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
@endsection
