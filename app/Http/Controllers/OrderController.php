<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderSparring;
use App\Models\SparringSchedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
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

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Logging untuk debugging
        Log::info('Checkout page accessed');
        Log::info('Request items', ['items' => $request->input('items', [])]);
        Log::info('Request price', ['price' => $request->input('price', [])]);

        // Ambil data dari cookie
        $allCarts = [];
        $carts = [];
        $sparrings = [];
        $venue = null;
        $total = 0;
        $tax = 0;
        $shipping = 30000; // Default shipping cost

        // Ambil semua data cart dari cookie
        if (Cookie::has('cart')) {
            $cartData = Cookie::get('cart');
            Log::info('Cart data from Cookie facade', ['data' => $cartData]);

            if ($cartData) {
                $allCarts = is_array($cartData) ? $cartData : json_decode($cartData, true) ?? [];
                Log::info('All decoded cart data', ['allCarts' => $allCarts]);

                // Jika ada items yang dipilih, filter cart berdasarkan items
                $selectedItems = $request->input('items', []);

                if (!empty($selectedItems)) {
                    // Konversi selectedItems ke integer untuk perbandingan yang benar
                    $selectedItems = array_map('intval', $selectedItems);

                    // Filter cart berdasarkan items yang dipilih
                    $carts = array_filter($allCarts, function($cart) use ($selectedItems) {
                        return in_array((int)$cart['id'], $selectedItems);
                    });

                    // Reset array keys
                    $carts = array_values($carts);

                    Log::info('Selected cart items', ['carts' => $carts]);
                } else {
                    // Jika tidak ada items yang dipilih, tampilkan semua
                    $carts = $allCarts;
                }

                // Hitung total
                foreach ($carts as $cart) {
                    if (isset($cart['price'])) {
                        $total += (int)$cart['price'];
                    }
                }
            }
        }

        // Ambil data sparring dari cookie jika ada
        if (Cookie::has('sparring')) {
            $sparringData = Cookie::get('sparring');
            $sparrings = is_array($sparringData) ? $sparringData : json_decode($sparringData, true) ?? [];

            foreach ($sparrings as $sparring) {
                if (isset($sparring['price'])) {
                    $total += $sparring['price'];
                }
            }
        }

        // Ambil data venue dari cookie jika ada
        if (Cookie::has('venue')) {
            $venueData = Cookie::get('venue');
            $venue = is_array($venueData) ? $venueData : json_decode($venueData, true);

            if ($venue && isset($venue['price'])) {
                $total += $venue['price'];
            }
        }

        // Hitung pajak (10% dari total)
        $tax = $total * 0.1;

        // Hitung grand total
        $grandTotal = $total + $shipping + $tax;

        // Tambahkan client key Midtrans untuk frontend
        $clientKey = config('midtrans.client_key');

        return view('public.checkout.checkout', compact('carts', 'sparrings', 'venue', 'total', 'shipping', 'tax', 'grandTotal', 'clientKey'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validasi data checkout
        $validatedData = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'zip' => 'required|string|max:20',
            'shipping_method' => 'required|in:express,standard',
            'note' => 'nullable|string',
        ]);

        // Ambil data dari cookie
        $carts = [];
        $items = [];
        $prices = [];
        $total = 0;
        $tax = 0;
        $shipping = 30000; // Default shipping cost

        // Ambil data cart dari cookie
        if (Cookie::has('cart')) {
            $cartData = Cookie::get('cart');
            $allCarts = is_array($cartData) ? $cartData : json_decode($cartData, true) ?? [];

            // Jika ada items yang dipilih, filter cart berdasarkan items
            $selectedItems = $request->input('items', []);

            if (!empty($selectedItems)) {
                // Konversi selectedItems ke integer untuk perbandingan yang benar
                $selectedItems = array_map('intval', $selectedItems);

                // Filter cart berdasarkan items yang dipilih
                $carts = array_filter($allCarts, function($cart) use ($selectedItems) {
                    return in_array((int)$cart['id'], $selectedItems);
                });

                // Reset array keys
                $carts = array_values($carts);
            } else {
                // Jika tidak ada items yang dipilih, gunakan semua
                $carts = $allCarts;
            }

            // Hitung total
            foreach ($carts as $cart) {
                $items[] = $cart['id'];
                $prices[] = $cart['price'];
                $total += (int)$cart['price'];
            }
        }

        // Jika tidak ada item, kembalikan error
        if (empty($items)) {
            return back()->with('error', 'No products in cart');
        }

        // Hitung pajak (10% dari total)
        $tax = $total * 0.1;

        // Hitung grand total
        $grandTotal = $total + $shipping + $tax;

        // Generate order ID
        $orderId = 'XB-' . time();

        // Simpan order ke database
        try {
            $order = DB::transaction(function () use ($request, $validatedData, $items, $prices, $carts, $total, $tax, $shipping, $grandTotal, $orderId) {
                $order = new Order();
                $order->id = $orderId;
                // Cek apakah user sudah login
                if ($request->user()) {
                    $order->user_id = $request->user()->id;
                } else {
                    // Jika belum login, set user_id ke null atau guest
                    $order->user_id = null; // Pastikan kolom user_id di database bisa null
                }
                $order->payment_status = 'pending';
                $order->delivery_status = 'pending';
                $order->payment_method = 'midtrans'; // Karena menggunakan Midtrans
                $order->total = $grandTotal;
                $order->save();

                // Simpan order items
                foreach ($carts as $index => $cart) {
                    $orderItem = new OrderItem();
                    $orderItem->order_id = $order->id;
                    $orderItem->product_id = $cart['id'];
                    $orderItem->price = $cart['price'];
                    $orderItem->quantity = 1;
                    $orderItem->subtotal = $cart['price'];
                    $orderItem->discount = 0;
                    $orderItem->save();
                }

                // Simpan sparring jika ada
                $sparrings = [];
                if (Cookie::has('sparring')) {
                    $sparringData = Cookie::get('sparring');
                    $sparrings = is_array($sparringData) ? $sparringData : json_decode($sparringData, true) ?? [];

                    foreach ($sparrings as $sparring) {
                        $orderSparring = new OrderSparring();
                        $orderSparring->order_id = $order->id;
                        $orderSparring->athlete_id = $sparring['athlete_id'];
                        $orderSparring->schedule_id = $sparring['schedule_id'];
                        $orderSparring->price = $sparring['price'];
                        $orderSparring->save();

                        // Update schedule menjadi booked
                        $schedule = SparringSchedule::find($sparring['schedule_id']);
                        if ($schedule) {
                            $schedule->is_booked = true;
                            $schedule->save();
                        }
                    }
                }

                return $order;
            });

            // Siapkan parameter untuk Midtrans
            $params = [
                'transaction_details' => [
                    'order_id' => $order->id,
                    'gross_amount' => (int)$grandTotal,
                ],
                'customer_details' => [
                    'first_name' => $validatedData['first_name'],
                    'last_name' => $validatedData['last_name'],
                    'email' => $validatedData['email'],
                    'phone' => $validatedData['phone'],
                    'billing_address' => [
                        'address' => $validatedData['address'],
                        'city' => $validatedData['city'],
                        'postal_code' => $validatedData['zip'],
                        'country_code' => 'IDN'
                    ],
                ],
                'item_details' => array_map(function($cart) {
                    return [
                        'id' => $cart['id'],
                        'price' => (int)$cart['price'],
                        'quantity' => 1,
                        'name' => $cart['name'],
                    ];
                }, $carts),
                'enabled_payments' => [
                    'credit_card', 'gopay', 'shopeepay', 'bca_va', 'bni_va', 'bri_va', 'other_va', 'dana'
                ],
            ];

            // Tambahkan sparring ke item_details jika ada
            if (!empty($sparrings)) {
                foreach ($sparrings as $sparring) {
                    $params['item_details'][] = [
                        'id' => 'sparring-' . $sparring['athlete_id'] . '-' . $sparring['schedule_id'],
                        'price' => (int)$sparring['price'],
                        'quantity' => 1,
                        'name' => 'Sparring with ' . $sparring['name'] . ' (' . $sparring['schedule'] . ')',
                    ];
                }
            }

            // Tambahkan biaya pengiriman
            $params['item_details'][] = [
                'id' => 'shipping',
                'price' => $shipping,
                'quantity' => 1,
                'name' => 'Shipping Cost (' . $validatedData['shipping_method'] . ')',
            ];

            // Tambahkan pajak
            $params['item_details'][] = [
                'id' => 'tax',
                'price' => (int)$tax,
                'quantity' => 1,
                'name' => 'Tax (10%)',
            ];

            // Buat Snap Token
            $snapToken = Snap::getSnapToken($params);

            // Simpan snap token ke database
            $order->snap_token = $snapToken;
            $order->save();

            // Hapus cookie cart jika berhasil
            Cookie::queue(Cookie::forget('cart'));

            // Hapus cookie sparring jika berhasil
            if (Cookie::has('sparring')) {
                Cookie::queue(Cookie::forget('sparring'));
            }

            // Kembalikan response dengan snap token
            return response()->json([
                'status' => 'success',
                'snap_token' => $snapToken,
                'order_id' => $order->id,
            ]);

        } catch (\Exception $e) {
            Log::error('Error creating order: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create order: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle notification from Midtrans
     */
    public function notification(Request $request)
    {
        // Ambil data notifikasi dari Midtrans
        $notif = json_decode($request->getContent(), true);

        // Ambil order ID dari notifikasi
        $orderId = $notif['order_id'];
        $transactionStatus = $notif['transaction_status'];
        $fraudStatus = $notif['fraud_status'] ?? null;

        Log::info('Midtrans notification', $notif);

        // Cari order berdasarkan ID
        $order = Order::find($orderId);

        if (!$order) {
            return response()->json([
                'status' => 'error',
                'message' => 'Order not found',
            ], 404);
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
            // Gunakan mapping untuk status lainnya
            $order->payment_status = $statusMapping[$transactionStatus] ?? 'pending';
        }

        // Simpan perubahan
        $order->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Notification processed successfully',
        ]);
    }

    /**
     * Handle payment finish
     */
    public function finish(Request $request)
    {
        $orderId = $request->order_id;
        $transactionStatus = $request->transaction_status;

        Log::info('Payment finish', [
            'order_id' => $orderId,
            'status' => $transactionStatus,
        ]);

        // Mapping status dari Midtrans ke format database
        $statusMapping = [
            'settlement' => 'paid',
            'capture' => 'paid',
            'pending' => 'pending',
            'deny' => 'failed',
            'expire' => 'failed',
            'cancel' => 'failed'
        ];

        // Update status pembayaran di database
        $order = Order::find($orderId);
        if ($order) {
            // Gunakan mapping untuk status
            $order->payment_status = $statusMapping[$transactionStatus] ?? 'pending';
            $order->save();
        }

        // Hapus cookie cart jika pembayaran berhasil
        if ($transactionStatus == 'settlement' || $transactionStatus == 'capture') {
            Cookie::queue(Cookie::forget('cart'));
            // Redirect ke halaman sukses
            return redirect()->route('checkout.success', ['order_id' => $orderId]);
        } else if ($transactionStatus == 'pending') {
            // Redirect ke halaman pending (bisa dibuat halaman terpisah)
            return redirect()->route('checkout.success', ['order_id' => $orderId])
                ->with('warning', 'Pembayaran kamu masih dalam proses. Silakan cek status pembayaran secara berkala.');
        } else {
            // Redirect kembali ke halaman checkout dengan pesan error
            return redirect()->route('checkout.index')
                ->with('error', 'Pembayaran gagal atau dibatalkan. Silakan coba lagi.');
        }
    }

    /**
     * Display payment success page
     */
    public function success(Request $request)
    {
        $orderId = $request->order_id;
        $order = Order::find($orderId);

        if (!$order) {
            return redirect()->route('home')->with('error', 'Order not found');
        }

        return view('public.checkout.success', compact('order'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
