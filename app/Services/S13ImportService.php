<?php

namespace App\Services;

use App\Models\Registro;
use Carbon\Carbon;

class S13ImportService
{
    /**
     * Parsear texto extraído de un PDF S-13
     * Formato detectado: nombres antes del territorio, fechas después separadas por |
     */
    public function parseS13Text(string $text): array
    {
        $registrosDetectados = [];

        // Normalizar texto: quitar saltos de línea extra
        $texto = preg_replace('/\s+/', ' ', $text);

        // Patrón para encontrar territorios con sus datos
        // Formato: "Nombre1 Nombre2 ... CEN-X |fecha|fecha|..."
        // Buscar todos los códigos de territorio
        $patronTerritorio = '/(CEN-?\d+|ROQ-?\d+|T\.?ROM-?\d+|\d{1,3})\s*\|/i';

        preg_match_all($patronTerritorio, $texto, $matches, PREG_OFFSET_CAPTURE);

        if (empty($matches[0])) {
            return [];
        }

        // Procesar cada territorio encontrado
        $posiciones = [];
        foreach ($matches[0] as $idx => $match) {
            $posiciones[] = [
                'codigo' => strtoupper(trim($matches[1][$idx][0])),
                'pos_inicio' => $match[1],
                'pos_codigo' => $matches[1][$idx][1],
            ];
        }

        // Para cada territorio, extraer nombres (antes) y fechas (después)
        for ($i = 0; $i < count($posiciones); $i++) {
            $territorio = $posiciones[$i];

            // Texto antes del territorio (contiene nombres)
            $posAnterior = ($i > 0) ? $this->encontrarFinFechas($texto, $posiciones[$i-1]['pos_inicio']) : 0;
            $textoAntes = substr($texto, $posAnterior, $territorio['pos_codigo'] - $posAnterior);

            // Texto después del territorio (contiene fechas)
            $posSiguiente = ($i < count($posiciones) - 1) ? $posiciones[$i+1]['pos_codigo'] : strlen($texto);
            $textoDespues = substr($texto, $territorio['pos_inicio'], $posSiguiente - $territorio['pos_inicio']);

            // Extraer fechas
            $fechas = $this->extraerFechas($textoDespues);

            // Extraer nombres
            $nombres = $this->extraerNombres($textoAntes);

            // Emparejar nombres con fechas
            // El formato S-13 tiene pares: fecha_asignada, fecha_finalización
            $numAsignaciones = min(count($nombres), floor(count($fechas) / 2));

            for ($j = 0; $j < $numAsignaciones; $j++) {
                $fechaSalidaIdx = $j * 2;
                $fechaEntradaIdx = $j * 2 + 1;

                $fechaSalida = isset($fechas[$fechaSalidaIdx]) ? $this->parseDate($fechas[$fechaSalidaIdx]) : null;
                $fechaEntrada = isset($fechas[$fechaEntradaIdx]) ? $this->parseDate($fechas[$fechaEntradaIdx]) : null;

                if ($fechaSalida && isset($nombres[$j])) {
                    $registrosDetectados[] = [
                        'territorio_numero' => $territorio['codigo'],
                        'publicador_nombre_raw' => $nombres[$j],
                        'fecha_salida' => $fechaSalida,
                        'fecha_entrada' => $fechaEntrada,
                        'raw_data' => [
                            'texto_antes' => $textoAntes,
                            'texto_despues' => $textoDespues,
                            'fechas_raw' => $fechas,
                            'nombres_raw' => $nombres,
                        ],
                    ];
                }
            }
        }

        return $registrosDetectados;
    }

    /**
     * Encontrar posición donde terminan las fechas de un territorio
     */
    private function encontrarFinFechas(string $texto, int $posInicio): int
    {
        // Buscar el último | o fecha después del territorio
        $patron = '/\d{1,2}\/\d{1,2}\/\d{2,4}[^\d]*/';
        $subtexto = substr($texto, $posInicio, 500); // Buscar en los próximos 500 chars

        if (preg_match_all($patron, $subtexto, $matches, PREG_OFFSET_CAPTURE)) {
            $ultimaFecha = end($matches[0]);
            return $posInicio + $ultimaFecha[1] + strlen($ultimaFecha[0]);
        }

        return $posInicio + 100; // Fallback
    }

    /**
     * Extraer fechas del texto
     */
    private function extraerFechas(string $texto): array
    {
        $fechas = [];

        // Buscar fechas en formato dd/mm/yyyy o dd/mm/yy
        preg_match_all('/(\d{1,2}\/\d{1,2}\/\d{2,4})/', $texto, $matches);

        if (!empty($matches[1])) {
            $fechas = $matches[1];
        }

        return $fechas;
    }

