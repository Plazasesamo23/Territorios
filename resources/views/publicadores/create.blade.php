@extends('layouts.app')

@section('title', 'Nuevo Publicador')

@section('content')
<div class="page-flat">
    <h1 class="page-title">Nuevo Publicador</h1>
    <p class="page-subtitle">Registra un nuevo publicador para asignar territorios</p>

    <form action="{{ route('publicadores.store') }}" method="POST">
        @csrf

        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Nombre *</label>
                <input type="text" name="nombre" required value="{{ old('nombre') }}"
                       class="form-input" placeholder="Nombre">
                @error('nombre')
                    <span class="text-muted text-xs">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Apellidos</label>
                <input type="text" name="apellidos" value="{{ old('apellidos') }}"
                       class="form-input" placeholder="Apellidos">
            </div>

            <div class="form-group">
                <label class="form-label">Genero *</label>
                <select name="genero" class="form-input" required>
                    <option value="">-- Seleccionar --</option>
                    <option value="M" {{ old('genero') == 'M' ? 'selected' : '' }}>Hermano</option>
                    <option value="F" {{ old('genero') == 'F' ? 'selected' : '' }}>Hermana</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Telefono *</label>
                <input type="tel" name="telefono" required value="{{ old('telefono') }}"
                       class="form-input" placeholder="+34 612 345 678">
            </div>

            <div class="form-group">
                <label class="form-label">Grupo de Predicacion</label>
                <select name="grupo_predicacion_id" class="form-input">
                    <option value="">-- Seleccionar --</option>
                    @foreach($grupos as $grupo)
                        <option value="{{ $grupo->id }}" {{ old('grupo_predicacion_id') == $grupo->id ? 'selected' : '' }}>
                            Grupo {{ $grupo->numero }}@if($grupo->nombre) - {{ $grupo->nombre }}@endif
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="options-row">
            <label class="option-item">
                <input type="checkbox" name="activo" value="1" {{ old('activo', true) ? 'checked' : '' }}>
                <span>Activo</span>
            </label>
            <label class="option-item">
                <input type="checkbox" name="es_anciano" id="chk-anciano" value="1" {{ old('es_anciano') ? 'checked' : '' }}
                       onchange="onCambioAncianoSm(this, 'sm')">
                <span>Anciano</span>
            </label>
            <label class="option-item">
                <input type="checkbox" name="es_siervo_ministerial" id="chk-sm" value="1" {{ old('es_siervo_ministerial') ? 'checked' : '' }}
                       onchange="onCambioAncianoSm(this, 'anciano')">
                <span>Siervo Ministerial</span>
            </label>
            <label class="option-item">
                <input type="checkbox" name="es_precursor" value="1" {{ old('es_precursor') ? 'checked' : '' }}>
                <span>Precursor</span>
            </label>
            <label class="option-item">
                <input type="checkbox" name="es_menor" value="1" {{ old('es_menor') ? 'checked' : '' }}>
                <span>Menor</span>
            </label>
            <label class="option-item">
                <input type="checkbox" name="excluido_reuniones" value="1" {{ old('excluido_reuniones') ? 'checked' : '' }}>
                <span>Excluido de reuniones</span>
            </label>
        </div>

        <div id="bloque-nombramientos" class="nombramientos-row" style="{{ old('es_anciano') ? '' : 'display:none' }}">
            <div class="nombramientos-titulo">Nombramientos del cuerpo de ancianos</div>
            <div class="options-grid">
                @foreach(\App\Models\Publicador::NOMBRAMIENTOS_CUERPO as $campo => $etiqueta)
                    <label class="option-item">
                        <input type="checkbox" name="{{ $campo }}" value="1" {{ old($campo) ? 'checked' : '' }}>
                        <span>{{ $etiqueta }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div id="bloque-cargos-sm" class="cargos-sm-row" style="{{ old('es_siervo_ministerial') ? '' : 'display:none' }}">
            <div class="cargos-sm-titulo">Cargos del siervo ministerial</div>
            <table class="cargos-sm-tabla">
                <thead>
                    <tr><th>Categoría</th><th>Titular</th><th>Auxiliar</th></tr>
                </thead>
                <tbody>
                    @foreach(\App\Models\Publicador::CARGOS_SM_CATEGORIAS as $cat)
                        <tr>
                            <td>{{ $cat['etiqueta'] }}</td>
                            <td><input type="checkbox" name="{{ $cat['titular'] }}" value="1" {{ old($cat['titular']) ? 'checked' : '' }}></td>
                            <td><input type="checkbox" name="{{ $cat['aux'] }}" value="1" {{ old($cat['aux']) ? 'checked' : '' }}></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <p class="cargos-sm-hint">Solo un publicador puede ser titular de cada categoría. Si marcas a este como titular, se le quitará al anterior automáticamente.</p>
        </div>

        <script>
        function onCambioAncianoSm(elem, otro) {
            const anciano = document.getElementById('chk-anciano');
            const sm = document.getElementById('chk-sm');
            const bloqueAnc = document.getElementById('bloque-nombramientos');
            const bloqueSm = document.getElementById('bloque-cargos-sm');
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

        <div class="form-group">
            <label class="form-label">Notas</label>
            <textarea name="notas" rows="2" class="form-input"
                      placeholder="Notas adicionales...">{{ old('notas') }}</textarea>
        </div>

        <div class="form-actions">
            <a href="{{ route('publicadores.index') }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Crear Publicador</button>
        </div>
    </form>
</div>

<style>
.page-flat {
    max-width: 700px;
    margin: 0 auto;
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    margin-bottom: 1rem;
}

.options-row {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1rem;
    padding: 1rem;
    background: var(--bg-white);
    border-radius: var(--radius);
}

.option-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    font-size: 0.875rem;
    color: var(--text);
}

.option-item input {
    width: 1rem;
    height: 1rem;
}

.nombramientos-row {
    margin-bottom: 1rem;
    padding: 1rem;
    background: rgba(168,85,247,0.06);
    border: 1px solid rgba(168,85,247,0.18);
    border-radius: var(--radius);
}
.nombramientos-titulo {
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #c98ee3;
    margin-bottom: 0.625rem;
}

.cargos-sm-row {
    margin-bottom: 1rem;
    padding: 1rem;
    background: rgba(59,130,246,0.06);
    border: 1px solid rgba(59,130,246,0.18);
    border-radius: var(--radius);
}
.cargos-sm-titulo {
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #8aa8d6;
    margin-bottom: 0.625rem;
}
.cargos-sm-tabla {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.8125rem;
}
.cargos-sm-tabla th, .cargos-sm-tabla td {
    padding: 0.375rem 0.5rem;
    border-bottom: 1px solid rgba(255,255,255,0.04);
    text-align: left;
}
.cargos-sm-tabla th {
    color: rgba(241,243,245,0.55);
    font-weight: 500;
    font-size: 0.6875rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}
.cargos-sm-tabla th:nth-child(2),
.cargos-sm-tabla th:nth-child(3),
.cargos-sm-tabla td:nth-child(2),
.cargos-sm-tabla td:nth-child(3) {
    text-align: center;
    width: 80px;
}
.cargos-sm-hint {
    font-size: 0.6875rem;
    color: rgba(241,243,245,0.5);
    margin: 0.5rem 0 0 0;
    font-style: italic;
}
.options-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 0.5rem 1rem;
}

.form-actions {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    margin-top: 1.5rem;
}

@media (max-width: 600px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
    .options-row {
        flex-direction: column;
        gap: 0.75rem;
    }
}
</style>
@endsection
