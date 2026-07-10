@extends('layouts.app')

@section('title', 'Configuración - Gestión de Territorios')

@section('content')

<!-- Navegación minimalista -->
<nav class="page-nav">
    <div class="page-breadcrumbs">
        <span class="breadcrumb-current">Configuracion</span>
    </div>
</nav>

<!-- Configuración General -->
<div class="card mb-4">
    <div class="card-title">&#9881; Configuración de {{ $congregacion->nombre ?? 'Congregación' }}</div>
    <div class="card-description">Administra los parámetros de asignación de territorios para tu congregación</div>

    <h3 class="font-medium mb-2" style="color: var(--text);">Parametros de Territorios por Tipo</h3>

    <form method="POST" action="{{ route('configuracion.guardar') }}">
        @csrf

        <!-- Territorios Normales -->
        <div class="tipo-config-section normal">
            <div class="tipo-config-title">
                <span>&#127968;</span> Territorios Normales
                <span class="badge badge-primary" style="margin-left: 0.5rem;">Incluidos en S-13</span>
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
                <span class="badge" style="margin-left: 0.5rem;">No en S-13</span>
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
                <span class="badge badge-primary" style="margin-left: 0.5rem;">No en S-13</span>
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

            <div class="mt-2">
            <button type="submit" class="btn btn-primary">
                &#128190; Guardar Configuración
            </button>
        </div>
    </form>

</div>

<!-- Configuración de WhatsApp -->
<div class="card mb-4">
    <div class="card-title">&#128172; Configuración de WhatsApp</div>
    <div class="card-description">Personaliza el mensaje que se envía al asignar territorios</div>

    <form method="POST" action="{{ route('configuracion.guardar-whatsapp') }}">
        @csrf

        <div class="config-info-box mb-2">
            <strong>WhatsApp habilitado</strong> - Los territorios con imagen pueden enviarse directamente por WhatsApp
        </div>

        <div class="form-group mb-2">
            <label class="form-label">Plantilla de mensaje</label>
            <textarea name="mensaje_whatsapp" rows="6" class="form-input" style="font-family: monospace;">{{ $congregacion->mensaje_whatsapp ?? \App\Models\Congregacion::getMensajeWhatsappDefault() }}</textarea>

            <div class="config-info-box mt-1">
                <strong>Variables disponibles:</strong>
                <code>{nombre}</code> <code>{nombre_completo}</code> <code>{numero}</code> <code>{territorio_nombre}</code> <code>{imagen_url}</code>
            </div>
        </div>

        <div class="form-group mb-2">
            <label class="form-label">Vista previa del mensaje:</label>
            <div id="preview-mensaje" class="config-preview">
                {{ str_replace(['{nombre}', '{nombre_completo}', '{numero}', '{territorio_nombre}', '{imagen_url}'], ['Juan', 'Juan Pérez', '42', 'Centro Ciudad', 'https://example.com/imagen.jpg'], $congregacion->mensaje_whatsapp ?? \App\Models\Congregacion::getMensajeWhatsappDefault()) }}
            </div>
        </div>

        <div class="flex gap-1 flex-wrap">
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

    <div class="stats-row mb-2">
        <div class="stat-item">
            <span class="stat-number primary">{{ $statsConfig['totalTerritorios'] ?? 0 }}</span>
            <span class="stat-label">Territorios</span>
        </div>
        <div class="stat-item">
            <span class="stat-number">{{ $statsConfig['publicadoresActivos'] ?? 0 }}</span>
            <span class="stat-label">Publicadores</span>
        </div>
        <div class="stat-item">
            <span class="stat-number">{{ $statsConfig['asignacionesActivas'] ?? 0 }}</span>
            <span class="stat-label">Asignaciones</span>
        </div>
        <div class="stat-item">
            <span class="stat-number">{{ $statsConfig['totalRegistros'] ?? 0 }}</span>
            <span class="stat-label">Registros</span>
        </div>
    </div>
</div>

<!-- Mantenimiento y Herramientas -->
<div class="grid grid-2">
    <!-- Mantenimiento -->
    <div class="card">
        <div class="card-title">&#128295; Mantenimiento</div>
        <div class="card-description">Herramientas de administración del sistema</div>

        <div class="config-actions mt-2">
            @can('superadmin')
            <a href="{{ route('referencia-ui') }}" class="btn btn-secondary w-full">
                Referencia Visual UI (desarrollo)
            </a>
            @endcan
            <div class="text-muted" style="font-size:0.85rem;">
                Las herramientas de exportación e importación de datos estarán disponibles próximamente.
            </div>
        </div>
    </div>

    <!-- Información del Sistema -->
    <div class="card">
        <div class="card-title">&#8505; Información del Sistema</div>
        <div class="card-description">Detalles técnicos y versión</div>

        <div class="info-list mt-2">
            <div class="info-row"><span class="text-muted">Version:</span> <strong>1.0.0</strong></div>
            <div class="info-row"><span class="text-muted">Laravel:</span> <strong>{{ app()->version() }}</strong></div>
            <div class="info-row"><span class="text-muted">PHP:</span> <strong>{{ phpversion() }}</strong></div>
            <div class="info-row"><span class="text-muted">Base de Datos:</span> <strong>MySQL</strong></div>
        </div>
    </div>
</div>

<style>
@media (max-width: 768px) {
    .grid.grid-2 { grid-template-columns: 1fr; }
    .grid.grid-4 { grid-template-columns: repeat(2, 1fr); }
    .config-row { flex-direction: column; align-items: flex-start; gap: 0.5rem; }
    .config-input-group { width: 100%; justify-content: flex-end; }
}
</style>
@endsection
