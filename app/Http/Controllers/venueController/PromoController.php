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

        $where = [];
        if ($venue) {
            // Jika venue ditemukan, gunakan ID venue untuk query
            $where['venue_id'] = $venue->id;
        }

        // Jika venue ditemukan, ambil voucher yang terkait dengan venue tersebut
        $vouchers = Voucher::where($where)->when(request()->has('filter'), function ($query) {
            $filter = request()->input('filter');
            if ($filter === 'ongoing') {
                return $query->where('is_active', true)
                    ->where('end_date', '>', now())
                    ->where('start_date', '<', now());
            } elseif ($filter === 'upcoming') {
                return $query->where('start_date', '>', now());
            } elseif ($filter === 'ended') {
                return $query->where('end_date', '<', now());
            }
            return $query; // Jika tidak ada filter, kembalikan query tanpa perubahan
        })->get();

        // Hitung jumlah voucher berdasarkan status
        $allCount = Voucher::where($where)->count();
        $ongoingCount = Voucher::where($where)->where('is_active', true)
            ->where('end_date', '>', now())
            ->where('start_date', '<', now())
            ->count();
        $upcomingCount = Voucher::where($where)->where('start_date', '>', now())->count();
        $endedCount = Voucher::where($where)->where('end_date', '<', now())->count();

        return view('dash.venue.promo', compact(
            'vouchers',
            'allCount',
            'ongoingCount',
            'upcomingCount',
            'endedCount'
        ));
    }
}
