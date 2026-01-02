@extends('layouts.app')

@section('title', 'Plantillas de Turnos PPOC')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-list"></i> Plantillas de Turnos Semanales</h5>
            <div>
                <a href="{{ route('ppoc.calendario') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-calendar"></i> Ver Calendario
                </a>
                <a href="{{ route('ppoc.turnos.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Nueva Plantilla
                </a>
            </div>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <p class="text-muted">
                Las plantillas definen los turnos que se repiten cada semana. Al generar el calendario de un mes,
                estos turnos se crearan automaticamente para cada dia correspondiente.
            </p>

            @php
                $diasSemana = ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado', 'Domingo'];
            @endphp

            @foreach($diasSemana as $index => $diaNombre)
                @php $turnosDia = $turnos->where('dia_semana', $index); @endphp
                @if($turnosDia->count() > 0)
                <div class="mb-4">
                    <h6 class="border-bottom pb-2">{{ $diaNombre }}</h6>
                    <div class="row">
                        @foreach($turnosDia as $turno)
                        <div class="col-md-4 mb-2">
                            <div class="card {{ $turno->activo ? '' : 'bg-light' }}">
                                <div class="card-body p-2">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <strong>{{ $turno->nombre }}</strong>
                                            <span class="badge bg-info">Turno {{ $turno->numero_turno }}</span>
                                            @if(!$turno->activo)
                                                <span class="badge bg-secondary">Inactivo</span>
                                            @endif
                                            <br>
                                            <small class="text-muted">
                                                <i class="fas fa-clock"></i> {{ \Carbon\Carbon::parse($turno->hora_inicio)->format('H:i') }} - {{ \Carbon\Carbon::parse($turno->hora_fin)->format('H:i') }}
                                            </small>
                                            <br>
                                            <small class="text-muted">
                                                <i class="fas fa-users"></i> {{ $turno->capacidad }} voluntarios
                                            </small>
                                            @if($turno->ubicacion)
                                            <br>
                                            <small class="text-muted">
                                                <i class="fas fa-map-marker-alt"></i> {{ $turno->ubicacion }}
                                            </small>
                                            @endif
                                        </div>
                                        <div class="btn-group-vertical">
                                            <a href="{{ route('ppoc.turnos.edit', $turno) }}" class="btn btn-outline-primary btn-sm" title="Editar">
                                                &#9998;
                                            </a>
                                            <a href="{{ route('ppoc.turnos.create') }}?copiar={{ $turno->id }}" class="btn btn-outline-success btn-sm" title="Duplicar">
                                                &#128464;
                                            </a>
                                            <form action="{{ route('ppoc.turnos.destroy', $turno) }}" method="POST" class="d-inline" onsubmit="return confirm('Eliminar esta plantilla?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm" title="Eliminar">
                                                    &#128465;
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            @endforeach

            @if($turnos->count() === 0)
                <div class="alert alert-info">
                    No hay plantillas de turnos definidas. <a href="{{ route('ppoc.turnos.create') }}">Crear la primera</a>.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection