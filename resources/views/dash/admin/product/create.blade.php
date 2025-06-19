@extends('app')
@section('title', 'Admin Dashboard - Products List')

@section('content')
    <div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
        <div class="flex flex-1 min-h-0">
            @include('partials.sidebar')
            <main class="flex-1 overflow-y-auto min-w-0 mb-8 py-8">
                @include('partials.topbar')
                <h1 class="text-3xl font-extrabold my-8 px-8">
                    Product Detail
                </h1>
                <form method="POST" action="{{ route('products.store') }}"
                    class="flex flex-col lg:flex-row lg:space-x-8 px-8">
                    @csrf
                    <section aria-labelledby="general-info-title"
                        class="bg-[#262626] rounded-lg p-8 flex-1 max-w-lg space-y-8">
                        <h2 class="text-lg font-bold border-b border-gray-600 pb-2" id="general-info-title">
                            General Information
                        </h2>
                        <div class="space-y-6">
                            <div>
                                <label class="block text-xs text-gray-400 mb-1" for="product-name">
                                    Product Name
                                </label>
                                <input name="name"
                                    class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-1.5 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                    id="product-name" type="text" placeholder="Enter product name" />
                            </div>
                            <div>
                                <label class="block text-xs text-gray-400 mb-1" for="product-description">
                                    Product Description
                                </label>
                                <textarea name="description"
                                    class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-1.5 text-xs text-white resize-none focus:outline-none focus:ring-1 focus:ring-blue-500"
                                    id="product-description" rows="5" placeholder="Enter product description"></textarea>
                            </div>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold border-b border-gray-600 pb-2 mb-4">
                                Category
                            </h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1" for="category">
                                        Product Category
                                    </label>
                                    <select name="category_id"
                                        class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-1.5 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                        id="category">
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
                                    <select name="brand"
                                        class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-1.5 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                        id="brand">
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
                                    <select name="condition"
                                        class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-1.5 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                        id="condition">
                                        <option disabled selected>Please choosec condition</option>
                                        @foreach (['new', 'used'] as $value)
                                            <option value="{{ $value }}">{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold border-b border-gray-600 pb-2 mb-4">
                                Inventory
                            </h3>
                            <div class="flex space-x-8 max-w-md">
                                <div class="flex-1">
                                    <label class="block text-xs text-gray-400 mb-1" for="quantity">
                                        Quantity
                                    </label>
                                    <input name="quantity"
                                        class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-1.5 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                        id="quantity" type="number" placeholder="0" />
                                </div>
                                <div class="flex-1">
                                    <label class="block text-xs text-gray-400 mb-1" for="sku">
                                        SKU (Optional)
                                    </label>
                                    <input name="sku"
                                        class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-1.5 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                        id="sku" type="text" placeholder="Enter SKU number" />
                                </div>
                            </div>
                        </div>
                    </section>
                    <section class="flex flex-col space-y-8 mt-8 lg:mt-0 w-full max-w-lg">
                        <div aria-labelledby="product-image-title" class="bg-[#262626] rounded-lg p-6 space-y-4">
                            <h2 class="text-lg font-bold border-b border-gray-600 pb-2" id="product-image-title">
                                Product Image
                            </h2>
                            <div class="flex space-x-4">
                                <input type="hidden" name="images[]" id="input-image-1" />
                                <img id="product-image-1" alt="Pool cue stick laying diagonally on a bright blue background"
                                    class="rounded-md w-20 h-30 object-cover flex-shrink-0" height="120"
                                    src="https://placehold.co/400x600?text=No+Image" width="80" />
                                <input type="hidden" name="images[]" id="input-image-2" />
                                <img name="images[]" id="product-image-2"
                                    alt="Close-up of a black pool cue stick on a bright blue background"
                                    class="rounded-md w-20 h-30 object-cover flex-shrink-0" height="120"
                                    src="https://placehold.co/400x600?text=No+Image" width="80" />
                                <div class="flex flex-col space-y-2">
                                    <input type="hidden" name="images[]" id="input-image-3" />
                                    <img name="images[]" id="product-image-3"
                                        alt="Close-up image of the joint and tip of a pool cue on a bright blue background"
                                        class="rounded-md w-10 h-10 object-cover flex-shrink-0" height="40"
                                        src="https://placehold.co/400x400?text=No+Image" width="40" />
                                    <input type="hidden" name="images[]" id="input-image-4" />
                                    <img name="images[]" id="product-image-4"
                                        alt="Close-up image of the tip of a pool cue on a bright blue background"
                                        class="rounded-md w-10 h-10 object-cover flex-shrink-0" height="40"
                                        src="https://placehold.co/400x400?text=No+Image" width="40" />
                                </div>
                                <input type="file" id="imageUpload" class="hidden" accept="image/*" multiple
                                    onchange="uploadFiles(event)">
                                <button aria-label="Add new product image" type="button"
                                    onclick="document.getElementById('imageUpload').click()"
                                    class="w-20 h-30 rounded-md border border-gray-600 flex items-center justify-center text-gray-400 hover:text-white focus:outline-none">
                                    <span class="text-3xl font-light select-none">
                                        +
                                    </span>
                                </button>
                            </div>
                        </div>
                        <div aria-labelledby="shipping-delivery-title" class="bg-[#262626] rounded-lg p-6 space-y-4">
                            <h2 class="text-lg font-bold border-b border-gray-600 pb-2" id="shipping-delivery-title">
                                Shipping and Delivery
                            </h2>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1" for="items-weight">
                                        Items Weight (gram)
                                    </label>
                                    <input name="weight"
                                        class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-1.5 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                        id="items-weight" type="text" placeholder="0" />
                                </div>
                                <div class="flex space-x-4 max-w-md">
                                    <div class="flex-1">
                                        <label class="block text-xs text-gray-400 mb-1" for="length">
                                            Length (cm)
                                        </label>
                                        <input name="length"
                                            class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-1.5 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                            id="length" type="text" placeholder="0" />
                                    </div>
                                    <div class="flex-1">
                                        <label class="block text-xs text-gray-400 mb-1" for="breadth">
                                            Breadth (cm)
                                        </label>
                                        <input name="breadth"
                                            class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-1.5 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                            id="breadth" type="text" placeholder="0" />
                                    </div>
                                    <div class="flex-1">
                                        <label class="block text-xs text-gray-400 mb-1" for="width">
                                            Width (cm)
                                        </label>
                                        <input name="width"
                                            class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-1.5 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                            id="width" type="text" placeholder="0" />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div aria-labelledby="pricing-title" class="bg-[#262626] rounded-lg p-6 space-y-4">
                            <h2 class="text-lg font-bold border-b border-gray-600 pb-2" id="pricing-title">
                                Pricing
                            </h2>
                            <div class="flex space-x-8 max-w-md">
                                <div class="flex-1">
                                    <label class="block text-xs text-gray-400 mb-1" for="price">
                                        Price (IDR)
                                    </label>
                                    <input name="pricing"
                                        class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-1.5 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                        id="price" type="text" placeholder="0" />
                                </div>
                                <div class="flex-1 max-w-[80px]">
                                    <label class="block text-xs text-gray-400 mb-1" for="discount">
                                        Discount (%)
                                    </label>
                                    <input name="discount"
                                        class="w-full rounded-md border border-gray-600 bg-[#262626] px-3 py-1.5 text-sm text-white focus:outline-none focus:ring-1 focus:ring-blue-500"
                                        id="discount" type="text" placeholder="0" />
                                </div>
                            </div>
                        </div>
                        <div class="flex space-x-4 justify-end">
                            <button
                                class="px-6 py-2 border border-red-600 text-red-600 rounded-md hover:bg-red-600 hover:text-white transition"
                                type="reset">
                                Discard
                            </button>
                            <button class="px-6 py-2 bg-[#0a8cff] rounded-md hover:bg-[#0077e6] transition"
                                type="submit">
                                Save
                            </button>
                        </div>
                    </section>
                </form>

            </main>
        </div>
    </div>
@endsection
