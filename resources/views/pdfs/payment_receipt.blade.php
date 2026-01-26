<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recibo de Pago</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; font-size: 12px; }
        .container { width: 100%; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 18px; }
        .header p { margin: 2px 0; }
        .content { margin-top: 25px; }
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .table th { background-color: #f2f2f2; }
        .text-right { text-align: right; }
        .footer { text-align: center; margin-top: 30px; font-size: 10px; color: #777; }
        .section-title {
            background-color: #f2f2f2;
            padding: 5px;
            font-weight: bold;
            margin-top: 15px;
            margin-bottom: 10px;
        }
        .info-table td { border: none; padding: 3px 8px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $company->name ?? 'Nombre de la Empresa' }}</h1>
            <p>{{ $company->address ?? 'Dirección no disponible' }}</p>
            <p>Tel: {{ $company->phone ?? 'Teléfono no disponible' }} | Email: {{ $company->email ?? 'Email no disponible' }}</p>
            <p>RIF: {{ $company->rif ?? 'RIF no disponible' }}</p>
            <h2>RECIBO DE PAGO</h2>
        </div>

        <table class="table info-table">
            <tr>
                <td><strong>Recibo N°:</strong></td>
                <td>{{ $payment->id }}</td>
                <td><strong>Fecha:</strong></td>
                <td>{{ $payment->created_at->format('d/m/Y H:i A') }}</td>
            </tr>
            <tr>
                <td><strong>Cliente:</strong></td>
                <td>{{ $payment->customer->name ?? 'N/A' }}</td>
                <td><strong>CI/RIF:</strong></td>
                <td>{{ $payment->customer->document ?? 'N/A' }}</td>
            </tr>
             <tr>
                <td><strong>Atendido por:</strong></td>
                <td colspan="3">{{ $payment->user->name ?? 'N/A' }}</td>
            </tr>
        </table>
        
        <div class="section-title">Detalles del Abono</div>

        <table class="table">
            <thead>
                <tr>
                    <th>Factura Afectada</th>
                    <th>Método de Pago</th>
                    <th>Referencia</th>
                    <th class="text-right">Monto Abonado (USD)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $payment->sale->invoice_number ?? 'N/A' }}</td>
                    <td>{{ $payment->payment_method }}</td>
                    <td>{{ $payment->reference ?? 'N/A' }}</td>
                    <td class="text-right">${{ number_format($payment->amount_usd, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <br>

        <table class="table" style="width: 50%; float: right;">
            <tr>
                <td><strong>Saldo Anterior:</strong></td>
                <td class="text-right">${{ number_format($saldoAnterior, 2) }}</td>
            </tr>
            <tr>
                <td><strong>Monto Abonado:</strong></td>
                <td class="text-right">${{ number_format($payment->amount_usd, 2) }}</td>
            </tr>
            <tr>
                <td><strong>Nuevo Saldo Pendiente:</strong></td>
                <td class="text-right"><strong>${{ number_format($payment->sale->pending_balance, 2) }}</strong></td>
            </tr>
        </table>

        <div style="clear: both;"></div>

        <div class="footer">
            <p>Gracias por su pago.</p>
            <p>Este es un recibo generado por el sistema y no requiere firma.</p>
        </div>
    </div>
</body>
</html>
