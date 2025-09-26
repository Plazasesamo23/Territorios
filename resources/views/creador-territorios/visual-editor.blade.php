@extends('layouts.app')

@section('title', 'Editor Visual de Territorios - Gestión de Territorios')

@section('content')
<nav class="page-nav">
    <div class="page-breadcrumbs">
        <a href="{{ route('dashboard') }}" class="breadcrumb-link">Dashboard</a>
        <span class="breadcrumb-sep">›</span>
        <span class="breadcrumb-current">Editor Visual de Territorios</span>
    </div>
</nav>

<!-- Header -->
<div class="creator-hero">
    <h1>🎨 Editor Visual de Territorios</h1>
    <p>Diseña territorios manualmente con herramientas de dibujo profesionales</p>
</div>

<div style="display: grid; grid-template-columns: 250px 1fr 300px; gap: 1rem; height: 80vh;">

    <!-- Panel de Herramientas Izquierdo -->
    <div class="card" style="overflow-y: auto;">
        <div class="card-title">🛠️ Herramientas de Dibujo</div>

        <!-- Herramientas de dibujo manual -->
        <div style="padding: 1rem;">
            <h4>🎨 Dibujo Manual</h4>
            <div style="display: flex; flex-direction: column; gap: 0.5rem; margin-bottom: 1rem;">
                <button class="tool-btn active-tool" data-tool="polygon">✏️ Dibujar Polígono</button>
                <button class="tool-btn" data-tool="rectangle">📐 Rectángulo Rápido</button>
                <button class="tool-btn" data-tool="select">👆 Seleccionar/Mover</button>
            </div>

            <div style="padding: 10px; background: #f8f9fa; border-radius: 4px; font-size: 11px; margin-bottom: 1rem;">
                <strong>🖱️ Cómo usar:</strong><br>
                • Polígono: Click para cada punto, doble-click para terminar<br>
                • Rectángulo: Click y arrastra<br>
                • Seleccionar: Click en elemento para mover
            </div>

            <h4>🎨 Configuración</h4>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; margin-bottom: 1rem;">
                <button class="color-preset" onclick="setColors('#FFFF00', '#000000')">🟡 Territorio</button>
                <button class="color-preset" onclick="setColors('#B0B0B0', '#808080')">⬜ Adyacente</button>
            </div>
            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-size: 12px;">Color de relleno:</label>
                <input type="color" id="fill-color" value="#FFFF00" style="width: 100%; height: 30px;">
            </div>
            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-size: 12px;">Color de borde:</label>
                <input type="color" id="stroke-color" value="#000000" style="width: 100%; height: 30px;">
            </div>

            <h4>🔢 Añadir Números</h4>
            <div style="margin-bottom: 1rem;">
                <input type="text" id="number-text" placeholder="Ej: 23-54 o 38-40" style="width: 100%; padding: 6px; margin-bottom: 8px;">
                <button class="btn btn-sm btn-primary" onclick="addNumberToCanvas()">➕ Añadir Número</button>
            </div>
        </div>
    </div>

    <!-- Canvas Principal -->
    <div class="card" style="position: relative; overflow: hidden;">
        <div class="card-title">
            🗺️ Territorio #<input type="number" id="territory-number" value="215" min="1" max="999" style="width: 60px; border: 1px solid #ccc; padding: 2px; margin: 0 5px;">
            - <input type="text" id="territory-name" placeholder="Nombre del territorio" style="border: 1px solid #ccc; padding: 2px; width: 200px;">
        </div>

        <!-- Canvas de dibujo -->
        <div id="drawing-canvas" style="width: 100%; height: 500px; background: white; border: 2px solid #000; position: relative; cursor: crosshair; margin-top: 10px;">
            <!-- Header de la tarjeta -->
            <div id="card-header" style="padding: 12px; border-bottom: 1px solid #000; background: white; position: relative;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-weight: bold; font-size: 14px;">Tarjeta de mapa del territorio</span>
                    <div id="territory-circle" style="width: 45px; height: 45px; background: #CC0000; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 16px;">
                        215
                    </div>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 11px; margin-top: 8px;">
                    <span>Localidad: <u style="color: #666;">Santa Coloma de Gramenet</u></span>
                    <span>Terr. núm.: ........................</span>
                </div>
            </div>

            <!-- Título del territorio -->
            <div id="territory-title" style="text-align: center; padding: 15px; background: white;">
                <h1 id="display-territory-name" style="margin: 0; font-size: 24px; font-weight: bold; color: black;">
                    Nombre del Territorio
                </h1>
            </div>

            <!-- Área de dibujo -->
            <svg id="drawing-area" width="100%" height="350" style="background: white;">
                <!-- Elementos se añaden aquí dinámicamente -->
            </svg>

            <!-- Pie de la tarjeta -->
            <div style="padding: 20px; border-top: 1px solid #000; position: relative; background: white;">
                <div id="zone-badge" style="position: absolute; right: 20px; top: -12px; background: white; border: 1px solid #000; padding: 6px 12px; font-size: 12px; font-weight: bold;">
                    CENTRO
                </div>
                <div style="text-align: center; font-style: italic; font-size: 13px; margin-bottom: 15px;">
                    (Pega el mapa arriba o dibuja el territorio)
                </div>
                <p style="font-size: 11px; line-height: 1.4; text-align: justify; margin-bottom: 15px;">
                    Por favor, mantén esta tarjeta en el sobre. No manches, marques ni dobles.
                    Cada vez que hayas trabajado completamente el territorio, infórmalo al hermano encargado de los territorios.
                </p>
                <div style="display: flex; justify-content: space-between; font-size: 10px; color: #666;">
                    <span>S-12-S 6/72</span>
                    <span>Printed in Britain</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel de Elementos Derecho -->
    <div class="card" style="overflow-y: auto;">
        <div class="card-title">🧩 Elementos Arrastrables</div>

        <!-- API de calles colindantes -->
        <div style="padding: 1rem;">
            <h4>🛣️ Calle Principal</h4>
            <input type="text" id="main-street" placeholder="Ej: C. President Lluís Companys" style="width: 100%; padding: 8px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 4px;">
            <button class="btn btn-sm btn-primary" onclick="getSurroundingStreets()" style="width: 100%; margin-bottom: 15px;">🔍 Buscar Calles Colindantes</button>

            <h4>📍 Calles Colindantes</h4>
            <div id="surrounding-streets" style="max-height: 300px; overflow-y: auto; margin-bottom: 15px;">
                <div style="text-align: center; color: #666; padding: 20px; font-size: 12px;">
                    Introduce una calle principal para ver las colindantes
                </div>
            </div>

            <div style="padding: 10px; background: #e3f2fd; border-radius: 4px; font-size: 11px; margin-bottom: 15px;">
                <strong>💡 Cómo usar:</strong><br>
                1. Escribe la calle principal<br>
                2. Haz clic en "Buscar Calles Colindantes"<br>
                3. Arrastra las calles al mapa donde quieras
            </div>

            <h4 style="margin-top: 1rem;">📝 Elementos en el Mapa</h4>
            <div id="elements-list" style="max-height: 200px; overflow-y: auto;">
                <!-- Lista de elementos en el canvas -->
            </div>
        </div>
    </div>
