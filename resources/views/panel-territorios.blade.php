@extends('layouts.app')

@section('title', 'Panel de Territorios')

@section('content')

<div class="panel-territorios">
    <!-- Header -->
    <div class="panel-header">
        <h1 class="panel-title">Panel de Territorios</h1>
        <p class="panel-subtitle">Gestiona las asignaciones de territorios</p>
    </div>

    <!-- Botones principales -->
    <div class="action-buttons">
        <a href="{{ route('registros.create') }}" class="action-btn asignar">
            <div class="action-icon-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 5v14M5 12h14"/>
                </svg>
            </div>
            <div class="action-content">
                <span class="action-text">Asignar Territorio</span>
                <span class="action-desc">Entregar territorio a un publicador</span>
            </div>
        </a>
        <a href="{{ route('registros.index') }}" class="action-btn devolver">
            <div class="action-icon-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 14l-5-5 5-5"/>
                    <path d="M4 9h10.5a5.5 5.5 0 0 1 5.5 5.5v0a5.5 5.5 0 0 1-5.5 5.5H11"/>
                </svg>
            </div>
            <div class="action-content">
                <span class="action-text">Devolver Territorio</span>
                <span class="action-desc">Registrar devolucion de territorio</span>
            </div>
        </a>
    </div>

    <!-- Filtros de tipo -->
    <div class="tipo-filters">
        <button class="tipo-filter active" data-tipo="todos">Todos <span class="filter-count">{{ $territorios->count() }}</span></button>
        <button class="tipo-filter" data-tipo="normal">Normales <span class="filter-count">{{ $territorios->filter(fn($t) => empty($t->tipo) || $t->tipo === 'normal')->count() }}</span></button>
        <button class="tipo-filter" data-tipo="campana">Campana <span class="filter-count">{{ $territorios->where('tipo', 'campana')->count() }}</span></button>
        <button class="tipo-filter" data-tipo="negocios">Negocios <span class="filter-count">{{ $territorios->where('tipo', 'negocios')->count() }}</span></button>
    </div>

    <!-- Toolbar -->
    <div class="section-toolbar">
        <div class="search-box">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" id="buscar-territorio" placeholder="Buscar territorio...">
        </div>
    </div>

    <!-- Vista de territorios -->
    <div class="territorios-grid" id="territorios-container">
        @foreach($territorios as $territorio)
        @php
            $registroActivo = $territorio->registroActivo();
            $estaAsignado = $registroActivo !== null;
            $estado = $territorio->calcularEstado();
            $imgUrl = $territorio->getImagenUrl();
        @endphp
        <a href="{{ $imgUrl }}" target="_blank" class="territorio-card estado-{{ $estado }}"
             data-tipo="{{ $territorio->tipo ?? 'normal' }}"
             data-numero="{{ $territorio->numero }}"
             data-nombre="{{ $territorio->nombre }}">

            <!-- Imagen del territorio -->
            <div class="territorio-imagen" style="background-image: url('{{ $imgUrl }}')">
                <!-- Icono de expandir en hover -->
                <div class="expand-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/>
                    </svg>
                </div>
            </div>

            <!-- Info superpuesta abajo -->
            <div class="territorio-info-bar">
                <div class="info-left">
                    <span class="territorio-numero">{{ $territorio->numero_completo }}</span>
                    @if($estaAsignado)
                        <span class="asignado-nombre">{{ $registroActivo->publicador->nombre ?? 'Asignado' }}</span>
                    @endif
                </div>
                <div class="info-right">
                    <span class="estado-badge estado-{{ $estado }}">{{ ucfirst($estado) }}</span>
                </div>
            </div>
        </a>
        @endforeach
    </div>

    <!-- Publicadores con territorios -->
    <div class="publicadores-section">
        <div class="section-header-pub">
            <h2>Publicadores con Territorios</h2>
            <span class="pub-count">{{ $publicadores->filter(fn($p) => $p->ultimoRegistroActivo())->count() }}</span>
        </div>
        <div class="publicadores-grid">
            @foreach($publicadores->filter(fn($p) => $p->ultimoRegistroActivo()) as $publicador)
            @php
                $registroActivoPublicador = $publicador->ultimoRegistroActivo();
                $territorioAsignado = $registroActivoPublicador?->territorio;
                $diasAsignado = $registroActivoPublicador?->fecha_salida->diffInDays(now()) ?? 0;
            @endphp
            <div class="publicador-card">
                <div class="pub-avatar">
                    {{ strtoupper(substr($publicador->nombre, 0, 1)) }}{{ strtoupper(substr($publicador->apellidos ?? '', 0, 1)) }}
                </div>
                <div class="pub-details">
                    <span class="pub-nombre">{{ $publicador->nombre_completo }}</span>
                    @if($publicador->telefono)
                    <a href="tel:{{ $publicador->telefono }}" class="pub-telefono">{{ $publicador->telefono }}</a>
                    @endif
                </div>
                @if($territorioAsignado)
                <div class="pub-territorio">
                    <span class="ter-numero">{{ $territorioAsignado->numero_completo }}</span>
                    <span class="ter-dias">{{ $diasAsignado }}d</span>
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</div>

