@extends('layouts.app')

@section('title', 'Editar programa - ' . $programa->fecha_semana->format('d/m/Y'))

@section('content')

<div class="page-md">
    <div class="flex justify-between items-center mb-2" style="flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 class="page-title">Semana del {{ $programa->fecha_semana->translatedFormat('d \d\e F, Y') }}</h1>
            <p class="page-subtitle">
                @if($programa->estado === 'publicado')
                    <span class="badge badge-green">Publicado</span>
                @else
                    <span class="badge badge-gray">Borrador</span>
                @endif
                &mdash; {{ $programa->contarAsignaciones() }} de {{ $programa->totalPartes() }} partes asignadas
            </p>
        </div>
        <div class="flex gap-1" style="flex-wrap: wrap;">
            <button type="button" id="btn-importar-jw" class="btn btn-secondary" onclick="importarDesdeJw()">Traer titulos de jw.org</button>
            <form action="{{ route('reuniones.auto-asignar', $programa) }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-teal" onclick="return confirm('Rellenar automaticamente las partes que estan vacias. Las que ya tienen publicador no se tocan. Continuar?')">Rellenar huecos</button>
            </form>
            <a href="{{ route('reuniones.show', $programa) }}" class="btn btn-secondary">Ver para imprimir</a>
            <a href="{{ route('reuniones.index') }}" class="btn btn-ghost">Volver</a>
        </div>
    </div>

    @if($programa->estado === 'publicado')
    <div class="alert-info-tipo mb-2" style="display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
        <span style="display: flex; align-items: center; gap: 0.5rem;">
            <span class="alert-icon">i</span>
            <span>Esta semana ya esta <strong>publicada</strong>. Puedes editar lo que necesites: los cambios se guardan al instante.</span>
        </span>
        <form action="{{ route('reuniones.despublicar', $programa) }}" method="POST" style="margin:0;">
            @csrf
            <button type="submit" class="btn btn-secondary" onclick="return confirm('Volver la semana a borrador para editarla con calma?')">↻ Volver a borrador</button>
        </form>
    </div>
    @endif

    @if($sinGenero > 0)
    <div class="alert-info-tipo mb-2">
        <span class="alert-icon">!</span>
        <span>Hay {{ $sinGenero }} publicadores sin genero asignado. <a href="{{ route('reuniones.generos') }}" style="color: inherit; text-decoration: underline;">Asignar generos</a> antes de usar auto-asignacion.</span>
    </div>
    @endif

    <form action="{{ route('reuniones.update', $programa) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- ROLES GLOBALES --}}
        <div class="reunion-seccion reunion-seccion-global">
            <h3 class="reunion-seccion-titulo">Roles generales</h3>
            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label">Presidente <button type="button" class="btn-rec" onclick="recomendar('presidente','[name=presidente_id]')" title="Ver recomendaciones">?</button></label>
                    @php $__autIds = $autPorTipo['presidente'] ?? []; @endphp
                    <select name="presidente_id" class="form-input">
                        <option value="">-- Sin asignar --</option>
                        @foreach($publicadores->filter(fn($p) => in_array($p->id, $__autIds)) as $p)
                            <option value="{{ $p->id }}" {{ $programa->presidente_id == $p->id ? 'selected' : '' }}>{{ $p->nombre_completo }}</option>
                        @endforeach
                        @php $__otros = $publicadores->filter(fn($p) => !in_array($p->id, $__autIds) && $p->genero === 'M'); @endphp
                        @if($__otros->isNotEmpty())
                            <optgroup label="Otros">
                            @foreach($__otros as $p)
                                <option value="{{ $p->id }}" {{ $programa->presidente_id == $p->id ? 'selected' : '' }}>{{ $p->nombre_completo }}</option>
                            @endforeach
                            </optgroup>
                        @endif
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Oracion de inicio <button type="button" class="btn-rec" onclick="recomendar('oracion_inicio','[name=oracion_inicio_id]')" title="Ver recomendaciones">?</button></label>
                    @php $__autIds = $autPorTipo['oracion'] ?? []; @endphp
                    <select name="oracion_inicio_id" class="form-input">
                        <option value="">-- Sin asignar --</option>
                        @foreach($publicadores->filter(fn($p) => in_array($p->id, $__autIds)) as $p)
                            <option value="{{ $p->id }}" {{ $programa->oracion_inicio_id == $p->id ? 'selected' : '' }}>{{ $p->nombre_completo }}</option>
                        @endforeach
                        @php $__otros = $publicadores->filter(fn($p) => !in_array($p->id, $__autIds) && $p->genero === 'M'); @endphp
                        @if($__otros->isNotEmpty())
                            <optgroup label="Otros">
                            @foreach($__otros as $p)
                                <option value="{{ $p->id }}" {{ $programa->oracion_inicio_id == $p->id ? 'selected' : '' }}>{{ $p->nombre_completo }}</option>
                            @endforeach
                            </optgroup>
                        @endif
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Conductor estudio <button type="button" class="btn-rec" onclick="recomendar('conductor_estudio','[name=conductor_estudio_id]')" title="Ver recomendaciones">?</button></label>
                    @php $__autIds = $autPorTipo['conductor_estudio'] ?? []; @endphp
                    <select name="conductor_estudio_id" class="form-input">
                        <option value="">-- Sin asignar --</option>
                        @foreach($publicadores->filter(fn($p) => in_array($p->id, $__autIds)) as $p)
                            <option value="{{ $p->id }}" {{ $programa->conductor_estudio_id == $p->id ? 'selected' : '' }}>{{ $p->nombre_completo }}</option>
                        @endforeach
                        @php $__otros = $publicadores->filter(fn($p) => !in_array($p->id, $__autIds) && $p->genero === 'M'); @endphp
                        @if($__otros->isNotEmpty())
                            <optgroup label="Otros">
                            @foreach($__otros as $p)
                                <option value="{{ $p->id }}" {{ $programa->conductor_estudio_id == $p->id ? 'selected' : '' }}>{{ $p->nombre_completo }}</option>
                            @endforeach
                            </optgroup>
                        @endif
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Lector estudio <button type="button" class="btn-rec" onclick="recomendar('lector_estudio','[name=lector_estudio_id]')" title="Ver recomendaciones">?</button></label>
                    @php $__autIds = $autPorTipo['lector_estudio'] ?? []; @endphp
                    <select name="lector_estudio_id" class="form-input">
                        <option value="">-- Sin asignar --</option>
                        @foreach($publicadores->filter(fn($p) => in_array($p->id, $__autIds)) as $p)
                            <option value="{{ $p->id }}" {{ $programa->lector_estudio_id == $p->id ? 'selected' : '' }}>{{ $p->nombre_completo }}</option>
                        @endforeach
                        @php $__otros = $publicadores->filter(fn($p) => !in_array($p->id, $__autIds) && $p->genero === 'M'); @endphp
                        @if($__otros->isNotEmpty())
                            <optgroup label="Otros">
                            @foreach($__otros as $p)
                                <option value="{{ $p->id }}" {{ $programa->lector_estudio_id == $p->id ? 'selected' : '' }}>{{ $p->nombre_completo }}</option>
                            @endforeach
                            </optgroup>
                        @endif
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Oracion final <button type="button" class="btn-rec" onclick="recomendar('oracion_final','[name=oracion_final_id]')" title="Ver recomendaciones">?</button></label>
                    @php $__autIds = $autPorTipo['oracion'] ?? []; @endphp
                    <select name="oracion_final_id" class="form-input">
                        <option value="">-- Sin asignar --</option>
                        @foreach($publicadores->filter(fn($p) => in_array($p->id, $__autIds)) as $p)
                            <option value="{{ $p->id }}" {{ $programa->oracion_final_id == $p->id ? 'selected' : '' }}>{{ $p->nombre_completo }}</option>
                        @endforeach
                        @php $__otros = $publicadores->filter(fn($p) => !in_array($p->id, $__autIds) && $p->genero === 'M'); @endphp
                        @if($__otros->isNotEmpty())
                            <optgroup label="Otros">
                            @foreach($__otros as $p)
                                <option value="{{ $p->id }}" {{ $programa->oracion_final_id == $p->id ? 'selected' : '' }}>{{ $p->nombre_completo }}</option>
                            @endforeach
                            </optgroup>
                        @endif
                    </select>
                </div>
            </div>
        </div>

        {{-- TESOROS DE LA BIBLIA --}}
        <div class="reunion-seccion reunion-seccion-tesoros">
            <h3 class="reunion-seccion-titulo"><span class="seccion-icono">&#x1F48E;</span> Tesoros de la Biblia</h3>
            @foreach($programa->partes->where('seccion', 'tesoros') as $parte)
            <div class="reunion-parte">
                <div class="reunion-parte-header">
                    <span class="reunion-parte-tipo">{{ $parte->nombre_tipo }}</span>
                    <span class="text-muted text-xs">{{ $parte->duracion_minutos }} min</span>
                </div>
                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">Titulo</label>
                        <input type="text" name="partes[{{ $parte->id }}][titulo]" class="form-input" value="{{ $parte->titulo }}" placeholder="{{ $parte->nombre_tipo }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Asignado</label>
                        @php
                            $tipoAuth = match($parte->tipo) {
                                'discurso_tesoros' => 'tesoros',
                                'perlas' => 'perlas',
                                'lectura' => 'lectura',
                                default => null,
                            };
                            $__autIds = $tipoAuth ? ($autPorTipo[$tipoAuth] ?? []) : [];
                            $__autorizados = $tipoAuth ? $publicadores->filter(fn($p) => in_array($p->id, $__autIds)) : $publicadores;
                            $__otros = $tipoAuth ? $publicadores->filter(fn($p) => !in_array($p->id, $__autIds) && $p->genero === 'M') : collect();
                        @endphp
                        <select name="partes[{{ $parte->id }}][publicador_id]" class="form-input">
                            <option value="">-- Sin asignar --</option>
                            @foreach($__autorizados as $p)
                                <option value="{{ $p->id }}" {{ $parte->publicador_id == $p->id ? 'selected' : '' }}>{{ $p->nombre_completo }}</option>
                            @endforeach
                            @if($__otros->isNotEmpty())
                                <optgroup label="Otros">
                                @foreach($__otros as $p)
                                    <option value="{{ $p->id }}" {{ $parte->publicador_id == $p->id ? 'selected' : '' }}>{{ $p->nombre_completo }}</option>
                                @endforeach
                                </optgroup>
                            @endif
                        </select>
                    </div>
                </div>
                <input type="hidden" name="partes[{{ $parte->id }}][duracion_minutos]" value="{{ $parte->duracion_minutos }}">
                <input type="hidden" name="partes[{{ $parte->id }}][ayudante_id]" value="">
            </div>
            @endforeach
        </div>

        {{-- SEAMOS MEJORES MAESTROS --}}
        <div class="reunion-seccion reunion-seccion-maestros">
            <h3 class="reunion-seccion-titulo"><span class="seccion-icono">&#x1F33E;</span> Seamos mejores maestros</h3>
            @foreach($programa->partes->where('seccion', 'maestros') as $parte)
            <div class="reunion-parte">
                <div class="reunion-parte-header">
                    <span class="reunion-parte-tipo">{{ $parte->nombre_tipo }}</span>
                    <span class="text-muted text-xs">{{ $parte->duracion_minutos }} min</span>
                </div>
                @if($parte->tipo === 'discurso_maestros')
                {{-- DISCURSO: solo varones, sin ayudante --}}
                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">Titulo</label>
                        <input type="text" name="partes[{{ $parte->id }}][titulo]" class="form-input" value="{{ $parte->titulo }}" placeholder="Discurso">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Asignado <span class="text-muted text-xs">(solo varones)</span></label>
                        @php $__autIds = $autPorTipo['discurso_maestros'] ?? []; @endphp
                        <select name="partes[{{ $parte->id }}][publicador_id]" class="form-input">
                            <option value="">-- Sin asignar --</option>
                            @foreach($publicadores->filter(fn($p) => in_array($p->id, $__autIds)) as $p)
                                <option value="{{ $p->id }}" {{ $parte->publicador_id == $p->id ? 'selected' : '' }}>{{ $p->nombre_completo }}</option>
                            @endforeach
                            @php $__otros = $publicadores->filter(fn($p) => !in_array($p->id, $__autIds) && $p->genero === 'M'); @endphp
                            @if($__otros->isNotEmpty())
                                <optgroup label="Otros">
                                @foreach($__otros as $p)
                                    <option value="{{ $p->id }}" {{ $parte->publicador_id == $p->id ? 'selected' : '' }}>{{ $p->nombre_completo }}</option>
                                @endforeach
                                </optgroup>
                            @endif
                        </select>
                    </div>
                </div>
                <input type="hidden" name="partes[{{ $parte->id }}][duracion_minutos]" value="{{ $parte->duracion_minutos }}">
                <input type="hidden" name="partes[{{ $parte->id }}][ayudante_id]" value="">
                @else
                {{-- PARTE DE ESTUDIANTE: estudiante + ayudante --}}
                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">Estudiante
                            <button type="button" class="btn-rec btn-emergency" onclick="recomendarEmergencia('{{ $parte->tipo }}', {{ $parte->id }})" title="Reemplazo de emergencia">&#9889;</button>
                        </label>
                        <input type="hidden" name="emergencia[{{ $parte->id }}]" id="emergencia-{{ $parte->id }}" value="">
                        @php $__autIds = $autPorTipo['maestros'] ?? []; $__otros = $publicadores->filter(fn($p) => !in_array($p->id, $__autIds) && $p->genero); @endphp
                        <select name="partes[{{ $parte->id }}][publicador_id]" class="form-input" onchange="filtrarAyudante(this, {{ $parte->id }})" id="estudiante-{{ $parte->id }}">
                            <option value="">-- Sin asignar --</option>
                            @foreach($publicadores->filter(fn($p) => in_array($p->id, $__autIds)) as $p)
                                <option value="{{ $p->id }}" data-genero="{{ $p->genero }}" {{ $parte->publicador_id == $p->id ? 'selected' : '' }}>{{ $p->nombre_completo }}</option>
                            @endforeach
                            @if($__otros->isNotEmpty())
                                <optgroup label="Otros">
                                @foreach($__otros as $p)
                                    <option value="{{ $p->id }}" data-genero="{{ $p->genero }}" {{ $parte->publicador_id == $p->id ? 'selected' : '' }}>{{ $p->nombre_completo }}</option>
                                @endforeach
                                </optgroup>
                            @endif
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Ayudante</label>
                        <select name="partes[{{ $parte->id }}][ayudante_id]" class="form-input" id="ayudante-{{ $parte->id }}">
                            <option value="">-- Sin asignar --</option>
                            @foreach($publicadores->filter(fn($p) => in_array($p->id, $__autIds)) as $p)
                                <option value="{{ $p->id }}" data-genero="{{ $p->genero }}" {{ $parte->ayudante_id == $p->id ? 'selected' : '' }}>{{ $p->nombre_completo }}</option>
                            @endforeach
                            @if($__otros->isNotEmpty())
                                <optgroup label="Otros">
                                @foreach($__otros as $p)
                                    <option value="{{ $p->id }}" data-genero="{{ $p->genero }}" {{ $parte->ayudante_id == $p->id ? 'selected' : '' }}>{{ $p->nombre_completo }}</option>
                                @endforeach
                                </optgroup>
                            @endif
                        </select>
                    </div>
                </div>
                <input type="hidden" name="partes[{{ $parte->id }}][titulo]" value="{{ $parte->titulo }}">
                <input type="hidden" name="partes[{{ $parte->id }}][duracion_minutos]" value="{{ $parte->duracion_minutos }}">
                @endif
            </div>
            @endforeach
        </div>

        {{-- NUESTRA VIDA CRISTIANA --}}
        <div class="reunion-seccion reunion-seccion-vida">
            <h3 class="reunion-seccion-titulo"><span class="seccion-icono">&#x1F411;</span> Nuestra vida cristiana</h3>
            @foreach($programa->partes->where('seccion', 'vida_cristiana') as $parte)
            <div class="reunion-parte">
                <div class="reunion-parte-header">
                    <span class="reunion-parte-tipo">{{ $parte->nombre_tipo }}</span>
                    <span class="text-muted text-xs">{{ $parte->duracion_minutos }} min</span>
                </div>
                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">Titulo</label>
                        <input type="text" name="partes[{{ $parte->id }}][titulo]" class="form-input" value="{{ $parte->titulo }}" placeholder="{{ $parte->nombre_tipo }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Asignado</label>
                        @php $__autIds = $autPorTipo[$parte->tipo] ?? []; @endphp
                        <select name="partes[{{ $parte->id }}][publicador_id]" class="form-input">
                            <option value="">-- Sin asignar --</option>
                            @foreach($publicadores->filter(fn($p) => in_array($p->id, $__autIds)) as $p)
                                <option value="{{ $p->id }}" {{ $parte->publicador_id == $p->id ? 'selected' : '' }}>{{ $p->nombre_completo }}</option>
                            @endforeach
                            @php $__otros = $publicadores->filter(fn($p) => !in_array($p->id, $__autIds) && $p->genero === 'M'); @endphp
                            @if($__otros->isNotEmpty())
                                <optgroup label="Otros">
                                @foreach($__otros as $p)
                                    <option value="{{ $p->id }}" {{ $parte->publicador_id == $p->id ? 'selected' : '' }}>{{ $p->nombre_completo }}</option>
                                @endforeach
                                </optgroup>
                            @endif
                        </select>
                    </div>
                </div>
                <input type="hidden" name="partes[{{ $parte->id }}][duracion_minutos]" value="{{ $parte->duracion_minutos }}">
                <input type="hidden" name="partes[{{ $parte->id }}][ayudante_id]" value="">
            </div>
            @endforeach
        </div>

        {{-- NOTAS --}}
        <div class="form-group mt-2">
            <label class="form-label">Notas (opcional)</label>
            <textarea name="notas" class="form-input" rows="3" placeholder="Notas internas sobre este programa...">{{ $programa->notas }}</textarea>
        </div>

        <div class="flex justify-between items-center mt-2" style="flex-wrap: wrap; gap: 0.5rem;">
            <div class="flex gap-1">
                <button type="submit" class="btn btn-teal">Guardar cambios</button>
                @if($programa->estado === 'borrador')
                <form action="{{ route('reuniones.publicar', $programa) }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-secondary" onclick="return confirm('Publicar este programa?')">Publicar</button>
                </form>
                @endif
            </div>
            <form action="{{ route('reuniones.destroy', $programa) }}" method="POST" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-ghost" style="color: var(--error); font-size: 0.8rem;" onclick="return confirm('ELIMINAR este programa completo con todas sus asignaciones? Esta accion no se puede deshacer.')">Eliminar programa</button>
            </form>
        </div>
    </form>
