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
    public function index(Request $request)
{
    $orders = Order::when($request->search, function ($query) use ($request) {
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

    $pendingCount    = Order::where('delivery_status', 'pending')->count();
    $processingCount = Order::where('delivery_status', 'processing')->count();
    $shippedCount    = Order::where('delivery_status', 'shipped')->count();
    $deliveredCount  = Order::where('delivery_status', 'delivered')->count();
    $cancelledCount  = Order::where('delivery_status', 'cancelled')->count();

    return view('dash.admin.order', compact(
        'orders',
        'pendingCount',
        'processingCount',
        'shippedCount',
        'deliveredCount',
        'cancelledCount'
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

        return view('dash.admin.detailOrder', compact('order', 'statusClass'));
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
            return redirect()->route('order.index')->with('error', 'Status pengiriman tidak valid');
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

            return redirect()->route('order.index')->with('success', 'Status pengiriman berhasil diperbarui');
        } catch (\Exception $e) {
            Log::error('Failed to update status', [
                'order_id' => $orderId,
                'status' => $status,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('order.index')->with('error', 'Gagal mengubah status: ' . $e->getMessage());
        }
    }
}
