@extends('layouts.app')

@section('title', 'Asignar Territorio - Gestión de Territorios')

@section('content')
<style>
    .territorio-section {
        background: #f8fafc;
        border-radius: 12px;
        padding: 1rem;
        margin-bottom: 1rem;
        border: 1px solid #e2e8f0;
    }
    .territorio-section.normal {
        border-left: 4px solid #10b981;
    }
    .territorio-section.campana {
        border-left: 4px solid #f59e0b;
    }
    .territorio-section.negocios {
        border-left: 4px solid #3b82f6;
    }
    .territorio-section-title {
        font-weight: 600;
        font-size: 0.95rem;
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .territorio-section.normal .territorio-section-title { color: #059669; }
    .territorio-section.campana .territorio-section-title { color: #d97706; }
    .territorio-section.negocios .territorio-section-title { color: #2563eb; }
    .territorio-badge {
        font-size: 0.7rem;
        padding: 2px 8px;
        border-radius: 12px;
        font-weight: 500;
    }
    .territorio-badge.normal { background: #d1fae5; color: #065f46; }
    .territorio-badge.campana { background: #fef3c7; color: #92400e; }
    .territorio-badge.negocios { background: #dbeafe; color: #1e40af; }
    .territorio-select {
        width: 100%;
        padding: 0.6rem;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 0.9rem;
    }
    .territorio-select:focus {
        outline: none;
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }
    .territorio-count {
        font-size: 0.8rem;
        color: #6b7280;
        margin-top: 0.25rem;
    }
    .territorio-count.available { color: #10b981; }
    .territorio-count.empty { color: #ef4444; }
    .info-panel {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 12px;
        padding: 1rem;
        margin-bottom: 1rem;
    }
    .info-panel-title {
        font-weight: 600;
        margin-bottom: 0.5rem;
    }
    .info-panel-list {
        font-size: 0.85rem;
        opacity: 0.9;
    }
    .zona-filter {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 0.75rem;
        flex-wrap: wrap;
    }
    .zona-btn {
        padding: 0.25rem 0.75rem;
        border-radius: 16px;
        font-size: 0.75rem;
        border: 1px solid #d1d5db;
        background: white;
        cursor: pointer;
        transition: all 0.2s;
    }
    .zona-btn:hover {
        background: #f3f4f6;
    }
    .zona-btn.active {
        background: #4f46e5;
        color: white;
        border-color: #4f46e5;
    }
    .zona-btn.normal.active { background: #10b981; border-color: #10b981; }
    .zona-btn.campana.active { background: #f59e0b; border-color: #f59e0b; }
    .zona-btn.negocios.active { background: #3b82f6; border-color: #3b82f6; }
    .zona-count {
        font-size: 0.65rem;
        opacity: 0.8;
        margin-left: 2px;
    }
</style>

<!-- Navegación minimalista -->
<nav class="page-nav">
    <div class="page-breadcrumbs">
        <a href="{{ route('dashboard') }}" class="breadcrumb-link">Dashboard</a>
        <span class="breadcrumb-separator">›</span>
        <a href="{{ route('registros.index') }}" class="breadcrumb-link">Registros</a>
        <span class="breadcrumb-separator">›</span>
        <span class="breadcrumb-current">Asignar</span>
    </div>
    <div class="page-actions">
        <a href="{{ route('registros.index') }}" class="btn btn-secondary">
            ← Volver
        </a>
    </div>
</nav>

<div class="grid grid-2">
    <!-- Formulario Principal -->
    <div class="card">
        <div class="card-title">Asignar Territorio</div>
        <div class="card-description">Selecciona un territorio libre y un publicador activo para crear una nueva asignación.</div>

        <form action="{{ route('registros.store') }}" method="POST" id="asignar-form">
            @csrf

            <!-- Selección de Territorios por Tipo -->
            <div class="mb-4">
                <label style="display: block; font-weight: 600; margin-bottom: 0.75rem;">
                    Territorio Disponible *
                </label>

                @php
                    $territoriosNormales = $territoriosDisponibles->where('tipo', 'normal');
                    $territoriosCampana = $territoriosDisponibles->where('tipo', 'campana');
                    $territoriosNegocios = $territoriosDisponibles->where('tipo', 'negocios');

                    // Extraer zonas únicas por tipo (basadas en el campo nombre)
                    $zonasNormales = $territoriosNormales->pluck('nombre')->filter()->unique()->sort()->values();
                    $zonasCampana = $territoriosCampana->pluck('nombre')->filter()->unique()->sort()->values();
                    $zonasNegocios = $territoriosNegocios->pluck('nombre')->filter()->unique()->sort()->values();
                @endphp

                <!-- Territorios Normales -->
                <div class="territorio-section normal">
                    <div class="territorio-section-title">
                        <span>&#127968;</span> Territorios Normales
                        <span class="territorio-badge normal">{{ $territoriosNormales->count() }}</span>
                    </div>
                    @if($territoriosNormales->count() > 0)
                        <!-- Filtro por zonas -->
                        @if($zonasNormales->count() > 0)
                        <div class="zona-filter" id="zonas-normal">
                            <button type="button" class="zona-btn normal active" data-zona="todas" onclick="filtrarPorZona('normal', 'todas')">
                                Todas <span class="zona-count">({{ $territoriosNormales->count() }})</span>
                            </button>
                            @foreach($zonasNormales as $zona)
                                @php $countZona = $territoriosNormales->where('nombre', $zona)->count(); @endphp
                                <button type="button" class="zona-btn normal" data-zona="{{ $zona }}" onclick="filtrarPorZona('normal', '{{ addslashes($zona) }}')">
                                    {{ $zona }} <span class="zona-count">({{ $countZona }})</span>
                                </button>
                            @endforeach
                        </div>
                        @endif
                        <select name="territorio_id" class="territorio-select territorio-select-group" data-tipo="normal" id="select-normal">
                            <option value="">Seleccionar territorio normal...</option>
                            @foreach($territoriosNormales as $territorio)
                                <option value="{{ $territorio->id }}" data-zona="{{ $territorio->nombre ?? '' }}" {{ old('territorio_id') == $territorio->id ? 'selected' : '' }}>
                                    #{{ $territorio->numero }} - {{ $territorio->nombre ?? 'Sin nombre' }}
                                </option>
                            @endforeach
                        </select>
                        <div class="territorio-count available" id="count-normal">{{ $territoriosNormales->count() }} disponibles</div>
                    @else
                        <div class="territorio-count empty">No hay territorios normales disponibles</div>
                    @endif
                </div>

                <!-- Territorios de Campaña -->
                <div class="territorio-section campana">
                    <div class="territorio-section-title">
                        <span>&#128227;</span> Territorios de Campaña
                        <span class="territorio-badge campana">{{ $territoriosCampana->count() }}</span>
                    </div>
                    @if($territoriosCampana->count() > 0)
                        <!-- Filtro por zonas -->
                        @if($zonasCampana->count() > 0)
                        <div class="zona-filter" id="zonas-campana">
                            <button type="button" class="zona-btn campana active" data-zona="todas" onclick="filtrarPorZona('campana', 'todas')">
                                Todas <span class="zona-count">({{ $territoriosCampana->count() }})</span>
                            </button>
                            @foreach($zonasCampana as $zona)
                                @php $countZona = $territoriosCampana->where('nombre', $zona)->count(); @endphp
                                <button type="button" class="zona-btn campana" data-zona="{{ $zona }}" onclick="filtrarPorZona('campana', '{{ addslashes($zona) }}')">
                                    {{ $zona }} <span class="zona-count">({{ $countZona }})</span>
                                </button>
                            @endforeach
                        </div>
                        @endif
                        <select name="territorio_id" class="territorio-select territorio-select-group" data-tipo="campana" id="select-campana">
                            <option value="">Seleccionar territorio campaña...</option>
                            @foreach($territoriosCampana as $territorio)
                                <option value="{{ $territorio->id }}" data-zona="{{ $territorio->nombre ?? '' }}" {{ old('territorio_id') == $territorio->id ? 'selected' : '' }}>
                                    C-{{ $territorio->numero }} - {{ $territorio->nombre ?? 'Sin nombre' }}
                                </option>
                            @endforeach
                        </select>
                        <div class="territorio-count available" id="count-campana">{{ $territoriosCampana->count() }} disponibles</div>
                    @else
                        <div class="territorio-count empty">No hay territorios de campaña disponibles</div>
                    @endif
                </div>

                <!-- Territorios de Negocios -->
                <div class="territorio-section negocios">
                    <div class="territorio-section-title">
                        <span>&#127970;</span> Territorios de Negocios
                        <span class="territorio-badge negocios">{{ $territoriosNegocios->count() }}</span>
                    </div>
                    @if($territoriosNegocios->count() > 0)
                        <!-- Filtro por zonas -->
                        @if($zonasNegocios->count() > 0)
                        <div class="zona-filter" id="zonas-negocios">
                            <button type="button" class="zona-btn negocios active" data-zona="todas" onclick="filtrarPorZona('negocios', 'todas')">
                                Todas <span class="zona-count">({{ $territoriosNegocios->count() }})</span>
                            </button>
                            @foreach($zonasNegocios as $zona)
                                @php $countZona = $territoriosNegocios->where('nombre', $zona)->count(); @endphp
                                <button type="button" class="zona-btn negocios" data-zona="{{ $zona }}" onclick="filtrarPorZona('negocios', '{{ addslashes($zona) }}')">
                                    {{ $zona }} <span class="zona-count">({{ $countZona }})</span>
                                </button>
                            @endforeach
                        </div>
                        @endif
                        <select name="territorio_id" class="territorio-select territorio-select-group" data-tipo="negocios" id="select-negocios">
                            <option value="">Seleccionar territorio negocios...</option>
                            @foreach($territoriosNegocios as $territorio)
                                <option value="{{ $territorio->id }}" data-zona="{{ $territorio->nombre ?? '' }}" {{ old('territorio_id') == $territorio->id ? 'selected' : '' }}>
                                    N-{{ $territorio->numero }} - {{ $territorio->nombre ?? 'Sin nombre' }}
                                </option>
                            @endforeach
                        </select>
                        <div class="territorio-count available" id="count-negocios">{{ $territoriosNegocios->count() }} disponibles</div>
                    @else
                        <div class="territorio-count empty">No hay territorios de negocios disponibles</div>
                    @endif
                </div>

                <!-- Campo oculto para el territorio seleccionado -->
                <input type="hidden" name="territorio_id_final" id="territorio_id_final" value="{{ old('territorio_id') }}">

                @error('territorio_id')
                    <div style="color: #ef4444; font-size: 0.875rem; margin-top: 0.5rem;">{{ $message }}</div>
                @enderror

                @if($territoriosDisponibles->count() == 0)
                    <div class="alert alert-error" style="margin-top: 1rem;">
                        <strong>&#9888; No hay territorios disponibles</strong><br>
                        Todos los territorios están asignados o en período de descanso.
                    </div>
                @endif
            </div>

            <!-- Selección de Publicador -->
            <div class="mb-4">
                <label for="publicador_id" style="display: block; font-weight: 600; margin-bottom: 0.5rem;">
                    Publicador Activo *
                </label>
                @if($publicadoresActivos->count() > 0)
                    <select id="publicador_id" name="publicador_id" required
                            style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 8px;">
                        <option value="">Selecciona un publicador...</option>
                        @foreach($publicadoresActivos as $publicador)
                            <option value="{{ $publicador->id }}" {{ old('publicador_id') == $publicador->id ? 'selected' : '' }}>
                                {{ $publicador->nombre }} {{ $publicador->apellidos }}
                            </option>
                        @endforeach
                    </select>
                    @error('publicador_id')
                        <div style="color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</div>
                    @enderror
                    <div style="color: #10b981; font-size: 0.875rem; margin-top: 0.25rem;">
                        &#9989; {{ $publicadoresActivos->count() }} publicadores disponibles
                    </div>
                @else
                    <div class="alert alert-error">
                        <strong>&#9888; No hay publicadores disponibles</strong><br>
                        Todos los publicadores están inactivos o ya tienen territorios asignados.
                    </div>
                @endif
            </div>

            <!-- Notas adicionales -->
            <div class="mb-4">
                <label for="notas" style="display: block; font-weight: 600; margin-bottom: 0.5rem;">
                    Notas adicionales
                </label>
                <textarea id="notas" name="notas" rows="3"
                          style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 8px;"
                          placeholder="Información adicional sobre esta asignación...">{{ old('notas') }}</textarea>
                @error('notas')
                    <div style="color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</div>
                @enderror
            </div>

            <!-- Botones de acción -->
            <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2rem;">
                <a href="{{ route('registros.index') }}" class="btn btn-secondary">
                    Cancelar
                </a>
                @if($territoriosDisponibles->count() > 0 && $publicadoresActivos->count() > 0)
                    <button type="submit" class="btn btn-primary">
                        Asignar y Enviar WhatsApp
                    </button>
                @else
                    <button type="button" disabled class="btn" style="background: #9ca3af; color: white; cursor: not-allowed;">
                        No se puede asignar
                    </button>
                @endif
            </div>
        </form>
    </div>

    <!-- Panel de información -->
    <div>
        <div class="info-panel">
            <div class="info-panel-title">&#128161; ¿Qué sucede al asignar?</div>
            <div class="info-panel-list">
                <div>• Se crea un registro de asignación</div>
                <div>• El territorio cambia a estado ACTIVO</div>
                <div>• Se envía WhatsApp automático al publicador</div>
                <div>• El mensaje incluye imagen del territorio</div>
            </div>
        </div>

        @if($territoriosNoDisponibles->count() > 0)
            <div class="card">
                <div class="card-title">
                    <span style="color: #f59e0b;">&#9203; En Período de Descanso</span>
                </div>
                <div class="card-description" style="font-size: 0.875rem;">
                    Territorios que deben esperar desde su devolución
                </div>
                <div style="max-height: 300px; overflow-y: auto;">
                    @foreach($territoriosNoDisponibles->take(10) as $territorio)
                        <div style="border-bottom: 1px solid #e5e7eb; padding: 0.75rem 0; background: #fef3c7; margin-bottom: 0.5rem; border-radius: 6px; padding-left: 1rem;">
                            <div><strong>{{ $territorio->numero_completo }}</strong>
                                @if($territorio->tipo !== 'normal')
                                <span class="territorio-badge {{ $territorio->tipo }}">{{ ucfirst($territorio->tipo) }}</span>
                                @endif
                            </div>
                            @if($territorio->nombre)
                                <div class="text-small text-muted">{{ $territorio->nombre }}</div>
                            @endif
                            <div style="color: #f59e0b; font-size: 0.8rem; margin-top: 0.25rem;">
                                &#128197; Disponible en {{ $territorio->diasRestantesParaEstarDisponible() }} días
                                ({{ $territorio->fechaDisponible()->format('d/m/Y') }})
                            </div>
                        </div>
                    @endforeach
                    @if($territoriosNoDisponibles->count() > 10)
                        <div class="text-small text-muted text-center" style="padding: 0.75rem;">
                            ... y {{ $territoriosNoDisponibles->count() - 10 }} más en descanso
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>

<script>
// Almacenar las opciones originales de cada select
const opcionesOriginales = {
    normal: [],
    campana: [],
    negocios: []
};

// Guardar opciones originales al cargar
document.addEventListener('DOMContentLoaded', function() {
    ['normal', 'campana', 'negocios'].forEach(tipo => {
        const select = document.getElementById('select-' + tipo);
        if (select) {
            const options = select.querySelectorAll('option');
            options.forEach(opt => {
                if (opt.value) {
                    opcionesOriginales[tipo].push({
                        value: opt.value,
                        text: opt.textContent,
                        zona: opt.dataset.zona || ''
                    });
                }
            });
        }
    });
});

// Filtrar territorios por zona
function filtrarPorZona(tipo, zona) {
    const select = document.getElementById('select-' + tipo);
    const countDiv = document.getElementById('count-' + tipo);
    const zonasContainer = document.getElementById('zonas-' + tipo);

    if (!select) return;

    // Actualizar botones activos
    if (zonasContainer) {
        zonasContainer.querySelectorAll('.zona-btn').forEach(btn => {
            btn.classList.remove('active');
            if (btn.dataset.zona === zona) {
                btn.classList.add('active');
            }
        });
    }

    // Guardar valor actual si existe
    const valorActual = select.value;

    // Limpiar select (mantener primera opción)
    const primeraOpcion = select.options[0];
    select.innerHTML = '';
    select.appendChild(primeraOpcion);

    // Filtrar y añadir opciones
    let count = 0;
    opcionesOriginales[tipo].forEach(opt => {
        if (zona === 'todas' || opt.zona === zona) {
            const option = document.createElement('option');
            option.value = opt.value;
            option.textContent = opt.text;
            option.dataset.zona = opt.zona;
            select.appendChild(option);
            count++;
        }
    });

    // Restaurar valor si sigue disponible
    if (valorActual) {
        const opcionExiste = Array.from(select.options).find(o => o.value === valorActual);
        if (opcionExiste) {
            select.value = valorActual;
        }
    }

    // Actualizar contador
    if (countDiv) {
        countDiv.textContent = count + ' disponibles';
    }
}

// Manejar selección exclusiva entre los 3 selectores de territorio
document.addEventListener('DOMContentLoaded', function() {
    const selects = document.querySelectorAll('.territorio-select-group');
    const hiddenInput = document.getElementById('territorio_id_final');

    selects.forEach(select => {
        select.addEventListener('change', function() {
            if (this.value) {
                // Actualizar el campo oculto
                hiddenInput.value = this.value;
                // Limpiar los otros selectores
                selects.forEach(otherSelect => {
                    if (otherSelect !== this) {
                        otherSelect.value = '';
                    }
                });
            } else {
                // Si se deselecciona, verificar si hay otro seleccionado
                let encontrado = false;
                selects.forEach(s => {
                    if (s.value) {
                        hiddenInput.value = s.value;
                        encontrado = true;
                    }
                });
                if (!encontrado) {
                    hiddenInput.value = '';
                }
            }
        });
    });

    // Validación antes de enviar
    document.getElementById('asignar-form').addEventListener('submit', function(e) {
        // Buscar el territorio seleccionado en cualquier selector
        let territorioSeleccionado = null;
        selects.forEach(select => {
            if (select.value) {
                territorioSeleccionado = select.value;
            }
        });

        if (!territorioSeleccionado) {
            e.preventDefault();
            alert('Por favor, selecciona un territorio de cualquier tipo.');
            return false;
        }

        // Asegurar que el campo oculto tenga el valor
        hiddenInput.value = territorioSeleccionado;

        // Deshabilitar los selectores que no están siendo usados para evitar conflictos
        selects.forEach(select => {
            if (!select.value) {
                select.disabled = true;
            }
        });
    });
});
</script>
@endsection
