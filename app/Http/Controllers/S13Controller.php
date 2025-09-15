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
                // Obtener registros con continuidad del año anterior
                $fechaInicioAñoServicio = Carbon::create($añoServicio, 9, 1);
                $fechaFinAñoServicio = Carbon::create($añoSiguiente, 8, 31);
                
                // Buscar registros activos del año anterior (sin fecha_entrada)
                $registrosAñoAnterior = $territorio->registros()
                    ->where('fecha_salida', '<', $fechaInicioAñoServicio)
                    ->whereNull('fecha_entrada')
                    ->with('publicador')
                    ->orderBy('fecha_salida', 'desc')
                    ->take(2) // Máximo 2 del año anterior
                    ->get();
                
                // Registros completados en los últimos 6 meses antes del año de servicio
                $registrosRecientes = $territorio->registros()
                    ->where('fecha_salida', '>=', Carbon::create($añoServicio, 3, 1)) // 6 meses antes
                    ->where('fecha_salida', '<', $fechaInicioAñoServicio)
                    ->whereNotNull('fecha_entrada')
                    ->with('publicador')
                    ->orderBy('fecha_salida', 'desc')
                    ->take(1) // Máximo 1 reciente completado
                    ->get();
                
                // Registros del año de servicio actual
                $registrosAñoActual = $territorio->registros()
                    ->whereBetween('fecha_salida', [$fechaInicioAñoServicio, $fechaFinAñoServicio])
                    ->with('publicador')
                    ->orderBy('fecha_salida')
                    ->get();
                
                // Combinar todos los registros en orden cronológico
                $territorio->registros = $registrosRecientes
                    ->concat($registrosAñoAnterior)
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
                // Obtener registros con continuidad del año anterior (igual lógica que el PDF)
                $fechaInicioAñoServicio = Carbon::create($añoServicio, 9, 1);
                $fechaFinAñoServicio = Carbon::create($añoSiguiente, 8, 31);
                
                // Buscar registros activos del año anterior (sin fecha_entrada)
                $registrosAñoAnterior = $territorio->registros()
                    ->where('fecha_salida', '<', $fechaInicioAñoServicio)
                    ->whereNull('fecha_entrada')
                    ->with('publicador')
                    ->orderBy('fecha_salida', 'desc')
                    ->take(2) // Máximo 2 del año anterior
                    ->get();
                
                // Registros completados en los últimos 6 meses antes del año de servicio
                $registrosRecientes = $territorio->registros()
                    ->where('fecha_salida', '>=', Carbon::create($añoServicio, 3, 1)) // 6 meses antes
                    ->where('fecha_salida', '<', $fechaInicioAñoServicio)
                    ->whereNotNull('fecha_entrada')
                    ->with('publicador')
                    ->orderBy('fecha_salida', 'desc')
                    ->take(1) // Máximo 1 reciente completado
                    ->get();
                
                // Registros del año de servicio actual
                $registrosAñoActual = $territorio->registros()
                    ->whereBetween('fecha_salida', [$fechaInicioAñoServicio, $fechaFinAñoServicio])
                    ->with('publicador')
                    ->orderBy('fecha_salida')
                    ->get();
                
                // Combinar todos los registros en orden cronológico
                $territorio->registros = $registrosRecientes
                    ->concat($registrosAñoAnterior)
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
