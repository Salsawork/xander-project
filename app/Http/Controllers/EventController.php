<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Bracket;
use App\Models\Bank;
use App\Models\OrderEvent;
use App\Models\EventTicket;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

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
        $event = Event::findOrFail($event);

        // Load bank untuk dropdown modal Buy Ticket
        $banks = Bank::orderBy('nama_bank', 'asc')->get();

        // Ambil/siapkan ticket default untuk event ini agar order_events.ticket_id terisi
        // - Jika belum ada baris di event_tickets, kita buat "General Admission" dari price_ticket & stock event
        $ticket = EventTicket::where('event_id', $event->id)->orderBy('id')->first();

        if (!$ticket) {
            $ticket = EventTicket::create([
                'event_id'    => $event->id,
                'name'        => 'General Admission',
                'price'       => (float) ($event->price_ticket ?? 0),
                'stock'       => (int) ($event->stock ?? 0),
                'description' => 'Default ticket for ' . $event->name,
            ]);
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
     * Register (POST) — update data user login ke role "player".
     * Route: POST /event/{event}/register (name: events.register) [auth]
     * Field dari modal: username (alias name), email, phone
     */
    public function register(Request $request, $event)
    {
        $event = Event::findOrFail($event);

        // Map "username" -> "name"
        if (!$request->filled('name') && $request->filled('username')) {
            $request->merge(['name' => $request->input('username')]);
        }

        $user = Auth::user();

        // Validasi
        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'required|string|max:15',
            '_from' => 'nullable|string'
        ]);

        // Hanya boleh daftar jika Upcoming dan belum lewat end_date
        $endDate = $event->end_date instanceof Carbon ? $event->end_date : Carbon::parse($event->end_date);
        if ($event->status !== 'Upcoming' || now()->gt($endDate)) {
            return back()
                ->withInput()
                ->with('error', 'Pendaftaran tidak tersedia untuk event ini.');
        }

        try {
            // Update profil user + set roles => 'player'
            $user->name  = $validated['name'];
            $user->email = $validated['email'];
            $user->phone = $validated['phone'];
            $user->roles = 'player';
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

        return back()->with('success', 'Data kamu berhasil diperbarui. Role kamu sekarang "player". Pendaftaran diterima!');
    }

    /**
     * Buy Ticket (POST) — validasi qty, bank, bukti_payment;
     * simpan ke order_events; kurangi stok di event_tickets (sinkron ke events.stock).
     * Route: POST /event/{event}/buy (name: events.buy) [auth]
     */
    public function buyTicket(Request $request, $event)
    {
        // Lock baris event saat transaksi untuk menghindari race
        $event = Event::lockForUpdate()->findOrFail($event);

        $user = Auth::user();

        // Validasi awal (server-side)
        $validated = $request->validate([
            'qty'            => 'required|integer|min:1',
            'ticket_id'      => 'required|integer|exists:event_tickets,id',
            'bank_id'        => 'required|integer|exists:mst_bank,id_bank',
            'bukti_payment'  => 'required|image|mimes:jpg,jpeg,png,webp|max:2048', // 2MB, image only
            '_from'          => 'nullable|string',
        ]);

        // Cek ketersediaan event (boleh beli tiket untuk status Upcoming atau Ongoing)
        $endDate = $event->end_date instanceof Carbon ? $event->end_date : Carbon::parse($event->end_date);
        if (!in_array($event->status, ['Upcoming', 'Ongoing']) || now()->gt($endDate)) {
            return back()
                ->withInput()
                ->with('error', 'Pembelian tiket tidak tersedia untuk event ini.');
        }

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

                // Simpan bukti pembayaran ke storage public
                $path = null;
                if ($request->hasFile('bukti_payment')) {
                    $file    = $request->file('bukti_payment');
                    $ext     = $file->getClientOriginalExtension();
                    $fname   = 'evt_' . $event->id . '_' . now()->format('YmdHis') . '_' . Str::random(6) . '.' . $ext;
                    $path    = $file->storeAs('payments/events', $fname, 'public');
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
                    'status'        => 'pending',
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
}
