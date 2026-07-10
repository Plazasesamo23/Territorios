@extends('layouts.app')

@section('title', 'Autorizaciones - Reuniones')

@section('content')

<div class="page-xl">
    <div class="flex justify-between items-center mb-2">
        <div>
            <h1 class="page-title">Autorizaciones</h1>
            <p class="page-subtitle">Gestiona quien puede hacer cada tipo de asignacion</p>
        </div>
        <a href="{{ route('reuniones.index') }}" class="btn btn-ghost">Volver</a>
    </div>

    @if(session('success'))
    <div class="config-info-box mt-1 mb-2">{{ session('success') }}</div>
    @endif

    <div class="auth-guide">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0;margin-top:2px"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
        <div>
            <strong>Cómo funciona:</strong> Usa el botón <strong>+</strong> de cada panel para buscar y añadir publicadores. Toca <strong>un nombre</strong> para ver sus estadísticas o quitarlo del panel.
        </div>
    </div>

    <div class="auth-layout">

        {{-- IZQUIERDA: Paneles de asignaciones --}}
        <div class="auth-left">

            <div class="section-title" style="color: var(--color-global);">Roles globales</div>
            <div class="auth-panels-row">
                <div class="auth-panel auth-panel-editable auth-panel-color-global">
                    <div class="auth-panel-header">
                        <span class="auth-panel-title">Presidente</span>
                        <span class="auth-panel-count" data-count></span>
                        <button type="button" class="auth-btn-add" onclick="abrirModalAgregar('presidente')" title="Agregar publicador">+</button>
                    </div>
                    <div class="auth-panel-list auth-dropzone" data-campo="presidente">
                        @foreach($autorizaciones['presidente'] as $pub)
                        <div class="auth-chip" draggable="true" data-id="{{ $pub->id }}" data-nombre="{{ strtolower($pub->nombre_completo) }}">{{ $pub->nombre_completo }}</div>
                        @endforeach
                    </div>
                </div>

                <div class="auth-panel auth-panel-editable auth-panel-color-global">
                    <div class="auth-panel-header">
                        <span class="auth-panel-title">Oraciones</span>
                        <span class="auth-panel-count" data-count></span>
                        <button type="button" class="auth-btn-add" onclick="abrirModalAgregar('oracion')" title="Agregar publicador">+</button>
                    </div>
                    <div class="auth-panel-list auth-dropzone" data-campo="oracion">
                        @foreach($autorizaciones['oracion'] as $pub)
                        <div class="auth-chip" draggable="true" data-id="{{ $pub->id }}" data-nombre="{{ strtolower($pub->nombre_completo) }}">{{ $pub->nombre_completo }}</div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="auth-panels-row">
                <div class="auth-panel auth-panel-editable auth-panel-color-global">
                    <div class="auth-panel-header">
                        <span class="auth-panel-title">Conductor estudio</span>
                        <span class="auth-panel-count" data-count></span>
                        <button type="button" class="auth-btn-add" onclick="abrirModalAgregar('conductor_estudio')" title="Agregar publicador">+</button>
                    </div>
                    <div class="auth-panel-list auth-dropzone" data-campo="conductor_estudio">
                        @foreach($autorizaciones['conductor_estudio'] as $pub)
                        <div class="auth-chip" draggable="true" data-id="{{ $pub->id }}" data-nombre="{{ strtolower($pub->nombre_completo) }}">{{ $pub->nombre_completo }}</div>
                        @endforeach
                    </div>
                </div>

                <div class="auth-panel auth-panel-editable auth-panel-color-global">
                    <div class="auth-panel-header">
                        <span class="auth-panel-title">Lector estudio</span>
                        <span class="auth-panel-count" data-count></span>
                        <button type="button" class="auth-btn-add" onclick="abrirModalAgregar('lector_estudio')" title="Agregar publicador">+</button>
                    </div>
                    <div class="auth-panel-list auth-dropzone" data-campo="lector_estudio">
                        @foreach($autorizaciones['lector_estudio'] as $pub)
                        <div class="auth-chip" draggable="true" data-id="{{ $pub->id }}" data-nombre="{{ strtolower($pub->nombre_completo) }}">{{ $pub->nombre_completo }}</div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="section-title" style="color: var(--color-tesoros-text);">Tesoros de la Biblia</div>
            <div class="auth-panels-row">
                <div class="auth-panel auth-panel-editable auth-panel-color-tesoros">
                    <div class="auth-panel-header">
                        <span class="auth-panel-title">Tesoros</span>
                        <span class="auth-panel-count" data-count></span>
                        <button type="button" class="auth-btn-add" onclick="abrirModalAgregar('tesoros')" title="Agregar publicador">+</button>
                    </div>
                    <div class="auth-panel-info">Discurso de 10 min</div>
                    <div class="auth-panel-list auth-dropzone" data-campo="tesoros">
                        @foreach($autorizaciones['tesoros'] as $pub)
                        <div class="auth-chip" draggable="true" data-id="{{ $pub->id }}" data-nombre="{{ strtolower($pub->nombre_completo) }}">{{ $pub->nombre_completo }}</div>
                        @endforeach
                    </div>
                </div>

                <div class="auth-panel auth-panel-editable auth-panel-color-tesoros">
                    <div class="auth-panel-header">
                        <span class="auth-panel-title">Perlas</span>
                        <span class="auth-panel-count" data-count></span>
                        <button type="button" class="auth-btn-add" onclick="abrirModalAgregar('perlas')" title="Agregar publicador">+</button>
                    </div>
                    <div class="auth-panel-info">Busquemos perlas escondidas</div>
                    <div class="auth-panel-list auth-dropzone" data-campo="perlas">
                        @foreach($autorizaciones['perlas'] as $pub)
                        <div class="auth-chip" draggable="true" data-id="{{ $pub->id }}" data-nombre="{{ strtolower($pub->nombre_completo) }}">{{ $pub->nombre_completo }}</div>
                        @endforeach
                    </div>
                </div>

                <div class="auth-panel auth-panel-editable auth-panel-color-tesoros">
                    <div class="auth-panel-header">
                        <span class="auth-panel-title">Lectura biblica</span>
                        <span class="auth-panel-count" data-count></span>
                        <button type="button" class="auth-btn-add" onclick="abrirModalAgregar('lectura')" title="Agregar publicador">+</button>
                    </div>
                    <div class="auth-panel-list auth-dropzone" data-campo="lectura">
                        @foreach($autorizaciones['lectura'] as $pub)
                        <div class="auth-chip" draggable="true" data-id="{{ $pub->id }}" data-nombre="{{ strtolower($pub->nombre_completo) }}">{{ $pub->nombre_completo }}</div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="section-title" style="color: var(--color-maestros-text);">Seamos mejores maestros</div>
            <div class="auth-panels-row">
                <div class="auth-panel auth-panel-editable auth-panel-color-maestros">
                    <div class="auth-panel-header">
                        <span class="auth-panel-title">Partes de maestros</span>
                        <span class="auth-panel-count" data-count></span>
                        <button type="button" class="auth-btn-add" onclick="abrirModalAgregar('maestros')" title="Agregar publicador">+</button>
                    </div>
                    <div class="auth-panel-info">Conversaciones, revisitas, discipulos, creencias</div>
                    <div class="auth-panel-list auth-dropzone" data-campo="maestros">
                        @foreach($autorizaciones['maestros'] as $pub)
                        <div class="auth-chip" draggable="true" data-id="{{ $pub->id }}" data-nombre="{{ strtolower($pub->nombre_completo) }}">
                            {{ $pub->nombre_completo }}
                            <span class="auth-chip-genero">{{ $pub->genero }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="auth-panels-row">
                <div class="auth-panel auth-panel-editable auth-panel-color-maestros">
                    <div class="auth-panel-header">
                        <span class="auth-panel-title">Discurso maestros</span>
                        <span class="auth-panel-count" data-count></span>
                        <button type="button" class="auth-btn-add" onclick="abrirModalAgregar('discurso_maestros')" title="Agregar publicador">+</button>
                    </div>
                    <div class="auth-panel-info">Discurso sin ayudante (solo varones)</div>
                    <div class="auth-panel-list auth-dropzone" data-campo="discurso_maestros">
                        @foreach($autorizaciones['discurso_maestros'] as $pub)
                        <div class="auth-chip" draggable="true" data-id="{{ $pub->id }}" data-nombre="{{ strtolower($pub->nombre_completo) }}">{{ $pub->nombre_completo }}</div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="section-title" style="color: var(--color-vida-text);">Nuestra vida cristiana</div>
            <div class="auth-panels-row">
                <div class="auth-panel auth-panel-editable auth-panel-color-vida">
                    <div class="auth-panel-header">
                        <span class="auth-panel-title">Discurso Vida Cristiana</span>
                        <span class="auth-panel-count" data-count></span>
                        <button type="button" class="auth-btn-add" onclick="abrirModalAgregar('discurso_vida')" title="Agregar publicador">+</button>
                    </div>
                    <div class="auth-panel-list auth-dropzone" data-campo="discurso_vida">
                        @foreach($autorizaciones['discurso_vida'] as $pub)
                        <div class="auth-chip" draggable="true" data-id="{{ $pub->id }}" data-nombre="{{ strtolower($pub->nombre_completo) }}">{{ $pub->nombre_completo }}</div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="section-title" style="color: #d97706;">Grupo de emergencia</div>
            <div class="auth-panels-row">
                <div class="auth-panel auth-panel-editable auth-panel-color-emergency">
                    <div class="auth-panel-header">
                        <span class="auth-panel-title">Voluntarios de emergencia</span>
                        <span class="auth-panel-count" data-count></span>
                        <button type="button" class="auth-btn-add" onclick="abrirModalAgregar('voluntario_emergencia')" title="Agregar publicador">+</button>
                    </div>
                    <div class="auth-panel-info">Disponibles para reemplazos de ultimo momento en partes de maestros. Rotacion independiente del ciclo normal.</div>
                    <div class="auth-panel-list auth-dropzone" data-campo="voluntario_emergencia">
                        @foreach($autorizaciones['voluntario_emergencia'] as $pub)
                        <div class="auth-chip auth-chip-emergency" draggable="true" data-id="{{ $pub->id }}" data-nombre="{{ strtolower($pub->nombre_completo) }}">{{ $pub->nombre_completo }}</div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="section-title" style="color: #ef4444;">Excluidos</div>
            <div class="auth-panels-row">
                <div class="auth-panel auth-panel-editable auth-panel-danger auth-panel-color-danger">
                    <div class="auth-panel-header">
                        <span class="auth-panel-title">Excluidos de reuniones</span>
                        <span class="auth-panel-count" data-count></span>
                        <button type="button" class="auth-btn-add" onclick="abrirModalAgregar('excluido_reuniones')" title="Agregar publicador">+</button>
                    </div>
                    <div class="auth-panel-info">No recibiran ninguna asignacion</div>
                    <div class="auth-panel-list auth-dropzone" data-campo="excluido_reuniones">
                        @foreach($autorizaciones['excluidos'] as $pub)
                        <div class="auth-chip auth-chip-excluded" draggable="true" data-id="{{ $pub->id }}" data-nombre="{{ strtolower($pub->nombre_completo) }}">{{ $pub->nombre_completo }}</div>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>

        {{-- DERECHA: Pool de publicadores (sticky) --}}
        <div class="auth-right">
            <div class="auth-pool-sticky">
                <div class="auth-panel auth-panel-editable" style="border-top-color: var(--text-muted);">
                    <div class="auth-panel-header">
                        <span class="auth-panel-title">Publicadores</span>
                        <span class="auth-panel-count" data-count></span>
                    </div>
                    <div class="auth-panel-info">Usa el botón + de cada panel para añadir desde esta lista</div>

                    <div style="padding: 0 0.5rem;">
                        <input type="text" id="auth-search" class="form-input" placeholder="Buscar..." style="padding: 0.35rem 0.6rem; font-size: 0.8rem; width: 100%;">
                    </div>

                    <div class="auth-panel-list auth-dropzone auth-pool-list" data-campo="pool">
                        @foreach($pool as $pub)
                        <div class="auth-chip" draggable="true" data-id="{{ $pub->id }}" data-nombre="{{ strtolower($pub->nombre_completo) }}">
                            {{ $pub->nombre_completo }}
                            <span class="auth-chip-genero">{{ $pub->genero }}</span>
                            @if($pub->es_anciano)<span class="auth-chip-tag">A</span>@endif
                            @if($pub->es_siervo_ministerial)<span class="auth-chip-tag">SM</span>@endif
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- Modal para agregar publicador (alternativa al drag & drop) --}}
<div id="modal-agregar" class="modal-backdrop" style="display:none;" onclick="if(event.target===this)cerrarModalAgregar()">
    <div class="modal" style="max-width:400px;" role="dialog" aria-modal="true" aria-label="Agregar publicador">
        <div class="modal-header">
            <h3 class="modal-title" id="modal-agregar-titulo">Agregar publicador</h3>
            <button class="modal-close" onclick="cerrarModalAgregar()">&times;</button>
        </div>
        <div class="modal-body">
            <input type="text" id="modal-agregar-buscar" class="form-input" placeholder="Buscar publicador..." style="margin-bottom: 0.75rem;">
            <div id="modal-agregar-lista" class="list-flat" style="max-height: 350px; overflow-y: auto;"></div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const poolData = @json($poolJson);
    const statsData = @json($statsJson);
    const promedios = @json($promediosPorTipo);

    // Mapeo de campo de panel a tipo(s) de historial para stats
    const campoATipos = {
        presidente: ['presidente'],
        oracion: ['oracion_inicio', 'oracion_final'],
        tesoros: ['discurso_tesoros'],
        perlas: ['perlas'],
        lectura: ['lectura'],
        maestros: ['empiece_conversaciones', 'haga_revisitas', 'haga_discipulos', 'explique_creencias'],
        discurso_maestros: ['discurso_maestros'],
        discurso_vida: ['discurso_vida'],
        conductor_estudio: ['conductor_estudio'],
        lector_estudio: ['lector_estudio'],
    };

    const nombres = {
        presidente: 'Presidente', oracion: 'Oraciones', conductor_estudio: 'Conductor estudio',
        lector_estudio: 'Lector estudio', tesoros: 'Tesoros', perlas: 'Perlas',
        lectura: 'Lectura biblica', maestros: 'Partes de maestros', discurso_maestros: 'Discurso maestros',
        discurso_vida: 'Discurso Vida Cristiana', voluntario_emergencia: 'Voluntarios de emergencia',
        excluido_reuniones: 'Excluidos de reuniones'
    };

    function getStatsParaPub(pubId, campo) {
        const tipos = campoATipos[campo];
        if (!tipos) return { total: 0, promedio: 0, pct: 0, ultima: null };
        let total = 0;
        let ultima = null;
        let promedioSum = 0;
        tipos.forEach(t => {
            const key = pubId + '-' + t;
            if (statsData[key]) {
                total += statsData[key].total;
                if (!ultima || statsData[key].ultima > ultima) ultima = statsData[key].ultima;
            }
            promedioSum += (promedios[t] || 0);
        });
        const prom = promedioSum || 0;
        const pct = prom > 0 ? Math.round((total - prom) / prom * 100) : 0;
        return { total, promedio: Math.round(prom * 10) / 10, pct, ultima };
    }

    function updateCounts() {
        document.querySelectorAll('.auth-panel').forEach(panel => {
            const list = panel.querySelector('.auth-panel-list');
            const counter = panel.querySelector('[data-count]');
            if (counter && list) {
                counter.textContent = list.querySelectorAll('.auth-chip:not(.hidden-search)').length;
            }
        });
    }
    updateCounts();

    // Buscador del pool
    const searchInput = document.getElementById('auth-search');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const q = this.value.toLowerCase().trim();
            document.querySelectorAll('.auth-pool-list .auth-chip').forEach(chip => {
                const nombre = chip.dataset.nombre || '';
                chip.classList.toggle('hidden-search', q && !nombre.includes(q));
            });
            updateCounts();
        });
    }

    // === CLICK EN CHIP: popover con stats ===
    function cerrarPopover() {
        const existing = document.getElementById('chip-popover');
        if (existing) existing.remove();
    }

    function bindChipClick(chip) {
        chip.addEventListener('click', function(e) {
            // No abrir popover si estamos arrastrando
            if (this.classList.contains('dragging')) return;
            e.stopPropagation();

            const panel = this.closest('.auth-dropzone');
            if (!panel) return;
            const campo = panel.dataset.campo;
            if (campo === 'pool') return; // no popover en el pool

            const pubId = this.dataset.id;
            const nombre = this.textContent.trim();

            cerrarPopover();

            const popover = document.createElement('div');
            popover.id = 'chip-popover';
            popover.className = 'chip-popover';

            if (campo === 'excluido_reuniones' || campo === 'voluntario_emergencia') {
                // Sin stats, solo opcion de quitar
                const accion = campo === 'excluido_reuniones' ? 'Des-excluir' : 'Quitar de emergencia';
                popover.innerHTML = `
                    <div class="chip-popover-header">${nombre}</div>
                    <button class="chip-popover-btn chip-popover-btn-remove" onclick="quitarDePanel('${pubId}','${campo}')">
                        ${accion}
                    </button>`;
            } else {
                const stats = getStatsParaPub(pubId, campo);
                const pctClass = stats.pct > 0 ? 'chip-stat-high' : stats.pct < 0 ? 'chip-stat-low' : '';
                const pctSign = stats.pct > 0 ? '+' : '';
                const ultimaTexto = stats.ultima || 'Nunca';
                const nombreTipo = nombres[campo] || campo;

                popover.innerHTML = `
                    <div class="chip-popover-header">${nombre}</div>
                    <div class="chip-popover-stats">
                        <div class="chip-stat-row">
                            <span class="chip-stat-label">${nombreTipo} (12 meses)</span>
                            <span class="chip-stat-value">${stats.total} asignaciones</span>
                        </div>
                        <div class="chip-stat-row">
                            <span class="chip-stat-label">Promedio del grupo</span>
                            <span class="chip-stat-value">${stats.promedio}</span>
                        </div>
                        <div class="chip-stat-row">
                            <span class="chip-stat-label">vs promedio</span>
                            <span class="chip-stat-value ${pctClass}">${pctSign}${stats.pct}%</span>
                        </div>
                        <div class="chip-stat-row">
                            <span class="chip-stat-label">Ultima vez</span>
                            <span class="chip-stat-value">${ultimaTexto}</span>
                        </div>
                    </div>
                    <button class="chip-popover-btn chip-popover-btn-remove" onclick="quitarDePanel('${pubId}','${campo}')">
                        Quitar de ${nombreTipo}
                    </button>`;
            }

            // Posicionar junto al chip
            const rect = this.getBoundingClientRect();
            popover.style.position = 'fixed';
            popover.style.top = (rect.bottom + 6) + 'px';
            popover.style.left = Math.min(rect.left, window.innerWidth - 260) + 'px';
            popover.style.zIndex = '9999';

            document.body.appendChild(popover);
        });
    }

    document.querySelectorAll('.auth-dropzone:not([data-campo="pool"]) .auth-chip').forEach(bindChipClick);

    // Cerrar popover al clicar fuera
    document.addEventListener('click', function(e) {
        if (!e.target.closest('#chip-popover') && !e.target.closest('.auth-chip')) {
            cerrarPopover();
        }
    });

    window.quitarDePanel = function(pubId, campo) {
        cerrarPopover();
        const dropzone = document.querySelector('[data-campo="' + campo + '"]');
        if (dropzone) {
            const chip = dropzone.querySelector('[data-id="' + pubId + '"]');
            if (chip) chip.remove();
        }
        updateCounts();
        guardarAutorizacion(pubId, 'pool', campo);
    };

    // === MODAL AGREGAR ===
    let modalCampoActual = null;

    window.abrirModalAgregar = function(campo) {
        modalCampoActual = campo;
        const modal = document.getElementById('modal-agregar');
        const titulo = document.getElementById('modal-agregar-titulo');
        const buscar = document.getElementById('modal-agregar-buscar');

        titulo.textContent = 'Agregar a: ' + (nombres[campo] || campo);
        buscar.value = '';
        renderModalLista('');
        modal.style.display = 'flex';
        buscar.focus();
    };

    window.cerrarModalAgregar = function() {
        document.getElementById('modal-agregar').style.display = 'none';
        modalCampoActual = null;
    };

    document.getElementById('modal-agregar-buscar').addEventListener('input', function() {
        renderModalLista(this.value.toLowerCase().trim());
    });

    function renderModalLista(filtro) {
        const lista = document.getElementById('modal-agregar-lista');
        const dropzone = document.querySelector('[data-campo="' + modalCampoActual + '"]');
        const idsExistentes = new Set();
        if (dropzone) {
            dropzone.querySelectorAll('.auth-chip[data-id]').forEach(c => idsExistentes.add(String(c.dataset.id)));
        }

        let html = '';
        poolData.forEach(p => {
            if (filtro && !p.nombre.toLowerCase().includes(filtro)) return;
            const yaExiste = idsExistentes.has(String(p.id));
            const tags = (p.anciano ? ' <span class="auth-chip-tag">A</span>' : '') + (p.sm ? ' <span class="auth-chip-tag">SM</span>' : '');

            // Stats del publicador en este tipo
            let statsHtml = '';
            if (modalCampoActual && campoATipos[modalCampoActual]) {
                const stats = getStatsParaPub(p.id, modalCampoActual);
                if (stats.total > 0) {
                    const pctSign = stats.pct > 0 ? '+' : '';
                    statsHtml = `<span class="subtitle">${stats.total} asig. (${pctSign}${stats.pct}% vs prom)</span>`;
                } else {
                    statsHtml = '<span class="subtitle">Sin asignaciones</span>';
                }
            }

            html += '<a href="#" class="list-item' + (yaExiste ? ' disabled' : '') + '" ' +
                (yaExiste ? 'style="opacity:0.4; pointer-events:none;"' : 'onclick="agregarDesdeModal(' + p.id + ',\'' + p.nombre.replace(/'/g, "\\'") + '\',\'' + p.genero + '\');return false;"') +
                '><span class="content"><span class="title">' + p.nombre + ' <span class="auth-chip-genero">' + p.genero + '</span>' + tags + '</span>' + statsHtml + '</span>' +
                (yaExiste ? '<span class="meta text-muted">Ya</span>' : '<span class="meta" style="color:#14b8a6;font-weight:600;">+</span>') +
                '</a>';
        });
        if (!html) html = '<p class="text-muted" style="padding: 1rem; text-align: center;">Sin resultados</p>';
        lista.innerHTML = html;
    }

    window.agregarDesdeModal = function(pubId, nombre, genero) {
        if (!modalCampoActual) return;

        const dropzone = document.querySelector('[data-campo="' + modalCampoActual + '"]');
        if (!dropzone) return;

        if (dropzone.querySelector('[data-id="' + pubId + '"]')) return;

        const chip = document.createElement('div');
        chip.className = 'auth-chip';
        if (modalCampoActual === 'excluido_reuniones') chip.classList.add('auth-chip-excluded');
        if (modalCampoActual === 'voluntario_emergencia') chip.classList.add('auth-chip-emergency');
        chip.setAttribute('draggable', 'true');
        chip.dataset.id = pubId;
        chip.dataset.nombre = nombre.toLowerCase();
        chip.textContent = nombre;
        bindDrag(chip);
        bindChipClick(chip);
        dropzone.appendChild(chip);

        updateCounts();
        guardarAutorizacion(pubId, modalCampoActual, 'pool');
        renderModalLista(document.getElementById('modal-agregar-buscar').value.toLowerCase().trim());
    };

    function guardarAutorizacion(pubId, campo, origen) {
        fetch('{{ route("reuniones.autorizaciones.guardar") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: JSON.stringify({
                publicador_id: pubId,
                campo: campo,
                origen: origen,
            })
        })
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                alert('Error al guardar: ' + (data.error || 'Desconocido'));
                location.reload();
            }
        })
        .catch(() => {
            alert('Error de conexion');
            location.reload();
        });
    }

    // === DRAG & DROP ===
    let draggedEl = null;
    let sourceZone = null;

    function bindDrag(chip) {
        chip.setAttribute('draggable', 'true');
        chip.addEventListener('dragstart', function(e) {
            draggedEl = this;
            sourceZone = this.closest('.auth-dropzone');
            this.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', this.dataset.id);
        });

        chip.addEventListener('dragend', function() {
            this.classList.remove('dragging');
            document.querySelectorAll('.drag-over').forEach(z => z.classList.remove('drag-over'));
            draggedEl = null;
            sourceZone = null;
        });
    }

    document.querySelectorAll('.auth-chip[draggable]').forEach(bindDrag);

    document.querySelectorAll('.auth-dropzone').forEach(zone => {
        zone.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            this.classList.add('drag-over');
        });

        zone.addEventListener('dragleave', function(e) {
            if (!this.contains(e.relatedTarget)) {
                this.classList.remove('drag-over');
            }
        });

        zone.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('drag-over');

            if (!draggedEl || this === sourceZone) return;

            const pubId = draggedEl.dataset.id;
            const targetCampo = this.dataset.campo;
            const sourceCampo = sourceZone ? sourceZone.dataset.campo : null;

            const yaExiste = this.querySelector('[data-id="' + pubId + '"]');
            if (yaExiste && targetCampo !== 'pool') return;

            if (targetCampo === 'pool') {
                draggedEl.remove();
            } else if (sourceCampo === 'pool') {
                const clon = draggedEl.cloneNode(true);
                clon.classList.remove('dragging');
                if (targetCampo === 'excluido_reuniones') clon.classList.add('auth-chip-excluded');
                if (targetCampo === 'voluntario_emergencia') clon.classList.add('auth-chip-emergency');
                bindDrag(clon);
                bindChipClick(clon);
                this.appendChild(clon);
            } else {
                const clon = draggedEl.cloneNode(true);
                clon.classList.remove('dragging');
                bindDrag(clon);
                bindChipClick(clon);
                this.appendChild(clon);
            }

            if (targetCampo === 'excluido_reuniones') {
                document.querySelectorAll('.auth-dropzone:not([data-campo="excluido_reuniones"]):not([data-campo="pool"])').forEach(z => {
                    const chip = z.querySelector('[data-id="' + pubId + '"]');
                    if (chip) chip.remove();
                });
            }

            updateCounts();
            guardarAutorizacion(pubId, targetCampo, sourceCampo);
        });
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') { cerrarModalAgregar(); cerrarPopover(); }
    });
});
</script>

