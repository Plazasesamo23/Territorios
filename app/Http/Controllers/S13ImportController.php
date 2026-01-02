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
     * Mostrar formulario de subida
     */
    public function index()
    {
        return view('s13.importar');
    }

    /**
     * Procesar PDF y mostrar vista previa
     */
    public function procesar(Request $request)
    {
        $request->validate([
            'pdf_file' => 'required|file|mimes:pdf|max:10240', // Max 10MB
        ], [
            'pdf_file.required' => 'Debes seleccionar un archivo PDF.',
            'pdf_file.mimes' => 'El archivo debe ser un PDF.',
            'pdf_file.max' => 'El archivo no puede superar 10MB.',
        ]);

        try {
            // Guardar archivo temporalmente
            $pdfPath = $request->file('pdf_file')->store('temp-imports');
            $fullPath = storage_path('app/' . $pdfPath);

            // Extraer texto del PDF
            $texto = $this->pdfParser->extractText($fullPath);

            if (empty(trim($texto))) {
                Storage::delete($pdfPath);
                return back()->with('error', 'No se pudo extraer texto del PDF. Asegúrate de que tenga OCR aplicado.');
            }

            // Parsear registros
            $registrosRaw = $this->importService->parseS13Text($texto);

            if (empty($registrosRaw)) {
                Storage::delete($pdfPath);
                return back()->with('error', 'No se detectaron registros en el PDF. Verifica que sea un formulario S-13 válido.');
            }

            // Procesar cada registro
            $registrosProcesados = [];

            foreach ($registrosRaw as $index => $registro) {
                // Buscar territorio
                $territorio = Territorio::where('numero', $registro['territorio_numero'])->first();

                // Matching de publicador
                $matchResult = $this->matcher->findMatch($registro['publicador_nombre_raw']);

                // Verificar sobreposición
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
                    // Datos originales del OCR
                    'raw_data' => $registro['raw_data'] ?? null,
                ];
            }

            // Guardar en sesión para la confirmación
            session(['importacion_registros' => $registrosProcesados]);
            session(['importacion_pdf_path' => $pdfPath]);
            session(['importacion_texto_completo' => $texto]); // Guardar texto completo

            // Obtener todos los publicadores para los selects
            $publicadores = Publicador::orderBy('nombre')->get();

            // Preparar datos raw para JavaScript (evitar closures en blade)
            $datosRawJs = [];
            foreach ($registrosProcesados as $reg) {
                $datosRawJs[$reg['index']] = [
                    'territorio_numero' => $reg['territorio_numero'],
                    'raw_data' => $reg['raw_data'],
                    'publicador_raw' => $reg['publicador_raw'],
                    'fecha_salida' => $reg['fecha_salida'] ? $reg['fecha_salida']->format('d/m/Y') : null,
                    'fecha_entrada' => $reg['fecha_entrada'] ? $reg['fecha_entrada']->format('d/m/Y') : null,
                ];
            }

            return view('s13.preview-importacion', [
                'registros' => $registrosProcesados,
                'publicadores' => $publicadores,
                'textoCompleto' => $texto, // Pasar texto completo a la vista
                'datosRawJs' => $datosRawJs, // Datos preparados para JS
                'resumen' => [
                    'total' => count($registrosProcesados),
                    'validos' => collect($registrosProcesados)->where('incluir', true)->count(),
                    'sobreposicion' => collect($registrosProcesados)->where('tiene_sobreposicion', true)->count(),
                    'territorio_invalido' => collect($registrosProcesados)->where('territorio_valido', false)->count(),
                    'sin_match' => collect($registrosProcesados)->whereNull('publicador_match')->count(),
                ],
            ]);
        } catch (\Exception $e) {
            return back()->with('error', 'Error al procesar el PDF: ' . $e->getMessage());
        }
    }

    /**
     * Confirmar importación
     */
    public function confirmar(Request $request)
    {
        $registrosSession = session('importacion_registros', []);
        $pdfPath = session('importacion_pdf_path');

        if (empty($registrosSession)) {
            return redirect()->route('s13.importar')
                ->with('error', 'No hay datos de importación. Por favor sube el PDF nuevamente.');
        }

        $selecciones = $request->input('registros', []);
        $importados = 0;
        $errores = [];

        foreach ($registrosSession as $index => $registroOriginal) {
            // Verificar si el usuario marcó incluir este registro
            $datos = $selecciones[$index] ?? [];
            if (!isset($datos['incluir'])) {
                continue;
            }

            // Obtener territorio (usar el número del formulario por si se editó)
            $territorioNumero = $datos['territorio_numero'] ?? $registroOriginal['territorio_numero'];
            $territorio = Territorio::where('numero', $territorioNumero)->first();
            if (!$territorio) {
                $errores[] = "Territorio {$territorioNumero} no encontrado";
                continue;
            }

            // Obtener publicador del select (ahora SIEMPRE viene del formulario)
            $publicadorId = $datos['publicador_id'] ?? null;
            if (!$publicadorId) {
                $errores[] = "Sin publicador para territorio {$territorioNumero}";
                continue;
            }

            $publicador = Publicador::find($publicadorId);
            if (!$publicador) {
                $errores[] = "Publicador ID {$publicadorId} no encontrado";
                continue;
            }

            // Obtener fechas del formulario (editadas por el usuario)
            $fechaSalida = null;
            $fechaEntrada = null;

            if (!empty($datos['fecha_salida'])) {
                try {
                    $fechaSalida = Carbon::parse($datos['fecha_salida']);
                } catch (\Exception $e) {
                    $errores[] = "Fecha salida inválida para territorio {$territorioNumero}";
                    continue;
                }
            }

            if (!empty($datos['fecha_entrada'])) {
                try {
                    $fechaEntrada = Carbon::parse($datos['fecha_entrada']);
                } catch (\Exception $e) {
                    $errores[] = "Fecha entrada inválida para territorio {$territorioNumero}";
                    continue;
                }
            }

            // Verificar que al menos hay fecha de salida
            if (!$fechaSalida) {
                $errores[] = "Territorio {$territorioNumero}: fecha de salida requerida";
                continue;
            }

            // Verificar sobreposición con las fechas editadas
            $sobreposicion = $this->importService->verificarSobreposicion(
                $territorio->id,
                $fechaSalida,
                $fechaEntrada
            );

            if ($sobreposicion['tiene_sobreposicion']) {
                $errores[] = "Territorio {$territorio->numero}: sobreposición con registro existente";
                continue;
            }

            // Crear registro
            Registro::create([
                'territorio_id' => $territorio->id,
                'publicador_id' => $publicador->id,
                'fecha_salida' => $fechaSalida,
                'fecha_entrada' => $fechaEntrada,
                'notas' => 'Importado desde S-13',
            ]);

            $importados++;
        }

        // Limpiar sesión
        session()->forget(['importacion_registros', 'importacion_pdf_path', 'importacion_texto_completo']);

        // Eliminar archivo temporal
        if ($pdfPath && Storage::exists($pdfPath)) {
            Storage::delete($pdfPath);
        }

        $mensaje = "Se importaron {$importados} registros exitosamente.";
        if (!empty($errores)) {
            $mensaje .= " Hubo " . count($errores) . " errores.";
            session()->flash('errores_importacion', $errores);
        }

        return redirect()->route('s13.index')
            ->with('success', $mensaje);
    }
}
