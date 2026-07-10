@extends('layouts.app')

@section('title', 'Editar Historial ' . $ano)

@section('content')
<div class="historial-editar-page">
    <div class="page-header">
        <div class="header-left">
            <a href="{{ route('grupos-predicacion.historial') }}" class="btn-back">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
            </a>
            <h1>Historial {{ $ano }}</h1>
            <span class="badge-historial">Modo Historial</span>
        </div>
        <div class="header-actions">
            <span class="guardado-status" id="status-guardado"></span>
        </div>
    </div>

    <div class="main-layout">
        <!-- Grupos del historial -->
        <div class="grupos-section">
            <div class="grupos-grid">
                @for($i = 1; $i <= 6; $i++)
                @php
                    $pubsGrupo = $historial->get($i, collect());
                @endphp
                <div class="grupo-box" data-grupo="{{ $i }}">
                    <div class="grupo-header">
                        <span class="grupo-label">GRUPO</span>
                        <span class="grupo-num">{{ $i }}</span>
                        <span class="grupo-count">{{ $pubsGrupo->count() }}</span>
                    </div>
                    <div class="grupo-lista" data-grupo="{{ $i }}">
                        @foreach($pubsGrupo as $registro)
                        @php
                            $rolTexto = '';
                            if ($registro->rol == 'superintendente') $rolTexto = ' (SUP)';
                            elseif ($registro->rol == 'auxiliar') $rolTexto = ' (AUX)';
                            elseif ($registro->rol == 'precursor') $rolTexto = ' (PR)';
                        @endphp
                        <div class="pub-item {{ $registro->rol }}"
                             draggable="true"
                             data-id="{{ $registro->publicador_id }}"
                             data-rol="{{ $registro->rol }}">
                            <span class="pub-nombre">{{ $registro->publicador->nombre_completo ?? 'Publicador #'.$registro->publicador_id }}{{ $rolTexto }}</span>
                            <div class="pub-actions">
                                <select class="select-rol" onchange="cambiarRol({{ $registro->publicador_id }}, this.value)">
                                    <option value="normal" {{ $registro->rol == 'normal' ? 'selected' : '' }}>Normal</option>
                                    <option value="precursor" {{ $registro->rol == 'precursor' ? 'selected' : '' }}>Precursor</option>
                                    <option value="auxiliar" {{ $registro->rol == 'auxiliar' ? 'selected' : '' }}>Auxiliar</option>
                                    <option value="superintendente" {{ $registro->rol == 'superintendente' ? 'selected' : '' }}>Superintendente</option>
                                </select>
                                <button class="btn-remove" onclick="quitarPublicador({{ $registro->publicador_id }})" title="Quitar">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="18" y1="6" x2="6" y2="18"/>
                                        <line x1="6" y1="6" x2="18" y2="18"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div class="grupo-footer">
                        <span class="total-label">Total:</span>
                        <span class="total-num">{{ $pubsGrupo->count() }}</span>
                    </div>
                </div>
                @endfor
            </div>
        </div>

        <!-- Panel de publicadores disponibles -->
        <div class="panel-disponibles">
            <div class="panel-header">
                <span class="panel-title">Publicadores</span>
                <span class="panel-count">{{ $publicadoresDisponibles->count() }}</span>
            </div>
            <div class="panel-search">
                <input type="text" id="buscar-pub" placeholder="Buscar..." onkeyup="filtrarPublicadores()">
            </div>
            <div class="panel-lista" data-grupo="0">
                @foreach($publicadoresDisponibles as $pub)
                <div class="pub-item disponible"
                     draggable="true"
                     data-id="{{ $pub->id }}"
                     data-nombre="{{ $pub->nombre_completo }}">
                    <span class="pub-nombre">{{ $pub->nombre_completo }}</span>
                    @if($pub->es_precursor)<span class="tag-pr">PR</span>@endif
                </div>
                @endforeach
            </div>
            <div class="panel-hint">
                Arrastra publicadores a los grupos
            </div>
        </div>
    </div>
</div>

