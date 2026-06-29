@extends('layouts.app')

@section('title', $publicador->nombre_completo . ' - Publicadores')

@section('content')
<nav class="page-nav">
    <div class="page-breadcrumbs">
        <a href="{{ route('dashboard') }}" class="breadcrumb-link">Inicio</a>
        <span class="breadcrumb-separator">/</span>
        <a href="{{ route('administracion') }}" class="breadcrumb-link">Administracion</a>
        <span class="breadcrumb-separator">/</span>
        <a href="{{ route('publicadores.index') }}" class="breadcrumb-link">Publicadores</a>
        <span class="breadcrumb-separator">/</span>
        <span class="breadcrumb-current">{{ $publicador->nombre_completo }}</span>
    </div>
</nav>

<!-- Header del publicador -->
<div class="pub-profile-header">
    <div class="pub-avatar-large">
        {{ strtoupper(substr($publicador->nombre, 0, 1)) }}{{ strtoupper(substr($publicador->apellidos ?? '', 0, 1)) }}
    </div>
    <div class="pub-header-info">
        <h1>{{ $publicador->nombre_completo }}</h1>
        <div class="pub-badges">
            @if($publicador->activo)
                <span class="badge badge-active">Activo</span>
            @else
                <span class="badge badge-inactive">Inactivo</span>
            @endif
            @if($publicador->es_anciano)
                <span class="badge badge-anciano">Anciano</span>
            @endif
            @if($publicador->es_siervo_ministerial)
                <span class="badge badge-sm">Siervo Ministerial</span>
            @endif
            @foreach($publicador->nombramientos_cuerpo_activos as $etiqueta)
                <span class="badge badge-nombramiento">{{ $etiqueta }}</span>
            @endforeach
            @foreach($publicador->cargos_sm_activos as $etiqueta)
                <span class="badge badge-cargo-sm">{{ $etiqueta }}</span>
            @endforeach
            @if($publicador->es_menor)
                <span class="badge badge-menor">Menor</span>
            @endif
            @if($publicador->es_precursor)
                <span class="badge badge-precursor">Precursor</span>
            @endif
            @if($publicador->es_superintendente)
                <span class="badge badge-sup">Superintendente</span>
            @endif
            @if($publicador->es_auxiliar)
                <span class="badge badge-aux">Auxiliar</span>
            @endif
            @if($publicador->aprobado_ppoc)
                <span class="badge badge-ppoc">PPOC</span>
            @endif
        </div>
        @if($publicador->telefono)
        <div class="pub-contact">{{ $publicador->telefono }}</div>
        @endif
    </div>
    <div class="pub-header-actions">
        @if(Auth::user()->canEditPublicadores())
        <button onclick="toggleEditMode()" class="btn-action btn-edit">Editar</button>
        @endif
        <a href="{{ route('publicadores.registros', $publicador) }}" class="btn-action btn-history">Historial Completo</a>
    </div>
</div>

<!-- Estadisticas principales -->
<div class="stats-section">
    <h2 class="section-title">Estadisticas</h2>
    <div class="stats-grid-4">
        <div class="stat-box">
            <div class="stat-icon stat-icon-total">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                    <circle cx="12" cy="10" r="3"></circle>
                </svg>
            </div>
            <div class="stat-value">{{ $estadisticas['total'] }}</div>
            <div class="stat-label">Territorios Totales</div>
        </div>
        <div class="stat-box">
            <div class="stat-icon stat-icon-active">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
            </div>
            <div class="stat-value">{{ $estadisticas['activos'] }}</div>
            <div class="stat-label">Activos Ahora</div>
        </div>
        <div class="stat-box">
            <div class="stat-icon stat-icon-completed">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
            </div>
            <div class="stat-value">{{ $estadisticas['completados'] }}</div>
            <div class="stat-label">Completados</div>
        </div>
        <div class="stat-box">
            <div class="stat-icon stat-icon-days">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
            </div>
            <div class="stat-value">{{ $estadisticas['promedio_dias'] }}</div>
            <div class="stat-label">Dias Promedio</div>
        </div>
    </div>
</div>

<!-- Territorio actual -->
@if($publicador->territorio_actual)
<div class="current-territory-section">
    <h2 class="section-title">Territorio Actual</h2>
    <div class="current-territory-card">
        <div class="territory-number-large">
            @php
                $territorio = $publicador->territorio_actual;
                $prefijo = '';
                if ($territorio->tipo === 'campana') $prefijo = 'C-';
                elseif ($territorio->tipo === 'negocios') $prefijo = 'N-';
            @endphp
            {{ $prefijo }}{{ $territorio->numero }}
        </div>
        <div class="territory-details">
            <div class="territory-name">{{ $territorio->nombre }}</div>
            @php
                $estado = $territorio->calcularEstado();
                $registro = $publicador->ultimoRegistroActivo();
                $dias = $registro ? $registro->fecha_salida->diffInDays(now()) : 0;
            @endphp
            <div class="territory-meta">
                <span class="estado-badge estado-{{ $estado }}">{{ ucfirst($estado) }}</span>
                <span class="dias-badge">{{ $dias }} dias</span>
            </div>
        </div>
        <div class="territory-actions">
            <a href="{{ route('registros.show', $registro->id ?? 0) }}" class="btn-sm btn-primary">Ver Registro</a>
        </div>
    </div>
