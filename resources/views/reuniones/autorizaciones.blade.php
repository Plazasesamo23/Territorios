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
                    <div class="auth-panel-info">Arrastra hacia la izquierda o usa los botones +</div>

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
    // Pool data for modal
    const poolData = @json($poolJson);

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

    // === MODAL AGREGAR ===
    let modalCampoActual = null;

    window.abrirModalAgregar = function(campo) {
        modalCampoActual = campo;
        const modal = document.getElementById('modal-agregar');
        const titulo = document.getElementById('modal-agregar-titulo');
        const buscar = document.getElementById('modal-agregar-buscar');

        const nombres = {
            presidente: 'Presidente', oracion: 'Oraciones', conductor_estudio: 'Conductor estudio',
            lector_estudio: 'Lector estudio', tesoros: 'Tesoros', perlas: 'Perlas',
            lectura: 'Lectura biblica', maestros: 'Partes de maestros', discurso_maestros: 'Discurso maestros',
            discurso_vida: 'Discurso Vida Cristiana', excluido_reuniones: 'Excluir de reuniones'
        };
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
        // Check which IDs are already in the target panel
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
            html += '<a href="#" class="list-item' + (yaExiste ? ' disabled' : '') + '" ' +
                (yaExiste ? 'style="opacity:0.4; pointer-events:none;"' : 'onclick="agregarDesdeModal(' + p.id + ',\'' + p.nombre.replace(/'/g, "\\'") + '\',\'' + p.genero + '\');return false;"') +
                '><span class="content"><span class="title">' + p.nombre + ' <span class="auth-chip-genero">' + p.genero + '</span>' + tags + '</span></span>' +
                (yaExiste ? '<span class="meta text-muted">Ya agregado</span>' : '<span class="meta">+</span>') +
                '</a>';
        });
        if (!html) html = '<p class="text-muted" style="padding: 1rem; text-align: center;">Sin resultados</p>';
        lista.innerHTML = html;
    }

    window.agregarDesdeModal = function(pubId, nombre, genero) {
        if (!modalCampoActual) return;

        const dropzone = document.querySelector('[data-campo="' + modalCampoActual + '"]');
        if (!dropzone) return;

        // Check if already exists
        if (dropzone.querySelector('[data-id="' + pubId + '"]')) return;

        // Create chip
        const chip = document.createElement('div');
        chip.className = 'auth-chip' + (modalCampoActual === 'excluido_reuniones' ? ' auth-chip-excluded' : '');
        chip.setAttribute('draggable', 'true');
        chip.dataset.id = pubId;
        chip.dataset.nombre = nombre.toLowerCase();
        chip.textContent = nombre;
        bindDrag(chip);
        dropzone.appendChild(chip);

        updateCounts();

        // Save via AJAX
        guardarAutorizacion(pubId, modalCampoActual, 'pool');

        // Re-render modal list
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
                if (targetCampo === 'excluido_reuniones') {
                    clon.classList.add('auth-chip-excluded');
                }
                bindDrag(clon);
                this.appendChild(clon);
            } else {
                const clon = draggedEl.cloneNode(true);
                clon.classList.remove('dragging');
                bindDrag(clon);
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

    // Escape para cerrar modal
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') cerrarModalAgregar();
    });
});
</script>

@endsection
