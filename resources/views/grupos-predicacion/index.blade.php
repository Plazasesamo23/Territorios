@extends('layouts.app')

@section('title', 'Grupos de Predicacion')

@section('content')

@php
    // Calcular año de servicio (Sep-Ago)
    $mes = now()->month;
    $ano = now()->year;
    if ($mes >= 9) {
        $anoServicio = $ano . '/' . ($ano + 1);
    } else {
        $anoServicio = ($ano - 1) . '/' . $ano;
    }
@endphp

<div class="grupos-page">
    <!-- Header -->
    <div class="page-header">
        <div class="header-left">
            <h1>Grupos de Predicacion</h1>
            <span class="ano-servicio">{{ $anoServicio }}</span>
        </div>
        <div class="header-actions">
            <a href="{{ route('grupos-predicacion.historial') }}" class="btn-historial">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
                Historial
            </a>
            <button onclick="guardarHistorial()" class="btn-secondary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                    <polyline points="17 21 17 13 7 13 7 21"/>
                    <polyline points="7 3 7 8 15 8"/>
                </svg>
                Cerrar Ano
            </button>
            <button onclick="generarAutomatico()" class="btn-generar">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/>
                </svg>
                Generar Automatico
            </button>
            <button onclick="window.print()" class="btn-exportar">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="7 10 12 15 17 10"/>
                    <line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
                Exportar PDF
            </button>
        </div>
    </div>

    <!-- Layout principal -->
    <div class="main-layout">
        <!-- Grupos -->
        <div class="grupos-section">
            <!-- Titulo documento (para impresión) -->
            <div class="doc-title print-only">
                GRUPOS DE PREDICACION - {{ strtoupper($congregacionActiva->nombre ?? 'CONGREGACION') }} - {{ $anoServicio }}
            </div>

            <div class="grupos-grid">
                @foreach($grupos as $grupo)
                @php
                    $ordenados = $grupo->publicadores->sortByDesc(function($p) {
                        if ($p->es_superintendente) return 3;
                        if ($p->es_auxiliar) return 2;
                        return 0;
                    });
                @endphp
                <div class="grupo-box" data-grupo-id="{{ $grupo->id }}">
                    <div class="grupo-header">
                        <span class="grupo-label">GRUPO</span>
                        <span class="grupo-num">{{ $grupo->numero }}</span>
                        <span class="grupo-count">{{ $grupo->publicadores->count() }}</span>
                    </div>

                    <div class="grupo-lista" data-grupo-id="{{ $grupo->id }}">
                        @foreach($ordenados as $pub)
                        @php
                            $roles = [];
                            if ($pub->es_superintendente) $roles[] = 'SUP';
                            if ($pub->es_auxiliar) $roles[] = 'AUX';
                            // Mostrar AN/SM solo si NO es SUP ni AUX
                            if (!$pub->es_superintendente && !$pub->es_auxiliar) {
                                if ($pub->es_anciano) $roles[] = 'AN';
                                elseif ($pub->es_siervo_ministerial) $roles[] = 'SM';
                            }
                            if ($pub->es_precursor) $roles[] = 'PR';
                            $rolTexto = count($roles) > 0 ? ' (' . implode('-', $roles) . ')' : '';
                            $esLider = $pub->es_superintendente || $pub->es_auxiliar;
                            $esPrecursor = $pub->es_precursor && !$esLider;
                            $esNombramiento = !$esLider && ($pub->es_anciano || $pub->es_siervo_ministerial);
                            $dataRol = $pub->es_superintendente ? 'sup' : ($pub->es_auxiliar ? 'aux' : '');
                        @endphp
                        <div class="pub-item {{ $pub->es_superintendente ? 'superintendente' : '' }} {{ $pub->es_auxiliar ? 'auxiliar' : '' }} {{ $esPrecursor ? 'precursor' : '' }} {{ $esNombramiento ? 'nombramiento' : '' }}"
                             draggable="true"
                             data-id="{{ $pub->id }}"
                             data-nombre="{{ $pub->nombre_completo }}"
                             data-precursor="{{ $pub->es_precursor ? '1' : '0' }}"
                             data-rol="{{ $dataRol }}">
                            <span class="pub-nombre">{{ $pub->nombre_completo }}{{ $rolTexto }}</span>
                            <div class="pub-actions no-print">
                                <button class="btn-rol {{ $pub->es_superintendente ? 'activo' : '' }}" onclick="toggleRol({{ $pub->id }}, 'superintendente', {{ $grupo->id }})" title="Superintendente">S</button>
                                <button class="btn-rol {{ $pub->es_auxiliar ? 'activo' : '' }}" onclick="toggleRol({{ $pub->id }}, 'auxiliar', {{ $grupo->id }})" title="Auxiliar">A</button>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="grupo-total">
                        <span>TOTAL</span>
                        <span class="total-num">{{ $grupo->publicadores->count() }}</span>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Footer documento -->
            <div class="doc-footer">
                <div class="total-congregacion">
                    TOTAL publicadores: <span class="total-box">{{ $grupos->sum(fn($g) => $g->publicadores->count()) + $sinGrupo->count() }}</span>
                </div>
                <div class="fecha-actualizacion">
                    Actualizado {{ now()->format('d-m-Y') }}
                </div>
            </div>
        </div>

        <!-- Panel publicadores sin grupo -->
        <div class="panel-sin-grupo no-print">
            <div class="panel-header">
                <span class="panel-title">Sin Grupo</span>
                <span class="panel-count">{{ $sinGrupo->count() }}</span>
            </div>
            <div class="panel-search">
                <input type="text" id="buscar-pub" placeholder="Buscar..." onkeyup="filtrarPublicadores()">
            </div>
            <div class="panel-lista" data-grupo-id="">
                @foreach($sinGrupo->sortBy('nombre') as $pub)
                <div class="pub-item sin-grupo"
                     draggable="true"
                     data-id="{{ $pub->id }}"
                     data-nombre="{{ $pub->nombre_completo }}"
                     data-precursor="{{ $pub->es_precursor ? '1' : '0' }}">
                    <span class="pub-nombre">{{ $pub->nombre_completo }}</span>
                    @if($pub->es_precursor)<span class="tag-pr">PR</span>@endif
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<style>
.grupos-page {
    max-width: 1200px;
    margin: 0 auto;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.page-header h1 {
    font-size: 1.3rem;
    font-weight: 700;
    color: var(--text-primary, #1f2937);
    margin: 0;
}

.ano-servicio {
    padding: 0.25rem 0.6rem;
    background: #4a6da7;
    color: white;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 600;
}

.btn-exportar {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.4rem 0.8rem;
    background: #4a6da7;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
}

.btn-exportar:hover {
    background: #3d5a8a;
}

.btn-secondary {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.4rem 0.8rem;
    background: #6b7280;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
}

.btn-secondary:hover {
    background: #4b5563;
}

.btn-generar {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.4rem 0.8rem;
    background: #4a6da7;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
}

.btn-generar:hover {
    background: #3d5a8a;
}

.btn-historial {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.4rem 0.8rem;
    background: #4a6da7;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
}

.btn-historial:hover {
    background: #3d5a8a;
    color: white;
}

/* Layout */
.main-layout {
    display: grid;
    grid-template-columns: 1fr 220px;
    gap: 1rem;
    align-items: start;
}

/* Grupos */
.grupos-section {
    background: white;
    border-radius: 10px;
    padding: 1rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.doc-title {
    display: none;
}

.grupos-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.75rem;
    margin-bottom: 1rem;
}

.grupo-box {
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    overflow: hidden;
}

.grupo-header {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.4rem 0.5rem;
    background: linear-gradient(135deg, #4a6da7 0%, #3d5a8a 100%);
}

.grupo-label {
    background: rgba(255,255,255,0.25);
    color: white;
    padding: 0.15rem 0.4rem;
    border-radius: 4px;
    font-size: 0.7rem;
    font-weight: 700;
}

.grupo-num {
    color: white;
    font-size: 0.85rem;
    font-weight: 700;
    flex: 1;
}

.grupo-count {
    background: rgba(255,255,255,0.25);
    color: white;
    padding: 0.1rem 0.4rem;
    border-radius: 10px;
    font-size: 0.7rem;
    font-weight: 600;
}

.grupo-lista {
    min-height: 150px;
    padding: 0.4rem;
    background: #fafafa;
}

.grupo-lista.drag-over {
    background: #f4f7fb;
}

.pub-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.3rem 0.4rem;
    margin-bottom: 0.25rem;
    background: white;
    border-radius: 4px;
    font-size: 0.75rem;
    cursor: grab;
    border: 1px solid transparent;
}

.pub-item:hover {
    border-color: #d1d5db;
}

.pub-item.dragging {
    opacity: 0.5;
}

.pub-item.superintendente {
    color: #343a40;
    font-weight: 600;
    background: #f8f9fa;
}

.pub-item.auxiliar {
    color: #3d5a8a;
    font-weight: 600;
    background: #f4f7fb;
}

.pub-item.precursor {
    color: #3d5a8a;
}

.pub-item.nombramiento {
    font-weight: 600;
    color: #3d5a8a;
    background: #f5f3ff;
}

.pub-nombre {
    flex: 1;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.pub-actions {
    display: flex;
    gap: 0.2rem;
    opacity: 0;
    transition: opacity 0.15s;
}

.pub-item:hover .pub-actions {
    opacity: 1;
}

.btn-rol {
    width: 18px;
    height: 18px;
    border: 1px solid #d1d5db;
    border-radius: 3px;
    background: white;
    font-size: 0.6rem;
    font-weight: 700;
    cursor: pointer;
    color: #9ca3af;
}

.btn-rol:hover {
    border-color: #4a6da7;
    color: #4a6da7;
}

.btn-rol.activo {
    background: #4a6da7;
    border-color: #4a6da7;
    color: white;
}

.grupo-total {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.35rem 0.5rem;
    border-top: 1px solid #e5e7eb;
    font-size: 0.7rem;
    font-weight: 600;
    background: white;
}

.grupo-total span:first-child {
    background: #f3f4f6;
    padding: 0.15rem 0.4rem;
    border-radius: 3px;
}

.total-num {
    background: #e5e7eb;
    padding: 0.15rem 0.4rem;
    border-radius: 3px;
}

/* Footer */
.doc-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 0.75rem;
    border-top: 1px solid #e5e7eb;
    font-size: 0.8rem;
}

.total-congregacion {
    font-weight: 500;
}

.total-box {
    background: #4a6da7;
    color: white;
    padding: 0.2rem 0.5rem;
    border-radius: 4px;
    font-weight: 700;
}

.fecha-actualizacion {
    color: #6b7280;
    font-size: 0.75rem;
}

/* Panel sin grupo */
.panel-sin-grupo {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    overflow: hidden;
    position: sticky;
    top: 1rem;
    max-height: calc(100vh - 120px);
    display: flex;
    flex-direction: column;
}

.panel-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.6rem 0.75rem;
    background: #6b7280;
    color: white;
}

.panel-title {
    font-weight: 600;
    font-size: 0.85rem;
}

.panel-count {
    background: rgba(255,255,255,0.2);
    padding: 0.15rem 0.5rem;
    border-radius: 10px;
    font-size: 0.75rem;
}

.panel-search {
    padding: 0.5rem;
    border-bottom: 1px solid #e5e7eb;
}

.panel-search input {
    width: 100%;
    padding: 0.4rem 0.6rem;
    border: 1px solid #e5e7eb;
    border-radius: 4px;
    font-size: 0.8rem;
}

.panel-lista {
    flex: 1;
    overflow-y: auto;
    padding: 0.5rem;
    min-height: 200px;
}

.panel-lista.drag-over {
    background: #f3f4f6;
}

.pub-item.sin-grupo {
    background: #fef3c7;
    border: 1px solid #fcd34d;
}

.tag-pr {
    background: #343a40;
    color: white;
    padding: 0.1rem 0.25rem;
    border-radius: 3px;
    font-size: 0.55rem;
    font-weight: 700;
}

/* Dark theme */
[data-theme="dark"] .page-header h1 { color: #f5f5f5; }
[data-theme="dark"] .ano-servicio, [data-theme="dark"] .btn-exportar { background: #4a6da7; }
[data-theme="dark"] .btn-secondary { background: #525252; }
[data-theme="dark"] .btn-secondary:hover { background: #404040; }
[data-theme="dark"] .btn-generar { background: #3d5a8a; }
[data-theme="dark"] .btn-generar:hover { background: #2d4266; }
[data-theme="dark"] .btn-historial { background: #3d5a8a; }
[data-theme="dark"] .btn-historial:hover { background: #2d4266; }
[data-theme="dark"] .grupos-section { background: #1a1a1a; }
[data-theme="dark"] .grupo-box { border-color: #2d2d2d; }
[data-theme="dark"] .grupo-header { background: linear-gradient(135deg, #4a6da7 0%, #3d5a8a 100%); }
[data-theme="dark"] .grupo-lista { background: #262626; }
[data-theme="dark"] .pub-item { background: #1a1a1a; color: #e5e5e5; }
[data-theme="dark"] .pub-item.superintendente { background: rgba(220,38,38,0.15); color: #f87171; }
[data-theme="dark"] .pub-item.auxiliar { background: rgba(37,99,235,0.15); color: #5c7fb8; }
[data-theme="dark"] .pub-item.precursor { color: #4ade80; }
[data-theme="dark"] .pub-item.nombramiento { background: rgba(124,58,237,0.15); color: #a8c0de; }
[data-theme="dark"] .grupo-total { background: #1a1a1a; border-color: #2d2d2d; }
[data-theme="dark"] .doc-footer { border-color: #2d2d2d; }
[data-theme="dark"] .total-box { background: #4a6da7; }
[data-theme="dark"] .panel-sin-grupo { background: #1a1a1a; }
[data-theme="dark"] .panel-header { background: #404040; }
[data-theme="dark"] .panel-search input { background: #262626; border-color: #404040; color: #e5e5e5; }
[data-theme="dark"] .pub-item.sin-grupo { background: rgba(245,158,11,0.15); border-color: #4a6da7; color: #fcd34d; }

/* Print - Solo grupos, sin elementos de la app */
@media print {
    @page { size: A4 portrait; margin: 8mm; }

    * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }

    /* OCULTAR ELEMENTOS DE LA APLICACION */
    .no-print,
    .page-header,
    .panel-sin-grupo,
    .pub-actions,
    .grupo-count {
        display: none !important;
    }

    /* Ocultar layout de la app (navbar, sidebar, etc) */
    body > header,
    body > nav,
    body > aside,
    body > footer,
    #app > header,
    #app > nav,
    #app > aside,
    .app-sidebar,
    .app-header,
    .app-nav,
    .sidebar,
    .navbar,
    .topbar,
    .bottom-bar {
        display: none !important;
    }

    body, html {
        background: white !important;
        margin: 0 !important;
        padding: 0 !important;
        font-size: 8pt !important;
    }

    .grupos-page {
        position: absolute !important;
        top: 0 !important;
        left: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
        background: white !important;
    }

    .main-layout { display: block !important; }

    .grupos-section {
        box-shadow: none !important;
        padding: 0 !important;
        background: white !important;
        border-radius: 0 !important;
        max-width: 250mm !important;
        margin: 0 auto !important;
    }

    .doc-title {
        display: block !important;
        background: white !important;
        color: #333 !important;
        padding: 4px 10px !important;
        text-align: center !important;
        font-weight: 700 !important;
        font-size: 11pt !important;
        margin-bottom: 8px !important;
        border-bottom: 2px solid #333 !important;
    }

    .grupos-grid {
        display: grid !important;
        grid-template-columns: repeat(3, 1fr) !important;
        gap: 5px !important;
        margin-bottom: 8px !important;
        max-width: 190mm !important;
        margin-left: auto !important;
        margin-right: auto !important;
    }

    .grupo-box {
        border: 1px solid #666 !important;
        border-radius: 0 !important;
        page-break-inside: avoid !important;
    }

    /* Colores diferentes por grupo */
    .grupo-box:nth-child(1) .grupo-header { background: #e3f2fd !important; border-color: #1976d2 !important; }
    .grupo-box:nth-child(1) { border-color: #1976d2 !important; }
    .grupo-box:nth-child(2) .grupo-header { background: #fff3e0 !important; border-color: #f57c00 !important; }
    .grupo-box:nth-child(2) { border-color: #f57c00 !important; }
    .grupo-box:nth-child(3) .grupo-header { background: #e8f5e9 !important; border-color: #388e3c !important; }
    .grupo-box:nth-child(3) { border-color: #388e3c !important; }
    .grupo-box:nth-child(4) .grupo-header { background: #fce4ec !important; border-color: #c2185b !important; }
    .grupo-box:nth-child(4) { border-color: #c2185b !important; }
    .grupo-box:nth-child(5) .grupo-header { background: #ede7f6 !important; border-color: #7b1fa2 !important; }
    .grupo-box:nth-child(5) { border-color: #7b1fa2 !important; }
    .grupo-box:nth-child(6) .grupo-header { background: #e0f2f1 !important; border-color: #00796b !important; }
    .grupo-box:nth-child(6) { border-color: #00796b !important; }

    .grupo-header {
        border-bottom: 1px solid #666 !important;
        padding: 4px 6px !important;
    }

    .grupo-label {
        background: #555 !important;
        color: white !important;
        padding: 1px 4px !important;
        font-size: 6pt !important;
    }

    .grupo-num {
        color: #000 !important;
        font-size: 9pt !important;
        font-weight: 700 !important;
        background: transparent !important;
        padding: 0 4px !important;
    }

    .grupo-lista {
        min-height: 105mm !important;
        padding: 4px 6px !important;
        background: #fff !important;
    }

    .pub-item,
    .pub-item.superintendente,
    .pub-item.auxiliar,
    .pub-item.precursor,
    .pub-item.nombramiento,
    .pub-item.lider,
    .grupo-lista .pub-item {
        background: #fff !important;
        background-color: #fff !important;
        border: none !important;
        padding: 0 !important;
        margin: 0 !important;
        font-size: 10pt !important;
        line-height: 1.3 !important;
        cursor: default !important;
        display: block !important;
        color: #000 !important;
    }

    .pub-nombre { display: inline !important; color: #000 !important; }

    .pub-item.superintendente, .pub-item.superintendente .pub-nombre { color: #cc0000 !important; font-weight: 700 !important; }
    .pub-item.auxiliar, .pub-item.auxiliar .pub-nombre { color: #0066cc !important; font-weight: 700 !important; }
    .pub-item.nombramiento, .pub-item.nombramiento .pub-nombre { color: #6b21a8 !important; font-weight: 700 !important; }
    .pub-item.precursor, .pub-item.precursor .pub-nombre { color: #008800 !important; }

    .grupo-total {
        border-top: 1px solid #ccc !important;
        padding: 3px 5px !important;
        background: white !important;
        font-size: 7pt !important;
        color: #333 !important;
    }

    .grupo-total span:first-child { background: transparent !important; padding: 0 !important; }
    .total-num { background: #eee !important; color: #333 !important; padding: 1px 4px !important; font-size: 7pt !important; }

    .doc-footer {
        border-top: 1px solid #ccc !important;
        padding-top: 5px !important;
        margin-top: 4px !important;
        font-size: 8pt !important;
        display: flex !important;
        justify-content: space-between !important;
        background: white !important;
    }

    .total-box { background: #eee !important; color: #333 !important; padding: 2px 6px !important; font-size: 8pt !important; border: 1px solid #ccc !important; }
    .fecha-actualizacion { color: #666 !important; font-size: 7pt !important; }
}

/* Responsive */
@media (max-width: 900px) {
    .main-layout { grid-template-columns: 1fr; }
    .grupos-grid { grid-template-columns: repeat(2, 1fr); }
    .panel-sin-grupo { position: static; max-height: 300px; }
}

@media (max-width: 600px) {
    .grupos-grid { grid-template-columns: 1fr; }
}
</style>

<script>
const csrfToken = '{{ csrf_token() }}';
let draggedElement = null;

document.addEventListener('DOMContentLoaded', initDragDrop);

function initDragDrop() {
    document.querySelectorAll('.pub-item').forEach(el => {
        el.addEventListener('dragstart', handleDragStart);
        el.addEventListener('dragend', handleDragEnd);
    });

    document.querySelectorAll('.grupo-lista, .panel-lista').forEach(zone => {
        zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('drag-over'); });
        zone.addEventListener('dragleave', e => { if (!zone.contains(e.relatedTarget)) zone.classList.remove('drag-over'); });
        zone.addEventListener('drop', handleDrop);
    });
}

function handleDragStart(e) {
    draggedElement = this;
    this.classList.add('dragging');
}

function handleDragEnd() {
    this.classList.remove('dragging');
    document.querySelectorAll('.drag-over').forEach(z => z.classList.remove('drag-over'));
}

function handleDrop(e) {
    e.preventDefault();
    this.classList.remove('drag-over');
    if (!draggedElement) return;

    const grupoId = this.dataset.grupoId || null;
    const pubId = draggedElement.dataset.id;

    // Mover elemento
    this.appendChild(draggedElement);

    // Resetear estilos del item
    draggedElement.classList.remove('lider', 'sin-grupo');
    if (!grupoId) {
        draggedElement.classList.add('sin-grupo');
    }

    // Ocultar botones S/A si va a sin grupo
    const actions = draggedElement.querySelector('.pub-actions');
    if (actions) actions.style.display = grupoId ? '' : 'none';

    // Actualizar contadores
    updateCounters();

    // Guardar en servidor
    fetch('/grupos-predicacion/mover', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ publicador_id: pubId, grupo_id: grupoId })
    });
}

function updateCounters() {
    document.querySelectorAll('.grupo-box').forEach(box => {
        const count = box.querySelectorAll('.pub-item').length;
        box.querySelector('.grupo-count').textContent = count;
        box.querySelector('.total-num').textContent = count;
    });
    document.querySelector('.panel-count').textContent = document.querySelectorAll('.panel-lista .pub-item').length;

    const total = document.querySelectorAll('.pub-item').length;
    document.querySelector('.total-box').textContent = total;
}

function toggleRol(pubId, rol, grupoId) {
    fetch('/grupos-predicacion/toggle-rol', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ publicador_id: pubId, rol: rol, grupo_id: grupoId })
    }).then(() => location.reload());
}

function filtrarPublicadores() {
    const term = document.getElementById('buscar-pub').value.toLowerCase();
    document.querySelectorAll('.panel-lista .pub-item').forEach(el => {
        el.style.display = el.dataset.nombre.toLowerCase().includes(term) ? '' : 'none';
    });
}

function guardarHistorial() {
    if (!confirm('¿Guardar el historial del ano actual? Esto creara un snapshot de los grupos que se usara para la generacion automatica.')) return;

    fetch('/grupos-predicacion/guardar-historial', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert(data.mensaje);
        } else {
            alert(data.error || 'Error al guardar');
        }
    })
    .catch(err => alert('Error de conexion'));
}

function generarAutomatico() {
    if (!confirm('¿Generar grupos automaticamente?\n\n- Los SUP/AUX se mantienen en sus grupos\n- Los precursores se distribuyen equitativamente\n- Se evita repetir las mismas parejas de anos anteriores')) return;

    fetch('/grupos-predicacion/generar-automatico', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert('Grupos generados correctamente');
            location.reload();
        } else {
            alert(data.error || 'Error al generar');
        }
    })
    .catch(err => alert('Error de conexion'));
}
</script>

@endsection
