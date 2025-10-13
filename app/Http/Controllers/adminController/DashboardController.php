<?php

namespace App\Http\Controllers\adminController;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\OrderItem;
use App\Models\OrderSparring;
use App\Models\OrderEvent;
use App\Models\Order;
use App\Models\Visit; // sesuaikan dengan nama model kamu
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $now = Carbon::now();

        // Rentang sebulan terakhir (30 hari ke belakang)
        $startDate = $now->copy()->subDays(30);

        // === EARNINGS 30 HARI TERAKHIR ===
        $bookingEarnings = Booking::whereBetween('created_at', [$startDate, $now])
            ->whereIn('status', ['booked', 'completed'])
            ->sum('price');

        $productEarnings = OrderItem::whereBetween('created_at', [$startDate, $now])
            ->sum(DB::raw('subtotal - discount'));

        $sparringEarnings = OrderSparring::whereBetween('created_at', [$startDate, $now])
            ->sum('price');

        $eventEarnings = OrderEvent::whereBetween('created_at', [$startDate, $now])
            ->whereIn('status', ['paid', 'completed'])
            ->sum('total_payment');

        $monthlyEarnings = $bookingEarnings + $productEarnings + $sparringEarnings + $eventEarnings;


        // === PAGE VISITS ===
        $visits = Visit::whereDate('visit_date', $now)->sum('visit');


        // === CHART: PENDAPATAN PER BULAN DALAM SETAHUN TERAKHIR ===
        $monthlyRevenue = collect(range(1, 12))->map(function ($month) {
            $start = Carbon::create(null, $month)->startOfMonth();
            $end = Carbon::create(null, $month)->endOfMonth();

            $booking = Booking::whereBetween('created_at', [$start, $end])
                ->whereIn('status', ['booked', 'completed'])
                ->sum('price');

            $product = OrderItem::whereBetween('created_at', [$start, $end])
                ->sum(DB::raw('subtotal - discount'));

            $sparring = OrderSparring::whereBetween('created_at', [$start, $end])
                ->sum('price');

            $event = OrderEvent::whereBetween('created_at', [$start, $end])
                ->whereIn('status', ['paid', 'completed'])
                ->sum('total_payment');

            return $booking + $product + $sparring + $event;
        });

        $labels = collect(range(1, 12))->map(fn($m) => Carbon::create()->month($m)->format('M'));
        $dataPoints = $monthlyRevenue;


        // === RECENT ORDERS ===
        $recentOrders = Order::orderBy('created_at', 'desc')->take(5)->get();


        return view('dash.admin.overview', compact(
            'monthlyEarnings',
            'visits',
            'labels',
            'dataPoints',
            'recentOrders'
        ));
    }
}
