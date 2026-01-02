@extends('layouts.app')

@php
    $tipoActual = $tipo ?? 'normal';
    $prefijo = \App\Models\Territorio::PREFIJOS[$tipoActual] ?? '';
    $tipoNombre = \App\Models\Territorio::TIPOS_NOMBRES[$tipoActual] ?? 'Normal';
@endphp

@section('title', 'Nuevo Territorio {{ $tipoNombre }} - Gestion de Territorios')

@section('content')
<!-- Navegacion y acciones en una sola linea -->
<div class="page-nav">
    <div class="page-breadcrumbs">
        <a href="{{ route('dashboard') }}" class="breadcrumb-link">Dashboard</a>
        <span class="breadcrumb-sep">></span>
        <a href="{{ route('territorios.index', ['tipo' => $tipoActual]) }}" class="breadcrumb-link">Territorios</a>
        <span class="breadcrumb-sep">></span>
        <span class="breadcrumb-current">Nuevo Territorio {{ $tipoNombre }}</span>
    </div>

    <div class="page-actions">
        <a href="{{ route('territorios.index', ['tipo' => $tipoActual]) }}" class="btn btn-secondary">
            Volver
        </a>
    </div>
</div>

<!-- Indicador de tipo -->
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
        Creando territorio de <strong>{{ $tipoNombre }}</strong>
        @if($tipoActual === 'campana' || $tipoActual === 'negocios')
            - Este territorio NO se incluira en el S-13
        @endif
    </span>
</div>
@endif

<!-- Formulario principal -->
    <div class="card has-header">
        <div class="card-header">
            <h2 class="card-title">
                @if($tipoActual === 'campana')
                    &#128227; Nuevo Territorio de Campana
                @elseif($tipoActual === 'negocios')
                    &#127970; Nuevo Territorio de Negocios
                @else
                    &#128203; Nuevo Territorio Normal
                @endif
            </h2>
            <p class="card-subtitle">Completa los datos para crear un nuevo territorio</p>
        </div>

        <form action="{{ route('territorios.store') }}" method="POST" class="card-body" enctype="multipart/form-data">
            @csrf
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
                            value="{{ old('numero', $siguienteNumero ?? '') }}"
                            required
                            min="1"
                            class="form-input @error('numero') error @enderror {{ $prefijo ? 'con-prefijo' : '' }}"
                            placeholder="Ej: 1, 2, 3..."
                        >
                    </div>
                    @error('numero')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                    <p class="form-help">
                        Siguiente numero sugerido: <strong>{{ $prefijo }}{{ $siguienteNumero ?? '1' }}</strong>
                    </p>
                </div>

                <!-- Zona del Territorio -->
                <div class="form-group">
                    <label for="zona" class="form-label">
                        Zona
                    </label>
                    <div class="input-group">
                        <input
                            type="text"
                            id="zona"
                            name="zona"
                            value="{{ old('zona') }}"
                            class="form-input @error('zona') error @enderror"
                            placeholder="Ej: Can Roqueta, Torre Romeu..."
                        >
                        <div class="input-icon">
                            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                    </div>
                    @error('zona')
                        <p class="form-error">
                            <svg class="icon" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                    <p class="form-help">
                        Zona donde se encuentra el territorio
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

                <!-- Imagen del Territorio -->
                <div class="form-group full-width">
                    <label class="form-label">
                        Imagen del Territorio
                    </label>

                    <!-- Selector de metodo -->
                    <div class="imagen-metodo-selector mb-3">
                        <button type="button" class="metodo-btn active" data-metodo="local" onclick="cambiarMetodoImagen('local')">
                            &#128194; Subir Archivo
                        </button>
                        <button type="button" class="metodo-btn" data-metodo="url" onclick="cambiarMetodoImagen('url')">
                            &#128279; Usar URL
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
                            value="{{ old('imagen_url') }}"
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

                    <p class="form-help mt-2">
                        La imagen ayuda a identificar el territorio y se envia por WhatsApp
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
                    <li><strong>Notas utiles:</strong> Incluye informacion sobre dificultades, horarios recomendados, o caracteristicas especiales</li>
                    <li><strong>Acceso por WhatsApp:</strong> Si incluyes una imagen, podras enviar el territorio por WhatsApp automaticamente</li>
                    @if($tipoActual !== 'normal')
                    <li><strong>Tipo {{ $tipoNombre }}:</strong> Los territorios de campana y negocios NO se incluyen en el reporte S-13</li>
                    @endif
                </ul>
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
    border: 2px solid #f59e0b;
    color: #92400e;
}
.tipo-indicator.tipo-negocios {
    background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
    border: 2px solid #3b82f6;
    color: #1e40af;
}
.tipo-icon {
    font-size: 1.5rem;
}
.numero-input-group {
    display: flex;
    align-items: center;
}
.numero-prefijo {
    background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
    color: white;
    padding: 0.75rem 1rem;
    font-weight: 700;
    font-size: 1.1rem;
    border-radius: 8px 0 0 8px;
    border: 2px solid #4f46e5;
    border-right: none;
}
.form-input.con-prefijo {
    border-radius: 0 8px 8px 0;
}

/* Selector de metodo de imagen */
.imagen-metodo-selector {
    display: flex;
    gap: 0.5rem;
}
.metodo-btn {
    flex: 1;
    padding: 0.75rem 1rem;
    border: 2px solid #e5e7eb;
    background: #f9fafb;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.2s;
    color: #6b7280;
}
.metodo-btn:hover {
    border-color: #3b82f6;
    background: #eff6ff;
}
.metodo-btn.active {
    border-color: #3b82f6;
    background: #3b82f6;
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
    border-color: #3b82f6;
    background: #eff6ff;
}
.upload-area.dragover {
    border-color: #3b82f6;
    background: #dbeafe;
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
    background: #ef4444;
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
    background: #dc2626;
}

.imagen-container.hidden {
    display: none;
}
.hidden {
    display: none !important;
}
.mt-2 {
    margin-top: 0.5rem;
}
.mb-3 {
    margin-bottom: 0.75rem;
}
</style>

<script>
function cambiarMetodoImagen(metodo) {
    // Actualizar botones
    document.querySelectorAll('.metodo-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    document.querySelector('[data-metodo="' + metodo + '"]').classList.add('active');

    // Mostrar/ocultar contenedores
    if (metodo === 'local') {
        document.getElementById('imagen-local-container').classList.remove('hidden');
        document.getElementById('imagen-url-container').classList.add('hidden');
        document.getElementById('imagen_url').value = '';
    } else {
        document.getElementById('imagen-local-container').classList.add('hidden');
        document.getElementById('imagen-url-container').classList.remove('hidden');
        removeImagen();
    }
}

function previewImagen(input) {
    if (input.files && input.files[0]) {
        var file = input.files[0];

        // Validar tamaño (2MB max)
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
    document.getElementById('imagen').value = '';
    document.getElementById('preview-img').src = '';
    document.getElementById('imagen-preview').classList.add('hidden');
    document.getElementById('upload-area').style.display = 'block';
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
});
</script>
@endsection 