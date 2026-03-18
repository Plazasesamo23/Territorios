<div class="submenu submenu-teal">
    <div class="container">
        <div class="submenu-items">
            <a href="{{ route('reuniones.index') }}" class="submenu-link {{ request()->routeIs('reuniones.index') ? 'active' : '' }}">Programas</a>
            <a href="{{ route('reuniones.create') }}" class="submenu-link {{ request()->routeIs('reuniones.create') ? 'active' : '' }}">Crear programa</a>
            <a href="{{ route('reuniones.generos') }}" class="submenu-link {{ request()->routeIs('reuniones.generos') ? 'active' : '' }}">Asignar generos</a>
        </div>
    </div>
</div>
