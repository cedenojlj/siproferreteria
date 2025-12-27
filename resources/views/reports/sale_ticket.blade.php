<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket de Venta #{{ $sale->id }}</title>
    <style>
        body {
            font-family: 'Consolas', 'Courier New', monospace;
            font-size: 10px;
            width: 300px; /* Ancho típico de un ticket térmico */
            margin: 0 auto;
            padding: 5mm;
        }
        pre {
            white-space: pre-wrap; /* Permite que el texto se ajuste */
            word-wrap: break-word;
            margin: 0;
            padding: 0;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <pre>{!! $formatted_ticket !!}</pre>
</body>
</html>