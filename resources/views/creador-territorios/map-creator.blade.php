@extends('layouts.app')

@section('title', 'Creador de Territorios - Mapa Interactivo')

@push('styles')
<!-- Leaflet CSS debe cargarse en el head -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
      crossorigin=""
      onload="console.log('✅ Leaflet CSS cargado')"/>
@endpush

@section('content')

<style>
/* Estilos específicos del creador de mapa */
.map-creator-container {
    display: grid;
    grid-template-columns: 1fr 450px;
    gap: 1.5rem;
    min-height: calc(100vh - 200px);
}

.map-section {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    overflow: hidden;
}

.map-header {
    padding: 1.5rem;
    border-bottom: 2px solid #e2e8f0;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.map-header h1 {
    margin: 0 0 0.5rem 0;
    font-size: 1.75rem;
    font-weight: 700;
}

.map-header p {
    margin: 0;
    opacity: 0.9;
    font-size: 0.95rem;
}

.map-controls {
    padding: 1rem 1.5rem;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    gap: 1rem;
    align-items: center;
    flex-wrap: wrap;
}

.search-box {
    flex: 1;
    min-width: 250px;
    display: flex;
    gap: 0.5rem;
    position: relative;
}

.search-box input {
    flex: 1;
    padding: 0.65rem 1rem;
    border: 2px solid #cbd5e1;
    border-radius: 8px;
    font-size: 0.95rem;
}

.search-box input:focus {
    outline: none;
    border-color: #667eea;
}

.search-suggestions {
    position: absolute;
    top: 100%;
    left: 0;
    right: 60px;
    background: white;
    border: 2px solid #667eea;
    border-radius: 8px;
    margin-top: 0.25rem;
    max-height: 300px;
    overflow-y: auto;
    z-index: 1000;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    display: none;
}

.search-suggestions.active {
    display: block;
}

.suggestion-item {
    padding: 0.75rem 1rem;
    cursor: pointer;
    border-bottom: 1px solid #e2e8f0;
    transition: background 0.15s;
}

.suggestion-item:last-child {
    border-bottom: none;
}

.suggestion-item:hover {
    background: #f1f5f9;
}

.suggestion-name {
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 0.25rem;
}

.suggestion-address {
    font-size: 0.85rem;
    color: #64748b;
}

.btn-search {
    padding: 0.65rem 1.5rem;
    background: #667eea;
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-search:hover {
    background: #5568d3;
}

.color-selector {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.color-option {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    border: 3px solid transparent;
    cursor: pointer;
    transition: all 0.2s;
}

.color-option:hover {
    transform: scale(1.1);
}

.color-option.active {
    border-color: #1e293b;
    transform: scale(1.15);
    box-shadow: 0 0 0 2px white, 0 0 0 4px #1e293b;
}

#map-canvas {
    width: 100%;
    height: 600px;
    background: #e5e7eb;
    position: relative;
}

#map-canvas .leaflet-container {
    background: #e5e7eb;
}

.map-loading {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    z-index: 1000;
    text-align: center;
}

.map-info-overlay {
    position: absolute;
    bottom: 20px;
    left: 20px;
    background: white;
    padding: 1rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    z-index: 1000;
    font-size: 0.85rem;
}

.map-info-overlay h4 {
    margin: 0 0 0.5rem 0;
    font-size: 0.9rem;
    color: #1e293b;
}

.map-info-overlay p {
    margin: 0.25rem 0;
    color: #64748b;
}

@keyframes loading {
    0% { width: 0%; }
    50% { width: 100%; }
    100% { width: 0%; }
}

.preview-section {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
}

.preview-header {
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #e2e8f0;
}

.preview-header h2 {
    margin: 0 0 0.5rem 0;
    font-size: 1.25rem;
    color: #1e293b;
}

.form-group {
    margin-bottom: 1rem;
}

.form-label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #475569;
    font-size: 0.9rem;
}

.form-input {
    width: 100%;
    padding: 0.65rem 1rem;
    border: 2px solid #cbd5e1;
    border-radius: 8px;
    font-size: 0.95rem;
}

.form-input:focus {
    outline: none;
    border-color: #667eea;
}

.territory-card-preview {
    flex: 1;
    border: 2px solid #000;
    background: white;
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 1rem;
    min-height: 500px;
}

.card-header-preview {
    padding: 1rem;
    border-bottom: 2px solid #000;
}

.card-title-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.card-title-text {
    font-size: 0.95rem;
    font-weight: bold;
    color: #000;
}

.territory-circle {
    width: 45px;
    height: 45px;
    background: #dc2626;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 1.2rem;
}

.card-locality {
    display: flex;
    justify-content: space-between;
    font-size: 0.85rem;
    color: #000;
}

.card-map-area {
    height: 350px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: white;
    position: relative;
}

#preview-canvas {
    max-width: 100%;
    max-height: 100%;
}

.card-footer-preview {
    padding: 1rem;
    border-top: 2px solid #000;
    position: relative;
}

.zone-badge {
    position: absolute;
    top: -15px;
    right: 20px;
    background: white;
    border: 2px solid #000;
    padding: 0.25rem 0.75rem;
    font-size: 0.8rem;
    font-weight: bold;
}

