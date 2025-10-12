<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Bracket;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Xoco70\LaravelTournaments\Models\Tournament;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;


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
    // Tambahkan method ini ke EventController.php

    /**
     * Update bracket winners dari fight results
     */
    // Tambahkan method ini ke EventController.php

    /**
     * Update bracket winners dari fight results
     */
    private function updateBracketWinners($eventId, $championship)
    {
        // Ambil semua fights yang sudah ada winner
        $fights = $championship->fights()
            ->whereNotNull('winner_id')
            ->get();

        foreach ($fights as $fight) {
            if (!$fight->winner_id) continue;

            // Dapatkan winner berdasarkan winner_id
            $winner = null;

            // Cek apakah c1 atau c2 yang menang
            if ($fight->c1 == $fight->winner_id) {
                $winner = $fight->fighter1;
            } elseif ($fight->c2 == $fight->winner_id) {
                $winner = $fight->fighter2;
            }

            if (!$winner) continue;

            $winnerName = $winner->fullName ?? $winner->name;
            $round = $fight->round ?? 1;

            // Update bracket: tandai sebagai winner
            Bracket::where('event_id', $eventId)
                ->where('player_name', $winnerName)
                ->where('round', $round)
                ->update(['is_winner' => true]);

            // Advance winner ke round berikutnya
            $this->advanceWinnerToNextRound($eventId, $winnerName, $round, $fight);
        }
    }

    /**
     * Advance winner ke round berikutnya
     */
    private function advanceWinnerToNextRound($eventId, $winnerName, $currentRound, $fight)
    {
        $nextRound = $currentRound + 1;

        // Dapatkan total rounds
        $maxRound = Bracket::where('event_id', $eventId)->max('round');

        if ($currentRound >= $maxRound) {
            // Sudah di final, tidak ada round berikutnya
            return;
        }

        // Tentukan posisi di round berikutnya
        $currentBracket = Bracket::where('event_id', $eventId)
            ->where('player_name', $winnerName)
            ->where('round', $currentRound)
            ->first();

        if (!$currentBracket) return;

        $nextPosition = $currentBracket->next_match_position;

        // Cek apakah sudah ada bracket di round berikutnya
        $nextBracket = Bracket::where('event_id', $eventId)
            ->where('round', $nextRound)
            ->where('position', $nextPosition)
            ->first();

        if ($nextBracket) {
            // Update existing bracket
            if ($nextBracket->player_name === 'TBD') {
                $nextBracket->update(['player_name' => $winnerName]);
            }
        } else {
            // Create new bracket di round berikutnya
            $nextNextPosition = (int) ceil($nextPosition / 2);

            Bracket::create([
                'event_id' => $eventId,
                'player_name' => $winnerName,
                'round' => $nextRound,
                'position' => $nextPosition,
                'next_match_position' => $nextRound < $maxRound ? $nextNextPosition : null,
                'is_winner' => false,
            ]);
        }
    }

    /**
     * Method untuk manual update winner dari admin panel
     */
    public function updateBracketWinner(Request $request, Event $event)
    {
        $validated = $request->validate([
            'bracket_id' => 'required|exists:brackets,id',
            'is_winner' => 'required|boolean',
        ]);

        $bracket = Bracket::findOrFail($validated['bracket_id']);

        // Pastikan bracket milik event ini
        if ($bracket->event_id !== $event->id) {
            return response()->json(['error' => 'Bracket not found'], 404);
        }

        DB::beginTransaction();
        try {
            // Update winner status
            $bracket->update(['is_winner' => $validated['is_winner']]);

            if ($validated['is_winner']) {
                // Reset winner lain di round yang sama di match yang sama
                $matchPosition = (int) ceil($bracket->position / 2);
                Bracket::where('event_id', $event->id)
                    ->where('round', $bracket->round)
                    ->where('id', '!=', $bracket->id)
                    ->whereRaw('CEIL(position / 2) = ?', [$matchPosition])
                    ->update(['is_winner' => false]);

                // Advance ke round berikutnya
                $this->advanceWinnerToNextRound(
                    $event->id,
                    $bracket->player_name,
                    $bracket->round,
                    null
                );
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Winner updated successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Sync brackets dengan championship fights (dipanggil setelah generate tree)
     */
    public function syncBracketsWithFights(Event $event)
    {
        if (!$event->tournament_id) {
            return;
        }

        $tournament = Tournament::with('championships.fights.winner')->find($event->tournament_id);
        if (!$tournament) {
            return;
        }

        $championship = $tournament->championships->first();
        if (!$championship) {
            return;
        }

        // Update winners dari fights
        $this->updateBracketWinners($event->id, $championship);
    }
}
