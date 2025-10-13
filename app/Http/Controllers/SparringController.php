<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AthleteDetail;
use App\Models\AthleteReview;
use App\Models\CartItem;
use App\Models\SparringSchedule;
use App\Models\Order;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Str;

class SparringController extends Controller
{
    /**
     * Display a listing of athletes.
     */
    public function index(Request $request)
    {
        // Filter dasar: hanya role athlete yang punya detail
        $query = User::where('roles', 'athlete')
            ->whereHas('athleteDetail')
            ->with('athleteDetail');

        // Filter search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhereHas('athleteDetail', function ($q2) use ($request) {
                      $q2->where('location', 'like', '%' . $request->search . '%');
                  });
            });
        }

        // Filter location
        if ($request->filled('address')) {
            $query->whereHas('athleteDetail', function ($q) use ($request) {
                $q->where('location', $request->address);
            });
        }

        // Filter price range
        if ($request->filled('price_min')) {
            $query->whereHas('athleteDetail', function ($q) use ($request) {
                $q->where('price_per_session', '>=', $request->price_min);
            });
        }
        if ($request->filled('price_max')) {
            $query->whereHas('athleteDetail', function ($q) use ($request) {
                $q->where('price_per_session', '<=', $request->price_max);
            });
        }

        // Ambil data
        $athletes = $query->get();

        // Lokasi unik
        $locations = AthleteDetail::distinct('location')->pluck('location');

        // Harga min dan max (placeholder di filter)
        $minPrice = AthleteDetail::min('price_per_session');
        $maxPrice = AthleteDetail::max('price_per_session');

        $cartProducts = collect();
        $cartVenues = collect();
        $cartSparrings = collect();

        if (auth()->check()) {
            $userId = auth()->id();

            $cartProducts = CartItem::with('product')
                ->where('user_id', $userId)
                ->where('item_type', 'product')
                ->get()
                ->map(fn($item) => [
                    'cart_id'  => $item->id,
                    'product_id' => $item->product?->id,
                    'name'     => $item->product?->name,
                    'brand'    => $item->product?->brand,
                    'category' => $item->product?->category?->name ?? '-',
                    'price'    => $item->product?->pricing,
                    'quantity' => $item->quantity,
                    'total'    => $item->quantity * ($item->product?->pricing ?? 0),
                    'discount' => $item->product?->discount ?? 0,
                    'images'    => $item->product?->images[0] ?? null,
                ]);

            $cartVenues = CartItem::with('venue')
                ->where('user_id', $userId)
                ->where('item_type', 'venue')
                ->get()
                ->map(fn($item) => [
                    'cart_id' => $item->id,
                    'venue_id' => $item->venue?->id,
                    'name'     => $item->venue?->name,
                    'address'  => $item->venue?->address ?? '-',
                    'date'     => $item->date,
                    'start'    => $item->start,
                    'end'      => $item->end,
                    'table'    => $item->table_number,
                    'price'    => $item->price,
                    'duration' => $item->start && $item->end
                        ? gmdate('H:i', strtotime($item->end) - strtotime($item->start))
                        : null,
                ]);

            $cartSparrings = CartItem::with(['sparringSchedule.athlete'])
                ->where('user_id', $userId)
                ->where('item_type', 'sparring')
                ->get()
                ->map(fn($item) => [
                    'cart_id'     => $item->id,
                    'schedule_id' => $item->sparringSchedule?->id,
                    'athlete_name' => $item->sparringSchedule?->athlete?->name ?? 'Unknown Athlete',
                    'athlete_image' => $item->sparringSchedule?->athlete?->athleteDetail?->image ?? null,
                    'date'        => $item->date,
                    'start'       => $item->start,
                    'end'         => $item->end,
                    'price'       => $item->price,
                ]);
        }

        return view('public.sparring.index', compact(
            'athletes',
            'cartProducts',
            'cartVenues',
            'cartSparrings',
            'locations',
            'minPrice',
            'maxPrice'
        ));
    }

    /**
     * Display the specified athlete (dengan slug). Redirect 301 ke URL kanonik kalau slug salah/hilang.
     */
    public function show(Request $request, $id, $slug = null)
    {
        $athlete = User::where('roles', 'athlete')
            ->where('id', $id)
            ->with('athleteDetail')
            ->firstOrFail();

        // Pastikan slug kanonik
        $expectedSlug = Str::slug($athlete->name ?? 'athlete');
        if ($slug !== $expectedSlug) {
            return redirect()->route('sparring.detail', ['id' => $id, 'slug' => $expectedSlug], 301);
        }

        // Ambil semua schedule yang belum booked
        $schedules = SparringSchedule::where('athlete_id', $id)
            ->where('is_booked', false)
            ->where('date', '>=', now()->toDateString())
            ->orderBy('date')
            ->orderBy('start_time')
            ->get()
            ->map(fn($item) => [
                'id'         => $item->id,
                'athlete_id' => $item->athlete_id,
                'date' => \Carbon\Carbon::parse($item->date)->format('Y-m-d'),
                'start_time' => \Carbon\Carbon::parse($item->start_time)->format('H:i'),
                'end_time' => \Carbon\Carbon::parse($item->end_time)->format('H:i'),
                'is_booked' => $item->is_booked,
            ]);

        // Ambil tanggal unik untuk date picker
        $availableDates = $schedules->pluck('date')->unique()->values();

        // Reviews
        $reviews = AthleteReview::where('athlete_id', $athlete->id)
            ->with('user')
            ->latest()
            ->get();

        $totalReviews  = $reviews->count();
        $averageRating = $totalReviews ? round($reviews->avg('rating'), 1) : 0.0;

        $counts  = [];
        $percents = [];
        for ($i = 5; $i >= 1; $i--) {
            $counts[$i]   = $reviews->where('rating', $i)->count();
            $percents[$i] = $totalReviews ? round($counts[$i] / $totalReviews * 100) : 0;
        }

        $alreadyReviewed = auth()->check()
            ? AthleteReview::where('athlete_id', $athlete->id)->where('user_id', auth()->id())->exists()
            : false;

        $userHasSparringOrder = false;
        if (auth()->check()) {
            $userHasSparringOrder = Order::where('user_id', auth()->id())
                ->whereHas('orderSparrings', function ($query) use ($athlete) {
                    $query->where('athlete_id', $athlete->id);
                })
                ->exists();
        }

        $cartProducts = collect();
        $cartVenues = collect();
        $cartSparrings = collect();

        if (auth()->check()) {
            $userId = auth()->id();

            $cartProducts = CartItem::with('product')
                ->where('user_id', $userId)
                ->where('item_type', 'product')
                ->get()
                ->map(fn($item) => [
                    'cart_id'  => $item->id,
                    'product_id' => $item->product?->id,
                    'name'     => $item->product?->name,
                    'brand'    => $item->product?->brand,
                    'category' => $item->product?->category?->name ?? '-',
                    'price'    => $item->product?->pricing,
                    'quantity' => $item->quantity,
                    'total'    => $item->quantity * ($item->product?->pricing ?? 0),
                    'discount' => $item->product?->discount ?? 0,
                    'images'    => $item->product?->images[0] ?? null,
                ]);

            $cartVenues = CartItem::with('venue')
                ->where('user_id', $userId)
                ->where('item_type', 'venue')
                ->get()
                ->map(fn($item) => [
                    'cart_id' => $item->id,
                    'venue_id' => $item->venue?->id,
                    'name'     => $item->venue?->name,
                    'address'  => $item->venue?->address ?? '-',
                    'date'     => $item->date,
                    'start'    => $item->start,
                    'end'      => $item->end,
                    'table'    => $item->table_number,
                    'price'    => $item->price,
                    'duration' => $item->start && $item->end
                        ? gmdate('H:i', strtotime($item->end) - strtotime($item->start))
                        : null,
                ]);

            $cartSparrings = CartItem::with(['sparringSchedule.athlete'])
                ->where('user_id', $userId)
                ->where('item_type', 'sparring')
                ->get()
                ->map(fn($item) => [
                    'cart_id'     => $item->id,
                    'schedule_id' => $item->sparringSchedule?->id,
                    'athlete_name' => $item->sparringSchedule?->athlete?->name ?? 'Unknown Athlete',
                    'athlete_image' => $item->sparringSchedule?->athlete?->athleteDetail?->image ?? null,
                    'date'        => $item->date,
                    'start'       => $item->start,
                    'end'         => $item->end,
                    'price'       => $item->price,
                ]);
        }
        
        return view('public.sparring.detail', compact(
            'athlete',
            'schedules',
            'availableDates',
            'cartProducts',
            'cartVenues',
            'cartSparrings',
            'reviews',
            'averageRating',
            'totalReviews',
            'counts',
            'percents',
            'alreadyReviewed',
            'userHasSparringOrder'
        ));
    }

    /**
     * Store review atlet (gunakan tabel athlete_reviews)
     */
    public function storeReview(Request $request, $id)
    {
        $request->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:2000',
        ]);

        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Silakan login untuk memberi review.');
        }

        $existing = AthleteReview::where('athlete_id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if ($existing) {
            return back()->with('error', 'Anda sudah pernah memberi review untuk atlet ini.');
        }

        AthleteReview::create([
            'athlete_id' => (int) $id,
            'user_id'    => Auth::id(),
            'rating'     => (int) $request->rating,
            'comment'    => $request->comment,
        ]);

        return back()->with('success', 'Review berhasil dikirim.');
    }

    public function addToCart(Request $request)
    {
        // Implementasi sesuai project-mu
        return back();
    }

    public function removeFromCart(Request $request)
    {
        // Implementasi sesuai project-mu
        return back();
    }
}
