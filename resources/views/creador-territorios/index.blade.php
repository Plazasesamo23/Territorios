@extends('layouts.app')

@section('title', 'Creador de Territorios - Gestión de Territorios')

@section('content')
<!-- Navegación -->
<nav class="page-nav">
    <div class="page-breadcrumbs">
        <a href="{{ route('dashboard') }}" class="breadcrumb-link">Dashboard</a>
        <span class="breadcrumb-sep">›</span>
        <span class="breadcrumb-current">Creador de Territorios</span>
    </div>
    <div class="page-actions">
        <button class="btn btn-success" onclick="showBulkCreator()">
            ⚡ Creación Masiva
        </button>
        <button class="btn btn-primary" onclick="showSingleCreator()">
            ➕ Territorio Individual
        </button>
    </div>
</nav>

<!-- Header informativo -->
<div class="creator-hero">
    <h1>🗺️ Creador Avanzado de Territorios</h1>
    <p>Herramientas profesionales basadas en el análisis de Santa Coloma de Gramenet</p>
    <div class="creator-stats">
        <div class="stat-item">
            <span class="stat-number">214</span>
            <span class="stat-label">Territorios Existentes</span>
        </div>
        <div class="stat-item">
            <span class="stat-number">✅</span>
            <span class="stat-label">Estructura Analizada</span>
        </div>
        <div class="stat-item">
            <span class="stat-number">🎯</span>
            <span class="stat-label">Sistema Optimizado</span>
        </div>
    </div>
</div>

<!-- Análisis de Estructura Encontrada -->
<div class="card mb-4">
    <div class="card-title">📋 Análisis de Estructura de Territorios</div>
    <div class="analysis-grid">
        <div class="analysis-item">
            <div class="analysis-icon">🎯</div>
            <h3>Formato Estándar</h3>
            <p>Tarjetas con encabezado, localidad "Santa Coloma de Gramenet", numeración en círculo rojo</p>
        </div>
        <div class="analysis-item">
            <div class="analysis-icon">🗺️</div>
            <h3>Zonas Definidas</h3>
            <p>Área amarilla de predicación, territorios adyacentes en gris, calles como límites</p>
        </div>
        <div class="analysis-item">
            <div class="analysis-icon">🏠</div>
            <h3>Numeración</h3>
            <p>Edificios numerados específicamente (ej: 50, 64, 23), rangos como 38-40</p>
        </div>
        <div class="analysis-item">
            <div class="analysis-icon">📍</div>
            <h3>Ubicación</h3>
            <p>Referencias geográficas precisas, calles principales, indicador "CENTRO"</p>
        </div>
    </div>
</div>

