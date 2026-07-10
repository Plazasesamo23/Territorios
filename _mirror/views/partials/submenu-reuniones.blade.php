<div class="submenu submenu-teal">
    <div class="container">
        <div class="submenu-items">
            <a href="{{ route('reuniones.index') }}" class="submenu-link {{ request()->routeIs('reuniones.index') || request()->routeIs('reuniones.edit') || request()->routeIs('reuniones.show') || request()->routeIs('reuniones.create') ? 'active' : '' }}">VyM</a>
            <span class="submenu-link" style="opacity:0.45;cursor:default;" title="En desarrollo">Fin de semana (próximamente)</span>
            <a href="{{ route('reuniones.asignaciones') }}" class="submenu-link {{ request()->routeIs('reuniones.asignaciones') || request()->routeIs('reuniones.historial') ? 'active' : '' }}">Asignaciones</a>
            <a href="{{ route('reuniones.autorizaciones') }}" class="submenu-link {{ request()->routeIs('reuniones.autorizaciones') ? 'active' : '' }}">Autorizaciones</a>
            <a href="{{ route('reuniones.generos') }}" class="submenu-link {{ request()->routeIs('reuniones.generos') ? 'active' : '' }}">Géneros</a>
        </div>
    </div>
</div>
