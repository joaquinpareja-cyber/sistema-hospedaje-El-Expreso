{{--
    Ficha del huésped, compartida por crear/editar huésped y el registro directo.
    $huesped: null al crear.
    $prefijo: opcional, para repetir la ficha en un mismo formulario (ej. "personas[1]").
--}}
@php
    $h = $huesped ?? null;
    $prefijo = $prefijo ?? null;
    // "personas[1]" → nombres personas[1][campo]; claves de old()/errores personas.1.campo
    $clave = $prefijo ? str_replace(['[', ']'], ['.', ''], $prefijo) : null;
    $n = fn($campo) => $prefijo ? "{$prefijo}[{$campo}]" : $campo;
    $k = fn($campo) => $clave ? "{$clave}.{$campo}" : $campo;
@endphp
<div class="js-ficha">
<div class="form-group">
    <label>Nombre Completo <span class="text-danger">*</span></label>
    <input type="text" name="{{ $n('nombre_completo') }}" class="form-control @error($k('nombre_completo')) is-invalid @enderror"
           value="{{ old($k('nombre_completo'), $h?->nombre_completo) }}" placeholder="Ej: Juan Pérez Mamani" maxlength="200" required>
    @error($k('nombre_completo'))<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>Tipo de Documento <span class="text-danger">*</span></label>
            <select name="{{ $n('tipo_documento') }}" class="form-control select2 @error($k('tipo_documento')) is-invalid @enderror" required>
                @foreach(\App\Models\Huesped::TIPOS_DOCUMENTO as $td => $tdLabel)
                    <option value="{{ $td }}" {{ old($k('tipo_documento'), $h?->tipo_documento) == $td ? 'selected' : '' }}>{{ $tdLabel }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Número de Documento <span class="text-danger">*</span></label>
            <input type="text" name="{{ $n('num_documento') }}" class="form-control @error($k('num_documento')) is-invalid @enderror"
                   value="{{ old($k('num_documento'), $h?->num_documento) }}" placeholder="Nro. Documento" required>
            @error($k('num_documento'))<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Género</label>
            <select name="{{ $n('genero') }}" class="form-control select2">
                <option value="">Sin especificar</option>
                @foreach(['M' => 'Masculino', 'F' => 'Femenino', 'Otro' => 'Otro'] as $g => $gLabel)
                    <option value="{{ $g }}" {{ old($k('genero'), $h?->genero) == $g ? 'selected' : '' }}>{{ $gLabel }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>Fecha de Nacimiento</label>
            <input type="date" name="{{ $n('fecha_nacimiento') }}"
                   class="form-control js-fecha-nac @error($k('fecha_nacimiento')) is-invalid @enderror"
                   value="{{ old($k('fecha_nacimiento'), $h?->fecha_nacimiento?->format('Y-m-d')) }}"
                   max="{{ now()->subDay()->format('Y-m-d') }}">
            @error($k('fecha_nacimiento'))<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group">
            <label>Edad</label>
            <div class="input-group">
                <input type="text" class="form-control text-center font-weight-bold js-edad" readonly tabindex="-1"
                       value="{{ $h?->edad }}" placeholder="—">
                <div class="input-group-append"><span class="input-group-text">años</span></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Estado Civil</label>
            <select name="{{ $n('estado_civil') }}" class="form-control select2 @error($k('estado_civil')) is-invalid @enderror">
                <option value="">Sin especificar</option>
                @foreach(\App\Models\Huesped::ESTADOS_CIVILES as $ec => $ecLabel)
                    <option value="{{ $ec }}" {{ old($k('estado_civil'), $h?->estado_civil) == $ec ? 'selected' : '' }}>{{ $ecLabel }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Nacionalidad</label>
            <input type="text" name="{{ $n('nacionalidad') }}" class="form-control"
                   value="{{ old($k('nacionalidad'), $h?->nacionalidad) }}" placeholder="Ej: Boliviana">
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>Procedencia</label>
            <input type="text" name="{{ $n('procedencia') }}" class="form-control @error($k('procedencia')) is-invalid @enderror"
                   value="{{ old($k('procedencia'), $h?->procedencia) }}" placeholder="Ej: Cochabamba">
            @error($k('procedencia'))<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Profesión / Ocupación</label>
            <input type="text" name="{{ $n('profesion') }}" class="form-control @error($k('profesion')) is-invalid @enderror"
                   value="{{ old($k('profesion'), $h?->profesion) }}" placeholder="Ej: Ingeniero, Comerciante">
            @error($k('profesion'))<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Teléfono / Celular</label>
            <input type="text" name="{{ $n('telefono') }}" class="form-control"
                   value="{{ old($k('telefono'), $h?->telefono) }}" placeholder="+591 7xxxxxxx">
        </div>
    </div>
</div>
<div class="form-group">
    <label>Observaciones</label>
    <textarea name="{{ $n('observaciones') }}" class="form-control" rows="2"
              placeholder="Alergias, preferencias especiales, etc.">{{ old($k('observaciones'), $h?->observaciones) }}</textarea>
</div>
</div>

@pushOnce('scripts')
<script>
// Calcula la edad al elegir la fecha de nacimiento (resta un año si aún no cumplió este año).
(function () {
    function calcular(fecha) {
        const edad = fecha.closest('.js-ficha').querySelector('.js-edad');
        if (!fecha.value) { edad.value = ''; return; }
        const [a, m, d] = fecha.value.split('-').map(Number);
        const hoy = new Date();
        let anios = hoy.getFullYear() - a;
        if (hoy.getMonth() + 1 < m || (hoy.getMonth() + 1 === m && hoy.getDate() < d)) anios--;
        edad.value = anios >= 0 ? anios : '';
    }

    document.addEventListener('input',  e => e.target.classList.contains('js-fecha-nac') && calcular(e.target));
    document.addEventListener('change', e => e.target.classList.contains('js-fecha-nac') && calcular(e.target));
    document.querySelectorAll('.js-fecha-nac').forEach(calcular);
})();
</script>
@endPushOnce
