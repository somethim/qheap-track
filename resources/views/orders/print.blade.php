<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order {{ $order->order_number }} - Print</title>
    <style>
        @page {
            size: A5 portrait;
            margin: 10mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', monospace;
            font-size: 11px;
            line-height: 1.4;
            width: 80mm;
            margin: 0 auto;
            padding: 10px;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px dashed #000;
        }

        .header h1 {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .header p {
            font-size: 10px;
            margin: 2px 0;
        }

        .section {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px dashed #000;
        }

        .section-title {
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .info-line {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
            font-size: 10px;
        }

        .info-line strong {
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        th {
            font-weight: bold;
            font-size: 10px;
            text-align: left;
            padding: 5px 0;
            border-bottom: 1px solid #000;
        }

        td {
            font-size: 10px;
            padding: 5px 0;
        }

        .product-row {
            border-bottom: 1px dotted #ccc;
        }

        .product-name {
            font-weight: bold;
        }

        .product-details {
            display: flex;
            justify-content: space-between;
            font-size: 9px;
        }

        .total-section {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 2px solid #000;
        }

        .total-line {
            display: flex;
            justify-content: space-between;
            margin: 5px 0;
            font-weight: bold;
            font-size: 14px;
        }

        @media screen {
            body {
                transform: scale(1.5);
                transform-origin: top center;
                margin-bottom: 50vh;
            }
        }

        @media print {
            @page {
                size: A5 portrait;
                margin: 10mm;
            }
            
            body {
                width: 80mm;
                margin: 0 auto;
                transform: none;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>ORDER INVOICE</h1>
        <p>{{ $order->order_number }}</p>
        <p>{{ $order->created_at->format('d/m/Y g:i A') }}</p>
    </div>

    <div class="section">
        <div class="section-title">{{ $order->client_id ? 'Client' : 'Supplier' }}</div>
        @if($order->client_id && $order->client)
            <div class="info-line">
                <span>Name:</span>
                <span>{{ $order->client->name }}</span>
            </div>
            @if($order->client->contact_phone)
                <div class="info-line">
                    <span>Phone:</span>
                    <span>{{ $order->client->contact_phone }}</span>
                </div>
            @endif
        @elseif($order->supplier_id && $order->supplier)
            <div class="info-line">
                <span>Name:</span>
                <span>{{ $order->supplier->name }}</span>
            </div>
            @if($order->supplier->contact_phone)
                <div class="info-line">
                    <span>Phone:</span>
                    <span>{{ $order->supplier->contact_phone }}</span>
                </div>
            @endif
        @endif
    </div>

    <div class="section">
        <div class="section-title">Items</div>
        @foreach($orderProducts as $product)
            <div class="product-row">
                <div class="product-name">{{ $product['product_name'] }}</div>
                <div class="product-details">
                    <span>{{ $product['quantity'] }} x {{ $product['price'] }}</span>
                    <span>{{ $product['subtotal'] }}</span>
                </div>
            </div>
        @endforeach
    </div>

    <div class="total-section">
        <div class="total-line">
            <span>TOTAL:</span>
            <span>{{ $total }}</span>
        </div>
    </div>
</body>
</html>

