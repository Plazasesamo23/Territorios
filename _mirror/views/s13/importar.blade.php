@extends('layouts.app')

@section('title', 'Añadir registros S-13')

@section('content')
<style>
    .import-page { max-width: 1100px; margin: 0 auto; padding: 0 1rem; }
    .import-card { background: var(--bg-card, #fff); border-radius: 12px; padding: 1.5rem; border: 1px solid var(--border-color, #e5e7eb); box-shadow: 0 2px 8px rgba(0,0,0,0.04); margin-top: 1.5rem; }
    .import-card-title { font-size: 1.1rem; font-weight: 600; color: var(--text-primary, #1f2937); margin-bottom: 1rem; display: flex; justify-content: space-between; align-items: center; }

    .entry-table { width: 100%; border-collapse: collapse; }
    .entry-table th { text-align: left; padding: 0.5rem; font-size: 0.85rem; color: var(--text-muted, #6b7280); font-weight: 500; border-bottom: 2px solid var(--border-color, #e5e7eb); }
    .entry-table td { padding: 0.4rem; vertical-align: middle; }
    .entry-table tr:hover { background: var(--bg-secondary, #f9fafb); }

    .entry-table input, .entry-table select {
        width: 100%; padding: 0.5rem; border: 1px solid #2d3339;
        border-radius: 6px; font-size: 0.9rem;
        background-color: #151719 !important;
        color: #f1f3f5 !important;
    }
    .entry-table input:focus, .entry-table select:focus {
        outline: none; border-color: #5c7fb8; box-shadow: 0 0 0 2px rgba(92,127,184,0.2);
    }

    .territorio-select { width: 110px !important; }
    .fecha-input { width: 130px !important; }

    .row-actions { display: flex; gap: 0.25rem; }
    .btn-icon {
        width: 32px; height: 32px; border: none; border-radius: 6px;
        cursor: pointer; display: flex; align-items: center; justify-content: center;
        font-size: 1rem; transition: all 0.15s;
    }
    .btn-copy { background: #e0f2fe; color: #0369a1; }
    .btn-copy:hover { background: #bae6fd; }
    .btn-delete { background: #fee2e2; color: #dc2626; }
    .btn-delete:hover { background: #fecaca; }

    .btn-add-row {
        background: #5c7fb8; color: white; border: none; padding: 0.6rem 1.2rem;
        border-radius: 6px; cursor: pointer; font-size: 0.9rem; font-weight: 500;
        display: inline-flex; align-items: center; gap: 0.4rem;
    }
    .btn-add-row:hover { background: #4a6da7; }

    .form-actions {
        display: flex; gap: 0.75rem; margin-top: 1.5rem;
        justify-content: space-between; align-items: center;
        padding-top: 1rem; border-top: 1px solid var(--border-color, #e5e7eb);
    }

    .btn-submit {
        background: #059669; color: white; border: none; padding: 0.7rem 1.5rem;
        border-radius: 6px; cursor: pointer; font-size: 0.95rem; font-weight: 500;
    }
    .btn-submit:hover { background: #047857; }
    .btn-submit:disabled { opacity: 0.5; cursor: not-allowed; }

    .btn-cancel {
        background: var(--bg-secondary, #f3f4f6); color: var(--text-primary, #374151);
        border: 1px solid var(--border-color, #d1d5db); padding: 0.7rem 1.5rem;
        border-radius: 6px; cursor: pointer; font-size: 0.95rem; text-decoration: none;
    }
    .btn-cancel:hover { background: #e5e7eb; }

    .count-badge {
        background: #5c7fb8; color: white; padding: 0.25rem 0.6rem;
        border-radius: 12px; font-size: 0.8rem;
    }

    .alert-success {
        background: rgba(34, 197, 94, 0.1); border: 1px solid rgba(34, 197, 94, 0.3);
        border-radius: 8px; padding: 0.875rem 1rem; color: #166534; font-size: 0.9rem; margin-bottom: 1rem;
    }
    .alert-error {
        background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.25);
        border-radius: 8px; padding: 0.875rem 1rem; color: #dc2626; font-size: 0.9rem; margin-bottom: 1rem;
    }

    .tips-box {
        background: rgba(59, 130, 246, 0.08); border: 1px solid rgba(59, 130, 246, 0.2);
        border-radius: 8px; padding: 0.75rem 1rem; font-size: 0.8rem; color: var(--text-secondary, #4b5563);
        margin-bottom: 1rem;
    }
    .tips-box strong { color: #1d4ed8; }

    @media (max-width: 768px) {
        .entry-table { display: block; overflow-x: auto; }
        .form-actions { flex-direction: column; }
        .form-actions > * { width: 100%; text-align: center; }
    }

    /* Autocomplete styles - COLORES FORZADOS */
    .autocomplete-wrapper { position: relative; }
    .autocomplete-list {
        position: absolute; top: 100%; left: 0; right: 0; z-index: 100;
        background-color: #1a1d21 !important;
        border: 1px solid #2d3339; border-radius: 6px;
        max-height: 200px; overflow-y: auto;
        box-shadow: 0 4px 12px rgba(0,0,0,0.5);
        display: none;
    }
    .autocomplete-list.show { display: block; }
    .autocomplete-item {
        padding: 0.6rem 0.75rem; cursor: pointer; font-size: 0.9rem;
        color: #f1f3f5 !important;
        background-color: #1a1d21 !important;
        border-bottom: 1px solid #2d3339;
    }
    .autocomplete-item:last-child { border-bottom: none; }
    .autocomplete-item:hover, .autocomplete-item.selected {
        background-color: #2d3a52 !important;
        color: #b8cceb !important;
    }
    .autocomplete-item small {
        color: #9ca3af !important;
        margin-left: 0.25rem;
    }

    /* Forzar colores en inputs */
    .publicador-search {
        background-color: #151719 !important;
        color: #f1f3f5 !important;
    }
    .publicador-search::placeholder {
        color: #6b7682 !important;
    }
</style>

<div class="page-nav">
    <div class="page-breadcrumbs">
        <a href="{{ route('dashboard') }}" class="breadcrumb-link">Inicio</a>
        <span class="breadcrumb-sep">›</span>
        <a href="{{ route('s13.index') }}" class="breadcrumb-link">S-13</a>
        <span class="breadcrumb-sep">›</span>
        <span class="breadcrumb-current">Añadir registros</span>
    </div>
</div>

<div class="import-page">
    @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert-error">{{ session('error') }}</div>
    @endif

    <div class="import-card">
        <div class="import-card-title">
            <span>Añadir registros del S-13 manualmente</span>
            <span class="count-badge" id="rowCount">0 registros</span>
        </div>

        <div class="tips-box">
            <strong>Cómo funciona:</strong>
            escribe una fila por asignación (territorio, publicador y fechas) y pulsa Guardar.
            Atajos: Tab para avanzar · botón 📋 copia las fechas de la fila anterior.
        </div>

        <form action="{{ route('s13.importar.guardar-rapido') }}" method="POST" id="quickEntryForm">
            @csrf
            <table class="entry-table">
                <thead>
                    <tr>
                        <th style="width:110px">Territorio</th>
                        <th>Publicador</th>
                        <th style="width:140px">F. Salida</th>
                        <th style="width:140px">F. Entrada</th>
                        <th style="width:70px"></th>
                    </tr>
                </thead>
                <tbody id="entryRows">
                </tbody>
            </table>

            <div style="margin-top: 1rem;">
                <button type="button" class="btn-add-row" id="addRowBtn">
                    + Añadir fila
                </button>
            </div>

            <div class="form-actions">
                <a href="{{ route('s13.index') }}" class="btn-cancel">Cancelar</a>
                <button type="submit" class="btn-submit" id="submitBtn" disabled>
                    Guardar registros
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Lista de publicadores para autocomplete
    const publicadores = @json(\App\Models\Publicador::orderBy('nombre')->get(['id', 'nombre', 'apellidos']));

    // Lista de territorios para el select
    const territorios = @json(\App\Models\Territorio::orderBy('numero')->pluck('numero'));

    let rowIndex = 0;
    let lastFechaSalida = '';
    let lastFechaEntrada = '';
    let lastTerritorio = '';

    // Generar opciones del select de territorios
    function getTerritorioOptions() {
        let options = '<option value="">-- Seleccionar --</option>';
        territorios.forEach(t => {
            const selected = (t == lastTerritorio) ? 'selected' : '';
            options += `<option value="${t}" ${selected}>${t}</option>`;
        });
        return options;
    }

    function createRow() {
        const row = document.createElement('tr');
        row.dataset.index = rowIndex;
        row.innerHTML = `
            <td>
                <select name="registros[${rowIndex}][territorio]" class="territorio-select">
                    ${getTerritorioOptions()}
                </select>
            </td>
            <td class="autocomplete-wrapper">
                <input type="text" class="publicador-search" placeholder="Buscar publicador..." autocomplete="off">
                <input type="hidden" name="registros[${rowIndex}][publicador_id]" class="publicador-id">
                <div class="autocomplete-list"></div>
            </td>
            <td>
                <input type="date" name="registros[${rowIndex}][fecha_salida]" class="fecha-input fecha-salida">
            </td>
            <td>
                <input type="date" name="registros[${rowIndex}][fecha_entrada]" class="fecha-input fecha-entrada">
            </td>
            <td>
                <div class="row-actions">
                    <button type="button" class="btn-icon btn-copy" title="Copiar fechas anteriores" onclick="copyDates(this)">📋</button>
                    <button type="button" class="btn-icon btn-delete" title="Eliminar fila" onclick="deleteRow(this)">✕</button>
                </div>
            </td>
        `;

        document.getElementById('entryRows').appendChild(row);
        rowIndex++;
        updateCount();
        setupAutocomplete(row);
        setupRowHandlers(row);

        // Focus en territorio
        row.querySelector('.territorio-select').focus();
    }

    function setupAutocomplete(row) {
        const searchInput = row.querySelector('.publicador-search');
        const hiddenInput = row.querySelector('.publicador-id');
        const list = row.querySelector('.autocomplete-list');
        let selectedIndex = -1;

        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            if (query.length < 1) {
                list.classList.remove('show');
                return;
            }

            const matches = publicadores.filter(p => {
                const fullName = (p.nombre + ' ' + p.apellidos).toLowerCase();
                return fullName.includes(query);
            }).slice(0, 10);

            if (matches.length === 0) {
                list.classList.remove('show');
                return;
            }

            list.innerHTML = matches.map((p, i) => `
                <div class="autocomplete-item" data-id="${p.id}" data-name="${p.nombre} ${p.apellidos}">
                    ${p.nombre} <small>${p.apellidos}</small>
                </div>
            `).join('');

            list.classList.add('show');
            selectedIndex = -1;

            list.querySelectorAll('.autocomplete-item').forEach(item => {
                item.addEventListener('click', function() {
                    selectPublicador(searchInput, hiddenInput, list, this.dataset.id, this.dataset.name);
                });
            });
        });

        searchInput.addEventListener('keydown', function(e) {
            const items = list.querySelectorAll('.autocomplete-item');
            if (!list.classList.contains('show') || items.length === 0) {
                if (e.key === 'Enter') e.preventDefault();
                return;
            }

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                selectedIndex = Math.min(selectedIndex + 1, items.length - 1);
                updateSelection(items, selectedIndex);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                selectedIndex = Math.max(selectedIndex - 1, 0);
                updateSelection(items, selectedIndex);
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (selectedIndex >= 0 && items[selectedIndex]) {
                    const item = items[selectedIndex];
                    selectPublicador(searchInput, hiddenInput, list, item.dataset.id, item.dataset.name);
                } else if (items.length > 0) {
                    // Seleccionar el primero si no hay seleccion
                    const item = items[0];
                    selectPublicador(searchInput, hiddenInput, list, item.dataset.id, item.dataset.name);
                }
            } else if (e.key === 'Escape') {
                list.classList.remove('show');
            }
        });

        searchInput.addEventListener('blur', function() {
            setTimeout(() => list.classList.remove('show'), 200);
        });

        // Al hacer focus, mostrar lista si hay texto
        searchInput.addEventListener('focus', function() {
            if (this.value.length >= 1) {
                this.dispatchEvent(new Event('input'));
            }
        });
    }

    function updateSelection(items, index) {
        items.forEach((item, i) => {
            item.classList.toggle('selected', i === index);
            if (i === index) {
                item.scrollIntoView({ block: 'nearest' });
            }
        });
    }

    function selectPublicador(searchInput, hiddenInput, list, id, name) {
        searchInput.value = name;
        hiddenInput.value = id;
        list.classList.remove('show');
        updateSubmitButton();
        // Avanzar al siguiente campo
        const row = searchInput.closest('tr');
        row.querySelector('.fecha-salida').focus();
    }

    function setupRowHandlers(row) {
        const fechaSalida = row.querySelector('.fecha-salida');
        const fechaEntrada = row.querySelector('.fecha-entrada');
        const territorioSelect = row.querySelector('.territorio-select');

        // Al cambiar territorio, guardar para siguiente fila
        territorioSelect.addEventListener('change', function() {
            if (this.value) lastTerritorio = this.value;
            updateSubmitButton();
        });

        fechaSalida.addEventListener('change', function() {
            if (this.value) lastFechaSalida = this.value;
            updateSubmitButton();
        });

        fechaEntrada.addEventListener('change', function() {
            if (this.value) lastFechaEntrada = this.value;
        });
    }

    function copyDates(btn) {
        const row = btn.closest('tr');
        if (lastFechaSalida) row.querySelector('.fecha-salida').value = lastFechaSalida;
        if (lastFechaEntrada) row.querySelector('.fecha-entrada').value = lastFechaEntrada;
        updateSubmitButton();
    }

    function deleteRow(btn) {
        const row = btn.closest('tr');
        row.remove();
        updateCount();
        updateSubmitButton();
    }

    function updateCount() {
        const count = document.querySelectorAll('#entryRows tr').length;
        document.getElementById('rowCount').textContent = count + ' registro' + (count !== 1 ? 's' : '');
    }

    function updateSubmitButton() {
        const rows = document.querySelectorAll('#entryRows tr');
        let validRows = 0;

        rows.forEach(row => {
            const territorio = row.querySelector('.territorio-select').value;
            const publicadorId = row.querySelector('.publicador-id').value;
            const fechaSalida = row.querySelector('.fecha-salida').value;

            if (territorio && publicadorId && fechaSalida) {
                validRows++;
            }
        });

        document.getElementById('submitBtn').disabled = validRows === 0;
    }

    // Añadir primera fila al cargar
    document.addEventListener('DOMContentLoaded', function() {
        createRow();
        document.getElementById('addRowBtn').addEventListener('click', createRow);
    });
</script>
@endsection
