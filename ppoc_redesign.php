<?php
/**
 * PPOC Redesign Script
 * Rediseño completo del sistema PPOC
 *
 * Nueva estructura:
 * - turnos: Plantillas semanales (lunes a domingo)
 * - turnos_generados: Turnos específicos de cada fecha del mes
 * - turno_asignaciones: Asignaciones de publicadores a turnos generados
 */

// =====================================================
// 1. MIGRACIÓN: Agregar numero_turno a tabla turnos
// =====================================================
$migration1 = <<<'PHP'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('turnos', function (Blueprint $table) {
            // Número de turno del día (1 = primer turno, 2 = segundo turno)
            $table->tinyInteger('numero_turno')->default(1)->after('dia_semana');
        });

        // Actualizar índice único
        Schema::table('turnos', function (Blueprint $table) {
            $table->dropUnique('turno_unique');
            $table->unique(['congregacion_id', 'dia_semana', 'numero_turno', 'hora_inicio'], 'turno_plantilla_unique');
        });
    }

    public function down(): void
    {
        Schema::table('turnos', function (Blueprint $table) {
            $table->dropUnique('turno_plantilla_unique');
            $table->unique(['congregacion_id', 'dia_semana', 'hora_inicio', 'nombre'], 'turno_unique');
            $table->dropColumn('numero_turno');
        });
    }
};
PHP;

