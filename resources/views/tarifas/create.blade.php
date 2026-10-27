@extends('layouts.app')
@section('title', 'Nueva Tarifa')
@section('page-title', 'Nueva Tarifa de Temporada')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('tarifas.index') }}">Tarifas</a></li>
    <li class="breadcrumb-item active">Nueva</li>
@endsection

@section('content')
<div class="row justify-content-center">
<div class="col-md-8">
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-plus mr-2"></i>Registrar Tarifa</h3>
    </div>
    <form action="{{ route('tarifas.store') }}" method="POST">
        @csrf
        @include('tarifas._form')
        <div class="card-footer">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-2"></i>Guardar</button>
            <a href="{{ route('tarifas.index') }}" class="btn btn-secondary ml-2">Cancelar</a>
        </div>
    </form>
</div>
</div>
</div>
@endsection
