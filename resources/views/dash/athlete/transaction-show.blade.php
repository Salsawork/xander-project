@extends('app')
@section('title', 'Transaction Detail')

@push('styles')
<style>
  :root {
    color-scheme: dark;
  }

  html,
  body {
    height: 100%;
    background: #0a0a0a;
    -webkit-text-size-adjust: 100%;
  }

  #antiBounceBg {
    position: fixed;
    inset: -20svh 0 -20svh 0;
    background: #0a0a0a;
    pointer-events: none;
    z-index: -1;
  }

  main {
    overscroll-behavior-y: contain;
    background: #0a0a0a;
  }

  .panel-dark {
    background: #1e1e1e;
    border-radius: 1rem;
    box-shadow: 0 10px 30px rgba(0, 0, 0, .35);
    border: 1px solid #2a2a2a;
  }

  .muted {
    color: #9ca3af;
  }

  .row {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1rem;
  }

  @media(min-width:640px) {
    .row {
      grid-template-columns: 1fr 1fr;
    }
  }

  .badge {
    padding: .35rem .75rem;
    border-radius: 9999px;
    font-size: .8rem;
    font-weight: 600;
  }

  .info-card {
    background: #111;
    border: 1px solid #2a2a2a;
    border-radius: .75rem;
    padding: 1rem;
  }
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
        $fmtMoney = fn($n) => 'Rp ' . number_format((int)$n, 0, ',', '.');

        $sparringDate = $transaction->schedule->date instanceof \Carbon\Carbon
        ? $transaction->schedule->date
        : \Carbon\Carbon::parse($transaction->schedule->date);

        $startTime = $transaction->schedule->start_time instanceof \Carbon\Carbon
        ? $transaction->schedule->start_time
        : \Carbon\Carbon::parse($transaction->schedule->start_time);

        $endTime = $transaction->schedule->end_time instanceof \Carbon\Carbon
        ? $transaction->schedule->end_time
        : \Carbon\Carbon::parse($transaction->schedule->end_time);

        $statusClass = [
        '0' => 'bg-blue-600 text-white',
        '1' => 'bg-green-500 text-white',
        ][$transaction->schedule->is_booked] ?? 'bg-gray-500 text-white';

        $total = (int)$transaction->price;
        @endphp

        <div class="mx-4 sm:mx-8 md:mx-16 mt-20 sm:mt-12 md:mt-16">
          <div class="flex items-center justify-between flex-wrap gap-3 mb-6">
            <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight">Transaction Detail</h1>
            <a href="{{ route('athlete.transaction') }}" class="px-4 py-2 rounded-lg bg-[#1e1e1e] border border-gray-700 hover:bg-[#242424] transition">
              ← Back to list
            </a>
          </div>

          <div class="panel-dark p-6 sm:p-8 space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between gap-4 flex-wrap">
              <div>
                <div class="muted text-xs uppercase tracking-wide">Sparring ID</div>
                <div class="text-2xl font-bold">#{{ $transaction->id }}</div>
              </div>
              <span class="badge {{ $statusClass }}">
                {{ $transaction->schedule->is_booked ? 'Booked' : 'Pending' }}
              </span>
            </div>

            <hr class="border-gray-800">

            <!-- Booking Info -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
              <div class="info-card">
                <div class="muted text-xs uppercase tracking-wide mb-1">Booking Date</div>
                <div class="text-base font-medium">{{ $sparringDate->format('d M Y') }}</div>
              </div>
              <div class="info-card">
                <div class="muted text-xs uppercase tracking-wide mb-1">Time</div>
                <div class="text-base font-medium">{{ $startTime->format('H:i') }} – {{ $endTime->format('H:i') }}</div>
              </div>
              <div class="info-card">
                <div class="muted text-xs uppercase tracking-wide mb-1">Payment Method</div>
                <div class="text-base font-medium">{{ ucfirst(str_replace('_', ' ', $transaction->order->payment_method ?? '-')) }}</div>
              </div>
            </div>

            <!-- People -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div class="info-card">
                <div class="muted text-xs uppercase tracking-wide mb-1">Customer</div>
                <div class="text-base font-medium">{{ $transaction->order->user->name ?? '-' }}</div>
                <div class="muted text-sm">{{ $transaction->user->email ?? '' }}</div>
              </div>
              <div class="info-card">
                <div class="muted text-xs uppercase tracking-wide mb-1">Athlete</div>
                <div class="text-base font-medium">{{ $transaction->athlete->name ?? '-' }}</div>
              </div>
            </div>

            <!-- Price -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div class="info-card">
                <div class="muted text-xs uppercase tracking-wide mb-1">Price</div>
                <div class="text-xl font-semibold">{{ $fmtMoney($transaction->price) }}</div>
              </div>
              <div class="info-card">
                <div class="muted text-xs uppercase tracking-wide mb-1">Total</div>
                <div class="text-xl font-extrabold">{{ $fmtMoney($total) }}</div>
              </div>
            </div>

            <!-- Notes -->
            @if(!empty($transaction->notes))
            <div class="info-card">
              <div class="muted text-xs uppercase tracking-wide mb-2">Notes</div>
              <div class="text-sm leading-relaxed">{{ $transaction->notes }}</div>
            </div>
            @endif

            <!-- Actions -->
            <div class="pt-2 flex items-center gap-3">
              <a href="{{ route('athlete.transaction') }}" class="px-4 py-2 rounded-lg bg-gray-800 hover:bg-gray-700 transition">
                Back
              </a>

              @if($transaction->schedule->is_booked == 0 && $transaction->order && $transaction->order->payment_status === 'paid')
              <form action="{{ route('athlete.transaction.verify', $transaction->id) }}" method="POST" onsubmit="return confirm('Yakin ingin verifikasi booking ini?')">
                @csrf
                @method('PUT')
                <button type="submit" class="px-4 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white transition">
                  Verify Booking
                </button>
              </form>
              @endif
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
</div>
@endsection