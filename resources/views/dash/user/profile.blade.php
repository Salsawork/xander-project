{{-- resources/views/dash/user/profile.blade.php --}}
@extends('app')
@section('title', 'User Dashboard - Profile')

@push('styles')
<style>
  :root{
    color-scheme: dark;
    --page-bg:#0a0a0a;
  }
  html, body{
    height:100%;
    background:var(--page-bg);
    overscroll-behavior-y: none;
    overscroll-behavior-x: none;
  }
  body::before{
    content:"";
    position:fixed;
    inset:0;
    background:var(--page-bg);
    pointer-events:none;
    z-index:-1;
  }
  #app, main, .min-h-screen{ background:var(--page-bg); }
  .prevent-bounce{
    overscroll-behavior: contain;
    -webkit-overflow-scrolling: touch;
    background:var(--page-bg);
  }

  /* ===== MOBILE ONLY (≤ 767px) ===== */
  @media (max-width: 767px){
    .profile-wrap{ margin-left:1rem !important; margin-right:1rem !important; }
    .profile-card{ padding:1rem !important; border-radius:16px !important; }
    .profile-header{ align-items:center !important; text-align:center !important; gap:.75rem !important; }
    .profile-avatar{ width:80px !important; height:80px !important; border-radius:9999px; outline:2px solid rgba(255,255,255,.12); box-shadow:0 6px 20px rgba(0,0,0,.35); }
    .profile-name{ font-size:1.25rem !important; }
    .profile-meta{ font-size:.9375rem !important; line-height:1.6; color:#d0d0d0; }
    .profile-label{ display:block; font-size:.8rem; color:#c8c8c8; margin-bottom:.35rem; letter-spacing:.02em; }
    .profile-input{
      width:100%; min-height:44px; border-radius:12px; background:#1a1a1a;
      border:1px solid rgba(255,255,255,.12); padding:.75rem .9rem; color:#fff;
      font-size:16px; line-height:1.2;
    }
    .profile-input:focus{ outline:none; border-color:rgba(10,138,255,.55); box-shadow:0 0 0 2px rgba(10,138,255,.30); }
    .profile-save{ width:100%; justify-content:center; }
  }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-neutral-900 text-white">
  <div class="flex min-h-[100dvh]">
    @include('partials.sidebar')

    <main class="flex-1 overflow-y-auto min-w-0 my-8 prevent-bounce">
      @include('partials.topbar')

      <div class="max-w-6xl mt-16 mx-16 profile-wrap">
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
          /** @var \App\Models\User|null $user */
          $user = auth()->user();

          /*
           |=========================================================
           | Avatar lookup order:
           | 1) NEW PATH: public/images/demo-xanders/avatars/{id}_*.ext
           | 2) LEGACY  : public/images/avatars/{id}_*.ext
           | 3) $user->avatar_url (absolute/relative)
           | 4) Initials
           |=========================================================
          */

          $searchBases = [
            ['web' => 'images/demo-xanders/avatars', 'fs' => public_path('images/demo-xanders/avatars')], // NEW
            ['web' => 'images/avatars',              'fs' => public_path('images/avatars')],              // LEGACY
          ];

          $avatarUrl = null;  // final <img src="">
          $avatarFs  = null;  // file path for cache-busting

          if ($user?->id) {
            $uid = $user->id;
            foreach ($searchBases as $base) {
              $pattern = rtrim($base['fs'], '/').'/'.$uid.'_*'.'.{jpg,jpeg,png,webp}';
              $found = glob($pattern, GLOB_BRACE) ?: [];
              if (!empty($found)) {
                // pilih file terbaru berdasarkan mtime
                usort($found, function($a,$b){ return filemtime($b) <=> filemtime($a); });
                $avatarFs  = $found[0];
                $avatarUrl = asset(rtrim($base['web'], '/').'/'.basename($avatarFs));
                break;
              }
            }
          }

          // Fallback: accessor avatar_url
          if (!$avatarUrl && ($user?->avatar_url)) {
            $maybe = $user->avatar_url;
            if (filter_var($maybe, FILTER_VALIDATE_URL)) {
              $avatarUrl = $maybe;                      // absolute URL
            } else {
              $avatarUrl = asset(ltrim($maybe, '/'));   // relative to public/
            }
          }

          // Cache-busting: append ?v=mtime jika tahu file fisiknya
          if ($avatarUrl && $avatarFs) {
            $mtime = @filemtime($avatarFs);
            if ($mtime) {
              $avatarUrl .= (strpos($avatarUrl, '?') !== false ? '&' : '?').'v='.$mtime;
            }
          }

          // Initials fallback
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
        <section class="mt-20 rounded-2xl bg-[#2a2a2a] shadow-xl ring-1 ring-white/10 p-6 md:p-8 profile-card">
          <div class="flex flex-col md:flex-row md:items-center md:gap-6 profile-header">
            {{-- AVATAR --}}
            <div class="relative">
              <button id="avatarTrigger"
                      type="button"
                      aria-haspopup="true"
                      aria-expanded="false"
                      class="relative h-20 w-20 rounded-full ring-2 ring-white/10 overflow-hidden focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 profile-avatar">
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

              {{-- Popover --}}
              <div id="avatarMenu"
                   class="hidden absolute z-20 left-1/2 -translate-x-1/2 mt-3 w-56 rounded-2xl border border-white/10 bg-[#1f1f1f] shadow-[0_20px_60px_rgba(0,0,0,.5)] overflow-hidden">
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
              <input id="photoInput" type="file" name="photo_profile" form="profileForm" accept="image/*" class="hidden">
              <input id="removePhotoInput" type="hidden" name="remove_photo" value="0" form="profileForm">
            </div>

            <div class="flex-1 mt-4 md:mt-0">
              <h2 class="text-xl font-extrabold profile-name">{{ $user->name ?? '' }}</h2>
              <p class="text-gray-300 text-sm leading-6 profile-meta">
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
                <label class="block text-xs text-gray-400 mb-1 profile-label">Full Name</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}"
                       class="w-full rounded-md bg-[#1f1f1f] border border-white/15 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 profile-input">
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs text-gray-400 mb-1 profile-label">First Name</label>
                  <input type="text" name="firstname" value="{{ old('firstname', $user->firstname) }}"
                         class="w-full rounded-md bg-[#1f1f1f] border border-white/15 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 profile-input">
                </div>
                <div>
                  <label class="block text-xs text-gray-400 mb-1 profile-label">Last Name</label>
                  <input type="text" name="lastname" value="{{ old('lastname', $user->lastname) }}"
                         class="w-full rounded-md bg-[#1f1f1f] border border-white/15 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 profile-input">
                </div>
              </div>

              <div>
                <label class="block text-xs text-gray-400 mb-1 profile-label">Phone</label>
                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                       class="w-full rounded-md bg-[#1f1f1f] border border-white/15 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 profile-input">
              </div>
            </div>

            {{-- Divider --}}
            <div class="hidden md:block border-l border-white/10"></div>

            {{-- Kanan --}}
            <div class="space-y-4 md:col-span-1">
              <div>
                <label class="block text-xs text-gray-400 mb-1 profile-label">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}"
                       class="w-full rounded-md bg-[#1f1f1f] border border-white/15 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600 profile-input">
              </div>

              <div class="flex justify-end pt-2">
                <button type="submit"
                        class="inline-flex items-center rounded-md bg-[#0a8aff] hover:bg-[#0a79e0] px-4 py-2 text-sm font-semibold shadow ring-1 ring-white/10 profile-save">
                  Save
                </button>
              </div>
            </div>
          </form>
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
    const input   = document.getElementById('photoInput');
    const rmInput = document.getElementById('removePhotoInput');
    const preview = document.getElementById('avatarPreview');
    const holder  = document.getElementById('avatarPlaceholder');

    const closeMenu = () => { if(menu){ menu.classList.add('hidden'); trigger?.setAttribute('aria-expanded','false'); } };
    const toggleMenu= () => { if(menu){ menu.classList.toggle('hidden'); trigger?.setAttribute('aria-expanded', menu.classList.contains('hidden') ? 'false' : 'true'); } };

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
