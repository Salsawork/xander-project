@extends('app')
@section('title', 'Session Detail - Xander Billiard')

@section('content')
<div class="min-h-screen bg-neutral-900 text-gray-100 font-sans py-10">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 md:px-8">
        <!-- Header -->
        <div class="flex items-center mb-6">
            <i class="fas fa-chevron-left text-xl mr-4 cursor-pointer hover:text-blue-400 transition"
                onclick="window.history.back();"></i>
            <h1 class="text-xl sm:text-2xl font-semibold items-center">
                Session Detail
                <span class="text-blue-500 font-bold">{{ $order->order_number }}</span>
                <span>|</span>
                <span class="text-sm text-gray-400 my-auto">
                    {{ $order->created_at->setTimezone('Asia/Jakarta')->format('d/m/Y \a\t H:i') }}
                </span>
            </h1>
        </div>

        <!-- Booking & Customer Info -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <!-- Booking Info -->
            <div class="md:col-span-3 bg-neutral-900 rounded-lg p-5 shadow-sm border border-neutral-800">
                <h2 class="text-lg font-semibold mb-4">Sparring Information</h2>

                @foreach ($sparrings as $sparring)
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-y-3 text-sm mb-6 border-b border-neutral-800 pb-4">
                    <div>
                        <p class="text-gray-400">Location</p>
                        <p class="font-medium">{{ $sparring->athlete->name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400">Price</p>
                        <p class="font-medium">Rp. {{ number_format($sparring->price, 0, ',', '.') }} /session</p>
                    </div>
                    <div>
                        <p class="text-gray-400">Booking Date</p>
                        <p class="font-medium">
                           {{ \Carbon\Carbon::parse($sparring->schedule->date)->format('d/m/Y') }} 
                        </p>
                    </div>
                    <div>
                        <p class="text-gray-400">Session Time</p>
                        <p class="font-medium">
                            {{ \Carbon\Carbon::parse($sparring->schedule->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($sparring->schedule->end_time)->format('H:i') }}
                        </p>
                    </div>
                    <div>
                        <p class="text-gray-400">Status</p>
                        <p class="font-medium capitalize">
                            {{ $sparring->schedule->is_booked ? 'Booked' : '' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-gray-400">Booking ID</p>
                        <div class="flex items-center gap-2">
                            <span class="font-medium">#SR{{ $sparring->id }}</span>
                            <button onclick="navigator.clipboard.writeText('#SR{{ $sparring->id }}')"
                                class="text-gray-400 rounded-xl border border-gray-400 px-2 py-0.5 text-xs hover:bg-gray-700">
                                Copy
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Customer Info -->
            <div class="bg-neutral-900 rounded-lg p-5 shadow-sm border border-neutral-800">
                <h2 class="text-lg font-semibold mb-4">Customer Information</h2>
                <div class="text-sm space-y-1">
                    <p class="font-semibold text-base">{{ $user->name ?? 'Alex Johnson' }}</p>
                    <a href="mailto:{{ $user->email ?? 'alex.johnson@email.com' }}" class="text-gray-300 hover:text-white block">{{ $user->email ?? 'alex.johnson@email.com' }}</a>
                    <p class="text-gray-300">{{ $user->phone ?? '+1 555-789-1234' }}</p>
                </div>
                <div class="mt-4">
                    <p class="text-gray-400 text-sm mb-1">Address:</p>
                    <p class="text-gray-300">{{ $order->products->first()->pivot->address ?? '-' }}</p>
                </div>
                <div class="mt-4">
                    <p class="text-gray-400 text-sm mb-1">Payment Details:</p>
                    <p class="text-gray-300 capitalize">{{ str_replace('_', ' ', $order->payment_method) }}</p>
                    <p class="text-gray-300">Status: <span class="text-green-400 font-semibold">{{ $order->payment_status ?? 'Paid' }}</span></p>
                    <p class="text-gray-400 text-xs mt-1">Transaction ID: <span class="text-gray-300">{{ $order->transaction_id ?? '-' }}</span></p>
                </div>
                @if ($order->payment_status === 'paid')
                <a href="{{ route('invoice.sparring', $order->id) }}" class="w-full mt-5 bg-blue-600 hover:bg-blue-700 text-white text-sm py-2 rounded-md font-medium transition inline-block text-center">Download Invoice</a>
                @endif
            </div>
        </div>

        <!-- Policies & Rules -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <!-- Cancellation Policy -->
            <div class="md:col-span-3 bg-neutral-900 rounded-lg p-5 shadow-sm border border-neutral-800">
                <h2 class="text-lg font-semibold mb-3">Cancellation & Rescheduling Policy</h2>
                <p class="text-sm text-gray-300 mb-3">
                    We understand schedules can change. If you need to cancel or reschedule your sparring booking, please follow these terms:
                </p>
                <ul class="list-disc list-inside space-y-2 text-sm text-gray-300">
                    <li>Cancellations must be made at least <span class="text-white">24 hours</span> before the scheduled time for a full refund.</li>
                    <li>If a cancellation request is made <span class="text-white">less than 24 hours</span> in advance, a <span class="text-white">50% cancellation fee</span> will apply.</li>
                    <li>Rescheduling is allowed up to <span class="text-white">12 hours</span> before the session, subject to sparring athlete availability.</li>
                    <li>No-shows without confirmation are non-refundable.</li>
                </ul>
            </div>

            <!-- Venue Rules -->
            <div class="bg-neutral-900 rounded-lg p-5 shadow-sm border border-neutral-800">
                <h2 class="text-lg font-semibold mb-4">Sparring Rules & Guidelines</h2>
                <p class="text-sm text-gray-300 mb-3">For an optimal sparring experience, please adhere to the following rules:</p>
                <ul class="list-disc list-inside space-y-2 text-sm text-gray-300">
                    <li>Players must confirm attendance at least 10 minutes before the session starts.</li>
                    <li>Outside food and drinks are not allowed, except for mineral water.</li>
                    <li>Players are responsible for any equipment damage during the sparring session.</li>
                    <li>The venue reserves the right to end the session if rules are violated.</li>
                </ul>
            </div>
        </div>

        <!-- Payment Policy + Contact -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Payment Policy -->
            <div class="md:col-span-3 bg-neutral-900 rounded-lg p-5 shadow-sm border border-neutral-800">
                <h2 class="text-lg font-semibold mb-3">Payment & Refund Policy</h2>
                <p class="text-sm text-gray-300 mb-3">
                    All payments must be completed at the time of booking. We accept bank transfers, e-wallets, and credit/debit cards.
                </p>
                <p class="text-sm text-gray-300 mb-3">
                    Refunds for eligible cancellations will be processed within <span class="text-white">5–7 business days</span> to the original payment method.
                </p>
                <p class="text-sm text-gray-300">
                    If you experience any issues with refunds, please contact our support team at
                    <a href="mailto:support@xanderbilliard.com" class="text-blue-500 hover:underline">support@xanderbilliard.com</a>.
                </p>
            </div>

            <!-- Contact -->
            <div class="bg-neutral-900 rounded-lg p-5 shadow-sm border border-neutral-800">
                <p class="text-sm text-gray-300 mb-4">
                    Upon arrival, confirm your attendance with the sparring athlete by showing your booking confirmation email. If you have any questions or need assistance, contact us:
                </p>
                <div class="space-y-2 text-sm text-gray-300">
                    <p><span class="text-gray-400">WhatsApp:</span> +62 812 3456 7890</p>
                    <p><span class="text-gray-400">Email:</span> <a href="mailto:support@xanderbilliard.com" class="text-blue-500 hover:underline">support@xanderbilliard.com</a></p>
                    <p><span class="text-gray-400">Address:</span> Jl. Billiard No. 88, South Jakarta</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection