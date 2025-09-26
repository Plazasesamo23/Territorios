<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Territorio;
use App\Models\Registro;
use App\Models\Publicador;
use Dompdf\Dompdf;
use Dompdf\Options;
use Carbon\Carbon;

class S13Controller extends Controller
{
    /**
     * Mostrar la página de reportes S13
     */
    public function index()
    {
        // Estadísticas para la página principal
        $estadisticas = [
            'total_territorios' => Territorio::count(),
            'territorios_libres' => 0, // Cálculo rápido para evitar errores
            'territorios_asignados' => Registro::whereNull('fecha_entrada')->count(),
            'total_registros' => Registro::count(),
            'año_actual' => now()->year,
        ];

        return view('s13.index', compact('estadisticas'));
    }

    /**
     * Generar PDF del reporte S13
     */
    public function generarPdf(Request $request)
    {
        // Obtener año de servicio (por defecto el actual)
        $añoServicio = $request->get('año', now()->year);
        $añoSiguiente = $añoServicio + 1;

        // Obtener todos los territorios del 1 al 214
        $territorios = Territorio::whereBetween('numero', [1, 214])
            ->orderBy('numero')
            ->get();

        // Completar hasta 214 si faltan territorios
        $territoriosCompletos = collect();
        for ($i = 1; $i <= 214; $i++) {
            $territorio = $territorios->where('numero', $i)->first();
            if (!$territorio) {
                // Crear territorio virtual si no existe
                $territorio = (object) [
                    'numero' => $i,
                    'registros' => collect([])
                ];
            } else {
                // LÓGICA MEJORADA: Siempre mostrar al menos el último registro del territorio
                $fechaInicioAñoServicio = Carbon::create($añoServicio, 9, 1);
                $fechaFinAñoServicio = Carbon::create($añoSiguiente, 8, 31);
                
                // 1. Registros del año de servicio actual (prioridad máxima)
                $registrosAñoActual = $territorio->registros()
                    ->whereBetween('fecha_salida', [$fechaInicioAñoServicio, $fechaFinAñoServicio])
                    ->with('publicador')
                    ->orderBy('fecha_salida')
                    ->get();
                
                // 2. Registros activos del año anterior (sin fecha_entrada)
                $registrosActivosAnteriores = $territorio->registros()
                    ->where('fecha_salida', '<', $fechaInicioAñoServicio)
                    ->whereNull('fecha_entrada')
                    ->with('publicador')
                    ->orderBy('fecha_salida', 'desc')
                    ->get();
                
                // 3. Si no hay suficientes registros, obtener los últimos históricos
                $registrosHistoricos = collect();
                $totalRegistros = $registrosAñoActual->count() + $registrosActivosAnteriores->count();
                
                if($totalRegistros < 4) {
                    $registrosHistoricos = $territorio->registros()
                        ->where('fecha_salida', '<', $fechaInicioAñoServicio)
                        ->whereNotNull('fecha_entrada') // Solo completados
                        ->with('publicador')
                        ->orderBy('fecha_salida', 'desc')
                        ->take(4 - $totalRegistros) // Completar hasta 4
                        ->get();
                }
                
                // 4. Si aún no hay registros, tomar CUALQUIER registro histórico
                if($totalRegistros == 0 && $registrosHistoricos->count() == 0) {
                    $registrosHistoricos = $territorio->registros()
                        ->with('publicador')
                        ->orderBy('fecha_salida', 'desc')
                        ->take(4) // Últimos 4 registros históricos
                        ->get();
                }
                
                // Combinar todos los registros en orden cronológico
                $territorio->registros = $registrosHistoricos
                    ->concat($registrosActivosAnteriores)
                    ->concat($registrosAñoActual)
                    ->sortBy('fecha_salida')
                    ->take(4) // Máximo 4 asignaciones por territorio
                    ->values();
            }
            $territoriosCompletos->push($territorio);
        }

        // Dividir en páginas de 20 territorios exactos
        $paginas = $territoriosCompletos->chunk(20);

        // Configurar dompdf
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isRemoteEnabled', false);
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);