</div>

<script>
// Estado del editor
let editorState = {
    currentTool: null,
    isDrawing: false,
    selectedElement: null,
    elements: [],
    nextId: 1
};

// Base de datos de calles con calles colindantes
const streetsDatabase = {
    'C. President Lluís Companys': {
        surrounding: ['C. Sant Joaquím', 'C. Santa Rosa', 'C. Baró', 'C. Industria']
    },
    'C. Sant Carles': {
        surrounding: ['Avda. Sta. Coloma', 'C. Francesc Macià', 'C. Valencia', 'C. Barcelona']
    },
    'Calle Irlanda': {
        surrounding: ['Calle San Silvestre', 'Rambla San Sebastián', 'Calle Rafael Casanova', 'C. Cultura']
    },
    'Av. Sta. Coloma': {
        surrounding: ['C/ Sant Joaquím', 'C. Frco. Macià', 'C. Cultura', 'C. Moragas']
    },
    'C. Santa Rosa': {
        surrounding: ['C. President Lluís Companys', 'C. Baró', 'C. Joan Maragall', 'C. Industria']
    }
};

// Estado del dibujo manual
let drawingState = {
    isDrawingPolygon: false,
    currentPolygon: [],
    isDragging: false,
    selectedStreet: null
};

// Inicializar editor
document.addEventListener('DOMContentLoaded', function() {
    initializeEditor();
    setupEventListeners();
    populateStreetSuggestions('');
});

