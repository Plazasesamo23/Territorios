@extends('layouts.app')

@section('title', 'Registros de Territorios')

@section('content')
<div class="page-md">
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

    <!-- Toggle de vistas -->
    <div class="vista-toggle">
        <button class="vista-btn active" data-vista="resumen" onclick="cambiarVista('resumen')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                <path d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/><path d="M9 12l2 2 4-4"/>
            </svg>
            Resumen
        </button>
        <button class="vista-btn" data-vista="lista" onclick="cambiarVista('lista')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                <path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/>
            </svg>
            Lista Activa
        </button>
    </div>

    <!-- ============================================
         VISTA RESUMEN — Gráficos + Publicadores
         ============================================ -->
    <div id="vista-resumen">

        <!-- Stats rápidas -->
        <div class="stats-row mb-2">
            <div class="stat-item">
                <span class="stat-number primary">{{ $estadisticas['activos_total'] }}</span>
                <span class="stat-label">Activos</span>
            </div>
            <div class="stat-item">
                <span class="stat-number" style="color: var(--warning, #f59e0b)">{{ $estadisticas['atrasados'] }}</span>
                <span class="stat-label">Atrasados</span>
            </div>
            <div class="stat-item">
                <span class="stat-number">{{ $estadisticas['territorios_disponibles'] }}</span>
                <span class="stat-label">Disponibles</span>
            </div>
            <div class="stat-item">
                <span class="stat-number">{{ round($estadisticas['promedio_dias']) }}</span>
                <span class="stat-label">Prom. dias</span>
            </div>
        </div>

        <!-- Gráficos pastel por nombramiento -->
        <div class="section-title">Distribucion por Nombramiento</div>

        <div class="chart-grid">
            @php
                $colores = [
                    'ancianos' => ['fill' => '#6366f1', 'bg' => 'rgba(99,102,241,0.12)', 'label' => 'Ancianos'],
                    'siervos' => ['fill' => '#14b8a6', 'bg' => 'rgba(20,184,166,0.12)', 'label' => 'Siervos Min.'],
                    'publicadores' => ['fill' => '#f59e0b', 'bg' => 'rgba(245,158,11,0.12)', 'label' => 'Publicadores'],
                ];
                $totalGeneral = collect($datosPastel)->sum('total_asignaciones') ?: 1;
            @endphp

            @foreach($datosPastel as $tipo => $datos)
            @php
                $color = $colores[$tipo];
                $porcentaje = round(($datos['total_asignaciones'] / $totalGeneral) * 100);
                $dashArray = ($porcentaje / 100) * 251.2;
                $dashOffset = 251.2 - $dashArray;
            @endphp
            <div class="chart-card" style="border-color: {{ $color['fill'] }}20">
                <div class="chart-donut">
                    <svg viewBox="0 0 100 100" width="90" height="90">
                        <circle cx="50" cy="50" r="40" fill="none" stroke="rgba(255,255,255,0.06)" stroke-width="8"/>
                        <circle cx="50" cy="50" r="40" fill="none" stroke="{{ $color['fill'] }}" stroke-width="8"
                            stroke-dasharray="{{ $dashArray }} {{ 251.2 - $dashArray }}"
                            stroke-dashoffset="62.8" stroke-linecap="round"
                            style="transition: stroke-dasharray 0.8s ease"/>
                        <text x="50" y="48" text-anchor="middle" fill="{{ $color['fill'] }}" font-size="18" font-weight="700">{{ $porcentaje }}%</text>
                        <text x="50" y="62" text-anchor="middle" fill="var(--text-muted)" font-size="9">{{ $datos['total_asignaciones'] }} asig.</text>
                    </svg>
                </div>
                <div class="chart-info">
                    <span class="chart-label" style="color: {{ $color['fill'] }}">{{ $color['label'] }}</span>
                    <div class="chart-stats">
                        <span><strong>{{ $datos['personas'] }}</strong> personas</span>
                        <span><strong>{{ $datos['activas'] }}</strong> activas</span>
                        <span><strong>{{ $datos['completadas'] }}</strong> completadas</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Tabla de publicadores con historial -->
        <div class="section-title mt-2">
            Publicadores
            <span class="badge badge-primary">{{ $publicadoresResumen->count() }}</span>
        </div>

        <!-- Buscador de publicadores -->
        <div class="form-group" style="margin-bottom: 0.5rem;">
            <input type="text" id="buscarPublicador" placeholder="Buscar publicador..."
                   class="form-input" oninput="filtrarPublicadores()">
        </div>

        <!-- Filtro por nombramiento -->
        <div class="filtro-nombramiento">
            <button class="filtro-btn active" data-filtro="todos" onclick="filtrarNombramiento('todos')">Todos</button>
            <button class="filtro-btn" data-filtro="Anciano" onclick="filtrarNombramiento('Anciano')" style="--filtro-color: #6366f1">Ancianos</button>
            <button class="filtro-btn" data-filtro="Siervo Ministerial" onclick="filtrarNombramiento('Siervo Ministerial')" style="--filtro-color: #14b8a6">Siervos</button>
            <button class="filtro-btn" data-filtro="Publicador" onclick="filtrarNombramiento('Publicador')" style="--filtro-color: #f59e0b">Publicadores</button>
        </div>

        <div class="publicadores-grid">
            @foreach($publicadoresResumen as $pub)
            @php
                $nombColor = match($pub['nombramiento']) {
                    'Anciano' => '#6366f1',
                    'Siervo Ministerial' => '#14b8a6',
                    default => '#f59e0b'
                };
            @endphp
            <div class="pub-card" data-nombramiento="{{ $pub['nombramiento'] }}">
                <div class="pub-header">
                    <div class="pub-avatar" style="background: {{ $nombColor }}20; color: {{ $nombColor }}">
                        {{ strtoupper(substr($pub['nombre'], 0, 1)) }}
                    </div>
                    <div class="pub-info">
                        <span class="pub-nombre">{{ $pub['nombre'] }}</span>
                        <span class="pub-nomb-badge" style="background: {{ $nombColor }}18; color: {{ $nombColor }}">{{ $pub['nombramiento'] }}</span>
                    </div>
                    <div class="pub-total">
                        <span class="pub-total-num">{{ $pub['total'] }}</span>
                        <span class="pub-total-label">total</span>
                    </div>
                </div>
                <div class="pub-detalles">
                    <div class="pub-stat">
                        <span class="pub-stat-val text-primary">{{ $pub['activas'] }}</span>
                        <span class="pub-stat-key">Activas</span>
                    </div>
                    <div class="pub-stat">
                        <span class="pub-stat-val">{{ $pub['completadas'] }}</span>
                        <span class="pub-stat-key">Completadas</span>
                    </div>
                    <div class="pub-stat">
                        <span class="pub-stat-val">{{ $pub['promedio_dias'] ?? '-' }}</span>
                        <span class="pub-stat-key">Prom. dias</span>
                    </div>
                    <div class="pub-stat">
                        <span class="pub-stat-val">{{ $pub['ultimo_territorio'] ?? '-' }}</span>
                        <span class="pub-stat-key">Ultima</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- ============================================
         VISTA LISTA — Lista activa original
         ============================================ -->
    <div id="vista-lista" style="display: none;">

        <!-- Filtros por tipo -->
        <div class="tabs-flat" id="tabs-tipo-lista">
            <button class="tab-item active" data-tipo="todos">Todos <span class="badge">{{ $conteoTipos['todos'] ?? 0 }}</span></button>
            <button class="tab-item" data-tipo="normal">Normales <span class="badge">{{ $conteoTipos['normal'] ?? 0 }}</span></button>
            <button class="tab-item" data-tipo="campana">Campana <span class="badge">{{ $conteoTipos['campana'] ?? 0 }}</span></button>
            <button class="tab-item" data-tipo="negocios">Negocios <span class="badge">{{ $conteoTipos['negocios'] ?? 0 }}</span></button>
        </div>

        <!-- Buscador -->
        <div class="form-group">
            <input type="text" id="buscarLista" placeholder="Buscar territorio, publicador..."
                   class="form-input" oninput="filtrarLista()">
        </div>

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

        <div class="section-title">
            Asignaciones Activas
            <span class="badge badge-primary">{{ $registrosActivos->count() }}</span>
        </div>

        @if($registrosActivos->count() > 0)
        <div class="table-responsive">
        <table class="table-flat">
            <thead>
                <tr>
                    <th class="hide-mobile">Fecha</th>
                    <th>Territorio</th>
                    <th>Publicador</th>
                    <th>Dias</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($registrosOrdenados as $registro)
                @php $estado = $registro->territorio->calcularEstado(); @endphp
                <tr class="clickable-row registro-row"
                    data-href="{{ route('registros.show', $registro) }}"
                    data-tipo="{{ $registro->territorio->tipo ?? 'normal' }}"
                    data-search="{{ strtolower($registro->territorio->numero_completo . ' ' . ($registro->publicador->nombre_completo ?? '') . ' ' . ($registro->territorio->zona ?? '')) }}">
                    <td class="text-muted hide-mobile">{{ $registro->fecha_salida->format('d/m/Y') }}</td>
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
        </div>
        @else
        <div class="empty-state">
            <div class="icon">&#x1F4CB;</div>
            <div class="title">No hay registros activos</div>
            <div class="desc">Todos los territorios estan libres o archivados.</div>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function cambiarVista(vista) {
    document.getElementById('vista-resumen').style.display = vista === 'resumen' ? 'block' : 'none';
    document.getElementById('vista-lista').style.display = vista === 'lista' ? 'block' : 'none';
    document.querySelectorAll('.vista-btn').forEach(function(btn) {
        btn.classList.toggle('active', btn.dataset.vista === vista);
    });
}

