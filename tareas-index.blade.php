@extends('layouts.app')

@section('title', 'Tareas')

@push('styles')
<style>
.tareas-page { max-width: 1100px; margin: 0 auto; }
.tareas-header {
    display: flex; align-items: center; justify-content: space-between;
    gap: 1rem; margin-bottom: 1rem; flex-wrap: wrap;
}
.tareas-header h1 { font-size: 1.5rem; margin: 0; color: #f1f3f5; }
.tareas-count { font-size: 0.8125rem; color: rgba(241,243,245,0.5); }

.tareas-filtros {
    display: flex; flex-wrap: wrap; gap: 0.375rem;
    margin-bottom: 1rem; padding: 0.5rem; background: #1a1d21;
    border: 1px solid rgba(255,255,255,0.06); border-radius: 10px;
}
.tareas-filtros .grupo {
    display: flex; align-items: center; gap: 0.25rem;
}
.tareas-filtros .label {
    font-size: 0.6875rem; text-transform: uppercase; letter-spacing: 0.04em;
    color: rgba(241,243,245,0.4); padding: 0 0.5rem;
}
.pill {
    padding: 0.3125rem 0.625rem; border-radius: 999px;
    font-size: 0.75rem; font-weight: 500; text-decoration: none;
    color: rgba(241,243,245,0.6); background: rgba(255,255,255,0.04);
    border: 1px solid transparent; transition: all 0.15s;
    white-space: nowrap;
}
.pill:hover { background: rgba(255,255,255,0.08); color: #f1f3f5; }
.pill.activa {
    background: rgba(107,143,199,0.18); color: #b8cceb;
    border-color: rgba(107,143,199,0.3);
}
.pill-select {
    background: rgba(255,255,255,0.04);
    color: #f1f3f5;
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 999px;
    padding: 0.3125rem 0.625rem;
    font-size: 0.75rem;
}
.pill-select option {
    background: #1a1d21;
    color: #f1f3f5;
}
.modal-box select option {
    background: #1a1d21;
    color: #f1f3f5;
}

.tareas-lista { display: flex; flex-direction: column; gap: 0.5rem; }

.tarea-card {
    background: #1a1d21;
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 10px;
    padding: 0.875rem 1rem;
    display: grid;
    grid-template-columns: auto 1fr auto;
    gap: 0.75rem;
    align-items: start;
    transition: border-color 0.15s, background 0.15s;
}
.tarea-card:hover { border-color: rgba(255,255,255,0.12); }
.tarea-card.hecha { opacity: 0.55; }
.tarea-card.hecha .tarea-titulo { text-decoration: line-through; }
.tarea-card.vencida { border-left: 3px solid #ff6b6b; }

.tarea-check {
    width: 22px; height: 22px;
    border: 2px solid rgba(255,255,255,0.2);
    border-radius: 6px; cursor: pointer;
    background: transparent;
    display: flex; align-items: center; justify-content: center;
    margin-top: 1px; flex-shrink: 0;
    transition: all 0.15s;
}
.tarea-check:hover { border-color: #6b8fc7; }
.tarea-card.hecha .tarea-check {
    background: #34c759; border-color: #34c759; color: #0d0f11;
}

.tarea-cuerpo { min-width: 0; }
.tarea-titulo {
    font-size: 0.9375rem; font-weight: 500; color: #f1f3f5;
    margin: 0 0 0.25rem 0; word-break: break-word;
}
.tarea-desc {
    font-size: 0.8125rem; color: rgba(241,243,245,0.55);
    margin: 0 0 0.375rem 0; line-height: 1.4;
    white-space: pre-wrap; word-break: break-word;
}
.tarea-meta {
    display: flex; flex-wrap: wrap; gap: 0.375rem;
    align-items: center; font-size: 0.6875rem;
}
.tarea-tag {
    padding: 0.125rem 0.5rem; border-radius: 4px;
    font-weight: 500; text-transform: uppercase; letter-spacing: 0.03em;
}
.tag-depto { background: rgba(107,143,199,0.15); color: #8aa8d6; }
.tag-prio-alta { background: rgba(255,69,58,0.15); color: #ff8a80; }
.tag-prio-media { background: rgba(255,159,10,0.15); color: #ffb86b; }
.tag-prio-baja { background: rgba(139,147,156,0.15); color: rgba(241,243,245,0.55); }
.tag-estado-pendiente { background: rgba(139,147,156,0.15); color: rgba(241,243,245,0.55); }
.tag-estado-en_curso { background: rgba(107,143,199,0.18); color: #b8cceb; }
.tag-estado-bloqueada { background: rgba(255,159,10,0.18); color: #ffb86b; }
.tag-estado-hecha { background: rgba(52,199,89,0.15); color: #6ee7a0; }
.tag-personal { background: rgba(175,82,222,0.15); color: #c98ee3; }
.tag-fecha { color: rgba(241,243,245,0.55); }
.tag-fecha.vencida { color: #ff8a80; font-weight: 600; }

.tarea-acciones {
    display: flex; gap: 0.25rem; align-items: center;
    flex-shrink: 0;
}
.icon-btn {
    width: 28px; height: 28px; border-radius: 6px;
    border: none; background: transparent;
    color: rgba(241,243,245,0.4); cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    transition: all 0.15s;
}
.icon-btn:hover { background: rgba(255,255,255,0.08); color: #f1f3f5; }
.icon-btn.danger:hover { background: rgba(255,69,58,0.12); color: #ff8a80; }
.icon-btn.pin-activo { color: #ffb86b; background: rgba(255,159,10,0.12); }
.icon-btn.pin-activo:hover { background: rgba(255,159,10,0.2); color: #ffd29e; }

.tareas-empty {
    text-align: center; padding: 3rem 1rem;
    color: rgba(241,243,245,0.4); font-size: 0.875rem;
    background: #1a1d21; border-radius: 10px;
    border: 1px dashed rgba(255,255,255,0.08);
}

/* Modal */
.modal-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,0.6); z-index: 2000;
    align-items: center; justify-content: center;
    padding: 1rem;
}
.modal-overlay.abierto { display: flex; }
.modal-box {
    background: #1a1d21; border: 1px solid rgba(255,255,255,0.08);
    border-radius: 12px; padding: 1.25rem;
    width: 100%; max-width: 520px;
    max-height: 90vh; overflow-y: auto;
}
.modal-box h2 {
    font-size: 1.125rem; margin: 0 0 1rem 0;
    color: #f1f3f5;
}
.modal-box label {
    display: block; font-size: 0.75rem; font-weight: 500;
    color: rgba(241,243,245,0.7); margin-bottom: 0.25rem;
    margin-top: 0.625rem;
}
.modal-box input[type=text],
.modal-box input[type=date],
.modal-box textarea,
.modal-box select {
    width: 100%; padding: 0.5rem 0.625rem;
    background: #0d0f11; color: #f1f3f5;
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 6px; font-size: 0.875rem;
    font-family: inherit;
}
.modal-box textarea { resize: vertical; min-height: 70px; }
.modal-box .row { display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; }
.modal-acciones {
    display: flex; gap: 0.5rem; justify-content: flex-end;
    margin-top: 1.25rem;
}
.btn-modal {
    padding: 0.5rem 1rem; border-radius: 6px;
    font-size: 0.8125rem; font-weight: 500;
    border: none; cursor: pointer;
    text-decoration: none; display: inline-flex; align-items: center;
}
.btn-cancelar { background: rgba(255,255,255,0.06); color: rgba(241,243,245,0.7); }
.btn-guardar { background: #6b8fc7; color: #0d0f11; }
.btn-guardar:hover { background: #8aa8d6; }
.btn-cancelar:hover { background: rgba(255,255,255,0.1); color: #f1f3f5; }

.btn-nueva {
    padding: 0.5rem 0.875rem; border-radius: 8px;
    background: #6b8fc7; color: #0d0f11;
    font-size: 0.8125rem; font-weight: 600;
    text-decoration: none; border: none; cursor: pointer;
    display: inline-flex; align-items: center; gap: 0.375rem;
}
.btn-nueva:hover { background: #8aa8d6; }

@media (max-width: 600px) {
    .tarea-card { grid-template-columns: auto 1fr; }
    .tarea-acciones { grid-column: 2; justify-content: flex-end; margin-top: 0.25rem; }
    .modal-box .row { grid-template-columns: 1fr; }
}
</style>
@endpush

@section('content')
<div class="tareas-page">
    <div class="tareas-header">
        <div>
            <h1>Tareas</h1>
            <div class="tareas-count">{{ $tareas->total() }} {{ $tareas->total() === 1 ? 'tarea' : 'tareas' }}</div>
        </div>
        <button type="button" class="btn-nueva" onclick="abrirModalNueva()">+ Nueva tarea</button>
    </div>

    <form method="GET" action="{{ route('tareas.index') }}" id="filtros-form">
    <div class="tareas-filtros">
        <div class="grupo">
            <span class="label">Asignación</span>
            @php $asig = $filtros['asignacion'] ?? 'todas'; @endphp
            <a class="pill {{ $asig === 'todas' ? 'activa' : '' }}"
               href="{{ route('tareas.index', request()->except('asignacion','page')) }}">Todas</a>
            <a class="pill {{ $asig === 'mias' ? 'activa' : '' }}"
               href="{{ route('tareas.index', array_merge(request()->except('page'), ['asignacion' => 'mias'])) }}">Mías</a>
        </div>

        <div class="grupo">
            <span class="label">Estado</span>
            @php $est = $filtros['estado'] ?? 'activas'; @endphp
            <a class="pill {{ $est === 'activas' ? 'activa' : '' }}"
               href="{{ route('tareas.index', array_merge(request()->except('page'), ['estado' => 'activas'])) }}">Activas</a>
            <a class="pill {{ $est === 'hecha' ? 'activa' : '' }}"
               href="{{ route('tareas.index', array_merge(request()->except('page'), ['estado' => 'hecha'])) }}">Hechas</a>
            <a class="pill {{ $est === 'todas' ? 'activa' : '' }}"
               href="{{ route('tareas.index', array_merge(request()->except('page'), ['estado' => 'todas'])) }}">Todas</a>
        </div>

        <div class="grupo">
            <span class="label">Departamento</span>
            <select name="depto" class="pill-select" onchange="document.getElementById('filtros-form').submit()">
                <option value="todos" {{ ($filtros['depto'] ?? '') === '' || ($filtros['depto'] ?? '') === 'todos' ? 'selected' : '' }}>Todos</option>
                @foreach($todosDepartamentos as $key => $nombre)
                    <option value="{{ $key }}" {{ ($filtros['depto'] ?? '') === $key ? 'selected' : '' }}>{{ $nombre }}</option>
                @endforeach
            </select>
        </div>

        <div class="grupo">
            <span class="label">Visibilidad</span>
            @php $vis = $filtros['visibilidad'] ?? ''; @endphp
            <a class="pill {{ $vis === '' ? 'activa' : '' }}"
               href="{{ route('tareas.index', request()->except('visibilidad','page')) }}">Todas</a>
            <a class="pill {{ $vis === 'publica' ? 'activa' : '' }}"
               href="{{ route('tareas.index', array_merge(request()->except('page'), ['visibilidad' => 'publica'])) }}">Públicas</a>
            <a class="pill {{ $vis === 'personal' ? 'activa' : '' }}"
               href="{{ route('tareas.index', array_merge(request()->except('page'), ['visibilidad' => 'personal'])) }}">Personales</a>
        </div>

        @foreach($filtros as $k => $v)
            @if($v && $k !== 'depto')
                <input type="hidden" name="{{ $k }}" value="{{ $v }}">
            @endif
        @endforeach
    </div>
    </form>

    <div class="tareas-lista">
        @forelse($tareas as $t)
            @php
                $vencida = $t->fecha_limite && $t->estado !== 'hecha' && $t->fecha_limite->isPast();
                $deptoNombre = \App\Models\Tarea::DEPARTAMENTOS[$t->departamento] ?? $t->departamento;
                $estadoNombre = \App\Models\Tarea::ESTADOS[$t->estado] ?? $t->estado;
                $puedeEditar = Auth::user()->canEditarTarea($t);
            @endphp
            <div class="tarea-card {{ $t->estado === 'hecha' ? 'hecha' : '' }} {{ $vencida ? 'vencida' : '' }}"
                 data-id="{{ $t->id }}"
                 data-titulo="{{ $t->titulo }}"
                 data-descripcion="{{ $t->descripcion }}"
                 data-departamento="{{ $t->departamento }}"
                 data-asignado="{{ $t->asignado_a }}"
                 data-visibilidad="{{ $t->visibilidad }}"
                 data-estado="{{ $t->estado }}"
                 data-prioridad="{{ $t->prioridad }}"
                 data-fecha="{{ $t->fecha_limite ? $t->fecha_limite->format('Y-m-d') : '' }}">

                <form method="POST" action="{{ route('tareas.toggle-hecha', $t) }}" style="margin:0">
                    @csrf
                    <button type="submit" class="tarea-check" title="{{ $t->estado === 'hecha' ? 'Marcar pendiente' : 'Marcar hecha' }}" {{ $puedeEditar ? '' : 'disabled' }}>
                        @if($t->estado === 'hecha')
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                        @endif
                    </button>
                </form>

                <div class="tarea-cuerpo">
                    <h3 class="tarea-titulo">{{ $t->titulo }}</h3>
                    @if($t->descripcion)
                        <p class="tarea-desc">{{ $t->descripcion }}</p>
                    @endif
                    <div class="tarea-meta">
                        <span class="tarea-tag tag-depto">{{ $deptoNombre }}</span>
                        <span class="tarea-tag tag-estado-{{ $t->estado }}">{{ $estadoNombre }}</span>
                        @if($t->prioridad !== 'media')
                            <span class="tarea-tag tag-prio-{{ $t->prioridad }}">{{ ucfirst($t->prioridad) }}</span>
                        @endif
                        @if($t->visibilidad === 'personal')
                            <span class="tarea-tag tag-personal">Personal</span>
                        @endif
                        @if($t->asignado)
                            <span class="tag-fecha">→ {{ $t->asignado->name }}</span>
                        @endif
                        @if($t->fecha_limite)
                            <span class="tag-fecha {{ $vencida ? 'vencida' : '' }}">📅 {{ $t->fecha_limite->format('d M') }}</span>
                        @endif
                    </div>
                </div>

                <div class="tarea-acciones">
                    @php $estaAnclada = in_array($t->id, $ancladasIds ?? []); @endphp
                    <form method="POST" action="{{ route('tareas.anclar', $t) }}" style="margin:0">
                        @csrf
                        <button type="submit" class="icon-btn {{ $estaAnclada ? 'pin-activo' : '' }}" title="{{ $estaAnclada ? 'Desanclar' : 'Anclar al widget' }}">
                            @if($estaAnclada)
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M16 12V4h1V2H7v2h1v8l-2 2v2h5.2v6h1.6v-6H18v-2l-2-2z"/></svg>
                            @else
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 12V4h1V2H7v2h1v8l-2 2v2h5.2v6h1.6v-6H18v-2l-2-2z"/></svg>
                            @endif
                        </button>
                    </form>
                    @if($puedeEditar)
                        <button type="button" class="icon-btn" title="Editar" onclick="abrirModalEditar({{ $t->id }})">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </button>
                    @endif
                    @if(Auth::user()->isAdmin() || $t->creado_por === Auth::id())
                        <form method="POST" action="{{ route('tareas.destroy', $t) }}" style="margin:0" onsubmit="return confirm('¿Eliminar tarea?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="icon-btn danger" title="Eliminar">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-2 14a2 2 0 0 1-2 2H9a2 2 0 0 1-2-2L5 6"/></svg>
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="tareas-empty">No hay tareas que coincidan con los filtros.</div>
        @endforelse
    </div>

    @if($tareas->hasPages())
        <div style="margin-top: 1rem; display: flex; justify-content: center;">
            {{ $tareas->links() }}
        </div>
    @endif
</div>

<!-- Modal nueva/editar -->
<div class="modal-overlay" id="modal-tarea" data-is-admin="{{ Auth::user()->isAdmin() ? '1' : '0' }}">
    <div class="modal-box">
        <h2 id="modal-titulo">Nueva tarea</h2>
        <form method="POST" id="form-tarea" action="{{ route('tareas.store') }}">
            @csrf
            <input type="hidden" name="_method" id="form-method" value="POST">

            <label>Título *</label>
            <input type="text" name="titulo" id="campo-titulo" required maxlength="255">

            <label>Descripción</label>
            <textarea name="descripcion" id="campo-descripcion" maxlength="5000"></textarea>

            <div class="row">
                <div>
                    <label>Visibilidad *</label>
                    <select name="visibilidad" id="campo-visibilidad" onchange="actualizarVisibilidad()">
                        <option value="publica">Pública (de departamento)</option>
                        <option value="personal">Personal (sólo mía)</option>
                    </select>
                </div>
                <div>
                    <label>Departamento *</label>
                    <select name="departamento" id="campo-departamento">
                        @foreach($todosDepartamentos as $key => $nombre)
                            <option value="{{ $key }}" {{ array_key_exists($key, $departamentosPermitidos) ? '' : 'data-restricted="1"' }}>
                                {{ $nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row">
                <div>
                    <label>Asignar a</label>
                    <select name="asignado_a" id="campo-asignado">
                        <option value="">Sin asignar</option>
                        @foreach($usuariosAsignables as $u)
                            <option value="{{ $u->id }}">{{ $u->name }} <span style="opacity:0.6">({{ $u->rol_nombre }})</span></option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label>Prioridad</label>
                    <select name="prioridad" id="campo-prioridad">
                        <option value="baja">Baja</option>
                        <option value="media" selected>Media</option>
                        <option value="alta">Alta</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div>
                    <label>Estado</label>
                    <select name="estado" id="campo-estado">
                        @foreach($estados as $k => $v)
                            <option value="{{ $k }}">{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label>Fecha límite</label>
                    <input type="date" name="fecha_limite" id="campo-fecha">
                </div>
            </div>

            <div class="modal-acciones">
                <button type="button" class="btn-modal btn-cancelar" onclick="cerrarModal()">Cancelar</button>
                <button type="submit" class="btn-modal btn-guardar">Guardar</button>
            </div>
        </form>
    </div>
</div>

<script>
(function() {
    const modal = document.getElementById('modal-tarea');
    const form = document.getElementById('form-tarea');
    const baseUrl = "{{ route('tareas.index') }}";

    window.abrirModalNueva = function() {
        document.getElementById('modal-titulo').textContent = 'Nueva tarea';
        form.action = "{{ route('tareas.store') }}";
        document.getElementById('form-method').value = 'POST';
        document.getElementById('campo-titulo').value = '';
        document.getElementById('campo-descripcion').value = '';
        document.getElementById('campo-visibilidad').value = 'publica';
        document.getElementById('campo-departamento').value = 'general';
        document.getElementById('campo-asignado').value = '';
        document.getElementById('campo-prioridad').value = 'media';
        document.getElementById('campo-estado').value = 'pendiente';
        document.getElementById('campo-estado').closest('div').style.display = 'none';
        document.getElementById('campo-fecha').value = '';
        actualizarVisibilidad();
        modal.classList.add('abierto');
    };

    window.abrirModalEditar = function(id) {
        const card = document.querySelector('.tarea-card[data-id="' + id + '"]');
        if (!card) return;
        document.getElementById('modal-titulo').textContent = 'Editar tarea';
        form.action = baseUrl.replace(/\/?$/, '') + '/' + id;
        document.getElementById('form-method').value = 'PUT';
        document.getElementById('campo-titulo').value = card.dataset.titulo;
        document.getElementById('campo-descripcion').value = card.dataset.descripcion;
        document.getElementById('campo-visibilidad').value = card.dataset.visibilidad;
        document.getElementById('campo-departamento').value = card.dataset.departamento;
        document.getElementById('campo-asignado').value = card.dataset.asignado;
        document.getElementById('campo-prioridad').value = card.dataset.prioridad;
        document.getElementById('campo-estado').value = card.dataset.estado;
        document.getElementById('campo-estado').closest('div').style.display = '';
        document.getElementById('campo-fecha').value = card.dataset.fecha;
        actualizarVisibilidad();
        modal.classList.add('abierto');
    };

    window.cerrarModal = function() { modal.classList.remove('abierto'); };

    const esAdmin = modal.dataset.isAdmin === '1';
    window.actualizarVisibilidad = function() {
        const vis = document.getElementById('campo-visibilidad').value;
        const asignado = document.getElementById('campo-asignado');
        const deptoCampo = document.getElementById('campo-departamento');
        // Admin: puede asignar personales a otros usuarios. Otros: personal = autoasignada.
        if (vis === 'personal' && !esAdmin) {
            asignado.value = '';
            asignado.disabled = true;
        } else {
            asignado.disabled = false;
        }
    };

    modal.addEventListener('click', function(e) {
        if (e.target === modal) cerrarModal();
    });
})();
</script>
@endsection
