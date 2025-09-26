@extends('layouts.app')

@section('title', 'Dashboard - Gestión de Territorios')
@section('page-title', 'Panel de Control')

@section('header-actions')
@endsection

@section('content')

<!-- Sección de bienvenida -->
<div class="hero">
    <h1>¡Bienvenido al Sistema de Territorios!</h1>
    <p>Gestiona de manera eficiente los territorios y publicadores de tu congregación</p>
    <div class="mt-4">
        <span class="badge badge-gray">📅 {{ date('d/m/Y') }}</span>
    </div>
</div>

<!-- Estadísticas principales -->
<div class="grid grid-4 mb-6">
    <!-- Total Territorios -->
    <div class="stat-card">
        <div class="stat-number">{{ $totalTerritorios ?? 0 }}</div>
        <div class="stat-label">Total Territorios</div>
    </div>

    <!-- Territorios Libres -->
    <div class="stat-card">
        <div class="stat-number stat-number-green">{{ $territoriosLibres ?? 0 }}</div>
        <div class="stat-label">Territorios Libres</div>
        <div class="badge badge-green mt-2">Disponibles</div>
    </div>

    <!-- Territorios Activos -->
    <div class="stat-card">
        <div class="stat-number stat-number-yellow">{{ $territoriosActivos ?? 0 }}</div>
        <div class="stat-label">Territorios Activos</div>
        <div class="badge badge-yellow mt-2">En curso</div>
    </div>

    <!-- Total Publicadores -->
    <div class="stat-card">
        <div class="stat-number stat-number-purple">{{ $publicadoresActivos ?? 0 }}</div>
        <div class="stat-label">Total Publicadores</div>
        <div class="badge badge-gray">Registrados</div>
    </div>
</div>

<!-- Resumen rápido -->
<div class="card text-center mb-6">
    <h2>Gestión de Territorios</h2>
    <p>Bienvenido al panel de control de territorios de la congregación</p>
    <div class="mt-4">
        <a href="{{ route('territorios.index') }}" class="btn btn-primary">Ver Todos los Territorios</a>
    </div>
</div>

<!-- Grid principal con 2 columnas en desktop -->
<div class="dashboard-grid">
    <!-- Columna principal -->
    <div class="dashboard-column">
        <!-- Accesos rápidos -->
        <div class="card">
            <h2 class="card-title">🚀 Accesos Rápidos</h2>
            <div class="grid grid-3">
                <!-- Nuevo Publicador -->
                <a href="{{ route('publicadores.create') }}" class="card card-hover">
                    <div class="flex items-center gap-2">
                        <div class="icon icon-green">
                            👤
                        </div>
                        <div>
                            <h3 class="card-subtitle">Nuevo Publicador</h3>
                            <p class="text-small text-muted">Registrar nuevo publicador</p>
                        </div>
                    </div>
                </a>

                <!-- Nueva Asignación -->
                <a href="{{ route('registros.create') }}" class="card card-hover">
                    <div class="flex items-center gap-2">
                        <div class="icon icon-purple">
                            📋
                        </div>
                        <div>
                            <h3 class="card-subtitle">Nueva Asignación</h3>
                            <p class="text-small text-muted">Asignar territorio a publicador</p>
                        </div>
                    </div>
                </a>

                <!-- Reporte S13 -->
                <a href="{{ route('s13.index') }}" class="card card-hover">
                    <div class="flex items-center gap-2">
                        <div class="icon icon-yellow">
                            📊
                        </div>
                        <div>
                            <h3 class="card-subtitle">Reporte S13</h3>
                            <p class="text-small text-muted">Ver informe mensual</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Territorios que requieren atención -->
        @if(isset($territoriosAtrasados) && $territoriosAtrasados > 0)
        <div class="card card-warning">
            <div class="flex flex-between items-center mb-3">
                <h2 class="card-title text-red">🚨 Territorios que Requieren Atención</h2>
                <span class="badge badge-red">
                    {{ $territoriosAtrasados }} {{ $territoriosAtrasados == 1 ? 'territorio' : 'territorios' }}
                </span>
            </div>
            <div class="warning-box">
                <h3 class="text-red font-bold mb-2">⚠️ Atención Requerida</h3>
                <p class="text-red-dark">
                    Hay territorios que han estado asignados por más de 6 meses. 
                    <a href="{{ route('territorios.index', ['estado' => 'atrasado']) }}" class="text-red font-bold underline">
                        Ver territorios atrasados →
                    </a>
                </p>
            </div>
        </div>
        @endif
    </div>

    <!-- Columna lateral -->
    <div class="dashboard-column">
        <!-- Actividad reciente -->
        <div class="card">
            <h2 class="card-subtitle mb-4">📈 Actividad Reciente</h2>
            @if(isset($registrosActivos) && $registrosActivos && count($registrosActivos) > 0)
                <div class="dashboard-column">
                    @foreach($registrosActivos->take(5) as $registro)
                    <div class="activity-item">
                        <div class="activity-icon">
                            📋
                        </div>
                        <div class="flex-1">
                            <p class="font-bold text-dark mb-1">
                                {{ $registro->publicador->nombre ?? 'Publicador desconocido' }}
                            </p>
                            <p class="text-small text-muted">
                                Territorio #{{ $registro->territorio->numero ?? 'N/A' }}
                            </p>
                            <p class="text-xs text-gray">
                                {{ isset($registro->fecha_salida) ? \Carbon\Carbon::parse($registro->fecha_salida)->diffForHumans() : 'Fecha no disponible' }}
                            </p>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <div class="text-4xl mb-4">📭</div>
                    <p class="text-muted">No hay actividad reciente</p>
                </div>
            @endif
        </div>

        <!-- Estado del sistema -->
        <div class="card">
            <h2 class="card-subtitle mb-4">⚙️ Estado del Sistema</h2>
            <div class="dashboard-column">
                <div class="flex flex-between">
                    <span class="text-small text-muted">Territorios en archivo</span>
                    <span class="font-bold">{{ ($territoriosArchivo ?? 0) }}</span>
                </div>
                <div class="flex flex-between">
                    <span class="text-small text-muted">Última actualización</span>
                    <span class="font-bold">{{ now()->format('d/m/Y H:i') }}</span>
                </div>
                <div class="flex flex-between">
                    <span class="text-small text-muted">Base de datos</span>
                    <span class="badge badge-green">🟢 Conectada</span>
                </div>
            </div>
        </div>

        <!-- Enlaces útiles -->
        <div class="card useful-links">
            <h2 class="card-subtitle mb-4">🔗 Enlaces Útiles</h2>
            <div class="dashboard-column">
                <a href="{{ route('territorios.index') }}" class="useful-link">
                    ▶️ Ver todos los territorios
                </a>
                <a href="{{ route('publicadores.index') }}" class="useful-link">
                    ▶️ Gestionar publicadores
                </a>
                <a href="{{ route('configuracion') }}" class="useful-link">
                    ▶️ Configuración del sistema
                </a>
            </div>
        </div>
    </div>
</div>
@endsection 