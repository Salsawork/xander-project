<?php

namespace App\Http\Controllers\athleteController;

use App\Http\Controllers\Controller;
use App\Models\MatchHistory;
use Illuminate\Support\Facades\Auth;

class MatchHistoryController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        // Pastikan cuma athlete yang bisa akses
        if ($user->roles !== 'athlete') {
            return redirect()->route('dashboard');
        }
        // Ambil match history user login, include venue
        $matches = MatchHistory::with('venue')
            ->where('user_id', $user->id)
            ->orderByDesc('date')
            ->get();
        return view('dash.athlete.match', compact('matches'));
    }

    public function create()
    {
        // Ambil data venue buat select option
        $venues = \App\Models\Venue::all();
        // Ambil data user lain buat opponent (optional, bisa diubah nanti)
        $opponents = \App\Models\User::where('roles', 'athlete')->where('id', '!=', auth()->id())->get();
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
        $match = new \App\Models\MatchHistory();
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
        $match = \App\Models\MatchHistory::with(['venue', 'opponent'])
            ->where('user_id', $user->id)
            ->findOrFail($id);
        return view('dash.athlete.match-detail', compact('match'));
    }
}
