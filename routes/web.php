<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

/**
 * Controllers
 */
use App\Http\Controllers\LoginController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SparringController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\VenueController;
use App\Http\Controllers\TreeController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\SubscriberController;
use App\Http\Controllers\OpinionController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\ShippingController;

// Event Admin
use App\Http\Controllers\adminController\EventController as AdminEventController;

// Venue Controllers
use App\Http\Controllers\venueController\DashboardController as VenueDashboardController;
use App\Http\Controllers\venueController\BookingController;
use App\Http\Controllers\venueController\PromoController;
use App\Http\Controllers\venueController\PriceScheduleController;
use App\Http\Controllers\venueController\TransactionController;

// Athlete Controllers
use App\Http\Controllers\athleteController\DashboardController as AthleteDashboardController;
use App\Http\Controllers\athleteController\MatchHistoryController;
use App\Http\Controllers\athleteController\SparringController as AthleteSparringController;

// Community & Admin Controllers
use App\Http\Controllers\communityController\NewsController;
use App\Http\Controllers\adminController\NewsController as AdminNewsController;
use App\Http\Controllers\adminController\GuidelinesController as AdminGuidelinesController;
use App\Http\Controllers\GuidelinesController as PublicGuidelinesController;
use App\Http\Controllers\adminController\AdminVenueController;
use App\Http\Controllers\adminController\AdminAthleteController;
use App\Http\Controllers\adminController\TournamentController;
use App\Http\Controllers\Dashboard\OrderController as DashboardOrderController;
use App\Http\Controllers\adminController\VoucherController;
use App\Http\Controllers\CartItemController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [ProductController::class, 'index'])->name('index');
Route::get('/level', [ProductController::class, 'filterByLevel'])->name('level');
Route::view('/about', 'about')->name('about');

/** Login & Register */
Route::view('/login', 'auth.login')->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('authenticate');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Google OAuth
Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('oauth.google.redirect');
Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('oauth.google.callback');

Route::view('/register', 'auth.register')->name('signup');
Route::post('/register', [LoginController::class, 'register'])->name('register');
Route::get('/verify', [LoginController::class, 'showVerificationForm'])->name('verification.form');
Route::post('/verify', [LoginController::class, 'verifyOtp'])->name('verification.verify');

/** Image Upload */
Route::post('/upload', [UploadController::class, 'store'])->name('upload.image');

/** Products */
Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'landing'])->name('products.landing');
    Route::get('/{id}/{slug?}', [ProductController::class, 'detail'])
        ->where('id', '[0-9]+')
        ->name('products.detail');
});

/** Venues */
Route::prefix('venues')->group(function () {
    Route::get('/', [VenueController::class, 'index'])->name('venues.index');

    // API price schedule (JANGAN DI BAWAH DETAIL)
    Route::get('/{venueId}/price-schedules', [VenueController::class, 'priceSchedules'])
        ->where(['venueId' => '[0-9]+'])
        ->name('venues.priceSchedules');

    // DETAIL PAGE: /venues/{id}/{slug?}
    Route::get('/{venue}/{slug?}', [VenueController::class, 'showDetail'])
        ->where(['venue' => '[0-9]+'])
        ->name('venues.detail');

    // FAVORITE toggle (opsional)
    Route::post('/{venue}/favorite', [FavoriteController::class, 'toggle'])->name('venues.favorite');

    // REVIEW venue (opsional)
    Route::post('/{venue}/reviews', [VenueController::class, 'storeReview'])
        ->middleware('auth')
        ->name('venues.reviews.store');
});

/** Events */
Route::get('/events', [EventController::class, 'index'])->name('events.index');
Route::get('/events/all', [EventController::class, 'list'])->name('events.list');

// Event Detail (Canonical with ID)
Route::get('/event/{event}/{name?}', [EventController::class, 'show'])
    ->where('event', '[0-9]+')
    ->name('events.show');

// Event Bracket (Canonical with ID)
Route::get('/event/{event}/{name?}/bracket', [EventController::class, 'bracketById'])
    ->where('event', '[0-9]+')
    ->name('events.bracket');

// Event Register (POST) — simpan input modal / pendaftaran
Route::post('/event/{event}/register', [EventController::class, 'register'])
    ->where('event', '[0-9]+')
    ->middleware('auth')
    ->name('events.register');

// Backward Compatibility Routes (redirect ke canonical)
Route::get('/event/{event:name}', [EventController::class, 'showByName'])
    ->name('events.show.byname');

Route::get('/event/{event:name}/bracket', [EventController::class, 'bracketByName'])
    ->name('events.bracket.byname');

/** Services */
Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
Route::get('/services/{slug}', [ServiceController::class, 'show'])->name('services.show');

