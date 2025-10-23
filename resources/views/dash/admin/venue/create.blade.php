@extends('app')
@section('title', 'Admin Dashboard - Tambah Venue')

@push('styles')
<link
  rel="stylesheet"
  href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
  integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
  crossorigin="" />
<style>
  /* ====== Anti overscroll / white bounce ====== */
  :root {
    color-scheme: dark;
    --page-bg: #0a0a0a;
  }

  html,
  body {
    height: 100%;
    min-height: 100%;
    background: var(--page-bg);
    overscroll-behavior: none;
    touch-action: pan-y;
    -webkit-text-size-adjust: 100%;
  }

  #antiBounceBg {
    position: fixed;
    left: 0;
    right: 0;
    top: -120svh;
    bottom: -120svh;
    background: var(--page-bg);
    z-index: -1;
    pointer-events: none;
  }

  .scroll-safe {
    background-color: #171717;
    overscroll-behavior: contain;
    -webkit-overflow-scrolling: touch;
  }

  /* ==== MAP ==== */
  .leaflet-map {
    height: 300px;
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid #3a3a3a;
  }

  .geocode-wrap {
    position: relative;
  }

  .geocode-input {
    width: 100%;
    padding: .65rem .9rem;
    border-radius: 10px;
    background: #222;
    color: #fff;
    border: 1.5px solid rgba(255, 255, 255, .2);
    outline: none;
    font-size: 13px;
  }

  .geocode-input:focus {
    box-shadow: 0 0 0 2px #3b82f6;
    border-color: #3b82f6;
  }

  .suggest-box {
    position: absolute;
    z-index: 30;
    left: 0;
    right: 0;
    top: 100%;
    margin-top: 6px;
    background: #1c1c1c;
    border: 1px solid rgba(255, 255, 255, .15);
    border-radius: 10px;
    overflow: hidden;
    max-height: 220px;
    overflow-y: auto;
  }

  .suggest-item {
    padding: .55rem .7rem;
    font-size: 13px;
    color: #e5e7eb;
    cursor: pointer;
  }

  .suggest-item:hover {
    background: #2a2a2a;
  }
</style>
@endpush

@section('content')
<div id="antiBounceBg" aria-hidden="true"></div>

