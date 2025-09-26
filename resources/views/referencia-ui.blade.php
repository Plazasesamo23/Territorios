@extends('layouts.app')

@section('title', 'Referencia UI - Gestión de Territorios')

@section('content')

<div class="page-nav">
    <div class="page-breadcrumbs">
        <a href="{{ route('dashboard') }}" class="breadcrumb-link">Dashboard</a>
        <span class="breadcrumb-sep">›</span>
        <a href="{{ route('configuracion') }}" class="breadcrumb-link">Configuración</a>
        <span class="breadcrumb-sep">›</span>
        <span class="breadcrumb-current">Referencia UI</span>
    </div>
    
    <div class="page-actions">
        <button id="demo-theme-toggle" class="btn btn-secondary">
            🌙 Cambiar Tema Demo
        </button>
    </div>
</div>

<div class="page-header">
    <h1 class="page-title">🎨 Referencia Visual de Componentes UI</h1>
    <p class="page-subtitle">Sistema de diseño completo con soporte para modo claro y oscuro</p>
</div>

<!-- 0. Sistema de Modo Oscuro -->
<div class="card mb-6">
    <h2 class="card-title">🌙 Sistema de Temas (Claro/Oscuro)</h2>
    <div class="grid grid-2 mb-4">
        <div>
            <h3 class="card-subtitle">Variables CSS Automáticas</h3>
            <p class="text-muted mb-4">El sistema utiliza variables CSS que cambian automáticamente según el tema activo.</p>
            <div class="code-block mb-4" style="background: var(--color-gray-100); padding: 1rem; border-radius: 8px; font-family: monospace; font-size: 0.875rem;">
                --bg-body: #f8fafc → #0f172a<br>
                --bg-card: #ffffff → #1e293b<br>
                --text-primary: #333333 → #f1f5f9<br>
                --border-color: #e5e7eb → #334155
            </div>
        </div>
        <div>
            <h3 class="card-subtitle">Toggle en Header</h3>
            <p class="text-muted mb-4">Usa el botón 🌙/☀️ en el header para cambiar entre temas. La preferencia se guarda en localStorage.</p>
            <div class="alert alert-success">
                <strong>¡Funcional!</strong> El cambio de tema afecta toda la aplicación instantáneamente.
            </div>
        </div>
    </div>
</div>

<!-- 1. Botones -->
<div class="card mb-6">
    <h2 class="card-title">🔘 Botones</h2>
    <div class="grid grid-3 mb-4">
        <div>
            <h3 class="card-subtitle">Botones Principales</h3>
            <div class="dashboard-column">
                <button class="btn btn-primary">Primario</button>
                <button class="btn btn-success">Éxito</button>
                <button class="btn btn-secondary">Secundario</button>
                <button class="btn btn-danger">Peligro</button>
                <button class="btn btn-outline">Outline</button>
            </div>
        </div>
        <div>
            <h3 class="card-subtitle">Estados</h3>
            <div class="dashboard-column">
                <button class="btn btn-primary" disabled>Deshabilitado</button>
                <a href="#" class="btn btn-primary">Enlace como Botón</a>
            </div>
        </div>
        <div>
            <h3 class="card-subtitle">Con Iconos</h3>
            <div class="dashboard-column">
                <button class="btn btn-primary">📋 Nuevo Registro</button>
                <button class="btn btn-success">✅ Guardar</button>
                <button class="btn btn-danger">🗑️ Eliminar</button>
            </div>
        </div>
    </div>
</div>

<!-- 2. Cards -->
<div class="card mb-6">
    <h2 class="card-title">🃏 Cards y Contenedores</h2>
    <div class="grid grid-3 mb-4">
        <div class="card">
            <h3 class="card-subtitle">Card Simple</h3>
            <p>Contenido básico de la card con texto de ejemplo. Se adapta automáticamente al tema activo.</p>
        </div>
        <div class="card has-header">
            <div class="card-header">
                <h3 class="card-title">Card con Header</h3>
            </div>
            <div class="card-body">
                <p>Card con header separado para formularios y contenido estructurado.</p>
            </div>
        </div>
        <div class="card card-hover">
            <h3 class="card-subtitle">Card Interactiva</h3>
            <p>Card con efecto hover y transformación suave.</p>
        </div>
    </div>
    
    <div class="grid grid-2">
        <div class="card card-warning">
            <h3 class="card-subtitle text-red">Card de Advertencia</h3>
            <div class="warning-box">
                <p class="text-red-dark">Mensaje de advertencia importante que se adapta al tema.</p>
            </div>
        </div>
        <div class="card useful-links">
            <h3 class="card-subtitle">Card con Estilo Especial</h3>
            <p>Card con gradiente azul para enlaces útiles, compatible con modo oscuro.</p>
        </div>
    </div>