.card-instructions {
    text-align: center;
    font-style: italic;
    font-size: 0.85rem;
    margin-bottom: 0.75rem;
}

.card-text {
    font-size: 0.75rem;
    line-height: 1.4;
    text-align: justify;
    margin-bottom: 0.75rem;
}

.card-footer-codes {
    display: flex;
    justify-content: space-between;
    font-size: 0.7rem;
    color: #666;
}

.action-buttons {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.btn {
    padding: 0.85rem 1.5rem;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 0.95rem;
}

.btn-primary {
    background: #10b981;
    color: white;
}

.btn-primary:hover {
    background: #059669;
}

.btn-secondary {
    background: #6366f1;
    color: white;
}

.btn-secondary:hover {
    background: #4f46e5;
}

.btn-outline {
    background: white;
    color: #475569;
    border: 2px solid #cbd5e1;
}

.btn-outline:hover {
    background: #f8fafc;
    border-color: #94a3b8;
}

.info-box {
    padding: 1rem;
    background: #f0f9ff;
    border: 2px solid #bae6fd;
    border-radius: 8px;
    margin-bottom: 1rem;
}

.info-box h4 {
    margin: 0 0 0.5rem 0;
    color: #0c4a6e;
    font-size: 0.9rem;
}

.info-box ul {
    margin: 0;
    padding-left: 1.25rem;
    font-size: 0.85rem;
    color: #075985;
}

.info-box li {
    margin-bottom: 0.25rem;
}

.selection-count {
    padding: 0.5rem 1rem;
    background: #dbeafe;
    border-radius: 8px;
    font-size: 0.9rem;
    color: #1e40af;
    font-weight: 600;
}

/* Responsive breakpoints */
@media (max-width: 1200px) {
    .map-creator-container {
        grid-template-columns: 1fr 400px;
        gap: 1rem;
    }
}

@media (max-width: 1024px) {
    .map-creator-container {
        grid-template-columns: 1fr;
    }

    .preview-section {
        order: -1; /* Mostrar vista previa arriba en tablets */
    }

    .map-controls {
        flex-direction: column;
        align-items: stretch;
    }

    .search-box {
        flex-direction: column;
    }

    .search-box input {
        width: 100%;
    }

    .color-selector {
        justify-content: center;
        flex-wrap: wrap;
    }

    .selection-count {
        text-align: center;
    }

    #map-canvas {
        height: 400px;
    }
}

@media (max-width: 768px) {
    .map-header h1 {
        font-size: 1.5rem;
    }

    .map-header p {
        font-size: 0.875rem;
    }

    .map-controls {
        padding: 0.75rem;
    }

    .map-tools {
        width: 100%;
        justify-content: center;
    }

    .map-tool-btn {
        font-size: 0.8rem;
        padding: 0.5rem 0.75rem;
    }

    #map-canvas {
        height: 350px;
    }

    .preview-section {
        padding: 1rem;
    }

    .territory-card-preview {
        min-height: 400px;
    }

    .card-map-area {
        height: 280px;
    }

    .action-buttons .btn {
        width: 100%;
        justify-content: center;
    }

    .info-box {
        font-size: 0.85rem;
    }
}

@media (max-width: 480px) {
    .map-header h1 {
        font-size: 1.25rem;
    }

    .map-header p {
        font-size: 0.8rem;
    }

    .btn-search,
    .map-tool-btn,
    .btn-outline {
        font-size: 0.75rem;
        padding: 0.5rem;
        white-space: nowrap;
    }

    .color-option {
        width: 32px;
        height: 32px;
    }

    .selection-count {
        font-size: 0.8rem;
        padding: 0.4rem 0.75rem;
    }

    #map-canvas {
        height: 300px;
    }

    .territory-card-preview {
        min-height: 350px;
    }

    .card-header-preview {
        padding: 0.75rem;
    }

    .card-title-text {
        font-size: 0.8rem;
    }

    .territory-circle {
        width: 38px;
        height: 38px;
        font-size: 1rem;
    }

    .card-locality {
        font-size: 0.75rem;
        flex-direction: column;
        gap: 0.25rem;
    }

    .card-map-area {
        height: 250px;
    }

    .card-footer-preview {
        padding: 0.75rem;
    }

    .form-input {
        font-size: 0.9rem;
    }
}
</style>

