<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #222; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 4px 6px; text-align: left; border-bottom: 1px solid #ddd; }
        .totals { margin-top: 15px; width: 100%; }
        .totals td { border: none; padding: 2px 6px; }
        .totals .label { text-align: right; }
        .totals .final { font-weight: bold; font-size: 14px; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ $sale->branch->tenant->name }}</h2>
        <div>NIT: {{ $sale->branch->tenant->nit ?? 'C/F' }}</div>
        <div>{{ $sale->branch->name }} - {{ $sale->branch->address }}</div>
        <div>Venta No. {{ $sale->sale_number }} — {{ $sale->created_at->format('d/m/Y H:i') }}</div>
    </div>

    <div>
        <strong>Cliente:</strong> {{ $sale->customer->name ?? 'Consumidor Final' }}
        @if($sale->customer?->nit) — NIT: {{ $sale->customer->nit }} @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th>Cant.</th>
                <th>P. Unit.</th>
                <th>Desc.</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $item)
            <tr>
                <td>{{ $item->product->name }}</td>
                <td>{{ $item->quantity }}</td>
                <td>Q{{ number_format($item->unit_price, 2) }}</td>
                <td>{{ $item->discount_percentage }}%</td>
                <td>Q{{ number_format($item->subtotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr><td class="label">Subtotal:</td><td>Q{{ number_format($sale->subtotal, 2) }}</td></tr>
        <tr><td class="label">Descuento:</td><td>Q{{ number_format($sale->discount, 2) }}</td></tr>
        <tr><td class="label">IVA:</td><td>Q{{ number_format($sale->tax, 2) }}</td></tr>
        <tr class="final"><td class="label">Total:</td><td>Q{{ number_format($sale->total, 2) }}</td></tr>
    </table>

    <div>
        <strong>Pagos:</strong>
        @foreach($sale->payments as $payment)
            <div>{{ $payment->paymentMethod->name }}: Q{{ number_format($payment->amount, 2) }}
                @if($payment->amount_tendered)
                    (Recibido: Q{{ number_format($payment->amount_tendered, 2) }},
                    Cambio: Q{{ number_format($payment->amount_tendered - $payment->amount, 2) }})
                @endif
            </div>
        @endforeach
    </div>

    <div class="footer">Gracias por su compra — NexusPOS</div>
</body>
</html>