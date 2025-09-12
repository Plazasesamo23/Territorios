@extends('layouts.app')

@section('title', 'Configuración - Gestión de Territorios')

@section('content')
<!-- Navegación minimalista -->
<nav class="page-nav">
    <div class="page-breadcrumbs">
        <a href="{{ route('dashboard') }}" class="breadcrumb-link">Dashboard</a>
        <span class="breadcrumb-separator">›</span>
        <span class="breadcrumb-current">Configuración</span>
    </div>
</nav>

<!-- Mensaje de éxito -->
@if(session('success'))
    <div style="padding: 1rem; background: #dcfce7; border: 1px solid #bbf7d0; border-radius: 8px; margin-bottom: 1rem; color: #166534;">
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <span>✅</span>
            <span>{{ session('success') }}</span>
        </div>
    </div>
@endif

<!-- Configuración General -->
<div class="card mb-4">
    <div class="card-title">⚙️ Configuración General</div>
    <div class="card-description">Administra la configuración del sistema de territorios</div>
    
    <h3 style="font-weight: 600; margin-bottom: 1rem; color: #374151;">Configuración Básica</h3>
    
    <form method="POST" action="{{ route('configuracion.guardar') }}">
        @csrf
        <div style="display: grid; grid-template-columns: 1fr; gap: 1rem; margin-bottom: 2rem;">
        <!-- Tiempo límite activo -->
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; background: #f8fafc; border-radius: 8px; border: 1px solid #e5e7eb;">
            <div>
                <div style="font-weight: 600; color: #374151; margin-bottom: 0.25rem;">Tiempo límite de territorios activos</div>
                <div style="font-size: 0.875rem; color: #6b7280;">Días máximos que un territorio puede estar asignado (activo → atrasado)</div>
            </div>
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <input type="number" name="dias_limite_activo" value="{{ config('territorios.dias_limite_activo', 60) }}" style="width: 80px; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 4px; text-align: center;" min="1" max="365">
                <span style="font-size: 0.875rem; color: #6b7280;">días</span>
            </div>
        </div>
        
        <!-- Tiempo archivo -->
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; background: #f8fafc; border-radius: 8px; border: 1px solid #e5e7eb;">
            <div>
                <div style="font-weight: 600; color: #374151; margin-bottom: 0.25rem;">Tiempo en archivo</div>
                <div style="font-size: 0.875rem; color: #6b7280;">Días que un territorio devuelto permanece archivado antes de estar libre</div>
            </div>
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <input type="number" name="dias_archivo" value="{{ config('territorios.dias_archivo', 30) }}" style="width: 80px; padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 4px; text-align: center;" min="1" max="365">
                <span style="font-size: 0.875rem; color: #6b7280;">días</span>
            </div>
        </div>
        
        <!-- Botón guardar -->
        <div style="margin-top: 1.5rem;">
            <button type="submit" class="btn btn-primary">
                💾 Guardar Configuración
            </button>
        </div>
    </form>
        
        <!-- Notificaciones -->
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; background: #f8fafc; border-radius: 8px; border: 1px solid #e5e7eb;">
            <div>
                <div style="font-weight: 600; color: #374151; margin-bottom: 0.25rem;">Notificaciones automáticas</div>
                <div style="font-size: 0.875rem; color: #6b7280;">Avisar cuando los territorios estén próximos a vencer</div>
            </div>
            <input type="checkbox" checked style="width: 1.2rem; height: 1.2rem;">
        </div>
        
        <!-- Modo visualización -->
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; background: #f8fafc; border-radius: 8px; border: 1px solid #e5e7eb;">
            <div>
                <div style="font-weight: 600; color: #374151; margin-bottom: 0.25rem;">Modo de visualización</div>
                <div style="font-size: 0.875rem; color: #6b7280;">Formato de presentación de territorios</div>
            </div>
            <select style="padding: 0.5rem; border: 1px solid #d1d5db; border-radius: 4px; background: white;">
                <option>Tarjetas (Grid)</option>
                <option>Lista</option>
                <option>Tabla</option>
            </select>
        </div>
    </div>
</div>

