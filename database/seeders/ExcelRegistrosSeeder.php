<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Territorio;
use App\Models\Publicador;
use App\Models\Registro;
use Carbon\Carbon;

class ExcelRegistrosSeeder extends Seeder
{
    public function run()
    {
        echo "🚀 Procesando registros desde Excel...\n";
        
        // Limpiar registros existentes
        Registro::truncate();
        echo "✅ Registros anteriores eliminados\n";
        
        // Datos del Excel
        $registros = [
            ['territorio' => 1, 'publicador' => 'DAMARIS DE LOPEZ', 'salida' => '7/05/2025', 'entrada' => '25/6/2025', 'estado' => 'Archivo', 'entrada_prevista' => '4/9/2025'],
            ['territorio' => 2, 'publicador' => 'SEBASTIANA LOPEZ', 'salida' => '1/09/2025', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => '30/12/2025'],
            ['territorio' => 3, 'publicador' => 'JOSEFA DE MILÁN', 'salida' => '9/09/2025', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => '7/1/2026'],
            ['territorio' => 4, 'publicador' => 'BENJAMIN ABARCA', 'salida' => '7/9/25', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => '5/1/2026'],
            ['territorio' => 5, 'publicador' => 'JOSEFA DE MILÁN', 'salida' => '25/06/2025', 'entrada' => '3/7/2025', 'estado' => 'Archivo', 'entrada_prevista' => '23/10/2025'],
            ['territorio' => 6, 'publicador' => 'JOSÉ M NOGUERA', 'salida' => '20/05/2025', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => '17/9/2025'],
            ['territorio' => 7, 'publicador' => 'DIRCILENE DA SILVA', 'salida' => '6/03/2025', 'entrada' => '15/7/2025', 'estado' => 'Archivo', 'entrada_prevista' => '3/7/2025'],
            ['territorio' => 8, 'publicador' => 'NUNCI CRUZ', 'salida' => '31/08/2025', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => null],
            ['territorio' => 9, 'publicador' => 'MANOLO ORTIZ', 'salida' => '25/06/2025', 'entrada' => '2/9/2025', 'estado' => 'Archivo', 'entrada_prevista' => '23/10/2025'],
            ['territorio' => 10, 'publicador' => 'NUNCI CRUZ', 'salida' => '31/08/2025', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => '29/12/2025'],
            ['territorio' => 11, 'publicador' => 'MAIRA DE MATEOS', 'salida' => '1/09/2025', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => '30/12/2025'],
            ['territorio' => 12, 'publicador' => 'ANTONIA MORALES', 'salida' => '27/04/2025', 'entrada' => null, 'estado' => 'Fuera de plazo', 'entrada_prevista' => '25/8/2025'],
            ['territorio' => 13, 'publicador' => 'PAQUI HABAS', 'salida' => '1/07/2025', 'entrada' => '9/7/2025', 'estado' => 'Archivo', 'entrada_prevista' => '29/10/2025'],
            ['territorio' => 14, 'publicador' => 'JOSÉ M NOGUERA', 'salida' => '20/05/2025', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => '17/9/2025'],
            ['territorio' => 15, 'publicador' => 'MANOLO ORTIZ', 'salida' => '25/6/25', 'entrada' => '28/8/2025', 'estado' => 'Archivo', 'entrada_prevista' => '23/10/2025'],
            ['territorio' => 16, 'publicador' => 'ANABEL DE BENÍTEZ', 'salida' => '29/03/2025', 'entrada' => '1/9/2025', 'estado' => 'Archivo', 'entrada_prevista' => '27/7/2025'],
            ['territorio' => 17, 'publicador' => 'FRANCISCO MUÑOZ', 'salida' => '30/06/2025', 'entrada' => '15/7/2025', 'estado' => 'Archivo', 'entrada_prevista' => '28/10/2025'],
            ['territorio' => 18, 'publicador' => 'KATA ABARCA', 'salida' => '24/06/2025', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => '22/10/2025'],
            ['territorio' => 19, 'publicador' => 'MANOLO ORTIZ', 'salida' => '2/09/2025', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => '31/12/2025'],
            ['territorio' => 20, 'publicador' => 'LIDIA DE ABARCA', 'salida' => '28/06/2025', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => '26/10/2025'],
            ['territorio' => 21, 'publicador' => 'NOEMÍ DE PAUNER', 'salida' => '13/03/2025', 'entrada' => '20/6/2025', 'estado' => 'Activo', 'entrada_prevista' => '11/7/2025'],
            ['territorio' => 22, 'publicador' => 'SEBASTIANA LOPEZ', 'salida' => '23/04/2025', 'entrada' => '8/5/2025', 'estado' => 'Pendiente de entrega', 'entrada_prevista' => '21/8/2025'],
            ['territorio' => 23, 'publicador' => 'AICHA FARAJ', 'salida' => '29/03/2025', 'entrada' => '7/5/2025', 'estado' => 'Pendiente de entrega', 'entrada_prevista' => '27/7/2025'],
            ['territorio' => 24, 'publicador' => 'AICHA FARAJ', 'salida' => '7/05/2025', 'entrada' => '5/7/2025', 'estado' => 'Archivo', 'entrada_prevista' => '4/9/2025'],
            ['territorio' => 25, 'publicador' => 'PAQUI HABAS', 'salida' => '21/06/25', 'entrada' => '1/7/2025', 'estado' => 'Archivo', 'entrada_prevista' => '19/10/2025'],
            ['territorio' => 26, 'publicador' => 'AICHA FARAJ', 'salida' => '7/05/2025', 'entrada' => '5/7/2025', 'estado' => 'Archivo', 'entrada_prevista' => '4/9/2025'],
            ['territorio' => 27, 'publicador' => 'MARI C DE CABALLER', 'salida' => '25/06/2025', 'entrada' => '8/7/2025', 'estado' => 'Archivo', 'entrada_prevista' => '23/10/2025'],
            ['territorio' => 28, 'publicador' => 'ANABEL DE BENÍTEZ', 'salida' => '10/09/2025', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => '8/1/2026'],
            ['territorio' => 29, 'publicador' => 'JESENIA ARIZAGA', 'salida' => '10/09/2025', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => '8/1/2026'],
            ['territorio' => 30, 'publicador' => 'BRYAN ANDRADE', 'salida' => '14/06/2025', 'entrada' => '1/7/2025', 'estado' => 'Archivo', 'entrada_prevista' => '12/10/2025'],
            ['territorio' => 31, 'publicador' => 'RAPHAELA RIVAS', 'salida' => '5/07/2025', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => '2/11/2025'],
            ['territorio' => 32, 'publicador' => 'TONI LÓPEZ', 'salida' => '14/06/2025', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => '12/10/2025'],
            ['territorio' => 33, 'publicador' => 'JUANI DE PULIDO', 'salida' => '23/06/2025', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => '21/10/2025'],
            ['territorio' => 34, 'publicador' => 'NUNCI CRUZ', 'salida' => '30/06/2025', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => '28/10/2025'],
            ['territorio' => 35, 'publicador' => 'SEBASTIANA LOPEZ', 'salida' => '27/06/2025', 'entrada' => '1/7/2025', 'estado' => 'Archivo', 'entrada_prevista' => '25/10/2025'],
            ['territorio' => 36, 'publicador' => 'PAQUI HABAS', 'salida' => '28/03/2025', 'entrada' => '9/7/2025', 'estado' => 'Archivo', 'entrada_prevista' => '26/7/2025'],
            ['territorio' => 37, 'publicador' => 'NUNCI CRUZ', 'salida' => '26/06/2025', 'entrada' => '30/6/2025', 'estado' => 'Archivo', 'entrada_prevista' => '24/10/2025'],
            ['territorio' => 38, 'publicador' => 'RAPHAELA RIVAS', 'salida' => '27/06/2025', 'entrada' => '6/7/2025', 'estado' => 'Archivo', 'entrada_prevista' => '25/10/2025'],
            ['territorio' => 39, 'publicador' => 'NOEMÍ DE PAUNER', 'salida' => '20-6-25', 'entrada' => '31/8/2025', 'estado' => 'Archivo', 'entrada_prevista' => '18/10/2025'],
            ['territorio' => 40, 'publicador' => 'MIGUEL PAUNER', 'salida' => '20/06/2025', 'entrada' => '31/8/2025', 'estado' => 'Archivo', 'entrada_prevista' => '18/10/2025'],
            ['territorio' => 41, 'publicador' => 'NELSON CICERY', 'salida' => '7/07/2025', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => '4/11/2025'],
            ['territorio' => 42, 'publicador' => 'MARI C DE CABALLER', 'salida' => '8/07/2025', 'entrada' => '2/9/2025', 'estado' => 'Archivo', 'entrada_prevista' => '5/11/2025'],
            ['territorio' => 43, 'publicador' => 'GLORIA LORENTE', 'salida' => '29/8/25', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => '27/12/2025'],
            ['territorio' => 44, 'publicador' => 'RAPHAELA RIVAS', 'salida' => '17/06/2025', 'entrada' => '27/6/2025', 'estado' => 'Archivo', 'entrada_prevista' => '15/10/2025'],
            ['territorio' => 45, 'publicador' => 'CARMEN DE ORTIZ', 'salida' => '31/03/2025', 'entrada' => '6/4/2025', 'estado' => 'Pendiente de entrega', 'entrada_prevista' => '29/7/2025'],
            ['territorio' => 46, 'publicador' => 'CARMEN DE ORTIZ', 'salida' => '6/04/2025', 'entrada' => '22/6/2025', 'estado' => 'Archivo', 'entrada_prevista' => '4/8/2025'],
            ['territorio' => 47, 'publicador' => 'RAPHAELA RIVAS', 'salida' => '20/05/2025', 'entrada' => '17/6/2025', 'estado' => 'Archivo', 'entrada_prevista' => '17/9/2025'],
            ['territorio' => 48, 'publicador' => 'LAUDE MANJON', 'salida' => '07/07/2025', 'entrada' => '1/9/2025', 'estado' => 'Archivo', 'entrada_prevista' => '4/11/2025'],
            ['territorio' => 49, 'publicador' => 'MARI C DE CABALLER', 'salida' => '3/09/2025', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => '1/1/2026'],
            ['territorio' => 50, 'publicador' => 'KATA ABARCA', 'salida' => '11/06/2025', 'entrada' => '23/6/2025', 'estado' => 'Archivo', 'entrada_prevista' => '9/10/2025'],
            ['territorio' => 51, 'publicador' => 'SANDRA DE MARTIN', 'salida' => '20/06/2025', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => '18/10/2025'],
            ['territorio' => 52, 'publicador' => 'TONI LÓPEZ', 'salida' => '29/03/2025', 'entrada' => '14/6/2025', 'estado' => 'Archivo', 'entrada_prevista' => '27/7/2025'],
            ['territorio' => 53, 'publicador' => 'MONICA FERRER', 'salida' => '31/03/2025', 'entrada' => null, 'estado' => 'Fuera de plazo', 'entrada_prevista' => '29/7/2025'],
            ['territorio' => 54, 'publicador' => 'LAUDE MANJON', 'salida' => '23/06/2025', 'entrada' => '3/7/2025', 'estado' => 'Archivo', 'entrada_prevista' => '21/10/2025'],
            ['territorio' => 55, 'publicador' => 'CARMEN DE ORTIZ', 'salida' => '22/06/2025', 'entrada' => '28/8/2025', 'estado' => 'Archivo', 'entrada_prevista' => '20/10/2025'],
            ['territorio' => 56, 'publicador' => 'FRANCISCO MUÑOZ', 'salida' => '11/06/2025', 'entrada' => '17/6/2025', 'estado' => 'Archivo', 'entrada_prevista' => '9/10/2025'],
            ['territorio' => 57, 'publicador' => 'NACHO PAUNER', 'salida' => '9/03/2025', 'entrada' => '22/6/2025', 'estado' => 'Archivo', 'entrada_prevista' => '7/7/2025'],
            ['territorio' => 58, 'publicador' => 'LAUDE MANJON', 'salida' => '23/06/2025', 'entrada' => '3/7/2025', 'estado' => 'Archivo', 'entrada_prevista' => '21/10/2025'],
            ['territorio' => 59, 'publicador' => 'NACHO PAUNER', 'salida' => '9/03/2025', 'entrada' => '22/6/2025', 'estado' => 'Archivo', 'entrada_prevista' => '7/7/2025'],
            ['territorio' => 60, 'publicador' => 'PAQUI HABAS', 'salida' => '9/07/2025', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => '6/11/2025'],
            ['territorio' => 61, 'publicador' => 'MAIRA DE MATEOS', 'salida' => '15/05/2025', 'entrada' => '1/8/2025', 'estado' => 'Archivo', 'entrada_prevista' => '12/9/2025'],
            ['territorio' => 62, 'publicador' => 'RAFA RIVERA', 'salida' => '15/03/2025', 'entrada' => '22/5/2025', 'estado' => 'Pendiente de entrega', 'entrada_prevista' => '13/7/2025'],
            ['territorio' => 63, 'publicador' => 'CLAUDIA SANCHEZ', 'salida' => '15/03/2025', 'entrada' => null, 'estado' => 'Fuera de plazo', 'entrada_prevista' => '13/7/2025'],
            ['territorio' => 64, 'publicador' => 'LIDIA DE ABARCA', 'salida' => '9/07/2025', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => '6/11/2025'],
            ['territorio' => 65, 'publicador' => 'MARIBEL DE MARTINEZ', 'salida' => '13/03/2025', 'entrada' => null, 'estado' => 'Fuera de plazo', 'entrada_prevista' => '11/7/2025'],
            ['territorio' => 66, 'publicador' => 'MARIA JOSE MARTINEZ', 'salida' => '13/03/2025', 'entrada' => null, 'estado' => 'Fuera de plazo', 'entrada_prevista' => '11/7/2025'],
            ['territorio' => 67, 'publicador' => 'LAUDE MANJON', 'salida' => '3/07/2025', 'entrada' => '7/7/2025', 'estado' => 'Archivo', 'entrada_prevista' => '31/10/2025'],
            ['territorio' => 68, 'publicador' => 'JUANI DE PULIDO', 'salida' => '14/02/2025', 'entrada' => '23/6/2025', 'estado' => 'Archivo', 'entrada_prevista' => '14/6/2025'],
            ['territorio' => 69, 'publicador' => 'NUNCI CRUZ', 'salida' => '17/06/2025', 'entrada' => '25/6/2025', 'estado' => 'Archivo', 'entrada_prevista' => '15/10/2025'],
            ['territorio' => 70, 'publicador' => 'NUNCI CRUZ', 'salida' => '8/05/2025', 'entrada' => '17/6/2025', 'estado' => 'Archivo', 'entrada_prevista' => '5/9/2025'],
            ['territorio' => 71, 'publicador' => 'LIDIA DE ABARCA', 'salida' => '31/05/2025', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => '28/9/2025'],
            ['territorio' => 72, 'publicador' => 'REBECA DE RIVERA', 'salida' => '9/07/2025', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => '6/11/2025'],
            ['territorio' => 73, 'publicador' => 'LOLI DE CORTES', 'salida' => '9/07/2025', 'entrada' => '21/7/2025', 'estado' => 'Archivo', 'entrada_prevista' => '6/11/2025'],
            ['territorio' => 74, 'publicador' => 'LOLI DE CORTES', 'salida' => '9/07/2025', 'entrada' => '21/7/2025', 'estado' => 'Archivo', 'entrada_prevista' => '6/11/2025'],
            ['territorio' => 75, 'publicador' => 'SANDRA DE MARTIN', 'salida' => '15/03/2025', 'entrada' => '20/6/2025', 'estado' => 'Archivo', 'entrada_prevista' => '13/7/2025'],
            ['territorio' => 76, 'publicador' => 'ALEJANDRO ABARCA', 'salida' => '7/02/2025', 'entrada' => '23/6/2025', 'estado' => 'Archivo', 'entrada_prevista' => '7/6/2025'],
            ['territorio' => 77, 'publicador' => 'ADHARA LOPEZ', 'salida' => '5/04/2025', 'entrada' => null, 'estado' => 'Fuera de plazo', 'entrada_prevista' => '3/8/2025', 'notas' => 'Lo terminará ella'],
            ['territorio' => 78, 'publicador' => 'LAUDE MANJON', 'salida' => '21/06/2025', 'entrada' => '23/6/2025', 'estado' => 'Archivo', 'entrada_prevista' => '19/10/2025'],
            ['territorio' => 79, 'publicador' => 'AICHA FARAJ', 'salida' => '29/03/2025', 'entrada' => '7/5/2025', 'estado' => 'Pendiente de entrega', 'entrada_prevista' => '27/7/2025'],
            ['territorio' => 80, 'publicador' => 'LOLI DE CORTES', 'salida' => '22/06/2025', 'entrada' => '1/7/2025', 'estado' => 'Archivo', 'entrada_prevista' => '20/10/2025'],
            ['territorio' => 81, 'publicador' => 'REBECA DE RIVERA', 'salida' => '2/06/2025', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => '30/9/2025'],
            ['territorio' => 82, 'publicador' => 'LOLI DE CORTES', 'salida' => '21/07/2025', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => '18/11/2025'],
            ['territorio' => 83, 'publicador' => 'NACHA ABARCAR', 'salida' => '5/4/2025', 'entrada' => '9/7/2025', 'estado' => 'Archivo', 'entrada_prevista' => '3/8/2025'],
            ['territorio' => 84, 'publicador' => 'LOLI DE CORTES', 'salida' => '21/07/2025', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => '18/11/2025'],
            ['territorio' => 85, 'publicador' => 'SARA LOPEZ', 'salida' => '2/04/2025', 'entrada' => '8/5/2025', 'estado' => 'Pendiente de entrega', 'entrada_prevista' => '31/7/2025'],
            ['territorio' => 86, 'publicador' => 'JOSE CORTES', 'salida' => '22/04/2025', 'entrada' => '8/7/2025', 'estado' => 'Archivo', 'entrada_prevista' => '20/8/2025'],
            ['territorio' => 87, 'publicador' => 'NORMA HOYOS', 'salida' => '5/07/2025', 'entrada' => '8/7/2025', 'estado' => 'Archivo', 'entrada_prevista' => '2/11/2025'],
            ['territorio' => 88, 'publicador' => 'MONICA DE ALVARADO', 'salida' => '22/03/2025', 'entrada' => '21/6/2025', 'estado' => 'Archivo', 'entrada_prevista' => '20/7/2025'],
            ['territorio' => 89, 'publicador' => 'OLEG POZNISHEV', 'salida' => '22/03/2025', 'entrada' => '30/5/2025', 'estado' => 'Pendiente de entrega', 'entrada_prevista' => '20/7/2025'],
            ['territorio' => 90, 'publicador' => 'TONI DE MAYORDOMO', 'salida' => '9/01/2025', 'entrada' => '20/5/2025', 'estado' => 'Pendiente de entrega', 'entrada_prevista' => '9/5/2025'],
            ['territorio' => 91, 'publicador' => 'NACHA ABARCAR', 'salida' => '9/07/2025', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => '6/11/2025'],
            ['territorio' => 92, 'publicador' => 'NORMA HOYOS', 'salida' => '6/04/2025', 'entrada' => '31/5/2025', 'estado' => 'Pendiente de entrega', 'entrada_prevista' => '4/8/2025'],
            ['territorio' => 93, 'publicador' => 'NACHA ABARCAR', 'salida' => '9/01/2025', 'entrada' => '5/4/2025', 'estado' => 'Pendiente de entrega', 'entrada_prevista' => '9/5/2025'],
            ['territorio' => 94, 'publicador' => 'SULMA DE MARROQ', 'salida' => '2/05/2025', 'entrada' => '1/8/2025', 'estado' => 'Archivo', 'entrada_prevista' => '30/8/2025'],
            ['territorio' => 95, 'publicador' => 'CRISTINA RODRIGUEZ', 'salida' => '7/4/25', 'entrada' => '2/5/2025', 'estado' => 'Pendiente de entrega', 'entrada_prevista' => '5/8/2025'],
            ['territorio' => 96, 'publicador' => 'DANIELA CASTRO', 'salida' => '21/06/2025', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => '19/10/2025'],
            ['territorio' => 97, 'publicador' => 'SULMA DE MARROQ', 'salida' => '21/06/2025', 'entrada' => '1/8/2025', 'estado' => 'Archivo', 'entrada_prevista' => '19/10/2025'],
            ['territorio' => 98, 'publicador' => 'JOSE CORTES', 'salida' => '8/07/2025', 'entrada' => '2/9/2025', 'estado' => 'Archivo', 'entrada_prevista' => '5/11/2025'],
            ['territorio' => 99, 'publicador' => 'LOLI DE CORTES', 'salida' => '1/07/2025', 'entrada' => '9/7/2025', 'estado' => 'Archivo', 'entrada_prevista' => '29/10/2025'],
            ['territorio' => 100, 'publicador' => 'NORMA HOYOS', 'salida' => '26/07/2025', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => '23/11/2025'],
            ['territorio' => 101, 'publicador' => 'MANOLO MATEOS', 'salida' => '22/05/2025', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => '19/9/2025'],
            ['territorio' => 102, 'publicador' => 'DAVID BENITEZ', 'salida' => '29/04/2025', 'entrada' => '1/9/2025', 'estado' => 'Archivo', 'entrada_prevista' => '27/8/2025'],
            ['territorio' => 103, 'publicador' => 'LAUDE MANJON', 'salida' => '21/06/2025', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => '19/10/2025'],
            ['territorio' => 104, 'publicador' => 'MARIA JOSE DE RODRIGUEZ', 'salida' => '18/06/2025', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => '16/10/2025'],
            ['territorio' => 105, 'publicador' => 'PATRICIA HERNÁNDEZ', 'salida' => '1/02/2025', 'entrada' => '1/5/2025', 'estado' => 'Pendiente de entrega', 'entrada_prevista' => '1/6/2025'],
            ['territorio' => 106, 'publicador' => 'NELSON CICERY', 'salida' => '12/05/2025', 'entrada' => null, 'estado' => 'Fuera de plazo', 'entrada_prevista' => '9/9/2025'],
            ['territorio' => 107, 'publicador' => 'LAUDE MANJON', 'salida' => '28/05/2025', 'entrada' => '7/7/2025', 'estado' => 'Archivo', 'entrada_prevista' => '25/9/2025'],
            ['territorio' => 108, 'publicador' => 'PATRICIA HERNÁNDEZ', 'salida' => '17/07/2025', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => '4/11/2025'],
            ['territorio' => 109, 'publicador' => 'JESENIA ARIZAGA', 'salida' => '25/05/2025', 'entrada' => '15/7/2025', 'estado' => 'Archivo', 'entrada_prevista' => '22/9/2025'],
            ['territorio' => 110, 'publicador' => 'NOEMÍ DE PAUNER', 'salida' => '21/05/2025', 'entrada' => '20/6/2025', 'estado' => 'Archivo', 'entrada_prevista' => '18/9/2025'],
            ['territorio' => 111, 'publicador' => 'JOSE CORTES', 'salida' => '28/05/2025', 'entrada' => '8/7/2025', 'estado' => 'Archivo', 'entrada_prevista' => '25/9/2025'],
            ['territorio' => 112, 'publicador' => 'FRANCISCO MUÑOZ', 'salida' => '11/06/2025', 'entrada' => '18/5/2025', 'estado' => 'Pendiente de entrega', 'entrada_prevista' => '9/10/2025'],
            ['territorio' => 113, 'publicador' => 'NOEMÍ DE PAUNER', 'salida' => '31/08/2025', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => '29/12/2025'],
            ['territorio' => 114, 'publicador' => 'ZOILA CHÁVEZ', 'salida' => '26/07/2025', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => '23/11/2025'],
            ['territorio' => 115, 'publicador' => 'SEBASTIANA LOPEZ', 'salida' => '31/05/2025', 'entrada' => '27/6/2025', 'estado' => 'Archivo', 'entrada_prevista' => '28/9/2025'],
            ['territorio' => 116, 'publicador' => 'BENJAMIN ABARCA', 'salida' => '31/05/2025', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => '28/9/2025'],
            ['territorio' => 117, 'publicador' => 'NORMA HOYOS', 'salida' => '31/05/2025', 'entrada' => '25/6/2025', 'estado' => 'Archivo', 'entrada_prevista' => '28/9/2025'],
            ['territorio' => 118, 'publicador' => 'DIRCILENE DA SILVA', 'salida' => '29/05/2025', 'entrada' => '30/6/2025', 'estado' => 'Archivo', 'entrada_prevista' => '26/9/2025'],
            ['territorio' => 119, 'publicador' => 'LAURA DE LOPEZ', 'salida' => '14/06/2025', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => '12/10/2025'],
            ['territorio' => 120, 'publicador' => 'SEBASTIANA LOPEZ', 'salida' => '8/05/2025', 'entrada' => '31/5/2025', 'estado' => 'Pendiente de entrega', 'entrada_prevista' => '5/9/2025'],
            ['territorio' => 121, 'publicador' => 'CRISTINA RODRIGUEZ', 'salida' => '25/06/2025', 'entrada' => '3/7/2025', 'estado' => 'Archivo', 'entrada_prevista' => '23/10/2025'],
            ['territorio' => 122, 'publicador' => 'NATALIA DE ABARCA', 'salida' => '8/03/2025', 'entrada' => null, 'estado' => 'Fuera de plazo', 'entrada_prevista' => '6/7/2025'],
            ['territorio' => 123, 'publicador' => 'FRANCISCO MUÑOZ', 'salida' => '11/06/2025', 'entrada' => '18/6/2025', 'estado' => 'Archivo', 'entrada_prevista' => '9/10/2025'],
            ['territorio' => 124, 'publicador' => 'JOSÉ M NOGUERA', 'salida' => '15/03/2025', 'entrada' => '3/5/2025', 'estado' => 'Pendiente de entrega', 'entrada_prevista' => '13/7/2025'],
            ['territorio' => 125, 'publicador' => 'JULIO MARROQUIN', 'salida' => '18/06/2025', 'entrada' => '1/8/2025', 'estado' => 'Archivo', 'entrada_prevista' => '16/10/2025'],
            ['territorio' => 126, 'publicador' => 'JOSEFINA DEL RIO', 'salida' => '20/06/2025', 'entrada' => '20/8/2025', 'estado' => 'Archivo', 'entrada_prevista' => '18/10/2025'],
            ['territorio' => 127, 'publicador' => 'CARMEN DE ORTIZ', 'salida' => '6/07/2025', 'entrada' => '28/8/2025', 'estado' => 'Archivo', 'entrada_prevista' => '3/11/2025'],
            ['territorio' => 128, 'publicador' => 'MONICA DE ALVARADO', 'salida' => '21/06/2025', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => '19/10/2025'],
            ['territorio' => 129, 'publicador' => 'CRISTINA RODRIGUEZ', 'salida' => '19/06/2025', 'entrada' => '25/6/2025', 'estado' => 'Archivo', 'entrada_prevista' => '17/10/2025'],
            ['territorio' => 130, 'publicador' => 'CRISTINA RODRIGUEZ', 'salida' => '19/06/2025', 'entrada' => '3/7/2025', 'estado' => 'Archivo', 'entrada_prevista' => '17/10/2025'],
            ['territorio' => 131, 'publicador' => 'ESTABAN MAYORDO', 'salida' => '22/03/2025', 'entrada' => null, 'estado' => 'Fuera de plazo', 'entrada_prevista' => '20/7/2025'],
            ['territorio' => 132, 'publicador' => 'LOLI DE CORTES', 'salida' => '1/07/2025', 'entrada' => '9/7/2025', 'estado' => 'Archivo', 'entrada_prevista' => '29/10/2025'],
            ['territorio' => 133, 'publicador' => 'RAFA RIVERA', 'salida' => '1/07/2025', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => '29/10/2025'],
            ['territorio' => 134, 'publicador' => 'NACHO PAUNER', 'salida' => '22/06/2025', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => '20/10/2025'],
            ['territorio' => 135, 'publicador' => 'RAFA RIVERA', 'salida' => '22/03/2025', 'entrada' => '20/4/2025', 'estado' => 'Pendiente de entrega', 'entrada_prevista' => '20/7/2025'],
            ['territorio' => 136, 'publicador' => 'MONTSE DE FUENTES', 'salida' => '29/06/2025', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => '26/10/2025'],
            ['territorio' => 137, 'publicador' => 'CARMEN DE ORTIZ', 'salida' => '29/08/2025', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => '27/12/2025'],
            ['territorio' => 138, 'publicador' => 'LOLI DE CORTES', 'salida' => '29/03/2025', 'entrada' => '22/6/2025', 'estado' => 'Archivo', 'entrada_prevista' => '27/7/2025'],
            ['territorio' => 139, 'publicador' => 'REBECA DE RIVERA', 'salida' => '19/07/2025', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => '16/11/2025'],
            ['territorio' => 140, 'publicador' => 'JOSE MARTINEZ', 'salida' => '9/08/2025', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => '7/12/2025'],
            ['territorio' => 141, 'publicador' => 'JESENIA ARIZAGA', 'salida' => '14/03/2025', 'entrada' => '25/5/2025', 'estado' => 'Pendiente de entrega', 'entrada_prevista' => '12/7/2025'],
            ['territorio' => 142, 'publicador' => 'NACHO PAUNER', 'salida' => '22/06/2025', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => '20/10/2025'],
            ['territorio' => 143, 'publicador' => 'OLEG POZNISHEV', 'salida' => '30/05/2025', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => '27/9/2025'],
            ['territorio' => 144, 'publicador' => 'LAUDE MANJON', 'salida' => '21/06/2025', 'entrada' => '7/7/2025', 'estado' => 'Archivo', 'entrada_prevista' => '19/10/2025'],
            ['territorio' => 145, 'publicador' => 'PETRI HABAS', 'salida' => '10/09/2025', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => '8/1/2026'],
            ['territorio' => 146, 'publicador' => 'NORMA HOYOS', 'salida' => '13/03/2025', 'entrada' => '19/3/2025', 'estado' => 'Pendiente de entrega', 'entrada_prevista' => '11/7/2025'],
            ['territorio' => 147, 'publicador' => 'FRANCISCO MUÑOZ', 'salida' => '18/06/2025', 'entrada' => '30/6/2025', 'estado' => 'Archivo', 'entrada_prevista' => '16/10/2025'],
            ['territorio' => 148, 'publicador' => 'FRANCISCO MUÑOZ', 'salida' => '18/06/2025', 'entrada' => '30/6/2025', 'estado' => 'Archivo', 'entrada_prevista' => '16/10/2025'],
            ['territorio' => 149, 'publicador' => 'AMPARO SÁNCHEZ', 'salida' => '20/05/2025', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => '17/9/2025'],
            ['territorio' => 150, 'publicador' => 'CRISTINA RODRIGUEZ', 'salida' => '3/07/2025', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => '31/10/2025'],
            ['territorio' => 151, 'publicador' => 'MANOLO ORTIZ', 'salida' => '22/03/2025', 'entrada' => '1/5/2025', 'estado' => 'Pendiente de entrega', 'entrada_prevista' => '20/7/2025'],
            ['territorio' => 152, 'publicador' => 'ADRIÁN RIBERA', 'salida' => '20/03/2025', 'entrada' => '29/4/2025', 'estado' => 'Pendiente de entrega', 'entrada_prevista' => '18/7/2025'],
            ['territorio' => 153, 'publicador' => 'PAQUI HABAS', 'salida' => '19/03/2025', 'entrada' => '1/7/2025', 'estado' => 'Archivo', 'entrada_prevista' => '17/7/2025'],
            ['territorio' => 154, 'publicador' => 'NORMA HOYOS', 'salida' => '19/03/2025', 'entrada' => '1/4/2025', 'estado' => 'Pendiente de entrega', 'entrada_prevista' => '17/7/2025'],
            ['territorio' => 155, 'publicador' => 'FRANCISCO MUÑOZ', 'salida' => '18/06/2025', 'entrada' => '30/6/2025', 'estado' => 'Archivo', 'entrada_prevista' => '16/10/2025'],
            ['territorio' => 156, 'publicador' => 'SEBASTIANA LOPEZ', 'salida' => '3/07/2025', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => '31/10/2025'],
            ['territorio' => 157, 'publicador' => 'REBECA DE RIVERA', 'salida' => '18/03/2025', 'entrada' => '7/4/2025', 'estado' => 'Pendiente de entrega', 'entrada_prevista' => '16/7/2025'],
            ['territorio' => 158, 'publicador' => 'CRISTINA RODRIGUEZ', 'salida' => '3/07/2025', 'entrada' => '9/7/2025', 'estado' => 'Archivo', 'entrada_prevista' => '31/10/2025'],
            ['territorio' => 159, 'publicador' => 'MARI CARMEN DE LOPEZ', 'salida' => '28/03/2025', 'entrada' => null, 'estado' => 'Fuera de plazo', 'entrada_prevista' => '26/7/2025'],
            ['territorio' => 160, 'publicador' => 'MARIA JOSE DEMUÑOZ', 'salida' => '24/04/2025', 'entrada' => '28/8/2025', 'estado' => 'Archivo', 'entrada_prevista' => '22/8/2025'],
            ['territorio' => 161, 'publicador' => 'FERNANDO LOPEZ', 'salida' => '13/03/2025', 'entrada' => null, 'estado' => 'Fuera de plazo', 'entrada_prevista' => '11/7/2025'],
            ['territorio' => 162, 'publicador' => 'JOSE MARTINEZ', 'salida' => '7/04/2025', 'entrada' => '21/6/2025', 'estado' => 'Archivo', 'entrada_prevista' => '5/8/2025'],
            ['territorio' => 163, 'publicador' => 'FRANCISCO MUÑOZ', 'salida' => '13/03/2025', 'entrada' => '2/5/2025', 'estado' => 'Pendiente de entrega', 'entrada_prevista' => '11/7/2025'],
            ['territorio' => 164, 'publicador' => 'FRANCISCO MUÑOZ', 'salida' => '2/05/2025', 'entrada' => '11/6/2025', 'estado' => 'Pendiente de entrega', 'entrada_prevista' => '30/8/2025'],
            ['territorio' => 165, 'publicador' => 'ROSALÍA GARRIDO', 'salida' => '31/05/2025', 'entrada' => '9/8/2025', 'estado' => 'Archivo', 'entrada_prevista' => '28/9/2025'],
            ['territorio' => 166, 'publicador' => 'DANIELA CASTRO', 'salida' => '9/04/2025', 'entrada' => '21/6/2025', 'estado' => 'Archivo', 'entrada_prevista' => '7/8/2025'],
            ['territorio' => 167, 'publicador' => 'CRISTINA RODRIGUEZ', 'salida' => '9/07/2025', 'entrada' => '1/9/2025', 'estado' => 'Archivo', 'entrada_prevista' => '6/11/2025'],
            ['territorio' => 168, 'publicador' => 'PATRICIA SOUZA', 'salida' => '26/04/2025', 'entrada' => '9/8/25', 'estado' => 'Archivo', 'entrada_prevista' => '24/8/2025'],
            ['territorio' => 169, 'publicador' => 'FRANCISCO MUÑOZ', 'salida' => '13/03/2025', 'entrada' => '2/5/2025', 'estado' => 'Pendiente de entrega', 'entrada_prevista' => '11/7/2025'],
            ['territorio' => 170, 'publicador' => 'FRANCISCO MUÑOZ', 'salida' => '2/05/2025', 'entrada' => '11/6/2025', 'estado' => 'Pendiente de entrega', 'entrada_prevista' => '30/8/2025'],
            ['territorio' => 171, 'publicador' => 'LAUDE MANJON', 'salida' => '21/06/2025', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => '19/10/2025'],
            ['territorio' => 172, 'publicador' => 'JOSE ANTONIO TENORIO', 'salida' => '7/04/2025', 'entrada' => null, 'estado' => 'Fuera de plazo', 'entrada_prevista' => '5/8/2025'],
            ['territorio' => 173, 'publicador' => 'ROSALÍA GARRIDO', 'salida' => '9/08/2025', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => '7/12/2025'],
            ['territorio' => 174, 'publicador' => 'KATA ABARCA', 'salida' => '3/09/2025', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => '1/1/2026'],
            ['territorio' => 175, 'publicador' => 'FRANCISCO MUÑOZ', 'salida' => '13/03/2025', 'entrada' => '2/5/2025', 'estado' => 'Pendiente de entrega', 'entrada_prevista' => '11/7/2025'],
            ['territorio' => 176, 'publicador' => 'FRANCISCO MUÑOZ', 'salida' => '10/09/2025', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => '8/1/2026'],
            ['territorio' => 177, 'publicador' => 'KATA ABARCA', 'salida' => '22/03/2025', 'entrada' => '24/6/2025', 'estado' => 'Archivo', 'entrada_prevista' => '20/7/2025'],
            ['territorio' => 178, 'publicador' => 'LAUDE MANJON', 'salida' => '1/09/2025', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => '30/12/2025'],
            ['territorio' => 179, 'publicador' => 'LAUDE MANJON', 'salida' => '1/09/2025', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => '30/12/2025'],
            ['territorio' => 180, 'publicador' => 'FRANCISCO MUÑOZ', 'salida' => '10/09/2025', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => '8/1/2026'],
            ['territorio' => 181, 'publicador' => 'REBECA DE RIVERA', 'salida' => '19/07/2025', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => '16/11/2025'],
            ['territorio' => 182, 'publicador' => 'FRANCISCO MUÑOZ', 'salida' => '5/09/2025', 'entrada' => '10/9/2025', 'estado' => 'Archivo', 'entrada_prevista' => '3/1/2026'],
            ['territorio' => 183, 'publicador' => 'FRANCISCO MUÑOZ', 'salida' => '22/07/2025', 'entrada' => '30/8/2025', 'estado' => 'Archivo', 'entrada_prevista' => '19/11/2025'],
            ['territorio' => 184, 'publicador' => 'IKER LÓPEZ', 'salida' => '2/08/2025', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => '30/11/2025'],
            ['territorio' => 185, 'publicador' => 'FRANCISCO MUÑOZ', 'salida' => '5/09/2025', 'entrada' => '10/9/2025', 'estado' => 'Archivo', 'entrada_prevista' => '3/1/2026'],
            ['territorio' => 186, 'publicador' => 'FRANCISCO MUÑOZ', 'salida' => '5/09/2025', 'entrada' => '10/9/2025', 'estado' => 'Archivo', 'entrada_prevista' => '3/1/2026'],
            ['territorio' => 187, 'publicador' => 'MARI C DE CABALLER', 'salida' => '6/05/2025', 'entrada' => '25/6/25', 'estado' => 'Archivo', 'entrada_prevista' => '3/9/2025'],
            ['territorio' => 188, 'publicador' => 'RAFA RIVERA', 'salida' => '22/05/2025', 'entrada' => '19/7/2025', 'estado' => 'Archivo', 'entrada_prevista' => '19/9/2025'],
            ['territorio' => 189, 'publicador' => 'FRANCISCO MUÑOZ', 'salida' => '22/07/2025', 'entrada' => '30/8/2025', 'estado' => 'Archivo', 'entrada_prevista' => '19/11/2025'],
            ['territorio' => 190, 'publicador' => 'ZOILA CHAVEZ', 'salida' => '3/04/2025', 'entrada' => '26/7/2025', 'estado' => 'Archivo', 'entrada_prevista' => '1/8/2025'],
            ['territorio' => 191, 'publicador' => 'FRANCISCO MUÑOZ', 'salida' => '10/09/2025', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => '8/1/2026'],
            ['territorio' => 192, 'publicador' => 'PATRICIA SOUZA', 'salida' => '9/08/2025', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => '7/12/2025'],
            ['territorio' => 193, 'publicador' => 'FRANCISCO MUÑOZ', 'salida' => '15/05/2025', 'entrada' => '11/5/2025', 'estado' => 'Pendiente de entrega', 'entrada_prevista' => '12/9/2025'],
            ['territorio' => 194, 'publicador' => 'DANIELA CASTRO', 'salida' => '26/05/2025', 'entrada' => '21/6/2025', 'estado' => 'Archivo', 'entrada_prevista' => '23/9/2025'],
            ['territorio' => 195, 'publicador' => 'JOSE MARTINEZ', 'salida' => '21-6-25', 'entrada' => '9/8/2025', 'estado' => 'Archivo', 'entrada_prevista' => '19/10/2025'],
            ['territorio' => 196, 'publicador' => 'ERIC MARTIN', 'salida' => '23/8/25', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => '21/12/2025'],
            ['territorio' => 197, 'publicador' => 'AITOR LOPEZ', 'salida' => '23/08/2025', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => '21/12/2025'],
            ['territorio' => 198, 'publicador' => 'MIGUEL PAUNER', 'salida' => '4/9/2025', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => '2/1/2026'],
            ['territorio' => 199, 'publicador' => 'ADRIANA SANCHEZ', 'salida' => '5/07/2025', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => '2/11/2025'],
            ['territorio' => 200, 'publicador' => 'RAFA RIVERA', 'salida' => '19/07/2025', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => '16/11/2025'],
            ['territorio' => 201, 'publicador' => 'ZOILA CHAVEZ', 'salida' => '20/06/2025', 'entrada' => '26/7/2025', 'estado' => 'Archivo', 'entrada_prevista' => '18/10/2025'],
            ['territorio' => 202, 'publicador' => 'DAMARIS DE LOPEZ', 'salida' => '25/06/2025', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => '23/10/2025'],
            ['territorio' => 203, 'publicador' => 'CRISTINA RODRIGUEZ', 'salida' => '1/09/2025', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => '30/12/2025'],
            ['territorio' => 204, 'publicador' => 'FRANCISCO MUÑOZ', 'salida' => '14/05/2025', 'entrada' => '11/6/2025', 'estado' => 'Pendiente de entrega', 'entrada_prevista' => '11/9/2025'],
            ['territorio' => 205, 'publicador' => 'JOSEFA DE MILÁN', 'salida' => '9/07/2025', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => '6/11/2025'],
            ['territorio' => 206, 'publicador' => 'LAURA DE LOPEZ', 'salida' => '5/04/2025', 'entrada' => '14/6/2025', 'estado' => 'Archivo', 'entrada_prevista' => '3/8/2025'],
            ['territorio' => 207, 'publicador' => 'ERLINDA ESCOTO', 'salida' => '17/08/2025', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => '15/12/2025'],
            ['territorio' => 208, 'publicador' => 'ZURAMA PEÑA', 'salida' => '5/04/2025', 'entrada' => null, 'estado' => 'Fuera de plazo', 'entrada_prevista' => '3/8/2025'],
            ['territorio' => 209, 'publicador' => 'FRANCISCO MUÑOZ', 'salida' => '30/06/2025', 'entrada' => '15-7-25', 'estado' => 'Archivo', 'entrada_prevista' => '28/10/2025'],
            ['territorio' => 210, 'publicador' => 'NORMA DE ROBLES', 'salida' => '21/05/2025', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => '18/9/2025'],
            ['territorio' => 211, 'publicador' => 'FRANCISCO MUÑOZ', 'salida' => '15/07/2025', 'entrada' => '22-7-25', 'estado' => 'Archivo', 'entrada_prevista' => '12/11/2025'],
            ['territorio' => 212, 'publicador' => 'FRANCISCO MUÑOZ', 'salida' => '15/07/2025', 'entrada' => '22/7/2025', 'estado' => 'Archivo', 'entrada_prevista' => '12/11/2025'],
            ['territorio' => 213, 'publicador' => 'FRANCISCO MUÑOZ', 'salida' => '15/07/2025', 'entrada' => '22/7/2025', 'estado' => 'Archivo', 'entrada_prevista' => '12/11/2025'],
            ['territorio' => 214, 'publicador' => 'TONI LOPEZ', 'salida' => '28/06/2025', 'entrada' => null, 'estado' => 'Activo', 'entrada_prevista' => '26/10/2025']
        ];
        
        $publicadores = [];
        $territorios = [];
        $registros_creados = 0;
        $errores = 0;
        
        foreach ($registros as $data) {
            try {
                // Crear o encontrar el publicador
                $nombre_completo = trim($data['publicador']);
                if (!isset($publicadores[$nombre_completo])) {
                    // Separar nombre y apellidos
                    $partes = explode(' ', $nombre_completo);
                    $nombre = array_shift($partes);
                    $apellidos = implode(' ', $partes);
                    
                    $publicador = Publicador::firstOrCreate(
                        ['nombre' => $nombre, 'apellidos' => $apellidos],
                        [
                            'telefono' => '+34 600 ' . rand(100, 999) . ' ' . rand(100, 999),
                            'activo' => true,
                            'notas' => 'Importado desde Excel'
                        ]
                    );
                    $publicadores[$nombre_completo] = $publicador->id;
                }
                
                // Crear o encontrar el territorio
                $numero_territorio = $data['territorio'];
                if (!isset($territorios[$numero_territorio])) {
                    $territorio = Territorio::firstOrCreate(
                        ['numero' => $numero_territorio],
                        [
                            'nombre' => 'Territorio ' . $numero_territorio,
                            'imagen_url' => 'imagenes/' . $numero_territorio . '.jpg',
                            'estado' => 'libre',
                            'notas' => 'Importado desde Excel'
                        ]
                    );
                    $territorios[$numero_territorio] = $territorio->id;
                }
                
                // Parsear fechas
                $fecha_salida = $this->parsearFecha($data['salida']);
                $fecha_entrada = $data['entrada'] ? $this->parsearFecha($data['entrada']) : null;
                $entrada_prevista = $data['entrada_prevista'] ? $this->parsearFecha($data['entrada_prevista']) : null;
                
                // Crear el registro
                $registro = Registro::create([
                    'territorio_id' => $territorios[$numero_territorio],
                    'publicador_id' => $publicadores[$nombre_completo],
                    'fecha_salida' => $fecha_salida,
                    'fecha_entrada' => $fecha_entrada,
                    'entrada_prevista' => $entrada_prevista,
                    'notas' => isset($data['notas']) ? $data['notas'] : null
                ]);
                
                $registros_creados++;
                echo "✅ Registro {$registros_creados}: Territorio {$numero_territorio} - {$nombre_completo}\n";
                
            } catch (\Exception $e) {
                $errores++;
                echo "❌ Error en territorio {$data['territorio']}: " . $e->getMessage() . "\n";
                continue;
            }
        }
        
        echo "\n🎉 IMPORTACIÓN COMPLETADA:\n";
        echo "✅ Registros creados: {$registros_creados}\n";
        echo "❌ Errores: {$errores}\n";
        echo "👥 Publicadores únicos: " . count($publicadores) . "\n";
        echo "🗺️ Territorios únicos: " . count($territorios) . "\n";
    }
    
    private function parsearFecha($fecha_str)
    {
        if (!$fecha_str) return null;
        
        // Limpiar la fecha
        $fecha_str = trim($fecha_str);
        $fecha_str = str_replace(['20-6-25', '21-6-25', '15-7-25', '22-7-25'], ['20/6/2025', '21/6/2025', '15/7/2025', '22/7/2025'], $fecha_str);
        
        // Intentar diferentes formatos
        $formatos = [
            'd/m/Y',
            'd/m/y', 
            'd-m-Y',
            'd-m-y',
            'Y-m-d',
            'd/M/Y'
        ];
        
        foreach ($formatos as $formato) {
            try {
                $fecha = Carbon::createFromFormat($formato, $fecha_str);
                if ($fecha && $fecha->year >= 2020 && $fecha->year <= 2030) {
                    return $fecha->format('Y-m-d');
                }
            } catch (\Exception $e) {
                continue;
            }
        }
        
        // Si todo falla, intentar parseo automático
        try {
            return Carbon::parse($fecha_str)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}

