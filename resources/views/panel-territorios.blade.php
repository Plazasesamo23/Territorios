@extends('layouts.app')

@section('title', 'Territorios')

@section('content')

@php
    $prioridad = ['pasado' => 0, 'en_uso' => 1];
    $fuera = $territorios->filter(fn($t) => $t->registroActivo())
        ->sortBy(fn($t) => sprintf('%d_%09d', $prioridad[$t->nivelUso()] ?? 3, 999999999 - ($t->diasEnUso() ?? 0)))
        ->values();
    $zonas = $fuera->map(fn($t) => $t->zona)->filter()->unique()->sort()->values();

    $conteoNivel = ['pasado' => 0, 'en_uso' => 0];
    foreach ($fuera as $t) {
        $n = $t->nivelUso();
        if (isset($conteoNivel[$n])) $conteoNivel[$n]++;
    }
@endphp

<div class="simple-panel">
    <h1 class="simple-h1">Territorios</h1>

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

    <!-- Dos acciones grandes -->
    <div class="acciones-grandes">
        <a href="{{ route('registros.create') }}" class="accion-card accion-asignar">
            <span class="accion-icono">&#x2795;</span>
            <span class="accion-titulo">Asignar territorio</span>
            <span class="accion-sub">Dar un territorio a un publicador</span>
        </a>
        <a href="#lista-fuera" class="accion-card accion-devolver">
            <span class="accion-icono">&#x21A9;</span>
            <span class="accion-titulo">Devolver territorio</span>
            <span class="accion-sub">Baja a la lista y pulsa Devolver</span>
        </a>
    </div>

    <!-- Territorios fuera ahora -->
    <div class="lista-fuera" id="lista-fuera">
        <h2 class="seccion-fuera">Territorios que están fuera ahora ({{ $fuera->count() }})</h2>

        @if($fuera->count() > 0)
            <!-- Buscador -->
            <div class="fuera-controles">
                <input type="text" id="buscar-fuera" class="buscar-fuera"
                       placeholder="Buscar por territorio o por hermano...">

                <!-- Filtro rápido por estado -->
                <div class="fuera-chips">
                    <button type="button" class="chip activo" data-nivel="">Todos <span class="chip-n">{{ $fuera->count() }}</span></button>
                    <button type="button" class="chip chip-pasado" data-nivel="pasado">Atrasados <span class="chip-n">{{ $conteoNivel['pasado'] }}</span></button>
                    <button type="button" class="chip chip-uso" data-nivel="en_uso">En plazo <span class="chip-n">{{ $conteoNivel['en_uso'] }}</span></button>
                </div>

                <div class="fuera-filtros">
                    @if($zonas->count() > 1)
                    <select id="filtro-zona" class="filtro-select" aria-label="Filtrar por zona">
                        <option value="">Todas las zonas</option>
                        @foreach($zonas as $z)
                            <option value="{{ strtolower($z) }}">{{ $z }}</option>
                        @endforeach
                    </select>
                    @endif
                    <select id="orden-fuera" class="filtro-select" aria-label="Ordenar">
                        <option value="urgencia">Ordenar: más urgentes primero</option>
                        <option value="numero">Ordenar: por número</option>
                        <option value="dias">Ordenar: más días fuera</option>
                        <option value="nombre">Ordenar: por hermano</option>
                    </select>
                </div>
            </div>

            <div class="fuera-items" id="fuera-items">
                @foreach($fuera as $indice => $territorio)
                    @php
                        $reg = $territorio->registroActivo();
                        $dias = $reg ? round($reg->fecha_salida->diffInDays(now())) : 0;
                        $nombrePub = trim(($reg->publicador->nombre ?? '') . ' ' . ($reg->publicador->apellidos ?? ''));
                        $nivel = $territorio->nivelUso() ?? 'en_uso';
                        $limite = $territorio->diasLimiteUso();
                        $deMas = max(0, $dias - $limite);
                        $etiquetas = [
                            'pasado' => '⚠ Atrasado',
                            'en_uso' => '✓ En plazo',
                        ];
                    @endphp
                    <div class="fuera-item nivel-{{ $nivel }}"
                         data-numero="{{ $territorio->numero }}"
                         data-dias="{{ $dias }}"
                         data-zona="{{ strtolower($territorio->zona ?? '') }}"
                         data-nombre="{{ strtolower($nombrePub) }}"
                         data-nivel="{{ $nivel }}"
                         data-orden="{{ $indice }}"
                         data-buscar="{{ strtolower($territorio->numero_completo . ' ' . $nombrePub . ' ' . ($territorio->zona ?? '')) }}">
                        <div class="fuera-info">
                            <span class="fuera-terr">{{ $territorio->numero_completo }}@if($territorio->zona) <span class="fuera-zona">· {{ $territorio->zona }}</span>@endif</span>
                            <span class="fuera-pub">{{ $nombrePub !== '' ? $nombrePub : 'Sin nombre' }}</span>
                            <span class="nivel-badge nivel-badge-{{ $nivel }}">
                                {{ $etiquetas[$nivel] }}
                                @if($nivel === 'pasado')
                                    · {{ $deMas }} {{ $deMas === 1 ? 'día' : 'días' }} de más
                                @endif
                            </span>
                            <span class="fuera-dias">{{ $dias }} días fuera</span>
                        </div>
                        <button type="button" class="btn-devolver-grande"
                                data-action="{{ route('registros.entrada', $reg) }}"
                                data-terr="{{ $territorio->numero_completo }}"
                                data-pub="{{ $nombrePub !== '' ? $nombrePub : 'Sin nombre' }}"
                                data-salida="{{ $reg->fecha_salida->format('Y-m-d') }}"
                                onclick="abrirModalDevolver(this)">Devolver</button>
                    </div>
                @endforeach
            </div>

            <div class="fuera-sin-resultados" id="fuera-sin-resultados" style="display:none;">
                No se encontró ningún territorio con esa búsqueda.
            </div>
        @else
            <div class="fuera-vacio">
                <div class="fuera-vacio-icono">&#x2705;</div>
                <div>Ahora mismo no hay ningún territorio fuera.</div>
            </div>
        @endif
    </div>
