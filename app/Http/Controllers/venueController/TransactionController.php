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
     * Tampilkan daftar transaksi milik venue user yang login.
     */
    public function index()
    {
        $venue = Venue::where('user_id', Auth::id())->first();

        if ($venue) {
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
            $transactions = Booking::where('id', 0)->paginate(10);
        }

        return view('dash.venue.transaction', compact('transactions'));
    }

    /**
     * Tampilkan detail transaksi tertentu (hanya jika milik venue user).
     */
    public function show($bookingId)
    {
        $venue = Venue::where('user_id', Auth::id())->firstOrFail();

        $transaction = Booking::with(['table', 'user', 'venue'])
            ->where('venue_id', $venue->id)
            ->where('id', $bookingId)
            ->firstOrFail();

        return view('dash.venue.transaction-show', compact('transaction'));
    }
}