function initializeEditor() {
    console.log('🎨 Editor Visual iniciado');

    // Actualizar número de territorio
    document.getElementById('territory-number').addEventListener('input', function() {
        document.getElementById('territory-circle').textContent = this.value;
    });

    // Actualizar nombre de territorio
    document.getElementById('territory-name').addEventListener('input', function() {
        document.getElementById('display-territory-name').textContent = this.value || 'Nombre del Territorio';
    });
}

function setupEventListeners() {
    // Herramientas de dibujo
    document.querySelectorAll('.tool-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            selectTool(this.dataset.tool);
        });
    });

    // Canvas de dibujo
    const canvas = document.getElementById('drawing-area');
    canvas.addEventListener('click', handleCanvasClick);
    canvas.addEventListener('dblclick', handleCanvasDoubleClick);
    canvas.addEventListener('mousemove', handleCanvasMouseMove);

    // Drag and drop para calles
    setupStreetDragAndDrop();

    // Actualizar zona badge
    document.getElementById('territory-number').addEventListener('input', updateZoneBadge);
}

function selectTool(tool) {
    editorState.currentTool = tool;

    // Actualizar UI
    document.querySelectorAll('.tool-btn').forEach(btn => {
        btn.classList.remove('btn-primary');
        btn.classList.add('btn-outline');
    });

    document.querySelector(`[data-tool="${tool}"]`).classList.add('btn-primary');
    document.querySelector(`[data-tool="${tool}"]`).classList.remove('btn-outline');

    showNotification(`🛠️ Herramienta "${tool}" seleccionada`, 'info');
}

function addElement(elementType) {
    const element = {
        id: editorState.nextId++,
        type: elementType,
        x: 100,
        y: 100,
        width: 100,
        height: 50,
        text: getDefaultText(elementType),
        fill: document.getElementById('fill-color').value,
        stroke: document.getElementById('stroke-color').value,
        strokeWidth: document.getElementById('stroke-width').value
    };

    editorState.elements.push(element);
    renderElement(element);
    updateElementsList();

    showNotification(`✅ ${elementType} añadido`, 'success');
}

function getDefaultText(elementType) {
    switch(elementType) {
        case 'street-text': return 'Nombre de Calle';
        case 'number': return '23';
        case 'territory': return '';
        case 'adjacent': return '';
        default: return '';
    }
}

function renderElement(element) {
    const svg = document.getElementById('drawing-area');
    let svgElement;

    switch(element.type) {
        case 'territory':
        case 'adjacent':
            svgElement = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
            svgElement.setAttribute('x', element.x);
            svgElement.setAttribute('y', element.y);
            svgElement.setAttribute('width', element.width);
            svgElement.setAttribute('height', element.height);
            svgElement.setAttribute('fill', element.fill);
            svgElement.setAttribute('stroke', element.stroke);
            svgElement.setAttribute('stroke-width', element.strokeWidth);
            break;

        case 'street-text':
        case 'number':
            svgElement = document.createElementNS('http://www.w3.org/2000/svg', 'text');
            svgElement.setAttribute('x', element.x);
            svgElement.setAttribute('y', element.y);
            svgElement.setAttribute('font-family', 'Arial, sans-serif');
            svgElement.setAttribute('font-size', element.type === 'number' ? '28' : '14');
            svgElement.setAttribute('font-weight', 'bold');
            svgElement.setAttribute('fill', 'black');
            svgElement.textContent = element.text;
            break;
    }

    svgElement.setAttribute('data-element-id', element.id);
    svgElement.style.cursor = 'move';
    svgElement.addEventListener('click', () => selectElement(element.id));

    svg.appendChild(svgElement);
}

function selectElement(elementId) {
    editorState.selectedElement = elementId;

    // Destacar elemento seleccionado
    document.querySelectorAll('[data-element-id]').forEach(el => {
        el.setAttribute('stroke', '#000');
        el.setAttribute('stroke-width', '1');
    });

    const selectedEl = document.querySelector(`[data-element-id="${elementId}"]`);
    if (selectedEl) {
        selectedEl.setAttribute('stroke', '#FF0000');
        selectedEl.setAttribute('stroke-width', '3');
    }
}

function handleCanvasClick(event) {
    if (!editorState.currentTool) return;

    const rect = event.currentTarget.getBoundingClientRect();
    const x = event.clientX - rect.left;
    const y = event.clientY - rect.top;

    if (editorState.currentTool === 'polygon') {
        addPolygonPoint(x, y);
    } else if (editorState.currentTool === 'rectangle') {
        startRectangle(x, y);
    }
}