<style>
.panel-territorios {
    max-width: 1400px;
    margin: 0 auto;
}

.panel-header {
    text-align: center;
    margin-bottom: 2rem;
}

.panel-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--text-primary, #1f2937);
    margin: 0 0 0.25rem 0;
}

.panel-subtitle {
    color: var(--text-muted, #6b7280);
    margin: 0;
}

/* Action Buttons */
.action-buttons {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
    margin-bottom: 2rem;
}

.action-btn {
    display: flex;
    align-items: center;
    gap: 1.25rem;
    padding: 1.5rem;
    border-radius: 16px;
    text-decoration: none;
    color: #000;
    transition: all 0.3s;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
}

.action-btn svg {
    stroke: #000;
}

.action-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.2);
}

.action-btn.asignar {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.action-btn.devolver {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
}

.action-icon-wrap {
    width: 56px;
    height: 56px;
    background: rgba(255,255,255,0.2);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.action-icon-wrap svg {
    width: 28px;
    height: 28px;
}

.action-content {
    flex: 1;
}

.action-text {
    display: block;
    font-size: 1.25rem;
    font-weight: 700;
    margin-bottom: 0.25rem;
}

.action-desc {
    font-size: 0.9rem;
    opacity: 0.9;
}

/* Filtros */
.tipo-filters {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1rem;
    flex-wrap: wrap;
}

.tipo-filter {
    padding: 0.5rem 1rem;
    border: 2px solid var(--border-color, #e5e7eb);
    background: var(--bg-card, #fff);
    border-radius: 25px;
    font-size: 0.85rem;
    font-weight: 500;
    color: var(--text-secondary, #6b7280);
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.tipo-filter:hover {
    border-color: #4f46e5;
    color: #4f46e5;
}

.tipo-filter.active {
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    border-color: transparent;
    color: white;
}

.filter-count {
    background: rgba(0,0,0,0.1);
    padding: 0.15rem 0.5rem;
    border-radius: 10px;
    font-size: 0.75rem;
}

.tipo-filter.active .filter-count {
    background: rgba(255,255,255,0.25);
}

/* Toolbar */
.section-toolbar {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    margin-bottom: 1rem;
    gap: 1rem;
}

.search-box {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: var(--bg-card, #fff);
    border: 1px solid var(--border-color, #e5e7eb);
    border-radius: 10px;
    flex: 1;
    max-width: 300px;
}

.search-box svg {
    width: 18px;
    height: 18px;
    color: var(--text-muted, #9ca3af);
}

.search-box input {
    border: none;
    background: transparent;
    flex: 1;
    font-size: 0.9rem;
    color: var(--text-primary, #1f2937);
    outline: none;
}

/* Grid de territorios */
.territorios-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}

/* Tarjeta de territorio - NUEVO DISENO */
.territorio-card {
    display: block;
    text-decoration: none;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 12px rgba(0,0,0,0.1);
    transition: all 0.2s;
    position: relative;
    background: var(--bg-card, #fff);
}

.territorio-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

/* Borde segun estado */
.territorio-card.estado-libre {
    border: 3px solid #10b981;
}

.territorio-card.estado-activo {
    border: 3px solid #3b82f6;
}

.territorio-card.estado-atrasado {
    border: 3px solid #ef4444;
}

.territorio-card.estado-archivo {
    border: 3px solid #6b7280;
}

/* Imagen del territorio */
.territorio-imagen {
    width: 100%;
    height: 180px;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    background-color: #e5e7eb;
    position: relative;
}

.expand-icon {
    position: absolute;
    top: 8px;
    right: 8px;
    width: 32px;
    height: 32px;
    background: rgba(0,0,0,0.5);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.2s;
}

.territorio-card:hover .expand-icon {
    opacity: 1;
}

.expand-icon svg {
    width: 18px;
    height: 18px;
    color: white;
}

/* Barra de info abajo */
.territorio-info-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem;
    background: var(--bg-card, #fff);
}

.info-left {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.territorio-numero {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--text-primary, #1f2937);
}

.asignado-nombre {
    font-size: 0.75rem;
    color: var(--text-muted, #6b7280);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 120px;
}

.info-right {
    flex-shrink: 0;
}

.estado-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
    font-size: 0.65rem;
    font-weight: 600;
    text-transform: uppercase;
}

.estado-libre { background: #dcfce7; color: #166534; }
.estado-activo { background: #dbeafe; color: #1e40af; }
.estado-atrasado { background: #fee2e2; color: #991b1b; }
.estado-archivo { background: #fef3c7; color: #92400e; }

/* Ocultar territorios filtrados */
.territorio-card.filtrado-oculto {
    display: none !important;
}

/* Publicadores section */
.publicadores-section {
    background: var(--bg-card, #fff);
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
}

.section-header-pub {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.section-header-pub h2 {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--text-primary, #1f2937);
    margin: 0;
}

.pub-count {
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

.publicadores-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 0.75rem;
}

.publicador-card {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem;
    background: var(--bg-secondary, #f9fafb);
    border-radius: 12px;
    transition: all 0.2s;
}

.publicador-card:hover {
    background: var(--bg-tertiary, #f3f4f6);
}

.pub-avatar {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.8rem;
    flex-shrink: 0;
}

.pub-details {
    flex: 1;
    min-width: 0;
}

.pub-nombre {
    display: block;
    font-weight: 600;
    font-size: 0.9rem;
    color: var(--text-primary, #1f2937);
}

.pub-telefono {
    font-size: 0.8rem;
    color: var(--text-muted, #6b7280);
    text-decoration: none;
}

.pub-telefono:hover {
    color: #3b82f6;
}

.pub-territorio {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 0.5rem 0.75rem;
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    border-radius: 10px;
    color: white;
}

.ter-numero {
    font-weight: 700;
    font-size: 0.9rem;
}

.ter-dias {
    font-size: 0.7rem;
    opacity: 0.9;
}

/* Responsive */
@media (max-width: 768px) {
    .action-buttons {
        grid-template-columns: 1fr;
    }

    .section-toolbar {
        justify-content: stretch;
    }

    .search-box {
        max-width: none;
    }

    .territorios-grid {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    }

    .territorio-imagen {
        height: 140px;
    }
}

/* ========================================
   DARK THEME
   ======================================== */
[data-theme="dark"] .panel-title {
    color: #f5f5f5;
}

[data-theme="dark"] .panel-subtitle {
    color: #a3a3a3;
}

[data-theme="dark"] .action-btn.asignar,
[data-theme="dark"] .action-btn.devolver {
    color: #000 !important;
}

[data-theme="dark"] .action-btn.asignar {
    background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
}

[data-theme="dark"] .action-btn.devolver {
    background: linear-gradient(135deg, #fb923c 0%, #f97316 100%);
}

[data-theme="dark"] .action-btn svg {
    stroke: #000 !important;
}

[data-theme="dark"] .action-btn .action-text,
[data-theme="dark"] .action-btn .action-desc {
    color: #000 !important;
}

[data-theme="dark"] .tipo-filter {
    background: #1a1a1a;
    border-color: #2d2d2d;
    color: #a3a3a3;
}

[data-theme="dark"] .tipo-filter:hover {
    border-color: #f97316;
    color: #f97316;
}

[data-theme="dark"] .tipo-filter.active {
    background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
    color: #0a0a0a;
}

[data-theme="dark"] .search-box {
    background: #1a1a1a;
    border-color: #2d2d2d;
}

[data-theme="dark"] .search-box input {
    color: #f5f5f5;
}

[data-theme="dark"] .territorio-card {
    background: #1a1a1a;
}

[data-theme="dark"] .territorio-card.estado-libre {
    border-color: #22c55e;
}

[data-theme="dark"] .territorio-card.estado-activo {
    border-color: #3b82f6;
}

[data-theme="dark"] .territorio-card.estado-atrasado {
    border-color: #ef4444;
}

[data-theme="dark"] .territorio-card.estado-archivo {
    border-color: #6b7280;
}

[data-theme="dark"] .territorio-imagen {
    background-color: #262626;
}

[data-theme="dark"] .territorio-info-bar {
    background: #1a1a1a;
}

[data-theme="dark"] .territorio-numero {
    color: #f5f5f5;
}

[data-theme="dark"] .asignado-nombre {
    color: #a3a3a3;
}

[data-theme="dark"] .estado-libre { background: rgba(34,197,94,0.2); color: #86efac; }
[data-theme="dark"] .estado-activo { background: rgba(59,130,246,0.2); color: #93c5fd; }
[data-theme="dark"] .estado-atrasado { background: rgba(239,68,68,0.2); color: #fca5a5; }
[data-theme="dark"] .estado-archivo { background: rgba(245,158,11,0.2); color: #fcd34d; }

[data-theme="dark"] .publicadores-section {
    background: #1a1a1a;
}

[data-theme="dark"] .section-header-pub h2 {
    color: #f5f5f5;
}

[data-theme="dark"] .pub-count {
    background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
    color: #0a0a0a;
}

[data-theme="dark"] .publicador-card {
    background: #262626;
}

[data-theme="dark"] .publicador-card:hover {
    background: #303030;
}

[data-theme="dark"] .pub-avatar {
    background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
    color: #0a0a0a;
}

[data-theme="dark"] .pub-nombre {
    color: #e5e5e5;
}

[data-theme="dark"] .pub-telefono {
    color: #a3a3a3;
}

[data-theme="dark"] .pub-telefono:hover {
    color: #f97316;
}
</style>

<script>
(function() {
    var filtroActual = 'todos';
    var busquedaActual = '';

    function aplicarFiltros() {
        var cards = document.querySelectorAll('.territorio-card');
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
        // Filtros de tipo
        var filtros = document.querySelectorAll('.tipo-filter');
        filtros.forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                filtros.forEach(function(b) { b.classList.remove('active'); });
                this.classList.add('active');
                filtroActual = this.getAttribute('data-tipo');
                aplicarFiltros();
            });
        });

        // Busqueda
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
