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

            <h1 class="text-3xl font-bold mb-4">Please Finish Your Payment</h1>
            <p class="text-gray-400 mb-8">Thank you for your purchase. Please send us your proof of payment to complete the order.</p>

            <div class="bg-[#222222] rounded-lg p-6 mb-8">
                <h2 class="text-xl font-bold mb-4 text-left">Order Details</h2>
                <div class="space-y-3 text-left">
                    <div class="flex justify-between">
                        <span class="text-gray-400">Order ID:</span>
                        <span>{{ $order->order_number }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Date:</span>
                        <span>{{ $order->created_at->format('d M Y, H:i') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Payment Method:</span>
                        <span>{{ ucfirst($order->payment_method) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Payment Status:</span>
                        <span class="text-green-500 font-medium">{{ ucfirst($order->payment_status) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Total:</span>
                        <span class="font-bold">Rp. {{ number_format($order->total, 0, ',', '.') }},-</span>
                    </div>
                </div>
                {{-- Uplaod file --}}
                <form action="{{ route('checkout.updatePayment', $order) }}" method="POST" enctype="multipart/form-data" class="mt-6">
                    @method('PUT')
                    @csrf
                    <div class="mb-4">
                        {{-- Show image --}}
                        @if($order->file)
                            <div class="mb-4">
                                <label class="block text-gray-400 mb-2">Current Proof of Payment</label>
                                <img src="{{ asset('storage/' . $order->file) }}" alt="Proof of Payment" class="w-full h-auto rounded-md">
                            </div>
                        @endif
                        <label for="proof_of_payment" class="block text-gray-400 mb-2">Upload Proof of Payment</label>
                        <input type="file" name="file" id="file" accept=".jpg,.jpeg,.png,.pdf" required class="bg-[#333333] text-white rounded-md p-2 w-full">
                    </div>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-3 px-6 rounded-md">
                        Upload Proof
                    </button>
                </form>
            </div>

            <div class="flex justify-center space-x-4">
                <a href="{{ route('index') }}" class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-3 px-6 rounded-md">
                    Back to Home
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