</div>

<!-- Modal de recomendaciones -->
<div id="modal-recomendar" class="modal-backdrop" style="display:none;" onclick="if(event.target===this)cerrarRecomendar()">
    <div class="modal" style="max-width:450px;" role="dialog" aria-modal="true" aria-label="Recomendaciones de asignacion">
        <div class="modal-header">
            <h3 class="modal-title" id="modal-recomendar-titulo">Recomendaciones</h3>
            <button class="modal-close" onclick="cerrarRecomendar()">&times;</button>
        </div>
        <div class="modal-body" id="modal-recomendar-body">
            <p class="text-muted">Cargando...</p>
        </div>
    </div>
</div>

<!-- Modal de emergencia -->
<div id="modal-emergencia" class="modal-backdrop" style="display:none;" onclick="if(event.target===this)cerrarEmergencia()">
    <div class="modal" style="max-width:450px;" role="dialog" aria-modal="true" aria-label="Reemplazo de emergencia">
        <div class="modal-header" style="border-top: 3px solid #d97706;">
            <h3 class="modal-title" id="modal-emergencia-titulo">&#9889; Reemplazo de emergencia</h3>
            <button class="modal-close" onclick="cerrarEmergencia()">&times;</button>
        </div>
        <div class="modal-body" id="modal-emergencia-body">
            <p class="text-muted">Cargando...</p>
        </div>
    </div>
