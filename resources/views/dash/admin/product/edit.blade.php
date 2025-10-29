@extends('app')
@section('title', 'Admin Dashboard - Edit Product')

@push('styles')
<style>
  :root{ color-scheme: dark; --page-bg:#0a0a0a; }
  html, body{ height:100%; min-height:100%; background:var(--page-bg); overscroll-behavior-y:none; overscroll-behavior-x:none; touch-action:pan-y; -webkit-text-size-adjust:100%; }
  #antiBounceBg{ position:fixed; left:0; right:0; top:-120svh; bottom:-120svh; background:var(--page-bg); z-index:-1; pointer-events:none; }
  #app, main{ background:var(--page-bg); }
  .scroll-safe{ background-color:#171717; overscroll-behavior:contain; -webkit-overflow-scrolling:touch; }
  .panel{ background:#262626; }
  .alert{ border-radius:10px; padding:.875rem 1rem; }
  .alert-success{ background:#052e1a; border:1px solid #14532d; color:#bbf7d0; }
  .alert-error{ background:#3a0b0b; border:1px solid #7f1d1d; color:#fecaca; }
  .alert-error ul{ list-style: disc; padding-left:1.25rem; margin-top:.375rem; }
</style>
@endpush

@section('content')
<div id="antiBounceBg" aria-hidden="true"></div>

<div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
  <div class="flex flex-1 min-h-0">
    @include('partials.sidebar')
    <main class="flex-1 overflow-y-auto min-w-0 mb-8 py-6 sm:py-8 scroll-safe">
      @include('partials.topbar')

      <div class="px-4 sm:px-8 space-y-4">
        {{-- Flash Success --}}
        @if(session('success'))
          <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        {{-- Flash Error --}}
        @if(session('error'))
          <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        {{-- Ringkasan Validasi --}}
        @if ($errors->any())
          <div class="alert alert-error">
            <div class="font-semibold">Periksa input kamu:</div>
            <ul>
              @foreach ($errors->all() as $err)
                <li>{{ $err }}</li>
              @endforeach
            </ul>
          </div>
        @endif
      </div>

      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between px-4 sm:px-8 my-6 sm:my-8 gap-3">
        <h1 class="text-2xl sm:text-3xl font-extrabold">Edit Product: {{ $product->name }}</h1>
        <a href="{{ route('products.index') }}" class="flex items-center gap-2 text-blue-400 hover:text-blue-300 text-sm sm:text-base">
          <i class="fas fa-arrow-left"></i><span>Kembali ke daftar produk</span>
        </a>
      </div>

      <form id="editProductForm" action="{{ route('products.update', $product->id) }}" method="POST" class="px-4 sm:px-8" enctype="multipart/form-data">
        @csrf @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 sm:gap-8">
          <div class="space-y-6">
            <div class="panel rounded-lg p-4 sm:p-6 space-y-4">
              <h2 class="text-base sm:text-lg font-bold border-b border-gray-600 pb-2 flex items-center">
                <i class="fas fa-info-circle mr-2 text-blue-400"></i> Informasi Dasar
              </h2>
              <div class="space-y-4">
                <div>
                  <label class="block text-xs text-gray-400 mb-1">Nama Produk</label>
                  <input name="name" type="text" value="{{ old('name', $product->name) }}" class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500" />
                  @error('name')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                  <label class="block text-xs text-gray-400 mb-1" for="desc">Deskripsi Produk</label>
                  <textarea name="description" id="desc" rows="5" wrap="soft"
                            class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm resize-none focus:outline-none focus:ring-1 focus:ring-blue-500 whitespace-pre-wrap break-words">{{ old('description', $product->description) }}</textarea>
                  @error('description')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                </div>
              </div>
            </div>

            <div class="panel rounded-lg p-4 sm:p-6 space-y-4">
              <h2 class="text-base sm:text-lg font-bold border-b border-gray-600 pb-2 flex items-center">
                <i class="fas fa-tag mr-2 text-green-400"></i> Kategori & Brand
              </h2>
              <div class="space-y-4">
                <div>
                  <label class="block text-xs text-gray-400 mb-1">Kategori Produk</label>
                  <select name="category_id" class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                    @foreach($categories ?? [] as $category)
                      <option value="{{ $category->id }}" {{ old('category_id', $product->category_id)==$category->id?'selected':'' }}>{{ $category->name }}</option>
                    @endforeach
                  </select>
                  @error('category_id')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                  <label class="block text-xs text-gray-400 mb-1">Brand</label>
                  <select name="brand" class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                    @foreach(['Mezz','Predator','Cuetec','Other'] as $brand)
                      <option value="{{ $brand }}" {{ old('brand', $product->brand)==$brand?'selected':'' }}>{{ $brand }}</option>
                    @endforeach
                  </select>
                  @error('brand')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                  <label class="block text-xs text-gray-400 mb-1">Kondisi</label>
                  <div class="flex flex-wrap gap-4">
                    @foreach(['new'=>'Baru','used'=>'Bekas'] as $val=>$label)
                      <label class="flex items-center gap-2 text-sm">
                        <input type="radio" name="condition" value="{{ $val }}" {{ old('condition', $product->condition)==$val?'checked':'' }} class="text-blue-500 focus:ring-blue-500 h-4 w-4" />
                        <span>{{ $label }}</span>
                      </label>
                    @endforeach
                  </div>
                  @error('condition')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                </div>
              </div>
            </div>
          </div>

          <div class="space-y-6">
            <div class="panel rounded-lg p-4 sm:p-6 space-y-4">
              <h2 class="text-base sm:text-lg font-bold border-b border-gray-600 pb-2 flex items-center">
                <i class="fas fa-box mr-2 text-yellow-400"></i> Inventori
              </h2>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs text-gray-400 mb-1" for="stock_qty">Stock QTy <span class="text-red-400">*</span></label>
                  {{-- Samakan dengan create: name="stock_qty" --}}
                  <input name="stock_qty" id="stock_qty" type="number" min="0" step="1"
                         value="{{ old('stock_qty', $product->stock) }}"
                         class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500" required />
                  @error('stock_qty')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                  <label class="block text-xs text-gray-400 mb-1">SKU (Opsional)</label>
                  <input name="sku" type="text" value="{{ old('sku', $product->sku) }}" class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500" />
                  @error('sku')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                </div>
              </div>
            </div>

            <div class="panel rounded-lg p-4 sm:p-6 space-y-4">
              <h2 class="text-base sm:text-lg font-bold border-b border-gray-600 pb-2 flex items-center">
                <i class="fas fa-image mr-2 text-indigo-400"></i> Gambar Produk (maks. 4)
              </h2>

              {{-- Samakan dengan create: input file hidden + preview grid (maks 4) --}}
              <input name="images[]" id="productImages" type="file" multiple accept="image/*" class="hidden">
              <div id="productImagePreview" class="mt-2 grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="col-span-2 sm:col-span-4 h-24 border-2 border-dashed border-gray-500 rounded-md flex items-center justify-center text-gray-400 text-xs hover:border-blue-500 hover:text-blue-400 transition cursor-pointer"
                     onclick="document.getElementById('productImages').click()">
                  Klik untuk pilih hingga 4 gambar
                </div>
              </div>
              <p class="text-xs text-gray-500">Format: JPG, JPEG, PNG, WEBP, GIF. Maks: 4MB/berkas. Upload baru akan <strong>mengganti semua gambar</strong> saat disimpan.</p>
              @error('images') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
              @error('images.*') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror

              {{-- Tampilkan gambar saat ini --}}
              @php
                $existingImages = is_array($product->images ?? null) ? $product->images : [];
                $normalizeImg = function($img){
                  $raw = trim((string)$img);
                  if (preg_match('/^https?:\/\//i', $raw)) return $raw;
                  $filename = basename($raw);
                  return asset('images/products/' . $filename);
                };
              @endphp
              @if(!empty($existingImages))
              <div class="mt-4">
                <label class="block text-xs text-gray-400 mb-2">Gambar Saat Ini</label>
                <div class="flex flex-wrap gap-2">
                  @foreach($existingImages as $img)
                    @php $src = $normalizeImg($img); @endphp
                    <div class="relative w-20 h-20">
                      <img src="{{ $src }}" alt="Product image" class="w-full h-full object-cover rounded-md" onerror="this.src='https://placehold.co/400x400?text=No+Img'"/>
                    </div>
                  @endforeach
                </div>
              </div>
              @endif
            </div>

            <div class="panel rounded-lg p-4 sm:p-6 space-y-4">
              <h2 class="text-base sm:text-lg font-bold border-b border-gray-600 pb-2 flex items-center">
                <i class="fas fa-truck mr-2 text-purple-400"></i> Pengiriman & Dimensi
              </h2>
              <div class="space-y-4">
                <div>
                  <label class="block text-xs text-gray-400 mb-1">Berat (gram)</label>
                  <input name="weight" type="number" value="{{ old('weight', $product->weight) }}" class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500" />
                  @error('weight')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                  <div>
                    <label class="block text-xs text-gray-400 mb-1">Panjang (cm)</label>
                    <input name="length" type="number" value="{{ old('length', $product->length) }}" class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500" />
                    @error('length')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                  </div>
                  <div>
                    <label class="block text-xs text-gray-400 mb-1">Lebar (cm)</label>
                    <input name="breadth" type="number" value="{{ old('breadth', $product->breadth) }}" class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500" />
                    @error('breadth')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                  </div>
                  <div>
                    <label class="block text-xs text-gray-400 mb-1">Tinggi (cm)</label>
                    <input name="width" type="number" value="{{ old('width', $product->width) }}" class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500" />
                    @error('width')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                  </div>
                </div>
              </div>
            </div>

            <div class="panel rounded-lg p-4 sm:p-6 space-y-4">
              <h2 class="text-base sm:text-lg font-bold border-b border-gray-600 pb-2 flex items-center">
                <i class="fas fa-tag mr-2 text-red-400"></i> Harga
              </h2>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs text-gray-400 mb-1" for="price">Harga (IDR)</label>
                  <input name="pricing" id="price" type="text" inputmode="numeric" autocomplete="off" value="{{ old('pricing', $product->pricing) }}" class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500" />
                  @error('pricing')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                  <label class="block text-xs text-gray-400 mb-1">Diskon (%)</label>
                  <input name="discount" type="number" value="{{ old('discount', $product->discount) }}" class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500" />
                  @error('discount')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="flex flex-col sm:flex-row justify-end mt-8 gap-3 sm:gap-4 px-4 sm:px-8">
          <a href="{{ route('products.index') }}" class="px-5 py-2 border border-red-600 text-red-600 rounded-md text-center hover:bg-red-600 hover:text-white transition">Batal</a>
          <button type="submit" class="px-5 py-2 bg-gradient-to-r from-blue-500 to-blue-600 rounded-md hover:from-blue-600 hover:to-blue-700 transition">Simpan Perubahan</button>
        </div>
      </form>
    </main>
  </div>
</div>

@push('scripts')
<script>
  // ===== Price (IDR) formatting =====
  (function(){
    const el = document.getElementById('price');
    if(!el) return;
    const nf = new Intl.NumberFormat('id-ID');
    const digits = (v) => (v || '').replace(/\D+/g, '');
    const fmt = (raw) => raw ? nf.format(Number(raw)) : '';
    const raw0 = digits(el.value);
    el.value = fmt(raw0);
    el.dataset.raw = raw0;
    el.addEventListener('input', () => {
      const raw = digits(el.value);
      el.dataset.raw = raw;
      el.value = fmt(raw);
      try{ el.setSelectionRange(el.value.length, el.value.length); }catch(e){}
    });
    el.addEventListener('blur', () => {
      const raw = digits(el.value);
      el.dataset.raw = raw;
      el.value = fmt(raw);
    });
    el.form?.addEventListener('submit', () => {
      el.value = el.dataset.raw ? el.dataset.raw : '';
    });
  })();

  // ===== Product Images preview (maks 4) ala "create" =====
  (function(){
    const MAX_FILES = 4;
    const input = document.getElementById('productImages');
    const grid  = document.getElementById('productImagePreview');
    let selectedFiles = [];
    if (!input || !grid) return;

    input.addEventListener('change', (e) => {
      const incoming = Array.from(e.target.files).map(file => {
        const uniqueName = Date.now() + '-' + file.name;
        return new File([file], uniqueName, { type: file.type });
      });
      if (selectedFiles.length + incoming.length > MAX_FILES) {
        alert('Maksimal 4 gambar.');
      }
      selectedFiles = [...selectedFiles, ...incoming]
        .filter(f => f.type.startsWith('image/'))
        .slice(0, MAX_FILES);
      render(); syncBack();
    });

    function removeAt(idx){
      selectedFiles.splice(idx, 1);
      render(); syncBack();
    }
    function syncBack(){
      const dt = new DataTransfer();
      selectedFiles.forEach(f => dt.items.add(f));
      input.files = dt.files;
    }
    function render(){
      grid.innerHTML = '';
      selectedFiles.forEach((file, idx) => {
        const reader = new FileReader();
        reader.onload = ev => {
          const wrap = document.createElement('div');
          wrap.className = 'relative group';
          const img = document.createElement('img');
          img.src = ev.target.result; img.alt = file.name;
          img.className = 'w-full h-24 object-cover rounded-md border border-gray-600';
          const rm = document.createElement('button');
          rm.type = 'button'; rm.textContent = '×';
          rm.className = 'absolute top-1 right-1 bg-red-600 text-white rounded-full w-5 h-5 text-xs opacity-0 group-hover:opacity-100 transition-opacity';
          rm.onclick = e => { e.stopPropagation(); removeAt(idx); };
          wrap.appendChild(img); wrap.appendChild(rm); grid.appendChild(wrap);
        };
        reader.readAsDataURL(file);
      });
      const remain = MAX_FILES - selectedFiles.length;
      for (let i = 0; i < remain; i++){
        const ph = document.createElement('div');
        ph.className = 'h-24 border-2 border-dashed border-gray-500 rounded-md flex items-center justify-center text-gray-400 text-xs cursor-pointer hover:border-blue-500 hover:text-blue-400 transition';
        ph.textContent = 'Klik untuk pilih';
        ph.onclick = () => input.click();
        grid.appendChild(ph);
      }
    }
  })();
</script>
@endpush
@endsection
