@extends('app')
@section('title', 'Admin Dashboard - Tambah Venue')

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

    /* tinggi + z-index topbar (header fixed) */
    --topbar-h: 64px;
    --topbar-z: 90;
  }

  html, body{
    height:100%; min-height:100%; background:var(--page-bg);
    overscroll-behavior: none; touch-action: pan-y; -webkit-text-size-adjust:100%;
  }

  #antiBounceBg{
    position: fixed; left:0; right:0; top:-120svh; bottom:-120svh;
    background:var(--page-bg); z-index:-1; pointer-events:none;
  }

  .scroll-safe{ background-color:#171717; overscroll-behavior: contain; -webkit-overflow-scrolling: touch; }

  /* Pastikan topbar (header fixed) selalu di atas */
  header.fixed, header[class*="fixed"]{ z-index: var(--topbar-z) !important; }

  /* Konten tidak nabrak topbar */
  .has-fixed-topbar{ padding-top: var(--topbar-h); }

  /* ==== MAP & komponen terkait jangan menutupi topbar ==== */
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
    position:absolute; z-index:30; left:0; right:0; top:100%; margin-top:6px;
    background:#1c1c1c; border:1px solid rgba(255,255,255,.15); border-radius:10px; overflow:hidden;
    max-height:220px; overflow-y:auto;
  }
  .suggest-item{ padding:.55rem .7rem; font-size:13px; color:#e5e7eb; cursor:pointer; }
  .suggest-item:hover{ background:#2a2a2a; }

  /* kecilkan z-index dropdown agar tetap di bawah topbar */
  .suggest-box{ z-index: 30; } /* < var(--topbar-z) */
</style>
@endpush

@section('content')
  <div id="antiBounceBg" aria-hidden="true"></div>

  <div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
    <div class="flex flex-1 min-h-0">
      @include('partials.sidebar')

      {{-- gunakan has-fixed-topbar agar semua konten berada di belakang topbar saat scroll --}}
      <main class="flex-1 overflow-y-auto min-w-0 mb-8 scroll-safe has-fixed-topbar">
        @include('partials.topbar')

        <div class="px-4 sm:px-8">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
            <h1 class="text-2xl sm:text-3xl font-extrabold">Tambah Venue Baru</h1>
            <a href="{{ route('venue.index') }}" class="flex items-center gap-2 text-blue-400 hover:text-blue-300 text-sm">
              <i class="fas fa-arrow-left"></i><span>Kembali ke daftar venue</span>
            </a>
          </div>

          {{-- Grid 2 kolom (seperti halaman Edit) --}}
          <form method="POST" action="{{ route('venue.store') }}" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-2 gap-6 sm:gap-8">
            @csrf

            {{-- Kolom Kiri: Informasi Akun --}}
            <div class="space-y-6">
              <div class="bg-[#262626] rounded-lg p-4 sm:p-6 space-y-4">
                <h2 class="text-base sm:text-lg font-bold border-b border-gray-600 pb-2 flex items-center">
                  <i class="fas fa-user-circle mr-2 text-blue-400"></i> Informasi Akun
                </h2>

                <div class="space-y-4">
                  <div>
                    <label class="block text-xs text-gray-400 mb-1" for="name">Nama Pengelola</label>
                    <input name="name" value="{{ old('name') }}"
                      class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                      id="name" type="text" placeholder="Masukkan nama pengelola venue" />
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                  </div>

                  <div>
                    <label class="block text-xs text-gray-400 mb-1" for="email">Email</label>
                    <input name="email" value="{{ old('email') }}"
                      class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                      id="email" type="email" placeholder="Masukkan email" />
                    @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                  </div>

                  <div>
                    <label class="block text-xs text-gray-400 mb-1" for="password">Password</label>
                    <input name="password"
                      class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                      id="password" type="password" placeholder="Masukkan password" />
                    @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                  </div>
                </div>
              </div>
            </div>

            {{-- Kolom Kanan: Informasi Venue (bagian gambar tetap create) --}}
            <div class="space-y-6">
              <div class="bg-[#262626] rounded-lg p-4 sm:p-6 space-y-4">
                <h2 class="text-base sm:text-lg font-bold border-b border-gray-600 pb-2 flex items-center">
                  <i class="fas fa-store mr-2 text-green-400"></i> Informasi Venue
                </h2>

                <div class="space-y-4">
                  <div>
                    <label class="block text-xs text-gray-400 mb-1" for="venue_name">Nama Venue</label>
                    <input name="venue_name" value="{{ old('venue_name') }}"
                      class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                      id="venue_name" type="text" placeholder="Masukkan nama venue" />
                    @error('venue_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                  </div>

                  {{-- === Gambar Venue (MAKS 3) — TETAP === --}}
                  <div>
                    <label class="block text-xs text-gray-400 mb-1" for="images">Gambar Venue (maks. 3)</label>
                    <input name="images[]" id="images" type="file" multiple accept="image/*" class="hidden">
                    <div id="imagePreview" class="mt-3 grid grid-cols-3 gap-3">
                      <div class="h-24 border-2 border-dashed border-gray-500 rounded-md flex items-center justify-center text-gray-400 text-xs col-span-3 sm:col-span-3 md:col-span-3 hover:border-blue-500 hover:text-blue-400 transition cursor-pointer"
                        onclick="document.getElementById('images').click()">
                        Klik untuk pilih hingga 3 gambar
                      </div>
                    </div>
                    @error('images.*') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                  </div>

                  {{-- Lokasi + Map (Auto-center saat mengetik) --}}
                  <div class="space-y-3">
                    <label class="block text-xs text-gray-400">Lokasi Venue</label>

                    <div class="geocode-wrap">
                      <input id="searchBox" type="text" class="geocode-input" placeholder="Cari alamat / tempat (gratis, OSM)">
                      <div id="suggestions" class="suggest-box hidden"></div>
                    </div>

                    <textarea name="address" id="address" rows="3"
                      class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white resize-none focus:outline-none focus:ring-1 focus:ring-blue-500"
                      placeholder="Alamat venue (terisi otomatis)">{{ old('address') }}</textarea>
                    @error('address') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror

                    <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude') }}">
                    <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude') }}">

                    <div id="mapCreate" class="leaflet-map"></div>
                    <p class="text-xs text-gray-400">Ketik alamat (contoh: “Jalan Gambir” atau “RW 02, Gambir, Jakarta”). Peta akan otomatis mengarah.</p>
                  </div>

                  <div>
                    <label class="block text-xs text-gray-400 mb-1" for="phone">Nomor Telepon</label>
                    <input name="phone" value="{{ old('phone') }}"
                      class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                      id="phone" type="text" placeholder="Masukkan nomor telepon" />
                    @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                  </div>

                  <div>
                    <label class="block text-xs text-gray-400 mb-1">Jam Operasional</label>
                    <div class="flex gap-3">
                      <div class="flex-1">
                        <label class="block text-xs text-gray-400 mb-1" for="operating_hour">Jam Buka</label>
                        <input name="operating_hour" value="{{ old('operating_hour') }}"
                          class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                          id="operating_hour" type="time" placeholder="08:00" />
                        @error('operating_hour') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                      </div>
                      <div class="flex-1">
                        <label class="block text-xs text-gray-400 mb-1" for="closing_hour">Jam Tutup</label>
                        <input name="closing_hour" value="{{ old('closing_hour') }}"
                          class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                          id="closing_hour" type="time" placeholder="17:00" />
                        @error('closing_hour') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                      </div>
                    </div>
                  </div>

                  <div>
                    <label class="block text-xs text-gray-400 mb-1" for="description">Deskripsi</label>
                    <textarea name="description" id="description" rows="4"
                      class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white resize-none focus:outline-none focus:ring-1 focus:ring-blue-500"
                      placeholder="Masukkan deskripsi venue">{{ old('description') }}</textarea>
                    @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                  </div>
                </div>
              </div>
            </div>

            {{-- Tombol aksi --}}
            <div class="lg:col-span-2 flex flex-col sm:flex-row justify-end mt-2 sm:mt-0 gap-3 sm:gap-0 sm:space-x-4">
              <a href="{{ route('venue.index') }}"
                class="w-full sm:w-auto px-6 py-2 border border-gray-600 text-gray-300 rounded-md hover:bg-gray-700 transition text-center text-sm order-2 sm:order-1">
                Batal
              </a>
              <button type="submit"
                class="w-full sm:w-auto px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition text-sm order-1 sm:order-2">
                Simpan Venue
              </button>
            </div>
          </form>
        </div>
      </main>
    </div>
  </div>
@endsection

@push('scripts')
<script
  src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
  integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
  crossorigin=""></script>
<script>
/**
 * Geocoding yang auto-center saat user MENGETIK.
 * - Utama: Photon (Komoot) + bias ke posisi saat ini
 * - Fallback: Nominatim (OSM)
 * - Auto-center ke hasil pertama, update hidden lat/lng + textarea alamat
 * - Dropdown suggestion tetap bisa diklik manual
 */
(function() {
  const DFLT = { lat: -6.175392, lng: 106.827153 }; // Monas
  const map = L.map('mapCreate', { zoomControl: true, scrollWheelZoom: true }).setView([DFLT.lat, DFLT.lng], 13);

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19, attribution: '&copy; OpenStreetMap'
  }).addTo(map);

  const latEl = document.getElementById('latitude');
  const lngEl = document.getElementById('longitude');
  const addrEl = document.getElementById('address');
  const searchBox = document.getElementById('searchBox');
  const suggestBox = document.getElementById('suggestions');

  const initLat = parseFloat(latEl.value || '');
  const initLng = parseFloat(lngEl.value || '');
  const hasInit = !isNaN(initLat) && !isNaN(initLng);

  let marker = L.marker(hasInit ? [initLat, initLng] : [DFLT.lat, DFLT.lng], { draggable: true }).addTo(map);
  if (hasInit) map.setView([initLat, initLng], 15);

  function updateLatLngFields(lat, lng){
    latEl.value = lat.toFixed(7);
    lngEl.value = lng.toFixed(7);
  }

  function reverseToAddress(lat, lng){
    fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}&accept-language=id`)
      .then(r => r.json())
      .then(j => {
        if (j && j.display_name && addrEl) addrEl.value = j.display_name;
      })
      .catch(()=>{});
  }

  function centerTo(lat, lng, label = null, zoom = 16){
    marker.setLatLng([lat, lng]);
    map.setView([lat, lng], zoom);
    updateLatLngFields(lat, lng);
    if (label) addrEl.value = label;
    // tetap reverse agar alamat lengkap
    reverseToAddress(lat, lng);
  }

  // Buat label dari Photon feature
  function labelFromPhoton(f){
    const p = f.properties || {};
    const name = p.name || '';
    const street = p.street || '';
    const city = p.city || p.district || p.county || p.state || '';
    const country = p.country || '';
    return [name, street, city, country].filter(Boolean).join(', ');
  }

  // Buat label dari Nominatim result
  function labelFromNominatim(obj){
    if (!obj) return '';
    const name = obj.display_name || '';
    return name;
  }

  // Render suggestion list (Photon-style objek)
  function renderSuggestionsFromPhoton(features){
    if (!features || !features.length){
      suggestBox.classList.add('hidden');
      suggestBox.innerHTML = '';
      return;
    }
    suggestBox.innerHTML = features.slice(0, 8).map(f => {
      const label = labelFromPhoton(f);
      const c = f.geometry.coordinates; // [lng, lat]
      return `<div class="suggest-item" data-lat="${c[1]}" data-lng="${c[0]}" data-label="${(label||'').replace(/"/g,'&quot;')}">${label}</div>`;
    }).join('');
    suggestBox.classList.remove('hidden');
  }

  // Render suggestion list (Nominatim-style array)
  function renderSuggestionsFromNominatim(list){
    if (!list || !list.length){
      suggestBox.classList.add('hidden');
      suggestBox.innerHTML = '';
      return;
    }
    suggestBox.innerHTML = list.slice(0, 8).map(it => {
      const label = labelFromNominatim(it);
      return `<div class="suggest-item" data-lat="${it.lat}" data-lng="${it.lon}" data-label="${(label||'').replace(/"/g,'&quot;')}">${label}</div>`;
    }).join('');
    suggestBox.classList.remove('hidden');
  }

  // Auto-center ke hasil pertama (Photon/Nominatim)
  function autoCenterFromPhoton(features){
    if (!features || !features.length) return false;
    const first = features[0];
    const [lng, lat] = first.geometry.coordinates;
    const label = labelFromPhoton(first) || (searchBox.value || '').trim();
    centerTo(lat, lng, label, 16);
    return true;
  }
  function autoCenterFromNominatim(list){
    if (!list || !list.length) return false;
    const first = list[0];
    const lat = parseFloat(first.lat), lng = parseFloat(first.lon);
    const label = labelFromNominatim(first) || (searchBox.value || '').trim();
    centerTo(lat, lng, label, 16);
    return true;
  }

  // Drag & click map
  marker.on('dragend', () => {
    const { lat, lng } = marker.getLatLng();
    updateLatLngFields(lat, lng);
    reverseToAddress(lat, lng);
  });
  map.on('click', (e) => {
    const { lat, lng } = e.latlng;
    centerTo(lat, lng, null, 16);
  });

  // ======= Pencarian otomatis saat KETIK =======
  let tmr;
  let currentController = null;
  const MIN_CHARS = 2;

  function abortLast(){
    if (currentController){
      try{ currentController.abort(); }catch(e){}
      currentController = null;
    }
  }

  // Cari via Photon, fallback ke Nominatim
  function queryAndCenter(q){
    abortLast();
    currentController = new AbortController();
    const signal = currentController.signal;

    // Bias ke posisi marker saat ini agar hasil lebih relevan
    const bias = marker.getLatLng ? marker.getLatLng() : {lat: DFLT.lat, lng: DFLT.lng};

    // 1) Photon
    const photonUrl =
      `https://photon.komoot.io/api/?q=${encodeURIComponent(q)}&lang=id&lat=${bias.lat}&lon=${bias.lng}&limit=8`;
    fetch(photonUrl, { signal })
      .then(r => r.json())
      .then(j => {
        const feats = (j && j.features) ? j.features : [];
        if (feats.length){
          renderSuggestionsFromPhoton(feats);
          autoCenterFromPhoton(feats);
        } else {
          // 2) Fallback: Nominatim
          const nomUrl = `https://nominatim.openstreetmap.org/search?format=jsonv2&addressdetails=1&limit=8&accept-language=id&q=${encodeURIComponent(q)}`;
          return fetch(nomUrl, { signal })
            .then(rr => rr.json())
            .then(list => {
              renderSuggestionsFromNominatim(list || []);
              autoCenterFromNominatim(list || []);
            });
        }
      })
      .catch(()=>{/* dilewati saat abort / error */})
      .finally(()=>{ currentController = null; });
  }

  const runLiveSearch = (q) => {
    if (q.length < MIN_CHARS){
      suggestBox.classList.add('hidden');
      suggestBox.innerHTML = '';
      return;
    }
    queryAndCenter(q);
  };

  const debounce = (fn, ms) => {
    return (...args) => {
      clearTimeout(tmr);
      tmr = setTimeout(() => fn(...args), ms);
    };
  };

  const debouncedSearch = debounce(runLiveSearch, 400);

  searchBox.addEventListener('input', (e) => {
    const q = (e.target.value || '').trim();
    debouncedSearch(q);
  });

  // Enter = cari langsung
  searchBox.addEventListener('keydown', (e) => {
    if (e.key === 'Enter'){
      e.preventDefault();
      const q = (searchBox.value || '').trim();
      if (!q) return;
      runLiveSearch(q);
    }
  });

  // Klik suggestion
  document.addEventListener('click', (e) => {
    if (!suggestBox.contains(e.target) && e.target !== searchBox) {
      suggestBox.classList.add('hidden');
    }
    const item = e.target.closest('.suggest-item');
    if (item){
      const lat = parseFloat(item.dataset.lat);
      const lng = parseFloat(item.dataset.lng);
      const label = item.dataset.label || '';
      centerTo(lat, lng, label, 16);
      suggestBox.classList.add('hidden');
    }
  });

  // Set nilai awal hidden fields
  const start = marker.getLatLng();
  updateLatLngFields(start.lat, start.lng);
  if (!hasInit) reverseToAddress(start.lat, start.lng);
})();
</script>

