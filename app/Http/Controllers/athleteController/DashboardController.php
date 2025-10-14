<?php

namespace App\Http\Controllers\athleteController;

use App\Http\Controllers\Controller;
use App\Models\MatchHistory;
use App\Models\BilliardSession;
use App\Models\Participants;
use App\Models\User;
use App\Models\AthleteReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AthleteExport;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->roles !== 'athlete') {
            return redirect()->route('dashboard');
        }

        // Ambil data rating dari review (diasumsikan rating 4.9)
        $averageRating = AthleteReview::where('athlete_id', $user->id)->avg('rating');
        // dd($averageRating);
        $totalRating = AthleteReview::where('athlete_id', $user->id)->count();

        $now = Carbon::now();

        // Bulan ini
        $monthlyEarnings = MatchHistory::where('user_id', $user->id)
            ->whereMonth('date', $now->month)
            ->whereYear('date', $now->year)
            ->where('status', 'completed')
            ->sum('total_amount');
        
        // Bulan lalu
        $lastMonthEarnings = MatchHistory::where('user_id', $user->id)
            ->whereMonth('date', $now->copy()->subMonth()->month)
            ->whereYear('date', $now->copy()->subMonth()->year)
            ->where('status', 'completed')
            ->sum('total_amount');
        
        // Hitung persentase perubahan
        $percentageChange = 0;
        if ($lastMonthEarnings > 0) {
            $percentageChange = round((($monthlyEarnings - $lastMonthEarnings) / $lastMonthEarnings) * 100, 1);
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

        // Ambil review dari database berdasarkan session yang diikuti athlete
        $reviews = AthleteReview::with('user')
        ->where('athlete_id', $user->id)
        ->latest()
        ->take(5)
        ->get();
  

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
            'averageRating',
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

    public function export(Request $request)
    {
        $filename = 'AthleteMatches_' . now()->format('Ymd_His') . '.xlsx';
        $search   = $request->get('search');

        return Excel::download(new AthleteExport($search), $filename);
    }
}
