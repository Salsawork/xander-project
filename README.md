<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[WebReinvent](https://webreinvent.com/)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Jump24](https://jump24.co.uk)**
- **[Redberry](https://redberry.international/laravel/)**
- **[Active Logic](https://activelogic.com)**
- **[byte5](https://byte5.de)**
- **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

# Xander Billiard

Aplikasi e-commerce untuk Xander Billiard dengan fitur lengkap termasuk integrasi payment gateway Midtrans.

## Integrasi Midtrans Payment Gateway

Berikut adalah panduan lengkap untuk mengintegrasikan Midtrans Payment Gateway ke aplikasi Xander Billiard.

### 1. Konfigurasi Environment

Pertama, tambahkan konfigurasi Midtrans di file `.env`:

```
MIDTRANS_SERVER_KEY=SB-Mid-server-XXXXXXXXXXXXXXXX
MIDTRANS_CLIENT_KEY=SB-Mid-client-XXXXXXXXXXXXXXXX
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_SNAP_URL=https://app.sandbox.midtrans.com/snap/snap.js
```

Keterangan:
- `MIDTRANS_SERVER_KEY`: Server key dari dashboard Midtrans
- `MIDTRANS_CLIENT_KEY`: Client key dari dashboard Midtrans
- `MIDTRANS_IS_PRODUCTION`: Set `false` untuk sandbox (testing) dan `true` untuk production
- `MIDTRANS_SNAP_URL`: URL untuk Snap.js (ganti dengan URL production jika sudah live)

### 2. Menambahkan Konfigurasi di Laravel

Buat file konfigurasi baru di `config/midtrans.php`:

```php
<?php

return [
    'server_key' => env('MIDTRANS_SERVER_KEY', ''),
    'client_key' => env('MIDTRANS_CLIENT_KEY', ''),
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
    'snap_url' => env('MIDTRANS_SNAP_URL', 'https://app.sandbox.midtrans.com/snap/snap.js'),
];
```

### 3. Instalasi Library Midtrans

Install library Midtrans PHP menggunakan Composer:

```bash
composer require midtrans/midtrans-php
```

### 4. Menambahkan Field Snap Token di Tabel Orders

Buat migration untuk menambahkan field `snap_token` di tabel orders:

```bash
php artisan make:migration add_snap_token_to_orders_table --table=orders
```

Isi file migration:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('snap_token')->nullable()->after('payment_method');
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('snap_token');
        });
    }
};
```

Jalankan migration:

```bash
php artisan migrate
```

### 5. Implementasi di Controller

Berikut adalah contoh implementasi di OrderController:

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use Midtrans\Config;
use Midtrans\Snap;

class OrderController extends Controller
{
    public function __construct()
    {
        // Set konfigurasi Midtrans
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    public function store(Request $request)
    {
        // Validasi dan simpan order ke database
        $order = new Order();
        $order->id = 'XB-' . time(); // Generate ID unik
        $order->user_id = auth()->id();
        $order->total = $request->total;
        $order->payment_status = 'pending';
        $order->delivery_status = 'pending';
        $order->payment_method = 'midtrans';
        $order->save();

        // Siapkan parameter untuk Midtrans
        $params = [
            'transaction_details' => [
                'order_id' => $order->id,
                'gross_amount' => (int)$order->total,
            ],
            'customer_details' => [
                'firstname' => $request->firstname,
                'lastname' => $request->lastname,
                'email' => $request->email,
                'phone' => $request->phone,
            ],
            'item_details' => $this->getItemDetails($order),
            'enabled_payments' => [
                'credit_card', 'gopay', 'shopeepay', 'bca_va', 'bni_va', 'bri_va'
            ],
        ];

        // Buat Snap Token
        $snapToken = Snap::getSnapToken($params);
        
        // Simpan snap token ke database
        $order->snap_token = $snapToken;
        $order->save();

        return response()->json([
            'status' => 'success',
            'snap_token' => $snapToken,
            'order_id' => $order->id,
        ]);
    }
}
```

### 6. Implementasi di Frontend

Tambahkan script Midtrans di view:

```html
<script src="{{ config('midtrans.snap_url') }}" data-client-key="{{ config('midtrans.client_key') }}"></script>

<script>
    document.getElementById('pay-button').addEventListener('click', function() {
        // Panggil endpoint untuk membuat order dan mendapatkan snap token
        fetch('/checkout', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                // Data order
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Tampilkan popup Snap
                window.snap.pay(data.snap_token, {
                    onSuccess: function(result) {
                        window.location.href = '/checkout/success?order_id=' + data.order_id;
                    },
                    onPending: function(result) {
                        window.location.href = '/checkout/pending?order_id=' + data.order_id;
                    },
                    onError: function(result) {
                        window.location.href = '/checkout/error?order_id=' + data.order_id;
                    },
                    onClose: function() {
                        alert('Anda menutup popup tanpa menyelesaikan pembayaran');
                    }
                });
            }
        });
    });
</script>
```

