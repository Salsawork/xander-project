<?php

namespace App\Http\Controllers\venueController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Voucher;
use App\Models\Venue;
use Illuminate\Support\Facades\Auth;

class PromoController extends Controller
{
    /**
     * Display the promo management page for venue.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Ambil venue yang terkait dengan user yang sedang login
        $venue = Venue::where('user_id', Auth::id())->first();
        
        if ($venue) {
            // Jika venue ditemukan, ambil voucher yang terkait dengan venue tersebut
            $vouchers = Voucher::where('venue_id', $venue->id)->get();
        } else {
            // Jika venue tidak ditemukan, tampilkan data kosong
            $vouchers = collect([]);
        }
        
        // Hitung jumlah voucher berdasarkan status
        $allCount = $vouchers->count();
        $ongoingCount = $vouchers->where('is_active', true)
            ->where('end_date', '>', now())
            ->where('start_date', '<', now())
            ->count();
        $upcomingCount = $vouchers->where('start_date', '>', now())->count();
        $endedCount = $vouchers->where('end_date', '<', now())->count();
        
        return view('dash.venue.promo', compact(
            'vouchers', 
            'allCount', 
            'ongoingCount', 
            'upcomingCount', 
            'endedCount'
        ));
    }
}