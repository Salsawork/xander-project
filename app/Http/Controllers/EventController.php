<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Bracket;
use Illuminate\Support\Str;

class EventController extends Controller
{
    /**
     * Landing/hero page — berisi section "Upcoming" & "Current".
     * Route: GET /events  (name: events.index)
     */
    public function index()
    {
        // Jika ada local scopes, gunakan; kalau tidak ada, fallback ke where status.
        $upcomingEvents = Event::when(method_exists(Event::class, 'scopeUpcoming'),
                fn($q) => $q->upcoming(),
                fn($q) => $q->where('status', 'Upcoming')
            )
            ->orderBy('start_date', 'asc')
            ->get();

        $currentEvents = Event::when(method_exists(Event::class, 'scopeOngoing'),
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
     * Query params:
     * - q        : string (search by name/desc/location)
     * - status   : upcoming|ongoing|ended
     * - game_type: string (LIKE)
     * - region   : string (LIKE pada location)
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
            'events', 'featured', 'gameTypes', 'regions', 'heading'
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
}
