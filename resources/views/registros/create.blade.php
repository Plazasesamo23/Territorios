@extends('layouts.app')

@section('title', 'Asignar Territorio')

@section('content')

<nav class="page-nav">
    <div class="page-breadcrumbs">
        <a href="{{ route('panel-territorios') }}" class="breadcrumb-link">Territorios</a>
        <span class="breadcrumb-separator">></span>
        <span class="breadcrumb-current">Asignar</span>
    </div>
    <div class="page-actions">
        <a href="{{ route('panel-territorios') }}" class="btn btn-secondary">← Volver</a>
    </div>
</nav>

<div class="asignar-container">
    <div class="asignar-card">
        <div class="asignar-header">
            <div class="asignar-title">Asignar Territorio</div>
            <div class="asignar-subtitle">Selecciona territorio y publicador</div>
        </div>

        <div class="asignar-body">
            @if($territoriosDisponibles->count() == 0)
                <div class="error-msg">
                    <strong>No hay territorios disponibles</strong><br>
                    Todos están asignados o en período de descanso.
                </div>
            @else
                <form action="{{ route('registros.store') }}" method="POST" id="asignar-form">
                    @csrf

                    @php
                        $territoriosNormales = $territoriosDisponibles->where('tipo', 'normal');
                        $territoriosCampana = $territoriosDisponibles->where('tipo', 'campana');
                        $territoriosNegocios = $territoriosDisponibles->where('tipo', 'negocios');
                    @endphp

                    <!-- Paso 1: Territorio -->
                    <div class="step-section">
                        <div class="step-label">
                            <span class="step-number" id="step1-number">1</span>
                            Selecciona un territorio
                        </div>

                        <!-- Filtro por zona -->
                        @php
                            $zonasUnicas = $territoriosDisponibles->pluck('zona')->filter()->unique()->sort();
                        @endphp
                        @if($zonasUnicas->count() > 1)
                        <div class="zona-filter-wrapper">
                            <label class="zona-filter-label">Filtrar por zona:</label>
                            <select id="zona-filter" class="zona-filter-select">
                                <option value="">Todas las zonas</option>
                                @foreach($zonasUnicas as $zona)
                                    <option value="{{ $zona }}">{{ $zona }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        <!-- Filtros por tipo -->
                        <div class="tipo-filters">
                            <button type="button" class="tipo-btn todos active" data-tipo="todos">
                                Todos
                                <span class="tipo-count">{{ $territoriosDisponibles->count() }}</span>
                            </button>
                            <button type="button" class="tipo-btn normal" data-tipo="normal">
                                Normal
                                <span class="tipo-count">{{ $territoriosNormales->count() }}</span>
                            </button>
                            <button type="button" class="tipo-btn campana" data-tipo="campana">
                                Campana
                                <span class="tipo-count">{{ $territoriosCampana->count() }}</span>
                            </button>
                            <button type="button" class="tipo-btn negocios" data-tipo="negocios">
                                Negocios
                                <span class="tipo-count">{{ $territoriosNegocios->count() }}</span>
                            </button>
                        </div>

                        <!-- Select unico -->
                        <div class="territorio-select-wrapper">
                            <select name="territorio_id" id="territorio-select" class="territorio-select" required>
                                <option value="">-- Selecciona un territorio --</option>
                                @foreach($territoriosDisponibles->sortBy('numero') as $territorio)
                                    <option value="{{ $territorio->id }}"
                                            data-tipo="{{ $territorio->tipo }}"
                                            data-zona="{{ $territorio->zona }}"
                                            data-numero="{{ $territorio->numero_completo }}">
                                        {{ $territorio->numero_completo }}@if($territorio->zona) - {{ $territorio->zona }}@endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="territorio-selected-info" id="territorio-info">
                            <div class="territorio-selected-name" id="territorio-info-name"></div>
                            <div class="territorio-selected-tipo" id="territorio-info-tipo"></div>
                        </div>
                    </div>

                    <!-- Paso 2: Publicador (oculto hasta seleccionar territorio) -->
                    <div class="step-section step-publicador" id="step-publicador">
                        <div class="step-label">
                            <span class="step-number" id="step2-number">2</span>
                            Selecciona un publicador
                        </div>

                        <!-- Barra de busqueda -->
                        <div class="search-wrapper">
                            <span class="search-icon">&#x1F50D;</span>
                            <input type="text"
                                   id="buscar-publicador"
                                   class="search-input"
                                   placeholder="Buscar publicador...">
                        </div>

                        <!-- Lista de publicadores -->
                        <div class="publicadores-list" id="publicadores-list">
                            @foreach($publicadoresActivos->sortBy('nombre') as $publicador)
                                <div class="publicador-item"
                                     data-id="{{ $publicador->id }}"
                                     data-nombre="{{ strtolower($publicador->nombre . ' ' . $publicador->apellidos) }}">
                                    <span class="publicador-radio"></span>
                                    <span class="publicador-name">{{ $publicador->nombre }} {{ $publicador->apellidos }}</span>
                                    @if($publicador->es_precursor)
                                        <span class="publicador-badge">PR</span>
                                    @endif
                                </div>
                            @endforeach
                            <div class="no-results" id="no-results" style="display: none;">
                                No se encontraron publicadores
                            </div>
                        </div>

                        <input type="hidden" name="publicador_id" id="publicador-id-input" required>
                    </div>

                    <!-- Boton enviar -->
                    <div class="submit-section">
                        <button type="submit" class="btn-submit" id="btn-submit" disabled>
                            <span class="btn-submit-icon">&#x1F4F2;</span>
                            Asignar y Enviar WhatsApp
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const territorioSelect = document.getElementById('territorio-select');
    const stepPublicador = document.getElementById('step-publicador');
    const territorioInfo = document.getElementById('territorio-info');
    const step1Number = document.getElementById('step1-number');
    const step2Number = document.getElementById('step2-number');
    const btnSubmit = document.getElementById('btn-submit');
    const publicadorInput = document.getElementById('publicador-id-input');
    const buscarInput = document.getElementById('buscar-publicador');
    const publicadoresList = document.getElementById('publicadores-list');
    const noResults = document.getElementById('no-results');

    if (!territorioSelect) return;

    // Guardar opciones originales del select
    const opcionesOriginales = Array.from(territorioSelect.options).slice(1).map(opt => ({
        value: opt.value,
        text: opt.textContent,
        tipo: opt.dataset.tipo,
        zona: opt.dataset.zona,
        numero: opt.dataset.numero
    }));

    // Variables de filtro
    const zonaFilter = document.getElementById('zona-filter');
    let zonaSeleccionada = '';
    let tipoSeleccionado = 'todos';

    // Funcion centralizada de filtrado
    function filtrarTerritorios() {
        territorioSelect.innerHTML = '<option value="">-- Selecciona un territorio --</option>';

        opcionesOriginales.forEach(opt => {
            const cumpleTipo = tipoSeleccionado === 'todos' || opt.tipo === tipoSeleccionado;
            const cumpleZona = !zonaSeleccionada || opt.zona === zonaSeleccionada;

            if (cumpleTipo && cumpleZona) {
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
        territorioInfo.classList.remove('show');
        stepPublicador.classList.remove('show');
        step1Number.classList.remove('completed');
        step1Number.textContent = '1';
        updateSubmitButton();
    }

    // Filtrar por zona
    if (zonaFilter) {
        zonaFilter.addEventListener('change', function() {
            zonaSeleccionada = this.value;
            filtrarTerritorios();
        });
    }

    // Filtrar por tipo
    document.querySelectorAll('.tipo-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.tipo-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            tipoSeleccionado = this.dataset.tipo;
            filtrarTerritorios();
        });
    });

    // Al seleccionar territorio
    territorioSelect.addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];

        if (this.value) {
            document.getElementById('territorio-info-name').textContent = selected.dataset.numero + (selected.dataset.zona ? ' - ' + selected.dataset.zona : '');

            const tipoLabels = {
                'normal': 'Territorio Normal',
                'campana': 'Territorio de Campana',
                'negocios': 'Territorio de Negocios'
            };
            document.getElementById('territorio-info-tipo').textContent = tipoLabels[selected.dataset.tipo] || '';

            territorioInfo.classList.add('show');
            step1Number.classList.add('completed');
            step1Number.textContent = '✓';

            setTimeout(() => {
                stepPublicador.classList.add('show');
                buscarInput.focus();
            }, 200);
        } else {
            territorioInfo.classList.remove('show');
            stepPublicador.classList.remove('show');
            step1Number.classList.remove('completed');
            step1Number.textContent = '1';
        }

        updateSubmitButton();
    });

    // Buscar publicador
    if (buscarInput) {
        buscarInput.addEventListener('input', function() {
            const busqueda = this.value.toLowerCase().trim();
            const items = publicadoresList.querySelectorAll('.publicador-item');
            let hayResultados = false;

            items.forEach(item => {
                const nombre = item.dataset.nombre;
                if (nombre.includes(busqueda)) {
                    item.style.display = 'flex';
                    hayResultados = true;
                } else {
                    item.style.display = 'none';
                }
            });

            noResults.style.display = hayResultados ? 'none' : 'block';
        });
    }

    // Seleccionar publicador
    if (publicadoresList) {
        publicadoresList.querySelectorAll('.publicador-item').forEach(item => {
            item.addEventListener('click', function() {
                publicadoresList.querySelectorAll('.publicador-item').forEach(i => i.classList.remove('selected'));
                this.classList.add('selected');
                publicadorInput.value = this.dataset.id;
                step2Number.classList.add('completed');
                step2Number.textContent = '✓';
                updateSubmitButton();
            });
        });
    }

    function updateSubmitButton() {
        const territorioOk = territorioSelect.value !== '';
        const publicadorOk = publicadorInput.value !== '';
        btnSubmit.disabled = !(territorioOk && publicadorOk);
    }
});
</script>

