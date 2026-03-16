<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Gestión de Territorios')</title>
    <meta name="description" content="Sistema de gestión de territorios para la organización">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modular.css') }}">
    <script src="{{ asset('js/app.js') }}" defer></script>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <a href="{{ route('dashboard') }}" class="logo">
                    Sistema de Territorios
                </a>

                <nav class="nav">
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        Inicio
                    </a>
                    @php
                        $currentModule = '';
                        if (request()->routeIs('territorios.*') || request()->routeIs('registros.*') || request()->routeIs('s13.*') || request()->routeIs('creador-territorios.*')) {
                            $currentModule = 'territorios';
                        } elseif (request()->routeIs('publicadores.*') || request()->is('configuracion')) {
                            $currentModule = 'admin';
                        }
                    @endphp
                    @if($currentModule === 'territorios')
                        <span class="nav-module-badge nav-module-green">Territorios</span>
                    @elseif($currentModule === 'admin')
                        <span class="nav-module-badge nav-module-orange">Administracion</span>
                    @endif
                </nav>

                <div class="header-actions">
                    <button id="theme-toggle" class="theme-toggle" title="Cambiar tema">
                        <span id="theme-icon">&#x1F319;</span>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Submenu automático por módulo -->
    @if(request()->routeIs('territorios.*') || request()->routeIs('registros.*') || request()->routeIs('s13.*') || request()->routeIs('creador-territorios.*'))
        @include('layouts.partials.submenu-territorios')
    @elseif(request()->routeIs('publicadores.*') || request()->is('configuracion'))
        @include('layouts.partials.submenu-admin')
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
            <p>&copy; {{ date('Y') }} Sistema de Gestión de Territorios</p>
        </div>
    </footer>

    <!-- Script para el toggle de modo oscuro -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
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
                    themeIcon.textContent = '\u2600\uFE0F';
                    themeToggle.title = 'Cambiar a modo claro';
                } else {
                    body.removeAttribute('data-theme');
                    themeIcon.textContent = '\uD83C\uDF19';
                    themeToggle.title = 'Cambiar a modo oscuro';
                }
            }
        });
    </script>
</body>
</html>
