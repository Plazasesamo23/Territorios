@extends('layouts.app')

@section('title', 'Registro #{{ $registro->id }} - Gestión de Territorios')

@section('content')
<!-- Navegación minimalista -->
<nav class="page-nav">
    <div class="page-breadcrumbs">
        <a href="{{ route('dashboard') }}" class="breadcrumb-link">Dashboard</a>
        <span class="breadcrumb-separator">›</span>
        <a href="{{ route('registros.index') }}" class="breadcrumb-link">Registros</a>
        <span class="breadcrumb-separator">›</span>
        <span class="breadcrumb-current">Registro #{{ $registro->id }}</span>
    </div>
    <div class="page-actions">
        <a href="{{ route('registros.index') }}" class="btn btn-secondary">
            ← Volver
        </a>
        <button onclick="toggleEdit()" class="btn btn-primary" id="editBtn">
            ✏️ Editar
        </button>
        <button onclick="saveChanges()" class="btn btn-success" id="saveBtn" style="display: none;">
            💾 Guardar
        </button>
        <button onclick="cancelEdit()" class="btn btn-secondary" id="cancelBtn" style="display: none;">
            ❌ Cancelar
        </button>
        @if(!$registro->fecha_entrada)
            <button onclick="showDateModal()" class="btn btn-primary">
                ✅ Marcar Devuelto
            </button>
        @endif
    </div>
</nav>