</div>

<!-- Modal: devolver eligiendo la fecha -->
<div id="modal-devolver" class="modal-overlay" style="display:none;">
    <div class="modal-box">
        <h3 class="modal-titulo">Devolver territorio</h3>
        <p class="modal-texto" id="modal-texto"></p>
        <form id="modal-form" method="POST" action="">
            @csrf
            <label class="modal-label" for="modal-fecha">¿Qué día te lo devolvieron?</label>
            <input type="date" name="fecha_entrada" id="modal-fecha" class="modal-fecha"
                   value="{{ now()->format('Y-m-d') }}" max="{{ now()->format('Y-m-d') }}" required>
            <p class="modal-ayuda">Por defecto es hoy. Cámbialo solo si fue otro día.</p>
            <div class="modal-botones">
                <button type="button" class="modal-btn-cancelar" onclick="cerrarModalDevolver()">Cancelar</button>
                <button type="submit" class="modal-btn-confirmar">Devolver</button>
            </div>
        </form>
    </div>
</div>

<style>
.simple-panel {
    max-width: 760px;
    margin: 0 auto;
}

.simple-h1 {
    font-size: 1.9rem;
    color: #f1f3f5;
    margin: 0 0 1.25rem 0;
    text-align: center;
}

/* Dos botones de acción grandes */
.acciones-grandes {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    margin-bottom: 2rem;
}

.accion-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 0.4rem;
    padding: 1.75rem 1rem;
    border-radius: 16px;
    text-decoration: none;
    color: #fff;
    transition: transform 0.15s, box-shadow 0.15s;
    min-height: 150px;
    justify-content: center;
}

.accion-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 28px rgba(0,0,0,0.4);
}

