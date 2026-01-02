@extends('layouts.app')

@section('title', 'Asignacion de Territorios')

@section('content')
@if(!auth()->user()->canEditTerritorios())
{{-- Botón volver para usuarios normales --}}
<div class="volver-container mb-4">
    <a href="{{ route('territorios.index') }}" class="btn-volver">
        <span class="btn-volver-arrow">&#10094;</span>
        <span class="btn-volver-text">Volver a Territorios</span>
    </a>
</div>
@endif

<!-- Navegación minimalista -->
<nav class="page-nav">
    <div class="page-breadcrumbs">
        <a href="{{ route('dashboard') }}" class="breadcrumb-link">Dashboard</a>
        <span class="breadcrumb-sep">›</span>
        <a href="{{ route('territorios.index') }}" class="breadcrumb-link">Territorios</a>
        <span class="breadcrumb-sep">›</span>
        <span class="breadcrumb-current">Asignacion</span>
    </div>
    <div class="page-actions">
        @if(auth()->user()->canEditTerritorios())
        <a href="{{ route('registros.archivados') }}" class="btn btn-secondary">
            📚 Ver Archivados
        </a>
        @endif
    </div>
</nav>

<!-- Mensajes y WhatsApp Modal -->
@if(session('success'))
    <div class="alert alert-success" style="background: #d4edda; color: #155724; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; border: 1px solid #c3e6cb;">
        ✅ {{ session('success') }}
    </div>
@endif

@if(session('mostrar_whatsapp') && session('whatsapp_url'))
    <div class="whatsapp-modal" style="background: #e8f5e8; border: 2px solid #25d366; padding: 1.5rem; border-radius: 12px; margin-bottom: 1.5rem; text-align: center;">
        <div style="font-size: 1.2rem; margin-bottom: 1rem;">
            📱 <strong>¡Asignación Completada!</strong>
        </div>
        <div style="margin-bottom: 1.5rem; color: #666;">
            Envía el territorio a <strong>{{ session('whatsapp_publicador') }}</strong> por WhatsApp
        </div>

        <!-- Botones separados para móvil -->
        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; margin-bottom: 1rem;">
            <button id="copyMessageBtn"
                    onclick="copyMessageAndOpenWhatsApp()"
                    class="btn btn-success"
                    style="background: #25d366; color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 8px; font-weight: bold; cursor: pointer;">
                📱 Copiar Mensaje y Abrir WhatsApp
            </button>

            <button onclick="openTerritorioImage()"
                    class="btn btn-primary"
                    style="background: #1877f2; color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 8px; font-weight: bold; cursor: pointer;">
                📸 Ver y Copiar Imagen
            </button>
        </div>

        <!-- Mensaje que se copiará -->
        <div id="mensaje-territorio" style="display: none;">{{ urldecode(parse_url(session('whatsapp_url'), PHP_URL_QUERY)) }}</div>

        <div style="font-size: 0.875rem; color: #666; line-height: 1.5;">
            <strong>📱 Para móvil:</strong><br>
            1️⃣ Copia mensaje y abre WhatsApp<br>
            2️⃣ Pega el mensaje<br>
            3️⃣ Ve la imagen del territorio<br>
            4️⃣ Mantén presionado → "Copiar imagen"<br>
            5️⃣ Vuelve a WhatsApp y pega la imagen
        </div>
    </div>

    <script>
        function copyMessageAndOpenWhatsApp() {
            var mensaje = {!! json_encode(session('whatsapp_mensaje', 'Hola, te envio el territorio asignado.')) !!};
            var whatsappUrl = "{{ session('whatsapp_url') }}";

            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(mensaje).then(function() {
                    var btn = document.getElementById('copyMessageBtn');
                    var originalText = btn.innerHTML;
                    btn.innerHTML = '✅ ¡Mensaje Copiado!';

                    setTimeout(function() {
                        btn.innerHTML = originalText;
                    }, 2000);

                    setTimeout(function() {
                        var match = whatsappUrl.match(/wa\.me\/(\d+)/);
                        if (match) {
                            window.open('https://wa.me/' + match[1], '_blank');
                        }
                    }, 500);
                }).catch(function() {
                    alert('Mensaje listo para copiar:\n\n' + mensaje);
                    var match = whatsappUrl.match(/wa\.me\/(\d+)/);
                    if (match) {
                        window.open('https://wa.me/' + match[1], '_blank');
                    }
                });
            } else {
                alert('Mensaje listo para copiar:\n\n' + mensaje);
                var match = whatsappUrl.match(/wa\.me\/(\d+)/);
                if (match) {
                    window.open('https://wa.me/' + match[1], '_blank');
                }
            }
        }

        function openTerritorioImage() {
            var imageUrl = "{{ session('whatsapp_imagen_url', '') }}";
            if (imageUrl) {
                window.open(imageUrl, '_blank');
            }
        }
    </script>