### 7. Setting Callback URL

Midtrans akan mengirimkan notifikasi ke endpoint yang kita tentukan ketika status pembayaran berubah. Berikut cara mengatur callback URL:

1. Buat route untuk menerima notifikasi:

```php
Route::post('/midtrans/notification', [OrderController::class, 'notification'])->name('midtrans.notification');
```

2. Implementasi method notification di OrderController:

```php
public function notification(Request $request)
{
    $notif = json_decode($request->getContent(), true);
    
    $orderId = $notif['order_id'];
    $transactionStatus = $notif['transaction_status'];
    $fraudStatus = $notif['fraud_status'] ?? null;
    
    $order = Order::find($orderId);
    
    if (!$order) {
        return response()->json(['status' => 'error', 'message' => 'Order not found'], 404);
    }
    
    // Mapping status dari Midtrans ke format database
    $statusMapping = [
        'settlement' => 'paid',
        'capture' => 'paid',
        'pending' => 'pending',
        'deny' => 'failed',
        'expire' => 'failed',
        'cancel' => 'failed'
    ];
    
    // Update status pembayaran berdasarkan notifikasi
    if ($transactionStatus == 'capture') {
        if ($fraudStatus == 'challenge') {
            $order->payment_status = 'processing';
        } else if ($fraudStatus == 'accept') {
            $order->payment_status = 'paid';
        }
    } else {
        $order->payment_status = $statusMapping[$transactionStatus] ?? 'pending';
    }
    
    $order->save();
    
    return response()->json(['status' => 'success']);
}
```

3. Di dashboard Midtrans, atur Payment Notification URL:
   - Login ke dashboard Midtrans
   - Pilih "Settings" > "Configuration"
   - Pada bagian "Payment Notification URL", masukkan URL: `https://yourdomain.com/midtrans/notification`
   - Klik "Update"

### 8. Testing dengan Ngrok

Untuk testing callback URL di lingkungan development, gunakan Ngrok:

1. Download dan install Ngrok dari [ngrok.com](https://ngrok.com)

2. Jalankan server Laravel:
```bash
php artisan serve
```

3. Di terminal terpisah, jalankan Ngrok:
```bash
ngrok http 8000
```

4. Ngrok akan memberikan URL publik (misalnya `https://abcd1234.ngrok.io`)

5. Gunakan URL ini sebagai base URL untuk callback Midtrans:
   - Payment Notification URL: `https://abcd1234.ngrok.io/midtrans/notification`

6. Update juga URL di file `.env` jika perlu:
```
APP_URL=https://abcd1234.ngrok.io
```

### 9. Kartu Kredit untuk Testing

Gunakan kartu kredit berikut untuk testing di sandbox Midtrans:

- Nomor Kartu: 4811 1111 1111 1114
- CVV: 123
- Tanggal Expired: Gunakan tanggal yang masih valid
- OTP/3DS: 112233

### 10. Menangani Redirect Setelah Pembayaran

Buat route dan method untuk menangani redirect setelah pembayaran:

```php
// Routes
Route::get('/checkout/success', [OrderController::class, 'success'])->name('checkout.success');
Route::get('/checkout/pending', [OrderController::class, 'pending'])->name('checkout.pending');
Route::get('/checkout/error', [OrderController::class, 'error'])->name('checkout.error');

// Controller methods
public function success(Request $request)
{
    $order = Order::find($request->order_id);
    return view('checkout.success', compact('order'));
}

public function pending(Request $request)
{
    $order = Order::find($request->order_id);
    return view('checkout.pending', compact('order'));
}

public function error(Request $request)
{
    $order = Order::find($request->order_id);
    return view('checkout.error', compact('order'));
}
```

### Troubleshooting

1. **Callback URL tidak berfungsi**
   - Pastikan URL dapat diakses dari internet (gunakan Ngrok)
   - Periksa log Laravel untuk melihat apakah request diterima
   - Pastikan CSRF protection dinonaktifkan untuk route callback

2. **Pembayaran selalu gagal**
   - Periksa apakah Anda menggunakan data kartu kredit testing yang benar
   - Pastikan gross_amount adalah integer, bukan float atau string dengan format

3. **Snap token tidak terbuat**
   - Periksa apakah server key dan client key sudah benar
   - Pastikan format parameter yang dikirim ke Midtrans sudah sesuai

4. **Popup Snap tidak muncul**
   - Pastikan script Snap.js sudah dimuat dengan benar
   - Periksa console browser untuk melihat error

## Referensi

- [Dokumentasi Midtrans](https://docs.midtrans.com)
- [Midtrans PHP Library](https://github.com/Midtrans/midtrans-php)
- [Midtrans Snap API](https://snap-docs.midtrans.com)
