<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <title>Iniciar Sesión - Gestor de Congregación</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        html, body {
            overflow: hidden;
            overscroll-behavior: none;
            position: fixed;
            width: 100%;
            height: 100%;
            height: 100dvh;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: #0d0f11;
            color: #f1f3f5;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        .login-wrapper {
            width: 100%;
            max-width: 380px;
        }

        .login-brand {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .login-brand svg {
            width: 40px;
            height: 40px;
            color: #6b8fc7;
            margin-bottom: 1.25rem;
        }

        .login-brand h1 {
            font-size: 1.375rem;
            font-weight: 700;
            color: #f1f3f5;
            letter-spacing: -0.02em;
        }

        .login-brand p {
            color: #5c656e;
            font-size: 0.875rem;
            margin-top: 0.375rem;
        }

        .login-form-group {
            margin-bottom: 1.25rem;
        }

        .login-form-group label {
            display: block;
            font-weight: 500;
            color: #8b939c;
            margin-bottom: 0.5rem;
            font-size: 0.8125rem;
            letter-spacing: 0.01em;
        }

        .login-form-group input {
            width: 100%;
            padding: 0.6875rem 0.875rem;
            border: 1px solid #2d3339;
            border-radius: 6px;
            font-size: 0.9375rem;
            background: #151719;
            color: #f1f3f5;
            transition: border-color 0.15s, box-shadow 0.15s;
        }

        .login-form-group input:focus {
            outline: none;
            border-color: #6b8fc7;
            box-shadow: 0 0 0 3px rgba(107,143,199,0.12);
        }

        .login-form-group input::placeholder {
            color: #3d454d;
        }

        .login-hint {
            color: #6b7682;
            font-size: 0.75rem;
            margin-top: 0.375rem;
            line-height: 1.4;
        }

        .login-form-group.error input {
            border-color: #ef4444;
        }

        .login-error-msg {
            color: #f87171;
            font-size: 0.75rem;
            margin-top: 0.375rem;
        }

        .login-options {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.75rem;
        }

        .login-options input[type="checkbox"] {
            width: 0.875rem;
            height: 0.875rem;
            accent-color: #6b8fc7;
            cursor: pointer;
        }

        .login-options label {
            font-size: 0.8125rem;
            color: #5c656e;
            cursor: pointer;
        }

        .login-btn {
            width: 100%;
            padding: 0.6875rem 1rem;
            background: #6b8fc7;
            color: #0d0f11;
            border: none;
            border-radius: 6px;
            font-size: 0.9375rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.15s;
            letter-spacing: -0.01em;
        }

        .login-btn:hover {
            background: #8aa8d6;
        }

        .login-btn:disabled {
            opacity: 0.6;
            cursor: wait;
        }

        .login-alert {
            padding: 0.625rem 0.875rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
            font-size: 0.8125rem;
            background: rgba(239,68,68,0.08);
            border: 1px solid rgba(239,68,68,0.2);
            color: #f87171;
        }

        .login-footer {
            text-align: center;
            margin-top: 2rem;
            font-size: 0.75rem;
            color: #3d454d;
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-brand">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 00-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0z"/>
            </svg>
            <h1>Gestor de Congregación</h1>
            <p>Inicia sesión para continuar</p>
        </div>

        @if ($errors->any())
            <div class="login-alert">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" id="login-form">
            @csrf

            <div class="login-form-group {{ $errors->has('name') ? 'error' : '' }}">
                <label for="name">Usuario</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="Tu nombre de acceso" required autofocus>
                <p class="login-hint">El responsable de la congregación te facilita tu usuario y contraseña.</p>
            </div>

            <div class="login-form-group {{ $errors->has('password') ? 'error' : '' }}">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="login-options">
                <input type="checkbox" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                <label for="remember">Recordarme</label>
            </div>

            <button type="submit" class="login-btn" id="login-btn">
                Iniciar Sesión
            </button>
        </form>

        <script>
            document.getElementById('login-form').addEventListener('submit', function(e) {
                e.preventDefault();
                var form = this;
                var btn = document.getElementById('login-btn');
                btn.disabled = true;
                btn.textContent = 'Verificando...';

                fetch('{{ url("/refresh-csrf") }}', {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin'
                })
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    if (data.token) {
                        form.querySelector('input[name="_token"]').value = data.token;
                    }
                    form.removeEventListener('submit', arguments.callee);
                    form.submit();
                })
                .catch(function() {
                    form.submit();
                });
            });
        </script>

        <p class="login-footer">
            Gestor de Congregación &copy; {{ date('Y') }}
        </p>
    </div>
</body>
</html>
