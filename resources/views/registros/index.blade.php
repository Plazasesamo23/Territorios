@extends('layouts.app')

@section('title', 'Registros - Gestión de Territorios')

@section('content')
<!-- Navegación minimalista -->
<nav class="page-nav">
    <div class="page-breadcrumbs">
        <a href="{{ route('dashboard') }}" class="breadcrumb-link">Dashboard</a>
        <span class="breadcrumb-sep">›</span>
        <span class="breadcrumb-current">Registros</span>
    </div>
    <div class="page-actions">
        <a href="{{ route('registros.create') }}" class="btn btn-primary">
            ➕ Nuevo Registro
        </a>
        <a href="{{ route('registros.archivados') }}" class="btn btn-secondary">
            📚 Ver Archivados
        </a>
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
            
            <button onclick="openTerritorioImage({{ session('whatsapp_territorio') }})" 
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
            // Mensaje limpio y formateado directamente desde PHP
            const mensaje = `Querido/a hermano/a aquí te mando el territorio asignado. Solo recordar que cuando lo termines de trabajar lo borres del teléfono y me avises. También recuerda que este territorio dura 3 meses, por lo tanto, puedes disfrutar y hacer uso de el por todo este tiempo, te animamos a poder trabajarlo a plenitud y tener conversaciones de provecho con las personas, así, podrás disfrutar por completo de tu ministerio 😁😁. Muchas gracias por su gran trabajo.

📸 La imagen del territorio te la envío por separado`;
            
            // Copiar mensaje al portapapeles
            if (navigator.clipboard) {
                navigator.clipboard.writeText(mensaje).then(function() {
                    // Cambiar texto del botón temporalmente
                    const btn = document.getElementById('copyMessageBtn');
                    const originalText = btn.innerHTML;
                    btn.innerHTML = '✅ ¡Mensaje Copiado!';
                    
                    setTimeout(() => {
                        btn.innerHTML = originalText;
                    }, 2000);
                    
                    // Abrir WhatsApp después de un pequeño delay
                    setTimeout(() => {
                        const whatsappUrl = "{{ session('whatsapp_url') }}";
                        const telefono = whatsappUrl.match(/wa\.me\/(\d+)/)[1];
                        window.open(`https://wa.me/${telefono}`, '_blank');
                    }, 500);
                }).catch(function() {
                    // Fallback si falla el clipboard
                    alert('Mensaje listo para copiar:\n\n' + mensaje);
                    const whatsappUrl = "{{ session('whatsapp_url') }}";
                    const telefono = whatsappUrl.match(/wa\.me\/(\d+)/)[1];
                    window.open(`https://wa.me/${telefono}`, '_blank');
                });
            } else {
                // Fallback para navegadores sin soporte de clipboard
                alert('Mensaje listo para copiar:\n\n' + mensaje);
                const whatsappUrl = "{{ session('whatsapp_url') }}";
                const telefono = whatsappUrl.match(/wa\.me\/(\d+)/)[1];
                window.open(`https://wa.me/${telefono}`, '_blank');
            }
        }
        
        function openTerritorioImage(numeroTerritorio) {
            const imageUrl = `{{ asset('imagenes') }}/${numeroTerritorio}.jpg`;
            window.open(imageUrl, '_blank');
        }
    </script>
@endif

<!-- Estadísticas compactas -->
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
        Registros Activos
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
                    <span style="font-size: 1.5rem; font-weight: 700; color: #3b82f6;">{{ $registro->territorio->numero }}</span>
                </div>
                
                <!-- Publicador -->
                <div>
                    <span style="font-weight: 500;">{{ $registro->publicador->nombre_completo }}</span>
                    <div style="font-size: 0.75rem; color: #9ca3af; margin-top: 0.125rem;">{{ $registro->publicador->telefono }}</div>
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
/* Mejoras para hover en filas */
.card a:hover {
    transform: none !important;
    box-shadow: none !important;
}

/* Responsive para pantallas pequeñas */
@media (max-width: 768px) {
    .card > a {
        display: block !important;
        padding: 1rem !important;
    }
    
    .card > div:first-of-type {
        display: none; /* Ocultar header en móvil */
    }
    
    .card a > div {
        margin-bottom: 0.5rem;
    }
    
    .card a > div:last-child {
        margin-bottom: 0;
    }
}
</style>
@endsection 