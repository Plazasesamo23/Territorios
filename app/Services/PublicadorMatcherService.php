<?php

namespace App\Services;

use App\Models\Publicador;
use Illuminate\Support\Collection;

class PublicadorMatcherService
{
    /**
     * Buscar publicador con matching flexible
     * Retorna: ['publicador' => Publicador|null, 'confidence' => float, 'candidates' => Collection]
     */
    public function findMatch(string $nombreBuscado, ?int $congregacionId = null): array
    {
        $nombreNormalizado = $this->normalizar($nombreBuscado);

        // Obtener publicadores (filtrar por congregación si aplica)
        $query = Publicador::query();
        if ($congregacionId) {
            $query->where('congregacion_id', $congregacionId);
        }
        $publicadores = $query->get();

        $mejorMatch = null;
        $mejorScore = 0;
        $candidatos = collect();

        foreach ($publicadores as $publicador) {
            $nombreCompleto = $this->normalizar($publicador->nombre . ' ' . $publicador->apellidos);
            $soloNombre = $this->normalizar($publicador->nombre);

            // Calcular scores con diferentes métodos
            $scoreCompleto = $this->calcularSimilitud($nombreNormalizado, $nombreCompleto);
            $scoreNombre = $this->calcularSimilitud($nombreNormalizado, $soloNombre);
            $scoreContiene = $this->contieneNombre($nombreNormalizado, $nombreCompleto);

            $score = max($scoreCompleto, $scoreNombre, $scoreContiene);

            if ($score >= 0.6) { // Umbral mínimo para ser candidato
                $candidatos->push([
                    'publicador' => $publicador,
                    'score' => $score,
                ]);
            }

            if ($score > $mejorScore) {
                $mejorScore = $score;
                $mejorMatch = $publicador;
            }
        }

        // Ordenar candidatos por score descendente y tomar top 5
        $candidatos = $candidatos->sortByDesc('score')->take(5)->values();

        return [
            'publicador' => $mejorScore >= 0.85 ? $mejorMatch : null, // Match automático solo si >85%
            'confidence' => $mejorScore,
            'candidates' => $candidatos,
        ];
    }

    /**
     * Normalizar texto: sin acentos, minúsculas, sin espacios extra
     */
    public function normalizar(string $texto): string
    {
        $texto = mb_strtolower(trim($texto));

        // Remover acentos
        $acentos = [
            'a' => '/[àáâãäå]/u',
            'e' => '/[èéêë]/u',
            'i' => '/[ìíîï]/u',
            'o' => '/[òóôõö]/u',
            'u' => '/[ùúûü]/u',
            'n' => '/[ñ]/u',
            'c' => '/[ç]/u',
        ];

        foreach ($acentos as $reemplazo => $patron) {
            $texto = preg_replace($patron, $reemplazo, $texto);
        }

        // Remover espacios múltiples
        return preg_replace('/\s+/', ' ', $texto);
    }

    /**
     * Calcular similitud usando similar_text y levenshtein
     */
    private function calcularSimilitud(string $str1, string $str2): float
    {
        if ($str1 === $str2) {
            return 1.0;
        }

        if (empty($str1) || empty($str2)) {
            return 0.0;
        }

        // Método 1: similar_text
        similar_text($str1, $str2, $percent);
        $similarScore = $percent / 100;

        // Método 2: Levenshtein (distancia de edición)
        $maxLen = max(strlen($str1), strlen($str2));
        if ($maxLen > 255) {
            // Levenshtein tiene límite de 255 caracteres
            return $similarScore;
        }

        $levenshtein = levenshtein($str1, $str2);
        $levenshteinScore = $maxLen > 0 ? 1 - ($levenshtein / $maxLen) : 0;

        // Promedio de ambos métodos
        return ($similarScore + $levenshteinScore) / 2;
    }

    /**
     * Verificar si el nombre buscado está contenido en el nombre completo
     */
    private function contieneNombre(string $buscado, string $completo): float
    {
        if (empty($buscado) || empty($completo)) {
            return 0.0;
        }

        // Coincidencia exacta de contenido
        if (str_contains($completo, $buscado)) {
            return 0.9;
        }
        if (str_contains($buscado, $completo)) {
            return 0.85;
        }

        // Verificar palabras individuales
        $palabrasBuscadas = explode(' ', $buscado);
        $palabrasCompleto = explode(' ', $completo);

        $coincidencias = 0;
        foreach ($palabrasBuscadas as $palabra) {
            if (strlen($palabra) > 2 && in_array($palabra, $palabrasCompleto)) {
                $coincidencias++;
            }
        }

        return count($palabrasBuscadas) > 0
            ? ($coincidencias / count($palabrasBuscadas)) * 0.8
            : 0;
    }
}
