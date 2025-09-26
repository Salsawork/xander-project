@php
    $notifications = [
        [
            'title' => 'Low Table Availability!',
            'desc'  => 'Only 2 tables are available for the next 3 hours.'
        ],
        [
            'title' => 'Price Updated Successfully!',
            'desc'  => 'Weekend sessions is now set at Rp. 80.000'
        ],
        [
            'title' => 'Booking Confirmed!',
            'desc'  => 'March 10, 13:00 PM - 14:00 has been reserved.'
        ],
        [
            'title' => 'New Venue Review!',
            'desc'  => 'A player just left a 5-star rating for your venue.'
        ],
    ];
@endphp

<div class="flex justify-between items-center mb-2">
    <span class="text-white text-lg font-semibold">Notification</span>
    <span class="text-white text-2xl cursor-pointer">...</span>
</div>
<hr class="border-gray-600 mb-4">

@foreach($notifications as $notif)
    <div class="mb-4">
        <div class="text-white font-bold">{{ $notif['title'] }}</div>
        <div class="text-gray-400 text-sm">{{ $notif['desc'] }}</div>
    </div>
@endforeach