</div>

<script>
let recomendarSelectActual = null;
let emergenciaParteIdActual = null;

async function recomendar(tipoParte, selectId) {
    recomendarSelectActual = document.querySelector(selectId) || document.getElementById(selectId);
    const modal = document.getElementById('modal-recomendar');
    const body = document.getElementById('modal-recomendar-body');
    const titulo = document.getElementById('modal-recomendar-titulo');

    titulo.textContent = 'Recomendaciones';
    body.innerHTML = '<p class="text-muted">Cargando...</p>';
    modal.style.display = 'flex';

    try {
        const resp = await fetch(`/reuniones/{{ $programa->id }}/recomendar/${tipoParte}`, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        });
        const data = await resp.json();

        if (!data.candidatos || data.candidatos.length === 0) {
            body.innerHTML = '<p class="text-muted">No hay candidatos disponibles</p>';
            return;
        }

        let html = '<div class="list-flat">';
        data.candidatos.forEach((c, i) => {
            const diasTexto = c.dias_desde_ultima !== null ? c.dias_desde_ultima + ' dias' : 'Nunca';
            const semanaTexto = c.esta_semana > 0 ? ` · ${c.esta_semana} esta semana` : '';
            html += `
                <a href="#" class="list-item" onclick="seleccionarRecomendado(${c.id});return false;">
                    <span class="content">
                        <span class="title">${i === 0 ? '⭐ ' : ''}${c.nombre}</span>
                        <span class="subtitle">${c.total_asignaciones} asignaciones · Ultima: ${diasTexto}${semanaTexto}</span>
                    </span>
                    <span class="meta">${c.puntuacion > 0 ? '+' : ''}${c.puntuacion}</span>
                </a>`;
        });
        html += '</div>';
        body.innerHTML = html;
    } catch (e) {
        body.innerHTML = '<p style="color:#ef4444;">Error al cargar recomendaciones</p>';
    }
}

