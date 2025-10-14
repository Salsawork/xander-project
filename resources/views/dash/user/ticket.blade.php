@extends('app')
@section('title', 'My Tickets')

@push('styles')
<style>
  :root{ color-scheme: dark; --page-bg:#0a0a0a; }
  html, body{ background:var(--page-bg); }
  .card{
    background:linear-gradient(180deg, rgba(255,255,255,.04), rgba(255,255,255,.02)), #232323;
    border:1px solid rgba(255,255,255,.08);
    border-radius:1rem;
    box-shadow:0 10px 30px rgba(0,0,0,.35);
  }
  .badge{ display:inline-block; padding:.25rem .5rem; border-radius:.5rem; font-size:.75rem; font-weight:600; }
  .badge-pending{ background:#fde68a22; color:#fcd34d; border:1px solid #fcd34d33; }
  .badge-paid{ background:#86efac22; color:#86efac; border:1px solid #86efac33; }
  .badge-verified{ background:#60a5fa22; color:#93c5fd; border:1px solid #93c5fd33; }
  .badge-rejected{ background:#fca5a522; color:#fca5a5; border:1px solid #fca5a533; }
</style>
@endpush

@section('content')
<div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
  <div class="flex flex-1 min-h-0">
    @include('partials.sidebar')

    <main class="flex-1 overflow-y-auto min-w-0 mb-8">
      @include('partials.topbar')

      <div class="max-w-6xl mx-auto px-5 sm:px-8 md:px-12 mt-20">
        <div class="mb-8 md:mb-10 flex items-center justify-between gap-4">
          <div>
            <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight">My Tickets</h1>
            <p class="text-sm text-gray-400 mt-1">Riwayat pembelian tiket event kamu.</p>
          </div>
        </div>

        {{-- Desktop Table --}}
        <div class="hidden md:block overflow-x-auto">
          <table class="w-full text-left text-sm border border-gray-700 rounded-md overflow-hidden">
            <thead class="bg-[#2c2c2c] text-gray-300">
              <tr>
                <th class="px-4 py-3">Event</th>
                <th class="px-4 py-3">Qty</th>
                <th class="px-4 py-3">Harga (Rp)</th>
                <th class="px-4 py-3">Total (Rp)</th>
                <th class="px-4 py-3">Tanggal Beli</th>
                <th class="px-4 py-3">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
              @forelse($tickets as $t)
                <tr>
                  <td class="px-4 py-3 font-semibold">{{ $t->event_name }}</td>
                  <td class="px-4 py-3">{{ (int) $t->qty }}</td>
                  <td class="px-4 py-3">
                    {{ $t->price !== null ? number_format($t->price, 0, ',', '.') : '-' }}
                  </td>
                  <td class="px-4 py-3">{{ number_format($t->total, 0, ',', '.') }}</td>
                  <td class="px-4 py-3 text-gray-400">{{ \Carbon\Carbon::parse($t->purchased_at)->format('d M Y H:i') }}</td>
                  <td class="px-4 py-3">
                    @php
                      $status = strtolower($t->payment_status ?? 'pending');
                      $map = [
                        'pending'  => 'badge badge-pending',
                        'paid'     => 'badge badge-paid',
                        'verified' => 'badge badge-verified',
                        'rejected' => 'badge badge-rejected',
                      ];
                    @endphp
                    <span class="{{ $map[$status] ?? 'badge badge-pending' }}">{{ ucfirst($status) }}</span>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="6" class="px-4 py-6 text-center text-gray-400">Belum ada pembelian tiket.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        {{-- Mobile Cards --}}
        <div class="md:hidden space-y-4">
          @forelse($tickets as $t)
            <div class="card p-4">
              <div class="flex items-start justify-between">
                <div>
                  <h3 class="font-semibold text-base">{{ $t->event_name }}</h3>
                  <p class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($t->purchased_at)->format('d M Y H:i') }}</p>
                </div>
                @php
                  $status = strtolower($t->payment_status ?? 'pending');
                  $map = [
                    'pending'  => 'badge badge-pending',
                    'paid'     => 'badge badge-paid',
                    'verified' => 'badge badge-verified',
                    'rejected' => 'badge badge-rejected',
                  ];
                @endphp
                <span class="{{ $map[$status] ?? 'badge badge-pending' }}">{{ ucfirst($status) }}</span>
              </div>

              <div class="mt-4 text-sm grid grid-cols-2 gap-3">
                <div class="text-gray-400">Qty</div>
                <div class="text-white text-right">{{ (int) $t->qty }}</div>

                <div class="text-gray-400">Harga</div>
                <div class="text-white text-right">
                  {{ $t->price !== null ? 'Rp ' . number_format($t->price, 0, ',', '.') : '-' }}
                </div>

                <div class="text-gray-400">Total</div>
                <div class="text-white text-right">Rp {{ number_format($t->total, 0, ',', '.') }}</div>
              </div>
            </div>
          @empty
            <div class="card p-6 text-center text-gray-400">Belum ada pembelian tiket.</div>
          @endforelse
        </div>
      </div>
    </main>
  </div>
</div>
@endsection
