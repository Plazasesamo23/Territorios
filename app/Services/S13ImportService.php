<?php

namespace App\Services;

use App\Models\Registro;
use Carbon\Carbon;

class S13ImportService
{
    /**
     * Parsear texto extraído de un PDF S-13
     * Retorna array de registros detectados
     */
    public function parseS13Text(string $text): array
    {
        $registrosDetectados = [];
        $lines = explode("\n", $text);

        // Patrón para fecha dd-mm-yy o dd-mm-yyyy
        $patronFecha = '/(\d{1,2}[-\/]\d{1,2}[-\/]\d{2,4})/';

        // Variables de estado
        $currentTerritorio = null;
        $currentNombres = [];
        $lineBuffer = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            // Ignorar headers conocidos
            if ($this->esLineaHeader($line)) {
                continue;
            }

            // Detectar número de territorio al inicio de línea (1-214)
            if (preg_match('/^(\d{1,3})\s/', $line, $matchTerr)) {
                $numeroTerritorio = (int) $matchTerr[1];

                if ($numeroTerritorio >= 1 && $numeroTerritorio <= 214) {
                    // Si ya teníamos un territorio pendiente, procesarlo
                    if ($currentTerritorio !== null && !empty($lineBuffer)) {
                        $registros = $this->procesarBloqueTerritorio($currentTerritorio, $lineBuffer);
                        $registrosDetectados = array_merge($registrosDetectados, $registros);
                    }

                    $currentTerritorio = $numeroTerritorio;
                    $lineBuffer = [$line];
                    continue;
                }
            }

            // Acumular líneas del territorio actual
            if ($currentTerritorio !== null) {
                $lineBuffer[] = $line;
            }
        }

        // Procesar el último territorio
        if ($currentTerritorio !== null && !empty($lineBuffer)) {
            $registros = $this->procesarBloqueTerritorio($currentTerritorio, $lineBuffer);
            $registrosDetectados = array_merge($registrosDetectados, $registros);
        }

