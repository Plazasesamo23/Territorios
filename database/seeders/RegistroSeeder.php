<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Territorio;
use App\Models\Publicador;
use App\Models\Registro;
use Carbon\Carbon;

class RegistroSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Asegurar que tenemos territorios y publicadores
        $territorios = Territorio::all();
        $publicadores = Publicador::where('activo', true)->get();

        if ($territorios->isEmpty() || $publicadores->isEmpty()) {
            $this->command->warn('No hay territorios o publicadores disponibles. Ejecuta primero sus seeders.');
            return;
        }

        // Limpiar registros existentes
        Registro::truncate();

        $this->command->info('Creando registros de prueba...');

        // Distribuir territorios entre diferentes escenarios
        $totalTerritorios = $territorios->count();
        $this->command->info("Total de territorios disponibles: {$totalTerritorios}");
        
        if ($totalTerritorios < 8) {
            $this->command->warn('Se necesitan al menos 8 territorios para crear una muestra representativa.');
            return;
        }

        // ESCENARIO 1: Territorios ACTIVOS (asignados recientemente)
        $this->crearRegistrosActivos($territorios, $publicadores, 3);

        // ESCENARIO 2: Territorios ATRASADOS (más de 90 días)  
        $this->crearRegistrosAtrasados($territorios, $publicadores, 2);

        // ESCENARIO 3: Territorios en ARCHIVO (devueltos recientemente)
        $this->crearRegistrosArchivo($territorios, $publicadores, 3);

        // ESCENARIO 4: Territorios con HISTORIAL (múltiples ciclos)
        // Los restantes tendrán historial
        $restantes = $totalTerritorios - 8;
        if ($restantes > 0) {
            $this->crearRegistrosConHistorial($territorios, $publicadores, $restantes);
        }

        $this->command->info('✅ Registros creados exitosamente!');
        $this->mostrarEstadisticas();
    }

    /**
     * Crear registros para territorios ACTIVOS
     */
    private function crearRegistrosActivos($territorios, $publicadores, $cantidad = 3)
    {
        $territoriosActivos = $territorios->random($cantidad);
        
        foreach ($territoriosActivos as $territorio) {
            $diasAtras = rand(1, 85); // Entre 1 y 85 días (dentro del límite)
            $fechaSalida = Carbon::now()->subDays($diasAtras);
            
            Registro::create([
                'territorio_id' => $territorio->id,
                'publicador_id' => $publicadores->random()->id,
                'fecha_salida' => $fechaSalida,
                'fecha_entrada' => null, // Aún activo
                'notas' => "Territorio asignado hace {$diasAtras} días - Estado: ACTIVO"
            ]);
        }
    }

    /**
     * Crear registros para territorios ATRASADOS
     */
    private function crearRegistrosAtrasados($territorios, $publicadores, $cantidad = 2)
    {
        $territoriosAtrasados = $territorios->random($cantidad);
        
        foreach ($territoriosAtrasados as $territorio) {
            $diasAtras = rand(91, 150); // Más de 90 días (atrasado)
            $fechaSalida = Carbon::now()->subDays($diasAtras);
            
            Registro::create([
                'territorio_id' => $territorio->id,
                'publicador_id' => $publicadores->random()->id,
                'fecha_salida' => $fechaSalida,
                'fecha_entrada' => null, // Aún no devuelto
                'notas' => "⚠️ Territorio ATRASADO - {$diasAtras} días sin devolver"
            ]);
        }
    }

    /**
     * Crear registros para territorios en ARCHIVO
     */
    private function crearRegistrosArchivo($territorios, $publicadores, $cantidad = 3)
    {
        $territoriosArchivo = $territorios->random($cantidad);
        
        foreach ($territoriosArchivo as $territorio) {
            $diasSalida = rand(30, 80); // Duración del trabajo
            $diasDescanso = rand(1, 25); // Días de descanso (menos de 30)
            
            $fechaSalida = Carbon::now()->subDays($diasSalida + $diasDescanso);
            $fechaEntrada = Carbon::now()->subDays($diasDescanso);
            
            Registro::create([
                'territorio_id' => $territorio->id,
                'publicador_id' => $publicadores->random()->id,
                'fecha_salida' => $fechaSalida,
                'fecha_entrada' => $fechaEntrada,
                'notas' => "Devuelto hace {$diasDescanso} días - En período de descanso"
            ]);
        }
    }

    /**
     * Crear registros con historial completo
     */
    private function crearRegistrosConHistorial($territorios, $publicadores, $cantidad = 2)
    {
        $territoriosConHistorial = $territorios->random($cantidad);
        
        foreach ($territoriosConHistorial as $territorio) {
            // Crear 2-4 registros históricos por territorio
            $numRegistros = rand(2, 4);
            $fechaBase = Carbon::now()->subMonths(12); // Empezar hace un año
            
            for ($i = 0; $i < $numRegistros; $i++) {
                $duracion = rand(20, 90); // Duración del trabajo
                $descanso = rand(30, 60); // Período de descanso
                
                $fechaSalida = $fechaBase->copy();
                $fechaEntrada = $fechaBase->copy()->addDays($duracion);
                
                Registro::create([
                    'territorio_id' => $territorio->id,
                    'publicador_id' => $publicadores->random()->id,
                    'fecha_salida' => $fechaSalida,
                    'fecha_entrada' => $fechaEntrada,
                    'notas' => "Registro histórico #" . ($i + 1) . " - Completado en {$duracion} días"
                ]);
                
                // Avanzar fecha para siguiente registro
                $fechaBase->addDays($duracion + $descanso);
            }
            
            // Algunos territorios con historial ahora están LIBRES
            if (rand(1, 3) === 1) {
                // Último registro hace más de 30 días = LIBRE
                $ultimoRegistro = $territorio->registros()->latest('fecha_salida')->first();
                if ($ultimoRegistro && $ultimoRegistro->fecha_entrada) {
                    $ultimoRegistro->update([
                        'fecha_entrada' => Carbon::now()->subDays(35) // Hace 35 días
                    ]);
                }
            }
        }
    }

    /**
     * Mostrar estadísticas de los registros creados
     */
    private function mostrarEstadisticas()
    {
        $total = Registro::count();
        $activos = Registro::whereNull('fecha_entrada')->count();
        $cerrados = Registro::whereNotNull('fecha_entrada')->count();
        
        $this->command->info("📊 Estadísticas de Registros:");
        $this->command->info("   Total: {$total}");
        $this->command->info("   Activos: {$activos}");
        $this->command->info("   Cerrados: {$cerrados}");
        
        // Estadísticas por estado calculado
        $territorios = Territorio::all();
        $estados = [
            'libre' => 0,
            'activo' => 0,
            'atrasado' => 0,
            'archivo' => 0
        ];
        
        foreach ($territorios as $territorio) {
            $estado = $territorio->calcularEstado();
            $estados[$estado]++;
        }
        
        $this->command->info("📊 Estados de Territorios:");
        $this->command->info("   🟢 Libres: {$estados['libre']}");
        $this->command->info("   🔵 Activos: {$estados['activo']}");
        $this->command->info("   🔴 Atrasados: {$estados['atrasado']}");
        $this->command->info("   ⚫ Archivo: {$estados['archivo']}");
    }
}
