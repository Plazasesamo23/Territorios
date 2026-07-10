@extends('layouts.app')

@section('title', 'Cambiar Usuario')

@section('content')

<div class="selector-container">
    <div class="selector-header">
        <h1 class="selector-title">¿Quién está usando el sistema?</h1>

        @if(isset($usuarioOriginal))
        <div class="usuario-original-banner">
            <span class="banner-icon">&#x1F511;</span>
            <span class="banner-text">Sesion iniciada como <strong>{{ $usuarioOriginal->name }}</strong></span>
            <a href="{{ route('cambiar-usuario.volver') }}" class="btn-volver-original">
                <span>&#x21A9;</span> Volver a mi cuenta
            </a>
        </div>
        @elseif($necesitaPassword)
        <div class="selector-alert">
            <span class="alert-icon">&#x1F512;</span>
            <span>Para cambiar al administrador necesitas su contraseña</span>
        </div>
        @endif
    </div>

    <div class="usuarios-grid">
        @foreach($usuarios as $usuario)
        @php
            $esActual = $usuario->id === auth()->id();
            $esOriginal = isset($usuarioOriginal) && $usuario->id === $usuarioOriginal->id;

            // Determinar color del avatar segun rol
            if ($usuario->isSuperAdmin()) {
                $avatarStyle = 'background: linear-gradient(135deg, #3d5a8a 0%, #5b21b6 100%);';
            } elseif ($usuario->isAdmin()) {
                $avatarStyle = 'background: linear-gradient(135deg, #343a40 0%, #212529 100%);';
            } elseif ($usuario->isTerritoriosUser()) {
                $avatarStyle = 'background: linear-gradient(135deg, #4a6da7 0%, #3d5a8a 100%);';
            } else {
                $avatarStyle = 'background: linear-gradient(135deg, #4a6da7 0%, #2d4266 100%);';
            }

            // Determinar si necesita password para este usuario especifico
            $necesitaPasswordParaEste = false;
            if ($necesitaPassword && ($usuario->isAdmin() || $usuario->isSuperAdmin())) {
                $necesitaPasswordParaEste = true;
            }
        @endphp
        <div class="usuario-card {{ $esActual ? 'usuario-actual' : '' }}"
             data-usuario-id="{{ $usuario->id }}"
             data-necesita-password="{{ $necesitaPasswordParaEste ? 'true' : 'false' }}"
             onclick="seleccionarUsuario(this)">

            <div class="usuario-avatar" style="{{ $avatarStyle }}">
                {{ strtoupper(substr($usuario->name, 0, 1)) }}
            </div>

            <div class="usuario-nombre">{{ $usuario->name }}</div>

            @if($usuario->isSuperAdmin())
            <div class="usuario-badge superadmin">Super</div>
            @elseif($usuario->isAdmin())
            <div class="usuario-badge admin">Admin</div>
            @elseif($usuario->isTerritoriosUser())
            <div class="usuario-badge territorios">Territorios</div>
            @else
            <div class="usuario-badge user">Usuario</div>
            @endif

            @if($esActual)
            <div class="usuario-actual-badge">Actual</div>
            @endif

            @if($esOriginal)
            <div class="usuario-original-mark">&#x1F511;</div>
            @endif
        </div>
        @endforeach
    </div>

    <div class="selector-footer">
        <a href="{{ url()->previous() }}" class="btn-volver">
            <span>&#x2190;</span> Volver
        </a>
    </div>
</div>

