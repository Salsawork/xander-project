<?php

namespace App\Http\Controllers;

use App\Models\Venue;
use Illuminate\Http\Request;

class VenueController extends Controller
{
    public function index(Request $request)
    {
        $favorites = $request->favorites
            ? explode(',', $request->favorites)
            : [];
    
        $venues = Venue::query()
            // 🔍 Search bisa ke name, address, description
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
    
        $carts = json_decode($request->cookie('cart') ?? '[]', true);
        $sparrings = json_decode($request->cookie('sparring') ?? '[]', true);
        $addresses = Venue::select('address')
        ->distinct()
        ->pluck('address');
        return view('public.venue.index', compact('venues', 'carts', 'sparrings', 'addresses'));
    }
    

    public function detail(Request $request, $venue)
    {
        $detail = Venue::findOrFail($venue);
        $carts = json_decode($request->cookie('cart') ?? '[]', true);
        $sparrings = json_decode($request->cookie('sparring') ?? '[]', true);

        return view('public.venue.detail', compact('detail', 'carts', 'sparrings'));
    }
}