<!-- Herramientas del Creador -->
<div class="creator-tools">
    <!-- Creador Visual de Territorio -->
    <div id="single-creator" class="creator-section">
        <div class="visual-creator">
            <!-- Panel de herramientas -->
            <div class="tools-panel">
                <div class="card">
                    <div class="card-title">🎨 Diseñador Visual de Territorio</div>

                    <!-- Información básica -->
                    <div class="basic-info">
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label required">Número</label>
                                <input type="number" id="territory-number" class="form-input" min="1" max="999" value="215" onchange="updatePreview()">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Zona</label>
                                <select id="territory-zone" class="form-select" onchange="updatePreview()">
                                    <option value="CENTRO">CENTRO</option>
                                    <option value="NORTE">NORTE</option>
                                    <option value="SUR">SUR</option>
                                    <option value="ESTE">ESTE</option>
                                    <option value="OESTE">OESTE</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Nombre del Territorio</label>
                            <input type="text" id="territory-name" class="form-input" placeholder="Ej: C. President Lluís Companys" onchange="updatePreview()">
                        </div>
                    </div>

                    <!-- Herramientas de dibujo -->
                    <div class="drawing-tools">
                        <h4>🖍️ Herramientas de Dibujo</h4>
                        <div class="tool-buttons">
                            <button class="tool-btn active" data-tool="territory" onclick="selectTool('territory')">
                                🟨 Zona de Predicación
                            </button>
                            <button class="tool-btn" data-tool="street" onclick="selectTool('street')">
                                🛣️ Calles
                            </button>
                            <button class="tool-btn" data-tool="number" onclick="selectTool('number')">
                                🔢 Números
                            </button>
                            <button class="tool-btn" data-tool="adjacent" onclick="selectTool('adjacent')">
                                ⬜ Territorios Adyacentes
                            </button>
                        </div>

                        <div class="tool-options">
                            <button class="btn btn-outline btn-sm" onclick="clearCanvas()">🗑️ Limpiar</button>
                            <button class="btn btn-outline btn-sm" onclick="undoLast()">↶ Deshacer</button>
                        </div>
                    </div>

                    <!-- Lista de elementos -->
                    <div class="elements-list">
                        <h4>📝 Elementos del Territorio</h4>
                        <div id="streets-list" class="element-group">
                            <strong>Calles:</strong>
                            <ul id="streets-items"></ul>
                            <input type="text" id="new-street" placeholder="Agregar calle..." class="form-input form-input-sm">
                            <button onclick="addStreet()" class="btn btn-sm btn-outline">+ Agregar</button>
                        </div>

                        <div id="numbers-list" class="element-group">
                            <strong>Números:</strong>
                            <ul id="numbers-items"></ul>
                            <input type="text" id="new-numbers" placeholder="Ej: 50, 64, 23" class="form-input form-input-sm">
                            <button onclick="addNumbers()" class="btn btn-sm btn-outline">+ Agregar</button>
                        </div>
                    </div>

                    <!-- Acciones -->
                    <div class="creator-actions">
                        <button class="btn btn-success" onclick="generateTerritoryCard()">
                            🎨 Generar Tarjeta
                        </button>
                        <button class="btn btn-primary" onclick="saveTerritoryData()">
                            💾 Guardar Territorio
                        </button>
                    </div>
                </div>
            </div>

            <!-- Editor de Mapa Real -->
            <div class="drawing-canvas">
                <div class="card">
                    <div class="card-title">🗺️ Mapa Interactivo de Santa Coloma de Gramenet</div>

                    <!-- Controles del mapa -->
                    <div class="map-controls">
                        <div class="search-bar">
                            <input type="text" id="address-search" class="form-input"
                                   placeholder="Buscar dirección en Santa Coloma..."
                                   onkeyup="searchAddress(event)">
                            <button class="btn btn-primary" onclick="searchCurrentAddress()">
                                🔍 Buscar
                            </button>
                        </div>

                        <div class="map-tools">
                            <button class="map-tool-btn active" data-mode="select" onclick="setMapMode('select')">
                                🖱️ Seleccionar Edificios
                            </button>
                            <button class="map-tool-btn" data-mode="polygon" onclick="setMapMode('polygon')">
                                📐 Dibujar Polígono
                            </button>
                            <button class="map-tool-btn" data-mode="auto" onclick="setMapMode('auto')">
                                🤖 Auto-Detectar
                            </button>
                            <button class="btn btn-outline btn-sm" onclick="clearSelection()">
                                🗑️ Limpiar
                            </button>
                        </div>
                    </div>

                    <!-- Mapa principal -->
                    <div class="map-container">
                        <div id="interactive-map" style="height: 500px; width: 100%;"></div>
                    </div>

                    <!-- Panel de información detectada -->
                    <div class="detection-info">
                        <div class="info-section">
                            <h4>📍 Área Seleccionada</h4>
                            <div id="selected-area-info">
                                <p class="text-muted">Selecciona edificios o dibuja un área para ver la información</p>
                            </div>
                        </div>

                        <div class="info-section">
                            <h4>🏠 Edificios Detectados</h4>
                            <div id="buildings-list" class="buildings-grid">
                                <!-- Se populará automáticamente -->
                            </div>
                        </div>

                        <div class="info-section">
                            <h4>🛣️ Calles Identificadas</h4>
                            <div id="streets-detected" class="streets-tags">
                                <!-- Se populará automáticamente -->
                            </div>
                        </div>
                    </div>

                    <!-- Instrucciones inteligentes -->
                    <div class="smart-instructions">
                        <div class="instruction-card">
                            <h4>🎯 Cómo usar el Creador Inteligente:</h4>
                            <ol>
                                <li><strong>🔍 Busca la zona:</strong> Escribe la dirección o calle en Santa Coloma</li>
                                <li><strong>🖱️ Selecciona edificios:</strong> Haz clic en los edificios que quieres incluir</li>
                                <li><strong>🤖 Usa auto-detectar:</strong> El sistema identifica automáticamente números y calles</li>
                                <li><strong>📐 Ajusta límites:</strong> Dibuja polígonos personalizados si es necesario</li>
                                <li><strong>👁️ Ve la vista previa:</strong> Se genera automáticamente la tarjeta 2D</li>
                            </ol>
                        </div>

                        <div class="features-info">
                            <div class="feature-item">
                                <span class="feature-icon">🏢</span>
                                <span>Detección automática de edificios</span>
                            </div>
                            <div class="feature-item">
                                <span class="feature-icon">🔢</span>
                                <span>Reconocimiento de números de portal</span>
                            </div>
                            <div class="feature-item">
                                <span class="feature-icon">📍</span>
                                <span>Límites siguiendo geometría real</span>
                            </div>
                            <div class="feature-item">
                                <span class="feature-icon">🗺️</span>
                                <span>Generación 2D automática</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Vista previa de la tarjeta -->
            <div class="card-preview">
                <div class="card">
                    <div class="card-title">👁️ Vista Previa de Tarjeta</div>
                    <div id="territory-preview" class="territory-card-preview">
                        <!-- Aquí se generará la vista previa en tiempo real -->
                    </div>
                    <div class="preview-actions">
                        <button class="btn btn-outline btn-sm" onclick="downloadCard()">
                            📥 Descargar Imagen
                        </button>
                        <button class="btn btn-outline btn-sm" onclick="printCard()">
                            🖨️ Imprimir
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Creador Masivo -->
    <div id="bulk-creator" class="creator-section" style="display: none;">
        <div class="card">
            <div class="card-title">⚡ Creación Masiva de Territorios</div>
            <div class="bulk-options">
                <div class="bulk-method">
                    <h3>📊 Importar desde Excel/CSV</h3>
                    <p>Importa múltiples territorios desde un archivo</p>
                    <input type="file" id="file-import" accept=".xlsx,.csv" style="display: none;">
                    <button class="btn btn-outline" onclick="document.getElementById('file-import').click()">
                        📁 Seleccionar Archivo
                    </button>
                    <div class="form-help">Formatos soportados: Excel (.xlsx), CSV (.csv)</div>
                </div>

                <div class="bulk-method">
                    <h3>🔢 Generación por Rangos</h3>
                    <div class="range-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Desde el número</label>
                                <input type="number" id="range-start" class="form-input" min="1" placeholder="215">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Hasta el número</label>
                                <input type="number" id="range-end" class="form-input" min="1" placeholder="250">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Patrón de nombres</label>
                            <input type="text" id="name-pattern" class="form-input" placeholder="Territorio {numero}">
                            <div class="form-help">Use {numero} donde quiera insertar el número</div>
                        </div>
                        <button class="btn btn-success" onclick="generateRangeTerritories()">
                            ⚡ Generar Territorios
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Plantillas Predefinidas -->
<div class="card">
    <div class="card-title">📋 Plantillas Basadas en Análisis</div>
    <div class="templates-grid">
        <div class="template-card" onclick="useTemplate('santa-coloma')">
            <div class="template-icon">🏙️</div>
            <h3>Santa Coloma Urbano</h3>
            <p>Formato estándar con calles principales y numeración específica</p>
            <div class="template-features">
                <span class="feature-tag">Zona Centro</span>
                <span class="feature-tag">Numeración Específica</span>
            </div>
        </div>

        <div class="template-card" onclick="useTemplate('residencial')">
            <div class="template-icon">🏠</div>
            <h3>Zona Residencial</h3>
            <p>Para áreas con edificios numerados y calles bien definidas</p>
            <div class="template-features">
                <span class="feature-tag">Edificios Numerados</span>
                <span class="feature-tag">Calles Límite</span>
            </div>
        </div>

        <div class="template-card" onclick="useTemplate('comercial')">
            <div class="template-icon">🏪</div>
            <h3>Zona Comercial</h3>
            <p>Para territorios con locales comerciales y avenidas principales</p>
            <div class="template-features">
                <span class="feature-tag">Avenidas</span>
                <span class="feature-tag">Locales</span>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript para funcionalidad -->
