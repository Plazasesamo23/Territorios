<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Gestión de Territorios')</title>
    <meta name="description" content="Sistema de gestión de territorios para la organización">
    @if(app()->environment('production'))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <link rel="stylesheet" href="{{ asset('build/assets/app-D7thK3vj.css') }}">
        <script src="{{ asset('build/assets/app-DNxiirP_.js') }}" defer></script>
    @endif

    @stack('styles')
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <a href="{{ route('dashboard') }}" class="logo">
                    🗺️ Sistema de Territorios
                </a>

                <button class="mobile-menu-toggle" id="mobile-menu-toggle" aria-label="Menú">
                    ☰
                </button>

                <nav class="nav" id="main-nav">
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
                    <a href="{{ route('creador-territorios.mapa') }}" class="nav-link {{ request()->routeIs('creador-territorios.mapa') ? 'active' : '' }}">
                        🗺️ Crear Territorio
                    </a>
                </nav>
                
                <div class="header-actions">
                    <button id="theme-toggle" class="theme-toggle" title="Cambiar tema">
                        <span id="theme-icon">🌙</span>
                    </button>
                    <a href="{{ route('configuracion') }}" class="btn btn-outline">
                        ⚙️ Configuración
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main">
        <div class="container">
            @yield('content')
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; {{ date('Y') }} Sistema de Gestión de Territorios</p>
        </div>
    </footer>

    <!-- Script para el toggle de modo oscuro y menú móvil -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle de tema oscuro
            const themeToggle = document.getElementById('theme-toggle');
            const themeIcon = document.getElementById('theme-icon');
            const body = document.body;

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

            // Toggle de menú móvil
            const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
            const mainNav = document.getElementById('main-nav');

            if (mobileMenuToggle && mainNav) {
                mobileMenuToggle.addEventListener('click', function() {
                    mainNav.classList.toggle('active');

                    // Cambiar icono
                    if (mainNav.classList.contains('active')) {
                        mobileMenuToggle.textContent = '✕';
                    } else {
                        mobileMenuToggle.textContent = '☰';
                    }
                });

                // Cerrar menú al hacer clic en un enlace
                const navLinks = mainNav.querySelectorAll('.nav-link');
                navLinks.forEach(link => {
                    link.addEventListener('click', function() {
                        if (window.innerWidth <= 768) {
                            mainNav.classList.remove('active');
                            mobileMenuToggle.textContent = '☰';
                        }
                    });
                });

                // Cerrar menú si se redimensiona la ventana
                window.addEventListener('resize', function() {
                    if (window.innerWidth > 768) {
                        mainNav.classList.remove('active');
                        mobileMenuToggle.textContent = '☰';
                    }
                });
            }
        });
    </script>
</body>
</html>