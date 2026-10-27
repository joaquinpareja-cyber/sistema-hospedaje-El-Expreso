<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    @include('reportes.pdf._estilos')
</head>
<body>
    <h1>{{ $empresa }} — Padrón de Huéspedes</h1>
    <p class="sub">Generado el {{ now()->format('d/m/Y H:i') }} · {{ $huespedes->count() }} huésped(es)</p>

    <table>
        <thead>
            <tr>
                <th>Nombre completo</th><th>Documento</th><th>Nacionalidad</th>
                <th>Procedencia</th><th>Teléfono</th><th class="c">Reservas</th>
            </tr>
        </thead>
        <tbody>
            @forelse($huespedes as $h)
            <tr>
                <td>{{ $h->nombre_completo }}</td>
                <td>{{ $h->tipo_documento }} {{ $h->num_documento }}</td>
                <td>{{ $h->nacionalidad ?? '—' }}</td>
                <td>{{ $h->procedencia ?? '—' }}</td>
                <td>{{ $h->telefono ?? '—' }}</td>
                <td class="c">{{ $h->total_reservas }}</td>
            </tr>
            @empty
            <tr><td colspan="6" class="c">Sin huéspedes registrados.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