@endif

<!-- Filtros por tipo de territorio -->
<div class="tipo-filtros mb-4">
    <a href="{{ route('registros.index', array_merge(request()->except('tipo'), ['tipo' => 'todos'])) }}"
       class="tipo-filtro {{ ($tipoFiltro ?? 'todos') === 'todos' ? 'active' : '' }}">
        <span class="tipo-icono">&#128203;</span>
        <span class="tipo-nombre">Todos</span>
        <span class="tipo-count">{{ $conteoTipos['todos'] ?? 0 }}</span>
    </a>
    <a href="{{ route('registros.index', array_merge(request()->except('tipo'), ['tipo' => 'normal'])) }}"
       class="tipo-filtro {{ ($tipoFiltro ?? '') === 'normal' ? 'active' : '' }}">
        <span class="tipo-icono">&#128506;</span>
        <span class="tipo-nombre">Normales</span>
        <span class="tipo-count">{{ $conteoTipos['normal'] ?? 0 }}</span>
    </a>
    <a href="{{ route('registros.index', array_merge(request()->except('tipo'), ['tipo' => 'campana'])) }}"
       class="tipo-filtro tipo-campana {{ ($tipoFiltro ?? '') === 'campana' ? 'active' : '' }}">
        <span class="tipo-icono">&#128227;</span>
        <span class="tipo-nombre">Campana</span>
        <span class="tipo-count">{{ $conteoTipos['campana'] ?? 0 }}</span>
    </a>
    <a href="{{ route('registros.index', array_merge(request()->except('tipo'), ['tipo' => 'negocios'])) }}"
       class="tipo-filtro tipo-negocios {{ ($tipoFiltro ?? '') === 'negocios' ? 'active' : '' }}">
        <span class="tipo-icono">&#127970;</span>
        <span class="tipo-nombre">Negocios</span>
        <span class="tipo-count">{{ $conteoTipos['negocios'] ?? 0 }}</span>
    </a>
</div>

<!-- Buscador -->
<div class="card mb-4">
    <form method="GET" action="{{ route('registros.index') }}" class="search-form">
        <input type="hidden" name="tipo" value="{{ $tipoFiltro ?? 'todos' }}">
        <div class="search-container">
            <input type="text"
                   name="search"
                   value="{{ request('search') }}"
                   placeholder="Buscar por territorio, publicador, telefono o notas..."
                   class="search-input">
            <button type="submit" class="search-btn">&#128269;</button>
            @if(request('search'))
                <a href="{{ route('registros.index', ['tipo' => $tipoFiltro ?? 'todos']) }}" class="search-clear">&#10006;</a>
            @endif
        </div>
        @if(request('search'))
            <div class="search-results-info">
                <small class="text-muted">
                    &#128203; Mostrando {{ $registrosActivos->count() }} resultados para: "<strong>{{ request('search') }}</strong>"
                </small>
            </div>
        @endif
    </form>
</div>

<!-- Estadisticas compactas -->
<div class="grid grid-4 mb-4">
    <div class="stat-card">
        <div class="stat-number stat-number-purple">{{ $estadisticas['activos_total'] }}</div>
        <div class="stat-label">Activos</div>
    </div>
    <div class="stat-card">
        <div class="stat-number text-red">{{ $estadisticas['atrasados'] }}</div>
        <div class="stat-label">Atrasados</div>
    </div>
    <div class="stat-card">
        <div class="stat-number stat-number-purple">{{ $estadisticas['territorios_disponibles'] }}</div>
        <div class="stat-label">Disponibles</div>
    </div>
    <div class="stat-card">
        <div class="stat-number stat-number-yellow">{{ round($estadisticas['promedio_dias']) }}</div>
        <div class="stat-label">Promedio</div>
    </div>
</div>

