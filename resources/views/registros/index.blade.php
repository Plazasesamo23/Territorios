@extends('layouts.app')

@section('title', 'Registros de Territorios')

@section('content')
<div class="page-flat">
    <h1 class="page-title">Registros de Territorios</h1>
    <p class="page-subtitle">Gestiona las asignaciones activas</p>

    @if(session('mostrar_whatsapp') && session('whatsapp_url'))
        <div class="alert-banner mb-2" style="flex-direction: column; gap: 0.5rem; text-align: left;">
            <strong>Asignacion Completada</strong>
            <span>Envia el territorio a {{ session('whatsapp_publicador') }} por WhatsApp</span>
            <div class="flex gap-1 mt-1">
                <button id="copyMessageBtn" onclick="copyMessageAndOpenWhatsApp()" class="btn btn-secondary">
                    Copiar Mensaje y Abrir WhatsApp
                </button>
                <button onclick="openTerritorioImage()" class="btn btn-secondary">
                    Ver Imagen
                </button>
            </div>
        </div>
        <script>
            function copyMessageAndOpenWhatsApp() {
                var mensaje = {!! json_encode(session('whatsapp_mensaje', 'Hola, te envio el territorio asignado.')) !!};
                var whatsappUrl = "{{ session('whatsapp_url') }}";
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(mensaje).then(function() {
                        document.getElementById('copyMessageBtn').innerHTML = 'Copiado!';
                        setTimeout(function() {
                            var match = whatsappUrl.match(/wa\.me\/(\d+)/);
                            if (match) window.open('https://wa.me/' + match[1], '_blank');
                        }, 500);
                    });
                }
            }
            function openTerritorioImage() {
                var imageUrl = "{{ session('whatsapp_imagen_url', '') }}";
                if (imageUrl) window.open(imageUrl, '_blank');
            }
        </script>
    @endif

    <!-- Acciones -->
    @if(auth()->user()->canEditTerritorios())
    <div class="shortcuts mb-2">
        <a href="{{ route('registros.archivados') }}" class="shortcut">
            <span class="icon">&#x1F4DA;</span>
            <span>Archivados</span>
        </a>
    </div>
    @endif

    <!-- Filtros -->
    <div class="tabs-flat">
        <a href="{{ route('registros.index', ['tipo' => 'todos']) }}" class="tab-item {{ ($tipoFiltro ?? 'todos') === 'todos' ? 'active' : '' }}">
            Todos <span class="badge">{{ $conteoTipos['todos'] ?? 0 }}</span>
        </a>
        <a href="{{ route('registros.index', ['tipo' => 'normal']) }}" class="tab-item {{ ($tipoFiltro ?? '') === 'normal' ? 'active' : '' }}">
            Normales <span class="badge">{{ $conteoTipos['normal'] ?? 0 }}</span>
        </a>
        <a href="{{ route('registros.index', ['tipo' => 'campana']) }}" class="tab-item {{ ($tipoFiltro ?? '') === 'campana' ? 'active' : '' }}">
            Campana <span class="badge">{{ $conteoTipos['campana'] ?? 0 }}</span>
        </a>
        <a href="{{ route('registros.index', ['tipo' => 'negocios']) }}" class="tab-item {{ ($tipoFiltro ?? '') === 'negocios' ? 'active' : '' }}">
            Negocios <span class="badge">{{ $conteoTipos['negocios'] ?? 0 }}</span>
        </a>
    </div>

    <!-- Buscador -->
    <form method="GET" action="{{ route('registros.index') }}" class="form-group">
        <input type="hidden" name="tipo" value="{{ $tipoFiltro ?? 'todos' }}">
        <div class="flex gap-1">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Buscar territorio, publicador..."
                   class="form-input">
            <button type="submit" class="btn btn-primary">Buscar</button>
            @if(request('search'))
                <a href="{{ route('registros.index', ['tipo' => $tipoFiltro ?? 'todos']) }}" class="btn btn-secondary">Limpiar</a>
            @endif
        </div>
    </form>

    <!-- Stats -->
    <div class="stats-row mb-2">
        <div class="stat-item">
            <span class="stat-number primary">{{ $estadisticas['activos_total'] }}</span>
            <span class="stat-label">Activos</span>
        </div>
        <div class="stat-item">
            <span class="stat-number">{{ $estadisticas['atrasados'] }}</span>
            <span class="stat-label">Atrasados</span>
        </div>
        <div class="stat-item">
            <span class="stat-number">{{ $estadisticas['territorios_disponibles'] }}</span>
            <span class="stat-label">Disponibles</span>
        </div>
        <div class="stat-item">
            <span class="stat-number">{{ round($estadisticas['promedio_dias']) }}</span>
            <span class="stat-label">Promedio dias</span>
        </div>
    </div>

    @php
        $registrosOrdenados = $registrosActivos->sortBy(function($registro) {
            $estado = $registro->territorio->calcularEstado();
            $fechaSalida = $registro->fecha_salida->timestamp;
            if ($estado === 'atrasado') return '1_' . (9999999999 - $fechaSalida);
            elseif ($estado === 'activo') return '2_' . (9999999999 - $fechaSalida);
            else return '3_' . $fechaSalida;
        });
    @endphp

    <!-- Lista -->
    <div class="section-title">
        Asignaciones Activas
        <span class="badge badge-primary">{{ $registrosActivos->count() }}</span>
    </div>

    @if($registrosActivos->count() > 0)
    <table class="table-flat">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Territorio</th>
                <th>Publicador</th>
                <th>Dias</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($registrosOrdenados as $registro)
            @php $estado = $registro->territorio->calcularEstado(); @endphp
            <tr class="clickable-row" data-href="{{ route('registros.show', $registro) }}">
                <td class="text-muted">{{ $registro->fecha_salida->format('d/m/Y') }}</td>
                <td><strong>{{ $registro->territorio->numero_completo }}</strong></td>
                <td>{{ $registro->publicador->nombre_completo }}</td>
                <td class="text-center">{{ round($registro->fecha_salida->diffInDays(now())) }}</td>
                <td>
                    @if($estado === 'activo')
                        <span class="text-primary font-medium">Activo</span>
                    @elseif($estado === 'atrasado')
                        <span class="text-muted font-medium">Atrasado</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="empty-state">
        <div class="icon">&#x1F4CB;</div>
        <div class="title">No hay registros activos</div>
        <div class="desc">Todos los territorios estan libres o archivados.</div>
        <a href="{{ route('registros.create') }}" class="btn btn-primary mt-2">Crear Primer Registro</a>
    </div>
    @endif
</div>

<style>
.page-flat {
    max-width: 1000px;
    margin: 0 auto;
}

.tabs-flat a {
    text-decoration: none;
}
</style>
@endsection
