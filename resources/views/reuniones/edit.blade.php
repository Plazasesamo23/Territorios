@extends('layouts.app')

@section('title', 'Editar programa - ' . $programa->fecha_semana->format('d/m/Y'))

@section('content')

<div class="page-md">
    <div class="flex justify-between items-center mb-2">
        <div>
            <h1 class="page-title">Semana del {{ $programa->fecha_semana->translatedFormat('d \d\e F, Y') }}</h1>
            <p class="page-subtitle">
                @if($programa->estado === 'publicado')
                    <span class="badge badge-green">Publicado</span>
                @else
                    <span class="badge badge-gray">Borrador</span>
                @endif
                &mdash; {{ $programa->contarAsignaciones() }}/{{ $programa->totalPartes() }} asignaciones
            </p>
        </div>
        <div class="flex gap-1">
            <button type="button" id="btn-importar-jw" class="btn btn-secondary" onclick="importarDesdeJw()">Importar de jw.org</button>
            <form action="{{ route('reuniones.auto-asignar', $programa) }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-teal" onclick="return confirm('Auto-asignar todas las partes vacias?')">Auto-asignar</button>
            </form>
            <a href="{{ route('reuniones.show', $programa) }}" class="btn btn-secondary">Vista imprimible</a>
        </div>
    </div>

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
                    <label class="form-label">Presidente <button type="button" class="btn-rec" onclick="recomendar('presidente','[name=presidente_id]')">?</button></label>
                    <select name="presidente_id" class="form-input">
                        <option value="">-- Sin asignar --</option>
                        @foreach($publicadores->where('es_anciano', true) as $p)
                            <option value="{{ $p->id }}" {{ $programa->presidente_id == $p->id ? 'selected' : '' }}>{{ $p->nombre_completo }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Oracion de inicio <button type="button" class="btn-rec" onclick="recomendar('oracion_inicio','[name=oracion_inicio_id]')">?</button></label>
                    <select name="oracion_inicio_id" class="form-input">
                        <option value="">-- Sin asignar --</option>
                        @foreach($publicadores->filter(fn($p) => $p->genero === 'M' && !$p->es_menor) as $p)
                            <option value="{{ $p->id }}" {{ $programa->oracion_inicio_id == $p->id ? 'selected' : '' }}>{{ $p->nombre_completo }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Conductor estudio <button type="button" class="btn-rec" onclick="recomendar('conductor_estudio','[name=conductor_estudio_id]')">?</button></label>
                    <select name="conductor_estudio_id" class="form-input">
                        <option value="">-- Sin asignar --</option>
                        @foreach($publicadores->where('puede_dirigir_estudio', true) as $p)
                            <option value="{{ $p->id }}" {{ $programa->conductor_estudio_id == $p->id ? 'selected' : '' }}>{{ $p->nombre_completo }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Lector estudio <button type="button" class="btn-rec" onclick="recomendar('lector_estudio','[name=lector_estudio_id]')">?</button></label>
                    <select name="lector_estudio_id" class="form-input">
                        <option value="">-- Sin asignar --</option>
                        @foreach($publicadores->where('puede_leer_estudio', true) as $p)
                            <option value="{{ $p->id }}" {{ $programa->lector_estudio_id == $p->id ? 'selected' : '' }}>{{ $p->nombre_completo }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Oracion final <button type="button" class="btn-rec" onclick="recomendar('oracion_final','[name=oracion_final_id]')">?</button></label>
                    <select name="oracion_final_id" class="form-input">
                        <option value="">-- Sin asignar --</option>
                        @foreach($publicadores->filter(fn($p) => $p->genero === 'M' && !$p->es_menor) as $p)
                            <option value="{{ $p->id }}" {{ $programa->oracion_final_id == $p->id ? 'selected' : '' }}>{{ $p->nombre_completo }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- TESOROS DE LA BIBLIA --}}
        <div class="reunion-seccion reunion-seccion-tesoros">
            <h3 class="reunion-seccion-titulo">Tesoros de la Biblia</h3>
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
                        <select name="partes[{{ $parte->id }}][publicador_id]" class="form-input">
                            <option value="">-- Sin asignar --</option>
                            @php
                                $elegibles = match($parte->tipo) {
                                    'lectura' => $publicadores->where('genero', 'M'),
                                    'discurso_tesoros', 'perlas' => $publicadores->filter(fn($p) => $p->genero === 'M' && ($p->es_anciano || $p->es_siervo_ministerial)),
                                    default => $publicadores,
                                };
                            @endphp
                            @foreach($elegibles as $p)
                                <option value="{{ $p->id }}" {{ $parte->publicador_id == $p->id ? 'selected' : '' }}>{{ $p->nombre_completo }}</option>
                            @endforeach
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
            <h3 class="reunion-seccion-titulo">Seamos mejores maestros</h3>
            @foreach($programa->partes->where('seccion', 'maestros') as $parte)
            <div class="reunion-parte">
                <div class="reunion-parte-header">
                    <span class="reunion-parte-tipo">{{ $parte->nombre_tipo }}</span>
                    <span class="text-muted text-xs">{{ $parte->duracion_minutos }} min</span>
                </div>
                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">Estudiante</label>
                        <select name="partes[{{ $parte->id }}][publicador_id]" class="form-input">
                            <option value="">-- Sin asignar --</option>
                            @foreach($publicadores as $p)
                                <option value="{{ $p->id }}" {{ $parte->publicador_id == $p->id ? 'selected' : '' }}>{{ $p->nombre_completo }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Ayudante</label>
                        <select name="partes[{{ $parte->id }}][ayudante_id]" class="form-input">
                            <option value="">-- Sin asignar --</option>
                            @foreach($publicadores as $p)
                                <option value="{{ $p->id }}" {{ $parte->ayudante_id == $p->id ? 'selected' : '' }}>{{ $p->nombre_completo }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <input type="hidden" name="partes[{{ $parte->id }}][titulo]" value="{{ $parte->titulo }}">
                <input type="hidden" name="partes[{{ $parte->id }}][duracion_minutos]" value="{{ $parte->duracion_minutos }}">
            </div>
            @endforeach
        </div>

        {{-- NUESTRA VIDA CRISTIANA --}}
        <div class="reunion-seccion reunion-seccion-vida">
            <h3 class="reunion-seccion-titulo">Nuestra vida cristiana</h3>
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
                        <select name="partes[{{ $parte->id }}][publicador_id]" class="form-input">
                            <option value="">-- Sin asignar --</option>
                            @foreach($publicadores->filter(fn($p) => $p->genero === 'M' && ($p->es_anciano || $p->es_siervo_ministerial)) as $p)
                                <option value="{{ $p->id }}" {{ $parte->publicador_id == $p->id ? 'selected' : '' }}>{{ $p->nombre_completo }}</option>
                            @endforeach
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

        <div class="flex gap-1 mt-2">
            <button type="submit" class="btn btn-teal">Guardar cambios</button>
            @if($programa->estado === 'borrador')
            <form action="{{ route('reuniones.publicar', $programa) }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-secondary" onclick="return confirm('Publicar este programa?')">Publicar</button>
            </form>
            @endif
            <form action="{{ route('reuniones.destroy', $programa) }}" method="POST" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-ghost" style="color:#ef4444;" onclick="return confirm('Eliminar este programa? Esta accion no se puede deshacer.')">Eliminar</button>
            </form>
        </div>
    </form>
</div>

<!-- Modal de recomendaciones -->
<div id="modal-recomendar" class="modal-backdrop" style="display:none;" onclick="if(event.target===this)cerrarRecomendar()">
    <div class="modal" style="max-width:450px;">
        <div class="modal-header">
            <h3 class="modal-title" id="modal-recomendar-titulo">Recomendaciones</h3>
            <button class="modal-close" onclick="cerrarRecomendar()">&times;</button>
        </div>
        <div class="modal-body" id="modal-recomendar-body">
            <p class="text-muted">Cargando...</p>
        </div>
    </div>
</div>

<script>
let recomendarSelectActual = null;

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
</script>

<script>
async function importarDesdeJw() {
    const btn = document.getElementById('btn-importar-jw');
    const fecha = new Date('{{ $programa->fecha_semana->format("Y-m-d") }}');
    const url = `https://wol.jw.org/es/wol/dt/r4/lp-s/${fecha.getFullYear()}/${fecha.getMonth()+1}/${fecha.getDate()}`;

    if (!confirm('Importar titulos de jw.org? Se reemplazaran las partes actuales.')) return;

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
        if (partes.length === 0) throw new Error('No se encontraron partes en el programa');

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
        btn.textContent = 'Importar de jw.org';
        btn.disabled = false;
    }
}

function parsearProgramaVym(html) {
    const parser = new DOMParser();
    const doc = parser.parseFromString(html, 'text/html');
    const partes = [];

    // Buscar todos los elementos con ids de partes (p1, p2, etc.)
    // El programa VyM en wol.jw.org usa clases especificas para secciones
    const body = doc.body.textContent || doc.body.innerText;
    const lines = body.split('\n').map(l => l.trim()).filter(l => l.length > 0);

    let seccion = null;

    for (const line of lines) {
        // Detectar secciones
        if (/TESOROS DE LA BIBLIA/i.test(line)) { seccion = 'tesoros'; continue; }
        if (/SEAMOS MEJORES MAESTROS/i.test(line)) { seccion = 'maestros'; continue; }
        if (/NUESTRA VIDA CRISTIANA/i.test(line)) { seccion = 'vida_cristiana'; continue; }
        if (!seccion) continue;

        // Detectar partes: "N. Titulo (X min.)" o "Titulo (X min.)"
        const match = line.match(/^(?:\d+\.\s*)?(.+?)\s*\((\d+)\s*min/i);
        if (!match) continue;

        const titulo = match[1].trim();
        const duracion = parseInt(match[2]);

        // Ignorar canticos, oraciones, conclusiones
        if (/^(Canci[oó]n|Song|Oraci[oó]n|Palabras de conclus)/i.test(titulo)) continue;
        if (/^Comentarios? iniciales?/i.test(titulo)) continue;

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
        // Parte generica de maestros - clasificar por duracion
        if (duracion <= 3) return 'empiece_conversaciones';
        if (duracion <= 4) return 'haga_revisitas';
        return 'haga_discipulos';
    }

    if (seccion === 'vida_cristiana') {
        if (/estudio b[ií]blico de la congregaci/i.test(titulo)) return null;
        return 'discurso_vida';
    }

    return null;
}
</script>

@endsection
