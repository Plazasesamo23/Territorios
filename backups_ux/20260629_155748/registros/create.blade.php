@extends('layouts.app')

@section('title', 'Asignar Territorio')

@section('content')
<div class="page-flat">
    <h1 class="page-title">Asignar Territorio</h1>
    <p class="page-subtitle">Selecciona territorio y publicador</p>

    @if($territoriosDisponibles->count() == 0)
        <div class="empty-state">
            <div class="title">No hay territorios disponibles</div>
            <div class="desc">Todos estan asignados o en periodo de descanso.</div>
            <a href="{{ route('panel-territorios') }}" class="btn btn-secondary mt-1">Volver</a>
        </div>
    @else
        <form action="{{ route('registros.store') }}" method="POST" id="asignar-form">
            @csrf

            <div class="assign-grid">
                <!-- Columna izquierda: Territorio -->
                <div class="assign-col">
                    <div class="col-header">1. Territorio</div>

                    <!-- Filtro por tipo -->
                    @php
                        $conteoNormal = $territoriosDisponibles->filter(fn($t) => empty($t->tipo) || $t->tipo === 'normal')->count();
                        $conteoCampana = $territoriosDisponibles->where('tipo', 'campana')->count();
                        $conteoNegocios = $territoriosDisponibles->where('tipo', 'negocios')->count();
                    @endphp
                    <div class="tipo-tabs">
                        <button type="button" class="tipo-btn active" data-tipo="">Todos <span class="badge">{{ $territoriosDisponibles->count() }}</span></button>
                        <button type="button" class="tipo-btn" data-tipo="normal">Normal <span class="badge">{{ $conteoNormal }}</span></button>
                        @if($conteoCampana > 0)
                        <button type="button" class="tipo-btn" data-tipo="campana">Campana <span class="badge">{{ $conteoCampana }}</span></button>
                        @endif
                        @if($conteoNegocios > 0)
                        <button type="button" class="tipo-btn" data-tipo="negocios">Negocios <span class="badge">{{ $conteoNegocios }}</span></button>
                        @endif
                    </div>

                    <!-- Filtro por zona -->
                    @php
                        $zonasUnicas = $territoriosDisponibles->pluck('zona')->filter()->unique()->sort();
                    @endphp
                    @if($zonasUnicas->count() > 1)
                    <div class="zona-tabs">
                        <button type="button" class="zona-btn active" data-zona="">Todas zonas</button>
                        @foreach($zonasUnicas as $zona)
                            <button type="button" class="zona-btn" data-zona="{{ $zona }}">{{ $zona }}</button>
                        @endforeach
                    </div>
                    @endif

                    <select name="territorio_id" id="territorio-select" class="form-input" required>
                        <option value="">-- Selecciona --</option>
                        @foreach($territoriosDisponibles->sortBy('numero') as $territorio)
                            <option value="{{ $territorio->id }}"
                                    data-tipo="{{ $territorio->tipo }}"
                                    data-zona="{{ $territorio->zona }}"
                                    data-numero="{{ $territorio->numero_completo }}">
                                {{ $territorio->numero_completo }}@if($territorio->zona) - {{ $territorio->zona }}@endif
                            </option>
                        @endforeach
                    </select>

                    <div class="selected-info hidden" id="territorio-info">
                        <span id="territorio-info-name"></span>
                        <span class="text-muted" id="territorio-info-tipo"></span>
                    </div>
                </div>

                <!-- Columna derecha: Publicador -->
                <div class="assign-col">
                    <div class="col-header">2. Publicador</div>

                    <input type="text" id="buscar-publicador" class="form-input" placeholder="Buscar...">

                    <div class="publicadores-list" id="publicadores-list">
                        @foreach($publicadoresActivos->sortBy('nombre') as $publicador)
                            <div class="pub-item"
                                data-id="{{ $publicador->id }}"
                                data-nombre="{{ strtolower($publicador->nombre . ' ' . $publicador->apellidos) }}">
                                <span class="pub-name">{{ $publicador->nombre }} {{ $publicador->apellidos }}</span>
                                @if($publicador->es_precursor)<span class="pub-badge">PR</span>@endif
                            </div>
                        @endforeach
                    </div>

                    <input type="hidden" name="publicador_id" id="publicador-id-input" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-lg btn-full" id="btn-submit" disabled>
                Asignar y Enviar WhatsApp
            </button>
        </form>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const territorioSelect = document.getElementById('territorio-select');
    const territorioInfo = document.getElementById('territorio-info');
    const btnSubmit = document.getElementById('btn-submit');
    const publicadorInput = document.getElementById('publicador-id-input');
    const buscarInput = document.getElementById('buscar-publicador');
    const publicadoresList = document.getElementById('publicadores-list');

    if (!territorioSelect) return;

    const opcionesOriginales = Array.from(territorioSelect.options).slice(1).map(opt => ({
        value: opt.value, text: opt.textContent,
        tipo: opt.dataset.tipo, zona: opt.dataset.zona, numero: opt.dataset.numero
    }));

    let zonaSeleccionada = '';
    let tipoSeleccionado = '';

    function filtrarTerritorios() {
        territorioSelect.innerHTML = '<option value="">-- Selecciona --</option>';
        opcionesOriginales.forEach(opt => {
            const tipoOpt = opt.tipo || 'normal';
            const pasaTipo = !tipoSeleccionado || tipoOpt === tipoSeleccionado;
            const pasaZona = !zonaSeleccionada || opt.zona === zonaSeleccionada;
            if (pasaTipo && pasaZona) {
                const option = document.createElement('option');
                option.value = opt.value;
                option.textContent = opt.text;
                option.dataset.tipo = opt.tipo;
                option.dataset.zona = opt.zona;
                option.dataset.numero = opt.numero;
                territorioSelect.appendChild(option);
            }
        });
        territorioSelect.value = '';
        territorioInfo.classList.add('hidden');
        updateSubmitButton();
    }

    document.querySelectorAll('.tipo-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelectorAll('.tipo-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            tipoSeleccionado = this.dataset.tipo;
            filtrarTerritorios();
        });
    });

    document.querySelectorAll('.zona-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelectorAll('.zona-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            zonaSeleccionada = this.dataset.zona;
            filtrarTerritorios();
        });
    });

    territorioSelect.addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        if (this.value) {
            document.getElementById('territorio-info-name').textContent = selected.dataset.numero;
            const tipoLabels = { 'normal': 'Normal', 'campana': 'Campana', 'negocios': 'Negocios' };
            document.getElementById('territorio-info-tipo').textContent = tipoLabels[selected.dataset.tipo] || '';
            territorioInfo.classList.remove('hidden');
        } else {
            territorioInfo.classList.add('hidden');
        }
        updateSubmitButton();
    });

    if (buscarInput) {
        buscarInput.addEventListener('input', function() {
            const busqueda = this.value.toLowerCase().trim();
            publicadoresList.querySelectorAll('.pub-item').forEach(item => {
                const nombre = item.dataset.nombre;
                item.style.display = nombre.includes(busqueda) ? 'flex' : 'none';
            });
        });
    }

    if (publicadoresList) {
        publicadoresList.querySelectorAll('.pub-item').forEach(item => {
            item.addEventListener('click', function() {
                publicadoresList.querySelectorAll('.pub-item').forEach(i => i.classList.remove('selected'));
                this.classList.add('selected');
                publicadorInput.value = this.dataset.id;
                updateSubmitButton();
            });
        });
    }

    function updateSubmitButton() {
        btnSubmit.disabled = !(territorioSelect.value && publicadorInput.value);
    }
});
</script>