<style>
.asignar-container {
    max-width: 600px;
    margin: 0 auto;
    padding: 1rem;
}

.asignar-card {
    background: var(--bg-card, #fff);
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    overflow: hidden;
}

.asignar-header {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    padding: 1.5rem;
    text-align: center;
}

.asignar-title {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 0.25rem;
}

.asignar-subtitle {
    opacity: 0.9;
    font-size: 0.9rem;
}

.asignar-body {
    padding: 1.5rem;
}

.step-section {
    margin-bottom: 1.5rem;
}

.step-label {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-weight: 600;
    font-size: 1rem;
    margin-bottom: 1rem;
    color: var(--text-primary, #1f2937);
}

.step-number {
    width: 28px;
    height: 28px;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.85rem;
    font-weight: 700;
}

.step-number.completed {
    background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
}

/* Filtro de zona */
.zona-filter-wrapper {
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.zona-filter-label {
    font-size: 0.85rem;
    font-weight: 500;
    color: var(--text-muted, #6b7280);
    white-space: nowrap;
}

.zona-filter-select {
    flex: 1;
    padding: 0.5rem 0.75rem;
    border: 2px solid var(--border-color, #e5e7eb);
    border-radius: 8px;
    font-size: 0.85rem;
    background: var(--bg-card, #fff);
    color: var(--text-primary, #1f2937);
    cursor: pointer;
    transition: all 0.2s;
}

.zona-filter-select:focus {
    outline: none;
    border-color: #10b981;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
}

.tipo-filters {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.tipo-btn {
    flex: 1;
    padding: 0.6rem 0.5rem;
    border: 2px solid var(--border-color, #e5e7eb);
    border-radius: 10px;
    background: var(--bg-card, #fff);
    cursor: pointer;
    transition: all 0.2s;
    text-align: center;
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--text-primary, #374151);
}

.tipo-btn:hover {
    border-color: #d1d5db;
}

.tipo-btn.active.todos { background: #10b981; border-color: #10b981; color: white; }
.tipo-btn.active.normal { background: #22c55e; border-color: #22c55e; color: white; }
.tipo-btn.active.campana { background: #f59e0b; border-color: #f59e0b; color: white; }
.tipo-btn.active.negocios { background: #3b82f6; border-color: #3b82f6; color: white; }

.tipo-count {
    display: block;
    font-size: 0.7rem;
    opacity: 0.8;
    margin-top: 2px;
}

.territorio-select {
    width: 100%;
    padding: 0.85rem 1rem;
    border: 2px solid var(--border-color, #e5e7eb);
    border-radius: 10px;
    font-size: 0.95rem;
    background: var(--bg-card, #fff);
    color: var(--text-primary, #1f2937);
    cursor: pointer;
    transition: all 0.2s;
}

.territorio-select:focus {
    outline: none;
    border-color: #10b981;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
}

.territorio-selected-info {
    margin-top: 0.75rem;
    padding: 0.75rem 1rem;
    background: rgba(16, 185, 129, 0.1);
    border-radius: 8px;
    border-left: 3px solid #10b981;
    display: none;
}

.territorio-selected-info.show {
    display: block;
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.territorio-selected-name {
    font-weight: 600;
    color: var(--text-primary, #1f2937);
}

.territorio-selected-tipo {
    font-size: 0.8rem;
    color: var(--text-muted, #6b7280);
    margin-top: 0.25rem;
}

.step-publicador {
    display: none;
    animation: fadeSlideIn 0.4s ease;
}

.step-publicador.show {
    display: block;
}

@keyframes fadeSlideIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.search-wrapper {
    position: relative;
    margin-bottom: 0.75rem;
}

.search-input {
    width: 100%;
    padding: 0.75rem 1rem 0.75rem 2.5rem;
    border: 2px solid var(--border-color, #e5e7eb);
    border-radius: 10px;
    font-size: 0.95rem;
    background: var(--bg-card, #fff);
    color: var(--text-primary, #1f2937);
    transition: all 0.2s;
}

.search-input:focus {
    outline: none;
    border-color: #10b981;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
}

.search-icon {
    position: absolute;
    left: 0.85rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted, #9ca3af);
    pointer-events: none;
}

.publicadores-list {
    max-height: 250px;
    overflow-y: auto;
    border: 2px solid var(--border-color, #e5e7eb);
    border-radius: 10px;
    background: var(--bg-card, #fff);
}

.publicador-item {
    padding: 0.75rem 1rem;
    cursor: pointer;
    transition: all 0.15s;
    border-bottom: 1px solid var(--border-color, #f3f4f6);
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.publicador-item:last-child {
    border-bottom: none;
}

.publicador-item:hover {
    background: var(--bg-hover, #f9fafb);
}

.publicador-item.selected {
    background: rgba(16, 185, 129, 0.1);
    border-left: 3px solid #10b981;
}

.publicador-radio {
    width: 18px;
    height: 18px;
    border: 2px solid var(--border-color, #d1d5db);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: all 0.2s;
}

.publicador-item.selected .publicador-radio {
    border-color: #10b981;
    background: #10b981;
}

.publicador-item.selected .publicador-radio::after {
    content: '';
    width: 6px;
    height: 6px;
    background: white;
    border-radius: 50%;
}

.publicador-name {
    font-weight: 500;
    color: var(--text-primary, #1f2937);
}

.publicador-badge {
    margin-left: auto;
    padding: 0.15rem 0.5rem;
    border-radius: 12px;
    font-size: 0.7rem;
    font-weight: 600;
    background: #d1fae5;
    color: #065f46;
}

.no-results {
    padding: 1.5rem;
    text-align: center;
    color: var(--text-muted, #6b7280);
    font-size: 0.9rem;
}

.submit-section {
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid var(--border-color, #e5e7eb);
}

.btn-submit {
    width: 100%;
    padding: 1rem;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.btn-submit:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
}

.btn-submit:disabled {
    background: #d1d5db;
    cursor: not-allowed;
}

.btn-submit-icon {
    font-size: 1.2rem;
}

.error-msg {
    background: #fef2f2;
    border: 1px solid #fecaca;
    color: #dc2626;
    padding: 1rem;
    border-radius: 10px;
    text-align: center;
    margin-bottom: 1rem;
}

/* Responsive */
@media (max-width: 768px) {
    .asignar-container { padding: 0.5rem; }
    .asignar-header { padding: 1.25rem 1rem; }
    .asignar-title { font-size: 1.25rem; }
    .asignar-body { padding: 1rem; }
    .publicadores-list { max-height: 200px; }
}

@media (max-width: 480px) {
    .tipo-filters { flex-wrap: wrap; gap: 0.4rem; }
    .tipo-btn { flex: 1 1 45%; padding: 0.5rem 0.25rem; font-size: 0.75rem; }
}

/* ========================================
   DARK THEME
   ======================================== */
[data-theme="dark"] .asignar-card {
    background: #171717;
    border: 1px solid #262626;
}

[data-theme="dark"] .asignar-header {
    background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
    color: #0a0a0a;
}

[data-theme="dark"] .step-label {
    color: #e5e5e5;
}

[data-theme="dark"] .step-number {
    background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
    color: #0a0a0a;
}

[data-theme="dark"] .tipo-btn {
    background: #262626;
    border-color: #404040;
    color: #e5e5e5;
}

[data-theme="dark"] .tipo-btn:hover {
    border-color: #525252;
}

[data-theme="dark"] .tipo-btn.active.todos,
[data-theme="dark"] .tipo-btn.active.normal {
    background: #f97316;
    border-color: #f97316;
    color: #0a0a0a;
}

[data-theme="dark"] .zona-filter-label {
    color: #a3a3a3;
}

[data-theme="dark"] .zona-filter-select {
    background: #262626;
    border-color: #404040;
    color: #e5e5e5;
}

[data-theme="dark"] .zona-filter-select:focus {
    border-color: #f97316;
    box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.15);
}

[data-theme="dark"] .territorio-select,
[data-theme="dark"] .search-input {
    background: #262626;
    border-color: #404040;
    color: #e5e5e5;
}

[data-theme="dark"] .territorio-select:focus,
[data-theme="dark"] .search-input:focus {
    border-color: #f97316;
    box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.15);
}

[data-theme="dark"] .territorio-selected-info {
    background: rgba(249, 115, 22, 0.15);
    border-left-color: #f97316;
}

[data-theme="dark"] .territorio-selected-name {
    color: #f5f5f5;
}

[data-theme="dark"] .territorio-selected-tipo {
    color: #a3a3a3;
}

[data-theme="dark"] .publicadores-list {
    background: #262626;
    border-color: #404040;
}

[data-theme="dark"] .publicador-item {
    border-bottom-color: #404040;
}

[data-theme="dark"] .publicador-item:hover {
    background: rgba(249, 115, 22, 0.1);
}

[data-theme="dark"] .publicador-item.selected {
    background: rgba(249, 115, 22, 0.15);
    border-left-color: #f97316;
}

[data-theme="dark"] .publicador-item.selected .publicador-radio {
    border-color: #f97316;
    background: #f97316;
}

[data-theme="dark"] .publicador-radio {
    border-color: #525252;
}

[data-theme="dark"] .publicador-name {
    color: #e5e5e5;
}

[data-theme="dark"] .publicador-badge {
    background: rgba(34, 197, 94, 0.2);
    color: #86efac;
}

[data-theme="dark"] .no-results {
    color: #a3a3a3;
}

[data-theme="dark"] .submit-section {
    border-top-color: #262626;
}

[data-theme="dark"] .btn-submit {
    background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
    color: #0a0a0a;
}

[data-theme="dark"] .btn-submit:hover:not(:disabled) {
    box-shadow: 0 4px 12px rgba(249, 115, 22, 0.4);
}

[data-theme="dark"] .btn-submit:disabled {
    background: #404040;
    color: #737373;
}

[data-theme="dark"] .error-msg {
    background: rgba(220, 38, 38, 0.15);
    border-color: #dc2626;
    color: #fca5a5;
}
</style>

@endsection