@push('styles')
<style>
.auth-guide {
    display: flex;
    gap: 0.75rem;
    padding: 0.875rem 1rem;
    background: rgba(20,184,166,0.08);
    border: 1px solid rgba(20,184,166,0.2);
    border-radius: var(--radius);
    font-size: 0.85rem;
    color: var(--text-secondary);
    line-height: 1.5;
    margin-bottom: 1.25rem;
}
.auth-guide strong { color: var(--text); }

/* Popover de stats al hacer clic en chip */
.chip-popover {
    background: var(--bg-white);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    box-shadow: 0 8px 24px rgba(0,0,0,0.3);
    width: 250px;
    overflow: hidden;
    animation: popoverIn 0.15s ease-out;
}
@keyframes popoverIn {
    from { opacity: 0; transform: translateY(-4px); }
    to { opacity: 1; transform: translateY(0); }
}
.chip-popover-header {
    padding: 0.625rem 0.75rem;
    font-weight: 600;
    font-size: 0.85rem;
    color: var(--text);
    border-bottom: 1px solid var(--border);
}
.chip-popover-stats {
    padding: 0.5rem 0.75rem;
}
.chip-stat-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.2rem 0;
    font-size: 0.8rem;
}
.chip-stat-label {
    color: var(--text-muted);
}
.chip-stat-value {
    color: var(--text);
    font-weight: 500;
}
.chip-stat-high { color: #f59e0b; }
.chip-stat-low { color: #14b8a6; }
.chip-popover-btn {
    display: block;
    width: 100%;
    padding: 0.5rem 0.75rem;
    border: none;
    cursor: pointer;
    font-size: 0.8rem;
    text-align: left;
    transition: background 0.15s;
}
.chip-popover-btn-remove {
    background: rgba(239,68,68,0.08);
    color: #ef4444;
    border-top: 1px solid var(--border);
}
.chip-popover-btn-remove:hover {
    background: rgba(239,68,68,0.15);
}

/* Chips clickeables */
.auth-dropzone:not([data-campo="pool"]) .auth-chip {
    cursor: pointer;
}
.auth-dropzone:not([data-campo="pool"]) .auth-chip:hover {
    border-color: var(--primary);
    background: var(--bg-hover);
}
</style>
@endpush

@endsection
