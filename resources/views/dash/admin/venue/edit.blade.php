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
  :root{
    color-scheme: dark;
    --page-bg:#0a0a0a;
    --topbar-h: 64px;
    --topbar-z: 90;
  }

  html, body{
    height:100%;
    min-height:100%;
    background:var(--page-bg);
    overscroll-behavior: none;
    touch-action: pan-y;
    -webkit-text-size-adjust:100%;
  }

  #antiBounceBg{
    position: fixed; left:0; right:0; top:-120svh; bottom:-120svh;
    background:var(--page-bg); z-index:-1; pointer-events:none;
  }

  .scroll-safe{ background-color:#171717; overscroll-behavior: contain; -webkit-overflow-scrolling: touch; }
  header.fixed, header[class*="fixed"]{ z-index: var(--topbar-z) !important; }
  .has-fixed-topbar{ padding-top: var(--topbar-h); }

  .leaflet-map{
    height:300px; border-radius:12px; overflow:hidden; border:1px solid #3a3a3a;
    position: relative; z-index:0;
  }
  .leaflet-container{ z-index:0 !important; }
  .leaflet-pane, .leaflet-top, .leaflet-bottom{ z-index:0 !important; }

  .geocode-wrap{ position:relative; }
  .geocode-input{
    width:100%; padding:.65rem .9rem; border-radius:10px; background:#222; color:#fff;
    border:1.5px solid rgba(255,255,255,.2); outline:none; font-size:13px;
  }
  .geocode-input:focus{ box-shadow:0 0 0 2px #3b82f6; border-color:#3b82f6; }
  .suggest-box{
    position:absolute; z-index:30;
    left:0; right:0; top:100%; margin-top:6px;
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

      <main class="flex-1 overflow-y-auto min-w-0 mb-8 scroll-safe has-fixed-topbar">
        @include('partials.topbar')

        <div class="px-4 sm:px-8">
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
                      <label class="block text-xs text-gray-400 mb-1">Upload Gambar (Opsional)</label>
                      <input type="file" name="images[]" multiple accept="image/*"
                        class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500" />
                      <p class="text-xs text-gray-500 mt-1">Format: JPG, PNG, WEBP, AVIF, GIF. Maks: 4MB/berkas</p>

                      @php
                        $existingImages = is_array($venue->images ?? null) ? $venue->images : [];
                        // Tampilkan dari FE CDN: https://demo-xanders.ptbmn.id/images/venue/{filename}
                        $normalizeImg = function($img){
                          $raw = trim((string)$img);
                          if (preg_match('/^https?:\/\//i', $raw)) return $raw;
                          $filename = basename($raw);
                          return 'https://demo-xanders.ptbmn.id/images/venue/' . $filename;
                        };
                      @endphp

                      @if(!empty($existingImages))
                      <div class="mt-4">
                        <label class="block text-xs text-gray-400 mb-2">Gambar Saat Ini</label>
                        <div class="flex flex-wrap gap-2">
                          @foreach($existingImages as $img)
                            @php $src = $normalizeImg($img); @endphp
                            <div class="relative w-20 h-20">
                              <img src="{{ $src }}" alt="venue image" class="w-full h-full object-cover rounded-md"
                                   onerror="this.src='https://placehold.co/400x400?text=No+Img'"/>
                            </div>
                          @endforeach
                        </div>
                      </div>
                      @endif
                    </div>

                    {{-- ADDRESS + MAP --}}
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

                      <input type="hidden" name="latitude" id="latitude" value="{{ $venue->latitude ?? '' }}">
                      <input type="hidden" name="longitude" id="longitude" value="{{ $venue->longitude ?? '' }}">

                      <div id="mapEdit" class="leaflet-map"></div>
                      <p class="text-xs text-gray-400">Ketik alamat (contoh: “Jalan Gambir” atau “RW 02, Gambir, Jakarta”). Peta akan otomatis mengarah.</p>
                    </div>

                    <div>
                      <label class="block text-xs text-gray-400 mb-1" for="phone">Nomor Telepon</label>
                      <input
                        class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                        name="phone" id="phone" type="text" value="{{ $venue->phone }}" />
                      @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                      <label class="block text-xs text-gray-400 mb-1">Jam Operasional</label>
                      <div class="flex gap-3">
                        <div class="flex-1">
                          <input name="operating_hour"
                            value="{{ old('operating_hour', $venue->operating_hour) }}"
                            class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                            id="operating_hour" type="time" />
                          @error('operating_hour') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="flex-1">
                          <input name="closing_hour"
                            value="{{ old('closing_hour', $venue->closing_hour) }}"
                            class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                            id="closing_hour" type="time" />
                          @error('closing_hour') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                      </div>
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
  const DFLT = { lat: -6.175392, lng: 106.827153 }; // Monas fallback

  const latFromDb = parseFloat(@json($venue->latitude ?? ''));
  const lngFromDb = parseFloat(@json($venue->longitude ?? ''));
  const addrFromDb = @json($venue->address ?? '');

  const map = L.map('mapEdit', { zoomControl: true, scrollWheelZoom: true }).setView([DFLT.lat, DFLT.lng], 13);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{ maxZoom:19, attribution:'&copy; OpenStreetMap' }).addTo(map);

  const latEl = document.getElementById('latitude');
  const lngEl = document.getElementById('longitude');
  const addrEl = document.getElementById('address');
  const searchBox = document.getElementById('searchBox');
  const suggestBox = document.getElementById('suggestions');

  let marker = L.marker([DFLT.lat, DFLT.lng], { draggable:true }).addTo(map);

  function setHidden(lat, lng){ latEl.value = (+lat).toFixed(7); lngEl.value = (+lng).toFixed(7); }
  function reverseToAddress(lat, lng){
    fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}&accept-language=id`)
      .then(r => r.json())
      .then(j => { if (j && j.display_name) addrEl.value = j.display_name; })
      .catch(()=>{});
  }
  function centerTo(lat, lng, label = null, zoom = 16){
    marker.setLatLng([lat, lng]);
    map.setView([lat, lng], zoom);
    setHidden(lat, lng);
    if (label) addrEl.value = label;
    reverseToAddress(lat, lng);
  }

  (function initPosition(){
    if (!isNaN(latFromDb) && !isNaN(lngFromDb)) {
      centerTo(latFromDb, lngFromDb, addrFromDb || null, 16);
    } else if (addrFromDb && addrFromDb.trim().length > 0) {
      const q = encodeURIComponent(addrFromDb);
      fetch(`https://nominatim.openstreetmap.org/search?format=jsonv2&limit=1&accept-language=id&q=${q}`)
        .then(r=>r.json())
        .then(list=>{
          if (Array.isArray(list) && list.length){
            const lat = parseFloat(list[0].lat), lng = parseFloat(list[0].lon);
            centerTo(lat, lng, list[0].display_name || null, 16);
          } else {
            centerTo(DFLT.lat, DFLT.lng, null, 13);
          }
        })
        .catch(()=>centerTo(DFLT.lat, DFLT.lng, null, 13));
    } else {
      centerTo(DFLT.lat, DFLT.lng, null, 13);
    }
  })();

  marker.on('dragend', () => {
    const { lat, lng } = marker.getLatLng();
    setHidden(lat, lng);
    reverseToAddress(lat, lng);
  });
  map.on('click', (e)=> centerTo(e.latlng.lat, e.latlng.lng));

  function labelFromPhoton(f){
    const p = f.properties || {};
    return [p.name, p.street, (p.city || p.district || p.county || p.state), p.country].filter(Boolean).join(', ');
  }
  function renderPhoton(features){
    if(!features || !features.length){ suggestBox.classList.add('hidden'); suggestBox.innerHTML=''; return; }
    suggestBox.innerHTML = features.slice(0,8).map(f=>{
      const label = labelFromPhoton(f);
      const [lng, lat] = f.geometry.coordinates;
      return `<div class="suggest-item" data-lat="${lat}" data-lng="${lng}" data-label="${(label||'').replace(/"/g,'&quot;')}">${label}</div>`;
    }).join('');
    suggestBox.classList.remove('hidden');
  }
  function renderNominatim(list){
    if(!list || !list.length){ suggestBox.classList.add('hidden'); suggestBox.innerHTML=''; return; }
    suggestBox.innerHTML = list.slice(0,8).map(it=>{
      const label = it.display_name || '';
      return `<div class="suggest-item" data-lat="${it.lat}" data-lng="${it.lon}" data-label="${(label||'').replace(/"/g,'&quot;')}">${label}</div>`;
    }).join('');
    suggestBox.classList.remove('hidden');
  }
  function autoCenterPhoton(features){
    if(!features || !features.length) return false;
    const [lng, lat] = features[0].geometry.coordinates;
    centerTo(lat, lng, labelFromPhoton(features[0]));
    return true;
  }
  function autoCenterNominatim(list){
    if(!list || !list.length) return false;
    centerTo(parseFloat(list[0].lat), parseFloat(list[0].lon), list[0].display_name || '');
    return true;
  }

  let tmr, ctrl = null;
  const debounce = (fn, ms) => (...a)=>{ clearTimeout(tmr); tmr=setTimeout(()=>fn(...a), ms); };
  function abortLast(){ if(ctrl){ try{ctrl.abort();}catch(e){} ctrl=null; } }

  const runSearch = (q)=>{
    abortLast(); ctrl = new AbortController(); const signal = ctrl.signal;
    const bias = marker.getLatLng();

    fetch(`https://photon.komoot.io/api/?q=${encodeURIComponent(q)}&lang=id&lat=${bias.lat}&lon=${bias.lng}&limit=8`, {signal})
      .then(r=>r.json()).then(j=>{
        const feats = j && j.features ? j.features : [];
        if (feats.length){ renderPhoton(feats); autoCenterPhoton(feats); }
        else {
          return fetch(`https://nominatim.openstreetmap.org/search?format=jsonv2&addressdetails=1&limit=8&accept-language=id&q=${encodeURIComponent(q)}`, {signal})
            .then(rr=>rr.json()).then(list=>{ renderNominatim(list||[]); autoCenterNominatim(list||[]); });
        }
      }).catch(()=>{}).finally(()=>{ ctrl=null; });
  };

  const debounced = debounce(q=>{
    q = (q || '').trim();
    if(!q || q.length < 2){ suggestBox.classList.add('hidden'); suggestBox.innerHTML=''; return; }
    runSearch(q);
  }, 400);

  searchBox.addEventListener('input', e => debounced(e.target.value));
  searchBox.addEventListener('keydown', e=>{
    if(e.key==='Enter'){ e.preventDefault(); const q=(searchBox.value||'').trim(); if(q) runSearch(q); }
  });

  document.addEventListener('click', (e)=>{
    if (!suggestBox.contains(e.target) && e.target!==searchBox) suggestBox.classList.add('hidden');
    const it = e.target.closest('.suggest-item');
    if (it){
      const lat = parseFloat(it.dataset.lat), lng = parseFloat(it.dataset.lng);
      const label = it.dataset.label || '';
      centerTo(lat, lng, label);
      suggestBox.classList.add('hidden');
    }
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
