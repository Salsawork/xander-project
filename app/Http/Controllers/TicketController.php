<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TicketController extends Controller
{
    /**
     * Tampilkan tiket yang pernah dibeli user dari tabel order_events
     * Kolom: event_name, qty (hasil perhitungan), price (event_tickets), total, purchased_at, payment_status.
     */
    public function index(Request $request)
    {
        $userId = Auth::id();

        $tickets = DB::table('order_events')
            ->join('events', 'events.id', '=', 'order_events.event_id')
            ->leftJoin('event_tickets', 'event_tickets.id', '=', 'order_events.ticket_id')
            ->where('order_events.user_id', $userId)
            ->select([
                'order_events.id',
                'events.name as event_name',
                DB::raw('CASE WHEN event_tickets.price IS NOT NULL AND event_tickets.price > 0 
                         THEN ROUND(order_events.total_payment / event_tickets.price) 
                         ELSE 1 END as qty'),
                'event_tickets.price',
                'order_events.total_payment as total',
                'order_events.created_at as purchased_at',
                'order_events.status as payment_status',
            ])
            ->orderBy('order_events.created_at', 'desc')
            ->get();

        return view('dash.user.ticket', compact('tickets'));
    }
}
