<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Territorio;

class UpdateTerritoriosNombres extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'territorios:update-nombres';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualizar nombres de territorios existentes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $territorios = [
            1 => 'Centro Histórico',
            2 => 'Barrio Residencial Norte', 
            3 => 'Zona Comercial Plaza',
            4 => 'Sector Rural Este',
            5 => 'Urbanización Nueva',
            6 => 'Casco Antiguo',
            7 => 'Campus Universitario',
            8 => 'Periferia Sur',
            9 => 'Polígono Industrial',
            10 => 'Residencial Las Flores'
        ];

        $this->info('Actualizando nombres de territorios...');

        foreach ($territorios as $numero => $nombre) {
            $territorio = Territorio::where('numero', $numero)->first();
            if ($territorio) {
                $territorio->update(['nombre' => $nombre]);
                $this->line("✅ Territorio $numero: $nombre - Actualizado");
            } else {
                $this->error("❌ Territorio $numero no encontrado");
            }
        }

        $this->info('¡Actualización completada!');
        
        return Command::SUCCESS;
    }
}
