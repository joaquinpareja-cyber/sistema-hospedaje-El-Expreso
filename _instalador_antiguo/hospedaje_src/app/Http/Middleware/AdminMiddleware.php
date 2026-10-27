<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next, string $role = 'admin'): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        if (!$user->activo) {
            auth()->logout();
            return redirect()->route('login')
                ->with('error', 'Tu cuenta está desactivada. Contacta al administrador.');
        }

        // Verificar rol mínimo requerido
        $jerarquia = ['recepcionista' => 1, 'supervisor' => 2, 'admin' => 3];
        $rolUsuario   = $jerarquia[$user->role]   ?? 0;
        $rolRequerido = $jerarquia[$role] ?? 3;

        if ($rolUsuario < $rolRequerido) {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        return $next($request);
    }
}