    /**
     * Extraer nombres del texto
     */
    private function extraerNombres(string $texto): array
    {
        $nombres = [];

        // Limpiar texto de headers y caracteres especiales
        $texto = preg_replace('/Registro\s+de\s+asignaci[oó]n.*?Zonas/is', '', $texto);
        $texto = preg_replace('/Asignado\s+a/i', '', $texto);
        $texto = preg_replace('/Fecha.*?(asignada|finalizaci[oó]n)/i', '', $texto);
        $texto = preg_replace('/[|"\'\[\]{}]/', ' ', $texto);
        $texto = preg_replace('/\s+/', ' ', $texto);
        $texto = trim($texto);

        if (empty($texto)) {
            return [];
        }

        // Detectar nombres (patrón: Nombre Apellido o Nombre Apellido Apellido)
        // Los nombres españoles suelen tener 2-4 palabras
        $palabras = preg_split('/\s+/', $texto);
        $nombreActual = [];

        foreach ($palabras as $palabra) {
            $palabra = trim($palabra);

            // Ignorar palabras vacías o muy cortas
            if (strlen($palabra) < 2) continue;

            // Ignorar si parece ser un código de territorio o fecha
            if (preg_match('/^(CEN|ROQ|T\.?ROM|\d{1,2}\/)/i', $palabra)) continue;

            // Ignorar palabras que son headers
            if (preg_match('/^(Grupo|Terr|Todos|Todas|categorías|Zonas)$/i', $palabra)) continue;

            // Si la palabra empieza con mayúscula, podría ser inicio de nombre
            if (preg_match('/^[A-ZÁÉÍÓÚÑ][a-záéíóúñ]+$/', $palabra)) {
                // Si ya tenemos 2+ palabras en nombreActual, guardar como nombre completo
                if (count($nombreActual) >= 2) {
                    $nombreCompleto = implode(' ', $nombreActual);
                    // Verificar que parece un nombre real
                    if ($this->pareceNombre($nombreCompleto)) {
                        $nombres[] = $nombreCompleto;
                    }
                    $nombreActual = [];
                }
                $nombreActual[] = $palabra;
            } elseif (!empty($nombreActual) && preg_match('/^[A-ZÁÉÍÓÚÑ]?[a-záéíóúñ]+$/', $palabra)) {
                // Palabra que continúa el nombre (apellido)
                $nombreActual[] = $palabra;
            }
        }

        // No olvidar el último nombre
        if (count($nombreActual) >= 2) {
            $nombreCompleto = implode(' ', $nombreActual);
            if ($this->pareceNombre($nombreCompleto)) {
                $nombres[] = $nombreCompleto;
            }
        }

        return $nombres;
    }

    /**
     * Verificar si un texto parece ser un nombre de persona
     */
    private function pareceNombre(string $texto): bool
    {
        // Debe tener al menos 2 palabras
        $palabras = preg_split('/\s+/', $texto);
        if (count($palabras) < 2) return false;

        // No debe contener números
        if (preg_match('/\d/', $texto)) return false;

        // Debe tener un tamaño razonable
        if (strlen($texto) < 5 || strlen($texto) > 50) return false;

        // No debe ser un header conocido
        $headersConocidos = ['Registro de', 'Año de', 'Asignado a', 'Fecha asignada', 'Todos Todas'];
        foreach ($headersConocidos as $header) {
            if (stripos($texto, $header) !== false) return false;
        }

        return true;
    }

    /**
     * Parsear fecha en varios formatos
     */
    public function parseDate(string $dateStr): ?Carbon
    {
        $dateStr = trim($dateStr);

        // Limpiar caracteres extraños del OCR
        $dateStr = preg_replace('/[^0-9\/\-]/', '', $dateStr);

        // Si la fecha quedó vacía o muy corta
        if (strlen($dateStr) < 6) return null;

        $dateStr = str_replace('/', '-', $dateStr);

        $formatos = [
            'd-m-Y',    // 01-12-2025
            'd-m-y',    // 01-12-25
            'j-n-Y',    // 1-2-2025
            'j-n-y',    // 1-2-25
            'dmY',      // 01122025 (sin separadores)
            'dmy',      // 011225
        ];

        foreach ($formatos as $formato) {
            try {
                $fecha = Carbon::createFromFormat($formato, $dateStr);
                if ($fecha && $fecha->year > 2000 && $fecha->year < 2100) {
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
     */
    public function verificarSobreposicion(
        int $territorioId,
        Carbon $fechaSalida,
        ?Carbon $fechaEntrada
    ): array {
        $registrosExistentes = Registro::where('territorio_id', $territorioId)->get();

        foreach ($registrosExistentes as $registro) {
            $existenteSalida = $registro->fecha_salida;
            $existenteEntrada = $registro->fecha_entrada ?? Carbon::now()->addYears(10);
            $importadoEntrada = $fechaEntrada ?? Carbon::now()->addYears(10);

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