function handleCanvasDoubleClick(event) {
    if (editorState.currentTool === 'polygon' && drawingState.isDrawingPolygon) {
        finishPolygon();
    }
}

function handleCanvasMouseMove(event) {
    if (drawingState.isDrawingPolygon && drawingState.currentPolygon.length > 0) {
        updatePolygonPreview(event);
    }
}

// Dibujo manual de polígonos
function addPolygonPoint(x, y) {
    if (!drawingState.isDrawingPolygon) {
        drawingState.isDrawingPolygon = true;
        drawingState.currentPolygon = [];
        showNotification('🖊️ Iniciando polígono. Doble-click para terminar', 'info');
    }

    drawingState.currentPolygon.push({ x, y });

    // Mostrar punto temporal
    const svg = document.getElementById('drawing-area');
    const circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
    circle.setAttribute('cx', x);
    circle.setAttribute('cy', y);
    circle.setAttribute('r', '3');
    circle.setAttribute('fill', '#FF0000');
    circle.setAttribute('class', 'polygon-point');
    svg.appendChild(circle);
}

function finishPolygon() {
    if (drawingState.currentPolygon.length < 3) {
        showNotification('❌ Necesitas al menos 3 puntos para un polígono', 'error');
        return;
    }

    const points = drawingState.currentPolygon.map(p => `${p.x},${p.y}`).join(' ');

    const element = {
        id: editorState.nextId++,
        type: 'polygon',
        points: points,
        fill: document.getElementById('fill-color').value,
        stroke: document.getElementById('stroke-color').value,
        strokeWidth: 2
    };

    editorState.elements.push(element);

    const svg = document.getElementById('drawing-area');

    // Remover puntos temporales
    document.querySelectorAll('.polygon-point').forEach(p => p.remove());

    // Crear polígono final
    const polygon = document.createElementNS('http://www.w3.org/2000/svg', 'polygon');
    polygon.setAttribute('points', points);
    polygon.setAttribute('fill', element.fill);
    polygon.setAttribute('stroke', element.stroke);
    polygon.setAttribute('stroke-width', element.strokeWidth);
    polygon.setAttribute('data-element-id', element.id);
    polygon.style.cursor = 'move';

    svg.appendChild(polygon);

    // Reset estado
    drawingState.isDrawingPolygon = false;
    drawingState.currentPolygon = [];

    updateElementsList();
    showNotification('✅ Polígono creado', 'success');
}

function addRectangle(x, y) {
    const element = {
        id: editorState.nextId++,
        type: 'rectangle',
        x: x,
        y: y,
        width: 100,
        height: 60,
        fill: document.getElementById('fill-color').value,
        stroke: document.getElementById('stroke-color').value,
        strokeWidth: document.getElementById('stroke-width').value
    };

    editorState.elements.push(element);

    const svg = document.getElementById('drawing-area');
    const rect = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
    rect.setAttribute('x', element.x);
    rect.setAttribute('y', element.y);
    rect.setAttribute('width', element.width);
    rect.setAttribute('height', element.height);
    rect.setAttribute('fill', element.fill);
    rect.setAttribute('stroke', element.stroke);
    rect.setAttribute('stroke-width', element.strokeWidth);
    rect.setAttribute('data-element-id', element.id);
    rect.style.cursor = 'move';

    svg.appendChild(rect);
    updateElementsList();
}

// API de calles colindantes
function getSurroundingStreets() {
    const mainStreet = document.getElementById('main-street').value.trim();
    const container = document.getElementById('surrounding-streets');

    if (!mainStreet) {
        showNotification('❌ Introduce una calle principal', 'error');
        return;
    }

    // Buscar calles colindantes en la base de datos
    let surroundingStreets = [];

    // Búsqueda exacta
    if (streetsDatabase[mainStreet]) {
        surroundingStreets = streetsDatabase[mainStreet].surrounding;
    } else {
        // Búsqueda parcial
        const foundKey = Object.keys(streetsDatabase).find(key =>
            key.toLowerCase().includes(mainStreet.toLowerCase()) ||
            mainStreet.toLowerCase().includes(key.toLowerCase())
        );

        if (foundKey) {
            surroundingStreets = streetsDatabase[foundKey].surrounding;
            showNotification(`📍 Encontradas calles para "${foundKey}"`, 'info');
        } else {
            container.innerHTML = `
                <div style="text-align: center; color: #e74c3c; padding: 20px; font-size: 12px;">
                    ❌ No se encontraron calles colindantes para "${mainStreet}"
                </div>
            `;
            return;
        }
    }

    // Mostrar calles colindantes arrastrables
    container.innerHTML = '';
    surroundingStreets.forEach(street => {
        const streetEl = document.createElement('div');
        streetEl.className = 'draggable-street';
        streetEl.textContent = `🛣️ ${street}`;
        streetEl.draggable = true;
        streetEl.dataset.streetName = street;

        streetEl.style.cssText = `
            padding: 10px;
            margin-bottom: 5px;
            background: #e3f2fd;
            border: 1px solid #2196f3;
            border-radius: 4px;
            cursor: grab;
            font-size: 12px;
            font-weight: 500;
            transition: all 0.2s;
        `;

        streetEl.addEventListener('mouseenter', function() {
            this.style.backgroundColor = '#bbdefb';
            this.style.transform = 'scale(1.02)';
        });

        streetEl.addEventListener('mouseleave', function() {
            this.style.backgroundColor = '#e3f2fd';
            this.style.transform = 'scale(1)';
        });

        container.appendChild(streetEl);
    });

    showNotification(`✅ Encontradas ${surroundingStreets.length} calles colindantes`, 'success');
}

