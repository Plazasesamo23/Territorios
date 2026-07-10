<nav class="submenu submenu-green">
    <div class="container">
        <div class="submenu-items">
            <a href="{{ route('panel-territorios') }}" class="submenu-link {{ request()->routeIs('panel-territorios') ? 'active' : '' }}">Panel</a>
            <a href="{{ route('territorios.index') }}" class="submenu-link {{ request()->routeIs('territorios.index') ? 'active' : '' }}">Todos</a>
            <a href="{{ route('registros.index') }}" class="submenu-link {{ request()->routeIs('registros.index') ? 'active' : '' }}">Asignaciones</a>
            <a href="{{ route('registros.create') }}" class="submenu-link {{ request()->routeIs('registros.create') ? 'active' : '' }}">Asignar</a>
            <a href="{{ route('registros.archivados') }}" class="submenu-link {{ request()->routeIs('registros.archivados') ? 'active' : '' }}">Archivados</a>
            <a href="{{ route('s13.index') }}" class="submenu-link {{ request()->routeIs('s13.*') ? 'active' : '' }}">S-13</a>
            @if(Auth::user()->isAdmin())
            <a href="{{ route('creador-territorios.index') }}" class="submenu-link {{ request()->routeIs('creador-territorios.*') ? 'active' : '' }}">Editor</a>
            @endif
        </div>
    </div>
</nav>
