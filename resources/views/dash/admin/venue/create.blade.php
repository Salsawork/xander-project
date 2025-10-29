@extends('app')
@section('title', 'Admin Dashboard - Tambah Venue')

@push('styles')
<style>
  :root{
    color-scheme: dark;
    --page-bg:#0a0a0a;
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
  header.fixed, header[class*="fixed"]{ z-index: var(--topbar-z) !important; }
  .has-fixed-topbar{ padding-top: var(--topbar-h); }

  .geocode-input{
    width:100%; padding:.65rem .9rem; border-radius:10px; background:#222; color:#fff;
    border:1.5px solid rgba(255,255,255,.2); outline:none; font-size:13px;
  }
  .geocode-input:focus{ box-shadow:0 0 0 2px #3b82f6; border-color:#3b82f6; }

  .iframe-wrap{ position: relative; width:100%; border:1px solid #3a3a3a; border-radius:12px; overflow:hidden; background:#111; }
  .iframe-wrap::before{content:"";display:block;padding-top:56.25%;} /* 16:9 */
  .iframe-wrap iframe{ position:absolute; inset:0; width:100%; height:100%; border:0; }

  /* Chips Facilities */
  .chip{ display:inline-flex; align-items:center; gap:.5rem; background:#2a2a2a; border:1px solid #3f3f3f; padding:.25rem .6rem; border-radius:9999px; font-size:.8rem; }
  .chip button{ background:transparent; border:0; color:#fca5a5; cursor:pointer; font-weight:700; }
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
            <h1 class="text-2xl sm:text-3xl font-extrabold">Tambah Venue Baru</h1>
            <a href="{{ route('venue.index') }}" class="flex items-center gap-2 text-blue-400 hover:text-blue-300 text-sm">
              <i class="fas fa-arrow-left"></i><span>Kembali ke daftar venue</span>
            </a>
          </div>

          <form method="POST" action="{{ route('venue.store') }}" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-2 gap-6 sm:gap-8" id="venueCreateForm">
            @csrf

            {{-- Kolom Kiri --}}
            <div class="space-y-6">
              {{-- Informasi Akun --}}
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

              {{-- Facilities (card baru) --}}
              <div class="bg-[#262626] rounded-lg p-4 sm:p-6 space-y-4">
                <h2 class="text-base sm:text-lg font-bold border-b border-gray-600 pb-2 flex items-center">
                  <i class="fas fa-list-check mr-2 text-emerald-400"></i> Facilities
                </h2>

                <div class="space-y-2">
                  <label class="block text-xs text-gray-400 mb-1">Tambah Fasilitas</label>
                  <div class="flex gap-2">
                    <input id="facilityInput" type="text"
                      class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                      placeholder="Contoh: VIP Lounge" />
                    <button type="button" id="addFacilityBtn"
                      class="px-3 py-2 bg-emerald-600 hover:bg-emerald-700 rounded-md text-sm">Tambah</button>
                  </div>
                  <p class="text-xs text-gray-400">Tekan <strong>Enter</strong> untuk menambah. Maks 50 item, masing-masing 100 karakter.</p>

                  <div id="facilitiesChips" class="mt-3 flex flex-wrap gap-2">
                    {{-- chips muncul di sini --}}
                  </div>

                  {{-- Hidden inputs untuk facilities[] --}}
                  <div id="facilitiesHidden"></div>

                  @error('facilities') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                  @error('facilities.*') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
              </div>
            </div>

            {{-- Kolom Kanan --}}
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

                  {{-- Gambar (maks 3) --}}
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

                  {{-- Alamat (auto-isi dari embed) --}}
                  <div class="space-y-3">
                    <label class="block text-xs text-gray-400">Alamat Venue</label>
                    <textarea name="address" id="address" rows="3"
                      class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white resize-none focus:outline-none focus:ring-1 focus:ring-blue-500"
                      placeholder="Alamat venue (akan terisi otomatis dari embed jika tersedia)">{{ old('address') }}</textarea>
                    @error('address') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                  </div>

                  {{-- Google Maps Embed (iframe / URL) --}}
                  <div class="space-y-2">
                    <label class="block text-xs text-gray-400" for="map_embed">Google Maps Embed (paste iframe atau URL)</label>
                    <textarea name="map_embed" id="map_embed" rows="3" placeholder='Tempel: <iframe src="https://www.google.com/maps/embed?..."></iframe> atau langsung URL "https://www.google.com/maps/place/..."'
                      class="geocode-input">{{ old('map_embed') }}</textarea>
                    @error('map_embed') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror

                    {{-- NOTE: ditambahkan sesuai permintaan --}}
                    <div class="mt-2 rounded-md border border-blue-500/40 bg-blue-500/10 text-blue-100 text-xs p-3 leading-relaxed">
                      <p class="font-semibold mb-1">Panduan cepat Google Maps Embed</p>
                      <ol class="list-decimal pl-4 space-y-1">
                        <li>Buka <span class="font-semibold">Google Maps</span> dan cari lokasi venue.</li>
                        <li>Pilih <span class="font-semibold">Bagikan</span> → <span class="font-semibold">Sematkan peta</span> lalu klik <span class="font-semibold">Salin HTML</span> (kode <code>&lt;iframe&gt;</code>), <em>atau</em> salin URL dengan pola <code>/maps/place/…</code> atau <code>?q=…</code>.</li>
                        <li>Tempelkan di kolom ini. Jika format valid, <strong>preview akan muncul</strong> dan kolom <strong>Alamat Venue</strong> otomatis terisi.</li>
                      </ol>
                      <p class="mt-2 opacity-80">Format yang diterima:</p>
                      <ul class="list-disc pl-5 space-y-1 mt-1">
                        <li><code>&lt;iframe src="https://www.google.com/maps/embed?pb=..."&gt;&lt;/iframe&gt;</code></li>
                        <li><code>https://www.google.com/maps/place/…</code></li>
                        <li><code>https://www.google.com/maps?q=…</code></li>
                      </ul>
                    </div>
                    {{-- END NOTE --}}

                    <div id="mapPreview" class="iframe-wrap mt-2" style="display:none;">
                      <iframe id="mapPreviewIframe" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen=""></iframe>
                    </div>
                    <p class="text-xs text-gray-400">Alamat akan terisi otomatis jika link valid (q=..., /place/..., atau embed pb=...).</p>
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
<script>
  // ======= Preview upload (maks 3) =======
  const fileInput = document.getElementById('images');
  const previewContainer = document.getElementById('imagePreview');
  let selectedFiles = [];

  if (fileInput) {
    fileInput.addEventListener('change', function(e) {
      const newFiles = Array.from(e.target.files).map(file => {
        const uniqueName = Date.now() + '-' + file.name;
        return new File([file], uniqueName, { type: file.type });
      });
      selectedFiles = [...selectedFiles, ...newFiles].slice(0, 3);
      updatePreview(); updateFileInput();
    });
  }

  function updatePreview() {
    if (!previewContainer) return;
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
    if (fileInput) fileInput.files = dataTransfer.files;
  }

  // ======= Preview Google Maps Embed + Auto Address (pb-aware) =======
  const embedEl      = document.getElementById('map_embed');
  const previewWrap  = document.getElementById('mapPreview');
  const previewIframe= document.getElementById('mapPreviewIframe');
  const addressEl    = document.getElementById('address');

  function extractSrc(val){
    if (!val) return null;
    val = val.trim();
    if (val.toLowerCase().startsWith('<iframe')) {
      const m = val.match(/src\s*=\s*"(.*?)"/i) || val.match(/src\s*=\s*'(.*?)'/i);
      return m ? m[1] : null;
    }
    return val;
  }

  // Sama seperti di halaman edit: dukung ?q=, /maps/place/, dan embed pb=!2s...
  function parseAddressFromSrc(src){
    if (!src) return '';
    try{
      const u = new URL(src);
      // 1) ?q=
      const q = u.searchParams.get('q');
      if (q) return decodeURIComponent(q.replace(/\+/g,' '));

      // 2) /maps/place/<nama>
      const mPlace = u.pathname.match(/\/maps\/place\/([^/]+)/i);
      if (mPlace && mPlace[1]) return decodeURIComponent(mPlace[1].replace(/\+/g,' '));

      // 3) embed pb param: ambil !2s token
      const pb = u.searchParams.get('pb');
      if (pb) {
        let pbDec = decodeURIComponent(pb);
        try { pbDec = decodeURIComponent(pbDec); } catch(e){}
        const matches = [...pbDec.matchAll(/!2s([^!]+)/g)].map(x=>x[1]);
        const cleaned = matches.map(t=>{
          try{ t = decodeURIComponent(t); }catch(e){}
          t = t.replace(/\+/g,' ').replace(/\\u0026/gi,'&').trim();
          return t;
        }).filter(t => t.length >= 5 && /[A-Za-z]/.test(t) && !/^[a-z]{2}(-[A-Z]{2})?$/.test(t) && !/^(Google|Maps|Street View)$/i.test(t));
        if (cleaned.length){
          cleaned.sort((a,b)=>b.length-a.length);
          return cleaned[0];
        }
      }
    }catch(e){}
    // 4) fallback: title pada iframe full
    if (src.toLowerCase().startsWith('<iframe')) {
      const t = src.match(/title\s*=\s*"(.*?)"/i);
      if (t && t[1] && t[1].toLowerCase() !== 'google maps') return t[1];
    }
    return '';
  }

  function renderPreview(){
    const src = extractSrc(embedEl.value);
    if (src && /^https?:\/\//i.test(src)) {
      previewIframe.src = src;
      previewWrap.style.display = '';
    } else {
      previewIframe.removeAttribute('src');
      previewWrap.style.display = 'none';
    }

    const parsed = parseAddressFromSrc(src);
    if (parsed) {
      // Disamakan dgn edit: langsung set alamat saat parsed tersedia
      addressEl.value = parsed;
    }
  }

  if (embedEl) {
    embedEl.addEventListener('input', renderPreview);
    // Render awal untuk meng-handle old('map_embed')
    renderPreview();
  }

  // ======= Facilities (chips) =======
  const facInput  = document.getElementById('facilityInput');
  const facAddBtn = document.getElementById('addFacilityBtn');
  const facChips  = document.getElementById('facilitiesChips');
  const facHidden = document.getElementById('facilitiesHidden');
  const form      = document.getElementById('venueCreateForm');

  let facilities = [];

  // Preload dari old('facilities') jika ada
  @php
    $oldFacilities = old('facilities', []);
    if (!is_array($oldFacilities) && is_string($oldFacilities)) {
        $oldFacilities = preg_split('/[\r\n,]+/', $oldFacilities);
    }
    $oldFacilities = array_values(array_filter(array_map(function($s){
        return trim((string)$s);
    }, (array)$oldFacilities)));
  @endphp
  facilities = @json($oldFacilities);

  function renderFacilities(){
    facChips.innerHTML = '';
    facHidden.innerHTML = '';

    facilities.forEach((text, idx) => {
      const chip = document.createElement('span');
      chip.className = 'chip';
      chip.innerHTML = `<span>${text}</span> <button type="button" aria-label="hapus">&times;</button>`;
      chip.querySelector('button').addEventListener('click', () => {
        facilities.splice(idx, 1);
        renderFacilities();
      });
      facChips.appendChild(chip);

      const hidden = document.createElement('input');
      hidden.type = 'hidden';
      hidden.name = 'facilities[]';
      hidden.value = text;
      facHidden.appendChild(hidden);
    });
  }

  function addFacilityFromInput(){
    const v = (facInput.value || '').trim();
    if (!v) return;
    if (v.length > 100) {
      alert('Maksimal 100 karakter per item.');
      return;
    }
    if (facilities.length >= 50) {
      alert('Maksimal 50 fasilitas.');
      return;
    }
    if (!facilities.includes(v)) {
      facilities.push(v);
      renderFacilities();
    }
    facInput.value = '';
    facInput.focus();
  }

  if (facAddBtn) facAddBtn.addEventListener('click', addFacilityFromInput);
  if (facInput) facInput.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') {
      e.preventDefault();
      addFacilityFromInput();
    }
  });

  if (form) {
    form.addEventListener('submit', () => {
      // pastikan hidden inputs up-to-date
      renderFacilities();
    });
  }

  // Render awal
  renderFacilities();
</script>
@endpush
