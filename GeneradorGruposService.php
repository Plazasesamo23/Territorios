<?php

namespace App\Services;

use App\Models\Publicador;
use App\Models\GrupoPredicacion;
use App\Models\GrupoHistorico;
use Illuminate\Support\Collection;

class GeneradorGruposService
{
    protected $congregacionId;
    protected $coincidencias = [];

    public function __construct($congregacionId)
    {
        $this->congregacionId = $congregacionId;
        $this->cargarCoincidencias();
    }

    protected function cargarCoincidencias()
    {
        // Cargar historial de ultimos 3 anos
        $anosConsiderar = $this->getUltimos3Anos();

        $historico = GrupoHistorico::where('congregacion_id', $this->congregacionId)
            ->whereIn('ano_servicio', $anosConsiderar)
            ->get()
            ->groupBy('ano_servicio');

        foreach ($historico as $ano => $registros) {
            $porGrupo = $registros->groupBy('grupo_numero');

            foreach ($porGrupo as $grupo => $miembros) {
                $ids = $miembros->pluck('publicador_id')->toArray();

                // Registrar todas las parejas que coincidieron
                for ($i = 0; $i < count($ids); $i++) {
                    for ($j = $i + 1; $j < count($ids); $j++) {
                        $clave = $this->clavePareja($ids[$i], $ids[$j]);
                        if (!isset($this->coincidencias[$clave])) {
                            $this->coincidencias[$clave] = [];
                        }
                        $this->coincidencias[$clave][$ano] = true;
                    }
                }
            }
        }
    }

    protected function getUltimos3Anos()
    {
        $actual = $this->getAnoServicioActual();
        $anos = [$actual];

        // Calcular anos anteriores
        preg_match('/(\d{4})/', $actual, $m);
        $inicio = (int)$m[1];

        for ($i = 1; $i <= 3; $i++) {
            $anos[] = ($inicio - $i) . '/' . ($inicio - $i + 1);
        }

        return $anos;
    }

    public function getAnoServicioActual()
    {
        $mes = now()->month;
        $ano = now()->year;

        if ($mes >= 9) {
            return $ano . '/' . ($ano + 1);
        }
        return ($ano - 1) . '/' . $ano;
    }

    protected function clavePareja($id1, $id2)
    {
        return min($id1, $id2) . '-' . max($id1, $id2);
    }

    public function generar()
    {
        // 1. Obtener publicadores activos
        $publicadores = Publicador::where('congregacion_id', $this->congregacionId)
            ->where('activo', true)
            ->get();

        // 2. Separar por roles (SUP y AUX se mantienen)
        $conRolFijo = $publicadores->filter(fn($p) => $p->es_superintendente || $p->es_auxiliar);
        $precursores = $publicadores->filter(fn($p) => $p->es_precursor && !$p->es_superintendente && !$p->es_auxiliar);
        $normales = $publicadores->diff($conRolFijo)->diff($precursores);

        // 3. Inicializar 6 grupos
        $grupos = [];
        for ($i = 1; $i <= 6; $i++) {
            $grupos[$i] = collect();
        }

        // 4. Colocar SUP/AUX en sus grupos actuales
        foreach ($conRolFijo as $pub) {
            if ($pub->grupo_predicacion_id) {
                $grupo = GrupoPredicacion::find($pub->grupo_predicacion_id);
                if ($grupo) {
                    $grupos[$grupo->numero]->push($pub);
                }
            }
        }

        // 5. Distribuir precursores equitativamente
        $precursoresPorGrupo = ceil($precursores->count() / 6);
        foreach ($precursores->shuffle() as $pub) {
            $grupoOptimo = $this->encontrarGrupoOptimo($pub, $grupos, $precursoresPorGrupo);
            $grupos[$grupoOptimo]->push($pub);
        }

        // 6. Distribuir normales
        foreach ($normales->shuffle() as $pub) {
            $grupoOptimo = $this->encontrarGrupoOptimo($pub, $grupos);
            $grupos[$grupoOptimo]->push($pub);
        }

        return $grupos;
    }

    protected function encontrarGrupoOptimo($publicador, $grupos, $limitePrecursores = null)
    {
        $puntuaciones = [];
        $anoActual = $this->getAnoServicioActual();

        foreach ($grupos as $numGrupo => $miembros) {
            $penalizacion = 0;

            // Penalizar si es precursor y grupo ya tiene muchos
            if ($limitePrecursores && $publicador->es_precursor) {
                $precursoresEnGrupo = $miembros->filter(fn($m) => $m->es_precursor)->count();
                if ($precursoresEnGrupo >= $limitePrecursores) {
                    $penalizacion += 100;
                }
            }

            // Penalizacion por coincidencias historicas
            foreach ($miembros as $miembro) {
                $clave = $this->clavePareja($publicador->id, $miembro->id);
                if (isset($this->coincidencias[$clave])) {
                    foreach ($this->coincidencias[$clave] as $ano => $coincidio) {
                        $anosAtras = $this->diferenciaAnos($anoActual, $ano);
                        // Peso: actual=10, -1ano=7, -2anos=4, -3anos=2
                        $peso = max(0, 10 - ($anosAtras * 3));
                        $penalizacion += $peso;
                    }
                }
            }

            // Penalizar grupos grandes
            $penalizacion += $miembros->count() * 2;

            $puntuaciones[$numGrupo] = $penalizacion;
        }

        return array_search(min($puntuaciones), $puntuaciones);
    }

    protected function diferenciaAnos($actual, $anterior)
    {
        preg_match('/(\d{4})/', $actual, $m1);
        preg_match('/(\d{4})/', $anterior, $m2);
        return (int)$m1[1] - (int)$m2[1];
    }
}
