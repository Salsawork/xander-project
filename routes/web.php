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
use App\Http\Controllers\Auth\GoogleController;

// Venue Controllers
use App\Http\Controllers\venueController\DashboardController as VenueDashboardController; 
use App\Http\Controllers\venueController\BookingController;
use App\Http\Controllers\venueController\PromoController;
use App\Http\Controllers\venueController\PriceScheduleController;
use App\Http\Controllers\venueController\TransactionController;
use App\Http\Controllers\FavoriteController;

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

// Google OAuth Routes
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
    Route::get('/{product}', [ProductController::class, 'detail'])->name('products.detail');
});

Route::prefix('venues')->group(function () {
    Route::get('/', [VenueController::class, 'index'])->name('venues.index');
    Route::get('/{venue}', [VenueController::class, 'showDetail'])->name('venues.detail');
    Route::get('/venues/{venueId}/price-schedules', [VenueController::class, 'detail'])->name('venues.priceSchedules');
    Route::post('/{venue}/favorite', [FavoriteController::class, 'toggle'])->name('venues.favorite');
});

/** Events */
Route::get('/events', [EventController::class, 'index'])->name('events.index');
Route::get('/event/{event:name}', [EventController::class, 'show'])->name('events.show');
Route::get('/event/{event:name}/bracket', [EventController::class, 'bracket'])->name('events.bracket');

/** Sparring */
Route::get('/sparring', [SparringController::class, 'index'])->name('sparring.index');
Route::get('/sparring/{id}', [SparringController::class, 'show'])->name('sparring.detail');
Route::post('/sparring/add-to-cart', [SparringController::class, 'addToCart'])->name('sparring.addToCart');
Route::delete('/sparring/remove-from-cart', [SparringController::class, 'removeFromCart'])->name('sparring.removeFromCart');
Route::post('/sparring/{id}/reviews', [SparringController::class, 'storeReview'])->name('sparring.review.store');

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

