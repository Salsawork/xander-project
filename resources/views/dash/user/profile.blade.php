{{-- resources/views/dash/user/profile.blade.php --}}
@extends('app')
@section('title', 'User Dashboard - Profile')

@section('content')
<div class="min-h-screen bg-neutral-900 text-white">
  <div class="flex min-h-[100dvh]">
    @include('partials.sidebar')

    <main class="flex-1 overflow-y-auto min-w-0 my-8">
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
          $avatarUrl = $user?->avatar_url; // accessor dari model
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
            <div class="relative">
              @if($avatarUrl)
                <img id="avatarPreview"
                     src="{{ $avatarUrl }}"
                     alt="Avatar"
                     class="h-20 w-20 rounded-full object-cover ring-2 ring-white/10 mb-4 md:mb-0">
                <div id="avatarPlaceholder" class="hidden"></div>
              @else
                <div id="avatarPlaceholder"
                     class="h-20 w-20 rounded-full bg-[#1f1f1f] ring-2 ring-white/10 mb-4 md:mb-0 grid place-items-center text-xl font-bold select-none">
                  {{ $initials }}
                </div>
                <img id="avatarPreview" src="" alt="" class="hidden h-20 w-20 rounded-full object-cover ring-2 ring-white/10">
              @endif
              <input id="avatarInput" type="file" name="avatar" form="profileForm" accept="image/*" class="hidden">
            </div>

            <div class="flex-1">
              <h2 class="text-xl font-extrabold">{{ $user->name ?? '' }}</h2>
              <p class="text-gray-300 text-sm leading-6">
                {{ $user->email ?? '' }}<br>
                {{ $user->phone ?? '' }}
              </p>

              <div class="mt-3 flex items-center gap-3">
                <button type="button"
                        onclick="document.getElementById('avatarInput').click()"
                        class="inline-flex items-center gap-2 text-sm font-semibold rounded-md px-3 py-1.5 border border-white/15 hover:bg-white/10">
                  <i class="fas fa-camera"></i>
                  Change Photo
                </button>

                @if($avatarUrl)
                  <label class="inline-flex items-center gap-2 text-sm font-semibold rounded-md px-3 py-1.5 border border-red-600/50 text-red-300 hover:bg-red-600/10 cursor-pointer">
                    <input type="checkbox" name="remove_avatar" value="1" form="profileForm" class="mr-1 accent-red-600" id="removeAvatarCheckbox">
                    Remove Photo
                  </label>
                @endif
              </div>
            </div>
          </div>

          {{-- Form --}}
          <form id="profileForm" method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data"
                class="mt-8 grid grid-cols-1 md:grid-cols-[1fr_auto_1fr] gap-6">
            @csrf

            {{-- Kiri --}}
            <div class="space-y-4 md:col-span-1">
              <div>
                <label class="block text-xs text-gray-400 mb-1">Full Name</label>
                <input type="text" name="name"
                       value="{{ old('name', $user->name) }}"
                       placeholder="Your full name"
                       class="w-full rounded-md bg-[#1f1f1f] border border-white/15 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs text-gray-400 mb-1">First Name</label>
                  <input type="text" name="firstname"
                         value="{{ old('firstname', $user->firstname) }}"
                         placeholder="First name"
                         class="w-full rounded-md bg-[#1f1f1f] border border-white/15 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
                </div>
                <div>
                  <label class="block text-xs text-gray-400 mb-1">Last Name</label>
                  <input type="text" name="lastname"
                         value="{{ old('lastname', $user->lastname) }}"
                         placeholder="Last name"
                         class="w-full rounded-md bg-[#1f1f1f] border border-white/15 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
                </div>
              </div>

              <div>
                <label class="block text-xs text-gray-400 mb-1">Phone</label>
                <input type="text" name="phone"
                       value="{{ old('phone', $user->phone) }}"
                       placeholder="+62 ..."
                       class="w-full rounded-md bg-[#1f1f1f] border border-white/15 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
              </div>
            </div>

            {{-- Divider --}}
            <div class="hidden md:block border-l border-white/10"></div>

            {{-- Kanan --}}
            <div class="space-y-4 md:col-span-1">
              <div>
                <label class="block text-xs text-gray-400 mb-1">Email</label>
                <input type="email" name="email"
                       value="{{ old('email', $user->email) }}"
                       placeholder="you@example.com"
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

        {{-- OPTIONAL: Payment Method contoh UI --}}
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

{{-- Avatar preview / remove UI logic --}}
<script>
  const input    = document.getElementById('avatarInput');
  const preview  = document.getElementById('avatarPreview');
  const holder   = document.getElementById('avatarPlaceholder');
  const removeCb = document.getElementById('removeAvatarCheckbox');

  if (input) {
    input.addEventListener('change', () => {
      const [file] = input.files || [];
      if (file) {
        const url = URL.createObjectURL(file);
        if (holder) holder.classList.add('hidden');
        if (preview) {
          preview.src = url;
          preview.classList.remove('hidden');
        }
        if (removeCb) removeCb.checked = false;
      }
    });
  }

  if (removeCb && preview && holder) {
    removeCb.addEventListener('change', (e) => {
      if (e.target.checked) {
        preview.classList.add('hidden');
        holder.classList.remove('hidden');
      } else {
        if (preview.src) preview.classList.remove('hidden');
        holder.classList.toggle('hidden', !!preview.src);
      }
    });
  }
</script>
@endsection
