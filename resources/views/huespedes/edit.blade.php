@extends('layouts.app')
@section('title', "Editar: {$huesped->nombre_completo}")
@section('page-title', "Editar Huésped")
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('huespedes.index') }}">Huéspedes</a></li>
    <li class="breadcrumb-item"><a href="{{ route('huespedes.show', $huesped) }}">{{ $huesped->nombre_completo }}</a></li>
    <li class="breadcrumb-item active">Editar</li>
@endsection

@section('content')
<div class="row justify-content-center">
<div class="col-md-9">
<div class="card">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-user-edit mr-2"></i>Editar: {{ $huesped->nombre_completo }}</h3></div>
    <form action="{{ route('huespedes.update', $huesped) }}" method="POST">
        @csrf @method('PUT')
        <div class="card-body">
            @include('huespedes._form')
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-warning"><i class="fas fa-save mr-2"></i>Guardar Cambios</button>
            <a href="{{ route('huespedes.show', $huesped) }}" class="btn btn-secondary ml-2">Cancelar</a>
        </div>
    </form>
</div>
</div>
</div>
@endsection
