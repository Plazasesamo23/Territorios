@extends('layouts.app')

@section('title', 'Territorios')

@section('content')

@php
    $fuera = $territorios->filter(fn($t) => $t->registroActivo())->sortBy('numero');
    $zonas = $fuera->map(fn($t) => $t->zona)->filter()->unique()->sort()->values();
@endphp

<div class="simple-panel">
    <h1 class="simple-h1">Territorios</h1>

    <!-- Dos acciones grandes -->
    <div class="acciones-grandes">
        <a href="{{ route('registros.create') }}" class="accion-card accion-asignar">
            <span class="accion-icono">&#x2795;</span>
            <span class="accion-titulo">Asignar territorio</span>
            <span class="accion-sub">Dar un territorio a un publicador</span>
        </a>
        <a href="#lista-fuera" class="accion-card accion-devolver">
            <span class="accion-icono">&#x21A9;</span>
            <span class="accion-titulo">Devolver territorio</span>
            <span class="accion-sub">Cuando alguien te lo entrega</span>
        </a>
    </div>

    <!-- Territorios fuera ahora -->
    <div class="lista-fuera" id="lista-fuera">
        <h2 class="seccion-fuera">Territorios que están fuera ahora ({{ $fuera->count() }})</h2>

        @if($fuera->count() > 0)
            <!-- Buscador y filtros -->
            <div class="fuera-controles">
                <input type="text" id="buscar-fuera" class="buscar-fuera"
                       placeholder="Buscar por territorio o por hermano...">
                <div class="fuera-filtros">
                    @if($zonas->count() > 1)
                    <select id="filtro-zona" class="filtro-select" aria-label="Filtrar por zona">
                        <option value="">Todas las zonas</option>
                        @foreach($zonas as $z)
                            <option value="{{ strtolower($z) }}">{{ $z }}</option>
                        @endforeach
                    </select>
                    @endif
                    <select id="orden-fuera" class="filtro-select" aria-label="Ordenar">
                        <option value="numero">Ordenar: por número</option>
                        <option value="dias">Ordenar: más días fuera</option>
                        <option value="nombre">Ordenar: por hermano</option>
                    </select>
                </div>
            </div>

            <div class="fuera-items" id="fuera-items">
                @foreach($fuera as $territorio)
                    @php
                        $reg = $territorio->registroActivo();
                        $dias = $reg ? round($reg->fecha_salida->diffInDays(now())) : 0;
                        $nombrePub = trim(($reg->publicador->nombre ?? '') . ' ' . ($reg->publicador->apellidos ?? ''));
                    @endphp
                    <div class="fuera-item"
                         data-numero="{{ $territorio->numero }}"
                         data-dias="{{ $dias }}"
                         data-zona="{{ strtolower($territorio->zona ?? '') }}"
                         data-nombre="{{ strtolower($nombrePub) }}"
                         data-buscar="{{ strtolower($territorio->numero_completo . ' ' . $nombrePub . ' ' . ($territorio->zona ?? '')) }}">
                        <div class="fuera-info">
                            <span class="fuera-terr">{{ $territorio->numero_completo }}@if($territorio->zona) <span class="fuera-zona">· {{ $territorio->zona }}</span>@endif</span>
                            <span class="fuera-pub">{{ $nombrePub !== '' ? $nombrePub : 'Sin nombre' }}</span>
                            <span class="fuera-dias">{{ $dias }} días fuera</span>
                        </div>
                        <form action="{{ route('registros.entrada', $reg) }}" method="POST" style="margin:0"
                              onsubmit="return confirm('¿Devolver el territorio {{ $territorio->numero_completo }} de {{ $nombrePub }}?');">
                            @csrf
                            <button type="submit" class="btn-devolver-grande">Devolver</button>
                        </form>
                    </div>
                @endforeach
            </div>

            <div class="fuera-sin-resultados" id="fuera-sin-resultados" style="display:none;">
                No se encontró ningún territorio con esa búsqueda.
            </div>
        @else
            <div class="fuera-vacio">
                <div class="fuera-vacio-icono">&#x2705;</div>
                <div>Ahora mismo no hay ningún territorio fuera.</div>
            </div>
        @endif
    </div>
</div>

<style>
.simple-panel {
    max-width: 760px;
    margin: 0 auto;
}

.simple-h1 {
    font-size: 1.9rem;
    color: #f1f3f5;
    margin: 0 0 1.25rem 0;
    text-align: center;
}

/* Dos botones de acción grandes */
.acciones-grandes {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    margin-bottom: 2rem;
}

.accion-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 0.4rem;
    padding: 1.75rem 1rem;
    border-radius: 16px;
    text-decoration: none;
    color: #fff;
    transition: transform 0.15s, box-shadow 0.15s;
    min-height: 150px;
    justify-content: center;
}

.accion-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 28px rgba(0,0,0,0.4);
}

