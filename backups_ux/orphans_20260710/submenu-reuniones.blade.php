<div class="submenu submenu-teal">
    <div class="container">
        <div class="submenu-items">
            <a href="{{ route('reuniones.index') }}" class="submenu-link {{ request()->routeIs('reuniones.index') || request()->routeIs('reuniones.edit') || request()->routeIs('reuniones.show') || request()->routeIs('reuniones.create') ? 'active' : '' }}">VyM</a>
            <a href="{{ route('reuniones.finsemana') }}" class="submenu-link {{ request()->routeIs('reuniones.finsemana*') ? 'active' : '' }}" style="opacity: 0.45; pointer-events: none;">Fin de semana</a>
            <a href="{{ route('reuniones.asignaciones') }}" class="submenu-link {{ request()->routeIs('reuniones.asignaciones') || request()->routeIs('reuniones.historial') ? 'active' : '' }}">Asignaciones</a>
            <a href="{{ route('reuniones.autorizaciones') }}" class="submenu-link {{ request()->routeIs('reuniones.autorizaciones') ? 'active' : '' }}">Autorizaciones</a>
            <a href="{{ route('reuniones.generos') }}" class="submenu-link {{ request()->routeIs('reuniones.generos') ? 'active' : '' }}">Generos</a>
        </div>
    </div>
</div>
