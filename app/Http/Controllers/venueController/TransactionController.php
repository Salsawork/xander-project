<?php

namespace App\Http\Controllers\venueController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Venue;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    /**
     * Display the transaction history page for venue.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Ambil venue yang terkait dengan user yang sedang login
        $venue = Venue::where('user_id', Auth::id())->first();
        
        if ($venue) {
            // Jika venue ditemukan, ambil booking yang terkait dengan venue tersebut
            $transactions = Booking::where('venue_id', $venue->id)
                ->with(['table', 'user'])
                ->orderBy('booking_date', 'desc')
                ->orderBy('start_time', 'desc')
                ->paginate(10);
        } else {
            // Jika venue tidak ditemukan, tampilkan data kosong
            $transactions = Booking::where('id', 0)->paginate(10); // Tidak ada data
        }
        
        return view('dash.venue.transaction', compact('transactions'));
    }
}