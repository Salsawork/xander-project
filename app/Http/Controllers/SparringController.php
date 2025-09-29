<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AthleteDetail;
use App\Models\AthleteReview;
use App\Models\SparringSchedule;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

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

        // Harga min dan max (supaya bisa jadi placeholder di filter)
        $minPrice = AthleteDetail::min('price_per_session');
        $maxPrice = AthleteDetail::max('price_per_session');

        // Data cart dari cookie
        $cartProducts = [];
        if (Cookie::has('cartProducts')) {
            $cartData = Cookie::get('cartProducts');
            $cartProducts = is_array($cartData) ? $cartData : json_decode($cartData, true) ?? [];
        }

        $cartVenues = [];
        if (Cookie::has('cartVenues')) {
            $cartData = Cookie::get('cartVenues');
            $cartVenues = is_array($cartData) ? $cartData : json_decode($cartData, true) ?? [];
        }

        $cartSparrings = [];
        if (Cookie::has('cartSparrings')) {
            $sparringData = Cookie::get('cartSparrings');
            $cartSparrings = is_array($sparringData) ? $sparringData : json_decode($sparringData, true) ?? [];
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
     * Display the specified athlete.
     */
    // public function show($id)
    // {
    //     // Ambil data atlet berdasarkan ID
    //     $athlete = User::where('roles', 'athlete')
    //         ->where('id', $id)
    //         ->with('athleteDetail')
    //         ->firstOrFail();

    //     // Ambil jadwal sparring yang tersedia untuk atlet ini
    //     $schedules = SparringSchedule::where('athlete_id', $id)
    //         ->where('is_booked', false)
    //         ->where('date', '>=', now()->format('Y-m-d'))
    //         ->orderBy('date')
    //         ->orderBy('start_time')
    //         ->limit(9) // Batasi hanya 9 schedule
    //         ->get();

    //     // Ambil data cart dari cookie
    //     $carts = [];
    //     if (Cookie::has('cart')) {
    //         $cartData = Cookie::get('cart');
    //         $carts = is_array($cartData) ? $cartData : json_decode($cartData, true) ?? [];
    //     }

    //     // Ambil data sparring dari cookie
    //     $sparrings = [];
    //     if (Cookie::has('sparring')) {
    //         $sparringData = Cookie::get('sparring');
    //         $sparrings = is_array($sparringData) ? $sparringData : json_decode($sparringData, true) ?? [];
    //     }

    //     return view('dash.sparring.detail', compact('athlete', 'schedules', 'carts', 'sparrings'));
    // }
    // public function show($id)
    // {
    //     $athlete = User::where('roles', 'athlete')
    //         ->where('id', $id)
    //         ->with('athleteDetail')
    //         ->firstOrFail();

    //     $schedules = SparringSchedule::where('athlete_id', $id)
    //         ->where('is_booked', false)
    //         ->where('date', '>=', now()->format('Y-m-d'))
    //         ->orderBy('date')
    //         ->orderBy('start_time')
    //         ->limit(9)
    //         ->get();

    //     // --- AthleteReview section ---
    //     $reviews = AthleteReview::where('athlete_id', $athlete->id)
    //         ->with('user')
    //         ->latest()
    //         ->get();

    //     $totalReviews = $reviews->count();
    //     $averageRating = $totalReviews ? round($reviews->avg('rating'), 1) : 0.0;

    //     $counts = [];
    //     $percents = [];
    //     for ($i = 5; $i >= 1; $i--) {
    //         $counts[$i] = $reviews->where('rating', $i)->count();
    //         $percents[$i] = $totalReviews ? round($counts[$i] / $totalReviews * 100) : 0;
    //     }

    //     // --- Check apakah user sudah kasih review ---
    //     $alreadyReviewed = false;
    //     if (auth()->check()) {
    //         $alreadyReviewed = AthleteReview::where('athlete_id', $athlete->id)
    //             ->where('user_id', auth()->id())
    //             ->exists();
    //     }

    //     // Cart & sparrings dari cookie
    //     $cartProducts = [];
    //     if (Cookie::has('cartProducts')) {
    //         $cartData = Cookie::get('cartProducts');
    //         $cartProducts = is_array($cartData) ? $cartData : json_decode($cartData, true) ?? [];
    //     }

    //     $cartVenues = [];
    //     if (Cookie::has('cartVenues')) {
    //         $cartData = Cookie::get('cartVenues');
    //         $cartVenues = is_array($cartData) ? $cartData : json_decode($cartData, true) ?? [];
    //     }

    //     $cartSparrings = [];
    //     if (Cookie::has('cartSparrings')) {
    //         $sparringData = Cookie::get('cartSparrings');
    //         $cartSparrings = is_array($sparringData) ? $sparringData : json_decode($sparringData, true) ?? [];
    //     }

    //     return view('public.sparring.detail', compact(
    //         'athlete',
    //         'schedules',
    //         'cartProducts',
    //         'cartVenues',
    //         'cartSparrings',
    //         'reviews',
    //         'averageRating',
    //         'totalReviews',
    //         'counts',
    //         'percents',
    //         'alreadyReviewed'
    //     ));
    // }

    public function show($id)
    {
        $athlete = User::where('roles', 'athlete')
            ->where('id', $id)
            ->with('athleteDetail')
            ->firstOrFail();

        // Ambil semua schedule yang belum booked
        $schedules = SparringSchedule::where('athlete_id', $id)
            ->where('is_booked', false)
            ->whereBetween('date', [now()->toDateString(), now()->addDays(3)->toDateString()])
            ->orderBy('date')
            ->orderBy('start_time')
            ->get()
            ->map(fn($item) => [
                'id' => $item->id,
                'athlete_id' => $item->athlete_id,
                'date' => \Carbon\Carbon::parse($item->date)->format('Y-m-d'), 
                'start_time' => \Carbon\Carbon::parse($item->start_time)->format('H:i'), 
                'end_time' => \Carbon\Carbon::parse($item->end_time)->format('H:i'), 
                'is_booked' => $item->is_booked,
            ]);

        // Ambil tanggal unik untuk date picker
        $availableDates = $schedules->pluck('date')->unique();

        // --- Review dan cart tetap seperti sebelumnya ---
        $reviews = AthleteReview::where('athlete_id', $athlete->id)
            ->with('user')
            ->latest()
            ->get();

        $totalReviews = $reviews->count();
        $averageRating = $totalReviews ? round($reviews->avg('rating'), 1) : 0.0;

        $counts = [];
        $percents = [];
        for ($i = 5; $i >= 1; $i--) {
            $counts[$i] = $reviews->where('rating', $i)->count();
            $percents[$i] = $totalReviews ? round($counts[$i] / $totalReviews * 100) : 0;
        }

        $alreadyReviewed = auth()->check()
            ? AthleteReview::where('athlete_id', $athlete->id)->where('user_id', auth()->id())->exists()
            : false;

        $cartProducts = json_decode(Cookie::get('cartProducts') ?? '[]', true);
        $cartVenues = json_decode(Cookie::get('cartVenues') ?? '[]', true);
        $cartSparrings = json_decode(Cookie::get('cartSparrings') ?? '[]', true);

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
            'alreadyReviewed'
        ));
    }

    public function storeReview(Request $request, $id)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
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
            'athlete_id' => $id,
            'user_id' => Auth::id(),
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return back()->with('success', 'AthleteReview berhasil dikirim.');
    }
}
