@extends('layouts.app')

@section('title', 'Territorio #{{ $territorio->numero ?? "N/A" }} - Gestión de Territorios')

@section('content')

@if(!isset($territorio) || !$territorio || !$territorio->id)
    <div class="card text-center" style="padding: 3rem;">
        <div style="font-size: 4rem; margin-bottom: 1rem;">⚠️</div>
        <h3 style="font-size: 1.5rem; margin-bottom: 0.5rem; color: #dc2626;">Error: Territorio no encontrado</h3>
        <p class="text-muted mb-4">El territorio que intentas acceder no existe o no se pudo cargar.</p>
        <a href="{{ route('territorios.index') }}" class="btn btn-primary">
            ↩️ Volver a Territorios
        </a>
    </div>
@else

<!-- Navegación de página minimalista -->
<div class="page-nav">
    <div class="page-breadcrumbs">
        <a href="{{ route('dashboard') }}" class="breadcrumb-link">Dashboard</a>
        <span class="breadcrumb-sep">›</span>
        <a href="{{ route('territorios.index') }}" class="breadcrumb-link">Territorios</a>
        <span class="breadcrumb-sep">›</span>
        <span class="breadcrumb-current">Territorio #{{ $territorio->numero }}</span>
    </div>
    
    <div class="page-actions">
        <a href="{{ route('territorios.index') }}" class="btn btn-secondary">
            ↩️ Volver al Listado
        </a>
    </div>
</div>

