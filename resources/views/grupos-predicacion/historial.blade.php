@extends('layouts.app')

@section('title', 'Historial de Grupos')

@section('content')
<div class="historial-page">
    <div class="page-header">
        <div class="header-left">
            <a href="{{ route('grupos-predicacion.index') }}" class="btn-back">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
            </a>
            <h1>Historial de Grupos</h1>
        </div>
        <div class="header-actions">
            <button onclick="abrirModalCrear()" class="btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"/>
                    <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Nuevo Año
            </button>
        </div>
    </div>

    <div class="anos-grid">
        @forelse($anos as $ano)
        <div class="ano-card" onclick="window.location='/grupos-predicacion/historial/{{ urlencode($ano->ano_servicio) }}/editar'">
            <div class="ano-header">
                <span class="ano-label">{{ $ano->ano_servicio }}</span>
                @if($ano->ano_servicio == $anoActual)
                <span class="tag-actual">Actual</span>
                @endif
            </div>
            <div class="ano-stats">
                <div class="stat">
                    <span class="stat-num">{{ $ano->total_publicadores }}</span>
                    <span class="stat-label">publicadores</span>
                </div>
            </div>
            <div class="ano-actions">
                <button onclick="event.stopPropagation(); editarAno('{{ $ano->ano_servicio }}')" class="btn-edit" title="Editar">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                    </svg>
                </button>
                <button onclick="event.stopPropagation(); eliminarAno('{{ $ano->ano_servicio }}')" class="btn-delete" title="Eliminar">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="3 6 5 6 21 6"/>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                    </svg>
                </button>
            </div>
        </div>
        @empty
        <div class="empty-state">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/>
                <line x1="8" y1="2" x2="8" y2="6"/>
                <line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
            <p>No hay años guardados</p>
            <p class="hint">Crea un nuevo año para empezar a registrar el historial</p>
        </div>
        @endforelse

        <!-- Card para crear nuevo -->
        <div class="ano-card ano-card-new" onclick="abrirModalCrear()">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            <span>Nuevo Año</span>
        </div>
    </div>
</div>

<!-- Modal Crear Ano -->
<div id="modal-crear" class="modal" style="display:none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Crear Año de Servicio</h3>
            <button onclick="cerrarModal()" class="btn-close">&times;</button>
        </div>
        <div class="modal-body">
            <p class="modal-hint">El año de servicio va de Septiembre a Agosto</p>
            <div class="form-group">
                <label>Año de inicio</label>
                <select id="ano-inicio">
                    @for($i = now()->year; $i >= 2015; $i--)
                    <option value="{{ $i }}">{{ $i }}</option>
                    @endfor
                </select>
            </div>
            <div class="preview-ano">
                <span id="preview-texto">2025/2026</span>
            </div>
        </div>
        <div class="modal-footer">
            <button onclick="cerrarModal()" class="btn-cancel">Cancelar</button>
            <button onclick="crearAno()" class="btn-primary">Crear</button>
        </div>
    </div>
</div>

<style>
.historial-page {
    max-width: 1000px;
    margin: 0 auto;
    padding: 1rem;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
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

.btn-back:hover {
    background: var(--bg-hover);
}

.page-header h1 {
    font-size: 1.3rem;
    font-weight: 700;
    color: var(--text-primary, #1f2937);
    margin: 0;
}

.btn-primary {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.5rem 1rem;
    background: #4a6da7;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
}

.btn-primary:hover {
    background: #3d5a8a;
}

/* Grid de anos */
.anos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1rem;
}

.ano-card {
    background: var(--bg-white);
    border-radius: 12px;
    padding: 1rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    cursor: pointer;
    transition: transform 0.15s, box-shadow 0.15s;
    border: 2px solid transparent;
}

.ano-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.12);
    border-color: #4a6da7;
}

.ano-card-new {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 140px;
    border: 2px dashed var(--border-medium);
    background: var(--bg);
    color: #6b7280;
}

.ano-card-new:hover {
    border-color: #4a6da7;
    color: #4a6da7;
    background: rgba(59,130,246,0.10);
}

.ano-card-new svg {
    margin-bottom: 0.5rem;
}

.ano-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.75rem;
}

.ano-label {
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--text-primary, #1f2937);
}

.tag-actual {
    background: #4a6da7;
    color: white;
    padding: 0.15rem 0.5rem;
    border-radius: 4px;
    font-size: 0.7rem;
    font-weight: 600;
}

.ano-stats {
    margin-bottom: 0.75rem;
}

.stat {
    display: flex;
    align-items: baseline;
    gap: 0.3rem;
}

.stat-num {
    font-size: 1.5rem;
    font-weight: 700;
    color: #4a6da7;
}

.stat-label {
    font-size: 0.8rem;
    color: #6b7280;
}

.ano-actions {
    display: flex;
    gap: 0.5rem;
    padding-top: 0.5rem;
    border-top: 1px solid var(--border);
}

.btn-edit, .btn-delete {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    background: #f3f4f6;
    color: #6b7280;
}

