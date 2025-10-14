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

        table,
        th,
        td {
            border: 1px solid #ccc;
        }

        th {
            background: #f5f5f5;
            text-align: left;
            padding: 6px;
        }

        td {
            padding: 6px;
        }

        .totals {
            margin-top: 10px;
            text-align: right;
        }

        .totals td {
            border: none;
            padding: 4px 6px;
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

        .text-center {
            text-align: center;
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
                    <td>{{ $order->products->first()->pivot->address ?? '-' }}</td>
                </tr>
            </table>
        </div>

        <!-- Sparring Details -->
        <div class="section">
            <div class="section-title">Sparring Session Details</div>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Athlete</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Price</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sparrings as $index => $sparring)
                    <tr>
                        <td>{{ (int)$index + 1 }}</td>
                        <td>{{ $sparring->athlete->name ?? '-' }}</td>
                        <td>{{ \Carbon\Carbon::parse($sparring->schedule->date)->format('d/m/Y') }}</td>
                        <td>{{ \Carbon\Carbon::parse($sparring->schedule->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($sparring->schedule->end_time)->format('H:i') }}</td>
                        <td>Rp {{ number_format($sparring->price, 0, ',', '.') }}</td>
                        <td>{{ $sparring->schedule->is_booked ? 'Booked' : '-' }}</td>
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
                    <td>{{ str_replace('_', ' ', $order->payment_method) }}</td>
                </tr>
                <tr>
                    <th>Transaction ID</th>
                    <td>{{ $order->transaction_id ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td>{{ ucfirst($order->payment_status ?? 'Paid') }}</td>
                </tr>
            </table>

            <!-- Total -->
            <table class="totals" width="100%">
                <tr>
                    <td><strong>Total:</strong></td>
                    <td class="text-right">
                        Rp {{ number_format($sparrings->sum('price'), 0, ',', '.') }}
                    </td>
                </tr>
            </table>
        </div>

        <!-- Footer -->
        <div class="footer">
            Thank you for booking with Xander Billiard!<br>
            For inquiries, contact <a href="mailto:support@xanderbilliard.com">support@xanderbilliard.com</a><br>
            <em>This is a computer-generated invoice and does not require a signature.</em>
        </div>
    </div>
</body>

</html>