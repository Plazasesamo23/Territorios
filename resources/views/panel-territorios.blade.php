@extends('layouts.app')

@section('title', 'Panel de Territorios')

@section('content')

<div class="panel-flat">
    <h1 class="page-title">Territorios</h1>
    <p class="page-subtitle">Gestiona las asignaciones de territorios</p>

    <!-- Acciones principales - Botones grandes -->
    <div class="acciones-principales">
        <a href="{{ route('registros.create') }}" class="btn-accion btn-asignar">
            <span class="btn-icono">+</span>
            <span class="btn-texto">Asignar</span>
        </a>
        <a href="{{ route('registros.index') }}" class="btn-accion btn-devolver">
            <span class="btn-icono">&#x21A9;</span>
            <span class="btn-texto">Devolver</span>
        </a>
    </div>

    <!-- Filtros de tipo -->
    <div class="tabs-flat">
        <button class="tab-item active" data-tipo="todos">Todos <span class="badge">{{ $territorios->count() }}</span></button>
        <button class="tab-item" data-tipo="normal">Normales <span class="badge">{{ $territorios->filter(fn($t) => empty($t->tipo) || $t->tipo === 'normal')->count() }}</span></button>
        <button class="tab-item" data-tipo="campana">Campana <span class="badge">{{ $territorios->where('tipo', 'campana')->count() }}</span></button>
        <button class="tab-item" data-tipo="negocios">Negocios <span class="badge">{{ $territorios->where('tipo', 'negocios')->count() }}</span></button>
    </div>

    <!-- Busqueda -->
    <div class="form-group">
        <input type="text" id="buscar-territorio" class="form-input" placeholder="Buscar territorio...">
    </div>

    <!-- Grid de territorios -->
    <div class="territorios-grid" id="territorios-container">
        @foreach($territorios as $territorio)
        @php
            $registroActivo = $territorio->registroActivo();
            $estaAsignado = $registroActivo !== null;
            $estado = $territorio->calcularEstado();
            $imgUrl = $territorio->getImagenUrl();
        @endphp
        <a href="{{ $imgUrl }}" target="_blank" class="territorio-item estado-{{ $estado }}"
             data-tipo="{{ $territorio->tipo ?? 'normal' }}"
             data-numero="{{ $territorio->numero }}"
             data-nombre="{{ $territorio->nombre }}">
            <div class="territorio-img" style="background-image: url('{{ $imgUrl }}')"></div>
            <div class="territorio-data">
                <span class="territorio-num">{{ $territorio->numero_completo }}</span>
                @if($estaAsignado)
                    <span class="territorio-asig">{{ $registroActivo->publicador->nombre ?? 'Asignado' }}</span>
                @else
                    <span class="territorio-estado">{{ ucfirst($estado) }}</span>
                @endif
            </div>
        </a>
        @endforeach
    </div>

    <!-- Publicadores con territorios -->
    <div class="section-title mt-3">
        Publicadores con Territorios
        <span class="badge badge-primary">{{ $publicadores->filter(fn($p) => $p->ultimoRegistroActivo())->count() }}</span>
    </div>
    <ul class="list-flat">
        @foreach($publicadores->filter(fn($p) => $p->ultimoRegistroActivo()) as $publicador)
        @php
            $registroActivoPublicador = $publicador->ultimoRegistroActivo();
            $territorioAsignado = $registroActivoPublicador?->territorio;
            $diasAsignado = $registroActivoPublicador?->fecha_salida->diffInDays(now()) ?? 0;
        @endphp
        <li class="list-item">
            <div class="avatar">{{ strtoupper(substr($publicador->nombre, 0, 1)) }}</div>
            <div class="content">
                <span class="title">{{ $publicador->nombre_completo }}</span>
                @if($publicador->telefono)
                <span class="subtitle">{{ $publicador->telefono }}</span>
                @endif
            </div>
            @if($territorioAsignado)
            <div class="meta">
                <strong>{{ $territorioAsignado->numero_completo }}</strong>
                <span class="text-muted text-xs">{{ $diasAsignado }}d</span>
            </div>
            @endif
        </li>
        @endforeach
    </ul>
</div>

<style>
.panel-flat {
    max-width: 1200px;
    margin: 0 auto;
}

/* ===== BOTONES DE ACCION PRINCIPALES ===== */
.acciones-principales {
    display: flex;
    gap: 1rem;
    margin-bottom: 2rem;
}

.btn-accion {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    padding: 1rem 1.5rem;
    border-radius: 12px;
    text-decoration: none;
    font-weight: 600;
    font-size: 1.1rem;
    transition: all 0.2s ease;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    flex: 1;
    max-width: 200px;
}

