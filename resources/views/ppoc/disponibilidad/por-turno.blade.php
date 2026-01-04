@extends('layouts.app')

@section('title', 'Disponibilidad por Turno')

@section('content')
<div class="page-nav">
    <div class="page-breadcrumbs">
        <a href="{{ route('dashboard') }}" class="breadcrumb-link">Dashboard</a>
        <span class="breadcrumb-sep">></span>
        <a href="{{ route('ppoc.calendario') }}" class="breadcrumb-link">PPOC</a>
        <span class="breadcrumb-sep">></span>
        <span class="breadcrumb-current">Disponibilidad</span>
    </div>
    <div class="page-actions">
        <a href="{{ route('ppoc.disponibilidad.por-publicador') }}" class="btn btn-secondary btn-sm">
            Ver por Publicador
        </a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success mb-4">
    {{ session('success') }}
</div>
@endif

<div class="card mb-4">
    <div class="card-title">Link para que los publicadores llenen su disponibilidad</div>
    <div class="link-compartir">
        @if($congregacion->token_disponibilidad)
        <input type="text" readonly class="link-input" id="linkDisponibilidad"
               value="{{ route('disponibilidad.form', $congregacion->token_disponibilidad) }}">
        <button type="button" class="btn btn-primary btn-sm" onclick="copiarLink()">
            Copiar Link
        </button>
        @else
        <p style="color: #f97316;">Genera el link visitando esta pagina.</p>
        @endif
    </div>
    <p class="text-muted mt-2" style="font-size: 0.85rem;">
        Comparte este link por WhatsApp para que los hermanos aprobados para PPOC indiquen su disponibilidad.
    </p>
</div>

<div class="card">
    <div class="card-title">Disponibilidad por Turno</div>

    @if($turnosPorDia->count() === 0)
    <div class="empty-state">
        <p>No hay turnos configurados.</p>
        <a href="{{ route('ppoc.turnos.create') }}">Crear el primer turno</a>
    </div>
    @else
    @php
        $diasNombres = [0 => 'Lunes', 1 => 'Martes', 2 => 'Miercoles', 3 => 'Jueves', 4 => 'Viernes', 5 => 'Sabado', 6 => 'Domingo'];
    @endphp

    <div class="disponibilidad-grid">
        @foreach($diasNombres as $diaNum => $diaNombre)
            @if(isset($turnosPorDia[$diaNum]))
            <div class="dia-columna">
                <div class="dia-header">{{ $diaNombre }}</div>
                @foreach($turnosPorDia[$diaNum] as $turno)
                <div class="turno-card">
                    <div class="turno-header">
                        <span class="turno-nombre">{{ $turno->nombre }}</span>
                        <span class="turno-horario">{{ $turno->horario }}</span>
                    </div>
                    <div class="publicadores-lista">
                        @if($turno->publicadoresDisponibles->count() > 0)
                            @foreach($turno->publicadoresDisponibles as $pub)
                            <div class="publicador-badge">
                                {{ $pub->nombre_completo }}
                            </div>
                            @endforeach
                        @else
                        <div class="sin-disponibilidad">
                            Sin voluntarios
                        </div>
                        @endif
                    </div>
                    <div class="turno-footer">
                        <span class="contador">{{ $turno->publicadoresDisponibles->count() }} disponibles</span>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        @endforeach
    </div>
    @endif
</div>

<style>
/* Link compartir */
.link-compartir {
    display: flex;
    gap: 0.5rem;
    align-items: center;
    margin-top: 0.5rem;
}
.link-input {
    flex: 1;
    padding: 0.5rem 0.75rem;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    font-size: 0.875rem;
    background: #f9fafb;
}
[data-theme="dark"] .link-input {
    background: #1a1a1a;
    border-color: #404040;
    color: #f5f5f5;
}

/* Grid de disponibilidad */
.disponibilidad-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}
.dia-columna {
    background: var(--bg-secondary, #f9fafb);
    border-radius: 12px;
    padding: 1rem;
}
[data-theme="dark"] .dia-columna {
    background: #1a1a1a;
}
.dia-header {
    font-weight: 700;
    font-size: 1rem;
    color: #f97316;
    margin-bottom: 0.75rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #f97316;
}

/* Tarjeta de turno */
.turno-card {
    background: var(--bg-card, #fff);
    border: 1px solid var(--border-color, #e5e7eb);
    border-radius: 8px;
    padding: 0.75rem;
    margin-bottom: 0.75rem;
}
[data-theme="dark"] .turno-card {
    background: #262626;
    border-color: #404040;
}
.turno-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}
.turno-nombre {
    font-weight: 600;
    color: var(--text-primary, #111827);
}
[data-theme="dark"] .turno-nombre {
    color: #f5f5f5;
}
.turno-horario {
    font-size: 0.75rem;
    color: #6b7280;
    background: #f3f4f6;
    padding: 0.125rem 0.5rem;
    border-radius: 4px;
}
[data-theme="dark"] .turno-horario {
    background: #333;
    color: #a3a3a3;
}

/* Lista de publicadores */
.publicadores-lista {
    display: flex;
    flex-wrap: wrap;
    gap: 0.375rem;
    min-height: 2rem;
}
.publicador-badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    border-radius: 4px;
    font-weight: 500;
}
.sin-disponibilidad {
    font-size: 0.8rem;
    color: #9ca3af;
    font-style: italic;
    padding: 0.25rem 0;
}

/* Footer turno */
.turno-footer {
    margin-top: 0.5rem;
    padding-top: 0.5rem;
    border-top: 1px dashed var(--border-color, #e5e7eb);
}
[data-theme="dark"] .turno-footer {
    border-top-color: #404040;
}
.contador {
    font-size: 0.75rem;
    color: #6b7280;
}

/* Empty state */
.empty-state {
    text-align: center;
    padding: 2rem;
    color: #6b7280;
}
.empty-state a {
    color: #f97316;
}

/* Responsive */
@media (max-width: 768px) {
    .link-compartir {
        flex-direction: column;
    }
    .link-input {
        width: 100%;
    }
    .disponibilidad-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
function copiarLink() {
    const input = document.getElementById('linkDisponibilidad');
    input.select();
    document.execCommand('copy');

    // Feedback visual
    const btn = event.target;
    const originalText = btn.textContent;
    btn.textContent = 'Copiado!';
    btn.style.background = '#10b981';
    setTimeout(() => {
        btn.textContent = originalText;
        btn.style.background = '';
    }, 2000);
}
</script>
@endsection