<div class="map-creator-container">
    <!-- Sección del Mapa -->
    <div class="map-section">
        <div class="map-header">
            <h1>🗺️ Creador de Territorios Interactivo</h1>
            <p>Selecciona edificios en el mapa para crear un nuevo territorio</p>
        </div>

        <div class="map-controls">
            <div class="search-box">
                <input type="text"
                       id="locality-search"
                       class="form-input"
                       value="Santa Coloma de Gramenet"
                       placeholder="Localidad"
                       style="flex: 0 0 200px;">
                <input type="text"
                       id="address-search"
                       placeholder="Ej: Carrer, Plaza, Avenida..."
                       oninput="searchAddressAutocomplete()"
                       onkeypress="if(event.key==='Enter') selectFirstSuggestion()">
                <button class="btn-search" onclick="selectFirstSuggestion()">🔍</button>
                <div id="search-suggestions" class="search-suggestions"></div>
            </div>

            <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap; justify-content: space-between; width: 100%;">
                <div class="color-selector">
                    <span style="font-size: 0.85rem; font-weight: 600; color: #475569;">Color:</span>
                    <div class="color-option active" data-color="#FFEB3B" style="background: #FFEB3B;" onclick="selectColor('#FFEB3B', this)" title="Amarillo"></div>
                    <div class="color-option" data-color="#FF5722" style="background: #FF5722;" onclick="selectColor('#FF5722', this)" title="Rojo"></div>
                    <div class="color-option" data-color="#4CAF50" style="background: #4CAF50;" onclick="selectColor('#4CAF50', this)" title="Verde"></div>
                    <div class="color-option" data-color="#2196F3" style="background: #2196F3;" onclick="selectColor('#2196F3', this)" title="Azul"></div>
                    <div class="color-option" data-color="#9C27B0" style="background: #9C27B0;" onclick="selectColor('#9C27B0', this)" title="Morado"></div>
                </div>

                <div class="selection-count" id="selection-count">
                    0 edificios
                </div>
            </div>

            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                <button class="btn-outline" onclick="clearSelection()" style="padding: 0.65rem 1rem; flex: 1; min-width: 120px;">
                    🗑️ Limpiar
                </button>

                <button class="btn-outline" onclick="reloadBuildings()" style="padding: 0.65rem 1rem; flex: 1; min-width: 120px;">
                    🔄 Recargar
                </button>
            </div>
        </div>

        <div id="map-canvas">
            <!-- Loading overlay -->
            <div class="map-loading" id="map-loading">
                <h3>🗺️ Cargando mapa...</h3>
                <p>Por favor espera un momento</p>
                <div style="margin-top: 1rem;">
                    <div style="width: 200px; height: 4px; background: #e5e7eb; border-radius: 2px;">
                        <div style="width: 0%; height: 100%; background: #667eea; border-radius: 2px; animation: loading 2s ease-in-out infinite;"></div>
                    </div>
                </div>
            </div>

            <div class="map-info-overlay" id="map-info" style="display: none;">
                <h4>💡 Cómo usar:</h4>
                <p>• Haz clic en edificios para seleccionarlos</p>
                <p>• Usa la rueda del ratón para hacer zoom</p>
                <p>• Arrastra para moverte por el mapa</p>
            </div>
        </div>
    </div>

    <!-- Sección de Vista Previa -->
    <div class="preview-section">
        <div class="preview-header">
            <h2>👁️ Vista Previa</h2>
        </div>

        <div class="form-group">
            <label class="form-label">Número del Territorio</label>
            <input type="number"
                   id="territory-number"
                   class="form-input"
                   value="215"
                   min="1"
                   oninput="updatePreview()">
        </div>

        <div class="territory-card-preview">
            <div class="card-header-preview">
                <div class="card-title-row">
                    <span class="card-title-text">Tarjeta de mapa del territorio</span>
                    <div class="territory-circle" id="preview-number">215</div>
                </div>
                <div class="card-locality">
                    <span><strong>Localidad:</strong> <u style="color: #666;">Santa Coloma de Gramenet</u></span>
                    <span><strong>Terr. núm.:</strong> ........................</span>
                </div>
            </div>

            <div class="card-map-area">
                <canvas id="preview-canvas" width="400" height="320"></canvas>
            </div>

            <div class="card-footer-preview">
                <div class="zone-badge">CENTRO</div>
                <div class="card-instructions">
                    (Pega el mapa arriba o dibuja el territorio)
                </div>
                <p class="card-text">
                    Por favor, mantén esta tarjeta en el sobre. No manches, marques ni dobles.
                    Cada vez que hayas trabajado completamente el territorio, infórmalo al hermano encargado de los territorios.
                </p>
                <div class="card-footer-codes">
                    <span>S-12-S 6/72</span>
                    <span>Printed in Britain</span>
                </div>
            </div>
        </div>

        <div class="info-box">
            <h4>💡 Cómo usar:</h4>
            <ul>
                <li>Busca la zona en Santa Coloma</li>
                <li>Haz clic en edificios para seleccionarlos</li>
                <li>Elige un color para la forma</li>
                <li>Guarda el territorio cuando estés listo</li>
            </ul>
        </div>

        <div class="action-buttons">
            <button class="btn btn-primary" onclick="saveTerritoryShape()">
                💾 Guardar Territorio
            </button>
            <button class="btn btn-secondary" onclick="downloadPreview()">
                📥 Descargar Vista Previa
            </button>
            <button class="btn btn-outline" onclick="resetCreator()">
                🔄 Reiniciar
            </button>
        </div>
    </div>
</div>

<script>
console.log('🔧 Iniciando carga de dependencias...');

// Variables globales
window.territoriosApp = {
    map: null,
    selectedBuildings: [],
    selectedColor: '#FFEB3B',
    drawnShapes: [],
    currentTerritoryNumber: 215,
    appBaseUrl: '{{ url("/") }}'
};

// Obtener CSRF token
function getCsrfToken() {
    const metaTag = document.querySelector('meta[name="csrf-token"]');
    return metaTag ? metaTag.getAttribute('content') : '';
}

