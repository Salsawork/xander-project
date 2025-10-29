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
        return view('dash.athlete.sparring.index', compact('schedule'));
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

        return redirect()
            ->route('athlete.sparring')
            ->with('success', 'Jadwal sparring berhasil dibuat.');
    }

    /**
     * Show the form for editing the specified sparring schedule.
     */
    public function edit($schedule)
    {
        $user = Auth::user();
        if ($user->roles !== 'athlete') {
            return redirect()->route('dashboard');
        }

        $schedule = SparringSchedule::where('id', $schedule)
            ->where('athlete_id', $user->id)
            ->firstOrFail();

        return view('dash.athlete.sparring.edit', compact('schedule'));
    }

    /**
     * Update the specified sparring schedule in storage.
     */
    public function update(Request $request, $schedule)
    {
        $user = Auth::user();
        if ($user->roles !== 'athlete') {
            return redirect()->route('dashboard');
        }

        $schedule = SparringSchedule::where('id', $schedule)
            ->where('athlete_id', $user->id)
            ->firstOrFail();

        // Jika hanya mengubah is_booked, lewati validasi tanggal
        if ($request->has('is_booked') && count($request->all()) === 2) {
            $request->validate([
                'is_booked' => 'required|boolean',
            ]);

            $schedule->update(['is_booked' => (bool) $request->input('is_booked')]);

            return redirect()
                ->route('athlete.sparring')
                ->with('success', 'Status booking berhasil diperbarui.');
        }

        // Validasi lengkap untuk perubahan tanggal/waktu
        $request->validate([
            'date'       => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time'   => 'required|date_format:H:i|after:start_time',
            'is_booked'  => 'nullable|boolean',
        ]);

        // Cek duplikat selain record yang sedang diedit
        $existing = SparringSchedule::where('athlete_id', $user->id)
            ->where('date', $request->date)
            ->where('start_time', $request->start_time)
            ->where('end_time', $request->end_time)
            ->where('id', '!=', $schedule->id)
            ->exists();

        if ($existing) {
            return back()->with('error', 'Anda sudah memiliki jadwal pada waktu tersebut.');
        }

        $schedule->update([
            'date'       => $request->date,
            'start_time' => $request->start_time,
            'end_time'   => $request->end_time,
            'is_booked'  => (bool) $request->input('is_booked', $schedule->is_booked),
        ]);

        return redirect()
            ->route('athlete.sparring')
            ->with('success', 'Jadwal sparring berhasil diperbarui.');
    }

    public function destroy($schedule)
    {
        $user = Auth::user();
        if ($user->roles !== 'athlete') {
            return redirect()->route('dashboard');
        }

        $schedule = SparringSchedule::where('id', $schedule)
            ->where('athlete_id', $user->id)
            ->firstOrFail();

        $schedule->delete();

        return redirect()
            ->route('athlete.sparring')
            ->with('success', 'Jadwal sparring berhasil dihapus.');
    }
}
