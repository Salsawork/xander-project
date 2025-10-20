<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    /**
     * Tampilkan semua order
     */
    public function indexProduct(Request $request)
    {
        $orders = Order::where('order_type', 'product')
            ->when($request->search, function ($query) use ($request) {
                $query->whereHas('user', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%')
                        ->orWhere('email', 'like', '%' . $request->search . '%');
                });
            })
            ->when($request->status, function ($query) use ($request) {
                $query->where('delivery_status', $request->status);
            })
            // hanya 1 orderBy, kasih default DESC
            ->orderBy('created_at', $request->orderBy === 'asc' ? 'asc' : 'desc')
            ->get();

        $pendingCount    = Order::where('order_type', 'product')->where('delivery_status', 'pending')->count();
        $processingCount = Order::where('order_type', 'product')->where('delivery_status', 'processing')->count();
        $shippedCount    = Order::where('order_type', 'product')->where('delivery_status', 'shipped')->count();
        $deliveredCount  = Order::where('order_type', 'product')->where('delivery_status', 'delivered')->count();
        $cancelledCount  = Order::where('order_type', 'product')->where('delivery_status', 'cancelled')->count();

        return view('dash.admin.order.product', compact(
            'orders',
            'pendingCount',
            'processingCount',
            'shippedCount',
            'deliveredCount',
            'cancelledCount'
        ));
    }
    public function indexBooking(Request $request)
    {
        $orders = Order::where('order_type', 'venue')
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

        $pendingCount    = Order::where('order_type', 'venue')->where('payment_status', 'pending')->count();
        $processingCount = Order::where('order_type', 'venue')->where('payment_status', 'processing')->count();
        $paidCount       = Order::where('order_type', 'venue')->where('payment_status', 'paid')->count();
        $failedCount     = Order::where('order_type', 'venue')->where('payment_status', 'failed')->count();
        $refundedCount   = Order::where('order_type', 'venue')->where('payment_status', 'refunded')->count();

        return view('dash.admin.order.booking', compact(
            'orders',
            'pendingCount',
            'processingCount',
            'paidCount',
            'failedCount',
            'refundedCount'
        ));
    }
    public function indexSparring(Request $request)
    {
        $orders = Order::where('order_type', 'sparring')
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

        $pendingCount    = Order::where('order_type', 'sparring')->where('payment_status', 'pending')->count();
        $processingCount = Order::where('order_type', 'sparring')->where('payment_status', 'processing')->count();
        $paidCount       = Order::where('order_type', 'sparring')->where('payment_status', 'paid')->count();
        $failedCount     = Order::where('order_type', 'sparring')->where('payment_status', 'failed')->count();
        $refundedCount   = Order::where('order_type', 'sparring')->where('payment_status', 'refunded')->count();

        return view('dash.admin.order.sparring', compact(
            'orders',
            'pendingCount',
            'processingCount',
            'paidCount',
            'failedCount',
            'refundedCount'
        ));
    }


    /**
     * Detail order
     */
    public function detail(Order $order = null)
    {
        $statusClass = [
            'pending'    => 'bg-[#3b82f6] text-white',
            'processing' => 'bg-[#fbbf24] text-[#78350f]',
            'packed'     => 'bg-[#3b82f6] text-white',
            'shipped'    => 'bg-[#3b82f6] text-white',
            'delivered'  => 'bg-[#22c55e] text-white',
            'cancelled'  => 'bg-[#f87171] text-[#7f1d1d]',
            'returned'   => 'bg-[#f87171] text-[#7f1d1d]',
        ];

        return view('dash.admin.order.detailOrder', compact('order', 'statusClass'));
    }

    /**
     * Hapus order
     */
    public function destroy($orderId)
    {
        try {
            $order = Order::findOrFail($orderId);
            $order->products()->detach();
            $order->delete();

            return response()->json([
                'success' => true,
                'message' => 'Order berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete order', [
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update status order
     */
    public function updateStatus(Request $request, $orderId)
    {
        Log::info('Update status request', [
            'order_id' => $orderId,
            'status' => $request->query('status'),
            'all_data' => $request->all()
        ]);

        $status = $request->query('status');
        $validStatuses = ['pending', 'processing', 'packed', 'shipped', 'delivered', 'cancelled', 'returned'];

        if (!$status || !in_array($status, $validStatuses)) {
            return redirect()->back()->with('error', 'Status pengiriman tidak valid');
        }

        try {
            $order = Order::findOrFail($orderId);
            $oldStatus = $order->delivery_status;
            $order->delivery_status = $status;
            $order->save();

            Log::info('Status updated', [
                'order_id' => $order->id,
                'old_status' => $oldStatus,
                'new_status' => $order->delivery_status
            ]);

            return redirect()->back()->with('success', 'Status pengiriman berhasil diperbarui');
        } catch (\Exception $e) {
            Log::error('Failed to update status', [
                'order_id' => $orderId,
                'status' => $status,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Gagal mengubah status: ' . $e->getMessage());
        }
    }
    public function updatePaymentStatus(Request $request, $orderId)
    {
        Log::info('Update status request', [
            'order_id' => $orderId,
            'status' => $request->query('status'),
            'all_data' => $request->all()
        ]);

        $status = $request->query('status');
        $validStatuses = ['pending', 'processing', 'paid', 'failed', 'refunded'];

        if (!$status || !in_array($status, $validStatuses)) {
            return redirect()->back()->with('error', 'Status pembayaran tidak valid');
        }

        try {
            $order = Order::findOrFail($orderId);
            $oldStatus = $order->payment_status;
            $order->payment_status = $status;
            $order->save();

            Log::info('Status updated', [
                'order_id' => $order->id,
                'old_status' => $oldStatus,
                'new_status' => $order->payment_status
            ]);

            return redirect()->back()->with('success', 'Status pembayaran berhasil diperbarui');
        } catch (\Exception $e) {
            Log::error('Failed to update status', [
                'order_id' => $orderId,
                'status' => $status,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Gagal mengubah status: ' . $e->getMessage());
        }
    }

    public function updateBookingStatus(Request $request, $orderId)
    {
        Log::info('Update booking status request', [
            'order_id' => $orderId,
            'status' => $request->query('status'),
            'all_data' => $request->all()
        ]);

        $status = $request->query('status');
        $validStatuses = ['pending', 'booked', 'confirmed', 'cancelled', 'completed'];

        if (!$status || !in_array($status, $validStatuses)) {
            return redirect()->back()->with('error', 'Status booking tidak valid');
        }

        try {
            $order = Order::findOrFail($orderId);
            $oldStatus = $order->bookings()->first()->status ?? null;
            $booking = $order->bookings()->first();
            if ($booking) {
                $booking->status = $status;
                $booking->save();
            }
            $order->save();

            Log::info('Booking status updated', [
                'order_id' => $order->id,
                'old_status' => $oldStatus,
                'new_status' => $order->bookings()->first()->status
            ]);

            return redirect()->back()->with('success', 'Status booking berhasil diperbarui');
        } catch (\Exception $e) {
            Log::error('Failed to update booking status', [
                'order_id' => $orderId,
                'status' => $status,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Gagal mengubah status booking: ' . $e->getMessage());
        }
    }
    
    public function updateSparringStatus(Request $request, $orderId)
    {
        Log::info('Update sparring status request', [
            'order_id' => $orderId,
            'status' => $request->query('status'),
            'all_data' => $request->all()
        ]);

        $status = $request->query('status');
        $validStatuses = [0, 1];

        if (!in_array((int)$status, $validStatuses, true)) {
            return redirect()->back()->with('error', 'Status sparring tidak valid');
        }

        try {
            $order = Order::findOrFail($orderId);

            // Ambil semua orderSparrings beserta schedule-nya
            $sparrings = $order->orderSparrings()->with('schedule')->get();

            if ($sparrings->isEmpty()) {
                throw new \Exception('Jadwal sparring tidak ditemukan');
            }

            $updatedCount = 0;

            foreach ($sparrings as $sparring) {
                if ($sparring->schedule) {
                    $oldStatus = $sparring->schedule->is_booked;
                    $sparring->schedule->is_booked = (int)$status;
                    $sparring->schedule->save();

                    Log::info('Sparring status updated', [
                        'order_id' => $order->id,
                        'schedule_id' => $sparring->schedule->id,
                        'old_status' => $oldStatus,
                        'new_status' => $sparring->schedule->is_booked
                    ]);

                    $updatedCount++;
                }
            }

            if ($updatedCount === 0) {
                throw new \Exception('Tidak ada jadwal sparring yang berhasil diperbarui');
            }

            return redirect()->back()->with('success', 'Status sparring berhasil diperbarui');
        } catch (\Exception $e) {
            Log::error('Failed to update sparring status', [
                'order_id' => $orderId,
                'status' => $status,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Gagal mengubah status sparring: ' . $e->getMessage());
        }
    }
}
