<nav class="submenu submenu-blue">
    <div class="container">
        <div class="submenu-items">
            <a href="{{ route('ppoc.calendario') }}" class="submenu-link {{ request()->routeIs('ppoc.calendario') ? 'active' : '' }}">Calendario</a>
            <a href="{{ route('ppoc.exportar-pdf') }}" class="submenu-link {{ request()->routeIs('ppoc.exportar-pdf') ? 'active' : '' }}">Exportar PDF</a>
            @if(Auth::user()->isAdmin())
            <a href="{{ route('ppoc.turnos.index') }}" class="submenu-link {{ request()->routeIs('ppoc.turnos.*') ? 'active' : '' }}">Turnos</a>
            <a href="{{ route('ppoc.aprobados') }}" class="submenu-link {{ request()->routeIs('ppoc.aprobados') ? 'active' : '' }}">Aprobados</a>
            <a href="{{ route('ppoc.disponibilidad.por-turno') }}" class="submenu-link {{ request()->routeIs('ppoc.disponibilidad.*') ? 'active' : '' }}">Disponibilidad</a>
            @endif
        </div>
    </div>
</nav>
