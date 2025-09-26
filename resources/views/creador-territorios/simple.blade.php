@extends('layouts.app')

@section('title', 'Creador de Territorios - Gestión de Territorios')

@section('content')
<nav class="page-nav">
    <div class="page-breadcrumbs">
        <a href="{{ route('dashboard') }}" class="breadcrumb-link">Dashboard</a>
        <span class="breadcrumb-sep">›</span>
        <span class="breadcrumb-current">Creador de Territorios</span>
    </div>
</nav>

<!-- Header -->
<div class="creator-hero">
    <h1>🗺️ Creador de Territorios</h1>
    <p>Genera tarjetas profesionales idénticas al formato original de Santa Coloma de Gramenet</p>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; max-width: 1200px; margin: 0 auto;">
    <!-- Panel de herramientas -->
    <div class="card">
            <div class="card-title">🎨 Configuración del Territorio</div>

            <div class="basic-info">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label required">Número</label>
                        <input type="number" id="territory-number" class="form-input" value="215" min="1" max="999">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Zona</label>
                        <select id="territory-zone" class="form-select">
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
                    <input type="text" id="territory-name" class="form-input" placeholder="Ej: C. Sant Carles">
                    <div class="form-help">Calle principal del territorio</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Números de Edificios</label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                        <input type="number" id="building-start" class="form-input" placeholder="Desde" min="1">
                        <input type="number" id="building-end" class="form-input" placeholder="Hasta" min="1">
                    </div>
                    <div class="form-help">Rango de números de edificios (ej: del 38 al 40)</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Números Adicionales</label>
                    <input type="text" id="additional-numbers" class="form-input" placeholder="Ej: 7, 15">
                    <div class="form-help">Números sueltos adicionales (separados por comas)</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Calle Principal (Centro)</label>
                    <input type="text" id="main-street" class="form-input" placeholder="Ej: C. President Lluís Companys">
                    <div class="form-help">Calle principal donde está el territorio</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Forma del Territorio</label>
                    <div style="display: flex; gap: 0.5rem; margin-bottom: 0.5rem;">
                        <button type="button" class="btn btn-outline btn-sm" onclick="useShapeTemplate('L')">
                            📐 Forma L
                        </button>
                        <button type="button" class="btn btn-outline btn-sm" onclick="useShapeTemplate('rectangle')">
                            ⬜ Rectangular
                        </button>
                        <button type="button" class="btn btn-outline btn-sm" onclick="useShapeTemplate('triangle')">
                            🔺 Triangular
                        </button>
                        <button type="button" class="btn btn-outline btn-sm" onclick="useShapeTemplate('pentagon')">
                            ⬟ Pentagonal
                        </button>
                    </div>
                    <div class="form-help">Selecciona la forma que mejor se adapte a tu territorio</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Calles Colindantes</label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; margin-bottom: 0.5rem;">
                        <input type="text" id="street-top" class="form-input" placeholder="Calle superior">
                        <input type="text" id="street-bottom" class="form-input" placeholder="Calle inferior">
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                        <input type="text" id="street-left" class="form-input" placeholder="Calle izquierda">
                        <input type="text" id="street-right" class="form-input" placeholder="Calle derecha">
                    </div>
                    <div class="form-help">Calles que rodean el territorio</div>
                </div>
            </div>

            <div style="display: flex; gap: 0.5rem; margin-top: 1.5rem;">
                <button class="btn btn-primary" onclick="generateCard()">
                    🎨 Generar Tarjeta
                </button>
                <button class="btn btn-outline" onclick="clearForm()">
                    🔄 Limpiar
                </button>
            </div>

            <div class="elements-list">
                <h4>🏠 Plantillas Rápidas</h4>
                <div class="template-buttons">
                    <button class="btn btn-outline btn-sm" onclick="useQuickTemplate('santcarles')">
                        📍 C. Sant Carles
                    </button>
                    <button class="btn btn-outline btn-sm" onclick="useQuickTemplate('companys')">
                        📍 C. President Lluís Companys
                    </button>
                    <button class="btn btn-outline btn-sm" onclick="useQuickTemplate('irlanda')">
                        📍 Calle Irlanda
                    </button>
                </div>
            </div>
        </div>
    </div>

        <!-- Vista Previa -->
        <div class="card">
            <div class="card-title">👁️ Vista Previa de Tarjeta</div>
            <div id="card-preview" style="border: 2px solid #e2e8f0; border-radius: 8px; background: white; min-height: 400px;">
                <div style="padding: 2rem; text-align: center; color: #64748b;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">🗺️</div>
                    <p>Configure los datos del territorio y haga clic en "Generar Tarjeta" para ver la vista previa</p>
                </div>
            </div>

            <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                <button class="btn btn-outline btn-sm" onclick="downloadCard()">
                    📥 Descargar
                </button>
                <button class="btn btn-outline btn-sm" onclick="printCard()">
                    🖨️ Imprimir
                </button>
                <button class="btn btn-primary btn-sm" onclick="saveTerritory()">
                    💾 Guardar en Sistema
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Datos del territorio actual
let currentTerritory = {
    number: 215,
    name: '',
    zone: 'CENTRO',
    numbers: [],
    streets: [],
    shape: 'L' // Forma por defecto
};