<!-- Información principal -->
<div class="card">
    <div class="card-title">
        Registro #{{ $registro->id }}
        <div style="float: right;">
            @if($registro->fecha_entrada)
                <span class="badge" style="background: #dcfce7; color: #166534;">✅ DEVUELTO</span>
            @else
                @php $estado = $registro->territorio->calcularEstado(); @endphp
                @if($estado === 'activo')
                    <span class="badge" style="background: #e8eef6; color: #2d4266;">🔵 ACTIVO</span>
                @elseif($estado === 'atrasado')
                    <span class="badge" style="background: #f8f9fa; color: #343a40;">🔴 ATRASADO</span>
                @endif
            @endif
        </div>
    </div>

    <!-- Formulario de edición (oculto) -->
    <form id="editForm" action="{{ route('registros.update', $registro) }}" method="POST" style="display: none;">
        @csrf
        @method('PUT')
        
        <div class="grid grid-2 gap-4 mb-4">
            <div>
                <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Fecha de Salida:</label>
                <input type="date" name="fecha_salida" value="{{ $registro->fecha_salida->format('Y-m-d') }}" 
                       style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px;">
            </div>
            
            @if($registro->fecha_entrada)
                <div>
                    <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Fecha de Entrada:</label>
                    <input type="date" name="fecha_entrada" value="{{ $registro->fecha_entrada->format('Y-m-d') }}" 
                           style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px;">
                </div>
            @endif
        </div>

        <div class="mb-4">
            <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Notas:</label>
            <textarea name="notas" rows="3" 
                      style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px;">{{ $registro->notas }}</textarea>
        </div>
    </form>

    <!-- Vista de solo lectura -->
    <div id="readOnlyView">
        <div class="grid grid-3 gap-4 mb-4">
            <!-- Territorio -->
            <div>
                <strong>Territorio:</strong>
                <div style="margin-top: 0.5rem;">
                    <a href="{{ route('territorios.show', $registro->territorio) }}" 
                       style="font-size: 1.25rem; font-weight: 600; color: #4a6da7; text-decoration: none;">
                        T{{ $registro->territorio->numero }}
                    </a>
                    @if($registro->territorio->nombre)
                        <div style="font-size: 0.875rem; color: #6b7280; margin-top: 0.25rem;">
                            {{ $registro->territorio->nombre }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Publicador -->
            <div>
                <strong>Publicador:</strong>
                <div style="margin-top: 0.5rem;">
                    <a href="{{ route('publicadores.show', $registro->publicador) }}" 
                       style="font-weight: 600; color: #374151; text-decoration: none;">
                        {{ $registro->publicador->nombre_completo }}
                    </a>
                    <div style="font-size: 0.875rem; color: #6b7280; margin-top: 0.25rem;">
                        {{ $registro->publicador->telefono }}
                    </div>
                </div>
            </div>

            <!-- Días -->
            <div>
                <strong>Duración:</strong>
                <div style="margin-top: 0.5rem; font-size: 1.25rem; font-weight: 600;">
                    @if($registro->fecha_entrada)
                        {{ $registro->fecha_salida->diffInDays($registro->fecha_entrada) }} días
                    @else
                        {{ $registro->fecha_salida->diffInDays(now()) }} días
                    @endif
                </div>
                <div style="font-size: 0.875rem; color: #6b7280;">
                    @if($registro->fecha_entrada)
                        Completado
                    @else
                        En progreso
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-2 gap-4 mb-4">
            <!-- Fechas -->
            <div>
                <strong>Fechas:</strong>
                <div style="margin-top: 0.5rem;">
                    <div>📅 Salida: {{ $registro->fecha_salida->format('d/m/Y') }}</div>
                    @if($registro->fecha_entrada)
                        <div>📥 Entrada: {{ $registro->fecha_entrada->format('d/m/Y') }}</div>
                    @else
                        <div style="color: #6b7280;">📥 Entrada: Pendiente</div>
                    @endif
                </div>
            </div>

            <!-- Enlaces rápidos -->
            <div>
                <strong>Enlaces:</strong>
                <div style="margin-top: 0.5rem; display: flex; gap: 0.5rem; flex-wrap: wrap;">
                    <a href="{{ route('territorios.show', $registro->territorio) }}" class="btn btn-secondary" style="padding: 0.5rem 0.75rem;">
                        🗺️ Territorio
                    </a>
                    <a href="{{ route('publicadores.show', $registro->publicador) }}" class="btn btn-secondary" style="padding: 0.5rem 0.75rem;">
                        👤 Publicador
                    </a>
                    @php
                        $congregacion = \App\Models\Congregacion::find(session('congregacion_activa_id'));
                        $mensajeWhatsapp = $congregacion ? $congregacion->getMensajeWhatsappFormateado($registro->publicador, $registro->territorio) : "Hola " . $registro->publicador->nombre . ", te envío el territorio " . $registro->territorio->numero;
                    @endphp
                    <a href="https://wa.me/{{ str_replace(['+', ' ', '-'], '', $registro->publicador->telefono) }}?text={{ urlencode($mensajeWhatsapp) }}"
                       target="_blank" class="btn btn-success" style="padding: 0.5rem 0.75rem;">
                        💬 WhatsApp
                    </a>
                </div>
            </div>
        </div>

        @if($registro->notas)
            <div>
                <strong>Notas:</strong>
                <div style="margin-top: 0.5rem; background: #f9fafb; padding: 0.75rem; border-radius: 6px; border: 1px solid #e5e7eb;">
                    {{ $registro->notas }}
                </div>
            </div>
        @endif
    </div>

    <!-- Zona de peligro (solo visible en modo edición) -->
    <div id="dangerZone" style="display: none; margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #495057;">
        <div style="margin-bottom: 1rem;">
            <strong style="color: #495057;">⚠️ Zona de Peligro</strong>
            <div style="font-size: 0.875rem; color: #6b7280;">Estas acciones no se pueden deshacer</div>
        </div>
        
        <form action="{{ route('registros.destroy', $registro) }}" method="POST" class="inline"
              onsubmit="return confirm('¿Estás COMPLETAMENTE SEGURO de eliminar este registro? Esta acción no se puede deshacer.')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn" style="background: #495057; color: white; padding: 0.75rem 1.5rem;">
                🗑️ Eliminar Registro
            </button>
        </form>
    </div>
</div>

<!-- Modal para marcar como devuelto -->
<div id="dateModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 2rem; border-radius: 8px; min-width: 300px;">
        <h3 style="margin-bottom: 1rem;">Marcar Territorio como Devuelto</h3>
        
        <form action="{{ route('registros.entrada', $registro) }}" method="POST">
            @csrf
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; font-weight: 600; margin-bottom: 0.5rem;">Fecha de Devolución:</label>
                <input type="date" name="fecha_entrada" value="{{ date('Y-m-d') }}" 
                       style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px;">
            </div>
            
            <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                <button type="button" onclick="hideDateModal()" class="btn btn-secondary">
                    Cancelar
                </button>
                <button type="submit" class="btn btn-success">
                    ✅ Confirmar Devolución
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleEdit() {
    const readOnlyView = document.getElementById('readOnlyView');
    const editForm = document.getElementById('editForm');
    const editBtn = document.getElementById('editBtn');
    const saveBtn = document.getElementById('saveBtn');
    const cancelBtn = document.getElementById('cancelBtn');
    const dangerZone = document.getElementById('dangerZone');
    
    readOnlyView.style.display = 'none';
    editForm.style.display = 'block';
    editBtn.style.display = 'none';
    saveBtn.style.display = 'inline-block';
    cancelBtn.style.display = 'inline-block';
    dangerZone.style.display = 'block';
}

function cancelEdit() {
    const readOnlyView = document.getElementById('readOnlyView');
    const editForm = document.getElementById('editForm');
    const editBtn = document.getElementById('editBtn');
    const saveBtn = document.getElementById('saveBtn');
    const cancelBtn = document.getElementById('cancelBtn');
    const dangerZone = document.getElementById('dangerZone');
    
    readOnlyView.style.display = 'block';
    editForm.style.display = 'none';
    editBtn.style.display = 'inline-block';
    saveBtn.style.display = 'none';
    cancelBtn.style.display = 'none';
    dangerZone.style.display = 'none';
}

function saveChanges() {
    document.getElementById('editForm').submit();
}

function showDateModal() {
    document.getElementById('dateModal').style.display = 'block';
}

function hideDateModal() {
    document.getElementById('dateModal').style.display = 'none';
}

// Cerrar modal con Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        hideDateModal();
    }
});
</script>
@endsection 