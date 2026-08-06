<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 0; }
        body {
            font-family: 'Courier New', monospace;
            font-size: {{ $paperWidth === '58mm' ? '9px' : '10px' }};
            width: {{ $paperWidth === '58mm' ? '48mm' : '72mm' }};
            margin: 0 auto;
            padding: 5px;
        }
        .center { text-align: center; }
        .line { border-top: 1px dashed #000; margin: 4px 0; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 1px 0; vertical-align: top; }
        .right { text-align: right; }
        .bold { font-weight: bold; }
        .item-name { font-size: {{ $paperWidth === '58mm' ? '8px' : '9px' }}; }
    </style>
</head>
<body>
    <div class="center bold">{{ $sale->branch->tenant->name }}</div>
    <div class="center">NIT: {{ $sale->branch->tenant->nit ?? 'C/F' }}</div>
    <div class="center">{{ $sale->branch->name }}</div>
    <div class="line"></div>
    <div>No. {{ $sale->sale_number }}</div>
    <div>{{ $sale->created_at->format('d/m/Y H:i') }}</div>
    <div>Cliente: {{ $sale->customer->name ?? 'CF' }}</div>
    <div class="line"></div>

    @foreach($sale->items as $item)
    <div class="item-name">{{ $item->product->name }}</div>
    <table>
        <tr>
            <td>{{ $item->quantity }} x {{ number_format($item->unit_price, 2) }}</td>
            <td class="right">{{ number_format($item->subtotal, 2) }}</td>
        </tr>
    </table>
    @endforeach

    <div class="line"></div>
    <table>
        <tr><td>Subtotal</td><td class="right">{{ number_format($sale->subtotal, 2) }}</td></tr>
        <tr><td>Descuento</td><td class="right">{{ number_format($sale->discount, 2) }}</td></tr>
        <tr><td>IVA</td><td class="right">{{ number_format($sale->tax, 2) }}</td></tr>
        <tr class="bold"><td>TOTAL</td><td class="right">Q{{ number_format($sale->total, 2) }}</td></tr>
    </table>
    <div class="line"></div>

    @foreach($sale->payments as $payment)
    <div>{{ $payment->paymentMethod->name }}: {{ number_format($payment->amount, 2) }}</div>
        @if($payment->amount_tendered)
        <div>Recibido: {{ number_format($payment->amount_tendered, 2) }}</div>
        <div>Cambio: {{ number_format($payment->amount_tendered - $payment->amount, 2) }}</div>
        @endif
    @endforeach

    <div class="line"></div>
    <div class="center">Gracias por su compra</div>
</body>
</html>