<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SparringController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\venueController\DashboardController;
use App\Http\Controllers\venueController\BookingController;
use App\Http\Controllers\venueController\PromoController;
use App\Http\Controllers\venueController\TransactionController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\VenueController;
use App\Http\Controllers\CommunityController\NewsController;
use App\Http\Controllers\adminController\NewsController as AdminNewsController;
use App\Http\Controllers\adminController\GuidelinesController as AdminGuidelinesController;
use App\Http\Controllers\GuidelinesController as PublicGuidelinesController;
use App\Http\Controllers\adminController\AdminVenueController;
use App\Http\Controllers\adminController\AdminAthleteController;

/**
 * Endpoint for home page
 */
Route::get('/', function () {
    $products = App\Models\Product::inRandomOrder()->limit(4)->get();
    return view('landing', compact('products'));
})->name('index');

/**
 * Endpoint for login page
 */
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

/**
 * Endpoint for user login
 */
Route::post(
    '/login',
    [LoginController::class, 'login']
)->name('authenticate');

/**
 * Endpoint for user logout
 */
Route::get(
    '/logout',
    [LoginController::class, 'logout']
)->name('logout');

/**
 * Endpoint for user registration
 */
Route::get('/register', function () {
    return view('auth.register');
})->name('signup');

/**
 * Endpoint for user signup
 */
Route::post(
    '/register',
    [LoginController::class, 'register']
)->name('register');

/**
 * Endpoint for image upload
 */
