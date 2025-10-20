<?php

namespace App\Http\Controllers\athleteController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderSparring;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    /**
     * Tampilkan daftar transaksi milik venue user yang login.
     */
    public function index(Request $request)
    {
        $orders = Order::where('order_type', 'sparring')
            ->whereHas('orderSparrings', function ($q) {
                $q->where('athlete_id', Auth::id());
            })
            ->when($request->search, function ($query) use ($request) {
                $query->whereHas('user', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%')
                        ->orWhere('email', 'like', '%' . $request->search . '%');
                });
            })
            ->when($request->status, function ($query) use ($request) {
                $query->where('payment_status', $request->status);
            })
            // hanya 1 orderBy, kasih default DESC
            ->orderBy('created_at', $request->orderBy === 'asc' ? 'asc' : 'desc')
            ->get();

        $pendingCount    = Order::where('order_type', 'sparring')->whereHas('orderSparrings', function ($q) {
            $q->where('athlete_id', Auth::id());
        })->where('payment_status', 'pending')->count();
        $processingCount = Order::where('order_type', 'sparring')->whereHas('orderSparrings', function ($q) {
            $q->where('athlete_id', Auth::id());
        })->where('payment_status', 'processing')->count();
        $paidCount       = Order::where('order_type', 'sparring')->whereHas('orderSparrings', function ($q) {
            $q->where('athlete_id', Auth::id());
        })->where('payment_status', 'paid')->count();
        $failedCount     = Order::where('order_type', 'sparring')->whereHas('orderSparrings', function ($q) {
            $q->where('athlete_id', Auth::id());
        })->where('payment_status', 'failed')->count();
        $refundedCount   = Order::where('order_type', 'sparring')->whereHas('orderSparrings', function ($q) {
            $q->where('athlete_id', Auth::id());
        })->where('payment_status', 'refunded')->count();

        return view('dash.athlete.transaction', compact(
            'orders',
            'pendingCount',
            'processingCount',
            'paidCount',
            'failedCount',
            'refundedCount'
        ));
    }

    /**
     * Tampilkan detail transaksi tertentu.
     */
    public function show($orderId)
    {
        $athlete = Auth::user();

        $transaction = OrderSparring::with(['schedule', 'order'])
            ->where('athlete_id', $athlete->id)
            ->where('order_id', $orderId)
            ->firstOrFail();

        return view('dash.athlete.transaction-show', compact('transaction'));
    }

    public function verifyBooking($id)
    {
        $transaction = OrderSparring::with(['schedule', 'order'])->findOrFail($id);

        if (!$transaction->order || $transaction->order->payment_status !== 'paid') {
            return redirect()->back()->with('error', 'Pembayaran belum diverifikasi oleh admin pusat.');
        }

        // Update the related schedule record, not the OrderSparring itself
        if ($transaction->schedule) {
            $transaction->schedule->update(['is_booked' => true]);
        }

        return redirect()->back()->with('success', 'Booking berhasil diverifikasi oleh admin venue.');
    }
}
