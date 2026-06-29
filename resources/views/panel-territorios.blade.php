@extends('layouts.app')

@section('title', 'Territorios')

@section('content')

@php
    $fuera = $territorios->filter(fn($t) => $t->registroActivo())->sortBy('numero');
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
            <div class="fuera-items">
                @foreach($fuera as $territorio)
                    @php
                        $reg = $territorio->registroActivo();
                        $dias = $reg ? round($reg->fecha_salida->diffInDays(now())) : 0;
                    @endphp
                    <div class="fuera-item">
                        <div class="fuera-info">
                            <span class="fuera-terr">{{ $territorio->numero_completo }}</span>
                            <span class="fuera-pub">{{ $reg->publicador->nombre ?? 'Sin nombre' }} {{ $reg->publicador->apellidos ?? '' }}</span>
                            <span class="fuera-dias">{{ $dias }} días fuera</span>
                        </div>
                        <form action="{{ route('registros.entrada', $reg) }}" method="POST" style="margin:0"
                              onsubmit="return confirm('¿Devolver el territorio {{ $territorio->numero_completo }} de {{ $reg->publicador->nombre ?? '' }}?');">
                            @csrf
                            <button type="submit" class="btn-devolver-grande">Devolver</button>
                        </form>
                    </div>
                @endforeach
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

.fuera-vacio {
    text-align: center;
    padding: 2.5rem 1rem;
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
}
</style>

@endsection
