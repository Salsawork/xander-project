<?php

namespace App\Http\Controllers\athleteController;

use Illuminate\Http\Request;
use App\Models\SparringSchedule;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class SparringScheduleController extends Controller
{
    /**
     * Display index sparring schedule of athletes.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if ($user->roles !== 'athlete') {
            return redirect()->route('dashboard');
        }

        $schedule = SparringSchedule::where('athlete_id', $user->id)
            ->when($request->status !== null, function ($q) use ($request) {
                $q->where('is_booked', (int) $request->status);
            })->get();
        return view('dash.athlete.sparring', compact('schedule'));
    }
    /**
     * Display create sparring schedule of athletes.
     */
    public function create()
    {
        return view('dash.athlete.create-sparring-schedule');
    }

    /**
     * Store untuk create data sparring schedule (gunakan tabel sparring schedule)
     */
    public function store(Request $request)
    {
        $request->validate([
            'date'      => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time'  => 'required|date_format:H:i|after:start_time',
        ]);

        $athlete_id = Auth::id();

        $existing = SparringSchedule::where('athlete_id', $athlete_id)
            ->where('date', $request->date)
            ->where('start_time', $request->start_time)
            ->where('end_time', $request->end_time)
            ->first();

        if ($existing) {
            return back()->with('error', 'Anda sudah pernah membuat jadwal ini.');
        }

        SparringSchedule::create([
            'athlete_id' => (int) $athlete_id,
            'date'       => $request->date,
            'start_time' => $request->start_time,
            'end_time'   => $request->end_time,
        ]);

        return back()->with('success', 'Jadwal sparring berhasil dibuat.');
    }
}
