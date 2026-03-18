<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar Sesion - Sistema de Territorios</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: #0d0f11;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .login-container {
            background: #151719;
            border-radius: 1rem;
            border: 1px solid #2d3339;
            padding: 2.5rem;
            width: 100%;
            max-width: 400px;
        }
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-header .icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        .login-header h1 {
            font-size: 1.5rem;
            color: #f1f3f5;
            margin-bottom: 0.5rem;
        }
        .login-header p {
            color: #8b939c;
            font-size: 0.875rem;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            font-weight: 500;
            color: #d8dce1;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }
        .form-group input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #3d454d;
            border-radius: 0.5rem;
            font-size: 1rem;
            background: #1a1d21;
            color: #f1f3f5;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-group input:focus {
            outline: none;
            border-color: #6b8fc7;
            box-shadow: 0 0 0 3px rgba(107,143,199,0.15);
        }
        .form-group input::placeholder {
            color: #5c656e;
        }
        .form-group.error input {
            border-color: #ef4444;
        }
        .error-message {
            color: #ef4444;
            font-size: 0.75rem;
            margin-top: 0.25rem;
        }
        .remember-me {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }
        .remember-me input[type="checkbox"] {
            width: 1rem;
            height: 1rem;
            accent-color: #6b8fc7;
        }
        .remember-me label {
            font-size: 0.875rem;
            color: #8b939c;
        }
        .btn-login {
            width: 100%;
            padding: 0.875rem 1rem;
            background: #6b8fc7;
            color: #121416;
            border: none;
            border-radius: 0.5rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s, transform 0.2s;
        }
        .btn-login:hover {
            background: #8aa8d6;
            transform: translateY(-1px);
        }
        .btn-login:active {
            transform: translateY(0);
        }
        .alert {
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
        }
        .alert-error {
            background: rgba(239,68,68,0.1);
            border: 1px solid rgba(239,68,68,0.3);
            color: #f87171;
        }
        .footer-text {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.75rem;
            color: #5c656e;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="icon">🗺️</div>
            <h1>Sistema de Territorios</h1>
            <p>Inicia sesion para continuar</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-error">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" id="login-form">
            @csrf

            <div class="form-group {{ $errors->has('name') ? 'error' : '' }}">
                <label for="name">Congregacion</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="Ej: Centro Santa Coloma" required autofocus>
            </div>

            <div class="form-group {{ $errors->has('password') ? 'error' : '' }}">
                <label for="password">Contrasena</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="remember-me">
                <input type="checkbox" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                <label for="remember">Recordarme</label>
            </div>

            <button type="submit" class="btn-login" id="login-btn">
                Iniciar Sesion
            </button>
        </form>

        <script>
            document.getElementById('login-form').addEventListener('submit', function(e) {
                e.preventDefault();
                var form = this;
                var btn = document.getElementById('login-btn');
                btn.disabled = true;
                btn.textContent = 'Verificando...';

                // Always fetch a fresh CSRF token before submitting
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
                    // If fetch fails, submit anyway with the existing token
                    form.submit();
                });
            });
        </script>

        <p class="footer-text">
            Sistema de Gestion de Territorios &copy; {{ date('Y') }}
        </p>
    </div>
</body>
</html>