function seleccionarRecomendado(id) {
    if (recomendarSelectActual) {
        recomendarSelectActual.value = id;
    }
    cerrarRecomendar();
}

function cerrarRecomendar() {
    document.getElementById('modal-recomendar').style.display = 'none';
}

async function recomendarEmergencia(tipoParte, parteId) {
    emergenciaParteIdActual = parteId;
    const modal = document.getElementById('modal-emergencia');
    const body = document.getElementById('modal-emergencia-body');
    const titulo = document.getElementById('modal-emergencia-titulo');

    titulo.textContent = '\u26A1 Reemplazo de emergencia';
    body.innerHTML = '<p class="text-muted">Cargando voluntarios...</p>';
    modal.style.display = 'flex';

    try {
        const resp = await fetch(`/reuniones/{{ $programa->id }}/recomendar-emergencia/${tipoParte}`, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        });
        const data = await resp.json();

        if (!data.candidatos || data.candidatos.length === 0) {
            body.innerHTML = '<p class="text-muted">No hay voluntarios de emergencia disponibles.<br><small>Agrega voluntarios en Autorizaciones &rarr; Grupo de emergencia</small></p>';
            return;
        }

        let html = '<div class="list-flat">';
        data.candidatos.forEach((c, i) => {
            const diasTexto = c.dias_desde_ultima !== null ? c.dias_desde_ultima + ' dias' : 'Nunca';
            const semanaTexto = c.esta_semana > 0 ? ` \u00B7 ${c.esta_semana} esta semana` : '';
            html += `
                <a href="#" class="list-item" onclick="seleccionarEmergencia(${c.id});return false;">
                    <span class="content">
                        <span class="title">${i === 0 ? '\u26A1 ' : ''}${c.nombre}</span>
                        <span class="subtitle">${c.total_emergencias} emergencias \u00B7 Ultima: ${diasTexto}${semanaTexto}</span>
                    </span>
                    <span class="meta">${c.puntuacion > 0 ? '+' : ''}${c.puntuacion}</span>
                </a>`;
        });
        html += '</div>';
        body.innerHTML = html;
    } catch (e) {
        body.innerHTML = '<p style="color:#ef4444;">Error al cargar voluntarios de emergencia</p>';
    }
}

