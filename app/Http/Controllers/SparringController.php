<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AthleteDetail;
use App\Models\SparringSchedule;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;

class SparringController extends Controller
{
    /**
     * Display a listing of athletes.
     */
    public function index(Request $request)
    {
        // Ambil semua user dengan role athlete yang memiliki athlete_detail
        $athletes = User::where('roles', 'athlete')
            ->whereHas('athleteDetail')
            ->with('athleteDetail')
            ->get();

        // Tambahkan log untuk debugging
        Log::info('Athletes query result:', [
            'count' => $athletes->count(),
            'roles_in_db' => User::distinct('roles')->pluck('roles')->toArray(),
            'athlete_users' => User::where('roles', 'athlete')->count(),
            'athlete_details' => \App\Models\AthleteDetail::count(),
        ]);

        // Ambil data cart dari cookie
        $carts = [];
        if (Cookie::has('cart')) {
            $cartData = Cookie::get('cart');
            $carts = is_array($cartData) ? $cartData : json_decode($cartData, true) ?? [];
        }

        // Ambil data sparring dari cookie
        $sparrings = [];
        if (Cookie::has('sparring')) {
            $sparringData = Cookie::get('sparring');
            $sparrings = is_array($sparringData) ? $sparringData : json_decode($sparringData, true) ?? [];
        }

        return view('dash.sparring.index', compact('athletes', 'carts', 'sparrings'));
    }

    /**
     * Display the specified athlete.
     */
    public function show($id)
    {
        // Ambil data atlet berdasarkan ID
        $athlete = User::where('roles', 'athlete')
            ->where('id', $id)
            ->with('athleteDetail')
            ->firstOrFail();

        // Ambil jadwal sparring yang tersedia untuk atlet ini
        $schedules = SparringSchedule::where('athlete_id', $id)
            ->where('is_booked', false)
            ->where('date', '>=', now()->format('Y-m-d'))
            ->orderBy('date')
            ->orderBy('start_time')
            ->limit(9) // Batasi hanya 9 schedule
            ->get();

        // Ambil data cart dari cookie
        $carts = [];
        if (Cookie::has('cart')) {
            $cartData = Cookie::get('cart');
            $carts = is_array($cartData) ? $cartData : json_decode($cartData, true) ?? [];
        }

        // Ambil data sparring dari cookie
        $sparrings = [];
        if (Cookie::has('sparring')) {
            $sparringData = Cookie::get('sparring');
            $sparrings = is_array($sparringData) ? $sparringData : json_decode($sparringData, true) ?? [];
        }

        return view('dash.sparring.detail', compact('athlete', 'schedules', 'carts', 'sparrings'));
    }

    /**
     * Add sparring to cart.
     */
    public function addToCart(Request $request)
    {
        // Validasi request
        $request->validate([
            'athlete_id' => 'required|exists:users,id',
            'schedule_id' => 'required|exists:sparring_schedules,id',
        ]);

        // Ambil data atlet dan jadwal
        $athlete = User::where('id', $request->athlete_id)
            ->with('athleteDetail')
            ->firstOrFail();

        $schedule = SparringSchedule::where('id', $request->schedule_id)
            ->where('is_booked', false)
            ->firstOrFail();

        // Ambil data sparring dari cookie
        $sparrings = [];
        if (Cookie::has('sparring')) {
            $sparringData = Cookie::get('sparring');
            $sparrings = is_array($sparringData) ? $sparringData : json_decode($sparringData, true) ?? [];
        }

        // Buat data sparring baru
        $newSparring = [
            'athlete_id' => $athlete->id,
            'name' => $athlete->name,
            'image' => $athlete->athleteDetail->image ?? null,
            'schedule_id' => $schedule->id,
            'schedule' => date('d M Y', strtotime($schedule->date)) . ' ' . date('H:i', strtotime($schedule->start_time)) . '-' . date('H:i', strtotime($schedule->end_time)),
            'price' => $athlete->athleteDetail->price_per_session,
        ];

        // Cek apakah sparring dengan schedule_id yang sama sudah ada di cart
        $existingIndex = array_search($schedule->id, array_column($sparrings, 'schedule_id'));

        if ($existingIndex !== false) {
            // Update sparring yang sudah ada
            $sparrings[$existingIndex] = $newSparring;
        } else {
            // Tambahkan sparring baru
            $sparrings[] = $newSparring;
        }

        // Simpan kembali ke cookie
        Cookie::queue('sparring', json_encode($sparrings), 60 * 24 * 7); // 1 minggu

        return redirect()->back()->with('success', 'Sparring session added to cart');
    }

    /**
     * Remove sparring from cart.
     */
    public function removeFromCart(Request $request)
    {
        // Validasi request
        $request->validate([
            'schedule_id' => 'required|exists:sparring_schedules,id',
        ]);

        // Ambil data sparring dari cookie
        $sparrings = [];
        if (Cookie::has('sparring')) {
            $sparringData = Cookie::get('sparring');
            $sparrings = is_array($sparringData) ? $sparringData : json_decode($sparringData, true) ?? [];
        }

        // Hapus sparring dengan schedule_id yang sesuai
        $sparrings = array_filter($sparrings, function($sparring) use ($request) {
            return $sparring['schedule_id'] != $request->schedule_id;
        });

        // Reset array keys
        $sparrings = array_values($sparrings);

        // Simpan kembali ke cookie
        Cookie::queue('sparring', json_encode($sparrings), 60 * 24 * 7); // 1 minggu

        return redirect()->back()->with('success', 'Sparring session removed from cart');
    }
}