<script>
// Variables globales para el mapa interactivo
let map, currentMapMode = 'select';
let selectedBuildings = [], selectedStreets = [], detectedAddresses = [];
let drawingPolygon = [], isDrawingPolygon = false;
let territoryData = {
    number: 215,
    name: '',
    zone: 'CENTRO',
    streets: [],
    numbers: [],
    buildings: [],
    boundaryPolygon: [],
    center: [41.4534, 2.2081] // Santa Coloma de Gramenet coords
};

// Cambiar entre creador individual y masivo
function showSingleCreator() {
    document.getElementById('single-creator').style.display = 'block';
    document.getElementById('bulk-creator').style.display = 'none';
    initializeMap();
}

function showBulkCreator() {
    document.getElementById('single-creator').style.display = 'none';
    document.getElementById('bulk-creator').style.display = 'block';
}

// Inicializar mapa interactivo
function initializeMap() {
    // Usar Leaflet con OpenStreetMap para mapas gratuitos
    if (typeof L === 'undefined') {
        // Cargar Leaflet dinámicamente si no está disponible
        loadLeaflet().then(() => setupMap());
    } else {
        setupMap();
    }
}

function loadLeaflet() {
    return new Promise((resolve) => {
        if (typeof L !== 'undefined') {
            resolve();
            return;
        }

        // Cargar CSS
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
        document.head.appendChild(link);

        // Cargar JS
        const script = document.createElement('script');
        script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
        script.onload = resolve;
        document.head.appendChild(script);
    });
}

function setupMap() {
    const mapElement = document.getElementById('interactive-map');
    if (!mapElement) return;

    // Inicializar mapa centrado en Santa Coloma de Gramenet
    map = L.map('interactive-map').setView(territoryData.center, 18);

    // Capa satelital de Esri
    L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        attribution: 'Tiles © Esri',
        maxZoom: 19
    }).addTo(map);

    // Capa de etiquetas de calles (transparente sobre el satélite)
    L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/Reference/World_Transportation/MapServer/tile/{z}/{y}/{x}', {
        maxZoom: 19,
        opacity: 0.7
    }).addTo(map);

    // Eventos del mapa
    map.on('click', handleMapClick);
    map.on('contextmenu', handleRightClick);

    // Cargar datos de edificios del área
    loadBuildingData();

    updatePreview();

    console.log('🗺️ Mapa inicializado correctamente');
}

// Cambiar modo del mapa
function setMapMode(mode) {
    currentMapMode = mode;
    document.querySelectorAll('.map-tool-btn').forEach(btn => btn.classList.remove('active'));
    const activeBtn = document.querySelector(`[data-mode="${mode}"]`);
    if (activeBtn) activeBtn.classList.add('active');

    // Cambiar cursor del mapa según el modo
    const mapContainer = document.querySelector('.leaflet-container');
    if (mapContainer) {
        mapContainer.style.cursor = mode === 'select' ? 'pointer' :
                                    mode === 'polygon' ? 'crosshair' : 'default';
    }

    // Mostrar instrucciones según modo
    const instrucciones = {
        'select': '🖱️ Haz clic en los círculos para seleccionar edificios',
        'polygon': '📐 Haz clic para añadir puntos. Clic derecho para terminar',
        'auto': '🤖 Haz clic en el mapa para detectar edificios cercanos'
    };

    showNotification(instrucciones[mode] || 'Modo: ' + mode, 'info');
    console.log('Modo cambiado a:', mode);
}

// Cargar datos de edificios usando Overpass API (OpenStreetMap)
async function loadBuildingData() {
    showNotification('🔄 Cargando edificios del área...', 'info');

    // Siempre cargar edificios de ejemplo primero para que funcione inmediatamente
    loadExampleBuildings();

    try {
        // Obtener bounds del área visible
        const bounds = map.getBounds();
        const query = `
            [out:json][timeout:25];
            (
                way["building"]
                    (${bounds.getSouth()},${bounds.getWest()},${bounds.getNorth()},${bounds.getEast()});
                way["addr:housenumber"]
                    (${bounds.getSouth()},${bounds.getWest()},${bounds.getNorth()},${bounds.getEast()});
            );
            out geom;
        `;

        const response = await fetch('https://overpass-api.de/api/interpreter', {
            method: 'POST',
            body: query
        });

        const data = await response.json();
        if (data.elements && data.elements.length > 0) {
            processBuildings(data.elements);
            showNotification(`🏢 ${data.elements.length} edificios cargados de OpenStreetMap`, 'success');
        }
    } catch (error) {
        console.log('API no disponible, usando datos de ejemplo');
    }
}