.btn-accion:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
}

.btn-accion:active {
    transform: translateY(0);
}

.btn-icono {
    font-size: 1.5rem;
    font-weight: 700;
    line-height: 1;
}

.btn-texto {
    font-size: 1.1rem;
}

/* Boton Asignar - Verde */
.btn-asignar {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
}

.btn-asignar:hover {
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
    color: white;
}

/* Boton Devolver - Azul */
.btn-devolver {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: white;
}

.btn-devolver:hover {
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    color: white;
}

/* Dark mode */
[data-theme="dark"] .btn-asignar {
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
}

[data-theme="dark"] .btn-devolver {
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
}

/* ===== RESPONSIVE MOVIL ===== */
@media (max-width: 768px) {
    .acciones-principales {
        flex-direction: row;
        gap: 0.75rem;
    }

    .btn-accion {
        flex: 1;
        max-width: none;
        padding: 1.25rem 1rem;
        border-radius: 16px;
        flex-direction: column;
        gap: 0.5rem;
    }

    .btn-icono {
        font-size: 2rem;
    }

    .btn-texto {
        font-size: 1rem;
    }
}

@media (max-width: 480px) {
    .acciones-principales {
        gap: 0.5rem;
    }

    .btn-accion {
        padding: 1.5rem 0.75rem;
        border-radius: 20px;
    }

    .btn-icono {
        font-size: 2.5rem;
    }

    .btn-texto {
        font-size: 1.1rem;
        font-weight: 700;
    }
}

/* Grid de territorios */
.territorios-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}

.territorio-item {
    display: block;
    text-decoration: none;
    border-radius: var(--radius);
    overflow: hidden;
    background: var(--bg-white);
    border-bottom: 3px solid var(--border);
    transition: transform 0.15s;
}

.territorio-item:hover {
    transform: translateY(-2px);
}

.territorio-item.estado-libre {
    border-bottom-color: var(--primary);
}

.territorio-item.estado-activo {
    border-bottom-color: var(--primary-light);
}

.territorio-item.estado-atrasado {
    border-bottom-color: var(--text-muted);
}

.territorio-item.estado-archivo {
    border-bottom-color: var(--text-light);
}

.territorio-img {
    width: 100%;
    height: 120px;
    background-size: cover;
    background-position: center;
    background-color: var(--bg-hover);
}

.territorio-data {
    padding: 0.625rem;
}

.territorio-num {
    display: block;
    font-size: 1rem;
    font-weight: 700;
    color: var(--text);
}

.territorio-asig {
    display: block;
    font-size: 0.75rem;
    color: var(--text-muted);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.territorio-estado {
    display: block;
    font-size: 0.7rem;
    color: var(--text-light);
    text-transform: uppercase;
}

.territorio-item.filtrado-oculto {
    display: none !important;
}

/* Tabs como botones */
.tabs-flat button {
    background: none;
    border: none;
    cursor: pointer;
}

@media (max-width: 768px) {
    .territorios-grid {
        grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
        gap: 0.75rem;
    }

    .territorio-img {
        height: 100px;
    }
}
</style>

<script>
(function() {
    var filtroActual = 'todos';
    var busquedaActual = '';

    function aplicarFiltros() {
        var cards = document.querySelectorAll('.territorio-item');
        cards.forEach(function(card) {
            var rawTipo = card.getAttribute('data-tipo');
            var tipoCard = (rawTipo && rawTipo.trim() !== '') ? rawTipo.trim() : 'normal';
            var numero = (card.getAttribute('data-numero') || '').toLowerCase();
            var nombre = (card.getAttribute('data-nombre') || '').toLowerCase();

            var pasaFiltroTipo = (filtroActual === 'todos' || tipoCard === filtroActual);
            var pasaFiltroBusqueda = (busquedaActual === '' || numero.indexOf(busquedaActual) !== -1 || nombre.indexOf(busquedaActual) !== -1);

            if (pasaFiltroTipo && pasaFiltroBusqueda) {
                card.classList.remove('filtrado-oculto');
            } else {
                card.classList.add('filtrado-oculto');
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        var filtros = document.querySelectorAll('.tab-item');
        filtros.forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                filtros.forEach(function(b) { b.classList.remove('active'); });
                this.classList.add('active');
                filtroActual = this.getAttribute('data-tipo');
                aplicarFiltros();
            });
        });

        var searchInput = document.getElementById('buscar-territorio');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                busquedaActual = this.value.toLowerCase();
                aplicarFiltros();
            });
        }
    });
})();
</script>

@endsection
