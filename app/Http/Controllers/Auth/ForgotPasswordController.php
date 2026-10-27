<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails {
        sendResetLinkEmail as protected enviarEnlaceDeRecuperacion;
    }

    /** Quien no puede cambiar su contraseña (personal de limpieza) tampoco puede recuperarla por correo */
    public function sendResetLinkEmail(Request $request)
    {
        $this->validateEmail($request);

        $user = User::where('email', $request->email)->first();
        if ($user && !$user->puedeEditarSuPerfil()) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'Para cambiar tu contraseña, solicítalo al administrador.']);
        }

        return $this->enviarEnlaceDeRecuperacion($request);
    }
}
