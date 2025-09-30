<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Leaflet Simple</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        body {
            margin: 0;
            padding: 20px;
            font-family: Arial, sans-serif;
        }
        #map {
            height: 600px;
            width: 100%;
            border: 2px solid #333;
        }
        #status {
            padding: 10px;
            margin-bottom: 20px;
            background: #f0f0f0;
            border-radius: 5px;
        }
        .log {
            font-family: monospace;
            font-size: 12px;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <h1>🧪 Test de Leaflet - Versión Simple</h1>

    <div id="status">
        <div class="log">Cargando...</div>
    </div>

    <div id="map"></div>

    <!-- Cargar Leaflet ANTES de nuestro script -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        const statusDiv = document.getElementById('status');

        function log(message, color = 'black') {
            console.log(message);
            const logDiv = document.createElement('div');
            logDiv.className = 'log';
            logDiv.style.color = color;
            logDiv.textContent = message;
            statusDiv.appendChild(logDiv);
        }

        // Verificar inmediatamente
        log('🔍 Verificando Leaflet...', 'blue');
        log('  typeof L: ' + typeof L);
        log('  typeof window.L: ' + typeof window.L);

        if (typeof L === 'undefined') {
            log('❌ L no está definido', 'red');
            log('⏳ Esperando 1 segundo...', 'orange');

            setTimeout(() => {
                log('🔍 Verificando nuevamente...', 'blue');
                log('  typeof L: ' + typeof L);
                log('  typeof window.L: ' + typeof window.L);

                if (typeof L !== 'undefined') {
                    inicializarMapa();
                } else {
                    log('❌ Leaflet no se cargó correctamente', 'red');
                    log('💡 Posibles causas:', 'orange');
                    log('  - Firewall bloqueando unpkg.com', 'gray');
                    log('  - Sin conexión a internet', 'gray');
                    log('  - Antivirus bloqueando scripts', 'gray');
                }
            }, 1000);
        } else {
            log('✅ Leaflet cargado correctamente!', 'green');
            log('  Versión: ' + L.version, 'green');
            inicializarMapa();
        }

        function inicializarMapa() {
            try {
                log('📍 Creando mapa...', 'blue');

                const map = L.map('map').setView([41.4534, 2.2081], 16);

                log('✅ Mapa creado', 'green');

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors'
                }).addTo(map);

                log('✅ Tiles agregadas', 'green');

                L.marker([41.4534, 2.2081])
                    .addTo(map)
                    .bindPopup('📍 Santa Coloma de Gramenet')
                    .openPopup();

                log('✅ Marcador agregado', 'green');
                log('🎉 ¡Mapa funcionando correctamente!', 'green');

            } catch (error) {
                log('❌ Error al crear mapa: ' + error.message, 'red');
                console.error(error);
            }
        }
    </script>
</body>
</html>
