@extends('layouts.app')

@section('title', 'Asignaciones de Territorios')

@section('content')
<div class="page-md">
    <h1 class="page-title">Asignaciones activas</h1>
    <p class="page-subtitle">Territorios que están fuera ahora mismo</p>

    @if(session('mostrar_whatsapp') && session('whatsapp_url'))
        <div class="alert-banner mb-2" style="flex-direction: column; gap: 0.5rem; text-align: left;">
            <strong>Territorio asignado</strong>
            <span>Envía el territorio a {{ session('whatsapp_publicador') }} por WhatsApp</span>
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
                // whatsappUrl ya incluye el mensaje (?text=...); abrimos esa URL directamente
                var abrir = function() { window.open(whatsappUrl, '_blank'); };
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(mensaje).then(function() {
                        document.getElementById('copyMessageBtn').innerHTML = 'Copiado!';
                        setTimeout(abrir, 400);
                    }).catch(abrir);
                } else {
                    abrir();
                }
            }
            function openTerritorioImage() {
                var imageUrl = "{{ session('whatsapp_imagen_url', '') }}";
                if (imageUrl) window.open(imageUrl, '_blank');
            }
        </script>
    @endif

    <!-- Filtros por tipo -->
    <div class="tabs-flat">
        <button class="tab-item active" data-tipo="todos" onclick="filtrarTipo('todos')">
            Todos <span class="badge">{{ $conteoTipos['todos'] ?? 0 }}</span>
        </button>
        <button class="tab-item" data-tipo="normal" onclick="filtrarTipo('normal')">
            Normales <span class="badge">{{ $conteoTipos['normal'] ?? 0 }}</span>
        </button>
        <button class="tab-item" data-tipo="campana" onclick="filtrarTipo('campana')">
            Campaña <span class="badge">{{ $conteoTipos['campana'] ?? 0 }}</span>
        </button>
        <button class="tab-item" data-tipo="negocios" onclick="filtrarTipo('negocios')">
            Negocios <span class="badge">{{ $conteoTipos['negocios'] ?? 0 }}</span>
        </button>
    </div>

    <!-- Buscador -->
    <div class="form-group">
        <div class="flex gap-1">
            <input type="text" id="search-lista"
                   placeholder="Buscar territorio o publicador..."
                   class="form-input"
                   oninput="filtrarLista()">
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
                <th>Días</th>
                <th>Estado</th>
                <th class="text-center">Acción</th>
            </tr>
        </thead>
        <tbody>
            @foreach($registrosOrdenados as $registro)
            @php $estado = $registro->territorio->calcularEstado(); @endphp
            <tr class="clickable-row registro-row"
                data-href="{{ route('registros.show', $registro) }}"
                data-tipo="{{ $registro->territorio->tipo }}"
                data-search="{{ strtolower($registro->territorio->numero_completo . ' ' . $registro->publicador->nombre_completo . ' ' . ($registro->territorio->zona ?? '')) }}">
                <td class="text-muted hide-mobile">{{ $registro->fecha_salida->format('d/m/Y') }}</td>
                <td><strong>{{ $registro->territorio->numero_completo }}</strong></td>
                <td>{{ $registro->publicador->nombre_completo }}</td>
                <td class="text-center">{{ round($registro->fecha_salida->diffInDays(now())) }}</td>
                <td>
                    @if($estado === 'atrasado')
                        <span class="badge badge-yellow">⚠ Atrasado</span>
                    @else
                        <span class="badge badge-blue">En plazo</span>
                    @endif
                </td>
                <td class="text-center">
                    <form action="{{ route('registros.entrada', $registro) }}" method="POST" style="margin:0"
                          onsubmit="event.stopPropagation(); return confirm('¿Devolver el territorio {{ $registro->territorio->numero_completo }} de {{ $registro->publicador->nombre_completo }}?');">
                        @csrf
                        <button type="submit" class="btn btn-secondary btn-sm">Devolver</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
    @else
    <div class="empty-state">
        <div class="icon">&#x1F4CB;</div>
        <div class="title">No hay territorios fuera</div>
        <div class="desc">Todos los territorios están libres o archivados.</div>
    </div>
    @endif
</div>

@push('scripts')
<script>
var tipoActivo = 'todos';

function filtrarTipo(tipo) {
    tipoActivo = tipo;
    document.querySelectorAll('.tabs-flat .tab-item').forEach(function(btn) {
        btn.classList.toggle('active', btn.dataset.tipo === tipo);
    });
    filtrarLista();
}

function filtrarLista() {
    var search = (document.getElementById('search-lista').value || '').toLowerCase();

    document.querySelectorAll('.registro-row').forEach(function(row) {
        var matchTipo = tipoActivo === 'todos' || row.dataset.tipo === tipoActivo;
        var matchSearch = !search || row.dataset.search.indexOf(search) !== -1;
        row.style.display = (matchTipo && matchSearch) ? '' : 'none';
    });
}
</script>
@endpush
@endsection
