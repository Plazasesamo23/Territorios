<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Creador de Territorios</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: #f0f2f5; }

        /* Header */
        .header {
            background: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header h1 { font-size: 1.5rem; color: #1a1a2e; }
        .header-actions { display: flex; gap: 1rem; align-items: center; }

        /* Steps */
        .steps-container {
            background: white;
            padding: 1rem 2rem;
            border-bottom: 1px solid #e0e0e0;
        }
        .steps {
            display: flex;
            justify-content: center;
            gap: 0;
            max-width: 600px;
            margin: 0 auto;
        }
        .step {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            color: #999;
            position: relative;
        }
        .step::after {
            content: '';
            position: absolute;
            right: -20px;
            width: 40px;
            height: 2px;
            background: #ddd;
        }
        .step:last-child::after { display: none; }
        .step.active { color: #e94560; }
        .step.completed { color: #10b981; }
        .step-number {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 0.875rem;
            color: white;
        }
        .step.active .step-number { background: #e94560; }
        .step.completed .step-number { background: #10b981; }
        .step-text { font-weight: 500; }

        /* Buttons */
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.875rem;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .btn-primary { background: #e94560; color: white; }
        .btn-primary:hover { background: #d63850; transform: translateY(-1px); }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-secondary:hover { background: #5a6268; }
        .btn-success { background: #10b981; color: white; }
        .btn-success:hover { background: #059669; }
        .btn-outline { background: white; border: 2px solid #e0e0e0; color: #333; }
        .btn-outline:hover { border-color: #e94560; color: #e94560; }
        .btn-lg { padding: 1rem 2rem; font-size: 1rem; }
        .btn:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }

        /* ==================== PASO 1: MAPA ==================== */
        #step1 { display: flex; height: calc(100vh - 130px); }
        #map { flex: 1; }

        .map-panel {
            width: 350px;
            background: white;
            padding: 1.5rem;
            overflow-y: auto;
            border-left: 1px solid #e0e0e0;
        }
        .panel-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .search-box {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }
        .search-box input {
            flex: 1;
            padding: 0.875rem 1rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 0.875rem;
            transition: border-color 0.2s;
        }
        .search-box input:focus {
            outline: none;
            border-color: #e94560;
        }

        .help-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.25rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
        }
        .help-card h4 { margin-bottom: 0.75rem; font-size: 1rem; }
        .help-card ol {
            margin-left: 1.25rem;
            font-size: 0.875rem;
            line-height: 1.8;
        }
        .help-card li { margin-bottom: 0.25rem; }

        .stats-card {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .stats-number {
            font-size: 3rem;
            font-weight: 700;
            color: #e94560;
            line-height: 1;
        }
        .stats-label {
            color: #666;
            font-size: 0.875rem;
            margin-top: 0.5rem;
        }
        .stats-hint {
            font-size: 0.75rem;
            color: #999;
            margin-top: 0.5rem;
        }

        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        /* ==================== PASO 2: EDITOR ==================== */
        #step2 { display: none; height: calc(100vh - 130px); }
        .editor-layout {
            display: flex;
            height: 100%;
        }

        /* Panel izquierdo - Herramientas */
        .tools-panel {
            width: 300px;
            background: white;
            border-right: 1px solid #e0e0e0;
            overflow-y: auto;
            padding: 1rem;
        }

        .tool-group {
            margin-bottom: 1.5rem;
        }
        .tool-group-title {
            font-size: 0.75rem;
            font-weight: 600;
            color: #999;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.75rem;
            padding-left: 0.5rem;
        }

        .tool-btn {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            width: 100%;
            padding: 0.875rem 1rem;
            margin-bottom: 0.5rem;
            border: 2px solid transparent;
            background: #f8f9fa;
            color: #333;
            border-radius: 10px;
            cursor: pointer;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.2s;
            text-align: left;
        }
        .tool-btn:hover {
            background: #e8f4fc;
            border-color: #e94560;
        }
        .tool-btn.active {
            background: #fef2f4;
            border-color: #e94560;
            color: #e94560;
        }
        .tool-btn .icon {
            font-size: 1.25rem;
            width: 28px;
            text-align: center;
        }
        .tool-btn .desc {
            font-size: 0.7rem;
            color: #999;
            font-weight: 400;
        }

        /* Colores */
        .color-palette {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 0.5rem;
            margin-bottom: 0.75rem;
        }
        .color-swatch {
            aspect-ratio: 1;
            border-radius: 8px;
            cursor: pointer;
            border: 3px solid transparent;
            transition: all 0.2s;
        }
        .color-swatch:hover { transform: scale(1.1); }
        .color-swatch.active {
            border-color: #333;
            box-shadow: 0 0 0 2px white, 0 0 0 4px #333;
        }

        /* Inputs */
        .input-row {
            margin-bottom: 1rem;
        }
        .input-label {
            font-size: 0.75rem;
            color: #666;
            margin-bottom: 0.5rem;
            display: block;
        }
        .text-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 0.875rem;
        }
        .text-input:focus {
            outline: none;
            border-color: #e94560;
        }

        /* Slider de rotacion */
        .rotation-control {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1rem;
        }
        .rotation-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
        }
        .rotation-value {
            font-weight: 700;
            color: #e94560;
            font-size: 1.25rem;
        }
        .rotation-slider {
            width: 100%;
            height: 8px;
            border-radius: 4px;
            -webkit-appearance: none;
            background: linear-gradient(to right, #e94560 0%, #e94560 var(--val), #ddd var(--val), #ddd 100%);
        }
        .rotation-slider::-webkit-slider-thumb {
            -webkit-appearance: none;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #e94560;
            cursor: pointer;
            border: 3px solid white;
            box-shadow: 0 2px 6px rgba(0,0,0,0.2);
        }
        .rotation-buttons {
            display: flex;
            gap: 0.5rem;
            margin-top: 0.75rem;
        }
        .rotation-buttons button {
            flex: 1;
            padding: 0.5rem;
            border: none;
            background: white;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.875rem;
            color: #666;
            transition: all 0.2s;
        }
        .rotation-buttons button:hover {
            background: #e94560;
            color: white;
        }

        /* Canvas del editor */
        .editor-canvas-area {
            flex: 1;
            background: #e8eaed;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: auto;
            padding: 2rem;
        }
        #editorCanvas {
            background: white;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            position: relative;
            border-radius: 4px;
        }

        /* Elementos en el canvas */
        .canvas-element {
            position: absolute;
            cursor: move;
            user-select: none;
        }
        .canvas-element.selected {
            outline: 3px solid #e94560;
            outline-offset: 3px;
        }
        .canvas-element.text-element {
            padding: 4px 8px;
            white-space: nowrap;
        }

        /* Panel derecho - Propiedades */
        .properties-panel {
            width: 280px;
            background: white;
            border-left: 1px solid #e0e0e0;
            padding: 1rem;
            overflow-y: auto;
        }

        .selected-info {
            background: #fef2f4;
            border: 2px solid #e94560;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
            text-align: center;
        }
        .selected-info .name {
            font-weight: 700;
            color: #e94560;
            font-size: 1rem;
        }
        .selected-info .type {
            font-size: 0.75rem;
            color: #999;
        }

        .no-selection {
            text-align: center;
            color: #999;
            padding: 2rem 1rem;
        }
        .no-selection .icon { font-size: 3rem; margin-bottom: 1rem; }

        .quick-actions {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #e0e0e0;
        }

        /* ==================== PASO 3: EXPORTAR ==================== */
        #step3 { display: none; height: calc(100vh - 130px); }
        .export-layout {
            display: flex;
            height: 100%;
        }
        .export-panel {
            width: 350px;
            background: white;
            padding: 1.5rem;
            border-right: 1px solid #e0e0e0;
        }
        .export-canvas-area {
            flex: 1;
            background: #e8eaed;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        #previewCanvas {
            background: white;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            border-radius: 4px;
        }

        .export-card {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1.25rem;
            margin-bottom: 1rem;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.2s;
        }
        .export-card:hover {
            border-color: #e94560;
            transform: translateY(-2px);
        }
        .export-card .format {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .export-card .format-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 0.875rem;
        }
        .export-card .format-info h4 { margin: 0 0 0.25rem 0; color: #333; }
        .export-card .format-info p { margin: 0; font-size: 0.75rem; color: #999; }

        /* Toast notification */
        .toast {
            position: fixed;
            bottom: 2rem;
            left: 50%;
            transform: translateX(-50%) translateY(100px);
            background: #333;
            color: white;
            padding: 1rem 2rem;
            border-radius: 8px;
            font-weight: 500;
            opacity: 0;
            transition: all 0.3s;
            z-index: 1000;
        }
        .toast.show {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
        }
        .toast.success { background: #10b981; }
        .toast.error { background: #ef4444; }

        /* ==================== TARJETA S-12 ==================== */
        .zona-color-swatch {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            cursor: pointer;
            border: 3px solid transparent;
            transition: all 0.2s;
        }
        .zona-color-swatch:hover { transform: scale(1.1); }
        .zona-color-swatch.active {
            border-color: #333;
            box-shadow: 0 0 0 2px white, 0 0 0 4px #333;
        }

        #s12Card {
            width: 847px;
            height: 609px;
            background: white;
            border: 1px solid #999;
            position: relative;
            font-family: 'Times New Roman', Times, serif;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            padding: 12px 15px;
            transform-origin: top left;
        }

        .s12-title {
            text-align: center;
            font-size: 22px;
            font-weight: bold;
            font-style: italic;
            margin-bottom: 3px;
            letter-spacing: 2px;
        }

        .s12-number {
            position: absolute;
            top: 8px;
            left: 787px;
            width: 44px;
            height: 44px;
            background: #C41E3A;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            font-weight: bold;
            font-family: Arial, sans-serif;
        }

        .s12-localidad-row {
            display: flex;
            align-items: baseline;
            gap: 5px;
            margin-bottom: 5px;
            font-size: 12px;
            padding-right: 60px;
        }

        .s12-label {
            font-style: italic;
            white-space: nowrap;
        }

        .s12-localidad-line {
            flex: 1;
            border-bottom: 1px dotted #666;
            min-height: 14px;
            padding-bottom: 2px;
            font-size: 14px;
            color: #333;
        }

        .s12-terr-line {
            width: 60px;
            border-bottom: 1px dotted #666;
            min-height: 14px;
        }

        .s12-map-area {
            border: 1px solid #333;
            height: 390px;
            margin: 5px 70px 5px 0;
            position: relative;
            background: white;
            overflow: hidden;
        }

        /* Recuadro del contenido del mapa */
        #s12MapContentBox {
            position: absolute;
            background: white;
            cursor: move;
        }

        #s12MapContent {
            position: relative;
            transform-origin: top left;
        }

        .s12-zona-container {
            position: absolute;
            top: 65px;
            left: 775px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 3px;
            width: 60px;
        }

        .s12-zona-dot {
            width: 36px;
            height: 36px;
            background: #FFD700;
            border-radius: 50%;
            border: 1px solid #333;
        }

        .s12-zona-text {
            font-size: 10px;
            text-align: center;
            line-height: 1.2;
        }

        .s12-zona-name {
            position: absolute;
            bottom: 75px;
            left: 775px;
            font-size: 10px;
            font-weight: normal;
            border: 1px solid #333;
            padding: 2px 6px;
            background: white;
            min-width: 50px;
            text-align: center;
            font-family: Arial, sans-serif;
        }

        .s12-pegar-text {
            text-align: center;
            font-size: 11px;
            font-style: italic;
            margin: 3px 0;
            color: #444;
        }

        .s12-instructions {
            font-size: 9.5px;
            font-weight: bold;
            line-height: 1.35;
            margin-top: 4px;
            padding: 0 3px;
            color: #111;
        }

        .s12-footer {
            display: flex;
            justify-content: space-between;
            font-size: 8px;
            margin-top: 4px;
            color: #555;
            padding: 0 3px;
        }

        /* Controles de seccion colapsable */
        .control-section {
            margin-top: 0.75rem;
            padding: 0.75rem;
            background: #f0f2f5;
            border-radius: 8px;
        }
        .control-section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #ddd;
            margin-bottom: 0.5rem;
        }
        .control-section-header:hover {
            color: #e94560;
        }
        .control-section-content {
            display: block;
        }
        .control-section-content.collapsed {
            display: none;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <h1>Creador de Territorios</h1>
        <div class="header-actions">
            <a href="{{ route('dashboard') }}" class="btn btn-outline">
                <span>Volver al sistema</span>
            </a>
        </div>
    </header>

    <!-- Steps -->
    <div class="steps-container">
        <div class="steps">
            <div class="step active" id="stepIndicator1">
                <span class="step-number">1</span>
                <span class="step-text">Dibujar zona</span>
            </div>
            <div class="step" id="stepIndicator2">
                <span class="step-number">2</span>
                <span class="step-text">Personalizar</span>
            </div>
            <div class="step" id="stepIndicator3">
                <span class="step-number">3</span>
                <span class="step-text">Descargar</span>
            </div>
        </div>
    </div>

    <!-- ==================== PASO 1 ==================== -->
    <div id="step1">
        <div id="map"></div>
        <div class="map-panel">
            <div class="panel-title">
                <span>Paso 1: Dibuja el territorio</span>
            </div>

            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Buscar direccion...">
                <button class="btn btn-primary" onclick="searchLocation()">Buscar</button>
            </div>

            <div class="help-card">
                <h4>Como dibujar:</h4>
                <ol>
                    <li>Busca la zona en el mapa</li>
                    <li>Haz <strong>clic</strong> en cada esquina del territorio</li>
                    <li>Necesitas minimo <strong>3 puntos</strong></li>
                    <li>Cuando termines, pulsa <strong>Continuar</strong></li>
                </ol>
            </div>

            <div class="stats-card">
                <div class="stats-number" id="pointCount">0</div>
                <div class="stats-label">puntos marcados</div>
                <div class="stats-hint" id="pointHint">Haz clic en el mapa para empezar</div>
            </div>

            <div class="action-buttons">
                <button class="btn btn-outline" onclick="undoLastPoint()" id="undoBtn" disabled>
                    Deshacer ultimo punto
                </button>
                <button class="btn btn-secondary" onclick="clearPoints()" id="clearBtn" disabled>
                    Limpiar todo
                </button>
                <button class="btn btn-success btn-lg" id="continueBtn" onclick="goToStep2()" disabled>
                    Continuar al editor
                </button>
            </div>
        </div>
    </div>

    <!-- ==================== PASO 2 ==================== -->
    <div id="step2">
        <div class="editor-layout">
            <!-- Panel izquierdo -->
            <div class="tools-panel">
                <div class="tool-group">
                    <div class="tool-group-title">Herramientas</div>
                    <button class="tool-btn active" data-tool="select" onclick="setTool('select')">
                        <span class="icon">Sel</span>
                        <div>
                            <div>Seleccionar / Mover</div>
                            <div class="desc">Clic para seleccionar, arrastra para mover</div>
                        </div>
                    </button>
                    <button class="tool-btn" data-tool="text" onclick="setTool('text')">
                        <span class="icon">T</span>
                        <div>
                            <div>Texto</div>
                            <div class="desc">Agregar nombres de calles</div>
                        </div>
                    </button>
                    <button class="tool-btn" data-tool="polygon" onclick="setTool('polygon')">
                        <span class="icon">Pol</span>
                        <div>
                            <div>Dibujar poligono</div>
                            <div class="desc">Clic para puntos, doble-clic para cerrar</div>
                        </div>
                    </button>
                </div>

                <!-- Instrucciones para dibujar polígono -->
                <div class="tool-group" id="polygonInstructions" style="display: none;">
                    <div style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; padding: 0.75rem; font-size: 0.8rem;">
                        <strong>Dibujando poligono:</strong><br>
                        - Clic para agregar puntos<br>
                        - Doble-clic o clic en primer punto para cerrar<br>
                        - ESC para cancelar
                        <div style="margin-top: 0.5rem;">
                            <strong>Puntos: <span id="drawingPointCount">0</span></strong>
                        </div>
                    </div>
                </div>

                <div class="tool-group">
                    <div class="tool-group-title">Texto a agregar</div>
                    <div class="input-row">
                        <input type="text" id="textInput" class="text-input" placeholder="Escribe aqui y haz clic en el canvas...">
                    </div>
                    <div class="input-row" style="margin-top: 0.5rem;">
                        <label class="input-label">Tamano de fuente</label>
                        <select id="fontSize" class="text-input" style="padding: 0.5rem;">
                            <option value="12">12px - Pequeno</option>
                            <option value="16" selected>16px - Normal</option>
                            <option value="20">20px - Mediano</option>
                            <option value="24">24px - Grande</option>
                            <option value="32">32px - Muy grande</option>
                        </select>
                    </div>
                </div>

                <div class="tool-group">
                    <div class="tool-group-title">Color</div>
                    <div class="color-palette">
                        <div class="color-swatch active" style="background: #FFFF00;" onclick="setColor('#FFFF00', this)" title="Amarillo"></div>
                        <div class="color-swatch" style="background: #FFD700;" onclick="setColor('#FFD700', this)" title="Oro"></div>
                        <div class="color-swatch" style="background: #FFA500;" onclick="setColor('#FFA500', this)" title="Naranja"></div>
                        <div class="color-swatch" style="background: #90EE90;" onclick="setColor('#90EE90', this)" title="Verde claro"></div>
                        <div class="color-swatch" style="background: #87CEEB;" onclick="setColor('#87CEEB', this)" title="Azul cielo"></div>
                        <div class="color-swatch" style="background: #DDA0DD;" onclick="setColor('#DDA0DD', this)" title="Lila"></div>
                        <div class="color-swatch" style="background: #000000;" onclick="setColor('#000000', this)" title="Negro"></div>
                        <div class="color-swatch" style="background: #FFFFFF; border: 1px solid #ccc;" onclick="setColor('#FFFFFF', this)" title="Blanco"></div>
                        <div class="color-swatch" style="background: #FF0000;" onclick="setColor('#FF0000', this)" title="Rojo"></div>
                        <div class="color-swatch" style="background: #0000FF;" onclick="setColor('#0000FF', this)" title="Azul"></div>
                        <div class="color-swatch" style="background: #808080;" onclick="setColor('#808080', this)" title="Gris"></div>
                        <div class="color-swatch" style="background: #C0C0C0;" onclick="setColor('#C0C0C0', this)" title="Plata"></div>
                    </div>
                </div>

                <div class="tool-group" id="scaleGroup">
                    <div class="tool-group-title">Tamano del elemento</div>
                    <div class="rotation-control">
                        <div class="rotation-header">
                            <span>Escala</span>
                            <span class="rotation-value" id="scaleDisplay">100%</span>
                        </div>
                        <input type="range" id="scaleSlider" class="rotation-slider"
                               min="25" max="300" value="100" style="--val: 27%"
                               oninput="scaleElement(this.value)">
                        <div class="rotation-buttons">
                            <button onclick="scaleBy(-10)">-10%</button>
                            <button onclick="scaleBy(10)">+10%</button>
                            <button onclick="scaleBy(-25)">-25%</button>
                            <button onclick="scaleBy(25)">+25%</button>
                        </div>
                    </div>
                </div>

                <div class="tool-group">
                    <div class="tool-group-title">Rotacion</div>
                    <div class="rotation-control">
                        <div class="rotation-header">
                            <span>Angulo</span>
                            <span class="rotation-value" id="rotationDisplay">0°</span>
                        </div>
                        <input type="range" id="rotationSlider" class="rotation-slider"
                               min="0" max="360" value="0" style="--val: 0%"
                               oninput="rotateElement(this.value)">
                        <div class="rotation-buttons">
                            <button onclick="rotateBy(-90)">-90°</button>
                            <button onclick="rotateBy(-45)">-45°</button>
                            <button onclick="rotateBy(45)">+45°</button>
                            <button onclick="rotateBy(90)">+90°</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Canvas central -->
            <div class="editor-canvas-area" id="canvasArea">
                <div id="editorCanvas"></div>
            </div>

            <!-- Panel derecho -->
            <div class="properties-panel">
                <div class="panel-title">Elemento seleccionado</div>

                <div id="selectionInfo">
                    <div class="no-selection">
                        <div class="icon">Mover</div>
                        <p>Haz clic en un elemento para seleccionarlo</p>
                    </div>
                </div>

                <div class="quick-actions">
                    <button class="btn btn-secondary" onclick="deleteSelected()" id="deleteBtn" disabled>
                        Eliminar seleccionado
                    </button>
                    <button class="btn btn-outline" onclick="goToStep1()">
                        Volver al mapa
                    </button>
                    <button class="btn btn-success btn-lg" onclick="goToStep3()">
                        Vista previa y descargar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== PASO 3 ==================== -->
    <div id="step3">
        <div class="export-layout">
            <div class="export-panel">
                <div class="panel-title">Paso 3: Tarjeta S-12</div>

                <p style="color: #666; margin-bottom: 1rem;">
                    Completa los datos de la tarjeta de territorio.
                </p>

                <div class="input-row">
                    <label class="input-label">Localidad</label>
                    <input type="text" id="s12Localidad" class="text-input" placeholder="Ej: Santa Coloma de Gramenet" oninput="updateS12Preview()">
                </div>

                <div class="input-row">
                    <label class="input-label">Numero de territorio</label>
                    <input type="text" id="territoryNumber" class="text-input" placeholder="Ej: 1, 2, 3..." oninput="updateS12Preview()">
                </div>

                <div class="input-row">
                    <label class="input-label">Nombre de zona (opcional)</label>
                    <input type="text" id="s12Zona" class="text-input" placeholder="Ej: CENTRO, NORTE, SUR..." oninput="updateS12Preview()">
                </div>

                <div class="input-row">
                    <label class="input-label">Color del indicador "Zona a predicar"</label>
                    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; margin-top: 0.5rem;">
                        <div class="zona-color-swatch active" style="background: #FFD700;" onclick="setZonaColor('#FFD700', this)" title="Amarillo"></div>
                        <div class="zona-color-swatch" style="background: #FF6B6B;" onclick="setZonaColor('#FF6B6B', this)" title="Rojo"></div>
                        <div class="zona-color-swatch" style="background: #4ECDC4;" onclick="setZonaColor('#4ECDC4', this)" title="Verde"></div>
                        <div class="zona-color-swatch" style="background: #45B7D1;" onclick="setZonaColor('#45B7D1', this)" title="Azul"></div>
                        <div class="zona-color-swatch" style="background: #96CEB4;" onclick="setZonaColor('#96CEB4', this)" title="Verde claro"></div>
                        <div class="zona-color-swatch" style="background: #DDA0DD;" onclick="setZonaColor('#DDA0DD', this)" title="Lila"></div>
                        <div class="zona-color-swatch" style="background: #FFA500;" onclick="setZonaColor('#FFA500', this)" title="Naranja"></div>
                        <div class="zona-color-swatch" style="background: #808080;" onclick="setZonaColor('#808080', this)" title="Gris"></div>
                    </div>
                </div>

                <!-- Controles del dibujo/contenido -->
                <div class="control-section">
                    <div class="control-section-header" onclick="toggleSection('boxControls')">
                        <span class="tool-group-title" style="margin: 0;">Dibujo del territorio</span>
                        <span id="boxControlsIcon">-</span>
                    </div>
                    <div class="control-section-content" id="boxControls">
                        <div class="input-row">
                            <label class="input-label">Escala</label>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <input type="range" id="s12MapScale" min="20" max="150" value="60"
                                       style="flex: 1;" oninput="updateMapScale(this.value)">
                                <span id="s12MapScaleValue" style="min-width: 45px; font-weight: bold;">60%</span>
                            </div>
                        </div>

                        <div class="input-row" style="margin-top: 0.5rem;">
                            <label class="input-label">Posicion X</label>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <input type="range" id="s12MapX" min="-100" max="600" value="50"
                                       style="flex: 1;" oninput="updateMapPosition()">
                                <span id="s12MapXValue" style="min-width: 45px; font-weight: bold;">50px</span>
                            </div>
                        </div>

                        <div class="input-row" style="margin-top: 0.5rem;">
                            <label class="input-label">Posicion Y</label>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <input type="range" id="s12MapY" min="-100" max="400" value="30"
                                       style="flex: 1;" oninput="updateMapPosition()">
                                <span id="s12MapYValue" style="min-width: 45px; font-weight: bold;">30px</span>
                            </div>
                        </div>

                        <button class="btn btn-outline" style="width: 100%; margin-top: 0.5rem;" onclick="resetBoxPosition()">
                            Centrar dibujo
                        </button>
                    </div>
                </div>

                <!-- Controles del area del mapa -->
                <div class="control-section">
                    <div class="control-section-header" onclick="toggleSection('mapAreaControls')">
                        <span class="tool-group-title" style="margin: 0;">Area del mapa</span>
                        <span id="mapAreaControlsIcon">-</span>
                    </div>
                    <div class="control-section-content" id="mapAreaControls">
                        <div class="input-row">
                            <label class="input-label">Borde del area</label>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <input type="range" id="s12MapAreaBorder" min="0" max="3" value="1"
                                       style="flex: 1;" oninput="updateMapAreaBorder(this.value)">
                                <span id="s12MapAreaBorderValue" style="min-width: 45px; font-weight: bold;">1px</span>
                            </div>
                        </div>

                        <div class="input-row" style="margin-top: 0.5rem;">
                            <label class="input-label">Color borde area</label>
                            <input type="color" id="s12MapAreaBorderColor" value="#333333"
                                   style="width: 60px; height: 30px; border: none; cursor: pointer;"
                                   oninput="updateMapAreaBorderColor(this.value)">
                        </div>
                    </div>
                </div>

                <!-- Controles del numero de territorio -->
                <div class="control-section">
                    <div class="control-section-header" onclick="toggleSection('numberControls')">
                        <span class="tool-group-title" style="margin: 0;">Numero de territorio (circulo rojo)</span>
                        <span id="numberControlsIcon">-</span>
                    </div>
                    <div class="control-section-content" id="numberControls">
                        <div class="input-row">
                            <label class="input-label">Posicion X</label>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <input type="range" id="s12NumberX" min="0" max="800" value="787"
                                       style="flex: 1;" oninput="updateNumberPosition()">
                                <span id="s12NumberXValue" style="min-width: 45px; font-weight: bold;">787px</span>
                            </div>
                        </div>

                        <div class="input-row" style="margin-top: 0.5rem;">
                            <label class="input-label">Posicion Y</label>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <input type="range" id="s12NumberY" min="0" max="550" value="8"
                                       style="flex: 1;" oninput="updateNumberPosition()">
                                <span id="s12NumberYValue" style="min-width: 45px; font-weight: bold;">8px</span>
                            </div>
                        </div>

                        <div class="input-row" style="margin-top: 0.5rem;">
                            <label class="input-label">Tamano</label>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <input type="range" id="s12NumberSize" min="30" max="80" value="44"
                                       style="flex: 1;" oninput="updateNumberSize(this.value)">
                                <span id="s12NumberSizeValue" style="min-width: 45px; font-weight: bold;">44px</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Controles de Zona a predicar -->
                <div class="control-section">
                    <div class="control-section-header" onclick="toggleSection('zonaControls')">
                        <span class="tool-group-title" style="margin: 0;">Zona a predicar</span>
                        <span id="zonaControlsIcon">-</span>
                    </div>
                    <div class="control-section-content" id="zonaControls">
                        <div class="input-row">
                            <label class="input-label">Posicion X</label>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <input type="range" id="s12ZonaX" min="0" max="800" value="775"
                                       style="flex: 1;" oninput="updateZonaPosition()">
                                <span id="s12ZonaXValue" style="min-width: 45px; font-weight: bold;">775px</span>
                            </div>
                        </div>

                        <div class="input-row" style="margin-top: 0.5rem;">
                            <label class="input-label">Posicion Y</label>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <input type="range" id="s12ZonaY" min="0" max="500" value="65"
                                       style="flex: 1;" oninput="updateZonaPosition()">
                                <span id="s12ZonaYValue" style="min-width: 45px; font-weight: bold;">65px</span>
                            </div>
                        </div>

                        <div class="input-row" style="margin-top: 0.5rem;">
                            <label class="input-label">Tamano del circulo</label>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <input type="range" id="s12ZonaDotSize" min="20" max="60" value="36"
                                       style="flex: 1;" oninput="updateZonaDotSize(this.value)">
                                <span id="s12ZonaDotSizeValue" style="min-width: 45px; font-weight: bold;">36px</span>
                            </div>
                        </div>

                        <div class="input-row" style="margin-top: 0.5rem;">
                            <label class="input-label">Tamano del texto</label>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <input type="range" id="s12ZonaTextSize" min="8" max="16" value="10"
                                       style="flex: 1;" oninput="updateZonaTextSize(this.value)">
                                <span id="s12ZonaTextSizeValue" style="min-width: 45px; font-weight: bold;">10px</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Controles del formato general -->
                <div class="control-section">
                    <div class="control-section-header" onclick="toggleSection('formatControls')">
                        <span class="tool-group-title" style="margin: 0;">Formato general</span>
                        <span id="formatControlsIcon">-</span>
                    </div>
                    <div class="control-section-content" id="formatControls">
                        <div class="input-row">
                            <label class="input-label">Tamano titulo</label>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <input type="range" id="s12TitleSize" min="16" max="32" value="22"
                                       style="flex: 1;" oninput="updateTitleSize(this.value)">
                                <span id="s12TitleSizeValue" style="min-width: 45px; font-weight: bold;">22px</span>
                            </div>
                        </div>

                        <div class="input-row" style="margin-top: 0.5rem;">
                            <label class="input-label">Tamano instrucciones</label>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <input type="range" id="s12InstructionsSize" min="7" max="14" value="9"
                                       style="flex: 1;" oninput="updateInstructionsSize(this.value)">
                                <span id="s12InstructionsSizeValue" style="min-width: 45px; font-weight: bold;">9px</span>
                            </div>
                        </div>

                        <button class="btn btn-outline" style="width: 100%; margin-top: 0.5rem;" onclick="resetAllDefaults()">
                            Restablecer todo
                        </button>
                    </div>
                </div>

                <div style="margin-top: 1.5rem;">
                    <div class="tool-group-title">Formato de descarga</div>

                    <div class="export-card" onclick="exportAs('png')">
                        <div class="format">
                            <div class="format-icon">PNG</div>
                            <div class="format-info">
                                <h4>Imagen PNG</h4>
                                <p>Alta calidad, ideal para pantallas</p>
                            </div>
                        </div>
                    </div>

                    <div class="export-card" onclick="exportAs('jpg')">
                        <div class="format">
                            <div class="format-icon">JPG</div>
                            <div class="format-info">
                                <h4>Imagen JPG</h4>
                                <p>Archivo mas pequeno</p>
                            </div>
                        </div>
                    </div>

                    <div class="export-card" onclick="exportAs('pdf')">
                        <div class="format">
                            <div class="format-icon">PDF</div>
                            <div class="format-info">
                                <h4>Documento PDF</h4>
                                <p>Ideal para imprimir</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="quick-actions">
                    <button class="btn btn-outline" onclick="goToStep2()">
                        Volver al editor
                    </button>
                    <button class="btn btn-primary" onclick="startNew()">
                        Crear otro territorio
                    </button>
                </div>
            </div>

            <div class="export-canvas-area">
                <!-- Tarjeta S-12 -->
                <div id="s12Card">
                    <!-- Titulo -->
                    <div class="s12-title">Tarjeta de mapa del territorio</div>

                    <!-- Numero grande -->
                    <div class="s12-number" id="s12NumberCircle">
                        <span id="s12NumberText"></span>
                    </div>

                    <!-- Localidad -->
                    <div class="s12-localidad-row">
                        <span class="s12-label">Localidad:</span>
                        <span class="s12-localidad-line" id="s12LocalidadText"></span>
                        <span class="s12-label">Terr. num.:</span>
                        <span class="s12-terr-line"></span>
                    </div>

                    <!-- Area del mapa -->
                    <div class="s12-map-area">
                        <div id="s12MapContentBox">
                            <div id="s12MapContent"></div>
                        </div>
                    </div>

                    <!-- Zona a predicar -->
                    <div class="s12-zona-container">
                        <div class="s12-zona-dot" id="s12ZonaDot"></div>
                        <div class="s12-zona-text">
                            Zona a<br>predicar
                        </div>
                    </div>

                    <!-- Nombre de zona -->
                    <div class="s12-zona-name" id="s12ZonaName"></div>

                    <!-- Instruccion pegar mapa -->
                    <div class="s12-pegar-text">(Pega el mapa arriba o dibuja el territorio)</div>

                    <!-- Instrucciones pie -->
                    <div class="s12-instructions">
                        Por favor, manten esta tarjeta en el sobre. No manches, marques ni dobles. Cada vez que hayas trabajado completamente el territorio, informalo al hermano encargado de los territorios.
                    </div>

                    <!-- Footer -->
                    <div class="s12-footer">
                        <span>S-12-S 6/72</span>
                        <span>Printed in Britain</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast -->
    <div class="toast" id="toast"></div>

<script>
// ============================================
// VARIABLES GLOBALES
// ============================================
let map;
let territoryPoints = [];
let markers = [];
let polyline = null;
let polygon = null;

let currentTool = 'select';
let currentColor = '#FFFF00';
let elements = [];
let selectedElement = null;
let elementId = 0;
let canvasWidth = 800;
let canvasHeight = 600;
let territoryElement = null;

// Variables para dibujar polígonos internos
let isDrawingPolygon = false;
let drawingPolygonPoints = [];
let drawingPolygonPreview = null;
let drawingPointsMarkers = [];

// Variables para la tarjeta S-12
let s12MapScaleFactor = 60;
let s12MapOffsetX = 50;
let s12MapOffsetY = 30;
let s12ZonaColor = '#FFD700';

// ============================================
// UTILIDADES
// ============================================
function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    toast.textContent = message;
    toast.className = 'toast ' + type + ' show';
    setTimeout(() => toast.classList.remove('show'), 3000);
}

// ============================================
// PASO 1: MAPA
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    map = L.map('map').setView([41.4534, 2.2081], 17);

    L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        maxZoom: 20
    }).addTo(map);

    L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/Reference/World_Transportation/MapServer/tile/{z}/{y}/{x}', {
        maxZoom: 20,
        opacity: 0.7
    }).addTo(map);

    map.on('click', function(e) {
        addPoint(e.latlng);
    });

    document.getElementById('searchInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') searchLocation();
    });
});

function addPoint(latlng) {
    territoryPoints.push(latlng);

    const marker = L.circleMarker(latlng, {
        radius: 10,
        color: '#ffffff',
        fillColor: '#e94560',
        fillOpacity: 1,
        weight: 3
    }).addTo(map);

    marker.bindTooltip(`${territoryPoints.length}`, {
        permanent: true,
        direction: 'center',
        className: 'point-label'
    });
    markers.push(marker);

    updateMapDisplay();
    updatePointCount();
    showToast(`Punto ${territoryPoints.length} agregado`);
}

function undoLastPoint() {
    if (territoryPoints.length === 0) return;

    const lastMarker = markers.pop();
    map.removeLayer(lastMarker);
    territoryPoints.pop();

    updateMapDisplay();
    updatePointCount();
    showToast('Punto eliminado');
}

function updateMapDisplay() {
    if (polyline) map.removeLayer(polyline);
    if (polygon) map.removeLayer(polygon);

    if (territoryPoints.length >= 2) {
        polyline = L.polyline([...territoryPoints, territoryPoints[0]], {
            color: '#e94560',
            weight: 3,
            dashArray: '10, 5'
        }).addTo(map);
    }

    if (territoryPoints.length >= 3) {
        polygon = L.polygon(territoryPoints, {
            color: '#e94560',
            fillColor: '#e94560',
            fillOpacity: 0.3,
            weight: 2
        }).addTo(map);
    }
}

function updatePointCount() {
    const count = territoryPoints.length;
    document.getElementById('pointCount').textContent = count;
    document.getElementById('continueBtn').disabled = count < 3;
    document.getElementById('undoBtn').disabled = count === 0;
    document.getElementById('clearBtn').disabled = count === 0;

    const hint = document.getElementById('pointHint');
    if (count === 0) hint.textContent = 'Haz clic en el mapa para empezar';
    else if (count < 3) hint.textContent = `Necesitas ${3 - count} punto(s) mas`;
    else hint.textContent = 'Listo para continuar';
}

function clearPoints() {
    markers.forEach(m => map.removeLayer(m));
    markers = [];
    territoryPoints = [];
    if (polyline) map.removeLayer(polyline);
    if (polygon) map.removeLayer(polygon);
    polyline = null;
    polygon = null;
    updatePointCount();
    showToast('Puntos eliminados');
}

async function searchLocation() {
    const query = document.getElementById('searchInput').value.trim();
    if (!query) return;

    try {
        const response = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query + ', Espana')}&limit=1`);
        const data = await response.json();

        if (data.length > 0) {
            map.setView([data[0].lat, data[0].lon], 18);
            showToast('Ubicacion encontrada');
        } else {
            showToast('No se encontro la ubicacion', 'error');
        }
    } catch (error) {
        showToast('Error de conexion', 'error');
    }
}

// ============================================
// NAVEGACION
// ============================================
function goToStep1() {
    document.getElementById('step1').style.display = 'flex';
    document.getElementById('step2').style.display = 'none';
    document.getElementById('step3').style.display = 'none';
    updateSteps(1);
    setTimeout(() => map.invalidateSize(), 100);
}

function goToStep2() {
    if (territoryPoints.length < 3) return;

    document.getElementById('step1').style.display = 'none';
    document.getElementById('step2').style.display = 'block';
    document.getElementById('step3').style.display = 'none';
    updateSteps(2);

    generateCanvas();
}

function goToStep3() {
    document.getElementById('step1').style.display = 'none';
    document.getElementById('step2').style.display = 'none';
    document.getElementById('step3').style.display = 'block';
    updateSteps(3);

    generatePreview();
}

function updateSteps(current) {
    for (let i = 1; i <= 3; i++) {
        const el = document.getElementById(`stepIndicator${i}`);
        el.classList.remove('active', 'completed');
        if (i === current) el.classList.add('active');
        else if (i < current) el.classList.add('completed');
    }
}

function startNew() {
    clearPoints();
    elements = [];
    selectedElement = null;
    territoryElement = null;
    document.getElementById('editorCanvas').innerHTML = '';
    goToStep1();
}

// ============================================
// PASO 2: EDITOR
// ============================================
function generateCanvas() {
    const canvas = document.getElementById('editorCanvas');
    canvas.innerHTML = '';

    const lats = territoryPoints.map(p => p.lat);
    const lngs = territoryPoints.map(p => p.lng);
    const minLat = Math.min(...lats);
    const maxLat = Math.max(...lats);
    const minLng = Math.min(...lngs);
    const maxLng = Math.max(...lngs);

    // Mantener proporcion
    const latRange = maxLat - minLat;
    const lngRange = maxLng - minLng;
    const ratio = lngRange / latRange;

    const padding = 60;
    if (ratio > 1) {
        canvasWidth = 800;
        canvasHeight = Math.max(400, 800 / ratio);
    } else {
        canvasHeight = 600;
        canvasWidth = Math.max(400, 600 * ratio);
    }

    canvas.style.width = canvasWidth + 'px';
    canvas.style.height = canvasHeight + 'px';

    const canvasPoints = territoryPoints.map(p => {
        const x = padding + ((p.lng - minLng) / (maxLng - minLng || 1)) * (canvasWidth - 2 * padding);
        const y = padding + ((maxLat - p.lat) / (maxLat - minLat || 1)) * (canvasHeight - 2 * padding);
        return { x, y };
    });

    // SVG para el territorio
    const svgNS = "http://www.w3.org/2000/svg";
    const svg = document.createElementNS(svgNS, "svg");
    svg.setAttribute("width", canvasWidth);
    svg.setAttribute("height", canvasHeight);
    svg.style.cssText = "position: absolute; top: 0; left: 0; pointer-events: none;";
    svg.id = "territorySvg";

    const poly = document.createElementNS(svgNS, "polygon");
    poly.setAttribute("points", canvasPoints.map(p => `${p.x},${p.y}`).join(' '));
    poly.setAttribute("fill", currentColor);
    poly.setAttribute("stroke", "#000000");
    poly.setAttribute("stroke-width", "3");
    poly.id = "territoryPolygon";
    poly.style.cursor = "pointer";
    poly.style.pointerEvents = "auto"; // Solo el polígono recibe clics
    svg.appendChild(poly);

    canvas.appendChild(svg);

    // Guardar referencia con puntos originales para escalar
    territoryElement = {
        id: ++elementId,
        type: 'territory',
        svg: svg,
        poly: poly,
        color: currentColor,
        rotation: 0,
        scale: 100,
        originalPoints: canvasPoints
    };
    elements = [territoryElement];

    // Click en polígono para seleccionar territorio
    poly.onclick = function(e) {
        e.stopPropagation();
        selectElement(territoryElement.id);
    };

    // Click en canvas para agregar elementos
    canvas.onclick = handleCanvasClick;

    updateSelectionInfo();
}

function handleCanvasClick(e) {
    const canvas = document.getElementById('editorCanvas');
    const rect = canvas.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;

    // Si estamos dibujando un polígono interno
    if (currentTool === 'polygon') {
        addPolygonPoint(x, y);
        return;
    }

    // Si clic en el polígono del territorio
    if (e.target.id === 'territoryPolygon') {
        if (currentTool === 'select') {
            selectElement(territoryElement.id);
        } else if (currentTool === 'text') {
            const text = document.getElementById('textInput').value.trim();
            if (!text) {
                showToast('Escribe el texto primero', 'error');
                return;
            }
            addTextToCanvas(x, y, text);
        }
        return;
    }

    // Clic en un polígono interno (SVG)
    if (e.target.classList && e.target.classList.contains('internal-polygon')) {
        const elId = parseInt(e.target.dataset.id);
        if (elId) {
            selectElement(elId);
            return;
        }
    }

    const clickedEl = e.target.closest('.canvas-element');
    if (clickedEl) {
        if (currentTool === 'select') {
            selectElement(parseInt(clickedEl.dataset.id));
        }
        return;
    }

    // Agregar elementos en cualquier parte del canvas
    if (currentTool === 'text') {
        const text = document.getElementById('textInput').value.trim();
        if (!text) {
            showToast('Escribe el texto primero', 'error');
            return;
        }
        addTextToCanvas(x, y, text);
    } else if (currentTool === 'select') {
        // Deseleccionar
        deselectAll();
    }
}

function deselectAll() {
    selectedElement = null;
    document.querySelectorAll('.canvas-element.selected').forEach(el => el.classList.remove('selected'));
    document.querySelectorAll('.internal-polygon').forEach(p => {
        p.style.strokeWidth = '2';
        p.style.stroke = '#000000';
    });
    const svg = document.getElementById('territorySvg');
    if (svg) svg.style.outline = 'none';
    document.getElementById('scaleGroup').style.display = 'none';
    document.getElementById('deleteBtn').disabled = true;
    updateSelectionInfo();
}

function addTextToCanvas(x, y, text) {
    const canvas = document.getElementById('editorCanvas');
    const fontSize = document.getElementById('fontSize').value;

    const div = document.createElement('div');
    div.className = 'canvas-element text-element';
    div.dataset.id = ++elementId;
    div.style.cssText = `
        left: ${x}px;
        top: ${y}px;
        color: #000000;
        font-size: ${fontSize}px;
        font-family: Arial, sans-serif;
        font-weight: bold;
        transform-origin: center center;
    `;
    div.textContent = text;

    makeDraggable(div);
    canvas.appendChild(div);

    const el = {
        id: elementId,
        type: 'text',
        element: div,
        text: text,
        rotation: 0,
        scale: 100,
        color: '#000000'
    };
    elements.push(el);

    selectElement(elementId);
    document.getElementById('textInput').value = '';
    showToast('Texto agregado');
}

// ============================================
// FUNCIONES PARA DIBUJAR POLÍGONOS INTERNOS
// ============================================
function addPolygonPoint(x, y) {
    const canvas = document.getElementById('editorCanvas');

    // Si es el primer punto cerca del primero y hay al menos 3 puntos, cerrar el polígono
    if (drawingPolygonPoints.length >= 3) {
        const firstPoint = drawingPolygonPoints[0];
        const distance = Math.sqrt(Math.pow(x - firstPoint.x, 2) + Math.pow(y - firstPoint.y, 2));
        if (distance < 20) {
            finishInternalPolygon();
            return;
        }
    }

    drawingPolygonPoints.push({ x, y });

    // Crear marcador visual para el punto
    const marker = document.createElement('div');
    marker.className = 'polygon-point-marker';
    marker.style.cssText = `
        position: absolute;
        left: ${x - 6}px;
        top: ${y - 6}px;
        width: 12px;
        height: 12px;
        background: #e94560;
        border: 2px solid white;
        border-radius: 50%;
        z-index: 1000;
        pointer-events: none;
    `;
    canvas.appendChild(marker);
    drawingPointsMarkers.push(marker);

    // Actualizar preview del polígono
    updatePolygonPreview();

    // Actualizar contador
    document.getElementById('drawingPointCount').textContent = drawingPolygonPoints.length;

    showToast(`Punto ${drawingPolygonPoints.length} agregado`);
}

function updatePolygonPreview() {
    const canvas = document.getElementById('editorCanvas');

    // Eliminar preview anterior
    if (drawingPolygonPreview) {
        drawingPolygonPreview.remove();
    }

    if (drawingPolygonPoints.length < 2) return;

    // Crear SVG de preview
    const svgNS = "http://www.w3.org/2000/svg";
    const svg = document.createElementNS(svgNS, "svg");
    svg.setAttribute("width", canvasWidth);
    svg.setAttribute("height", canvasHeight);
    svg.style.cssText = "position: absolute; top: 0; left: 0; pointer-events: none; z-index: 999;";

    const polyline = document.createElementNS(svgNS, "polyline");
    polyline.setAttribute("points", drawingPolygonPoints.map(p => `${p.x},${p.y}`).join(' '));
    polyline.setAttribute("fill", "none");
    polyline.setAttribute("stroke", currentColor);
    polyline.setAttribute("stroke-width", "2");
    polyline.setAttribute("stroke-dasharray", "5,5");
    svg.appendChild(polyline);

    canvas.appendChild(svg);
    drawingPolygonPreview = svg;
}

function finishInternalPolygon() {
    if (drawingPolygonPoints.length < 3) {
        showToast('Necesitas al menos 3 puntos', 'error');
        return;
    }

    const canvas = document.getElementById('editorCanvas');
    const svg = document.getElementById('territorySvg');

    // Crear el polígono final como elemento SVG
    const svgNS = "http://www.w3.org/2000/svg";
    const poly = document.createElementNS(svgNS, "polygon");
    poly.setAttribute("points", drawingPolygonPoints.map(p => `${p.x},${p.y}`).join(' '));
    poly.setAttribute("fill", currentColor);
    poly.setAttribute("stroke", "#000000");
    poly.setAttribute("stroke-width", "2");
    poly.setAttribute("fill-opacity", "0.7");
    poly.classList.add('internal-polygon');
    poly.dataset.id = ++elementId;
    poly.style.cursor = "move";
    poly.style.pointerEvents = "auto";

    svg.appendChild(poly);

    // Calcular centro del polígono para transformaciones
    const centerX = drawingPolygonPoints.reduce((sum, p) => sum + p.x, 0) / drawingPolygonPoints.length;
    const centerY = drawingPolygonPoints.reduce((sum, p) => sum + p.y, 0) / drawingPolygonPoints.length;

    const el = {
        id: elementId,
        type: 'internalPolygon',
        poly: poly,
        color: currentColor,
        rotation: 0,
        scale: 100,
        originalPoints: [...drawingPolygonPoints],
        centerX: centerX,
        centerY: centerY,
        offsetX: 0,
        offsetY: 0
    };
    elements.push(el);

    // Hacer el polígono arrastrable
    makePolygonDraggable(poly, el);

    // Limpiar estado de dibujo
    clearPolygonDrawing();

    // Cambiar a herramienta de selección
    setTool('select');

    // Seleccionar el nuevo polígono
    selectElement(elementId);

    showToast('Poligono creado - Arrastra para mover');
}

// Función para hacer un polígono SVG arrastrable
function makePolygonDraggable(poly, elementData) {
    let isDragging = false;
    let startX, startY;
    let initialOffsetX, initialOffsetY;

    poly.addEventListener('mousedown', function(e) {
        if (currentTool !== 'select') return;
        e.stopPropagation();

        isDragging = true;
        startX = e.clientX;
        startY = e.clientY;
        initialOffsetX = elementData.offsetX || 0;
        initialOffsetY = elementData.offsetY || 0;

        selectElement(elementData.id);

        const onMouseMove = function(e) {
            if (!isDragging) return;

            const deltaX = e.clientX - startX;
            const deltaY = e.clientY - startY;

            elementData.offsetX = initialOffsetX + deltaX;
            elementData.offsetY = initialOffsetY + deltaY;

            // Actualizar los puntos del polígono
            updatePolygonPosition(elementData);
        };

        const onMouseUp = function() {
            isDragging = false;
            document.removeEventListener('mousemove', onMouseMove);
            document.removeEventListener('mouseup', onMouseUp);
        };

        document.addEventListener('mousemove', onMouseMove);
        document.addEventListener('mouseup', onMouseUp);
    });
}

// Actualizar posición del polígono basado en offset
function updatePolygonPosition(el) {
    if (!el || !el.poly || !el.originalPoints) return;

    const offsetX = el.offsetX || 0;
    const offsetY = el.offsetY || 0;

    // Aplicar offset a los puntos originales
    const newPoints = el.originalPoints.map(p => ({
        x: p.x + offsetX,
        y: p.y + offsetY
    }));

    // Actualizar centro
    el.centerX = newPoints.reduce((sum, p) => sum + p.x, 0) / newPoints.length;
    el.centerY = newPoints.reduce((sum, p) => sum + p.y, 0) / newPoints.length;

    // Establecer nuevos puntos
    el.poly.setAttribute("points", newPoints.map(p => `${p.x},${p.y}`).join(' '));

    // Re-aplicar transformaciones
    applyInternalPolygonTransform(el);
}

function clearPolygonDrawing() {
    // Eliminar marcadores
    drawingPointsMarkers.forEach(m => m.remove());
    drawingPointsMarkers = [];

    // Eliminar preview
    if (drawingPolygonPreview) {
        drawingPolygonPreview.remove();
        drawingPolygonPreview = null;
    }

    // Resetear puntos
    drawingPolygonPoints = [];
    isDrawingPolygon = false;

    // Ocultar instrucciones
    document.getElementById('polygonInstructions').style.display = 'none';
    document.getElementById('drawingPointCount').textContent = '0';
}

function cancelPolygonDrawing() {
    clearPolygonDrawing();
    setTool('select');
    showToast('Dibujo cancelado');
}

function makeDraggable(element) {
    let isDragging = false;
    let startX, startY, initialX, initialY;

    element.onmousedown = function(e) {
        if (currentTool !== 'select') return;
        e.stopPropagation();

        isDragging = true;
        startX = e.clientX;
        startY = e.clientY;
        initialX = element.offsetLeft;
        initialY = element.offsetTop;

        selectElement(parseInt(element.dataset.id));

        document.onmousemove = function(e) {
            if (!isDragging) return;
            element.style.left = (initialX + e.clientX - startX) + 'px';
            element.style.top = (initialY + e.clientY - startY) + 'px';
        };

        document.onmouseup = function() {
            isDragging = false;
            document.onmousemove = null;
            document.onmouseup = null;
        };
    };
}

function selectElement(id) {
    // Quitar seleccion anterior
    document.querySelectorAll('.canvas-element.selected').forEach(el => el.classList.remove('selected'));
    const svgEl = document.getElementById('territorySvg');
    if (svgEl) svgEl.style.outline = 'none';

    // Quitar selección de polígonos internos
    document.querySelectorAll('.internal-polygon').forEach(p => {
        p.style.strokeWidth = '2';
        p.style.stroke = '#000000';
    });

    selectedElement = elements.find(e => e.id === id);

    if (selectedElement) {
        if (selectedElement.type === 'territory') {
            selectedElement.svg.style.outline = '3px solid #e94560';
            selectedElement.svg.style.outlineOffset = '3px';
        } else if (selectedElement.type === 'internalPolygon') {
            // Resaltar el polígono interno
            selectedElement.poly.style.strokeWidth = '4';
            selectedElement.poly.style.stroke = '#e94560';
        } else if (selectedElement.element) {
            selectedElement.element.classList.add('selected');
        }

        // Mostrar control de escala para TODOS los elementos
        document.getElementById('scaleGroup').style.display = 'block';
        document.getElementById('scaleSlider').value = selectedElement.scale || 100;
        updateScaleDisplay(selectedElement.scale || 100);

        // Actualizar controles de rotación
        document.getElementById('rotationSlider').value = selectedElement.rotation || 0;
        updateRotationDisplay(selectedElement.rotation || 0);

        // Permitir eliminar todo excepto territorio principal
        document.getElementById('deleteBtn').disabled = selectedElement.type === 'territory';
    }

    updateSelectionInfo();
}

function updateSelectionInfo() {
    const info = document.getElementById('selectionInfo');

    if (!selectedElement) {
        info.innerHTML = `
            <div class="no-selection">
                <div class="icon">Mover</div>
                <p>Haz clic en un elemento para seleccionarlo</p>
            </div>
        `;
        document.getElementById('deleteBtn').disabled = true;
        return;
    }

    let name = '';
    let type = '';
    switch(selectedElement.type) {
        case 'territory':
            name = 'Territorio Principal';
            type = 'Poligono - Color, rotacion y tamano';
            break;
        case 'text':
            name = selectedElement.text;
            type = 'Texto - Arrastra, rota y escala';
            break;
        case 'internalPolygon':
            name = 'Poligono interno';
            type = 'Forma - Color, rotacion y tamano';
            break;
    }

    info.innerHTML = `
        <div class="selected-info">
            <div class="name">${name}</div>
            <div class="type">${type}</div>
        </div>
    `;
}

function setTool(tool) {
    // Si cambiamos de herramienta polygon, cancelar dibujo actual
    if (currentTool === 'polygon' && tool !== 'polygon' && drawingPolygonPoints.length > 0) {
        clearPolygonDrawing();
    }

    currentTool = tool;
    document.querySelectorAll('.tool-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelector(`[data-tool="${tool}"]`)?.classList.add('active');

    const canvas = document.getElementById('editorCanvas');
    canvas.style.cursor = tool === 'select' ? 'default' : 'crosshair';

    // Mostrar/ocultar instrucciones de polígono
    const polyInstructions = document.getElementById('polygonInstructions');
    if (tool === 'polygon') {
        polyInstructions.style.display = 'block';
        isDrawingPolygon = true;
        showToast('Haz clic para agregar puntos, doble-clic para cerrar');
    } else {
        polyInstructions.style.display = 'none';
    }
}

function setColor(color, swatch) {
    currentColor = color;
    document.querySelectorAll('.color-swatch').forEach(s => s.classList.remove('active'));
    if (swatch) swatch.classList.add('active');

    // Aplicar al elemento seleccionado
    if (selectedElement) {
        if (selectedElement.type === 'territory') {
            selectedElement.poly.setAttribute('fill', color);
            selectedElement.color = color;
        } else if (selectedElement.type === 'text') {
            selectedElement.element.style.color = color;
            selectedElement.color = color;
        } else if (selectedElement.type === 'internalPolygon') {
            selectedElement.poly.setAttribute('fill', color);
            selectedElement.color = color;
        }
        showToast('Color aplicado');
    }
}

function rotateElement(angle) {
    angle = parseInt(angle);
    updateRotationDisplay(angle);

    if (!selectedElement) return;

    selectedElement.rotation = angle;

    if (selectedElement.type === 'territory') {
        applyTerritoryTransform();
    } else if (selectedElement.type === 'internalPolygon') {
        applyInternalPolygonTransform(selectedElement);
    } else if (selectedElement.type === 'text' && selectedElement.element) {
        applyTextTransform(selectedElement);
    }
}

function rotateBy(delta) {
    if (!selectedElement) {
        showToast('Selecciona un elemento primero', 'error');
        return;
    }

    let newAngle = ((selectedElement.rotation || 0) + delta + 360) % 360;
    document.getElementById('rotationSlider').value = newAngle;
    rotateElement(newAngle);
}

function updateRotationDisplay(angle) {
    document.getElementById('rotationDisplay').textContent = angle + '°';
    document.getElementById('rotationSlider').style.setProperty('--val', (angle / 360 * 100) + '%');
}

// Funciones de escala - para TODOS los elementos
function scaleElement(scale) {
    scale = parseInt(scale);
    updateScaleDisplay(scale);

    if (!selectedElement) return;

    selectedElement.scale = scale;

    if (selectedElement.type === 'territory') {
        applyTerritoryTransform();
    } else if (selectedElement.type === 'internalPolygon') {
        applyInternalPolygonTransform(selectedElement);
    } else if (selectedElement.type === 'text' && selectedElement.element) {
        applyTextTransform(selectedElement);
    }
}

function scaleBy(delta) {
    if (!selectedElement) {
        showToast('Selecciona un elemento primero', 'error');
        return;
    }

    let newScale = Math.max(25, Math.min(300, (selectedElement.scale || 100) + delta));
    document.getElementById('scaleSlider').value = newScale;
    scaleElement(newScale);
}

// Aplicar transformación a texto
function applyTextTransform(el) {
    if (!el || !el.element) return;
    const rotation = el.rotation || 0;
    const scale = (el.scale || 100) / 100;
    el.element.style.transform = `rotate(${rotation}deg) scale(${scale})`;
}

// Aplicar transformación a polígono interno
function applyInternalPolygonTransform(el) {
    if (!el || !el.poly) return;

    const rotation = el.rotation || 0;
    const scale = (el.scale || 100) / 100;
    const centerX = el.centerX;
    const centerY = el.centerY;

    // Combinar transformaciones: rotar y escalar desde el centro
    el.poly.setAttribute('transform',
        `rotate(${rotation}, ${centerX}, ${centerY}) ` +
        `translate(${centerX}, ${centerY}) ` +
        `scale(${scale}) ` +
        `translate(${-centerX}, ${-centerY})`
    );
}

function updateScaleDisplay(scale) {
    document.getElementById('scaleDisplay').textContent = scale + '%';
    // Rango de 25 a 300, así que el % va de 0 a 100
    const percentage = ((scale - 25) / (300 - 25)) * 100;
    document.getElementById('scaleSlider').style.setProperty('--val', percentage + '%');
}

// Aplicar rotación y escala al territorio
function applyTerritoryTransform() {
    if (!territoryElement) return;

    const poly = territoryElement.poly;
    const rotation = territoryElement.rotation || 0;
    const scale = (territoryElement.scale || 100) / 100;
    const centerX = canvasWidth / 2;
    const centerY = canvasHeight / 2;

    // Combinar transformaciones: primero escalar desde el centro, luego rotar
    poly.setAttribute('transform',
        `rotate(${rotation}, ${centerX}, ${centerY}) ` +
        `translate(${centerX}, ${centerY}) ` +
        `scale(${scale}) ` +
        `translate(${-centerX}, ${-centerY})`
    );
}

function deleteSelected() {
    if (!selectedElement || selectedElement.type === 'territory') {
        showToast('No puedes eliminar el territorio principal', 'error');
        return;
    }

    // Eliminar el elemento del DOM
    if (selectedElement.type === 'internalPolygon') {
        selectedElement.poly.remove();
    } else if (selectedElement.element) {
        selectedElement.element.remove();
    }

    elements = elements.filter(e => e.id !== selectedElement.id);
    selectedElement = null;
    document.getElementById('scaleGroup').style.display = 'none';
    updateSelectionInfo();
    showToast('Elemento eliminado');
}

// ============================================
// PASO 3: EXPORTAR - FORMATO S-12
// ============================================

function generatePreview() {
    // Copiar el mapa del editor al área de mapa de la tarjeta S-12
    const editor = document.getElementById('editorCanvas');
    const s12MapContent = document.getElementById('s12MapContent');
    const s12MapContentBox = document.getElementById('s12MapContentBox');

    // Clonar contenido del editor
    s12MapContent.innerHTML = editor.innerHTML;
    s12MapContent.style.width = canvasWidth + 'px';
    s12MapContent.style.height = canvasHeight + 'px';
    s12MapContent.style.background = 'white';

    // Quitar estilos de selección
    s12MapContent.querySelectorAll('.selected').forEach(el => el.classList.remove('selected'));
    s12MapContent.querySelectorAll('.internal-polygon').forEach(p => {
        p.style.strokeWidth = '2';
        p.style.stroke = '#000000';
    });
    const svg = s12MapContent.querySelector('#territorySvg');
    if (svg) {
        svg.style.outline = 'none';
        svg.id = 's12TerritoryMap';
    }

    // Resetear controles del dibujo
    s12MapScaleFactor = 60;
    s12MapOffsetX = 50;
    s12MapOffsetY = 30;

    document.getElementById('s12MapScale').value = s12MapScaleFactor;
    document.getElementById('s12MapX').value = s12MapOffsetX;
    document.getElementById('s12MapY').value = s12MapOffsetY;

    // Resetear controles del area del mapa
    document.getElementById('s12MapAreaBorder').value = 1;
    document.getElementById('s12MapAreaBorderColor').value = '#333333';

    // Resetear controles del numero
    document.getElementById('s12NumberX').value = 787;
    document.getElementById('s12NumberY').value = 8;
    document.getElementById('s12NumberSize').value = 44;

    // Resetear controles de zona
    document.getElementById('s12ZonaX').value = 775;
    document.getElementById('s12ZonaY').value = 65;
    document.getElementById('s12ZonaDotSize').value = 36;
    document.getElementById('s12ZonaTextSize').value = 10;

    // Resetear controles del formato
    document.getElementById('s12TitleSize').value = 22;
    document.getElementById('s12InstructionsSize').value = 9;

    // Aplicar transformación inicial
    applyMapTransform();

    // Actualizar campos editables
    updateS12Preview();

    // Hacer el recuadro arrastrable
    makeContentBoxDraggable();
}

function applyMapTransform() {
    const s12MapContent = document.getElementById('s12MapContent');
    const s12MapContentBox = document.getElementById('s12MapContentBox');
    if (!s12MapContent || !s12MapContentBox) return;

    const scale = s12MapScaleFactor / 100;

    // Aplicar escala al contenido
    s12MapContent.style.transform = `scale(${scale})`;

    // Posicionar el recuadro
    s12MapContentBox.style.left = s12MapOffsetX + 'px';
    s12MapContentBox.style.top = s12MapOffsetY + 'px';

    // Actualizar displays
    document.getElementById('s12MapScaleValue').textContent = s12MapScaleFactor + '%';
    document.getElementById('s12MapXValue').textContent = s12MapOffsetX + 'px';
    document.getElementById('s12MapYValue').textContent = s12MapOffsetY + 'px';
}

function makeContentBoxDraggable() {
    const box = document.getElementById('s12MapContentBox');
    const mapArea = document.querySelector('.s12-map-area');
    if (!box || !mapArea) return;

    let isDragging = false;
    let startX, startY, initialX, initialY;

    box.addEventListener('mousedown', function(e) {
        isDragging = true;
        startX = e.clientX;
        startY = e.clientY;
        initialX = s12MapOffsetX;
        initialY = s12MapOffsetY;
        box.style.cursor = 'grabbing';
        e.preventDefault();
    });

    document.addEventListener('mousemove', function(e) {
        if (!isDragging) return;
        const deltaX = e.clientX - startX;
        const deltaY = e.clientY - startY;
        s12MapOffsetX = Math.max(-100, Math.min(600, initialX + deltaX));
        s12MapOffsetY = Math.max(-100, Math.min(400, initialY + deltaY));
        document.getElementById('s12MapX').value = s12MapOffsetX;
        document.getElementById('s12MapY').value = s12MapOffsetY;
        applyMapTransform();
    });

    document.addEventListener('mouseup', function() {
        if (isDragging) {
            isDragging = false;
            box.style.cursor = 'move';
        }
    });
}

function updateMapScale(value) {
    s12MapScaleFactor = parseInt(value);
    applyMapTransform();
}

function updateMapPosition() {
    s12MapOffsetX = parseInt(document.getElementById('s12MapX').value);
    s12MapOffsetY = parseInt(document.getElementById('s12MapY').value);
    applyMapTransform();
}

function resetBoxPosition() {
    s12MapOffsetX = 50;
    s12MapOffsetY = 30;
    s12MapScaleFactor = 60;
    document.getElementById('s12MapScale').value = 60;
    document.getElementById('s12MapX').value = 50;
    document.getElementById('s12MapY').value = 30;
    applyMapTransform();
    showToast('Dibujo centrado');
}

// Funciones de control del formato S-12
function toggleSection(sectionId) {
    const content = document.getElementById(sectionId);
    const icon = document.getElementById(sectionId + 'Icon');
    if (content.classList.contains('collapsed')) {
        content.classList.remove('collapsed');
        icon.textContent = '-';
    } else {
        content.classList.add('collapsed');
        icon.textContent = '+';
    }
}

// Funciones para el area del mapa (borde grande)
function updateMapAreaBorder(value) {
    const mapArea = document.querySelector('.s12-map-area');
    if (mapArea) {
        if (parseInt(value) === 0) {
            mapArea.style.border = 'none';
        } else {
            const color = document.getElementById('s12MapAreaBorderColor').value;
            mapArea.style.border = `${value}px solid ${color}`;
        }
    }
    document.getElementById('s12MapAreaBorderValue').textContent = value + 'px';
}

function updateMapAreaBorderColor(value) {
    const mapArea = document.querySelector('.s12-map-area');
    const borderWidth = document.getElementById('s12MapAreaBorder').value;
    if (mapArea && parseInt(borderWidth) > 0) {
        mapArea.style.borderColor = value;
    }
}

// Funciones para el numero de territorio (circulo rojo)
function updateNumberPosition() {
    const numberCircle = document.getElementById('s12NumberCircle');
    const x = parseInt(document.getElementById('s12NumberX').value);
    const y = parseInt(document.getElementById('s12NumberY').value);

    numberCircle.style.left = x + 'px';
    numberCircle.style.top = y + 'px';

    document.getElementById('s12NumberXValue').textContent = x + 'px';
    document.getElementById('s12NumberYValue').textContent = y + 'px';
}

function updateNumberSize(value) {
    const numberCircle = document.getElementById('s12NumberCircle');
    const size = parseInt(value);
    numberCircle.style.width = size + 'px';
    numberCircle.style.height = size + 'px';
    numberCircle.style.fontSize = Math.round(size * 0.55) + 'px';
    document.getElementById('s12NumberSizeValue').textContent = size + 'px';
}

// Funciones para Zona a predicar
function updateZonaPosition() {
    const zonaContainer = document.querySelector('.s12-zona-container');
    const zonaName = document.getElementById('s12ZonaName');
    const x = parseInt(document.getElementById('s12ZonaX').value);
    const y = parseInt(document.getElementById('s12ZonaY').value);

    if (zonaContainer) {
        zonaContainer.style.left = x + 'px';
        zonaContainer.style.top = y + 'px';
    }
    if (zonaName) {
        zonaName.style.left = x + 'px';
    }

    document.getElementById('s12ZonaXValue').textContent = x + 'px';
    document.getElementById('s12ZonaYValue').textContent = y + 'px';
}

function updateZonaDotSize(value) {
    const zonaDot = document.getElementById('s12ZonaDot');
    if (zonaDot) {
        zonaDot.style.width = value + 'px';
        zonaDot.style.height = value + 'px';
    }
    document.getElementById('s12ZonaDotSizeValue').textContent = value + 'px';
}

function updateZonaTextSize(value) {
    const zonaText = document.querySelector('.s12-zona-text');
    if (zonaText) {
        zonaText.style.fontSize = value + 'px';
    }
    document.getElementById('s12ZonaTextSizeValue').textContent = value + 'px';
}

function updateTitleSize(value) {
    const title = document.querySelector('.s12-title');
    title.style.fontSize = value + 'px';
    document.getElementById('s12TitleSizeValue').textContent = value + 'px';
}

function updateInstructionsSize(value) {
    const instructions = document.querySelector('.s12-instructions');
    instructions.style.fontSize = value + 'px';
    document.getElementById('s12InstructionsSizeValue').textContent = value + 'px';
}

function resetAllDefaults() {
    // Resetear dibujo
    s12MapOffsetX = 50;
    s12MapOffsetY = 30;
    s12MapScaleFactor = 60;
    document.getElementById('s12MapScale').value = 60;
    document.getElementById('s12MapX').value = 50;
    document.getElementById('s12MapY').value = 30;
    applyMapTransform();

    // Resetear borde del area
    document.getElementById('s12MapAreaBorder').value = 1;
    document.getElementById('s12MapAreaBorderColor').value = '#333333';
    updateMapAreaBorder(1);

    // Resetear numero
    document.getElementById('s12NumberX').value = 787;
    document.getElementById('s12NumberY').value = 8;
    document.getElementById('s12NumberSize').value = 44;
    updateNumberPosition();
    updateNumberSize(44);

    // Resetear zona
    document.getElementById('s12ZonaX').value = 775;
    document.getElementById('s12ZonaY').value = 65;
    document.getElementById('s12ZonaDotSize').value = 36;
    document.getElementById('s12ZonaTextSize').value = 10;
    updateZonaPosition();
    updateZonaDotSize(36);
    updateZonaTextSize(10);

    // Resetear titulo e instrucciones
    document.getElementById('s12TitleSize').value = 22;
    document.getElementById('s12InstructionsSize').value = 9;
    updateTitleSize(22);
    updateInstructionsSize(9);

    showToast('Todo restablecido');
}

function updateS12Preview() {
    const localidad = document.getElementById('s12Localidad').value;
    const numero = document.getElementById('territoryNumber').value;
    const zona = document.getElementById('s12Zona').value;

    document.getElementById('s12LocalidadText').textContent = localidad;
    document.getElementById('s12NumberText').textContent = numero;

    const zonaNameEl = document.getElementById('s12ZonaName');
    if (zona) {
        zonaNameEl.textContent = zona;
        zonaNameEl.style.display = 'block';
    } else {
        zonaNameEl.style.display = 'none';
    }
}

function setZonaColor(color, swatch) {
    s12ZonaColor = color;
    document.querySelectorAll('.zona-color-swatch').forEach(s => s.classList.remove('active'));
    if (swatch) swatch.classList.add('active');

    document.getElementById('s12ZonaDot').style.background = color;
}

async function exportAs(format) {
    const s12Card = document.getElementById('s12Card');
    const num = document.getElementById('territoryNumber').value || 'territorio';
    const filename = `territorio_${num}`;

    try {
        showToast('Generando archivo...');

        const canvas = await html2canvas(s12Card, {
            backgroundColor: '#FFFFFF',
            scale: 2,
            useCORS: true,
            logging: false
        });

        if (format === 'png') {
            downloadCanvas(canvas, filename + '.png', 'image/png');
        } else if (format === 'jpg') {
            downloadCanvas(canvas, filename + '.jpg', 'image/jpeg');
        } else if (format === 'pdf') {
            const { jsPDF } = window.jspdf;
            const pdf = new jsPDF({
                orientation: 'landscape',
                unit: 'px',
                format: [canvas.width, canvas.height]
            });
            pdf.addImage(canvas.toDataURL('image/jpeg', 0.95), 'JPEG', 0, 0, canvas.width, canvas.height);
            pdf.save(filename + '.pdf');
        }

        showToast('Archivo descargado correctamente', 'success');
    } catch (error) {
        showToast('Error al exportar: ' + error.message, 'error');
    }
}

function downloadCanvas(canvas, filename, type) {
    const link = document.createElement('a');
    link.download = filename;
    link.href = canvas.toDataURL(type, 0.95);
    link.click();
}

// Atajos de teclado
document.addEventListener('keydown', function(e) {
    if (e.key === 'Delete' && selectedElement && selectedElement.type !== 'territory') {
        deleteSelected();
    }
    if (e.key === 'Escape') {
        // Si estamos dibujando un polígono, cancelar el dibujo
        if (currentTool === 'polygon' && drawingPolygonPoints.length > 0) {
            cancelPolygonDrawing();
            return;
        }

        // Deseleccionar elemento
        selectedElement = null;
        document.querySelectorAll('.canvas-element.selected').forEach(el => el.classList.remove('selected'));
        document.querySelectorAll('.internal-polygon').forEach(p => {
            p.style.strokeWidth = '2';
            p.style.stroke = '#000000';
        });
        const svg = document.getElementById('territorySvg');
        if (svg) svg.style.outline = 'none';
        document.getElementById('scaleGroup').style.display = 'none';
        updateSelectionInfo();
    }
});

// Doble-clic para terminar polígono
document.addEventListener('dblclick', function(e) {
    if (currentTool === 'polygon' && drawingPolygonPoints.length >= 3) {
        e.preventDefault();
        finishInternalPolygon();
    }
});

// CSS adicional para etiquetas del mapa
const style = document.createElement('style');
style.textContent = `
    .point-label {
        background: #e94560 !important;
        border: none !important;
        color: white !important;
        font-weight: bold !important;
        padding: 2px 6px !important;
        border-radius: 50% !important;
    }
`;
document.head.appendChild(style);
</script>
</body>
</html>