<!-- Modal para pedir contraseña -->
<div class="modal-overlay" id="modalPassword" onclick="cerrarModal(event)">
    <div class="modal-content" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3>Cambiar a <span id="nombreUsuarioModal"></span></h3>
            <button class="modal-close" onclick="cerrarModal()">&times;</button>
        </div>
        <form action="{{ route('cambiar-usuario.cambiar') }}" method="POST">
            @csrf
            <input type="hidden" name="usuario_id" id="usuarioIdInput">
            <div class="modal-body">
                <p class="modal-info">Ingresa la contraseña del administrador para continuar</p>
                <div class="form-group">
                    <label for="password">Contraseña del Admin</label>
                    <input type="password" name="password" id="password" class="form-input" required autofocus>
                </div>
                @if(session('error'))
                <div class="modal-error">{{ session('error') }}</div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancelar" onclick="cerrarModal()">Cancelar</button>
                <button type="submit" class="btn-confirmar">Cambiar Usuario</button>
            </div>
        </form>
    </div>
</div>

<!-- Form oculto para cambio directo (sin password) -->
<form id="formCambioDirecto" action="{{ route('cambiar-usuario.cambiar') }}" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="usuario_id" id="usuarioIdDirecto">
</form>

<script>
function seleccionarUsuario(card) {
    const usuarioId = card.dataset.usuarioId;
    const necesitaPassword = card.dataset.necesitaPassword === 'true';
    const nombreUsuario = card.querySelector('.usuario-nombre').textContent;

    if (card.classList.contains('usuario-actual')) {
        return;
    }

    if (necesitaPassword) {
        document.getElementById('usuarioIdInput').value = usuarioId;
        document.getElementById('nombreUsuarioModal').textContent = nombreUsuario;
        document.getElementById('modalPassword').classList.add('show');
        document.getElementById('password').focus();
    } else {
        document.getElementById('usuarioIdDirecto').value = usuarioId;
        document.getElementById('formCambioDirecto').submit();
    }
}

function cerrarModal(event) {
    if (event && event.target !== event.currentTarget) return;
    document.getElementById('modalPassword').classList.remove('show');
    document.getElementById('password').value = '';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') cerrarModal();
});

@if(session('error') && session('usuario_id_intento'))
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('usuarioIdInput').value = '{{ session('usuario_id_intento') }}';
    document.getElementById('modalPassword').classList.add('show');
});
@endif
</script>

<style>
.selector-container {
    max-width: 900px;
    margin: 2rem auto;
    padding: 2rem;
    text-align: center;
}

.selector-header {
    margin-bottom: 3rem;
}

.selector-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--text-primary, #1f2937);
    margin-bottom: 1rem;
}

.usuario-original-banner {
    display: inline-flex;
    align-items: center;
    gap: 1rem;
    padding: 0.75rem 1.5rem;
    background: rgba(74,109,167,0.15);
    border: 2px solid #4a6da7;
    border-radius: 12px;
    color: #93c5fd;
    flex-wrap: wrap;
    justify-content: center;
}

.banner-icon {
    font-size: 1.5rem;
}

.banner-text {
    font-size: 0.95rem;
}

.btn-volver-original {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: linear-gradient(135deg, #4a6da7 0%, #2d4266 100%);
    color: white;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.85rem;
    transition: all 0.2s;
}

.btn-volver-original:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
}

.selector-alert {
    display: inline-flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1.5rem;
    background: rgba(245,158,11,0.12);
    border: 1px solid #4a6da7;
    border-radius: 10px;
    color: #fbbf24;
    font-size: 0.95rem;
}

.alert-icon {
    font-size: 1.25rem;
}

.usuarios-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 180px));
    gap: 1.5rem;
    justify-content: center;
    margin-bottom: 3rem;
}

.usuario-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 1.5rem 1rem;
    background: var(--bg-card, #fff);
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
}

.usuario-card:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: 0 12px 40px rgba(0,0,0,0.15);
}

.usuario-card.usuario-actual {
    border: 3px solid #4a6da7;
    cursor: default;
}

.usuario-card.usuario-actual:hover {
    transform: none;
}

.usuario-avatar {
    width: 100px;
    height: 100px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    font-weight: 700;
    color: white;
    margin-bottom: 1rem;
    transition: transform 0.3s;
}

.usuario-card:hover .usuario-avatar {
    transform: scale(1.05);
}

