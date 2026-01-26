<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recibo de Pago</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 14px;
            color: #333;
        }
        .receipt-container {
            width: 300px;
            margin: auto;
            border: 1px solid #eee;
            padding: 15px;
            box-shadow: 0 0 5px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
        }
        .header p {
            margin: 2px 0;
            font-size: 12px;
        }
        .item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            padding-bottom: 8px;
            border-bottom: 1px dashed #ccc;
        }
        .item span:first-child {
            font-weight: bold;
        }
        .total {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
            font-size: 16px;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            margin-top: 25px;
            font-size: 12px;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="header">
            <h1>{{ $company->name ?? 'Nombre de la Empresa' }}</h1>
            <p>{{ $company->address ?? 'Dirección de la Empresa' }}</p>
            <p>Tel: {{ $company->phone ?? 'N/A' }}</p>
            <h2>Recibo de Abono</h2>
        </div>

        <div class="item">
            <span>Fecha:</span>
            <span>{{ $payment->created_at->format('d/m/Y H:i') }}</span>
        </div>
        <div class="item">
            <span>Recibo #:</span>
            <span>{{ $payment->id }}</span>
        </div>
        <div class="item">
            <span>Cliente:</span>
            <span>{{ $payment->customer->name ?? 'N/A' }}</span>
        </div>
         <div class="item">
            <span>Factura Ref:</span>
            <span>{{ $payment->sale->invoice_number ?? 'N/A' }}</span>
        </div>
        <div class="item">
            <span>Método:</span>
            <span>{{ $payment->payment_method }}</span>
        </div>
        @if($payment->reference)
        <div class="item">
            <span>Referencia:</span>
            <span>{{ $payment->reference }}</span>
        </div>
        @endif
        <div class="item">
            <span>Atendido por:</span>
            <span>{{ $payment->user->name ?? 'N/A' }}</span>
        </div>

        <div class="total">
            <span>MONTO ABONADO:</span>
            <span>${{ number_format($payment->amount_usd, 2) }}</span>
        </div>

         <div class="item" style="margin-top: 20px; border-top: 1px solid #ccc; padding-top: 10px;">
            <span>Saldo Anterior:</span>
            <span>${{ number_format($saldoAnterior, 2) }}</span>
        </div>
        <div class="item">
            <span>Saldo Actual:</span>
            <span>${{ number_format($payment->sale->pending_balance, 2) }}</span>
        </div>


        <div class="footer">
            <p>Gracias por su pago.</p>
        </div>
    </div>
</body>
</html>
