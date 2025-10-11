<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Bracket;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EventController extends Controller
{
    /**
     * Landing/hero page — berisi section "Upcoming" & "Current".
     * Route: GET /events  (name: events.index)
     */
    public function index()
    {
        $upcomingEvents = Event::when(
            method_exists(Event::class, 'scopeUpcoming'),
            fn($q) => $q->upcoming(),
            fn($q) => $q->where('status', 'Upcoming')
        )
            ->orderBy('start_date', 'asc')
            ->get();

        $currentEvents = Event::when(
            method_exists(Event::class, 'scopeOngoing'),
            fn($q) => $q->ongoing(),
            fn($q) => $q->where('status', 'Ongoing')
        )
            ->orderBy('start_date', 'asc')
            ->get();

        return view('public.event.index', compact('upcomingEvents', 'currentEvents'));
    }

    /**
     * Katalog (list/show) — semua event + filter + pagination.
     * Route: GET /events/all  (name: events.list)
     */
    public function list(Request $request)
    {
        $q = Event::query();

        // Search (q)
        if ($term = trim((string) $request->query('q'))) {
            $q->where(function ($qq) use ($term) {
                $qq->where('name', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%")
                    ->orWhere('location', 'like', "%{$term}%");
            });
        }

        // Status: upcoming/ongoing/ended (case-insensitive)
        if ($status = $request->query('status')) {
            $status = ucfirst(strtolower($status));
            if (in_array($status, ['Upcoming', 'Ongoing', 'Ended'])) {
                $q->where('status', $status);
            }
        }

        // Game type (LIKE)
        if ($gt = trim((string) $request->query('game_type'))) {
            $q->where('game_types', 'like', "%{$gt}%");
        }

        // Region (LIKE pada location)
        if ($region = trim((string) $request->query('region'))) {
            $q->where('location', 'like', "%{$region}%");
        }

        $events = $q->orderBy('start_date', 'asc')
            ->paginate(8)
            ->appends($request->query());

        // Optional featured: event terdekat yang akan datang
        $featured = Event::whereDate('start_date', '>', now())
            ->orderBy('start_date')
            ->first();

        // Dropdown statis (bisa dibuat dinamis kalau mau)
        $gameTypes = ['9-Ball', '8-Ball', '10-Ball'];
        $regions   = ['Los Angeles, CA', 'New York, NY', 'Chicago, IL'];

        // Heading dinamis
        $heading = $request->filled('status')
            ? (ucfirst($request->query('status')) . ' Events')
            : 'All Events';

        return view('public.event.show', compact(
            'events',
            'featured',
            'gameTypes',
            'regions',
            'heading'
        ));
    }

    /**
     * DETAIL: /event/{id}/{slug?}
     * Route name: events.show
     */
    public function show($event, $name = null)
    {
        $event = Event::findOrFail($event);
        return view('public.event.detail', compact('event'));
    }

    /**
     * Kompat lama: /event/{name} -> redirect ke format kanonik /event/{id}/{slug}
     */
    public function showByName(Event $event)
    {
        return redirect()->route('events.show', [
            'event' => $event->id,
            'name'  => Str::slug($event->name),
        ], 301);
    }

    /**
     * Bracket (kanonik): /event/{id}/{slug?}/bracket
     */
    public function bracketById($event, $name = null)
    {
        $event = Event::findOrFail($event);

        $brackets = Bracket::where('event_id', $event->id)
            ->orderBy('round')
            ->orderBy('position')
            ->get();

        return view('public.event.bracket', compact('event', 'brackets'));
    }

    /**
     * Bracket (kompat lama): /event/{name}/bracket -> redirect
     */
    public function bracketByName(Event $event)
    {
        return redirect()->route('events.bracket', [
            'event' => $event->id,
            'name'  => Str::slug($event->name),
        ], 301);
    }

    /**
     * Register (POST) — sekarang menyimpan input modal ke tabel users (user yg login).
     * Route: POST /event/{event}/register (name: events.register) [auth]
     * Field dari modal: username (alias name), email, phone
     */
    public function register(Request $request, $event)
    {
        $event = Event::findOrFail($event);

        // Map "username" -> "name" kalau form mengirim username
        if (!$request->filled('name') && $request->filled('username')) {
            $request->merge(['name' => $request->input('username')]);
        }

        $user = Auth::user();

        // Validasi, email unik kecuali milik user sendiri.
        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            // batasi sesuai schema phone varchar(15)
            'phone' => 'required|string|max:15',
            '_from' => 'nullable|string'
        ]);

        // Cek ketersediaan pendaftaran (hanya Upcoming & belum lewat end_date)
        $endDate = $event->end_date instanceof Carbon ? $event->end_date : Carbon::parse($event->end_date);
        if ($event->status !== 'Upcoming' || now()->gt($endDate)) {
            return back()
                ->withInput()
                ->with('error', 'Pendaftaran tidak tersedia untuk event ini.');
        }

        try {
            // Update data user yang login
            $user->name  = $validated['name'];
            $user->email = $validated['email'];
            $user->phone = $validated['phone'];
            $user->save();
        } catch (\Throwable $e) {
            Log::error('Failed to update user from event register modal', [
                'user_id'  => $user->id,
                'event_id' => $event->id,
                'error'    => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan data. Coba lagi.');
        }

        // Sukses
        return back()->with('success', 'Data kamu berhasil diperbarui. Pendaftaran diterima!');
    }
}
