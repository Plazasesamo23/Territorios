<nav class="submenu submenu-orange">
    <div class="container">
        <div class="submenu-items">
            <a href="{{ route('administracion') }}" class="submenu-link {{ request()->routeIs('administracion') ? 'active' : '' }}">Panel</a>
            <a href="{{ route('publicadores.index') }}" class="submenu-link {{ request()->routeIs('publicadores.*') ? 'active' : '' }}">Publicadores</a>
            <a href="{{ route('usuarios.index') }}" class="submenu-link {{ request()->routeIs('usuarios.*') ? 'active' : '' }}">Usuarios</a>
            <a href="{{ route('grupos-predicacion.index') }}" class="submenu-link {{ request()->routeIs('grupos-predicacion.*') ? 'active' : '' }}">Grupos</a>
            <a href="{{ route('s13.index') }}" class="submenu-link {{ request()->routeIs('s13.*') ? 'active' : '' }}">S-13</a>
            <a href="{{ route('configuracion') }}" class="submenu-link {{ request()->is('configuracion') ? 'active' : '' }}">Configuracion</a>
            @can('superadmin')
            <a href="{{ route('congregaciones.index') }}" class="submenu-link {{ request()->routeIs('congregaciones.*') ? 'active' : '' }}">Congregaciones</a>
            @endcan
        </div>
    </div>
</nav>
