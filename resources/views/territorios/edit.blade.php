@extends('layouts.app')

@php
    $tipoActual = $territorio->tipo ?? 'normal';
    $prefijo = \App\Models\Territorio::PREFIJOS[$tipoActual] ?? '';
    $tipoNombre = \App\Models\Territorio::TIPOS_NOMBRES[$tipoActual] ?? 'Normal';
@endphp

@section('title', 'Editar Territorio {{ $territorio->numero_completo }} - Gestion de Territorios')

@section('content')
<!-- Navegacion y acciones en una sola linea -->
<div class="page-nav">
    <div class="page-breadcrumbs">
        <a href="{{ route('dashboard') }}" class="breadcrumb-link">Dashboard</a>
        <span class="breadcrumb-sep">></span>
        <a href="{{ route('territorios.index', ['tipo' => $tipoActual]) }}" class="breadcrumb-link">Territorios</a>
        <span class="breadcrumb-sep">></span>
        <a href="{{ route('territorios.show', $territorio) }}" class="breadcrumb-link">Territorio {{ $territorio->numero_completo }}</a>
        <span class="breadcrumb-sep">></span>
        <span class="breadcrumb-current">Editar</span>
    </div>

    <div class="page-actions">
        <a href="{{ route('territorios.show', $territorio) }}" class="btn btn-primary">
            Ver
        </a>
        <a href="{{ route('territorios.index', ['tipo' => $tipoActual]) }}" class="btn btn-secondary">
            Volver
        </a>
    </div>
</div>

<!-- Indicador de tipo para territorios especiales -->
@if($tipoActual !== 'normal')
<div class="tipo-indicator tipo-{{ $tipoActual }} mb-4">
    <span class="tipo-icon">
        @if($tipoActual === 'campana')
            &#128227;
        @else
            &#127970;
        @endif
    </span>
    <span class="tipo-text">
        Territorio de <strong>{{ $tipoNombre }}</strong> - No se incluye en el S-13
    </span>
</div>
@endif

