@extends('app')
@section('title', 'User Dashboard - Profile')

@section('content')
    <div class="flex flex-col min-h-screen bg-neutral-900 text-white font-sans">
        <div class="flex flex-1 min-h-0">
            @include('partials.sidebar')
            <main class="flex-1 overflow-y-auto min-w-0 mb-8">
                @include('partials.topbar')
                <section aria-label="User profile information" class="bg-gray-800 rounded-xl mt-20 mx-8 p-8 shadow-lg">
                    <div class="flex flex-col md:flex-row md:items-center md:space-x-8 mb-8">
                        <img alt="Portrait of a young woman with red hair in a dimly lit room"
                            class="w-20 h-20 rounded-full object-cover mb-4 md:mb-0" height="80"
                            src="https://storage.googleapis.com/a1aa/image/853fca6f-7f35-41d8-d0be-509c46584a50.jpg"
                            width="80" />
                        <div>
                            <h2 class="text-white font-bold text-lg">
                                {{ auth()->user()->name ?? 'Jhon Doe' }}
                            </h2>
                            <p class="text-gray-300 text-sm">
                                {{ auth()->user()->username ?? 'jhon doe' }}
                            </p>
                        </div>
                    </div>
                    <form aria-label="Edit profile form" class="flex flex-col md:flex-row md:space-x-8" method="post" action="{{ route('profile.update') }}">
                        @csrf
                        <div class="flex flex-col space-y-4 md:w-1/2">
                            <div>
                                <label class="block text-gray-400 text-xs mb-1" for="firstName">
                                    Name
                                </label>
                                <input
                                    class="w-full rounded-md border border-gray-600 bg-gray-800 text-gray-200 text-sm px-3 py-1.5 focus:outline-none focus:ring-1 focus:ring-blue-600"
                                    name="name"
                                    id="firstName" type="text" value="{{ auth()->user()->name ?? 'Jhon Doe' }}" />
                            </div>
                        </div>
                        <div class="pl-8 mt-6 md:mt-0 md:w-1/2 space-y-4">
                            <div>
                                <label class="block text-gray-400 text-xs mb-1" for="gender">
                                    Username/Email/Phone
                                </label>
                                <input
                                    class="w-full rounded-md border border-gray-600 bg-gray-800 text-gray-200 text-sm px-3 py-1.5 focus:outline-none focus:ring-1 focus:ring-blue-600"
                                    name="username"
                                    id="lastName" type="text" value="{{ auth()->user()->username ?? 'jhon doe' }}" />
                            </div>
                            <div class="flex justify-end">
                                <button
                                    class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    type="submit">
                                    Save
                                </button>
                            </div>
                        </div>
                    </form>
                </section>
                {{-- <section aria-label="Payment methods" class="bg-gray-800 rounded-xl p-8 mx-8 shadow-lg mt-8">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-white font-bold text-lg">
                            Payment Method
                        </h3>
                        <button
                            class="text-blue-500 border border-blue-500 rounded px-3 py-1 text-sm hover:bg-blue-600 hover:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 flex items-center space-x-1"
                            type="button">
                            <i class="fas fa-plus">
                            </i>
                            <span>
                                Add Payment
                            </span>
                        </button>
                    </div>
                    <hr class="border-gray-700 mb-6" />
                    <ul class="space-y-6">
                        <li class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <img alt="OVO payment method logo in purple and white"
                                    class="w-18 h-9 object-contain rounded" height="36"
                                    src="https://storage.googleapis.com/a1aa/image/ae8e12d0-71d2-4f16-b90b-281d434c51ac.jpg"
                                    width="72" />
                                <span class="text-gray-300 text-sm">
                                    OVO
                                </span>
                            </div>
                            <div class="text-gray-400 text-xs tracking-widest">
                                +62 *** **** ****
                            </div>
                            <button aria-label="Delete OVO payment method"
                                class="text-gray-500 hover:text-gray-400 focus:outline-none">
                                <i class="fas fa-trash-alt">
                                </i>
                            </button>
                        </li>
                        <li class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <img alt="Master Card payment method logo with red and orange circles"
                                    class="w-18 h-9 object-contain rounded" height="36"
                                    src="https://storage.googleapis.com/a1aa/image/3aaeed85-aadc-4634-2fa7-addd4daa6ec0.jpg"
                                    width="72" />
                                <span class="text-gray-300 text-sm">
                                    Master Card
                                </span>
                            </div>
                            <div class="text-gray-400 text-xs tracking-widest">
                                **** **** **** 1234
                            </div>
                            <button aria-label="Delete Master Card payment method"
                                class="text-gray-500 hover:text-gray-400 focus:outline-none">
                                <i class="fas fa-trash-alt">
                                </i>
                            </button>
                        </li>
                    </ul>
                </section> --}}
            </main>
        </div>
    </div>
    </section>
@endsection
