<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Territorio;
use App\Models\Publicador;
use App\Models\Registro;

class TestEstadosCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'territorios:test-estados';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Probar y mostrar el estado actual del sistema de territorios';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 PROBANDO SISTEMA DE ESTADOS AUTOMÁTICO');
        $this->newLine();

        // Mostrar configuración
        $this->info('⚙️ Configuración del Sistema:');
        $this->line('   Días máximos activo: ' . config('territorios.dias_maximos_activo', 90));
        $this->line('   Días descanso archivo: ' . config('territorios.dias_descanso_archivo', 30));
        $this->newLine();

        // Estadísticas generales
        $totalTerritorios = Territorio::count();
        $totalPublicadores = Publicador::count();
        $totalRegistros = Registro::count();
        $registrosActivos = Registro::whereNull('fecha_entrada')->count();

        $this->info('📊 Estadísticas Generales:');
        $this->line("   Total territorios: {$totalTerritorios}");
        $this->line("   Total publicadores: {$totalPublicadores}");
        $this->line("   Total registros: {$totalRegistros}");
        $this->line("   Registros activos: {$registrosActivos}");
        $this->newLine();

        // Analizar estados de territorios
        $territorios = Territorio::all();
        $estados = [
            'libre' => [],
            'activo' => [],
            'atrasado' => [],
            'archivo' => []
        ];

        foreach ($territorios as $territorio) {
            $estado = $territorio->calcularEstado();
            $estados[$estado][] = $territorio;
        }

        $this->info('🗺️ Estados de Territorios:');
        foreach ($estados as $estado => $territoriosEstado) {
            $emoji = config("territorios.estados.{$estado}.emoji", '⚪');
            $nombre = config("territorios.estados.{$estado}.nombre", ucfirst($estado));
            $count = count($territoriosEstado);
            
            $this->line("   {$emoji} {$nombre}: {$count}");
            
            if ($count > 0 && $count <= 5) {
                foreach ($territoriosEstado as $territorio) {
                    $this->line("      - #{$territorio->numero}: {$territorio->nombre}");
                }
            } elseif ($count > 5) {
                $primeros = array_slice($territoriosEstado, 0, 3);
                foreach ($primeros as $territorio) {
                    $this->line("      - #{$territorio->numero}: {$territorio->nombre}");
                }
                $this->line("      ... y " . ($count - 3) . " más");
            }
        }
        $this->newLine();

        // Mostrar registros activos con detalle
        $registrosActivos = Registro::with(['territorio', 'publicador'])
            ->whereNull('fecha_entrada')
            ->orderBy('fecha_salida', 'asc')
            ->get();

        if ($registrosActivos->count() > 0) {
            $this->info('📋 Registros Activos Detallados:');
            foreach ($registrosActivos as $registro) {
                $diasTranscurridos = \Carbon\Carbon::parse($registro->fecha_salida)->diffInDays(now());
                $estado = $registro->territorio->calcularEstado();
                $emoji = config("territorios.estados.{$estado}.emoji", '⚪');
                
                $this->line("   {$emoji} Territorio #{$registro->territorio->numero} → {$registro->publicador->nombre}");
                $this->line("      Salida: {$registro->fecha_salida->format('d/m/Y')} ({$diasTranscurridos} días)");
                $this->line("      Estado: " . strtoupper($estado));
            }
            $this->newLine();
        }

        // Mostrar historial reciente
        $historialReciente = Registro::with(['territorio', 'publicador'])
            ->whereNotNull('fecha_entrada')
            ->orderBy('fecha_entrada', 'desc')
            ->limit(5)
            ->get();

        if ($historialReciente->count() > 0) {
            $this->info('📚 Historial Reciente (últimas 5 devoluciones):');
            foreach ($historialReciente as $registro) {
                $duracion = \Carbon\Carbon::parse($registro->fecha_salida)
                    ->diffInDays(\Carbon\Carbon::parse($registro->fecha_entrada));
                
                $this->line("   📥 Territorio #{$registro->territorio->numero} ← {$registro->publicador->nombre}");
                $this->line("      Período: {$registro->fecha_salida->format('d/m/Y')} - {$registro->fecha_entrada->format('d/m/Y')} ({$duracion} días)");
            }
            $this->newLine();
        }

        // Verificar consistencia del sistema
        $this->info('🔍 Verificación de Consistencia:');
        
        $inconsistencias = 0;
        
        // Verificar que no hay publicadores con múltiples territorios activos
        $publicadoresConMultiples = Publicador::whereHas('registros', function($query) {
            $query->whereNull('fecha_entrada');
        })->withCount(['registros as registros_activos_count' => function($query) {
            $query->whereNull('fecha_entrada');
        }])->having('registros_activos_count', '>', 1)->get();
        
        if ($publicadoresConMultiples->count() > 0) {
            $this->error("   ❌ {$publicadoresConMultiples->count()} publicadores con múltiples territorios activos");
            $inconsistencias++;
        } else {
            $this->line("   ✅ No hay publicadores con múltiples territorios activos");
        }

        // Verificar que no hay territorios con múltiples registros activos
        $territoriosConMultiples = Territorio::whereHas('registros', function($query) {
            $query->whereNull('fecha_entrada');
        })->withCount(['registros as registros_activos_count' => function($query) {
            $query->whereNull('fecha_entrada');
        }])->having('registros_activos_count', '>', 1)->get();
        
        if ($territoriosConMultiples->count() > 0) {
            $this->error("   ❌ {$territoriosConMultiples->count()} territorios con múltiples registros activos");
            $inconsistencias++;
        } else {
            $this->line("   ✅ No hay territorios con múltiples registros activos");
        }

        $this->newLine();
        
        if ($inconsistencias === 0) {
            $this->info('🎉 ¡Sistema funcionando correctamente! Todas las verificaciones pasaron.');
        } else {
            $this->error("⚠️ Se encontraron {$inconsistencias} inconsistencias en el sistema.");
        }

        return Command::SUCCESS;
    }
}
