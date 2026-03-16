<?php

namespace App\Http\Controllers\Territorios;

use App\Http\Controllers\Controller;
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
            'territorios_libres' => 0,
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
                $territorio = (object) [
                    'numero' => $i,
                    'registros' => collect([])
                ];
            } else {
                $fechaInicioAñoServicio = Carbon::create($añoServicio, 9, 1);
                $fechaFinAñoServicio = Carbon::create($añoSiguiente, 8, 31);

                $registrosAñoActual = $territorio->registros()
                    ->whereBetween('fecha_salida', [$fechaInicioAñoServicio, $fechaFinAñoServicio])
                    ->with('publicador')
                    ->orderBy('fecha_salida')
                    ->get();

                $registrosActivosAnteriores = $territorio->registros()
                    ->where('fecha_salida', '<', $fechaInicioAñoServicio)
                    ->whereNull('fecha_entrada')
                    ->with('publicador')
                    ->orderBy('fecha_salida', 'desc')
                    ->get();

                $registrosHistoricos = collect();
                $totalRegistros = $registrosAñoActual->count() + $registrosActivosAnteriores->count();

                if($totalRegistros < 4) {
                    $registrosHistoricos = $territorio->registros()
                        ->where('fecha_salida', '<', $fechaInicioAñoServicio)
                        ->whereNotNull('fecha_entrada')
                        ->with('publicador')
                        ->orderBy('fecha_salida', 'desc')
                        ->take(4 - $totalRegistros)
                        ->get();
                }

                if($totalRegistros == 0 && $registrosHistoricos->count() == 0) {
                    $registrosHistoricos = $territorio->registros()
                        ->with('publicador')
                        ->orderBy('fecha_salida', 'desc')
                        ->take(4)
                        ->get();
                }

                $territorio->registros = $registrosHistoricos
                    ->concat($registrosActivosAnteriores)
                    ->concat($registrosAñoActual)
                    ->sortBy('fecha_salida')
                    ->take(4)
                    ->values();
            }
            $territoriosCompletos->push($territorio);
        }

        $paginas = $territoriosCompletos->chunk(20);

        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isRemoteEnabled', false);
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);

        $html = view('s13.pdf-simple', [
            'paginas' => $paginas,
            'añoServicio' => $añoServicio,
            'añoSiguiente' => $añoSiguiente,
            'fechaGeneracion' => now()
        ])->render();

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $nombreArchivo = "S13_Registro_Territorios_{$añoServicio}-{$añoSiguiente}.pdf";

        return $dompdf->stream($nombreArchivo, [
            'Attachment' => false
        ]);
    }

    /**
     * Vista previa del reporte (HTML)
     */
    public function vistaPrevia(Request $request)
    {
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
                $fechaInicioAñoServicio = Carbon::create($añoServicio, 9, 1);
                $fechaFinAñoServicio = Carbon::create($añoSiguiente, 8, 31);

                $registrosAñoActual = $territorio->registros()
                    ->whereBetween('fecha_salida', [$fechaInicioAñoServicio, $fechaFinAñoServicio])
                    ->with('publicador')
                    ->orderBy('fecha_salida')
                    ->get();

                $registrosActivosAnteriores = $territorio->registros()
                    ->where('fecha_salida', '<', $fechaInicioAñoServicio)
                    ->whereNull('fecha_entrada')
                    ->with('publicador')
                    ->orderBy('fecha_salida', 'desc')
                    ->get();

                $registrosHistoricos = collect();
                $totalRegistros = $registrosAñoActual->count() + $registrosActivosAnteriores->count();

                if($totalRegistros < 4) {
                    $registrosHistoricos = $territorio->registros()
                        ->where('fecha_salida', '<', $fechaInicioAñoServicio)
                        ->whereNotNull('fecha_entrada')
                        ->with('publicador')
                        ->orderBy('fecha_salida', 'desc')
                        ->take(4 - $totalRegistros)
                        ->get();
                }

                if($totalRegistros == 0 && $registrosHistoricos->count() == 0) {
                    $registrosHistoricos = $territorio->registros()
                        ->with('publicador')
                        ->orderBy('fecha_salida', 'desc')
                        ->take(4)
                        ->get();
                }

                $territorio->registros = $registrosHistoricos
                    ->concat($registrosActivosAnteriores)
                    ->concat($registrosAñoActual)
                    ->sortBy('fecha_salida')
                    ->take(4)
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