// Drag and Drop para calles
function setupStreetDragAndDrop() {
    // Drag start
    document.addEventListener('dragstart', function(e) {
        if (e.target.classList.contains('draggable-street')) {
            e.dataTransfer.setData('text/plain', e.target.dataset.streetName);
            e.target.style.opacity = '0.5';
            drawingState.selectedStreet = e.target.dataset.streetName;
        }
    });

    // Drag end
    document.addEventListener('dragend', function(e) {
        if (e.target.classList.contains('draggable-street')) {
            e.target.style.opacity = '1';
        }
    });

    // Drop en canvas
    const canvas = document.getElementById('drawing-area');
    canvas.addEventListener('dragover', function(e) {
        e.preventDefault();
        canvas.style.backgroundColor = '#f0f8ff';
    });

    canvas.addEventListener('dragleave', function(e) {
        canvas.style.backgroundColor = 'white';
    });

    canvas.addEventListener('drop', function(e) {
        e.preventDefault();
        canvas.style.backgroundColor = 'white';

        if (drawingState.selectedStreet) {
            const rect = canvas.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;

            addStreetTextAtPosition(drawingState.selectedStreet, x, y);
            drawingState.selectedStreet = null;
        }
    });
}

function addStreetTextAtPosition(streetName, x, y) {
    const element = {
        id: editorState.nextId++,
        type: 'street-text',
        x: x,
        y: y,
        text: streetName,
        rotation: 0
    };

    editorState.elements.push(element);

    const svg = document.getElementById('drawing-area');
    const text = document.createElementNS('http://www.w3.org/2000/svg', 'text');
    text.setAttribute('x', x);
    text.setAttribute('y', y);
    text.setAttribute('font-family', 'Arial, sans-serif');
    text.setAttribute('font-size', '14');
    text.setAttribute('font-weight', 'bold');
    text.setAttribute('fill', 'black');
    text.setAttribute('data-element-id', element.id);
    text.style.cursor = 'move';
    text.textContent = streetName;

    // Hacer rotable con doble click
    text.addEventListener('dblclick', function() {
        const currentRotation = parseFloat(text.getAttribute('transform')?.match(/rotate\\(([^)]+)\\)/)?.[1] || 0);
        const newRotation = (currentRotation + 90) % 360;
        text.setAttribute('transform', `rotate(${newRotation} ${x} ${y})`);

        showNotification(`🔄 "${streetName}" rotada ${newRotation}°`, 'info');
    });

    svg.appendChild(text);
    updateElementsList();

    showNotification(`📍 "${streetName}" añadida al mapa`, 'success');
}

function addStreetText(streetName) {
    const element = {
        id: editorState.nextId++,
        type: 'street-text',
        x: 150,
        y: 50,
        text: streetName,
        rotation: 0
    };

    editorState.elements.push(element);

    const svg = document.getElementById('drawing-area');
    const text = document.createElementNS('http://www.w3.org/2000/svg', 'text');
    text.setAttribute('x', element.x);
    text.setAttribute('y', element.y);
    text.setAttribute('font-family', 'Arial, sans-serif');
    text.setAttribute('font-size', '14');
    text.setAttribute('font-weight', 'bold');
    text.setAttribute('fill', 'black');
    text.setAttribute('data-element-id', element.id);
    text.style.cursor = 'move';
    text.textContent = streetName;

    svg.appendChild(text);
    updateElementsList();

    showNotification(`📍 Calle "${streetName}" añadida`, 'success');
}

