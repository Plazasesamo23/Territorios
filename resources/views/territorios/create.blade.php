@extends('layouts.app')

@section('title', 'Nuevo Territorio - Gestión de Territorios')

@section('content')
<!-- Navegación y acciones en una sola línea -->
<div class="page-nav">
    <div class="page-breadcrumbs">
        <a href="{{ route('dashboard') }}" class="breadcrumb-link">Dashboard</a>
        <span class="breadcrumb-sep">›</span>
        <a href="{{ route('territorios.index') }}" class="breadcrumb-link">Territorios</a>
        <span class="breadcrumb-sep">›</span>
        <span class="breadcrumb-current">Nuevo Territorio</span>
    </div>
    
    <div class="page-actions">
        <a href="{{ route('territorios.index') }}" class="btn btn-secondary">
            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Volver
        </a>
    </div>
</div>

<!-- Formulario principal -->
    <div class="card has-header">
        <div class="card-header">
            <h2 class="card-title">Información del Territorio</h2>
            <p class="card-subtitle">Completa los datos para crear un nuevo territorio</p>
        </div>

        <form action="{{ route('territorios.store') }}" method="POST" class="card-body">
            @csrf
            
            <div class="grid grid-2">
                <!-- Número del Territorio -->
                <div class="form-group">
                    <label for="numero" class="form-label required">
                        Número del Territorio
                    </label>
                    <div class="input-group">
                        <input 
                            type="number" 
                            id="numero" 
                            name="numero" 
                            value="{{ old('numero') }}"
                            required
                            min="1"
                            class="form-input @error('numero') error @enderror"
                            placeholder="Ej: 1, 2, 3..."
                        >
                        <div class="input-icon">
                            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"></path>
                            </svg>
                        </div>
                    </div>
                    @error('numero')
                        <p class="form-error">
                            <svg class="icon" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                    <p class="form-help">
                        Debe ser un número único en el sistema
                    </p>
                </div>

                <!-- Nombre del Territorio -->
                <div class="form-group">
                    <label for="nombre" class="form-label">
                        Nombre del Territorio
                    </label>
                    <div class="input-group">
                        <input 
                            type="text" 
                            id="nombre" 
                            name="nombre" 
                            value="{{ old('nombre') }}"
                            class="form-input @error('nombre') error @enderror"
                            placeholder="Ej: Centro Histórico, Barrio Norte..."
                        >
                        <div class="input-icon">
                            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                    </div>
                    @error('nombre')
                        <p class="form-error">
                            <svg class="icon" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                    <p class="form-help">
                        Nombre descriptivo para identificar fácilmente el territorio
                    </p>
                </div>

                <!-- Estado -->
                <div class="form-group">
                    <label for="estado" class="form-label">
                        Estado Inicial
                    </label>
                    <select 
                        id="estado" 
                        name="estado" 
                        class="form-select @error('estado') error @enderror"
                    >
                        <option value="libre" {{ old('estado', 'libre') == 'libre' ? 'selected' : '' }}>
                            🟢 Libre
                        </option>
                        <option value="activo" {{ old('estado') == 'activo' ? 'selected' : '' }}>
                            🔵 Activo
                        </option>
                        <option value="atrasado" {{ old('estado') == 'atrasado' ? 'selected' : '' }}>
                            🔴 Atrasado
                        </option>
                        <option value="archivo" {{ old('estado') == 'archivo' ? 'selected' : '' }}>
                            ⚫ Archivo
                        </option>
                    </select>
                    @error('estado')
                        <p class="form-error">
                            <svg class="icon" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                    <p class="form-help">
                        Por defecto, los territorios nuevos se crean como "Libre"
                    </p>
                </div>

                <!-- URL de Imagen -->
                <div class="form-group full-width">
                    <label for="imagen_url" class="form-label">
                        URL de la Imagen del Territorio
                    </label>
                    <div class="input-group">
                        <input 
                            type="url" 
                            id="imagen_url" 
                            name="imagen_url" 
                            value="{{ old('imagen_url') }}"
                            class="form-input @error('imagen_url') error @enderror"
                            placeholder="https://ejemplo.com/imagen-territorio.jpg"
                        >
                        <div class="input-icon">
                            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    </div>
                    @error('imagen_url')
                        <p class="form-error">
                            <svg class="icon" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                    <p class="form-help">
                        Opcional: URL de la imagen o mapa del territorio para facilitar la identificación
                    </p>
                </div>

                <!-- Notas -->
                <div class="form-group full-width">
                    <label for="notas" class="form-label">
                        Notas del Territorio
                    </label>
                    <textarea 
                        id="notas" 
                        name="notas" 
                        rows="4"
                        class="form-textarea @error('notas') error @enderror"
                        placeholder="Información adicional sobre el territorio, características especiales, instrucciones, dificultades, horarios recomendados, etc."
                    >{{ old('notas') }}</textarea>
                    @error('notas')
                        <p class="form-error">
                            <svg class="icon" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                    <p class="form-help">
                        Incluye cualquier información útil para los publicadores
                    </p>
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="form-actions">
                <a href="{{ route('territorios.index') }}" class="btn btn-secondary">
                    <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    Cancelar
                </a>
                <button type="submit" class="btn btn-primary">
                    <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Crear Territorio
                </button>
            </div>
        </form>
    </div>

    <!-- Información adicional -->
    <div class="info-box">
        <div class="info-box-header">
            <svg class="icon info" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        <div class="info-box-content">
            <h3 class="info-box-title">
                Consejos para crear territorios
            </h3>
            <div class="info-box-text">
                <ul>
                    <li><strong>Número único:</strong> Cada territorio debe tener un número único que no se repita en el sistema</li>
                    <li><strong>Imagen recomendada:</strong> Sube una imagen del mapa del territorio para facilitar su identificación</li>
                    <li><strong>Estado inicial:</strong> La mayoría de territorios nuevos se crean como "Libre" para estar disponibles</li>
                    <li><strong>Notas útiles:</strong> Incluye información sobre dificultades, horarios recomendados, o características especiales</li>
                    <li><strong>Acceso por WhatsApp:</strong> Si incluyes una imagen, podrás enviar el territorio por WhatsApp automáticamente</li>
                </ul>
            </div>
        </div>
    </div>
@endsection 