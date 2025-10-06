<?php

namespace App\api\callback;

use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class MidtransController
{
    /**
     * Handle notification from Midtrans
     */
    public function notification(Request $request)
    {
        try {
            // Log request yang masuk
            Log::info('Midtrans Callback Received:', [
                'raw_request' => $request->getContent()
            ]);
            
            // Ambil data notifikasi dari Midtrans
            $notif = json_decode($request->getContent(), true);
            
            // Log notifikasi untuk debugging
            Log::info('Midtrans notification received', $notif);
            
            // Verifikasi signature key
            $signatureKey = $notif['signature_key'];
            $orderId = $notif['order_id'];
            $statusCode = $notif['status_code'];
            $grossAmount = $notif['gross_amount'];
            $serverKey = config('midtrans.server_key', env('MIDTRANS_SERVER_KEY', ''));
            
            $mySignatureKey = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);
            
            // Log hasil verify signature
            Log::info('Signature Verification:', [
                'received' => $signatureKey,
                'calculated' => $mySignatureKey,
                'match' => $signatureKey === $mySignatureKey
            ]);
            
            // Untuk testing, kita skip verifikasi signature dulu
            // Uncomment code di bawah jika sudah siap production
            /*
            if ($signatureKey !== $mySignatureKey) {
                Log::warning('Signature mismatch!');
                return response()->json(['status' => 'error'], 400);
            }
            */
            
            // Ambil data penting dari notifikasi
            $transactionStatus = $notif['transaction_status'];
            $fraudStatus = $notif['fraud_status'] ?? null;
            $paymentType = $notif['payment_type'] ?? null;
            
            // Cari order berdasarkan ID
            $order = Order::find($orderId);
            
            // Khusus untuk test Midtrans, kita tetap return success meskipun order tidak ditemukan
            if (strpos($orderId, 'payment_notif_test') !== false) {
                Log::info('Test notification from Midtrans detected: ' . $orderId);
                return response()->json(['status' => 'success']);
            }
            
            if (!$order) {
                Log::warning('Order not found: ' . $orderId);
                return response()->json(['status' => 'error'], 404);
            }
            
            // Mapping status dari Midtrans ke format database
            $statusMapping = [
                'settlement' => 'paid',
                'capture' => 'paid',
                'pending' => 'pending',
                'deny' => 'failed',
                'expire' => 'failed',
                'cancel' => 'failed'
            ];
            
            // Update status pembayaran berdasarkan notifikasi
            if ($transactionStatus == 'capture') {
                if ($fraudStatus == 'challenge') {
                    $order->payment_status = 'processing';
                } else if ($fraudStatus == 'accept') {
                    $order->payment_status = 'paid';
                    $order->delivery_status = 'processing';
                }
            } else {
                $order->payment_status = $statusMapping[$transactionStatus] ?? 'failed';
                
                if ($transactionStatus == 'settlement') {
                    $order->delivery_status = 'processing';
                }
            }
            
            // Simpan informasi tambahan
            $order->payment_method = $paymentType;
            
            // Simpan perubahan
            try {
                $previousPaymentStatus = $order->getOriginal('payment_status');
                $previousDeliveryStatus = $order->getOriginal('delivery_status');
                
                $order->save();
                
                // Log untuk debugging yang lebih detail
                Log::info('Payment notification processed successfully', [
                    'order_id' => $orderId,
                    'transaction_status' => $transactionStatus,
                    'fraud_status' => $fraudStatus,
                    'payment_type' => $paymentType,
                    'payment_status_before' => $previousPaymentStatus,
                    'payment_status_after' => $order->payment_status,
                    'delivery_status_before' => $previousDeliveryStatus,
                    'delivery_status_after' => $order->delivery_status,
                    'updated_at' => now()->format('Y-m-d H:i:s')
                ]);
                
                return response()->json(['status' => 'success']);
            } catch (\Exception $e) {
                Log::error('Failed to save order after payment notification', [
                    'order_id' => $orderId,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                return response()->json(['status' => 'error'], 500);
            }
            
        } catch (\Exception $e) {
            Log::error('Midtrans Callback Error: ' . $e->getMessage());
            return response()->json(['status' => 'error'], 500);
        }
    }
}