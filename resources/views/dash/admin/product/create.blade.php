@extends('app')
@section('title', 'Admin Dashboard - Products List')

@push('styles')
<style>
  /* ===== Global anti white-flash / rubber-band ===== */
  :root{ color-scheme: dark; --page-bg:#0a0a0a; }
  html, body{
    height:100%;
    min-height:100%;
    background:var(--page-bg);
    overscroll-behavior-y: none;   /* cegah chaining ke body */
    overscroll-behavior-x: none;
    touch-action: pan-y;           /* iOS Safari: tetap bisa scroll vertikal */
    -webkit-text-size-adjust: 100%;
  }
  /* Kanvas gelap “di belakang segalanya” saat bounce */
  #antiBounceBg{
    position: fixed;
    left:0; right:0;
    top:-120svh;                   /* svh stabil di mobile */
    bottom:-120svh;
    background:var(--page-bg);
    z-index:-1;
    pointer-events:none;
  }
  /* Pastikan wrapper gelap */
  #app, main{ background:var(--page-bg); }

  /* Container scroll utama aman dari bounce tembus body */
  .scroll-safe{
    background-color:#171717;      /* match bg-neutral-900 */
    overscroll-behavior: contain;
    -webkit-overflow-scrolling: touch;
  }

  /* Styling form/panel (tetap sama) */
  .panel{ background:#262626; }
</style>
@endpush

@section('content')
<!-- Layer gelap anti-bounce -->
<div id="antiBounceBg" aria-hidden="true"></div>

<div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
    <div class="flex flex-1 min-h-0">
        @include('partials.sidebar')

        <!-- Tambahkan .scroll-safe di sini -->
        <main class="flex-1 overflow-y-auto min-w-0 mb-8 py-8 scroll-safe">
            @include('partials.topbar')

            <h1 class="text-3xl font-extrabold my-8 px-4 sm:px-8">
                Tambah Product
            </h1>

            <form method="POST" action="{{ route('products.store') }}"
                  class="flex flex-col lg:flex-row lg:space-x-8 px-4 sm:px-8 gap-8"
                  enctype="multipart/form-data">
                @csrf

                <!-- General Information -->
                <section aria-labelledby="general-info-title"
                    class="panel rounded-lg p-6 sm:p-8 flex-1 w-full space-y-8">
                    <h2 class="text-lg font-bold border-b border-gray-600 pb-2" id="general-info-title">
                        General Information
                    </h2>

                    <div class="space-y-6">
                        <div>
                            <label class="block text-xs text-gray-400 mb-1" for="product-name">
                                Product Name
                            </label>
                            <input name="name" id="product-name" type="text"
                                placeholder="Enter product name"
                                value="{{ old('name') }}"
                                class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500" />
                            @error('name')
                              <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 mb-1" for="product-description">
                                Product Description
                            </label>
                            <textarea name="description" id="product-description" rows="5" wrap="soft"
                                placeholder="Enter product description"
                                class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white resize-none focus:outline-none focus:ring-1 focus:ring-blue-500 whitespace-pre-wrap break-words">{{ old('description') }}</textarea>
                            @error('description')
                              <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                            @enderror
                            {{-- NOTE:
                                 - whitespace-pre-wrap memastikan newline terlihat saat mengetik.
                                 - Saat MENAMPILKAN deskripsi di halaman lain (bukan textarea),
                                   gunakan class Tailwind `whitespace-pre-line` atau:
                                   {!! nl2br(e($product->description)) !!} --}}
                        </div>
                    </div>

                    <!-- Category Section -->
                    <div>
                        <h3 class="text-lg font-bold border-b border-gray-600 pb-2 mb-4">
                            Category
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs text-gray-400 mb-1" for="category">
                                    Product Category
                                </label>
                                <select name="category_id" id="category"
                                    class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                                    <option disabled {{ old('category_id') ? '' : 'selected' }}>Please choose category</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id')==$category->id?'selected':'' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                  <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-xs text-gray-400 mb-1" for="brand">
                                    Product Brand
                                </label>
                                <select name="brand" id="brand"
                                    class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                                    <option disabled {{ old('brand') ? '' : 'selected' }}>Please choose brand</option>
                                    @foreach (['Mezz', 'Predator', 'Cuetec', 'Other'] as $value)
                                        <option value="{{ $value }}" {{ old('brand')==$value?'selected':'' }}>{{ $value }}</option>
                                    @endforeach
                                </select>
                                @error('brand')
                                  <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-xs text-gray-400 mb-1" for="condition">
                                    Product Condition
                                </label>
                                <select name="condition" id="condition"
                                    class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                                    <option disabled {{ old('condition') ? '' : 'selected' }}>Please choose condition</option>
                                    @foreach (['new', 'used'] as $value)
                                        <option value="{{ $value }}" {{ old('condition')==$value?'selected':'' }}>{{ $value }}</option>
                                    @endforeach
                                </select>
                                @error('condition')
                                  <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Right Panel -->
                <section class="flex flex-col space-y-8 w-full max-w-lg">
                    <!-- Product Image -->
                    <div class="panel rounded-lg p-6 space-y-4">
                        <h2 class="text-lg font-bold border-b border-gray-600 pb-2">Product Image</h2>
                        <div class="flex flex-wrap gap-4">
                            <input type="file" name="images[]" id="imageUpload" class="w-full border border-gray-600 bg-[#262626] px-3 py-2 text-sm rounded-md" accept="image/*" multiple>
                            <p class="text-xs text-gray-500">Format: JPG, JPEG, PNG, WEBP, GIF. Maks: 4MB/berkas.</p>
                            {{-- PENTING:
                               - Name harus "images[]" dan multiple agar ProductController@store
                                 memproses & menyimpan ke:
                                 public/images/products (CMS) dan
                                 ..//images/products (FE) --}}
                        </div>
                    </div>

                    <!-- Shipping & Delivery -->
                    <div class="panel rounded-lg p-6 space-y-4">
                        <h2 class="text-lg font-bold border-b border-gray-600 pb-2">Shipping and Delivery</h2>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs text-gray-400 mb-1" for="items-weight">
                                    Items Weight (gram)
                                </label>
                                <input name="weight" id="items-weight" type="number" placeholder="0" value="{{ old('weight') }}"
                                    class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500" />
                                @error('weight')
                                  <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1" for="length">Length (cm)</label>
                                    <input name="length" id="length" type="number" placeholder="0" value="{{ old('length') }}"
                                        class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500" />
                                    @error('length')
                                      <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1" for="breadth">Breadth (cm)</label>
                                    <input name="breadth" id="breadth" type="number" placeholder="0" value="{{ old('breadth') }}"
                                        class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500" />
                                    @error('breadth')
                                      <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1" for="width">Width (cm)</label>
                                    <input name="width" id="width" type="number" placeholder="0" value="{{ old('width') }}"
                                        class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500" />
                                    @error('width')
                                      <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pricing -->
                   <div class="panel rounded-lg p-6 space-y-4">
                        <h2 class="text-lg font-bold border-b border-gray-600 pb-2">Pricing</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            {{-- Harga --}}
                            <div>
                                <label class="block text-xs text-gray-400 mb-1" for="price">Price (IDR)</label>
                                <input name="pricing" id="price" type="text" placeholder="0"
                                    inputmode="numeric" autocomplete="off" value="{{ old('pricing') }}"
                                    class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500" />
                                @error('pricing')
                                    <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                    
                            {{-- Diskon --}}
                            <div>
                                <label class="block text-xs text-gray-400 mb-1" for="discount">Discount (%)</label>
                                <input name="discount" id="discount" type="number" placeholder="0"
                                    value="{{ old('discount') }}"
                                    class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500" />
                                @error('discount')
                                    <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                    
                            {{-- Stok --}}
                            <div>
                                <label class="block text-xs text-gray-400 mb-1" for="stock_qty">Stock</label>
                                <input name="stock_qty" id="stock_qty" type="number" placeholder="0" value="{{ old('stock_qty') }}"
                                    class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500" />
                                @error('stock_qty')
                                    <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>


                    <!-- Buttons -->
                    <div class="flex flex-col sm:flex-row gap-4 justify-end">
                        <button type="reset"
                            class="px-6 py-2 border border-red-600 text-red-600 rounded-md hover:bg-red-600 hover:text-white transition">
                            Discard
                        </button>
                        <button type="submit"
                            class="px-6 py-2 bg-[#0a8cff] rounded-md hover:bg-[#0077e6] transition">
                            Save
                        </button>
                    </div>
                </section>
            </form>
        </main>
    </div>
</div>

{{-- ===== Price (IDR) number format ===== --}}
<script>
  (function(){
    const el = document.getElementById('price');
    if(!el) return;

    const nf = new Intl.NumberFormat('id-ID'); // 1.234.567
    const digits = (v) => (v || '').replace(/\D+/g, '');
    const fmt    = (raw) => raw ? nf.format(Number(raw)) : '';

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
</script>
@endsection