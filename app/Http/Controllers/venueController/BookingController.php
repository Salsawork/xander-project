<?php

namespace App\Http\Controllers\venueController;

use App\Http\Controllers\Controller;
use App\Models\Table;
use App\Models\PriceSchedule;
use App\Models\Venue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BookingController extends Controller
{
    public function index()
    {
        // Ambil venue yang terkait dengan user yang sedang login
        $venue = Venue::where('user_id', Auth::id())->first();
        
        if ($venue) {
            // Jika venue ditemukan, ambil table dan price schedule yang terkait dengan venue tersebut
            $tables = Table::where('venue_id', $venue->id)->get();
            $priceSchedules = PriceSchedule::where('venue_id', $venue->id)->get();
        } else {
            // Jika venue tidak ditemukan, tampilkan data kosong
            $tables = collect([]);
            $priceSchedules = collect([]);
        }
        
        $availableCount = $tables->where('status', 'available')->count();
        $bookedCount = $tables->where('status', 'booked')->count();

        return view('dash.venue.booking', compact('tables', 'availableCount', 'bookedCount', 'priceSchedules'));
    }
}