/*
|--------------------------------------------------------------------------
| Cart & Checkout
|--------------------------------------------------------------------------
*/
Route::prefix('cart')->group(function () {
    Route::post('/add/product', [CartController::class, 'addProductToCart'])->name('cart.add.product');
    Route::post('/add/venue', [CartController::class, 'addVenueToCart'])->name('cart.add.venue');
    Route::post('/add/sparring', [CartController::class, 'addSparringToCart'])->name('cart.add.sparring');
    Route::post('/del/product', [CartController::class, 'removeProductFromCart'])->name('cart.del.product');
    Route::post('/del/venue', [CartController::class, 'removeVenueFromCart'])->name('cart.del.venue');
    Route::post('/del/sparring', [CartController::class, 'removeSparringFromCart'])->name('cart.del.sparring');
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
    Route::get('/', [DashboardOrderController::class, 'index'])->name('order.index');
    Route::get('/detail/{order?}', [DashboardOrderController::class, 'detail'])->name('order.detail.index');
    Route::delete('/delete/{order}', [DashboardOrderController::class, 'destroy'])->name('admin.orders.delete');
    Route::get('/update-status/{order}', [DashboardOrderController::class, 'updateStatus'])->name('admin.orders.update-status');
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
    Route::get('dashboard/booking', fn() => view('dash.user.booking'))->name('booking.index');

    Route::post('profile/update', function (Request $request) {
        Auth::user()->update($request->only('name', 'email'));
        return back()->with('success', 'Profile updated successfully');
    })->name('profile.update');

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

    Route::prefix('dashboard/promo')->group(function () {
        Route::get('/', [VoucherController::class, 'index'])->name('promo.index');
        Route::get('/create', [VoucherController::class, 'create'])->name('promo.create');
        Route::post('/', [VoucherController::class, 'store'])->name('promo.store');
        Route::get('/{voucher}/edit', [VoucherController::class, 'edit'])->name('promo.edit');
        Route::put('/{voucher}', [VoucherController::class, 'update'])->name('promo.update');
        Route::delete('/{voucher}', [VoucherController::class, 'destroy'])->name('promo.destroy');
    });
    Route::prefix('dashboard/venue')->group(function () {
        Route::get('/', [AdminVenueController::class, 'index'])->name('venue.index');
        Route::get('/create', [AdminVenueController::class, 'create'])->name('venue.create');
        Route::post('/', [AdminVenueController::class, 'store'])->name('venue.store');
        Route::get('/{venue}/edit', [AdminVenueController::class, 'edit'])->name('venue.edit');
        Route::put('/{venue}', [AdminVenueController::class, 'update'])->name('venue.update');
        Route::delete('/{venue}', [AdminVenueController::class, 'destroy'])->name('venue.destroy');
        
    });

    Route::middleware('auth')->prefix('venue')->group(function () {
        // Venue Dashboard
        Route::get('/dashboard', [VenueDashboardController::class, 'index'])->name('venue.dashboard');
        // Venue Booking Management
        Route::get('/booking', [BookingController::class, 'index'])->name('venue.booking');
        // Create table
        Route::get('/booking/create-table', [BookingController::class, 'createTable'])->name('venue.booking.create-table');
        Route::post('/booking/create-table', [BookingController::class, 'storeTable'])->name('venue.booking.store-table');
        // Delete table
        Route::delete('/booking/delete-table/{table}', [BookingController::class, 'deleteTable'])->name('venue.booking.delete-table');
        // Venue price-schedule.destroy
        Route::delete('/booking/delete-price-schedule/{priceSchedule}', [PriceScheduleController::class, 'destroy'])->name('price-schedule.destroy');
        // price-schedule.create
        Route::get('/booking/create-price-schedule', [PriceScheduleController::class, 'create'])->name('price-schedule.create');
        // price-schedule.store
        Route::post('/booking/create-price-schedule', [PriceScheduleController::class, 'store'])->name('price-schedule.store');
        // Venue Promo Management
        Route::get('/promo', [PromoController::class, 'index'])->name('venue.promo');
        // Create Promo
        Route::get('/promo/create', [PromoController::class, 'create'])->name('venue.promo.create');
        // Store Promo
        Route::post('/promo/create', [PromoController::class, 'store'])->name('venue.promo.store');
        Route::delete('/promo/delete/{voucher}', [PromoController::class, 'delete'])->name('venue.promo.delete');
        // Venue Transaction History
        Route::get('/transaction', [TransactionController::class, 'index'])->name('venue.transaction');
    });


    Route::prefix('dashboard/athlete')->group(function () {
        Route::get('/', [AdminAthleteController::class, 'index'])->name('athlete.index');
        Route::get('/create', [AdminAthleteController::class, 'create'])->name('athlete.create');
        Route::post('/', [AdminAthleteController::class, 'store'])->name('athlete.store');
        Route::get('/{athlete}/edit', [AdminAthleteController::class, 'edit'])->name('athlete.edit');
        Route::put('/{athlete}', [AdminAthleteController::class, 'update'])->name('athlete.update');
        Route::delete('/{athlete}', [AdminAthleteController::class, 'destroy'])->name('athlete.destroy');
    });

    Route::prefix('athlete')->group(function () {
        // Athlete Dashboard
        Route::get('/dashboard', [AthleteDashboardController::class, 'index'])->name('athlete.dashboard');
    
        // Athlete Sparring - Create Session
        Route::get('/sparring/create', function () {
            return view('dash.athlete.sparring.create');
        })->name('athlete.sparring.create');
    
    
        // Athlete Match History (BARU)
        Route::get('/match', [MatchHistoryController::class, 'index'])->name('athlete.match');
        // Create Session
        Route::get('/match/create', [MatchHistoryController::class, 'create'])->name('athlete.match.create');
        Route::post('/match', [MatchHistoryController::class, 'store'])->name('athlete.match.store');
    
        // Athlete Calendar
        Route::get('/calendar/{year}/{month}', [AthleteDashboardController::class, 'getCalendar']);
    
        // Athlete Match History (BARU)
        Route::get('/match/{id}', [MatchHistoryController::class, 'show'])->name('athlete.match.show');
    });

    Route::prefix('dashboard/partner')->group(function () {
        Route::get('/', function () {
            return view('dash.admin.partner');
        })->name('partner.index');
    });
});

/*
|--------------------------------------------------------------------------
| Tournament & Tree
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->prefix('dashboard/tournament')->group(function () {
    Route::get('/', [TournamentController::class, 'index'])->name('tournament.index');
    Route::get('/create', [TournamentController::class, 'create'])->name('tournament.create');
    Route::post('/', [TournamentController::class, 'store'])->name('tournament.store');
    Route::get('/{tournament}/edit', [TournamentController::class, 'edit'])->name('tournament.edit');
    Route::put('/{tournament}/{championship}', [TournamentController::class, 'update'])->name('tournament.update');
    Route::delete('/{tournament}', [TournamentController::class, 'destroy'])->name('tournament.destroy');
});

/** Tree (Public) */
Route::get('/championships', [TreeController::class, 'index'])->name('tree.index');
Route::post('/championships/{championship}/trees', [TreeController::class, 'store'])->name('tree.store');
Route::put('/championships/{championship}/trees', [TreeController::class, 'update'])->name('tree.update');