<div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
  <div class="flex flex-1 min-h-0">
    @include('partials.sidebar')
    <main class="flex-1 overflow-y-auto min-w-0 mb-8 scroll-safe">
      @include('partials.topbar')

      <div class="mt-20 sm:mt-0 px-4 sm:px-8">
        <h1 class="text-2xl sm:text-3xl font-extrabold my-6 sm:my-8">
          Tambah Venue Baru
        </h1>

        <form method="POST" action="{{ route('venue.store') }}" enctype="multipart/form-data" class="flex flex-col lg:flex-row lg:space-x-8">
          @csrf

          <section aria-labelledby="user-info-title"
            class="bg-[#262626] rounded-lg p-4 sm:p-8 flex-1 max-w-full lg:max-w-lg space-y-6 sm:space-y-8 mb-6 lg:mb-0">
            <h2 class="text-base sm:text-lg font-bold border-b border-gray-600 pb-2" id="user-info-title">
              Informasi Akun
            </h2>
            <div class="space-y-4 sm:space-y-6">
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
          </section>

          <section class="flex flex-col space-y-6 sm:space-y-8 w-full max-w-full lg:max-w-lg">
            <div aria-labelledby="venue-info-title" class="bg-[#262626] rounded-lg p-4 sm:p-6 space-y-4">
              <h2 class="text-base sm:text-lg font-bold border-b border-gray-600 pb-2" id="venue-info-title">
                Informasi Venue
              </h2>

              <div class="space-y-4">
                <div>
                  <label class="block text-xs text-gray-400 mb-1" for="venue_name">Nama Venue</label>
                  <input name="venue_name" value="{{ old('venue_name') }}"
                    class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                    id="venue_name" type="text" placeholder="Masukkan nama venue" />
                  @error('venue_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Image --}}
                <div>
                  <label class="block text-xs text-gray-400 mb-1" for="images">Gambar Venue (maks. 3)</label>
                  <input name="images[]" id="images" type="file" multiple accept="image/*" class="hidden">
                  <div id="imagePreview" class="mt-3 grid grid-cols-3 gap-3">
                    <div class="h-24 border-2 border-dashed border-gray-500 rounded-md flex items-center justify-center text-gray-400 text-xs col-span-3 sm:col-span-3 md:col-span-3 hover:border-blue-500 hover:text-blue-400 transition cursor-pointer"
                      onclick="document.getElementById('images').click()">
                      Klik untuk pilih hingga 3 gambar
                    </div>
                  </div>

                  @error('images.*')
                  <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                  @enderror
                </div>

                {{-- ADDRESS + MAP (Leaflet) --}}
                <div class="space-y-3">
                  <label class="block text-xs text-gray-400">Lokasi Venue</label>

                  <div class="geocode-wrap">
                    <input id="searchBox" type="text" class="geocode-input" placeholder="Cari alamat / tempat (gratis, OSM)">
                    <div id="suggestions" class="suggest-box hidden"></div>
                  </div>

                  <textarea name="address" id="address" rows="3"
                    class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white resize-none focus:outline-none focus:ring-1 focus:ring-blue-500"
                    placeholder="Alamat venue (akan terisi otomatis saat pilih pin/hasil pencarian)">{{ old('address') }}</textarea>
                  @error('address') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror

                  <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude') }}">
                  <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude') }}">

                  <div id="mapCreate" class="leaflet-map"></div>
                  <p class="text-xs text-gray-400">Tip: klik peta untuk menetapkan pin, atau tarik pin untuk pindah. Alamat akan diisi otomatis.</p>
                </div>

                <div>
                  <label class="block text-xs text-gray-400 mb-1" for="phone">Nomor Telepon</label>
                  <input name="phone" value="{{ old('phone') }}"
                    class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                    id="phone" type="text" placeholder="Masukkan nomor telepon" />
                  @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                  <label class="block text-xs text-gray-400 mb-1">
                    Jam Operasional
                  </label>
                  <div class="flex gap-3">
                    <div class="flex-1">
                      <label class="block text-xs text-gray-400 mb-1" for="operating_hour">Jam Buka</label>
                      <input name="operating_hour" value="{{ old('operating_hour') }}"
                        class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                        id="operating_hour" type="time" placeholder="08:00" />
                      @error('operating_hour')
                      <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                      @enderror
                    </div>
                    <div class="flex-1">
                      <label class="block text-xs text-gray-400 mb-1" for="closing_hour">Jam Tutup</label>
                      <input name="closing_hour" value="{{ old('closing_hour') }}"
                        class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                        id="closing_hour" type="time" placeholder="17:00" />
                      @error('closing_hour')
                      <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                      @enderror
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

            <div class="flex flex-col sm:flex-row gap-3 sm:gap-0 sm:space-x-4 sm:justify-end">
              <a href="{{ route('venue.index') }}"
                class="w-full sm:w-auto px-6 py-2 border border-red-600 text-red-600 rounded-md hover:bg-red-600 hover:text-white transition text-center text-sm order-2 sm:order-1">
                Batal
              </a>
              <button class="w-full sm:w-auto px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition text-sm order-1 sm:order-2" type="submit">
                Simpan Venue
              </button>
            </div>
          </section>
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
  // ====== LEAFLET + PHOTON (search) + NOMINATIM (reverse) ======
  (function() {
    const DFLT = {
      lat: -6.175392,
      lng: 106.827153
    }; // Monas (default Jakarta)
    const map = L.map('mapCreate', {
      zoomControl: true,
      scrollWheelZoom: true
    }).setView([DFLT.lat, DFLT.lng], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    const latEl = document.getElementById('latitude');
    const lngEl = document.getElementById('longitude');
    const addrEl = document.getElementById('address');
    const searchBox = document.getElementById('searchBox');
    const suggestBox = document.getElementById('suggestions');

    // initial position if old() exists
    const initLat = parseFloat(latEl.value || '');
    const initLng = parseFloat(lngEl.value || '');
    const hasInit = !isNaN(initLat) && !isNaN(initLng);

    let marker = L.marker(hasInit ? [initLat, initLng] : [DFLT.lat, DFLT.lng], {
      draggable: true
    }).addTo(map);
    if (hasInit) map.setView([initLat, initLng], 15);

    // update hidden + reverse geocode
    function updatePosition(lat, lng, doReverse = true) {
      latEl.value = lat.toFixed(7);
      lngEl.value = lng.toFixed(7);
      if (doReverse) {
        fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`)
          .then(r => r.json())
          .then(j => {
            if (j && j.display_name && addrEl) addrEl.value = j.display_name;
          })
          .catch(() => {});
      }
    }
    updatePosition(marker.getLatLng().lat, marker.getLatLng().lng, !hasInit);

    marker.on('dragend', () => {
      const {
        lat,
        lng
      } = marker.getLatLng();
      updatePosition(lat, lng, true);
    });

    map.on('click', (e) => {
      const {
        lat,
        lng
      } = e.latlng;
      marker.setLatLng([lat, lng]);
      updatePosition(lat, lng, true);
    });

    // Simple debounce
    let tmr;

    function debounce(fn, ms) {
      return (...args) => {
        clearTimeout(tmr);
        tmr = setTimeout(() => fn(...args), ms);
      };
    }

    // Search with Photon (Komoot)
    const runSearch = debounce(() => {
      const q = (searchBox.value || '').trim();
      if (!q) {
        suggestBox.classList.add('hidden');
        suggestBox.innerHTML = '';
        return;
      }
      fetch(`https://photon.komoot.io/api/?q=${encodeURIComponent(q)}&lang=id`)
        .then(r => r.json())
        .then(j => {
          const feats = j.features || [];
          if (!feats.length) {
            suggestBox.classList.add('hidden');
            suggestBox.innerHTML = '';
            return;
          }
          suggestBox.innerHTML = feats.slice(0, 8).map(f => {
            const name = f.properties.name || '';
            const city = f.properties.city || f.properties.county || f.properties.state || '';
            const country = f.properties.country || '';
            const label = [name, city, country].filter(Boolean).join(', ');
            const c = f.geometry.coordinates; // [lng, lat]
            return `<div class="suggest-item" data-lat="${c[1]}" data-lng="${c[0]}">${label}</div>`;
          }).join('');
          suggestBox.classList.remove('hidden');
        })
        .catch(() => {
          suggestBox.classList.add('hidden');
          suggestBox.innerHTML = '';
        });
    }, 350);

    searchBox.addEventListener('input', runSearch);
    document.addEventListener('click', (e) => {
      if (!suggestBox.contains(e.target) && e.target !== searchBox) {
        suggestBox.classList.add('hidden');
      }
    });
    suggestBox.addEventListener('click', (e) => {
      const item = e.target.closest('.suggest-item');
      if (!item) return;
      const lat = parseFloat(item.dataset.lat);
      const lng = parseFloat(item.dataset.lng);
      marker.setLatLng([lat, lng]);
      map.setView([lat, lng], 16);
      updatePosition(lat, lng, true);
      suggestBox.classList.add('hidden');
    });
  })();
</script>

<script>
  const fileInput = document.getElementById('images');
  const previewContainer = document.getElementById('imagePreview');
  let selectedFiles = [];

  fileInput.addEventListener('change', function(e) {
    const newFiles = Array.from(e.target.files).map(file => {
      // Tambahkan timestamp untuk membuat nama unik
      const uniqueName = Date.now() + '-' + file.name;
      return new File([file], uniqueName, {
        type: file.type
      });
    });

    // Gabungkan file baru dengan yang lama, maksimal 3
    selectedFiles = [...selectedFiles, ...newFiles].slice(0, 3);

    updatePreview();
    updateFileInput();
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

        // Tombol hapus
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.textContent = '×';
        removeBtn.className = 'absolute top-1 right-1 bg-red-600 text-white rounded-full w-5 h-5 text-xs opacity-0 group-hover:opacity-100 transition-opacity';
        removeBtn.onclick = (e) => {
          e.stopPropagation();
          removeFile(index);
        };

        wrapper.appendChild(img);
        wrapper.appendChild(removeBtn);
        previewContainer.appendChild(wrapper);
      };
      reader.readAsDataURL(file);
    });

    // Placeholder untuk slot kosong
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
    updatePreview();
    updateFileInput();
  }

  function updateFileInput() {
    const dataTransfer = new DataTransfer();
    selectedFiles.forEach(file => dataTransfer.items.add(file));
    fileInput.files = dataTransfer.files;
  }
</script>
@endpush