function filtrarNombramiento(tipo) {
    document.querySelectorAll('.filtro-btn').forEach(function(btn) {
        btn.classList.toggle('active', btn.dataset.filtro === tipo);
    });
    filtrarPublicadores();
}

function filtrarPublicadores() {
    var texto = (document.getElementById('buscarPublicador').value || '').toLowerCase();
    var filtroActivo = document.querySelector('.filtro-btn.active');
    var tipo = filtroActivo ? filtroActivo.dataset.filtro : 'todos';
    document.querySelectorAll('.pub-card').forEach(function(card) {
        var nombre = card.querySelector('.pub-nombre').textContent.toLowerCase();
        var coincideNombre = !texto || nombre.indexOf(texto) !== -1;
        var coincideTipo = tipo === 'todos' || card.dataset.nombramiento === tipo;
        card.style.display = (coincideNombre && coincideTipo) ? '' : 'none';
    });
}

// --- Filtrado de lista activa (client-side, sin recargar) ---
var filtroTipoLista = 'todos';

function filtrarLista() {
    var texto = (document.getElementById('buscarLista').value || '').toLowerCase();
    document.querySelectorAll('.registro-row').forEach(function(row) {
        var tipo = row.dataset.tipo || 'normal';
        var search = row.dataset.search || '';
        var pasaTipo = (filtroTipoLista === 'todos' || tipo === filtroTipoLista);
        var pasaTexto = (!texto || search.indexOf(texto) !== -1);
        row.style.display = (pasaTipo && pasaTexto) ? '' : 'none';
    });
}

document.addEventListener('DOMContentLoaded', function() {
    var tabsLista = document.querySelectorAll('#tabs-tipo-lista .tab-item');
    tabsLista.forEach(function(btn) {
        btn.addEventListener('click', function() {
            tabsLista.forEach(function(b) { b.classList.remove('active'); });
            this.classList.add('active');
            filtroTipoLista = this.dataset.tipo;
            filtrarLista();
        });
    });
});
</script>
@endpush
@endsection
