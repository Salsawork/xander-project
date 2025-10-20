<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bank;
use App\Models\Booking;
use App\Models\CartItem;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderSparring;
use App\Models\SparringSchedule;
use App\Models\OrderVenue;
use App\Models\Product;
use App\Models\Table;
use App\Models\Venue;
use App\Models\Voucher;
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
        Log::info('Checkout page accessed');

        $total = $shipping = $tax = 0;
        $selectedItems = (array) $request->input('selected_items', []);

        $selectedProducts = [];
        $selectedVenues = [];
        $selectedSparrings = [];

        foreach ($selectedItems as $item) {
            if (!str_contains($item, ':')) {
                \Log::warning('Invalid selected item format', ['item' => $item]);
                continue;
            }

            [$type, $id] = explode(':', $item);
            switch ($type) {
                case 'product':
                    $selectedProducts[] = $id;
                    break;
                case 'venue':
                    $selectedVenues[] = $id;
                    break;
                case 'sparring':
                    $selectedSparrings[] = $id;
                    break;
            }
        }


        $carts = collect();
        $venues = collect();
        $sparrings = collect();

        if (auth()->check()) {
            $userId = auth()->id();

            // --- PRODUK ---
            $carts = CartItem::with('product')
                ->where('user_id', $userId)
                ->where('item_type', 'product')
                ->whereIn('id', $selectedProducts)
                ->get()
                ->map(fn($item) => [
                    'cart_id'    => $item->id,
                    'product_id' => $item->product?->id,
                    'name'       => $item->product?->name,
                    'brand'      => $item->product?->brand,
                    'category'   => $item->product?->category?->name ?? '-',
                    'price'      => $item->product?->pricing ?? 0,
                    'quantity'   => $item->quantity,
                    'weight'     => $item->product?->weight ?? 0,
                    'total'      => $item->quantity * ($item->product?->pricing ?? 0),
                    'discount'   => $item->product?->discount ?? 0,
                    'images'     => $item->product?->images[0] ?? null,
                ]);

            foreach ($carts as $c) {
                $price = (int) $c['price'];
                $discountPercent = $c['discount'] ?? 0;
                $discount = $price * $discountPercent;
                $subtotal = ($price - $discount) * $c['quantity'];
                $total += $subtotal;
                $tax += $subtotal * 0.1;
            }

            // --- VENUE ---
            $venues = CartItem::with('venue')
                ->where('user_id', $userId)
                ->where('item_type', 'venue')
                ->whereIn('id', $selectedVenues)
                ->get()
                ->map(fn($item) => [
                    'cart_id' => $item->id,
                    'id'      => $item->venue?->id,
                    'name'    => $item->venue?->name,
                    'address' => $item->venue?->address ?? '-',
                    'date'    => $item->date,
                    'start'   => $item->start,
                    'end'     => $item->end,
                    'table'   => $item->table_number,
                    'price'   => $item->price,
                    'duration' => $item->start && $item->end
                        ? gmdate('H:i', strtotime($item->end) - strtotime($item->start))
                        : null,
                    'code_promo' => $item->code_promo,
                ]);

            $venueDiscount = 0;

            foreach ($venues as $v) {
                $total += (int) $v['price'];

                if (!empty($v['code_promo'])) {
                    $voucher = Voucher::where('code', $v['code_promo'])
                        ->where('venue_id', $v['id'])
                        ->where('is_active', 1)
                        ->where('start_date', '<=', now())
                        ->where('end_date', '>=', now())
                        ->first();

                    if ($voucher) {
                        $venuePrice = (int) $v['price'];
                        if ($venuePrice >= $voucher->minimum_purchase) {
                            $discount = 0;
                            if ($voucher->discount_percentage) {
                                $discount = $venuePrice * ($voucher->discount_percentage / 100);
                            } elseif ($voucher->discount_amount) {
                                $discount = $voucher->discount_amount;
                            }
                            $venueDiscount += $discount;
                            \Log::info("Voucher {$voucher->code} applied for venue {$v['name']}", [
                                'discount' => $discount,
                            ]);
                        } else {
                            \Log::warning("Voucher {$voucher->code} not applied (below minimum purchase)");
                        }
                    } else {
                        \Log::warning("Invalid or expired voucher for venue", ['code' => $v['code_promo']]);
                    }
                }
            }

            // --- SPARRING ---
            $sparrings = CartItem::with(['sparringSchedule.athlete'])
                ->where('user_id', $userId)
                ->where('item_type', 'sparring')
                ->whereIn('id', $selectedSparrings)
                ->get()
                ->map(fn($item) => [
                    'cart_id'       => $item->id,
                    'schedule_id'   => $item->sparringSchedule?->id,
                    'athlete_id'    => $item->sparringSchedule?->athlete?->id,
                    'athlete_name'  => $item->sparringSchedule?->athlete?->name ?? 'Unknown Athlete',
                    'athlete_image' => $item->sparringSchedule?->athlete?->athleteDetail?->image ?? null,
                    'date'          => $item->date,
                    'start'         => $item->start,
                    'end'           => $item->end,
                    'price'         => $item->price,
                ]);

            foreach ($sparrings as $s) {
                $total += (int) $s['price'];
            }
        }

        $grandTotal = round($total + $shipping + $tax - $venueDiscount);

        $user = $request->user();
        $banks = Bank::all();

        return view('public.checkout.checkout', compact(
            'carts',
            'venues',
            'sparrings',
            'total',
            'shipping',
            'tax',
            'grandTotal',
            'venueDiscount',
            'user',
            'banks'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'firstname'         => 'required|string|max:255',
            'lastname'          => 'required|string|max:255',
            'email'             => 'required|email',
            'phone'             => 'required|string|max:20',
            'payment_method'    => 'required|in:transfer_manual',
            'shipping'          => 'nullable|numeric|min:0',
            'tax'               => 'nullable|numeric|min:0',
            'province_name'     => 'nullable|string|max:255',
            'city_name'         => 'nullable|string|max:255',
            'district_name'     => 'nullable|string|max:255',
            'subdistrict_name'  => 'nullable|string|max:255',
            'courier'           => 'nullable|string|max:255',
            'address'           => 'nullable|string|max:255',

            // products
            'products'             => 'array',
            'products.*.id'        => 'required|exists:products,id',
            'products.*.quantity'  => 'nullable|integer|min:1',

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
            'venues.*.table'    => 'nullable|exists:tables,table_number',
            'venues.*.code_promo'    => 'nullable'
        ]);

        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Harus login untuk checkout.');
        }

        DB::beginTransaction();

        try {
            $user = auth()->user();

            // ==========================
            // Tentukan order_type berdasarkan data yang dikirim
            // ==========================
            $types = '';
            if ($request->has('products') && count($request->products) > 0) {
                $types = 'product';
            } elseif ($request->has('venues') && count($request->venues) > 0) {
                $types = 'venue';
            } elseif ($request->has('sparrings') && count($request->sparrings) > 0) {
                $types = 'sparring';
            }

            if ($types === '') {
                throw new \Exception('Tidak ada item yang dikirim untuk checkout.');
            }

            $orderType = $types;

            // ==========================
            // Buat order utama
            // ==========================
            $orderNumber = 'ORD-' . strtoupper(uniqid());
            $order = Order::create([
                'id'              => (string) Str::uuid(),
                'user_id'         => $user->id,
                'order_number'    => $orderNumber,
                'total'           => 0,
                'payment_status'  => 'pending',
                'delivery_status' => 'pending',
                'payment_method'  => $request->payment_method,
                'order_type'      => $orderType,
            ]);

            $total = 0;

            /** =========================
             *  Tambahkan Products
             *  ========================= */
            if ($orderType === 'product') {
                $checkedIds = array_column($request->products, 'id');

                CartItem::where('user_id', $user->id)
                    ->where('item_type', 'product')
                    ->whereIn('item_id', $checkedIds)
                    ->delete();

                foreach ($request->products as $item) {
                    $product = Product::findOrFail($item['id']);
                    $qty     = $item['quantity'];

                    if ($product->stock < $qty) {
                        throw new \Exception("Stok produk {$product->name} tidak mencukupi.");
                    }

                    $product->decrement('stock', $qty);
                    $price   = $product->pricing;
                    $tax      = $request->tax ?? 0;
                    $shipping = $request->shipping ?? 0;
                    $discount = ($price * $product->discount);
                    $subtotal = ($price - $discount) * $qty;
                    $courier  = $request->courier;
                    $address  = $request->address;
                    $province = $request->province_name;
                    $city     = $request->city_name;
                    $district = $request->district_name;
                    $subdistrict = $request->subdistrict_name;

                    $total += $subtotal;

                    $order->products()->attach($product->id, [
                        'quantity' => $qty,
                        'price'    => $price,
                        'subtotal' => $subtotal,
                        'discount' => $discount * $qty,
                        'tax'      => $tax,
                        'shipping' => $shipping,
                        'courier'  => $courier,
                        'address'  => $address,
                        'province' => $province,
                        'city'     => $city,
                        'district' => $district,
                        'subdistrict' => $subdistrict,
                    ]);
                }
            }

            /** =========================
             *  Tambahkan Venues / Bookings
             *  ========================= */
            if ($orderType === 'venue') {
                $venueTotal = 0; 
                $venueDiscount = 0; 

                foreach ($request->venues as $venueData) {
                    $bookingDate = \Carbon\Carbon::parse($venueData['date'])->format('Y-m-d');

                    if (!isset($venueData['table']) || !isset($venueData['id'])) {
                        throw new \Exception('Venue ID dan Table number wajib diisi.');
                    }

                    $tableId = DB::table('tables')
                        ->where('table_number', $venueData['table'])
                        ->where('venue_id', $venueData['id'])
                        ->value('id');

                    if (!$tableId) {
                        throw new \Exception('Table not found for venue: ' . $venueData['id']);
                    }

                    $existingBooking = Booking::where('table_id', $tableId)
                        ->where('booking_date', $bookingDate)
                        ->whereIn('status', ['booked', 'confirmed'])
                        ->where(function ($q) use ($venueData) {
                            $q->where('start_time', '<', $venueData['end'])
                                ->where('end_time', '>', $venueData['start']);
                        })
                        ->exists();

                    if ($existingBooking) {
                        throw new \Exception('Table ' . $venueData['table'] . ' pada venue ini sudah dipesan untuk tanggal dan waktu yang sama.');
                    }

                    $venuePrice = (int) $venueData['price'];
                    $discountValue = 0; 
                    if (!empty($venueData['code_promo'])) {
                        $voucher = Voucher::where('code', $venueData['code_promo'])
                            ->where('venue_id', $venueData['id'])
                            ->where('is_active', 1)
                            ->where('start_date', '<=', now())
                            ->where('end_date', '>=', now())
                            ->first();

                        if ($voucher) {
                            // Cek apakah quota masih tersedia
                            if ($voucher->claimed >= $voucher->quota) {
                                \Log::warning("Voucher {$voucher->code} quota exceeded", [
                                    'claimed' => $voucher->claimed,
                                    'quota'   => $voucher->quota,
                                ]);
                            } elseif ($venuePrice >= $voucher->minimum_purchase) {
                                $discount = 0;

                                if ($voucher->discount_percentage) {
                                    $discount = $venuePrice * ($voucher->discount_percentage / 100);
                                } elseif ($voucher->discount_amount) {
                                    $discount = $voucher->discount_amount;
                                }

                                $venuePrice -= $discount;
                                $venueDiscount += $discount;
                                $discountValue = $discount; // simpan nilai diskon

                                // Increment claimed count
                                $voucher->increment('claimed');

                                \Log::info("Voucher {$voucher->code} applied for venue {$venueData['id']}", [
                                    'discount' => $discount,
                                    'final_price' => $venuePrice,
                                    'claimed' => $voucher->claimed,
                                ]);
                            } else {
                                \Log::warning("Voucher {$voucher->code} not applied (below minimum purchase)");
                            }
                        } else {
                            \Log::warning("Invalid or expired voucher for venue", ['code' => $venueData['code_promo']]);
                        }
                    }

                    // Tambahkan ke tabel bookings
                    $order->bookings()->create([
                        'venue_id'     => $venueData['id'],
                        'price'        => $venuePrice, // harga setelah diskon
                        'table_id'     => $tableId,
                        'user_id'      => $user->id,
                        'booking_date' => $bookingDate,
                        'status'       => 'pending',
                        'start_time'   => $venueData['start'],
                        'end_time'     => $venueData['end'],
                        'discount'     => $discountValue,
                    ]);

                    // Hapus item dari cart
                    CartItem::where('user_id', $user->id)
                        ->where('item_type', 'venue')
                        ->where('item_id', $venueData['id'])
                        ->where('date', $bookingDate)
                        ->where('start', $venueData['start'])
                        ->where('end', $venueData['end'])
                        ->delete();

                    // Tambahkan harga venue (setelah diskon) ke total keseluruhan
                    $venueTotal += $venuePrice;
                }

                // Akumulasi ke total order global (sudah dikurangi diskon)
                $total += $venueTotal;

                \Log::info('Total venue setelah diskon', [
                    'venue_total' => $venueTotal,
                    'venue_discount' => $venueDiscount,
                    'total_order' => $total
                ]);
            }


            /** =========================
             *  Tambahkan Sparring
             *  ========================= */
            if ($orderType === 'sparring') {
                $checkedIds = array_column($request->sparrings, 'schedule_id');

                CartItem::where('user_id', $user->id)
                    ->where('item_type', 'sparring')
                    ->whereIn('item_id', $checkedIds)
                    ->delete();

                // Cek apakah jadwal sparring masih tersedia
                $schedules = SparringSchedule::whereIn('id', $checkedIds)->get();
                foreach ($schedules as $schedule) {
                    if ($schedule->is_booked) {
                        throw new \Exception('Jadwal sparring tidak tersedia atau sudah dipesan.');
                    }
                }

                foreach ($request->sparrings as $sparring) {
                    $order->orderSparrings()->create([
                        'athlete_id'  => $sparring['athlete_id'],
                        'schedule_id' => $sparring['schedule_id'],
                        'price'       => $sparring['price'],
                    ]);

                    // SparringSchedule::where('id', $sparring['schedule_id'])->update(['is_booked' => true]);

                    $total += $sparring['price'];

                    $athlete = User::find($sparring['athlete_id']);
                    if ($athlete && $athlete->email) {
                        $messageBody = "Halo {$athlete->name}, ada user yang baru saja melakukan order sparring denganmu.\n\n" .
                            "Detail Pemesan:\n" .
                            "- Nama: {$user->name}\n" .
                            "- Email: {$user->email}\n" .
                            "- Telepon: {$user->phone}\n\n" .
                            "Detail Order:\n" .
                            "- Order Number: {$order->order_number}\n" .
                            "- Total: Rp " . number_format($total, 0, ',', '.');

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
                'order_type'   => $orderType,
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
        $bank = Bank::all();

        return view('public.checkout.payment', compact('order', 'bank'));
    }


    public function updatePayment(Request $request, Order $order)
    {
        $data = $request->validate([
            'bank_id' => 'required|integer',
            'no_rekening' => 'required|string',
            'atas_nama' => 'required|string',
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf',
        ]);

        // Save file
        $filePath = $data['file']->store('payment_proofs', 'public');

        // Update order with file path
        $order->bank_id = $data['bank_id'];
        $order->no_rekening = $data['no_rekening'];
        $order->atas_nama = $data['atas_nama'];
        $order->file = $filePath;
        $order->payment_status = 'processing';
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
        switch ($order->order_type) {
            case 'product':
                $order->load('products.category');
                $user = User::find($order->user_id);
                return view('public.order.item', compact('order', 'user'));

            case 'venue':
                $order->load(['bookings.venue', 'bookings.table']);
                $bookings = $order->bookings;
                $user = User::find($order->user_id);
                return view('public.order.booking', compact('order', 'bookings', 'user'));

            case 'sparring':
                $order->load('orderSparrings');
                $sparrings = $order->orderSparrings;
                $user = User::find($order->user_id);
                return view('public.order.sparring', compact('order', 'sparrings', 'user'));

            default:
                return redirect()->back()->with('error', 'Jenis order tidak dikenal.');
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
