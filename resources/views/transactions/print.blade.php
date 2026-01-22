<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Transaksi #{{ $transaction->transaction_code }}</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            margin: 0;
            padding: 10px;
            width: 58mm; /* Standard thermal paper width */
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
        }
        .header h2 {
            margin: 0;
            font-size: 16px;
        }
        .divider {
            border-top: 1px dashed #000;
            margin: 5px 0;
        }
        .item {
            display: flex;
            justify-content: space-between;
        }
        .item-name {
            width: 100%;
            margin-bottom: 2px;
        }
        .item-details {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
        }
        .total-section {
            margin-top: 10px;
        }
        .row {
            display: flex;
            justify-content: space-between;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 10px;
        }
        @media print {
            @page {
                margin: 0;
                size: auto;
            }
            body {
                padding: 0 5px;
            }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="header">
        <h2>MINIMARKET POS</h2>
        <p>Jl. Contoh No. 123<br>Jakarta, Indonesia</p>
        <p>{{ $transaction->created_at->format('d/m/Y H:i') }}</p>
        <p>No: {{ $transaction->transaction_code }}</p>
    </div>

    <div class="divider"></div>

    @foreach($transaction->items as $item)
    <div style="margin-bottom: 5px;">
        <div class="item-name">{{ $item->product->name }}</div>
        <div class="item-details">
            <span>{{ $item->quantity }} x {{ number_format($item->price, 0, ',', '.') }}</span>
            <span>{{ number_format($item->subtotal, 0, ',', '.') }}</span>
        </div>
    </div>
    @endforeach

    <div class="divider"></div>

    <div class="total-section">
        <div class="row">
            <strong>Total</strong>
            <strong>{{ number_format($transaction->total_amount, 0, ',', '.') }}</strong>
        </div>
        <div class="row">
            <span>Bayar ({{ ucfirst($transaction->payment_method) }})</span>
            <span>{{ number_format($transaction->pay_amount, 0, ',', '.') }}</span>
        </div>
        <div class="row">
            <span>Kembali</span>
            <span>{{ number_format($transaction->change_amount, 0, ',', '.') }}</span>
        </div>
    </div>

    <div class="divider"></div>

    <div class="footer">
        <p>Terima Kasih atas Kunjungan Anda<br>Barang yang sudah dibeli<br>tidak dapat ditukar/dikembalikan</p>
    </div>
</body>
</html>