function seleccionarEmergencia(id) {
    if (emergenciaParteIdActual) {
        const select = document.getElementById('estudiante-' + emergenciaParteIdActual);
        if (select) {
            select.value = id;
            // Disparar onchange para filtrar ayudante
            select.dispatchEvent(new Event('change'));
        }
        // Marcar como emergencia
        const hidden = document.getElementById('emergencia-' + emergenciaParteIdActual);
        if (hidden) hidden.value = '1';
    }
    cerrarEmergencia();
}

function cerrarEmergencia() {
    document.getElementById('modal-emergencia').style.display = 'none';
    emergenciaParteIdActual = null;
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') { cerrarRecomendar(); cerrarEmergencia(); }
});
</script>

<script>
async function importarDesdeJw() {
    const btn = document.getElementById('btn-importar-jw');
    const fecha = new Date('{{ $programa->fecha_semana->format("Y-m-d") }}');
    const url = `https://wol.jw.org/es/wol/dt/r4/lp-s/${fecha.getFullYear()}/${fecha.getMonth()+1}/${fecha.getDate()}`;

    if (!confirm('Traer titulos de jw.org? Se reemplazaran las partes actuales (no se pierden las personas asignadas).')) return;

    btn.textContent = 'Importando...';
    btn.disabled = true;

    try {
        // Fetch via proxy en VPS (wol.jw.org bloquea CORS y OVH bloquea salida HTTPS)
        const proxyUrl = `https://n8n.trastosbvaa.org/wol-proxy?y=${fecha.getFullYear()}&m=${fecha.getMonth()+1}&d=${fecha.getDate()}`;
        const resp = await fetch(proxyUrl);
        if (!resp.ok) throw new Error('No se pudo acceder a wol.jw.org');
        const html = await resp.text();

        // Parsear las partes del HTML
        const partes = parsearProgramaVym(html);
        if (partes.length === 0) {
            throw new Error('No se encontraron partes en el programa');
        }

        // Enviar al servidor
        const saveResp = await fetch('{{ route("reuniones.importar-titulos", $programa) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ partes }),
        });

        const result = await saveResp.json();
        if (result.success) {
            window.location.reload();
        } else {
            alert('Error: ' + (result.error || 'Error desconocido'));
        }
    } catch (e) {
        alert('Error al importar: ' + e.message);
    } finally {
        btn.textContent = 'Traer titulos de jw.org';
        btn.disabled = false;
    }
}

