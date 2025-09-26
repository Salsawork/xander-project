@extends('app')

@section('title', 'Sparring Detail')

@section('content')
    <div class="bg-gray-950 text-white min-h-screen overflow-hidden">
        <!-- Header Section with Background Image -->
        <div class="relative bg-gray-900">
            <div class="absolute inset-0 overflow-hidden">
                <img src="{{ asset('images/billiard.jpg') }}" alt="Billiard Table"
                    class="w-full h-full object-cover opacity-30">
                <div class="absolute inset-0 bg-black/50"></div>
            </div>
            <div class="relative max-w-7xl mx-auto px-6 py-16">
                <div class="flex flex-col space-y-2">
                    <div class="flex items-center space-x-2 text-sm">
                        <a href="/" class="text-gray-400 hover:text-white">Home</a>
                        <span class="text-gray-600">/</span>
                        <a href="{{ route('sparring.index') }}" class="text-gray-400 hover:text-white">Sparring</a>
                        <span class="text-gray-600">/</span>
                        <span class="text-gray-400">{{ $athlete->name }}</span>
                    </div>
                    <h1 class="text-4xl md:text-5xl font-bold mt-2 mb-4">SPARRING WITH PRO</h1>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 py-10 grid grid-cols-1 md:grid-cols-3 gap-10">
            <!-- Left Column: Photo + Bio -->
            <div class="md:col-span-1">
                @if ($athlete->athleteDetail && $athlete->athleteDetail->image)
                    <img src="{{ asset('images/athlete/' . $athlete->athleteDetail->image) }}" alt="{{ $athlete->name }}"
                        class="rounded-lg shadow-md w-full object-cover"
                        onerror="this.src='{{ asset('images/placeholder.jpg') }}'">
                @else
                    <img src="{{ asset('images/placeholder.jpg') }}" alt="{{ $athlete->name }}"
                        class="rounded-lg shadow-md w-full object-cover">
                @endif
            </div>

            <!-- Middle Column: Details -->
            <div class="md:col-span-1 space-y-4">
                <h2 class="text-3xl font-bold">{{ $athlete->name }}</h2>
                <p class="text-gray-400">{{ $athlete->athleteDetail->handicap ?? 'Handicap N/A' }}</p>

                <div class="text-sm space-y-1 mt-4">
                    <p><span class="font-semibold">Year of Experience:</span>
                        {{ $athlete->athleteDetail->experience_years ?? 'N/A' }} Years</p>
                    <p><span class="font-semibold">Specialty:</span> {{ $athlete->athleteDetail->specialty ?? 'N/A' }}</p>
                    <p><span class="font-semibold">Location:</span> {{ $athlete->athleteDetail->location ?? 'N/A' }}</p>
                </div>

                <p class="text-sm text-gray-300 mt-4">
                    {{ $athlete->athleteDetail->bio ?? 'No bio available.' }}
                </p>

                <!-- Share Icons Placeholder -->
                <div class="flex space-x-3 mt-4">
                    <a href="#"
                        class="w-8 h-8 bg-gray-700 rounded-full flex items-center justify-center hover:bg-gray-600">
                        <i class="fab fa-facebook-f text-white"></i>
                    </a>
                    <a href="#"
                        class="w-8 h-8 bg-gray-700 rounded-full flex items-center justify-center hover:bg-gray-600">
                        <i class="fab fa-twitter text-white"></i>
                    </a>
                    <a href="#"
                        class="w-8 h-8 bg-gray-700 rounded-full flex items-center justify-center hover:bg-gray-600">
                        <i class="fab fa-instagram text-white"></i>
                    </a>
                </div>
            </div>

            <!-- Right Column: Booking -->
            <div class="bg-gray-900 p-6 rounded-lg shadow-md space-y-4">
                <p class="text-sm text-gray-400">Start from</p>
                <h3 class="text-2xl font-bold">Rp.
                    {{ number_format($athlete->athleteDetail->price_per_session ?? 0, 0, ',', '.') }} <span
                        class="text-sm font-normal">/ session</span></h3>

                <form action="{{ route('sparring.addToCart') }}" method="POST" class="space-y-3" id="addToCartForm">
                    @csrf
                    <input type="hidden" name="athlete_id" value="{{ $athlete->id }}">

                    <div>
                        <label class="text-sm">Schedule</label>
                        <div class="grid grid-cols-3 gap-2 mt-1 text-xs text-center">
                            @forelse ($schedules as $schedule)
                                <button type="button"
                                    class="schedule-btn bg-gray-800 hover:bg-blue-600 px-2 py-3 rounded cursor-pointer transition-colors duration-200"
                                    data-schedule-id="{{ $schedule->id }}"
                                    data-date="{{ date('d M Y', strtotime($schedule->date)) }}"
                                    data-time="{{ date('H:i', strtotime($schedule->start_time)) }}-{{ date('H:i', strtotime($schedule->end_time)) }}">
                                    {{ date('d/m', strtotime($schedule->date)) }}
                                    {{ date('H:i', strtotime($schedule->start_time)) }}
                                </button>
                            @empty
                                <p class="col-span-3 text-gray-400 text-center py-2">No schedules available</p>
                            @endforelse
                        </div>
                        <input type="hidden" name="schedule_id" id="selected_schedule_id" required>
                        <p id="selected_schedule_text" class="mt-2 text-sm text-blue-400 hidden"></p>
                    </div>

                    <div>
                        <label class="text-sm">Promo Code (optional)</label>
                        <input type="text" name="promo_code" placeholder="Ex. PROMOTODAY"
                            class="w-full mt-1 p-2 rounded bg-gray-800 text-white border border-gray-700">
                    </div>

                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 py-2 rounded font-semibold">
                        Add to cart
                    </button>
                </form>
            </div>
        </div>

        <!-- Customer Reviews -->
        <div class="max-w-7xl mx-auto px-4 mt-12">
            <h2 class="text-2xl font-bold mb-6">CUSTOMER REVIEWS</h2>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-gray-900 p-5 rounded-lg shadow-md">
                    <div class="flex items-center text-yellow-400 text-2xl font-bold mb-4">
                        <span class="text-3xl">4.3</span>
                        <span class="text-sm text-gray-400 ml-2">out of 5</span>
                    </div>
                    <div class="space-y-2">
                        <div class="flex items-center">
                            <div class="text-yellow-400">★★★★★</div>
                            <div class="w-full bg-gray-700 rounded-full h-2.5 ml-2">
                                <div class="bg-yellow-400 h-2.5 rounded-full" style="width: 85%"></div>
                            </div>
                            <span class="text-xs text-gray-400 ml-2">1932</span>
                        </div>
                        <div class="flex items-center">
                            <div class="text-yellow-400">★★★★☆</div>
                            <div class="w-full bg-gray-700 rounded-full h-2.5 ml-2">
                                <div class="bg-yellow-400 h-2.5 rounded-full" style="width: 12%"></div>
                            </div>
                            <span class="text-xs text-gray-400 ml-2">320</span>
                        </div>
                        <div class="flex items-center">
                            <div class="text-yellow-400">★★★☆☆</div>
                            <div class="w-full bg-gray-700 rounded-full h-2.5 ml-2">
                                <div class="bg-yellow-400 h-2.5 rounded-full" style="width: 3%"></div>
                            </div>
                            <span class="text-xs text-gray-400 ml-2">10</span>
                        </div>
                        <div class="flex items-center">
                            <div class="text-yellow-400">★★☆☆☆</div>
                            <div class="w-full bg-gray-700 rounded-full h-2.5 ml-2">
                                <div class="bg-yellow-400 h-2.5 rounded-full" style="width: 0%"></div>
                            </div>
                            <span class="text-xs text-gray-400 ml-2">0</span>
                        </div>
                        <div class="flex items-center">
                            <div class="text-yellow-400">★☆☆☆☆</div>
                            <div class="w-full bg-gray-700 rounded-full h-2.5 ml-2">
                                <div class="bg-yellow-400 h-2.5 rounded-full" style="width: 0%"></div>
                            </div>
                            <span class="text-xs text-gray-400 ml-2">0</span>
                        </div>
                    </div>
                </div>

                <div class="md:col-span-3 space-y-4">
                    @foreach ([
            [
                'name' => 'Michael Santoso',
                'date' => '25/01/2025',
                'text' => 'Sparring with ' . $athlete->name . ' was a game-changer! Their technique and patience in explaining the finer points of the game really helped me improve my skills. Highly recommended for players of all levels.',
                'stars' => 5,
            ],
            [
                'name' => 'Kevin Lim',
                'date' => '25/01/2025',
                'text' => $athlete->name . ' is an incredible mentor. They not only showed me how to improve my shots but also taught me about strategy and mental focus. Worth every penny!',
                'stars' => 5,
            ],
            [
                'name' => 'Rafi Putra',
                'date' => '25/01/2025',
                'text' => 'Playing against ' . $athlete->name . ' was a humbling yet inspiring experience. They were able to identify my weaknesses and provide practical advice on how to overcome them. Looking forward to our next session!',
                'stars' => 4,
            ],
        ] as $review)
                        <div class="bg-gray-900 p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow">
                            <div class="flex items-center space-x-4 mb-3">
                                <div
                                    class="w-12 h-12 bg-gray-700 rounded-full flex items-center justify-center text-xl font-bold">
                                    {{ substr($review['name'], 0, 1) }}
                                </div>
                                <div>
                                    <p class="font-semibold">{{ $review['name'] }}</p>
                                    <p class="text-xs text-gray-400">{{ $review['date'] }}</p>
                                </div>
                            </div>
                            <div class="text-yellow-400 text-sm mb-2">
                                {!! str_repeat('★', $review['stars']) !!}{!! str_repeat('☆', 5 - $review['stars']) !!}
                            </div>
                            <p class="text-gray-300">{{ $review['text'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Floating Shopping Cart Button -->
        <button aria-label="Shopping cart with {{ count($carts ?? []) + count($sparrings ?? []) }} items"
            onclick="showCart()"
            class="fixed right-6 bottom-10 bg-gray-800 hover:bg-gray-700 w-16 h-16 rounded-full shadow-xl flex items-center justify-center group transition-transform transform hover:scale-110 z-50">
            <i class="fas fa-shopping-cart text-white text-3xl">
                <!-- Badge -->
                <span
                    class="absolute top-1.5 right-1.5 bg-blue-600 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center animate-pulse">
                    {{ count($carts ?? []) + count($sparrings ?? []) }}
                </span>
            </i>
        </button>

        @include('public.cart')
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle schedule selection
            const scheduleButtons = document.querySelectorAll('.schedule-btn');
            const selectedScheduleId = document.getElementById('selected_schedule_id');
            const selectedScheduleText = document.getElementById('selected_schedule_text');

            // Debugging - cek apakah elemen ditemukan
            console.log('Schedule buttons found:', scheduleButtons.length);
            console.log('Selected schedule ID element:', selectedScheduleId);
            console.log('Selected schedule text element:', selectedScheduleText);

            scheduleButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault(); // Mencegah default action
                    console.log('Button clicked:', this.dataset.scheduleId);

                    // Remove active class from all buttons
                    scheduleButtons.forEach(btn => {
                        btn.classList.remove('bg-blue-600');
                        btn.classList.add('bg-gray-800');
                    });

                    // Add active class to clicked button
                    this.classList.remove('bg-gray-800');
                    this.classList.add('bg-blue-600');

                    // Set selected schedule ID
                    selectedScheduleId.value = this.dataset.scheduleId;

                    // Show selected schedule text
                    selectedScheduleText.textContent =
                        `Selected: ${this.dataset.date} (${this.dataset.time})`;
                    selectedScheduleText.classList.remove('hidden');
                });
            });

            // Handle add to cart form submission
            const addToCartForm = document.getElementById('addToCartForm');
            if (addToCartForm) {
                addToCartForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    // Cek apakah schedule sudah dipilih
                    if (!selectedScheduleId.value) {
                        Swal.fire({
                            title: 'Attention!',
                            text: 'Please select a sparring schedule first',
                            icon: 'warning',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#3085d6',
                            background: '#1E1E1F',
                            color: '#FFFFFF',
                            iconColor: '#FFC107'
                        });
                        return;
                    }

                    // Tampilkan SweetAlert
                    Swal.fire({
                        title: 'Success!',
                        text: 'Sparring session added to cart',
                        icon: 'success',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#3085d6',
                        background: '#1E1E1F',
                        color: '#FFFFFF',
                        iconColor: '#4BB543'
                    }).then((result) => {
                        // Kirim form setelah user klik OK
                        this.submit();
                    });
                });
            }
        });
    </script>
@endpush