<div class="territorio-detail-container">
    <!-- Header con botones de acción -->
    <div class="detail-header">
        <div class="header-info">
            <div class="territorio-title">
                <div class="numero-circle">{{ $territorio->numero }}</div>
                <div class="title-content">
                    <h1 id="titulo-territorio">Territorio #{{ $territorio->numero }}</h1>
                    <div class="status-badges">
                        <span class="estado-badge estado-{{ $territorio->calcularEstado() }}">
                            {{ ucfirst($territorio->calcularEstado()) }}
                        </span>
                        @if(!$territorio->activo)
                            <span class="inactive-badge">Inactivo</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <div class="action-buttons">
            <button type="button" id="btn-editar" class="btn-primary">
                <i class="icon">✏️</i> Editar
            </button>
            <button type="button" id="btn-cancelar" class="btn-secondary hidden">
                <i class="icon">❌</i> Cancelar
            </button>
            <button type="button" id="btn-guardar" class="btn-success hidden">
                <i class="icon">💾</i> Guardar
            </button>
            <button type="button" id="btn-eliminar" class="btn-danger">
                <i class="icon">🗑️</i> Eliminar
            </button>
        </div>
    </div>

    <!-- Formulario principal -->
    <form id="form-territorio" action="{{ route('territorios.update', $territorio) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="content-grid">
            <!-- Columna izquierda: Imagen -->
                         <div class="image-section card">
                 <h3><i class="icon">🖼️</i> Imagen del Territorio</h3>
                
                <div class="image-container">
                    <img id="imagen-territorio" src="{{ $territorio->getImagenUrl() }}" alt="Territorio {{ $territorio->numero }}">
                    
                    <!-- Input para subir imagen (oculto por defecto) -->
                    <div id="upload-container" class="upload-container hidden">
                        <input type="file" id="imagen" name="imagen" accept=".jpg,.jpeg,.png" class="file-input">
                        <label for="imagen" class="file-label">
                            <i class="icon">📁</i>
                            <span>Seleccionar nueva imagen</span>
                            <small>Formato: JPG, PNG. Máximo 2MB</small>
                        </label>
                    </div>
                </div>
                
                @if($territorio->tieneCoordenadasValidas())
                    <div class="maps-link">
                        <a href="{{ $territorio->getGoogleMapsUrl() }}" target="_blank" class="btn-maps">
                            <i class="icon">🗺️</i> Ver en Google Maps
                        </a>
                    </div>
                @endif
            </div>

            <!-- Columna derecha: Información -->
            <div class="info-section">
                
                                 <!-- Información básica -->
                 <div class="card">
                     <h3><i class="icon">📄</i> Información Básica</h3>
                    
                    <div class="field-group">
                        <label for="numero">Número del Territorio</label>
                        <input type="number" id="numero" name="numero" value="{{ $territorio->numero }}" readonly class="campo-lectura">
                    </div>
                    
                    <div class="field-group">
                        <label for="nombre">Nombre</label>
                        <input type="text" id="nombre" name="nombre" value="{{ $territorio->nombre }}" readonly class="campo-lectura">
                    </div>
                    
                    <div class="field-group">
                        <label for="descripcion">Descripción</label>
                        <textarea id="descripcion" name="descripcion" rows="3" readonly class="campo-lectura">{{ $territorio->descripcion }}</textarea>
                    </div>
                    
                    <div class="field-row">
                        <div class="field-group">
                            <label for="estado">Estado</label>
                            <select id="estado" name="estado" disabled class="campo-lectura">
                                <option value="libre" {{ $territorio->estado == 'libre' ? 'selected' : '' }}>Libre</option>
                                <option value="activo" {{ $territorio->estado == 'activo' ? 'selected' : '' }}>Activo</option>
                                <option value="atrasado" {{ $territorio->estado == 'atrasado' ? 'selected' : '' }}>Atrasado</option>
                                <option value="archivo" {{ $territorio->estado == 'archivo' ? 'selected' : '' }}>En Archivo</option>
                            </select>
                        </div>
                        
                        <div class="field-group">
                            <label for="activo">Estado General</label>
                            <div class="checkbox-container">
                                <input type="checkbox" id="activo" name="activo" {{ $territorio->activo ? 'checked' : '' }} disabled class="campo-lectura">
                                <label for="activo" class="checkbox-label">Territorio Activo</label>
                            </div>
                        </div>
                    </div>
                </div>

                                 <!-- Coordenadas -->
                 <div class="card">
                     <h3><i class="icon">📍</i> Ubicación</h3>
                    
                    <div class="field-row">
                        <div class="field-group">
                            <label for="coordenadas_lat">Latitud</label>
                            <input type="number" id="coordenadas_lat" name="coordenadas_lat" 
                                   value="{{ $territorio->coordenadas_lat }}" 
                                   step="0.00000001" readonly class="campo-lectura"
                                   placeholder="Ej: 40.7128">
                        </div>
                        
                        <div class="field-group">
                            <label for="coordenadas_lng">Longitud</label>
                            <input type="number" id="coordenadas_lng" name="coordenadas_lng" 
                                   value="{{ $territorio->coordenadas_lng }}" 
                                   step="0.00000001" readonly class="campo-lectura"
                                   placeholder="Ej: -74.0060">
                        </div>
                    </div>
                    
                    <small class="field-help">
                        💡 Puedes obtener las coordenadas desde Google Maps haciendo clic derecho en el mapa
                    </small>
                </div>

                                 <!-- Notas -->
                 <div class="card">
                     <h3><i class="icon">📝</i> Anotaciones</h3>
                    
                    <div class="field-group">
                        <label for="notas">Notas internas</label>
                        <textarea id="notas" name="notas" rows="4" readonly class="campo-lectura" 
                                  placeholder="Información adicional, observaciones, etc...">{{ $territorio->notas }}</textarea>
                    </div>
                </div>

                                 <!-- Registros -->
                 <div class="card">
                     <h3><i class="icon">📊</i> Registros</h3>
                    
                    <div class="field-row">
                        <div class="field-group">
                            <label>Creado</label>
                            <span class="field-value">{{ $territorio->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        
                        <div class="field-group">
                            <label>Última Actualización</label>
                            <span class="field-value">{{ $territorio->updated_at->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                    
                    @if($territorio->publicadorActual())
                        <div class="field-group">
                            <label>Asignado a</label>
                            <span class="field-value">{{ $territorio->publicadorActual()->nombre }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Modal de confirmación para eliminar -->
<div id="modal-eliminar" class="modal-overlay hidden">
    <div class="modal-content">
        <h3>¿Eliminar territorio?</h3>
        <p>Esta acción no se puede deshacer. ¿Estás seguro de que quieres eliminar el territorio #{{ $territorio->numero }}?</p>
        <div class="modal-buttons">
            <button type="button" id="confirmar-eliminar" class="btn-danger">Sí, eliminar</button>
            <button type="button" id="cancelar-eliminar" class="btn-secondary">Cancelar</button>
        </div>
    </div>
</div>

<!-- Formulario oculto para eliminar -->
<form id="form-eliminar" action="{{ route('territorios.destroy', $territorio) }}" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<style>
/* Contenedor principal */
.territorio-detail-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 1rem;
    space-y: 1.5rem;
}

/* Header */
.detail-header {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.territorio-title {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.numero-circle {
    width: 4rem;
    height: 4rem;
    background: #ef4444;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: 900;
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
}

.title-content h1 {
    font-size: 2rem;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
}

.status-badges {
    display: flex;
    gap: 0.5rem;
    margin-top: 0.5rem;
}

.estado-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
}

.estado-libre { background: #dcfce7; color: #166534; }
.estado-activo { background: #dbeafe; color: #1e40af; }
.estado-atrasado { background: #fee2e2; color: #991b1b; }
.estado-archivo { background: #f3f4f6; color: #374151; }

.inactive-badge {
    background: #fef3c7;
    color: #92400e;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 700;
}

.action-buttons {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

/* Botones */
 .btn-primary, .btn-secondary, .btn-success, .btn-danger {
     border-radius: 8px;
     text-decoration: none;
     display: inline-flex;
     align-items: center;
     gap: 0.5rem;
     border: none;
     cursor: pointer;
 }

.btn-primary { background: #3b82f6; color: white; }
.btn-primary:hover { background: #2563eb; }

.btn-secondary { background: #6b7280; color: white; }
.btn-secondary:hover { background: #4b5563; }

.btn-success { background: #10b981; color: white; }
.btn-success:hover { background: #059669; }

.btn-danger { background: #ef4444; color: white; }
.btn-danger:hover { background: #dc2626; }

/* Grid de contenido */
.content-grid {
    display: grid;
    grid-template-columns: 1fr 2fr;
    gap: 1.5rem;
}

/* Cards */
.card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    margin-bottom: 1.5rem;
}

.card h3 {
    font-size: 1.25rem;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 1rem 0;
    border-bottom: 2px solid #f3f4f6;
    padding-bottom: 0.5rem;
}

/* Sección de imagen */
.image-container {
    position: relative;
    margin-bottom: 1rem;
}

 .image-container img {
     width: 100%;
     height: auto;
     max-height: 400px;
     object-fit: contain;
     border-radius: 8px;
     border: 2px solid #e5e7eb;
 }

.upload-container {
    margin-top: 1rem;
    padding: 1rem;
    border: 2px dashed #d1d5db;
    border-radius: 8px;
    text-align: center;
}

.file-input {
    display: none;
}

.file-label {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    color: #6b7280;
}

.file-label:hover {
    color: #3b82f6;
}

 .btn-maps {
     display: inline-flex;
     align-items: center;
     gap: 0.5rem;
     padding: 0.75rem 1rem;
     background: #4285f4;
     color: white;
     text-decoration: none;
     border-radius: 8px;
     font-weight: 600;
 }

.btn-maps:hover {
    background: #3367d6;
}

/* Campos de formulario */
.field-group {
    margin-bottom: 1rem;
}

.field-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.field-group label {
    display: block;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}

 .field-group input,
 .field-group textarea,
 .field-group select {
     width: 100%;
     padding: 0.75rem;
     border: 2px solid #e5e7eb;
     border-radius: 8px;
     font-size: 0.95rem;
 }

.field-group input:focus,
.field-group textarea:focus,
.field-group select:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.campo-lectura {
    background: #f9fafb !important;
    color: #6b7280 !important;
    cursor: not-allowed;
}

.campo-edicion {
    background: white !important;
    color: #1f2937 !important;
    cursor: text;
}

.checkbox-container {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.checkbox-label {
    margin: 0;
    font-weight: normal;
    cursor: pointer;
}

.field-value {
    display: block;
    padding: 0.75rem;
    background: #f9fafb;
    border-radius: 8px;
    color: #6b7280;
    font-size: 0.95rem;
}

.field-help {
    display: block;
    color: #6b7280;
    font-size: 0.8rem;
    margin-top: 0.5rem;
    font-style: italic;
}

/* Modal */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal-content {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    max-width: 400px;
    width: 90%;
    text-align: center;
}

.modal-content h3 {
    color: #ef4444;
    margin-bottom: 1rem;
}

.modal-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-top: 1.5rem;
}

/* Utilidades */
.hidden {
    display: none !important;
}

.icon {
    font-size: 1.1em;
}

/* Responsive */
@media (max-width: 768px) {
    .territorio-detail-container {
        padding: 0.5rem;
    }
    
    .detail-header {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
    
    .territorio-title {
        flex-direction: column;
        text-align: center;
    }
    
    .numero-circle {
        width: 3rem;
        height: 3rem;
        font-size: 1.25rem;
    }
    
    .title-content h1 {
        font-size: 1.5rem;
    }
    
    .action-buttons {
        justify-content: center;
    }
    
    .content-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .field-row {
        grid-template-columns: 1fr;
    }
    
    .btn-primary, .btn-secondary, .btn-success, .btn-danger {
        padding: 0.6rem 1rem;
        font-size: 0.85rem;
    }
    
    .card {
        padding: 1rem;
    }
    
    .modal-content {
        margin: 1rem;
        padding: 1.5rem;
    }
    
    .modal-buttons {
        flex-direction: column;
    }
}

@media (max-width: 480px) {
    .action-buttons {
        grid-template-columns: 1fr 1fr;
        display: grid;
        gap: 0.5rem;
        width: 100%;
    }
    
    .btn-primary, .btn-secondary, .btn-success, .btn-danger {
        padding: 0.5rem 0.75rem;
        font-size: 0.8rem;
        justify-content: center;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnEditar = document.getElementById('btn-editar');
    const btnCancelar = document.getElementById('btn-cancelar');
    const btnGuardar = document.getElementById('btn-guardar');
    const btnEliminar = document.getElementById('btn-eliminar');
    const form = document.getElementById('form-territorio');
    const uploadContainer = document.getElementById('upload-container');
    
    // Modal
    const modalEliminar = document.getElementById('modal-eliminar');
    const confirmarEliminar = document.getElementById('confirmar-eliminar');
    const cancelarEliminar = document.getElementById('cancelar-eliminar');
    const formEliminar = document.getElementById('form-eliminar');
    
    // Campos editables
    const camposEditables = [
        'numero', 'nombre', 'descripcion', 'coordenadas_lat', 'coordenadas_lng',
        'estado', 'activo', 'notas'
    ];
    
    // Estado original para cancelar
    let estadoOriginal = {};
    
    // Guardar estado original
    function guardarEstadoOriginal() {
        camposEditables.forEach(campo => {
            const elemento = document.getElementById(campo);
            if (elemento) {
                if (elemento.type === 'checkbox') {
                    estadoOriginal[campo] = elemento.checked;
                } else {
                    estadoOriginal[campo] = elemento.value;
                }
            }
        });
    }
    
    // Restaurar estado original
    function restaurarEstadoOriginal() {
        camposEditables.forEach(campo => {
            const elemento = document.getElementById(campo);
            if (elemento && estadoOriginal.hasOwnProperty(campo)) {
                if (elemento.type === 'checkbox') {
                    elemento.checked = estadoOriginal[campo];
                } else {
                    elemento.value = estadoOriginal[campo];
                }
            }
        });
    }
    
    // Cambiar modo edición
    function toggleModoEdicion(modoEdicion) {
        camposEditables.forEach(campo => {
            const elemento = document.getElementById(campo);
            if (elemento) {
                if (modoEdicion) {
                    elemento.removeAttribute('readonly');
                    elemento.removeAttribute('disabled');
                    elemento.classList.remove('campo-lectura');
                    elemento.classList.add('campo-edicion');
                } else {
                    elemento.setAttribute('readonly', true);
                    if (elemento.tagName === 'SELECT' || elemento.type === 'checkbox') {
                        elemento.setAttribute('disabled', true);
                    }
                    elemento.classList.remove('campo-edicion');
                    elemento.classList.add('campo-lectura');
                }
            }
        });
        
        // Mostrar/ocultar upload de imagen
        if (modoEdicion) {
            uploadContainer.classList.remove('hidden');
        } else {
            uploadContainer.classList.add('hidden');
        }
        
        // Cambiar visibilidad de botones
        btnEditar.classList.toggle('hidden', modoEdicion);
        btnCancelar.classList.toggle('hidden', !modoEdicion);
        btnGuardar.classList.toggle('hidden', !modoEdicion);
    }
    
    // Event listeners
    btnEditar.addEventListener('click', function() {
        guardarEstadoOriginal();
        toggleModoEdicion(true);
    });
    
    btnCancelar.addEventListener('click', function() {
        restaurarEstadoOriginal();
        toggleModoEdicion(false);
    });
    
    btnGuardar.addEventListener('click', function() {
        form.submit();
    });
    
    btnEliminar.addEventListener('click', function() {
        modalEliminar.classList.remove('hidden');
    });
    
    cancelarEliminar.addEventListener('click', function() {
        modalEliminar.classList.add('hidden');
    });
    
    confirmarEliminar.addEventListener('click', function() {
        formEliminar.submit();
    });
    
    // Cerrar modal con escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            modalEliminar.classList.add('hidden');
        }
    });
    
    // Preview de imagen
    const inputImagen = document.getElementById('imagen');
    const imagenTerritorio = document.getElementById('imagen-territorio');
    
    inputImagen.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imagenTerritorio.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });
    
    // Inicializar en modo lectura
    guardarEstadoOriginal();
    toggleModoEdicion(false);
});
</script>

@endif
@endsection 