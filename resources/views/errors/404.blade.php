<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Página no encontrada - Territorios</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
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
        .container {
            max-width: 500px;
            text-align: center;
        }
        .card {
            background: #262640;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }
        .icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        h1 {
            font-size: 1.5rem;
            color: #4a6da7;
            margin-bottom: 1rem;
        }
        p {
            color: #a3a3a3;
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }
        .btn {
            display: inline-block;
            padding: 0.875rem 1.5rem;
            font-size: 1rem;
            font-weight: 600;
            background: linear-gradient(135deg, #4a6da7 0%, #3d5a8a 100%);
            color: white;
            border: none;
            border-radius: 10px;
            text-decoration: none;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            margin: 0.5rem;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(74, 109, 167, 0.4);
        }
        .btn-secondary {
            background: #404050;
        }
        .hint {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #404050;
            font-size: 0.85rem;
            color: #737373;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="icon">🔍</div>
            <h1>Página no encontrada</h1>
            <p>La página que buscas no existe o el enlace ha cambiado.</p>

            <a href="/" class="btn">Ir al inicio</a>
            <a href="javascript:location.reload(true)" class="btn btn-secondary">Reintentar</a>

            <div class="hint">
                <strong>¿Problemas para acceder?</strong><br>
                Prueba a recargar la página o contacta al administrador de tu congregación.
            </div>
        </div>
    </div>
</body>
</html>
