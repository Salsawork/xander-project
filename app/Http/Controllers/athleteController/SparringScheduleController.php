<?php

namespace App\Http\Controllers\athleteController;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\SparringSchedule;
use App\Models\Venue;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Models\Order;

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

    public function indexSparring(Request $request)
    {
        $orders = Order::where('order_type', 'sparring')
            ->whereHas('orderSparrings', function ($q) {
                $q->where('athlete_id', Auth::id());
            })
            ->when($request->search, function ($query) use ($request) {
                $query->whereHas('user', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%')
                        ->orWhere('email', 'like', '%' . $request->search . '%');
                });
            })
            ->when($request->status, function ($query) use ($request) {
                $query->where('payment_status', $request->status);
            })
            // hanya 1 orderBy, kasih default DESC
            ->orderBy('created_at', $request->orderBy === 'asc' ? 'asc' : 'desc')
            ->get();

        $pendingCount    = Order::where('order_type', 'sparring')->whereHas('orderSparrings', function ($q) {$q->where('athlete_id', Auth::id());})->where('payment_status', 'pending')->count();
        $processingCount = Order::where('order_type', 'sparring')->whereHas('orderSparrings', function ($q) {$q->where('athlete_id', Auth::id());})->where('payment_status', 'processing')->count();
        $paidCount       = Order::where('order_type', 'sparring')->whereHas('orderSparrings', function ($q) {$q->where('athlete_id', Auth::id());})->where('payment_status', 'paid')->count();
        $failedCount     = Order::where('order_type', 'sparring')->whereHas('orderSparrings', function ($q) {$q->where('athlete_id', Auth::id());})->where('payment_status', 'failed')->count();
        $refundedCount   = Order::where('order_type', 'sparring')->whereHas('orderSparrings', function ($q) {$q->where('athlete_id', Auth::id());})->where('payment_status', 'refunded')->count();

        return view('dash.athlete.order-sparring', compact(
            'orders',
            'pendingCount',
            'processingCount',
            'paidCount',
            'failedCount',
            'refundedCount'
        ));
    }
}
