@extends('app')
@section('title', 'Admin Dashboard - Products List')

@push('styles')
<style>
  :root{ color-scheme: dark; --page-bg:#0a0a0a; }
  html, body{
    height:100%; min-height:100%; background:var(--page-bg);
    overscroll-behavior-y:none; overscroll-behavior-x:none;
    touch-action: pan-y; -webkit-text-size-adjust:100%;
  }
  #antiBounceBg{ position:fixed; left:0; right:0; top:-120svh; bottom:-120svh; background:var(--page-bg); z-index:-1; pointer-events:none; }
  #app, main{ background:var(--page-bg); }
  .scroll-safe{ background-color:#171717; overscroll-behavior:contain; -webkit-overflow-scrolling:touch; }
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

                  <h1 class="text-2xl sm:text-3xl font-extrabold mb-6">My Product</h1>

                  @if (session('success'))
                      <div class="mb-4 bg-green-500 text-white px-4 py-2 rounded text-sm">
                          {{ session('success') }}
                      </div>
                  @endif

                  @if (session('error'))
                      <div class="mb-4 bg-red-500 text-white px-4 py-2 rounded text-sm">
                          {{ session('error') }}
                      </div>
                  @endif

                  @php
                    /**
                     * NORMALISASI URL GAMBAR PRODUK
                     *
                     * Target akhir SELALU:
                     *   https://domain.tld/images/products/<filename>
                     *
                     * Kasus yang dibersihkan:
                     *   1) https://domain.tld/images/demo-xanders/products/<filename>
                     *   2) https://domain.tld/demo-xanders/images/products/<filename>
                     *   3) Varian "demo-xander" (tanpa "s")
                     *   4) URL relatif dengan pola yang sama
                     */
                    $normalizeProductImage = function ($url) {
                        if (!$url) return null;

                        // Ambil PATH dari URL absolute, atau pakai string jika sudah relatif
                        $path = parse_url($url, PHP_URL_PATH) ?: $url;

                        // Pastikan diawali slash
                        $path = '/'.ltrim($path, '/');

                        // Ganti "/images/demo-xanders/products/" atau "/images/demo-xander/products/" -> "/images/products/"
                        $path = preg_replace('#/images/(demo-xanders?|demo-xander)/products/#', '/images/products/', $path);

                        // Ganti "/demo-xanders/images/products/" atau "/demo-xander/images/products/" -> "/images/products/"
                        $path = preg_replace('#/demo-xanders?/images/products/#', '/images/products/', $path);

                        // Rapikan double slash
                        $path = preg_replace('#/+#', '/', $path);

                        // Kembalikan absolute URL berbasis app URL (scheme+host dari app)
                        return url($path);
                    };
                  @endphp

                  <!-- Filter & Search -->
                  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
                      <input
                          class="w-full sm:w-64 rounded-md border border-gray-600 bg-transparent px-3 py-2 text-gray-300 placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-[#999] focus:border-[#999]"
                          onchange="window.location.href='{{ route('products.index') }}?search='+this.value+'&status='+document.getElementById('statusFilter').value+'&category='+document.getElementById('categoryFilter').value;"
                          value="{{ request('search') }}" placeholder="Search" type="search" />

                      <div class="flex flex-col sm:flex-row flex-wrap gap-2 items-stretch sm:items-center">
                          <select id="statusFilter"
                              onchange="window.location.href='{{ route('products.index') }}?search='+document.querySelector('input[type=search]').value+'&status='+this.value+'&category='+document.getElementById('categoryFilter').value;"
                              class="bg-[#2c2c2c] text-gray-400 text-xs sm:text-sm rounded border border-gray-700 px-2 py-2 cursor-pointer">
                              <option value="">-- Status --</option>
                              <option value="in-stock" {{ request('status') == 'in-stock' ? 'selected' : '' }}>In Stock</option>
                              <option value="out-of-stock" {{ request('status') == 'out-of-stock' ? 'selected' : '' }}>Out of Stock</option>
                          </select>
                          <select id="categoryFilter"
                              onchange="window.location.href='{{ route('products.index') }}?search='+document.querySelector('input[type=search]').value+'&status='+document.getElementById('statusFilter').value+'&category='+this.value;"
                              class="bg-[#2c2c2c] text-gray-400 text-xs sm:text-sm rounded border border-gray-700 px-2 py-2 cursor-pointer">
                              <option value="">-- Category --</option>
                              @foreach ($categories as $category)
                                  <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                      {{ $category->name }}
                                  </option>
                              @endforeach
                          </select>
                          <a href="{{ route('products.create') }}"
                             class="flex items-center justify-center gap-1 border border-[#1e90ff] text-[#1e90ff] rounded px-3 py-2 text-xs sm:text-sm hover:bg-[#1e90ff] hover:text-white transition whitespace-nowrap">
                              <i class="fas fa-plus"></i> Add Product
                          </a>
                      </div>
                  </div>

                  <!-- Desktop & Tablet Table -->
                  <div class="hidden sm:block overflow-x-auto">
                      <table class="min-w-full text-left text-sm border border-gray-700 rounded-md">
                          <thead class="bg-[#2c2c2c] text-gray-300">
                              <tr>
                                  <th class="px-4 py-3">Product</th>
                                  <th class="px-4 py-3">Category</th>
                                  <th class="px-4 py-3">Price</th>
                                  <th class="px-4 py-3">Stock</th>
                                  <th class="px-4 py-3">Status</th>
                                  <th class="px-4 py-3">Action</th>
                              </tr>
                          </thead>
                          <tbody class="divide-y divide-gray-700">
                              @foreach ($products as $product)
                                  <tr>
                                      <td class="px-4 py-3">
                                          <div class="flex items-center gap-4">
                                              <div class="w-12 h-12 bg-gray-600 rounded flex items-center justify-center shrink-0">
                                                  @php
                                                      $imagePath = $normalizeProductImage($product->first_image_url) ?? 'https://placehold.co/600x400';
                                                  @endphp
                                                  <img class="w-10 h-10 object-cover rounded"
                                                       src="{{ $imagePath }}"
                                                       alt="{{ $product->name }}"
                                                       onerror="this.src='https://placehold.co/600x400'"/>
                                              </div>
                                              <div>
                                                  <a href="{{ route('products.edit', $product->id) }}" class="hover:text-blue-400 font-medium">{{ $product->name }}</a>
                                                  <p class="text-xs text-gray-500">{{ $product->brand }}</p>
                                              </div>
                                          </div>
                                      </td>
                                      <td class="px-4 py-3">
                                          <span class="inline-block border border-gray-600 rounded-full px-2 text-xs text-gray-300">
                                              {{ $product->category->name ?? '-' }}
                                          </span><br>
                                          <span class="inline-block border border-gray-600 rounded-full px-2 text-xs text-gray-300 mt-1">
                                              {{ $product->condition }}
                                          </span>
                                      </td>
                                      <td class="px-4 py-3">Rp {{ number_format($product->pricing, 0, ',', '.') }}</td>
                                      <td class="px-4 py-3">{{ $product->stock }}</td>
                                      <td class="px-4 py-3">
                                          <span class="inline-block {{ $product->stock > 0 ? 'bg-green-500' : 'bg-red-500' }} rounded-full px-3 py-1 text-xs font-semibold">
                                              {{ $product->stock > 0 ? 'In Stock' : 'Out of Stock' }}
                                          </span>
                                      </td>
                                      <td class="px-4 py-3">
                                          <div class="flex gap-3 text-gray-400">
                                              <a href="{{ route('products.edit', $product->id) }}" aria-label="Edit" class="hover:text-gray-200"><i class="fas fa-pen"></i></a>
                                              <button aria-label="Delete" class="hover:text-gray-200" onclick="deleteProduct({{ $product->id }})"><i class="fas fa-trash"></i></button>
                                          </div>
                                      </td>
                                  </tr>
                              @endforeach
                          </tbody>
                      </table>
                  </div>

                  <!-- Mobile Cards -->
                  <div class="sm:hidden space-y-4">
                      @foreach ($products as $product)
                          <div class="bg-[#2c2c2c] border border-gray-700 rounded-lg p-4">
                              <div class="flex items-start gap-3 mb-3">
                                  <div class="w-16 h-16 bg-gray-600 rounded flex items-center justify-center shrink-0">
                                      @php
                                          $imagePath = $normalizeProductImage($product->first_image_url) ?? 'https://placehold.co/600x400';
                                      @endphp
                                      <img class="w-full h-full object-cover rounded"
                                           src="{{ $imagePath }}"
                                           alt="{{ $product->name }}"
                                           onerror="this.src='https://placehold.co/600x400'"/>
                                  </div>
                                  <div class="flex-1 min-w-0">
                                      <h2 class="font-semibold text-sm mb-1">{{ $product->name }}</h2>
                                      <p class="text-xs text-gray-500">{{ $product->brand }}</p>
                                      <span class="inline-block mt-2 px-2 py-1 rounded text-xs font-semibold {{ $product->stock > 0 ? 'bg-green-500' : 'bg-red-500' }}">
                                          {{ $product->stock > 0 ? 'In Stock' : 'Out of Stock' }}
                                      </span>
                                  </div>
                              </div>

                              <div class="space-y-2 text-sm mb-3 pb-3 border-b border-gray-700">
                                  <div class="flex justify-between"><span class="text-gray-400">Category:</span><span class="text-xs">{{ $product->category->name ?? '-' }}</span></div>
                                  <div class="flex justify-between"><span class="text-gray-400">Condition:</span><span class="text-xs">{{ $product->condition }}</span></div>
                                  <div class="flex justify-between"><span class="text-gray-400">Price:</span><span class="font-medium">Rp {{ number_format($product->pricing, 0, ',', '.') }}</span></div>
                                  <div class="flex justify-between"><span class="text-gray-400">Stock:</span><span class="font-medium">{{ $product->stock }}</span></div>
                              </div>

                              <div class="flex gap-2">
                                  <a href="{{ route('products.edit', $product->id) }}" class="flex-1 flex items-center justify-center gap-2 px-3 py-2 bg-gray-700 text-gray-300 rounded text-sm hover:bg-gray-600 transition">
                                      <i class="fas fa-pen text-xs"></i> Edit
                                  </a>
                                  <button onclick="deleteProduct({{ $product->id }})" class="flex-1 flex items-center justify-center gap-2 px-3 py-2 bg-gray-700 text-gray-300 rounded text-sm hover:bg-gray-600 transition">
                                      <i class="fas fa-trash text-xs"></i> Delete
                                  </button>
                              </div>
                          </div>
                      @endforeach
                  </div>

                  <!-- Pagination (clean, centered, no "Showing" text) -->
                  @php
                      $current = $products->currentPage();
                      $last    = $products->lastPage();
                      $start   = max(1, $current - 2);
                      $end     = min($last, $current + 2);
                  @endphp

                  @if ($last > 1)
                      <div class="mt-8 w-full flex justify-center">
                          <nav role="navigation" aria-label="Pagination" class="flex items-center justify-center">
                              <ul class="inline-flex items-stretch rounded-xl overflow-hidden border border-slate-700 bg-slate-800/40 backdrop-blur-sm shadow-lg shadow-black/20 divide-x divide-slate-700">
                                  {{-- Prev icon --}}
                                  @if ($current > 1)
                                      <li>
                                          <a href="{{ $products->url($current - 1) }}" rel="prev" aria-label="@lang('pagination.previous')"
                                             class="px-3 sm:px-4 h-10 sm:h-9 inline-flex items-center text-slate-200 hover:bg-slate-700/60 focus:outline-none focus:ring-2 focus:ring-blue-500/40">
                                              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                          </a>
                                      </li>
                                  @else
                                      <li>
                                          <span class="px-3 sm:px-4 h-10 sm:h-9 inline-flex items-center text-slate-500 bg-slate-900/40 select-none cursor-not-allowed">
                                              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                          </span>
                                      </li>
                                  @endif

                                  {{-- First + ellipsis --}}
                                  @if ($start > 1)
                                      <li>
                                          <a href="{{ $products->url(1) }}" class="px-3 sm:px-4 h-10 sm:h-9 inline-flex items-center justify-center text-slate-200 hover:bg-slate-700/60 focus:outline-none focus:ring-2 focus:ring-blue-500/40">
                                              <span class="text-xs">1</span>
                                          </a>
                                      </li>
                                      @if ($start > 2)
                                          <li><span class="px-3 sm:px-4 h-10 sm:h-9 inline-flex items-center text-slate-500 select-none">…</span></li>
                                      @endif
                                  @endif

                                  {{-- Window pages --}}
                                  @for ($i = $start; $i <= $end; $i++)
                                      @if ($i == $current)
                                          <li>
                                              <span class="px-3 sm:px-4 h-10 sm:h-9 inline-flex items-center justify-center text-blue-300 bg-blue-500/10 font-semibold select-none">
                                                  <span class="text-xs">{{ $i }}</span>
                                              </span>
                                          </li>
                                      @else
                                          <li>
                                              <a href="{{ $products->url($i) }}" class="px-3 sm:px-4 h-10 sm:h-9 inline-flex items-center justify-center text-slate-200 hover:bg-slate-700/60 focus:outline-none focus:ring-2 focus:ring-blue-500/40">
                                                  <span class="text-xs">{{ $i }}</span>
                                              </a>
                                          </li>
                                      @endif
                                  @endfor

                                  {{-- Ellipsis + last --}}
                                  @if ($end < $last)
                                      @if ($end < $last - 1)
                                          <li><span class="px-3 sm:px-4 h-10 sm:h-9 inline-flex items-center text-slate-500 select-none">…</span></li>
                                      @endif
                                      <li>
                                          <a href="{{ $products->url($last) }}" class="px-3 sm:px-4 h-10 sm:h-9 inline-flex items-center justify-center text-slate-200 hover:bg-slate-700/60 focus:outline-none focus:ring-2 focus:ring-blue-500/40">
                                              <span class="text-xs">{{ $last }}</span>
                                          </a>
                                      </li>
                                  @endif

                                  {{-- Next icon --}}
                                  @if ($current < $last)
                                      <li>
                                          <a href="{{ $products->url($current + 1) }}" rel="next" aria-label="@lang('pagination.next')"
                                             class="px-3 sm:px-4 h-10 sm:h-9 inline-flex items-center text-slate-200 hover:bg-slate-700/60 focus:outline-none focus:ring-2 focus:ring-blue-500/40">
                                              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                          </a>
                                      </li>
                                  @else
                                      <li>
                                          <span class="px-3 sm:px-4 h-10 sm:h-9 inline-flex items-center text-slate-500 bg-slate-900/40 select-none cursor-not-allowed">
                                              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                          </span>
                                      </li>
                                  @endif
                              </ul>
                          </nav>
                      </div>
                  @endif
                  <!-- /Pagination -->

              </div>
          </main>
      </div>
  </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function deleteProduct(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "This action cannot be undone!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel',
                background: '#222',
                color: '#fff'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/dashboard/products/${id}`;

                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    csrfToken.value = '{{ csrf_token() }}';
                    form.appendChild(csrfToken);

                    const methodField = document.createElement('input');
                    methodField.type = 'hidden';
                    methodField.name = '_method';
                    methodField.value = 'DELETE';
                    form.appendChild(methodField);

                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '{{ session('success') }}',
                showConfirmButton: false,
                timer: 3000,
                background: '#222',
                color: '#fff'
            });
        @endif

        @if (session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: '{{ session('error') }}',
                background: '#222',
                color: '#fff'
            });
        @endif
    </script>
@endpush
