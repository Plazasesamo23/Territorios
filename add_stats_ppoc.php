<?php
$file = file_get_contents("app/Http/Controllers/TurnoController.php");

// Verificar si ya existe
if (strpos($file, "estadisticasAsignaciones") !== false) {
    echo "El sistema de estadisticas ya existe\n";
    exit;
}

// Buscar donde agregar el calculo de estadisticas en calendario()
// Despues de "$publicadores = Publicador::where" agregar calculo de estadisticas

$searchCode = // Generar estructura del calendario;
$statsCode = "
        // Calcular estadisticas de asignaciones del mes
        \$estadisticasAsignaciones = \$this->calcularEstadisticasAsignaciones(\$congregacion->id, \$year, \$month, \$publicadores);

        // Generar estructura del calendario";

$file = str_replace("// Generar estructura del calendario", \$statsCode, \$file);

// Buscar el return de calendario() y agregar la variable
$searchReturn = "compact(\"semanas\", \"turnosGenerados\", \"publicadores\", \"plantillas\", \"year\", \"month\", \"nombreMes\", \"hoy\", \"prevMonth\", \"nextMonth\")";
$newReturn = "compact(\"semanas\", \"turnosGenerados\", \"publicadores\", \"plantillas\", \"year\", \"month\", \"nombreMes\", \"hoy\", \"prevMonth\", \"nextMonth\", \"estadisticasAsignaciones\")";
$file = str_replace(\$searchReturn, \$newReturn, \$file);

// Agregar el metodo de calculo de estadisticas antes del ultimo }
$statsMethod = "

    /**
     * Calcular estadisticas de asignaciones para el mes
     */
    private function calcularEstadisticasAsignaciones(\$congregacionId, \$year, \$month, \$publicadores)
    {
        // Obtener todas las asignaciones del mes
        \$asignaciones = TurnoAsignacion::whereHas(\"turnoGenerado\", function(\$q) use (\$congregacionId, \$year, \$month) {
            \$q->where(\"congregacion_id\", \$congregacionId)
              ->whereYear(\"fecha\", \$year)
              ->whereMonth(\"fecha\", \$month);
        })->get()->groupBy(\"publicador_id\");

        // Separar precursores y publicadores normales
        \$precursores = [];
        \$publicadoresNormales = [];

        foreach (\$publicadores as \$pub) {
            \$count = isset(\$asignaciones[\$pub->id]) ? \$asignaciones[\$pub->id]->count() : 0;
            \$data = [
                \"id\" => \$pub->id,
                \"nombre\" => \$pub->nombre . \" \" . \$pub->apellidos,
                \"asignaciones\" => \$count,
                \"es_precursor\" => \$pub->es_precursor
            ];
            
            if (\$pub->es_precursor) {
                \$precursores[] = \$data;
            } else {
                \$publicadoresNormales[] = \$data;
            }
        }

        // Calcular promedios
        \$totalAsigPrecursores = array_sum(array_column(\$precursores, \"asignaciones\"));
        \$totalAsigPublicadores = array_sum(array_column(\$publicadoresNormales, \"asignaciones\"));
        
        \$mediaPrecursores = count(\$precursores) > 0 ? \$totalAsigPrecursores / count(\$precursores) : 0;
        \$mediaPublicadores = count(\$publicadoresNormales) > 0 ? \$totalAsigPublicadores / count(\$publicadoresNormales) : 0;

        // Clasificar por color (verde, amarillo, rojo)
        \$clasificados = [
            \"rojos\" => [],    // Problemas graves
            \"amarillos\" => [],  // Atencion
            \"verdes\" => []    // OK
        ];

        // Precursores: deben tener mas que la media de publicadores
        foreach (\$precursores as \$p) {
            if (\$p[\"asignaciones\"] < \$mediaPublicadores) {
                // Precursor con menos que la media de publicadores = ROJO
                \$p[\"motivo\"] = \"Precursor con menos turnos que la media de publicadores (\" . round(\$mediaPublicadores, 1) . \")\";
                \$clasificados[\"rojos\"][] = \$p;
            } elseif (\$p[\"asignaciones\"] < \$mediaPrecursores) {
                // Precursor por debajo de la media de precursores = AMARILLO
                \$p[\"motivo\"] = \"Por debajo de la media de precursores (\" . round(\$mediaPrecursores, 1) . \")\";
                \$clasificados[\"amarillos\"][] = \$p;
            } else {
                // Precursor OK
                \$p[\"motivo\"] = \"OK (\" . \$p[\"asignaciones\"] . \" turnos)\";
                \$clasificados[\"verdes\"][] = \$p;
            }
        }

        // Publicadores: no deben tener mas que la media de precursores
        foreach (\$publicadoresNormales as \$p) {
            if (\$p[\"asignaciones\"] > \$mediaPrecursores && count(\$precursores) > 0) {
                // Publicador con mas que la media de precursores = ROJO
                \$p[\"motivo\"] = \"Publicador con mas turnos que la media de precursores (\" . round(\$mediaPrecursores, 1) . \")\";
                \$clasificados[\"rojos\"][] = \$p;
            } elseif (\$p[\"asignaciones\"] > \$mediaPublicadores) {
                // Publicador por encima de la media de publicadores = AMARILLO
                \$p[\"motivo\"] = \"Por encima de la media de publicadores (\" . round(\$mediaPublicadores, 1) . \")\";
                \$clasificados[\"amarillos\"][] = \$p;
            } else {
                // Publicador OK
                \$p[\"motivo\"] = \"OK (\" . \$p[\"asignaciones\"] . \" turnos)\";
                \$clasificados[\"verdes\"][] = \$p;
            }
        }

        // Ordenar por numero de asignaciones
        usort(\$clasificados[\"rojos\"], fn(\$a, \$b) => \$b[\"asignaciones\"] <=> \$a[\"asignaciones\"]);
        usort(\$clasificados[\"amarillos\"], fn(\$a, \$b) => \$b[\"asignaciones\"] <=> \$a[\"asignaciones\"]);
        usort(\$clasificados[\"verdes\"], fn(\$a, \$b) => \$b[\"asignaciones\"] <=> \$a[\"asignaciones\"]);

        return [
            \"mediaPrecursores\" => round(\$mediaPrecursores, 1),
            \"mediaPublicadores\" => round(\$mediaPublicadores, 1),
            \"totalPrecursores\" => count(\$precursores),
            \"totalPublicadores\" => count(\$publicadoresNormales),
            \"clasificados\" => \$clasificados
        ];
    }
";

// Encontrar la ultima llave de cierre de la clase y agregar antes
$lastBrace = strrpos($file, "}");
if ($lastBrace !== false) {
    $file = substr($file, 0, $lastBrace) . $statsMethod . substr($file, $lastBrace);
}

file_put_contents("app/Http/Controllers/TurnoController.php", $file);
echo "TurnoController modificado con estadisticas\n";
