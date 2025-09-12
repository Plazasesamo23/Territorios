<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class S13Controller extends Controller
{
    /**
     * Mostrar la página de reportes S13
     */
    public function index()
    {
        return view('s13.index');
    }
}
