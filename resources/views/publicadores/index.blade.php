@extends('layouts.app')

@section('title', 'Publicadores')

@section('content')
<div class="page-flat">
    <h1 class="page-title">Publicadores</h1>
    <p class="page-subtitle">{{ $publicadores->count() }} publicadores registrados</p>

    <!-- Acciones -->
    <div class="shortcuts mb-2">
        <a href="{{ route('publicadores.create') }}" class="shortcut">
            <span class="icon">+</span>
            <span>Nuevo Publicador</span>
        </a>
    </div>

    <!-- Buscador -->
    <form method="GET" action="{{ route('publicadores.index') }}" class="form-group">
        <div class="flex gap-1">
            <input type="text"
                   name="search"
                   value="{{ request('search') }}"
                   placeholder="Buscar publicador..."
                   class="form-input">
            <button type="submit" class="btn btn-primary">Buscar</button>
            @if(request('search'))
                <a href="{{ route('publicadores.index') }}" class="btn btn-secondary">Limpiar</a>
            @endif
        </div>
    </form>

    @if($publicadores->count() > 0)
    <!-- Tabla flat -->
    <table class="table-flat">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Apellidos</th>
                <th>Telefono</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($publicadores as $publicador)
            <tr onclick="window.location='{{ route('publicadores.show', $publicador) }}'" style="cursor:pointer;">
                <td>
                    <span class="font-medium">{{ $publicador->nombre }}</span>
                    @if($publicador->es_anciano)
                        <span class="badge">ANC</span>
                    @endif
                    @if($publicador->es_siervo_ministerial)
                        <span class="badge badge-primary">SM</span>
                    @endif
                    @if($publicador->es_precursor)
                        <span class="badge badge-primary">PR</span>
                    @endif
                </td>
                <td class="text-muted">{{ $publicador->apellidos }}</td>
                <td class="text-muted">{{ $publicador->telefono }}</td>
                <td>
                    @if($publicador->activo)
                        <span class="text-primary font-medium">Activo</span>
                    @else
                        <span class="text-muted">Inactivo</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="empty-state">
        <div class="icon">&#x1F465;</div>
        <div class="title">No hay publicadores</div>
        <div class="desc">Comienza agregando publicadores para gestionar territorios.</div>
        <a href="{{ route('publicadores.create') }}" class="btn btn-primary mt-2">Crear Primer Publicador</a>
    </div>
    @endif
</div>

<style>
.page-flat {
    max-width: 900px;
    margin: 0 auto;
}
</style>
@endsection
