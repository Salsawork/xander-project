<?php

namespace App\Http\Controllers\adminController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Event;
use App\Models\OrderEvent;
use App\Models\EventRegistration;
use App\Exports\EventExport;
use App\Models\User;
use App\Models\Bracket;

use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class EventController extends Controller
{
    /**
     * Lokasi absolut folder gambar event (FE):
     *   /home/xanderbilliard.site/public_html/images/event
     *
     * URL publik yang cocok:
     *   https://xanderbilliard.site/images/event/{filename}
     *
     * Di Blade:
     *   asset('images/event/'.$filename)
     *
     * Di DB:
     *   hanya simpan nama file, BUKAN path lengkap.
     */
    private function getFeEventDir(): string
    {
        return '/home/xanderbilliard.site/public_html/images/event';
    }

    /**
     * Upload 1 file gambar event ke folder FE.
     * Return: filename saja.
     */
    private function uploadImage($file): string
    {
        $fePath = $this->getFeEventDir();

        if (!File::exists($fePath)) {
            File::makeDirectory($fePath, 0755, true);
        }

        if (!$file || !$file->isValid()) {
            throw new \RuntimeException('File gambar event tidak valid.');
        }

        $ext      = strtolower($file->getClientOriginalExtension());
        $origName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeBase = Str::slug($origName) ?: 'event';
        $unique   = now()->format('YmdHis') . '-' . Str::random(6);

        $filename = "{$unique}-{$safeBase}.{$ext}";

        // Simpan langsung ke folder FE target
        $file->move($fePath, $filename);

        return $filename;
    }

    /**
     * Hapus file gambar event berdasarkan nama file di DB
     * dari folder:
     *   /home/xanderbilliard.site/public_html/images/event
     */
    private function deleteImage(?string $filename): void
    {
        if (!$filename) {
            return;
        }

        $fePath = $this->getFeEventDir();
        $feFile = $fePath . DIRECTORY_SEPARATOR . $filename;

        if (File::exists($feFile)) {
            @unlink($feFile);
        }
    }

    /**
     * GET /dashboard/event
     * name: admin.event.index
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $events = Event::query()
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('location', 'like', "%{$search}%")
                      ->orWhere('status', 'like', "%{$search}%");
            })
            ->orderBy('start_date', 'asc')
            ->get();

        return view('dash.admin.event.index', compact('events', 'search'));
    }

    /**
     * GET /dashboard/event/create
     * name: admin.event.create
     */
    public function create()
    {
        return view('dash.admin.event.create');
    }

    /**
     * POST /dashboard/event
     * name: admin.event.store
     */
    public function store(Request $request)
    {
        // 1) Sanitasi angka uang -> integer
        $this->sanitizeMoneyFields($request);
        // 2) Normalisasi teks opsional -> ''
        $this->normalizeOptionalTextFields($request);

        $request->validate([
            'name'                => 'required|string|max:255',
            'description'         => 'required|string',
            'location'            => 'required|string|max:255',
            'start_date'          => 'required|date',
            'end_date'            => 'required|date|after_or_equal:start_date',
            'game_types'          => 'required|string',
            'stock'               => 'nullable|integer|min:0',
            'price_ticket'        => 'nullable|numeric|min:0',
            'total_prize_money'   => 'nullable|numeric|min:0',
            'champion_prize'      => 'nullable|numeric|min:0',
            'runner_up_prize'     => 'nullable|numeric|min:0',
            'third_place_prize'   => 'nullable|numeric|min:0',
            'match_style'         => 'nullable|string|max:100',
            'finals_format'       => 'nullable|string|max:100',
            'divisions'           => 'nullable|string',
            'social_media_handle' => 'nullable|string|max:255',
            'status'              => 'nullable|string|in:Upcoming,Ongoing,Ended',
            'image_url'           => 'nullable|image|mimes:jpg,jpeg,png,webp,avif,gif|max:4096',
        ]);

        // Upload gambar (opsional) → simpan hanya filename
        $filename = null;
        if ($request->hasFile('image_url')) {
            $filename = $this->uploadImage($request->file('image_url'));
        }

        Event::create([
            'name'                => $request->name,
            'description'         => $request->description,
            'location'            => $request->location,
            'start_date'          => $request->start_date,
            'end_date'            => $request->end_date,
            'game_types'          => $request->game_types,
            'price_ticket'        => $request->price_ticket ?? 0,
            'stock'               => (int) ($request->stock ?? 0),
            'total_prize_money'   => $request->total_prize_money ?? 0,
            'champion_prize'      => $request->champion_prize ?? 0,
            'runner_up_prize'     => $request->runner_up_prize ?? 0,
            'third_place_prize'   => $request->third_place_prize ?? 0,
            'match_style'         => $request->match_style,
            'finals_format'       => $request->finals_format,
            'divisions'           => $request->divisions,
            'social_media_handle' => $request->social_media_handle,
            'status'              => $request->status ?? 'Upcoming',
            'image_url'           => $filename, // hanya nama file
        ]);

        return redirect()
            ->route('admin.event.index')
            ->with('success', 'Event berhasil dibuat.');
    }

    /**
     * GET /dashboard/event/{id}/edit
     * name: admin.event.edit
     */
    public function edit($id)
    {
        $event = Event::findOrFail($id);
        return view('dash.admin.event.edit', compact('event'));
    }

    /**
     * PUT /dashboard/event/{id}
     * name: admin.event.update
     */
    public function update(Request $request, $id)
    {
        $event = Event::findOrFail($id);

        // 1) Sanitasi angka uang -> integer
        $this->sanitizeMoneyFields($request);
        // 2) Normalisasi teks opsional -> ''
        $this->normalizeOptionalTextFields($request);

        $request->validate([
            'name'                => 'required|string|max:255',
            'description'         => 'required|string',
            'location'            => 'required|string|max:255',
            'start_date'          => 'required|date',
            'end_date'            => 'required|date|after_or_equal:start_date',
            'game_types'          => 'required|string',
            'price_ticket'        => 'nullable|numeric|min:0',
            'stock'               => 'nullable|integer|min:0',
            'total_prize_money'   => 'nullable|numeric|min:0',
            'champion_prize'      => 'nullable|numeric|min:0',
            'runner_up_prize'     => 'nullable|numeric|min:0',
            'third_place_prize'   => 'nullable|numeric|min:0',
            'match_style'         => 'nullable|string|max:100',
            'finals_format'       => 'nullable|string|max:100',
            'divisions'           => 'nullable|string',
            'social_media_handle' => 'nullable|string|max:255',
            'status'              => 'nullable|string|in:Upcoming,Ongoing,Ended',
            'image_url'           => 'nullable|image|mimes:jpg,jpeg,png,webp,avif,gif|max:4096',
        ]);

        $filename = $event->image_url;

        // Jika ada upload baru → hapus lama & simpan baru ke /images/event
        if ($request->hasFile('image_url')) {
            $this->deleteImage($event->image_url);
            $filename = $this->uploadImage($request->file('image_url'));
        }

        $event->update([
            'name'                => $request->name,
            'description'         => $request->description,
            'location'            => $request->location,
            'start_date'          => $request->start_date,
            'end_date'            => $request->end_date,
            'game_types'          => $request->game_types,
            'price_ticket'        => $request->price_ticket ?? 0,
            'stock'               => (int) ($request->stock ?? 0),
            'total_prize_money'   => $request->total_prize_money ?? 0,
            'champion_prize'      => $request->champion_prize ?? 0,
            'runner_up_prize'     => $request->runner_up_prize ?? 0,
            'third_place_prize'   => $request->third_place_prize ?? 0,
            'match_style'         => $request->match_style,
            'finals_format'       => $request->finals_format,
            'divisions'           => $request->divisions,
            'social_media_handle' => $request->social_media_handle,
            'status'              => $request->status ?? $event->status,
            'image_url'           => $filename, // hanya nama file
        ]);

        return redirect()
            ->route('admin.event.index')
            ->with('success', 'Event berhasil diperbarui.');
    }

    /**
     * DELETE /dashboard/event/{id}
     * name: admin.event.destroy
     */
    public function destroy($id)
    {
        $event = Event::withCount('tickets')->findOrFail($id);

        if ($event->tickets_count > 0) {
            return back()->with('error', 'Tidak bisa menghapus event karena masih memiliki tiket.');
        }

        // hapus file di folder FE /images/event
        $this->deleteImage($event->image_url);

        $event->delete();

        return redirect()
            ->route('admin.event.index')
            ->with('success', 'Event berhasil dihapus.');
    }

    /**
     * Export Excel
     */
    public function export(Request $request)
    {
        $filename = 'events_' . now()->format('Ymd_His') . '.xlsx';
        $search   = $request->get('search');

        return Excel::download(new EventExport($search), $filename);
    }

    /**
     * Hapus semua karakter non-digit dari field uang supaya selalu numerik.
     */
    private function sanitizeMoneyFields(Request $request): void
    {
        $fields = [
            'price_ticket',
            'total_prize_money',
            'champion_prize',
            'runner_up_prize',
            'third_place_prize',
        ];

        $clean = [];
        foreach ($fields as $f) {
            $raw    = (string) $request->input($f, '');
            $digits = preg_replace('/[^\d]/', '', $raw ?? '');
            $clean[$f] = $digits === '' ? 0 : (int) $digits;
        }

        $request->merge($clean);
    }

    /**
     * Pastikan field teks opsional tidak null (pakai '' jika kosong).
     */
    private function normalizeOptionalTextFields(Request $request): void
    {
        $fields = [
            'match_style',
            'finals_format',
            'divisions',
            'social_media_handle',
        ];

        $normalized = [];
        foreach ($fields as $f) {
            $val = $request->input($f);
            $normalized[$f] = is_null($val) ? '' : trim((string) $val);
        }

        $request->merge($normalized);
    }

    // Detail untuk verify pemesanan tiket event user
    public function detail($id)
    {
        $event  = Event::findOrFail($id);
        $orders = OrderEvent::with(['user', 'event', 'ticket', 'bank'])
            ->where('event_id', $id)
            ->latest()
            ->get();

        return view('dash.admin.event.detail', compact('event', 'orders'));
    }

    public function verify(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if ($user->roles !== 'player') {
            return back()->with('error', 'User bukan player.');
        }

        $eventId = $request->input('event_id');
        $event   = Event::findOrFail($eventId);

        // Ambil data pendaftaran
        $registration = EventRegistration::where('user_id', $user->id)
            ->where('event_id', $eventId)
            ->first();

        if (!$registration) {
            return back()->with('error', 'Data pendaftaran tidak ditemukan.');
        }

        // Pastikan masih ada slot
        if ($event->player_slots <= 0) {
            return back()->with('error', 'Slot pemain sudah habis.');
        }

        // Kurangi slot
        Event::where('id', $eventId)->decrement('player_slots', $registration->slot ?? 1);

        // Update status player & registrasi
        $user->update(['status_player' => 1]);
        $registration->update(['status' => 'approved']);

        // Tambah ke bracket
        Bracket::create([
            'event_id'            => $eventId,
            'player_name'         => $user->name,
            'round'               => 1,
            'position'            => null,
            'next_match_position' => null,
            'is_winner'           => false,
        ]);

        return back()->with('success', 'Player berhasil diverifikasi dan slot event berkurang.');
    }

    public function reject($id)
    {
        $order = OrderEvent::findOrFail($id);

        if ($order->status !== 'paid') {
            return back()->with('error', 'Hanya pesanan berstatus "paid" yang bisa ditolak.');
        }

        $order->status = 'rejected';
        $order->save();

        return back()->with('success', 'Pesanan berhasil ditolak.');
    }
}
