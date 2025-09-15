@extends('layouts.app')

@section('title', 'Asignar Territorio - Gestión de Territorios')

@section('content')
<!-- Navegación minimalista -->
<nav class="page-nav">
    <div class="page-breadcrumbs">
        <a href="{{ route('dashboard') }}" class="breadcrumb-link">Dashboard</a>
        <span class="breadcrumb-separator">›</span>
        <a href="{{ route('registros.index') }}" class="breadcrumb-link">Registros</a>
        <span class="breadcrumb-separator">›</span>
        <span class="breadcrumb-current">Asignar</span>
    </div>
    <div class="page-actions">
        <a href="{{ route('registros.index') }}" class="btn btn-secondary">
            ← Volver
        </a>
    </div>
</nav>

<div class="grid grid-2">
    <!-- Formulario Principal -->
    <div class="card">
        <div class="card-title">Asignar Territorio</div>
        <div class="card-description">Selecciona un territorio libre y un publicador activo para crear una nueva asignación.</div>

        <form action="{{ route('registros.store') }}" method="POST">
            @csrf

            <!-- Selección de Territorio -->
            <div class="mb-4">
                <label for="territorio_id" style="display: block; font-weight: 600; margin-bottom: 0.5rem;">
                    Territorio Disponible *
                </label>
                @if($territoriosDisponibles->count() > 0)
                    <select id="territorio_id" name="territorio_id" required
                            style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 8px;">
                        <option value="">Selecciona un territorio...</option>
                        @foreach($territoriosDisponibles as $territorio)
                            <option value="{{ $territorio->id }}" {{ old('territorio_id') == $territorio->id ? 'selected' : '' }}>
                                #{{ $territorio->numero }} - {{ $territorio->nombre ?? 'Sin nombre' }}
                            </option>
                        @endforeach
                    </select>
                    @error('territorio_id')
                        <div style="color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</div>
                    @enderror
                    <div style="color: #10b981; font-size: 0.875rem; margin-top: 0.25rem;">
                        ✅ {{ $territoriosDisponibles->count() }} territorios disponibles para asignar
                    </div>
                @else
                    <div class="alert alert-error">
                        <strong>⚠️ No hay territorios disponibles</strong><br>
                        Todos los territorios están asignados o en período de descanso (90 días).
                    </div>
                @endif
            </div>

            <!-- Selección de Publicador -->
            <div class="mb-4">
                <label for="publicador_id" style="display: block; font-weight: 600; margin-bottom: 0.5rem;">
                    Publicador Activo *
                </label>
                @if($publicadoresActivos->count() > 0)
                    <select id="publicador_id" name="publicador_id" required
                            style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 8px;">
                        <option value="">Selecciona un publicador...</option>
                        @foreach($publicadoresActivos as $publicador)
                            <option value="{{ $publicador->id }}" {{ old('publicador_id') == $publicador->id ? 'selected' : '' }}>
                                {{ $publicador->nombre }} - {{ $publicador->telefono }}
                            </option>
                        @endforeach
                    </select>
                    @error('publicador_id')
                        <div style="color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</div>
                    @enderror
                    <div style="color: #10b981; font-size: 0.875rem; margin-top: 0.25rem;">
                        ✅ {{ $publicadoresActivos->count() }} publicadores disponibles
                    </div>
                @else
                    <div class="alert alert-error">
                        <strong>⚠️ No hay publicadores disponibles</strong><br>
                        Todos los publicadores están inactivos o ya tienen territorios asignados.
                    </div>
                @endif
            </div>

            <!-- Notas adicionales -->
            <div class="mb-4">
                <label for="notas" style="display: block; font-weight: 600; margin-bottom: 0.5rem;">
                    Notas adicionales
                </label>
                <textarea id="notas" name="notas" rows="3"
                          style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 8px;"
                          placeholder="Información adicional sobre esta asignación...">{{ old('notas') }}</textarea>
                @error('notas')
                    <div style="color: #ef4444; font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</div>
                @enderror
            </div>

            <!-- Botones de acción -->
            <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2rem;">
                <a href="{{ route('registros.index') }}" class="btn btn-secondary">
                    Cancelar
                </a>
                @if($territoriosDisponibles->count() > 0 && $publicadoresActivos->count() > 0)
                    <button type="submit" class="btn btn-primary">
                        Asignar y Enviar WhatsApp
                    </button>
                @else
                    <button type="button" disabled class="btn" style="background: #9ca3af; color: white; cursor: not-allowed;">
                        No se puede asignar
                    </button>
                @endif
            </div>
        </form>
    </div>

    <!-- Panel de información -->
    <div>
        <div class="card mb-4">
            <div class="card-title">¿Qué sucede al asignar?</div>
            <div style="font-size: 0.875rem; line-height: 1.5;">
                <div>• Se crea un registro de asignación</div>
                <div>• El territorio cambia a estado ACTIVO</div>
                <div>• Se envía WhatsApp automático al publicador</div>
                <div>• El mensaje incluye imagen del territorio</div>
            </div>
        </div>

        @if($territoriosDisponibles->count() > 0)
            <div class="card mb-4">
                <div class="card-title">Territorios Disponibles</div>
                <div style="max-height: 200px; overflow-y: auto;">
                    @foreach($territoriosDisponibles->take(8) as $territorio)
                        <div style="border-bottom: 1px solid #e5e7eb; padding: 0.75rem 0;">
                            <div><strong>#{{ $territorio->numero }}</strong></div>
                            @if($territorio->nombre)
                                <div class="text-small text-muted">{{ $territorio->nombre }}</div>
                            @endif
                        </div>
                    @endforeach
                    @if($territoriosDisponibles->count() > 8)
                        <div class="text-small text-muted text-center" style="padding: 0.75rem;">
                            ... y {{ $territoriosDisponibles->count() - 8 }} más
                        </div>
                    @endif
                </div>
            </div>
        @endif

        @if($territoriosNoDisponibles->count() > 0)
            <div class="card">
                <div class="card-title">
                    <span style="color: #f59e0b;">⏳ En Período de Descanso</span>
                </div>
                <div class="card-description" style="font-size: 0.875rem;">
                    Territorios que deben esperar 90 días desde su devolución
                </div>
                <div style="max-height: 200px; overflow-y: auto;">
                    @foreach($territoriosNoDisponibles->take(5) as $territorio)
                        <div style="border-bottom: 1px solid #e5e7eb; padding: 0.75rem 0; background: #fef3c7; margin-bottom: 0.5rem; border-radius: 6px; padding-left: 1rem;">
                            <div><strong>#{{ $territorio->numero }}</strong></div>
                            @if($territorio->nombre)
                                <div class="text-small text-muted">{{ $territorio->nombre }}</div>
                            @endif
                            <div style="color: #f59e0b; font-size: 0.8rem; margin-top: 0.25rem;">
                                📅 Disponible en {{ $territorio->diasRestantesParaEstarDisponible() }} días
                                ({{ $territorio->fechaDisponible()->format('d/m/Y') }})
                            </div>
                        </div>
                    @endforeach
                    @if($territoriosNoDisponibles->count() > 5)
                        <div class="text-small text-muted text-center" style="padding: 0.75rem;">
                            ... y {{ $territoriosNoDisponibles->count() - 5 }} más en descanso
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
@endsection 