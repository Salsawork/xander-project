<?php

namespace App\Http\Controllers\adminController;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Event;
use App\Models\Bracket;
use Illuminate\Http\Request;
use App\Exports\PlayersExport;

class UserController extends Controller
{
    // Tampilkan semua player yang butuh verifikasi
    public function index(Request $request)
    {
        // Ambil keyword pencarian dari query string (?search=...)
        $search = $request->input('search');

        // Query dasar: hanya ambil user dengan roles = 'player'
        $query = User::where('roles', 'player');

        // Kalau ada keyword pencarian, tambahkan filter
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        // Ambil data hasil filter, bisa tambahkan pagination juga
        $players = $query->orderBy('created_at', 'desc')->get();

        return view('dash.admin.user.index', compact('players'));
    }

    // Verifikasi user agar status_player = 1 dan masuk ke bracket
    public function verify(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Cek apakah dia player
        if ($user->roles !== 'player') {
            return back()->with('error', 'User bukan player.');
        }

        // Update status player
        $user->update(['status_player' => 1]);

        // Tambahkan ke tabel bracket
        // Pastikan event_id dikirim dari form atau request
        $eventId = $request->input('event_id');
        if (!$eventId) {
            return back()->with('error', 'Event ID tidak ditemukan.');
        }

        $event = Event::findOrFail($eventId);
        $event->update(['player_slots' => $event->player_slots - 1]);

        Bracket::create([
            'event_id' => $eventId,
            'player_name' => $user->name,
            'round' => 1, // round awal
            'position' => null,
            'next_match_position' => null,
            'is_winner' => false,
        ]);

        return back()->with('success', 'Player berhasil diverifikasi dan dimasukkan ke bracket.');
    }

    /**
     * Export Excel Data Player (ikutin ?search= kalau ada).
     */
    public function export(Request $request)
    {
        $search = $request->get('search');

        // Cara 1: return instance export langsung (pakai Exportable + Responsable)
        return new PlayersExport($search);

        // Cara 2 (alternatif setara):
        // return \Maatwebsite\Excel\Facades\Excel::download(new PlayersExport($search), 'players_' . now()->format('Ymd_His') . '.xlsx');
    }
}
