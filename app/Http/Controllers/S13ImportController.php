<?php

namespace App\Http\Controllers;

use App\Services\PdfParserService;
use App\Services\PublicadorMatcherService;
use App\Services\S13ImportService;
use App\Models\Territorio;
use App\Models\Publicador;
use App\Models\Registro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class S13ImportController extends Controller
{
    protected PdfParserService $pdfParser;
    protected PublicadorMatcherService $matcher;
    protected S13ImportService $importService;

    public function __construct(
        PdfParserService $pdfParser,
        PublicadorMatcherService $matcher,
        S13ImportService $importService
    ) {
        $this->pdfParser = $pdfParser;
        $this->matcher = $matcher;
        $this->importService = $importService;
    }

    /**
     * Mostrar formulario de entrada rapida
     */
    public function index()
    {
        return view('s13.importar');
    }

    /**
     * Guardar registros desde entrada rapida
     */
    public function guardarRapido(Request $request)
    {
        $registros = $request->input('registros', []);

        if (empty($registros)) {
            return back()->with('error', 'No se recibieron registros para guardar.');
        }

        $importados = 0;
        $errores = [];

        foreach ($registros as $index => $datos) {
            // Validar campos requeridos
            if (empty($datos['territorio']) || empty($datos['publicador_id']) || empty($datos['fecha_salida'])) {
                continue; // Saltar filas incompletas
            }

            // Buscar territorio (puede ser número o código como CEN-1)
            $territorioInput = strtoupper(trim($datos['territorio']));

            // Extraer número si es código tipo CEN-1, ROQ-95, etc.
            if (preg_match('/^(CEN|ROQ|T\.?ROM)-?(\d+)$/i', $territorioInput, $matches)) {
                $numeroTerritorio = $matches[2];
            } else {
                $numeroTerritorio = $territorioInput;
            }

            $territorio = Territorio::where('numero', $numeroTerritorio)->first();
            if (!$territorio) {
                $errores[] = "Territorio '{$territorioInput}' no encontrado";
                continue;
            }

            // Verificar publicador
            $publicador = Publicador::find($datos['publicador_id']);
            if (!$publicador) {
                $errores[] = "Publicador no encontrado para territorio {$territorioInput}";
                continue;
            }

            // Parsear fechas
            try {
                $fechaSalida = Carbon::parse($datos['fecha_salida']);
            } catch (\Exception $e) {
                $errores[] = "Fecha salida invalida para territorio {$territorioInput}";
                continue;
            }

            $fechaEntrada = null;
            if (!empty($datos['fecha_entrada'])) {
                try {
                    $fechaEntrada = Carbon::parse($datos['fecha_entrada']);
                } catch (\Exception $e) {
                    $errores[] = "Fecha entrada invalida para territorio {$territorioInput}";
                    continue;
                }
            }

            // Verificar sobreposicion
            $sobreposicion = $this->importService->verificarSobreposicion(
                $territorio->id,
                $fechaSalida,
                $fechaEntrada
            );

            if ($sobreposicion['tiene_sobreposicion']) {
                $errores[] = "Territorio {$territorioInput}: ya existe un registro en esas fechas ({$sobreposicion['publicador_conflicto']})";
                continue;
            }

            // Crear registro
            Registro::create([
                'territorio_id' => $territorio->id,
                'publicador_id' => $publicador->id,
                'fecha_salida' => $fechaSalida,
                'fecha_entrada' => $fechaEntrada,
                'notas' => 'Importado desde S-13 (entrada rapida)',
            ]);

            $importados++;
        }

        // Preparar mensaje
        if ($importados > 0) {
            $mensaje = "Se importaron {$importados} registro" . ($importados > 1 ? 's' : '') . " correctamente.";

            if (!empty($errores)) {
                session()->flash('errores_importacion', $errores);
                $mensaje .= " Hubo " . count($errores) . " error" . (count($errores) > 1 ? 'es' : '') . ".";
            }

            return redirect()->route('s13.index')->with('success', $mensaje);
        } else {
            $mensaje = "No se pudo importar ningun registro.";
            if (!empty($errores)) {
                $mensaje .= " Errores: " . implode(', ', array_slice($errores, 0, 3));
                if (count($errores) > 3) {
                    $mensaje .= "... y " . (count($errores) - 3) . " mas.";
                }
            }
            return back()->with('error', $mensaje);
        }
    }

    /**
     * Procesar PDF y mostrar vista previa
     */
    public function procesar(Request $request)
    {
        $request->validate([
            'pdf_file' => 'required|file|mimes:pdf|max:10240',
        ], [
            'pdf_file.required' => 'Debes seleccionar un archivo PDF.',
            'pdf_file.mimes' => 'El archivo debe ser un PDF.',
            'pdf_file.max' => 'El archivo no puede superar 10MB.',
        ]);

        try {
            $pdfPath = $request->file('pdf_file')->store('temp-imports');
            $fullPath = storage_path('app/' . $pdfPath);

            $texto = $this->pdfParser->extractText($fullPath);

            if (empty(trim($texto))) {
                Storage::delete($pdfPath);
                return back()->with('error', 'No se pudo extraer texto del PDF.');
            }

            return $this->procesarTexto($texto, $pdfPath);

        } catch (\Exception $e) {
            return back()->with('error', 'Error al procesar el PDF: ' . $e->getMessage());
        }
    }

    /**
     * Procesar texto OCR desde imagen (Tesseract.js)
     */
    public function procesarOcr(Request $request)
    {
        $request->validate([
            'texto_ocr' => 'required|string|min:10',
        ]);

        try {
            $texto = $request->input('texto_ocr');
            return $this->procesarTexto($texto, null);
        } catch (\Exception $e) {
            return back()->with('error', 'Error al procesar el texto OCR: ' . $e->getMessage());
        }
    }

    /**
     * Procesar texto (comun para PDF y OCR)
     */
    protected function procesarTexto(string $texto, ?string $pdfPath)
    {
        $registrosRaw = $this->importService->parseS13Text($texto);

        if (empty($registrosRaw)) {
            if ($pdfPath) {
                Storage::delete($pdfPath);
            }
            $textoDebug = mb_substr($texto, 0, 3000);
            return back()
                ->with('error', 'No se detectaron registros.')
                ->with('texto_debug', $textoDebug);
        }

        $registrosProcesados = [];

        foreach ($registrosRaw as $index => $registro) {
            $territorio = Territorio::where('numero', $registro['territorio_numero'])->first();
            $matchResult = $this->matcher->findMatch($registro['publicador_nombre_raw']);

            $sobreposicion = ['tiene_sobreposicion' => false];
            if ($territorio && $registro['fecha_salida']) {
                $sobreposicion = $this->importService->verificarSobreposicion(
                    $territorio->id,
                    $registro['fecha_salida'],
                    $registro['fecha_entrada']
                );
            }

            $registrosProcesados[] = [
                'index' => $index,
                'territorio_numero' => $registro['territorio_numero'],
                'territorio' => $territorio,
                'territorio_valido' => $territorio !== null,
                'publicador_raw' => $registro['publicador_nombre_raw'],
                'publicador_match' => $matchResult['publicador'],
                'publicador_confidence' => $matchResult['confidence'],
                'publicador_candidatos' => $matchResult['candidates'],
                'fecha_salida' => $registro['fecha_salida'],
                'fecha_entrada' => $registro['fecha_entrada'],
                'tiene_sobreposicion' => $sobreposicion['tiene_sobreposicion'],
                'conflicto_info' => $sobreposicion,
                'incluir' => !$sobreposicion['tiene_sobreposicion'] && $territorio !== null,
            ];
        }

        session(['importacion_registros' => $registrosProcesados]);
        session(['importacion_pdf_path' => $pdfPath]);

        $publicadores = Publicador::orderBy('nombre')->get();

        return view('s13.preview-importacion', [
            'registros' => $registrosProcesados,
            'publicadores' => $publicadores,
            'resumen' => [
                'total' => count($registrosProcesados),
                'validos' => collect($registrosProcesados)->where('incluir', true)->count(),
                'sobreposicion' => collect($registrosProcesados)->where('tiene_sobreposicion', true)->count(),
                'territorio_invalido' => collect($registrosProcesados)->where('territorio_valido', false)->count(),
                'sin_match' => collect($registrosProcesados)->whereNull('publicador_match')->count(),
            ],
        ]);
    }

    /**
     * Confirmar importacion
     */
    public function confirmar(Request $request)
    {
        $registrosSession = session('importacion_registros', []);
        $pdfPath = session('importacion_pdf_path');

        if (empty($registrosSession)) {
            return redirect()->route('s13.importar')
                ->with('error', 'No hay datos de importacion.');
        }

        $selecciones = $request->input('registros', []);
        $importados = 0;
        $errores = [];

        foreach ($registrosSession as $index => $registroOriginal) {
            $datos = $selecciones[$index] ?? [];
            if (!isset($datos['incluir'])) {
                continue;
            }

            $territorioNumero = $datos['territorio_numero'] ?? $registroOriginal['territorio_numero'];
            $territorio = Territorio::where('numero', $territorioNumero)->first();
            if (!$territorio) {
                $errores[] = "Territorio {$territorioNumero} no encontrado";
                continue;
            }

            $publicadorId = $datos['publicador_id'] ?? null;
            if (!$publicadorId) {
                $errores[] = "Sin publicador para territorio {$territorioNumero}";
                continue;
            }

            $publicador = Publicador::find($publicadorId);
            if (!$publicador) {
                continue;
            }

            $fechaSalida = null;
            $fechaEntrada = null;

            if (!empty($datos['fecha_salida'])) {
                try {
                    $fechaSalida = Carbon::parse($datos['fecha_salida']);
                } catch (\Exception $e) {
                    continue;
                }
            }

            if (!empty($datos['fecha_entrada'])) {
                try {
                    $fechaEntrada = Carbon::parse($datos['fecha_entrada']);
                } catch (\Exception $e) {
                    continue;
                }
            }

            if (!$fechaSalida) {
                continue;
            }

            $sobreposicion = $this->importService->verificarSobreposicion(
                $territorio->id,
                $fechaSalida,
                $fechaEntrada
            );

            if ($sobreposicion['tiene_sobreposicion']) {
                $errores[] = "Territorio {$territorio->numero}: sobreposicion";
                continue;
            }

            Registro::create([
                'territorio_id' => $territorio->id,
                'publicador_id' => $publicador->id,
                'fecha_salida' => $fechaSalida,
                'fecha_entrada' => $fechaEntrada,
                'notas' => 'Importado desde S-13',
            ]);

            $importados++;
        }

        session()->forget(['importacion_registros', 'importacion_pdf_path']);

        if ($pdfPath && Storage::exists($pdfPath)) {
            Storage::delete($pdfPath);
        }

        $mensaje = "Se importaron {$importados} registros.";
        if (!empty($errores)) {
            $mensaje .= " Hubo " . count($errores) . " errores.";
        }

        return redirect()->route('s13.index')->with('success', $mensaje);
    }
}
