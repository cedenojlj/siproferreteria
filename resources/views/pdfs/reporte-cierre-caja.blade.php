<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Cierre de Caja</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #333; }
        .container { width: 100%; margin: 0 auto; }
        h1 { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
        .header, .footer { text-align: center; margin-bottom: 20px; }
        .content { margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .summary-table td:first-child { font-weight: bold; }
        .text-right { text-align: right; }
        .total { font-weight: bold; font-size: 1.1em; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Reporte de Cierre de Caja (Corte Z)</h2>
            <p><strong>Fecha y Hora del Cierre:</strong> {{ $cierre->fecha_cierre->format('d/m/Y H:i:s') }}</p>
            <p><strong>Generado por:</strong> {{ $cierre->user->name }}</p>
        </div>

        <div class="content">
            <h3>Información del Periodo</h3>
            <table class="summary-table">
                <tr>
                    <td>ID del Cierre</td>
                    <td>{{ $cierre->id }}</td>
                </tr>
                <tr>
                    <td>Periodo del Reporte</td>
                    <td>Desde: {{ $cierre->rango_inicio->format('d/m/Y H:i:s') }}<br>Hasta: {{ $cierre->rango_fin->format('d/m/Y H:i:s') }}</td>
                </tr>
                <tr>
                    <td>Número de Transacciones</td>
                    <td>{{ $cierre->numero_transacciones }}</td>
                </tr>
            </table>

            <h3 style="margin-top: 30px;">Resumen Financiero</h3>
            <table>
                <tr>
                    <td>Total Ventas Bruto</td>
                    <td class="text-right">${{ number_format($cierre->total_ventas_bruto, 2) }}</td>
                </tr>
                <tr>
                    <td>Total Devoluciones</td>
                    <td class="text-right">-${{ number_format($cierre->total_devoluciones, 2) }}</td>
                </tr>
                <tr class="total">
                    <td>Total Ventas Neto</td>
                    <td class="text-right">${{ number_format($cierre->total_ventas_neto, 2) }}</td>
                </tr>
                <tr>
                    <td>Total Impuestos</td>
                    <td class="text-right">${{ number_format($cierre->total_impuestos, 2) }}</td>
                </tr>
            </table>

            <h3 style="margin-top: 30px;">Desglose por Método de Pago</h3>
            <table>
                <thead>
                    <tr>
                        <th>Método de Pago</th>
                        <th>Total Recibido</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($cierre->totales_por_metodo as $metodo => $total)
                        <tr>
                            <td>{{ ucfirst($metodo) }}</td>
                            <td class="text-right">${{ number_format($total, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2">No se registraron pagos en este período.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="footer" style="margin-top: 40px;">
            <p>Fin del Reporte</p>
        </div>
    </div>
</body>
</html>