</div>

<!-- 3. Formularios -->
<div class="card mb-6">
    <h2 class="card-title">📝 Formularios</h2>
    <div class="grid grid-2">
        <div>
            <h3 class="card-subtitle">Campos Básicos</h3>
            <div class="form-group">
                <label class="form-label required">Campo Requerido</label>
                <input type="text" class="form-input" placeholder="Texto de ejemplo">
            </div>
            <div class="form-group">
                <label class="form-label">Campo Opcional</label>
                <select class="form-select">
                    <option>Opción 1</option>
                    <option>Opción 2</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Área de Texto</label>
                <textarea class="form-textarea" placeholder="Descripción..."></textarea>
                <div class="form-help">Texto de ayuda opcional</div>
            </div>
        </div>
        <div>
            <h3 class="card-subtitle">Estados de Error</h3>
            <div class="form-group">
                <label class="form-label required">Campo con Error</label>
                <input type="text" class="form-input error" value="Valor incorrecto">
                <div class="form-error">
                    <span class="icon">⚠️</span>
                    Este campo es requerido
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Campo en Grupo</label>
                <div class="input-group">
                    <input type="text" class="form-input" placeholder="Búsqueda...">
                    <div class="input-icon">
                        <span class="icon">🔍</span>
                    </div>
                </div>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-secondary">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </div>
    </div>
</div>

<!-- 4. Badges y Estados -->
<div class="card mb-6">
    <h2 class="card-title">🏷️ Badges y Estados</h2>
    <div class="grid grid-4">
        <div>
            <h3 class="card-subtitle">Estados Territorios</h3>
            <div class="dashboard-column">
                <span class="badge badge-green">Libre</span>
                <span class="badge badge-yellow">Activo</span>
                <span class="badge badge-red">Atrasado</span>
                <span class="badge badge-gray">En Archivo</span>
            </div>
        </div>
        <div>
            <h3 class="card-subtitle">Estados Sistema</h3>
            <div class="dashboard-column">
                <span class="badge badge-blue">Información</span>
                <span class="badge badge-green">Conectado</span>
                <span class="badge badge-red">Error</span>
            </div>
        </div>
        <div>
            <h3 class="card-subtitle">Alertas</h3>
            <div class="alert alert-success mb-2">
                Operación exitosa
            </div>
            <div class="alert alert-error">
                Error en la operación
            </div>
        </div>
        <div>
            <h3 class="card-subtitle">Iconos Coloridos</h3>
            <div class="flex gap-2">
                <div class="icon icon-green">👤</div>
                <div class="icon icon-purple">📋</div>
                <div class="icon icon-yellow">📊</div>
                <div class="icon icon-blue">🗺️</div>
                <div class="icon icon-red">⚠️</div>
            </div>
        </div>
    </div>
</div>

<!-- 5. Estadísticas y Métricas -->
<div class="card mb-6">
    <h2 class="card-title">📊 Estadísticas y Métricas</h2>
    <div class="grid grid-4">
        <div class="stat-card">
            <div class="stat-number">214</div>
            <div class="stat-label">Total</div>
        </div>
        <div class="stat-card">
            <div class="stat-number stat-number-green">180</div>
            <div class="stat-label">Libres</div>
            <div class="badge badge-green mt-2">Disponibles</div>
        </div>
        <div class="stat-card">
            <div class="stat-number stat-number-yellow">25</div>
            <div class="stat-label">Activos</div>
            <div class="badge badge-yellow mt-2">En curso</div>
        </div>
        <div class="stat-card">
            <div class="stat-number stat-number-purple">9</div>
            <div class="stat-label">Atrasados</div>
            <div class="badge badge-red mt-2">Atención</div>
        </div>
    </div>