/** Sparring (PAKAI SLUG) */
Route::prefix('sparring')->group(function () {
    Route::get('/', [SparringController::class, 'index'])->name('sparring.index');

    // DETAIL: /sparring/{id}/{slug?}
    Route::get('/{id}/{slug?}', [SparringController::class, 'show'])
        ->where(['id' => '[0-9]+'])
        ->name('sparring.detail');

    Route::post('/add-to-cart', [SparringController::class, 'addToCart'])->name('sparring.addToCart');
    Route::delete('/remove-from-cart', [SparringController::class, 'removeFromCart'])->name('sparring.removeFromCart');

    // Reviews
    Route::post('/{id}/reviews', [SparringController::class, 'storeReview'])->name('sparring.review.store');
});

/** Community (Public) */
Route::prefix('community')->name('community.')->withoutMiddleware(['auth', 'verified'])->group(function () {
    Route::get('/', [NewsController::class, 'index'])->name('index');
    Route::get('/news', [NewsController::class, 'index'])->name('news.index');
    Route::get('/news/{news}', [NewsController::class, 'show'])->name('news.show');
});

/** Guidelines (Public) */
Route::get('/guideline', [PublicGuidelinesController::class, 'index'])->name('guideline.index');
Route::get('/guideline/category/{category}', [PublicGuidelinesController::class, 'category'])->name('guideline.category');
Route::get('/guideline/{slug}', [PublicGuidelinesController::class, 'show'])->name('guideline.show');

Route::post('/subscribe', [SubscriberController::class, 'store'])->name('subscribe.store');
Route::post('/opinion', [OpinionController::class, 'store'])->name('opinion.store');

/*
|--------------------------------------------------------------------------
| Cart & Checkout
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->prefix('cart')->group(function () {
    Route::post('/add/product', [CartItemController::class, 'addProductToCart'])->name('cart.add.product');
    Route::post('/add/venue', [CartItemController::class, 'addVenueToCart'])->name('cart.add.venue');
    Route::post('/add/sparring', [CartItemController::class, 'addSparringToCart'])->name('cart.add.sparring');
    Route::post('/delete', [CartItemController::class, 'removeFromCart'])->name('cart.delete');
});

/** Checkout (auth required) */
Route::prefix('checkout')->group(function () {
    Route::get('/', [OrderController::class, 'index'])->name('checkout.index');
    Route::post('/', [OrderController::class, 'store'])->name('checkout.store');
    Route::get('/payment', [OrderController::class, 'payment'])->name('checkout.payment');
    Route::put('/payment/{order}', [OrderController::class, 'updatePayment'])->name('checkout.updatePayment');
    Route::get('/finish', [OrderController::class, 'finish'])->name('checkout.finish');
    Route::get('/success', [OrderController::class, 'success'])->name('checkout.success');
});

Route::middleware('auth')->prefix('dashboard/order')->group(function () {
    Route::get('/product', [DashboardOrderController::class, 'indexProduct'])->name('order.index.product');    
    Route::get('/booking', [DashboardOrderController::class, 'indexBooking'])->name('order.index.booking');
    Route::get('/sparring', [DashboardOrderController::class, 'indexSparring'])->name('order.index.sparring');
    Route::get('/detail/{order?}', [DashboardOrderController::class, 'detail'])->name('order.detail.index');
    Route::delete('/delete/{order}', [DashboardOrderController::class, 'destroy'])->name('order.delete');
    Route::get('/update-status/{order}', [DashboardOrderController::class, 'updateStatus'])->name('admin.orders.update-status');
    Route::get('/update-payment-status/{order}', [DashboardOrderController::class, 'updatePaymentStatus'])->name('admin.orders.update-payment-status');
    Route::get('/update-booking-status/{order}', [DashboardOrderController::class, 'updateBookingStatus'])->name('admin.orders.update-booking-status');
});

/** Midtrans Notification (public) */
Route::post('/payment/notification', [OrderController::class, 'notification'])->name('payment.notification');

