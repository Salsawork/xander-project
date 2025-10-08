@extends('app')

@section('title', 'Payment Success - Xander Billiard')

@section('content')
<div class="min-h-screen bg-[#1E1E1F] text-white py-16">
    <div class="max-w-3xl mx-auto px-4">
        <div class="bg-[#2D2D2D] rounded-lg p-8 text-center">
            <div class="mb-6">
                <div class="w-24 h-24 bg-green-500 rounded-full flex items-center justify-center mx-auto">
                    <i class="fas fa-check text-white text-4xl"></i>
                </div>
            </div>
            
            <h1 class="text-3xl font-bold mb-4">Payment Successful!</h1>
            <p class="text-gray-400 mb-8">Thank you for your purchase. Your order has been successfully processed.</p>
            
            <div class="bg-[#222222] rounded-lg p-6 mb-8">
                <h2 class="text-xl font-bold mb-4 text-left">Order Details</h2>
                <div class="space-y-3 text-left">
                    <div class="flex justify-between">
                        <span class="text-gray-400">Order ID:</span>
                        <span>{{ $order->id }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Date:</span>
                        <span>{{ $order->created_at->format('d M Y, H:i') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Payment Method:</span>
                        <span>{{ $order->payment_method === 'transfer_manual' ? 'Transfer manual' : ucfirst($order->payment_method) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Payment Status:</span>
                        <span class="text-green-500 font-medium">{{ ucfirst($order->payment_status) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Total:</span>
                        <span class="font-bold">Rp. {{ number_format($order->total, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
            
            <div class="flex justify-center space-x-4">
                <a href="{{ route('index') }}" class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-3 px-6 rounded-md">
                    Back to Home
                </a>
                <a href="{{ route('order.detail', ['order' => $order->id]) }}" class="bg-gray-700 hover:bg-gray-600 text-white font-medium py-3 px-6 rounded-md">
                    View Order Details
                </a>
            </div>
        </div>
    </div>
</div>
@endsection