function parsearProgramaVym(html) {
    const parser = new DOMParser();
    const doc = parser.parseFromString(html, 'text/html');
    const partes = [];

    // Buscar el bloque del programa VyM (pub-mwb)
    const mwbBlock = doc.querySelector('.todayItem.pub-mwb');
    if (!mwbBlock) return partes;

    // Recorrer h2 (secciones) y h3 (partes) en orden dentro del bloque mwb
    const elementos = mwbBlock.querySelectorAll('h2, h3');
    let seccion = null;

    for (const el of elementos) {
        const texto = el.textContent.trim();

        // Detectar secciones (h2)
        if (el.tagName === 'H2') {
            if (/TESOROS DE LA BIBLIA/i.test(texto)) seccion = 'tesoros';
            else if (/SEAMOS MEJORES MAESTROS/i.test(texto)) seccion = 'maestros';
            else if (/NUESTRA VIDA CRISTIANA/i.test(texto)) seccion = 'vida_cristiana';
            continue;
        }

        if (!seccion) continue;

        // Extraer titulo del h3: "N. Titulo" o "Titulo"
        const tituloMatch = texto.match(/^(?:\d+\.\s*)?(.+)/);
        if (!tituloMatch) continue;
        let titulo = tituloMatch[1].trim();

        // La duracion puede estar:
        // 1. Dentro del h3: "Titulo (X min.)"
        // 2. En el siguiente <p> hermano: "(X min.)"
        let duracion = 0;
        const durInH3 = texto.match(/\((\d+)\s*mins?\.?\)/i);
        if (durInH3) {
            duracion = parseInt(durInH3[1]);
            titulo = titulo.replace(/\s*\(\d+\s*mins?\.?\)/, '').trim();
        } else {
            // Buscar en el siguiente elemento hermano (p)
            let next = el.nextElementSibling;
            if (next) {
                const durInNext = next.textContent.match(/\((\d+)\s*mins?\.?\)/i);
                if (durInNext) duracion = parseInt(durInNext[1]);
            }
        }

        // Ignorar canticos, oraciones, conclusiones, introducciones
        if (/^(Canci[oó]n|Song|Oraci[oó]n|Palabras de conclus|Palabras de introduc|Comentarios? iniciales?)/i.test(titulo)) continue;
        if (/canci[oó]n.*oraci[oó]n|oraci[oó]n.*canci[oó]n/i.test(titulo)) continue;

        // Necesitamos al menos un titulo valido (con o sin duracion)
        if (!titulo) continue;

        const tipo = clasificarTipo(seccion, titulo, duracion);
        if (!tipo) continue;

        const necesitaAyudante = ['empiece_conversaciones', 'haga_revisitas', 'haga_discipulos', 'explique_creencias'].includes(tipo);

        partes.push({ seccion, tipo, titulo, duracion, necesita_ayudante: necesitaAyudante });
    }

    return partes;
}