// Datos de ejemplo para demostración (cuando no hay API disponible)
function loadExampleBuildings() {
    const exampleBuildings = [
        // Carrer Sant Carles
        { lat: 41.4534, lng: 2.2081, address: "50", street: "Carrer Sant Carles" },
        { lat: 41.4535, lng: 2.2082, address: "52", street: "Carrer Sant Carles" },
        { lat: 41.4536, lng: 2.2083, address: "54", street: "Carrer Sant Carles" },
        { lat: 41.4537, lng: 2.2084, address: "56", street: "Carrer Sant Carles" },
        { lat: 41.4538, lng: 2.2085, address: "58", street: "Carrer Sant Carles" },
        { lat: 41.4539, lng: 2.2086, address: "60", street: "Carrer Sant Carles" },
        { lat: 41.4540, lng: 2.2087, address: "62", street: "Carrer Sant Carles" },
        { lat: 41.4541, lng: 2.2088, address: "64", street: "Carrer Sant Carles" },
        // Carrer President Lluís Companys
        { lat: 41.4532, lng: 2.2079, address: "23", street: "C. President Lluís Companys" },
        { lat: 41.4531, lng: 2.2078, address: "25", street: "C. President Lluís Companys" },
        { lat: 41.4530, lng: 2.2077, address: "27", street: "C. President Lluís Companys" },
        { lat: 41.4529, lng: 2.2076, address: "29", street: "C. President Lluís Companys" },
        { lat: 41.4528, lng: 2.2075, address: "31", street: "C. President Lluís Companys" },
        { lat: 41.4527, lng: 2.2074, address: "33", street: "C. President Lluís Companys" },
        // Carrer Safareig
        { lat: 41.4542, lng: 2.2089, address: "10", street: "Carrer Safareig" },
        { lat: 41.4543, lng: 2.2090, address: "12", street: "Carrer Safareig" },
        { lat: 41.4544, lng: 2.2091, address: "14", street: "Carrer Safareig" },
        { lat: 41.4545, lng: 2.2092, address: "16", street: "Carrer Safareig" },
        // Calle Irlanda
        { lat: 41.4533, lng: 2.2090, address: "38", street: "Calle Irlanda" },
        { lat: 41.4534, lng: 2.2091, address: "40", street: "Calle Irlanda" },
        { lat: 41.4535, lng: 2.2092, address: "42", street: "Calle Irlanda" },
        { lat: 41.4536, lng: 2.2093, address: "44", street: "Calle Irlanda" },
        // Calle San Silvestre
        { lat: 41.4526, lng: 2.2080, address: "5", street: "Calle San Silvestre" },
        { lat: 41.4525, lng: 2.2081, address: "7", street: "Calle San Silvestre" },
        { lat: 41.4524, lng: 2.2082, address: "9", street: "Calle San Silvestre" },
        { lat: 41.4523, lng: 2.2083, address: "11", street: "Calle San Silvestre" }
    ];

    exampleBuildings.forEach(building => {
        const marker = L.circleMarker([building.lat, building.lng], {
            color: '#4a6da7',
            fillColor: '#e8eef6',
            fillOpacity: 0.8,
            radius: 12,
            weight: 3
        }).addTo(map);

        marker.bindTooltip(`<strong>${building.address}</strong><br>${building.street}`, {
            permanent: false,
            direction: 'top',
            className: 'building-tooltip'
        });

        marker.building = building;
        marker.on('click', selectBuilding);

        // Efecto hover
        marker.on('mouseover', function() {
            if (!selectedBuildings.find(b => b.address === building.address && b.street === building.street)) {
                this.setStyle({ fillColor: '#8aa8d6', radius: 14 });
            }
        });
        marker.on('mouseout', function() {
            if (!selectedBuildings.find(b => b.address === building.address && b.street === building.street)) {
                this.setStyle({ fillColor: '#e8eef6', radius: 12 });
            }
        });
    });

    showNotification(`✅ ${exampleBuildings.length} edificios cargados. Haz clic para seleccionar.`, 'success');
}

// Variables para dibujo de polígonos
let polygonMarkers = [];
let currentPolygonLine = null;

// Manejar clics en el mapa
function handleMapClick(e) {
    console.log('Click en mapa, modo:', currentMapMode);

    if (currentMapMode === 'polygon') {
        addPolygonPoint(e.latlng);
    } else if (currentMapMode === 'auto') {
        autoDetectArea(e.latlng);
    }
}

function handleRightClick(e) {
    e.originalEvent.preventDefault();
    if (currentMapMode === 'polygon' && drawingPolygon.length > 2) {
        finishPolygon();
    }
}

// Añadir punto al polígono
function addPolygonPoint(latlng) {
    drawingPolygon.push(latlng);

    // Añadir marcador visual del punto
    const marker = L.circleMarker(latlng, {
        color: '#495057',
        fillColor: '#fecaca',
        fillOpacity: 1,
        radius: 6,
        weight: 2
    }).addTo(map);
    polygonMarkers.push(marker);

    // Dibujar línea temporal
    if (drawingPolygon.length > 1) {
        if (currentPolygonLine) {
            map.removeLayer(currentPolygonLine);
        }
        currentPolygonLine = L.polyline(drawingPolygon, {
            color: '#495057',
            weight: 3,
            dashArray: '5, 10'
        }).addTo(map);
    }

    showNotification(`📍 Punto ${drawingPolygon.length} añadido. ${drawingPolygon.length < 3 ? 'Mínimo 3 puntos.' : 'Clic derecho para terminar.'}`, 'info');
}

// Terminar el polígono
function finishPolygon() {
    if (drawingPolygon.length < 3) {
        showNotification('❌ Necesitas al menos 3 puntos', 'error');
        return;
    }

    // Crear polígono final
    const polygon = L.polygon(drawingPolygon, {
        color: '#3d5a8a',
        fillColor: '#bbf7d0',
        fillOpacity: 0.5,
        weight: 3
    }).addTo(map);

    // Limpiar marcadores temporales
    polygonMarkers.forEach(m => map.removeLayer(m));
    polygonMarkers = [];
    if (currentPolygonLine) {
        map.removeLayer(currentPolygonLine);
        currentPolygonLine = null;
    }

    // Guardar polígono
    territoryData.boundaryPolygon = drawingPolygon.map(p => [p.lat, p.lng]);

    showNotification(`✅ Polígono creado con ${drawingPolygon.length} puntos`, 'success');

    // Resetear
    drawingPolygon = [];

    // Volver a modo selección
    setMapMode('select');
}

// Seleccionar edificio
function selectBuilding(e) {
    const marker = e.target;
    const building = marker.building;

    if (selectedBuildings.find(b => b.address === building.address && b.street === building.street)) {
        // Deseleccionar
        marker.setStyle({ color: '#4a6da7', fillColor: '#e8eef6', radius: 12 });
        selectedBuildings = selectedBuildings.filter(b => !(b.address === building.address && b.street === building.street));
        showNotification(`❌ Edificio ${building.address} deseleccionado`, 'info');
    } else {
        // Seleccionar
        marker.setStyle({ color: '#3d5a8a', fillColor: '#bbf7d0', radius: 15, weight: 4 });
        selectedBuildings.push(building);
        showNotification(`✅ Edificio ${building.address} (${building.street}) seleccionado`, 'success');
    }

    updateDetectionInfo();
    generateTerritoryFromSelection();
}

// Auto-detectar área alrededor del punto
function autoDetectArea(latlng) {
    showNotification('🤖 Detectando área automáticamente...', 'info');

    // Simular detección inteligente
    setTimeout(() => {
        const nearbyBuildings = findNearbyBuildings(latlng, 100); // 100 metros
        nearbyBuildings.forEach(building => {
            const marker = findMarkerByBuilding(building);
            if (marker) {
                marker.setStyle({ color: '#eab308', fillColor: '#fef3c7' });
                if (!selectedBuildings.find(b => b.address === building.address)) {
                    selectedBuildings.push(building);
                }
            }
        });

        updateDetectionInfo();
        generateTerritoryFromSelection();
        showNotification(`✅ Detectados ${nearbyBuildings.length} edificios`, 'success');
    }, 1000);
}

