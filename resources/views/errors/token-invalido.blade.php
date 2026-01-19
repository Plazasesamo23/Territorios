<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <title>Enlace no válido - Territorios</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            color: #f5f5f5;
        }
        .container { max-width: 500px; text-align: center; }
        .card {
            background: #262640;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }
        .icon { font-size: 4rem; margin-bottom: 1rem; }
        h1 { font-size: 1.5rem; color: #f87171; margin-bottom: 1rem; }
        p { color: #a3a3a3; margin-bottom: 1rem; line-height: 1.6; }
        .sugerencia {
            background: #1a1a2e;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
            font-size: 0.9rem;
        }
        .btn {
            display: inline-block;
            padding: 0.875rem 1.5rem;
            margin-top: 1.5rem;
            font-size: 1rem;
            font-weight: 600;
            background: linear-gradient(135deg, #4a6da7 0%, #3d5a8a 100%);
            color: white;
            border: none;
            border-radius: 10px;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="icon">⚠️</div>
            <h1>{{ $mensaje ?? 'Enlace no válido' }}</h1>
            <p>{{ $sugerencia ?? 'Por favor contacta al administrador de tu congregación.' }}</p>
            <div class="sugerencia">
                <strong>¿Qué puedo hacer?</strong><br>
                Pide al encargado de PPOC que te envíe el enlace actualizado.
            </div>
            <a href="/" class="btn">Ir al inicio</a>
        </div>
    </div>
</body>
</html>
