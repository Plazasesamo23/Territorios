@extends('layouts.app')

@section('title', 'Crear programa - Reuniones')

@section('content')

<div class="page-sm">
    <div class="rc-header">
        <h1 class="page-title">Crear programas</h1>
        <p class="page-subtitle">Se importaran automaticamente los titulos desde jw.org</p>
    </div>

    <form action="{{ route('reuniones.store') }}" method="POST" class="rc-form">
        @csrf

        <div class="rc-fields">
            <div class="form-group">
                <label class="form-label">Desde la semana del (lunes)</label>
                <input type="date" name="fecha_semana" class="form-input rc-input--narrow" value="{{ $proximoLunes->format('Y-m-d') }}" required>
                @error('fecha_semana')
                    <span class="rc-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Cantidad de semanas</label>
                <select name="cantidad_semanas" class="form-input rc-input--narrow">
                    <option value="4">4 semanas (1 mes)</option>
                    <option value="8">8 semanas (2 meses)</option>
                    <option value="12">12 semanas (3 meses)</option>
                    <option value="16" selected>16 semanas (4 meses)</option>
                    <option value="24">24 semanas (6 meses)</option>
                </select>
            </div>
        </div>

        <div class="rc-notice">
            <svg class="rc-notice__icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
            <span>Cada programa se creara con los titulos reales del cuadernillo VyM importados directamente de jw.org. Si alguna semana no esta disponible, se creara con partes estandar.</span>
        </div>

        <div class="rc-actions">
            <button type="submit" class="btn btn-teal">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                Crear e importar de jw.org
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
