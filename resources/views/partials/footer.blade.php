<footer class="bg-[#111] text-white px-6 md:px-20 py-12">
    <div class="flex flex-col md:flex-row md:justify-between gap-12">
        <div class="md:w-1/5">
            <img src="{{ asset('images/big-logo.png') }}" alt="Logo" class="mb-4" />
            <div class="flex space-x-4 text-gray-400">
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-linkedin-in"></i></a>
                <a href="#"><i class="fab fa-youtube"></i></a>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-sm">
            <div>
                <h4 class="font-semibold mb-4">Features</h4>
                <ul class="space-y-2 text-gray-400">
                    <li><a href="{{ route('products.landing') }}">Product</a></li>
                    <li><a href="{{ route('venues.index') }}">Venue</a></li>
                    <li><a href="{{ route('events.index') }}">Event</a></li>
                    <li><a href="#">Guidelines</a></li>
                    <li><a href="https://chat.whatsapp.com/BXUFjPpsG9QArMalFEY2IB">Community</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-semibold mb-4">Company</h4>
                <ul class="space-y-2 text-gray-400">
                    <li><a href="{{ route('about') }}">About Us</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-semibold mb-4">Support</h4>
                <ul class="space-y-2 text-gray-400">
                    <li><a href="#">Help center</a></li>
                    <li><a href="#">Report a bug</a></li>
                    <li><a href="#">Chat support</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-semibold mb-4">Contact Us</h4>
                <ul class="space-y-3 text-gray-400 text-sm">
                    <li class="flex items-center gap-2"><i class="fas fa-envelope"></i> xanderbilliard@gmail.com
                    </li>
                    <li class="flex items-center gap-2">
                        <i class="fas fa-phone-alt"></i>
                        <a href="https://wa.me/6281284679921" target="_blank"
                            class="hover:text-white transition duration-200">+62 812-8467-9921</a>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="fas fa-map-marker-alt mt-1"></i>
                        <span>Duri Kepa,Jakarta Barat
                        </span>
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