.accion-asignar {
    background: linear-gradient(135deg, #2e9e5b 0%, #1f7a44 100%);
}

.accion-devolver {
    background: linear-gradient(135deg, #4a6da7 0%, #2d4266 100%);
}

.accion-icono {
    font-size: 2.4rem;
    line-height: 1;
}

.accion-titulo {
    font-size: 1.35rem;
    font-weight: 700;
}

.accion-sub {
    font-size: 0.9rem;
    opacity: 0.85;
}

/* Lista de territorios fuera */
.seccion-fuera {
    font-size: 1.15rem;
    color: #f1f3f5;
    margin: 0 0 1rem 0;
    padding-bottom: 0.6rem;
    border-bottom: 1px solid #2d3339;
}

/* Buscador y filtros */
.fuera-controles {
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
    margin-bottom: 1rem;
}

.buscar-fuera {
    width: 100%;
    padding: 0.85rem 1rem;
    font-size: 1.05rem;
    border-radius: 10px;
    border: 1px solid #2d3339;
    background: #151719;
    color: #f1f3f5;
}

.buscar-fuera::placeholder { color: #6b7682; }
.buscar-fuera:focus {
    outline: none;
    border-color: #6b8fc7;
    box-shadow: 0 0 0 3px rgba(107,143,199,0.12);
}

.fuera-filtros {
    display: flex;
    gap: 0.6rem;
    flex-wrap: wrap;
}

.filtro-select {
    flex: 1;
    min-width: 150px;
    padding: 0.7rem 0.9rem;
    font-size: 0.95rem;
    border-radius: 10px;
    border: 1px solid #2d3339;
    background: #151719;
    color: #f1f3f5;
    cursor: pointer;
}

.filtro-select:focus {
    outline: none;
    border-color: #6b8fc7;
}

.fuera-items {
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
}

.fuera-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    background: #171717;
    border: 1px solid #262626;
    border-radius: 12px;
    padding: 0.9rem 1.1rem;
}

.fuera-info {
    display: flex;
    flex-direction: column;
    gap: 0.15rem;
    min-width: 0;
}

.fuera-terr {
    font-size: 1.15rem;
    font-weight: 700;
    color: #f1f3f5;
}

.fuera-zona {
    font-size: 0.85rem;
    font-weight: 500;
    color: #9ca3af;
}

.fuera-pub {
    font-size: 1rem;
    color: #cbd5e1;
}

.fuera-dias {
    font-size: 0.8rem;
    color: #9ca3af;
}

.btn-devolver-grande {
    flex-shrink: 0;
    background: #4a6da7;
    color: #fff;
    border: none;
    border-radius: 10px;
    padding: 0.85rem 1.5rem;
    font-size: 1.05rem;
    font-weight: 700;
    cursor: pointer;
    transition: background 0.15s;
}

.btn-devolver-grande:hover {
    background: #3d5a8a;
}

.fuera-vacio, .fuera-sin-resultados {
    text-align: center;
    padding: 2rem 1rem;
    color: #9ca3af;
    background: #171717;
    border: 1px solid #262626;
    border-radius: 12px;
}

.fuera-vacio-icono {
    font-size: 2.5rem;
    margin-bottom: 0.5rem;
}

/* Móvil */
@media (max-width: 600px) {
    .acciones-grandes {
        grid-template-columns: 1fr;
    }
    .fuera-item {
        flex-direction: column;
        align-items: stretch;
        gap: 0.75rem;
    }
    .btn-devolver-grande {
        width: 100%;
        padding: 0.95rem;
        font-size: 1.1rem;
    }
    .filtro-select { min-width: 0; }
}
</style>

<script>
(function() {
    var contenedor = document.getElementById('fuera-items');
    if (!contenedor) return;

    var inputBuscar = document.getElementById('buscar-fuera');
    var selectZona = document.getElementById('filtro-zona');
    var selectOrden = document.getElementById('orden-fuera');
    var sinResultados = document.getElementById('fuera-sin-resultados');
    var items = Array.prototype.slice.call(contenedor.querySelectorAll('.fuera-item'));

    function aplicar() {
        var texto = (inputBuscar.value || '').toLowerCase().trim();
        var zona = selectZona ? selectZona.value : '';
        var visibles = 0;

        items.forEach(function(item) {
            var coincideTexto = !texto || item.getAttribute('data-buscar').indexOf(texto) !== -1;
            var coincideZona = !zona || item.getAttribute('data-zona') === zona;
            var mostrar = coincideTexto && coincideZona;
            item.style.display = mostrar ? '' : 'none';
            if (mostrar) visibles++;
        });

        sinResultados.style.display = visibles === 0 ? 'block' : 'none';
    }

    function ordenar() {
        var criterio = selectOrden.value;
        var ordenados = items.slice().sort(function(a, b) {
            if (criterio === 'dias') {
                return parseInt(b.getAttribute('data-dias')) - parseInt(a.getAttribute('data-dias'));
            } else if (criterio === 'nombre') {
                return a.getAttribute('data-nombre').localeCompare(b.getAttribute('data-nombre'));
            }
            return parseFloat(a.getAttribute('data-numero')) - parseFloat(b.getAttribute('data-numero'));
        });
        ordenados.forEach(function(item) { contenedor.appendChild(item); });
    }

    inputBuscar.addEventListener('input', aplicar);
    if (selectZona) selectZona.addEventListener('change', aplicar);
    selectOrden.addEventListener('change', ordenar);
})();
</script>

@endsection
