@extends('layouts.app')
@section('title', 'Editar Tarifa')
@section('page-title', 'Editar Tarifa de Temporada')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('tarifas.index') }}">Tarifas</a></li>
    <li class="breadcrumb-item active">Editar</li>
@endsection

@section('content')
<div class="row justify-content-center">
<div class="col-md-8">
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-edit mr-2"></i>{{ $tarifa->nombre }}</h3>
    </div>
    <form action="{{ route('tarifas.update', $tarifa) }}" method="POST">
        @csrf @method('PUT')
        @include('tarifas._form')
        <div class="card-footer">
            <button type="submit" class="btn btn-warning"><i class="fas fa-save mr-2"></i>Guardar Cambios</button>
            <a href="{{ route('tarifas.index') }}" class="btn btn-secondary ml-2">Cancelar</a>
        </div>
    </form>
</div>
</div>
</div>
@endsection
