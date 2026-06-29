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
        <a href="{{ route('registros.index', ['vista' => 'lista']) }}" class="btn-accion btn-devolver">
            <span class="btn-icono">&#x21A9;</span>
            <span class="btn-texto">Devolver</span>
        </a>
    </div>

    <!-- Filtros de tipo -->
    <div class="tabs-flat">
        <button class="tab-item active" data-tipo="todos">Todos <span class="badge">{{ $territorios->count() }}</span></button>
        <button class="tab-item" data-tipo="normal">Normales <span class="badge">{{ $territorios->filter(fn($t) => empty($t->tipo) || $t->tipo === 'normal')->count() }}</span></button>
        <button class="tab-item" data-tipo="campana">Campaña <span class="badge">{{ $territorios->where('tipo', 'campana')->count() }}</span></button>
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
        <a href="{{ route('territorios.show', $territorio) }}" class="territorio-item estado-{{ $estado }}"
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
