@extends('layouts.app')
@section('title', 'Usuarios del Sistema')
@section('page-title', 'Gestión de Usuarios')
@section('breadcrumb')
    <li class="breadcrumb-item active">Usuarios</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-users-cog mr-2"></i>Usuarios del Sistema</h3>
        <div class="card-tools">
            <a href="{{ route('usuarios.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-user-plus mr-1"></i>Nuevo Usuario
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover table-striped mb-0">
            <thead class="thead-light">
                <tr>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Teléfono</th>
                    <th class="text-center">Rol</th>
                    <th class="text-center">Reservas</th>
                    <th class="text-center">Facturas</th>
                    <th class="text-center">Estado</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($usuarios as $u)
                <tr class="{{ !$u->activo ? 'table-secondary text-muted' : '' }}">
                    <td>
                        <strong>{{ $u->name }}</strong>
                        @if($u->id === auth()->id())
                            <span class="badge badge-light ml-1">Tú</span>
                        @endif
                    </td>
                    <td>{{ $u->email }}</td>
                    <td>{{ $u->telefono ?? '—' }}</td>
                    <td class="text-center">
                        <span class="badge badge-{{ $u->role_badge }}">{{ $u->role_label }}</span>
                    </td>
                    <td class="text-center">
                        <span class="badge badge-secondary">{{ $u->reservas_count }}</span>
                    </td>
                    <td class="text-center">
                        <span class="badge badge-secondary">{{ $u->facturas_count }}</span>
                    </td>
                    <td class="text-center">
                        @if($u->id !== auth()->id())
                        <form action="{{ route('usuarios.toggle', $u) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit"
                                    class="btn btn-xs {{ $u->activo ? 'btn-success' : 'btn-secondary' }}">
                                <i class="fas fa-{{ $u->activo ? 'check' : 'ban' }}"></i>
                                {{ $u->activo ? 'Activo' : 'Inactivo' }}
                            </button>
                        </form>
                        @else
                            <span class="badge badge-success">Activo</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <a href="{{ route('usuarios.edit', $u) }}"
                           class="btn btn-xs btn-warning" title="Editar">
                            <i class="fas fa-edit"></i>
                        </a>
                        @if($u->id !== auth()->id())
                        <form action="{{ route('usuarios.destroy', $u) }}" method="POST"
                              class="d-inline"
                              onsubmit="return confirm('¿Eliminar/desactivar a {{ $u->name }}?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-xs btn-danger" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-4 text-muted">Sin usuarios registrados.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer text-muted">
        <small>{{ $usuarios->count() }} usuario(s) en el sistema</small>
    </div>
</div>
@endsection
