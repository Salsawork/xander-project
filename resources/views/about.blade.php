<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Xander Billiard - Where Passion Meets Precision</title>
    @vite('resources/css/app.css')
</head>

<body class="bg-gray-950 text-white">
    <!-- Navigation -->
    @include('partials.navbar')

    <!-- Hero Section -->
    <section class="relative bg-gray-900">
        <div class="absolute inset-0 overflow-hidden">
            <img src="{{ asset('images/billiard-hero.jpg') }}" alt="Billiard Table"
                class="w-full h-full object-cover opacity-30">
        </div>
        <div class="relative max-w-7xl mx-auto px-6 py-16">
            <div class="flex flex-col space-y-2">
                <div class="flex items-center space-x-2 text-sm">
                    <a href="/" class="text-gray-400 hover:text-white">Home</a>
                    <span class="text-gray-600">/</span>
                    <span class="text-gray-400">About Us</span>
                </div>
                <h1 class="text-4xl md:text-5xl font-bold mt-2 mb-4">Xander Billiard – Where Passion Meets Precision
                </h1>
            </div>
        </div>
    </section>

    <!-- Our Story Section -->
    <section class="py-16 border-b border-gray-800">
        <div class="max-w-7xl mx-auto px-6">
            <div class="flex flex-col md:flex-row gap-12">
                <div class="md:w-2/3">
                    <h2 class="text-3xl font-bold mb-8">Our Story: A Legacy of Excellence</h2>
                    <p class="text-gray-300 mb-4">
                        Founded by a group of billiard lovers who wanted to make a difference in the sport, Xander
                        Billiard was built with dedication, precision, and a deep appreciation for the game. We
                        understand that every cue, every shot, and every table tells a story—stories of skill, strategy,
                        and the pursuit of excellence. That's why we are committed to providing only the best equipment,
                        services, and resources to help players perform at their peak.
                    </p>
                    <p class="text-gray-300 mb-4">
                        From custom cue repairs and precision balancing, to advanced training resources and expert
                        coaching, we've got everything a billiard player needs to elevate their game.
                    </p>
                    <p class="text-gray-300">
                        But we offer more than just products and services—we create an environment where players can
                        grow, learn, and connect with others who share the same passion. Through our training programs,
                        community events, and mentorship initiatives, we provide a space where players can sharpen their
                        skills and gain valuable insights from seasoned professionals. Whether you're just starting out
                        or looking to refine your techniques, we are dedicated to helping every player unlock their full
                        potential. At Xander Billiard, we believe in the power of community, precision, and unwavering
                        dedication to the game.
                    </p>
                </div>
                <div class="md:w-1/3">
                    <img src="{{ asset('images/about/player1.png') }}" alt="Billiard Player"
                        class="w-full h-auto rounded-lg">
                </div>
            </div>
        </div>
    </section>

    <!-- Billiard Balls Image -->
    <section class="py-12">
        <div class="max-w-7xl mx-auto px-6">
            <img src="{{ asset('images/about/player2.png') }}" alt="Billiard Balls" class="w-full h-auto rounded-lg">
        </div>
    </section>

    <!-- Two Column Section -->
    <section class="py-16 border-b border-gray-800">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid md:grid-cols-2 gap-12">
                <div>
                    <h2 class="text-2xl font-bold mb-2">How We Operate:</h2>
                    <h3 class="text-3xl font-bold mb-6">A Commitment to Quality and Community</h3>
                    <p class="text-gray-300">
                        At Xander Billiard, we do things differently. We don't just sell products—we cultivate
                        experiences. Our shop offers an extensive range of carefully curated billiard supplies, while
                        our venue services ensure players have access to top-tier facilities. Our technical team
                        provides hands-on services like cue balancing, tip installation, and refinishing, so every piece
                        of equipment is in peak condition. We also believe in fostering a strong community by organizing
                        events, sparring sessions, and educational content to help players grow and improve.
                    </p>
                </div>
                <div>
                    <h2 class="text-2xl font-bold mb-2">Who We Serve:</h2>
                    <h3 class="text-3xl font-bold mb-6">Every Player, Every Level</h3>
                    <p class="text-gray-300">
                        Whether you're a seasoned professional, a casual player, or someone just discovering the world
                        of billiards, Xander Billiard is here for you. We provide high-quality gear, expert maintenance,
                        and a welcoming space for practice, competition, and learning. We also connect athletes with
                        opportunities, helping them find sparring partners, training sessions, and even competitive
                        events to test their skills.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section class="py-16 border-b border-gray-800">
        <div class="max-w-7xl mx-auto px-6">
            <div class="flex flex-col md:flex-row gap-12">
                <div class="md:w-1/3">
                    <h2 class="text-3xl font-bold mb-6">The Faces Behind Xander Billiard</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <img src="{{ asset('images/about/player3.png') }}" alt="Team Member"
                            class="w-full h-auto rounded-lg">
                        <img src="{{ asset('images/about/player4.png') }}" alt="Team Member"
                            class="w-full h-auto rounded-lg">
                        <img src="{{ asset('images/about/player5.png') }}" alt="Team Member"
                            class="w-full h-auto rounded-lg">
                        <img src="{{ asset('images/about/player6.png') }}" alt="Team Member"
                            class="w-full h-auto rounded-lg">
                    </div>
                </div>
                <div class="md:w-2/3">
                    <p class="text-gray-300 mt-8 md:mt-16">
                        Xander Billiard isn't just a brand—it's a family. Our team is made up of passionate players,
                        skilled technicians, and dedicated professionals who live and breathe billiards. We believe that
                        the best way to serve our customers is by sharing our knowledge and enthusiasm for the game.
                        Whether you meet us in-store, online, or at one of our many events, you'll always be greeted by
                        people who truly care about helping you become a better player.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Join Community Section -->
    <section class="py-16 border-b border-gray-800">
        <div class="max-w-7xl mx-auto px-6">
            <div class="flex flex-col md:flex-row gap-12">
                <div class="md:w-1/3">
                    <img src="{{ asset('images/about/player7.png') }}" alt="Billiard Community"
                        class="w-full h-auto rounded-lg">
                </div>
                <div class="md:w-2/3">
                    <h2 class="text-3xl font-bold mb-6">Join the Xander Billiard Community</h2>
                    <p class="text-gray-300 mb-6">
                        We invite you to become a part of our growing community. Follow us for the latest billiard news,
                        join our training sessions, or stop by our venue to experience the game like never before.
                        Whether you're looking to refine your skills, connect with fellow players, or find the best gear
                        in the game, Xander Billiard is your ultimate destination.
                    </p>
                    <p class="text-gray-300 mb-8">
                        Let's keep the passion alive, one shot at a time. Welcome to Xander Billiard!
                    </p>
                    <a href="#"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-md inline-block">Join
                        Now!</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    @include('partials.footer')
</body>

</html>
