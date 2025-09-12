@extends('layouts.app')

@section('title', 'Editar Territorio #{{ $territorio->numero }} - Gestión de Territorios')

@section('content')
<!-- Navegación y acciones en una sola línea -->
<div class="page-nav">
    <div class="page-breadcrumbs">
        <a href="{{ route('dashboard') }}" class="breadcrumb-link">Dashboard</a>
        <span class="breadcrumb-sep">›</span>
        <a href="{{ route('territorios.index') }}" class="breadcrumb-link">Territorios</a>
        <span class="breadcrumb-sep">›</span>
        <a href="{{ route('territorios.show', $territorio) }}" class="breadcrumb-link">Territorio #{{ $territorio->numero }}</a>
        <span class="breadcrumb-sep">›</span>
        <span class="breadcrumb-current">Editar</span>
    </div>
    
    <div class="page-actions">
        <a href="{{ route('territorios.show', $territorio) }}" class="btn btn-primary">
            Ver
        </a>
        <a href="{{ route('territorios.index') }}" class="btn btn-secondary">
            Volver
        </a>
    </div>
</div>

<!-- Formulario -->
    <div class="card has-header">
        <div class="card-header">
            <h2 class="card-title">Editar Territorio #{{ $territorio->numero }}</h2>
            <p class="card-subtitle">Modificar información del territorio</p>
        </div>

        <form action="{{ route('territorios.update', $territorio) }}" method="POST" class="card-body">
            @csrf
            @method('PUT')
            
            <div class="grid grid-2">
                <!-- Número del Territorio -->
                <div class="form-group">
                    <label for="numero" class="form-label required">
                        Número del Territorio
                    </label>
                    <input 
                        type="number" 
                        id="numero" 
                        name="numero" 
                        value="{{ old('numero', $territorio->numero) }}"
                        required
                        min="1"
                        class="form-input @error('numero') error @enderror"
                        placeholder="Ej: 1, 2, 3..."
                    >
                    @error('numero')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nombre del Territorio -->
                <div class="form-group">
                    <label for="nombre" class="form-label">
                        Nombre del Territorio
                    </label>
                    <input 
                        type="text" 
                        id="nombre" 
                        name="nombre" 
                        value="{{ old('nombre', $territorio->nombre) }}"
                        class="form-input @error('nombre') error @enderror"
                        placeholder="Ej: Centro Histórico, Barrio Norte..."
                    >
                    @error('nombre')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                    <p class="form-help">
                        Nombre descriptivo para identificar fácilmente el territorio
                    </p>
                </div>

                <!-- URL de Imagen -->
                <div class="form-group full-width">
                    <label for="imagen_url" class="form-label">
                        URL de la Imagen del Territorio
                    </label>
                    <input 
                        type="url" 
                        id="imagen_url" 
                        name="imagen_url" 
                        value="{{ old('imagen_url', $territorio->imagen_url) }}"
                        class="form-input @error('imagen_url') error @enderror"
                        placeholder="https://ejemplo.com/imagen-territorio.jpg"
                    >
                    @error('imagen_url')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                    <p class="form-help">
                        Opcional: URL de la imagen o mapa del territorio
                    </p>
                    
                    <!-- Vista previa de imagen actual -->
                    @if($territorio->imagen_url)
                        <div class="image-preview">
                            <p class="image-preview-label">Imagen actual:</p>
                            <img src="{{ $territorio->imagen_url }}" alt="Territorio {{ $territorio->numero }}" class="image-preview-img">
                        </div>
                    @endif
                </div>

                <!-- Estado -->
                <div class="form-group">
                    <label for="estado" class="form-label">
                        Estado del Territorio
                    </label>
                    <select 
                        id="estado" 
                        name="estado" 
                        class="form-select @error('estado') error @enderror"
                    >
                        <option value="libre" {{ old('estado', $territorio->estado) == 'libre' ? 'selected' : '' }}>
                            🟢 Libre
                        </option>
                        <option value="activo" {{ old('estado', $territorio->estado) == 'activo' ? 'selected' : '' }}>
                            🔵 Activo
                        </option>
                        <option value="atrasado" {{ old('estado', $territorio->estado) == 'atrasado' ? 'selected' : '' }}>
                            🔴 Atrasado
                        </option>
                        <option value="archivo" {{ old('estado', $territorio->estado) == 'archivo' ? 'selected' : '' }}>
                            ⚫ Archivo
                        </option>
                    </select>
                    @error('estado')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                    <p class="form-help">
                        Estado actual: <strong>{{ ucfirst($territorio->estado) }}</strong>
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
                        placeholder="Información adicional sobre el territorio, características especiales, instrucciones, etc."
                    >{{ old('notas', $territorio->notas) }}</textarea>
                    @error('notas')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Botones -->
            <div class="form-actions">
                <a href="{{ route('territorios.show', $territorio) }}" class="btn btn-secondary">
                    Cancelar
                </a>
                <button type="submit" class="btn btn-primary">
                    Actualizar Territorio
                </button>
            </div>
        </form>
    </div>

    <!-- Información adicional -->
    <div class="grid grid-2">
        <!-- Información del territorio -->
        <div class="info-card">
            <h3 class="info-card-title">
                Información actual del territorio
            </h3>
            <div class="info-card-content">
                <div><strong>Creado:</strong> {{ $territorio->created_at->format('d/m/Y H:i') }}</div>
                <div><strong>Última actualización:</strong> {{ $territorio->updated_at->format('d/m/Y H:i') }}</div>
                @if($territorio->ultima_salida)
                    <div><strong>Última salida:</strong> {{ $territorio->ultima_salida->format('d/m/Y') }}</div>
                @endif
            </div>
        </div>

        <!-- Historial reciente -->
        <div class="info-card">
            <h3 class="info-card-title">
                Registros recientes
            </h3>
            <div class="info-card-content">
                @if($territorio->registros()->limit(3)->count() > 0)
                    @foreach($territorio->registros()->with('publicador')->latest()->limit(3)->get() as $registro)
                        <div class="registro-item">
                            <span>{{ $registro->publicador->nombre ?? 'N/A' }}</span>
                            <span>{{ $registro->fecha_salida->format('d/m/Y') }}</span>
                        </div>
                    @endforeach
                    @if($territorio->registros()->count() > 3)
                        <p class="registro-more">
                            ... y {{ $territorio->registros()->count() - 3 }} registros más
                        </p>
                    @endif
                @else
                    <p class="no-data">Sin registros aún</p>
                @endif
            </div>
        </div>
    </div>
@endsection 