@php
    // Ordenar registros activos según criterios especificados
    $registrosOrdenados = $registrosActivos->sortBy(function($registro) {
        $estado = $registro->territorio->calcularEstado();
        $fechaSalida = $registro->fecha_salida->timestamp;

        // Prioridad: 1=Atrasados, 2=Activos
        if ($estado === 'atrasado') {
            return '1_' . (9999999999 - $fechaSalida); // Más recientes primero
        } elseif ($estado === 'activo') {
            return '2_' . (9999999999 - $fechaSalida); // Más recientes primero
        } else {
            return '3_' . $fechaSalida; // Más antiguos primero para otros estados
        }
    });
@endphp

<!-- Lista minimalista tipo tabla -->
<div class="card">
    <div class="card-title">
        Asignaciones Activas
        <span class="badge badge-blue">{{ $registrosActivos->count() }}</span>
    </div>

    @if($registrosActivos->count() > 0)
        <!-- Header de tabla -->
        <div class="table-header table-7-col">
            <div>FECHA SALIDA</div>
            <div>TERRITORIO</div>
            <div>PUBLICADOR</div>
            <div>DÍAS</div>
            <div>DEVOLUCIÓN</div>
            <div>ESTADO</div>
        </div>

        <!-- Filas de datos -->
        @foreach($registrosOrdenados as $index => $registro)
            <a href="{{ route('registros.show', $registro) }}"
               style="display: grid; grid-template-columns: 120px 120px 1fr 1fr 120px 120px 120px; gap: 1rem; padding: 0.75rem 1rem; border-bottom: 1px solid #e5e7eb; text-decoration: none; color: inherit; {{ $index % 2 == 0 ? 'background: #ffffff;' : 'background: #f9fafb;' }}"
               onmouseover="this.style.backgroundColor='#f1f5f9'"
               onmouseout="this.style.backgroundColor='{{ $index % 2 == 0 ? '#ffffff' : '#f9fafb' }}'">

                <!-- Fecha Salida -->
                <div style="font-size: 0.875rem; color: #6b7280;">
                    {{ $registro->fecha_salida->format('d/m/Y') }}
                </div>

                <!-- Territorio -->
                <div style="text-align: center;">
                    <span style="font-size: 1.5rem; font-weight: 700; color: {{ $registro->territorio->tipo === 'campana' ? '#f59e0b' : ($registro->territorio->tipo === 'negocios' ? '#3b82f6' : '#3b82f6') }};">{{ $registro->territorio->numero_completo }}</span>
                    @if($registro->territorio->tipo !== 'normal')
                    <span style="font-size: 0.65rem; display: block; color: #6b7280; margin-top: 2px;">{{ ucfirst($registro->territorio->tipo) }}</span>
                    @endif
                </div>

                <!-- Publicador -->
                <div>
                    <span style="font-weight: 600;" class="{{ $registro->publicador->es_precursor ? 'text-precursor' : '' }}">
                        {{ $registro->publicador->nombre_completo }}
                        @if($registro->publicador->es_precursor)
                        <span class="precursor-badge-sm">PR</span>
                        @endif
                    </span>
                </div>

                <!-- Días -->
                <div style="text-align: center;">
                    <span style="font-weight: 600; color: #374151;">{{ round($registro->fecha_salida->diffInDays(now())) }}</span>
                </div>

                <!-- Fecha Devolución -->
                <div style="text-align: center;">
                    @if($registro->fecha_entrada)
                        <span style="font-size: 0.875rem; color: #059669;">{{ $registro->fecha_entrada->format('d/m/Y') }}</span>
                    @else
                        <span style="font-size: 0.875rem; color: #ef4444; font-weight: 600;">PENDIENTE</span>
                    @endif
                </div>

                <!-- Estado -->
                <div style="text-align: center;">
                    @php $estado = $registro->territorio->calcularEstado(); @endphp
                    @if($estado === 'activo')
                        <span style="background: #dbeafe; color: #1e40af; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem; font-weight: 600;">ACTIVO</span>
                    @elseif($estado === 'atrasado')
                        <span style="background: #fef2f2; color: #dc2626; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem; font-weight: 600;">ATRASADO</span>
                    @endif
                </div>
            </a>
        @endforeach

    @else
        <!-- Estado vacío -->
        <div class="text-center" style="padding: 3rem;">
            <div style="font-size: 3rem; margin-bottom: 1rem; color: #d1d5db;">📋</div>
            <h3 style="font-size: 1.25rem; margin-bottom: 0.5rem; color: #6b7280;">No hay registros activos</h3>
            <p style="color: #9ca3af; margin-bottom: 1.5rem;">Todos los territorios están libres o archivados.</p>
            <a href="{{ route('registros.create') }}" class="btn btn-primary">
                Crear Primer Registro
            </a>
        </div>
    @endif
