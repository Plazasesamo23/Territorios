<?php

namespace App\Http\Controllers\Territorios;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Territorio;
use App\Models\Registro;
use App\Models\Publicador;
use App\Models\Congregacion;
use Dompdf\Dompdf;
use Dompdf\Options;
use Carbon\Carbon;

class S13Controller extends Controller
{
    /**
     * Obtener la congregacion activa de forma segura
     */
    private function getCongregacionActivaId(): int
    {
        return session('congregacion_activa_id') ?? 0;
    }

    /**
     * Año de servicio en curso. El año de servicio teocrático va de
     * septiembre a agosto; antes de septiembre seguimos en el año anterior.
     */
    private function añoServicioActual(): int
    {
        return now()->month < 9 ? now()->year - 1 : now()->year;
    }

    /**
     * Obtener territorios de la congregacion activa
     */
    private function getTerritoriosCongregacion(): \Illuminate\Database\Eloquent\Collection
    {
        $congregacionId = $this->getCongregacionActivaId();

        return Territorio::withoutGlobalScope('congregacion')
            ->where('congregacion_id', $congregacionId)
            ->where('tipo', 'normal')->orderBy('numero')
            ->get();
    }

    /**
     * Obtener IDs de territorios de la congregacion activa
     */
    private function getTerritorioIdsCongregacion(): array
    {
        $congregacionId = $this->getCongregacionActivaId();

        return Territorio::withoutGlobalScope('congregacion')
            ->where('congregacion_id', $congregacionId)
            ->pluck('id')
            ->toArray();
    }

    /**
     * Mostrar la pagina de reportes S13
     */
    public function index()
    {
        if (!auth()->user()->canGenerateS13()) {
            abort(403, 'No tienes permisos para acceder al reporte S-13.');
        }

        $congregacionId = $this->getCongregacionActivaId();

        // Obtener IDs de territorios SOLO de la congregacion activa
        $territorioIds = $this->getTerritorioIdsCongregacion();
        $territorios = $this->getTerritoriosCongregacion();

        // Estadisticas para la pagina principal
        $estadisticas = [
            'total_territorios' => $territorios->count(),
            'territorios_libres' => $territorios->filter(fn($t) => $t->calcularEstado() === 'libre')->count(),
            'territorios_asignados' => count($territorioIds) > 0
                ? Registro::whereIn('territorio_id', $territorioIds)->whereNull('fecha_entrada')->count()
                : 0,
            'total_registros' => count($territorioIds) > 0
                ? Registro::whereIn('territorio_id', $territorioIds)->count()
                : 0,
            'año_actual' => $this->añoServicioActual(),
        ];

        $congregacionActiva = Congregacion::find($congregacionId);

        return view('s13.index', compact('estadisticas', 'congregacionActiva'));
    }

    /**
     * Procesar territorios para el reporte S13
     */
    private function procesarTerritoriosParaS13(int $añoServicio): \Illuminate\Support\Collection
    {
        $añoSiguiente = $añoServicio + 1;
        $congregacionId = $this->getCongregacionActivaId();

        // Obtener territorios SOLO de la congregacion activa
        $territorios = $this->getTerritoriosCongregacion();

        if ($territorios->isEmpty()) {
            return collect();
        }

        $territoriosCompletos = collect();

        foreach ($territorios as $territorio) {
            $fechaInicioAñoServicio = Carbon::create($añoServicio, 9, 1);
            $fechaFinAñoServicio = Carbon::create($añoSiguiente, 8, 31);

            // IMPORTANTE: Cargar publicador SIN el global scope para evitar filtrado
            $withPublicador = ['publicador' => function($query) {
                $query->withoutGlobalScope('congregacion');
            }];

            // 1. Registros del año de servicio actual
            $registrosAñoActual = $territorio->registros()
                ->whereBetween('fecha_salida', [$fechaInicioAñoServicio, $fechaFinAñoServicio])
                ->with($withPublicador)
                ->orderBy('fecha_salida')
                ->get();

            // 2. Registros activos del año anterior
            $registrosActivosAnteriores = $territorio->registros()
                ->where('fecha_salida', '<', $fechaInicioAñoServicio)
                ->whereNull('fecha_entrada')
                ->with($withPublicador)
                ->orderBy('fecha_salida', 'desc')
                ->get();

            // 3. Registros historicos si faltan
            $registrosHistoricos = collect();
            $totalRegistros = $registrosAñoActual->count() + $registrosActivosAnteriores->count();

            if ($totalRegistros < 4) {
                $registrosHistoricos = $territorio->registros()
                    ->where('fecha_salida', '<', $fechaInicioAñoServicio)
                    ->whereNotNull('fecha_entrada')
                    ->with($withPublicador)
                    ->orderBy('fecha_salida', 'desc')
                    ->take(4 - $totalRegistros)
                    ->get();
            }

            // 4. Si no hay registros, tomar cualquier historico
            if ($totalRegistros == 0 && $registrosHistoricos->count() == 0) {
                $registrosHistoricos = $territorio->registros()
                    ->with($withPublicador)
                    ->orderBy('fecha_salida', 'desc')
                    ->take(4)
                    ->get();
            }

            // Combinar registros
            $registrosCombinados = $registrosHistoricos
                ->concat($registrosActivosAnteriores)
                ->concat($registrosAñoActual)
                ->sortBy('fecha_salida')
                ->take(4)
                ->values();

            $territorio->registros = $registrosCombinados;
            $territoriosCompletos->push($territorio);
        }

        return $territoriosCompletos;
    }

    /**
     * Generar PDF del reporte S13
     */
    public function generarPdf(Request $request)
    {
        $añoServicio = (int) $request->get('año', $request->get('ano', $this->añoServicioActual()));
        $añoSiguiente = $añoServicio + 1;
        $congregacionId = $this->getCongregacionActivaId();

        $territoriosCompletos = $this->procesarTerritoriosParaS13($añoServicio);

        if ($territoriosCompletos->isEmpty()) {
            $paginas = collect([collect()]);
        } else {
            $paginas = $territoriosCompletos->chunk(20);
        }

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

        // Nombre del archivo incluye congregacion para evitar cache
        $congregacion = Congregacion::find($congregacionId);
        $nombreCong = $congregacion ? preg_replace('/[^a-zA-Z0-9]/', '', $congregacion->nombre) : 'General';
        $timestamp = now()->format('His');
        $nombreArchivo = "S13_{$nombreCong}_{$añoServicio}-{$añoSiguiente}_{$timestamp}.pdf";

        // Enviar con headers anti-cache
        return response($dompdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', "inline; filename=\"{$nombreArchivo}\"")
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    /**
     * Vista previa del reporte (HTML)
     */
    public function vistaPrevia(Request $request)
    {
        $añoServicio = (int) $request->get('año', $request->get('ano', $this->añoServicioActual()));
        $añoSiguiente = $añoServicio + 1;
        $congregacionId = $this->getCongregacionActivaId();

        $territoriosCompletos = $this->procesarTerritoriosParaS13($añoServicio);

        if ($territoriosCompletos->isEmpty()) {
            $paginas = collect([collect()]);
        } else {
            $paginas = $territoriosCompletos->chunk(20);
        }

        return response()
            ->view('s13.vista-previa', [
                'paginas' => $paginas,
                'añoServicio' => $añoServicio,
                'añoSiguiente' => $añoSiguiente,
                'fechaGeneracion' => now(),
                'congregacionActiva' => Congregacion::find($congregacionId),
                'totalTerritorios' => $territoriosCompletos->count()
            ])
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
}