</div>

<!-- 6. Navegación y Breadcrumbs -->
<div class="card mb-6">
    <h2 class="card-title">🧭 Navegación</h2>
    <div class="grid grid-2">
        <div>
            <h3 class="card-subtitle">Breadcrumbs</h3>
            <div class="page-breadcrumbs mb-4">
                <a href="#" class="breadcrumb-link">Dashboard</a>
                <span class="breadcrumb-sep">›</span>
                <a href="#" class="breadcrumb-link">Territorios</a>
                <span class="breadcrumb-sep">›</span>
                <span class="breadcrumb-current">Territorio #123</span>
            </div>
        </div>
        <div>
            <h3 class="card-subtitle">Enlaces Útiles</h3>
            <div class="dashboard-column">
                <a href="#" class="useful-link">▶️ Ver todos los territorios</a>
                <a href="#" class="useful-link">▶️ Gestionar publicadores</a>
                <a href="#" class="useful-link">▶️ Configuración del sistema</a>
            </div>
        </div>
    </div>
</div>

<!-- 7. Grids y Layouts -->
<div class="card mb-6">
    <h2 class="card-title">📐 Grids y Layouts</h2>
    <div class="mb-4">
        <h3 class="card-subtitle">Grid de 2 Columnas</h3>
        <div class="grid grid-2">
            <div class="card">Columna 1</div>
            <div class="card">Columna 2</div>
        </div>
    </div>
    <div class="mb-4">
        <h3 class="card-subtitle">Grid de 3 Columnas</h3>
        <div class="grid grid-3">
            <div class="card">Columna 1</div>
            <div class="card">Columna 2</div>
            <div class="card">Columna 3</div>
        </div>
    </div>
    <div class="mb-4">
        <h3 class="card-subtitle">Dashboard Grid (2fr + 1fr)</h3>
        <div class="dashboard-grid">
            <div class="card">Columna Principal (2fr)</div>
            <div class="card">Columna Lateral (1fr)</div>
        </div>
    </div>
</div>

<!-- 8. Tipografía -->
<div class="card mb-6">
    <h2 class="card-title">✏️ Tipografía y Texto</h2>
    <div class="grid grid-2">
        <div>
            <h1 class="page-title">Título Principal</h1>
            <h2 class="card-title">Título de Card</h2>
            <h3 class="card-subtitle">Subtítulo</h3>
            <p>Párrafo normal con texto regular que se adapta al tema activo.</p>
            <p class="text-small">Texto pequeño para detalles.</p>
            <p class="text-xs">Texto extra pequeño.</p>
        </div>
        <div>
            <p class="text-dark font-bold">Texto oscuro en negrita</p>
            <p class="text-muted">Texto muted para información secundaria</p>
            <p class="text-red">Texto rojo para errores</p>
            <p class="text-red-dark">Texto rojo oscuro</p>
            <p class="text-gray">Texto gris</p>
            <p class="underline">Texto subrayado</p>
        </div>
    </div>
</div>

<!-- 9. Utilidades y Espaciado -->
<div class="card mb-6">
    <h2 class="card-title">🔧 Utilidades</h2>
    <div class="grid grid-3">
        <div>
            <h3 class="card-subtitle">Flexbox</h3>
            <div class="flex flex-between items-center mb-2">
                <span>Izquierda</span>
                <span>Derecha</span>
            </div>
            <div class="flex flex-center gap-2">
                <span class="badge badge-blue">Centro</span>
                <span class="badge badge-green">Gap</span>
            </div>
        </div>
        <div>
            <h3 class="card-subtitle">Espaciado</h3>
            <div class="mb-1">Margen bottom 1</div>
            <div class="mb-2">Margen bottom 2</div>
            <div class="mb-4">Margen bottom 4</div>
            <div class="py-8 text-center" style="background: var(--color-gray-100); border-radius: 8px;">
                Padding vertical 8
            </div>
        </div>
        <div>
            <h3 class="card-subtitle">Actividad</h3>
            <div class="activity-item">
                <div class="activity-icon">📋</div>
                <div class="flex-1">
                    <p class="font-bold">María González</p>
                    <p class="text-small text-muted">Territorio #45</p>
                    <p class="text-xs text-gray">Hace 2 días</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 10. Hero y Destacados -->
