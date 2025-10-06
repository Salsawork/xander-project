<?php

namespace App\Http\Controllers\venueController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Venue;
use App\Models\Booking;
use App\Models\Review;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

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
                'percentageChange' => 0
            ]);
        }

        // --------------------------
        // Statistik bulanan (tidak berubah)
        // --------------------------
        $year = Carbon::now()->year;
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

        $now = Carbon::now();
        $monthlyEarnings = Booking::where('venue_id', $venue->id)
            ->whereBetween('created_at', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()])
            ->sum('price');

        $lastMonthEarnings = Booking::where('venue_id', $venue->id)
            ->whereBetween('created_at', [$now->copy()->subMonth()->startOfMonth(), $now->copy()->subMonth()->endOfMonth()])
            ->sum('price');

        $percentageChange = $lastMonthEarnings > 0
            ? round((($monthlyEarnings - $lastMonthEarnings) / $lastMonthEarnings) * 100, 1)
            : 0;

        // --------------------------
        // Recent Transactions (dengan filter)
        // --------------------------
        $transactions = Booking::with(['table', 'user'])
            ->where('venue_id', $venue->id);

        // 🔍 Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $venues->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%") // nama venue
                  ->orWhereHas('user', function($uq) use ($search) {
                      $uq->where('name', 'like', "%$search%")   // nama user
                         ->orWhere('email', 'like', "%$search%"); // email user
                  });
            });
        }

        // ✅ Status filter
        if ($request->filled('status')) {
            $transactions->where('status', $request->status);
        }

        // 📅 Date range filter
        if ($request->filled('date_range')) {
            $dates = explode(' to ', str_replace(' - ', ' to ', $request->date_range));
            if (count($dates) === 2) {
                try {
                    $start = \Carbon\Carbon::parse(trim($dates[0]))->startOfDay();
                    $end   = \Carbon\Carbon::parse(trim($dates[1]))->endOfDay();
                    $transactions->whereBetween('created_at', [$start, $end]);
                } catch (\Exception $e) {
                    // abaikan jika format salah
                }
            }
        }

        $recentTransactions = $transactions
            ->orderBy('created_at', 'desc')
            ->paginate(10) // pakai pagination biar bisa lebih banyak
            ->appends($request->query()); // supaya query filter tetap ada di pagination link

        // --------------------------
        // Recent Reviews
        // --------------------------
        $recentReviews = Review::with('user')
            ->where('venue_id', $venue->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('dash.venue.dashboard', [
            'venue' => $venue,
            'monthlyEarnings' => $monthlyEarnings,
            'lastMonthEarnings' => $lastMonthEarnings,
            'percentageChange' => $percentageChange,
            'salesData' => $salesData,
            'recentTransactions' => $recentTransactions,
            'recentReviews' => $recentReviews
        ]);
    }

}
