<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bank;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderSparring;
use App\Models\SparringSchedule;
use App\Models\OrderVenue;
use App\Models\Product;
use App\Models\Table;
use App\Models\Venue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Midtrans\Config;
use Midtrans\Snap;
use Illuminate\Support\Facades\Mail;

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
        $carts = $venues = $sparrings = [];
        $total = $shipping = $tax = 0;

        $selectedItems = (array) $request->input('selected_items', []);

        // Products
        if (Cookie::has('cartProducts')) {
            $cartData = json_decode(Cookie::get('cartProducts'), true) ?? [];
            $carts = array_values(array_filter($cartData, fn($cart) => in_array((string)$cart['id'], $selectedItems)));
            foreach ($carts as $c) {
                $price = (int) $c['price'];
                $total += $price;
                $tax += $price * 0.1; 
            }
        }

        // Venues
        if (Cookie::has('cartVenues')) {
            $venueData = json_decode(Cookie::get('cartVenues'), true) ?? [];
            $venues = array_values(array_filter($venueData, fn($venue) => in_array("venue-{$venue['id']}", $selectedItems)));
            foreach ($venues as $v) {
                $price = (int) $v['price'];
                $total += $price;
            }
        }

        // Sparrings
        if (Cookie::has('cartSparrings')) {
            $sparringData = json_decode(Cookie::get('cartSparrings'), true) ?? [];
            $sparrings = array_values(array_filter($sparringData, fn($sparring) => in_array("sparring-{$sparring['schedule_id']}", $selectedItems)));
            foreach ($sparrings as $s) {
                $price = (int) $s['price'];
                $total += $price;
            }
        }

        $grandTotal = $total + $shipping + $tax;

        $user = $request->user();
        $banks = Bank::all();
        return view('public.checkout.checkout', compact('carts', 'venues', 'sparrings', 'total', 'shipping', 'tax', 'grandTotal', 'user', 'banks'));
    }




    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
       
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'firstname'      => 'required|string|max:255',
            'lastname'       => 'required|string|max:255',
            'email'          => 'required|email',
            'phone'          => 'required|string|max:20',
            'payment_method' => 'required|in:transfer_manual',
            'shipping'       => 'nullable|numeric|min:0',
            'tax'            => 'nullable|numeric|min:0',

            // products
            'products'        => 'array',
            'products.*.id'   => 'required|exists:products,id',
            'products.*.qty'  => 'required|integer|min:1',

            // sparrings
            'sparrings'                => 'array',
            'sparrings.*.athlete_id'   => 'required|exists:users,id',
            'sparrings.*.schedule_id'  => 'required|exists:sparring_schedules,id',
            'sparrings.*.price'        => 'required|numeric|min:0',

            // venues
            'venues'            => 'array',
            'venues.*.id'       => 'required|exists:venues,id',
            'venues.*.price'    => 'required|numeric|min:0',
            'venues.*.date'     => 'required',
            'venues.*.start'    => 'required',
            'venues.*.end'      => 'required',
            'venues.*.table' => 'nullable|exists:tables,table_number'
        ]);

        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Harus login untuk checkout.');
        }

        DB::beginTransaction();

        try {
            $user = auth()->user();

            // Generate order number
            $orderNumber = 'ORD-' . strtoupper(uniqid());

            // Buat order utama
            $order = Order::create([
                'id'              => (string) Str::uuid(),
                'user_id'         => $user->id,
                'bank_id'         => $request->bank_id,
                'order_number'    => $orderNumber,
                'total'           => 0, // update setelah item masuk
                'payment_status'  => 'pending',
                'delivery_status' => 'pending',
                'payment_method'  => $request->payment_method,
            ]);

            $total = 0;

            /** =========================
             *  Tambahkan Products
             *  ========================= */
            if ($request->has('products')) {
                foreach ($request->products as $item) {
                    $product = Product::findOrFail($item['id']);
                    $qty     = $item['qty'];
                    $price   = $product->pricing;
                    $tax      = $request->tax ?? 0;
                    $shipping = $request->shipping ?? 0;
                    $subtotal = $qty * $price + $tax + $shipping;

                    $order->products()->attach($product->id, [
                        'quantity' => $qty,
                        'price'    => $price,
                        'subtotal' => $subtotal,
                        'discount' => 0,
                        'tax'      => $tax,
                        'shipping' => $shipping,
                    ]);

                    $total += $subtotal;
                }
            }

            /** =========================
             *  Tambahkan Venues / Bookings
             *  ========================= */
            if ($request->has('venues')) {
                foreach ($request->venues as $venue) {
                    $bookingDate = \Carbon\Carbon::createFromFormat('d-m-Y', $venue['date'])->format('Y-m-d');

                    // Cara yang lebih aman untuk mendapatkan table id
                    if (isset($venue['table']) && isset($venue['id'])) {
                        $tableId = DB::table('tables')
                            ->where('table_number', $venue['table'])
                            ->where('venue_id', $venue['id'])
                            ->value('id');

                        if (!$tableId) {
                            throw new \Exception('Table not found for venue: ' . $venue['id']);
                        }
                    } else {
                        throw new \Exception('Venue ID and Table number are required');
                    }


                    $order->bookings()->create([
                        'venue_id' => $venue['id'],
                        'price'    => $venue['price'],
                        'table_id' => $tableId,
                        'user_id'  => $user->id,
                        'booking_date' => $bookingDate,
                        'start_time'   => $venue['start'],
                        'end_time'     => $venue['end'],
                    ]);

                    $total += $venue['price'];
                }
            }

            /** =========================
             *  Tambahkan Sparring
             *  ========================= */
            if ($request->has('sparrings')) {
                foreach ($request->sparrings as $sparring) {
                    $order->orderSparrings()->create([
                        'athlete_id'  => $sparring['athlete_id'],
                        'schedule_id' => $sparring['schedule_id'],
                        'price'       => $sparring['price'],
                    ]);

                    $total += $sparring['price'];

                    // Kirim email ke athlete
                    $athlete = User::find($sparring['athlete_id']);
                    $user    = auth()->user(); // pemesan
                    if ($athlete && $athlete->email) {
                        $messageBody = "Halo {$athlete->name}, ada user yang baru saja melakukan order sparring denganmu.\n\n" .
                            "Detail Pemesan:\n" .
                            "- Nama: {$user->name}\n" .
                            "- Email: {$user->email}\n" .
                            "- Telepon: {$user->phone}\n\n" .
                            "Detail Order:\n" .
                            "- Order Number: {$order->order_number}\n" .
                            "- Total: Rp " . number_format($order->total, 0, ',', '.');

                        Mail::raw($messageBody, function ($message) use ($athlete) {
                            $message->to($athlete->email)
                                ->subject('Notifikasi Order Sparring Baru');
                        });
                    }
                }
            }

            if ($request->filled('shipping')) {
                $total += $request->shipping;
            }

            if ($request->filled('tax')) {
                $total += $request->tax;
            }

            // Update total order
            $order->update(['total' => $total]);

            DB::commit();

            return response()->json([
                'status'       => 'success',
                'message'      => 'Order berhasil dibuat!',
                'order_number' => $order->order_number,
                'data'         => $order->load('user', 'products', 'orderSparrings', 'bookings'),
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
        $request->validate([
            'order_number' => 'required|string',
        ]);

        $order = Order::where('order_number', $request->order_number)->firstOrFail();

        return view('public.checkout.payment', compact('order'));
    }


    public function updatePayment(Request $request, Order $order)
    {
        $data = $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf', // Maksimal 2MB
        ]);

        // Save file
        $filePath = $data['file']->store('payment_proofs', 'public');

        // Update order with file path
        $order->file = $filePath;
        $order->payment_status = 'paid';
        $order->save();

        return redirect()->route('checkout.success', ['order_id' => $order->id])->with('success', 'Payment proof uploaded successfully. Please wait for confirmation.');
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
    public function showDetailOrder(Order $order)
    {
        if ($order->items()->exists()) {
            $items = $order->items()->with('product')->get();
            $products = $items->pluck('product')->unique();
            
            $hasBooking = $order->bookings()->exists();
            $hasSparring = $order->orderSparrings()->exists();
            return view('public.order.item', compact('order', 'items', 'products', 'hasBooking', 'hasSparring'));
        }

        if ($order->orderSparrings()->exists()) {
            $sparrings = $order->orderSparrings()->get();
            return view('public.order.sparring', compact('order', 'sparrings'));
        }

        if ($order->bookings()->exists()) {
            $bookings = $order->bookings()->with(['venue', 'table'])->get();
            $venues = $bookings->pluck('venue')->unique();
            $tables = $bookings->pluck('table')->unique();
            return view('public.order.booking', compact('order', 'bookings', 'venues', 'tables'));
        }
    }

    public function showDetailBooking(Order $order)
    {
        if ($order->bookings()->exists()) {
            $bookings = $order->bookings()->with(['venue', 'table'])->get();
            $venues = $bookings->pluck('venue')->unique();
            $tables = $bookings->pluck('table')->unique();
            return view('public.order.booking', compact('order', 'bookings', 'venues', 'tables'));
        }
    }

    public function showDetailSparring(Order $order)
    {
        if ($order->orderSparrings()->exists()) {
            $sparrings = $order->orderSparrings()->get();
            return view('public.order.sparring', compact('order', 'sparrings'));
        }
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
