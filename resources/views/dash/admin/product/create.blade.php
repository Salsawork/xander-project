@extends('app')
@section('title', 'Admin Dashboard - Products List')

@section('content')
<div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
    <div class="flex flex-1 min-h-0">
        @include('partials.sidebar')
        <main class="flex-1 overflow-y-auto min-w-0 mb-8 py-8">
            @include('partials.topbar')

            <h1 class="text-3xl font-extrabold my-8 px-4 sm:px-8">
                Product Detail
            </h1>

            <form method="POST" action="{{ route('products.store') }}"
                  class="flex flex-col lg:flex-row lg:space-x-8 px-4 sm:px-8 gap-8">
                @csrf

                <!-- General Information -->
                <section aria-labelledby="general-info-title"
                    class="bg-[#262626] rounded-lg p-6 sm:p-8 flex-1 w-full space-y-8">
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
                                class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500" />
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 mb-1" for="product-description">
                                Product Description
                            </label>
                            <textarea name="description" id="product-description" rows="5"
                                placeholder="Enter product description"
                                class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white resize-none focus:outline-none focus:ring-1 focus:ring-blue-500"></textarea>
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
                                    <option disabled selected>Please choose category</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs text-gray-400 mb-1" for="brand">
                                    Product Brand
                                </label>
                                <select name="brand" id="brand"
                                    class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                                    <option disabled selected>Please choose brand</option>
                                    @foreach (['Mezz', 'Predator', 'Cuetec', 'Other'] as $value)
                                        <option value="{{ $value }}">{{ $value }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs text-gray-400 mb-1" for="condition">
                                    Product Condition
                                </label>
                                <select name="condition" id="condition"
                                    class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                                    <option disabled selected>Please choose condition</option>
                                    @foreach (['new', 'used'] as $value)
                                        <option value="{{ $value }}">{{ $value }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Inventory -->
                    <div>
                        <h3 class="text-lg font-bold border-b border-gray-600 pb-2 mb-4">
                            Inventory
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs text-gray-400 mb-1" for="quantity">
                                    Quantity
                                </label>
                                <input name="quantity" id="quantity" type="number" placeholder="0"
                                    class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500" />
                            </div>
                            <div>
                                <label class="block text-xs text-gray-400 mb-1" for="sku">
                                    SKU (Optional)
                                </label>
                                <input name="sku" id="sku" type="text" placeholder="Enter SKU number"
                                    class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500" />
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Right Panel -->
                <section class="flex flex-col space-y-8 w-full max-w-lg">
                    <!-- Product Image -->
                    <div class="bg-[#262626] rounded-lg p-6 space-y-4">
                        <h2 class="text-lg font-bold border-b border-gray-600 pb-2">Product Image</h2>
                        <div class="flex flex-wrap gap-4">
                            <input type="hidden" name="images[]" id="input-image-1" />
                            <img id="product-image-1"
                                src="https://placehold.co/400x600?text=No+Image"
                                alt="Image 1"
                                class="rounded-md w-20 h-28 object-cover" />

                            <input type="hidden" name="images[]" id="input-image-2" />
                            <img id="product-image-2"
                                src="https://placehold.co/400x600?text=No+Image"
                                alt="Image 2"
                                class="rounded-md w-20 h-28 object-cover" />

                            <div class="flex flex-col gap-2">
                                <input type="hidden" name="images[]" id="input-image-3" />
                                <img id="product-image-3"
                                    src="https://placehold.co/400x400?text=No+Image"
                                    alt="Image 3"
                                    class="rounded-md w-12 h-12 object-cover" />

                                <input type="hidden" name="images[]" id="input-image-4" />
                                <img id="product-image-4"
                                    src="https://placehold.co/400x400?text=No+Image"
                                    alt="Image 4"
                                    class="rounded-md w-12 h-12 object-cover" />
                            </div>

                            <input type="file" id="imageUpload" class="hidden" accept="image/*" multiple
                                onchange="uploadFiles(event)">
                            <button type="button"
                                onclick="document.getElementById('imageUpload').click()"
                                class="w-20 h-28 rounded-md border border-gray-600 flex items-center justify-center text-gray-400 hover:text-white">
                                <span class="text-3xl font-light">+</span>
                            </button>
                        </div>
                    </div>

                    <!-- Shipping & Delivery -->
                    <div class="bg-[#262626] rounded-lg p-6 space-y-4">
                        <h2 class="text-lg font-bold border-b border-gray-600 pb-2">Shipping and Delivery</h2>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs text-gray-400 mb-1" for="items-weight">
                                    Items Weight (gram)
                                </label>
                                <input name="weight" id="items-weight" type="text" placeholder="0"
                                    class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500" />
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1" for="length">Length (cm)</label>
                                    <input name="length" id="length" type="text" placeholder="0"
                                        class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500" />
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1" for="breadth">Breadth (cm)</label>
                                    <input name="breadth" id="breadth" type="text" placeholder="0"
                                        class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500" />
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1" for="width">Width (cm)</label>
                                    <input name="width" id="width" type="text" placeholder="0"
                                        class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pricing -->
                    <div class="bg-[#262626] rounded-lg p-6 space-y-4">
                        <h2 class="text-lg font-bold border-b border-gray-600 pb-2">Pricing</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs text-gray-400 mb-1" for="price">Price (IDR)</label>
                                <input name="pricing" id="price" type="text" placeholder="0"
                                    class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500" />
                            </div>
                            <div>
                                <label class="block text-xs text-gray-400 mb-1" for="discount">Discount (%)</label>
                                <input name="discount" id="discount" type="text" placeholder="0"
                                    class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-2 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500" />
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
@endsection
