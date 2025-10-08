@extends('app')
@section('title', 'Session Detail - Xander Billiard')

@section('content')
<div class="min-h-screen bg-neutral-900 text-gray-100 font-sans py-10">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 md:px-8">
        <!-- Header -->
        <div class="flex items-center mb-6">
            <i class="fas fa-chevron-left text-xl mr-4 cursor-pointer hover:text-blue-400 transition" onclick="window.history.back();"></i>
            <h1 class="text-xl sm:text-2xl font-semibold items-center">
                Session Detail
                <span class="text-blue-500 font-bold">#XB12345678</span>
                <span>|</span>
                <span class="text-sm text-gray-400 my-auto">02/06/2025 at 10.41</span>
            </h1>
        </div>

        <!-- Booking & Customer Info -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <!-- Booking Info -->
            <div class="md:col-span-3 bg-neutral-900 rounded-lg p-5 shadow-sm border border-neutral-800">
                <h2 class="text-lg font-semibold mb-4">Booking Information</h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-y-3 text-sm">
                    <div>
                        <p class="text-gray-400">Location</p>
                        <p class="font-medium">The Cue Lounge</p>
                    </div>
                    <div>
                        <p class="text-gray-400">Price</p>
                        <p class="font-medium">Rp. 60.000 /session</p>
                    </div>
                    <div>
                        <p class="text-gray-400">Table</p>
                        <p class="font-medium">Table #125</p>
                    </div>
                    <div>
                        <p class="text-gray-400">Booking Date</p>
                        <p class="font-medium">March 5, 2025</p>
                    </div>
                    <div>
                        <p class="text-gray-400">Session Time</p>
                        <p class="font-medium">14.00–15.00<br>15.00–16.00</p>
                    </div>
                    <div>
                        <p class="text-gray-400">Promo Code</p>
                        <p class="font-medium">JNTX1235</p>
                    </div>
                    <div class="sm:col-span-3">
                        <p class="text-gray-400">Booking ID</p>
                        <div class="flex items-center gap-2">
                            <span class="font-medium">#BK202503021234</span>
                            <button class="text-gray-400 rounded-xl border border-gray-400 px-2 py-0.5 text-xs">Copy</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Info -->
            <div class="bg-neutral-900 rounded-lg p-5 shadow-sm border border-neutral-800">
                <h2 class="text-lg font-semibold mb-4">Customer Information</h2>
                <div class="text-sm space-y-1">
                    <p class="font-semibold text-base">Alex Johnson</p>
                    <a href="mailto:alex.johnson@email.com" class="text-gray-300 hover:text-white block">alex.johnson@email.com</a>
                    <p class="text-gray-300">+1 555-789-1234</p>
                </div>
                <div class="mt-4">
                    <p class="text-gray-400 text-sm mb-1">Payment Details:</p>
                    <p class="text-gray-300">Credit Card (Visa)</p>
                </div>
                <button class="w-full mt-5 bg-blue-600 hover:bg-blue-700 text-white text-sm py-2 rounded-md font-medium transition">Download Invoice</button>
            </div>
        </div>

        <!-- Policies & Rules -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <!-- Cancellation Policy -->
            <div class="md:col-span-3 bg-neutral-900 rounded-lg p-5 shadow-sm border border-neutral-800">
                <h2 class="text-lg font-semibold mb-3">Cancellation & Reschedule Policy</h2>
                <p class="text-sm text-gray-300 mb-3">
                    We understand that plans can change. If you need to cancel or reschedule your booking, please follow our guidelines:
                </p>
                <ul class="list-disc list-inside space-y-2 text-sm text-gray-300">
                    <li>Cancellations must be made at least <span class="text-white">24 hours</span> before the scheduled time to receive a full refund.</li>
                    <li>If a cancellation request is made within <span class="text-white">less than 24 hours</span>, a <span class="text-white">50% cancellation fee</span> will apply.</li>
                    <li>Rescheduling is allowed up to <span class="text-white">12 hours</span> before your booking, subject to table availability.</li>
                    <li>No-shows will not be eligible for refunds.</li>
                </ul>
            </div>

            <!-- Venue Rules -->
            <div class="bg-neutral-900 rounded-lg p-5 shadow-sm border border-neutral-800">
                <h2 class="text-lg font-semibold mb-4">Venue Rules & Guidelines</h2>
                <p class="text-sm text-gray-300 mb-3">To ensure a great experience for all players, please adhere to the following rules:</p>
                <ul class="list-disc list-inside space-y-2 text-sm text-gray-300">
                    <li>Players must check in at least 10 minutes before the reserved time.</li>
                    <li>Outside food and drinks are not allowed, except bottled water.</li>
                    <li>Players are responsible for any damage to the table or equipment during play.</li>
                    <li>The venue reserves the right to terminate a booking if rules are violated.</li>
                </ul>
            </div>
        </div>

        <!-- Payment Policy + Contact -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Payment Policy -->
            <div class="md:col-span-3 bg-neutral-900 rounded-lg p-5 shadow-sm border border-neutral-800">
                <h2 class="text-lg font-semibold mb-3">Payment & Refund Policy</h2>
                <p class="text-sm text-gray-300 mb-3">
                    All payments must be completed at the time of booking. We accept credit/debit cards, PayPal, and digital wallets.
                </p>
                <p class="text-sm text-gray-300 mb-3">
                    Refunds for eligible cancellations will be processed within <span class="text-white">5–7 business days</span> to the original payment method.
                </p>
                <p class="text-sm text-gray-300">
                    If you experience issues with your refund, please contact our support team at
                    <a href="mailto:support@billiardvenue.com" class="text-blue-500 hover:underline">support@billiardvenue.com</a>.
                </p>
            </div>

            <!-- Contact -->
            <div class="bg-neutral-900 rounded-lg p-5 shadow-sm border border-neutral-800">
                <p class="text-sm text-gray-300 mb-4">
                    Upon arrival, please check in at the front desk by showing your booking confirmation email. If you have any questions or require assistance, contact us:
                </p>
                <div class="space-y-2 text-sm text-gray-300">
                    <p><span class="text-gray-400">Phone:</span> +1 234 567 890</p>
                    <p><span class="text-gray-400">Email:</span> <a href="mailto:support@billiardvenue.com" class="text-blue-500 hover:underline">support@billiardvenue.com</a></p>
                    <p><span class="text-gray-400">Address:</span> 4568 Greenway Street, Los Angeles, CA</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection