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

<!-- Buscador -->
<div class="card mb-4">
    <form method="GET" action="{{ route('publicadores.index') }}" class="search-form">
        <div class="search-container">
            <input type="text"
                   name="search"
                   value="{{ request('search') }}"
                   placeholder="Buscar publicador por nombre, apellidos, teléfono..."
                   class="search-input">
            <button type="submit" class="search-btn">🔍</button>
            @if(request('search'))
                <a href="{{ route('publicadores.index') }}" class="search-clear">✖</a>
            @endif
        </div>
    </form>
</div>

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
                <div class="nombre-con-badges">
                    <span class="font-bold text-dark">{{ $publicador->nombre }}</span>
                    <div class="pub-mini-badges">
                        @if($publicador->es_anciano)
                            <span class="mini-badge badge-anc">ANC</span>
                        @endif
                        @if($publicador->es_siervo_ministerial)
                            <span class="mini-badge badge-sm">SM</span>
                        @endif
                        @if($publicador->es_precursor)
                            <span class="mini-badge badge-pr">PR</span>
                        @endif
                        @if($publicador->es_menor)
                            <span class="mini-badge badge-menor">MEN</span>
                        @endif
                    </div>
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

<style>
/* Mini badges para la lista */
.nombre-con-badges {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.pub-mini-badges {
    display: flex;
    gap: 0.25rem;
}

.mini-badge {
    padding: 0.1rem 0.35rem;
    border-radius: 4px;
    font-size: 0.6rem;
    font-weight: 700;
    letter-spacing: 0.5px;
}

.badge-anc {
    background: #dc2626;
    color: white;
}

.badge-sm {
    background: #3b82f6;
    color: white;
}

.badge-pr {
    background: #16a34a;
    color: white;
}

.badge-menor {
    background: #a855f7;
    color: white;
}

[data-theme="dark"] .badge-anc {
    background: #ef4444;
}

[data-theme="dark"] .badge-sm {
    background: #60a5fa;
}

[data-theme="dark"] .badge-pr {
    background: #22c55e;
}

[data-theme="dark"] .badge-menor {
    background: #c084fc;
}

/* Estilos del buscador */
.search-form {
    padding: 0;
}

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
    transition: border-color 0.3s;
}

.search-input:focus {
    outline: none;
    border-color: #3b82f6;
}

.search-btn {
    padding: 0.75rem 1rem;
    background: #3b82f6;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    cursor: pointer;
    transition: background-color 0.3s;
}

.search-btn:hover {
    background: #2563eb;
}

.search-clear {
    padding: 0.75rem 1rem;
    background: #ef4444;
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-size: 1rem;
    transition: background-color 0.3s;
}

.search-clear:hover {
    background: #dc2626;
}

@media (max-width: 768px) {
    .search-container {
        flex-direction: column;
    }

    .search-input {
        width: 100%;
    }

    .search-btn,
    .search-clear {
        width: 100%;
        text-align: center;
    }
}
</style>
@endsection