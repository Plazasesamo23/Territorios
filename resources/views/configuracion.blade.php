@extends('layouts.app')

@section('title', 'Configuración - Gestión de Territorios')

@section('content')
<style>
    .tipo-config-section {
        background: #f8fafc;
        border-radius: 12px;
        padding: 1.25rem;
        margin-bottom: 1rem;
        border: 1px solid #e2e8f0;
    }
    .tipo-config-section.normal {
        border-left: 4px solid #10b981;
    }
    .tipo-config-section.campana {
        border-left: 4px solid #f59e0b;
    }
    .tipo-config-section.negocios {
        border-left: 4px solid #3b82f6;
    }
    .tipo-config-title {
        font-weight: 600;
        font-size: 1rem;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .tipo-config-section.normal .tipo-config-title { color: #059669; }
    .tipo-config-section.campana .tipo-config-title { color: #d97706; }
    .tipo-config-section.negocios .tipo-config-title { color: #2563eb; }
    .config-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem;
        background: white;
        border-radius: 8px;
        margin-bottom: 0.5rem;
        border: 1px solid #e5e7eb;
    }
    .config-row:last-child {
        margin-bottom: 0;
    }
    .config-label {
        font-weight: 500;
        color: #374151;
    }
    .config-description {
        font-size: 0.8rem;
        color: #6b7280;
    }
    .config-input-group {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .config-input {
        width: 80px;
        padding: 0.5rem;
        border: 1px solid #d1d5db;
        border-radius: 4px;
        text-align: center;
    }
    .config-unit {
        font-size: 0.875rem;
        color: #6b7280;
    }
</style>

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
            <span>&#9989;</span>
            <span>{{ session('success') }}</span>
        </div>
    </div>
@endif

@if(session('error'))
    <div style="padding: 1rem; background: #fee2e2; border: 1px solid #fecaca; border-radius: 8px; margin-bottom: 1rem; color: #991b1b;">
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <span>&#10060;</span>
            <span>{{ session('error') }}</span>
        </div>
    </div>
@endif

<!-- Configuración General -->
<div class="card mb-4">
    <div class="card-title">&#9881; Configuración de {{ $congregacion->nombre ?? 'Congregación' }}</div>
    <div class="card-description">Administra los parámetros de asignación de territorios para tu congregación</div>

    <h3 style="font-weight: 600; margin-bottom: 1rem; color: #374151;">Parámetros de Territorios por Tipo</h3>

    <form method="POST" action="{{ route('configuracion.guardar') }}">
        @csrf

        <!-- Territorios Normales -->
        <div class="tipo-config-section normal">
            <div class="tipo-config-title">
                <span>&#127968;</span> Territorios Normales
                <span style="font-size: 0.75rem; padding: 2px 8px; background: #d1fae5; color: #065f46; border-radius: 12px; margin-left: 0.5rem;">Incluidos en S-13</span>
            </div>
            <div class="config-row">
                <div>
                    <div class="config-label">Tiempo límite activo</div>
                    <div class="config-description">Días máximos asignado antes de marcarse como atrasado</div>
                </div>
                <div class="config-input-group">
                    <input type="number" name="dias_limite_activo" class="config-input" value="{{ $congregacion->dias_limite_activo ?? 120 }}" min="1" max="365">
                    <span class="config-unit">días</span>
                </div>
            </div>
            <div class="config-row">
                <div>
                    <div class="config-label">Tiempo en archivo</div>
                    <div class="config-description">Días de descanso después de devolución</div>
                </div>
                <div class="config-input-group">
                    <input type="number" name="dias_archivo" class="config-input" value="{{ $congregacion->dias_archivo ?? 90 }}" min="1" max="365">
                    <span class="config-unit">días</span>
                </div>
            </div>
        </div>

        <!-- Territorios de Campaña -->
        <div class="tipo-config-section campana">
            <div class="tipo-config-title">
                <span>&#128227;</span> Territorios de Campaña
                <span style="font-size: 0.75rem; padding: 2px 8px; background: #fef3c7; color: #92400e; border-radius: 12px; margin-left: 0.5rem;">No en S-13</span>
            </div>
            <div class="config-row">
                <div>
                    <div class="config-label">Tiempo límite activo</div>
                    <div class="config-description">Días máximos asignado antes de marcarse como atrasado</div>
                </div>
                <div class="config-input-group">
                    <input type="number" name="dias_limite_activo_campana" class="config-input" value="{{ $congregacion->dias_limite_activo_campana ?? 30 }}" min="1" max="365">
                    <span class="config-unit">días</span>
                </div>
            </div>
            <div class="config-row">
                <div>
                    <div class="config-label">Tiempo en archivo</div>
                    <div class="config-description">Días de descanso después de devolución</div>
                </div>
                <div class="config-input-group">
                    <input type="number" name="dias_archivo_campana" class="config-input" value="{{ $congregacion->dias_archivo_campana ?? 30 }}" min="1" max="365">
                    <span class="config-unit">días</span>
                </div>
            </div>
        </div>

        <!-- Territorios de Negocios -->
        <div class="tipo-config-section negocios">
            <div class="tipo-config-title">
                <span>&#127970;</span> Territorios de Negocios
                <span style="font-size: 0.75rem; padding: 2px 8px; background: #dbeafe; color: #1e40af; border-radius: 12px; margin-left: 0.5rem;">No en S-13</span>
            </div>
            <div class="config-row">
                <div>
                    <div class="config-label">Tiempo límite activo</div>
                    <div class="config-description">Días máximos asignado antes de marcarse como atrasado</div>
                </div>
                <div class="config-input-group">
                    <input type="number" name="dias_limite_activo_negocios" class="config-input" value="{{ $congregacion->dias_limite_activo_negocios ?? 60 }}" min="1" max="365">
                    <span class="config-unit">días</span>
                </div>
            </div>
            <div class="config-row">
                <div>
                    <div class="config-label">Tiempo en archivo</div>
                    <div class="config-description">Días de descanso después de devolución</div>
                </div>
                <div class="config-input-group">
                    <input type="number" name="dias_archivo_negocios" class="config-input" value="{{ $congregacion->dias_archivo_negocios ?? 60 }}" min="1" max="365">
                    <span class="config-unit">días</span>
                </div>
            </div>
        </div>

        <!-- Botón guardar -->
        <div style="margin-top: 1.5rem;">
            <button type="submit" class="btn btn-primary">
                &#128190; Guardar Configuración
            </button>
        </div>
    </form>

    <div style="display: grid; grid-template-columns: 1fr; gap: 1rem; margin-top: 2rem;">
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
    <div class="card-title">&#128172; Configuración de WhatsApp</div>
    <div class="card-description">Personaliza el mensaje que se envía al asignar territorios</div>

    <form method="POST" action="{{ route('configuracion.guardar-whatsapp') }}">
        @csrf

        <!-- Estado WhatsApp -->
        <div style="padding: 1rem; background: #dcfce7; border: 1px solid #bbf7d0; border-radius: 8px; margin-bottom: 1rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div style="color: #16a34a; font-size: 1.25rem;">&#9989;</div>
                <div>
                    <div style="font-weight: 600; color: #15803d;">WhatsApp habilitado</div>
                    <div style="font-size: 0.875rem; color: #166534;">Los territorios con imagen pueden enviarse directamente por WhatsApp</div>
                </div>
            </div>
        </div>

        <!-- Plantilla mensaje -->
        <div style="margin-bottom: 1rem;">
            <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #374151;">Plantilla de mensaje</label>
            <textarea name="mensaje_whatsapp" rows="6" style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.875rem; font-family: monospace;">{{ $congregacion->mensaje_whatsapp ?? \App\Models\Congregacion::getMensajeWhatsappDefault() }}</textarea>

            <div style="margin-top: 0.75rem; padding: 1rem; background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 6px;">
                <div style="font-weight: 600; color: #0369a1; margin-bottom: 0.5rem;">Variables disponibles:</div>
                <div style="font-size: 0.875rem; color: #0c4a6e; display: grid; gap: 0.25rem;">
                    <div><code style="background: #e0f2fe; padding: 0.125rem 0.375rem; border-radius: 3px;">{nombre}</code> - Nombre del publicador</div>
                    <div><code style="background: #e0f2fe; padding: 0.125rem 0.375rem; border-radius: 3px;">{nombre_completo}</code> - Nombre y apellidos</div>
                    <div><code style="background: #e0f2fe; padding: 0.125rem 0.375rem; border-radius: 3px;">{numero}</code> - Número del territorio</div>
                    <div><code style="background: #e0f2fe; padding: 0.125rem 0.375rem; border-radius: 3px;">{territorio_nombre}</code> - Nombre del territorio</div>
                    <div><code style="background: #e0f2fe; padding: 0.125rem 0.375rem; border-radius: 3px;">{imagen_url}</code> - URL de la imagen</div>
                </div>
            </div>
        </div>

        <!-- Vista previa -->
        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #374151;">Vista previa del mensaje:</label>
            <div id="preview-mensaje" style="padding: 1rem; background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 6px; font-size: 0.875rem; white-space: pre-wrap; color: #374151;">
                {{ str_replace(['{nombre}', '{nombre_completo}', '{numero}', '{territorio_nombre}', '{imagen_url}'], ['Juan', 'Juan Pérez', '42', 'Centro Ciudad', 'https://example.com/imagen.jpg'], $congregacion->mensaje_whatsapp ?? \App\Models\Congregacion::getMensajeWhatsappDefault()) }}
            </div>
        </div>

        <!-- Botones -->
        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
            <button type="submit" class="btn btn-success">
                &#128190; Guardar Mensaje
            </button>
            <button type="button" onclick="restaurarMensajeDefault()" class="btn btn-secondary">
                &#128260; Restaurar por Defecto
            </button>
        </div>
    </form>
</div>

<script>
    // Actualizar vista previa en tiempo real
    const textarea = document.querySelector('textarea[name="mensaje_whatsapp"]');
    const preview = document.getElementById('preview-mensaje');

    if (textarea && preview) {
        textarea.addEventListener('input', function() {
            let mensaje = this.value;
            mensaje = mensaje.replace(/{nombre}/g, 'Juan');
            mensaje = mensaje.replace(/{nombre_completo}/g, 'Juan Pérez');
            mensaje = mensaje.replace(/{numero}/g, '42');
            mensaje = mensaje.replace(/{territorio_nombre}/g, 'Centro Ciudad');
            mensaje = mensaje.replace(/{imagen_url}/g, 'https://example.com/imagen.jpg');
            preview.textContent = mensaje;
        });
    }

    function restaurarMensajeDefault() {
        const mensajeDefault = `Querido/a {nombre}, aquí te mando el territorio asignado. Solo recordar que cuando lo termines de trabajar lo borres del teléfono y me avises. También recuerda que este territorio dura 3 meses, por lo tanto, puedes disfrutar y hacer uso de el por todo este tiempo, te animamos a poder trabajarlo a plenitud y tener conversaciones de provecho con las personas, así, podrás disfrutar por completo de tu ministerio.

Territorio #{numero}

Imagen del territorio:
{imagen_url}`;
        textarea.value = mensajeDefault;
        textarea.dispatchEvent(new Event('input'));
    }
</script>

<!-- Estadísticas del Sistema -->
<div class="card mb-4">
    <div class="card-title">&#128202; Estadísticas del Sistema</div>
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
        <div class="card-title">&#128295; Mantenimiento</div>
        <div class="card-description">Herramientas de administración del sistema</div>

        <div style="display: grid; gap: 0.75rem; margin-top: 1rem;">
            <a href="{{ route('referencia-ui') }}" class="btn btn-primary w-full">
                &#127912; Referencia Visual UI
            </a>
            <button class="btn btn-secondary w-full">
                &#128228; Exportar Datos
            </button>
            <button class="btn btn-secondary w-full">
                &#128229; Importar Datos
            </button>
            <button class="btn btn-warning w-full">
                &#128465; Limpiar Registros Antiguos
            </button>
            <button class="btn btn-danger w-full">
                &#9888; Reiniciar Base de Datos
            </button>
        </div>
    </div>

    <!-- Información del Sistema -->
    <div class="card">
        <div class="card-title">&#8505; Información del Sistema</div>
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

    .config-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }

    .config-input-group {
        width: 100%;
        justify-content: flex-end;
    }
}
</style>
@endsection