.accion-asignar { background: linear-gradient(135deg, #2e9e5b 0%, #1f7a44 100%); }
.accion-devolver { background: linear-gradient(135deg, #4a6da7 0%, #2d4266 100%); }

.accion-icono { font-size: 2.4rem; line-height: 1; }
.accion-titulo { font-size: 1.35rem; font-weight: 700; }
.accion-sub { font-size: 0.9rem; opacity: 0.85; }

/* Lista de territorios fuera */
.seccion-fuera {
    font-size: 1.15rem;
    color: #f1f3f5;
    margin: 0 0 1rem 0;
    padding-bottom: 0.6rem;
    border-bottom: 1px solid #2d3339;
}

/* Buscador y filtros */
.fuera-controles {
    display: flex;
    flex-direction: column;
    gap: 0.7rem;
    margin-bottom: 1rem;
}

.buscar-fuera {
    width: 100%;
    padding: 0.85rem 1rem;
    font-size: 1.05rem;
    border-radius: 10px;
    border: 1px solid #2d3339;
    background: #151719;
    color: #f1f3f5;
}

.buscar-fuera::placeholder { color: #6b7682; }
.buscar-fuera:focus {
    outline: none;
    border-color: #6b8fc7;
    box-shadow: 0 0 0 3px rgba(107,143,199,0.12);
}

/* Chips de estado */
.fuera-chips {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.chip {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.5rem 0.85rem;
    border-radius: 999px;
    border: 1px solid #2d3339;
    background: #171717;
    color: #cbd5e1;
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s;
}

.chip .chip-n {
    font-size: 0.78rem;
    background: rgba(255,255,255,0.08);
    padding: 0.05rem 0.45rem;
    border-radius: 999px;
}

.chip.activo { border-color: #6b8fc7; color: #fff; background: #233047; }
.chip-pasado.activo { border-color: #f59e0b; background: rgba(245,158,11,0.18); color: #fbbf24; }
.chip-uso.activo { border-color: #3b82f6; background: rgba(59,130,246,0.15); color: #93c5fd; }

.fuera-filtros { display: flex; gap: 0.6rem; flex-wrap: wrap; }

.filtro-select {
    flex: 1;
    min-width: 150px;
    padding: 0.7rem 0.9rem;
    font-size: 0.95rem;
    border-radius: 10px;
    border: 1px solid #2d3339;
    background: #151719;
    color: #f1f3f5;
    cursor: pointer;
}

.filtro-select:focus { outline: none; border-color: #6b8fc7; }

.fuera-items { display: flex; flex-direction: column; gap: 0.6rem; }

.fuera-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    background: #171717;
    border: 1px solid #262626;
    border-left: 5px solid #3a3a3a;
    border-radius: 12px;
    padding: 0.9rem 1.1rem;
}

/* Distintivo por nivel (borde izquierdo) */
.fuera-item.nivel-pasado { border-left-color: #f59e0b; background: rgba(245,158,11,0.06); }
.fuera-item.nivel-en_uso { border-left-color: #3b82f6; }

.fuera-info { display: flex; flex-direction: column; gap: 0.2rem; min-width: 0; }

.fuera-terr { font-size: 1.15rem; font-weight: 700; color: #f1f3f5; }
.fuera-zona { font-size: 0.85rem; font-weight: 500; color: #9ca3af; }
.fuera-pub { font-size: 1rem; color: #cbd5e1; }
.fuera-dias { font-size: 0.8rem; color: #9ca3af; }

/* Etiqueta de estado */
.nivel-badge {
    display: inline-block;
    width: fit-content;
    font-size: 0.82rem;
    font-weight: 700;
    padding: 0.15rem 0.55rem;
    border-radius: 6px;
    margin: 0.1rem 0;
}
.nivel-badge-pasado { background: rgba(245,158,11,0.18); color: #fbbf24; }
.nivel-badge-en_uso { background: rgba(59,130,246,0.15); color: #93c5fd; }

.btn-devolver-grande {
    flex-shrink: 0;
    background: #4a6da7;
    color: #fff;
    border: none;
    border-radius: 10px;
    padding: 0.85rem 1.5rem;
    font-size: 1.05rem;
    font-weight: 700;
    cursor: pointer;
    transition: background 0.15s;
}

.btn-devolver-grande:hover { background: #3d5a8a; }

.fuera-vacio, .fuera-sin-resultados {
    text-align: center;
    padding: 2rem 1rem;
    color: #9ca3af;
    background: #171717;
    border: 1px solid #262626;
    border-radius: 12px;
}

.fuera-vacio-icono { font-size: 2.5rem; margin-bottom: 0.5rem; }

/* Modal devolver con fecha */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.65);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    padding: 1rem;
}
.modal-box {
    background: #1c1f22;
    border: 1px solid #2d3339;
    border-radius: 16px;
    padding: 1.5rem;
    width: 100%;
    max-width: 420px;
}
.modal-titulo { font-size: 1.3rem; color: #f1f3f5; margin: 0 0 0.5rem; }
.modal-texto { color: #cbd5e1; margin: 0 0 1.1rem; font-size: 1.05rem; }
.modal-label { display: block; color: #f1f3f5; font-weight: 600; margin-bottom: 0.4rem; }
.modal-fecha {
    width: 100%;
    padding: 0.85rem 1rem;
    font-size: 1.1rem;
    border-radius: 10px;
    border: 1px solid #2d3339;
    background: #151719;
    color: #f1f3f5;
}
.modal-fecha:focus { outline: none; border-color: #6b8fc7; }
.modal-ayuda { color: #9ca3af; font-size: 0.85rem; margin: 0.5rem 0 1.25rem; }
.modal-botones { display: flex; gap: 0.75rem; }
.modal-btn-cancelar, .modal-btn-confirmar {
    flex: 1;
    padding: 0.9rem;
    font-size: 1.05rem;
    font-weight: 700;
    border-radius: 10px;
    border: none;
    cursor: pointer;
}
.modal-btn-cancelar { background: #2d3339; color: #cbd5e1; }
.modal-btn-confirmar { background: #4a6da7; color: #fff; }
.modal-btn-confirmar:hover { background: #3d5a8a; }

/* Móvil */
@media (max-width: 600px) {
    .acciones-grandes { grid-template-columns: 1fr; }
    .fuera-item { flex-direction: column; align-items: stretch; gap: 0.75rem; }
    .btn-devolver-grande { width: 100%; padding: 0.95rem; font-size: 1.1rem; }
    .filtro-select { min-width: 0; }
}
</style>

<script>
(function() {
    var contenedor = document.getElementById('fuera-items');
    if (!contenedor) return;

    var inputBuscar = document.getElementById('buscar-fuera');
    var selectZona = document.getElementById('filtro-zona');
    var selectOrden = document.getElementById('orden-fuera');
    var sinResultados = document.getElementById('fuera-sin-resultados');
    var chips = Array.prototype.slice.call(document.querySelectorAll('.fuera-chips .chip'));
    var items = Array.prototype.slice.call(contenedor.querySelectorAll('.fuera-item'));
    var nivelActivo = '';

    function aplicar() {
        var texto = (inputBuscar.value || '').toLowerCase().trim();
        var zona = selectZona ? selectZona.value : '';
        var visibles = 0;

        items.forEach(function(item) {
            var coincideTexto = !texto || item.getAttribute('data-buscar').indexOf(texto) !== -1;
            var coincideZona = !zona || item.getAttribute('data-zona') === zona;
            var coincideNivel = !nivelActivo || item.getAttribute('data-nivel') === nivelActivo;
            var mostrar = coincideTexto && coincideZona && coincideNivel;
            item.style.display = mostrar ? '' : 'none';
            if (mostrar) visibles++;
        });

        sinResultados.style.display = visibles === 0 ? 'block' : 'none';
    }

    function ordenar() {
        var criterio = selectOrden.value;
        var ordenados = items.slice().sort(function(a, b) {
            if (criterio === 'dias') {
                return parseInt(b.getAttribute('data-dias')) - parseInt(a.getAttribute('data-dias'));
            } else if (criterio === 'nombre') {
                return a.getAttribute('data-nombre').localeCompare(b.getAttribute('data-nombre'));
            } else if (criterio === 'numero') {
                return parseFloat(a.getAttribute('data-numero')) - parseFloat(b.getAttribute('data-numero'));
            }
            // urgencia = orden original calculado en el servidor
            return parseInt(a.getAttribute('data-orden')) - parseInt(b.getAttribute('data-orden'));
        });
        ordenados.forEach(function(item) { contenedor.appendChild(item); });
    }

    inputBuscar.addEventListener('input', aplicar);
    if (selectZona) selectZona.addEventListener('change', aplicar);
    selectOrden.addEventListener('change', ordenar);

    chips.forEach(function(chip) {
        chip.addEventListener('click', function() {
            chips.forEach(function(c) { c.classList.remove('activo'); });
            chip.classList.add('activo');
            nivelActivo = chip.getAttribute('data-nivel');
            aplicar();
        });
    });
})();

// Modal de devolución con fecha
function abrirModalDevolver(btn) {
    var modal = document.getElementById('modal-devolver');
    var form = document.getElementById('modal-form');
    var fecha = document.getElementById('modal-fecha');
    if (!modal || !form || !fecha) return;
    form.setAttribute('action', btn.getAttribute('data-action'));
    document.getElementById('modal-texto').textContent =
        'Territorio ' + btn.getAttribute('data-terr') + ' · ' + btn.getAttribute('data-pub');
    var salida = btn.getAttribute('data-salida');
    if (salida) { fecha.setAttribute('min', salida); }
    modal.style.display = 'flex';
}
function cerrarModalDevolver() {
    var modal = document.getElementById('modal-devolver');
    if (modal) modal.style.display = 'none';
}
(function() {
    var overlay = document.getElementById('modal-devolver');
    if (overlay) {
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) cerrarModalDevolver();
        });
    }
})();
</script>

@endsection
