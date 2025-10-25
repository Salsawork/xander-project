<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Bracket;
use App\Models\Bank;
use App\Models\OrderEvent;
use App\Models\EventTicket;
use App\Models\EventRegistration;
use Illuminate\Support\Str;

use Xoco70\LaravelTournaments\Models\Tournament;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class EventController extends Controller
{
    /**
     * Landing/hero page — berisi section "Upcoming" & "Ongoing".
     * Route: GET /events  (name: events.index)
     */
    public function index()
    {
        // Pastikan status up to date (pakai guard bila method tidak ada)
        if (method_exists(Event::class, 'refreshStatuses')) {
            Event::refreshStatuses();
        }

        $upcomingEvents = Event::when(
            method_exists(Event::class, 'scopeUpcoming'),
            fn($q) => $q->upcoming(),
            fn($q) => $q->where('status', 'Upcoming')
        )->orderBy('start_date', 'asc')->get();

        $ongoingEvents = Event::when(
            method_exists(Event::class, 'scopeOngoing'),
            fn($q) => $q->ongoing(),
            fn($q) => $q->where('status', 'Ongoing')
        )->orderBy('start_date', 'asc')->get();

        return view('public.event.index', compact('upcomingEvents', 'ongoingEvents'));
    }

    /**
     * Katalog (list/show) — semua event + filter + pagination.
     * Route: GET /events/all  (name: events.list)
     */
    public function list(Request $request)
    {
        // Pastikan status up to date
        if (method_exists(Event::class, 'refreshStatuses')) {
            Event::refreshStatuses();
        }

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

        // Dropdown statis
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
        if (method_exists(Event::class, 'refreshStatuses')) {
            Event::refreshStatuses();
        }
    
        $event = Event::findOrFail($event);
        $banks = Bank::orderBy('nama_bank', 'asc')->get();
    
        $ticket = EventTicket::where('event_id', $event->id)->orderBy('id')->first();
    
        if (!$ticket) {
            $ticket = EventTicket::create([
                'event_id'    => $event->id,
                'name'        => 'General Admission',
                'price'       => (float) ($event->price_ticket ?? 0),
                'stock'       => (int) ($event->stock ?? 0),
                'description' => 'Default ticket for ' . $event->name,
            ]);
        } else {
            // 🧩 Sinkron otomatis jika data event berubah
            if (
                (float)$ticket->price !== (float)$event->price_ticket ||
                (int)$ticket->stock !== (int)$event->stock
            ) {
                $ticket->update([
                    'price' => (float)$event->price_ticket,
                    'stock' => (int)$event->stock,
                ]);
            }
        }
    
        return view('public.event.detail', compact('event', 'banks', 'ticket'));
    }
    

    /**
     * Kompat lama: /event/{name} -> redirect ke /event/{id}/{slug}
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
     * Register (POST) — hanya boleh sampai H-1 sebelum start_date.
     * Route: POST /event/{event}/register (name: events.register) [auth]
     * Field dari modal: username (alias name), email, phone
     */
   // EventRegistrationController.php
   public function register(Request $request, $eventId)
   {
       $event = Event::findOrFail($eventId);
       $user = auth()->user();
   
       // Cek slot pemain masih tersedia
       if ($event->player_slots <= 0) {
           return back()->with('error', 'Slot pemain sudah penuh.');
       }
   
       // Cek apakah user sudah daftar sebelumnya
       $existing = EventRegistration::where('event_id', $eventId)
           ->where('user_id', $user->id)
           ->first();
   
       if ($existing) {
           return back()->with('error', 'Anda sudah mendaftar sebagai pemain.');
       }
   
       // Ubah role user menjadi player (jika belum)
       if ($user->roles == 'user') {
           $user->update(['roles' => 'player']);
       }
   
       // Buat pendaftaran pemain
       EventRegistration::create([
           'event_id' => $event->id,
           'user_id' => $user->id,
           'slot' => 1,
           'price' => $event->price_ticket_player,
           'status' => 'pending',
       ]);
   
       return back()->with('success', 'Pendaftaran pemain berhasil dikirim, menunggu verifikasi admin.');
   }

    /**
     * Buy Ticket (POST)
     * - Diizinkan sebelum start_date (Upcoming) dan selama Ongoing
     * - Dilarang saat sudah memasuki hari end_date
     * Route: POST /event/{event}/buy (name: events.buy) [auth]
     */
    public function buyTicket(Request $request, $event)
    {
        // Lock baris event saat transaksi untuk menghindari race
        $event = Event::lockForUpdate()->findOrFail($event);

        // Pastikan status up to date
        if (method_exists(Event::class, 'refreshStatuses')) {
            Event::refreshStatuses();
        }

        $user = Auth::user();

        // Validasi awal (server-side)
        $validated = $request->validate([
            'qty'            => 'required|integer|min:1',
            'ticket_id'      => 'required|integer|exists:event_tickets,id',
            'bank_id'        => 'required|integer|exists:mst_bank,id_bank',
            'bukti_payment'  => 'required|image|mimes:jpg,jpeg,png,webp|max:2048', // 2MB, image only
            '_from'          => 'nullable|string',
        ]);

        // Aturan waktu pembelian
        $startDate = $event->start_date instanceof Carbon
            ? $event->start_date->copy()->startOfDay()
            : Carbon::parse($event->start_date)->startOfDay();
        $endDate   = $event->end_date instanceof Carbon
            ? $event->end_date->copy()->startOfDay()
            : Carbon::parse($event->end_date)->startOfDay();

        // Tidak boleh saat sudah memasuki hari end_date
        if (!now()->lt($endDate)) {
            return back()->withInput()->with('error', 'Penjualan tiket sudah berakhir.');
        }

        // Boleh sebelum start & saat Ongoing

        $qty = (int) $validated['qty'];

        try {
            $orderEvent = DB::transaction(function () use ($event, $user, $validated, $qty, $request) {

                // Pastikan ticket milik event ini & lock row ticket
                $ticket = EventTicket::where('id', $validated['ticket_id'])
                    ->where('event_id', $event->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                // Cek stok cukup
                if ((int) $ticket->stock < $qty) {
                    abort(422, 'Stok tiket tidak mencukupi.');
                }

                // Hitung total berdasarkan harga ticket (bukan dari events)
                $unitPrice    = (float) $ticket->price;
                $totalPayment = $unitPrice * $qty;

                $path = null;
                if ($request->hasFile('bukti_payment')) {
                    $file    = $request->file('bukti_payment');
                    $ext     = $file->getClientOriginalExtension();
                    $fname   = 'evt_' . $event->id . '_' . now()->format('YmdHis') . '_' . Str::random(6) . '.' . $ext;

                    // Path target yang benar (naik 2 level ke public_html/demo-xanders)
                    $targetDir = base_path('../demo-xanders/images/payments/events');

                    // Pastikan folder ada
                    if (!file_exists($targetDir)) {
                        mkdir($targetDir, 0755, true);
                    }

                    // Simpan file
                    $file->move($targetDir, $fname);

                    // Simpan hanya nama file di DB
                    $path = $fname;
                }

                // Generate order_number unik
                $orderNumber = $this->generateUniqueOrderNumber();

                // Buat order_events
                $orderEvent = OrderEvent::create([
                    'order_number'  => $orderNumber,
                    'user_id'       => $user->id,
                    'event_id'      => $event->id,
                    'ticket_id'     => $ticket->id,
                    'bank_id'       => (int) $validated['bank_id'],
                    'total_payment' => $totalPayment,
                    'bukti_payment' => $path,
                    'status'        => 'paid',
                ]);

                // Kurangi stok ticket
                $ticket->stock = (int) $ticket->stock - $qty;
                $ticket->save();

                // (Opsional) sinkron stok event agar tetap konsisten jika kolom event.stock digunakan
                if (!is_null($event->stock)) {
                    $event->stock = max(0, (int) $event->stock - $qty);
                    $event->save();
                }

                // Pastikan role user sebagai 'user' (kecuali admin)
                try {
                    if ($user->roles !== 'admin') {
                        $user->roles = 'user';
                        $user->save();
                    }
                } catch (\Throwable $e) {
                    Log::warning('Failed setting user role=user on buyTicket', [
                        'user_id' => $user->id,
                        'error'   => $e->getMessage(),
                    ]);
                }

                return $orderEvent;
            });
        } catch (\Throwable $e) {
            Log::error('Buy ticket failed', [
                'user_id' => $user->id ?? null,
                'event_id'=> $event->id ?? null,
                'error'   => $e->getMessage(),
            ]);

            $msg = $e->getCode() === 422 ? $e->getMessage() : 'Terjadi kesalahan saat memproses pembelian tiket.';
            return back()->withInput()->with('error', $msg);
        }

        return back()->with('success', 'Pembelian tiket berhasil dibuat! Nomor pesanan: ' . $orderEvent->order_number);
    }

    /**
     * Generate order_number unik, format: EVT-YYYYMMDD-XXXXXX
     */
    protected function generateUniqueOrderNumber(): string
    {
        $prefix = 'EVT-' . now()->format('Ymd') . '-';
        do {
            $code = strtoupper(Str::random(6));
            $candidate = $prefix . $code;
            $exists = OrderEvent::where('order_number', $candidate)->exists();
        } while ($exists);

        return $candidate;
    }

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
