@extends('layouts.app')

@section('title', 'Importar S-13')

@section('content')
<style>
    .import-page { max-width: 900px; margin: 0 auto; padding: 0 1rem; }
    .import-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-top: 1.5rem; }
    .import-card { background: var(--bg-card, #fff); border-radius: 12px; padding: 1.5rem; border: 1px solid var(--border-color, #e5e7eb); box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
    .import-card-title { font-size: 1.1rem; font-weight: 600; color: var(--text-primary, #1f2937); margin-bottom: 1rem; padding-bottom: 0.75rem; border-bottom: 1px solid var(--border-color, #e5e7eb); }

    .upload-zone { display: flex; flex-direction: column; align-items: center; justify-content: center; border: 2px dashed var(--border-color, #d1d5db); border-radius: 10px; padding: 2rem 1.5rem; text-align: center; cursor: pointer; transition: all 0.2s ease; background: var(--bg-secondary, #f9fafb); }
    .upload-zone:hover, .upload-zone.dragover { border-color: #5c7fb8; background: rgba(99, 102, 241, 0.05); }
    .upload-zone input[type="file"] { display: none; }
    .upload-icon { font-size: 2.5rem; margin-bottom: 0.75rem; opacity: 0.7; }
    .upload-text { font-size: 1rem; font-weight: 500; color: var(--text-primary, #374151); margin-bottom: 0.25rem; }
    .upload-hint { font-size: 0.85rem; color: var(--text-muted, #6b7280); margin-bottom: 1rem; }
    .upload-btn-fake { display: inline-block; padding: 0.6rem 1.25rem; background: #5c7fb8; color: white; border-radius: 6px; font-size: 0.9rem; font-weight: 500; }

    .file-selected { display: none; margin-top: 1rem; padding: 0.75rem 1rem; background: rgba(34, 197, 94, 0.1); border: 1px solid rgba(34, 197, 94, 0.3); border-radius: 6px; color: #3d5a8a; font-size: 0.9rem; text-align: left; }
    .file-selected.show { display: block; }

    .ocr-progress { display: none; margin-top: 1rem; padding: 1rem; background: var(--bg-secondary, #f3f4f6); border-radius: 8px; }
    .ocr-progress.show { display: block; }
    .ocr-status { font-size: 0.9rem; color: var(--text-secondary, #4b5563); margin-bottom: 0.5rem; }
    .ocr-bar-container { background: #e5e7eb; border-radius: 4px; height: 8px; overflow: hidden; }
    .ocr-bar { background: linear-gradient(90deg, #5c7fb8, #4a6da7); height: 100%; width: 0%; transition: width 0.3s; }
    .ocr-percent { font-size: 0.8rem; color: var(--text-muted, #6b7280); margin-top: 0.25rem; text-align: right; }

    .form-buttons { display: flex; gap: 0.75rem; margin-top: 1.5rem; justify-content: flex-end; }
    .form-buttons .btn { padding: 0.65rem 1.25rem; font-size: 0.9rem; border-radius: 6px; font-weight: 500; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; min-height: 44px; cursor: pointer; border: none; }
    .btn-cancel { background: var(--bg-secondary, #f3f4f6); color: var(--text-primary, #374151); border: 1px solid var(--border-color, #d1d5db); }
    .btn-submit { background: #5c7fb8; color: white; }
    .btn-submit:disabled { opacity: 0.5; cursor: not-allowed; }

    .instructions-card { background: linear-gradient(135deg, rgba(99, 102, 241, 0.03) 0%, rgba(139, 92, 246, 0.03) 100%); border-color: rgba(99, 102, 241, 0.15); }
    .instructions-list { list-style: none; padding: 0; margin: 0; }
    .instructions-list li { padding: 0.6rem 0; padding-left: 1.5rem; position: relative; color: var(--text-secondary, #4b5563); font-size: 0.9rem; border-bottom: 1px solid var(--border-color, #e5e7eb); }
    .instructions-list li:last-child { border-bottom: none; }
    .instructions-list li::before { content: ''; position: absolute; left: 0; top: 0.95rem; width: 6px; height: 6px; background: #5c7fb8; border-radius: 50%; }

    .warning-box { margin-top: 1.25rem; padding: 0.875rem; background: rgba(245, 158, 11, 0.08); border: 1px solid rgba(245, 158, 11, 0.25); border-radius: 8px; }
    .warning-title { font-weight: 600; color: #b45309; font-size: 0.85rem; margin-bottom: 0.35rem; }
    .warning-text { font-size: 0.8rem; color: var(--text-secondary, #4b5563); margin: 0; line-height: 1.4; }

    .info-box { margin-top: 1rem; padding: 0.75rem; background: rgba(59, 130, 246, 0.08); border: 1px solid rgba(59, 130, 246, 0.25); border-radius: 8px; font-size: 0.8rem; color: var(--text-secondary, #4b5563); }

    .alert-error { background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.25); border-radius: 8px; padding: 0.875rem 1rem; color: #343a40; font-size: 0.9rem; margin-bottom: 1rem; }

    @media (max-width: 768px) { .import-grid { grid-template-columns: 1fr; } }

    .pdf-preview { display: none; margin-top: 1rem; }
    .pdf-preview.show { display: block; }
    .pdf-preview canvas { max-width: 100%; border-radius: 8px; border: 1px solid var(--border-color, #e5e7eb); }
</style>

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
    @if(session('texto_debug'))
        <div style="margin-bottom:1rem; padding:1rem; background:#f8f9fa; border:1px solid #dee2e6; border-radius:8px; max-height:300px; overflow-y:auto;">
            <strong>Texto extraido (debug):</strong>
            <pre style="font-size:0.75rem; white-space:pre-wrap; margin-top:0.5rem;">{{ session('texto_debug') }}</pre>
        </div>
    @endif

    @if(session('error'))
        <div class="alert-error">{{ session('error') }}</div>
    @endif

    <div class="import-grid">
        <div class="import-card">
            <div class="import-card-title">Importar registros S-13</div>

            <form action="{{ route('s13.importar.procesar-ocr') }}" method="POST" id="mainForm">
                @csrf
                <input type="hidden" name="texto_ocr" id="textoOcrInput">

                <label class="upload-zone" id="uploadZone">
                    <input type="file" id="pdfFile" accept=".pdf,image/*">
                    <div class="upload-icon">📄</div>
                    <div class="upload-text">Arrastra tu PDF aqui</div>
                    <div class="upload-hint">PDF normal o escaneado - detectamos automaticamente</div>
                    <span class="upload-btn-fake">Seleccionar archivo</span>
                </label>

                <div class="file-selected" id="fileSelected">
                    <strong>Archivo:</strong> <span id="fileName"></span>
                </div>

                <div class="pdf-preview" id="pdfPreview">
                    <canvas id="pdfCanvas"></canvas>
                </div>

                <div class="ocr-progress" id="ocrProgress">
                    <div class="ocr-status" id="ocrStatus">Analizando PDF...</div>
                    <div class="ocr-bar-container">
                        <div class="ocr-bar" id="ocrBar"></div>
                    </div>
                    <div class="ocr-percent" id="ocrPercent">0%</div>
                </div>

                <div class="form-buttons">
                    <a href="{{ route('s13.index') }}" class="btn btn-cancel">Cancelar</a>
                    <button type="submit" class="btn btn-submit" id="submitBtn" disabled>Procesar</button>
                </div>
            </form>
        </div>

        <div class="import-card instructions-card">
            <div class="import-card-title">Instrucciones</div>

            <ul class="instructions-list">
                <li>Sube cualquier PDF del formulario S-13</li>
                <li>Si el PDF es escaneado, se aplicara OCR automaticamente</li>
                <li>Se detectan los registros y podras revisarlos</li>
                <li>La primera vez tarda mas (descarga idioma espanol)</li>
            </ul>

            <div class="info-box">
                <strong>OCR en tu navegador:</strong> El reconocimiento de texto se hace en tu dispositivo, no se envia a servidores externos.
            </div>

            <div class="warning-box">
                <div class="warning-title">Regla de prioridad</div>
                <p class="warning-text">Si un registro se solapa con uno existente, se ignorara el importado.</p>
            </div>
        </div>
    </div>
</div>

<!-- PDF.js para leer PDFs -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<!-- Tesseract.js para OCR -->
<script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>

<script>
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

    const pdfFile = document.getElementById('pdfFile');
    const uploadZone = document.getElementById('uploadZone');
    const fileSelected = document.getElementById('fileSelected');
    const fileName = document.getElementById('fileName');
    const ocrProgress = document.getElementById('ocrProgress');
    const ocrStatus = document.getElementById('ocrStatus');
    const ocrBar = document.getElementById('ocrBar');
    const ocrPercent = document.getElementById('ocrPercent');
    const submitBtn = document.getElementById('submitBtn');
    const textoOcrInput = document.getElementById('textoOcrInput');
    const pdfPreview = document.getElementById('pdfPreview');
    const pdfCanvas = document.getElementById('pdfCanvas');

    // Drag and drop
    ['dragenter', 'dragover'].forEach(evt => {
        uploadZone.addEventListener(evt, e => { e.preventDefault(); uploadZone.classList.add('dragover'); });
    });
    ['dragleave', 'drop'].forEach(evt => {
        uploadZone.addEventListener(evt, e => { e.preventDefault(); uploadZone.classList.remove('dragover'); });
    });
    uploadZone.addEventListener('drop', e => {
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            pdfFile.files = files;
            pdfFile.dispatchEvent(new Event('change'));
        }
    });

    pdfFile.addEventListener('change', async () => {
        if (pdfFile.files.length === 0) return;

        const file = pdfFile.files[0];
        fileName.textContent = file.name;
        fileSelected.classList.add('show');
        submitBtn.disabled = true;
        ocrProgress.classList.add('show');
        ocrBar.style.width = '0%';
        ocrBar.style.background = 'linear-gradient(90deg, #5c7fb8, #4a6da7)';

        try {
            // Si es imagen directamente
            if (file.type.startsWith('image/')) {
                ocrStatus.textContent = 'Procesando imagen con OCR...';
                await processImageOCR(file);
                return;
            }

            // Es PDF - primero intentar extraer texto
            ocrStatus.textContent = 'Analizando PDF...';
            ocrBar.style.width = '10%';

            const arrayBuffer = await file.arrayBuffer();
            const pdf = await pdfjsLib.getDocument({ data: arrayBuffer }).promise;

            let textoExtraido = '';
            const totalPages = pdf.numPages;

            // Intentar extraer texto de todas las paginas
            for (let i = 1; i <= totalPages; i++) {
                ocrStatus.textContent = `Extrayendo texto (pagina ${i}/${totalPages})...`;
                ocrBar.style.width = (10 + (i / totalPages) * 30) + '%';

                const page = await pdf.getPage(i);
                const textContent = await page.getTextContent();
                const pageText = textContent.items.map(item => item.str).join(' ');
                textoExtraido += pageText + '\n';
            }

            // Verificar si hay texto util
            const textoLimpio = textoExtraido.replace(/\s+/g, ' ').trim();

            if (textoLimpio.length > 50) {
                // PDF tiene texto extraible
                ocrStatus.textContent = 'PDF con texto detectado';
                ocrBar.style.width = '100%';
                textoOcrInput.value = textoExtraido;
                ocrProgress.classList.remove('show');
                fileSelected.innerHTML = '<strong>PDF con texto.</strong> Listo para procesar.';
                submitBtn.disabled = false;
            } else {
                // PDF escaneado - necesita OCR
                ocrStatus.textContent = 'PDF escaneado detectado. Aplicando OCR...';
                ocrBar.style.width = '40%';

                // Renderizar todas las paginas y hacer OCR
                let textoOcr = '';
                for (let i = 1; i <= totalPages; i++) {
                    ocrStatus.textContent = `OCR pagina ${i}/${totalPages}...`;

                    const page = await pdf.getPage(i);
                    const viewport = page.getViewport({ scale: 2.0 }); // Mayor escala = mejor OCR

                    pdfCanvas.width = viewport.width;
                    pdfCanvas.height = viewport.height;
                    const ctx = pdfCanvas.getContext('2d');

                    await page.render({ canvasContext: ctx, viewport: viewport }).promise;

                    // Mostrar preview de la primera pagina
                    if (i === 1) {
                        pdfPreview.classList.add('show');
                    }

                    // OCR de esta pagina
                    const imgData = pdfCanvas.toDataURL('image/png');
                    const result = await Tesseract.recognize(imgData, 'spa', {
                        logger: m => {
                            if (m.status === 'recognizing text') {
                                const baseProgress = 40 + ((i - 1) / totalPages) * 55;
                                const pageProgress = (m.progress / totalPages) * 55;
                                const totalProgress = baseProgress + pageProgress;
                                ocrBar.style.width = totalProgress + '%';
                                ocrPercent.textContent = Math.round(totalProgress) + '%';
                            }
                        }
                    });

                    textoOcr += result.data.text + '\n\n';
                }

                textoOcrInput.value = textoOcr;
                ocrProgress.classList.remove('show');
                fileSelected.innerHTML = '<strong>OCR completado.</strong> Listo para procesar.';
                fileSelected.classList.add('show');
                submitBtn.disabled = false;

                console.log('Texto OCR:', textoOcr);
            }

        } catch (error) {
            console.error('Error:', error);
            ocrStatus.textContent = 'Error: ' + error.message;
            ocrBar.style.background = '#ef4444';
        }
    });

    async function processImageOCR(file) {
        ocrBar.style.width = '10%';

        try {
            const result = await Tesseract.recognize(file, 'spa', {
                logger: m => {
                    if (m.status === 'recognizing text') {
                        const pct = 10 + Math.round(m.progress * 90);
                        ocrBar.style.width = pct + '%';
                        ocrPercent.textContent = pct + '%';
                        ocrStatus.textContent = 'Reconociendo texto...';
                    } else if (m.status === 'loading language traineddata') {
                        ocrStatus.textContent = 'Cargando idioma espanol...';
                        ocrBar.style.width = '15%';
                    }
                }
            });

            textoOcrInput.value = result.data.text;
            ocrProgress.classList.remove('show');
            fileSelected.innerHTML = '<strong>OCR completado.</strong> Listo para procesar.';
            submitBtn.disabled = false;

        } catch (error) {
            console.error('Error OCR:', error);
            ocrStatus.textContent = 'Error: ' + error.message;
            ocrBar.style.background = '#ef4444';
        }
    }
</script>
@endsection