        // Generar HTML del PDF
        $html = view('s13.pdf-simple', [
            'paginas' => $paginas,
            'añoServicio' => $añoServicio,
            'añoSiguiente' => $añoSiguiente,
            'fechaGeneracion' => now()
        ])->render();

        // Cargar HTML en dompdf
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Descargar PDF
        $nombreArchivo = "S13_Registro_Territorios_{$añoServicio}-{$añoSiguiente}.pdf";
        
        return $dompdf->stream($nombreArchivo, [
            'Attachment' => false // true para descargar, false para ver en navegador
        ]);
    }

    /**
     * Vista previa del reporte (HTML)
     */
    public function vistaPrevia(Request $request)
    {
        // Misma lógica que el PDF pero devolver vista HTML
        $añoServicio = $request->get('año', now()->year);
        $añoSiguiente = $añoServicio + 1;

        $territorios = Territorio::whereBetween('numero', [1, 214])
            ->orderBy('numero')
            ->get();

        $territoriosCompletos = collect();
        for ($i = 1; $i <= 214; $i++) {
            $territorio = $territorios->where('numero', $i)->first();
            if (!$territorio) {
                $territorio = (object) [
                    'numero' => $i,
                    'registros' => collect([])
                ];
            } else {
                // LÓGICA MEJORADA: Siempre mostrar al menos el último registro del territorio (igual que PDF)
                $fechaInicioAñoServicio = Carbon::create($añoServicio, 9, 1);
                $fechaFinAñoServicio = Carbon::create($añoSiguiente, 8, 31);
                
                // 1. Registros del año de servicio actual (prioridad máxima)
                $registrosAñoActual = $territorio->registros()
                    ->whereBetween('fecha_salida', [$fechaInicioAñoServicio, $fechaFinAñoServicio])
                    ->with('publicador')
                    ->orderBy('fecha_salida')
                    ->get();
                
                // 2. Registros activos del año anterior (sin fecha_entrada)
                $registrosActivosAnteriores = $territorio->registros()
                    ->where('fecha_salida', '<', $fechaInicioAñoServicio)
                    ->whereNull('fecha_entrada')
                    ->with('publicador')
                    ->orderBy('fecha_salida', 'desc')
                    ->get();
                
                // 3. Si no hay suficientes registros, obtener los últimos históricos
                $registrosHistoricos = collect();
                $totalRegistros = $registrosAñoActual->count() + $registrosActivosAnteriores->count();
                
                if($totalRegistros < 4) {
                    $registrosHistoricos = $territorio->registros()
                        ->where('fecha_salida', '<', $fechaInicioAñoServicio)
                        ->whereNotNull('fecha_entrada') // Solo completados
                        ->with('publicador')
                        ->orderBy('fecha_salida', 'desc')
                        ->take(4 - $totalRegistros) // Completar hasta 4
                        ->get();
                }
                
                // 4. Si aún no hay registros, tomar CUALQUIER registro histórico
                if($totalRegistros == 0 && $registrosHistoricos->count() == 0) {
                    $registrosHistoricos = $territorio->registros()
                        ->with('publicador')
                        ->orderBy('fecha_salida', 'desc')
                        ->take(4) // Últimos 4 registros históricos
                        ->get();
                }
                
                // Combinar todos los registros en orden cronológico
                $territorio->registros = $registrosHistoricos
                    ->concat($registrosActivosAnteriores)
                    ->concat($registrosAñoActual)
                    ->sortBy('fecha_salida')
                    ->take(4) // Máximo 4 asignaciones por territorio
                    ->values();
            }
            $territoriosCompletos->push($territorio);
        }

        $paginas = $territoriosCompletos->chunk(20);

        return view('s13.vista-previa', [
            'paginas' => $paginas,
            'añoServicio' => $añoServicio,
            'añoSiguiente' => $añoSiguiente,
            'fechaGeneracion' => now()
        ]);
    }
}
