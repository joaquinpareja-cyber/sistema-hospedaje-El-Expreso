<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');  // solo admins pueden gestionar usuarios
    }

    public function index()
    {
        $usuarios = User::withCount(['reservas', 'facturas', 'documentos'])
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
            'role'      => 'required|in:' . implode(',', array_keys(User::ROLES)),
            'telefono'  => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:255',
            'activo'    => 'boolean',
        ]);

        $data['password'] = Hash::make($data['password']);
        $data['activo']   = $request->boolean('activo');

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
        $usuario->load('documentos');
        return view('usuarios.edit', compact('usuario'));
    }

    public function update(Request $request, User $usuario)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => "required|email|max:150|unique:users,email,{$usuario->id}",
            'role'     => 'required|in:' . implode(',', array_keys(User::ROLES)),
            'telefono' => 'nullable|string|max:20',
            'direccion'=> 'nullable|string|max:255',
            'activo'   => 'boolean',
        ]);

        // Cambio de contraseña opcional
        if ($request->filled('password')) {
            $request->validate([
                'password' => ['confirmed', Password::min(8)],
            ]);
            $data['password'] = Hash::make($request->password);
        }

        $data['activo'] = $request->boolean('activo');

        // Evitar que el sistema se quede sin administradores activos
        $dejaDeSerAdminActivo = $usuario->isAdmin() && $usuario->activo
            && ($data['role'] !== 'admin' || !$data['activo']);
        if ($dejaDeSerAdminActivo) {
            if ($usuario->id === auth()->id()) {
                return back()->withInput()->with('error', 'No puedes quitarte el rol de administrador ni desactivarte a ti mismo.');
            }
            if (!User::where('role', 'admin')->where('activo', true)->where('id', '!=', $usuario->id)->exists()) {
                return back()->withInput()->with('error', 'Debe existir al menos un administrador activo.');
            }
        }

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
        // Sus documentos se borran en cascada en la base; aquí se borran los archivos
        Storage::disk('local')->deleteDirectory("documentos/{$usuario->id}");
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
