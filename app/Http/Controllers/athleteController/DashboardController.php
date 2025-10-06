<?php

namespace App\Http\Controllers\athleteController;

use App\Http\Controllers\Controller;
use App\Models\MatchHistory;
use App\Models\BilliardSession;
use App\Models\Participants;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->roles !== 'athlete') {
            return redirect()->route('dashboard');
        }

        // Ambil data rating dari review (diasumsikan rating 4.9)
        $rating = 4.9;
        $totalRating = 123000;

        // Ambil data pendapatan bulanan
        $currentMonth = Carbon::now()->format('Y-m');
        $lastMonth = Carbon::now()->subMonth()->format('Y-m');

        $monthlyEarnings = MatchHistory::where('user_id', $user->id)
            ->whereMonth('created_at', '=', Carbon::now()->month)
            ->whereYear('created_at', '=', Carbon::now()->year)
            ->where('status', 'completed')
            ->sum('total_amount');

        $lastMonthEarnings = MatchHistory::where('user_id', $user->id)
            ->whereMonth('created_at', '=', Carbon::now()->subMonth()->month)
            ->whereYear('created_at', '=', Carbon::now()->subMonth()->year)
            ->where('status', 'completed')
            ->sum('total_amount');

        // Hitung persentase perubahan pendapatan
        $percentageChange = 0;
        if ($lastMonthEarnings > 0) {
            $percentageChange = (($monthlyEarnings - $lastMonthEarnings) / $lastMonthEarnings) * 100;
        }

        // Ambil data jumlah sesi yang dibuat
        $sessionCreated = BilliardSession::where('created_at', '>=', Carbon::now()->subYear())
            ->whereHas('participants', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->count();

        $lastYearSessionCreated = BilliardSession::where('created_at', '>=', Carbon::now()->subYears(2))
            ->where('created_at', '<', Carbon::now()->subYear())
            ->whereHas('participants', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->count();

        // Hitung persentase perubahan jumlah sesi
        $sessionPercentageChange = 0;
        if ($lastYearSessionCreated > 0) {
            $sessionPercentageChange = (($sessionCreated - $lastYearSessionCreated) / $lastYearSessionCreated) * 100;
        }

        // Ambil data sesi terbaru
        $recentSessions = BilliardSession::whereHas('participants', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->with('venue')
            ->orderBy('date', 'desc')
            ->take(3)
            ->get();

        // Ambil data review (dummy data sementara)
        $reviews = [
            [
                'name' => 'Lee Han Yu',
                'rating' => 5,
                'comment' => 'Had an amazing time playing against...',
                'positive' => true
            ],
            [
                'name' => 'Jessica Huang',
                'rating' => 2,
                'comment' => 'The session was well-organized, but...',
                'positive' => false
            ],
            [
                'name' => 'Nathanael Immanuel',
                'rating' => 5,
                'comment' => 'The services here is amazing!',
                'positive' => true
            ]
        ];

        // Tambahkan di Controller
        $scheduledDates = BilliardSession::whereHas('participants', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->whereMonth('date', Carbon::now()->month)
            ->whereYear('date', Carbon::now()->year)
            ->pluck('date')
            ->map(function($date) {
                return Carbon::parse($date)->day;
            })
            ->toArray();

        return view('dash.athlete.dashboard', compact(
            'user',
            'rating',
            'totalRating',
            'monthlyEarnings',
            'lastMonthEarnings',
            'percentageChange',
            'sessionCreated',
            'lastYearSessionCreated',
            'sessionPercentageChange',
            'recentSessions',
            'reviews',
            'scheduledDates'
        ));
    }

    public function getCalendar($month = null, $year = null)
    {
        if (!$month || !$year) {
            $month = date('m');
            $year = date('Y');
        }

        $date = Carbon::createFromDate($year, $month, 1);
        $daysInMonth = $date->daysInMonth;
        $firstDayOfWeek = $date->dayOfWeek;

        // Tanggal-tanggal dengan event (hardcoded untuk contoh)
        $eventDates = [1, 5, 7];

        return [
            'currentMonth' => $date->format('F Y'),
            'prevMonth' => $date->copy()->subMonth()->format('m'),
            'prevYear' => $date->copy()->subMonth()->format('Y'),
            'nextMonth' => $date->copy()->addMonth()->format('m'),
            'nextYear' => $date->copy()->addMonth()->format('Y'),
            'daysInMonth' => $daysInMonth,
            'firstDayOfWeek' => $firstDayOfWeek,
            'eventDates' => $eventDates
        ];
    }
}
