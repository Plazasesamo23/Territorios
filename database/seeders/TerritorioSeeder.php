<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Territorio;

class TerritorioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $territorios = [
            [
                'numero' => 1,
                'nombre' => 'Centro Histórico',
                'imagen_url' => 'https://drive.google.com/file/d/ejemplo1/view',
                'estado' => 'libre',
                'notas' => 'Territorio céntrico, muchos edificios'
            ],
            [
                'numero' => 2,
                'nombre' => 'Barrio Residencial Norte',
                'imagen_url' => 'https://drive.google.com/file/d/ejemplo2/view',
                'estado' => 'libre',
                'notas' => 'Zona residencial tranquila'
            ],
            [
                'numero' => 3,
                'nombre' => 'Zona Comercial Plaza',
                'imagen_url' => 'https://drive.google.com/file/d/ejemplo3/view',
                'estado' => 'libre',
                'notas' => 'Área comercial, horario recomendado 10-14h'
            ],
            [
                'numero' => 4,
                'nombre' => 'Sector Rural Este',
                'imagen_url' => 'https://drive.google.com/file/d/ejemplo4/view',
                'estado' => 'libre',
                'notas' => 'Territorio rural, casas dispersas'
            ],
            [
                'numero' => 5,
                'nombre' => 'Urbanización Nueva',
                'imagen_url' => 'https://drive.google.com/file/d/ejemplo5/view',
                'estado' => 'libre',
                'notas' => 'Zona de nuevas construcciones'
            ],
            [
                'numero' => 6,
                'nombre' => 'Casco Antiguo',
                'imagen_url' => 'https://drive.google.com/file/d/ejemplo6/view',
                'estado' => 'libre',
                'notas' => 'Barrio antiguo, edificios bajos'
            ],
            [
                'numero' => 7,
                'nombre' => 'Campus Universitario',
                'imagen_url' => 'https://drive.google.com/file/d/ejemplo7/view',
                'estado' => 'libre',
                'notas' => 'Zona universitaria, muchos jóvenes'
            ],
            [
                'numero' => 8,
                'nombre' => 'Periferia Sur',
                'imagen_url' => 'https://drive.google.com/file/d/ejemplo8/view',
                'estado' => 'libre',
                'notas' => 'Territorio amplio, recomendado coche'
            ],
            [
                'numero' => 9,
                'nombre' => 'Polígono Industrial',
                'imagen_url' => 'https://drive.google.com/file/d/ejemplo9/view',
                'estado' => 'libre',
                'notas' => 'Área industrial, visitar en horario laboral'
            ],
            [
                'numero' => 10,
                'nombre' => 'Residencial Las Flores',
                'imagen_url' => 'https://drive.google.com/file/d/ejemplo10/view',
                'estado' => 'libre',
                'notas' => 'Zona de chalets unifamiliares'
            ],
        ];

        foreach ($territorios as $territorio) {
            Territorio::create($territorio);
        }
    }
}