// Plantillas de formas de territorios reales
const territoryShapes = {
    'L': {
        yellowZone: "180,40 400,30 420,120 320,130 320,220 220,280 180,160",
        grayZones: [
            "10,60 180,40 220,160 180,280 40,300 10,200",
            "420,30 450,50 440,200 400,220 420,120"
        ],
        numberPositions: [{ x: 360, y: 80 }, { x: 270, y: 200 }]
    },
    'rectangle': {
        yellowZone: "180,80 380,80 380,220 180,220",
        grayZones: [
            "20,50 180,50 180,250 20,250",
            "380,50 440,50 440,250 380,250"
        ],
        numberPositions: [{ x: 280, y: 130 }, { x: 280, y: 170 }]
    },
    'triangle': {
        yellowZone: "150,80 350,80 400,200 200,250 100,200",
        grayZones: [
            "50,50 150,50 100,200 50,180",
            "350,50 420,80 450,220 400,250 350,200"
        ],
        numberPositions: [{ x: 250, y: 140 }, { x: 280, y: 180 }]
    },
    'pentagon': {
        yellowZone: "180,60 350,50 400,140 320,220 150,200",
        grayZones: [
            "20,80 180,60 150,200 50,220",
            "350,50 420,70 450,180 400,140"
        ],
        numberPositions: [{ x: 280, y: 120 }, { x: 250, y: 180 }]
    }
};

// Función para usar plantilla de forma
function useShapeTemplate(shapeName) {
    currentTerritory.shape = shapeName;

    // Actualizar visualmente botones
    document.querySelectorAll('.btn-outline').forEach(btn => {
        btn.classList.remove('btn-primary');
        btn.classList.add('btn-outline');
    });

    if (event && event.target) {
        event.target.classList.remove('btn-outline');
        event.target.classList.add('btn-primary');
    }

    showNotification(`🔧 Forma "${shapeName}" seleccionada`, 'info');
}

// Usar plantilla rápida
function useQuickTemplate(template) {
    const templates = {
        'santcarles': {
            name: 'C. Sant Carles',
            start: 50,
            end: 64,
            additional: '',
            mainStreet: 'C. Sant Carles',
            streetTop: 'Avda. Sta. Coloma',
            streetBottom: 'C. Francesc Macià',
            streetLeft: 'C. Valencia',
            streetRight: 'C. Barcelona',
            shape: 'rectangle'
        },
        'companys': {
            name: 'C. President Lluís Companys',
            start: 23,
            end: 54,
            additional: '',
            mainStreet: 'C. President Lluís Companys',
            streetTop: 'C. Sant Joaquím',
            streetBottom: 'C. Santa Rosa',
            streetLeft: 'C. Sant Joaquím',
            streetRight: 'C. Baró',
            shape: 'L'
        },
        'irlanda': {
            name: 'Calle Irlanda',
            start: 38,
            end: 40,
            additional: '7',
            mainStreet: 'Calle Irlanda',
            streetTop: 'Calle Irlanda',
            streetBottom: 'Calle San Silvestre',
            streetLeft: 'Calle Rafael Casanova',
            streetRight: 'Rambla San Sebastián',
            shape: 'triangle'
        }
    };

    if (templates[template]) {
        document.getElementById('territory-name').value = templates[template].name;
        document.getElementById('building-start').value = templates[template].start;
        document.getElementById('building-end').value = templates[template].end;
        document.getElementById('additional-numbers').value = templates[template].additional;
        document.getElementById('main-street').value = templates[template].mainStreet;
        document.getElementById('street-top').value = templates[template].streetTop;
        document.getElementById('street-bottom').value = templates[template].streetBottom;
        document.getElementById('street-left').value = templates[template].streetLeft;
        document.getElementById('street-right').value = templates[template].streetRight;

        // Aplicar forma del territorio
        currentTerritory.shape = templates[template].shape;
        useShapeTemplate(templates[template].shape);

        showNotification(`📋 Plantilla "${templates[template].name}" aplicada`, 'success');

        // Generar automáticamente
        setTimeout(() => generateCard(), 500);
    }
}