// Función para cargar Leaflet de forma controlada
function cargarLeaflet() {
    return new Promise((resolve, reject) => {
        console.log('📦 Cargando Leaflet...');

        // Verificar si ya está cargado
        if (typeof window.L !== 'undefined' && typeof window.L.map === 'function') {
            console.log('✅ Leaflet ya estaba cargado');
            resolve();
            return;
        }

        // Cargar script dinámicamente
        const script = document.createElement('script');
        script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
        script.crossOrigin = 'anonymous';

        let intentos = 0;
        const maxIntentos = 10;

        script.onload = function() {
            console.log('📦 Script Leaflet descargado');
            console.log('🔍 Verificando disponibilidad...');

            // Esperar un poco y verificar varias veces
            const verificarLeaflet = setInterval(() => {
                intentos++;
                console.log(`  Intento ${intentos}/${maxIntentos}: typeof window.L = ${typeof window.L}`);

                if (typeof window.L !== 'undefined') {
                    console.log('  ✅ window.L existe!');
                    console.log('  ✅ Propiedades:', Object.keys(window.L).slice(0, 10).join(', '));

                    if (typeof window.L.map === 'function') {
                        clearInterval(verificarLeaflet);
                        console.log('✅ Leaflet cargado exitosamente!');
                        console.log('✅ Versión:', window.L.version || 'desconocida');
                        resolve();
                    } else {
                        console.warn('  ⚠️ window.L existe pero L.map no es función');
                        console.warn('  ⚠️ typeof L.map:', typeof window.L.map);
                    }
                }

                if (intentos >= maxIntentos) {
                    clearInterval(verificarLeaflet);
                    console.error('❌ Timeout: Leaflet no se cargó después de', maxIntentos, 'intentos');
                    console.error('❌ Estado final: typeof window.L =', typeof window.L);
                    console.error('❌ window.L =', window.L);
                    reject(new Error('Leaflet no disponible después de cargar (timeout)'));
                }
            }, 100); // Verificar cada 100ms
        };

        script.onerror = function(error) {
            console.error('❌ Error al cargar script Leaflet:', error);
            console.error('❌ URL:', script.src);
            reject(new Error('No se pudo cargar Leaflet desde CDN'));
        };

        document.head.appendChild(script);
        console.log('📦 Script Leaflet agregado al DOM');
        console.log('📦 URL:', script.src);
    });
}

// Función de inicialización principal
async function inicializarAplicacion() {
    console.log('🚀 Inicializando aplicación...');

    const loadingEl = document.getElementById('map-loading');

    try {
        // Cargar Leaflet primero
        await cargarLeaflet();

        console.log('✅ Todas las dependencias cargadas');

        // Ocultar loading
        if (loadingEl) {
            loadingEl.style.display = 'none';
        }

        // Inicializar mapa
        initMap();
        updatePreview();

    } catch (error) {
        console.error('❌ Error fatal:', error);

        if (loadingEl) {
            loadingEl.innerHTML = `
                <h3 style="color: #ef4444;">❌ Error al cargar</h3>
                <p>${error.message}</p>
                <p style="margin-top: 1rem;">
                    <button onclick="location.reload()"
                            style="padding: 0.5rem 1rem; background: #3b82f6; color: white; border: none; border-radius: 6px; cursor: pointer;">
                        🔄 Reintentar
                    </button>
                </p>
            `;
        }

        showNotification('❌ Error: ' + error.message, 'error');
    }
}

// Iniciar cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', inicializarAplicacion);
} else {
    inicializarAplicacion();
}


// Aliases para acceso más fácil
let map, selectedBuildings, selectedColor, currentTerritoryNumber, appBaseUrl;

function actualizarAliases() {
    map = window.territoriosApp.map;
    selectedBuildings = window.territoriosApp.selectedBuildings;
    selectedColor = window.territoriosApp.selectedColor;
    currentTerritoryNumber = window.territoriosApp.currentTerritoryNumber;
    appBaseUrl = window.territoriosApp.appBaseUrl;
}

function initMap() {
    console.log('🗺️ Inicializando mapa...');

    try {
        // Verificar que L existe y tiene el método map
        console.log('🔍 Verificando Leaflet...');
        console.log('  - typeof window.L:', typeof window.L);
        console.log('  - window.L:', window.L);

        if (typeof window.L === 'undefined') {
            throw new Error('Leaflet (L) no está definido');
        }

        if (typeof window.L.map !== 'function') {
            console.error('  - L.map:', window.L.map);
            throw new Error('L.map no es una función (tipo: ' + typeof window.L.map + ')');
        }

        console.log('✅ L.map está disponible');

        // Coordenadas de Santa Coloma de Gramenet
        const santaColoma = [41.4534, 2.2081];

        console.log('📍 Creando instancia del mapa...');

        // Crear mapa usando window.L
        window.territoriosApp.map = window.L.map('map-canvas', {
            center: santaColoma,
            zoom: 16,
            zoomControl: true,
            tap: true,
            tapTolerance: 15
        });

        // Actualizar alias global
        actualizarAliases();

        console.log('✅ Mapa creado exitosamente');
        console.log('  - Instancia:', map);

        // Vista satelital usando Esri World Imagery
        window.L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community',
            maxZoom: 19,
            minZoom: 13
        }).addTo(map);

        // Capa opcional de etiquetas (nombres de calles) sobre el satélite
        window.L.tileLayer('https://{s}.basemaps.cartocdn.com/light_only_labels/{z}/{x}/{y}.png', {
            attribution: '&copy; CARTO',
            maxZoom: 19,
            pane: 'shadowPane'
        }).addTo(map);

        console.log('✅ Tiles agregadas');

        // Agregar marcador de ubicación inicial
        window.L.marker(santaColoma).addTo(map)
            .bindPopup('📍 Santa Coloma de Gramenet')
            .openPopup();

        // Cargar edificios del área después de un breve delay
        setTimeout(() => {
            loadBuildings();
        }, 500);

        console.log('Mapa inicializado correctamente');
        showNotification('✅ Mapa cargado. Haz clic en edificios para seleccionarlos', 'success');

        // Mostrar overlay de ayuda después de 2 segundos
        setTimeout(() => {
            const overlay = document.getElementById('map-info');
            if (overlay) {
                overlay.style.display = 'block';
                // Ocultar después de 5 segundos
                setTimeout(() => {
                    overlay.style.display = 'none';
                }, 5000);
            }
        }, 2000);

    } catch (error) {
        console.error('Error al inicializar mapa:', error);
        showNotification('❌ Error al cargar el mapa: ' + error.message, 'error');
    }
}