Route::post('/upload', function (Request $request) {
    try {
        if (!$request->hasFile('image')) {
            return response()->json([
                'success' => false,
                'message' => 'No file uploaded'
            ], 400);
        }

        $file = $request->file('image');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $file->storeAs('uploads', $fileName, 'public');

        return response()->json([
            'success' => true,
            'message' => 'File uploaded successfully',
            'data' => [
                'image' => $fileName,
                'path' => $fileName
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Upload failed: ' . $e->getMessage()
        ], 500);
    }
})->name('upload.image');

/**
 * Endpoint to view all products
 */
Route::prefix('products')->group(function () {
    Route::get('/{product}', function ($product) {
        $detail = \App\Models\Product::findOrFail($product);
        $carts = json_decode(request()->cookie('cart') ?? '[]', true);
        $sparrings = json_decode(request()->cookie('sparring') ?? '[]', true);
        return view('public.product.detail', compact('detail', 'carts', 'sparrings'));
    })->name('products.detail');
    Route::get('/', function () {
        $products = \App\Models\Product::all();
        $carts = json_decode(request()->cookie('cart') ?? '[]', true);
        $sparrings = json_decode(request()->cookie('sparring') ?? '[]', true);
        return view('public.product.index', compact('products', 'carts', 'sparrings'));
    })->name('products.landing');
});

/**
 * Endpoint for events page
 */
Route::get('/events', [EventController::class, 'index'])->name('events.index');
Route::get('/event/{event:name}', [EventController::class, 'show'])->name('events.show');
Route::get('/event/{event:name}/bracket', [EventController::class, 'bracket'])->name('events.bracket');


/**
 * Can only be accessed when logged in
 * Dashboard overview page (all users)
 */
Route::redirect('dashboard', 'dashboard/overview');
Route::get('dashboard/overview', function () {
    return view('dashboard');
})->middleware('auth')->name('dashboard');

/**
 * Can only be accessed when logged in
 * Dashboard admin product page
 */
Route::middleware('auth')->prefix('dashboard/products')->group(function () {
    Route::get('/', [ProductController::class, 'index'])->name('products.index');
    Route::get('/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/', [ProductController::class, 'store'])->name('products.store');
    Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
});

/**
 * Can only be accessed when logged in
 * Dashboard admin comunity/news page
 */
Route::middleware('auth')->prefix('dashboard/comunity')->group(function () {
    Route::get('/', [AdminNewsController::class, 'index'])->name('comunity.index');
    Route::get('/create', [AdminNewsController::class, 'create'])->name('comunity.create');
    Route::post('/', [AdminNewsController::class, 'store'])->name('comunity.store');
    Route::get('/{news}/edit', [AdminNewsController::class, 'edit'])->name('comunity.edit');
    Route::put('/{news}', [AdminNewsController::class, 'update'])->name('comunity.update');
    Route::delete('/{news}', [AdminNewsController::class, 'destroy'])->name('comunity.destroy');
});

/**
 * Can only be accessed when logged in
 * Dashboard admin guidelines page
 */
Route::middleware('auth')->prefix('dashboard/guidelines')->group(function () {
    Route::get('/', [AdminGuidelinesController::class, 'index'])->name('admin.guidelines.index');
    Route::get('/create', [AdminGuidelinesController::class, 'create'])->name('admin.guidelines.create');
    Route::post('/', [AdminGuidelinesController::class, 'store'])->name('admin.guidelines.store');
    Route::get('/{guideline}/edit', [AdminGuidelinesController::class, 'edit'])->name('admin.guidelines.edit');
    Route::put('/{guideline}', [AdminGuidelinesController::class, 'update'])->name('admin.guidelines.update');
    Route::delete('/{guideline}', [AdminGuidelinesController::class, 'destroy'])->name('admin.guidelines.destroy');
});

/**
 * Can only be accessed when logged in
 * Dashboard admin venue management page
 */
Route::middleware('auth')->prefix('dashboard/venue')->group(function () {
    Route::get('/', [AdminVenueController::class, 'index'])->name('venue.index');
    Route::get('/create', [AdminVenueController::class, 'create'])->name('venue.create');
    Route::post('/', [AdminVenueController::class, 'store'])->name('venue.store');
    Route::get('/{venue}/edit', [AdminVenueController::class, 'edit'])->name('venue.edit');
    Route::put('/{venue}', [AdminVenueController::class, 'update'])->name('venue.update');
    Route::delete('/{venue}', [AdminVenueController::class, 'destroy'])->name('venue.destroy');
});

/**
 * Can only be accessed when logged in
 * Dashboard admin athlete management page
 */
Route::middleware('auth')->prefix('dashboard/athlete')->group(function () {
    Route::get('/', [AdminAthleteController::class, 'index'])->name('athlete.index');
    Route::get('/create', [AdminAthleteController::class, 'create'])->name('athlete.create');
    Route::post('/', [AdminAthleteController::class, 'store'])->name('athlete.store');
    Route::get('/{athlete}/edit', [AdminAthleteController::class, 'edit'])->name('athlete.edit');
    Route::put('/{athlete}', [AdminAthleteController::class, 'update'])->name('athlete.update');
    Route::delete('/{athlete}', [AdminAthleteController::class, 'destroy'])->name('athlete.destroy');
});

/**
 * Can only be accessed when logged in
 * Dashboard admin order page
 */
Route::middleware('auth')->prefix('dashboard/order')->group(function () {
    Route::get('/', function () {
        // Ambil semua order dari database
        $orders = \App\Models\Order::orderBy('created_at', 'desc')->get();

        // Hitung jumlah order berdasarkan status
        $pendingCount = \App\Models\Order::where('delivery_status', 'pending')->count();
        $processingCount = \App\Models\Order::where('delivery_status', 'processing')->count();
        $shippedCount = \App\Models\Order::where('delivery_status', 'shipped')->count();
        $deliveredCount = \App\Models\Order::where('delivery_status', 'delivered')->count();
        $cancelledCount = \App\Models\Order::where('delivery_status', 'cancelled')->count();

        return view('dash.admin.order', compact('orders', 'pendingCount', 'processingCount', 'shippedCount', 'deliveredCount', 'cancelledCount'));
    })->name('order.index');

    // Route untuk detail order
    Route::get('/detail/{order?}', function (\App\Models\Order $order = null) {
        // Definisikan status class untuk tampilan
        $statusClass = [
            'pending' => 'bg-[#3b82f6] text-white',
            'processing' => 'bg-[#fbbf24] text-[#78350f]',
            'packed' => 'bg-[#3b82f6] text-white',
            'shipped' => 'bg-[#3b82f6] text-white',
            'delivered' => 'bg-[#22c55e] text-white',
            'cancelled' => 'bg-[#f87171] text-[#7f1d1d]',
            'returned' => 'bg-[#f87171] text-[#7f1d1d]',
        ];

        return view('dash.admin.detailOrder', compact('order', 'statusClass'));
    })->name('order.detail.index');

    // Route untuk hapus order
    Route::delete('/delete/{order}', function ($order) {
        try {
            // Cari order berdasarkan ID
            $order = \App\Models\Order::findOrFail($order);

            // Hapus order items terlebih dahulu
            $order->products()->detach();

            // Hapus order
            $order->delete();

            return response()->json([
                'success' => true,
                'message' => 'Order berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to delete order', [
                'order_id' => $order,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus order: ' . $e->getMessage()
            ], 500);
        }
    })->name('admin.orders.delete');

    // Route untuk update status pengiriman
    Route::get('/update-status/{order}', function (Request $request, $order) {
        // Debug untuk melihat data yang diterima
        \Illuminate\Support\Facades\Log::info('Update status request', [
            'order_id' => $order,
            'status' => $request->query('status'),
            'all_data' => $request->all()
        ]);

        // Ambil status dari parameter URL
        $status = $request->query('status');

        // Validasi status tidak boleh kosong dan harus valid
        $validStatuses = ['pending', 'processing', 'packed', 'shipped', 'delivered', 'cancelled', 'returned'];

        if (!$status || !in_array($status, $validStatuses)) {
            return redirect()->route('order.index')->with('error', 'Status pengiriman tidak valid');
        }

        try {
            $order = \App\Models\Order::findOrFail($order);
            $oldStatus = $order->delivery_status;
            $order->delivery_status = $status;
            $order->save();

            \Illuminate\Support\Facades\Log::info('Status updated', [
                'order_id' => $order->id,
                'old_status' => $oldStatus,
                'new_status' => $order->delivery_status
            ]);

            return redirect()->route('order.index')->with('success', 'Status pengiriman berhasil diperbarui');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to update status', [
                'order_id' => $order,
                'status' => $status,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('order.index')->with('error', 'Gagal mengubah status: ' . $e->getMessage());
        }
    })->name('admin.orders.update-status');
});

/**
 * Can only be accessed when logged in
 * Dashboard admin promo page
 */
Route::middleware('auth')->prefix('dashboard/promo')->group(function () {
    Route::get('/', function () {
        return view('dash.admin.promo');
    })->name('promo.index');
});

/**
 * Can only be accessed when logged in
 * Dashboard admin partner/venue page
 */
Route::middleware('auth')->prefix('dashboard/partner')->group(function () {
    Route::get('/', function () {
        return view('dash.admin.partner');
    })->name('partner.index');
});

/**
 * Can only be accessed when logged in
 * Dashboard venue pages
 */
Route::middleware('auth')->prefix('venue')->group(function () {
    // Venue Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('venue.dashboard');


    // Venue Booking Management
    Route::get('/booking', [BookingController::class, 'index'])->name('venue.booking');

    // Venue Promo Management
    Route::get('/promo', [PromoController::class, 'index'])->name('venue.promo');

    // Venue Transaction History
    Route::get('/transaction', [TransactionController::class, 'index'])->name('venue.transaction');
});

/**
 * Can only be accessed when logged in
 * Dashboard user notification page
 */
Route::middleware('auth')->prefix('dashboard/notification')->group(function () {
    Route::get('/', function () {
        return view('dash.user.notification');
    })->name('notification.index');
});

/**
 * Can only be accessed when logged in
 * Dashboard user my order page
 */
Route::middleware('auth')->prefix('dashboard/myorder')->group(function () {
    Route::get('/', function () {
        return view('dash.user.myorder');
    })->name('myorder.index');
});

/**
 * Can only be accessed when logged in
 * Dashboard user booking page
 */
Route::middleware('auth')->prefix('dashboard/booking')->group(function () {
    Route::get('/', function () {
        return view('dash.user.booking');
    })->name('booking.index');
});

/**
 * Add to cart page
 */
Route::prefix('cart')->group(function () {
    Route::post('/add', function (Request $request) {
        $cart = json_decode($request->cookie('cart') ?? '[]', true);
        $product = \App\Models\Product::findOrFail($request->id);
        $exists = false;
        foreach ($cart as $item) {
            if ($item['id'] === $product->id) {
                $exists = true;
                break;
            }
        }
        if (!$exists) {
            $cart[] = [
                'id' => $product->id,
                'name' => $product->name,
                'image' => $product->images[0] ?? null,
                'price' => $product->pricing,
            ];
        }
        return redirect()->back()
            ->withCookie(cookie('cart', json_encode($cart), 60 * 24 * 7));
    })->name('cart.add');
    Route::post('/del', function (Request $request) {
        $cart = json_decode($request->cookie('cart') ?? '[]', true);
        $cart = array_filter($cart, function ($item) use ($request) {
            return $item['id'] !== (int)$request->id;
        });
        return redirect()->back()
            ->withCookie(cookie('cart', json_encode(array_values($cart)), 60 * 24 * 7));
    })->name('cart.del');
});

/**
 * Can only be accessed when logged in
 * Checkout Order page
 */
Route::middleware('auth')->prefix('checkout')->group(function () {
    Route::post('/', [OrderController::class, 'store'])->name('checkout.store');
    Route::get('/', [OrderController::class, 'index'])->name('checkout.index');
    Route::get('/finish', [OrderController::class, 'finish'])->name('checkout.finish');
    Route::get('/success', [OrderController::class, 'success'])->name('checkout.success');
});

/**
 * Midtrans notification handler
 * Can be accessed without login
 */
Route::post('/payment/notification', [OrderController::class, 'notification'])->name('payment.notification');

/**
 * Can only be accessed when logged in
 * Dashboard athlete pages
 */
Route::middleware('auth')->prefix('athlete')->group(function () {
    // Athlete Dashboard
    Route::get('/dashboard', [App\Http\Controllers\athleteController\DashboardController::class, 'index'])->name('athlete.dashboard');

    // Athlete Sparring - Create Session
    Route::get('/sparring/create', function () {
        return view('dash.athlete.sparring.create');
    })->name('athlete.sparring.create');


    // Athlete Match History (BARU)
    Route::get('/match', [App\Http\Controllers\athleteController\MatchHistoryController::class, 'index'])->name('athlete.match');
    // Create Session
    Route::get('/match/create', [App\Http\Controllers\athleteController\MatchHistoryController::class, 'create'])->name('athlete.match.create');
    Route::post('/match', [App\Http\Controllers\athleteController\MatchHistoryController::class, 'store'])->name('athlete.match.store');

    // Athlete Calendar
    Route::get('/calendar/{year}/{month}', [App\Http\Controllers\athleteController\DashboardController::class, 'getCalendar']);

    // Athlete Match History (BARU)
    Route::get('/match/{id}', [App\Http\Controllers\athleteController\MatchHistoryController::class, 'show'])->name('athlete.match.show');
});

/**
 * Endpoint to view all venues
 */
Route::get('/venues', [VenueController::class, 'index'])->name('venues.index');
Route::view('/about', 'about')->name('about');

// Sparring
Route::get('/sparring', [SparringController::class, 'index'])->name('sparring.index');
Route::get('/sparring/{id}', [SparringController::class, 'show'])->name('sparring.detail');
Route::post('/sparring/add-to-cart', [SparringController::class, 'addToCart'])->name('sparring.addToCart');
Route::delete('/sparring/remove-from-cart', [SparringController::class, 'removeFromCart'])->name('sparring.removeFromCart');

// Community
Route::get('/community', function () {
    return view('dash.community.index');
})->name('community.index');

Route::get('/community/news', function () {
    return view('dash.community.news');
})->name('community.news');

Route::get('/community/news/historic-rivalry', function () {
    return view('dash.community.show');
})->name('community.show');

// Community Routes
Route::prefix('community')->name('community.')->group(function () {
    // Main community page (redirect to news index)
    Route::get('/', [NewsController::class, 'index'])->name('index');

    // News Routes
    Route::get('/news', [NewsController::class, 'index'])->name('news.index');
    Route::get('/news/{news}', [NewsController::class, 'show'])->name('news.show');

    // Remove duplicate routes
})->withoutMiddleware([
    'auth',
    'verified'
]);

// Guidline
Route::get('/guideline', [PublicGuidelinesController::class, 'index'])->name('guideline.index');
Route::get('/guideline/category/{category}', [PublicGuidelinesController::class, 'category'])->name('guideline.category');
Route::get('/guideline/{slug}', [PublicGuidelinesController::class, 'show'])->name('guideline.show');
