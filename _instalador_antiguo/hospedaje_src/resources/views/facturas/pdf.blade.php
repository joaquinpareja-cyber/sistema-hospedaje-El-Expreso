<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ strtoupper($factura->tipo_comprobante) }}: {{ $factura->numero }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; margin: 20px; }
        .header { display: flex; justify-content: space-between; margin-bottom: 20px; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
        .title { text-align: center; margin: 15px 0; }
        .title h2 { color: #007bff; margin: 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th { background: #007bff; color: white; padding: 6px 8px; text-align: left; }
        td { padding: 5px 8px; border-bottom: 1px solid #eee; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .totales td { font-weight: bold; }
        .totales .total { font-size: 14px; background: #f8f9fa; }
        .footer { margin-top: 20px; text-align: center; color: #888; font-size: 10px; }
    </style>
</head>
<body>

<div class="header">
    <div>
        <strong style="font-size:16px">{{ config('app.name') }}</strong><br>
        Sistema de Gestión Hotelera<br>
        <small>sistema@hospedaje.com</small>
    </div>
    <div style="text-align:right">
        <strong style="font-size:18px; color:#007bff">{{ strtoupper($factura->tipo_comprobante) }}</strong><br>
        <strong style="font-size:14px">{{ $factura->numero }}</strong><br>
        Fecha: {{ $factura->fecha_emision->format('d/m/Y') }}
    </div>
</div>

<table>
    <tr>
        <td width="50%"><strong>Cliente:</strong> {{ $factura->huesped->nombre_completo }}</td>
        <td><strong>Documento:</strong> {{ $factura->huesped->tipo_documento }}: {{ $factura->huesped->num_documento }}</td>
    </tr>
    @if($factura->razon_social)
    <tr>
        <td><strong>Empresa:</strong> {{ $factura->razon_social }}</td>
        <td><strong>RUC:</strong> {{ $factura->ruc_cliente }}</td>
    </tr>
    @endif
    <tr>
        <td><strong>Reserva:</strong> {{ $factura->reserva->codigo }}</td>
        <td><strong>Habitación:</strong> {{ $factura->reserva->habitacion->numero }} — {{ $factura->reserva->habitacion->tipoHabitacion->nombre }}</td>
    </tr>
    <tr>
        <td><strong>Check-in:</strong> {{ $factura->reserva->fecha_entrada->format('d/m/Y') }}</td>
        <td><strong>Check-out:</strong> {{ $factura->reserva->fecha_salida->format('d/m/Y') }}</td>
    </tr>
</table>

<table>
    <thead>
        <tr><th>Descripción</th><th class="text-right">P. Unit.</th><th class="text-center">Cant.</th><th class="text-right">Subtotal</th></tr>
    </thead>
    <tbody>
        <tr>
            <td>Alojamiento ({{ $factura->reserva->num_noches }} noches)</td>
            <td class="text-right">S/ {{ number_format($factura->reserva->precio_noche, 2) }}</td>
            <td class="text-center">{{ $factura->reserva->num_noches }}</td>
            <td class="text-right">S/ {{ number_format($factura->reserva->subtotal, 2) }}</td>
        </tr>
        @foreach($factura->reserva->cargosAdicionales as $c)
        <tr>
            <td>{{ $c->concepto }}</td>
            <td class="text-right">S/ {{ number_format($c->precio_unitario, 2) }}</td>
            <td class="text-center">{{ $c->cantidad }}</td>
            <td class="text-right">S/ {{ number_format($c->subtotal, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot class="totales">
        <tr><td colspan="3" class="text-right">Subtotal:</td><td class="text-right">S/ {{ number_format($factura->subtotal, 2) }}</td></tr>
        @if($factura->descuento > 0)
        <tr><td colspan="3" class="text-right">Descuento:</td><td class="text-right">- S/ {{ number_format($factura->descuento, 2) }}</td></tr>
        @endif
        @if($factura->igv > 0)
        <tr><td colspan="3" class="text-right">IGV (18%):</td><td class="text-right">S/ {{ number_format($factura->igv, 2) }}</td></tr>
        @endif
        <tr class="total"><td colspan="3" class="text-right" style="font-size:14px">TOTAL A PAGAR:</td>
            <td class="text-right" style="font-size:14px;color:#007bff">S/ {{ number_format($factura->total, 2) }}</td></tr>
    </tfoot>
</table>

@if($factura->pagos->count() > 0)
<strong>Pagos Recibidos:</strong>
<table>
    <thead><tr><th>Fecha</th><th>Método</th><th>Referencia</th><th class="text-right">Monto</th></tr></thead>
    <tbody>
        @foreach($factura->pagos as $p)
        <tr>
            <td>{{ $p->fecha_pago->format('d/m/Y') }}</td>
            <td>{{ ucfirst(str_replace('_',' ',$p->metodo_pago)) }}</td>
            <td>{{ $p->referencia ?? '—' }}</td>
            <td class="text-right">S/ {{ number_format($p->monto, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

<div class="footer">
    <p>Documento generado el {{ now()->format('d/m/Y H:i:s') }} — {{ config('app.name') }}</p>
    <p>Gracias por su preferencia</p>
</div>
</body>
</html>