function clasificarTipo(seccion, titulo, duracion) {
    const t = titulo.toLowerCase();

    if (seccion === 'tesoros') {
        if (/perlas escondidas|busquemos perlas/i.test(titulo)) return 'perlas';
        if (/lectura de la biblia/i.test(titulo)) return 'lectura';
        return 'discurso_tesoros';
    }

    if (seccion === 'maestros') {
        if (/empiece conversacion/i.test(titulo)) return 'empiece_conversaciones';
        if (/haga revisita/i.test(titulo)) return 'haga_revisitas';
        if (/haga disc[ií]pulo/i.test(titulo)) return 'haga_discipulos';
        if (/explique.*creencia/i.test(titulo)) return 'explique_creencias';
        // Si no coincide con parte de estudiante, es un discurso (solo varones, sin ayudante)
        return 'discurso_maestros';
    }

    if (seccion === 'vida_cristiana') {
        if (/estudio b[ií]blico de la congregaci/i.test(titulo)) return null;
        if (/necesidades.*congregaci/i.test(titulo)) return 'necesidades';
        return 'discurso_vida';
    }

    return null;
}
</script>

<script>
// Mapa de conyuges para permitir genero opuesto si son matrimonio
const conyuges = @json($conyuges);

function filtrarAyudante(selectEstudiante, parteId) {
    const ayudanteSelect = document.getElementById('ayudante-' + parteId);
    if (!ayudanteSelect) return;

    const estudianteId = selectEstudiante.value;
    const opcionEstudiante = selectEstudiante.options[selectEstudiante.selectedIndex];
    const generoEstudiante = opcionEstudiante ? opcionEstudiante.dataset.genero : null;
    const conyugeId = estudianteId ? (conyuges[estudianteId] || null) : null;

    // Guardar valor actual del ayudante
    const ayudanteActual = ayudanteSelect.value;

    // Mostrar/ocultar opciones del ayudante segun genero
    for (let i = 0; i < ayudanteSelect.options.length; i++) {
        const opt = ayudanteSelect.options[i];
        if (!opt.value) continue; // opcion vacia

        if (!estudianteId || !generoEstudiante) {
            // Sin estudiante, mostrar todos
            opt.hidden = false;
            opt.disabled = false;
        } else if (opt.value === ayudanteActual && ayudanteActual) {
            // Ayudante ya asignado: siempre visible
            opt.hidden = false;
            opt.disabled = false;
        } else if (opt.value === estudianteId) {
            // No puede ser el mismo
            opt.hidden = true;
            opt.disabled = true;
        } else if (opt.dataset.genero === generoEstudiante) {
            // Mismo genero: OK
            opt.hidden = false;
            opt.disabled = false;
        } else if (conyugeId && opt.value === String(conyugeId)) {
            // Conyuge: OK aunque sea genero opuesto
            opt.hidden = false;
            opt.disabled = false;
        } else {
            // Genero opuesto y no es conyuge
            opt.hidden = true;
            opt.disabled = true;
        }
    }
}

// Ejecutar filtro al cargar para partes que ya tienen estudiante
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('select[onchange^="filtrarAyudante"]').forEach(function(sel) {
        if (sel.value) {
            const parteId = sel.getAttribute('onchange').match(/(\d+)/);
            if (parteId) filtrarAyudante(sel, parteId[1]);
        }
    });
});
</script>

@endsection
