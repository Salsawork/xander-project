<!-- Recent Session Card -->
<div class="bg-background-950 rounded-lg p-6 shadow-md">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-white text-xl font-semibold">Recent Session</h2>
        <button class="text-white">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6">
                <circle cx="12" cy="12" r="1"></circle>
                <circle cx="19" cy="12" r="1"></circle>
                <circle cx="5" cy="12" r="1"></circle>
            </svg>
        </button>
    </div>

    <div class="space-y-4">
        @php
            // Dapatkan ID user yang sedang login (athlete)
            $athleteId = auth()->id();

            // Ambil sesi terakhir yang diikuti athlete (maksimal 3)
            $recentSessions = App\Models\Participants::where('user_id', $athleteId)
                ->where('status', '!=', 'cancelled')
                ->with(['billiardSession.venue'])
                ->latest()
                ->take(3)
                ->get();
        @endphp

        @forelse($recentSessions as $participant)
            @php
                $session = $participant->billiardSession;
                $venue = $session->venue;

                // Format tanggal dan waktu
                $date = \Carbon\Carbon::parse($session->date)->format('j F Y');
                $startTime = \Carbon\Carbon::parse($session->start_time)->format('H.i');
                $endTime = \Carbon\Carbon::parse($session->end_time)->format('H.i');
                $timeRange = $startTime . '-' . $endTime;
            @endphp

            <div class="border-t border-gray-700 pt-4">
                <div class="flex justify-between">
                    <div>
                        <h3 class="text-white font-medium">{{ $venue->name }}</h3>
                        <p class="text-gray-400 text-sm">Table #{{ $session->id }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-white">{{ $date }}</p>
                        <p class="text-gray-400 text-sm">{{ $timeRange }}</p>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-4 text-gray-400">
                <p>No recent sessions found</p>
            </div>
        @endforelse
    </div>
</div>
