<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota #{{ $order->order_number }}</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
            color: #000;
            margin: 0;
            padding: 10px;
            width: 58mm;
            /* Standard narrow thermal printer width */
            background-color: #fff;
            line-height: 1.2;
        }

        @media print {
            body {
                width: 100%;
                margin: 0;
                padding: 0;
            }

            .no-print {
                display: none;
            }
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
            border-bottom: 1px dashed #000;
            padding-bottom: 8px;
        }

        .restaurant-name {
            font-weight: bold;
            font-size: 15px;
            margin-bottom: 2px;
        }

        .info {
            margin-bottom: 10px;
            font-size: 10px;
            line-height: 1.3;
        }

        .table-items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .table-items th {
            text-align: left;
            border-bottom: 1px dashed #000;
            padding: 5px 0;
            font-size: 10px;
        }

        .table-items td {
            padding: 4px 0;
            vertical-align: top;
        }

        .total-section {
            border-top: 1px dashed #000;
            padding-top: 8px;
            margin-bottom: 20px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            font-weight: bold;
            font-size: 13px;
        }

        .footer {
            text-align: center;
            font-size: 9px;
            margin-top: 20px;
            border-top: 1px dashed #000;
            padding-top: 5px;
        }

        .no-print-btn {
            background: #2563eb;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin-bottom: 20px;
            width: 100%;
            font-weight: bold;
            font-size: 14px;
        }

        /* Area tambahan agar total tidak terpotong saat disobek */
        .cutting-zone {
            margin-top: 40px;
            border-top: 1px dashed #000;
            text-align: center;
            font-size: 8px;
            padding-top: 5px;
            margin-bottom: 80px;
        }
    </style>
</head>

<body onload="window.print()">

    <div class="no-print">
        <button onclick="window.print()" class="no-print-btn">KLIK UNTUK PRINT ULANG</button>
    </div>

    <div class="header">
        <div class="restaurant-name">{{ $order->restaurant->name ?? 'RESTO KAMI' }}</div>
        <div>{{ $order->restaurant->address ?? 'Pusat Kota' }}</div>
        <div>Telp: {{ $order->restaurant->phone ?? '-' }}</div>
    </div>

    <div class="info">
        <div>Nota: #{{ $order->order_number }}</div>
        <div>Tgl : {{ $order->created_at->format('d/m/y H:i') }}</div>
        <div>Meja: {{ $order->table->table_number ?? 'TAKEAWAY' }}</div>
        <div>Pelanggan: {{ $order->customer_name }}</div>
    </div>

    <table class="table-items">
        <thead>
            <tr>
                <th>Menu</th>
                <th style="text-align: center;">Qty</th>
                <th style="text-align: right;">Harga</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
                <tr>
                    <td>{{ $item->menu->name }}</td>
                    <td style="text-align: center;">{{ $item->quantity }}</td>
                    <td style="text-align: right;">{{ number_format($item->price * $item->quantity, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-section">
        <div class="total-row">
            <span>TOTAL</span>
            <span>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
        </div>
    </div>

    <div class="footer">
        *** TERIMA KASIH ***<br>
        Silakan berkunjung kembali
    </div>

    <!-- Garis potong dan spasi tambahan -->
    <div class="cutting-zone">
        -------------------------------<br>
        GUNTING DI SINI / CUT HERE
    </div>

</body>

</html>