// Recargar edificios del área actual
function reloadBuildings() {
    console.log('Recargando edificios...');

    // Remover edificios existentes
    map.eachLayer(layer => {
        if (layer instanceof window.L.Polygon && layer.buildingData) {
            map.removeLayer(layer);
        }
    });

    // Limpiar selección
    selectedBuildings = [];
    updateSelectionCount();
    updatePreview();

    // Cargar nuevos edificios
    loadBuildings();
}

// Cargar edificios usando Overpass API
async function loadBuildings() {
    console.log('🏗️ Cargando edificios...');

    try {
        const bounds = map.getBounds();
        const south = bounds.getSouth();
        const west = bounds.getWest();
        const north = bounds.getNorth();
        const east = bounds.getEast();

        console.log(`📍 Área de búsqueda: ${south.toFixed(4)}, ${west.toFixed(4)}, ${north.toFixed(4)}, ${east.toFixed(4)}`);

        const query = `
            [out:json][timeout:25];
            (
                way["building"](${south},${west},${north},${east});
            );
            out geom;
        `;

        showNotification('🔄 Cargando edificios del área...', 'info');

        const response = await fetch('https://overpass-api.de/api/interpreter', {
            method: 'POST',
            body: query
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        console.log('📦 Respuesta de Overpass API:', data);

        if (data.elements && data.elements.length > 0) {
            console.log(`✅ ${data.elements.length} edificios encontrados en OSM`);
            renderBuildings(data.elements);
            showNotification(`✅ ${data.elements.length} edificios cargados. Haz clic para seleccionar`, 'success');
        } else {
            console.log('⚠️ No se encontraron edificios en esta área, usando ejemplos');
            loadExampleBuildings();
        }
    } catch (error) {
        console.error('❌ Error cargando edificios:', error);
        console.log('📦 Usando edificios de ejemplo como fallback');
        loadExampleBuildings();
    }
}

// Renderizar edificios en el mapa
function renderBuildings(buildings) {
    console.log(`🏗️ Renderizando ${buildings.length} edificios...`);
    let rendered = 0;

    buildings.forEach((building, index) => {
        if (building.geometry && building.geometry.length > 0) {
            const coords = building.geometry.map(node => [node.lat, node.lon]);

            const polygon = window.L.polygon(coords, {
                color: '#3b82f6',
                fillColor: '#dbeafe',
                fillOpacity: 0.5,
                weight: 2
            }).addTo(map);

            polygon.buildingData = building;
            polygon.originalStyle = {
                color: '#3b82f6',
                fillColor: '#dbeafe',
                fillOpacity: 0.5
            };

            polygon.on('click', function(e) {
                console.log('💥 Click en edificio real OSM', building.id);
                toggleBuildingSelection(this);
                window.L.DomEvent.stopPropagation(e);
            });

            polygon.on('mouseover', function() {
                if (!this.selected) {
                    this.setStyle({ fillOpacity: 0.7, weight: 3 });
                }
            });

            polygon.on('mouseout', function() {
                if (!this.selected) {
                    this.setStyle({ fillOpacity: 0.5, weight: 2 });
                }
            });

            // Tooltip con info del edificio
            const name = building.tags?.name || building.tags?.['addr:street'] || `Edificio ${building.id}`;
            polygon.bindTooltip(`🏢 ${name}`, {
                permanent: false,
                direction: 'center'
            });

            rendered++;
        }
    });

    console.log(`✅ ${rendered} edificios renderizados correctamente`);
}

// Cargar edificios de ejemplo si falla la API
function loadExampleBuildings() {
    console.log('🏢 Cargando edificios de ejemplo...');
    console.log('   Mapa disponible:', map ? 'SÍ' : 'NO');
    console.log('   window.L disponible:', window.L ? 'SÍ' : 'NO');

    const exampleBuildings = [
        // Edificio 1 - Norte
        [[41.4542, 2.2075], [41.4543, 2.2078], [41.4541, 2.2079], [41.4540, 2.2076]],
        // Edificio 2 - Centro
        [[41.4536, 2.2083], [41.4537, 2.2085], [41.4535, 2.2086], [41.4534, 2.2084]],
        // Edificio 3 - Sur
        [[41.4532, 2.2079], [41.4533, 2.2081], [41.4531, 2.2082], [41.4530, 2.2080]],
        // Edificio 4 - Este
        [[41.4538, 2.2090], [41.4539, 2.2092], [41.4537, 2.2093], [41.4536, 2.2091]],
        // Edificio 5 - Oeste
        [[41.4530, 2.2070], [41.4531, 2.2072], [41.4529, 2.2073], [41.4528, 2.2071]],
        // Edificio 6
        [[41.4534, 2.2088], [41.4535, 2.2090], [41.4533, 2.2091], [41.4532, 2.2089]],
        // Edificio 7
        [[41.4528, 2.2082], [41.4529, 2.2084], [41.4527, 2.2085], [41.4526, 2.2083]],
        // Edificio 8
        [[41.4540, 2.2086], [41.4541, 2.2088], [41.4539, 2.2089], [41.4538, 2.2087]]
    ];

    let count = 0;
    exampleBuildings.forEach((coords, index) => {
        console.log(`   📍 Creando edificio ejemplo ${index + 1}...`);
        const polygon = window.L.polygon(coords, {
            color: '#3b82f6',
            fillColor: '#dbeafe',
            fillOpacity: 0.5,
            weight: 2
        }).addTo(map);

        polygon.buildingData = { id: `example-${index}`, name: `Edificio ${index + 1}` };
        polygon.originalStyle = {
            color: '#3b82f6',
            fillColor: '#dbeafe',
            fillOpacity: 0.5
        };

        polygon.on('click', function(e) {
            console.log('💥 Click event fired en edificio', index + 1);
            toggleBuildingSelection(this);
            window.L.DomEvent.stopPropagation(e);
        });

        polygon.on('mouseover', function() {
            console.log('👆 Hover en edificio', index + 1);
            if (!this.selected) {
                this.setStyle({ fillOpacity: 0.7, weight: 3 });
            }
        });

        polygon.on('mouseout', function() {
            if (!this.selected) {
                this.setStyle({ fillOpacity: 0.5, weight: 2 });
            }
        });

        // Tooltip
        polygon.bindTooltip(`🏢 Edificio ${index + 1}`, {
            permanent: false,
            direction: 'center'
        });

        count++;
    });

    console.log(`${count} edificios de ejemplo cargados`);
    showNotification(`✅ ${count} edificios de ejemplo cargados. Haz clic para seleccionar`, 'success');
}

// Alternar selección de edificio
function toggleBuildingSelection(polygon) {
    console.log('🖱️ Click en edificio detectado', polygon);
    console.log('   Estado actual:', polygon.selected ? 'SELECCIONADO' : 'NO SELECCIONADO');

    if (polygon.selected) {
        // Deseleccionar
        console.log('   → Deseleccionando edificio');
        polygon.setStyle(polygon.originalStyle);
        polygon.selected = false;
        selectedBuildings = selectedBuildings.filter(b => b !== polygon);
    } else {
        // Seleccionar
        console.log('   → Seleccionando edificio con color:', selectedColor);
        polygon.setStyle({
            color: selectedColor,
            fillColor: selectedColor,
            fillOpacity: 0.7,
            weight: 3
        });
        polygon.selected = true;
        selectedBuildings.push(polygon);
    }

    console.log('   Total seleccionados:', selectedBuildings.length);
    updateSelectionCount();
    updatePreview();
}

// Actualizar contador de selección
function updateSelectionCount() {
    const count = selectedBuildings.length;
    const text = window.innerWidth <= 768 ?
        `${count} edificio${count !== 1 ? 's' : ''}` :
        `${count} edificio${count !== 1 ? 's' : ''} seleccionado${count !== 1 ? 's' : ''}`;
    document.getElementById('selection-count').textContent = text;
}

// Seleccionar color
function selectColor(color, element) {
    selectedColor = color;

    document.querySelectorAll('.color-option').forEach(opt => {
        opt.classList.remove('active');
    });
    element.classList.add('active');

    // Actualizar edificios ya seleccionados
    selectedBuildings.forEach(polygon => {
        polygon.setStyle({
            color: selectedColor,
            fillColor: selectedColor,
            fillOpacity: 0.7
        });
    });

    updatePreview();
}

// Limpiar selección
function clearSelection() {
    selectedBuildings.forEach(polygon => {
        polygon.setStyle(polygon.originalStyle);
        polygon.selected = false;
    });

    selectedBuildings = [];
    updateSelectionCount();
    updatePreview();
}

// Variables para autocompletado
let searchTimeout = null;
let currentSuggestions = [];

// Buscar direcciones con autocompletado (mientras escribes)
async function searchAddressAutocomplete() {
    clearTimeout(searchTimeout);

    const address = document.getElementById('address-search').value.trim();
    const locality = document.getElementById('locality-search').value || 'Santa Coloma de Gramenet';
    const suggestionsDiv = document.getElementById('search-suggestions');

    // Si el campo está vacío, ocultar sugerencias
    if (address.length < 3) {
        suggestionsDiv.classList.remove('active');
        currentSuggestions = [];
        return;
    }

    // Esperar 500ms después de que el usuario deje de escribir
    searchTimeout = setTimeout(async () => {
        try {
            const response = await fetch(`${appBaseUrl}/api/territorios/buscar-direccion`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    address: address,
                    locality: locality
                })
            });

            const responseData = await response.json();

            if (responseData.success && responseData.results && responseData.results.length > 0) {
                currentSuggestions = responseData.results;
                showSuggestions(responseData.results);
            } else {
                suggestionsDiv.classList.remove('active');
                currentSuggestions = [];
            }
        } catch (error) {
            console.error('Error en autocompletado:', error);
            suggestionsDiv.classList.remove('active');
            currentSuggestions = [];
        }
    }, 500);
}

