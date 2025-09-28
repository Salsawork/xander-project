@extends('app')
@section('title', 'Venues - Xander Billiard')

@section('content')
    <div class="min-h-screen bg-neutral-900 text-white">
        <div class="mb-16 bg-cover bg-center p-24" style="background-image: url('/images/bg/product_breadcrumb.png');">
            <p class="text-sm text-gray-400 mt-1"><span onclick="window.location='{{ route('index') }}'">Home</span> / Venue
            </p>
            <h2 class="text-4xl font-bold uppercase text-white">FIND YOUR FAVORITE VENUE HERE</h2>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 px-24 py-18">

            <div class="w-full space-y-6 rounded-xl bg-neutral-900 p-4 text-white text-sm">
                <form method="GET" action="{{ route('venues.index') }}" class="space-y-4">

                    <div>
                        <input type="text" name="search" placeholder="Search" value="{{ request('search') }}"
                            class="w-full rounded border border-gray-400 bg-transparent px-3 py-1.5 text-sm placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                    </div>
                    <!-- <div class="border-t border-gray-500 pt-4">
                        <div class="flex items-center justify-between mb-2 font-semibold">
                            <span>Date</span>
                            <span class="text-xl leading-none text-gray-300">–</span>
                        </div>
                        <div class="flex items-center gap-2 justify-center">
                            <button type="button" class="text-gray-400 hover:text-white">&#60;</button>
                            <span>February</span>
                            <button type="button" class="text-gray-400 hover:text-white">&#62;</button>
                        </div>
                        <div class="grid grid-cols-7 gap-1 text-center mt-2 text-xs text-gray-400">
                            @for($i = 1; $i <= 28; $i++)
                                <span class="py-1">{{ $i }}</span>
                            @endfor
                        </div>
                    </div> -->

                    <div x-data="calendar()" class="bg-neutral-900 pt-4 text-white rounded-xl text-sm">
                        <div class="flex items-center justify-between mb-2 font-semibold">
                            <span>Date</span>
                            <span @click="toggle = !toggle" class="cursor-pointer text-xl">–</span>
                        </div>

                        <div class="flex items-center justify-center gap-2 mb-2">
                            <button type="button" @click="prevMonth()" class="text-gray-400 hover:text-white">&lt;</button>
                            <span x-text="monthNames[month] + ' ' + year"></span>
                            <button type="button" @click="nextMonth()" class="text-gray-400 hover:text-white">&gt;</button>
                        </div>


                        <div class="grid grid-cols-7 gap-1 text-center text-gray-400 text-xs">
                            <template x-for="d in daysInMonth()" :key="d">
                                <span class="py-1 cursor-pointer hover:bg-gray-600 rounded" @click="selectDate(d)"
                                    x-text="d"></span>
                            </template>
                        </div>
                    </div>

                    <div class="border-t border-gray-500 pt-4">
                        <div class="flex items-center justify-between mb-2 font-semibold">
                            <span>Location</span>
                            <span class="text-xl leading-none text-gray-300">–</span>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            @foreach(['Depok', 'Bekasi', 'Tangerang', 'Bogor'] as $loc)
                                <label class="px-3 py-1 rounded-full border border-gray-500 text-gray-400 cursor-pointer">
                                    <input type="radio" name="address" value="{{ $loc }}" class="hidden"
                                        @checked(request('address') == $loc) />
                                    {{ $loc }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Price Range -->
                    <div class="border-t border-gray-500 pt-4">
                        <div class="flex items-center justify-between mb-2 font-semibold">
                            <span>Price Range</span>
                            <span class="text-xl leading-none text-gray-300">–</span>
                        </div>
                        <div class="w-full flex items-center gap-2">
                            <input type="number" name="price_min" placeholder="Min" value="{{ request('price_min') }}"
                                class="w-1/2 rounded border border-gray-400 px-2 py-1 focus:outline-none focus:ring focus:ring-blue-500" />
                            <input type="number" name="price_max" placeholder="Max" value="{{ request('price_max') }}"
                                class="w-1/2 rounded border border-gray-400 px-2 py-1 focus:outline-none focus:ring focus:ring-blue-500" />
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="flex gap-2 pt-2">
                        <button type="submit"
                            class="flex-1 rounded bg-blue-500 px-4 py-2 font-medium text-white hover:bg-blue-600">
                            Filter
                        </button>
                        <a href="{{ route('venues.index') }}"
                            class="flex-1 rounded border border-blue-500 px-4 py-2 font-medium text-blue-500 hover:bg-blue-500 hover:text-white">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            <section class="lg:col-span-4 flex flex-col gap-8">
                @forelse ($venues as $venue)
                <span onclick="window.location='{{ route('venues.detail', $venue->id) }}'">
                    <div class="bg-neutral-800 rounded-xl overflow-hidden shadow-lg flex flex-row items-center p-6 relative">
                        <div class="w-64 h-36 bg-neutral-700 rounded-lg mr-8 flex-shrink-0 flex items-center justify-center">
                            <span class="text-gray-500 text-2xl">Image</span>
                        </div>
                        <div class="flex-1 flex flex-col justify-between h-full">
                            <div>
                                <h3 class="text-2xl font-bold mb-1">{{ $venue->name }}</h3>
                                <div class="text-gray-400 text-sm mb-2">{{ $venue->address ?? 'Jakarta' }}</div>
                            </div>
                            <div class="mt-4">
                                <span class="text-gray-400 text-sm">start from</span>
                                <span class="text-xl font-bold text-white ml-2">Rp.
                                    {{ number_format($venue->price ?? 50000, 0, ',', '.') }}</span>
                                <span class="text-gray-400 text-sm">/ session</span>
                            </div>
                        </div>
                        <div class="absolute top-6 right-6">
                            <i id="bookmarkIcon" 
                               class="fa-regular fa-bookmark text-gray-400 text-2xl cursor-pointer hover:text-blue-500">
                            </i>
                        </div>
                    </div>
                </span>
                @empty
                    <div class="text-center py-12">
                        <p class="text-gray-400">No venues found.</p>
                    </div>
                @endforelse

                <div class="mt-6 flex justify-center">
                    {{ $venues->links() }}
                </div>
            </section>
        </div>

    </div>
    <script>
        const bookmark = document.getElementById('bookmarkIcon');

            bookmark.addEventListener('click', function () {
            this.classList.toggle('fa-regular'); 
            this.classList.toggle('fa-solid'); 
            this.classList.toggle('text-white'); 
            this.classList.toggle('text-gray-400'); 
        });
    </script>
@endsection

@push('scripts')
    <script>
        function calendar() {
            return {
                toggle: true,
                month: new Date().getMonth(),
                year: new Date().getFullYear(),
                selectedDate: null,
                monthNames: [
                    "January", "February", "March", "April", "May", "June",
                    "July", "August", "September", "October", "November", "December"
                ],
                daysInMonth() {
                    return Array.from(
                        { length: new Date(this.year, this.month + 1, 0).getDate() },
                        (_, i) => i + 1
                    );
                },
                prevMonth() {
                    if (this.month === 0) {
                        this.month = 11;
                        this.year--;
                    } else this.month--;
                },
                nextMonth() {
                    if (this.month === 11) {
                        this.month = 0;
                        this.year++;
                    } else this.month++;
                },
                selectDate(day) {
                    this.selectedDate = new Date(this.year, this.month, day);
                    console.log(this.selectedDate.toISOString().slice(0, 10));
                }
            }
        }
    </script>
@endpush
