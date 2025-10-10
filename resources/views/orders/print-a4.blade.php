<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order {{ $order->order_number }} - Print</title>
    <style>
        @page {
            size: A4;
            margin: 20mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            max-width: 100%;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #333;
        }

        .header h1 {
            font-size: 24px;
            margin-bottom: 10px;
        }

        .order-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .info-section {
            padding: 15px;
            background-color: #f5f5f5;
            border-radius: 5px;
        }

        .info-section h2 {
            font-size: 16px;
            margin-bottom: 10px;
            color: #333;
        }

        .info-section p {
            font-size: 14px;
            line-height: 1.6;
            color: #666;
        }

        .info-section p strong {
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        thead {
            background-color: #333;
            color: white;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            font-weight: 600;
            font-size: 14px;
        }

        td {
            font-size: 13px;
        }

        tbody tr:hover {
            background-color: #f9f9f9;
        }

        .total-section {
            text-align: right;
            padding: 20px;
            background-color: #f5f5f5;
            border-radius: 5px;
            margin-top: 20px;
        }

        .total-section h3 {
            font-size: 18px;
            color: #333;
        }

        .total-section .amount {
            font-size: 24px;
            font-weight: bold;
            color: #000;
            margin-top: 5px;
        }

        @media screen {
            body {
                transform: scale(1.3);
                transform-origin: top center;
                margin-bottom: 30vh;
            }
        }

        @media print {
            body {
                transform: none;
            }

            .info-section {
                break-inside: avoid;
            }
            
            table {
                page-break-inside: auto;
            }
            
            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Order Invoice</h1>
        <p><strong>Order Number:</strong> {{ $order->order_number }}</p>
        <p><strong>Date:</strong> {{ $order->created_at->format('F d, Y g:i A') }}</p>
    </div>

    <div class="order-info">
        <div class="info-section">
            <h2>{{ $order->client_id ? 'Client' : 'Supplier' }} Information</h2>
            @if($order->client_id && $order->client)
                <p><strong>Name:</strong> {{ $order->client->name }}</p>
                @if($order->client->contact_email)
                    <p><strong>Email:</strong> {{ $order->client->contact_email }}</p>
                @endif
                @if($order->client->contact_phone)
                    <p><strong>Phone:</strong> {{ $order->client->contact_phone }}</p>
                @endif
                @if($order->client->address)
                    <p><strong>Address:</strong> {{ $order->client->address }}</p>
                @endif
            @elseif($order->supplier_id && $order->supplier)
                <p><strong>Name:</strong> {{ $order->supplier->name }}</p>
                @if($order->supplier->contact_email)
                    <p><strong>Email:</strong> {{ $order->supplier->contact_email }}</p>
                @endif
                @if($order->supplier->contact_phone)
                    <p><strong>Phone:</strong> {{ $order->supplier->contact_phone }}</p>
                @endif
                @if($order->supplier->address)
                    <p><strong>Address:</strong> {{ $order->supplier->address }}</p>
                @endif
            @endif
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Product Name</th>
                <th>SKU</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orderProducts as $product)
                <tr>
                    <td>{{ $product['product_name'] }}</td>
                    <td>{{ $product['product_sku'] }}</td>
                    <td>{{ $product['quantity'] }}</td>
                    <td>{{ $product['price'] }}</td>
                    <td>{{ $product['subtotal'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-section">
        <h3>Total Amount</h3>
        <div class="amount">{{ $total }}</div>
    </div>
</body>
</html>