// Mostrar sugerencias en el dropdown
function showSuggestions(results) {
    const suggestionsDiv = document.getElementById('search-suggestions');
    suggestionsDiv.innerHTML = '';

    results.slice(0, 5).forEach((result, index) => {
        const item = document.createElement('div');
        item.className = 'suggestion-item';
        item.innerHTML = `
            <div class="suggestion-name">${result.display_name.split(',')[0]}</div>
            <div class="suggestion-address">${result.display_name}</div>
        `;
        item.onclick = () => selectSuggestion(result);
        suggestionsDiv.appendChild(item);
    });

    suggestionsDiv.classList.add('active');
}

// Seleccionar una sugerencia
function selectSuggestion(result) {
    const suggestionsDiv = document.getElementById('search-suggestions');
    suggestionsDiv.classList.remove('active');

    // Mover el mapa a la ubicación seleccionada
    const lat = parseFloat(result.lat);
    const lon = parseFloat(result.lon);

    if (map) {
        map.setView([lat, lon], 18);

        // Agregar marcador temporal
        if (window.tempMarker) {
            map.removeLayer(window.tempMarker);
        }

        window.tempMarker = window.L.marker([lat, lon], {
            icon: window.L.divIcon({
                className: 'custom-marker',
                html: '<div style="background: #667eea; color: white; padding: 8px 12px; border-radius: 20px; font-weight: bold; box-shadow: 0 2px 4px rgba(0,0,0,0.3);">📍 ' + result.display_name.split(',')[0] + '</div>',
                iconSize: [150, 40]
            })
        }).addTo(map);

        showNotification('✅ Ubicación encontrada', 'success');

        // Cargar edificios en esta área
        setTimeout(() => {
            reloadBuildings();
        }, 500);
    }
}

