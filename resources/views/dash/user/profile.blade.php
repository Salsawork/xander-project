{{-- resources/views/dash/user/profile.blade.php --}}
@extends('app')
@section('title', 'User Dashboard - Profile')

@push('styles')
<style>
  :root{
    color-scheme: dark;
    --page-bg:#0a0a0a;
  }
  /* cegah “white glow” saat overscroll di iOS/Android */
  html, body{
    height:100%;
    background:var(--page-bg);
    overscroll-behavior-y: none;   /* stop rubber-band to parent */
    overscroll-behavior-x: none;
  }
  /* lapisan tetap agar tak pernah kelihatan putih */
  body::before{
    content:"";
    position:fixed;
    inset:0;
    background:var(--page-bg);
    pointer-events:none;
    z-index:-1;
  }
  /* pastikan semua container utama juga gelap */
  #app, main, .min-h-screen{ background:var(--page-bg); }
  /* container scroll utama: momentum namun tanpa bounce */
  .prevent-bounce{
    overscroll-behavior: contain;  /* contain within this scroller */
    -webkit-overflow-scrolling: touch;
    background:var(--page-bg);
  }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-neutral-900 text-white">
  <div class="flex min-h-[100dvh]">
    @include('partials.sidebar')

    {{-- tambahkan prevent-bounce pada main --}}
    <main class="flex-1 overflow-y-auto min-w-0 my-8 prevent-bounce">
      @include('partials.topbar')

      <div class="max-w-6xl mt-16 mx-16">
        {{-- Flash & Validation --}}
        @if (session('success'))
          <div class="mb-4 rounded-md bg-green-600/20 text-green-300 px-4 py-3 border border-green-600/30">
            {{ session('success') }}
          </div>
        @endif
        @if ($errors->any())
          <div class="mb-4 rounded-md bg-red-600/15 text-red-300 px-4 py-3 border border-red-600/30">
            <ul class="list-disc list-inside text-sm">
              @foreach($errors->all() as $e)
                <li>{{ $e }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        @php
          $user = auth()->user();
          $avatarUrl = $user?->avatar_url;
          $nameRaw = trim($user->name ?? '');
          $parts = preg_split('/\s+/', $nameRaw, -1, PREG_SPLIT_NO_EMPTY);
          $initials = '';
          if ($parts) {
            $initials .= mb_strtoupper(mb_substr($parts[0],0,1));
            if (count($parts) > 1) $initials .= mb_strtoupper(mb_substr($parts[1],0,1));
          }
          if ($initials === '') $initials = 'U';
        @endphp

        {{-- PROFILE CARD --}}
        <section class="mt-20 rounded-2xl bg-[#2a2a2a] shadow-xl ring-1 ring-white/10 p-6 md:p-8">
          <div class="flex flex-col md:flex-row md:items-center md:gap-6">
            {{-- AVATAR (klik untuk menu) --}}
            <div class="relative">
              <button id="avatarTrigger"
                      type="button"
                      aria-haspopup="true"
                      aria-expanded="false"
                      class="relative h-20 w-20 rounded-full ring-2 ring-white/10 overflow-hidden focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500">
                @if($avatarUrl)
                  <img id="avatarPreview"
                       src="{{ $avatarUrl }}"
                       alt="Profile photo"
                       class="h-full w-full object-cover">
                @else
                  <div id="avatarPlaceholder"
                       class="h-full w-full bg-[#1f1f1f] grid place-items-center text-xl font-bold select-none">
                    {{ $initials }}
                  </div>
                @endif
              </button>

              {{-- Popover solid (BG #1f1f1f) --}}
              <div id="avatarMenu"
                   class="hidden absolute z-20 left-1/2 -translate-x-1/2 mt-3 w-56 rounded-2xl border border-white/10 bg-[#1f1f1f] shadow-[0_20px_60px_rgba(0,0,0,.5)] overflow-hidden">
                {{-- Arrow solid --}}
                <div class="absolute -top-2 left-1/2 -translate-x-1/2 h-4 w-4 rotate-45 bg-[#1f1f1f] border-t border-l border-white/10"></div>

                <div class="py-1">
                  <button id="menuUpload" type="button"
                          class="w-full text-left px-4 py-3 text-[15px] hover:bg-white/10 flex items-center gap-3 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500">
                    <i class="fas fa-camera text-base"></i>
                    <span class="font-medium">Upload New Photo</span>
                  </button>

                  @if($avatarUrl)
                    <button id="menuRemove" type="button"
                            class="w-full text-left px-4 py-3 text-[15px] text-red-300 hover:bg-red-600/15 flex items-center gap-3 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500">
                      <i class="fas fa-trash-alt text-base"></i>
                      <span class="font-medium">Remove Photo</span>
                    </button>
                  @endif
                </div>
              </div>

              {{-- Inputs (terhubung ke form) --}}
              <input id="avatarInput" type="file" name="avatar" form="profileForm" accept="image/*" class="hidden">
              <input id="removeAvatarInput" type="hidden" name="remove_avatar" value="0" form="profileForm">
            </div>

            <div class="flex-1 mt-4 md:mt-0">
              <h2 class="text-xl font-extrabold">{{ $user->name ?? '' }}</h2>
              <p class="text-gray-300 text-sm leading-6">
                {{ $user->email ?? '' }}<br>
                {{ $user->phone ?? '' }}
              </p>
            </div>
          </div>

          {{-- FORM --}}
          <form id="profileForm" method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data"
                class="mt-8 grid grid-cols-1 md:grid-cols-[1fr_auto_1fr] gap-6">
            @csrf

            {{-- Kiri --}}
            <div class="space-y-4 md:col-span-1">
              <div>
                <label class="block text-xs text-gray-400 mb-1">Full Name</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}"
                       class="w-full rounded-md bg-[#1f1f1f] border border-white/15 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs text-gray-400 mb-1">First Name</label>
                  <input type="text" name="firstname" value="{{ old('firstname', $user->firstname) }}"
                         class="w-full rounded-md bg-[#1f1f1f] border border-white/15 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
                </div>
                <div>
                  <label class="block text-xs text-gray-400 mb-1">Last Name</label>
                  <input type="text" name="lastname" value="{{ old('lastname', $user->lastname) }}"
                         class="w-full rounded-md bg-[#1f1f1f] border border-white/15 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
                </div>
              </div>

              <div>
                <label class="block text-xs text-gray-400 mb-1">Phone</label>
                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                       class="w-full rounded-md bg-[#1f1f1f] border border-white/15 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
              </div>
            </div>

            {{-- Divider --}}
            <div class="hidden md:block border-l border-white/10"></div>

            {{-- Kanan --}}
            <div class="space-y-4 md:col-span-1">
              <div>
                <label class="block text-xs text-gray-400 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}"
                       class="w-full rounded-md bg-[#1f1f1f] border border-white/15 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
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

        {{-- OPTIONAL: Payment Method demo --}}
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

{{-- Avatar interactions --}}
<script>
  (function(){
    const trigger = document.getElementById('avatarTrigger');
    const menu    = document.getElementById('avatarMenu');
    const upload  = document.getElementById('menuUpload');
    const remove  = document.getElementById('menuRemove');
    const input   = document.getElementById('avatarInput');
    const rmInput = document.getElementById('removeAvatarInput');
    const preview = document.getElementById('avatarPreview');
    const holder  = document.getElementById('avatarPlaceholder');

    const openMenu  = () => { if(menu){ menu.classList.remove('hidden'); trigger?.setAttribute('aria-expanded','true'); } };
    const closeMenu = () => { if(menu){ menu.classList.add('hidden'); trigger?.setAttribute('aria-expanded','false'); } };
    const toggleMenu= () => { if(menu){ menu.classList.toggle('hidden'); trigger?.setAttribute('aria-expanded', menu.classList.contains('hidden') ? 'false' : 'true'); } };

    // open/close
    trigger?.addEventListener('click', (e) => { e.stopPropagation(); toggleMenu(); });

    // Upload
    upload?.addEventListener('click', (e) => {
      e.stopPropagation();
      closeMenu();
      if (rmInput) rmInput.value = '0';
      input?.click();
    });

    // Remove
    remove?.addEventListener('click', (e) => {
      e.stopPropagation();
      closeMenu();
      if (rmInput) rmInput.value = '1';
      if (preview) preview.classList.add('hidden');
      if (holder)  holder.classList.remove('hidden');
    });

    // Preview on choose
    input?.addEventListener('change', () => {
      const [file] = input.files || [];
      if (file) {
        const url = URL.createObjectURL(file);
        if (preview) { preview.src = url; preview.classList.remove('hidden'); }
        if (holder) holder.classList.add('hidden');
        if (rmInput) rmInput.value = '0';
      }
    });

    // Close on outside click / ESC
    document.addEventListener('click', (e) => {
      if (!menu || menu.classList.contains('hidden')) return;
      if (!(menu.contains(e.target) || trigger.contains(e.target))) closeMenu();
    });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeMenu(); });
  })();
</script>
@endsection
