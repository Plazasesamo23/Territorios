@extends('layouts.app')

@section('title', 'Gestión de Congregaciones')

@section('content')
<style>
    .credentials-box {
        font-size: 0.8rem;
        line-height: 1.4;
    }
    .password-field {
        font-family: monospace;
        background: #f3f4f6;
        padding: 2px 6px;
        border-radius: 4px;
        color: #1f2937;
    }
    [data-theme="dark"] .password-field {
        background: #374151;
        color: #e5e7eb;
    }
</style>
<div class="page-header">
    <div class="page-header-content">
        <h1>🏛️ Gestión de Congregaciones</h1>
        <p>Administra todas las congregaciones del sistema</p>
    </div>
    <div class="page-header-actions">
        <a href="{{ route('congregaciones.create') }}" class="btn btn-primary">
            ➕ Nueva Congregación
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Código</th>
                    <th>Ciudad</th>
                    <th>Credenciales</th>
                    <th>Territorios</th>
                    <th>Publicadores</th>
                    <th>Usuarios</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($congregaciones as $congregacion)
                    <tr>
                        <td>
                            <strong>{{ $congregacion->nombre }}</strong>
                            @if($congregacionActiva && $congregacionActiva->id === $congregacion->id)
                                <span class="badge badge-primary" style="margin-left: 0.5rem;">Actual</span>
                            @endif
                        </td>
                        <td><code>{{ $congregacion->codigo }}</code></td>
                        <td>{{ $congregacion->ciudad ?? '-' }}</td>
                        <td>
                            <div class="credentials-box">
                                <div><strong>Usuario:</strong> {{ $congregacion->usuario ?? $congregacion->codigo }}</div>
                                <div>
                                    <strong>Clave:</strong>
                                    <span class="password-field">
                                        {{ $congregacion->password_plain ?? 'No establecida' }}
                                    </span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-info">{{ $congregacion->territorios_count }}</span>
                        </td>
                        <td>
                            <span class="badge badge-info">{{ $congregacion->publicadores_count }}</span>
                        </td>
                        <td>
                            <span class="badge badge-info">{{ $congregacion->users_count }}</span>
                        </td>
                        <td>
                            @if($congregacion->activa)
                                <span class="badge badge-success">Activa</span>
                            @else
                                <span class="badge badge-secondary">Inactiva</span>
                            @endif
                        </td>
                        <td>
                            <div style="display: flex; gap: 0.5rem;">
                                <form action="{{ route('congregaciones.cambiar', $congregacion) }}" method="POST" style="margin: 0;">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline" title="Cambiar a esta congregación">
                                        🔄
                                    </button>
                                </form>
                                <a href="{{ route('congregaciones.edit', $congregacion) }}" class="btn btn-sm btn-outline" title="Editar">
                                    ✏️
                                </a>
                                @if($congregacion->territorios_count == 0 && $congregacion->publicadores_count == 0)
                                    <form action="{{ route('congregaciones.destroy', $congregacion) }}" method="POST" style="margin: 0;" onsubmit="return confirm('¿Estás seguro de eliminar esta congregación?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                            🗑️
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 2rem;">
                            No hay congregaciones registradas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