.usuario-nombre {
    font-size: 1rem;
    font-weight: 600;
    color: var(--text-primary, #1f2937);
    margin-bottom: 0.5rem;
    max-width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.usuario-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
}

.usuario-badge.admin {
    background: rgba(239,68,68,0.15);
    color: #f87171;
}

.usuario-badge.superadmin {
    background: linear-gradient(135deg, #a8c0de 0%, #8aa8d6 100%);
    color: #5b21b6;
}

.usuario-badge.territorios {
    background: linear-gradient(135deg, #a7f3d0 0%, #6ee7b7 100%);
    color: #93c5fd;
}

.usuario-badge.user {
    background: rgba(74,109,167,0.2);
    color: #93c5fd;
}

.usuario-actual-badge {
    position: absolute;
    top: -8px;
    right: -8px;
    background: #4a6da7;
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 8px;
    font-size: 0.65rem;
    font-weight: 700;
}

.usuario-original-mark {
    position: absolute;
    top: -8px;
    left: -8px;
    background: #4a6da7;
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 8px;
    font-size: 0.9rem;
}

.selector-footer {
    margin-top: 2rem;
}

.btn-volver {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background: var(--bg-card, #fff);
    color: var(--text-primary, #374151);
    border: 2px solid var(--border-color, #e5e7eb);
    border-radius: 10px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.2s;
}

.btn-volver:hover {
    border-color: #4a6da7;
    color: #4a6da7;
}

/* Modal */
.modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.7);
    z-index: 9999;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}

.modal-overlay.show {
    display: flex;
}

.modal-content {
    background: var(--bg-card, #fff);
    border-radius: 16px;
    width: 100%;
    max-width: 400px;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.25rem 1.5rem;
    background: linear-gradient(135deg, #4a6da7 0%, #2d4266 100%);
    color: white;
}

.modal-header h3 {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 600;
}

.modal-close {
    background: rgba(255,255,255,0.2);
    border: none;
    color: white;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    font-size: 1.5rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
}

.modal-close:hover {
    background: rgba(255,255,255,0.3);
}

.modal-body {
    padding: 1.5rem;
}

.modal-info {
    color: var(--text-muted, #6b7280);
    font-size: 0.9rem;
    margin-bottom: 1.25rem;
}

.form-group {
    margin-bottom: 1rem;
}

.form-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: var(--text-primary, #374151);
}

.form-input {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 2px solid var(--border-color, #e5e7eb);
    border-radius: 8px;
    font-size: 1rem;
    transition: border-color 0.2s;
    background: var(--bg-card, #fff);
    color: var(--text-primary, #1f2937);
}

.form-input:focus {
    outline: none;
    border-color: #4a6da7;
}

.modal-error {
    background: rgba(239,68,68,0.12);
    color: #f87171;
    padding: 0.75rem 1rem;
    border-radius: 8px;
    font-size: 0.875rem;
    margin-top: 1rem;
}

.modal-footer {
    display: flex;
    gap: 1rem;
    padding: 1rem 1.5rem;
    background: var(--bg-secondary, #f9fafb);
    border-top: 1px solid var(--border-color, #e5e7eb);
}

.btn-cancelar, .btn-confirmar {
    flex: 1;
    padding: 0.75rem 1rem;
    border: none;
    border-radius: 8px;
    font-size: 0.95rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-cancelar {
    background: var(--bg-card, #fff);
    color: var(--text-primary, #374151);
    border: 2px solid var(--border-color, #e5e7eb);
}

.btn-cancelar:hover {
    background: var(--bg-secondary, #f3f4f6);
}

.btn-confirmar {
    background: linear-gradient(135deg, #4a6da7 0%, #2d4266 100%);
    color: white;
}

.btn-confirmar:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
}

/* Responsive */
@media (max-width: 768px) {
    .selector-container {
        padding: 1rem;
        margin: 1rem auto;
    }

    .selector-title {
        font-size: 1.75rem;
    }

    .usuario-original-banner {
        flex-direction: column;
        gap: 0.75rem;
        padding: 1rem;
    }

    .selector-alert {
        flex-direction: column;
        text-align: center;
        padding: 1rem;
    }

    .usuarios-grid {
        grid-template-columns: repeat(auto-fit, minmax(120px, 150px));
        gap: 1rem;
    }

    .usuario-card {
        padding: 1rem 0.75rem;
    }

    .usuario-avatar {
        width: 80px;
        height: 80px;
        font-size: 2.5rem;
    }

    .usuario-nombre {
        font-size: 0.9rem;
    }
}

@media (max-width: 480px) {
    .selector-title {
        font-size: 1.5rem;
    }

    .usuarios-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .usuario-avatar {
        width: 70px;
        height: 70px;
        font-size: 2rem;
        border-radius: 12px;
    }

    .modal-footer {
        flex-direction: column;
    }
}

/* ========================================
   DARK THEME
   ======================================== */
[data-theme="dark"] .selector-title {
    color: #f5f5f5;
}

[data-theme="dark"] .selector-alert {
    background: rgba(249, 115, 22, 0.2);
    border-color: #4a6da7;
    color: #8aa8d6;
}

[data-theme="dark"] .usuario-original-banner {
    background: rgba(249, 115, 22, 0.2);
    border-color: #4a6da7;
    color: #8aa8d6;
}

[data-theme="dark"] .btn-volver-original {
    background: linear-gradient(135deg, #4a6da7 0%, #3d5a8a 100%);
    color: #0a0a0a;
}

[data-theme="dark"] .usuario-card {
    background: #171717;
    border: 1px solid #262626;
}

[data-theme="dark"] .usuario-card:hover {
    box-shadow: 0 12px 40px rgba(249, 115, 22, 0.2);
}

[data-theme="dark"] .usuario-card.usuario-actual {
    border-color: #4a6da7;
}

[data-theme="dark"] .usuario-nombre {
    color: #e5e5e5;
}

[data-theme="dark"] .usuario-badge.admin {
    background: rgba(220, 38, 38, 0.2);
    color: #ced4da;
}

[data-theme="dark"] .usuario-badge.superadmin {
    background: rgba(124, 58, 237, 0.2);
    color: #a8c0de;
}

[data-theme="dark"] .usuario-badge.territorios {
    background: rgba(16, 185, 129, 0.2);
    color: #6ee7b7;
}

[data-theme="dark"] .usuario-badge.user {
    background: rgba(59, 130, 246, 0.2);
    color: #8aa8d6;
}

[data-theme="dark"] .btn-volver {
    background: #171717;
    border-color: #262626;
    color: #e5e5e5;
}

[data-theme="dark"] .btn-volver:hover {
    border-color: #4a6da7;
    color: #4a6da7;
}

[data-theme="dark"] .modal-content {
    background: #171717;
    border: 1px solid #262626;
}

[data-theme="dark"] .modal-header {
    background: linear-gradient(135deg, #4a6da7 0%, #3d5a8a 100%);
    color: #0a0a0a;
}

[data-theme="dark"] .modal-info {
    color: #a3a3a3;
}

[data-theme="dark"] .form-group label {
    color: #e5e5e5;
}

[data-theme="dark"] .form-input {
    background: #262626;
    border-color: #404040;
    color: #e5e5e5;
}

[data-theme="dark"] .form-input:focus {
    border-color: #4a6da7;
}

[data-theme="dark"] .modal-footer {
    background: #0a0a0a;
    border-color: #262626;
}

[data-theme="dark"] .btn-cancelar {
    background: #262626;
    border-color: #404040;
    color: #e5e5e5;
}

[data-theme="dark"] .btn-confirmar {
    background: linear-gradient(135deg, #4a6da7 0%, #3d5a8a 100%);
    color: #0a0a0a;
}

[data-theme="dark"] .modal-error {
    background: rgba(220, 38, 38, 0.2);
    color: #ced4da;
}
</style>

@endsection
