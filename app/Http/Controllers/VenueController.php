<?php

namespace App\Http\Controllers;

use App\Models\Venue;
use App\Models\BilliardSession;
use Illuminate\Http\Request;

class VenueController extends Controller
{
    public function index(Request $request)
    {
        $favorites = collect(explode(',', $request->favorites ?? ''))
            ->filter()
            ->map(fn($id) => (int) $id)
            ->toArray();
    
            $venues = Venue::query()
            // 🔍 Search
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($sub) use ($request) {
                    $sub->where('name', 'like', "%{$request->search}%")
                        ->orWhere('address', 'like', "%{$request->search}%")
                        ->orWhere('description', 'like', "%{$request->search}%");
                });
            })
            // 📍 Filter lokasi (kalau dipilih di radio)
            ->when($request->address, fn ($q) => $q->where('address', $request->address))
            // 💰 Range harga
            ->when($request->price_min, fn ($q) => $q->where('price', '>=', $request->price_min))
            ->when($request->price_max, fn ($q) => $q->where('price', '<=', $request->price_max))
            // ⭐ Prioritas favorit
            ->when(! empty($favorites), function ($q) use ($favorites) {
                $ids = implode(',', $favorites);
                $q->orderByRaw("FIELD(id, $ids) DESC");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(5)
            ->withQueryString();

        $cartProducts = json_decode($request->cookie('cartProduct') ?? '[]', true);
        $cartVenues = json_decode($request->cookie('cartVenues') ?? '[]', true);
        $cartSparrings = json_decode($request->cookie('cartsparring') ?? '[]', true);
        $addresses = Venue::select('address')
            ->distinct()
            ->pluck('address');
        return view('public.venue.index', compact('venues', 'cartProducts', 'cartVenues', 'cartSparrings', 'addresses'));
    }


    public function detail(Request $request, $venueId)
    {
        // Ambil detail venue
        $detail = Venue::findOrFail($venueId);
    
        // Ambil semua session billiard yang masih available
        $sessions = BilliardSession::where('venue_id', $detail->id)
            ->where('status', 'confirmed')
            ->whereBetween('date', [now()->toDateString(), now()->addDays(3)->toDateString()])
            ->orderBy('date')
            ->orderBy('start_time')
            ->get()
            ->map(fn($item) => [
                'id' => $item->id,
                'venue_id' => $item->venue_id,
                'title' => $item->title,
                'game_type' => $item->game_type,
                'skill_level' => $item->skill_level,
                'price' => $item->price,
                'date' => \Carbon\Carbon::parse($item->date)->format('Y-m-d'),
                'start_time' => \Carbon\Carbon::parse($item->start_time)->format('H:i'),
                'end_time' => \Carbon\Carbon::parse($item->end_time)->format('H:i'),
                'promo_code' => $item->promo_code,
                'status' => $item->status,
            ]);
    
        // Ambil tanggal unik untuk date picker
        $availableDates = $sessions->pluck('date')->unique();
    
        // Cart
        $cartProducts = json_decode($request->cookie('cartProducts') ?? '[]', true);
        $cartVenues = json_decode($request->cookie('cartVenues') ?? '[]', true);
        $cartSparrings = json_decode($request->cookie('cartSparrings') ?? '[]', true);
    
        return view('public.venue.detail', compact(
            'detail',
            'sessions',
            'availableDates',
            'cartProducts',
            'cartVenues',
            'cartSparrings'
        ));
    }
    
}
