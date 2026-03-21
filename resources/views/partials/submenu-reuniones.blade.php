<div class="submenu submenu-teal">
    <div class="container">
        <div class="submenu-items">
            <a href="{{ route('reuniones.index') }}" class="submenu-link {{ request()->routeIs('reuniones.index') || request()->routeIs('reuniones.edit') || request()->routeIs('reuniones.show') ? 'active' : '' }}">VyM</a>
            <a href="{{ route('reuniones.finsemana') }}" class="submenu-link {{ request()->routeIs('reuniones.finsemana*') ? 'active' : '' }}">Fin de semana</a>
            <a href="{{ route('reuniones.asignaciones') }}" class="submenu-link {{ request()->routeIs('reuniones.asignaciones') ? 'active' : '' }}">Asignaciones</a>
            <a href="{{ route('reuniones.autorizaciones') }}" class="submenu-link {{ request()->routeIs('reuniones.autorizaciones') ? 'active' : '' }}">Autorizaciones</a>
        </div>
    </div>
</div>
