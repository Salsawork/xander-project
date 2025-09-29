<footer class="bg-[#111] text-white px-6 md:px-20 py-12">
  <div class="flex flex-col md:flex-row md:justify-between gap-12">
    <div class="md:w-1/5">
      <a href="{{ url('/') }}" aria-label="Go to Home" class="inline-block">
        <img src="{{ asset('images/big-logo.png') }}" alt="Logo" class="mb-4 hover:opacity-90 transition-opacity" />
      </a>

      <!-- Socials -->
      <div class="flex items-center gap-4 text-gray-400">
        <a href="https://facebook.com/xanderbilliard"
           target="_blank" rel="noopener"
           aria-label="Facebook" title="Facebook"
           class="hover:text-white transition-colors">
          <i class="fab fa-facebook-f"></i>
        </a>

        <a href="https://instagram.com/xanderbilliard"
           target="_blank" rel="noopener"
           aria-label="Instagram" title="Instagram"
           class="hover:text-white transition-colors">
          <i class="fab fa-instagram"></i>
        </a>

        <!-- X (Twitter). Jika pakai FA v6: ganti ke 'fa-x-twitter' -->
        <a href="https://x.com/xanderbilliard"
           target="_blank" rel="noopener"
           aria-label="X (Twitter)" title="X (Twitter)"
           class="hover:text-white transition-colors">
          <i class="fab fa-twitter"></i>
        </a>

        <a href="https://www.linkedin.com/company/xander-billiard"
           target="_blank" rel="noopener"
           aria-label="LinkedIn" title="LinkedIn"
           class="hover:text-white transition-colors">
          <i class="fab fa-linkedin-in"></i>
        </a>

        <a href="https://www.youtube.com/@xanderbilliard"
           target="_blank" rel="noopener"
           aria-label="YouTube" title="YouTube"
           class="hover:text-white transition-colors">
          <i class="fab fa-youtube"></i>
        </a>
      </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-sm">
      <div>
        <h4 class="font-semibold mb-4">Features</h4>
        <ul class="space-y-2 text-gray-400">
          <li><a href="{{ route('products.landing') }}" class="hover:text-white">Product</a></li>
          <li><a href="{{ route('venues.index') }}" class="hover:text-white">Venue</a></li>
          <li><a href="{{ route('events.index') }}" class="hover:text-white">Event</a></li>
          <li><a href="#" class="hover:text-white">Guidelines</a></li>
          <li><a href="https://chat.whatsapp.com/BXUFjPpsG9QArMalFEY2IB" class="hover:text-white">Community</a></li>
        </ul>
      </div>
      <div>
        <h4 class="font-semibold mb-4">Company</h4>
        <ul class="space-y-2 text-gray-400">
          <li><a href="{{ route('about') }}" class="hover:text-white">About Us</a></li>
        </ul>
      </div>
      <div>
        <h4 class="font-semibold mb-4">Support</h4>
        <ul class="space-y-2 text-gray-400">
          <li><a href="#" class="hover:text-white">Help center</a></li>
          <li><a href="#" class="hover:text-white">Report a bug</a></li>
          <li><a href="#" class="hover:text-white">Chat support</a></li>
        </ul>
      </div>
      <div>
        <h4 class="font-semibold mb-4">Contact Us</h4>
        <ul class="space-y-3 text-gray-400 text-sm">
          <li class="flex items-center gap-2"><i class="fas fa-envelope"></i> <span class="wrap">xanderbilliard@gmail. com</span></li>
          <li class="flex items-center gap-2">
            <i class="fas fa-phone-alt"></i>
            <a href="https://wa.me/6281284679921" target="_blank" class="hover:text-white transition">+62 812-8467-9921</a>
          </li>
          <li class="flex items-start gap-2">
            <i class="fas fa-map-marker-alt mt-1"></i>
            <span>Duri Kepa, Jakarta Barat</span>
          </li>
        </ul>
      </div>
    </div>
  </div>

  <div class="mt-12 pt-6 border-t border-gray-700 flex flex-col md:flex-row justify-between text-sm text-gray-400">
    <p>&copy; Copyright Xander Billiard</p>
    <div class="space-x-4">
      <a href="#" class="hover:underline">All Rights Reserved</a>
      <a href="#" class="hover:underline">Terms and Conditions</a>
      <a href="#" class="hover:underline">Privacy Policy</a>
    </div>
  </div>
</footer>

<style>
  .wrap{
    text-wrap: wrap;
  }
</style>
