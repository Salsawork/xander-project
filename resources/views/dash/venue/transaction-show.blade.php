@extends('app')
@section('title', 'Transaction Detail')

@push('styles')
<style>
  :root { color-scheme: dark; }
  html, body{ height:100%; background:#0a0a0a; -webkit-text-size-adjust:100%; }
  #antiBounceBg{ position:fixed; inset:-20svh 0 -20svh 0; background:#0a0a0a; pointer-events:none; z-index:-1; }
  main{ overscroll-behavior-y:contain; background:#0a0a0a; }
  .panel-dark{ background:#292929; border-radius:.75rem; box-shadow:0 10px 30px rgba(0,0,0,.35); }
  .muted{ color:#a3a3a3; }
  .row{ display:grid; grid-template-columns: 1fr; gap:.75rem; }
  @media(min-width:640px){ .row{ grid-template-columns: 1fr 1fr; } }
  .badge{ padding:.25rem .6rem; border-radius:9999px; font-size:.8rem; font-weight:600; }
</style>
@endpush

@section('content')
<div id="antiBounceBg" aria-hidden="true"></div>

<div class="text-white">
  <div class="flex flex-col min-h-[100dvh] bg-neutral-900 font-sans">
    <div class="flex flex-1 min-h-0">
      @include('partials.sidebar')

      <main class="flex-1 overflow-y-auto min-w-0 my-4 sm:my-6 md:my-8">
        @include('partials.topbar')

        @php
          $fmtMoney = fn($n) => 'Rp. ' . number_format((int)$n, 0, ',', '.');

          $bookingDate = $transaction->booking_date instanceof \Carbon\Carbon
              ? $transaction->booking_date
              : \Carbon\Carbon::parse($transaction->booking_date);

          $startTime = $transaction->start_time instanceof \Carbon\Carbon
              ? $transaction->start_time
              : \Carbon\Carbon::parse($transaction->start_time);

          $endTime = $transaction->end_time instanceof \Carbon\Carbon
              ? $transaction->end_time
              : \Carbon\Carbon::parse($transaction->end_time);

          $statusClass = [
            'pending'   => 'bg-yellow-500 text-yellow-900',
            'confirmed' => 'bg-blue-500 text-white',
            'booked'    => 'bg-blue-600 text-white',
            'completed' => 'bg-green-500 text-white',
            'cancelled' => 'bg-red-500 text-white',
          ][$transaction->status] ?? 'bg-gray-500 text-white';

          $total = (int)$transaction->price - (int)$transaction->discount;
        @endphp

        <div class="mx-4 sm:mx-8 md:mx-16 mt-20 sm:mt-12 md:mt-16">
          <div class="flex items-center justify-between flex-wrap gap-3 mb-5">
            <h1 class="text-2xl md:text-3xl font-extrabold">Transaction Detail</h1>
            <div class="flex items-center gap-3">
              <a href="{{ route('venue.transaction') }}" class="px-4 py-2 rounded-lg bg-[#1e1e1e] border border-gray-700 hover:bg-[#242424]">
                ← Back to list
              </a>
            </div>
          </div>

          <div class="panel-dark p-5 sm:p-7 space-y-6">
            <div class="flex items-center justify-between gap-4 flex-wrap">
              <div>
                <div class="muted text-sm">Booking ID</div>
                <div class="text-xl font-bold">#{{ $transaction->id }}</div>
              </div>
              <span class="badge {{ $statusClass }}">{{ ucfirst($transaction->status) }}</span>
            </div>

            <hr class="border-gray-700/60">

            <div class="row">
              <div>
                <div class="muted text-sm">Booking Date</div>
                <div class="text-base">{{ $bookingDate->format('d M Y') }}</div>
              </div>
              <div>
                <div class="muted text-sm">Time</div>
                <div class="text-base">{{ $startTime->format('H:i') }} - {{ $endTime->format('H:i') }}</div>
              </div>
            </div>

            <div class="row">
              <div>
                <div class="muted text-sm">Table</div>
                <div class="text-base">Table #{{ $transaction->table->table_number ?? 'N/A' }}</div>
              </div>
              <div>
                <div class="muted text-sm">Payment Method</div>
                <div class="text-base">{{ $transaction->payment_method ?? '-' }}</div>
              </div>
            </div>

            <div class="row">
              <div>
                <div class="muted text-sm">Customer</div>
                <div class="text-base">{{ $transaction->user->name ?? '-' }}</div>
                <div class="muted text-sm">{{ $transaction->user->email ?? '' }}</div>
              </div>
              <div>
                <div class="muted text-sm">Venue</div>
                <div class="text-base">{{ $transaction->venue->name ?? '-' }}</div>
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
              <div class="bg-[#1f1f1f] border border-gray-700 rounded-lg p-4">
                <div class="muted text-sm">Price</div>
                <div class="text-lg font-semibold">{{ $fmtMoney($transaction->price) }}</div>
              </div>
              <div class="bg-[#1f1f1f] border border-gray-700 rounded-lg p-4">
                <div class="muted text-sm">Discount</div>
                <div class="text-lg font-semibold">{{ $fmtMoney($transaction->discount) }}</div>
              </div>
              <div class="bg-[#1f1f1f] border border-gray-700 rounded-lg p-4">
                <div class="muted text-sm">Total</div>
                <div class="text-lg font-extrabold">{{ $fmtMoney($total) }}</div>
              </div>
            </div>

            @if(!empty($transaction->notes ?? null))
              <div>
                <div class="muted text-sm mb-1">Notes</div>
                <div class="bg-[#1f1f1f] border border-gray-700 rounded-lg p-4">{{ $transaction->notes }}</div>
              </div>
            @endif

            <div class="pt-2">
              <a href="{{ route('venue.transaction') }}" class="inline-block px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700">
                Back
              </a>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
</div>
@endsection