<div class="hero text-center mb-6">
    <h1>🌟 Sección Hero</h1>
    <p>Sección destacada con gradiente y texto centrado para llamadas a la acción importantes</p>
    <div class="mt-4">
        <span class="badge badge-gray">📅 {{ date('d/m/Y') }}</span>
    </div>
</div>

<!-- 11. Variables CSS y Sistema de Diseño -->
<div class="card mb-6">
    <h2 class="card-title">🎨 Variables CSS y Sistema</h2>
    <div class="grid grid-2">
        <div>
            <h3 class="card-subtitle">Colores Principales</h3>
            <div class="mb-4">
                <div style="display: flex; gap: 0.5rem; margin-bottom: 0.5rem;">
                    <div style="width: 2rem; height: 2rem; background: var(--color-primary); border-radius: 4px;"></div>
                    <span>--color-primary</span>
                </div>
                <div style="display: flex; gap: 0.5rem; margin-bottom: 0.5rem;">
                    <div style="width: 2rem; height: 2rem; background: var(--color-success); border-radius: 4px;"></div>
                    <span>--color-success</span>
                </div>
                <div style="display: flex; gap: 0.5rem; margin-bottom: 0.5rem;">
                    <div style="width: 2rem; height: 2rem; background: var(--color-warning); border-radius: 4px;"></div>
                    <span>--color-warning</span>
                </div>
                <div style="display: flex; gap: 0.5rem; margin-bottom: 0.5rem;">
                    <div style="width: 2rem; height: 2rem; background: var(--color-danger); border-radius: 4px;"></div>
                    <span>--color-danger</span>
                </div>
            </div>
        </div>
        <div>
            <h3 class="card-subtitle">Fondos y Textos</h3>
            <div class="mb-4">
                <div style="display: flex; gap: 0.5rem; margin-bottom: 0.5rem;">
                    <div style="width: 2rem; height: 2rem; background: var(--bg-body); border: 1px solid var(--border-color); border-radius: 4px;"></div>
                    <span>--bg-body</span>
                </div>
                <div style="display: flex; gap: 0.5rem; margin-bottom: 0.5rem;">
                    <div style="width: 2rem; height: 2rem; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 4px;"></div>
                    <span>--bg-card</span>
                </div>
                <div style="display: flex; gap: 0.5rem; margin-bottom: 0.5rem;">
                    <div style="width: 2rem; height: 2rem; color: var(--text-primary); background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 0.75rem;">T</div>
                    <span>--text-primary</span>
                </div>
                <div style="display: flex; gap: 0.5rem; margin-bottom: 0.5rem;">
                    <div style="width: 2rem; height: 2rem; background: var(--border-color); border-radius: 4px;"></div>
                    <span>--border-color</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="text-center py-8">
    <h2 class="card-title mb-4">✅ Sistema CloudLoss UI</h2>
    <p class="text-muted">Todos los componentes visuales están centralizados con soporte completo para modo claro y oscuro</p>
    <div class="mt-4">
        <a href="{{ route('configuracion') }}" class="btn btn-primary">Volver a Configuración</a>
    </div>
</div>

<script>
// Script para demo del toggle de tema
document.addEventListener('DOMContentLoaded', function() {
    const demoToggle = document.getElementById('demo-theme-toggle');
    if (demoToggle) {
        demoToggle.addEventListener('click', function() {
            const body = document.body;
            const currentTheme = body.getAttribute('data-theme') || 'light';
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            
            if (newTheme === 'dark') {
                body.setAttribute('data-theme', 'dark');
                demoToggle.innerHTML = '☀️ Cambiar Tema Demo';
            } else {
                body.removeAttribute('data-theme');
                demoToggle.innerHTML = '🌙 Cambiar Tema Demo';
            }
        });
    }
});
</script>

@endsection 