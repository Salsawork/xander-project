<?php

namespace App\Http\Controllers\venueController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Venue;
use App\Models\Booking;
use App\Models\Review;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\VenuesExport;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Ambil venue berdasarkan user login
        $venue = Venue::where('user_id', Auth::id())->first();

        if (!$venue) {
            return view('dash.venue.dashboard', [
                'venue' => null,
                'monthlyEarnings' => 0,
                'lastMonthEarnings' => 0,
                'percentageChange' => 0,
                'salesData' => [],
                'recentTransactions' => collect(),
                'recentReviews' => collect(),
                'averageRating' => 0,
                'totalRatings' => 0,
                'sessionPurchased' => 0,
                'lastYearSessions' => 0,
                'sessionPercentageChange' => 0,
            ]);
        }

        // ============================================
        // 📊 Earnings (bulan ini & bulan lalu)
        // ============================================
        $now = Carbon::now();

        // 💰 Total bulan ini
        $monthlyEarnings = Booking::where('venue_id', $venue->id)
        ->where('status', 'completed') // hanya transaksi selesai
        ->whereBetween('booking_date', [$now->copy()->startOfMonth()->toDateString(), $now->copy()->endOfMonth()->toDateString()])
        ->sum('price');

        // 💰 Total bulan lalu
        $lastMonthEarnings = Booking::where('venue_id', $venue->id)
        ->where('status', 'completed')
        ->whereBetween('booking_date', [$now->copy()->subMonth()->startOfMonth()->toDateString(), $now->copy()->subMonth()->endOfMonth()->toDateString()])
        ->sum('price');

        // 📈 Persentase perubahan
        $percentageChange = $lastMonthEarnings > 0
        ? round((($monthlyEarnings - $lastMonthEarnings) / $lastMonthEarnings) * 100, 1)
        : 0;

        // ============================================
        // 💰 Sales Report (12 bulan terakhir)
        // ============================================
        $year = $now->year;
        $monthlyBookings = Booking::selectRaw('MONTH(created_at) as month, COUNT(*) as total')
            ->whereYear('created_at', $year)
            ->where('venue_id', $venue->id)
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $salesData = [];
        for ($i = 1; $i <= 12; $i++) {
            $salesData[] = $monthlyBookings[$i] ?? 0;
        }

        // ============================================
        // 🎱 Session Purchased (total booking)
        // ============================================
        $sessionPurchased = Booking::where('venue_id', $venue->id)
            ->whereYear('created_at', $now->year)
            ->count();

        $lastYearSessions = Booking::where('venue_id', $venue->id)
            ->whereYear('created_at', $now->copy()->subYear()->year)
            ->count();

        $sessionPercentageChange = $lastYearSessions > 0
            ? round((($sessionPurchased - $lastYearSessions) / $lastYearSessions) * 100, 1)
            : 0;

        // ============================================
        // ⭐ Ratings (ambil dari tabel reviews)
        // ============================================
        $averageRating = Review::where('venue_id', $venue->id)->avg('rating') ?? 0;
        $totalRatings = Review::where('venue_id', $venue->id)->count();

        // ============================================
        // 💬 Recent Reviews
        // ============================================
        $recentReviews = Review::with('user')
            ->where('venue_id', $venue->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // ============================================
        // 🧾 Recent Transactions (with filters)
        // ============================================
        $transactions = Booking::with(['table', 'user'])
            ->where('venue_id', $venue->id);

        if ($request->filled('search')) {
            $search = $request->search;
            $transactions->where(function ($q) use ($search) {
                $q->whereHas('user', function ($uq) use ($search) {
                    $uq->where('name', 'like', "%$search%")
                        ->orWhere('email', 'like', "%$search%");
                });
            });
        }

        if ($request->filled('status')) {
            $transactions->where('status', $request->status);
        }

        if ($request->filled('date_range')) {
            $dates = explode(' to ', str_replace(' - ', ' to ', $request->date_range));
            if (count($dates) === 2) {
                try {
                    $start = Carbon::parse(trim($dates[0]))->startOfDay();
                    $end = Carbon::parse(trim($dates[1]))->endOfDay();
                    $transactions->whereBetween('created_at', [$start, $end]);
                } catch (\Exception $e) {}
            }
        }

        $recentTransactions = $transactions
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->appends($request->query());

        // ============================================
        // 📤 Return ke view
        // ============================================
        return view('dash.venue.dashboard', [
            'venue' => $venue,
            'monthlyEarnings' => $monthlyEarnings,
            'lastMonthEarnings' => $lastMonthEarnings,
            'percentageChange' => $percentageChange,
            'salesData' => $salesData,
            'recentTransactions' => $recentTransactions,
            'recentReviews' => $recentReviews,
            'averageRating' => round($averageRating, 2),
            'totalRatings' => $totalRatings,
            'sessionPurchased' => $sessionPurchased,
            'lastYearSessions' => $lastYearSessions,
            'sessionPercentageChange' => $sessionPercentageChange,
        ]);
    }

    public function export(Request $request)
    {
        $filename = 'venues_' . now()->format('Ymd_His') . '.xlsx';
        $search   = $request->get('search');

        return Excel::download(new VenuesExport($search), $filename);
    }
    
}
