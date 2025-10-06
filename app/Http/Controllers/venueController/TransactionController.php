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
                ->when(request('status'), function ($query) {
                    return $query->where('status', request('status'));
                })
                ->when(request('orderBy'), function ($query) {
                    return $query->orderBy('created_at', request('orderBy', 'asc'));
                })
                ->when(request('search'), function ($query) {
                    return $query->where(function ($q) {
                        $q->where('booking_date', 'like', '%' . request('search') . '%')
                            ->orWhere('start_time', 'like', '%' . request('search') . '%')
                            ->orWhere('end_time', 'like', '%' . request('search') . '%')
                            ->orWhere('price', 'like', '%' . request('search') . '%')
                            ->orWhere('discount', 'like', '%' . request('search') . '%')
                            ->orWhere('payment_method', 'like', '%' . request('search') . '%')
                            ->orWhereHas('table', function ($q) {
                                $q->where('table_number', 'like', '%' . request('search') . '%');
                            })
                            ->orWhereHas('user', function ($q) {
                                $q->where('name', 'like', '%' . request('search') . '%')
                                    ->orWhere('email', 'like', '%' . request('search') . '%');
                            });
                    });
                })
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
