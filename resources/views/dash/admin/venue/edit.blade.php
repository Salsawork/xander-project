@extends('app')
@section('title', 'Admin Dashboard - Edit Venue')

@push('styles')
<style>
  :root{ color-scheme: dark; --page-bg:#0a0a0a; --topbar-h:64px; --topbar-z:90; }
  html, body{ height:100%; min-height:100%; background:var(--page-bg); overscroll-behavior:none; touch-action:pan-y; -webkit-text-size-adjust:100%; }
  #antiBounceBg{ position:fixed; left:0; right:0; top:-120svh; bottom:-120svh; background:var(--page-bg); z-index:-1; pointer-events:none; }
  .scroll-safe{ background-color:#171717; overscroll-behavior:contain; -webkit-overflow-scrolling:touch; }
  header.fixed, header[class*="fixed"]{ z-index: var(--topbar-z) !important; }
  .has-fixed-topbar{ padding-top: var(--topbar-h); }
  .geocode-input{ width:100%; padding:.65rem .9rem; border-radius:10px; background:#222; color:#fff; border:1.5px solid rgba(255,255,255,.2); outline:none; font-size:13px; }
  .geocode-input:focus{ box-shadow:0 0 0 2px #3b82f6; border-color:#3b82f6; }
  .iframe-wrap{ position:relative; width:100%; border:1px solid #3a3a3a; border-radius:12px; overflow:hidden; background:#111; }
  .iframe-wrap::before{content:"";display:block;padding-top:56.25%;}
  .iframe-wrap iframe{ position:absolute; inset:0; width:100%; height:100%; border:0; }
  .chip{ display:inline-flex; align-items:center; gap:.5rem; background:#2a2a2a; border:1px solid #3f3f3f; padding:.25rem .6rem; border-radius:9999px; font-size:.8rem; }
  .chip button{ background:transparent; border:0; color:#fca5a5; cursor:pointer; font-weight:700; }

  .img-tile{ position:relative; width:80px; height:80px; }
  .img-tile img{ width:100%; height:100%; object-fit:cover; border-radius:8px; display:block; }
  .badge-replace{ display:inline-block; background:#2563eb; color:#fff; font-size:11px; padding:2px 6px; border-radius:999px; }
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

          {{-- ERROR VALIDASI --}}
          @if ($errors->any())
            <div class="mb-4 rounded-md bg-red-600/20 text-red-200 border border-red-600/40 px-4 py-3 text-sm">
              <strong class="block mb-1">Periksa kembali isian:</strong>
              <ul class="list-disc list-inside">
                @foreach ($errors->all() as $err)
                  <li>{{ $err }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          @php
            // Formatter HH:MM
            $toHi = function($val) {
              if (!$val) return '';
              if (is_object($val) && method_exists($val, 'format')) { return $val->format('H:i'); }
              $s = (string) $val;
              if (preg_match('/^\d{2}:\d{2}/', $s, $m)) return $m[0];
              $ts = strtotime($s);
              return $ts ? date('H:i', $ts) : '';
            };
            $openValue  = old('operating_hour', $toHi($venue->operating_hour));
            $closeValue = old('closing_hour',  $toHi($venue->closing_hour));
          @endphp

          <form id="editVenueForm" action="{{ route('venue.update', $venue->id) }}" method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 sm:gap-8">
              {{-- KIRI --}}
              <div class="space-y-6">
                <div class="bg-[#262626] rounded-lg p-4 sm:p-6 space-y-4">
                  <h2 class="text-base sm:text-lg font-bold border-b border-gray-600 pb-2 flex items-center">
                    <i class="fas fa-user-circle mr-2 text-blue-400"></i> Informasi Akun
                  </h2>

                  <div class="space-y-4">
                    <div>
                      <label class="block text-xs text-gray-400 mb-1" for="name">Nama Pengelola</label>
                      <input class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                        name="name" id="name" type="text" value="{{ old('name', $venue->user->name) }}" />
                      @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                      <label class="block text-xs text-gray-400 mb-1" for="email">Email</label>
                      <input class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                        name="email" id="email" type="email" value="{{ old('email', $venue->user->email) }}" />
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

                {{-- Facilities --}}
                @php
                  $facFromDb = [];
                  if (is_array($venue->facilities)) $facFromDb = $venue->facilities;
                  elseif (is_string($venue->facilities) && $venue->facilities !== '') {
                    $json = json_decode($venue->facilities, true);
                    $facFromDb = is_array($json) ? $json : preg_split('/[\r\n,]+/', $venue->facilities);
                  }
                  $facFromDb = array_values(array_filter(array_map(fn($s)=>trim((string)$s), $facFromDb)));
                  $facOld = old('facilities', $facFromDb);
                  if (!is_array($facOld) && is_string($facOld)) $facOld = preg_split('/[\r\n,]+/', $facOld);
                  $facOld = array_values(array_filter(array_map(fn($s)=>trim((string)$s),(array)$facOld)));
                @endphp

                <div class="bg-[#262626] rounded-lg p-4 sm:p-6 space-y-4">
                  <h2 class="text-base sm:text-lg font-bold border-b border-gray-600 pb-2 flex items-center">
                    <i class="fas fa-list-check mr-2 text-emerald-400"></i> Facilities
                  </h2>

                  <div class="space-y-2">
                    <label class="block text-xs text-gray-400 mb-1">Tambah Fasilitas</label>
                    <div class="flex gap-2">
                      <input id="facilityInputEdit" type="text"
                        class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                        placeholder="Contoh: VIP Lounge" />
                      <button type="button" id="addFacilityBtnEdit"
                        class="px-3 py-2 bg-emerald-600 hover:bg-emerald-700 rounded-md text-sm">Tambah</button>
                    </div>
                    <p class="text-xs text-gray-400">Tekan <strong>Enter</strong> untuk menambah. Maks 50 item, masing-masing 100 karakter.</p>

                    <div id="facilitiesChipsEdit" class="mt-3 flex flex-wrap gap-2"></div>
                    <div id="facilitiesHiddenEdit"></div>

                    @error('facilities') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    @error('facilities.*') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                  </div>
                </div>
              </div>

              {{-- KANAN --}}
              <div class="space-y-6">
                <div class="bg-[#262626] rounded-lg p-4 sm:p-6 space-y-4">
                  <h2 class="text-base sm:text-lg font-bold border-b border-gray-600 pb-2 flex items-center">
                    <i class="fas fa-store mr-2 text-green-400"></i> Informasi Venue
                  </h2>

                  <div class="space-y-4">
                    <div>
                      <label class="block text-xs text-gray-400 mb-1" for="venue_name">Nama Venue</label>
                      <input class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                        name="venue_name" id="venue_name" type="text" value="{{ old('venue_name', $venue->name) }}" />
                      @error('venue_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- ===== Gambar: pilih file → GANTI semua gambar lama (maks 3) ===== --}}
                    @php
                      $maxImages = 3;

                      // ==== NORMALIZER ROBUST ====
                      $feBase = 'https://demo-xanders.ptbmn.id/images/venue/';
                      $placeholder = asset('images/placeholder/venue.png');
                      $normalizeImg = function($img) use ($feBase, $placeholder) {
                          $raw = is_string($img) ? trim($img) : '';
                          if ($raw === '') return $placeholder;

                          // 1) Full URL
                          if (preg_match('~^https?://~i', $raw)) return $raw;

                          // 2) Sudah mengarah ke /storage (public disk)
                          if (stripos($raw, 'storage/') === 0) {
                              return asset($raw);
                          }

                          // 3) Path relatif yang biasa disimpan di DB (public disk)
                          //    contoh: "venue/abc.jpg", "uploads/venue/abc.jpg"
                          if (preg_match('~^(venue/|uploads/venue/)~i', $raw)) {
                              return asset('storage/'.$raw);
                          }

                          // 4) Asset publik di folder /public/images/venue/...
                          if (preg_match('~^(images/venue/|images/venues/|img/venue/)~i', $raw)) {
                              return asset($raw);
                          }

                          // 5) Hanya nama file → arahkan ke FE CDN bawaan
                          $name = basename($raw);
                          if ($name && $name !== '/' && $name !== '.') {
                              return $feBase.$name;
                          }

                          return $placeholder;
                      };

                      $existingImages = is_array($venue->images ?? null) ? $venue->images : [];
                      $existingImages = array_values(array_filter(array_map(fn($v)=> (string)$v, $existingImages)));
                      $existingImages = array_slice($existingImages, 0, $maxImages);
                    @endphp

                    <div>
                      <label class="block text-xs text-gray-400 mb-1">Upload Gambar (maks {{ $maxImages }})</label>
                      <input
                        id="imagesInput"
                        type="file"
                        name="images[]"
                        class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                        accept="image/*"
                        multiple
                        data-max-total="{{ $maxImages }}"
                      />
                      <input type="hidden" name="replace_images" id="replaceImagesFlag" value="0">
                      <p id="imagesHelp" class="text-xs mt-1 text-gray-500">
                        Memilih file akan <span class="badge-replace">MENGGANTI</span> semua gambar lama saat disimpan (maks {{ $maxImages }} gambar).
                        Jika tidak memilih file, gambar lama dipertahankan.
                      </p>

                      {{-- Preview gambar baru (belum upload) --}}
                      <div id="newPreview" class="flex flex-wrap gap-2 mt-3"></div>

                      {{-- Gambar lama (informasi) --}}
                      @if(count($existingImages))
                        <div class="mt-4">
                          <label class="block text-xs text-gray-400 mb-2">Gambar Saat Ini</label>
                          <div class="flex flex-wrap gap-2" id="oldImagesWrap">
                            @foreach($existingImages as $img)
                              @php $src = $normalizeImg($img); @endphp
                              <div class="img-tile">
                                <img src="{{ $src }}" alt="venue image"
                                     onerror="this.onerror=null;this.src='{{ $placeholder }}'">
                              </div>
                            @endforeach
                          </div>
                          <p class="text-xs text-gray-500 mt-2" id="oldInfo">Akan <u>tetap digunakan</u> jika tidak memilih file baru.</p>
                        </div>
                      @endif

                      @error('images') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                      @error('images.*') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    {{-- ===== END Gambar ===== --}}

                    {{-- Alamat --}}
                    <div class="space-y-2">
                      <label class="block text-xs text-gray-400">Alamat Venue</label>
                      <div id="addressDisplay" class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-gray-200 min-h-[90px] whitespace-pre-line select-text cursor-default pointer-events-none">
                        {{ $venue->address }}
                      </div>
                      <input type="hidden" name="address" id="addressHidden" value="{{ $venue->address }}">
                      @error('address') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Google Maps Embed --}}
                    <div class="space-y-2">
                      <label class="block text-xs text-gray-400" for="map_embed">Google Maps Embed (iframe/URL)</label>
                      <textarea name="map_embed" id="map_embed" rows="3" class="geocode-input"
                        placeholder='Tempel: <iframe src="https://www.google.com/maps/embed?..."></iframe> atau URL "https://www.google.com/maps/place/...".'>{{ old('map_embed', $venue->map_embed) }}</textarea>
                      @error('map_embed') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror

                      <div id="mapPreview" class="iframe-wrap mt-2" style="display:none;">
                        <iframe id="mapPreviewIframe" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen=""></iframe>
                      </div>
                      <p class="text-xs text-gray-400">Preview & alamat akan terisi otomatis dari link di atas.</p>
                    </div>

                    {{-- Phone --}}
                    <div>
                      <label class="block text-xs text-gray-400 mb-1" for="phone">Nomor Telepon</label>
                      <input name="phone" id="phone" type="text"
                        value="{{ old('phone', $venue->phone) }}"
                        placeholder="Masukkan nomor telepon"
                        class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500" />
                      @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                      <label class="block text-xs text-gray-400 mb-1">Jam Operasional</label>
                      <div class="flex gap-3">
                        <div class="flex-1">
                          <input name="operating_hour"
                            value="{{ $openValue }}"
                            class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                            id="operating_hour" type="time" />
                          @error('operating_hour') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="flex-1">
                          <input name="closing_hour"
                            value="{{ $closeValue }}"
                            class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                            id="closing_hour" type="time" />
                          @error('closing_hour') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                      </div>
                      <p class="text-xs text-gray-500 mt-1">Mendukung format HH:MM dan HH:MM:SS.</p>
                    </div>

                    <div>
                      <label class="block text-xs text-gray-400 mb-1" for="description">Deskripsi</label>
                      <textarea class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white resize-none focus:outline-none focus:ring-1 focus:ring-blue-500"
                        name="description" id="description" rows="4">{{ old('description', $venue->description) }}</textarea>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  @if(session('success'))
    Swal.fire({ icon:'success', title:'Berhasil!', text:'{{ session('success') }}', showConfirmButton:false, timer:3000, background:'#222', color:'#fff' });
  @endif
  @if(session('error'))
    Swal.fire({ icon:'error', title:'Error!', text:'{{ session('error') }}', showConfirmButton:true, background:'#222', color:'#fff' });
  @endif

  // ====== Map preview + auto address ======
  const embedEl = document.getElementById('map_embed');
  const previewWrap = document.getElementById('mapPreview');
  const previewIframe = document.getElementById('mapPreviewIframe');
  const addressDisplay = document.getElementById('addressDisplay');
  const addressHidden  = document.getElementById('addressHidden');

  function extractSrc(val){
    if (!val) return null;
    val = val.trim();
    if (val.toLowerCase().startsWith('<iframe')) {
      const m = val.match(/src\s*=\s*"(.*?)"/i) || val.match(/src\s*=\s*'(.*?)'/i);
      return m ? m[1] : null;
    }
    return val;
  }
  function parseAddressFromSrc(src){
    if (!src) return '';
    try{
      const u = new URL(src);
      const q = u.searchParams.get('q');
      if (q) return decodeURIComponent(q.replace(/\+/g,' '));
      const mPlace = u.pathname.match(/\/maps\/place\/([^/]+)/i);
      if (mPlace && mPlace[1]) return decodeURIComponent(mPlace[1].replace(/\+/g,' '));
      const pb = u.searchParams.get('pb');
      if (pb) {
        let pbDec = decodeURIComponent(pb); try { pbDec = decodeURIComponent(pbDec); } catch(e){}
        const matches = [...pbDec.matchAll(/!2s([^!]+)/g)].map(x=>x[1]);
        const cleaned = matches.map(t=>{
          try{ t = decodeURIComponent(t); }catch(e){}
          t = t.replace(/\+/g,' ').replace(/\\u0026/gi,'&').trim();
          return t;
        }).filter(t => t.length >= 5 && /[A-Za-z]/.test(t) && !/^[a-z]{2}(-[A-Z]{2})?$/.test(t) && !/^(Google|Maps|Street View)$/i.test(t));
        if (cleaned.length){ cleaned.sort((a,b)=>b.length-a.length); return cleaned[0]; }
      }
    }catch(e){}
    if (src && src.toLowerCase().startsWith('<iframe')) {
      const t = src.match(/title\s*=\s*"(.*?)"/i);
      if (t && t[1] && t[1].toLowerCase() !== 'google maps') return t[1];
    }
    return '';
  }
  function renderPreview(){
    const src = extractSrc(embedEl.value);
    if (src && /^https?:\/\//i.test(src)) { previewIframe.src = src; previewWrap.style.display = ''; }
    else { previewIframe.removeAttribute('src'); previewWrap.style.display = 'none'; }
    const parsed = parseAddressFromSrc(src);
    if (parsed) { addressHidden.value = parsed; addressDisplay.textContent = parsed; }
  }
  if (embedEl) { embedEl.addEventListener('input', renderPreview); renderPreview(); }

  // ====== Facilities (chips) ======
  const facInput   = document.getElementById('facilityInputEdit');
  const facAddBtn  = document.getElementById('addFacilityBtnEdit');
  const facChips   = document.getElementById('facilitiesChipsEdit');
  const facHidden  = document.getElementById('facilitiesHiddenEdit');
  const form       = document.getElementById('editVenueForm');

  let facilities = @json($facOld);
  function renderFacilities(){
    facChips.innerHTML = ''; facHidden.innerHTML = '';
    facilities.forEach((text, idx) => {
      const chip = document.createElement('span');
      chip.className = 'chip';
      chip.innerHTML = `<span>${text}</span> <button type="button" aria-label="hapus">&times;</button>`;
      chip.querySelector('button').addEventListener('click', () => { facilities.splice(idx, 1); renderFacilities(); });
      facChips.appendChild(chip);

      const hidden = document.createElement('input');
      hidden.type = 'hidden'; hidden.name = 'facilities[]'; hidden.value = text;
      facHidden.appendChild(hidden);
    });
  }
  function addFacilityFromInput(){
    const v = (facInput.value || '').trim();
    if (!v) return;
    if (v.length > 100) return alert('Maksimal 100 karakter per item.');
    if (facilities.length >= 50) return alert('Maksimal 50 fasilitas.');
    if (!facilities.includes(v)) { facilities.push(v); renderFacilities(); }
    facInput.value = ''; facInput.focus();
  }
  if (facAddBtn) facAddBtn.addEventListener('click', addFacilityFromInput);
  if (facInput) facInput.addEventListener('keydown', (e) => { if (e.key === 'Enter') { e.preventDefault(); addFacilityFromInput(); } });
  if (form) { form.addEventListener('submit', () => renderFacilities()); }
  renderFacilities();

  // ====== File baru = GANTI semua gambar lama (maks 3) + preview ======
  (function(){
    const input  = document.getElementById('imagesInput');
    const help   = document.getElementById('imagesHelp');
    const previewWrap = document.getElementById('newPreview');
    const flag   = document.getElementById('replaceImagesFlag');
    const oldWrap= document.getElementById('oldImagesWrap');
    const oldInfo= document.getElementById('oldInfo');

    if(!input) return;
    const maxTotal = parseInt(input.dataset.maxTotal || '3', 10);

    function renderPreviews(files){
      previewWrap.innerHTML = '';
      files.forEach(file => {
        const url = URL.createObjectURL(file);
        const fig = document.createElement('div');
        fig.className = 'img-tile';
        fig.innerHTML = `<img src="${url}" alt="preview">`;
        previewWrap.appendChild(fig);
      });
    }

    input.addEventListener('change', () => {
      const files = Array.from(input.files || []);
      if(files.length > maxTotal){
        const dt = new DataTransfer();
        files.slice(0, maxTotal).forEach(f => dt.items.add(f));
        input.files = dt.files;
      }
      const picked = Array.from(input.files || []);
      if (picked.length > 0) {
        flag.value = '1';
        help.className = 'text-xs mt-1 text-yellow-300';
        help.innerHTML = `Mode <b>GANTI</b> aktif. Semua gambar lama akan diganti dengan ${picked.length} gambar baru (maks ${maxTotal}).`;
        if (oldInfo) oldInfo.textContent = 'Akan DIGANTI saat disimpan.';
      } else {
        flag.value = '0';
        help.className = 'text-xs mt-1 text-gray-500';
        help.textContent = `Memilih file akan MENGGANTI semua gambar lama saat disimpan (maks ${maxTotal} gambar). Jika tidak memilih file, gambar lama dipertahankan.`;
        if (oldInfo) oldInfo.textContent = 'Akan tetap digunakan jika tidak memilih file baru.';
      }
      renderPreviews(picked);
      if (oldWrap) oldWrap.style.opacity = picked.length ? '.35' : '1';
    });
  })();
</script>
@endpush