// Funciones adicionales
function addNumberToCanvas() {
    const numberText = document.getElementById('number-text').value.trim();

    if (!numberText) {
        showNotification('❌ Introduce un número o rango', 'error');
        return;
    }

    addNumberText(numberText, 200, 150);
    document.getElementById('number-text').value = '';
}

function setColors(fillColor, strokeColor) {
    document.getElementById('fill-color').value = fillColor;
    document.getElementById('stroke-color').value = strokeColor;
    showNotification(`🎨 Colores actualizados`, 'info');
}

function updateZoneBadge() {
    const territoryNumber = parseInt(document.getElementById('territory-number').value);
    let zone = 'CENTRO';

    // Determinar zona basada en número (simulado)
    if (territoryNumber >= 1 && territoryNumber <= 50) zone = 'CENTRO';
    else if (territoryNumber >= 51 && territoryNumber <= 100) zone = 'NORTE';
    else if (territoryNumber >= 101 && territoryNumber <= 150) zone = 'SUR';
    else if (territoryNumber >= 151 && territoryNumber <= 200) zone = 'ESTE';
    else zone = 'OESTE';

    document.getElementById('zone-badge').textContent = zone;
}

function addNumberText(numberText, x = 200, y = 150) {
    const element = {
        id: editorState.nextId++,
        type: 'number',
        x: x,
        y: y,
        text: numberText
    };

    editorState.elements.push(element);

    const svg = document.getElementById('drawing-area');
    const text = document.createElementNS('http://www.w3.org/2000/svg', 'text');
    text.setAttribute('x', x);
    text.setAttribute('y', y);
    text.setAttribute('font-family', 'Arial, sans-serif');
    text.setAttribute('font-size', '28');
    text.setAttribute('font-weight', 'bold');
    text.setAttribute('fill', 'black');
    text.setAttribute('data-element-id', element.id);
    text.setAttribute('text-anchor', 'middle');
    text.style.cursor = 'move';
    text.textContent = numberText;

    svg.appendChild(text);
    updateElementsList();

    showNotification(`🔢 Número "${numberText}" añadido`, 'success');
}

function updateElementsList() {
    const container = document.getElementById('elements-list');
    container.innerHTML = '';

    editorState.elements.forEach(element => {
        const elementEl = document.createElement('div');
        elementEl.style.cssText = `
            padding: 8px;
            margin-bottom: 4px;
            border: 1px solid #eee;
            border-radius: 4px;
            font-size: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        `;

        elementEl.innerHTML = `
            <span>${getElementIcon(element.type)} ${element.text || element.type}</span>
            <button onclick="removeElement(${element.id})" style="color: red; border: none; background: none; cursor: pointer;">🗑️</button>
        `;

        container.appendChild(elementEl);
    });
}

function getElementIcon(type) {
    const icons = {
        'territory': '🟡',
        'adjacent': '⬜',
        'street-text': '🛣️',
        'number': '🔢',
        'rectangle': '📐'
    };
    return icons[type] || '❓';
}

function removeElement(elementId) {
    editorState.elements = editorState.elements.filter(el => el.id !== elementId);

    const svgElement = document.querySelector(`[data-element-id="${elementId}"]`);
    if (svgElement) {
        svgElement.remove();
    }

    updateElementsList();
    showNotification('🗑️ Elemento eliminado', 'info');
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
        z-index: 1000;
        max-width: 300px;
    `;

    switch(type) {
        case 'success':
            notification.style.background = '#10b981';
            break;
        case 'error':
            notification.style.background = '#ef4444';
            break;
        default:
            notification.style.background = '#3b82f6';
    }

    document.body.appendChild(notification);
    setTimeout(() => notification.remove(), 3000);
}
</script>

<style>
.tool-btn, .element-btn {
    padding: 8px 12px;
    border: 1px solid #ccc;
    background: white;
    cursor: pointer;
    border-radius: 4px;
    font-size: 12px;
    transition: all 0.2s;
}

.tool-btn:hover, .element-btn:hover {
    background: #f0f0f0;
}

.tool-btn.btn-primary {
    background: #3b82f6;
    color: white;
    border-color: #3b82f6;
}

#drawing-canvas {
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.street-suggestion:hover {
    background-color: #f0f0f0 !important;
}
</style>
@endsection