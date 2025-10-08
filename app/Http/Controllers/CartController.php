<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\SparringSchedule;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Support\Facades\Cookie;

class CartController extends Controller
{
    public function addProductToCart(Request $request)
    {
        $cartProduct = json_decode($request->cookie('cartProducts') ?? '[]', true);
        $product = Product::findOrFail($request->id);

        $cartProduct[] = [
            'id' => $product->id,
            'name' => $product->name,
            'image' => $product->images[0] ?? null,
            'price' => $product->pricing,
            'weight' => $product->weight,
        ];

        Cookie::queue('cartProducts', json_encode($cartProduct), 60 * 24 * 7);

        return response()->json([
            'success' => true,
            'message' => 'Product added to cart',
            'cartCount' => count($cartProduct),
            'products' => $cartProduct,
        ]);
    }
    
    public function addVenueToCart(Request $request)
    {
        $cartVenues = json_decode($request->cookie('cartVenues') ?? '[]', true);
        $venue = Venue::findOrFail($request->id);

        $date = date('d-m-Y', strtotime($request->date));
        $startTime = $request->input('schedule.start');
        $endTime   = $request->input('schedule.end');
        $price = $request->input('price');
        $table = $request->input('table');

        $exists = collect($cartVenues)->contains(function ($item) use ($date, $startTime) {
            return isset($item['date'], $item['start']) && $item['date'] === $date && $item['start'] === $startTime;
        });

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => "Venue with start time {$startTime} on {$date} already in cart",
            ], 400);
        }

        $cartVenues[] = [
            'id'    => $venue->id,
            'name'  => $venue->name,
            'price' => $price,
            'date'  => $date,
            'start' => $startTime,
            'end'   => $endTime,
            'table' => $table,
        ];

        Cookie::queue('cartVenues', json_encode($cartVenues), 60 * 24 * 7);

        return response()->json([
            'success' => true,
            'message' => 'Venue added to cart',
            'cartCount' => count($cartVenues),
            'venues' => $cartVenues,
        ]);
    }

    public function addSparringToCart(Request $request)
    {
        $request->validate([
            'athlete_id' => 'required|exists:users,id',
            'schedule_id' => 'required|exists:sparring_schedules,id',
        ]);

        $athlete = User::where('id', $request->athlete_id)
            ->with('athleteDetail')
            ->firstOrFail();

        $schedule = SparringSchedule::where('id', $request->schedule_id)
            ->where('is_booked', false)
            ->firstOrFail();

        $sparrings = [];
        if (Cookie::has('cartSparrings')) {
            $sparringData = Cookie::get('cartSparrings');
            $sparrings = is_array($sparringData) ? $sparringData : json_decode($sparringData, true) ?? [];
        }

        $exists = collect($sparrings)->contains(function ($item) use ($schedule) {
            return $item['schedule_id'] == $schedule->id;
        });

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Sparring session with the same schedule already in cart'
            ], 400);
        }

        $newSparring = [
            'athlete_id' => $athlete->id,
            'name' => $athlete->name,
            'image' => $athlete->athleteDetail->image ?? null,
            'schedule_id' => $schedule->id,
            'schedule' => date('d M Y', strtotime($schedule->date)) . ' ' . date('H:i', strtotime($schedule->start_time)) . '-' . date('H:i', strtotime($schedule->end_time)),
            'price' => $athlete->athleteDetail->price_per_session,
        ];

        $sparrings[] = $newSparring;
        Cookie::queue('cartSparrings', json_encode($sparrings), 60 * 24 * 7);

        return response()->json([
            'success' => true,
            'message' => 'Sparring session added to cart',
            'cartCount' => count($sparrings),
            'sparrings' => $sparrings,
        ]);
    }


    public function removeProductFromCart(Request $request)
    {
        $cartProducts = json_decode($request->cookie('cartProducts') ?? '[]', true);
        $cartProduct = array_values(array_filter($cartProducts, fn($item) => $item['id'] !== (int) $request->id));

        return redirect()->back()->withCookie(cookie('cartProducts', json_encode($cartProduct), 60 * 24 * 7));
    }
    public function removeVenueFromCart(Request $request)
    {
        $cartVenues = json_decode($request->cookie('cartVenues') ?? '[]', true);
        $cartVenue = array_values(array_filter($cartVenues, fn($item) => $item['id'] !== (int) $request->id));

        return redirect()->back()->withCookie(cookie('cartVenues', json_encode($cartVenue), 60 * 24 * 7));
    }

    public function removeSparringFromCart(Request $request)
    {
        // Validasi request
        $request->validate([
            'schedule_id' => 'required|exists:sparring_schedules,id',
        ]);

        // Ambil data sparring dari cookie
        $sparrings = [];
        if (Cookie::has('cartSparrings')) {
            $sparringData = Cookie::get('cartSparrings');
            $sparrings = is_array($sparringData) ? $sparringData : json_decode($sparringData, true) ?? [];
        }

        // Hapus sparring dengan schedule_id yang sesuai
        $sparrings = array_filter($sparrings, function ($sparring) use ($request) {
            return $sparring['schedule_id'] != $request->schedule_id;
        });

        // Reset array keys
        $sparrings = array_values($sparrings);

        // Simpan kembali ke cookie
        Cookie::queue('cartSparrings', json_encode($sparrings), 60 * 24 * 7); // 1 minggu

        return redirect()->back()->with('success', 'Sparring session removed from cart');
    }
}
