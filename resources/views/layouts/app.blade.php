<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0d0f11">
    <title>@yield('title', 'Gestor de Congregacion')</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('css/flat-global.css') }}?v={{ time() }}">
    <script src="{{ asset('js/app.js') }}" defer></script>
    @stack('styles')
    <style>
        /* ========================================
           LAYOUT — Dark mode only
           ======================================== */

        /* --- Header --- */
        .header {
            background: #1a1d21;
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 1px solid rgba(255,255,255,0.06);
        }

        .header .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 1.25rem;
        }

        .header-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 52px;
            gap: 1.5rem;
        }

        .logo {
            font-size: 0.9375rem;
            font-weight: 600;
            color: #f1f3f5;
            text-decoration: none;
            letter-spacing: -0.01em;
            white-space: nowrap;
        }

        .logo:hover {
            color: #fff;
        }

        /* --- Navigation --- */
        .nav {
            display: flex;
            align-items: center;
            gap: 0.125rem;
        }

        .nav-link {
            padding: 0.375rem 0.75rem;
            color: rgba(241,243,245,0.55);
            text-decoration: none;
            font-size: 0.8125rem;
            font-weight: 500;
            border-radius: 6px;
            transition: color 0.15s, background 0.15s;
        }

        .nav-link:hover {
            color: rgba(241,243,245,0.9);
            background: rgba(255,255,255,0.06);
        }

        .nav-link.active {
            color: #f1f3f5;
            background: rgba(255,255,255,0.08);
        }

        /* --- Header Actions --- */
        .header-actions {
            display: flex;
            align-items: center;
            gap: 0.625rem;
        }

        .congregacion-badge {
            padding: 0.1875rem 0.625rem;
            background: rgba(107,143,199,0.15);
            color: #8aa8d6;
            border-radius: 20px;
            font-size: 0.6875rem;
            font-weight: 600;
            letter-spacing: 0.02em;
            border: 1px solid rgba(107,143,199,0.2);
        }

        .header-buttons {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .header-btn {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: background 0.15s;
            color: rgba(241,243,245,0.6);
            background: transparent;
        }

        .header-btn:hover {
            background: rgba(255,255,255,0.08);
            color: #f1f3f5;
        }

        .btn-volver {
            color: #8aa8d6;
        }

        .btn-volver:hover {
            background: rgba(107,143,199,0.15);
            color: #b8cceb;
        }

        .btn-logout {
            border: none;
            cursor: pointer;
            color: rgba(241,243,245,0.4);
        }

        .btn-logout:hover {
            background: rgba(255,80,80,0.1);
            color: #ff6b6b;
        }

        /* --- Impersonation Bar --- */
        .impersonation-bar {
            background: rgba(107,143,199,0.12);
            border-bottom: 1px solid rgba(107,143,199,0.2);
            padding: 0.375rem 1.25rem;
            text-align: center;
            font-size: 0.75rem;
            color: #8aa8d6;
        }

        .impersonation-bar a {
            color: #b8cceb;
            text-decoration: underline;
            margin-left: 0.5rem;
        }

        /* --- Flash Messages --- */
        .flash-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0.75rem 1.25rem 0;
        }

        .alert {
            padding: 0.75rem 1rem;
            border-radius: 8px;
            margin-bottom: 0.5rem;
            font-size: 0.8125rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
        }

        .alert-success {
            background: rgba(52,199,89,0.1);
            border: 1px solid rgba(52,199,89,0.2);
            color: #6ee7a0;
        }

        .alert-error {
            background: rgba(255,69,58,0.1);
            border: 1px solid rgba(255,69,58,0.2);
            color: #ff8a80;
        }

        .alert-dismiss {
            background: none;
            border: none;
            color: inherit;
            opacity: 0.5;
            cursor: pointer;
            padding: 0.25rem;
            line-height: 1;
            font-size: 1.125rem;
            flex-shrink: 0;
            transition: opacity 0.15s;
        }

        .alert-dismiss:hover {
            opacity: 1;
        }

        /* --- Main Content --- */
        .main {
            padding: 1.5rem 0;
            background: #0d0f11;
            flex: 1;
            min-height: 0;
        }

        .main .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 1.25rem;
        }

        /* --- Footer --- */
        .footer {
            background: #111315;
            border-top: 1px solid rgba(255,255,255,0.04);
            padding: 0.875rem 0;
            text-align: center;
            font-size: 0.75rem;
            color: rgba(139,147,156,0.6);
        }

        .footer .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 1.25rem;
        }

        .footer p {
            margin: 0;
        }

        /* --- Responsive --- */
        @media (max-width: 768px) {
            .header-content {
                flex-wrap: wrap;
                height: auto;
                padding: 0.5rem 0;
            }

            .nav {
                order: 3;
                width: 100%;
                justify-content: flex-start;
                flex-wrap: wrap;
                padding-top: 0.375rem;
                border-top: 1px solid rgba(255,255,255,0.06);
                margin-top: 0.375rem;
                gap: 0.125rem;
            }

            .nav-link {
                padding: 0.25rem 0.5rem;
                font-size: 0.75rem;
            }

            .congregacion-badge {
                display: none;
            }

            .main {
                padding: 1rem 0;
            }
        }
    </style>
</head>
<body style="display:flex;flex-direction:column;min-height:100vh;min-height:100dvh;">
    @if(session('usuario_original_id'))
    <div class="impersonation-bar">
        Viendo como: <strong>{{ Auth::user()->name }}</strong>
        <a href="{{ route('cambiar-usuario.volver') }}">Volver a tu cuenta</a>
    </div>
    @endif

    <header class="header">
        <div class="container">
            <div class="header-content">
                <a href="{{ route('dashboard') }}" class="logo">Gestor de Congregacion</a>

                @if(!View::hasSection('hide_nav'))
                <nav class="nav">
                    @auth
                        @php
                            $enTerritorios = request()->routeIs('panel-territorios') || request()->routeIs('territorios.*') || request()->routeIs('registros.*') || request()->routeIs('s13.*') || request()->routeIs('creador-territorios.*');
                            $enReuniones = request()->routeIs('reuniones.*');
                            $enPPOC = request()->routeIs('ppoc.*');
                            $enAdmin = request()->routeIs('administracion') || request()->routeIs('publicadores.*') || request()->routeIs('grupos-predicacion.*') || request()->routeIs('usuarios.*') || request()->is('configuracion') || request()->routeIs('congregaciones.*');
                        @endphp

                        @if(Auth::user()->isTerritoriosUser())
                            <a href="{{ route('panel-territorios') }}" class="nav-link {{ request()->routeIs('panel-territorios') || request()->routeIs('registros.*') ? 'active' : '' }}">Territorios</a>
                            @if(Auth::user()->canGenerateS13())
                            <a href="{{ route('s13.index') }}" class="nav-link {{ request()->routeIs('s13.*') ? 'active' : '' }}">S-13</a>
                            @endif
                        @elseif($enTerritorios)
                            <a href="{{ route('dashboard') }}" class="nav-link">Inicio</a>
                            <a href="{{ route('panel-territorios') }}" class="nav-link {{ request()->routeIs('panel-territorios') ? 'active' : '' }}">Panel</a>
                            <a href="{{ route('territorios.index') }}" class="nav-link {{ request()->routeIs('territorios.*') ? 'active' : '' }}">Todos</a>
                            <a href="{{ route('registros.index') }}" class="nav-link {{ request()->routeIs('registros.*') ? 'active' : '' }}">Asignaciones</a>
                            <a href="{{ route('s13.index') }}" class="nav-link {{ request()->routeIs('s13.*') ? 'active' : '' }}">S-13</a>
                        @elseif($enReuniones)
                            <a href="{{ route('dashboard') }}" class="nav-link">Inicio</a>
                            <a href="{{ route('reuniones.index') }}" class="nav-link {{ request()->routeIs('reuniones.index') || request()->routeIs('reuniones.edit') || request()->routeIs('reuniones.show') || request()->routeIs('reuniones.create') ? 'active' : '' }}">VyM</a>
                            <a href="{{ route('reuniones.asignaciones') }}" class="nav-link {{ request()->routeIs('reuniones.asignaciones') || request()->routeIs('reuniones.historial') ? 'active' : '' }}">Asignaciones</a>
                            <a href="{{ route('reuniones.autorizaciones') }}" class="nav-link {{ request()->routeIs('reuniones.autorizaciones') ? 'active' : '' }}">Autorizaciones</a>
                            <a href="{{ route('reuniones.generos') }}" class="nav-link {{ request()->routeIs('reuniones.generos') ? 'active' : '' }}">Generos</a>
                        @elseif($enPPOC)
                            <a href="{{ route('dashboard') }}" class="nav-link">Inicio</a>
                            <a href="{{ route('ppoc.calendario') }}" class="nav-link active">PPOC</a>
                        @elseif($enAdmin)
                            <a href="{{ route('dashboard') }}" class="nav-link">Inicio</a>
                            <a href="{{ route('administracion') }}" class="nav-link {{ request()->routeIs('administracion') ? 'active' : '' }}">Panel</a>
                            <a href="{{ route('publicadores.index') }}" class="nav-link {{ request()->routeIs('publicadores.*') ? 'active' : '' }}">Publicadores</a>
                            <a href="{{ route('grupos-predicacion.index') }}" class="nav-link {{ request()->routeIs('grupos-predicacion.*') ? 'active' : '' }}">Grupos</a>
                            <a href="{{ route('configuracion') }}" class="nav-link {{ request()->is('configuracion') ? 'active' : '' }}">Config</a>
                        @else
                            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">Inicio</a>
                            <a href="{{ route('panel-territorios') }}" class="nav-link">Territorios</a>
                            @if(Auth::user()->canAccessPPOC())
                            <a href="{{ route('ppoc.calendario') }}" class="nav-link">PPOC</a>
                            @endif
                            @if(Auth::user()->isAdmin())
                            <a href="{{ route('reuniones.index') }}" class="nav-link">Reuniones</a>
                            <a href="{{ route('administracion') }}" class="nav-link">Administracion</a>
                            @endif
                            @can('superadmin')
                            <a href="{{ route('congregaciones.index') }}" class="nav-link {{ request()->routeIs('congregaciones.*') ? 'active' : '' }}">Congregaciones</a>
                            @endcan
                        @endif
                    @endauth
                </nav>
                @endif

                <div class="header-actions">
                    @auth
                        @if(isset($congregacionActiva))
                        <span class="congregacion-badge">{{ $congregacionActiva->nombre }}</span>
                        @endif

                        <div class="header-buttons">
                            <a href="{{ route('cambiar-usuario.index') }}" class="header-btn" title="Cambiar usuario">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><path d="M20 8v6M23 11h-6"></path></svg>
                            </a>
                            <a href="{{ route('perfil.index') }}" class="header-btn" title="Mi perfil">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                            </a>
                            <form action="{{ route('logout') }}" method="POST" style="margin:0">
                                @csrf
                                <button type="submit" class="header-btn btn-logout" title="Salir">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                                </button>
                            </form>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="nav-link">Iniciar Sesion</a>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    @if(session('success'))
    <div class="flash-container">
        <div class="alert alert-success">
            <span>{{ session('success') }}</span>
            <button type="button" class="alert-dismiss" onclick="this.parentElement.remove()">&times;</button>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="flash-container">
        <div class="alert alert-error">
            <span>{{ session('error') }}</span>
            <button type="button" class="alert-dismiss" onclick="this.parentElement.remove()">&times;</button>
        </div>
    </div>
    @endif

    @php
        $rutaActual = request()->route()?->getName() ?? '';
        $submenus = [
            'reuniones' => 'partials.submenu-reuniones',
        ];
        $submenuVista = null;
        foreach ($submenus as $prefijo => $vista) {
            if (str_starts_with($rutaActual, $prefijo . '.')) {
                $submenuVista = $vista;
                break;
            }
        }
    @endphp

    @if($submenuVista)
        @include($submenuVista)
    @endif

    <main class="main">
        <div class="container">
            @yield('content')
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; {{ date('Y') }} Gestor de Congregacion</p>
        </div>
    </footer>

    @stack('scripts')

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.clickable-row[data-href]').forEach(function(row) {
                row.addEventListener('click', function(e) {
                    if (e.target.closest('a, button, input, select')) return;
                    if (e.ctrlKey || e.metaKey) {
                        window.open(this.dataset.href, '_blank');
                    } else {
                        window.location = this.dataset.href;
                    }
                });
            });
        });
    </script>
</body>
</html>