</div>
@else
<div class="no-territory-section">
    <div class="no-territory-card">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="no-territory-icon">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="8" x2="12" y2="12"></line>
            <line x1="12" y1="16" x2="12.01" y2="16"></line>
        </svg>
        <div class="no-territory-text">Sin territorio asignado actualmente</div>
        <div class="no-territory-subtext">Disponible para nueva asignacion</div>
    </div>
</div>
@endif

<!-- Ano de servicio actual -->
<div class="service-year-section">
    <h2 class="section-title">Ano de Servicio {{ $anoServicio }}</h2>
    <div class="stats-grid-3">
        <div class="stat-box-sm">
            <div class="stat-value-sm">{{ $estadisticasAno['territorios'] }}</div>
            <div class="stat-label-sm">Territorios</div>
        </div>
        <div class="stat-box-sm">
            <div class="stat-value-sm">{{ $estadisticasAno['completados'] }}</div>
            <div class="stat-label-sm">Completados</div>
        </div>
        <div class="stat-box-sm">
            <div class="stat-value-sm">{{ $estadisticasAno['dias_servicio'] }}</div>
            <div class="stat-label-sm">Dias Predicando</div>
        </div>
    </div>
</div>


<!-- Seccion Familia -->
<div class="family-section">
    <div class="section-header-with-action">
        <h2 class="section-title">Familia</h2>
        @if(Auth::user()->canEditPublicadores())
        <button onclick="openFamilyModal()" class="btn-action-sm btn-add-family">+ Gestionar</button>
        @endif
    </div>
    <div id="familyList" class="family-list">
        <div class="loading-family">Cargando...</div>
    </div>
</div>

<!-- Modal Familia -->
<div id="familyModal" class="modal-overlay" style="display: none;">
    <div class="modal-content modal-family">
        <div class="modal-header">
            <h3>Gestionar Familia</h3>
            <button onclick="closeFamilyModal()" class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <div class="modal-section">
                <h4>Familiares Actuales</h4>
                <div id="currentFamily" class="current-family-list">
                    <div class="loading-family">Cargando...</div>
                </div>
            </div>
            <div class="modal-section">
                <h4>Agregar Familiar</h4>
                <div class="add-family-form">
                    <div class="form-row-family">
                        <div class="form-group">
                            <label for="familiarSelect">Publicador</label>
                            <select id="familiarSelect" class="family-select">
                                <option value="">Seleccionar...</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="tipoRelacion">Relacion</label>
                            <select id="tipoRelacion" class="family-select">
                                <option value="conyuge">Conyuge</option>
                                <option value="progenitor">Padre/Madre</option>
                                <option value="hijo">Hijo/a</option>
                            </select>
                        </div>
                    </div>
                    <button onclick="addFamiliar()" class="btn btn-primary btn-add">Agregar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Ultimos registros -->
@if($ultimosRegistros->count() > 0)
<div class="recent-section">
    <h2 class="section-title">Ultimos Registros</h2>
    <div class="recent-list">
        @foreach($ultimosRegistros as $registro)
        <div class="recent-item">
            <div class="recent-territory">
                @php
                    $prefijo = '';
                    if ($registro->territorio->tipo === 'campana') $prefijo = 'C-';
                    elseif ($registro->territorio->tipo === 'negocios') $prefijo = 'N-';
                @endphp
                <span class="recent-number">{{ $prefijo }}{{ $registro->territorio->numero }}</span>
                <span class="recent-name">{{ $registro->territorio->nombre }}</span>
            </div>
            <div class="recent-dates">
                <span class="date-out">{{ $registro->fecha_salida->format('d/m/Y') }}</span>
                <span class="date-arrow">→</span>
                <span class="date-in {{ $registro->fecha_entrada ? '' : 'date-active' }}">
                    {{ $registro->fecha_entrada ? $registro->fecha_entrada->format('d/m/Y') : 'En curso' }}
                </span>
            </div>
            <div class="recent-days">
                @php
                    $diasReg = $registro->fecha_entrada
                        ? $registro->fecha_salida->diffInDays($registro->fecha_entrada)
                        : $registro->fecha_salida->diffInDays(now());
                @endphp
                {{ $diasReg }} dias
            </div>
        </div>
        @endforeach
    </div>
    <a href="{{ route('publicadores.registros', $publicador) }}" class="view-all-link">Ver todos los registros →</a>
</div>
@endif

