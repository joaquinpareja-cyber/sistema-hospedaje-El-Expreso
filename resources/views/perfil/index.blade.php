@extends('layouts.app')
@section('title', 'Mi Perfil')
@section('page-title', 'Mi Perfil')
@section('breadcrumb')
    <li class="breadcrumb-item active">Mi Perfil</li>
@endsection

@php
    $editable = $user->puedeEditarSuPerfil();
    $tabPorDefecto = $editable ? 'datos' : 'info';
    $tab = session('tab', match (true) {
        $errors->has('password_actual') || $errors->has('password') => 'seguridad',
        $errors->has('avatar')  => 'avatar',
        $errors->has('archivo') => 'documentacion',
        default                 => $tabPorDefecto,
    });
@endphp

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card card-primary card-outline">
            <div class="card-body box-profile text-center">
                <img src="{{ $user->avatar_url }}" class="profile-user-img img-fluid img-circle" style="width:110px;height:110px;object-fit:cover" alt="">
                <h3 class="profile-username mt-2">{{ $user->name }}</h3>
                <p class="text-muted mb-1">{{ $user->email }}</p>
                <span class="badge badge-{{ $user->role_badge }}">{{ $user->role_label }}</span>
                <ul class="list-group list-group-unbordered mt-3 text-left">
                    <li class="list-group-item"><b>Teléfono</b> <span class="float-right">{{ $user->telefono ?? '—' }}</span></li>
                    <li class="list-group-item"><b>Dirección</b> <span class="float-right text-right ml-3">{{ $user->direccion ?? '—' }}</span></li>
                    <li class="list-group-item"><b>Miembro desde</b> <span class="float-right">{{ $user->created_at?->format('d/m/Y') ?? '—' }}</span></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header p-2">
                <ul class="nav nav-pills">
                    @if($editable)
                        <li class="nav-item"><a class="nav-link {{ $tab === 'datos' ? 'active' : '' }}" href="#datos" data-toggle="tab">Datos</a></li>
                        <li class="nav-item"><a class="nav-link {{ $tab === 'seguridad' ? 'active' : '' }}" href="#seguridad" data-toggle="tab">Contraseña</a></li>
                    @else
                        <li class="nav-item"><a class="nav-link {{ $tab === 'info' ? 'active' : '' }}" href="#info" data-toggle="tab">Mi Información</a></li>
                    @endif
                    <li class="nav-item"><a class="nav-link {{ $tab === 'avatar' ? 'active' : '' }}" href="#avatar" data-toggle="tab">Foto</a></li>
                    @if($user->requiereDocumentacion())
                        <li class="nav-item">
                            <a class="nav-link {{ $tab === 'documentacion' ? 'active' : '' }}" href="#documentacion" data-toggle="tab">
                                Documentación
                                @php($faltan = count(\App\Models\DocumentoUsuario::TIPOS) - $user->documentos->count())
                                @if($faltan > 0)<span class="badge badge-warning ml-1">{{ $faltan }}</span>@endif
                            </a>
                        </li>
                    @endif
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">

                    @if($editable)
                    {{-- Datos generales --}}
                    <div class="tab-pane {{ $tab === 'datos' ? 'active' : '' }}" id="datos">
                        <form action="{{ route('perfil.update') }}" method="POST">
                            @csrf @method('PUT')
                            <div class="form-group">
                                <label>Nombre <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name', $user->name) }}" required maxlength="100">
                            </div>
                            <div class="form-group">
                                <label>Correo electrónico <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email', $user->email) }}" required maxlength="150">
                            </div>
                            <div class="form-group">
                                <label>Teléfono</label>
                                <input type="text" name="telefono" class="form-control" value="{{ old('telefono', $user->telefono) }}" maxlength="20">
                            </div>
                            <div class="form-group">
                                <label>Dirección</label>
                                <input type="text" name="direccion" class="form-control" value="{{ old('direccion', $user->direccion) }}" maxlength="255">
                            </div>
                            <button class="btn btn-primary"><i class="fas fa-save mr-2"></i>Guardar</button>
                        </form>
                    </div>

                    {{-- Contraseña --}}
                    <div class="tab-pane {{ $tab === 'seguridad' ? 'active' : '' }}" id="seguridad">
                        <form action="{{ route('perfil.password') }}" method="POST">
                            @csrf @method('PUT')
                            <div class="form-group">
                                <label>Contraseña actual <span class="text-danger">*</span></label>
                                <input type="password" name="password_actual" class="form-control @error('password_actual') is-invalid @enderror"
                                       required autocomplete="current-password">
                            </div>
                            <div class="form-group">
                                <label>Nueva contraseña <span class="text-danger">*</span></label>
                                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                                       required minlength="8" autocomplete="new-password">
                                <small class="text-muted">Mínimo 8 caracteres.</small>
                            </div>
                            <div class="form-group">
                                <label>Confirmar nueva contraseña <span class="text-danger">*</span></label>
                                <input type="password" name="password_confirmation" class="form-control" required autocomplete="new-password">
                            </div>
                            <button class="btn btn-warning"><i class="fas fa-key mr-2"></i>Cambiar contraseña</button>
                        </form>
                    </div>
                    @endif

                    {{-- Foto (todos los roles, incluido el personal de limpieza) --}}
                    <div class="tab-pane {{ $tab === 'avatar' ? 'active' : '' }}" id="avatar">
                        <form action="{{ route('perfil.avatar') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group">
                                <label>Nueva foto</label>
                                <input type="file" name="avatar" class="form-control-file @error('avatar') is-invalid @enderror"
                                       accept="image/png,image/jpeg" required>
                                <small class="text-muted">PNG o JPG · Máx. 1MB</small>
                            </div>
                            <button class="btn btn-primary"><i class="fas fa-upload mr-2"></i>Subir foto</button>
                        </form>
                        @if($user->avatar)
                        <form action="{{ route('perfil.avatar.delete') }}" method="POST" class="mt-3"
                              onsubmit="return confirm('¿Eliminar tu foto de perfil?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-outline-danger btn-sm"><i class="fas fa-trash mr-1"></i>Eliminar foto actual</button>
                        </form>
                        @endif
                    </div>

                    @if(!$editable)
                    {{-- Solo lectura: los datos los registra y corrige el administrador --}}
                    <div class="tab-pane {{ $tab === 'info' ? 'active' : '' }}" id="info">
                        <table class="table table-sm table-borderless mb-3">
                            <tr><th class="text-muted" width="35%">Nombre</th><td>{{ $user->name }}</td></tr>
                            <tr><th class="text-muted">Usuario (correo)</th><td>{{ $user->email }}</td></tr>
                            <tr><th class="text-muted">Cargo</th><td>{{ $user->role_label }}</td></tr>
                            <tr><th class="text-muted">Teléfono</th><td>{{ $user->telefono ?? '—' }}</td></tr>
                            <tr><th class="text-muted">Dirección</th><td>{{ $user->direccion ?? '—' }}</td></tr>
                            <tr><th class="text-muted">Miembro desde</th><td>{{ $user->created_at?->format('d/m/Y') ?? '—' }}</td></tr>
                        </table>
                        <div class="alert alert-light border mb-0">
                            <i class="fas fa-info-circle text-info mr-2"></i>
                            Si algún dato es incorrecto o necesitas cambiar tu contraseña, pídeselo al administrador.
                            Tu foto sí la puedes cambiar en la pestaña <strong>Foto</strong>.
                        </div>
                    </div>
                    @endif

                    @if($user->requiereDocumentacion())
                    {{-- Documentación --}}
                    <div class="tab-pane {{ $tab === 'documentacion' ? 'active' : '' }}" id="documentacion">
                        @error('archivo')
                        <div class="alert alert-danger"><i class="fas fa-exclamation-circle mr-2"></i>{{ $message }}</div>
                        @enderror
                        @include('usuarios._documentos', ['usuario' => $user, 'puedeSubir' => true, 'puedeEliminar' => false])
                    </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
