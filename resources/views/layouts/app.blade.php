<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Gestión de Territorios')</title>
    <meta name="description" content="Sistema de gestión de territorios para la organización">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script src="{{ asset('js/app.js') }}" defer></script>
    <style>
        .congregacion-badge {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.25rem 0.75rem;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .congregacion-selector {
            position: relative;
        }
        .congregacion-selector .dropdown-menu {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            min-width: 200px;
            z-index: 50;
            margin-top: 0.5rem;
        }
        .congregacion-selector .dropdown-menu.show {
            display: block;
        }
        .congregacion-selector .dropdown-item {
            display: block;
            padding: 0.5rem 1rem;
            color: #374151;
            text-decoration: none;
            font-size: 0.875rem;
        }
        .congregacion-selector .dropdown-item:hover {
            background: #f3f4f6;
        }
        .congregacion-selector .dropdown-item.active {
            background: #4f46e5;
            color: white;
        }
        .user-menu {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .user-info {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            font-size: 0.75rem;
            line-height: 1.2;
        }
        .user-name {
            font-weight: 600;
            color: var(--text-primary);
        }
        .user-role {
            color: var(--text-muted);
        }
        [data-theme="dark"] .congregacion-selector .dropdown-menu {
            background: #1f2937;
            border-color: #374151;
        }
        [data-theme="dark"] .congregacion-selector .dropdown-item {
            color: #e5e7eb;
        }
        [data-theme="dark"] .congregacion-selector .dropdown-item:hover {
            background: #374151;
        }

        /* Botón de configuración */
        .config-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
            border-radius: 50%;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(107, 114, 128, 0.4);
        }
        .config-btn:hover {
            transform: scale(1.1) rotate(90deg);
            box-shadow: 0 4px 12px rgba(107, 114, 128, 0.6);
        }
        .config-icon {
            font-size: 1.25rem;
        }

        /* Botón de perfil */
        .profile-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            border-radius: 50%;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.4);
        }
        .profile-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.6);
        }
        .profile-icon {
            font-size: 1.25rem;
            filter: grayscale(1) brightness(10);
        }

        /* Botón de logout */
        .logout-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            border: none;
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(239, 68, 68, 0.4);
            font-size: 1.1rem;
        }
        .logout-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.6);
        }

        /* Tema oscuro para botones */
        [data-theme="dark"] .config-btn {
            box-shadow: 0 2px 8px rgba(107, 114, 128, 0.3);
        }
        [data-theme="dark"] .profile-btn {
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
        }
        [data-theme="dark"] .logout-btn {
            box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3);
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <a href="{{ route('dashboard') }}" class="logo">
                    🗺️ Sistema de Territorios
                </a>

                <nav class="nav">
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        Dashboard
                    </a>
                    <a href="{{ route('territorios.index') }}" class="nav-link {{ request()->routeIs('territorios.*') ? 'active' : '' }}">
                        Territorios
                    </a>
                    <a href="{{ route('publicadores.index') }}" class="nav-link {{ request()->routeIs('publicadores.*') ? 'active' : '' }}">
                        Publicadores
                    </a>
                    <a href="{{ route('registros.index') }}" class="nav-link {{ request()->routeIs('registros.*') ? 'active' : '' }}">
                        Registros
                    </a>
                    <a href="{{ route('s13.index') }}" class="nav-link {{ request()->routeIs('s13.*') ? 'active' : '' }}">
                        S13
                    </a>
                    <a href="{{ route('creador-territorios.index') }}" class="nav-link {{ request()->routeIs('creador-territorios.*') ? 'active' : '' }}">
                        Creador
                    </a>
                    @can('superadmin')
                    <a href="{{ route('congregaciones.index') }}" class="nav-link {{ request()->routeIs('congregaciones.*') ? 'active' : '' }}">
                        Congregaciones
                    </a>
                    @endcan
                </nav>

                <div class="header-actions">
                    @auth
                        {{-- Selector de congregación --}}
                        @if(isset($congregacionActiva))
                            <div class="congregacion-selector">
                                <div class="congregacion-badge" id="congregacion-toggle" style="cursor: {{ $esSuperAdmin ?? false ? 'pointer' : 'default' }}">
                                    🏛️ {{ $congregacionActiva->nombre }}
                                    @if($esSuperAdmin ?? false)
                                        <span style="margin-left: 0.25rem;">▼</span>
                                    @endif
                                </div>

                                @if($esSuperAdmin ?? false)
                                    <div class="dropdown-menu" id="congregacion-dropdown">
                                        @foreach($todasCongregaciones ?? [] as $cong)
                                            <form action="{{ route('congregaciones.cambiar', $cong) }}" method="POST" style="margin: 0;">
                                                @csrf
                                                <button type="submit" class="dropdown-item {{ $congregacionActiva->id === $cong->id ? 'active' : '' }}" style="width: 100%; text-align: left; border: none; background: inherit; cursor: pointer;">
                                                    {{ $cong->nombre }}
                                                </button>
                                            </form>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endif

                        {{-- Botón de configuración --}}
                        <a href="{{ route('configuracion') }}" class="config-btn" title="Configuración">
                            <span class="config-icon">⚙️</span>
                        </a>

                        {{-- Botón de perfil con icono --}}
                        <a href="{{ route('perfil.index') }}" class="profile-btn" title="Mi perfil - {{ Auth::user()->name }}">
                            <span class="profile-icon">👤</span>
                        </a>

                        {{-- Botón de logout --}}
                        <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                            @csrf
                            <button type="submit" class="logout-btn" title="Cerrar sesión">
                                🚪
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-outline">
                            🔐 Iniciar Sesión
                        </a>
                    @endauth

                    <button id="theme-toggle" class="theme-toggle" title="Cambiar tema">
                        <span id="theme-icon">🌙</span>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="container" style="margin-top: 1rem;">
            <div class="alert alert-success" style="background: #d1fae5; border: 1px solid #10b981; color: #065f46; padding: 1rem; border-radius: 0.5rem;">
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="container" style="margin-top: 1rem;">
            <div class="alert alert-error" style="background: #fee2e2; border: 1px solid #ef4444; color: #991b1b; padding: 1rem; border-radius: 0.5rem;">
                {{ session('error') }}
            </div>
        </div>
    @endif

    <!-- Main Content -->
    <main class="main">
        <div class="container">
            @yield('content')
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; {{ date('Y') }} Sistema de Gestión de Territorios
                @if(isset($congregacionActiva))
                    - {{ $congregacionActiva->nombre }}
                @endif
            </p>
        </div>
    </footer>

    <!-- Script para el toggle de modo oscuro y selector de congregación -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const themeToggle = document.getElementById('theme-toggle');
            const themeIcon = document.getElementById('theme-icon');
            const body = document.body;

            // Selector de congregación (click toggle)
            const congregacionToggle = document.getElementById('congregacion-toggle');
            const congregacionDropdown = document.getElementById('congregacion-dropdown');

            if (congregacionToggle && congregacionDropdown) {
                congregacionToggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    congregacionDropdown.classList.toggle('show');
                });

                // Cerrar al hacer click fuera
                document.addEventListener('click', function(e) {
                    if (!congregacionDropdown.contains(e.target) && !congregacionToggle.contains(e.target)) {
                        congregacionDropdown.classList.remove('show');
                    }
                });
            }

            // Cargar tema guardado o usar modo claro por defecto
            const savedTheme = localStorage.getItem('theme') || 'light';
            setTheme(savedTheme);

            themeToggle.addEventListener('click', function() {
                const currentTheme = body.getAttribute('data-theme') || 'light';
                const newTheme = currentTheme === 'light' ? 'dark' : 'light';
                setTheme(newTheme);
                localStorage.setItem('theme', newTheme);
            });

            function setTheme(theme) {
                if (theme === 'dark') {
                    body.setAttribute('data-theme', 'dark');
                    themeIcon.textContent = '☀️';
                    themeToggle.title = 'Cambiar a modo claro';
                } else {
                    body.removeAttribute('data-theme');
                    themeIcon.textContent = '🌙';
                    themeToggle.title = 'Cambiar a modo oscuro';
                }
            }

            // Auto-hide flash messages
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(function(alert) {
                    alert.style.transition = 'opacity 0.5s';
                    alert.style.opacity = '0';
                    setTimeout(function() {
                        alert.remove();
                    }, 500);
                });
            }, 5000);
        });
    </script>
</body>
</html>
