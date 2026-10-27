<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    @include('reportes.pdf._estilos')
</head>
<body>
    <h1>{{ $empresa }} — Reporte de Ingresos</h1>
    <p class="sub">Del {{ $desde->format('d/m/Y') }} al {{ $hasta->format('d/m/Y') }} · Generado el {{ now()->format('d/m/Y H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th>N° Comprobante</th><th>Fecha</th><th>Huésped</th><th>Habitación</th>
                <th class="r">Subtotal</th><th class="r">Descuento</th><th class="r">IVA</th><th class="r">Total</th><th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($facturas as $f)
            <tr class="{{ $f->estado === 'anulada' ? 'anulada' : '' }}">
                <td>{{ $f->numero }}</td>
                <td>{{ $f->fecha_emision->format('d/m/Y') }}</td>
                <td>{{ $f->huesped->nombre_completo ?? '—' }}</td>
                <td>Hab. {{ $f->reserva->habitacion->numero ?? '—' }}</td>
                <td class="r">{{ $moneda }} {{ number_format($f->subtotal, 2) }}</td>
                <td class="r">{{ $moneda }} {{ number_format($f->descuento, 2) }}</td>
                <td class="r">{{ $moneda }} {{ number_format($f->iva, 2) }}</td>
                <td class="r">{{ $moneda }} {{ number_format($f->total, 2) }}</td>
                <td>{{ ucfirst($f->estado) }}</td>
            </tr>
            @empty
            <tr><td colspan="9" class="c">Sin comprobantes en el período.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="tot">
                <td colspan="6" class="r">Totales (sin anulados):</td>
                <td class="r">{{ $moneda }} {{ number_format($totalIVA, 2) }}</td>
                <td class="r">{{ $moneda }} {{ number_format($totalIngresos, 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
