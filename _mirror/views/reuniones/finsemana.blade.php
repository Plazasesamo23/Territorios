@extends('layouts.app')

@section('title', 'Reunion Fin de Semana')

@section('content')

<div class="page-md">
    <div class="flex justify-between items-center mb-2">
        <div>
            <h1 class="page-title">Reunion de fin de semana</h1>
            <p class="page-subtitle">Discurso publico y Estudio de La Atalaya</p>
        </div>
    </div>

    <div class="empty-state">
        <div class="icon">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/>
            </svg>
        </div>
        <div class="title">En desarrollo</div>
        <div class="desc">La gestion del discurso publico y estudio de La Atalaya estara disponible proximamente. Por ahora puedes gestionar la reunion de entre semana (VyM) desde el menu.</div>
        <a href="{{ route('reuniones.index') }}" class="btn btn-ghost" style="margin-top: 1rem;">Ir a VyM</a>
    </div>
</div>

@endsection