.btn-edit:hover {
    background: #e8eef6;
    color: #3d5a8a;
}

.btn-delete:hover {
    background: #e9ecef;
    color: var(--text);
}

/* Empty state */
.empty-state {
    grid-column: 1 / -1;
    text-align: center;
    padding: 3rem;
    color: #6b7280;
}

.empty-state svg {
    margin-bottom: 1rem;
    opacity: 0.5;
}

.empty-state p {
    margin: 0.25rem 0;
}

.empty-state .hint {
    font-size: 0.85rem;
    opacity: 0.7;
}

/* Modal */
.modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal-content {
    background: var(--bg-white);
    border-radius: 12px;
    width: 90%;
    max-width: 400px;
    overflow: hidden;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    border-bottom: 1px solid var(--border);
}

.modal-header h3 {
    margin: 0;
    font-size: 1.1rem;
}

.btn-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: #6b7280;
    line-height: 1;
}

.modal-body {
    padding: 1rem;
}

.modal-hint {
    font-size: 0.85rem;
    color: #6b7280;
    margin-bottom: 1rem;
}

.form-group {
    margin-bottom: 1rem;
}

.form-group label {
    display: block;
    font-size: 0.85rem;
    font-weight: 600;
    margin-bottom: 0.4rem;
}

.form-group select {
    width: 100%;
    padding: 0.6rem;
    border: 1px solid var(--border-medium);
    border-radius: 6px;
    font-size: 1rem;
}

.preview-ano {
    text-align: center;
    padding: 1rem;
    background: #f3f4f6;
    border-radius: 8px;
}

#preview-texto {
    font-size: 1.5rem;
    font-weight: 700;
    color: #4a6da7;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
    padding: 1rem;
    border-top: 1px solid var(--border);
}

.btn-cancel {
    padding: 0.5rem 1rem;
    background: #f3f4f6;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
}

.btn-cancel:hover {
    background: var(--bg-hover);
}

/* Dark theme */
[data-theme="dark"] .historial-page { color: #f5f5f5; }
[data-theme="dark"] .page-header h1 { color: #f5f5f5; }
[data-theme="dark"] .btn-back { background: #262626; color: #e5e5e5; }
[data-theme="dark"] .btn-primary { background: #4a6da7; }
[data-theme="dark"] .ano-card { background: #1a1a1a; }
[data-theme="dark"] .ano-label { color: #f5f5f5; }
[data-theme="dark"] .tag-actual { background: #4a6da7; }
[data-theme="dark"] .stat-num { color: #4a6da7; }
[data-theme="dark"] .ano-card-new { background: #262626; border-color: #404040; color: #a3a3a3; }
[data-theme="dark"] .ano-card-new:hover { border-color: #4a6da7; color: #4a6da7; background: rgba(249,115,22,0.1); }
[data-theme="dark"] .ano-actions { border-color: #2d2d2d; }
[data-theme="dark"] .btn-edit, [data-theme="dark"] .btn-delete { background: #262626; color: #a3a3a3; }
[data-theme="dark"] .modal-content { background: #1a1a1a; }
[data-theme="dark"] .modal-header, [data-theme="dark"] .modal-footer { border-color: #2d2d2d; }
[data-theme="dark"] .form-group select { background: #262626; border-color: #404040; color: #f5f5f5; }
[data-theme="dark"] .preview-ano { background: #262626; }
[data-theme="dark"] #preview-texto { color: #4a6da7; }
[data-theme="dark"] .btn-cancel { background: #262626; color: #e5e5e5; }
</style>

<script>
const csrfToken = '{{ csrf_token() }}';

document.getElementById('ano-inicio').addEventListener('change', actualizarPreview);
actualizarPreview();

function actualizarPreview() {
    const inicio = parseInt(document.getElementById('ano-inicio').value);
    document.getElementById('preview-texto').textContent = inicio + '/' + (inicio + 1);
}

function abrirModalCrear() {
    document.getElementById('modal-crear').style.display = 'flex';
}

function cerrarModal() {
    document.getElementById('modal-crear').style.display = 'none';
}

function crearAno() {
    const inicio = document.getElementById('ano-inicio').value;
    const anoServicio = inicio + '/' + (parseInt(inicio) + 1);

    fetch('/grupos-predicacion/historial/crear', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ ano_servicio: anoServicio })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            window.location.href = '/grupos-predicacion/historial/' + encodeURIComponent(anoServicio) + '/editar';
        } else {
            alert(data.error || 'Error al crear');
        }
    });
}

function editarAno(ano) {
    window.location.href = '/grupos-predicacion/historial/' + encodeURIComponent(ano) + '/editar';
}

function eliminarAno(ano) {
    if (!confirm('¿Eliminar el historial del año ' + ano + '? Esta acción no se puede deshacer.')) return;

    fetch('/grupos-predicacion/historial/' + encodeURIComponent(ano), {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrfToken }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Error al eliminar');
        }
    });
}

// Cerrar modal con Escape
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') cerrarModal();
});
</script>
@endsection
