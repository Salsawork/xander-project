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
        if (method_exists(Event::class, 'refreshStatuses')) {
            Event::refreshStatuses();
        }

        $q = Event::query();

        if ($term = trim((string) $request->query('q'))) {
            $q->where(function ($qq) use ($term) {
                $qq->where('name', 'like', "%{$term}%")
                   ->orWhere('description', 'like', "%{$term}%")
                   ->orWhere('location', 'like', "%{$term}%");
            });
        }

        if ($status = $request->query('status')) {
            $status = ucfirst(strtolower($status));
            if (in_array($status, ['Upcoming', 'Ongoing', 'Ended'])) {
                $q->where('status', $status);
            }
        }

        if ($gt = trim((string) $request->query('game_type'))) {
            $q->where('game_types', 'like', "%{$gt}%");
        }

        if ($region = trim((string) $request->query('region'))) {
            $q->where('location', 'like', "%{$region}%");
        }

        $events = $q->orderBy('start_date', 'asc')
            ->paginate(6)
            ->appends($request->query());

        $featured = Event::whereDate('start_date', '>', now())
            ->orderBy('start_date')
            ->first();

        $gameTypes = ['9-Ball', '8-Ball', '10-Ball'];
        
        $regions = Event::select('location')
            ->distinct()
            ->orderBy('location')
            ->pluck('location')
            ->toArray();

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
                'event_id' => $event->id,
                'name' => 'General Admission',
                'price' => (float) ($event->price_ticket ?? 0),
                'stock' => (int) ($event->stock ?? 0),
                'description' => 'Default ticket for ' . $event->name,
            ]);
        } else {
            if (
                (float) $ticket->price !== (float) $event->price_ticket ||
                (int) $ticket->stock !== (int) $event->stock
            ) {
                $ticket->update([
                    'price' => (float) $event->price_ticket,
                    'stock' => (int) $event->stock,
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
     * Bracket (kompat lama)
     */
    public function bracketByName(Event $event)
    {
        return redirect()->route('events.bracket', [
            'event' => $event->id,
            'name'  => Str::slug($event->name),
        ], 301);
    }

    /**
     * Register (PLAYER) — H-1 sebelum start date
     * POST /event/{event}/register
     */
    public function register(Request $request, $eventId)
    {
        $event = Event::lockForUpdate()->findOrFail($eventId);
        $user  = auth()->user();
    
        $validated = $request->validate([
            'bank_id'       => 'required|integer|exists:mst_bank,id_bank',
            'bukti_payment' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);
    
        if ($event->player_slots <= 0) {
            return back()->with('error', 'Slot pemain sudah penuh.');
        }
    
        if (EventRegistration::where('event_id', $eventId)->where('user_id', $user->id)->exists()) {
            return back()->with('error', 'Anda sudah mendaftar sebagai pemain.');
        }
    
        try {
            DB::beginTransaction();
    
            $file    = $request->file('bukti_payment');
            $ext     = $file->getClientOriginalExtension();
            $fname   = 'reg_' . $event->id . '_' . now()->format('YmdHis') . '_' . Str::random(6) . '.' . $ext;
    
            $targetDir = base_path('../demo-xanders/images/payments/registrations');
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0755, true);
            }
            $file->move($targetDir, $fname);
    
            $registrationNumber = 'REG-' . strtoupper(Str::random(6));
    
            $totalPayment = $event->price_ticket_player ?? 0;
    
            EventRegistration::create([
                'event_id'            => $event->id,
                'user_id'             => $user->id,
                'bank_id'             => $validated['bank_id'],
                'bukti_payment'       => $fname,
                'registration_number' => $registrationNumber,
                'slot'                => 1,
                'price'               => $event->price_ticket_player,
                'total_payment'       => $totalPayment,
                'status'              => 'pending',
            ]);
    
            if ($user->roles === 'user') {
                $user->update(['roles' => 'player']);
            }
    
            DB::commit();

            // Tampilkan Annual Pass (PLAYER)
            $this->flashAnnualPass($user, 'player', $event);

            return back()->with('success', 'Pendaftaran pemain berhasil dikirim. Nomor registrasi: ' . $registrationNumber);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Event register failed', [
                'user_id' => $user->id,
                'event_id'=> $event->id,
                'error'   => $e->getMessage(),
            ]);
            return back()->with('error', 'Terjadi kesalahan saat pendaftaran.');
        }
    }

    /**
     * Buy Ticket (VIEWER) — sebelum end_date
     * POST /event/{event}/buy
     */
    public function buyTicket(Request $request, $event)
    {
        $event = Event::lockForUpdate()->findOrFail($event);

        if (method_exists(Event::class, 'refreshStatuses')) {
            Event::refreshStatuses();
        }

        $user = Auth::user();

        $validated = $request->validate([
            'qty'            => 'required|integer|min:1',
            'ticket_id'      => 'required|integer|exists:event_tickets,id',
            'bank_id'        => 'required|integer|exists:mst_bank,id_bank',
            'bukti_payment'  => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            '_from'          => 'nullable|string',
        ]);

        $endDate   = $event->end_date instanceof Carbon
            ? $event->end_date->copy()->startOfDay()
            : Carbon::parse($event->end_date)->startOfDay();

        if (!now()->lt($endDate)) {
            return back()->withInput()->with('error', 'Penjualan tiket sudah berakhir.');
        }

        $qty = (int) $validated['qty'];

        try {
            $orderEvent = DB::transaction(function () use ($event, $user, $validated, $qty, $request) {
                $ticket = EventTicket::where('id', $validated['ticket_id'])
                    ->where('event_id', $event->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ((int) $ticket->stock < $qty) {
                    abort(422, 'Stok tiket tidak mencukupi.');
                }

                $unitPrice    = (float) $ticket->price;
                $totalPayment = $unitPrice * $qty;

                $path = null;
                if ($request->hasFile('bukti_payment')) {
                    $file    = $request->file('bukti_payment');
                    $ext     = $file->getClientOriginalExtension();
                    $fname   = 'evt_' . $event->id . '_' . now()->format('YmdHis') . '_' . Str::random(6) . '.' . $ext;

                    $targetDir = base_path('../demo-xanders/images/payments/events');
                    if (!file_exists($targetDir)) {
                        mkdir($targetDir, 0755, true);
                    }
                    $file->move($targetDir, $fname);
                    $path = $fname;
                }

                $orderNumber = $this->generateUniqueOrderNumber();

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

                $ticket->stock = (int) $ticket->stock - $qty;
                $ticket->save();

                if (!is_null($event->stock)) {
                    $event->stock = max(0, (int) $event->stock - $qty);
                    $event->save();
                }

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

        // Tampilkan Annual Pass (VIEWER)
        $this->flashAnnualPass($user, 'viewer', $event);

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
     * FLASH Annual Pass ke session untuk ditampilkan + diunduh di halaman detail event.
     */
    private function flashAnnualPass($user, string $type, Event $event): void
    {
        $year = now()->year;
        $passNo = 'XB-' . $year . '-' . str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $payload = [
            'year'       => $year,
            'type'       => strtoupper($type), // VIEWER / PLAYER
            'number'     => $passNo,
            'name'       => (string) ($user->name ?? 'Guest'),
            'event'      => (string) ($event->name ?? 'Event'),
            'valid_from' => now()->toDateString(),
            'valid_to'   => now()->endOfYear()->toDateString(),
            'qr_text'    => sprintf('XB|%s|%s|%s|%s', $year, strtoupper($type), $passNo, $user->name ?? 'Guest'),
        ];

        session()->flash('annual_pass', $payload);
    }

    /**
     * ====== DOWNLOAD ANNUAL PASS (SERVER-SIDE PNG) ======
     * GET /event/{event}/annual-pass/download?type=viewer|player
     */
    public function downloadAnnualPass(Request $request, $eventId)
    {
        $event = Event::findOrFail($eventId);
        $user  = Auth::user();
        if (!$user) {
            abort(403, 'Unauthorized');
        }

        $type = strtolower($request->query('type', strtolower(session('annual_pass.type') ?? 'viewer')));
        if (!in_array($type, ['viewer', 'player'])) {
            $type = 'viewer';
        }

        $pngBinary = $this->renderAnnualPassPng($user, $type, $event);

        $fileName = sprintf(
            'AnnualPass-%s-%s-%s.png',
            now()->year,
            strtoupper($type),
            Str::slug($user->name ?? 'guest')
        );

        return response($pngBinary, 200, [
            'Content-Type'        => 'image/png',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
            'Cache-Control'       => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma'              => 'no-cache',
        ]);
    }

    /**
     * Render Annual Pass PNG (server-side) menggunakan GD.
     * - Tanpa dependency wajib.
     * - Jika package Simple QrCode tersedia, akan dipakai otomatis.
     */
    private function renderAnnualPassPng($user, string $type, Event $event): string
    {
        $w = 1400; $h = 770;
        $im = imagecreatetruecolor($w, $h);

        // Background gradient gelap
        for ($y = 0; $y < $h; $y++) {
            $ratio = $y / $h;
            $color = imagecolorallocate($im,
                (int)(11 + $ratio*4),
                (int)(16 + $ratio*7),
                (int)(32 + $ratio*10)
            );
            imageline($im, 0, $y, $w, $y, $color);
        }

        $white       = imagecolorallocate($im, 255, 255, 255);
        $muted       = imagecolorallocate($im, 185, 193, 203);
        $accentBlue  = imagecolorallocate($im, 37, 99, 235);
        $accentGreen = imagecolorallocate($im, 16, 185, 129);
        $border      = imagecolorallocatealpha($im, 255, 255, 255, 110);

        // Border
        imagerectangle($im, 8, 8, $w-9, $h-9, $border);

        // Fonts (opsional)
        $fontBold = public_path('fonts/Inter-Bold.ttf');
        $fontReg  = public_path('fonts/Inter-Regular.ttf');
        $hasTtfBold = is_file($fontBold);
        $hasTtfReg  = is_file($fontReg);

        $drawText = function($text, $size, $x, $y, $color, $bold = false) use ($im, $fontBold, $fontReg, $hasTtfBold, $hasTtfReg) {
            if (($bold && $hasTtfBold) || (!$bold && $hasTtfReg)) {
                $font = $bold ? $fontBold : $fontReg;
                imagettftext($im, $size, 0, $x, $y, $color, $font, $text);
            } else {
                imagestring($im, $bold ? 5 : 4, $x, $y - 12, $text, $color);
            }
        };

        // Payload
        $year       = now()->year;
        $validFrom  = now()->format('d M Y');
        $validTo    = now()->endOfYear()->format('d M Y');
        $name       = (string)($user->name ?? 'Guest');
        $passNo     = 'XB-' . $year . '-' . str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $typeText   = strtoupper($type);
        $eventName  = (string)($event->name ?? 'Event');
        $qrText     = sprintf('XB|%s|%s|%s|%s', $year, $typeText, $passNo, $name);

        // Left texts
        $padX = 72; $padY = 88;
        $drawText('Xander Billiard', 22, $padX, $padY, $muted, true);
        $drawText("Annual Pass {$year}", 48, $padX, $padY + 64, $white, true);
        $drawText("Valid {$validFrom} — {$validTo}", 20, $padX, $padY + 104, $muted, false);

        $kvY = $padY + 168;
        $drawText('Name', 20, $padX, $kvY, $muted, false);
        $drawText($name, 26, $padX + 180, $kvY, $white, true);

        $kvY += 46;
        $drawText('Pass No', 20, $padX, $kvY, $muted, false);
        $drawText($passNo, 26, $padX + 180, $kvY, $white, true);

        $kvY += 46;
        $drawText('Type', 20, $padX, $kvY, $muted, false);
        $badgeColor = ($type === 'viewer') ? $accentGreen : $accentBlue;
        imagefilledrectangle($im, $padX + 180, $kvY - 24, $padX + 180 + 220, $kvY + 6, imagecolorallocatealpha($im, 255,255,255,110));
        $drawText($typeText, 22, $padX + 200, $kvY, $badgeColor, true);

        $kvY += 46;
        $drawText('Event', 20, $padX, $kvY, $muted, false);
        $drawText($eventName, 24, $padX + 180, $kvY, $white, true);

        // QR area
        $qrSize = 360;
        $qrX = $w - $qrSize - 120;
        $qrY = $padY;

        $boxBorder = imagecolorallocatealpha($im, 255,255,255,90);
        imagerectangle($im, $qrX - 14, $qrY - 14, $qrX + $qrSize + 14, $qrY + $qrSize + 14, $boxBorder);

        // Try generate QR with Simple QrCode if available
        $qrPngBin = null;
        try {
            if (class_exists(\SimpleSoftwareIO\QrCode\Generator::class)) {
                $qr = new \SimpleSoftwareIO\QrCode\Generator;
                $qrPngBin = $qr->format('png')->size($qrSize)->margin(1)->generate($qrText);
            }
        } catch (\Throwable $e) {
            $qrPngBin = null;
        }

        if ($qrPngBin) {
            $qrImg = imagecreatefromstring($qrPngBin);
            if ($qrImg) {
                imagecopyresampled($im, $qrImg, $qrX, $qrY, 0, 0, $qrSize, $qrSize, imagesx($qrImg), imagesy($qrImg));
                imagedestroy($qrImg);
            }
        } else {
            // Fallback kotak + teks
            imagefilledrectangle($im, $qrX, $qrY, $qrX + $qrSize, $qrY + $qrSize, imagecolorallocatealpha($im, 255,255,255,115));
            $drawText('QR not available', 18, $qrX + 80, $qrY + $qrSize/2, $muted, false);
        }

        $drawText('Scan QR untuk verifikasi pass (offline capable).', 18, $padX, $h - 72, $muted, false);

        ob_start();
        imagepng($im);
        $data = ob_get_clean();
        imagedestroy($im);

        return $data;
    }

    /**
     * Update bracket winners dari fight results
     */
    private function updateBracketWinners($eventId, $championship)
    {
        $fights = $championship->fights()
            ->whereNotNull('winner_id')
            ->get();

        foreach ($fights as $fight) {
            if (!$fight->winner_id) continue;

            $winner = null;
            if ($fight->c1 == $fight->winner_id) {
                $winner = $fight->fighter1;
            } elseif ($fight->c2 == $fight->winner_id) {
                $winner = $fight->fighter2;
            }

            if (!$winner) continue;

            $winnerName = $winner->fullName ?? $winner->name;
            $round = $fight->round ?? 1;

            Bracket::where('event_id', $eventId)
                ->where('player_name', $winnerName)
                ->where('round', $round)
                ->update(['is_winner' => true]);

            $this->advanceWinnerToNextRound($eventId, $winnerName, $round, $fight);
        }
    }

    private function advanceWinnerToNextRound($eventId, $winnerName, $currentRound, $fight)
    {
        $nextRound = $currentRound + 1;
        $maxRound = Bracket::where('event_id', $eventId)->max('round');

        if ($currentRound >= $maxRound) return;

        $currentBracket = Bracket::where('event_id', $eventId)
            ->where('player_name', $winnerName)
            ->where('round', $currentRound)
            ->first();

        if (!$currentBracket) return;

        $nextPosition = $currentBracket->next_match_position;

        $nextBracket = Bracket::where('event_id', $eventId)
            ->where('round', $nextRound)
            ->where('position', $nextPosition)
            ->first();

        if ($nextBracket) {
            if ($nextBracket->player_name === 'TBD') {
                $nextBracket->update(['player_name' => $winnerName]);
            }
        } else {
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

    public function updateBracketWinner(Request $request, Event $event)
    {
        $validated = $request->validate([
            'bracket_id' => 'required|exists:brackets,id',
            'is_winner' => 'required|boolean',
        ]);

        $bracket = Bracket::findOrFail($validated['bracket_id']);

        if ($bracket->event_id !== $event->id) {
            return response()->json(['error' => 'Bracket not found'], 404);
        }

        DB::beginTransaction();
        try {
            $bracket->update(['is_winner' => $validated['is_winner']]);

            if ($validated['is_winner']) {
                $matchPosition = (int) ceil($bracket->position / 2);
                Bracket::where('event_id', $event->id)
                    ->where('round', $bracket->round)
                    ->where('id', '!=', $bracket->id)
                    ->whereRaw('CEIL(position / 2) = ?', [$matchPosition])
                    ->update(['is_winner' => false]);

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

        $this->updateBracketWinners($event->id, $championship);
    }
}
