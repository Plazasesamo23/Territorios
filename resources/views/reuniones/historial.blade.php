@extends('layouts.app')

@section('title', 'Historial - ' . $publicador->nombre_completo)

@section('content')

<div class="page-md">
    <div class="flex justify-between items-center mb-2">
        <div>
            <h1 class="page-title">{{ $publicador->nombre_completo }}</h1>
            <p class="page-subtitle">
                @if($publicador->es_anciano) Anciano
                @elseif($publicador->es_siervo_ministerial) Siervo ministerial
                @elseif($publicador->es_precursor) Precursor
                @elseif($publicador->es_menor) Menor
                @else Publicador
                @endif
                &mdash; {{ $historial->count() }} asignaciones
            </p>
        </div>
        <a href="{{ route('reuniones.asignaciones') }}" class="btn btn-ghost">Volver</a>
    </div>

    @if($historial->isEmpty())
        <div class="empty-state">
            <div class="title">Sin asignaciones</div>
            <div class="desc">Este publicador no tiene asignaciones de reuniones registradas</div>
        </div>
    @else
        @php
            $porTipo = $historial->groupBy('tipo_parte');
        @endphp

        <div class="stats-row mb-2">
            @foreach($porTipo as $tipo => $items)
            <div class="stat-item">
                <span class="stat-number">{{ $items->count() }}</span>
                <span class="stat-label">{{ match($tipo) {
                    'presidente' => 'Presidente',
                    'oracion_inicio' => 'Oracion inicio',
                    'oracion_final' => 'Oracion final',
                    'discurso_tesoros' => 'Tesoros',
                    'perlas' => 'Perlas',
                    'lectura' => 'Lectura',
                    'empiece_conversaciones' => 'Conversaciones',
                    'haga_revisitas' => 'Revisitas',
                    'haga_discipulos' => 'Discipulos',
                    'explique_creencias' => 'Creencias',
                    'discurso_vida' => 'Vida cristiana',
                    'conductor_estudio' => 'Conductor',
                    'lector_estudio' => 'Lector',
                    'ayudante' => 'Ayudante',
                    default => $tipo,
                } }}</span>
            </div>
            @endforeach
        </div>

        <table class="table-flat">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Tipo de parte</th>
                    <th>Titulo</th>
                    <th>Rol</th>
                </tr>
            </thead>
            <tbody>
                @foreach($historial as $h)
                <tr>
                    <td>
                        <span class="font-medium">{{ $h->fecha_semana->translatedFormat('d M Y') }}</span>
                    </td>
                    <td>
                        @php
                            $seccionColor = match($h->tipo_parte) {
                                'discurso_tesoros', 'perlas', 'lectura' => '#6366f1',
                                'empiece_conversaciones', 'haga_revisitas', 'haga_discipulos', 'explique_creencias' => '#f59e0b',
                                'discurso_vida', 'conductor_estudio', 'lector_estudio' => '#ef4444',
                                default => '#14b8a6',
                            };
                        @endphp
                        <span style="border-left: 3px solid {{ $seccionColor }}; padding-left: 0.5rem;">
                            {{ match($h->tipo_parte) {
                                'presidente' => 'Presidente',
                                'oracion_inicio' => 'Oracion de inicio',
                                'oracion_final' => 'Oracion final',
                                'discurso_tesoros' => 'Discurso Tesoros',
                                'perlas' => 'Perlas escondidas',
                                'lectura' => 'Lectura biblica',
                                'empiece_conversaciones' => 'Empiece conversaciones',
                                'haga_revisitas' => 'Haga revisitas',
                                'haga_discipulos' => 'Haga discipulos',
                                'explique_creencias' => 'Explique creencias',
                                'discurso_vida' => 'Discurso Vida Cristiana',
                                'conductor_estudio' => 'Conductor estudio',
                                'lector_estudio' => 'Lector estudio',
                                'ayudante' => 'Ayudante',
                                default => $h->tipo_parte,
                            } }}
                        </span>
                    </td>
                    <td class="text-muted text-sm">{{ $h->titulo_parte ?? '—' }}</td>
                    <td>
                        @if($h->rol === 'principal')
                            <span class="badge badge-primary">Principal</span>
                        @else
                            <span class="badge">Ayudante</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

@endsection
