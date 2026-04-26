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

        /* --- Widget tareas (popup ancladas) --- */
        .widget-tareas-wrap { position: relative; }
        .widget-tareas-btn { position: relative; }
        .widget-badge {
            position: absolute; top: -2px; right: -2px;
            background: #ff6b6b; color: #fff;
            font-size: 0.6rem; font-weight: 700;
            min-width: 16px; height: 16px;
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            padding: 0 4px; line-height: 1;
            border: 2px solid #1a1d21;
        }
        .widget-dropdown {
            display: none;
            position: absolute; top: calc(100% + 6px); right: 0;
            width: 340px; max-width: calc(100vw - 1rem);
            max-height: 480px; overflow-y: auto;
            background: #1a1d21;
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 10px;
            box-shadow: 0 12px 32px rgba(0,0,0,0.5);
            z-index: 1500;
        }
        .widget-dropdown.abierto { display: block; }
        .widget-header {
            display: flex; align-items: center; justify-content: space-between;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.06);
            font-size: 0.8125rem; color: #f1f3f5;
        }
        .widget-ver-todas {
            font-size: 0.75rem; color: #8aa8d6;
            text-decoration: none;
        }
        .widget-ver-todas:hover { color: #b8cceb; }
        .widget-contenido { padding: 0.25rem 0; }
        .widget-loading {
            padding: 1.5rem 1rem; text-align: center;
            color: rgba(241,243,245,0.4); font-size: 0.8125rem;
        }
        .widget-empty {
            padding: 2rem 1rem; text-align: center;
            color: rgba(241,243,245,0.5); font-size: 0.8125rem;
        }
        .widget-empty-icon { font-size: 1.5rem; margin-bottom: 0.5rem; }
        .widget-empty p { margin: 0 0 0.25rem 0; color: rgba(241,243,245,0.7); }
        .widget-empty small { font-size: 0.6875rem; opacity: 0.7; }

        .widget-lista { list-style: none; margin: 0; padding: 0; }
        .widget-item {
            display: grid;
            grid-template-columns: auto 1fr auto;
            gap: 0.5rem;
            align-items: center;
            padding: 0.625rem 0.875rem;
            border-bottom: 1px solid rgba(255,255,255,0.04);
        }
        .widget-item:last-child { border-bottom: none; }
        .widget-item.vencida { background: rgba(255,69,58,0.06); }

        .widget-check-form, .widget-anclar-form { margin: 0; }
        .widget-check {
            width: 18px; height: 18px;
            border: 2px solid rgba(255,255,255,0.2);
            border-radius: 5px;
            background: transparent; cursor: pointer;
            transition: all 0.15s;
        }
        .widget-check:hover { border-color: #34c759; background: rgba(52,199,89,0.1); }

        .widget-cuerpo { min-width: 0; }
        .widget-titulo {
            font-size: 0.8125rem; color: #f1f3f5;
            line-height: 1.3; margin-bottom: 0.25rem;
            word-break: break-word;
        }
        .widget-meta {
            display: flex; flex-wrap: wrap; gap: 0.25rem;
            font-size: 0.625rem;
        }
        .widget-tag-depto {
            padding: 0.0625rem 0.375rem; border-radius: 3px;
            background: rgba(107,143,199,0.15); color: #8aa8d6;
            text-transform: uppercase; letter-spacing: 0.04em;
        }
        .widget-tag-prio {
            padding: 0.0625rem 0.375rem; border-radius: 3px;
            background: rgba(255,69,58,0.15); color: #ff8a80;
        }
        .widget-tag-fecha {
            color: rgba(241,243,245,0.55);
            padding: 0.0625rem 0.375rem;
        }
        .widget-tag-fecha.vencida { color: #ff8a80; font-weight: 600; }

        .widget-desanclar {
            width: 22px; height: 22px;
            border: none; background: transparent;
            color: rgba(241,243,245,0.3);
            font-size: 1.1rem; line-height: 1;
            cursor: pointer; border-radius: 4px;
            transition: all 0.15s;
        }
        .widget-desanclar:hover {
            background: rgba(255,159,10,0.12);
            color: #ffb86b;
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
                            <a href="{{ route('tareas.index') }}" class="nav-link {{ request()->routeIs('tareas.*') ? 'active' : '' }}">Tareas</a>
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
                            @php
                                $countAncladas = Auth::user()->tareasAncladas()
                                    ->whereIn('estado', ['pendiente', 'en_curso', 'bloqueada'])
                                    ->count();
                            @endphp
                            <div class="widget-tareas-wrap">
                                <button type="button" class="header-btn widget-tareas-btn" id="widget-tareas-btn" title="Mis tareas ancladas">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                                    @if($countAncladas > 0)
                                        <span class="widget-badge" id="widget-badge">{{ $countAncladas > 99 ? '99+' : $countAncladas }}</span>
                                    @endif
                                </button>
                                <div class="widget-dropdown" id="widget-dropdown">
                                    <div class="widget-header">
                                        <strong>Tareas ancladas</strong>
                                        <a href="{{ route('tareas.index') }}" class="widget-ver-todas">Ver todas →</a>
                                    </div>
                                    <div class="widget-contenido" id="widget-contenido">
                                        <div class="widget-loading">Cargando…</div>
                                    </div>
                                </div>
                            </div>

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

    @auth
    <script>
    (function() {
        const btn = document.getElementById('widget-tareas-btn');
        const dropdown = document.getElementById('widget-dropdown');
        const contenido = document.getElementById('widget-contenido');
        const badge = document.getElementById('widget-badge');
        if (!btn || !dropdown) return;

        const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
        let cargado = false;

        function cargarWidget() {
            contenido.innerHTML = '<div class="widget-loading">Cargando…</div>';
            fetch("{{ route('tareas.widget') }}", { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.text())
                .then(html => { contenido.innerHTML = html; cargado = true; bindAcciones(); })
                .catch(() => { contenido.innerHTML = '<div class="widget-loading">Error al cargar</div>'; });
        }

        function actualizarBadge(count) {
            const wrap = document.querySelector('.widget-tareas-wrap');
            let b = document.getElementById('widget-badge');
            if (count > 0) {
                if (!b) {
                    b = document.createElement('span');
                    b.id = 'widget-badge';
                    b.className = 'widget-badge';
                    btn.appendChild(b);
                }
                b.textContent = count > 99 ? '99+' : count;
            } else if (b) {
                b.remove();
            }
        }

        function bindAcciones() {
            contenido.querySelectorAll('form').forEach(function(form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    }).then(r => r.json()).then(() => {
                        cargarWidget();
                        const items = contenido.querySelectorAll('.widget-item');
                        actualizarBadge(items.length);
                    });
                });
            });
        }

        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const abierto = dropdown.classList.toggle('abierto');
            if (abierto && !cargado) cargarWidget();
        });

        document.addEventListener('click', function(e) {
            if (!dropdown.contains(e.target) && !btn.contains(e.target)) {
                dropdown.classList.remove('abierto');
            }
        });
    })();
    </script>
    @endauth
</body>
</html>
