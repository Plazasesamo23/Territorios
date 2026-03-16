<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Gestor de Congregacion')</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/flat-global.css') }}">
    <script src="{{ asset('js/app.js') }}" defer></script>
    <style>
        /* ========================================
           LAYOUT STYLES - Solo azul, gris, blanco, negro
           ======================================== */

        /* Congregacion Badge */
        .congregacion-badge {
            padding: 0.25rem 0.75rem;
            background: #4a6da7;
            color: white;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        [data-theme="dark"] .congregacion-badge {
            background: #6b8fc7;
            color: #121416;
        }

        /* Header Buttons */
        .header-buttons {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .header-btn {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all 0.2s;
            font-size: 1rem;
            background: #4a6da7;
            color: white;
        }

        .header-btn:hover {
            background: #3d5a8a;
            transform: scale(1.05);
        }

        [data-theme="dark"] .header-btn {
            background: #6b8fc7;
            color: #121416;
        }

        [data-theme="dark"] .header-btn:hover {
            background: #8aa8d6;
        }

        /* Boton volver - gris oscuro para destacar */
        .btn-volver {
            background: #495057;
        }

        .btn-volver:hover {
            background: #343a40;
        }

        [data-theme="dark"] .btn-volver {
            background: #8b939c;
        }

        [data-theme="dark"] .btn-volver:hover {
            background: #b8bfc7;
        }

        /* Boton logout - gris mas oscuro */
        .btn-logout {
            background: #6c757d;
            border: none;
            cursor: pointer;
        }

        .btn-logout:hover {
            background: #495057;
        }

        [data-theme="dark"] .btn-logout {
            background: #5c656e;
        }

        [data-theme="dark"] .btn-logout:hover {
            background: #8b939c;
        }

        /* Header Principal */
        .header {
            background: #4a6da7;
            padding: 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        [data-theme="dark"] .header {
            background: #1a1d21;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        }

        .header .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .header-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 56px;
            gap: 1rem;
        }

        .logo {
            font-size: 1.125rem;
            font-weight: 700;
            color: white;
            text-decoration: none;
        }

        [data-theme="dark"] .logo {
            color: #f1f3f5;
        }

        /* Navegacion */
        .nav {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .nav-link {
            padding: 0.5rem 0.875rem;
            color: rgba(255,255,255,0.85);
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            border-radius: 6px;
            transition: all 0.2s;
        }

        .nav-link:hover {
            background: rgba(255,255,255,0.15);
            color: white;
        }

        .nav-link.active {
            background: rgba(255,255,255,0.2);
            color: white;
        }

        [data-theme="dark"] .nav-link {
            color: rgba(241,243,245,0.75);
        }

        [data-theme="dark"] .nav-link:hover,
        [data-theme="dark"] .nav-link.active {
            background: rgba(107,143,199,0.2);
            color: #f1f3f5;
        }

        /* Header Actions */
        .header-actions {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        /* Theme Toggle */
        .theme-toggle {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: none;
            background: rgba(255,255,255,0.15);
            color: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            transition: all 0.2s;
        }

        .theme-toggle:hover {
            background: rgba(255,255,255,0.25);
        }

        [data-theme="dark"] .theme-toggle {
            background: rgba(107,143,199,0.2);
        }

        [data-theme="dark"] .theme-toggle:hover {
            background: rgba(107,143,199,0.3);
        }

        /* Main Content */
        .main {
            min-height: calc(100vh - 56px - 60px);
            padding: 1.5rem 0;
            background: #f1f3f5;
        }

        [data-theme="dark"] .main {
            background: #0d0f11;
        }

        .main .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        /* Footer */
        .footer {
            background: #ffffff;
            border-top: 1px solid #dee2e6;
            padding: 1rem 0;
            text-align: center;
            font-size: 0.8rem;
            color: #6c757d;
        }

        [data-theme="dark"] .footer {
            background: #151719;
            border-top-color: #2d3339;
            color: #8b939c;
        }

        .footer .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .footer p {
            margin: 0;
        }

        /* Alertas */
        .alert {
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1rem;
        }

        .alert-success {
            background: #e8eef6;
            border: 1px solid #4a6da7;
            color: #2d4266;
        }

        [data-theme="dark"] .alert-success {
            background: rgba(107,143,199,0.15);
            border-color: #6b8fc7;
            color: #b8bfc7;
        }

        .alert-error {
            background: #f8f9fa;
            border: 1px solid #495057;
            color: #212529;
        }

        [data-theme="dark"] .alert-error {
            background: rgba(139,147,156,0.15);
            border-color: #8b939c;
            color: #d8dce1;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header-content {
                flex-wrap: wrap;
                height: auto;
                padding: 0.75rem 0;
            }

            .nav {
                order: 3;
                width: 100%;
                justify-content: center;
                flex-wrap: wrap;
                padding-top: 0.5rem;
                border-top: 1px solid rgba(255,255,255,0.1);
                margin-top: 0.5rem;
            }

            .nav-link {
                padding: 0.375rem 0.625rem;
                font-size: 0.8rem;
            }

            .congregacion-badge {
                display: none;
            }
        }
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
                            @if(Auth::user()->canGenerateS13())
                            <a href="{{ route('s13.index') }}" class="nav-link {{ request()->routeIs('s13.*') ? 'active' : '' }}">S-13</a>
                            @endif
                        @else
                            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">Inicio</a>
                            <a href="{{ route('panel-territorios') }}" class="nav-link {{ request()->routeIs('panel-territorios') || request()->routeIs('registros.*') || request()->routeIs('territorios.*') ? 'active' : '' }}">Territorios</a>
                            @if(Auth::user()->canAccessPPOC())
                            <a href="{{ route('ppoc.calendario') }}" class="nav-link {{ request()->routeIs('ppoc.*') ? 'active' : '' }}">PPOC</a>
                            @endif
                            @if(Auth::user()->canGenerateS13() && !Auth::user()->isAdmin())
                            <a href="{{ route('s13.index') }}" class="nav-link {{ request()->routeIs('s13.*') ? 'active' : '' }}">S-13</a>
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
                            <a href="{{ route('cambiar-usuario.index') }}" class="header-btn" title="Cambiar usuario">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><path d="M20 8v6M23 11h-6"></path></svg>
                            </a>
                            @if(session('usuario_original_id'))
                            <a href="{{ route('cambiar-usuario.volver') }}" class="header-btn btn-volver" title="Volver a tu cuenta">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 14l-5-5 5-5"></path><path d="M4 9h10.5a5.5 5.5 0 0 1 5.5 5.5v0a5.5 5.5 0 0 1-5.5 5.5H11"></path></svg>
                            </a>
                            @endif
                            <a href="{{ route('perfil.index') }}" class="header-btn" title="Mi perfil">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                            </a>
                            <form action="{{ route('logout') }}" method="POST" style="margin:0;">
                                @csrf
                                <button type="submit" class="header-btn btn-logout" title="Salir">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
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

    @auth
        @if(request()->routeIs('panel-territorios') || request()->routeIs('territorios.*') || request()->routeIs('registros.*') || request()->routeIs('s13.*') || request()->routeIs('creador-territorios.*'))
            @include('layouts.partials.submenu-territorios')
        @elseif(request()->routeIs('ppoc.*'))
            @include('layouts.partials.submenu-ppoc')
        @elseif(request()->routeIs('administracion') || request()->routeIs('publicadores.*') || request()->routeIs('usuarios.*') || request()->routeIs('grupos-predicacion.*') || request()->is('configuracion') || request()->routeIs('congregaciones.*'))
            @include('layouts.partials.submenu-admin')
        @endif
    @endauth

    @if(session('success'))
    <div class="container" style="margin-top:1rem;">
        <div class="alert alert-success">{{ session('success') }}</div>
    </div>
    @endif

    @if(session('error'))
    <div class="container" style="margin-top:1rem;">
        <div class="alert alert-error">{{ session('error') }}</div>
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

            // Clickable table rows - supports right-click/new tab
            document.querySelectorAll('.clickable-row[data-href]').forEach(row => {
                row.addEventListener('click', function(e) {
                    if (e.target.tagName === 'A' || e.target.tagName === 'BUTTON') return;
                    if (e.ctrlKey || e.metaKey) {
                        window.open(this.dataset.href, '_blank');
                    } else {
                        window.location = this.dataset.href;
                    }
                });
            });

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
