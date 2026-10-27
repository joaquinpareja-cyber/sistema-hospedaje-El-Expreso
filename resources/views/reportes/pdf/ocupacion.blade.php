<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    @include('reportes.pdf._estilos')
</head>
<body>
    <h1>{{ $empresa }} — Reporte de Ocupación</h1>
    <p class="sub">Del {{ $desde->format('d/m/Y') }} al {{ $hasta->format('d/m/Y') }} · Generado el {{ now()->format('d/m/Y H:i') }}</p>

    <table class="resumen" style="margin-bottom:12px">
        <tr>
            <td>Habitaciones activas: <strong>{{ $totalHabitaciones }}</strong></td>
            <td>Reservas del período: <strong>{{ $totalReservas }}</strong></td>
            <td>Ocupación promedio: <strong>{{ number_format($promOcupacion, 1) }}%</strong></td>
        </tr>
    </table>

    <table>
        <thead>
            <tr><th>Fecha</th><th class="c">Ocupadas</th><th class="c">Disponibles</th><th class="r">% Ocupación</th></tr>
        </thead>
        <tbody>
            @foreach($ocupacionDiaria as $d)
            <tr>
                <td>{{ \Carbon\Carbon::parse($d['fecha'])->format('d/m/Y') }}</td>
                <td class="c">{{ $d['ocupadas'] }}</td>
                <td class="c">{{ $d['disponibles'] }}</td>
                <td class="r">{{ number_format($d['porcentaje'], 1) }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
