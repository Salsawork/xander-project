<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\EventRegistration;

class TicketPlayerController extends Controller
{
    public function index(Request $request)
    {
        // Ambil user yang sedang login
        $userId = Auth::id();

        // Ambil data dari tabel event_registrations
        $tickets = EventRegistration::with('event')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get([
                'id',
                'event_id',
                'bukti_payment',
                'total_payment',
                'slot',
                'status',
                'created_at',
            ]);

        return view('dash.user.ticket_player', compact('tickets'));
    }
}
