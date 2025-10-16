<?php

namespace App\Http\Controllers\adminController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Exports\EventExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class EventController extends Controller
{
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
            'price_ticket'        => 'nullable|integer|min:0',
            'stock'               => 'nullable|integer|min:0',
            'total_prize_money'   => 'nullable|integer|min:0',
            'champion_prize'      => 'nullable|integer|min:0',
            'runner_up_prize'     => 'nullable|integer|min:0',
            'third_place_prize'   => 'nullable|integer|min:0',
            'match_style'         => 'nullable|string|max:100',
            'finals_format'       => 'nullable|string|max:100',
            'divisions'           => 'nullable|string',
            'social_media_handle' => 'nullable|string|max:255',
            'status'              => 'nullable|string|in:Upcoming,Ongoing,Ended',
            'image_url'           => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        // Upload gambar (opsional) — menyimpan filename saja
        $filename = null;
        if ($request->hasFile('image_url')) {
            $filename = $this->uploadFile($request->file('image_url'));
        }

        Event::create([
            'name'                => $request->name,
            'description'         => $request->description,
            'location'            => $request->location,
            'start_date'          => $request->start_date,
            'end_date'            => $request->end_date,
            'game_types'          => $request->game_types,
            'price_ticket'        => (int) ($request->price_ticket ?? 0),
            'stock'               => (int) ($request->stock ?? 0),
            'total_prize_money'   => (int) ($request->total_prize_money ?? 0),
            'champion_prize'      => (int) ($request->champion_prize ?? 0),
            'runner_up_prize'     => (int) ($request->runner_up_prize ?? 0),
            'third_place_prize'   => (int) ($request->third_place_prize ?? 0),
            'match_style'         => $request->match_style,
            'finals_format'       => $request->finals_format,
            'divisions'           => $request->divisions,
            'social_media_handle' => $request->social_media_handle,
            'status'              => $request->status ?? 'Upcoming',
            'image_url'           => $filename, // simpan filename
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
            'price_ticket'        => 'nullable|integer|min:0',
            'stock'               => 'nullable|integer|min:0',
            'total_prize_money'   => 'nullable|integer|min:0',
            'champion_prize'      => 'nullable|integer|min:0',
            'runner_up_prize'     => 'nullable|integer|min:0',
            'third_place_prize'   => 'nullable|integer|min:0',
            'match_style'         => 'nullable|string|max:100',
            'finals_format'       => 'nullable|string|max:100',
            'divisions'           => 'nullable|string',
            'social_media_handle' => 'nullable|string|max:255',
            'status'              => 'nullable|string|in:Upcoming,Ongoing,Ended',
            'image_url'           => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        $filename = $event->image_url;

        // Ganti gambar jika upload baru
        if ($request->hasFile('image_url')) {
            // hapus lama di CMS & FE
            $this->deleteFile($event->image_url);
            // upload baru
            $filename = $this->uploadFile($request->file('image_url'));
        }

        $event->update([
            'name'                => $request->name,
            'description'         => $request->description,
            'location'            => $request->location,
            'start_date'          => $request->start_date,
            'end_date'            => $request->end_date,
            'game_types'          => $request->game_types,
            'price_ticket'        => (int) ($request->price_ticket ?? 0),
            'stock'               => (int) ($request->stock ?? 0),
            'total_prize_money'   => (int) ($request->total_prize_money ?? 0),
            'champion_prize'      => (int) ($request->champion_prize ?? 0),
            'runner_up_prize'     => (int) ($request->runner_up_prize ?? 0),
            'third_place_prize'   => (int) ($request->third_place_prize ?? 0),
            'match_style'         => $request->match_style,
            'finals_format'       => $request->finals_format,
            'divisions'           => $request->divisions,
            'social_media_handle' => $request->social_media_handle,
            'status'              => $request->status ?? $event->status,
            'image_url'           => $filename, // simpan filename
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

        // hapus file di CMS & FE
        $this->deleteFile($event->image_url);

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
        $search = $request->get('search');

        return Excel::download(new EventExport($search), $filename);
    }

    /* ============================================================
     | Helpers upload/delete seperti BannerController
     | Target: CMS => public/demo-xanders/images/events
     |         FE  => ../demo-xanders/images/events
     * ============================================================*/

    /**
     * Upload file, return filename aman (tanpa path).
     */
    private function uploadFile($file): string
    {
        // Nama aman
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeName     = preg_replace('/[^a-zA-Z0-9-_]/', '', Str::slug($originalName));
        $filename     = time() . '-' . $safeName . '.' . $file->getClientOriginalExtension();

        // Path CMS & FE
        $cmsPath = public_path('demo-xanders/images/events');
        $fePath  = base_path('../demo-xanders/images/events');

        // Buat folder jika belum ada
        if (!File::exists($cmsPath)) File::makeDirectory($cmsPath, 0755, true);
        if (!File::exists($fePath))  File::makeDirectory($fePath, 0755, true);

        // Simpan di CMS
        $file->move($cmsPath, $filename);

        // Copy ke FE
        @copy($cmsPath . DIRECTORY_SEPARATOR . $filename, $fePath . DIRECTORY_SEPARATOR . $filename);

        return $filename;
    }

    /**
     * Hapus file di CMS & FE bila ada.
     */
    private function deleteFile(?string $filename): void
    {
        if (!$filename) return;

        $cms = public_path('demo-xanders/images/events/' . $filename);
        $fe  = base_path('../demo-xanders/images/events/' . $filename);

        if (File::exists($cms)) @File::delete($cms);
        if (File::exists($fe))  @File::delete($fe);
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
}