<!-- Configuración de WhatsApp -->
<div class="card mb-4">
    <div class="card-title">💬 Configuración de WhatsApp</div>
    
    <!-- Estado WhatsApp -->
    <div style="padding: 1rem; background: #dcfce7; border: 1px solid #bbf7d0; border-radius: 8px; margin-bottom: 1rem;">
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <div style="color: #16a34a; font-size: 1.25rem;">✅</div>
            <div>
                <div style="font-weight: 600; color: #15803d;">WhatsApp habilitado</div>
                <div style="font-size: 0.875rem; color: #166534;">Los territorios con imagen pueden enviarse directamente por WhatsApp</div>
            </div>
        </div>
    </div>
    
    <!-- Plantilla mensaje -->
    <div>
        <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #374151;">Plantilla de mensaje</label>
        <textarea rows="4" style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.875rem;" placeholder="Territorio #{numero}

Imagen: {imagen_url}

Saludos!">Territorio #{numero}

Imagen: {imagen_url}

¡Que tengas un buen día en el servicio!</textarea>
        <div style="margin-top: 0.5rem; font-size: 0.75rem; color: #6b7280;">
            Variables disponibles: {numero}, {imagen_url}, {notas}
        </div>
    </div>
</div>

<!-- Estadísticas del Sistema -->
<div class="card mb-4">
    <div class="card-title">📊 Estadísticas del Sistema</div>
    <div class="card-description">Información general sobre el uso del sistema</div>
    
    <div class="grid grid-4">
        <div class="stat-card">
            <div class="stat-number" style="color: #3b82f6;">{{ \App\Models\Territorio::count() }}</div>
            <div class="stat-label">Total Territorios</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" style="color: #10b981;">{{ \App\Models\Publicador::where('activo', true)->count() }}</div>
            <div class="stat-label">Publicadores Activos</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" style="color: #f59e0b;">{{ \App\Models\Registro::whereNull('fecha_entrada')->count() }}</div>
            <div class="stat-label">Asignaciones Activas</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" style="color: #8b5cf6;">{{ \App\Models\Registro::count() }}</div>
            <div class="stat-label">Total Registros</div>
        </div>
    </div>
</div>

<!-- Mantenimiento y Herramientas -->
<div class="grid grid-2">
    <!-- Mantenimiento -->
    <div class="card">
        <div class="card-title">🔧 Mantenimiento</div>
        <div class="card-description">Herramientas de administración del sistema</div>
        
        <div style="display: grid; gap: 0.75rem; margin-top: 1rem;">
            <a href="{{ route('referencia-ui') }}" class="btn btn-primary w-full">
                🎨 Referencia Visual UI
            </a>
            <button class="btn btn-secondary w-full">
                📤 Exportar Datos
            </button>
            <button class="btn btn-secondary w-full">
                📥 Importar Datos
            </button>
            <button class="btn btn-warning w-full">
                🗑️ Limpiar Registros Antiguos
            </button>
            <button class="btn btn-danger w-full">
                ⚠️ Reiniciar Base de Datos
            </button>
        </div>
    </div>
    
    <!-- Información del Sistema -->
    <div class="card">
        <div class="card-title">ℹ️ Información del Sistema</div>
        <div class="card-description">Detalles técnicos y versión</div>
        
        <div style="margin-top: 1rem;">
            <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #e5e7eb;">
                <span style="font-weight: 500; color: #6b7280;">Versión:</span>
                <span style="color: #374151;">1.0.0</span>
            </div>
            <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #e5e7eb;">
                <span style="font-weight: 500; color: #6b7280;">Laravel:</span>
                <span style="color: #374151;">{{ app()->version() }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #e5e7eb;">
                <span style="font-weight: 500; color: #6b7280;">PHP:</span>
                <span style="color: #374151;">{{ phpversion() }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; padding: 0.5rem 0;">
                <span style="font-weight: 500; color: #6b7280;">Base de Datos:</span>
                <span style="color: #374151;">MySQL</span>
            </div>
        </div>
    </div>
</div>

<style>
.w-full {
    width: 100%;
}

.btn-warning {
    background: #f59e0b;
    color: white;
    border: 1px solid #f59e0b;
}

.btn-warning:hover {
    background: #d97706;
    border-color: #d97706;
}

.btn-danger {
    background: #ef4444;
    color: white;
    border: 1px solid #ef4444;
}

.btn-danger:hover {
    background: #dc2626;
    border-color: #dc2626;
}

/* Responsive */
@media (max-width: 768px) {
    .grid.grid-2 {
        grid-template-columns: 1fr;
    }
    
    .grid.grid-4 {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>
@endsection 