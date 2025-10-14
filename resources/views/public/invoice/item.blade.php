<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $order->order_number }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: #333;
            font-size: 12px;
            margin: 0;
            padding: 0;
            background: #fff;
        }

        .container {
            width: 90%;
            margin: 30px auto;
        }

        h1,
        h2,
        h3 {
            margin: 0;
        }

        .header {
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .header .title {
            font-size: 20px;
            font-weight: bold;
        }

        .header .date {
            text-align: right;
            font-size: 12px;
            color: #666;
        }

        .section {
            margin-bottom: 25px;
        }

        .section-title {
            font-weight: bold;
            margin-bottom: 8px;
            font-size: 14px;
            color: #000;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 6px;
        }

        th {
            background: #f5f5f5;
            text-align: left;
        }

        .totals {
            margin-top: 10px;
            text-align: right;
        }

        .totals td {
            border: none;
            padding: 4px 6px;
        }

        .totals .total-row td {
            font-weight: bold;
            border-top: 2px solid #000;
            padding-top: 6px;
        }

        .footer {
            border-top: 1px solid #aaa;
            padding-top: 10px;
            font-size: 11px;
            color: #666;
            text-align: center;
        }

        .text-right {
            text-align: right;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <table width="100%">
                <tr>
                    <td>
                        <div class="title">Xander Billiard</div>
                        <div>Jl. Billiard No. 88, South Jakarta</div>
                        <div>Email: support@xanderbilliard.com</div>
                        <div>WhatsApp: +62 812 3456 7890</div>
                    </td>
                    <td class="date text-right">
                        <strong>Invoice:</strong> #{{ $order->order_number }}<br>
                        <strong>Date:</strong> {{ $order->created_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }}
                    </td>
                </tr>
            </table>
        </div>

        <!-- Customer Info -->
        <div class="section">
            <div class="section-title">Customer Information</div>
            <table>
                <tr>
                    <th>Name</th>
                    <td>{{ $user->name ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td>{{ $user->email ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Phone</th>
                    <td>{{ $user->phone ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Address</th>
                    <td>{{ $order->address ?? '-' }}</td>
                </tr>
            </table>
        </div>

        <!-- Product Details -->
        <div class="section">
            <div class="section-title">Order Details</div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 30px;">#</th>
                        <th>Product</th>
                        <th style="width: 60px;">Qty</th>
                        <th style="width: 90px;" class="text-right">Price</th>
                        <th style="width: 90px;" class="text-right">Discount</th>
                        <th style="width: 100px;" class="text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $subtotal = 0;
                        $discountTotal = 0;
                        $tax = $order->products->first()->pivot->tax ?? 0;
                        $shipping = $order->products->first()->pivot->shipping ?? 0;
                    @endphp
                    @foreach($order->products as $i => $product)
                        @php
                            $itemSubtotal = ($product->pivot->price * $product->pivot->quantity) - $product->pivot->discount;
                            $subtotal += $itemSubtotal;
                            $discountTotal += $product->pivot->discount;
                        @endphp
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $product->name }}</td>
                            <td class="text-right">{{ $product->pivot->quantity }}</td>
                            <td class="text-right">Rp {{ number_format($product->pivot->price, 0, ',', '.') }}</td>
                            <td class="text-right">Rp {{ number_format($product->pivot->discount, 0, ',', '.') }}</td>
                            <td class="text-right">Rp {{ number_format($itemSubtotal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Payment Info -->
        <div class="section">
            <div class="section-title">Payment Information</div>
            <table>
                <tr>
                    <th>Payment Method</th>
                    <td>{{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</td>
                </tr>
                <tr>
                    <th>Transaction ID</th>
                    <td>{{ $order->transaction_id ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td>{{ ucfirst($order->payment_status) }}</td>
                </tr>
            </table>

            <!-- Totals -->
            <table class="totals" width="100%">
                <tr>
                    <td>Subtotal:</td>
                    <td class="text-right">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Tax:</td>
                    <td class="text-right">Rp {{ number_format($tax, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Discount:</td>
                    <td class="text-right">Rp {{ number_format($discountTotal, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Shipping:</td>
                    <td class="text-right">Rp {{ number_format($shipping, 0, ',', '.') }}</td>
                </tr>
                <tr class="total-row">
                    <td>Grand Total:</td>
                    <td class="text-right">
                        Rp {{ number_format($subtotal + $tax + $shipping - $discountTotal, 0, ',', '.') }}
                    </td>
                </tr>
            </table>
        </div>

        <!-- Footer -->
        <div class="footer">
            Thank you for shopping with Xander Billiard!<br>
            For inquiries, contact <a href="mailto:support@xanderbilliard.com">support@xanderbilliard.com</a><br>
            <em>This is a computer-generated invoice and does not require a signature.</em>
        </div>
    </div>
</body>
</html>