/*
|--------------------------------------------------------------------------
| Authenticated Dashboard Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    /** General Dashboard */
    Route::redirect('dashboard', 'dashboard/overview');
    Route::get('dashboard/overview', fn() => view('dashboard'))->name('dashboard');

    /** User: pages for sidebar **/
    Route::get('dashboard/notification', fn() => view('dash.user.notification'))->name('notification.index');
    Route::get('dashboard/myorder', fn() => view('dash.user.myorder'))->name('myorder.index');
    Route::get('dashboard/sparring', fn() => view('dash.user.sparring'))->name('user.sparring.index');
    Route::get('dashboard/booking', fn() => view('dash.user.booking'))->name('booking.index');

    /** Profile */
    Route::get('dashboard/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('profile/update', [ProfileController::class, 'update'])->name('profile.update');

    /** Favorite */
    Route::get('dashboard/favorite', [FavoriteController::class, 'index'])->name('favorite.index');

    /** Admin: Products */
    Route::prefix('dashboard/products')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('products.index');
        Route::get('/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('/', [ProductController::class, 'store'])->name('products.store');
        Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    });

    /** Admin: Community News */
    Route::prefix('dashboard/comunity')->group(function () {
        Route::get('/', [AdminNewsController::class, 'index'])->name('comunity.index');
        Route::get('/create', [AdminNewsController::class, 'create'])->name('comunity.create');
        Route::post('/', [AdminNewsController::class, 'store'])->name('comunity.store');
        Route::get('/{news}/edit', [AdminNewsController::class, 'edit'])->name('comunity.edit');
        Route::put('/{news}', [AdminNewsController::class, 'update'])->name('comunity.update');
        Route::delete('/{news}', [AdminNewsController::class, 'destroy'])->name('comunity.destroy');
    });

    /** Admin: Guidelines */
    Route::prefix('dashboard/guidelines')->group(function () {
        Route::get('/', [AdminGuidelinesController::class, 'index'])->name('admin.guidelines.index');
        Route::get('/create', [AdminGuidelinesController::class, 'create'])->name('admin.guidelines.create');
        Route::post('/', [AdminGuidelinesController::class, 'store'])->name('admin.guidelines.store');
        Route::get('/{guideline}/edit', [AdminGuidelinesController::class, 'edit'])->name('admin.guidelines.edit');
        Route::put('/{guideline}', [AdminGuidelinesController::class, 'update'])->name('admin.guidelines.update');
        Route::delete('/{guideline}', [AdminGuidelinesController::class, 'destroy'])->name('admin.guidelines.destroy');
    });

    /** Admin: Promo/Vouchers */
    Route::prefix('dashboard/promo')->group(function () {
        Route::get('/', [VoucherController::class, 'index'])->name('promo.index');
        Route::get('/create', [VoucherController::class, 'create'])->name('promo.create');
        Route::post('/', [VoucherController::class, 'store'])->name('promo.store');
        Route::get('/{voucher}/edit', [VoucherController::class, 'edit'])->name('promo.edit');
        Route::put('/{voucher}', [VoucherController::class, 'update'])->name('promo.update');
        Route::delete('/{voucher}', [VoucherController::class, 'destroy'])->name('promo.destroy');
    });

    /** Admin: Venues */
    Route::prefix('dashboard/venue')->group(function () {
        Route::get('/', [AdminVenueController::class, 'index'])->name('venue.index');
        Route::get('/create', [AdminVenueController::class, 'create'])->name('venue.create');
        Route::post('/', [AdminVenueController::class, 'store'])->name('venue.store');
        Route::get('/{venue}/edit', [AdminVenueController::class, 'edit'])->name('venue.edit');
        Route::put('/{venue}', [AdminVenueController::class, 'update'])->name('venue.update');
        Route::delete('/{venue}', [AdminVenueController::class, 'destroy'])->name('venue.destroy');
    });

    /** Venue owner */
    Route::prefix('venue')->group(function () {
        Route::get('/dashboard', [VenueDashboardController::class, 'index'])->name('venue.dashboard');

        // Booking management
        Route::get('/booking', [BookingController::class, 'index'])->name('venue.booking');
        Route::get('/booking/create-table', [BookingController::class, 'createTable'])->name('venue.booking.create-table');
        Route::post('/booking/create-table', [BookingController::class, 'storeTable'])->name('venue.booking.store-table');
        Route::delete('/booking/delete-table/{table}', [BookingController::class, 'deleteTable'])->name('venue.booking.delete-table');

        // Price schedules
        Route::delete('/booking/delete-price-schedule/{priceSchedule}', [PriceScheduleController::class, 'destroy'])->name('price-schedule.destroy');
        Route::get('/booking/create-price-schedule', [PriceScheduleController::class, 'create'])->name('price-schedule.create');
        Route::post('/booking/create-price-schedule', [PriceScheduleController::class, 'store'])->name('price-schedule.store');

        // Promo
        Route::get('/promo', [PromoController::class, 'index'])->name('venue.promo');
        Route::get('/promo/create', [PromoController::class, 'create'])->name('venue.promo.create');
        Route::post('/promo/create', [PromoController::class, 'store'])->name('venue.promo.store');
        Route::delete('/promo/delete/{voucher}', [PromoController::class, 'delete'])->name('venue.promo.delete');

        // Transactions
        Route::get('/transaction', [TransactionController::class, 'index'])->name('venue.transaction');
    });

    /** Admin: Athletes */
    Route::prefix('dashboard/athlete')->group(function () {
        Route::get('/', [AdminAthleteController::class, 'index'])->name('athlete.index');
        Route::get('/create', [AdminAthleteController::class, 'create'])->name('athlete.create');
        Route::post('/', [AdminAthleteController::class, 'store'])->name('athlete.store');
        Route::get('/{athlete}/edit', [AdminAthleteController::class, 'edit'])->name('athlete.edit');
        Route::put('/{athlete}', [AdminAthleteController::class, 'update'])->name('athlete.update');
        Route::delete('/{athlete}', [AdminAthleteController::class, 'destroy'])->name('athlete.destroy');
    });

    /** Athlete area (user) */
    Route::prefix('athlete')->group(function () {
        Route::get('/dashboard', [AthleteDashboardController::class, 'index'])->name('athlete.dashboard');
        Route::get('/sparring/create', function () { return view('dash.athlete.sparring.create'); })->name('athlete.sparring.create');
        Route::get('/match', [MatchHistoryController::class, 'index'])->name('athlete.match');
        Route::get('/match/create', [MatchHistoryController::class, 'create'])->name('athlete.match.create');
        Route::post('/match', [MatchHistoryController::class, 'store'])->name('athlete.match.store');
        Route::get('/calendar/{year}/{month}', [AthleteDashboardController::class, 'getCalendar']);
        Route::get('/match/{id}', [MatchHistoryController::class, 'show'])->name('athlete.match.show');
    });

    Route::prefix('dashboard/partner')->group(function () {
        Route::get('/', fn() => view('dash.admin.partner'))->name('partner.index');
    });

    Route::get('/dashboard/subscriber', [SubscriberController::class, 'index'])->name('dash.admin.subscriber');
    Route::get('/dashboard/opinion', [OpinionController::class, 'index'])->name('dash.admin.opinion');
});

