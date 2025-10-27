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

          {{-- ====== TAMPILAN ERROR VALIDASI ====== --}}
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
          {{-- ===================================== --}}

          @php
            // Robust formatter untuk input type="time" → selalu "HH:MM"
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
              <!-- Kolom Kiri -->
              <div class="space-y-6">
                <!-- Informasi Akun -->
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
                        name="venue_name" id="venue_name" type="text" value="{{ old('venue_name', $venue->name) }}" />
                      @error('venue_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                      <label class="block text-xs text-gray-400 mb-1">Upload Gambar (Opsional)</label>
                      <input type="file" name="images[]" multiple accept="image/*"
                        class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500" />
                      <p class="text-xs text-gray-500 mt-1">Format: JPG, PNG, WEBP, AVIF, GIF. Maks: 4MB/berkas</p>

                      @php
                        $existingImages = is_array($venue->images ?? null) ? $venue->images : [];
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

                    {{-- ADDRESS: tampilan saja + hidden untuk submit --}}
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

                    {{-- ===== PHONE (BARU DITAMBAHKAN) ===== --}}
                    <div>
                      <label class="block text-xs text-gray-400 mb-1" for="phone">Nomor Telepon</label>
                      <input name="phone" id="phone" type="text"
                        value="{{ old('phone', $venue->phone) }}"
                        placeholder="Masukkan nomor telepon"
                        class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500" />
                      @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    {{-- ===================================== --}}

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
                      <p class="text-xs text-gray-500 mt-1">Nilai di atas diambil langsung dari data yang tersimpan (mendukung format HH:MM dan HH:MM:SS).</p>
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

  // ====== Map preview + auto address (pb-aware) ======
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
      addressHidden.value = parsed;
      addressDisplay.textContent = parsed;
    }
  }

  if (embedEl) {
    embedEl.addEventListener('input', renderPreview);
    renderPreview();
  }

  // ====== Facilities (chips) ======
  const facInput   = document.getElementById('facilityInputEdit');
  const facAddBtn  = document.getElementById('addFacilityBtnEdit');
  const facChips   = document.getElementById('facilitiesChipsEdit');
  const facHidden  = document.getElementById('facilitiesHiddenEdit');
  const form       = document.getElementById('editVenueForm');

  let facilities = @json($facOld);

  function renderFacilities(){
    facChips.innerHTML = '';
    facHidden.innerHTML = '';
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
</script>
@endpush