<!-- Formulario de edicion oculto -->
@if(Auth::user()->canEditPublicadores())
<div id="editForm" class="edit-form-section" style="display: none;">
    <h2 class="section-title">Editar Publicador</h2>
    <form method="POST" action="{{ route('publicadores.update', $publicador) }}" class="edit-form">
        @csrf
        @method('PUT')

        <div class="form-row">
            <div class="form-group">
                <label for="nombre">Nombre</label>
                <input type="text" id="nombre" name="nombre" value="{{ old('nombre', $publicador->nombre) }}" required>
            </div>
            <div class="form-group">
                <label for="apellidos">Apellidos</label>
                <input type="text" id="apellidos" name="apellidos" value="{{ old('apellidos', $publicador->apellidos) }}" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="genero">Genero</label>
                <select id="genero" name="genero">
                    <option value="">-- Seleccionar --</option>
                    <option value="M" {{ $publicador->genero == 'M' ? 'selected' : '' }}>Hermano</option>
                    <option value="F" {{ $publicador->genero == 'F' ? 'selected' : '' }}>Hermana</option>
                </select>
            </div>
            <div class="form-group">
                <label for="telefono">Telefono</label>
                <input type="text" id="telefono" name="telefono" value="{{ old('telefono', $publicador->telefono) }}">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="grupo_predicacion_id">Grupo de Predicacion</label>
                <select id="grupo_predicacion_id" name="grupo_predicacion_id">
                    <option value="">Sin grupo</option>
                    @foreach($grupos ?? [] as $grupo)
                        <option value="{{ $grupo->id }}" {{ $publicador->grupo_predicacion_id == $grupo->id ? 'selected' : '' }}>
                            Grupo {{ $grupo->numero }} - {{ $grupo->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="notas">Notas</label>
            <textarea id="notas" name="notas" rows="3">{{ old('notas', $publicador->notas) }}</textarea>
        </div>

        <div class="form-checkboxes-section">
            <label class="form-section-label">Estado</label>
            <div class="form-checkboxes">
                <label class="checkbox-label">
                    <input type="checkbox" name="activo" value="1" {{ $publicador->activo ? 'checked' : '' }}>
                    <span>Activo</span>
                </label>
                <label class="checkbox-label checkbox-menor">
                    <input type="checkbox" name="es_menor" value="1" {{ $publicador->es_menor ? 'checked' : '' }}>
                    <span>Menor de edad</span>
                </label>
            </div>
        </div>

        <div class="form-checkboxes-section">
            <label class="form-section-label">Nombramientos</label>
            <div class="form-checkboxes">
                <label class="checkbox-label checkbox-anciano">
                    <input type="checkbox" name="es_anciano" id="edit-anciano" value="1" {{ $publicador->es_anciano ? 'checked' : '' }} onchange="onEditAncianoSm(this, 'sm')">
                    <span>Anciano</span>
                </label>
                <label class="checkbox-label checkbox-sm">
                    <input type="checkbox" name="es_siervo_ministerial" id="edit-sm" value="1" {{ $publicador->es_siervo_ministerial ? 'checked' : '' }} onchange="onEditAncianoSm(this, 'anciano')">
                    <span>Siervo Ministerial</span>
                </label>
            </div>
        </div>

        <div id="edit-bloque-nombramientos" class="form-checkboxes-section nombramientos-cuerpo-section" style="{{ $publicador->es_anciano ? '' : 'display:none' }}">
            <label class="form-section-label">Nombramientos del cuerpo</label>
            <div class="form-checkboxes">
                @foreach(\App\Models\Publicador::NOMBRAMIENTOS_CUERPO as $campo => $etiqueta)
                    <label class="checkbox-label checkbox-nombramiento">
                        <input type="checkbox" name="{{ $campo }}" value="1" {{ $publicador->{$campo} ? 'checked' : '' }}>
                        <span>{{ $etiqueta }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div id="edit-bloque-cargos-sm" class="form-checkboxes-section cargos-sm-edit-section" style="{{ $publicador->es_siervo_ministerial ? '' : 'display:none' }}">
            <label class="form-section-label">Cargos del siervo ministerial</label>
            <table class="cargos-sm-tabla-edit">
                <thead>
                    <tr><th>Categoría</th><th>Titular</th><th>Auxiliar</th></tr>
                </thead>
                <tbody>
                    @foreach(\App\Models\Publicador::CARGOS_SM_CATEGORIAS as $cat)
                        <tr>
                            <td>{{ $cat['etiqueta'] }}</td>
                            <td><input type="checkbox" name="{{ $cat['titular'] }}" value="1" {{ $publicador->{$cat['titular']} ? 'checked' : '' }}></td>
                            <td><input type="checkbox" name="{{ $cat['aux'] }}" value="1" {{ $publicador->{$cat['aux']} ? 'checked' : '' }}></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <p class="cargos-sm-hint-edit">Solo un publicador puede ser titular de cada categoría — al guardar se le retirará al anterior.</p>
        </div>

        <script>
        function onEditAncianoSm(elem, otro) {
            const anciano = document.getElementById('edit-anciano');
            const sm = document.getElementById('edit-sm');
            const bloqueAnc = document.getElementById('edit-bloque-nombramientos');
            const bloqueSm = document.getElementById('edit-bloque-cargos-sm');
            if (elem.checked) {
                if (otro === 'sm') sm.checked = false;
                else if (otro === 'anciano') anciano.checked = false;
            }
            if (anciano.checked) {
                bloqueAnc.style.display = '';
            } else {
                bloqueAnc.style.display = 'none';
                bloqueAnc.querySelectorAll('input[type=checkbox]').forEach(cb => cb.checked = false);
            }
            if (sm.checked) {
                bloqueSm.style.display = '';
            } else {
                bloqueSm.style.display = 'none';
                bloqueSm.querySelectorAll('input[type=checkbox]').forEach(cb => cb.checked = false);
            }
        }
        </script>

        <div class="form-checkboxes-section">
            <label class="form-section-label">Privilegios</label>
            <div class="form-checkboxes">
                <label class="checkbox-label">
                    <input type="checkbox" name="es_precursor" value="1" {{ $publicador->es_precursor ? 'checked' : '' }}>
                    <span>Precursor</span>
                </label>
                <label class="checkbox-label">
                    <input type="checkbox" name="aprobado_ppoc" value="1" {{ $publicador->aprobado_ppoc ? 'checked' : '' }}>
                    <span>Aprobado PPOC</span>
                </label>
            </div>
        </div>

        <div class="form-checkboxes-section">
            <label class="form-section-label">Reuniones</label>
            <div class="form-checkboxes">
                <label class="checkbox-label">
                    <input type="checkbox" name="puede_dirigir_estudio" value="1" {{ $publicador->puede_dirigir_estudio ? 'checked' : '' }}>
                    <span>Puede dirigir estudio</span>
                </label>
                <label class="checkbox-label">
                    <input type="checkbox" name="puede_leer_estudio" value="1" {{ $publicador->puede_leer_estudio ? 'checked' : '' }}>
                    <span>Puede leer estudio</span>
                </label>
                <label class="checkbox-label">
                    <input type="checkbox" name="excluido_reuniones" value="1" {{ $publicador->excluido_reuniones ? 'checked' : '' }}>
                    <span>Excluido de reuniones</span>
                </label>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            <button type="button" onclick="toggleEditMode()" class="btn btn-secondary">Cancelar</button>
        </div>
    </form>
</div>

<!-- Zona de peligro -->
<div class="danger-zone">
    <h3>Zona de Peligro</h3>
    <p>Eliminar este publicador borrara todos sus registros. Esta accion no se puede deshacer.</p>
    <form method="POST" action="{{ route('publicadores.destroy', $publicador) }}" onsubmit="return confirm('¿Estas seguro de eliminar este publicador?')">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger">Eliminar Publicador</button>
    </form>
</div>
@endif

<script>
function toggleEditMode() {
    const form = document.getElementById('editForm');
    if (form.style.display === 'none') {
        form.style.display = 'block';
        form.scrollIntoView({ behavior: 'smooth' });
    } else {
        form.style.display = 'none';
    }
}
</script>

<style>
/* Header del publicador */
.pub-profile-header {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    padding: 1.5rem;
    background: linear-gradient(135deg, #4a6da7 0%, #3d5a8a 100%);
    border-radius: 16px;
    margin-bottom: 1.5rem;
    box-shadow: 0 10px 40px rgba(79, 70, 229, 0.3);
}

[data-theme="dark"] .pub-profile-header {
    background: linear-gradient(135deg, #4a6da7 0%, #3d5a8a 100%);
    box-shadow: 0 10px 40px rgba(249, 115, 22, 0.2);
}

.pub-avatar-large {
    width: 80px;
    height: 80px;
    background: rgba(255,255,255,0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
    font-weight: 700;
    color: white;
    flex-shrink: 0;
}

.pub-header-info {
    flex: 1;
}

.pub-header-info h1 {
    margin: 0 0 0.5rem 0;
    color: white;
    font-size: 1.5rem;
}

.pub-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
}

.badge {
    padding: 0.25rem 0.6rem;
    border-radius: 20px;
    font-size: 0.7rem;
    font-weight: 600;
}

.badge-active { background: rgba(34,197,94,0.9); color: white; }
.badge-inactive { background: rgba(239,68,68,0.9); color: white; }
.badge-anciano { background: rgba(220,38,38,0.9); color: white; }
.badge-sm { background: rgba(59,130,246,0.9); color: white; }
.badge-menor { background: rgba(168,85,247,0.9); color: white; }
.badge-precursor { background: rgba(255,255,255,0.25); color: white; }
.badge-sup { background: rgba(139,92,246,0.9); color: white; }
.badge-aux { background: rgba(6,182,212,0.9); color: white; }
.badge-ppoc { background: rgba(245,158,11,0.9); color: white; }
.badge-nombramiento { background: rgba(168,85,247,0.85); color: white; }
.badge-cargo-sm { background: rgba(59,130,246,0.85); color: white; }

.nombramientos-cuerpo-section {
    background: rgba(168,85,247,0.06);
    border: 1px solid rgba(168,85,247,0.18);
    border-radius: 8px;
    padding: 0.75rem 1rem;
    margin-top: 0.75rem;
}
.checkbox-nombramiento input:checked + span { color: #c98ee3; font-weight: 600; }

.cargos-sm-edit-section {
    background: rgba(59,130,246,0.06);
    border: 1px solid rgba(59,130,246,0.18);
    border-radius: 8px;
    padding: 0.75rem 1rem;
    margin-top: 0.75rem;
}
.cargos-sm-tabla-edit {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.8125rem;
    margin-top: 0.5rem;
}
.cargos-sm-tabla-edit th, .cargos-sm-tabla-edit td {
    padding: 0.375rem 0.5rem;
    border-bottom: 1px solid rgba(255,255,255,0.04);
    text-align: left;
}
.cargos-sm-tabla-edit th {
    color: rgba(241,243,245,0.55);
    font-weight: 500;
    font-size: 0.6875rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}
.cargos-sm-tabla-edit th:nth-child(2),
.cargos-sm-tabla-edit th:nth-child(3),
.cargos-sm-tabla-edit td:nth-child(2),
.cargos-sm-tabla-edit td:nth-child(3) {
    text-align: center;
    width: 80px;
}
.cargos-sm-hint-edit {
    font-size: 0.6875rem;
    color: rgba(241,243,245,0.5);
    margin: 0.5rem 0 0 0;
    font-style: italic;
}

.pub-contact {
    color: rgba(255,255,255,0.9);
    font-size: 0.9rem;
}

.pub-header-actions {
    display: flex;
    gap: 0.75rem;
}

.btn-action {
    padding: 0.6rem 1.25rem;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.85rem;
    text-decoration: none;
    transition: all 0.2s;
    border: none;
    cursor: pointer;
}

.btn-edit {
    background: rgba(255,255,255,0.2);
    color: white;
}

.btn-edit:hover {
    background: rgba(255,255,255,0.3);
}

.btn-history {
    background: white;
    color: #4a6da7;
}

[data-theme="dark"] .btn-history {
    color: #4a6da7;
}

.btn-history:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

/* Section titles */
.section-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--text-primary, #111827);
    margin: 0 0 1rem 0;
}

[data-theme="dark"] .section-title {
    color: #f5f5f5;
}

/* Stats section */
.stats-section {
    margin-bottom: 1.5rem;
}

.stats-grid-4 {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
}

.stats-grid-3 {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
}

.stat-box {
    background: var(--card-bg, #fff);
    border: 1px solid var(--card-border, #e5e7eb);
    border-radius: 12px;
    padding: 1.25rem;
    text-align: center;
    transition: transform 0.2s, box-shadow 0.2s;
}

[data-theme="dark"] .stat-box {
    background: #1a1a1a;
    border-color: #2d2d2d;
}

.stat-box:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
}

.stat-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 0.75rem;
}

.stat-icon svg {
    width: 20px;
    height: 20px;
}

.stat-icon-total { background: rgba(79,70,229,0.1); color: #4a6da7; }
.stat-icon-active { background: rgba(245,158,11,0.1); color: #4a6da7; }
.stat-icon-completed { background: rgba(34,197,94,0.1); color: #4a6da7; }
.stat-icon-days { background: rgba(59,130,246,0.1); color: #4a6da7; }

[data-theme="dark"] .stat-icon-total { background: rgba(249,115,22,0.15); color: #4a6da7; }
[data-theme="dark"] .stat-icon-active { background: rgba(251,191,36,0.15); color: #fbbf24; }
[data-theme="dark"] .stat-icon-completed { background: rgba(34,197,94,0.15); color: #4a6da7; }
[data-theme="dark"] .stat-icon-days { background: rgba(59,130,246,0.15); color: #5c7fb8; }

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: var(--text-primary, #111827);
    line-height: 1;
    margin-bottom: 0.25rem;
}

[data-theme="dark"] .stat-value {
    color: #f5f5f5;
}

.stat-label {
    font-size: 0.75rem;
    color: var(--text-secondary, #6b7280);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

[data-theme="dark"] .stat-label {
    color: #a3a3a3;
}

/* Small stat boxes */
.stat-box-sm {
    background: var(--card-bg, #fff);
    border: 1px solid var(--card-border, #e5e7eb);
    border-radius: 10px;
    padding: 1rem;
    text-align: center;
}

[data-theme="dark"] .stat-box-sm {
    background: #1a1a1a;
    border-color: #2d2d2d;
}

.stat-value-sm {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text-primary, #111827);
}

[data-theme="dark"] .stat-value-sm {
    color: #f5f5f5;
}

.stat-label-sm {
    font-size: 0.7rem;
    color: var(--text-secondary, #6b7280);
    text-transform: uppercase;
}

[data-theme="dark"] .stat-label-sm {
    color: #a3a3a3;
}

/* Current territory */
.current-territory-section {
    margin-bottom: 1.5rem;
}

.current-territory-card {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    padding: 1.25rem;
    background: linear-gradient(135deg, rgba(34,197,94,0.1) 0%, rgba(34,197,94,0.05) 100%);
    border: 1px solid rgba(34,197,94,0.3);
    border-radius: 12px;
}

[data-theme="dark"] .current-territory-card {
    background: linear-gradient(135deg, rgba(34,197,94,0.15) 0%, rgba(34,197,94,0.05) 100%);
    border-color: rgba(34,197,94,0.3);
}

.territory-number-large {
    font-size: 2rem;
    font-weight: 700;
    color: #166534;
    min-width: 80px;
    text-align: center;
}

[data-theme="dark"] .territory-number-large {
    color: #8aa8d6;
}

.territory-details {
    flex: 1;
}

.territory-name {
    font-weight: 600;
    color: var(--text-primary, #111827);
    margin-bottom: 0.5rem;
}

[data-theme="dark"] .territory-name {
    color: #f5f5f5;
}

.territory-meta {
    display: flex;
    gap: 0.5rem;
}

.estado-badge {
    padding: 0.2rem 0.5rem;
    border-radius: 6px;
    font-size: 0.7rem;
    font-weight: 600;
}

.estado-activo { background: #e8eef6; color: #2d4266; }
.estado-libre { background: #dcfce7; color: #166534; }
.estado-atrasado { background: #fef3c7; color: #92400e; }
.estado-archivo { background: #e9ecef; color: #212529; }

[data-theme="dark"] .estado-activo { background: rgba(59,130,246,0.2); color: #8aa8d6; }
[data-theme="dark"] .estado-libre { background: rgba(34,197,94,0.2); color: #8aa8d6; }
[data-theme="dark"] .estado-atrasado { background: rgba(245,158,11,0.2); color: #fcd34d; }
[data-theme="dark"] .estado-archivo { background: rgba(239,68,68,0.2); color: #ced4da; }

.dias-badge {
    padding: 0.2rem 0.5rem;
    background: rgba(107,114,128,0.1);
    border-radius: 6px;
    font-size: 0.7rem;
    color: var(--text-secondary, #6b7280);
}

[data-theme="dark"] .dias-badge {
    background: rgba(255,255,255,0.1);
    color: #a3a3a3;
}

.territory-actions .btn-sm {
    padding: 0.4rem 0.8rem;
    font-size: 0.8rem;
    border-radius: 6px;
    text-decoration: none;
    background: #4a6da7;
    color: white;
}

/* No territory */
.no-territory-section {
    margin-bottom: 1.5rem;
}

.no-territory-card {
    padding: 2rem;
    text-align: center;
    background: var(--card-bg, #fff);
    border: 2px dashed var(--card-border, #e5e7eb);
    border-radius: 12px;
}

[data-theme="dark"] .no-territory-card {
    background: #1a1a1a;
    border-color: #2d2d2d;
}

.no-territory-icon {
    width: 48px;
    height: 48px;
    color: var(--text-muted, #9ca3af);
    margin-bottom: 0.75rem;
}

.no-territory-text {
    font-weight: 600;
    color: var(--text-primary, #111827);
    margin-bottom: 0.25rem;
}

[data-theme="dark"] .no-territory-text {
    color: #f5f5f5;
}

.no-territory-subtext {
    font-size: 0.85rem;
    color: var(--text-secondary, #6b7280);
}

[data-theme="dark"] .no-territory-subtext {
    color: #a3a3a3;
}

/* Service year section */
.service-year-section {
    margin-bottom: 1.5rem;
}

/* Recent registros */
.recent-section {
    margin-bottom: 1.5rem;
}

.recent-list {
    background: var(--card-bg, #fff);
    border: 1px solid var(--card-border, #e5e7eb);
    border-radius: 12px;
    overflow: hidden;
}

[data-theme="dark"] .recent-list {
    background: #1a1a1a;
    border-color: #2d2d2d;
}

.recent-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.85rem 1rem;
    border-bottom: 1px solid var(--card-border, #e5e7eb);
}

[data-theme="dark"] .recent-item {
    border-bottom-color: #2d2d2d;
}

.recent-item:last-child {
    border-bottom: none;
}

.recent-territory {
    flex: 1;
    min-width: 0;
}

.recent-number {
    font-weight: 700;
    color: #4a6da7;
    margin-right: 0.5rem;
}

[data-theme="dark"] .recent-number {
    color: #4a6da7;
}

.recent-name {
    color: var(--text-secondary, #6b7280);
    font-size: 0.85rem;
}

[data-theme="dark"] .recent-name {
    color: #a3a3a3;
}

.recent-dates {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.8rem;
}

.date-out, .date-in {
    color: var(--text-secondary, #6b7280);
}

[data-theme="dark"] .date-out,
[data-theme="dark"] .date-in {
    color: #a3a3a3;
}

.date-arrow {
    color: var(--text-muted, #9ca3af);
}

.date-active {
    color: #4a6da7 !important;
    font-weight: 600;
}

.recent-days {
    font-size: 0.8rem;
    color: var(--text-muted, #9ca3af);
    min-width: 60px;
    text-align: right;
}

.view-all-link {
    display: block;
    text-align: center;
    padding: 0.75rem;
    color: #4a6da7;
    text-decoration: none;
    font-weight: 500;
    font-size: 0.9rem;
}

[data-theme="dark"] .view-all-link {
    color: #4a6da7;
}

.view-all-link:hover {
    text-decoration: underline;
}

/* Edit form */
.edit-form-section {
    background: var(--card-bg, #fff);
    border: 1px solid var(--card-border, #e5e7eb);
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

[data-theme="dark"] .edit-form-section {
    background: #1a1a1a;
    border-color: #2d2d2d;
}

.form-row {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
    margin-bottom: 1rem;
}

.form-group {
    margin-bottom: 1rem;
}

.form-group label {
    display: block;
    font-weight: 500;
    margin-bottom: 0.5rem;
    color: var(--text-primary, #111827);
    font-size: 0.9rem;
}

[data-theme="dark"] .form-group label {
    color: #f5f5f5;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 0.6rem 0.75rem;
    border: 1px solid var(--card-border, #e5e7eb);
    border-radius: 8px;
    font-size: 0.9rem;
    background: var(--card-bg, #fff);
    color: var(--text-primary, #111827);
}

[data-theme="dark"] .form-group input,
[data-theme="dark"] .form-group select,
[data-theme="dark"] .form-group textarea {
    background: #262626;
    border-color: #404040;
    color: #f5f5f5;
}

.form-checkboxes-section {
    margin-bottom: 1.25rem;
    padding: 1rem;
    background: var(--bg-secondary, #f9fafb);
    border-radius: 8px;
}

[data-theme="dark"] .form-checkboxes-section {
    background: #262626;
}

.form-section-label {
    display: block;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--text-secondary, #6b7280);
    margin-bottom: 0.75rem;
}

[data-theme="dark"] .form-section-label {
    color: #a3a3a3;
}

.form-checkboxes {
    display: flex;
    flex-wrap: wrap;
    gap: 1.5rem;
}

.checkbox-anciano input:checked + span {
    color: #343a40;
    font-weight: 600;
}

.checkbox-sm input:checked + span {
    color: #4a6da7;
    font-weight: 600;
}

.checkbox-menor input:checked + span {
    color: #a855f7;
    font-weight: 600;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    font-size: 0.9rem;
    color: var(--text-primary, #111827);
}

[data-theme="dark"] .checkbox-label {
    color: #f5f5f5;
}

.form-actions {
    display: flex;
    gap: 0.75rem;
}

/* Danger zone */
.danger-zone {
    background: rgba(239,68,68,0.05);
    border: 1px solid rgba(239,68,68,0.3);
    border-radius: 12px;
    padding: 1.25rem;
    margin-top: 1.5rem;
}

.danger-zone h3 {
    color: #343a40;
    margin: 0 0 0.5rem 0;
    font-size: 1rem;
}

.danger-zone p {
    color: var(--text-secondary, #6b7280);
    font-size: 0.85rem;
    margin: 0 0 1rem 0;
}

.btn-danger {
    background: #343a40;
    color: white;
    border: none;
    padding: 0.6rem 1rem;
    border-radius: 8px;
    font-weight: 500;
    cursor: pointer;
}

.btn-danger:hover {
    background: #212529;
}

/* Responsive */
@media (max-width: 768px) {
    .pub-profile-header {
        flex-direction: column;
        text-align: center;
    }

    .pub-badges {
        justify-content: center;
    }

    .pub-header-actions {
        width: 100%;
        justify-content: center;
    }

    .stats-grid-4 {
        grid-template-columns: repeat(2, 1fr);
    }

    .stats-grid-3 {
        grid-template-columns: 1fr;
    }

    .current-territory-card {
        flex-direction: column;
        text-align: center;
    }

    .form-row {
        grid-template-columns: 1fr;
    }

    .form-checkboxes {
        flex-direction: column;
        gap: 0.75rem;
    }
}

/* ===== FAMILY SECTION STYLES ===== */
.family-section {
    margin-top: 2rem;
    padding: 1.5rem;
    background: var(--card-bg, #fff);
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.section-header-with-action {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.family-list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.family-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 1rem;
    background: var(--bg-secondary, #f8fafc);
    border-radius: 8px;
    text-decoration: none;
    color: inherit;
    transition: background 0.2s, transform 0.2s;
}

.family-item:hover {
    background: var(--bg-hover, #f1f5f9);
    transform: translateX(4px);
}

.family-name {
    font-weight: 500;
    color: var(--text-primary, #1e293b);
}

.family-relation {
    font-size: 0.75rem;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.family-rel-conyuge {
    background: #fef3c7;
    color: #92400e;
}

.family-rel-progenitor {
    background: #e8eef6;
    color: #2d4266;
}

.family-rel-hijo {
    background: #dcfce7;
    color: #166534;
}

.no-family {
    text-align: center;
    padding: 1.5rem;
    color: var(--text-muted, #64748b);
    font-style: italic;
}

.loading-family {
    text-align: center;
    padding: 1rem;
    color: var(--text-muted, #64748b);
}

.btn-add-family {
    background: #4a6da7;
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-size: 0.875rem;
    cursor: pointer;
    transition: background 0.2s;
}

.btn-add-family:hover {
    background: #3d5a8a;
}

/* Modal Family Styles */
.modal-family {
    max-width: 500px;
    width: 90%;
}

.modal-section {
    margin-bottom: 1.5rem;
}

.modal-section h4 {
    margin-bottom: 0.75rem;
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--text-muted, #64748b);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.current-family-list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    max-height: 200px;
    overflow-y: auto;
}

.family-item-modal {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem;
    background: var(--bg-secondary, #f8fafc);
    border-radius: 8px;
}

.family-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.btn-remove-family {
    background: #495057;
    color: white;
    border: none;
    width: 28px;
    height: 28px;
    border-radius: 6px;
    font-size: 1.25rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.2s;
}

.btn-remove-family:hover {
    background: #343a40;
}

.add-family-form {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.form-row-family {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.family-select {
    width: 100%;
    padding: 0.75rem;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 0.875rem;
    background: white;
    transition: border-color 0.2s;
}

.family-select:focus {
    outline: none;
    border-color: #4a6da7;
}

.btn-add {
    align-self: flex-start;
}

/* Dark Theme */
[data-theme="dark"] .family-section {
    background: var(--card-bg-dark, #1e293b);
}

[data-theme="dark"] .family-item {
    background: var(--bg-secondary-dark, #334155);
}

[data-theme="dark"] .family-item:hover {
    background: var(--bg-hover-dark, #475569);
}

[data-theme="dark"] .family-name {
    color: var(--text-primary-dark, #f1f5f9);
}

[data-theme="dark"] .family-rel-conyuge {
    background: #78350f;
    color: #fef3c7;
}

[data-theme="dark"] .family-rel-progenitor {
    background: #1e3a8a;
    color: #e8eef6;
}

[data-theme="dark"] .family-rel-hijo {
    background: #14532d;
    color: #dcfce7;
}

[data-theme="dark"] .family-item-modal {
    background: var(--bg-secondary-dark, #334155);
}

[data-theme="dark"] .family-select {
    background: #334155;
    border-color: #475569;
    color: #f1f5f9;
}

@media (max-width: 640px) {
    .form-row-family {
        grid-template-columns: 1fr;
    }

    .section-header-with-action {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.75rem;
    }
}
</style>
<script>
// ===== FAMILY MANAGEMENT FUNCTIONS =====
const publicadorId = {{ $publicador->id }};

function loadFamilyList() {
    fetch(`/publicadores/${publicadorId}/familiares`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        const container = document.getElementById('familyList');
        if (!data.familiares || data.familiares.length === 0) {
            container.innerHTML = '<div class="no-family">Sin familiares registrados</div>';
            return;
        }
        container.innerHTML = data.familiares.map(f => `
            <a href="/publicadores/${f.familiar.id}" class="family-item">
                <span class="family-name">${f.familiar.nombre} ${f.familiar.apellidos}</span>
                <span class="family-relation family-rel-${f.tipo_relacion}">${f.tipo_label}</span>
            </a>
        `).join('');
    })
    .catch(e => {
        document.getElementById('familyList').innerHTML = '<div class="no-family">Error al cargar</div>';
    });
}

function openFamilyModal() {
    document.getElementById('familyModal').style.display = 'flex';
    loadCurrentFamily();
    loadAvailablePublishers();
}

function closeFamilyModal() {
    document.getElementById('familyModal').style.display = 'none';
}

function loadCurrentFamily() {
    fetch(`/publicadores/${publicadorId}/familiares`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        const container = document.getElementById('currentFamily');
        if (!data.familiares || data.familiares.length === 0) {
            container.innerHTML = '<div class="no-family">Sin familiares registrados</div>';
            return;
        }
        container.innerHTML = data.familiares.map(f => `
            <div class="family-item-modal">
                <div class="family-info">
                    <span class="family-name">${f.familiar.nombre} ${f.familiar.apellidos}</span>
                    <span class="family-relation family-rel-${f.tipo_relacion}">${f.tipo_label}</span>
                </div>
                <button onclick="removeFamiliar(${f.familiar.id})" class="btn-remove-family" title="Eliminar">x</button>
            </div>
        `).join('');
    });
}

function loadAvailablePublishers() {
    fetch(`/publicadores/${publicadorId}/disponibles-familia`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        const select = document.getElementById('familiarSelect');
        select.innerHTML = '<option value="">Seleccionar...</option>';
        data.disponibles.forEach(p => {
            select.innerHTML += `<option value="${p.id}">${p.nombre} ${p.apellidos}</option>`;
        });
    });
}

function addFamiliar() {
    const familiarId = document.getElementById('familiarSelect').value;
    const tipo = document.getElementById('tipoRelacion').value;

    if (!familiarId) {
        alert('Selecciona un publicador');
        return;
    }

    fetch(`/publicadores/${publicadorId}/add-familiar`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ familiar_id: familiarId, tipo_relacion: tipo })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            loadCurrentFamily();
            loadAvailablePublishers();
            loadFamilyList();
        } else {
            alert(data.message || 'Error al agregar familiar');
        }
    });
}

function removeFamiliar(familiarId) {
    if (!confirm('Eliminar esta relacion familiar?')) return;

    fetch(`/publicadores/${publicadorId}/remove-familiar`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ familiar_id: familiarId })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            loadCurrentFamily();
            loadAvailablePublishers();
            loadFamilyList();
        }
    });
}

// Close modal on overlay click
document.getElementById('familyModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeFamilyModal();
});

// Load family list on page load
document.addEventListener('DOMContentLoaded', loadFamilyList);
</script>
@endsection