        return $registrosDetectados;
    }

    /**
     * Procesar bloque de líneas de un territorio
     */
    private function procesarBloqueTerritorio(int $numeroTerritorio, array $lines): array
    {
        $registros = [];
        $texto = implode(' ', $lines);

        // Guardar el texto original antes de procesarlo
        $textoOriginal = $texto;
        $lineasOriginales = $lines;

        // Extraer todas las fechas con sus posiciones
        preg_match_all('/(\d{1,2}[-\/]\d{1,2}[-\/]\d{2,4})/', $texto, $matchesFechas, PREG_OFFSET_CAPTURE);
        $fechasConPos = $matchesFechas[1] ?? [];
        $fechas = array_column($fechasConPos, 0);
        $fechasRaw = $fechas; // Guardar las fechas tal como se leyeron

        // Extraer nombres (texto que no son fechas ni números de territorio)
        $textoSinFechas = preg_replace('/\d{1,2}[-\/]\d{1,2}[-\/]\d{2,4}/', '|||', $texto);
        $textoSinNumTerr = preg_replace('/^\d{1,3}\s+/', '', $textoSinFechas);

        // Dividir por el separador que pusimos
        $partesTexto = explode('|||', $textoSinNumTerr);
        $nombres = [];
        $nombresRaw = []; // Guardar los nombres tal como se leyeron

        foreach ($partesTexto as $parte) {
            $parteOriginal = $parte;
            $parte = trim($parte);
            // Filtrar partes que parecen nombres (más de 3 caracteres, contiene letras)
            if (strlen($parte) > 3 && preg_match('/[a-záéíóúñ]/i', $parte)) {
                // Limpiar y normalizar
                $parte = preg_replace('/\s+/', ' ', $parte);
                if (!empty($parte)) {
                    $nombres[] = $parte;
                    $nombresRaw[] = trim($parteOriginal);
                }
            }
        }

        // Las fechas vienen en pares: [salida1, entrada1, salida2, entrada2, ...]
        // Ignorar la primera fecha si parece ser "última fecha completada"
        $fechaOffset = 0;
        if (count($fechas) > 0 && count($fechas) % 2 === 1) {
            // Número impar de fechas, la primera es "última fecha completada"
            $fechaOffset = 1;
        }

        // Crear registros
        for ($i = 0; $i < count($nombres); $i++) {
            $fechaIndex = $fechaOffset + ($i * 2);

            if (!isset($fechas[$fechaIndex])) {
                continue;
            }

            $fechaSalidaRaw = $fechas[$fechaIndex];
            $fechaEntradaRaw = $fechas[$fechaIndex + 1] ?? null;

            $fechaSalida = $this->parseDate($fechaSalidaRaw);
            $fechaEntrada = $fechaEntradaRaw ? $this->parseDate($fechaEntradaRaw) : null;

            if ($fechaSalida) {
                $registros[] = [
                    'territorio_numero' => $numeroTerritorio,
                    'publicador_nombre_raw' => $nombres[$i],
                    'fecha_salida' => $fechaSalida,
                    'fecha_entrada' => $fechaEntrada,
                    // Nuevos campos para el registro original
                    'raw_data' => [
                        'texto_completo' => $textoOriginal,
                        'lineas' => $lineasOriginales,
                        'nombre_detectado' => $nombresRaw[$i] ?? $nombres[$i],
                        'fecha_salida_raw' => $fechaSalidaRaw,
                        'fecha_entrada_raw' => $fechaEntradaRaw,
                        'todas_fechas' => $fechasRaw,
                        'todos_nombres' => $nombresRaw,
                    ],
                ];
            }
        }

        return $registros;
    }

    /**
     * Verificar si una línea es header del PDF
     */
    private function esLineaHeader(string $line): bool
    {
        $headersConocidos = [
            'REGISTRO DE ASIGNACIÓN',
            'Año de servicio',
            'Página',
            'Núm. de terr',
            'Última fecha',
            'Asignado a',
            'Fecha en que',
            'se asignó',
            'se completó',
            'S-13',
        ];

        $lineUpper = mb_strtoupper($line);
        foreach ($headersConocidos as $header) {
            if (str_contains($lineUpper, mb_strtoupper($header))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Parsear fecha en varios formatos
     */
    public function parseDate(string $dateStr): ?Carbon
    {
        $dateStr = trim($dateStr);

        // Reemplazar / por -
        $dateStr = str_replace('/', '-', $dateStr);

        $formatos = [
            'd-m-y',    // 01-12-25
            'd-m-Y',    // 01-12-2025
            'j-n-y',    // 1-2-25
            'j-n-Y',    // 1-2-2025
        ];

        foreach ($formatos as $formato) {
            try {
                $fecha = Carbon::createFromFormat($formato, $dateStr);
                if ($fecha) {
                    return $fecha;
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return null;
    }

    /**
     * Verificar si un registro importado se solapa con registros existentes
     * Regla: Si hay sobreposición, el registro de BD GANA (se ignora el importado)
     */
    public function verificarSobreposicion(
        int $territorioId,
        Carbon $fechaSalida,
        ?Carbon $fechaEntrada
    ): array {
        // Obtener todos los registros existentes del territorio
        $registrosExistentes = Registro::where('territorio_id', $territorioId)->get();

        foreach ($registrosExistentes as $registro) {
            $existenteSalida = $registro->fecha_salida;
            // Si no tiene entrada, asumimos que sigue activo (hasta muy en el futuro)
            $existenteEntrada = $registro->fecha_entrada ?? Carbon::now()->addYears(10);

            // Para el importado, si no tiene entrada, también asumimos activo
            $importadoEntrada = $fechaEntrada ?? Carbon::now()->addYears(10);

            // Verificar sobreposición de rangos
            // Rango A: [existenteSalida, existenteEntrada]
            // Rango B: [fechaSalida, importadoEntrada]
            // Sobreposición si: A.inicio <= B.fin AND B.inicio <= A.fin
            $haySobreposicion = $existenteSalida->lte($importadoEntrada)
                && $fechaSalida->lte($existenteEntrada);

            if ($haySobreposicion) {
                return [
                    'tiene_sobreposicion' => true,
                    'registro_conflicto' => $registro,
                    'publicador_conflicto' => $registro->publicador
                        ? ($registro->publicador->nombre . ' ' . $registro->publicador->apellidos)
                        : 'Desconocido',
                    'fechas_conflicto' => [
                        'salida' => $registro->fecha_salida->format('d-m-Y'),
                        'entrada' => $registro->fecha_entrada?->format('d-m-Y') ?? 'Sin devolver',
                    ],
                ];
            }
        }

        return ['tiene_sobreposicion' => false];
    }
}
