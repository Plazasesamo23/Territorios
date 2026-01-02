<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Use 'name' instead of 'email' for login.
     */
    public function username()
    {
        return 'name';
    }

    /**
     * Redirect based on user role after login.
     */
    protected function authenticated(Request $request, $user)
    {
        // Guardar la congregacion activa en sesion
        if ($user->congregacion_id) {
            session(['congregacion_activa_id' => $user->congregacion_id]);
        }

        // Usuarios de territorios van al panel de territorios
        if ($user->isTerritoriosUser()) {
            return redirect()->route('panel-territorios');
        }

        // Admins y superadmins van al dashboard
        if ($user->isAdmin() || $user->isSuperAdmin()) {
            return redirect()->route('dashboard');
        }

        // Usuarios normales van al dashboard tambien
        return redirect()->route('dashboard');
    }
}
