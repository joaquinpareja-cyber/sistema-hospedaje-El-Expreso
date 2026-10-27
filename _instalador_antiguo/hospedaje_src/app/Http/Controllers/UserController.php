<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');  // solo admins pueden gestionar usuarios
    }

    public function index()
    {
        $usuarios = User::withCount(['reservas', 'facturas'])
            ->orderBy('name')
            ->get();

        return view('usuarios.index', compact('usuarios'));
    }

    public function create()
    {
        return view('usuarios.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:100',
            'email'     => 'required|email|max:150|unique:users,email',
            'password'  => ['required', 'confirmed', Password::min(8)],
            'role'      => 'required|in:admin,supervisor,recepcionista',
            'telefono'  => 'nullable|string|max:20',
            'activo'    => 'boolean',
        ]);

        $data['password'] = Hash::make($data['password']);
        $data['activo']   = $request->boolean('activo', true);

        User::create($data);

        return redirect()->route('usuarios.index')
            ->with('success', "Usuario {$data['name']} creado correctamente.");
    }

    public function edit(User $usuario)
    {
        // No permitir editar el propio perfil de admin por aquí
        if ($usuario->id === auth()->id() && !$usuario->isAdmin()) {
            return back()->with('error', 'Usa "Mi Perfil" para editar tus propios datos.');
        }
        return view('usuarios.edit', compact('usuario'));
    }

    public function update(Request $request, User $usuario)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => "required|email|max:150|unique:users,email,{$usuario->id}",
            'role'     => 'required|in:admin,supervisor,recepcionista',
            'telefono' => 'nullable|string|max:20',
            'activo'   => 'boolean',
        ]);

        // Cambio de contraseña opcional
        if ($request->filled('password')) {
            $request->validate([
                'password' => ['confirmed', Password::min(8)],
            ]);
            $data['password'] = Hash::make($request->password);
        }

        $data['activo'] = $request->boolean('activo', true);
        $usuario->update($data);

        return redirect()->route('usuarios.index')
            ->with('success', "Usuario {$usuario->name} actualizado.");
    }

    public function destroy(User $usuario)
    {
        if ($usuario->id === auth()->id()) {
            return back()->with('error', 'No puedes eliminar tu propio usuario.');
        }
        if ($usuario->reservas()->exists() || $usuario->facturas()->exists()) {
            // Desactivar en lugar de eliminar para preservar historial
            $usuario->update(['activo' => false]);
            return redirect()->route('usuarios.index')
                ->with('info', "El usuario {$usuario->name} fue desactivado (tiene registros asociados).");
        }
        $usuario->delete();
        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario eliminado.');
    }

    public function toggleActivo(User $usuario)
    {
        if ($usuario->id === auth()->id()) {
            return back()->with('error', 'No puedes desactivarte a ti mismo.');
        }
        $usuario->update(['activo' => !$usuario->activo]);
        $estado = $usuario->activo ? 'activado' : 'desactivado';
        return back()->with('success', "Usuario {$usuario->name} $estado.");
    }
}
