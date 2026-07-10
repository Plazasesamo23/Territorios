<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verificar Acceso - {{ $congregacion->nombre }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #0d0f11;
            padding: 1rem;
        }
        .verify-container {
            background: #151719;
            border-radius: 1rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.4); border: 1px solid #2d3339;
            padding: 2.5rem;
            width: 100%;
            max-width: 420px;
        }
        .verify-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .verify-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        .verify-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #f1f3f5;
            margin-bottom: 0.5rem;
        }
        .verify-subtitle {
            color: #8b939c;
            font-size: 0.95rem;
        }
        .congregation-name {
            display: inline-block;
            background: linear-gradient(135deg, #4a6da7 0%, #3d5a8a 100%);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            font-weight: 600;
            margin-top: 1rem;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-label {
            display: block;
            font-weight: 600;
            color: #f1f3f5;
            margin-bottom: 0.5rem;
        }
        .form-input {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid #2d3339; background: #0d0f11; color: #f1f3f5;
            border-radius: 0.5rem;
            font-size: 1rem;
            transition: all 0.2s;
            box-sizing: border-box;
        }
        .form-input:focus {
            outline: none;
            border-color: #4a6da7;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }
        .btn-verify {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #4a6da7 0%, #3d5a8a 100%);
            color: white;
            border: none;
            border-radius: 0.5rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-verify:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.3);
        }
        .error-message {
            background: rgba(239,68,68,0.12);
            border: 1px solid rgba(239,68,68,0.4);
            color: #f87171;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
        }
        .logout-link {
            display: block;
            text-align: center;
            margin-top: 1.5rem;
            color: #8b939c;
            text-decoration: none;
            font-size: 0.875rem;
        }
        .logout-link:hover {
            color: #f1f3f5;
        }
    </style>
</head>
<body>
    <div class="verify-container">
        <div class="verify-header">
            <div class="verify-icon">🔐</div>
            <h1 class="verify-title">Verificar Acceso</h1>
            <p class="verify-subtitle">Escribe la contraseña de tu congregación para continuar</p>
            <div class="congregation-name">{{ $congregacion->nombre }}</div>
        </div>

        @if($errors->any())
            <div class="error-message">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('congregacion.verificar-password') }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label" for="password">Contraseña de la Congregación</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-input"
                    placeholder="Ingresa la contraseña"
                    autofocus
                    required
                >
            </div>

            <button type="submit" class="btn-verify">
                Verificar y Continuar
            </button>
        </form>

        <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
            @csrf
            <button type="submit" class="logout-link" style="background: none; border: none; cursor: pointer; width: 100%;">
                Cerrar sesión y volver al inicio
            </button>
        </form>
    </div>
</body>
</html>
