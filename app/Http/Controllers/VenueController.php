<?php

namespace App\Http\Controllers;

use App\Models\Venue;
use Illuminate\Http\Request;

class VenueController extends Controller
{
    public function index(Request $request)
    {
        $venues = Venue::query()
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->when($request->address, fn($q) => $q->where('address', $request->address))
            ->when($request->price_min, fn($q) => $q->where('price', '>=', $request->price_min))
            ->when($request->price_max, fn($q) => $q->where('price', '<=', $request->price_max))
            ->orderBy('created_at', 'desc')
            ->paginate(2)
            ->withQueryString();

        return view('public.venue.index', compact('venues'));
    }

}