// =====================================================
// 2. MIGRACIÓN: Crear tabla turnos_generados
// =====================================================
$migration2 = <<<'PHP'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('turnos_generados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('turno_id')->constrained('turnos')->onDelete('cascade');
            $table->foreignId('congregacion_id')->constrained('congregaciones')->onDelete('cascade');
            $table->date('fecha');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->integer('capacidad')->default(3);
            $table->string('ubicacion')->nullable();
            $table->text('notas')->nullable();
            $table->enum('estado', ['abierto', 'completo', 'cancelado'])->default('abierto');
            $table->timestamps();

            // Un turno solo puede existir una vez por fecha
            $table->unique(['turno_id', 'fecha'], 'turno_generado_unique');
            $table->index(['congregacion_id', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('turnos_generados');
    }
};
PHP;

// =====================================================
// 3. MIGRACIÓN: Modificar turno_asignaciones
// =====================================================
$migration3 = <<<'PHP'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('turno_asignaciones', function (Blueprint $table) {
            // Agregar referencia a turno_generado
            $table->foreignId('turno_generado_id')->nullable()->after('turno_id')
                  ->constrained('turnos_generados')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('turno_asignaciones', function (Blueprint $table) {
            $table->dropForeign(['turno_generado_id']);
            $table->dropColumn('turno_generado_id');
        });
    }
};
PHP;

// =====================================================
// 4. MODELO: TurnoGenerado
// =====================================================
$modeloTurnoGenerado = <<<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToCongregacion;

class TurnoGenerado extends Model
{
    use BelongsToCongregacion;

    protected $table = 'turnos_generados';

    protected $fillable = [
        'turno_id',
        'congregacion_id',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'capacidad',
        'ubicacion',
        'notas',
        'estado',
    ];

    protected $casts = [
        'fecha' => 'date',
        'capacidad' => 'integer',
    ];

    public const ESTADOS = [
        'abierto' => 'Abierto',
        'completo' => 'Completo',
        'cancelado' => 'Cancelado',
    ];

    /**
     * Relación con la plantilla de turno
     */
    public function turno()
    {
        return $this->belongsTo(Turno::class);
    }

    /**
     * Relación con la congregación
     */
    public function congregacion()
    {
        return $this->belongsTo(Congregacion::class);
    }

    /**
     * Relación con las asignaciones
     */
    public function asignaciones()
    {
        return $this->hasMany(TurnoAsignacion::class, 'turno_generado_id');
    }

    /**
     * Obtener horario formateado
     */
    public function getHorarioAttribute(): string
    {
        return substr($this->hora_inicio, 0, 5) . ' - ' . substr($this->hora_fin, 0, 5);
    }

    /**
     * Verificar si hay espacio disponible
     */
    public function tieneEspacioDisponible(): bool
    {
        return $this->asignaciones()->count() < $this->capacidad;
    }

    /**
     * Obtener espacios disponibles
     */
    public function getEspaciosDisponiblesAttribute(): int
    {
        return max(0, $this->capacidad - $this->asignaciones()->count());
    }

    /**
     * Verificar si está completo
     */
    public function estaCompleto(): bool
    {
        return !$this->tieneEspacioDisponible();
    }

    /**
     * Scope para filtrar por congregación
     */
    public function scopeDeCongregacion($query, $congregacionId)
    {
        return $query->where('congregacion_id', $congregacionId);
    }

    /**
     * Scope para filtrar por mes
     */
    public function scopeDelMes($query, $year, $month)
    {
        return $query->whereYear('fecha', $year)->whereMonth('fecha', $month);
    }

    /**
     * Scope para filtrar por fecha
     */
    public function scopeDeFecha($query, $fecha)
    {
        return $query->where('fecha', $fecha);
    }
}
PHP;

// =====================================================
// 5. MODELO: Turno actualizado
// =====================================================
$modeloTurno = <<<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToCongregacion;

class Turno extends Model
{
    use BelongsToCongregacion;

    protected $table = 'turnos';

    protected $fillable = [
        'congregacion_id',
        'nombre',
        'dia_semana',
        'numero_turno',
        'hora_inicio',
        'hora_fin',
        'ubicacion',
        'tipo',
        'capacidad',
        'notas',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'dia_semana' => 'integer',
        'numero_turno' => 'integer',
        'capacidad' => 'integer',
    ];

    /**
     * Nombres de los días de la semana
     */
    public const DIAS_SEMANA = [
        0 => 'Domingo',
        1 => 'Lunes',
        2 => 'Martes',
        3 => 'Miércoles',
        4 => 'Jueves',
        5 => 'Viernes',
        6 => 'Sábado',
    ];

    /**
     * Tipos de turno disponibles
     */
    public const TIPOS = [
        'predicacion' => 'Predicación',
        'carrito' => 'Carrito',
        'telefonica' => 'Predicación Telefónica',
        'cartas' => 'Cartas',
        'informal' => 'Predicación Informal',
    ];

    /**
     * Relación con la congregación
     */
    public function congregacion()
    {
        return $this->belongsTo(Congregacion::class);
    }

    /**
     * Relación con los turnos generados
     */
    public function turnosGenerados()
    {
        return $this->hasMany(TurnoGenerado::class);
    }

    /**
     * Obtener el nombre del día de la semana
     */
    public function getDiaNombreAttribute(): string
    {
        return self::DIAS_SEMANA[$this->dia_semana] ?? 'Desconocido';
    }

    /**
     * Obtener el nombre del tipo de turno
     */
    public function getTipoNombreAttribute(): string
    {
        return self::TIPOS[$this->tipo] ?? $this->tipo;
    }

    /**
     * Obtener hora formateada
     */
    public function getHorarioAttribute(): string
    {
        return substr($this->hora_inicio, 0, 5) . ' - ' . substr($this->hora_fin, 0, 5);
    }

    /**
     * Obtener descripción completa del turno
     */
    public function getDescripcionAttribute(): string
    {
        return $this->dia_nombre . ' - Turno ' . $this->numero_turno . ' (' . $this->horario . ')';
    }

    /**
     * Obtener fechas del mes donde aplica este turno
     */
    public function getFechasDelMes(int $year, int $month): array
    {
        $fechas = [];
        $primerDia = \Carbon\Carbon::create($year, $month, 1);
        $ultimoDia = $primerDia->copy()->endOfMonth();

        $actual = $primerDia->copy();
        while ($actual <= $ultimoDia) {
            if ($actual->dayOfWeek === $this->dia_semana) {
                $fechas[] = $actual->copy();
            }
            $actual->addDay();
        }

        return $fechas;
    }

    /**
     * Generar turnos para un mes específico
     */
    public function generarParaMes(int $year, int $month): int
    {
        $fechas = $this->getFechasDelMes($year, $month);
        $creados = 0;

        foreach ($fechas as $fecha) {
            // Verificar si ya existe
            $existe = TurnoGenerado::where('turno_id', $this->id)
                ->where('fecha', $fecha->format('Y-m-d'))
                ->exists();

            if (!$existe) {
                TurnoGenerado::create([
                    'turno_id' => $this->id,
                    'congregacion_id' => $this->congregacion_id,
                    'fecha' => $fecha->format('Y-m-d'),
                    'hora_inicio' => $this->hora_inicio,
                    'hora_fin' => $this->hora_fin,
                    'capacidad' => $this->capacidad,
                    'ubicacion' => $this->ubicacion,
                    'estado' => 'abierto',
                ]);
                $creados++;
            }
        }

        return $creados;
    }
}
PHP;

// =====================================================
// 6. MODELO: TurnoAsignacion actualizado
// =====================================================
$modeloTurnoAsignacion = <<<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TurnoAsignacion extends Model
{
    protected $table = 'turno_asignaciones';

    protected $fillable = [
        'turno_id',
        'turno_generado_id',
        'publicador_id',
        'fecha',
        'rol',
        'estado',
        'notas',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public const ROLES = [
        'capitan' => 'Capitán',
        'voluntario' => 'Voluntario',
    ];

    public const ESTADOS = [
        'pendiente' => 'Pendiente',
        'confirmado' => 'Confirmado',
        'completado' => 'Completado',
        'cancelado' => 'Cancelado',
    ];

    /**
     * Relación con el turno plantilla
     */
    public function turno()
    {
        return $this->belongsTo(Turno::class);
    }

    /**
     * Relación con el turno generado
     */
    public function turnoGenerado()
    {
        return $this->belongsTo(TurnoGenerado::class, 'turno_generado_id');
    }

    /**
     * Relación con el publicador
     */
    public function publicador()
    {
        return $this->belongsTo(Publicador::class);
    }

    /**
     * Obtener nombre del rol
     */
    public function getRolNombreAttribute(): string
    {
        return self::ROLES[$this->rol] ?? $this->rol;
    }

    /**
     * Obtener nombre del estado
     */
    public function getEstadoNombreAttribute(): string
    {
        return self::ESTADOS[$this->estado] ?? $this->estado;
    }

    /**
     * Scope para filtrar por congregación
     */
    public function scopeDeCongregacion($query, $congregacionId)
    {
        return $query->whereHas('turnoGenerado', function($q) use ($congregacionId) {
            $q->where('congregacion_id', $congregacionId);
        });
    }

    /**
     * Scope para filtrar por mes
     */
    public function scopeDelMes($query, $year, $month)
    {
        return $query->whereYear('fecha', $year)->whereMonth('fecha', $month);
    }
}
PHP;

// =====================================================
// 7. CONTROLADOR: TurnoController actualizado
// =====================================================
$controlador = <<<'PHP'
<?php

namespace App\Http\Controllers;

use App\Models\Turno;
use App\Models\TurnoGenerado;
use App\Models\TurnoAsignacion;
use App\Models\Publicador;
use App\Models\Congregacion;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TurnoController extends Controller
{
    /**
     * Obtener la congregación activa
     */
    private function getCongregacion()
    {
        $congregacionId = session('congregacion_activa_id');
        return Congregacion::find($congregacionId);
    }

    /**
     * Mostrar lista de plantillas de turnos semanales
     */
    public function index()
    {
        $congregacion = $this->getCongregacion();
        $turnos = Turno::where('congregacion_id', $congregacion->id)
            ->orderBy('dia_semana')
            ->orderBy('numero_turno')
            ->orderBy('hora_inicio')
            ->get();

        return view('ppoc.turnos.index', compact('turnos'));
    }

    /**
     * Mostrar formulario para crear plantilla de turno
     */
    public function create()
    {
        return view('ppoc.turnos.create', [
            'diasSemana' => Turno::DIAS_SEMANA,
            'tipos' => Turno::TIPOS,
        ]);
    }

    /**
     * Guardar nueva plantilla de turno
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'dia_semana' => 'required|integer|min:0|max:6',
            'numero_turno' => 'required|integer|min:1|max:2',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
            'ubicacion' => 'nullable|string|max:255',
            'tipo' => 'required|string|in:' . implode(',', array_keys(Turno::TIPOS)),
            'capacidad' => 'required|integer|min:1|max:20',
            'notas' => 'nullable|string',
        ]);

        $congregacion = $this->getCongregacion();
        $validated['congregacion_id'] = $congregacion->id;
        $validated['activo'] = true;

        Turno::create($validated);

        return redirect()->route('ppoc.turnos.index')
            ->with('success', 'Plantilla de turno creada correctamente.');
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(Turno $turno)
    {
        return view('ppoc.turnos.edit', [
            'turno' => $turno,
            'diasSemana' => Turno::DIAS_SEMANA,
            'tipos' => Turno::TIPOS,
        ]);
    }

    /**
     * Actualizar plantilla de turno
     */
    public function update(Request $request, Turno $turno)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'dia_semana' => 'required|integer|min:0|max:6',
            'numero_turno' => 'required|integer|min:1|max:2',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
            'ubicacion' => 'nullable|string|max:255',
            'tipo' => 'required|string|in:' . implode(',', array_keys(Turno::TIPOS)),
            'capacidad' => 'required|integer|min:1|max:20',
            'notas' => 'nullable|string',
            'activo' => 'boolean',
        ]);

        $turno->update($validated);

        return redirect()->route('ppoc.turnos.index')
            ->with('success', 'Plantilla de turno actualizada.');
    }

    /**
     * Eliminar plantilla de turno
     */
    public function destroy(Turno $turno)
    {
        $turno->delete();

        return redirect()->route('ppoc.turnos.index')
            ->with('success', 'Plantilla de turno eliminada.');
    }

    /**
     * Vista del calendario mensual de asignaciones
     */
    public function calendario(Request $request)
    {
        $congregacion = $this->getCongregacion();
        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);

        // Obtener turnos generados del mes
        $turnosGenerados = TurnoGenerado::with(['turno', 'asignaciones.publicador'])
            ->where('congregacion_id', $congregacion->id)
            ->delMes($year, $month)
            ->orderBy('fecha')
            ->get()
            ->groupBy(function($tg) {
                return $tg->fecha->format('Y-m-d');
            });

        // Obtener plantillas de turnos activas
        $plantillas = Turno::where('congregacion_id', $congregacion->id)
            ->where('activo', true)
            ->orderBy('dia_semana')
            ->orderBy('numero_turno')
            ->get();

        $publicadores = Publicador::where('congregacion_id', $congregacion->id)
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        // Generar estructura del calendario
        $primerDia = Carbon::create($year, $month, 1);
        $ultimoDia = $primerDia->copy()->endOfMonth();
        $semanas = [];
        $semanaActual = [];

        // Rellenar días vacíos al inicio
        for ($i = 0; $i < $primerDia->dayOfWeek; $i++) {
            $semanaActual[] = null;
        }

        // Agregar días del mes
        $dia = $primerDia->copy();
        while ($dia <= $ultimoDia) {
            $semanaActual[] = $dia->copy();
            if (count($semanaActual) === 7) {
                $semanas[] = $semanaActual;
                $semanaActual = [];
            }
            $dia->addDay();
        }

        // Rellenar días vacíos al final
        while (count($semanaActual) > 0 && count($semanaActual) < 7) {
            $semanaActual[] = null;
        }
        if (!empty($semanaActual)) {
            $semanas[] = $semanaActual;
        }

        $nombreMes = Carbon::create($year, $month, 1)->translatedFormat("F");
        $prevMonth = Carbon::create($year, $month, 1)->subMonth();
        $nextMonth = Carbon::create($year, $month, 1)->addMonth();

        // Verificar si el mes ya está generado
        $mesGenerado = $turnosGenerados->isNotEmpty();

        return view('ppoc.calendario', compact(
            'turnosGenerados', 'plantillas', 'publicadores',
            'year', 'month', 'semanas', 'nombreMes', 'prevMonth', 'nextMonth',
            'mesGenerado'
        ));
    }

    /**
     * Generar turnos para un mes basándose en las plantillas
     */
    public function generarMes(Request $request)
    {
        $validated = $request->validate([
            'year' => 'required|integer|min:2024|max:2100',
            'month' => 'required|integer|min:1|max:12',
        ]);

        $congregacion = $this->getCongregacion();
        $year = $validated['year'];
        $month = $validated['month'];

        $plantillas = Turno::where('congregacion_id', $congregacion->id)
            ->where('activo', true)
            ->get();

        $creados = 0;
        foreach ($plantillas as $plantilla) {
            $creados += $plantilla->generarParaMes($year, $month);
        }

        return redirect()->route('ppoc.calendario', ['year' => $year, 'month' => $month])
            ->with('success', "Calendario generado: {$creados} turnos creados.");
    }

    /**
     * Asignar publicador a un turno generado
     */
    public function asignar(Request $request)
    {
        $validated = $request->validate([
            'turno_generado_id' => 'required|exists:turnos_generados,id',
            'publicador_id' => 'required|exists:publicadores,id',
            'rol' => 'required|in:capitan,voluntario',
        ]);

        $turnoGenerado = TurnoGenerado::findOrFail($validated['turno_generado_id']);

        // Verificar capacidad
        if (!$turnoGenerado->tieneEspacioDisponible()) {
            return back()->with('error', 'Este turno ya está completo.');
        }

        // Verificar que el publicador no esté ya asignado
        $yaAsignado = TurnoAsignacion::where('turno_generado_id', $validated['turno_generado_id'])
            ->where('publicador_id', $validated['publicador_id'])
            ->exists();

        if ($yaAsignado) {
            return back()->with('error', 'Este publicador ya está asignado a este turno.');
        }

        // Verificar que solo haya un capitán
        if ($validated['rol'] === 'capitan') {
            $hayCapitan = TurnoAsignacion::where('turno_generado_id', $validated['turno_generado_id'])
                ->where('rol', 'capitan')
                ->exists();

            if ($hayCapitan) {
                return back()->with('error', 'Ya hay un capitán asignado a este turno.');
            }
        }

        TurnoAsignacion::create([
            'turno_id' => $turnoGenerado->turno_id,
            'turno_generado_id' => $validated['turno_generado_id'],
            'publicador_id' => $validated['publicador_id'],
            'fecha' => $turnoGenerado->fecha,
            'rol' => $validated['rol'],
            'estado' => 'pendiente',
        ]);

        // Actualizar estado del turno si está completo
        if ($turnoGenerado->estaCompleto()) {
            $turnoGenerado->update(['estado' => 'completo']);
        }

        return back()->with('success', 'Publicador asignado correctamente.');
    }

    /**
     * Quitar asignación
     */
    public function desasignar(TurnoAsignacion $asignacion)
    {
        $turnoGenerado = $asignacion->turnoGenerado;
        $asignacion->delete();

        // Reabrir turno si estaba completo
        if ($turnoGenerado && $turnoGenerado->estado === 'completo') {
            $turnoGenerado->update(['estado' => 'abierto']);
        }

        return back()->with('success', 'Asignación eliminada.');
    }

    /**
     * Actualizar estado de asignación
     */
    public function actualizarEstado(Request $request, TurnoAsignacion $asignacion)
    {
        $validated = $request->validate([
            'estado' => 'required|in:pendiente,confirmado,completado,cancelado',
        ]);

        $asignacion->update(['estado' => $validated['estado']]);

        return back()->with('success', 'Estado actualizado.');
    }
}
PHP;

// Guardar archivos
$timestamp = date('Y_m_d_His');

file_put_contents("database/migrations/{$timestamp}_add_numero_turno_to_turnos.php", $migration1);
echo "Creada migración 1: add_numero_turno_to_turnos\n";

file_put_contents("database/migrations/{$timestamp}1_create_turnos_generados_table.php", $migration2);
echo "Creada migración 2: create_turnos_generados_table\n";

file_put_contents("database/migrations/{$timestamp}2_add_turno_generado_to_asignaciones.php", $migration3);
echo "Creada migración 3: add_turno_generado_to_asignaciones\n";

file_put_contents("app/Models/TurnoGenerado.php", $modeloTurnoGenerado);
echo "Creado modelo: TurnoGenerado\n";

file_put_contents("app/Models/Turno.php", $modeloTurno);
echo "Actualizado modelo: Turno\n";

file_put_contents("app/Models/TurnoAsignacion.php", $modeloTurnoAsignacion);
echo "Actualizado modelo: TurnoAsignacion\n";

file_put_contents("app/Http/Controllers/TurnoController.php", $controlador);
echo "Actualizado controlador: TurnoController\n";

echo "\n=== PPOC Redesign Completado ===\n";
echo "Ahora ejecuta: php artisan migrate --force\n";
