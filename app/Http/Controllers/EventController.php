<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Bracket;

class EventController extends Controller
{
    // Menampilkan semua event ke halaman public.event.index
    public function index()
    {
        $events = Event::orderBy('start_date', 'asc')->get();
        return view('public.event.index', compact('events'));
    }

    public function show(Event $event)
    {
        return view('public.event.detail', compact('event'));
    }
    
    public function bracket(Event $event)
    {
        // Ambil data bracket untuk event ini
        $brackets = Bracket::where('event_id', $event->id)
                          ->orderBy('round')
                          ->orderBy('position')
                          ->get();
        
        return view('public.event.bracket', compact('event', 'brackets'));
    }
}