<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Territorio;
use App\Models\Publicador;

class PanelTerritoriosController extends Controller
{
    /**
     * Mostrar el panel simplificado para usuarios de territorios
     */
    public function index()
    {
        $congregacionId = session('congregacion_activa_id');

        $territorios = Territorio::where('congregacion_id', $congregacionId)
            ->with('registros.publicador')
            ->orderBy('numero')
            ->get();

        $publicadores = Publicador::where('congregacion_id', $congregacionId)
            ->where('activo', true)
            ->with('registros.territorio')
            ->orderBy('nombre')
            ->get();

        return view('panel-territorios', compact('territorios', 'publicadores'));
    }
}
