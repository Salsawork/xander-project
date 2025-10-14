<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Order;
use App\Models\Booking;
use App\Models\OrderSparring;

class InvoiceController extends Controller
{
    /**
     * Download invoice untuk produk.
     */
    public function productInvoice($orderId)
    {
        $order = Order::with(['user', 'products'])->findOrFail($orderId);
        $data = [
            'title' => 'Product Invoice',
            'order' => $order,
            'user' => $order->user,
            'items' => $order->products
        ];

        $pdf = Pdf::loadView('public.invoice.item', $data)->setPaper('A4', 'portrait');
        return $pdf->download('Invoice_Product_' . $order->order_number . '.pdf');
    }

    /**
     * Download invoice untuk booking.
     */
    public function bookingInvoice($orderId)
    {
        $order = Order::with(['user'])->findOrFail($orderId);
        $bookings = Booking::where('order_id', $order->id)->with(['venue', 'table'])->get();

        $data = [
            'title' => 'Booking Invoice',
            'order' => $order,
            'bookings' => $bookings,
            'user' => $order->user
        ];

        $pdf = Pdf::loadView('public.invoice.booking', $data)->setPaper('A4', 'portrait');
        return $pdf->download('Invoice_Booking_' . $order->order_number . '.pdf');
    }

    /**
     * Download invoice untuk sparring.
     */
    public function sparringInvoice($orderId)
    {
        $order = Order::with(['user'])->findOrFail($orderId);
        $sparrings = $order->orderSparrings;

        $data = [
            'title' => 'Sparring Invoice',
            'order' => $order,
            'sparrings' => $sparrings,
            'user' => $order->user
        ];

        $pdf = Pdf::loadView('public.invoice.sparring', $data)->setPaper('A4', 'portrait');
        return $pdf->download('Invoice_Sparring_' . $order->order_number . '.pdf');
    }
}