// Seleccionar la primera sugerencia (al presionar Enter o botón)
function selectFirstSuggestion() {
    if (currentSuggestions.length > 0) {
        selectSuggestion(currentSuggestions[0]);
    } else {
        searchAddress();
    }
}

// Ocultar sugerencias al hacer clic fuera
document.addEventListener('click', (e) => {
    const searchBox = document.querySelector('.search-box');
    if (searchBox && !searchBox.contains(e.target)) {
        document.getElementById('search-suggestions').classList.remove('active');
    }
});

// Buscar dirección (función original - ahora usada como fallback)
async function searchAddress() {
    const address = document.getElementById('address-search').value;
    const locality = document.getElementById('locality-search').value || 'Santa Coloma de Gramenet';

    if (!address) {
        showNotification('⚠️ Por favor introduce una dirección', 'warning');
        return;
    }

    try {
        showNotification('🔍 Buscando dirección...', 'info');

        // Usar endpoint del servidor para evitar CORS
        const response = await fetch(`${appBaseUrl}/api/territorios/buscar-direccion`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                address: address,
                locality: locality
            })
        });

        const responseData = await response.json();

        if (!response.ok) {
            throw new Error(responseData.message || 'Error en la búsqueda');
        }

        if (responseData.success && responseData.results && responseData.results.length > 0) {
            const result = responseData.results[0];

            // Verificar que esté en Santa Coloma (aproximado)
            const lat = parseFloat(result.lat);
            const lon = parseFloat(result.lon);

            // Bounding box aproximado de Santa Coloma de Gramenet
            const inSantaColoma = (lat >= 41.43 && lat <= 41.47) && (lon >= 2.19 && lon <= 2.23);

            if (!inSantaColoma) {
                showNotification('⚠️ La dirección no parece estar en Santa Coloma de Gramenet', 'warning');
            }

            map.setView([lat, lon], 18);

            // Remover marcadores anteriores de búsqueda
            map.eachLayer(layer => {
                if (layer instanceof L.Marker && layer.isSearchMarker) {
                    map.removeLayer(layer);
                }
            });

            // Agregar nuevo marcador
            const marker = L.marker([lat, lon]).addTo(map);
            marker.isSearchMarker = true;
            marker.bindPopup(`📍 ${result.display_name}`).openPopup();

            showNotification('✅ Dirección encontrada', 'success');

            // Recargar edificios del área
            setTimeout(() => {
                loadBuildings();
            }, 500);

        } else {
            showNotification('❌ No se encontró la dirección', 'error');
        }
    } catch (error) {
        console.error('Error en búsqueda:', error);
        showNotification('⚠️ Error en la búsqueda: ' + error.message, 'error');
    }
}

