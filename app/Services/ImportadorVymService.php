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
        $proxyUrl = "https://n8n.trastosbvaa.org/wol-proxy?y={$fecha->year}&m={$fecha->month}&d={$fecha->day}";

        $response = Http::timeout(20)->get($proxyUrl);

        if (!$response->successful()) {
            return ['error' => 'No se pudo acceder al proxy wol (HTTP ' . $response->status() . ')'];
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

        // Usar DOMDocument para parsear el HTML estructurado de wol.jw.org
        libxml_use_internal_errors(true);
        $doc = new \DOMDocument();
        $doc->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        libxml_clear_errors();

        $xpath = new \DOMXPath($doc);

        // Buscar el bloque del programa VyM (pub-mwb)
        $mwbBlocks = $xpath->query("//*[contains(@class, 'pub-mwb') and contains(@class, 'todayItem')]");
        if ($mwbBlocks->length === 0) {
            return [];
        }
        $mwbBlock = $mwbBlocks->item(0);

        // Recorrer h2 (secciones) y h3 (partes) dentro del bloque mwb
        $elementos = $xpath->query('.//h2 | .//h3', $mwbBlock);
        $seccionActual = null;

        foreach ($elementos as $el) {
            $texto = trim($el->textContent);

            // Detectar secciones (h2)
            if ($el->tagName === 'h2') {
                if (preg_match('/TESOROS DE LA BIBLIA/iu', $texto)) $seccionActual = 'tesoros';
                elseif (preg_match('/SEAMOS MEJORES MAESTROS/iu', $texto)) $seccionActual = 'maestros';
                elseif (preg_match('/NUESTRA VIDA CRISTIANA/iu', $texto)) $seccionActual = 'vida_cristiana';
                continue;
            }

            if (!$seccionActual) continue;

            // Extraer titulo: "N. Titulo" o "Titulo"
            if (!preg_match('/^(?:\d+\.\s*)?(.+)/u', $texto, $tMatch)) continue;
            $titulo = trim($tMatch[1]);

            // La duracion puede estar dentro del h3 o en el siguiente <p> hermano
            $duracion = 0;
            if (preg_match('/\((\d+)\s*mins?\.?\)/iu', $texto, $dMatch)) {
                $duracion = (int) $dMatch[1];
                $titulo = preg_replace('/\s*\(\d+\s*mins?\.?\)/u', '', $titulo);
            } else {
                $next = $el->nextSibling;
                while ($next && $next->nodeType !== XML_ELEMENT_NODE) $next = $next->nextSibling;
                if ($next && preg_match('/\((\d+)\s*mins?\.?\)/iu', $next->textContent, $dMatch)) {
                    $duracion = (int) $dMatch[1];
                }
            }

            $titulo = trim($titulo);

            // Ignorar canticos, oraciones, conclusiones, introducciones
            if (preg_match('/^(Canción|Cancion|Song|Oración|Palabras de conclus|Palabras de introduc|Comentarios? iniciales?)/iu', $titulo)) continue;
            if (preg_match('/canci[oó]n.*oraci[oó]n|oraci[oó]n.*canci[oó]n/iu', $titulo)) continue;
            if (empty($titulo)) continue;

            $tipo = $this->clasificarTipo($seccionActual, $titulo, $duracion);
            if (!$tipo) continue;

            $partes[] = [
                'seccion' => $seccionActual,
                'tipo' => $tipo,
                'titulo' => $titulo,
                'duracion' => $duracion,
                'necesita_ayudante' => in_array($tipo, [
                    'empiece_conversaciones', 'haga_revisitas', 'haga_discipulos', 'explique_creencias'
                ]),
            ];
        }

        return $partes;
    }

    private function clasificarTipo(string $seccion, string $titulo, int $duracion): ?string
    {
        if ($seccion === 'tesoros') {
            if (preg_match('/perlas escondidas|busquemos perlas/iu', $titulo)) return 'perlas';
            if (preg_match('/lectura de la biblia/iu', $titulo)) return 'lectura';
            return 'discurso_tesoros';
        }

        if ($seccion === 'maestros') {
            if (preg_match('/empiece conversacion/iu', $titulo)) return 'empiece_conversaciones';
            if (preg_match('/haga revisita/iu', $titulo)) return 'haga_revisitas';
            if (preg_match('/haga disc[ií]pulo/iu', $titulo)) return 'haga_discipulos';
            if (preg_match('/explique.*creencia/iu', $titulo)) return 'explique_creencias';
            // Parte generica de maestros - clasificar por duracion
            if ($duracion <= 3) return 'empiece_conversaciones';
            if ($duracion <= 4) return 'haga_revisitas';
            return 'haga_discipulos';
        }

        if ($seccion === 'vida_cristiana') {
            if (preg_match('/estudio b[ií]blico de la congregaci/iu', $titulo)) return null;
            return 'discurso_vida';
        }

        return null;
    }
}
