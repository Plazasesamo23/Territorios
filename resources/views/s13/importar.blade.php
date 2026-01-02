@extends('layouts.app')

@section('title', 'Importar S-13')

@section('content')
<style>
    .import-page {
        max-width: 900px;
        margin: 0 auto;
        padding: 0 1rem;
    }

    .import-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
        margin-top: 1.5rem;
    }

    .import-card {
        background: var(--bg-card, #fff);
        border-radius: 12px;
        padding: 1.5rem;
        border: 1px solid var(--border-color, #e5e7eb);
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }

    .import-card-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--text-primary, #1f2937);
        margin-bottom: 1rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid var(--border-color, #e5e7eb);
    }

    .upload-zone {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        border: 2px dashed var(--border-color, #d1d5db);
        border-radius: 10px;
        padding: 2rem 1.5rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s ease;
        background: var(--bg-secondary, #f9fafb);
    }

    .upload-zone:hover,
    .upload-zone.dragover {
        border-color: #6366f1;
        background: rgba(99, 102, 241, 0.05);
    }

    .upload-zone input[type="file"] {
        display: none;
    }

    .upload-icon {
        font-size: 2.5rem;
        margin-bottom: 0.75rem;
        opacity: 0.7;
    }

    .upload-text {
        font-size: 1rem;
        font-weight: 500;
        color: var(--text-primary, #374151);
        margin-bottom: 0.25rem;
    }

    .upload-hint {
        font-size: 0.85rem;
        color: var(--text-muted, #6b7280);
        margin-bottom: 1rem;
    }

    .upload-btn-fake {
        display: inline-block;
        padding: 0.6rem 1.25rem;
        background: #6366f1;
        color: white;
        border-radius: 6px;
        font-size: 0.9rem;
        font-weight: 500;
    }

    .file-selected {
        display: none;
        margin-top: 1rem;
        padding: 0.75rem 1rem;
        background: rgba(34, 197, 94, 0.1);
        border: 1px solid rgba(34, 197, 94, 0.3);
        border-radius: 6px;
        color: #16a34a;
        font-size: 0.9rem;
        text-align: left;
    }

    .file-selected.show {
        display: block;
    }

    .form-buttons {
        display: flex;
        gap: 0.75rem;
        margin-top: 1.5rem;
        justify-content: flex-end;
    }

    .form-buttons .btn {
        padding: 0.65rem 1.25rem;
        font-size: 0.9rem;
        border-radius: 6px;
        font-weight: 500;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 44px;
        cursor: pointer;
        border: none;
    }

    .btn-cancel {
        background: var(--bg-secondary, #f3f4f6);
        color: var(--text-primary, #374151);
        border: 1px solid var(--border-color, #d1d5db);
    }

    .btn-cancel:hover {
        background: var(--bg-hover, #e5e7eb);
    }

    .btn-submit {
        background: #6366f1;
        color: white;
    }

    .btn-submit:hover:not(:disabled) {
        background: #4f46e5;
    }

    .btn-submit:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    /* Card de instrucciones */
    .instructions-card {
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.03) 0%, rgba(139, 92, 246, 0.03) 100%);
        border-color: rgba(99, 102, 241, 0.15);
    }

    .instructions-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .instructions-list li {
        padding: 0.6rem 0;
        padding-left: 1.5rem;
        position: relative;
        color: var(--text-secondary, #4b5563);
        font-size: 0.9rem;
        border-bottom: 1px solid var(--border-color, #e5e7eb);
    }

    .instructions-list li:last-child {
        border-bottom: none;
    }

    .instructions-list li::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0.95rem;
        width: 6px;
        height: 6px;
        background: #6366f1;
        border-radius: 50%;
    }

    .warning-box {
        margin-top: 1.25rem;
        padding: 0.875rem;
        background: rgba(245, 158, 11, 0.08);
        border: 1px solid rgba(245, 158, 11, 0.25);
        border-radius: 8px;
    }

    .warning-title {
        font-weight: 600;
        color: #b45309;
        font-size: 0.85rem;
        margin-bottom: 0.35rem;
    }

    .warning-text {
        font-size: 0.8rem;
        color: var(--text-secondary, #4b5563);
        margin: 0;
        line-height: 1.4;
    }

    .alert-error {
        background: rgba(239, 68, 68, 0.08);
        border: 1px solid rgba(239, 68, 68, 0.25);
        border-radius: 8px;
        padding: 0.875rem 1rem;
        color: #dc2626;
        font-size: 0.9rem;
        margin-bottom: 1rem;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .import-page {
            padding: 0 0.75rem;
        }

        .import-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .import-card {
            padding: 1.25rem;
        }

        .upload-zone {
            padding: 1.5rem 1rem;
        }

        .upload-icon {
            font-size: 2rem;
        }

        .upload-text {
            font-size: 0.95rem;
        }

        .form-buttons {
            flex-direction: column;
        }

        .form-buttons .btn {
            width: 100%;
        }
    }

    @media (max-width: 480px) {
        .import-card {
            padding: 1rem;
            border-radius: 10px;
        }

        .import-card-title {
            font-size: 1rem;
        }

        .upload-zone {
            padding: 1.25rem 0.75rem;
        }

        .upload-icon {
            font-size: 1.75rem;
        }

        .upload-text {
            font-size: 0.9rem;
        }

        .upload-hint {
            font-size: 0.8rem;
        }

        .upload-btn-fake {
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
        }

        .instructions-list li {
            font-size: 0.85rem;
            padding-left: 1.25rem;
        }

        .warning-box {
            padding: 0.75rem;
        }

        .warning-title {
            font-size: 0.8rem;
        }

        .warning-text {
            font-size: 0.75rem;
        }
    }

    /* Touch devices */
    @media (hover: none) {
        .upload-zone:hover {
            border-color: var(--border-color, #d1d5db);
            background: var(--bg-secondary, #f9fafb);
        }

        .upload-zone.dragover {
            border-color: #6366f1;
            background: rgba(99, 102, 241, 0.05);
        }
    }
</style>

<!-- Navegacion -->
<div class="page-nav">
    <div class="page-breadcrumbs">
        <a href="{{ route('dashboard') }}" class="breadcrumb-link">Dashboard</a>
        <span class="breadcrumb-sep">›</span>
        <a href="{{ route('s13.index') }}" class="breadcrumb-link">S13</a>
        <span class="breadcrumb-sep">›</span>
        <span class="breadcrumb-current">Importar</span>
    </div>
</div>

<div class="import-page">
    @if(session('error'))
        <div class="alert-error">
            {{ session('error') }}
        </div>
    @endif

    <div class="import-grid">
        <!-- Formulario de subida -->
        <div class="import-card">
            <div class="import-card-title">Subir PDF S-13</div>

            <form action="{{ route('s13.importar.procesar') }}" method="POST" enctype="multipart/form-data" id="importForm">
                @csrf

                <label class="upload-zone" id="uploadZone">
                    <input type="file" name="pdf_file" id="pdfFile" accept=".pdf" required>
                    <div class="upload-icon">📄</div>
                    <div class="upload-text">Arrastra tu archivo PDF aqui</div>
                    <div class="upload-hint">o haz clic para seleccionar</div>
                    <span class="upload-btn-fake">Seleccionar archivo</span>

                    <div class="file-selected" id="fileSelected">
                        <strong>Archivo:</strong> <span id="fileName"></span>
                    </div>
                </label>

                @error('pdf_file')
                    <div class="alert-error" style="margin-top: 0.75rem;">
                        {{ $message }}
                    </div>
                @enderror

                <div class="form-buttons">
                    <a href="{{ route('s13.index') }}" class="btn btn-cancel">Cancelar</a>
                    <button type="submit" class="btn btn-submit" id="submitBtn" disabled>
                        Procesar PDF
                    </button>
                </div>
            </form>
        </div>

        <!-- Instrucciones -->
        <div class="import-card instructions-card">
            <div class="import-card-title">Instrucciones</div>

            <ul class="instructions-list">
                <li>El PDF debe ser un formulario S-13 oficial</li>
                <li>Asegurate de que el PDF tenga <strong>OCR aplicado</strong> (texto seleccionable)</li>
                <li>Se detectaran automaticamente los registros del documento</li>
                <li>Podras revisar y corregir la informacion antes de importar</li>
                <li>Los publicadores se buscaran entre los existentes en el sistema</li>
            </ul>

            <div class="warning-box">
                <div class="warning-title">Regla de prioridad</div>
                <p class="warning-text">Si un registro importado se solapa en fechas con uno ya existente para el mismo territorio, se <strong>ignorara el importado</strong>. Los datos de tu base de datos siempre tienen prioridad.</p>
            </div>
        </div>
    </div>
</div>

<script>
    const uploadZone = document.getElementById('uploadZone');
    const pdfFile = document.getElementById('pdfFile');
    const fileSelected = document.getElementById('fileSelected');
    const fileName = document.getElementById('fileName');
    const submitBtn = document.getElementById('submitBtn');

    // Drag and drop
    ['dragenter', 'dragover'].forEach(event => {
        uploadZone.addEventListener(event, (e) => {
            e.preventDefault();
            uploadZone.classList.add('dragover');
        });
    });

    ['dragleave', 'drop'].forEach(event => {
        uploadZone.addEventListener(event, (e) => {
            e.preventDefault();
            uploadZone.classList.remove('dragover');
        });
    });

    uploadZone.addEventListener('drop', (e) => {
        const files = e.dataTransfer.files;
        if (files.length > 0 && files[0].type === 'application/pdf') {
            pdfFile.files = files;
            updateFileDisplay(files[0]);
        }
    });

    pdfFile.addEventListener('change', () => {
        if (pdfFile.files.length > 0) {
            updateFileDisplay(pdfFile.files[0]);
        }
    });

    function updateFileDisplay(file) {
        fileName.textContent = file.name;
        fileSelected.classList.add('show');
        submitBtn.disabled = false;
    }
</script>
@endsection
