{{--
    Documentación del personal.
    $usuario:       dueño de los documentos (con la relación "documentos" cargada)
    $puedeSubir:    el propio empleado, desde Mi Perfil
    $puedeEliminar: el administrador, desde Editar Usuario
--}}
@php
    $docs = $usuario->documentos->keyBy('tipo');
    $tipos = \App\Models\DocumentoUsuario::TIPOS;
    $formatos = strtoupper(implode(', ', array_diff(\App\Models\DocumentoUsuario::EXTENSIONES, ['jpeg'])));
    $maxMb = \App\Models\DocumentoUsuario::MAX_KB / 1024;
@endphp

<p class="text-muted mb-3">
    <i class="fas fa-folder-open mr-1"></i>
    {{ $docs->count() }} de {{ count($tipos) }} documentos entregados.
    @if($puedeSubir) Formatos: {{ $formatos }} · Máx. {{ $maxMb }} MB por archivo. @endif
</p>

@foreach($tipos as $tipo => $info)
@php($doc = $docs->get($tipo))
<div class="border rounded p-3 mb-3 {{ $doc ? 'border-success' : 'border-warning' }}">
    <div class="d-flex justify-content-between align-items-start flex-wrap">
        <div class="mb-2">
            <h6 class="mb-1">
                <i class="fas {{ $info['icono'] }} mr-2 {{ $doc ? 'text-success' : 'text-warning' }}"></i>{{ $info['label'] }}
            </h6>
            @if($doc)
                <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Entregado</span>
                <small class="text-muted ml-1">
                    <i class="fas {{ $doc->es_pdf ? 'fa-file-pdf' : 'fa-file-image' }} mr-1"></i>{{ $doc->nombre_original }}
                    · {{ $doc->tamano_legible }} · {{ $doc->updated_at->format('d/m/Y H:i') }}
                </small>
            @else
                <span class="badge badge-warning">Pendiente</span>
                <small class="text-muted ml-1">{{ $info['ayuda'] }}</small>
            @endif
        </div>
        <div>
            @if($doc)
                <a href="{{ route('documentos.ver', [$usuario, $tipo]) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-eye mr-1"></i>Ver
                </a>
                @if($puedeEliminar)
                <form action="{{ route('usuarios.documentos.eliminar', [$usuario, $tipo]) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('¿Eliminar {{ $info['label'] }}? El empleado tendrá que subirlo de nuevo.')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                </form>
                @endif
            @endif
        </div>
    </div>

    @if($puedeSubir)
    <form action="{{ route('perfil.documentos.subir', $tipo) }}" method="POST" enctype="multipart/form-data" class="d-flex align-items-center flex-wrap">
        @csrf
        <input type="file" name="archivo" class="form-control-file mr-2 mb-1" style="max-width:320px"
               accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png" required>
        <button class="btn btn-sm {{ $doc ? 'btn-outline-secondary' : 'btn-primary' }} mb-1">
            <i class="fas fa-upload mr-1"></i>{{ $doc ? 'Reemplazar' : 'Subir' }}
        </button>
    </form>
    @endif
</div>
@endforeach
