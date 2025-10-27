<?php

namespace App\Http\Controllers\adminController;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Bracket;
use Illuminate\Http\Request;
use App\Exports\PlayersExport;

class UserController extends Controller
{
    // Tampilkan semua player yang butuh verifikasi
    public function index(Request $request)
    {
        $search = $request->input('search');
    
        // Ambil user dengan role player, sekaligus relasi eventRegistrations
        $players = User::with('eventRegistrations.event')
        ->where('roles', 'player')
        ->when($search, function ($query, $search) {
            $query->where('name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%");
        })
        ->orderBy('created_at', 'desc')
        ->get();
    

    
        return view('dash.admin.user.index', compact('players'));
    }
    

    // Verifikasi user agar status_player = 1 dan masuk ke bracket
    public function verify(Request $request, $id)
    {
        $user = User::findOrFail($id);
    
        // Pastikan user ini memang player
        if ($user->roles !== 'player') {
            return back()->with('error', 'User bukan player.');
        }
    
        // Ambil data registrasi event dari user (bukan dari form)
        $registration = EventRegistration::where('user_id', $user->id)
            ->where('status', 'pending')
            ->latest()
            ->first();
    
        if (!$registration) {
            return back()->with('error', 'Data pendaftaran pemain tidak ditemukan.');
        }
    
        $event = Event::findOrFail($registration->event_id);
    
        // Update status player
        $user->update(['status_player' => 1]);
    
        // Kurangi slot player
        $event->update(['player_slots' => max(0, $event->player_slots - 1)]);
    
        // Masukkan ke tabel bracket
        Bracket::create([
            'event_id' => $event->id,
            'player_name' => $user->name,
            'round' => 1,
            'position' => null,
            'next_match_position' => null,
            'is_winner' => false,
        ]);
    
        // Update status registrasi
        $registration->update(['status' => 'approved']);
    
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
