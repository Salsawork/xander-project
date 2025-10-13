{{-- resources/views/partials/footer.blade.php --}}
@php $year = now()->year; @endphp

@once
  {{-- Font Awesome v5 (untuk kelas .fab .fas) --}}
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"
    integrity="sha512-dNmKU8fYV/6X0f2xRzYfU0Y8KpA1ziG+v7/7U8mX0T6q0nQp3T38q9e9i9r0qNwzG6nXw+0xV1m+0YF6Z9G7Ww=="
    crossorigin="anonymous"
    referrerpolicy="no-referrer"
  />
@endonce

<footer class="bg-[#111] text-white px-6 md:px-20 py-12">
  <div class="flex flex-col md:flex-row md:justify-between gap-12">
    {{-- BRAND + SOCIALS --}}
    <div class="md:w-1/5">
      <a href="{{ url('/') }}" aria-label="Go to Home" class="inline-block">
        <img
          src="{{ asset('images/big-logo.png') }}"
          alt="Xander Billiard Logo"
          class="mb-4 hover:opacity-90 transition-opacity"
          loading="lazy"
        />
      </a>

      <div class="flex items-center gap-4 text-gray-400">
        <a href="https://facebook.com/xanderbilliard" target="_blank" rel="noopener noreferrer" aria-label="Facebook" title="Facebook" class="hover:text-white transition-colors">
          <i class="fab fa-facebook-f"></i><span class="sr-only">Facebook</span>
        </a>

        {{-- IG diganti ke WhatsApp +62 812-8467-9921 --}}
        <a href="https://wa.me/6281284679921" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp" title="+62 812-8467-9921" class="hover:text-white transition-colors">
          <i class="fab fa-whatsapp"></i><span class="sr-only">WhatsApp: +62 812-8467-9921</span>
        </a>

        <a href="https://x.com/xanderbilliard" target="_blank" rel="noopener noreferrer" aria-label="X (Twitter)" title="X (Twitter)" class="hover:text-white transition-colors">
          <i class="fab fa-twitter"></i><span class="sr-only">X (Twitter)</span>
        </a>
        <a href="https://www.linkedin.com/company/xander-billiard" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn" title="LinkedIn" class="hover:text-white transition-colors">
          <i class="fab fa-linkedin-in"></i><span class="sr-only">LinkedIn</span>
        </a>
        <a href="https://www.youtube.com/@xanderbilliard" target="_blank" rel="noopener noreferrer" aria-label="YouTube" title="YouTube" class="hover:text-white transition-colors">
          <i class="fab fa-youtube"></i><span class="sr-only">YouTube</span>
        </a>
      </div>
    </div>

    {{-- LINKS GRID --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-sm">
      {{-- Features --}}
      <div>
        <h4 class="font-semibold mb-4">Features</h4>
        <ul class="space-y-2 text-gray-400">
          <li><a href="{{ route('products.landing') }}" class="hover:text-white">Product</a></li>
          <li><a href="{{ route('venues.index') }}" class="hover:text-white">Venue</a></li>
          <li><a href="{{ route('events.index') }}" class="hover:text-white">Event</a></li>
          <li><a href="{{ route('guideline.index') }}" class="hover:text-white">Guidelines</a></li>
          <li><a href="{{ route('community.index') }}" class="hover:text-white">Community</a></li>
        </ul>
      </div>

      {{-- Company (lengkap) --}}
      <div>
        <h4 class="font-semibold mb-4">Company</h4>
        <ul class="space-y-2 text-gray-400">
          <li><a href="{{ route('about') }}" class="hover:text-white">About Us</a></li>
          <li><a href="{{ route('company.careers') }}" class="hover:text-white">Careers</a></li>
          <li><a href="{{ route('company.partners') }}" class="hover:text-white">Partners</a></li>
          <li><a href="{{ route('company.press') }}" class="hover:text-white">Press &amp; Media</a></li>
          <li><a href="{{ route('blog.index') }}" class="hover:text-white">Blog</a></li>
        </ul>
      </div>

      {{-- Support --}}
      <div>
        <h4 class="font-semibold mb-4">Support</h4>
        <ul class="space-y-2 text-gray-400">
          <li><a href="#" class="hover:text-white">Help Center</a></li>
          <li><a href="#" class="hover:text-white">Report a Bug</a></li>
          <li><a href="#" class="hover:text-white">Chat Support</a></li>
        </ul>
      </div>

      {{-- Contact --}}
      <div>
        <h4 class="font-semibold mb-4">Contact Us</h4>
        <ul class="space-y-3 text-gray-400 text-sm">
          <li class="flex items-center gap-2">
            <i class="fas fa-envelope"></i>
            <a href="mailto:xanderbilliard@gmail.com" class="hover:text-white transition wrap">xanderbilliard@gmail.com</a>
          </li>
          <li class="flex items-center gap-2">
            <i class="fas fa-phone-alt"></i>
            <a href="https://wa.me/6281284679921" target="_blank" rel="noopener noreferrer" class="hover:text-white transition">
              +62 812-8467-9921
            </a>
          </li>
          <li class="flex items-start gap-2">
            <i class="fas fa-map-marker-alt mt-1"></i>
            <span>Duri Kepa, Jakarta Barat</span>
          </li>
        </ul>
      </div>
    </div>
  </div>

  {{-- Bottom bar --}}
  {{-- <div class="mt-12 pt-6 border-t border-gray-700 flex flex-col md:flex-row justify-between text-sm text-gray-400">
    <p>&copy; {{ $year }} Xander Billiard</p>
    <div class="space-x-4">
      <a href="#" class="hover:underline">All Rights Reserved</a>
      <a href="#" class="hover:underline">Terms and Conditions</a>
      <a href="#" class="hover:underline">Privacy Policy</a>
    </div>
  </div> --}}
</footer>

<style>
  .wrap { overflow-wrap: anywhere; word-break: break-word; }
</style>