</div>

<style>
/* Filtros por tipo */
.tipo-filtros {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}
.tipo-filtro {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.25rem;
    background: var(--bg-card, #fff);
    border: 2px solid var(--border-color, #e5e7eb);
    border-radius: 10px;
    text-decoration: none;
    color: var(--text-primary, #374151);
    transition: all 0.2s;
    font-weight: 500;
}
.tipo-filtro:hover {
    border-color: var(--color-primary, #3b82f6);
    background: #f8fafc;
}
.tipo-filtro.active {
    border-color: var(--color-primary, #3b82f6);
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
    color: var(--color-primary, #3b82f6);
}
.tipo-filtro.tipo-campana.active {
    border-color: #f59e0b;
    background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
    color: #b45309;
}
.tipo-filtro.tipo-negocios.active {
    border-color: #3b82f6;
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
    color: #1d4ed8;
}
.tipo-icono {
    font-size: 1.25rem;
}
.tipo-nombre {
    font-size: 0.9rem;
}
.tipo-count {
    background: var(--color-gray-200, #e5e7eb);
    padding: 0.15rem 0.5rem;
    border-radius: 10px;
    font-size: 0.75rem;
    font-weight: 600;
}
.tipo-filtro.active .tipo-count {
    background: rgba(255,255,255,0.8);
}

/* Estilos del buscador */
.search-form {
    padding: 0;
}

.search-container {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.search-input {
    flex: 1;
    padding: 0.75rem 1rem;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 1rem;
    transition: border-color 0.3s;
}

.search-input:focus {
    outline: none;
    border-color: #3b82f6;
}

.search-btn {
    padding: 0.75rem 1rem;
    background: #3b82f6;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    cursor: pointer;
    transition: background-color 0.3s;
}

.search-btn:hover {
    background: #2563eb;
}

.search-clear {
    padding: 0.75rem 1rem;
    background: #ef4444;
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-size: 1rem;
    transition: background-color 0.3s;
}

.search-clear:hover {
    background: #dc2626;
}

.search-results-info {
    margin-top: 0.75rem;
    padding-top: 0.75rem;
    border-top: 1px solid #e5e7eb;
}

/* Mejoras para hover en filas */
.card a:hover {
    transform: none !important;
    box-shadow: none !important;
}

/* Boton volver para usuarios normales */
.volver-container {
    display: flex;
    justify-content: flex-start;
}

.btn-volver {
    display: inline-flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1.5rem;
    background: var(--bg-card, #fff);
    color: #374151;
    font-size: 1rem;
    font-weight: 600;
    text-decoration: none;
    border-radius: 10px;
    border: 2px solid #e5e7eb;
    transition: all 0.3s ease;
}

.btn-volver:hover {
    border-color: #3b82f6;
    color: #3b82f6;
    transform: translateX(-3px);
}

.btn-volver-arrow {
    font-size: 1.25rem;
    transition: transform 0.3s;
}

.btn-volver:hover .btn-volver-arrow {
    transform: translateX(-3px);
}

@media (max-width: 768px) {
    .btn-volver {
        padding: 0.6rem 1rem;
        font-size: 0.9rem;
    }
}

/* Responsive para pantallas pequenas */
@media (max-width: 768px) {
    .tipo-filtros {
        overflow-x: auto;
        flex-wrap: nowrap;
        padding-bottom: 0.5rem;
    }
    .tipo-filtro {
        flex-shrink: 0;
        padding: 0.5rem 0.75rem;
    }
    .tipo-nombre {
        font-size: 0.8rem;
    }
    .search-container {
        flex-direction: column;
    }

    .search-input {
        width: 100%;
    }

    .search-btn,
    .search-clear {
        width: 100%;
        text-align: center;
    }

    .card > a {
        display: block !important;
        padding: 1rem !important;
    }

    .card > div:first-of-type {
        display: none; /* Ocultar header en movil */
    }

    .card a > div {
        margin-bottom: 0.5rem;
    }

    .card a > div:last-child {
        margin-bottom: 0;
    }
}

/* Precursor styles */
.text-precursor {
    color: #16a34a !important;
}

.precursor-badge-sm {
    display: inline-block;
    padding: 0.1rem 0.35rem;
    background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
    color: white;
    font-size: 0.6rem;
    font-weight: 700;
    border-radius: 4px;
    margin-left: 0.4rem;
    vertical-align: middle;
}
</style>
@endsection
