@extends('app')
@section('title', 'User Dashboard - Profile')

@section('content')
<div class="min-h-screen bg-neutral-900 text-white">
  <div class="flex min-h-[100dvh]">
    @include('partials.sidebar')
    <main class="flex-1 overflow-y-auto min-w-0 my-8">
      @include('partials.topbar')
      <div class="max-w-6xl mt-16 mx-16">
        {{-- ===== PROFILE CARD ===== --}}
        <section class="mt-20 rounded-2xl bg-[#2a2a2a] shadow-xl ring-1 ring-white/10 p-6 md:p-8">
          {{-- Header: avatar + identity --}}
          <div class="flex flex-col md:flex-row md:items-center md:gap-6">
            <img
              src="{{ auth()->user()->avatar_url ?? 'https://i.pravatar.cc/120?img=15' }}"
              alt="Avatar"
              class="h-20 w-20 rounded-full object-cover ring-2 ring-white/10 mb-4 md:mb-0">

            <div class="flex-1">
              <h2 class="text-xl font-extrabold">{{ auth()->user()->name ?? 'Alex Murphy' }}</h2>
              <p class="text-gray-300 text-sm leading-6">
                {{ auth()->user()->email ?? 'alex@example.com' }}<br>
                {{ auth()->user()->phone ?? 'no phone' }}
              </p>
            </div>
          </div>

          {{-- Form dua kolom + divider --}}
          <form method="POST" action="{{ route('profile.update') }}"
            class="mt-8 grid grid-cols-1 md:grid-cols-[1fr_auto_1fr] gap-6">
            @csrf

            {{-- KIRI --}}
            <div class="space-y-4 md:col-span-1">
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs text-gray-400 mb-1">First Name</label>
                  <input type="text" name="name"
                    value="{{ old('name', auth()->user()->name ?? 'Alex') }}"
                    class="w-full rounded-md bg-[#1f1f1f] border border-white/15 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
                </div>
                <div>
                  <label class="block text-xs text-gray-400 mb-1">Last Name</label>
                  <input type="text" disabled
                    value="{{ old('last_name','Murphy') }}"
                    class="w-full rounded-md bg-[#1f1f1f] border border-white/15 px-3 py-2 text-sm text-white/70">
                </div>
              </div>

              <div>
                <span class="block text-xs text-gray-400 mb-1">Gender</span>
                <div class="flex items-center gap-6 text-sm">
                  <label class="inline-flex items-center gap-2">
                    <input type="radio" name="gender" value="male" class="accent-blue-600" checked>
                    <span>Male</span>
                  </label>
                  <label class="inline-flex items-center gap-2">
                    <input type="radio" name="gender" value="female" class="accent-blue-600">
                    <span>Female</span>
                  </label>
                </div>
              </div>

              <div>
                <label class="block text-xs text-gray-400 mb-1">Date of Birth</label>
                <input type="text" placeholder="20 March 2003"
                  class="w-full rounded-md bg-[#1f1f1f] border border-white/15 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
              </div>
            </div>

            {{-- DIVIDER --}}
            <div class="hidden md:block border-l border-white/10"></div>

            {{-- KANAN --}}
            <div class="space-y-4 md:col-span-1">
              <div>
                <label class="block text-xs text-gray-400 mb-1">Address</label>
                <input type="text" placeholder="789 Greenway Street, Apt 4B"
                  class="w-full rounded-md bg-[#1f1f1f] border border-white/15 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-2">
                  <label class="block text-xs text-gray-400 mb-1">Town/City</label>
                  <input type="text" placeholder="Los Angeles"
                    class="w-full rounded-md bg-[#1f1f1f] border border-white/15 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
                </div>
                <div>
                  <label class="block text-xs text-gray-400 mb-1">Zip Code</label>
                  <input type="text" placeholder="90015"
                    class="w-full rounded-md bg-[#1f1f1f] border border-white/15 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
                </div>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2 md:col-span-1">
                  <label class="block text-xs text-gray-400 mb-1">Country</label>
                  <input type="text" placeholder="USA"
                    class="w-full rounded-md bg-[#1f1f1f] border border-white/15 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
                </div>
                <div>
                  <label class="block text-xs text-gray-400 mb-1">Email / Phone</label>
                  <input type="text" name="email"
                    value="{{ old('email"', auth()->user()->email" ?? auth()->user()->email ?? '') }}"
                    class="w-full rounded-md bg-[#1f1f1f] border border-white/15 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
                </div>
              </div>

              <div class="flex justify-end pt-2">
                <button type="submit"
                  class="inline-flex items-center rounded-md bg-[#0a8aff] hover:bg-[#0a79e0] px-4 py-2 text-sm font-semibold shadow ring-1 ring-white/10">
                  Save
                </button>
              </div>
            </div>
          </form>
        </section>

        {{-- ===== PAYMENT METHOD CARD ===== --}}
        <section class="mt-8 rounded-2xl bg-[#2a2a2a] shadow-xl ring-1 ring-white/10 p-6 md:p-8">
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-extrabold">Payment Method</h3>
            <button type="button"
              class="inline-flex items-center gap-2 text-sm font-semibold rounded-md px-3 py-1.5 border border-[#0a8aff] text-[#0a8aff] hover:bg-[#0a8aff] hover:text-white transition">
              <i class="fas fa-plus"></i>
              Add Payment
            </button>
          </div>
          <hr class="my-5 border-white/10">

          <ul class="space-y-4">
            <li class="grid grid-cols-[auto_1fr_auto_auto] items-center gap-4 bg-black/10 rounded-lg px-4 py-3 ring-1 ring-white/5">
              <img src="https://upload.wikimedia.org/wikipedia/commons/3/3b/Logo_OVO_purple.svg" alt="OVO" class="h-7 w-auto bg-white rounded-md p-1">
              <span class="text-sm text-gray-200">OVO</span>
              <span class="text-xs tracking-widest text-gray-400">+62 ••• •••• ••••</span>
              <button class="ml-3 text-gray-400 hover:text-gray-200" title="Delete">
                <i class="fas fa-trash-alt"></i>
              </button>
            </li>

            <li class="grid grid-cols-[auto_1fr_auto_auto] items-center gap-4 bg-black/10 rounded-lg px-4 py-3 ring-1 ring-white/5">
              <img src="https://upload.wikimedia.org/wikipedia/commons/2/2a/Mastercard-logo.svg" alt="MasterCard" class="h-7 w-auto bg-white rounded-md p-1">
              <span class="text-sm text-gray-200">Master Card</span>
              <span class="text-xs tracking-widest text-gray-400">•••• •••• •••• 1234</span>
              <button class="ml-3 text-gray-400 hover:text-gray-200" title="Delete">
                <i class="fas fa-trash-alt"></i>
              </button>
            </li>
          </ul>
        </section>
      </div>
    </main>
  </div>
</div>
@endsection