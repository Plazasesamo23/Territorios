<?php

namespace App\Http\Controllers;

use App\Models\Tarea;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TareaController extends Controller
{
    /**
     * Listado de tareas con filtros.
     * Filtros aceptados: depto, estado, asignacion (mias|todas), visibilidad (publica|personal)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $congregacionId = session('congregacion_activa_id');

        $depto = $request->query('depto');
        $estado = $request->query('estado', 'activas');
        $asignacion = $request->query('asignacion', 'todas');
        $visibilidad = $request->query('visibilidad');

        $query = Tarea::query()->visiblePara($user);

        if ($depto && $depto !== 'todos') {
            $query->where('departamento', $depto);
        }
        if ($estado === 'activas') {
            $query->whereIn('estado', ['pendiente', 'en_curso', 'bloqueada']);
        } elseif ($estado && $estado !== 'todas') {
            $query->where('estado', $estado);
        }
        if ($asignacion === 'mias') {
            $query->where('asignado_a', $user->id);
        }
        if ($visibilidad && in_array($visibilidad, ['publica', 'personal'], true)) {
            $query->where('visibilidad', $visibilidad);
        }

        $tareas = $query->with(['asignado', 'creador'])
            ->orderByRaw("FIELD(estado, 'en_curso', 'pendiente', 'bloqueada', 'hecha')")
            ->orderByRaw("FIELD(prioridad, 'alta', 'media', 'baja')")
            ->orderByRaw('fecha_limite IS NULL, fecha_limite ASC')
            ->orderBy('created_at', 'desc')
            ->paginate(50)
            ->withQueryString();

        $ancladasIds = $user->tareasAncladas()->pluck('tareas.id')->toArray();

        $usuariosAsignables = User::where('congregacion_id', $congregacionId)
            ->orderBy('name')
            ->get(['id', 'name', 'role']);

        $departamentosPermitidos = collect(Tarea::DEPARTAMENTOS)
            ->filter(fn($_, $key) => $user->canCrearTareaPublica($key) || $key === 'general')
            ->all();

        return view('tareas.index', [
            'tareas' => $tareas,
            'ancladasIds' => $ancladasIds,
            'usuariosAsignables' => $usuariosAsignables,
            'departamentosPermitidos' => $departamentosPermitidos,
            'todosDepartamentos' => Tarea::DEPARTAMENTOS,
            'estados' => Tarea::ESTADOS,
            'prioridades' => Tarea::PRIORIDADES,
            'filtros' => compact('depto', 'estado', 'asignacion', 'visibilidad'),
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:5000',
            'departamento' => 'required|in:' . implode(',', array_keys(Tarea::DEPARTAMENTOS)),
            'asignado_a' => 'nullable|exists:users,id',
            'visibilidad' => 'required|in:publica,personal',
            'prioridad' => 'required|in:baja,media,alta',
            'fecha_limite' => 'nullable|date',
        ]);

        // Personal: autoasignada salvo que sea admin asignandola a otro usuario
        if ($data['visibilidad'] === 'personal') {
            if (!$user->isAdmin() || empty($data['asignado_a'])) {
                $data['asignado_a'] = $user->id;
            }
        } else {
            if (!$user->canCrearTareaPublica($data['departamento'])) {
                return back()->with('error', 'No tienes permiso para crear tareas publicas en ese departamento.');
            }
        }

        // Si tarea publica esta sin asignar y el creador esta en el depto, no auto-asignar -> queda libre
        $data['creado_por'] = $user->id;
        $data['estado'] = 'pendiente';

        Tarea::create($data);

        return redirect()->route('tareas.index', $request->only(['depto', 'estado']))
            ->with('success', 'Tarea creada.');
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $tarea = Tarea::findOrFail($id);

        if (!$user->canEditarTarea($tarea)) {
            abort(403);
        }

        $data = $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:5000',
            'departamento' => 'required|in:' . implode(',', array_keys(Tarea::DEPARTAMENTOS)),
            'asignado_a' => 'nullable|exists:users,id',
            'visibilidad' => 'required|in:publica,personal',
            'estado' => 'required|in:' . implode(',', array_keys(Tarea::ESTADOS)),
            'prioridad' => 'required|in:baja,media,alta',
            'fecha_limite' => 'nullable|date',
        ]);

        if ($data['visibilidad'] === 'personal') {
            if ($user->isAdmin()) {
                $data['asignado_a'] = $data['asignado_a'] ?: ($tarea->asignado_a ?: $user->id);
            } else {
                $data['asignado_a'] = $tarea->asignado_a ?: $user->id;
            }
        }

        // Marcar completada
        if ($data['estado'] === 'hecha' && $tarea->estado !== 'hecha') {
            $data['completada_at'] = now();
            $data['completada_por'] = $user->id;
        } elseif ($data['estado'] !== 'hecha') {
            $data['completada_at'] = null;
            $data['completada_por'] = null;
        }

        $tarea->update($data);

        return back()->with('success', 'Tarea actualizada.');
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $tarea = Tarea::findOrFail($id);

        if (!$user->isAdmin() && $tarea->creado_por !== $user->id) {
            abort(403);
        }

        $tarea->delete();
        return back()->with('success', 'Tarea eliminada.');
    }

    /**
     * Marcar/desmarcar tarea como hecha (toggle rapido).
     */
    public function toggleHecha($id)
    {
        $user = Auth::user();
        $tarea = Tarea::findOrFail($id);

        if (!$user->canEditarTarea($tarea)) {
            abort(403);
        }

        if ($tarea->estado === 'hecha') {
            $tarea->update([
                'estado' => 'pendiente',
                'completada_at' => null,
                'completada_por' => null,
            ]);
        } else {
            $tarea->update([
                'estado' => 'hecha',
                'completada_at' => now(),
                'completada_por' => $user->id,
            ]);
        }

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'estado' => $tarea->estado]);
        }
        return back();
    }

    /**
     * Toggle anclaje de una tarea para el usuario actual.
     */
    public function toggleAnclar($id)
    {
        $user = Auth::user();
        $tarea = Tarea::findOrFail($id);

        // Solo puede anclar tareas que ya pueda ver
        if ($tarea->visibilidad === 'personal' && $tarea->asignado_a !== $user->id && $tarea->creado_por !== $user->id) {
            abort(403);
        }
        if ($tarea->visibilidad === 'publica' && !$user->canVerTareasDepartamento($tarea->departamento)) {
            abort(403);
        }

        $existe = $user->tareasAncladas()->where('tareas.id', $tarea->id)->exists();
        if ($existe) {
            $user->tareasAncladas()->detach($tarea->id);
            $anclada = false;
        } else {
            $user->tareasAncladas()->attach($tarea->id);
            $anclada = true;
        }

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'anclada' => $anclada]);
        }
        return back();
    }

    /**
     * Dropdown widget: HTML con las tareas ancladas pendientes del usuario.
     */
    public function widget()
    {
        $user = Auth::user();

        $tareas = $user->tareasAncladas()
            ->whereIn('estado', ['pendiente', 'en_curso', 'bloqueada'])
            ->with(['asignado:id,name'])
            ->orderByRaw("FIELD(estado, 'en_curso', 'pendiente', 'bloqueada')")
            ->orderByRaw("FIELD(prioridad, 'alta', 'media', 'baja')")
            ->orderByRaw('fecha_limite IS NULL, fecha_limite ASC')
            ->limit(15)
            ->get();

        return view('tareas.widget', compact('tareas'));
    }

    /**
     * Cambiar estado puntual via POST simple.
     */
    public function cambiarEstado(Request $request, $id)
    {
        $user = Auth::user();
        $tarea = Tarea::findOrFail($id);

        if (!$user->canEditarTarea($tarea)) {
            abort(403);
        }

        $data = $request->validate([
            'estado' => 'required|in:' . implode(',', array_keys(Tarea::ESTADOS)),
        ]);

        $payload = ['estado' => $data['estado']];
        if ($data['estado'] === 'hecha' && $tarea->estado !== 'hecha') {
            $payload['completada_at'] = now();
            $payload['completada_por'] = $user->id;
        } elseif ($data['estado'] !== 'hecha') {
            $payload['completada_at'] = null;
            $payload['completada_por'] = null;
        }

        $tarea->update($payload);

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }
        return back();
    }
}