// Generar tarjeta
function generateCard() {
    // Recopilar datos y generar rangos de números
    const buildingStart = parseInt(document.getElementById('building-start').value) || 0;
    const buildingEnd = parseInt(document.getElementById('building-end').value) || 0;
    const additionalNumbers = document.getElementById('additional-numbers').value.split(',').map(n => n.trim()).filter(n => n);

    let buildingNumbers = [];

    // Generar rango si ambos campos están llenos
    if (buildingStart > 0 && buildingEnd > 0 && buildingEnd >= buildingStart) {
        for (let i = buildingStart; i <= buildingEnd; i++) {
            buildingNumbers.push(i.toString());
        }
    }

    // Agregar números adicionales
    buildingNumbers = [...buildingNumbers, ...additionalNumbers];

    currentTerritory = {
        number: document.getElementById('territory-number').value,
        name: document.getElementById('territory-name').value,
        zone: document.getElementById('territory-zone').value,
        numbers: buildingNumbers,
        rangeDisplay: buildingStart && buildingEnd && buildingEnd >= buildingStart ?
            (buildingStart === buildingEnd ? buildingStart.toString() : `${buildingStart}-${buildingEnd}`) : '',
        additionalDisplay: additionalNumbers.join(', '),
        mainStreet: document.getElementById('main-street').value,
        streetTop: document.getElementById('street-top').value,
        streetBottom: document.getElementById('street-bottom').value,
        streetLeft: document.getElementById('street-left').value,
        streetRight: document.getElementById('street-right').value,
        shape: currentTerritory.shape
    };

    // COPIA EXACTA del territorio #50 (C. President Lluís Companys)
    const cardHTML = `
        <div style="width: 500px; height: 650px; font-family: Arial, sans-serif; border: 2px solid #000; background: white; margin: 0 auto;">
            <!-- Header exacto -->
            <div style="text-align: center; padding: 15px 20px; border-bottom: 1px solid #000; position: relative;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <h2 style="margin: 0; font-size: 16px; font-weight: bold; color: black;">Tarjeta de mapa del territorio</h2>
                    <div style="width: 50px; height: 50px; background: #CC0000; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 18px;">
                        ${currentTerritory.number}
                    </div>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 12px; color: black;">
                    <span><strong>Localidad:</strong> <u style="color: #666;">Santa Coloma de Gramenet</u></span>
                    <span><strong>Terr. núm.:</strong> ........................</span>
                </div>
            </div>

            <!-- Título de la calle principal -->
            <div style="text-align: center; padding: 15px; background: white;">
                <h1 style="margin: 0; font-size: 24px; font-weight: bold; color: black;">
                    ${currentTerritory.mainStreet || currentTerritory.name}
                </h1>
            </div>

            <!-- Área del mapa ADAPTABLE según forma seleccionada -->
            <div style="height: 350px; margin: 0 20px; position: relative; background: white; border: none;">

                <!-- Generación dinámica de formas -->
                <svg width="100%" height="100%" viewBox="0 0 460 350" style="position: absolute; top: 0; left: 0;">

                    <!-- Territorios grises (adyacentes) -->
                    ${territoryShapes[currentTerritory.shape].grayZones.map((points, index) => `
                        <polygon points="${points}"
                                 fill="#B0B0B0"
                                 stroke="#808080"
                                 stroke-width="1"/>
                    `).join('')}

                    <!-- Territorio amarillo principal (forma adaptable) -->
                    <polygon points="${territoryShapes[currentTerritory.shape].yellowZone}"
                             fill="#FFFF00"
                             stroke="#000000"
                             stroke-width="1"/>

                    <!-- Números del territorio en posiciones adaptadas -->
                    ${currentTerritory.rangeDisplay ? `
                        ${currentTerritory.rangeDisplay.includes('-') ? `
                            <text x="${territoryShapes[currentTerritory.shape].numberPositions[0].x}" y="${territoryShapes[currentTerritory.shape].numberPositions[0].y}"
                                  font-family="Arial, sans-serif"
                                  font-size="32"
                                  font-weight="bold"
                                  fill="black"
                                  text-anchor="middle">
                                ${currentTerritory.rangeDisplay.split('-')[1]}
                            </text>
                            <text x="${territoryShapes[currentTerritory.shape].numberPositions[1].x}" y="${territoryShapes[currentTerritory.shape].numberPositions[1].y}"
                                  font-family="Arial, sans-serif"
                                  font-size="32"
                                  font-weight="bold"
                                  fill="black"
                                  text-anchor="middle">
                                ${currentTerritory.rangeDisplay.split('-')[0]}
                            </text>
                        ` : `
                            <text x="${(territoryShapes[currentTerritory.shape].numberPositions[0].x + territoryShapes[currentTerritory.shape].numberPositions[1].x) / 2}"
                                  y="${(territoryShapes[currentTerritory.shape].numberPositions[0].y + territoryShapes[currentTerritory.shape].numberPositions[1].y) / 2}"
                                  font-family="Arial, sans-serif"
                                  font-size="32"
                                  font-weight="bold"
                                  fill="black"
                                  text-anchor="middle">
                                ${currentTerritory.rangeDisplay}
                            </text>
                        `}
                    ` : ''}

                    ${currentTerritory.additionalDisplay ? `
                        <text x="${territoryShapes[currentTerritory.shape].numberPositions[1].x}"
                              y="${territoryShapes[currentTerritory.shape].numberPositions[1].y + 40}"
                              font-family="Arial, sans-serif"
                              font-size="28"
                              font-weight="bold"
                              fill="black"
                              text-anchor="middle">
                            ${currentTerritory.additionalDisplay}
                        </text>
                    ` : ''}

                </svg>

                <!-- Nombres de calles EXACTOS como territorio #50 -->

                <!-- C. Sant Joaquím (izquierda, vertical) -->
                <div style="position: absolute; left: 8px; top: 50%; transform: translateY(-50%) rotate(90deg); transform-origin: center; font-size: 14px; font-weight: bold; color: black; white-space: nowrap;">
                    ${currentTerritory.streetLeft || 'C. Sant Joaquím'}
                </div>

                <!-- C. Baró (derecha, vertical) -->
                <div style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%) rotate(-90deg); transform-origin: center; font-size: 14px; font-weight: bold; color: black; white-space: nowrap;">
                    ${currentTerritory.streetRight || 'C. Baró'}
                </div>

                <!-- C. Santa Rosa (abajo, horizontal) -->
                <div style="position: absolute; bottom: 10px; left: 50%; transform: translateX(-50%); font-size: 14px; font-weight: bold; color: black; text-align: center;">
                    ${currentTerritory.streetBottom || 'C. Santa Rosa'}
                </div>

                <!-- Leyenda EXACTA -->
                <div style="position: absolute; top: 15px; right: 15px; display: flex; align-items: center; gap: 6px;">
                    <div style="width: 18px; height: 12px; background: #FFFF00; border: 1px solid #000; border-radius: 50%;"></div>
                    <span style="font-size: 12px; font-weight: bold; color: black;">Zona a<br>predicar</span>
                </div>
            </div>

            <!-- Sección inferior -->
            <div style="padding: 20px; position: relative; border-top: 1px solid #000;">
                <!-- Badge de zona -->
                <div style="position: absolute; right: 20px; top: -12px; background: white; border: 1px solid #000; padding: 6px 12px; font-size: 12px; font-weight: bold;">
                    ${currentTerritory.zone}
                </div>

                <!-- Instrucción central -->
                <div style="text-align: center; font-style: italic; font-size: 13px; margin-bottom: 15px;">
                    (Pega el mapa arriba o dibuja el territorio)
                </div>

                <!-- Texto de instrucciones -->
                <p style="font-size: 11px; line-height: 1.4; text-align: justify; margin-bottom: 15px;">
                    Por favor, mantén esta tarjeta en el sobre. No manches, marques ni dobles.
                    Cada vez que hayas trabajado completamente el territorio, infórmalo al hermano encargado de los territorios.
                </p>

                <!-- Pie de página -->
                <div style="display: flex; justify-content: space-between; font-size: 10px; color: #666;">
                    <span>S-12-S 6/72</span>
                    <span>Printed in Britain</span>
                </div>
            </div>
        </div>
    `;

    document.getElementById('card-preview').innerHTML = cardHTML;
    showNotification('✅ Tarjeta generada exitosamente', 'success');
}

// Guardar territorio
function saveTerritory() {
    if (!currentTerritory.name) {
        showNotification('❌ Por favor complete el nombre del territorio', 'error');
        return;
    }

    showNotification(`💾 Territorio #${currentTerritory.number} guardado en el sistema`, 'success');
}

// Descargar tarjeta
function downloadCard() {
    showNotification('📥 Función de descarga en desarrollo', 'info');
}

// Imprimir tarjeta
function printCard() {
    const content = document.getElementById('card-preview').innerHTML;
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head><title>Territorio #${currentTerritory.number}</title></head>
            <body style="margin: 0; padding: 20px;">${content}</body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
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

// Inicialización
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Creador Simple iniciado correctamente');
});
</script>
@endsection