Route::middleware('auth')->group(function () {
    // Detail order & booking
    Route::get('/order/{order}', [OrderController::class, 'showDetailOrder'])->name('order.detail');
    Route::get('/order/booking/{order}', [OrderController::class, 'showDetailBooking'])->name('order.booking');
    Route::get('/order/sparring/{order}', [OrderController::class, 'showDetailSparring'])->name('order.sparring');
});

// RajaOngkir
Route::get('/shipping/provinces', [ShippingController::class, 'getProvinces'])->name('rajaongkir.provinces');
Route::get('/shipping/cities', [ShippingController::class, 'getCities'])->name('rajaongkir.cities');
Route::get('/shipping/districts', [ShippingController::class, 'getDistricts'])->name('rajaongkir.districts');
Route::get('/shipping/subdistricts', [ShippingController::class, 'getSubDistricts'])->name('rajaongkir.subdistricts');
Route::post('/shipping/cost', [ShippingController::class, 'getCost'])->name('rajaongkir.cost');

// Admin Event (dashboard)
Route::middleware(['auth'])->prefix('dashboard/event')->name('admin.event.')->group(function () {
    Route::get('/', [AdminEventController::class, 'index'])->name('index');
    Route::get('/create', [AdminEventController::class, 'create'])->name('create');
    Route::post('/', [AdminEventController::class, 'store'])->name('store');
    Route::get('/{event}/edit', [AdminEventController::class, 'edit'])->name('edit');
    Route::put('/{event}', [AdminEventController::class, 'update'])->name('update');
    Route::delete('/{event}', [AdminEventController::class, 'destroy'])->name('destroy');
    Route::get('/{event}', [AdminEventController::class, 'show'])->name('show');
});

/*
|--------------------------------------------------------------------------
| Tournament & Tree
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->prefix('dashboard/tournament')->name('tournament.')->group(function () {
    Route::get('/', [TournamentController::class, 'index'])->name('index');
    Route::get('/create', [TournamentController::class, 'create'])->name('create');
    Route::post('/', [TournamentController::class, 'store'])->name('store');
    Route::get('/{tournament:slug}/edit', [TournamentController::class, 'edit'])->name('edit');
    Route::put('/{tournament}/{championship}', [TournamentController::class, 'update'])->name('update');
    Route::delete('/{tournament}', [TournamentController::class, 'destroy'])->name('destroy');
});

Route::prefix('championships')->name('tree.')->group(function () {
    Route::get('/', [TreeController::class, 'index'])->name('index');
    Route::post('/{championship}/trees', [TreeController::class, 'store'])->name('store');
    Route::put('/{championship}/trees', [TreeController::class, 'update'])->name('update');
});

/* Company pages */
Route::prefix('company')->group(function () {
    Route::view('/careers', 'careers')->name('company.careers');
    Route::view('/partners', 'partners')->name('company.partners');
    Route::view('/press-media', 'press-media')->name('company.press');
});

/* Blog */
Route::prefix('blog')->group(function () {
    Route::view('/', 'blog.index')->name('blog.index');
    Route::view('/{slug}', 'blog.show')->name('blog.show');
});
