<?php

namespace App\Services;

use App\Models\ReunionPrograma;
use App\Models\ReunionParte;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class ImportadorVymService
{
    /**
     * Importa los titulos del programa VyM desde wol.jw.org
     * y actualiza/recrea las partes del programa
     */
    public function importar(ReunionPrograma $programa): array
    {
        $fecha = $programa->fecha_semana;
        $url = "https://wol.jw.org/es/wol/dt/r4/lp-s/{$fecha->year}/{$fecha->month}/{$fecha->day}";

        $response = Http::timeout(10)->get($url);

        if (!$response->successful()) {
            return ['error' => 'No se pudo acceder a wol.jw.org (HTTP ' . $response->status() . ')'];
        }

        $html = $response->body();
        $partes = $this->parsearHtml($html);

        if (empty($partes)) {
            return ['error' => 'No se encontraron partes en el programa de esa semana.'];
        }

        // Eliminar partes existentes y recrear con los titulos correctos
        $programa->partes()->delete();

        $orden = 1;
        foreach ($partes as $parte) {
            ReunionParte::create([
                'programa_id' => $programa->id,
                'seccion' => $parte['seccion'],
                'tipo' => $parte['tipo'],
                'titulo' => $parte['titulo'],
                'duracion_minutos' => $parte['duracion'],
                'orden' => $orden++,
                'necesita_ayudante' => $parte['necesita_ayudante'],
            ]);
        }

        return [
            'success' => true,
            'partes' => count($partes),
        ];
    }

    private function parsearHtml(string $html): array
    {
        $partes = [];

        // Limpiar HTML
        $html = mb_convert_encoding($html, 'UTF-8', 'UTF-8');

        // Buscar los elementos de las partes del programa
        // Cada parte tiene un id como "p{n}" y clases que indican la seccion

        // Patron para encontrar las partes principales
        // Las partes estan en elementos con clases como "dc-icon--gem" (tesoros),
        // "dc-icon--ministry" (maestros), "dc-icon--living" (vida cristiana)

        // Estrategia: buscar los textos de las partes usando patrones de regex
        // ya que el HTML de wol.jw.org tiene una estructura consistente

        // Extraer texto plano agrupado por secciones
        $texto = strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>', '</div>', '</li>'], "\n", $html));
        $lineas = array_filter(array_map('trim', explode("\n", $texto)));
        $lineas = array_values($lineas);

        $seccionActual = null;
        $partesRaw = [];

        foreach ($lineas as $i => $linea) {
            // Detectar secciones
            if (preg_match('/TESOROS DE LA BIBLIA/iu', $linea)) {
                $seccionActual = 'tesoros';
                continue;
            }
            if (preg_match('/SEAMOS MEJORES MAESTROS/iu', $linea)) {
                $seccionActual = 'maestros';
                continue;
            }
            if (preg_match('/NUESTRA VIDA CRISTIANA/iu', $linea)) {
                $seccionActual = 'vida_cristiana';
                continue;
            }

            if (!$seccionActual) continue;

            // Detectar partes con duracion: "Titulo (X min.)" o "N. Titulo (X min.)"
            if (preg_match('/^(?:\d+\.\s*)?(.+?)\s*\((\d+)\s*min/iu', $linea, $m)) {
                $titulo = trim($m[1]);
                $duracion = (int) $m[2];

                // Ignorar canticos y oraciones
                if (preg_match('/^(Canción|Cancion|Song|Oración)/iu', $titulo)) continue;
                // Ignorar comentarios de conclusion
                if (preg_match('/^Palabras de conclus/iu', $titulo)) continue;

                $partesRaw[] = [
                    'seccion' => $seccionActual,
                    'titulo' => $titulo,
                    'duracion' => $duracion,
                ];
            }
        }

        // Clasificar cada parte segun seccion y titulo
        foreach ($partesRaw as $raw) {
            $tipo = $this->clasificarTipo($raw['seccion'], $raw['titulo'], $raw['duracion']);
            if (!$tipo) continue;

            $partes[] = [
                'seccion' => $raw['seccion'],
                'tipo' => $tipo,
                'titulo' => $raw['titulo'],
                'duracion' => $raw['duracion'],
                'necesita_ayudante' => in_array($tipo, [
                    'empiece_conversaciones', 'haga_revisitas', 'haga_discipulos', 'explique_creencias'
                ]),
            ];
        }

        return $partes;
    }

    private function clasificarTipo(string $seccion, string $titulo, int $duracion): ?string
    {
        $tituloLower = mb_strtolower($titulo);

        if ($seccion === 'tesoros') {
            if (preg_match('/perlas escondidas|busquemos perlas/iu', $titulo)) {
                return 'perlas';
            }
            if (preg_match('/lectura de la biblia/iu', $titulo)) {
                return 'lectura';
            }
            // El primer discurso de tesoros (10 min)
            return 'discurso_tesoros';
        }

        if ($seccion === 'maestros') {
            if (preg_match('/empiece conversacion/iu', $titulo)) return 'empiece_conversaciones';
            if (preg_match('/haga revisita/iu', $titulo)) return 'haga_revisitas';
            if (preg_match('/haga disc[ií]pulo/iu', $titulo)) return 'haga_discipulos';
            if (preg_match('/explique.*creencia/iu', $titulo)) return 'explique_creencias';
            // Parte generica de maestros
            if (preg_match('/empiece|revisita|disc[ií]pulo|creencia/iu', $titulo)) {
                return 'empiece_conversaciones'; // fallback
            }
            return 'empiece_conversaciones';
        }

        if ($seccion === 'vida_cristiana') {
            if (preg_match('/estudio b[ií]blico de la congregaci/iu', $titulo)) {
                return null; // El estudio lo gestionamos aparte (conductor/lector)
            }
            return 'discurso_vida';
        }

        return null;
    }
}