<!-- Formulario -->
    <div class="card has-header">
        <div class="card-header">
            <h2 class="card-title">Editar Territorio {{ $territorio->numero_completo }}</h2>
            <p class="card-subtitle">Modificar informacion del territorio</p>
        </div>

        <form action="{{ route('territorios.update', $territorio) }}" method="POST" class="card-body" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" name="tipo" value="{{ $tipoActual }}">

            <div class="grid grid-2">
                <!-- Numero del Territorio -->
                <div class="form-group">
                    <label for="numero" class="form-label required">
                        Numero del Territorio
                    </label>
                    <div class="input-group numero-input-group">
                        @if($prefijo)
                        <span class="numero-prefijo">{{ $prefijo }}</span>
                        @endif
                        <input
                            type="number"
                            id="numero"
                            name="numero"
                            value="{{ old('numero', $territorio->numero) }}"
                            required
                            min="1"
                            class="form-input @error('numero') error @enderror {{ $prefijo ? 'con-prefijo' : '' }}"
                            placeholder="Ej: 1, 2, 3..."
                        >
                    </div>
                    @error('numero')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Zona del Territorio -->
                <div class="form-group">
                    <label for="zona" class="form-label">
                        Zona
                    </label>
                    <input
                        type="text"
                        id="zona"
                        name="zona"
                        value="{{ old('zona', $territorio->zona) }}"
                        class="form-input @error('zona') error @enderror"
                        placeholder="Ej: Can Roqueta, Torre Romeu..."
                    >
                    @error('zona')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                    <p class="form-help">
                        Nombre descriptivo para identificar fácilmente el territorio
                    </p>
                </div>

                <!-- Imagen del Territorio -->
                <div class="form-group full-width">
                    <label class="form-label">
                        Imagen del Territorio
                    </label>

                    <!-- Imagen actual -->
                    @if($territorio->tieneImagen())
                    <div class="imagen-actual mb-3">
                        <p class="imagen-actual-label">Imagen actual:</p>
                        <div class="imagen-actual-preview">
                            <img src="{{ $territorio->getImagenUrl() }}" alt="Territorio {{ $territorio->numero_completo }}">
                        </div>
                    </div>
                    @endif

                    <!-- Selector de metodo -->
                    <div class="imagen-metodo-selector mb-3">
                        <button type="button" class="metodo-btn active" data-metodo="local" onclick="cambiarMetodoImagen('local')">
                            &#128194; Subir Nueva Imagen
                        </button>
                        <button type="button" class="metodo-btn" data-metodo="url" onclick="cambiarMetodoImagen('url')">
                            &#128279; Usar URL
                        </button>
                        <button type="button" class="metodo-btn" data-metodo="mantener" onclick="cambiarMetodoImagen('mantener')">
                            &#10004; Mantener Actual
                        </button>
                    </div>

                    <!-- Opcion: Subir archivo local -->
                    <div id="imagen-local-container" class="imagen-container">
                        <div class="upload-area" id="upload-area" onclick="document.getElementById('imagen').click()">
                            <div class="upload-icon">&#128247;</div>
                            <div class="upload-text">
                                <strong>Haz clic para seleccionar</strong> o arrastra una imagen aqui
                            </div>
                            <div class="upload-hint">JPG, JPEG o PNG. Maximo 2MB</div>
                        </div>
                        <input
                            type="file"
                            id="imagen"
                            name="imagen"
                            accept=".jpg,.jpeg,.png"
                            class="file-input-hidden"
                            onchange="previewImagen(this)"
                        >
                        <div id="imagen-preview" class="imagen-preview hidden">
                            <img id="preview-img" src="" alt="Preview">
                            <button type="button" class="remove-image-btn" onclick="removeImagen()">&#10006;</button>
                        </div>
                        @error('imagen')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Opcion: URL externa -->
                    <div id="imagen-url-container" class="imagen-container hidden">
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
                            Soporta enlaces de Dropbox y Google Drive
                        </p>
                    </div>

                    <!-- Opcion: Mantener actual -->
                    <div id="imagen-mantener-container" class="imagen-container hidden">
                        <div class="mantener-info">
                            &#10004; Se mantendra la imagen actual del territorio
                        </div>
                    </div>
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
                            ... y {{ $territorio->registros()->count() - 3 }} registros mas
                        </p>
                    @endif
                @else
                    <p class="no-data">Sin registros aun</p>
                @endif
            </div>
        </div>
    </div>