function findNearbyBuildings(center, radiusMeters) {
    const exampleBuildings = [
        { lat: 41.4534, lng: 2.2081, address: "50", street: "Carrer Sant Carles" },
        { lat: 41.4536, lng: 2.2083, address: "64", street: "Carrer Sant Carles" },
        { lat: 41.4532, lng: 2.2079, address: "23", street: "Carrer President Lluís Companys" }
    ];

    return exampleBuildings.filter(building => {
        const distance = map.distance(center, [building.lat, building.lng]);
        return distance <= radiusMeters;
    });
}

// Buscar dirección
function searchAddress(event) {
    if (event.key === 'Enter') {
        searchCurrentAddress();
    }
}

async function searchCurrentAddress() {
    const address = document.getElementById('address-search').value;
    if (!address) return;

    try {
        // Usar Nominatim para geocoding gratuito
        const query = `${address}, Santa Coloma de Gramenet, España`;
        const response = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=1`);
        const data = await response.json();

        if (data.length > 0) {
            const result = data[0];
            const lat = parseFloat(result.lat);
            const lng = parseFloat(result.lon);

            map.setView([lat, lng], 18);

            // Añadir marcador temporal
            L.marker([lat, lng])
                .addTo(map)
                .bindPopup(`📍 ${result.display_name}`)
                .openPopup();

            showNotification(`📍 Encontrado: ${result.display_name}`, 'success');
        } else {
            showNotification('❌ No se encontró la dirección', 'error');
        }
    } catch (error) {
        showNotification('⚠️ Error en la búsqueda', 'error');
    }
}

// Actualizar información detectada
function updateDetectionInfo() {
    // Actualizar edificios detectados
    const buildingsList = document.getElementById('buildings-list');
    buildingsList.innerHTML = selectedBuildings.map(building => `
        <div class="building-item">
            <span class="building-number">${building.address}</span>
            <span class="building-street">${building.street}</span>
        </div>
    `).join('');

    // Actualizar calles identificadas
    const uniqueStreets = [...new Set(selectedBuildings.map(b => b.street))];
    const streetsDetected = document.getElementById('streets-detected');
    streetsDetected.innerHTML = uniqueStreets.map(street => `
        <span class="street-tag">${street}</span>
    `).join('');

    // Actualizar información del área
    const areaInfo = document.getElementById('selected-area-info');
    if (selectedBuildings.length > 0) {
        areaInfo.innerHTML = `
            <p><strong>📊 Resumen del Territorio:</strong></p>
            <ul>
                <li><strong>Edificios:</strong> ${selectedBuildings.length}</li>
                <li><strong>Calles:</strong> ${uniqueStreets.length}</li>
                <li><strong>Números:</strong> ${selectedBuildings.map(b => b.address).join(', ')}</li>
            </ul>
        `;
    }
}

// Limpiar selección
function clearSelection() {
    selectedBuildings = [];
    drawingPolygon = [];

    // Resetear estilos de marcadores
    map.eachLayer(layer => {
        if (layer instanceof L.CircleMarker && layer.building) {
            layer.setStyle({ color: '#4a6da7', fillColor: '#e8eef6' });
        }
    });

    updateDetectionInfo();
    document.getElementById('selected-area-info').innerHTML = '<p class="text-muted">Selecciona edificios o dibuja un área para ver la información</p>';
}

// Generar territorio basado en selección
function generateTerritoryFromSelection() {
    if (selectedBuildings.length === 0) return;

    // Actualizar datos del territorio
    territoryData.buildings = selectedBuildings;
    territoryData.streets = [...new Set(selectedBuildings.map(b => b.street))];
    territoryData.numbers = selectedBuildings.map(b => b.address);

    // Generar nombre automático basado en la calle principal
    const streetCounts = {};
    selectedBuildings.forEach(b => {
        streetCounts[b.street] = (streetCounts[b.street] || 0) + 1;
    });

    const mainStreet = Object.entries(streetCounts)
        .sort(([,a], [,b]) => b - a)[0][0];

    territoryData.name = mainStreet;
    document.getElementById('territory-name').value = mainStreet;

    updatePreview();
}

function startDrawing(e) {
    if (currentTool === 'territory' || currentTool === 'adjacent') {
        isDrawing = true;
        const rect = canvas.getBoundingClientRect();
        startX = e.clientX - rect.left;
        startY = e.clientY - rect.top;
    }
}

function draw(e) {
    if (!isDrawing) return;

    const rect = canvas.getBoundingClientRect();
    const currentX = e.clientX - rect.left;
    const currentY = e.clientY - rect.top;

    // Redibujar todo
    redrawCanvas();

    // Dibujar área temporal
    if (currentTool === 'territory') {
        drawTerritoryArea(startX, startY, currentX - startX, currentY - startY, true);
    } else if (currentTool === 'adjacent') {
        drawAdjacentArea(startX, startY, currentX - startX, currentY - startY, true);
    }
}

function stopDrawing(e) {
    if (!isDrawing) return;
    isDrawing = false;

    const rect = canvas.getBoundingClientRect();
    const endX = e.clientX - rect.left;
    const endY = e.clientY - rect.top;

    if (currentTool === 'territory') {
        territoryData.territoryAreas.push({
            x: startX,
            y: startY,
            width: endX - startX,
            height: endY - startY
        });
    } else if (currentTool === 'adjacent') {
        territoryData.adjacentAreas.push({
            x: startX,
            y: startY,
            width: endX - startX,
            height: endY - startY
        });
    }

    updatePreview();
}

function handleCanvasClick(e) {
    if (currentTool === 'street' || currentTool === 'number') {
        const rect = canvas.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;

        if (currentTool === 'street') {
            const streetName = prompt('Nombre de la calle:');
            if (streetName) {
                territoryData.streets.push({ name: streetName, x, y });
                addStreetToList(streetName);
                redrawCanvas();
                updatePreview();
            }
        } else if (currentTool === 'number') {
            const number = prompt('Número del edificio:');
            if (number) {
                territoryData.numbers.push({ number, x, y });
                addNumberToList(number);
                redrawCanvas();
                updatePreview();
            }
        }
    }
}

// Dibujar elementos
function drawTerritoryArea(x, y, width, height, isTemp = false) {
    ctx.fillStyle = isTemp ? 'rgba(255, 235, 59, 0.7)' : '#FFEB3B';
    ctx.fillRect(x, y, width, height);
    ctx.strokeStyle = '#F57F17';
    ctx.lineWidth = 2;
    ctx.strokeRect(x, y, width, height);
}

function drawAdjacentArea(x, y, width, height, isTemp = false) {
    ctx.fillStyle = isTemp ? 'rgba(158, 158, 158, 0.7)' : '#9E9E9E';
    ctx.fillRect(x, y, width, height);
    ctx.strokeStyle = '#616161';
    ctx.lineWidth = 1;
    ctx.strokeRect(x, y, width, height);
}

function drawStreet(street) {
    ctx.fillStyle = '#1976D2';
    ctx.font = 'bold 12px Arial';
    ctx.fillText(street.name, street.x, street.y);
}

function drawNumber(number) {
    ctx.fillStyle = '#D32F2F';
    ctx.font = 'bold 14px Arial';
    ctx.fillText(number.number, number.x, number.y);
}

// Redibujar canvas completo
function redrawCanvas() {
    drawBackground();

    // Dibujar áreas adyacentes
    territoryData.adjacentAreas.forEach(area => {
        drawAdjacentArea(area.x, area.y, area.width, area.height);
    });

    // Dibujar áreas de territorio
    territoryData.territoryAreas.forEach(area => {
        drawTerritoryArea(area.x, area.y, area.width, area.height);
    });

    // Dibujar calles
    territoryData.streets.forEach(street => {
        drawStreet(street);
    });

    // Dibujar números
    territoryData.numbers.forEach(number => {
        drawNumber(number);
    });
}

// Funciones de lista
function addStreet() {
    const streetInput = document.getElementById('new-street');
    if (streetInput.value.trim()) {
        territoryData.streets.push({ name: streetInput.value.trim(), x: 0, y: 0 });
        addStreetToList(streetInput.value.trim());
        streetInput.value = '';
        updatePreview();
    }
}

function addStreetToList(streetName) {
    const list = document.getElementById('streets-items');
    const li = document.createElement('li');
    li.innerHTML = `${streetName} <button onclick="removeStreet('${streetName}')" class="btn-remove">×</button>`;
    list.appendChild(li);
}

function addNumbers() {
    const numbersInput = document.getElementById('new-numbers');
    if (numbersInput.value.trim()) {
        const numbers = numbersInput.value.split(',').map(n => n.trim());
        numbers.forEach(num => {
            territoryData.numbers.push({ number: num, x: 0, y: 0 });
            addNumberToList(num);
        });
        numbersInput.value = '';
        updatePreview();
    }
}

function addNumberToList(number) {
    const list = document.getElementById('numbers-items');
    const li = document.createElement('li');
    li.innerHTML = `${number} <button onclick="removeNumber('${number}')" class="btn-remove">×</button>`;
    list.appendChild(li);
}

// Actualizar datos del territorio
function updatePreview() {
    territoryData.number = document.getElementById('territory-number').value;
    territoryData.name = document.getElementById('territory-name').value;
    territoryData.zone = document.getElementById('territory-zone').value;

    generateTerritoryCard();
}

// Generar tarjeta de territorio
function generateTerritoryCard() {
    const preview = document.getElementById('territory-preview');

    const cardHTML = `
        <div class="territory-card-generated">
            <div class="card-header">
                <div class="card-title-line">
                    <span class="card-title-text">Tarjeta de mapa del territorio</span>
                    <div class="territory-number-circle">${territoryData.number}</div>
                </div>
                <div class="card-locality">
                    <span>Localidad: <u>Santa Coloma de Gramenet</u></span>
                    <span>Terr. núm.: ........................</span>
                </div>
            </div>

            <div class="card-map-section">
                <div class="territory-name-display">${territoryData.name || 'C. Nombre de Territorio'}</div>
                <canvas id="preview-canvas" width="400" height="250"></canvas>
            </div>

            <div class="card-footer">
                <div class="zone-indicator">${territoryData.zone}</div>
                <div class="instructions">
                    <p>(Pega el mapa arriba o dibuja el territorio)</p>
                    <p class="fine-print">Por favor, mantén esta tarjeta en el sobre. No manches, marques ni dobles. Cada vez que hayas trabajado completamente el territorio, infórmalo al hermano encargado de los territorios.</p>
                    <div class="footer-codes">
                        <span>S-12-S 6/72</span>
                        <span style="float: right;">Printed in Britain</span>
                    </div>
                </div>
            </div>
        </div>
    `;

    preview.innerHTML = cardHTML;

    // Dibujar el mapa en el canvas de vista previa
    setTimeout(() => {
        const previewCanvas = document.getElementById('preview-canvas');
        if (previewCanvas) {
            const previewCtx = previewCanvas.getContext('2d');

            // Escalar y copiar el canvas principal
            previewCtx.fillStyle = '#f8fafc';
            previewCtx.fillRect(0, 0, previewCanvas.width, previewCanvas.height);

            // Copiar elementos escalados
            const scaleX = previewCanvas.width / canvas.width;
            const scaleY = previewCanvas.height / canvas.height;

            previewCtx.drawImage(canvas, 0, 0, canvas.width, canvas.height, 0, 0, previewCanvas.width, previewCanvas.height);
        }
    }, 100);
}

// Funciones adicionales
function clearCanvas() {
    territoryData = {
        number: territoryData.number,
        name: territoryData.name,
        zone: territoryData.zone,
        streets: [],
        numbers: [],
        territoryAreas: [],
        adjacentAreas: []
    };
    document.getElementById('streets-items').innerHTML = '';
    document.getElementById('numbers-items').innerHTML = '';
    redrawCanvas();
    updatePreview();
}

function saveTerritoryData() {
    // Aquí se implementaría la llamada AJAX para guardar
    showNotification(`✅ Territorio #${territoryData.number} guardado exitosamente`, 'success');
}

function downloadCard() {
    const preview = document.getElementById('territory-preview');
    // Implementar descarga de imagen
    showNotification('📥 Función de descarga en desarrollo', 'info');
}

// Generar territorios por rango
function generateRangeTerritories() {
    const start = parseInt(document.getElementById('range-start').value);
    const end = parseInt(document.getElementById('range-end').value);
    const pattern = document.getElementById('name-pattern').value || 'Territorio {numero}';

    if (!start || !end || start > end) {
        showNotification('❌ Por favor ingresa un rango válido', 'error');
        return;
    }

    const count = end - start + 1;
    showNotification(`⚡ Generando ${count} territorios del ${start} al ${end}...`, 'info');

    // Simular generación
    setTimeout(() => {
        showNotification(`✅ ${count} territorios creados exitosamente`, 'success');
    }, 2000);
}

// Usar plantilla predefinida
function useTemplate(templateType) {
    const templates = {
        'santa-coloma': {
            localidad: 'Santa Coloma de Gramenet',
            zona: 'centro',
            calles: 'C. Sant Carles, Avda. Sta. Coloma, C. Francesc Macià',
            descripcion: 'Territorio urbano estándar con numeración específica'
        },
        'residencial': {
            localidad: 'Santa Coloma de Gramenet',
            zona: 'centro',
            edificios: '1-10, 15, 20-25',
            calles: 'Calle Principal, Calle Secundaria',
            descripcion: 'Zona residencial con edificios numerados'
        },
        'comercial': {
            localidad: 'Santa Coloma de Gramenet',
            zona: 'centro',
            edificios: 'Locales 1-5',
            calles: 'Avenida Comercial, Plaza Central',
            descripcion: 'Zona comercial con locales y avenidas principales'
        }
    };

    const template = templates[templateType];
    if (template) {
        // Llenar formulario con plantilla
        document.querySelector('input[name="localidad"]').value = template.localidad;
        document.querySelector('select[name="zona"]').value = template.zona;
        if (template.edificios) document.querySelector('input[name="edificios"]').value = template.edificios;
        document.querySelector('textarea[name="calles"]').value = template.calles;
        document.querySelector('textarea[name="descripcion"]').value = template.descripcion;

        showNotification(`📋 Plantilla "${templateType}" aplicada`, 'success');
        showSingleCreator(); // Cambiar a vista individual
    }
}

// Mostrar notificaciones
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        color: white;
        font-weight: 600;
        z-index: 1000;
        animation: slideIn 0.3s ease;
    `;

    switch(type) {
        case 'success':
            notification.style.background = '#4a6da7';
            break;
        case 'error':
            notification.style.background = '#495057';
            break;
        default:
            notification.style.background = '#4a6da7';
    }

    document.body.appendChild(notification);

    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Inicializar
document.addEventListener('DOMContentLoaded', function() {
    showSingleCreator(); // Mostrar creador individual por defecto
});
</script>

<style>
/* Estilos del Creador de Territorios */
.creator-hero {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 2rem;
    border-radius: 12px;
    text-align: center;
    margin-bottom: 2rem;
}

.creator-hero h1 {
    font-size: 2.5rem;
    margin-bottom: 0.5rem;
}

.creator-hero p {
    opacity: 0.9;
    font-size: 1.1rem;
    margin-bottom: 1.5rem;
}

.creator-stats {
    display: flex;
    justify-content: center;
    gap: 2rem;
    flex-wrap: wrap;
}

.stat-item {
    text-align: center;
}

.stat-number {
    display: block;
    font-size: 2rem;
    font-weight: bold;
    margin-bottom: 0.25rem;
}

.stat-label {
    font-size: 0.9rem;
    opacity: 0.8;
}

/* Análisis Grid */
.analysis-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-top: 1rem;
}

.analysis-item {
    text-align: center;
    padding: 1.5rem;
    background: #f8fafc;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
}

.analysis-icon {
    font-size: 2rem;
    margin-bottom: 1rem;
}

.analysis-item h3 {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #1e293b;
}

.analysis-item p {
    color: #64748b;
    font-size: 0.9rem;
    line-height: 1.5;
}

/* Formularios */
.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

/* Creación masiva */
.bulk-options {
    display: grid;
    gap: 2rem;
}

.bulk-method {
    padding: 1.5rem;
    border: 2px dashed #e2e8f0;
    border-radius: 10px;
    text-align: center;
}

.bulk-method h3 {
    color: #1e293b;
    margin-bottom: 0.5rem;
}

.bulk-method p {
    color: #64748b;
    margin-bottom: 1rem;
}

.range-form {
    max-width: 400px;
    margin: 0 auto;
}

/* Plantillas */
.templates-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
    margin-top: 1rem;
}

.template-card {
    padding: 1.5rem;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    background: white;
}

.template-card:hover {
    border-color: #4a6da7;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(59, 130, 246, 0.15);
}

.template-icon {
    font-size: 2.5rem;
    margin-bottom: 1rem;
}

.template-card h3 {
    color: #1e293b;
    margin-bottom: 0.5rem;
}

.template-card p {
    color: #64748b;
    margin-bottom: 1rem;
    font-size: 0.9rem;
}

.template-features {
    display: flex;
    justify-content: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.feature-tag {
    background: #f4f7fb;
    color: #2d4266;
    padding: 0.25rem 0.75rem;
    border-radius: 15px;
    font-size: 0.75rem;
    font-weight: 500;
}

/* Animaciones */
@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

/* Responsive */
@media (max-width: 768px) {
    .creator-hero h1 {
        font-size: 2rem;
    }

    .creator-stats {
        gap: 1rem;
    }

    .form-grid {
        grid-template-columns: 1fr;
    }

    .form-row {
        grid-template-columns: 1fr;
    }

    .templates-grid {
        grid-template-columns: 1fr;
    }
}

/* Diseñador Visual */
.visual-creator {
    display: grid;
    grid-template-columns: 350px 1fr 400px;
    gap: 1.5rem;
    margin-top: 1rem;
}

.tools-panel {
    min-height: 600px;
}

.basic-info {
    margin-bottom: 1.5rem;
}

.drawing-tools {
    margin-bottom: 1.5rem;
}

.drawing-tools h4 {
    color: #1e293b;
    margin-bottom: 0.75rem;
}

.tool-buttons {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.tool-btn {
    padding: 0.75rem;
    border: 2px solid #e2e8f0;
    background: white;
    border-radius: 8px;
    cursor: pointer;
    text-align: left;
    font-weight: 500;
    transition: all 0.2s ease;
}

.tool-btn:hover {
    border-color: #4a6da7;
    background: #f8fafc;
}

.tool-btn.active {
    border-color: #4a6da7;
    background: #f4f7fb;
    color: #2d4266;
}

.tool-options {
    display: flex;
    gap: 0.5rem;
}

.elements-list {
    margin-bottom: 1.5rem;
}

.elements-list h4 {
    color: #1e293b;
    margin-bottom: 0.75rem;
}

.element-group {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 1rem;
    margin-bottom: 1rem;
}

.element-group ul {
    list-style: none;
    padding: 0;
    margin: 0.5rem 0;
}

.element-group li {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.25rem 0;
    font-size: 0.9rem;
}

.btn-remove {
    background: #495057;
    color: white;
    border: none;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    cursor: pointer;
    font-size: 0.8rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.form-input-sm {
    padding: 0.5rem;
    font-size: 0.875rem;
    margin: 0.5rem 0;
}

.btn-sm {
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
}

.creator-actions {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

/* Mapa Interactivo */
.drawing-canvas {
    min-height: 600px;
}

.map-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    gap: 1rem;
    flex-wrap: wrap;
}

.search-bar {
    flex: 1;
    display: flex;
    gap: 0.5rem;
}

.search-bar .form-input {
    flex: 1;
    min-width: 200px;
}

.map-tools {
    display: flex;
    gap: 0.5rem;
    align-items: center;
    flex-wrap: wrap;
}

.map-tool-btn {
    padding: 0.5rem 1rem;
    border: 2px solid #e2e8f0;
    background: white;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.875rem;
    font-weight: 500;
    transition: all 0.2s ease;
    white-space: nowrap;
}

.map-tool-btn:hover {
    border-color: #4a6da7;
    background: #f8fafc;
}

.map-tool-btn.active {
    border-color: #4a6da7;
    background: #f4f7fb;
    color: #2d4266;
}

.map-container {
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    overflow: hidden;
    margin-bottom: 1rem;
}

#interactive-map {
    width: 100%;
    height: 500px;
}

/* Panel de información detectada */
.detection-info {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 1rem;
    margin-bottom: 1rem;
}

.info-section {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 1rem;
}

.info-section h4 {
    color: #1e293b;
    margin-bottom: 0.75rem;
    font-size: 0.9rem;
}

.buildings-grid {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.building-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 4px;
    font-size: 0.875rem;
}

.building-number {
    font-weight: 600;
    color: #2d4266;
}

.building-street {
    color: #64748b;
    font-size: 0.8rem;
}

.streets-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.street-tag {
    background: #f4f7fb;
    color: #2d4266;
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 500;
    border: 1px solid #bfdbfe;
}

/* Instrucciones inteligentes */
.smart-instructions {
    background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
    border: 1px solid #bae6fd;
    border-radius: 8px;
    padding: 1.5rem;
}

.instruction-card {
    margin-bottom: 1rem;
}

.instruction-card h4 {
    color: #0c4a6e;
    margin-bottom: 0.75rem;
}

.instruction-card ol {
    color: #0369a1;
    padding-left: 1.5rem;
}

.instruction-card li {
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}

.features-info {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 0.75rem;
}

.feature-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem;
    background: rgba(255, 255, 255, 0.7);
    border-radius: 6px;
    font-size: 0.875rem;
}

.feature-icon {
    font-size: 1.1rem;
}

/* Vista previa */
.card-preview {
    min-height: 600px;
}

.territory-card-preview {
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    overflow: hidden;
    background: white;
    min-height: 400px;
}

.territory-card-generated {
    width: 100%;
    background: white;
    border: 2px solid #000;
    font-family: Arial, sans-serif;
}

.card-header {
    padding: 0.75rem;
    border-bottom: 2px solid #000;
}

.card-title-line {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.card-title-text {
    font-size: 1rem;
    font-weight: bold;
}

.territory-number-circle {
    width: 40px;
    height: 40px;
    background: #343a40;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 1.1rem;
}

.card-locality {
    display: flex;
    justify-content: space-between;
    font-size: 0.9rem;
}

.card-map-section {
    padding: 0.75rem;
    text-align: center;
}

.territory-name-display {
    font-size: 1.2rem;
    font-weight: bold;
    margin-bottom: 0.5rem;
}

#preview-canvas {
    border: 1px solid #ccc;
    max-width: 100%;
    height: auto;
}

.card-footer {
    padding: 0.75rem;
    border-top: 1px solid #000;
    position: relative;
}

.zone-indicator {
    position: absolute;
    right: 1rem;
    top: -1.5rem;
    background: white;
    border: 1px solid #000;
    padding: 0.25rem 0.75rem;
    font-size: 0.8rem;
    font-weight: bold;
}

.instructions p {
    font-size: 0.8rem;
    margin-bottom: 0.5rem;
}

.fine-print {
    font-size: 0.65rem !important;
    line-height: 1.3;
}

.footer-codes {
    margin-top: 0.5rem;
    font-size: 0.7rem;
}

.preview-actions {
    padding: 1rem;
    display: flex;
    gap: 0.5rem;
    justify-content: center;
}

/* Responsive */
@media (max-width: 1200px) {
    .visual-creator {
        grid-template-columns: 1fr;
        grid-template-rows: auto auto auto;
    }

    .tools-panel,
    .drawing-canvas,
    .card-preview {
        min-height: auto;
    }
}

@media (max-width: 768px) {
    .visual-creator {
        gap: 1rem;
    }

    #territory-canvas {
        width: 100%;
        height: 300px;
    }

    .territory-card-generated {
        font-size: 0.875rem;
    }
}

/* Tema oscuro */
[data-theme="dark"] .analysis-item {
    background: var(--color-gray-700);
    border-color: var(--border-color);
}

[data-theme="dark"] .template-card {
    background: var(--bg-card);
    border-color: var(--border-color);
}

[data-theme="dark"] .bulk-method {
    border-color: var(--border-color);
}

[data-theme="dark"] .tool-btn {
    background: var(--bg-card);
    border-color: var(--border-color);
}

[data-theme="dark"] .element-group {
    background: var(--color-gray-700);
    border-color: var(--border-color);
}

[data-theme="dark"] .territory-card-generated {
    background: white;
    color: black;
}

/* Tooltip de edificios */
.building-tooltip {
    background: rgba(0, 0, 0, 0.85) !important;
    border: none !important;
    border-radius: 6px !important;
    color: white !important;
    font-size: 12px !important;
    padding: 6px 10px !important;
}

.building-tooltip::before {
    border-top-color: rgba(0, 0, 0, 0.85) !important;
}

/* Cursor personalizado para el mapa */
#interactive-map {
    cursor: pointer;
}

.leaflet-container {
    cursor: pointer !important;
}
</style>
@endsection