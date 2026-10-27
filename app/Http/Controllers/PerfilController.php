<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class PerfilController extends Controller
{
    public function __construct()
    {
        // El personal de limpieza solo consulta sus datos: nombre, usuario y contraseña
        // los gestiona el administrador. Su foto sí la puede cambiar.
        $this->middleware(function ($request, $next) {
            if (!$request->user()->puedeEditarSuPerfil()) {
                return redirect()->route('perfil.index')
                    ->with('error', 'Tus datos solo los puede modificar el administrador.');
            }
            return $next($request);
        })->except('index', 'updateAvatar', 'deleteAvatar');
    }

    public function index()
    {
        $user = auth()->user()->load('documentos');
        return view('perfil.index', compact('user'));
    }

    /** Actualizar datos generales */
    public function update(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|max:150|unique:users,email,' . $user->id,
            'telefono' => 'nullable|string|max:20',
            'direccion'=> 'nullable|string|max:255',
        ], [
            'name.required'  => 'El nombre es obligatorio.',
            'email.required' => 'El correo es obligatorio.',
            'email.unique'   => 'Este correo ya está en uso por otro usuario.',
        ]);

        $user->update([
            'name'     => $request->name,
            'email'    => $request->email,
            'telefono' => $request->telefono,
            'direccion'=> $request->direccion,
        ]);

        return back()->with('success', 'Datos actualizados correctamente.');
    }

    /** Cambiar contraseña */
    public function updatePassword(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'password_actual' => 'required',
            'password'        => ['required', 'confirmed', Password::min(8)],
        ], [
            'password_actual.required' => 'Ingresa tu contraseña actual.',
            'password.min'             => 'La nueva contraseña debe tener al menos 8 caracteres.',
            'password.confirmed'       => 'La confirmación no coincide.',
        ]);

        if (!Hash::check($request->password_actual, $user->password)) {
            return back()->withErrors(['password_actual' => 'La contraseña actual es incorrecta.'])
                         ->with('tab', 'seguridad');
        }

        $user->update(['password' => Hash::make($request->password)]);

        return back()->with('success', '✓ Contraseña actualizada correctamente.')->with('tab', 'seguridad');
    }

    /** Subir avatar */
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:png,jpg,jpeg|max:1024',
        ], [
            'avatar.image' => 'El archivo debe ser una imagen.',
            'avatar.max'   => 'La imagen no debe superar 1MB.',
        ]);

        $user = auth()->user();

        // Eliminar avatar anterior
        if ($user->avatar) {
            Storage::disk('public')->delete('avatars/' . $user->avatar);
        }

        $ext      = $request->file('avatar')->extension(); // según el contenido real, no el nombre enviado
        $filename = 'user_' . $user->id . '_' . time() . '.' . $ext;
        $request->file('avatar')->storeAs('avatars', $filename, 'public');

        $user->update(['avatar' => $filename]);

        return back()->with('success', '✓ Foto de perfil actualizada.')->with('tab', 'avatar');
    }

    /** Eliminar avatar */
    public function deleteAvatar()
    {
        $user = auth()->user();
        if ($user->avatar) {
            Storage::disk('public')->delete('avatars/' . $user->avatar);
            $user->update(['avatar' => null]);
        }
        return back()->with('success', 'Foto eliminada.')->with('tab', 'avatar');
    }
}
