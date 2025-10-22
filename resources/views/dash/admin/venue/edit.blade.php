@extends('app')
@section('title', 'Admin Dashboard - Edit Venue')

@push('styles')
<link
  rel="stylesheet"
  href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
  integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
  crossorigin=""
/>
<style>
  :root{ color-scheme: dark; --page-bg:#0a0a0a; }
  html, body{
    height:100%; min-height:100%; background:var(--page-bg);
    overscroll-behavior: none; touch-action: pan-y; -webkit-text-size-adjust:100%;
  }
  #antiBounceBg{ position: fixed; left:0; right:0; top:-120svh; bottom:-120svh; background:var(--page-bg); z-index:-1; pointer-events:none; }
  .scroll-safe{ background-color:#171717; overscroll-behavior: contain; -webkit-overflow-scrolling: touch; }

  .leaflet-map{ height:300px; border-radius:12px; overflow:hidden; border:1px solid #3a3a3a; }
  .geocode-wrap{ position:relative; }
  .geocode-input{
    width:100%; padding:.65rem .9rem; border-radius:10px; background:#222; color:#fff;
    border:1.5px solid rgba(255,255,255,.2); outline:none; font-size:13px;
  }
  .geocode-input:focus{ box-shadow:0 0 0 2px #3b82f6; border-color:#3b82f6; }
  .suggest-box{
    position:absolute; z-index:30; left:0; right:0; top:100%; margin-top:6px;
    background:#1c1c1c; border:1px solid rgba(255,255,255,.15); border-radius:10px; overflow:hidden;
    max-height:220px; overflow-y:auto;
  }
  .suggest-item{ padding:.55rem .7rem; font-size:13px; color:#e5e7eb; cursor:pointer; }
  .suggest-item:hover{ background:#2a2a2a; }
</style>
@endpush

@section('content')
  <div id="antiBounceBg" aria-hidden="true"></div>

  <div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
    <div class="flex flex-1 min-h-0">
      @include('partials.sidebar')
      <main class="flex-1 overflow-y-auto min-w-0 mb-8 scroll-safe">
        @include('partials.topbar')

        <div class="mt-20 sm:mt-28 px-4 sm:px-8">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
            <h1 class="text-2xl sm:text-3xl font-extrabold">Edit Venue: {{ $venue->name }}</h1>
            <a href="{{ route('venue.index') }}" class="flex items-center gap-2 text-blue-400 hover:text-blue-300 text-sm">
              <i class="fas fa-arrow-left"></i><span>Kembali ke daftar venue</span>
            </a>
          </div>

          <form id="editVenueForm" action="{{ route('venue.update', $venue->id) }}" method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 sm:gap-8">
              <!-- Kolom Kiri -->
              <div class="space-y-6">
                <div class="bg-[#262626] rounded-lg p-4 sm:p-6 space-y-4">
                  <h2 class="text-base sm:text-lg font-bold border-b border-gray-600 pb-2 flex items-center">
                    <i class="fas fa-user-circle mr-2 text-blue-400"></i> Informasi Akun
                  </h2>

                  <div class="space-y-4">
                    <div>
                      <label class="block text-xs text-gray-400 mb-1" for="name">Nama Pengelola</label>
                      <input class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                        name="name" id="name" type="text" value="{{ $venue->user->name }}" />
                      @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                      <label class="block text-xs text-gray-400 mb-1" for="email">Email</label>
                      <input class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                        name="email" id="email" type="email" value="{{ $venue->user->email }}" />
                      @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                      <label class="block text-xs text-gray-400 mb-1" for="password">Password (opsional)</label>
                      <input class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                        name="password" id="password" type="password" placeholder="Kosongkan jika tidak ingin mengubah" />
                      @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                  </div>
                </div>
              </div>

              <!-- Kolom Kanan -->
              <div class="space-y-6">
                <div class="bg-[#262626] rounded-lg p-4 sm:p-6 space-y-4">
                  <h2 class="text-base sm:text-lg font-bold border-b border-gray-600 pb-2 flex items-center">
                    <i class="fas fa-store mr-2 text-green-400"></i> Informasi Venue
                  </h2>

                  <div class="space-y-4">
                    <div>
                      <label class="block text-xs text-gray-400 mb-1" for="venue_name">Nama Venue</label>
                      <input class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                        name="venue_name" id="venue_name" type="text" value="{{ $venue->name }}" />
                      @error('venue_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                      <label class="block text-xs text-gray-400 mb-1" for="image">Gambar Venue</label>
                      <input name="image" id="image" type="file"
                        class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500" />
                      @error('image') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- ADDRESS + MAP (Leaflet) --}}
                    <div class="space-y-3">
                      <label class="block text-xs text-gray-400">Lokasi Venue</label>

                      <div class="geocode-wrap">
                        <input id="searchBox" type="text" class="geocode-input" placeholder="Cari alamat / tempat (gratis, OSM)">
                        <div id="suggestions" class="suggest-box hidden"></div>
                      </div>

                      <textarea
                        class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white resize-none focus:outline-none focus:ring-1 focus:ring-blue-500"
                        name="address" id="address" rows="3">{{ $venue->address }}</textarea>
                      @error('address') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror

                      <input type="hidden" name="latitude" id="latitude" value="{{ $venue->latitude }}">
                      <input type="hidden" name="longitude" id="longitude" value="{{ $venue->longitude }}">

                      <div id="mapEdit" class="leaflet-map"></div>
                      <p class="text-xs text-gray-400">Tip: klik peta untuk menetapkan pin, atau tarik pin untuk pindah. Alamat akan diisi otomatis.</p>
                    </div>

                    <div>
                      <label class="block text-xs text-gray-400 mb-1" for="phone">Nomor Telepon</label>
                      <input
                        class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                        name="phone" id="phone" type="text" value="{{ $venue->phone }}" />
                      @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                      <label class="block text-xs text-gray-400 mb-1" for="operating_hours">Jam Operasional</label>
                      <input
                        class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                        name="operating_hours" id="operating_hours" type="text" value="{{ $venue->operating_hours }}" />
                      @error('operating_hours') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                      <label class="block text-xs text-gray-400 mb-1" for="description">Deskripsi</label>
                      <textarea
                        class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white resize-none focus:outline-none focus:ring-1 focus:ring-blue-500"
                        name="description" id="description" rows="4">{{ $venue->description }}</textarea>
                      @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="flex flex-col sm:flex-row justify-end mt-6 sm:mt-8 gap-3 sm:gap-0 sm:space-x-4">
              <a href="{{ route('venue.index') }}"
                class="w-full sm:w-auto px-6 py-2 border border-gray-600 text-gray-300 rounded-md hover:bg-gray-700 transition text-center text-sm order-2 sm:order-1">
                Batal
              </a>
              <button type="submit"
                class="w-full sm:w-auto px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition text-sm order-1 sm:order-2">
                Simpan Perubahan
              </button>
            </div>
          </form>
        </div>
      </main>
    </div>
  </div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
  integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
  crossorigin=""></script>
<script>
(function(){
  const init = {
    lat: parseFloat(@json($venue->latitude ?? -6.175392)),
    lng: parseFloat(@json($venue->longitude ?? 106.827153))
  };
  if (isNaN(init.lat) || isNaN(init.lng)) { init.lat = -6.175392; init.lng = 106.827153; }

  const map = L.map('mapEdit', { zoomControl: true, scrollWheelZoom: true }).setView([init.lat, init.lng], 15);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{ maxZoom:19, attribution:'&copy; OpenStreetMap' }).addTo(map);

  const latEl = document.getElementById('latitude');
  const lngEl = document.getElementById('longitude');
  const addrEl = document.getElementById('address');
  const searchBox = document.getElementById('searchBox');
  const suggestBox = document.getElementById('suggestions');

  let marker = L.marker([init.lat, init.lng], { draggable:true }).addTo(map);

  function updatePosition(lat, lng, doReverse = true){
    latEl.value = lat.toFixed(7);
    lngEl.value = lng.toFixed(7);
    if (doReverse) {
      fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`)
        .then(r => r.json())
        .then(j => { if (j && j.display_name && addrEl) addrEl.value = j.display_name; })
        .catch(()=>{});
    }
  }

  marker.on('dragend', () => {
    const { lat, lng } = marker.getLatLng();
    updatePosition(lat, lng, true);
  });

  map.on('click', (e)=>{
    const { lat, lng } = e.latlng;
    marker.setLatLng([lat,lng]);
    updatePosition(lat,lng,true);
  });

  let t; function debounce(fn, ms){ return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a), ms); }; }
  const runSearch = debounce(()=>{
    const q = (searchBox.value||'').trim();
    if (!q){ suggestBox.classList.add('hidden'); suggestBox.innerHTML=''; return; }
    fetch(`https://photon.komoot.io/api/?q=${encodeURIComponent(q)}&lang=id`)
      .then(r=>r.json()).then(j=>{
        const feats = j.features||[];
        if (!feats.length){ suggestBox.classList.add('hidden'); suggestBox.innerHTML=''; return; }
        suggestBox.innerHTML = feats.slice(0,8).map(f=>{
          const name=f.properties.name||'';
          const city=f.properties.city||f.properties.county||f.properties.state||'';
          const country=f.properties.country||'';
          const label=[name,city,country].filter(Boolean).join(', ');
          const c=f.geometry.coordinates; // [lng, lat]
          return `<div class="suggest-item" data-lat="${c[1]}" data-lng="${c[0]}">${label}</div>`;
        }).join('');
        suggestBox.classList.remove('hidden');
      }).catch(()=>{ suggestBox.classList.add('hidden'); suggestBox.innerHTML=''; });
  }, 350);
  searchBox.addEventListener('input', runSearch);
  document.addEventListener('click', (e)=>{ if (!suggestBox.contains(e.target) && e.target!==searchBox) suggestBox.classList.add('hidden'); });
  suggestBox.addEventListener('click', (e)=>{
    const it = e.target.closest('.suggest-item'); if (!it) return;
    const lat = parseFloat(it.dataset.lat), lng=parseFloat(it.dataset.lng);
    marker.setLatLng([lat,lng]); map.setView([lat,lng], 16); updatePosition(lat,lng,true); suggestBox.classList.add('hidden');
  });
})();
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  @if(session('success'))
  Swal.fire({ icon:'success', title:'Berhasil!', text:'{{ session('success') }}', showConfirmButton:false, timer:3000, background:'#222', color:'#fff' });
  @endif
  @if(session('error'))
  Swal.fire({ icon:'error', title:'Error!', text:'{{ session('error') }}', showConfirmButton:true, background:'#222', color:'#fff' });
  @endif
</script>
@endpush
