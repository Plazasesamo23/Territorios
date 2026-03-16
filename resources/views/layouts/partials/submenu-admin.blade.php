<nav class="submenu submenu-orange">
    <div class="container">
        <div class="submenu-items">
            <a href="{{ route('publicadores.index') }}" class="submenu-link {{ request()->routeIs('publicadores.index') ? 'active' : '' }}">
                Publicadores
            </a>
            <a href="{{ route('publicadores.create') }}" class="submenu-link {{ request()->routeIs('publicadores.create') ? 'active' : '' }}">
                Nuevo Publicador
            </a>
            <a href="{{ route('configuracion') }}" class="submenu-link {{ request()->routeIs('configuracion') ? 'active' : '' }}">
                Configuracion
            </a>
        </div>
    </div>
</nav>
