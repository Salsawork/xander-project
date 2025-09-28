@extends('app')

@section('title', 'Sparring')

@section('content')
    <div class="bg-gray-950 text-white min-h-screen">
        <!-- Header Section with Background Image -->
        <div class="mb-16 bg-cover bg-center p-24" style="background-image: url('/images/bg/product_breadcrumb.png');">
            <p class="text-sm text-gray-400 mt-1">
                <a href="{{ route('index') }}" class="text-gray-400 hover:underline">Home</a> / Sparring
            </p>
            <h2 class="text-4xl font-bold uppercase text-white">POWER. PRECISION. PLAY.</h2>
        </div>

        {{-- <div class="max-w-7xl mx-auto px-4 py-8 flex flex-col md:flex-row gap-8"> --}}
        <div class="flex gap-6 px-24 py-18">
            <!-- Sidebar Filter -->
            <div class="hidden lg:block w-64 space-y-6 rounded-xl bg-neutral-900 p-4 text-white text-sm">    
                <form method="GET" action="{{ route('sparring.index') }}" class="space-y-4">
            
                    <!-- Search -->
                    <div>
                        <input type="text" name="search" placeholder="Search"
                            value="{{ request('search') }}"
                            class="w-full rounded border border-gray-400 bg-transparent px-3 py-1.5 text-sm placeholder-gray-400 
                                   focus:outline-none focus:ring-1 focus:ring-blue-500" />
                    </div>
            
                    <!-- Date -->
                    <div x-data="calendar('{{ request('date') }}')" class="bg-neutral-900 pt-4 text-white rounded-xl text-sm">
                        <input type="hidden" name="date" x-model="selectedDate">
            
                        <div class="flex items-center justify-between mb-2 font-semibold">
                            <span>Date</span>
                            <span @click="toggle = !toggle" class="cursor-pointer text-xl">–</span>
                        </div>
                        <div x-show="toggle" x-transition>
                            <div class="flex items-center justify-center gap-2 mb-2">
                                <button type="button" @click="prevMonth()" class="text-gray-400 hover:text-white">&lt;</button>
                                <span x-text="monthNames[month] + ' ' + year"></span>
                                <button type="button" @click="nextMonth()" class="text-gray-400 hover:text-white">&gt;</button>
                            </div>
                            <div class="grid grid-cols-7 gap-1 text-center text-gray-400 text-xs">
                                <template x-for="d in daysInMonth()" :key="d">
                                    <span 
                                        class="py-1 cursor-pointer rounded transition-colors"
                                        :class="selectedDate === formatDate(year, month, d) 
                                                ? 'bg-blue-500 text-white' 
                                                : 'hover:bg-gray-600'"
                                        @click="selectDate(d)"
                                        x-text="d">
                                    </span>
                                </template>
                            </div>
                        </div>
                    </div>
            
                    <!-- Location -->
                    <div class="border-t border-gray-500 pt-4">
                        <div class="flex items-center justify-between mb-2 font-semibold">
                            <span>Location</span>
                            <span class="toggleBtn text-xl leading-none text-gray-300 cursor-pointer">–</span>
                        </div>
                        <div class="toggleContent flex flex-wrap gap-2">
                            @foreach ($locations as $loc)
                                <label class="px-3 py-1 rounded-full border cursor-pointer
                                              {{ request('address') == $loc ? 'border-blue-500 text-blue-400' : 'border-gray-500 text-gray-400' }}">
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
                            <span class="toggleBtn text-xl leading-none text-gray-300 cursor-pointer">–</span>
                        </div>
                        <div class="toggleContent w-full flex items-center gap-2">
                            <input type="number" name="price_min" placeholder="{{ $minPrice }}"
                                value="{{ request('price_min') }}"
                                class="w-1/2 rounded border border-gray-400 px-2 py-1 
                                       focus:outline-none focus:ring focus:ring-blue-500" />
                            <input type="number" name="price_max" placeholder="{{ $maxPrice }}"
                                value="{{ request('price_max') }}"
                                class="w-1/2 rounded border border-gray-400 px-2 py-1 
                                       focus:outline-none focus:ring focus:ring-blue-500" />
                        </div>
                    </div>
            
                    <!-- Buttons -->
                    <div class="flex gap-2 pt-2">
                        <button type="submit"
                            class="flex-1 rounded bg-blue-500 px-4 py-2 font-medium text-white hover:bg-blue-600">
                            Filter
                        </button>
                        <a href="{{ route('sparring.index') }}"
                            class="flex-1 rounded border border-blue-500 px-4 py-2 font-medium text-blue-500 hover:bg-blue-500 hover:text-white">
                            Reset
                        </a>
                    </div>
                </form>
            </div>
            

            <!-- Athletes List -->
            <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @forelse ($athletes as $athlete)
                    <a href="{{ route('sparring.detail', $athlete->id) }}" class="block">
                        <div class="bg-gray-800 rounded-lg overflow-hidden shadow-md transition-transform hover:scale-105">
                            <div class="relative h-64 overflow-hidden">
                                @if ($athlete->athleteDetail && $athlete->athleteDetail->image)
                                    <img src="{{ asset('images/athlete/' . $athlete->athleteDetail->image) }}" 
                                        class="w-full h-full object-cover" 
                                        alt="{{ $athlete->name }}"
                                        onerror="this.src='{{ asset('images/athlete/athlete-1.png') }}'">
                                @else
                                    <img src="{{ asset('images/athlete/athlete-1.png') }}" 
                                        class="w-full h-full object-cover" 
                                        alt="{{ $athlete->name }}">
                                @endif
                            </div>
                            <div class="p-4">
                                <h3 class="font-semibold text-lg">{{ $athlete->name }}</h3>
                                <p class="text-sm text-gray-300">
                                    Rp. {{ number_format($athlete->athleteDetail->price_per_session ?? 0, 0, ',', '.') }} / session
                                </p>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="col-span-full p-8 text-center">
                        <p class="text-gray-400">No athletes available at the moment.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Floating Shopping Cart Button -->
        <button aria-label="Shopping cart with {{ count($carts ?? []) + count($sparrings ?? []) }} items" onclick="showCart()"
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

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const toggles = document.querySelectorAll(".toggleBtn");
        
            toggles.forEach((btn) => {
                const content = btn.parentElement.nextElementSibling;
        
                btn.addEventListener("click", () => {
                    if (content.classList.contains("max-h-0")) {
                        content.classList.remove("max-h-0");
                        btn.textContent = "–";
                    } else {
                        content.classList.add("max-h-0");
                        btn.textContent = "+";
                    }
                });
            });
        });
        </script>
        
        <style>
        .toggleContent {
            overflow: hidden;
            transition: max-height 0.3s ease;
            max-height: 1000px; /* default terbuka */
        }
        .toggleContent.max-h-0 {
            max-height: 0;
        }
        </style>
        
@endsection

@push('scripts')
<script>
    function calendar(defaultDate = '') {
        const today = new Date();
        let initialDate = defaultDate ? new Date(defaultDate) : today;
    
        return {
            month: initialDate.getMonth(),
            year: initialDate.getFullYear(),
            selectedDate: defaultDate || '',
            toggle: true,
            monthNames: [
                "January", "February", "March", "April", "May", "June",
                "July", "August", "September", "October", "November", "December"
            ],
            daysInMonth() {
                return new Date(this.year, this.month + 1, 0).getDate();
            },
            prevMonth() {
                if (this.month === 0) {
                    this.month = 11;
                    this.year--;
                } else {
                    this.month--;
                }
            },
            nextMonth() {
                if (this.month === 11) {
                    this.month = 0;
                    this.year++;
                } else {
                    this.month++;
                }
            },
            formatDate(year, month, day) {
                return `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            },
            selectDate(day) {
                this.selectedDate = this.formatDate(this.year, this.month, day);
            }
        }
    }
    </script>
    
@endpush
