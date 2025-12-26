@extends('layouts.app')

@section('title', 'Registros: {{ $publicador->nombre_completo }} - Gestión de Territorios')

@section('content')
<!-- Navegación minimalista -->
<nav class="page-nav">
    <div class="page-breadcrumbs">
        <a href="{{ route('dashboard') }}" class="breadcrumb-link">Dashboard</a>
        <span class="breadcrumb-separator">›</span>
        <a href="{{ route('publicadores.index') }}" class="breadcrumb-link">Publicadores</a>
        <span class="breadcrumb-separator">›</span>
        <span class="breadcrumb-current">{{ $publicador->nombre_completo }} - Registros</span>
    </div>
    <div class="page-actions">
        <a href="{{ route('publicadores.index') }}" class="btn btn-secondary">
            ← Volver al Listado
        </a>
        <a href="{{ route('publicadores.show', $publicador) }}" class="btn btn-primary">
            👁️ Ver Publicador
        </a>
    </div>
</nav>

<div class="grid grid-2">
    <!-- Panel principal -->
    <div>
        <!-- Información del publicador (resumen) -->
        <div class="card mb-4">
            <div class="card-title">
                📋 Gestión de Territorios
                <div style="float: right;">
                    @if($publicador->activo)
                        <span class="badge badge-green">✅ Activo</span>
                    @else
                        <span class="badge badge-gray">❌ Inactivo</span>
                    @endif
                </div>
            </div>
            
            <div style="margin-bottom: 1rem;">
                <strong style="font-size: 1.2rem;">{{ $publicador->nombre_completo }}</strong>
                <div class="text-small text-muted">📞 {{ $publicador->telefono }}</div>
            </div>
        </div>

        <!-- Territorio actual -->
        <div class="card mb-4">
            <div class="card-title">🗺️ Territorio Actual</div>
            @if($estadisticas['territorio_actual'])
                @php $registro = $estadisticas['territorio_actual']; @endphp
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div style="flex: 1;">
                        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.5rem;">
                            <strong style="font-size: 1.5rem; color: #3b82f6;">#{{ $registro->territorio->numero }}</strong>
                            @php
                                $estado = $registro->territorio->calcularEstado();
                                $dias = $registro->fecha_salida->diffInDays(now());
                            @endphp
                            @if($estado === 'activo')
                                <span class="badge badge-blue">🔵 Activo</span>
                            @elseif($estado === 'atrasado')
                                <span class="badge badge-red">🔴 Atrasado</span>
                            @endif
                        </div>
                        
                        @if($registro->territorio->nombre)
                            <div style="margin-bottom: 0.5rem; color: #6b7280;">{{ $registro->territorio->nombre }}</div>
                        @endif
                        
                        <div class="text-small text-muted">
                            <div>📅 Asignado: {{ $registro->fecha_salida->format('d/m/Y') }}</div>
                            <div>⏰ Días transcurridos: {{ $dias }}</div>
                            @if($registro->entrada_prevista)
                                <div>🎯 Devolución prevista: {{ $registro->entrada_prevista->format('d/m/Y') }}</div>
                            @endif
                        </div>
                        
                        @if($registro->notas)
                            <div style="margin-top: 0.75rem; padding: 0.5rem; background: #f9fafb; border-radius: 6px; border: 1px solid #e5e7eb;">
                                💬 {{ $registro->notas }}
                            </div>
                        @endif
                    </div>
                    
                    <div style="display: flex; flex-direction: column; gap: 0.5rem; align-items: flex-end;">
                        <!-- Ver territorio -->
                        <a href="{{ route('territorios.show', $registro->territorio) }}" class="btn btn-primary">
                            👁️ Ver Territorio
                        </a>
                        
                        <!-- WhatsApp -->
                        @php
                            $congregacion = \App\Models\Congregacion::find(session('congregacion_activa_id'));
                            $mensajeWhatsapp = $congregacion ? $congregacion->getMensajeWhatsappFormateado($publicador, $registro->territorio) : "Hola " . $publicador->nombre . ", te envío el territorio " . $registro->territorio->numero;
                        @endphp
                        <a href="https://wa.me/{{ str_replace(['+', ' ', '-'], '', $publicador->telefono) }}?text={{ urlencode($mensajeWhatsapp) }}"
                           target="_blank" class="btn btn-secondary">
                            💬 WhatsApp
                        </a>
                        
                        <!-- Devolver territorio -->
                        <form action="{{ route('registros.entrada', $registro) }}" method="POST" class="inline"
                              onsubmit="return confirm('¿Confirmas que el territorio #{{ $registro->territorio->numero }} ha sido devuelto?')">
                            @csrf
                            <button type="submit" class="btn btn-success">
                                ✅ Marcar Devuelto
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <div class="text-center" style="padding: 2rem;">
                    <div style="font-size: 3rem; margin-bottom: 0.5rem;">🆓</div>
                    <div class="text-muted mb-3">Sin territorio asignado actualmente</div>
                    @if($publicador->activo)
                        <a href="{{ route('registros.create') }}?publicador={{ $publicador->id }}" class="btn btn-primary">
                            📋 Asignar Territorio
                        </a>
                    @else
                        <div class="text-small text-muted">
                            ⚠️ Publicador inactivo - No se pueden asignar territorios
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <!-- Historial de territorios -->
        @if($registros->where('fecha_entrada', '!=', null)->count() > 0)
            <div class="card">
                <div class="card-title">
                    📈 Historial de Territorios
                    <span class="badge badge-blue">{{ $registros->where('fecha_entrada', '!=', null)->count() }}</span>
                </div>
                <div class="card-description">Territorios completados anteriormente</div>

                @foreach($registros->where('fecha_entrada', '!=', null) as $registro)
                    <div style="border-bottom: 1px solid #e5e7eb; padding: 1rem 0; display: flex; justify-content: space-between; align-items: center;">
                        <div style="flex: 1;">
                            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.5rem;">
                                <strong style="color: #3b82f6;">Territorio #{{ $registro->territorio->numero }}</strong>
                                @if($registro->territorio->nombre)
                                    <span class="text-muted">{{ $registro->territorio->nombre }}</span>
                                @endif
                                <span class="badge badge-green">✅ Completado</span>
                            </div>
                            
                            <div class="text-small text-muted">
                                📅 {{ $registro->fecha_salida->format('d/m/Y') }} → {{ $registro->fecha_entrada->format('d/m/Y') }}
                                ({{ $registro->fecha_salida->diffInDays($registro->fecha_entrada) }} días)
                            </div>
                            
                            @if($registro->notas)
                                <div style="margin-top: 0.25rem; font-size: 0.875rem; color: #6b7280;">
                                    💬 {{ Str::limit($registro->notas, 80) }}
                                </div>
                            @endif
                        </div>
                        
                        <div>
                            <a href="{{ route('territorios.show', $registro->territorio) }}" class="btn-icon">👁️</a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Panel lateral -->
    <div>
        <!-- Estadísticas -->
        <div class="card mb-4">
            <div class="card-title">📊 Estadísticas del Publicador</div>
            <div class="grid grid-2">
                <div class="text-center">
                    <div style="font-size: 1.75rem; font-weight: 600; color: #3b82f6;">{{ $estadisticas['territorios_completados'] }}</div>
                    <div class="text-small text-muted">Completados</div>
                </div>
                <div class="text-center">
                    <div style="font-size: 1.75rem; font-weight: 600; color: #f59e0b;">{{ $estadisticas['promedio_dias'] }}</div>
                    <div class="text-small text-muted">Días Promedio</div>
                </div>
            </div>
            <div class="text-center" style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #e5e7eb;">
                <div style="font-size: 1.25rem; font-weight: 600; color: #10b981;">{{ $estadisticas['total_dias_trabajados'] }}</div>
                <div class="text-small text-muted">Total Días Trabajados</div>
            </div>
        </div>

        <!-- Acciones rápidas -->
        <div class="card mb-4">
            <div class="card-title">⚡ Acciones Rápidas</div>
            
            @if($estadisticas['territorio_actual'])
                @php
                    $congregacionAcciones = \App\Models\Congregacion::find(session('congregacion_activa_id'));
                    $mensajeContacto = $congregacionAcciones ? $congregacionAcciones->getMensajeWhatsappFormateado($publicador, $estadisticas['territorio_actual']->territorio) : "Hola " . $publicador->nombre . ", te escribo respecto al territorio " . $estadisticas['territorio_actual']->territorio->numero;
                @endphp
                <a href="https://wa.me/{{ str_replace(['+', ' ', '-'], '', $publicador->telefono) }}?text={{ urlencode($mensajeContacto) }}"
                   target="_blank" class="btn btn-primary" style="margin-bottom: 0.5rem; width: 100%;">
                    💬 Contactar por WhatsApp
                </a>
                
                <a href="{{ route('territorios.show', $estadisticas['territorio_actual']->territorio) }}" 
                   class="btn btn-secondary" style="margin-bottom: 0.5rem; width: 100%;">
                    🗺️ Ver Territorio Actual
                </a>
            @else
                <a href="{{ route('registros.create') }}?publicador={{ $publicador->id }}"
                   class="btn btn-primary" style="margin-bottom: 0.5rem; width: 100%;">
                    📋 Asignar Territorio
                </a>

                <a href="https://wa.me/{{ str_replace(['+', ' ', '-'], '', $publicador->telefono) }}?text={{ urlencode('Hola ' . $publicador->nombre . ', ¿cómo estás?') }}"
                   target="_blank" class="btn btn-secondary" style="margin-bottom: 0.5rem; width: 100%;">
                    💬 Contactar por WhatsApp
                </a>
            @endif
        </div>

        <!-- Información del sistema -->
        <div class="card">
            <div class="card-title">ℹ️ Información</div>
            <div class="text-small text-muted">
                <div style="margin-bottom: 0.5rem;">
                    <strong>Registrado:</strong> {{ $publicador->created_at->format('d/m/Y H:i') }}
                </div>
                @if($publicador->updated_at != $publicador->created_at)
                    <div style="margin-bottom: 0.5rem;">
                        <strong>Actualizado:</strong> {{ $publicador->updated_at->format('d/m/Y H:i') }}
                    </div>
                @endif
                <div>
                    <strong>Total registros:</strong> {{ $estadisticas['total_territorios'] }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 