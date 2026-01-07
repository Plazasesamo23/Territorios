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
                <input type="checkbox" name="es_anciano" value="1" {{ old('es_anciano') ? 'checked' : '' }}
                       onchange="if(this.checked) document.querySelector('input[name=es_siervo_ministerial]').checked=false">
                <span>Anciano</span>
            </label>
            <label class="option-item">
                <input type="checkbox" name="es_siervo_ministerial" value="1" {{ old('es_siervo_ministerial') ? 'checked' : '' }}
                       onchange="if(this.checked) document.querySelector('input[name=es_anciano]').checked=false">
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
        </div>

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