<style>
.page-flat {
    max-width: 900px;
    margin: 0 auto;
}

.assign-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.assign-col {
    background: var(--bg-white);
    border-radius: var(--radius);
    padding: 1rem;
}

.col-header {
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--text-muted);
    text-transform: uppercase;
    margin-bottom: 0.75rem;
}

.tipo-tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 0.375rem;
    margin-bottom: 0.625rem;
}

.tipo-btn {
    padding: 0.375rem 0.625rem;
    background: var(--bg-hover);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    font-size: 0.8rem;
    cursor: pointer;
    color: var(--text-secondary);
    display: flex;
    align-items: center;
    gap: 0.375rem;
}

.tipo-btn .badge {
    font-size: 0.65rem;
    padding: 0.1rem 0.35rem;
    background: rgba(255,255,255,0.08);
    border-radius: 10px;
    color: var(--text-muted);
}

.tipo-btn.active {
    background: var(--primary);
    color: white;
    border-color: var(--primary);
}

.tipo-btn.active .badge {
    background: rgba(255,255,255,0.25);
    color: white;
}

.zona-tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 0.75rem;
}

.zona-btn {
    padding: 0.375rem 0.75rem;
    background: var(--bg-hover);
    border: none;
    border-radius: var(--radius);
    font-size: 0.8rem;
    cursor: pointer;
    color: var(--text-secondary);
}

.zona-btn.active {
    background: var(--primary);
    color: white;
}

.selected-info {
    margin-top: 0.75rem;
    padding: 0.5rem;
    background: var(--bg-hover);
    border-radius: var(--radius);
    font-size: 0.9rem;
}

.selected-info span {
    display: block;
}

.publicadores-list {
    max-height: 250px;
    overflow-y: auto;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    margin-top: 0.5rem;
}

.pub-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.625rem 0.75rem;
    cursor: pointer;
    border-bottom: 1px solid var(--border);
    font-size: 0.875rem;
}

.pub-item:last-child {
    border-bottom: none;
}

.pub-item:hover {
    background: var(--bg-hover);
}

.pub-item.selected {
    background: var(--bg-hover);
    border-left: 3px solid var(--primary);
}

.pub-badge {
    font-size: 0.65rem;
    padding: 0.15rem 0.4rem;
    background: var(--primary);
    color: white;
    border-radius: 4px;
}

.btn-full {
    width: 100%;
}

@media (max-width: 700px) {
    .assign-grid {
        grid-template-columns: 1fr;
    }
}
</style>

@endsection
