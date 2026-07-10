<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Territorio;
use App\Models\Publicador;
use App\Models\Registro;
use Carbon\Carbon;

class LimpiarDatosCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'territorios:limpiar-datos {--force : Ejecutar sin confirmación}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Limpiar y corregir inconsistencias en los datos del sistema';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧹 LIMPIEZA Y CORRECCIÓN DE DATOS DEL SISTEMA');
        $this->newLine();

        // Mostrar problemas encontrados
        $this->mostrarProblemas();

        if (!$this->option('force')) {
            if (!$this->confirm('¿Deseas proceder con la limpieza?')) {
                $this->info('Operación cancelada.');
                return Command::FAILURE;
            }
        }

        $this->newLine();
        $this->info('🔧 Iniciando correcciones...');

        // Corregir registros duplicados
        $this->corregirRegistrosDuplicados();

        // Corregir fechas inconsistentes
        $this->corregirFechasInconsistentes();

        // Verificar integridad final
        $this->verificarIntegridad();

        $this->newLine();
        $this->info('✅ Limpieza completada.');

        return Command::SUCCESS;
    }

    private function mostrarProblemas()
    {
        $this->info('🔍 Identificando problemas...');

        // Territorios con múltiples registros activos
        $territoriosConMultiples = Territorio::whereHas('registros', function($query) {
            $query->whereNull('fecha_entrada');
        })->withCount(['registros as registros_activos_count' => function($query) {
            $query->whereNull('fecha_entrada');
        }])->having('registros_activos_count', '>', 1)->get();

        if ($territoriosConMultiples->count() > 0) {
            $this->warn("❌ {$territoriosConMultiples->count()} territorios con múltiples registros activos:");
            foreach ($territoriosConMultiples as $territorio) {
                $this->line("   - Territorio #{$territorio->numero}: {$territorio->registros_activos_count} registros activos");
                
                // Mostrar detalles de los registros
                $registros = $territorio->registros()->whereNull('fecha_entrada')->get();
                foreach ($registros as $registro) {
                    $this->line("     * Registro #{$registro->id} - {$registro->publicador->nombre} - {$registro->fecha_salida->format('d/m/Y')}");
                }
            }
        }

        // Publicadores con múltiples territorios activos
        $publicadoresConMultiples = Publicador::whereHas('registros', function($query) {
            $query->whereNull('fecha_entrada');
        })->withCount(['registros as registros_activos_count' => function($query) {
            $query->whereNull('fecha_entrada');
        }])->having('registros_activos_count', '>', 1)->get();

        if ($publicadoresConMultiples->count() > 0) {
            $this->warn("❌ {$publicadoresConMultiples->count()} publicadores con múltiples territorios activos:");
            foreach ($publicadoresConMultiples as $publicador) {
                $this->line("   - {$publicador->nombre}: {$publicador->registros_activos_count} territorios activos");
            }
        }

        // Registros con fechas inconsistentes
        $registrosInconsistentes = Registro::whereNotNull('fecha_entrada')
            ->whereRaw('fecha_entrada < fecha_salida')
            ->count();

        if ($registrosInconsistentes > 0) {
            $this->warn("❌ {$registrosInconsistentes} registros con fechas inconsistentes (entrada antes que salida)");
        }

        $this->newLine();
    }

    private function corregirRegistrosDuplicados()
    {
        $this->info('🔧 Corrigiendo registros duplicados...');

        // Territorios con múltiples registros activos
        $territoriosConMultiples = Territorio::whereHas('registros', function($query) {
            $query->whereNull('fecha_entrada');
        })->withCount(['registros as registros_activos_count' => function($query) {
            $query->whereNull('fecha_entrada');
        }])->having('registros_activos_count', '>', 1)->get();

        foreach ($territoriosConMultiples as $territorio) {
            $registrosActivos = $territorio->registros()->whereNull('fecha_entrada')->orderBy('fecha_salida', 'asc')->get();
            
            $this->line("   Territorio #{$territorio->numero}: {$registrosActivos->count()} registros activos");
            
            // Mantener solo el más reciente, cerrar los demás
            $registroMasReciente = $registrosActivos->last();
            $registrosACorregir = $registrosActivos->take($registrosActivos->count() - 1);
            
            foreach ($registrosACorregir as $registro) {
                // Calcular una fecha de entrada lógica (30 días después de la salida)
                $fechaEntrada = Carbon::parse($registro->fecha_salida)->addDays(30);
                if ($fechaEntrada > now()) {
                    $fechaEntrada = now()->subDays(1);
                }
                
                $registro->update(['fecha_entrada' => $fechaEntrada]);
                $this->line("     ✓ Cerrado registro #{$registro->id} con fecha {$fechaEntrada->format('d/m/Y')}");
            }
            
            $this->line("     ✅ Mantenido registro #{$registroMasReciente->id} como activo");
        }

        // Publicadores con múltiples territorios activos
        $publicadoresConMultiples = Publicador::whereHas('registros', function($query) {
            $query->whereNull('fecha_entrada');
        })->withCount(['registros as registros_activos_count' => function($query) {
            $query->whereNull('fecha_entrada');
        }])->having('registros_activos_count', '>', 1)->get();

        foreach ($publicadoresConMultiples as $publicador) {
            $registrosActivos = $publicador->registros()->whereNull('fecha_entrada')->orderBy('fecha_salida', 'asc')->get();
            
            $this->line("   {$publicador->nombre}: {$registrosActivos->count()} territorios activos");
            
            // Mantener solo el más reciente
            $registroMasReciente = $registrosActivos->last();
            $registrosACorregir = $registrosActivos->take($registrosActivos->count() - 1);
            
            foreach ($registrosACorregir as $registro) {
                $fechaEntrada = Carbon::parse($registro->fecha_salida)->addDays(30);
                if ($fechaEntrada > now()) {
                    $fechaEntrada = now()->subDays(1);
                }
                
                $registro->update(['fecha_entrada' => $fechaEntrada]);
                $this->line("     ✓ Cerrado registro territorio #{$registro->territorio->numero}");
            }
        }
    }

    private function corregirFechasInconsistentes()
    {
        $this->info('🔧 Corrigiendo fechas inconsistentes...');

        $registrosInconsistentes = Registro::whereNotNull('fecha_entrada')
            ->whereRaw('fecha_entrada < fecha_salida')
            ->get();

        foreach ($registrosInconsistentes as $registro) {
            // Intercambiar las fechas
            $fechaSalidaOriginal = $registro->fecha_salida;
            $fechaEntradaOriginal = $registro->fecha_entrada;
            
            $registro->update([
                'fecha_salida' => $fechaEntradaOriginal,
                'fecha_entrada' => $fechaSalidaOriginal
            ]);
            
            $this->line("   ✓ Corregido registro #{$registro->id}: intercambiadas fechas");
        }
    }

    private function verificarIntegridad()
    {
        $this->info('🔍 Verificación de integridad final...');

        // Verificar territorios
        $territoriosConMultiples = Territorio::whereHas('registros', function($query) {
            $query->whereNull('fecha_entrada');
        })->withCount(['registros as registros_activos_count' => function($query) {
            $query->whereNull('fecha_entrada');
        }])->having('registros_activos_count', '>', 1)->count();

        if ($territoriosConMultiples === 0) {
            $this->line('   ✅ No hay territorios con múltiples registros activos');
        } else {
            $this->error("   ❌ Aún hay {$territoriosConMultiples} territorios con múltiples registros activos");
        }

        // Verificar publicadores
        $publicadoresConMultiples = Publicador::whereHas('registros', function($query) {
            $query->whereNull('fecha_entrada');
        })->withCount(['registros as registros_activos_count' => function($query) {
            $query->whereNull('fecha_entrada');
        }])->having('registros_activos_count', '>', 1)->count();

        if ($publicadoresConMultiples === 0) {
            $this->line('   ✅ No hay publicadores con múltiples territorios activos');
        } else {
            $this->error("   ❌ Aún hay {$publicadoresConMultiples} publicadores con múltiples territorios activos");
        }

        // Verificar fechas
        $registrosInconsistentes = Registro::whereNotNull('fecha_entrada')
            ->whereRaw('fecha_entrada < fecha_salida')
            ->count();

        if ($registrosInconsistentes === 0) {
            $this->line('   ✅ No hay registros con fechas inconsistentes');
        } else {
            $this->error("   ❌ Aún hay {$registrosInconsistentes} registros con fechas inconsistentes");
        }
    }
}
