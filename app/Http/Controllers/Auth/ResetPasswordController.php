<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords {
        reset as protected restablecer;
    }

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /** Bloquea también un enlace de recuperación emitido antes de que el usuario pasara a limpieza */
    public function reset(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        if ($user && !$user->puedeEditarSuPerfil()) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'Para cambiar tu contraseña, solicítalo al administrador.']);
        }

        return $this->restablecer($request);
    }
}