<style>
.tipo-indicator {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem 1.25rem;
    border-radius: 10px;
    font-size: 0.95rem;
}
.tipo-indicator.tipo-campana {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    border: 2px solid #4a6da7;
    color: #92400e;
}
.tipo-indicator.tipo-negocios {
    background: linear-gradient(135deg, #e8eef6 0%, #bfdbfe 100%);
    border: 2px solid #4a6da7;
    color: #2d4266;
}
.tipo-icon {
    font-size: 1.5rem;
}
.numero-input-group {
    display: flex;
    align-items: center;
}
.numero-prefijo {
    background: linear-gradient(135deg, #5c7fb8 0%, #4a6da7 100%);
    color: white;
    padding: 0.75rem 1rem;
    font-weight: 700;
    font-size: 1.1rem;
    border-radius: 8px 0 0 8px;
    border: 2px solid #4a6da7;
    border-right: none;
}
.form-input.con-prefijo {
    border-radius: 0 8px 8px 0;
}

/* Imagen actual */
.imagen-actual {
    background: #f3f4f6;
    border-radius: 12px;
    padding: 1rem;
}
.imagen-actual-label {
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #374151;
}
.imagen-actual-preview img {
    max-width: 100%;
    max-height: 200px;
    border-radius: 8px;
    object-fit: contain;
}

/* Selector de metodo de imagen */
.imagen-metodo-selector {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}
.metodo-btn {
    flex: 1;
    min-width: 120px;
    padding: 0.75rem 1rem;
    border: 2px solid #e5e7eb;
    background: #f9fafb;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.2s;
    color: #6b7280;
    font-size: 0.85rem;
}
.metodo-btn:hover {
    border-color: #4a6da7;
    background: #f4f7fb;
}
.metodo-btn.active {
    border-color: #4a6da7;
    background: #4a6da7;
    color: white;
}

/* Area de upload */
.upload-area {
    border: 2px dashed #d1d5db;
    border-radius: 12px;
    padding: 2rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s;
    background: #fafafa;
}
.upload-area:hover {
    border-color: #4a6da7;
    background: #f4f7fb;
}
.upload-area.dragover {
    border-color: #4a6da7;
    background: #e8eef6;
}
.upload-icon {
    font-size: 3rem;
    margin-bottom: 0.5rem;
}
.upload-text {
    color: #374151;
    margin-bottom: 0.25rem;
}
.upload-hint {
    color: #9ca3af;
    font-size: 0.85rem;
}
.file-input-hidden {
    display: none;
}

/* Preview de imagen */
.imagen-preview {
    position: relative;
    margin-top: 1rem;
    border-radius: 12px;
    overflow: hidden;
    border: 2px solid #e5e7eb;
}
.imagen-preview img {
    width: 100%;
    max-height: 300px;
    object-fit: contain;
    background: #f3f4f6;
}
.remove-image-btn {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    width: 32px;
    height: 32px;
    background: #495057;
    color: white;
    border: none;
    border-radius: 50%;
    cursor: pointer;
    font-size: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
}
.remove-image-btn:hover {
    background: #343a40;
}

.imagen-container.hidden {
    display: none;
}
.hidden {
    display: none !important;
}
.mb-3 {
    margin-bottom: 0.75rem;
}

/* Mantener actual */
.mantener-info {
    background: #e8eef6;
    color: #2d4266;
    padding: 1rem;
    border-radius: 8px;
    font-weight: 500;
    text-align: center;
}
</style>

<script>
function cambiarMetodoImagen(metodo) {
    // Actualizar botones
    document.querySelectorAll('.metodo-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    document.querySelector('[data-metodo="' + metodo + '"]').classList.add('active');

    // Ocultar todos los contenedores
    document.getElementById('imagen-local-container').classList.add('hidden');
    document.getElementById('imagen-url-container').classList.add('hidden');
    document.getElementById('imagen-mantener-container').classList.add('hidden');

    // Mostrar el contenedor seleccionado
    if (metodo === 'local') {
        document.getElementById('imagen-local-container').classList.remove('hidden');
        document.getElementById('imagen_url').value = '';
    } else if (metodo === 'url') {
        document.getElementById('imagen-url-container').classList.remove('hidden');
        removeImagen();
    } else {
        document.getElementById('imagen-mantener-container').classList.remove('hidden');
        document.getElementById('imagen_url').value = '';
        removeImagen();
    }
}

function previewImagen(input) {
    if (input.files && input.files[0]) {
        var file = input.files[0];

        // Validar tamano (2MB max)
        if (file.size > 2 * 1024 * 1024) {
            alert('La imagen es demasiado grande. Maximo 2MB.');
            input.value = '';
            return;
        }

        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview-img').src = e.target.result;
            document.getElementById('imagen-preview').classList.remove('hidden');
            document.getElementById('upload-area').style.display = 'none';
        };
        reader.readAsDataURL(file);
    }
}

function removeImagen() {
    var imgInput = document.getElementById('imagen');
    var previewImg = document.getElementById('preview-img');
    var imgPreview = document.getElementById('imagen-preview');
    var uploadArea = document.getElementById('upload-area');

    if (imgInput) imgInput.value = '';
    if (previewImg) previewImg.src = '';
    if (imgPreview) imgPreview.classList.add('hidden');
    if (uploadArea) uploadArea.style.display = 'block';
}

// Drag and drop
document.addEventListener('DOMContentLoaded', function() {
    var uploadArea = document.getElementById('upload-area');
    var fileInput = document.getElementById('imagen');

    if (uploadArea) {
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            uploadArea.addEventListener(eventName, () => uploadArea.classList.add('dragover'), false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, () => uploadArea.classList.remove('dragover'), false);
        });

        uploadArea.addEventListener('drop', function(e) {
            var files = e.dataTransfer.files;
            if (files.length) {
                fileInput.files = files;
                previewImagen(fileInput);
            }
        }, false);
    }

    // Por defecto seleccionar "Mantener actual" si hay imagen
    @if($territorio->tieneImagen())
    cambiarMetodoImagen('mantener');
    @endif
});
</script>
@endsection