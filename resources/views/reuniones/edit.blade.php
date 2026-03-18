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
                    <label class="form-label">Presidente</label>
                    <select name="presidente_id" class="form-input">
                        <option value="">-- Sin asignar --</option>
                        @foreach($publicadores->where('es_anciano', true) as $p)
                            <option value="{{ $p->id }}" {{ $programa->presidente_id == $p->id ? 'selected' : '' }}>{{ $p->nombre_completo }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Oracion de inicio</label>
                    <select name="oracion_inicio_id" class="form-input">
                        <option value="">-- Sin asignar --</option>
                        @foreach($publicadores->where('genero', 'M') as $p)
                            <option value="{{ $p->id }}" {{ $programa->oracion_inicio_id == $p->id ? 'selected' : '' }}>{{ $p->nombre_completo }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Conductor estudio biblico</label>
                    <select name="conductor_estudio_id" class="form-input">
                        <option value="">-- Sin asignar --</option>
                        @foreach($publicadores->where('es_anciano', true) as $p)
                            <option value="{{ $p->id }}" {{ $programa->conductor_estudio_id == $p->id ? 'selected' : '' }}>{{ $p->nombre_completo }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Lector estudio biblico</label>
                    <select name="lector_estudio_id" class="form-input">
                        <option value="">-- Sin asignar --</option>
                        @foreach($publicadores->where('genero', 'M') as $p)
                            <option value="{{ $p->id }}" {{ $programa->lector_estudio_id == $p->id ? 'selected' : '' }}>{{ $p->nombre_completo }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Oracion final</label>
                    <select name="oracion_final_id" class="form-input">
                        <option value="">-- Sin asignar --</option>
                        @foreach($publicadores->where('genero', 'M') as $p)
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

@endsection