// Actualizar vista previa
function updatePreview() {
    const territoryNumber = document.getElementById('territory-number').value;
    document.getElementById('preview-number').textContent = territoryNumber;
    currentTerritoryNumber = territoryNumber;

    const canvas = document.getElementById('preview-canvas');
    const ctx = canvas.getContext('2d');

    // Limpiar canvas
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, canvas.width, canvas.height);

    if (selectedBuildings.length === 0) {
        // Mostrar mensaje si no hay selección
        ctx.fillStyle = '#94a3b8';
        ctx.font = '16px Arial';
        ctx.textAlign = 'center';
        ctx.fillText('Selecciona edificios en el mapa', canvas.width / 2, canvas.height / 2 - 10);
        ctx.font = '14px Arial';
        ctx.fillText('para ver la vista previa', canvas.width / 2, canvas.height / 2 + 15);
        return;
    }

    // Obtener todos los puntos de todos los edificios seleccionados
    let allPoints = [];
    selectedBuildings.forEach(polygon => {
        const latlngs = polygon.getLatLngs()[0];
        latlngs.forEach(latlng => {
            allPoints.push(latlng);
        });
    });

    if (allPoints.length === 0) return;

    // Calcular bounding box
    let minLat = allPoints[0].lat, maxLat = allPoints[0].lat;
    let minLng = allPoints[0].lng, maxLng = allPoints[0].lng;

    allPoints.forEach(point => {
        minLat = Math.min(minLat, point.lat);
        maxLat = Math.max(maxLat, point.lat);
        minLng = Math.min(minLng, point.lng);
        maxLng = Math.max(maxLng, point.lng);
    });

    const padding = 40;
    const width = canvas.width - padding * 2;
    const height = canvas.height - padding * 2;

    const latRange = maxLat - minLat;
    const lngRange = maxLng - minLng;

    const scale = Math.min(width / lngRange, height / latRange);

    // Dibujar cada edificio
    ctx.fillStyle = selectedColor;
    ctx.strokeStyle = '#000000';
    ctx.lineWidth = 2;

    selectedBuildings.forEach(polygon => {
        const latlngs = polygon.getLatLngs()[0];

        ctx.beginPath();
        latlngs.forEach((latlng, index) => {
            const x = padding + (latlng.lng - minLng) * scale;
            const y = canvas.height - padding - (latlng.lat - minLat) * scale;

            if (index === 0) {
                ctx.moveTo(x, y);
            } else {
                ctx.lineTo(x, y);
            }
        });
        ctx.closePath();
        ctx.fill();
        ctx.stroke();
    });

    // Dibujar número del territorio en el centro
    const centerX = padding + (lngRange / 2) * scale;
    const centerY = canvas.height - padding - (latRange / 2) * scale;

    ctx.fillStyle = '#000000';
    ctx.font = 'bold 48px Arial';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillText(territoryNumber, centerX, centerY);
}

// Guardar territorio
async function saveTerritoryShape() {
    if (selectedBuildings.length === 0) {
        showNotification('⚠️ Selecciona al menos un edificio', 'warning');
        return;
    }

    const territoryNumber = currentTerritoryNumber;

    // Recopilar coordenadas de todos los edificios
    let shapeData = [];
    selectedBuildings.forEach(polygon => {
        const latlngs = polygon.getLatLngs()[0];
        const coords = latlngs.map(latlng => ({
            lat: latlng.lat,
            lng: latlng.lng
        }));
        shapeData.push(coords);
    });

    // Preparar datos para enviar
    const data = {
        numero: territoryNumber,
        color: selectedColor,
        formas: shapeData
    };

    try {
        const response = await fetch(`${appBaseUrl}/api/territorios/guardar-forma`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
            showNotification(`✅ Territorio #${territoryNumber} guardado exitosamente`, 'success');

            // Opcional: redirigir después de 2 segundos
            setTimeout(() => {
                window.location.href = `/territorios/${territoryNumber}`;
            }, 2000);
        } else {
            showNotification('❌ Error al guardar el territorio', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('❌ Error al guardar el territorio', 'error');
    }
}

// Descargar vista previa
function downloadPreview() {
    const canvas = document.getElementById('preview-canvas');
    const link = document.createElement('a');
    link.download = `territorio-${currentTerritoryNumber}.png`;
    link.href = canvas.toDataURL();
    link.click();

    showNotification('📥 Vista previa descargada', 'success');
}

// Reiniciar creador
function resetCreator() {
    clearSelection();
    document.getElementById('territory-number').value = 215;
    currentTerritoryNumber = 215;
    updatePreview();
    map.setView([41.4534, 2.2081], 16);
}

// Mostrar notificaciones
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        color: white;
        font-weight: 600;
        z-index: 10000;
        animation: slideIn 0.3s ease;
    `;

    switch(type) {
        case 'success':
            notification.style.background = '#10b981';
            break;
        case 'error':
            notification.style.background = '#ef4444';
            break;
        case 'warning':
            notification.style.background = '#f59e0b';
            break;
        default:
            notification.style.background = '#3b82f6';
    }

    document.body.appendChild(notification);

    setTimeout(() => {
        notification.remove();
    }, 3000);
}
</script>
@endsection
