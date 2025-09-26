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
            // Kalau venue null, bisa redirect, kasih pesan, atau tampilin view khusus
            return view('dash.venue.dashboard', [
                'venue' => null,
                'monthlyEarnings' => 0,
                'lastMonthEarnings' => 0,
                'percentageChange' => 0
            ]);
        }

        // Ambil data booking per bulan (khusus venue user ini)
        $year = Carbon::now()->year;
        $monthlyBookings = Booking::selectRaw('MONTH(created_at) as month, COUNT(*) as total')
            ->whereYear('created_at', $year)
            ->when($venue, function ($query) use ($venue) {
                $query->where('venue_id', $venue->id);
            })
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();

        // Biar urut, isi bulan yang kosong dengan 0
        $salesData = [];
        for ($i = 1; $i <= 12; $i++) {
            $salesData[] = $monthlyBookings[$i] ?? 0;
        }

        // Hitung earnings bulan ini & bulan lalu
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();
        $startOfLastMonth = $now->copy()->subMonth()->startOfMonth();
        $endOfLastMonth = $now->copy()->subMonth()->endOfMonth();

        $monthlyEarnings = Booking::where('venue_id', $venue->id)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('price');

        $lastMonthEarnings = Booking::where('venue_id', $venue->id)
            ->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])
            ->sum('price');

        $percentageChange = $lastMonthEarnings > 0
            ? round((($monthlyEarnings - $lastMonthEarnings) / $lastMonthEarnings) * 100, 1)
            : 0;

        $recentTransactions = Booking::with(['table', 'user'])
            ->where('venue_id', optional($venue)->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

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
