@extends('layouts.app')

@section('title', 'Vista Previa de Importación')

@section('content')
<style>
    .preview-container {
        max-width: 1400px;
        margin: 0 auto;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .stat-box {
        background: var(--bg-card);
        border-radius: 10px;
        padding: 1.25rem;
        text-align: center;
        border: 1px solid var(--border-color);
    }

    .stat-box-number {
        font-size: 2rem;
        font-weight: 700;
        line-height: 1;
    }

    .stat-box-label {
        font-size: 0.875rem;
        color: var(--text-muted);
        margin-top: 0.5rem;
    }

    .stat-green .stat-box-number { color: #4a6da7; }
    .stat-yellow .stat-box-number { color: #4a6da7; }
    .stat-red .stat-box-number { color: #495057; }
    .stat-blue .stat-box-number { color: #4a6da7; }

    .preview-table-wrapper {
        overflow-x: auto;
        background: var(--bg-card);
        border-radius: 12px;
        border: 1px solid var(--border-color);
    }

    .preview-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 1100px;
    }

    .preview-table th {
        background: var(--bg-secondary);
        padding: 1rem 0.75rem;
        text-align: left;
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--text-muted);
        border-bottom: 2px solid var(--border-color);
    }

    .preview-table th.center,
    .preview-table td.center {
        text-align: center;
    }

    .preview-table td {
        padding: 0.875rem 0.75rem;
        border-bottom: 1px solid var(--border-color);
        vertical-align: middle;
    }

    .preview-table tbody tr:hover {
        background: rgba(99, 102, 241, 0.03);
    }

    .row-sobreposicion {
        background: rgba(239, 68, 68, 0.08) !important;
    }

    .row-territorio-invalido {
        background: rgba(245, 158, 11, 0.08) !important;
    }

    .row-sin-match {
        background: rgba(59, 130, 246, 0.05) !important;
    }

    .territorio-num {
        font-weight: 700;
        font-size: 1rem;
    }

    .territorio-error {
        color: #343a40;
        font-size: 0.75rem;
        display: block;
    }

    .publicador-raw {
        font-size: 0.875rem;
        color: var(--text-secondary);
    }

    .confidence-badge {
        display: inline-block;
        font-size: 0.7rem;
        padding: 0.15rem 0.4rem;
        border-radius: 4px;
        margin-left: 0.5rem;
    }

    .confidence-high {
        background: rgba(34, 197, 94, 0.15);
        color: #3d5a8a;
    }

    .confidence-medium {
        background: rgba(245, 158, 11, 0.15);
        color: #3d5a8a;
    }

    .confidence-low {
        background: rgba(239, 68, 68, 0.15);
        color: #343a40;
    }

    .select-publicador {
        width: 100%;
        min-width: 180px;
        padding: 0.5rem;
        border: 1px solid var(--border-color);
        border-radius: 6px;
        background: var(--bg-card);
        font-size: 0.875rem;
    }

    .select-publicador:focus {
        outline: none;
        border-color: #5c7fb8;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }

    .input-fecha {
        padding: 0.4rem 0.5rem;
        border: 1px solid var(--border-color);
        border-radius: 6px;
        background: var(--bg-card);
        font-size: 0.8rem;
        width: 130px;
        color: var(--text-primary);
    }

    .input-fecha:focus {
        outline: none;
        border-color: #5c7fb8;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }

    .input-fecha:disabled {
        background: var(--bg-secondary);
        opacity: 0.6;
        cursor: not-allowed;
    }

    .fecha-cell {
        white-space: nowrap;
    }

    .badge {
        display: inline-block;
        padding: 0.25rem 0.6rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 500;
    }

    .badge-ok {
        background: rgba(34, 197, 94, 0.15);
        color: #3d5a8a;
    }

    .badge-sobreposicion {
        background: rgba(239, 68, 68, 0.15);
        color: #343a40;
    }

    .badge-revisar {
        background: rgba(245, 158, 11, 0.15);
        color: #3d5a8a;
    }

    .badge-invalido {
        background: rgba(107, 114, 128, 0.15);
        color: #6b7280;
    }

    .checkbox-cell {
        width: 50px;
    }

    .checkbox-cell input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }

    .checkbox-cell input[type="checkbox"]:disabled {
        cursor: not-allowed;
        opacity: 0.5;
    }

    .conflicto-tooltip {
        font-size: 0.75rem;
        color: #343a40;
        display: block;
        margin-top: 0.25rem;
    }

    .actions-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 2rem;
        padding: 1.5rem;
        background: var(--bg-card);
        border-radius: 12px;
        border: 1px solid var(--border-color);
    }

    .actions-info {
        color: var(--text-muted);
        font-size: 0.875rem;
    }

    .actions-buttons {
        display: flex;
        gap: 1rem;
    }

    .legend {
        display: flex;
        gap: 1.5rem;
        flex-wrap: wrap;
        margin-bottom: 1rem;
        padding: 1rem;
        background: var(--bg-secondary);
        border-radius: 8px;
        font-size: 0.875rem;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .legend-color {
        width: 16px;
        height: 16px;
        border-radius: 4px;
    }

    .legend-sobreposicion { background: rgba(239, 68, 68, 0.3); }
    .legend-invalido { background: rgba(245, 158, 11, 0.3); }
    .legend-revisar { background: rgba(59, 130, 246, 0.2); }
    .legend-ok { background: rgba(34, 197, 94, 0.2); }

    .edit-notice {
        background: rgba(99, 102, 241, 0.08);
        border: 1px solid rgba(99, 102, 241, 0.2);
        border-radius: 8px;
        padding: 0.875rem 1rem;
        margin-bottom: 1rem;
        color: var(--text-secondary);
        font-size: 0.875rem;
    }

    .edit-notice strong {
        color: #5c7fb8;
    }

    /* Botón ver original */
    .btn-ver-original {
        background: none;
        border: 1px solid var(--border-color);
        border-radius: 4px;
        padding: 0.25rem 0.5rem;
        font-size: 0.7rem;
        color: var(--text-muted);
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-ver-original:hover {
        background: var(--bg-secondary);
        border-color: #5c7fb8;
        color: #5c7fb8;
    }

    /* Modal de datos originales */
    .modal-raw {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.6);
        backdrop-filter: blur(4px);
        z-index: 9999;
        justify-content: center;
        align-items: center;
        padding: 1rem;
    }

    .modal-raw.active {
        display: flex;
    }

    .modal-raw-content {
        background: var(--bg-card, #fff);
        border-radius: 12px;
        max-width: 700px;
        width: 100%;
        max-height: 85vh;
        overflow: hidden;
        box-shadow: 0 25px 50px rgba(0,0,0,0.25);
    }

    .modal-raw-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 1.25rem;
        background: linear-gradient(135deg, #5c7fb8 0%, #4a6da7 100%);
        color: white;
    }

    .modal-raw-title {
        font-size: 1.1rem;
        font-weight: 600;
        margin: 0;
    }

    .modal-raw-close {
        background: rgba(255,255,255,0.2);
        border: none;
        border-radius: 50%;
        width: 32px;
        height: 32px;
        color: white;
        font-size: 1.25rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .modal-raw-close:hover {
        background: rgba(255,255,255,0.3);
    }

    .modal-raw-body {
        padding: 1.25rem;
        overflow-y: auto;
        max-height: calc(85vh - 60px);
    }

    .raw-section {
        margin-bottom: 1.25rem;
    }

    .raw-section:last-child {
        margin-bottom: 0;
    }

    .raw-section-title {
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-muted);
        margin-bottom: 0.5rem;
        padding-bottom: 0.35rem;
        border-bottom: 1px solid var(--border-color);
    }

    .raw-content {
        background: var(--bg-secondary, #f3f4f6);
        border-radius: 8px;
        padding: 0.875rem;
        font-family: 'Consolas', 'Monaco', monospace;
        font-size: 0.85rem;
        color: var(--text-primary);
        white-space: pre-wrap;
        word-break: break-word;
        line-height: 1.5;
    }

    .raw-comparison {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }

    .raw-col {
        background: var(--bg-secondary, #f3f4f6);
        border-radius: 8px;
        padding: 0.75rem;
    }

    .raw-col-title {
        font-size: 0.7rem;
        font-weight: 600;
        color: var(--text-muted);
        margin-bottom: 0.5rem;
        text-transform: uppercase;
    }

    .raw-col-value {
        font-size: 0.9rem;
        color: var(--text-primary);
        font-weight: 500;
    }

    .raw-col-value.interpreted {
        color: #4a6da7;
    }

    .raw-highlight {
        background: rgba(251, 191, 36, 0.3);
        padding: 0.1rem 0.25rem;
        border-radius: 3px;
    }

    /* Panel de texto completo */
    .texto-completo-toggle {
        display: flex;
        justify-content: flex-end;
        margin-bottom: 1rem;
    }

    .btn-texto-completo {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 6px;
        font-size: 0.85rem;
        color: var(--text-secondary);
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-texto-completo:hover {
        border-color: #5c7fb8;
        color: #5c7fb8;
    }

    .texto-completo-panel {
        display: none;
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        margin-bottom: 1.5rem;
        overflow: hidden;
    }

    .texto-completo-panel.show {
        display: block;
    }

    .texto-completo-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.875rem 1rem;
        background: var(--bg-secondary);
        border-bottom: 1px solid var(--border-color);
    }

    .texto-completo-title {
        font-weight: 600;
        font-size: 0.9rem;
    }

    .texto-completo-body {
        padding: 1rem;
        max-height: 400px;
        overflow-y: auto;
    }

    .texto-completo-content {
        background: var(--bg-secondary);
        border-radius: 8px;
        padding: 1rem;
        font-family: 'Consolas', 'Monaco', monospace;
        font-size: 0.8rem;
        white-space: pre-wrap;
        word-break: break-word;
        line-height: 1.6;
        color: var(--text-primary);
    }

    /* Responsive */
    @media (max-width: 1200px) {
        .preview-container {
            padding: 0 1rem;
        }

        .stat-box-number {
            font-size: 1.75rem;
        }

        .input-fecha {
            width: 115px;
            font-size: 0.75rem;
        }
    }

    @media (max-width: 768px) {
        .preview-container {
            padding: 0 0.75rem;
        }

        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 0.75rem;
        }

        .stat-box {
            padding: 1rem;
            border-radius: 8px;
        }

        .stat-box-number {
            font-size: 1.5rem;
        }

        .stat-box-label {
            font-size: 0.75rem;
        }

        .legend {
            flex-direction: column;
            gap: 0.5rem;
            padding: 0.75rem;
            font-size: 0.8rem;
        }

        .edit-notice {
            font-size: 0.8rem;
            padding: 0.75rem;
        }

        .preview-table-wrapper {
            border-radius: 10px;
            margin: 0 -0.75rem;
            border-left: none;
            border-right: none;
            border-radius: 0;
        }

        .preview-table {
            min-width: 900px;
        }

        .preview-table th,
        .preview-table td {
            padding: 0.75rem 0.5rem;
            font-size: 0.8rem;
        }

        .territorio-num {
            font-size: 0.9rem;
        }

        .publicador-raw {
            font-size: 0.8rem;
        }

        .select-publicador {
            min-width: 140px;
            padding: 0.4rem;
            font-size: 0.8rem;
        }

        .input-fecha {
            width: 105px;
            padding: 0.35rem 0.4rem;
            font-size: 0.7rem;
        }

        .badge {
            padding: 0.2rem 0.4rem;
            font-size: 0.65rem;
        }

        .actions-bar {
            flex-direction: column;
            gap: 1rem;
            text-align: center;
            padding: 1rem;
            margin-top: 1.5rem;
            border-radius: 10px;
        }

        .actions-buttons {
            width: 100%;
            flex-direction: column;
            gap: 0.75rem;
        }

        .actions-buttons .btn {
            width: 100%;
            min-height: 48px;
        }

        .raw-comparison {
            grid-template-columns: 1fr;
        }

        .modal-raw-content {
            max-height: 95vh;
        }

        .modal-raw-body {
            max-height: calc(95vh - 60px);
        }
    }

    @media (max-width: 480px) {
        .stats-grid {
            gap: 0.5rem;
        }

        .stat-box {
            padding: 0.75rem;
        }

        .stat-box-number {
            font-size: 1.25rem;
        }

        .preview-table {
            min-width: 800px;
        }

        .preview-table th,
        .preview-table td {
            padding: 0.625rem 0.375rem;
            font-size: 0.75rem;
        }

        .checkbox-cell {
            width: 40px;
        }

        .checkbox-cell input[type="checkbox"] {
            width: 20px;
            height: 20px;
        }

        .btn-ver-original {
            font-size: 0.65rem;
            padding: 0.2rem 0.35rem;
        }
    }

    /* Dark theme */
    [data-theme="dark"] .raw-content,
    [data-theme="dark"] .raw-col,
    [data-theme="dark"] .texto-completo-content {
        background: rgba(255,255,255,0.05);
    }
</style>

<!-- Navegación -->
<div class="page-nav">
    <div class="page-breadcrumbs">
        <a href="{{ route('dashboard') }}" class="breadcrumb-link">Dashboard</a>
        <span class="breadcrumb-sep">›</span>
        <a href="{{ route('s13.index') }}" class="breadcrumb-link">S13</a>
        <span class="breadcrumb-sep">›</span>
        <a href="{{ route('s13.importar') }}" class="breadcrumb-link">Importar</a>
        <span class="breadcrumb-sep">›</span>
        <span class="breadcrumb-current">Vista Previa</span>
    </div>
</div>

<div class="preview-container">
    <!-- Resumen estadístico -->
    <div class="stats-grid">
        <div class="stat-box stat-blue">
            <div class="stat-box-number">{{ $resumen['total'] }}</div>
            <div class="stat-box-label">Total Detectados</div>
        </div>
        <div class="stat-box stat-green">
            <div class="stat-box-number">{{ $resumen['validos'] }}</div>
            <div class="stat-box-label">Listos para Importar</div>
        </div>
        <div class="stat-box stat-yellow">
            <div class="stat-box-number">{{ $resumen['sin_match'] }}</div>
            <div class="stat-box-label">Requieren Revisión</div>
        </div>
        <div class="stat-box stat-red">
            <div class="stat-box-number">{{ $resumen['sobreposicion'] }}</div>
            <div class="stat-box-label">Con Sobreposición</div>
        </div>
    </div>

    <!-- Botón para ver texto completo extraído -->
    <div class="texto-completo-toggle">
        <button type="button" class="btn-texto-completo" id="toggleTextoCompleto">
            <span>📄</span> Ver texto completo del PDF
        </button>
    </div>

    <!-- Panel de texto completo -->
    <div class="texto-completo-panel" id="textoCompletoPanel">
        <div class="texto-completo-header">
            <span class="texto-completo-title">Texto extraído del PDF (OCR)</span>
            <button type="button" class="btn-ver-original" id="cerrarTextoCompleto">Cerrar</button>
        </div>
        <div class="texto-completo-body">
            <div class="texto-completo-content">{{ $textoCompleto ?? 'No disponible' }}</div>
        </div>
    </div>

    <!-- Aviso de edición -->
    <div class="edit-notice">
        <strong>Todos los campos son editables.</strong> Puedes corregir las fechas si el OCR las leyó incorrectamente. Haz clic en "Ver original" para comparar con lo que se leyó del PDF.
    </div>

    <!-- Leyenda -->
    <div class="legend">
        <div class="legend-item">
            <div class="legend-color legend-ok"></div>
            <span>Listo para importar</span>
        </div>
        <div class="legend-item">
            <div class="legend-color legend-revisar"></div>
            <span>Requiere seleccionar publicador</span>
        </div>
        <div class="legend-item">
            <div class="legend-color legend-invalido"></div>
            <span>Territorio no existe</span>
        </div>
        <div class="legend-item">
            <div class="legend-color legend-sobreposicion"></div>
            <span>Sobreposición (se ignorará)</span>
        </div>
    </div>

    <form action="{{ route('s13.importar.confirmar') }}" method="POST" id="confirmForm">
        @csrf

        <div class="preview-table-wrapper">
            <table class="preview-table">
                <thead>
                    <tr>
                        <th class="checkbox-cell center">Incluir</th>
                        <th class="center" style="width: 70px;">Terr.</th>
                        <th>Publicador Detectado</th>
                        <th>Publicador Asignado</th>
                        <th class="center">F. Salida</th>
                        <th class="center">F. Entrada</th>
                        <th class="center">Estado</th>
                        <th class="center" style="width: 70px;">Original</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($registros as $reg)
                    @php
                        $rowClass = '';
                        if ($reg['tiene_sobreposicion']) {
                            $rowClass = 'row-sobreposicion';
                        } elseif (!$reg['territorio_valido']) {
                            $rowClass = 'row-territorio-invalido';
                        } elseif (!$reg['publicador_match']) {
                            $rowClass = 'row-sin-match';
                        }
                        $isDisabled = $reg['tiene_sobreposicion'] || !$reg['territorio_valido'];
                    @endphp
                    <tr class="{{ $rowClass }}" data-index="{{ $reg['index'] }}">
                        <td class="checkbox-cell center">
                            <input type="checkbox"
                                   name="registros[{{ $reg['index'] }}][incluir]"
                                   value="1"
                                   {{ $reg['incluir'] ? 'checked' : '' }}
                                   {{ $isDisabled ? 'disabled' : '' }}
                                   class="registro-checkbox">
                            <input type="hidden" name="registros[{{ $reg['index'] }}][territorio_numero]" value="{{ $reg['territorio_numero'] }}">
                        </td>

                        <td class="center">
                            <span class="territorio-num">{{ $reg['territorio_numero'] }}</span>
                            @if(!$reg['territorio_valido'])
                                <span class="territorio-error">No existe</span>
                            @endif
                        </td>

                        <td>
                            <span class="publicador-raw">{{ $reg['publicador_raw'] }}</span>
                            @if($reg['publicador_confidence'] > 0)
                                @php
                                    $confClass = $reg['publicador_confidence'] >= 0.85 ? 'confidence-high' :
                                                ($reg['publicador_confidence'] >= 0.6 ? 'confidence-medium' : 'confidence-low');
                                @endphp
                                <span class="confidence-badge {{ $confClass }}">
                                    {{ round($reg['publicador_confidence'] * 100) }}%
                                </span>
                            @endif
                        </td>

                        <td>
                            <select name="registros[{{ $reg['index'] }}][publicador_id]"
                                    class="select-publicador"
                                    {{ $isDisabled ? 'disabled' : '' }}>
                                <option value="">-- Seleccionar publicador --</option>

                                @if($reg['publicador_candidatos'] && $reg['publicador_candidatos']->count() > 0)
                                    <optgroup label="Sugerencias">
                                        @foreach($reg['publicador_candidatos'] as $candidato)
                                            <option value="{{ $candidato['publicador']->id }}"
                                                {{ $reg['publicador_match'] && $reg['publicador_match']->id == $candidato['publicador']->id ? 'selected' : '' }}>
                                                {{ $candidato['publicador']->nombre }}
                                                {{ $candidato['publicador']->apellidos }}
                                                ({{ round($candidato['score'] * 100) }}%)
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endif

                                <optgroup label="Todos los publicadores">
                                    @foreach($publicadores as $pub)
                                        <option value="{{ $pub->id }}"
                                            {{ $reg['publicador_match'] && $reg['publicador_match']->id == $pub->id && (!$reg['publicador_candidatos'] || $reg['publicador_candidatos']->count() == 0) ? 'selected' : '' }}>
                                            {{ $pub->nombre }} {{ $pub->apellidos }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            </select>
                        </td>

                        <td class="center fecha-cell">
                            <input type="date"
                                   name="registros[{{ $reg['index'] }}][fecha_salida]"
                                   class="input-fecha"
                                   value="{{ $reg['fecha_salida']?->format('Y-m-d') ?? '' }}"
                                   {{ $isDisabled ? 'disabled' : '' }}>
                        </td>

                        <td class="center fecha-cell">
                            <input type="date"
                                   name="registros[{{ $reg['index'] }}][fecha_entrada]"
                                   class="input-fecha"
                                   value="{{ $reg['fecha_entrada']?->format('Y-m-d') ?? '' }}"
                                   {{ $isDisabled ? 'disabled' : '' }}>
                        </td>

                        <td class="center">
                            @if($reg['tiene_sobreposicion'])
                                <span class="badge badge-sobreposicion">Sobreposición</span>
                                @if(isset($reg['conflicto_info']['publicador_conflicto']))
                                    <span class="conflicto-tooltip">
                                        Conflicto: {{ $reg['conflicto_info']['publicador_conflicto'] }}
                                    </span>
                                @endif
                            @elseif(!$reg['territorio_valido'])
                                <span class="badge badge-invalido">Terr. Inválido</span>
                            @elseif(!$reg['publicador_match'])
                                <span class="badge badge-revisar">Revisar</span>
                            @else
                                <span class="badge badge-ok">OK</span>
                            @endif
                        </td>

                        <td class="center">
                            @if(isset($reg['raw_data']) && $reg['raw_data'])
                            <button type="button" class="btn-ver-original"
                                    onclick="mostrarOriginal({{ $reg['index'] }})"
                                    title="Ver datos originales del OCR">
                                Ver
                            </button>
                            @else
                            <span style="color: var(--text-muted); font-size: 0.7rem;">N/A</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Barra de acciones -->
        <div class="actions-bar">
            <div class="actions-info">
                <span id="selectedCount">{{ $resumen['validos'] }}</span> registros seleccionados para importar
            </div>
            <div class="actions-buttons">
                <a href="{{ route('s13.importar') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">
                    Confirmar Importación
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Modal para ver datos originales -->
<div class="modal-raw" id="modalRaw">
    <div class="modal-raw-content">
        <div class="modal-raw-header">
            <h3 class="modal-raw-title">Datos originales del OCR - Territorio <span id="modalTerrNum"></span></h3>
            <button type="button" class="modal-raw-close" onclick="cerrarModalRaw()">&times;</button>
        </div>
        <div class="modal-raw-body">
            <div class="raw-section">
                <div class="raw-section-title">Texto completo leído para este territorio</div>
                <div class="raw-content" id="rawTextoCompleto"></div>
            </div>

            <div class="raw-section">
                <div class="raw-section-title">Comparación de datos</div>
                <div class="raw-comparison">
                    <div class="raw-col">
                        <div class="raw-col-title">Nombre leído (OCR)</div>
                        <div class="raw-col-value" id="rawNombre"></div>
                    </div>
                    <div class="raw-col">
                        <div class="raw-col-title">Nombre interpretado</div>
                        <div class="raw-col-value interpreted" id="interpretedNombre"></div>
                    </div>
                </div>
            </div>

            <div class="raw-section">
                <div class="raw-section-title">Fechas</div>
                <div class="raw-comparison">
                    <div class="raw-col">
                        <div class="raw-col-title">Fecha salida (OCR)</div>
                        <div class="raw-col-value" id="rawFechaSalida"></div>
                    </div>
                    <div class="raw-col">
                        <div class="raw-col-title">Interpretada como</div>
                        <div class="raw-col-value interpreted" id="interpretedFechaSalida"></div>
                    </div>
                </div>
                <div class="raw-comparison" style="margin-top: 0.75rem;">
                    <div class="raw-col">
                        <div class="raw-col-title">Fecha entrada (OCR)</div>
                        <div class="raw-col-value" id="rawFechaEntrada"></div>
                    </div>
                    <div class="raw-col">
                        <div class="raw-col-title">Interpretada como</div>
                        <div class="raw-col-value interpreted" id="interpretedFechaEntrada"></div>
                    </div>
                </div>
            </div>

            <div class="raw-section">
                <div class="raw-section-title">Líneas originales del bloque</div>
                <div class="raw-content" id="rawLineas"></div>
            </div>
        </div>
    </div>
</div>

<script>
    // Datos raw para cada registro (preparados en el controlador)
    const datosRaw = @json($datosRawJs);

    function mostrarOriginal(index) {
        const data = datosRaw[index];
        if (!data || !data.raw_data) {
            alert('No hay datos originales disponibles');
            return;
        }

        const raw = data.raw_data;

        document.getElementById('modalTerrNum').textContent = data.territorio_numero;
        document.getElementById('rawTextoCompleto').textContent = raw.texto_completo || 'No disponible';
        document.getElementById('rawNombre').textContent = raw.nombre_detectado || 'No disponible';
        document.getElementById('interpretedNombre').textContent = data.publicador_raw || 'No disponible';
        document.getElementById('rawFechaSalida').textContent = raw.fecha_salida_raw || 'No detectada';
        document.getElementById('interpretedFechaSalida').textContent = data.fecha_salida || 'No interpretada';
        document.getElementById('rawFechaEntrada').textContent = raw.fecha_entrada_raw || 'No detectada';
        document.getElementById('interpretedFechaEntrada').textContent = data.fecha_entrada || 'No interpretada';

        // Mostrar líneas originales
        if (raw.lineas && raw.lineas.length > 0) {
            document.getElementById('rawLineas').textContent = raw.lineas.join('\n');
        } else {
            document.getElementById('rawLineas').textContent = 'No disponible';
        }

        document.getElementById('modalRaw').classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function cerrarModalRaw() {
        document.getElementById('modalRaw').classList.remove('active');
        document.body.style.overflow = '';
    }

    // Cerrar modal al hacer clic fuera
    document.getElementById('modalRaw').addEventListener('click', function(e) {
        if (e.target === this) cerrarModalRaw();
    });

    // Cerrar con Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') cerrarModalRaw();
    });

    // Toggle texto completo
    document.getElementById('toggleTextoCompleto').addEventListener('click', function() {
        document.getElementById('textoCompletoPanel').classList.toggle('show');
    });

    document.getElementById('cerrarTextoCompleto').addEventListener('click', function() {
        document.getElementById('textoCompletoPanel').classList.remove('show');
    });

    // Actualizar contador de seleccionados
    const checkboxes = document.querySelectorAll('.registro-checkbox');
    const selectedCount = document.getElementById('selectedCount');

    function updateCount() {
        const checked = document.querySelectorAll('.registro-checkbox:checked').length;
        selectedCount.textContent = checked;
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateCount);
    });
</script>
@endsection
