@extends('layouts.app')

@section('title', 'Crear programa - Reuniones')

@section('content')

<div class="page-sm">
    <div class="rc-header">
        <h1 class="page-title">Crear programas</h1>
        <p class="page-subtitle">Genera programas semanales de la reunion VyM</p>
    </div>

    <form action="{{ route('reuniones.store') }}" method="POST" class="rc-form">
        @csrf

        <div class="rc-fields">
            <div class="form-group">
                <label class="form-label">Desde la semana del</label>
                <input type="date" name="fecha_semana" class="form-input rc-input--narrow" value="{{ $proximoLunes->format('Y-m-d') }}" required>
                @error('fecha_semana')
                    <span class="rc-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">¿Cuantas semanas?</label>
                <select name="cantidad_semanas" class="form-input rc-input--narrow">
                    <option value="1" selected>1 semana</option>
                    <option value="2">2 semanas</option>
                    <option value="4">4 semanas (1 mes)</option>
                    <option value="8">8 semanas (2 meses)</option>
                    <option value="12">12 semanas (3 meses)</option>
                    <option value="24">24 semanas (6 meses)</option>
                </select>
            </div>

            <div class="form-group">
                <label class="rc-check">
                    <input type="checkbox" name="importar_titulos" value="1">
                    <span>Importar titulos de jw.org al crearlas</span>
                </label>
                <span class="rc-check__hint">Si lo dejas sin marcar, las semanas se crean en blanco y puedes importar los titulos desde la pagina de edicion.</span>
            </div>
        </div>

        <div class="rc-notice">
            <svg class="rc-notice__icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
            <span>Si alguna de esas semanas ya existe, se omitira y se te indicara cuantas se crearon. No duplica nada.</span>
        </div>

        <div class="rc-actions">
            <button type="submit" class="btn btn-teal">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                Crear programas
            </button>
            <a href="{{ route('reuniones.index') }}" class="btn btn-ghost">Cancelar</a>
        </div>
    </form>
</div>

<style>
.rc-header {
    margin-bottom: 2rem;
}

.rc-form {
    margin-top: 0;
}

.rc-fields {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    margin-bottom: 1.5rem;
}

.rc-input--narrow {
    max-width: 320px;
}

.rc-error {
    display: block;
    margin-top: 0.375rem;
    font-size: 0.8rem;
    color: #ef4444;
}

.rc-check {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    font-size: 0.95rem;
    color: var(--text);
}

.rc-check input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
    accent-color: var(--color-tesoros-text);
}

.rc-check__hint {
    display: block;
    margin-top: 0.25rem;
    font-size: 0.8rem;
    color: var(--text-muted);
    line-height: 1.4;
}

.rc-notice {
    display: flex;
    align-items: flex-start;
    gap: 0.625rem;
    padding: 0.875rem 1rem;
    background: var(--bg-hover);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    font-size: 0.85rem;
    color: var(--text-secondary);
    line-height: 1.5;
    margin-bottom: 1.75rem;
}

.rc-notice__icon {
    flex-shrink: 0;
    margin-top: 2px;
    color: var(--text-muted);
}

.rc-actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}
</style>

@endsection
