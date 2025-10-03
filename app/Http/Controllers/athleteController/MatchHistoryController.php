<?php

namespace App\Http\Controllers\athleteController;

use App\Http\Controllers\Controller;
use App\Models\MatchHistory;
use App\Models\Venue;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MatchHistoryController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if ($user->roles !== 'athlete') {
            return redirect()->route('dashboard');
        }
    
        $matches = MatchHistory::with('venue')
            ->where('user_id', $user->id);
    
        // 🔍 Search (by location name or payment method)
        if ($request->filled('search')) {
            $search = $request->search;
            $matches->where(function($q) use ($search) {
                $q->whereHas('venue', fn($v) => $v->where('name', 'like', "%$search%"))
                  ->orWhere('payment_method', 'like', "%$search%");
            });
        }
    
        // ✅ Status filter
        if ($request->filled('status')) {
            $matches->where('status', $request->status);
        }
    
        // 📅 Date range filter (format: 2025-10-01 - 2025-10-03)
        if ($request->filled('date_range')) {
            $dates = explode(' - ', $request->date_range);
            if (count($dates) === 2) {
                try {
                    $start = \Carbon\Carbon::parse(trim($dates[0]))->startOfDay();
                    $end   = \Carbon\Carbon::parse(trim($dates[1]))->endOfDay();
                    $matches->whereBetween('date', [$start, $end]);
                } catch (\Exception $e) {
                    // kalau format salah, skip
                }
            }
        }
    
        $matches = $matches->orderByDesc('date')->get();
    
        return view('dash.athlete.match', compact('matches'));
    }
    

    public function create()
    {
        // Ambil data venue buat select option
        $venues = Venue::all();
        // Ambil data user lain buat opponent (optional, bisa diubah nanti)
        $opponents = User::where('roles', 'athlete')->where('id', '!=', auth()->id())->get();
        return view('dash.athlete.create-session', compact('venues', 'opponents'));
    }

    public function store(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'venue_id' => 'required|exists:venues,id',
            'opponent_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'time_start' => 'required',
            'time_end' => 'required',
            'payment_method' => 'nullable|string',
            'total_amount' => 'required|numeric',
        ]);
        $match = new MatchHistory();
        $match->user_id = auth()->id();
        $match->venue_id = $request->venue_id;
        $match->opponent_id = $request->opponent_id;
        $match->date = $request->date;
        $match->time_start = $request->time_start;
        $match->time_end = $request->time_end;
        $match->payment_method = $request->payment_method;
        $match->total_amount = $request->total_amount;
        $match->status = 'pending';
        $match->save();
        return redirect()->route('athlete.match')->with('success', 'Session created!');
    }

    public function show($id)
    {
        $user = auth()->user();
        $match = MatchHistory::with(['venue', 'opponent'])
            ->where('user_id', $user->id)
            ->findOrFail($id);
        return view('dash.athlete.match-detail', compact('match'));
    }
}
