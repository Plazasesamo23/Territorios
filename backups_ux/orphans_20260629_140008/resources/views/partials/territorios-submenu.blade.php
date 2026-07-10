@php
    $esTerritorios = request()->routeIs('territorios.*') && !request()->routeIs('territorios.create');
    $esAsignacion = request()->routeIs('registros.*');
@endphp

@if(auth()->user()->canEditTerritorios())
{{-- Solo mostrar submenú para administradores --}}
<div class="territorios-submenu">
    <a href="{{ route('territorios.index') }}"
       class="submenu-tab {{ $esTerritorios ? 'active' : '' }}">
        <span class="submenu-icon">&#128506;</span>
        <span class="submenu-text">Territorios</span>
    </a>
    <a href="{{ route('registros.index') }}"
       class="submenu-tab {{ $esAsignacion ? 'active' : '' }}">
        <span class="submenu-icon">&#128203;</span>
        <span class="submenu-text">Asignacion</span>
    </a>
</div>

<style>
.territorios-submenu {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
    padding: 0.5rem;
    background: var(--bg-card, #fff);
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.submenu-tab {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background: transparent;
    border: 2px solid transparent;
    border-radius: 8px;
    color: var(--text-muted, #6b7280);
    text-decoration: none;
    font-weight: 500;
    font-size: 0.95rem;
    transition: all 0.2s ease;
    flex: 1;
    justify-content: center;
}

.submenu-tab:hover {
    background: var(--color-gray-100, #f3f4f6);
    color: var(--text-primary, #374151);
}

.submenu-tab.active {
    background: linear-gradient(135deg, #4a6da7 0%, #3d5a8a 100%);
    color: white;
    border-color: transparent;
    box-shadow: 0 2px 8px rgba(79, 70, 229, 0.3);
}

.submenu-icon {
    font-size: 1.1rem;
}

.submenu-text {
    font-size: 0.9rem;
}

/* Dark theme */
[data-theme="dark"] .territorios-submenu {
    background: var(--bg-card-dark, #1f2937);
}

[data-theme="dark"] .submenu-tab:hover {
    background: #374151;
}

/* Mobile */
@media (max-width: 480px) {
    .territorios-submenu {
        padding: 0.35rem;
    }

    .submenu-tab {
        padding: 0.6rem 1rem;
    }

    .submenu-text {
        font-size: 0.85rem;
    }
}
</style>
@endif