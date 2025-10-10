<?php

namespace App\Http\Controllers\adminController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use Illuminate\Support\Facades\File;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::orderBy('start_date', 'asc')->get();
        return view('dash.admin.event.index', compact('events'));
    }

    public function create()
    {
        return view('dash.admin.event.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'                => 'required|string|max:255',
            'description'         => 'required|string',
            'location'            => 'required|string|max:255',
            'start_date'          => 'required|date',
            'end_date'            => 'required|date|after_or_equal:start_date',
            'game_types'          => 'required|string',
            'total_prize_money'   => 'nullable|numeric|min:0',
            'champion_prize'      => 'nullable|numeric|min:0',
            'runner_up_prize'     => 'nullable|numeric|min:0',
            'third_place_prize'   => 'nullable|numeric|min:0',
            'match_style'         => 'nullable|string|max:100',
            'finals_format'       => 'nullable|string|max:100',
            'divisions'           => 'nullable|string',
            'social_media_handle' => 'nullable|string|max:255',
            'status'              => 'nullable|string|in:Upcoming,Ongoing,Ended',
            'image_url'           => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $imagePath = null;

        if ($request->hasFile('image_url')) {
            $image = $request->file('image_url');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $destination = public_path('events');

            // Pastikan folder public/events ada
            if (!File::exists($destination)) {
                File::makeDirectory($destination, 0755, true);
            }

            $image->move($destination, $imageName);
            $imagePath = 'events/' . $imageName; // Disimpan relatif terhadap public/
        }

        $event = Event::create([
            'name'                => $request->name,
            'description'         => $request->description,
            'location'            => $request->location,
            'start_date'          => $request->start_date,
            'end_date'            => $request->end_date,
            'game_types'          => $request->game_types,
            'total_prize_money'   => $request->total_prize_money ?? 0,
            'champion_prize'      => $request->champion_prize ?? 0,
            'runner_up_prize'     => $request->runner_up_prize ?? 0,
            'third_place_prize'   => $request->third_place_prize ?? 0,
            'match_style'         => $request->match_style ?? null,
            'finals_format'       => $request->finals_format ?? null,
            'divisions'           => $request->divisions ?? null,
            'social_media_handle' => $request->social_media_handle ?? null,
            'status'              => $request->status ?? 'Upcoming',
            'image_url'           => $imagePath,
        ]);

        return redirect()
            ->route('admin.guidelines.create', $event)
            ->with('success', 'Event berhasil dibuat.');
    }

    public function edit(Event $event)
    {
        return view('dash.admin.event.edit', compact('event'));
    }

    public function update(Request $request, Event $event)
    {
        $request->validate([
            'name'                => 'required|string|max:255',
            'description'         => 'required|string',
            'location'            => 'required|string|max:255',
            'start_date'          => 'required|date',
            'end_date'            => 'required|date|after_or_equal:start_date',
            'game_types'          => 'required|string',
            'total_prize_money'   => 'nullable|numeric|min:0',
            'champion_prize'      => 'nullable|numeric|min:0',
            'runner_up_prize'     => 'nullable|numeric|min:0',
            'third_place_prize'   => 'nullable|numeric|min:0',
            'match_style'         => 'nullable|string|max:100',
            'finals_format'       => 'nullable|string|max:100',
            'divisions'           => 'nullable|string',
            'social_media_handle' => 'nullable|string|max:255',
            'status'              => 'nullable|string|in:Upcoming,Ongoing,Ended',
            'image_url'           => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $imagePath = $event->image_url;

        if ($request->hasFile('image_url')) {
            // Hapus gambar lama jika ada
            if ($event->image_url && File::exists(public_path($event->image_url))) {
                File::delete(public_path($event->image_url));
            }

            $image = $request->file('image_url');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $destination = public_path('events');

            if (!File::exists($destination)) {
                File::makeDirectory($destination, 0755, true);
            }

            $image->move($destination, $imageName);
            $imagePath = 'events/' . $imageName;
        }

        $event->update([
            'name'                => $request->name,
            'description'         => $request->description,
            'location'            => $request->location,
            'start_date'          => $request->start_date,
            'end_date'            => $request->end_date,
            'game_types'          => $request->game_types,
            'total_prize_money'   => $request->total_prize_money ?? 0,
            'champion_prize'      => $request->champion_prize ?? 0,
            'runner_up_prize'     => $request->runner_up_prize ?? 0,
            'third_place_prize'   => $request->third_place_prize ?? 0,
            'match_style'         => $request->match_style ?? null,
            'finals_format'       => $request->finals_format ?? null,
            'divisions'           => $request->divisions ?? null,
            'social_media_handle' => $request->social_media_handle ?? null,
            'status'              => $request->status ?? $event->status,
            'image_url'           => $imagePath,
        ]);

        return redirect()
            ->route('admin.event.index', $event)
            ->with('success', 'Event berhasil diperbarui.');
    }

    public function destroy(Event $event)
    {
        if ($event->image_url && File::exists(public_path($event->image_url))) {
            File::delete(public_path($event->image_url));
        }

        $event->delete();

        return redirect()
            ->route('admin.event.index')
            ->with('success', 'Event berhasil dihapus.');
    }

}
