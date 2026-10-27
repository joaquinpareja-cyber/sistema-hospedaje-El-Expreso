@php($t = $tarifa ?? null)
<div class="card-body">
    <div class="form-group">
        <label>Nombre <span class="text-danger">*</span></label>
        <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror"
               value="{{ old('nombre', $t?->nombre) }}" placeholder="Ej: Temporada alta, Fiestas Patrias" required maxlength="100">
        @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="form-group">
        <label>Aplica a</label>
        <select name="tipo_habitacion_id" class="form-control select2">
            <option value="">Todos los tipos de habitación</option>
            @foreach($tipos as $tipo)
            <option value="{{ $tipo->id }}" {{ old('tipo_habitacion_id', $t?->tipo_habitacion_id) == $tipo->id ? 'selected' : '' }}>
                {{ $tipo->nombre }} (base {{ $moneda }} {{ number_format($tipo->precio_base, 2) }})
            </option>
            @endforeach
        </select>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label>Desde <span class="text-danger">*</span></label>
                <input type="date" name="fecha_inicio" class="form-control @error('fecha_inicio') is-invalid @enderror"
                       value="{{ old('fecha_inicio', $t?->fecha_inicio?->format('Y-m-d')) }}" required>
                @error('fecha_inicio')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>Hasta (inclusive) <span class="text-danger">*</span></label>
                <input type="date" name="fecha_fin" class="form-control @error('fecha_fin') is-invalid @enderror"
                       value="{{ old('fecha_fin', $t?->fecha_fin?->format('Y-m-d')) }}" required>
                @error('fecha_fin')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-5">
            <div class="form-group">
                <label>Tipo de precio <span class="text-danger">*</span></label>
                <select name="tipo_precio" id="tipo_precio" class="form-control">
                    <option value="fijo" {{ old('tipo_precio', $t?->tipo_precio) === 'fijo' ? 'selected' : '' }}>Precio fijo por noche</option>
                    <option value="porcentaje" {{ old('tipo_precio', $t?->tipo_precio) === 'porcentaje' ? 'selected' : '' }}>% sobre el precio base</option>
                </select>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label id="lblPrecio">Valor <span class="text-danger">*</span></label>
                <input type="number" name="precio_noche" step="0.01"
                       class="form-control @error('precio_noche') is-invalid @enderror"
                       value="{{ old('precio_noche', $t?->precio_noche) }}" required>
                @error('precio_noche')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <small class="text-muted" id="ayudaPrecio"></small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label>Prioridad</label>
                <input type="number" name="prioridad" class="form-control" min="0" max="10"
                       value="{{ old('prioridad', $t?->prioridad ?? 0) }}">
            </div>
        </div>
    </div>

    <div class="form-group">
        <label>Descripción</label>
        <textarea name="descripcion" class="form-control" rows="2" maxlength="300">{{ old('descripcion', $t?->descripcion) }}</textarea>
    </div>

    <div class="custom-control custom-switch">
        <input type="hidden" name="activa" value="0">
        <input type="checkbox" class="custom-control-input" id="activa" name="activa" value="1"
               {{ old('activa', $t?->activa ?? true) ? 'checked' : '' }}>
        <label class="custom-control-label" for="activa">Tarifa activa</label>
    </div>
</div>

@push('scripts')
<script>
function ayudaPrecio() {
    const pct = $('#tipo_precio').val() === 'porcentaje';
    $('#lblPrecio').html((pct ? 'Porcentaje (%)' : 'Precio por noche ({{ $moneda }})') + ' <span class="text-danger">*</span>');
    $('#ayudaPrecio').text(pct ? 'Ej: 20 = +20%, -15 = 15% de descuento' : 'Reemplaza al precio base');
}
$('#tipo_precio').on('change', ayudaPrecio);
$(ayudaPrecio);
</script>
@endpush