<style>
.historial-editar-page {
    max-width: 1200px;
    margin: 0 auto;
    padding: 1rem;
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

.btn-back {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    background: var(--bg-secondary, #f3f4f6);
    border-radius: 8px;
    color: var(--text-primary, #374151);
    text-decoration: none;
}

.page-header h1 {
    font-size: 1.3rem;
    font-weight: 700;
    margin: 0;
}

.badge-historial {
    background: #4a6da7;
    color: white;
    padding: 0.25rem 0.6rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
}

.guardado-status {
    font-size: 0.85rem;
    color: #4a6da7;
}

/* Layout */
.main-layout {
    display: grid;
    grid-template-columns: 1fr 250px;
    gap: 1rem;
    align-items: start;
}

/* Grupos */
.grupos-section {
    background: var(--bg-white);
    border-radius: 10px;
    padding: 1rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.grupos-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.75rem;
}

.grupo-box {
    border: 1px solid var(--border);
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
}

.grupo-lista {
    min-height: 150px;
    max-height: 300px;
    overflow-y: auto;
    padding: 0.4rem;
    background: var(--bg);
}

.grupo-lista.drag-over {
    background: #f3e8ff;
}

.pub-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.3rem 0.4rem;
    margin-bottom: 0.25rem;
    background: var(--bg-white);
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
    color: var(--text);
    font-weight: 600;
    background: var(--bg-hover);
}

.pub-item.auxiliar {
    color: #3d5a8a;
    font-weight: 600;
    background: rgba(59,130,246,0.10);
}

.pub-item.precursor {
    color: #3d5a8a;
    background: #f0fdf4;
}

.pub-nombre {
    flex: 1;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.pub-actions {
    display: flex;
    gap: 0.25rem;
    align-items: center;
}

.select-rol {
    padding: 0.15rem 0.25rem;
    font-size: 0.78rem;
    border: 1px solid var(--border-medium);
    border-radius: 3px;
    background: var(--bg-white);
    cursor: pointer;
}

.btn-remove {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 20px;
    height: 20px;
    border: none;
    border-radius: 3px;
    background: #e9ecef;
    color: var(--text);
    cursor: pointer;
}

.btn-remove:hover {
    background: #fecaca;
}

.grupo-footer {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.35rem 0.5rem;
    border-top: 1px solid var(--border);
    font-size: 0.7rem;
    font-weight: 600;
    background: var(--bg-white);
}

.total-label {
    color: #6b7280;
}

.total-num {
    background: var(--bg-hover);
    padding: 0.1rem 0.4rem;
    border-radius: 3px;
}

/* Panel disponibles */
.panel-disponibles {
    background: var(--bg-white);
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
    border-bottom: 1px solid var(--border);
}

.panel-search input {
    width: 100%;
    padding: 0.4rem 0.6rem;
    border: 1px solid var(--border);
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

.pub-item.disponible {
    background: #fef3c7;
    border: 1px solid #fcd34d;
}

.tag-pr {
    background: #343a40;
    color: white;
    padding: 0.1rem 0.25rem;
    border-radius: 3px;
    font-size: 0.72rem;
    font-weight: 700;
}

.panel-hint {
    padding: 0.5rem;
    text-align: center;
    font-size: 0.75rem;
    color: #9ca3af;
    border-top: 1px solid var(--border);
}

/* Dark theme */
[data-theme="dark"] .page-header h1 { color: #f5f5f5; }
[data-theme="dark"] .btn-back { background: #262626; color: #e5e5e5; }
[data-theme="dark"] .badge-historial { background: #3d5a8a; }
[data-theme="dark"] .grupos-section { background: #1a1a1a; }
[data-theme="dark"] .grupo-box { border-color: #2d2d2d; }
[data-theme="dark"] .grupo-header { background: linear-gradient(135deg, #3d5a8a 0%, #2d4266 100%); }
[data-theme="dark"] .grupo-lista { background: #262626; }
[data-theme="dark"] .pub-item { background: #1a1a1a; color: #e5e5e5; }
[data-theme="dark"] .pub-item.superintendente { background: rgba(220,38,38,0.15); color: #f87171; }
[data-theme="dark"] .pub-item.auxiliar { background: rgba(37,99,235,0.15); color: #5c7fb8; }
[data-theme="dark"] .pub-item.precursor { background: rgba(22,163,74,0.1); color: #4ade80; }
[data-theme="dark"] .select-rol { background: #262626; border-color: #404040; color: #e5e5e5; }
[data-theme="dark"] .grupo-footer { background: #1a1a1a; border-color: #2d2d2d; }
[data-theme="dark"] .panel-disponibles { background: #1a1a1a; }
[data-theme="dark"] .panel-header { background: #404040; }
[data-theme="dark"] .panel-search input { background: #262626; border-color: #404040; color: #e5e5e5; }
[data-theme="dark"] .pub-item.disponible { background: rgba(245,158,11,0.15); border-color: #4a6da7; color: #fcd34d; }
[data-theme="dark"] .panel-hint { border-color: #2d2d2d; }

/* Responsive */
@media (max-width: 900px) {
    .main-layout { grid-template-columns: 1fr; }
    .grupos-grid { grid-template-columns: repeat(2, 1fr); }
    .panel-disponibles { position: static; max-height: 300px; }
}

@media (max-width: 600px) {
    .grupos-grid { grid-template-columns: 1fr; }
    .page-header { flex-direction: column; align-items: flex-start; }
    .page-header h1 { font-size: 1.1rem; }
}
</style>

<script>
const csrfToken = '{{ csrf_token() }}';
const anoServicio = '{{ $ano }}';
let draggedElement = null;
let saveTimeout = null;

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

    const grupoNum = parseInt(this.dataset.grupo) || 0;
    const pubId = draggedElement.dataset.id;

    // Mover al nuevo grupo
    this.appendChild(draggedElement);

    // Actualizar estilos
    if (grupoNum === 0) {
        draggedElement.classList.add('disponible');
        const actions = draggedElement.querySelector('.pub-actions');
        if (actions) actions.remove();
    } else {
        draggedElement.classList.remove('disponible');
        if (!draggedElement.querySelector('.pub-actions')) {
            const actions = document.createElement('div');
            actions.className = 'pub-actions';
            actions.innerHTML = `
                <select class="select-rol" onchange="cambiarRol(${pubId}, this.value)">
                    <option value="normal">Normal</option>
                    <option value="precursor">Precursor</option>
                    <option value="auxiliar">Auxiliar</option>
                    <option value="superintendente">Superintendente</option>
                </select>
                <button class="btn-remove" onclick="quitarPublicador(${pubId})">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            `;
            draggedElement.appendChild(actions);
        }
    }

    updateCounters();
    guardarCambio(pubId, grupoNum, 'normal');
}

function updateCounters() {
    document.querySelectorAll('.grupo-box').forEach(box => {
        const count = box.querySelector('.grupo-lista').querySelectorAll('.pub-item').length;
        box.querySelector('.grupo-count').textContent = count;
        box.querySelector('.total-num').textContent = count;
    });
    const disponibles = document.querySelectorAll('.panel-lista .pub-item').length;
    document.querySelector('.panel-count').textContent = disponibles;
}

function cambiarRol(pubId, rol) {
    const item = document.querySelector(`.pub-item[data-id="${pubId}"]`);
    const grupoLista = item.closest('.grupo-lista');
    const grupoNum = grupoLista ? parseInt(grupoLista.dataset.grupo) : 0;

    // Actualizar clase visual
    item.classList.remove('superintendente', 'auxiliar', 'precursor', 'normal');
    item.classList.add(rol);
    item.dataset.rol = rol;

    // Actualizar texto
    const nombre = item.querySelector('.pub-nombre');
    let nombreBase = nombre.textContent.replace(/ \((SUP|AUX|PR)\)$/, '');
    let sufijo = '';
    if (rol === 'superintendente') sufijo = ' (SUP)';
    else if (rol === 'auxiliar') sufijo = ' (AUX)';
    else if (rol === 'precursor') sufijo = ' (PR)';
    nombre.textContent = nombreBase + sufijo;

    guardarCambio(pubId, grupoNum, rol);
}

function quitarPublicador(pubId) {
    const item = document.querySelector(`.pub-item[data-id="${pubId}"]`);
    const panelLista = document.querySelector('.panel-lista');

    // Mover a disponibles
    item.classList.add('disponible');
    item.classList.remove('superintendente', 'auxiliar', 'precursor');
    const actions = item.querySelector('.pub-actions');
    if (actions) actions.remove();

    // Limpiar sufijo de rol
    const nombre = item.querySelector('.pub-nombre');
    nombre.textContent = nombre.textContent.replace(/ \((SUP|AUX|PR)\)$/, '');

    panelLista.appendChild(item);
    updateCounters();
    guardarCambio(pubId, 0, null);
}

function guardarCambio(pubId, grupoNum, rol) {
    // Mostrar estado
    document.getElementById('status-guardado').textContent = 'Guardando...';

    // Debounce
    clearTimeout(saveTimeout);
    saveTimeout = setTimeout(() => {
        fetch('/grupos-predicacion/historial/actualizar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({
                ano_servicio: anoServicio,
                publicador_id: pubId,
                grupo_numero: grupoNum,
                rol: rol
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.getElementById('status-guardado').textContent = 'Guardado';
                setTimeout(() => {
                    document.getElementById('status-guardado').textContent = '';
                }, 2000);
            }
        });
    }, 300);
}

function filtrarPublicadores() {
    const term = document.getElementById('buscar-pub').value.toLowerCase();
    document.querySelectorAll('.panel-lista .pub-item').forEach(el => {
        const nombre = el.dataset.nombre || el.querySelector('.pub-nombre').textContent;
        el.style.display = nombre.toLowerCase().includes(term) ? '' : 'none';
    });
}
</script>
@endsection
