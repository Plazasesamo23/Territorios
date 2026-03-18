@extends('layouts.app')

@section('title', 'Gestor de Congregacion')
@section('hide_nav', true)

@section('content')

<div class="inicio-page">
    <div class="inicio-header">
        <h1 class="inicio-titulo">Gestor de Congregacion</h1>
        <p class="inicio-subtitulo">{{ $congregacionActiva->nombre ?? 'Tu congregacion' }}</p>
    </div>

    <div class="inicio-grid">
        <!-- Servicio -->
        <a href="{{ route('servicio') }}" class="inicio-card inicio-card-green">
            <div class="inicio-card-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 00-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0z"/>
                </svg>
            </div>
            <div class="inicio-card-body">
                <h2 class="inicio-card-titulo">Servicio</h2>
                <p class="inicio-card-desc">Territorios, PPOC, S-13 y administracion</p>
            </div>
            <div class="inicio-card-arrow">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                </svg>
            </div>
        </a>

        <!-- Reuniones -->
        <a href="{{ route('reuniones.index') }}" class="inicio-card inicio-card-teal">
            <div class="inicio-card-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/>
                </svg>
            </div>
            <div class="inicio-card-body">
                <h2 class="inicio-card-titulo">Reuniones</h2>
                <p class="inicio-card-desc">Programas VyM y asignaciones</p>
            </div>
            <div class="inicio-card-arrow">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                </svg>
            </div>
        </a>

        <!-- Departamentos -->
        <div class="inicio-card inicio-card-orange inicio-card-disabled">
            <div class="inicio-card-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z"/>
                </svg>
            </div>
            <div class="inicio-card-body">
                <h2 class="inicio-card-titulo">Departamentos</h2>
                <p class="inicio-card-desc">Proximamente</p>
            </div>
            <span class="inicio-card-badge">Pronto</span>
        </div>
    </div>
</div>

@endsection
