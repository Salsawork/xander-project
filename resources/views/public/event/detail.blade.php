@extends('app')
@section('title', $event->name . ' - Xander Billiard')

@section('content')
    <style>
        :root { color-scheme: dark; }
        html, body {
            height: 100%;
            min-height: 100%;
            background: #0a0a0a;
            overscroll-behavior-y: none;
            overscroll-behavior-x: none;
        }
        #app, main { background: #0a0a0a; }
        body::before{
            content: "";
            position: fixed;
            inset: -40vh -40vw;
            background: #0a0a0a;
            z-index: -1;
            pointer-events: none;
        }
        body { -webkit-overflow-scrolling: touch; touch-action: pan-y; }
        img { display: block; background: transparent; }

        /* Modal Styles (Register & Buy Ticket) */
        .modal { display:none; position:fixed; z-index:9999; inset:0; width:100%; height:100%; overflow:auto; background:rgba(0,0,0,.7); backdrop-filter: blur(4px); }
        .modal.active { display:flex; align-items:center; justify-content:center; }
        .modal-content { background:#1f1f1f; margin:auto; padding:0; border-radius:12px; width:90%; max-width:520px; box-shadow:0 4px 6px rgba(0,0,0,.3); animation: slideDown .3s ease-out; }
        @keyframes slideDown { from{opacity:0; transform:translateY(-50px);} to{opacity:1; transform:translateY(0);} }
        .modal-header { padding:20px 24px; border-bottom:1px solid #333; display:flex; justify-content:space-between; align-items:center; }
        .modal-body { padding:24px; }
        .modal-footer { padding:16px 24px; border-top:1px solid #333; display:flex; justify-content:flex-end; gap:12px; }
        .close { color:#aaa; font-size:28px; font-weight:bold; cursor:pointer; transition:color .3s; }
        .close:hover,.close:focus { color:#fff; }
        .form-group { margin-bottom:16px; }
        .form-label { display:block; margin-bottom:8px; font-weight:500; color:#e5e5e5; }
        .form-input, .form-select { width:100%; padding:12px 14px; background:#2a2a2a; border:1px solid #404040; border-radius:8px; color:#fff; font-size:14px; transition:all .3s; }
        .form-input:focus, .form-select:focus { outline:none; border-color:#3b82f6; box-shadow:0 0 0 3px rgba(59,130,246,.1); }
        .form-input::placeholder { color:#666; }
        .btn-primary { background:#3b82f6; color:#fff; padding:10px 24px; border:none; border-radius:8px; font-weight:500; cursor:pointer; transition:background-color .3s; }
        .btn-primary:hover { background:#2563eb; }
        .btn-secondary { background:transparent; color:#9ca3af; padding:10px 24px; border:1px solid #404040; border-radius:8px; font-weight:500; cursor:pointer; transition:all .3s; }
        .btn-secondary:hover { background:#2a2a2a; color:#fff; }
        .hint { font-size:12px; color:#9ca3af; }
    </style>

    @php
        use Illuminate\Support\Str;
        use Carbon\Carbon;

        $pretty   = Str::slug($event->name);
        $now      = Carbon::now();
        $endDate  = $event->end_date instanceof Carbon ? $event->end_date : Carbon::parse($event->end_date);

        // Sumber harga & stok dari ticket default jika dikirim controller
        $ticketPrice  = isset($ticket) ? (float) ($ticket->price ?? 0) : (float) ($event->price_ticket ?? 0);
        $stockLeft    = isset($ticket) ? (int) ($ticket->stock ?? 0) : (int) ($event->stock ?? 0);

        $showRegister = ($event->status === 'Upcoming') && $now->lte($endDate);
    @endphp

    <div class="min-h-screen bg-neutral-900 text-white">
        <!-- HERO / BREADCRUMB -->
        <div class="mb-8 bg-cover bg-center p-24" style="background-image: url('/images/bg/product_breadcrumb.png');">
            <nav class="text-sm text-gray-400 mt-1" aria-label="Breadcrumb">
                <a href="{{ route('index') }}" class="hover:text-white transition">Home</a>
                <span class="mx-1 opacity-60">/</span>
                <a href="{{ route('events.index') }}" class="hover:text-white transition">Event</a>
                <span class="mx-1 opacity-60">/</span>
                <span class="text-gray-200" aria-current="page">{{ $event->name }}</span>
            </nav>
            <h2 class="text-4xl font-bold uppercase text-white">{{ $event->name }}</h2>
        </div>

        <!-- ALERTS -->
        <div class="container mx-auto px-8">
            @if (session('success'))
                <div class="mb-6 rounded-lg bg-green-600/15 border border-green-600/40 text-green-300 px-4 py-3">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 rounded-lg bg-red-600/15 border border-red-600/40 text-red-300 px-4 py-3">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 rounded-lg bg-red-600/15 border border-red-600/40 text-red-300 px-4 py-3">
                    <strong>Terjadi kesalahan:</strong>
                    <ul class="list-disc list-inside text-sm mt-2">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <!-- Layout utama -->
        <div class="container mx-auto px-8 pb-16">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Kiri -->
                <div class="lg:col-span-2">
                    <div class="bg-neutral-800 rounded-xl p-6 h-auto">
                        <div class="mb-6 rounded-lg overflow-hidden">
                            <img
                                src="{{ $event->image_url ? asset($event->image_url) : 'https://via.placeholder.com/1200x600' }}"
                                alt="{{ $event->name }}"
                                class="w-full h-auto object-cover rounded-lg">
                        </div>

                        <div>
                            <div class="flex justify-between items-center mb-6">
                                <h1 class="text-3xl font-bold">{{ $event->name }}</h1>
                                <span class="px-4 py-1 rounded-full text-sm
                                    @if($event->status == 'Upcoming') bg-red-600
                                    @elseif($event->status == 'Ongoing') bg-green-600
                                    @else bg-gray-600 @endif
                                    text-white">
                                    {{ $event->status }}
                                </span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8 bg-neutral-700 p-6 rounded-lg">
                                <div>
                                    <p class="text-gray-400 text-sm mb-1 uppercase">Date:</p>
                                    <p class="font-semibold">
                                        {{ $event->start_date->format('M d') }} - {{ $event->end_date->format('M d, Y') }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-gray-400 text-sm mb-1 uppercase">Location:</p>
                                    <p class="font-semibold">{{ $event->location }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-400 text-sm mb-1 uppercase">Game Types:</p>
                                    <p class="font-semibold">{{ $event->game_types }}</p>
                                </div>
                            </div>

                            <div class="mb-8">
                                <h3 class="text-xl font-bold mb-4">Registration & Tickets</h3>
                                <div class="space-y-4 bg-neutral-700 p-6 rounded-lg">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <p class="font-medium mb-1">Player Registration</p>
                                            <p class="text-gray-300">Until {{ $endDate->format('F d, Y') }}</p>
                                        </div>
                                        <div>
                                            <p class="font-medium mb-1">Ticket Price</p>
                                            <p class="text-gray-300">Rp. {{ number_format($ticketPrice, 0, ',', '.') }}</p>
                                        </div>
                                        <div>
                                            <p class="font-medium mb-1">Stock Left</p>
                                            <p class="text-gray-300">{{ $stockLeft }}</p>
                                        </div>
                                    </div>

                                    <div class="flex flex-wrap gap-4 pt-4">
                                        @if($showRegister)
                                            @guest
                                                <a href="{{ route('login') }}"
                                                   class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                                                    Register Now
                                                </a>
                                            @else
                                                <button type="button" onclick="openModal('#registrationModal')"
                                                   class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                                                    Register Now
                                                </button>
                                            @endguest
                                        @endif

                                        @guest
                                            <a href="{{ route('login') }}"
                                               class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                                                Buy Ticket
                                            </a>
                                        @else
                                            <button type="button"
                                                    onclick="openModal('#buyTicketModal')"
                                                    class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                                                Buy Ticket
                                            </button>
                                        @endguest
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Kanan -->
                <div class="space-y-6">
                    <div class="bg-neutral-800 rounded-xl p-6">
                        <h3 class="text-xl font-bold mb-4">About the Event</h3>
                        <p class="text-gray-300">
                            The {{ $event->name }} is the ultimate battleground for elite billiard players across the country.
                            This annual event brings together top-ranked professionals, rising stars, and passionate cue sports
                            enthusiasts to compete for national glory and a prize pool of over
                            <strong>Rp. {{ number_format($event->total_prize_money, 0, ',', '.') }}</strong>.
                        </p>
                    </div>

                    <div class="bg-neutral-800 rounded-xl p-6">
                        <h3 class="text-xl font-bold mb-4">Prize Pool & Awards</h3>
                        <div class="space-y-2">
                            <div>
                                <p class="font-medium">Total Prize Pool:</p>
                                <p class="text-gray-300">Rp. {{ number_format($event->total_prize_money, 0, ',', '.') }}+</p>
                            </div>
                            <div>
                                <p class="font-medium">Champion:</p>
                                <p class="text-gray-300">Rp. {{ number_format($event->champion_prize, 0, ',', '.') }} + National Champion Trophy</p>
                            </div>
                            <div>
                                <p class="font-medium">Runner-up:</p>
                                <p class="text-gray-300">Rp. {{ number_format($event->runner_up_prize, 0, ',', '.') }}</p>
                            </div>
                            <div>
                                <p class="font-medium">Third Place:</p>
                                <p class="text-gray-300">Rp. {{ number_format($event->third_place_prize, 0, ',', '.') }}</p>
                            </div>
                            <div>
                                <p class="font-medium">Top 8 Finalists:</p>
                                <p class="text-gray-300">Cash prizes & special recognition</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-neutral-800 rounded-xl p-6">
                        <h3 class="text-xl font-bold mb-4">Tournament Format</h3>
                        <div class="space-y-2">
                            <div>
                                <p class="font-medium">Divisions:</p>
                                <p class="text-gray-300">{{ $event->divisions }}</p>
                            </div>
                            <div>
                                <p class="font-medium">Match Style:</p>
                                <p class="text-gray-300">{{ $event->match_style }}</p>
                            </div>
                            <div>
                                <p class="font-medium">Finals:</p>
                                <p class="text-gray-300">{{ $event->finals_format }}</p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('events.bracket', ['event'=>$event->id, 'name'=>$pretty]) }}"
                               class="block text-center bg-transparent border border-blue-500 text-blue-500 hover:bg-blue-500 hover:text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                View Tournament Bracket
                            </a>
                        </div>
                    </div>

                    <div class="bg-neutral-800 rounded-xl p-6">
                        <h3 class="text-xl font-bold mb-4">Broadcast & Live Streaming</h3>
                        <p class="text-gray-300 mb-4">
                            Can't make it in person? Catch all the action live on major sports
                            networks and online streaming platforms.
                        </p>
                        <p class="text-gray-300 mb-4">
                            Don't miss the chance to witness history in the making! Whether you're
                            here to compete, watch, or learn, {{ $event->name }} promises
                            an unforgettable experience for every billiards fan.
                        </p>
                        <p class="text-gray-300">
                            Follow us for updates:
                            <a href="https://twitter.com/{{ $event->social_media_handle }}" class="text-blue-400 hover:underline">
                                {{ $event->social_media_handle }}
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ========== MODAL: Registration (role player) ========== --}}
    @auth
    @php $showRegisterModal = $showRegister; @endphp
    @if($showRegisterModal)
    <div id="registrationModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="text-2xl font-bold text-white">Event Registration</h2>
                <span class="close" onclick="closeModal('#registrationModal')">&times;</span>
            </div>
            <form id="registrationForm" action="{{ route('events.register', $event->id) }}" method="POST">
                @csrf
                <input type="hidden" name="_from" value="event-registration">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label" for="username">Username <span class="text-red-500">*</span></label>
                        <input
                            type="text"
                            id="username"
                            name="username"
                            class="form-input"
                            placeholder="Enter your username"
                            value="{{ old('username', auth()->user()->name ?? '') }}"
                            required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="email">Email Address <span class="text-red-500">*</span></label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-input"
                            placeholder="your.email@example.com"
                            value="{{ old('email', auth()->user()->email ?? '') }}"
                            required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="phone">Phone Number <span class="text-red-500">*</span></label>
                        <input
                            type="tel"
                            id="phone"
                            name="phone"
                            class="form-input"
                            placeholder="+62 812 3456 7890"
                            value="{{ old('phone', auth()->user()->phone ?? '') }}"
                            required>
                        <p class="hint mt-2">Maksimal 15 karakter sesuai format data.</p>
                    </div>

                    <p class="text-sm text-gray-400 mt-4">
                        <span class="text-red-500">*</span> Required fields
                    </p>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeModal('#registrationModal')">Cancel</button>
                    <button type="submit" class="btn-primary">Submit Registration</button>
                </div>
            </form>
        </div>
    </div>
    @endif
    @endauth

    {{-- ========== MODAL: Buy Ticket ========== --}}
    @auth
    <div id="buyTicketModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="text-2xl font-bold text-white">Buy Ticket</h2>
                <span class="close" onclick="closeModal('#buyTicketModal')">&times;</span>
            </div>
            <form id="buyTicketForm" action="{{ route('events.buy', $event->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="_from" value="event-buy">

                {{-- Penting: kirim ticket_id default agar order_events.ticket_id terisi --}}
                <input type="hidden" name="ticket_id" value="{{ $ticket->id ?? '' }}"/>

                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Ticket Price</label>
                        <input type="text" class="form-input" value="Rp. {{ number_format($ticketPrice, 0, ',', '.') }}" readonly>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="qty">Quantity <span class="text-red-500">*</span></label>
                        <input type="number" min="1" max="{{ $stockLeft }}" value="1" id="qty" name="qty" class="form-input" required>
                        <p class="hint mt-2">Sisa stok: {{ $stockLeft }}</p>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="bank_id">Bank <span class="text-red-500">*</span></label>
                        <select id="bank_id" name="bank_id" class="form-select" required>
                            <option value="">Pilih bank</option>
                            @foreach($banks as $b)
                                <option value="{{ $b->id_bank }}">{{ $b->nama_bank }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="bukti_payment">Bukti Pembayaran <span class="text-red-500">*</span></label>
                        <input type="file" id="bukti_payment" name="bukti_payment" accept="image/*" class="form-input" required>
                        <p class="hint mt-2">Format: JPG/PNG/WEBP, maks 2MB.</p>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Total Payment</label>
                        <input type="text" id="totalPaymentDisplay" class="form-input" value="Rp. {{ number_format($ticketPrice, 0, ',', '.') }}" readonly>
                        <input type="hidden" id="unitPrice" value="{{ $ticketPrice }}">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeModal('#buyTicketModal')">Cancel</button>
                    <button type="submit" class="btn-primary">Submit Purchase</button>
                </div>
            </form>
        </div>
    </div>
    @endauth

    <script>
        function openModal(sel){ const m = document.querySelector(sel); if(!m) return; m.classList.add('active'); document.body.style.overflow='hidden'; }
        function closeModal(sel){ const m = document.querySelector(sel); if(!m) return; m.classList.remove('active'); document.body.style.overflow='auto'; }

        // Auto-calc total payment pada Buy Ticket
        (function(){
            const qtyEl   = document.getElementById('qty');
            const priceEl = document.getElementById('unitPrice');
            const outEl   = document.getElementById('totalPaymentDisplay');

            function formatRupiah(n){
                n = Math.floor(n);
                return 'Rp. ' + n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }

            function recalc(){
                if(!qtyEl || !priceEl || !outEl) return;
                const qty   = Math.max(1, parseInt(qtyEl.value || '1', 10));
                const price = parseFloat(priceEl.value || '0');
                const total = qty * price;
                outEl.value = formatRupiah(total);
            }

            qtyEl && qtyEl.addEventListener('input', recalc);
            recalc();
        })();

        // Buka ulang modal bila ada error validasi
        @if ($errors->any())
            @if (old('_from') === 'event-registration')
                window.addEventListener('load', () => openModal('#registrationModal'));
            @endif
            @if (old('_from') === 'event-buy')
                window.addEventListener('load', () => openModal('#buyTicketModal'));
            @endif
        @endif
    </script>
@endsection
