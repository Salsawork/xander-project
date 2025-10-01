<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderSparring;
use App\Models\SparringSchedule;
use App\Models\OrderVenue;
use App\Models\Product;
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
        Log::info('Checkout page accessed');
    
        $allCarts = [];
        $carts = [];
        $sparrings = [];
        $venue = null;
        $total = 0;
        $tax = 0;
        $shipping = 30000;
    
        // Ambil semua data cart dari cookie
        if (Cookie::has('cart')) {
            $cartData = Cookie::get('cart');
            if ($cartData) {
                $allCarts = is_array($cartData) ? $cartData : json_decode($cartData, true) ?? [];
    
                $selectedItems = $request->input('items', []);
                if (!empty($selectedItems)) {
                    $selectedItems = array_map('intval', $selectedItems);
                    $carts = array_filter($allCarts, function($cart) use ($selectedItems) {
                        return in_array((int)$cart['id'], $selectedItems);
                    });
                    $carts = array_values($carts);
                } else {
                    $carts = $allCarts;
                }
    
                foreach ($carts as $cart) {
                    if (isset($cart['price'])) {
                        $total += (int)$cart['price'];
                    }
                }
            }
        }
    
        // Sparring dari cookie
        if (Cookie::has('sparring')) {
            $sparringData = Cookie::get('sparring');
            $sparrings = is_array($sparringData) ? $sparringData : json_decode($sparringData, true) ?? [];
    
            foreach ($sparrings as $sparring) {
                if (isset($sparring['price'])) {
                    $total += (int)$sparring['price'];
                }
            }
        }
    
        // Venue dari cookie
        if (Cookie::has('venue')) {
            $venueData = Cookie::get('venue');
            $venue = is_array($venueData) ? $venueData : json_decode($venueData, true);
    
            if ($venue && isset($venue['price'])) {
                $total += (int)$venue['price'];
            }
        }
    
        // Hitung pajak dan grand total
        $tax = $total * 0.1;
        $grandTotal = (int)($total + $shipping + $tax);
    
        // Kirim client key midtrans
        $clientKey = config('midtrans.client_key');
    
        // Kirim user (jika login) agar bisa prefill di blade
        $user = $request->user();
    
        return view('public.checkout.checkout', compact('carts','sparrings','venue','total','shipping','tax','grandTotal','clientKey','user'));
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
        $request->validate([
            'firstname'      => 'required|string|max:255',
            'lastname'       => 'required|string|max:255',
            'email'           => 'required|email',
            'phone'           => 'required|string|max:20',
            'payment_method'  => 'required|in:transfer_manual',
            'file'            => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'products'        => 'array',
            'products.*.id'   => 'required|exists:products,id',
            'products.*.qty'  => 'required|integer|min:1',
            'sparrings'       => 'array',
            'venues'          => 'array',
        ]);
    
        DB::beginTransaction();
    
        try {
            // Upload file bukti pembayaran
            $filename = time() . '_' . $request->file('file')->getClientOriginalName();
            $path = $request->file('file')->storeAs('payments', $filename, 'public');
    
            // Hitung total
            $total = 0;
    
            if (!auth()->check()) {
                return redirect()->route('login')->with('error', 'Harus login untuk checkout.');
            }
        
            $user = auth()->user();
        
            // Generate order number
            $orderNumber = 'ORD-' . strtoupper(uniqid());
        
            // Order utama
            $order = Order::create([
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'user_id' => $user->id,
                'order_number' => $orderNumber,
                'total'           => 0, 
                'payment_status'  => 'pending',
                'delivery_status' => 'pending',
                'payment_method'  => $request->payment_method,
                'file'            => $path,
            ]);
    
            // Tambahkan products
            if ($request->has('products')) {
                foreach ($request->products as $item) {
                    $product = Product::findOrFail($item['id']);
                    $qty     = $item['qty'];
                    $price   = $product->price;
                    $subtotal = $qty * $price;
    
                    $order->products()->attach($product->id, [
                        'quantity'  => $qty,
                        'price'     => $price,
                        'subtotal'  => $subtotal,
                        'discount'  => 0,
                    ]);
    
                    $total += $subtotal;
                }
            }
    
            // Tambahkan sparring (kalau ada)
            if ($request->has('sparrings')) {
                foreach ($request->sparrings as $sparring) {
                    $order->orderSparrings()->create([
                        'athlete_id'  => $sparring['athlete_id'],
                        'schedule_id' => $sparring['schedule_id'],
                        'price'       => $sparring['price'],
                    ]);
                    $total += $sparring['price'];
                }
            }
    
            // Venue (kalau ada, logikanya bisa mirip sparring)
            if ($request->has('venues')) {
                foreach ($request->venues as $venue) {
                    // misalnya simpan di order_items juga
                    $order->products()->attach($venue['id'], [
                        'quantity' => 1,
                        'price'    => $venue['price'],
                        'subtotal' => $venue['price'],
                        'discount' => 0,
                    ]);
                    $total += $venue['price'];
                }
            }
    
            // Update total
            $order->update(['total' => $total]);
    
            DB::commit();
    
            return response()->json([
                'status'  => 'success',
                'message' => 'Order berhasil dibuat!',
                'order_number' => $order->order_number,
                'data'    => $order->load('products', 'orderSparrings'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Checkout error', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
    
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to process order',
                'error'   => $e->getMessage(),
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

    public function payment(Request $request)
    {
        // Validasi input
        $request->validate([
            'order_id' => 'required|string',
        ]);

        $order = Order::find($request->order_id);

        return view('public.checkout.payment', compact('order'));
    }

    public function updatePayment(Request $request, Order $order) {
        $data = $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf', // Maksimal 2MB
        ]);

        // Save file
        $filePath = $data['file']->store('payment_proofs', 'public');

        // Update order with file path
        $order->file = $filePath;
        $order->save();

        return back()->with('success', 'Payment proof uploaded successfully. Please wait for confirmation.');
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
