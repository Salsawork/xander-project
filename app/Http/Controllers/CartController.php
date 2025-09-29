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
        ];

        return redirect()->back()->withCookie(cookie('cartProducts', json_encode($cartProduct), 60 * 24 * 7));
    }

    public function addVenueToCart(Request $request)
    {
        $cartVenue = json_decode($request->cookie('cartVenues') ?? '[]', true);
        $venue = Venue::findOrFail($request->id);

        $date = date('d-m-Y', strtotime($request->date));
        $schedule = $request->schedule;

        // Cek apakah ada venue dengan id + date + schedule yang sama
        $exists = collect($cartVenue)->contains(function ($item) use ($venue, $date, $schedule) {
            return $item['id'] === $venue->id && $item['date'] === $date && $item['schedule'] === $schedule;
        });

        if (!$exists) {
            $cartVenue[] = [
                'id' => $venue->id,
                'name' => $venue->name,
                'price' => $venue->price,
                'date' => $date,
                'schedule' => $schedule,
            ];
        }

        return redirect()->back()->withCookie(cookie('cartVenues', json_encode($cartVenue), 60 * 24 * 7));
    }

    public function addSparringToCart(Request $request)
    {
        // Validasi request
        $request->validate([
            'athlete_id' => 'required|exists:users,id',
            'schedule_id' => 'required|exists:sparring_schedules,id',
        ]);

        // Ambil data atlet dan jadwal
        $athlete = User::where('id', $request->athlete_id)
            ->with('athleteDetail')
            ->firstOrFail();

        $schedule = SparringSchedule::where('id', $request->schedule_id)
            ->where('is_booked', false)
            ->firstOrFail();

        // Ambil data sparring dari cookie
        $sparrings = [];
        if (Cookie::has('cartSparring')) {
            $sparringData = Cookie::get('cartSparring');
            $sparrings = is_array($sparringData) ? $sparringData : json_decode($sparringData, true) ?? [];
        }

        // Buat data sparring baru
        $newSparring = [
            'athlete_id' => $athlete->id,
            'name' => $athlete->name,
            'image' => $athlete->athleteDetail->image ?? null,
            'schedule_id' => $schedule->id,
            'schedule' => date('d M Y', strtotime($schedule->date)) . ' ' . date('H:i', strtotime($schedule->start_time)) . '-' . date('H:i', strtotime($schedule->end_time)),
            'price' => $athlete->athleteDetail->price_per_session,
        ];

        // Cek apakah sparring dengan schedule_id yang sama sudah ada di cart
        $existingIndex = array_search($schedule->id, array_column($sparrings, 'schedule_id'));

        if ($existingIndex !== false) {
            // Update sparring yang sudah ada
            $sparrings[$existingIndex] = $newSparring;
        } else {
            // Tambahkan sparring baru
            $sparrings[] = $newSparring;
        }

        // Simpan kembali ke cookie
        Cookie::queue('cartSparrings', json_encode($sparrings), 60 * 24 * 7); // 1 minggu

        return redirect()->back()->with('success', 'Sparring session added to cart');
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
