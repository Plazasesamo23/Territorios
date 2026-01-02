<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Gestor de Congregacion')</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/responsive.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dark-theme.css') }}">
    <link rel="stylesheet" href="{{ asset('css/fixes.css') }}">
    <script src="{{ asset('js/app.js') }}" defer></script>
    <style>
        .congregacion-badge {
            padding: 0.25rem 0.75rem;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .header-buttons {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .header-btn {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: transform 0.2s;
            font-size: 1.1rem;
        }
        .header-btn:hover {
            transform: scale(1.1);
        }
        .btn-switch { background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%); }
        .btn-volver { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); animation: pulse 2s infinite; }
        .btn-profile { background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); }
        .btn-logout { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); border: none; cursor: pointer; }
        @keyframes pulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.4); }
            50% { box-shadow: 0 0 0 8px rgba(245, 158, 11, 0); }
        }
        [data-theme="dark"] .btn-switch { background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); }
        [data-theme="dark"] .btn-volver { background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); }
        [data-theme="dark"] .congregacion-badge { background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); }
    </style>
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <a href="{{ route('dashboard') }}" class="logo">Gestor de Congregacion</a>

                <nav class="nav">
                    @auth
                        @if(Auth::user()->isTerritoriosUser())
                            <a href="{{ route('panel-territorios') }}" class="nav-link {{ request()->routeIs('panel-territorios') || request()->routeIs('registros.*') ? 'active' : '' }}">Territorios</a>
                        @else
                            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">Inicio</a>
                            <a href="{{ route('panel-territorios') }}" class="nav-link {{ request()->routeIs('panel-territorios') || request()->routeIs('registros.*') || request()->routeIs('territorios.*') ? 'active' : '' }}">Territorios</a>
                            @if(Auth::user()->canAccessPPOC())
                            <a href="{{ route('ppoc.calendario') }}" class="nav-link {{ request()->routeIs('ppoc.*') ? 'active' : '' }}">PPOC</a>
                            @endif
                            @if(Auth::user()->isAdmin())
                            <a href="{{ route('administracion') }}" class="nav-link {{ request()->routeIs('administracion') || request()->routeIs('publicadores.*') || request()->routeIs('grupos-predicacion.*') || request()->routeIs('s13.*') || request()->routeIs('usuarios.*') || request()->routeIs('configuracion') || request()->routeIs('creador-territorios.*') ? 'active' : '' }}">Administracion</a>
                            @endif
                            @can('superadmin')
                            <a href="{{ route('congregaciones.index') }}" class="nav-link {{ request()->routeIs('congregaciones.*') ? 'active' : '' }}">Congregaciones</a>
                            @endcan
                        @endif
                    @endauth
                </nav>

                <div class="header-actions">
                    @auth
                        @if(isset($congregacionActiva))
                        <span class="congregacion-badge">{{ $congregacionActiva->nombre }}</span>
                        @endif

                        <div class="header-buttons">
                            <a href="{{ route('cambiar-usuario.index') }}" class="header-btn btn-switch" title="Cambiar usuario">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><path d="M20 8v6M23 11h-6"></path></svg>
                            </a>
                            @if(session('usuario_original_id'))
                            <a href="{{ route('cambiar-usuario.volver') }}" class="header-btn btn-volver" title="Volver a tu cuenta">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M9 14l-5-5 5-5"></path><path d="M4 9h10.5a5.5 5.5 0 0 1 5.5 5.5v0a5.5 5.5 0 0 1-5.5 5.5H11"></path></svg>
                            </a>
                            @endif
                            <a href="{{ route('perfil.index') }}" class="header-btn btn-profile" title="Mi perfil">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                            </a>
                            <form action="{{ route('logout') }}" method="POST" style="margin:0;">
                                @csrf
                                <button type="submit" class="header-btn btn-logout" title="Salir">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                                </button>
                            </form>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-outline">Iniciar Sesion</a>
                    @endauth

                    <button id="theme-toggle" class="theme-toggle" title="Cambiar tema">
                        <span id="theme-icon">🌙</span>
                    </button>
                </div>
            </div>
        </div>
    </header>

    @if(session('success'))
    <div class="container" style="margin-top:1rem;">
        <div class="alert alert-success" style="background:#d1fae5;border:1px solid #10b981;color:#065f46;padding:1rem;border-radius:0.5rem;">{{ session('success') }}</div>
    </div>
    @endif

    @if(session('error'))
    <div class="container" style="margin-top:1rem;">
        <div class="alert alert-error" style="background:#fee2e2;border:1px solid #ef4444;color:#991b1b;padding:1rem;border-radius:0.5rem;">{{ session('error') }}</div>
    </div>
    @endif

    <main class="main">
        <div class="container">
            @yield('content')
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; {{ date('Y') }} Gestor de Congregacion @if(isset($congregacionActiva)) - {{ $congregacionActiva->nombre }} @endif</p>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggle = document.getElementById('theme-toggle');
            const icon = document.getElementById('theme-icon');
            const saved = localStorage.getItem('theme') || 'light';
            setTheme(saved);

            toggle.addEventListener('click', function() {
                const current = document.body.getAttribute('data-theme') || 'light';
                const next = current === 'light' ? 'dark' : 'light';
                setTheme(next);
                localStorage.setItem('theme', next);
            });

            function setTheme(t) {
                if (t === 'dark') {
                    document.body.setAttribute('data-theme', 'dark');
                    icon.textContent = '☀️';
                } else {
                    document.body.removeAttribute('data-theme');
                    icon.textContent = '🌙';
                }
            }

            setTimeout(() => {
                document.querySelectorAll('.alert').forEach(a => {
                    a.style.transition = 'opacity 0.5s';
                    a.style.opacity = '0';
                    setTimeout(() => a.remove(), 500);
                });
            }, 5000);
        });
    </script>
</body>
</html>