<script>
  // === Preview upload (maks 3) — TETAP seperti create ===
  const fileInput = document.getElementById('images');
  const previewContainer = document.getElementById('imagePreview');
  let selectedFiles = [];

  fileInput.addEventListener('change', function(e) {
    const newFiles = Array.from(e.target.files).map(file => {
      const uniqueName = Date.now() + '-' + file.name; // nama unik
      return new File([file], uniqueName, { type: file.type });
    });
    selectedFiles = [...selectedFiles, ...newFiles].slice(0, 3);
    updatePreview(); updateFileInput();
  });

  function updatePreview() {
    previewContainer.innerHTML = '';
    selectedFiles.forEach((file, index) => {
      if (!file.type.startsWith('image/')) return;
      const reader = new FileReader();
      reader.onload = event => {
        const wrapper = document.createElement('div');
        wrapper.className = 'relative group';
        const img = document.createElement('img');
        img.src = event.target.result;
        img.className = 'w-full h-24 object-cover rounded-md border border-gray-600';
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.textContent = '×';
        removeBtn.className = 'absolute top-1 right-1 bg-red-600 text-white rounded-full w-5 h-5 text-xs opacity-0 group-hover:opacity-100 transition-opacity';
        removeBtn.onclick = (e) => { e.stopPropagation(); removeFile(index); };
        wrapper.appendChild(img); wrapper.appendChild(removeBtn);
        previewContainer.appendChild(wrapper);
      };
      reader.readAsDataURL(file);
    });
    const emptySlots = 3 - selectedFiles.length;
    for (let i = 0; i < emptySlots; i++) {
      const placeholder = document.createElement('div');
      placeholder.className = 'h-24 border-2 border-dashed border-gray-500 rounded-md flex items-center justify-center text-gray-400 text-xs cursor-pointer hover:border-blue-500 hover:text-blue-400 transition';
      placeholder.textContent = 'Klik untuk pilih';
      placeholder.onclick = () => fileInput.click();
      previewContainer.appendChild(placeholder);
    }
  }

  function removeFile(index) {
    selectedFiles.splice(index, 1);
    updatePreview(); updateFileInput();
  }

  function updateFileInput() {
    const dataTransfer = new DataTransfer();
    selectedFiles.forEach(file => dataTransfer.items.add(file));
    fileInput.files = dataTransfer.files;
  }
</script>
@endpush
