<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Xander Billiard - Where Passion Meets Precision</title>
    @vite('resources/css/app.css')

    <style>
        :root {
            color-scheme: dark;
        }

        /* Kunci warna & tinggi root */
        html,
        body {
            height: 100%;
            background: #0a0a0a;
        }

        /* Matikan scroll di root; gunakan #page-root sebagai scroller tunggal */
        html,
        body {
            overflow: hidden;
            overscroll-behavior: none;
        }

        /* Satu-satunya container yang boleh scroll + background konten */
        #page-root {
            height: 100%;
            min-height: 100svh;
            overflow-y: auto;
            overscroll-behavior: contain;
            -webkit-overflow-scrolling: touch;

            /* BACKGROUND isi konten */
            background: #0a0a0a url('{{ asset('images/bg/background_3.png') }}') center / cover no-repeat;
        }

        /* Hindari white gap horizontal */
        html,
        body,
        #page-root {
            overflow-x: hidden;
        }

        @supports (overflow: clip) {

            html,
            body,
            #page-root {
                overflow-x: clip;
            }
        }

        /* Image rendering */
        img {
            display: block;
        }

        /* ==== Style khusus "Our Story" agar mirip screenshot ==== */
        .story-section {
            position: relative;
        }

        .story-section::before {
            /* wedge/diagonal di kanan atas */
            content: "";
            position: absolute;
            inset: 0 0 auto auto;
            /* top-right */
            width: min(55vw, 920px);
            height: 150px;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.10), rgba(255, 255, 255, 0.04));
            opacity: .15;
            clip-path: polygon(100% 0, 0 0, 100% 100%);
            pointer-events: none;
        }

        /* Garis tipis di bawah judul (pakai elemen flex .rule untuk kompat) */
        .rule {
            height: 1px;
            background: rgb(75 85 99 / 1);
        }

        /* gray-600 */

        /* ========== PERBAIKAN MOBILE ========== */
        @media (max-width: 768px) {
            /* Hero Section Mobile */
            .hero-section {
                padding: 4rem 1.5rem !important;
                text-align: center;
                /* FOKUS KE BOLA di mobile */
                background-position: 88% 50% !important;
            }

            .hero-section h2 {
                font-size: 1.75rem !important;
                line-height: 1.2;
            }

            /* Breadcrumb Mobile */
            .breadcrumb {
                font-size: 0.875rem;
                justify-content: center;
            }

            /* Our Story Section Mobile */
            .story-section {
                padding: 3rem 1.5rem !important;
            }

            .story-section .flex {
                flex-direction: column;
                gap: 1rem;
            }

            .story-section h2 {
                font-size: 1.75rem !important;
                text-align: center;
            }

            .story-section .rule {
                width: 100%;
                margin-top: 0.5rem;
            }

            .story-section .grid {
                grid-template-columns: 1fr !important;
                gap: 2rem;
            }

            .story-section .text-lg {
                font-size: 1rem;
                line-height: 1.6;
            }

            /* Billiard Balls Section Mobile */
            .billiard-balls-section {
                padding: 2rem 1.5rem !important;
            }

            /* Two Column Section Mobile */
            .two-column-section {
                padding: 2rem 1.5rem !important;
            }

            .two-column-section .grid {
                grid-template-columns: 1fr !important;
                gap: 2rem;
            }

            .two-column-section h3,
            .two-column-section h4 {
                font-size: 1.5rem !important;
                text-align: center;
            }

            /* Team Section Mobile */
            .team-section {
                padding: 3rem 1.5rem !important;
            }

            .team-section .grid {
                grid-template-columns: 1fr !important;
                gap: 2rem;
            }

            .team-member-img {
                height: 280px !important;
            }

            .team-heading-section .grid {
                grid-template-columns: 1fr !important;
                gap: 1.5rem;
            }

            .team-heading-section h2 {
                font-size: 1.75rem !important;
                text-align: center;
                margin-bottom: 0 !important;
            }

            .team-heading-section p {
                margin-left: 0 !important;
                text-align: center;
            }

            /* Join Community Section Mobile */
            .join-community-section {
                padding: 3rem 1.5rem !important;
            }

            .join-community-section .flex {
                flex-direction: column;
                gap: 2rem;
            }

            .join-community-section h2 {
                font-size: 1.75rem !important;
                text-align: center;
            }

            .join-community-section .md\:w-1\/3,
            .join-community-section .md\:w-2\/3 {
                width: 100% !important;
            }

            .join-community-section .text-center-mobile {
                text-align: center;
            }

            /* Button Mobile */
            .join-button {
                display: block;
                width: 100%;
                text-align: center;
                padding: 0.75rem 1.5rem;
            }
        }

        /* ========== PERBAIKAN TABLET ========== */
        @media (min-width: 769px) and (max-width: 1024px) {
            .story-section .grid {
                grid-template-columns: 1fr 1fr !important;
            }

            .team-section .grid {
                grid-template-columns: repeat(2, 1fr) !important;
            }
        }
    </style>
</head>

<body class="bg-gray-950 text-white">
    <div id="page-root">
        <!-- Navigation -->
        @include('partials.navbar')

        <!-- Hero Section (ukuran seperti venues, tanpa overlay biru) -->
        <section class="hero-section mb-16 bg-cover bg-center p-24"
            style="background-image: url('{{ asset('images/bg/product_breadcrumb.png') }}');">
            <div class="max-w-7xl mx-auto px-6">
                <p class="breadcrumb text-sm text-gray-400 mt-1 flex">
                    <a href="{{ url('/') }}" class="hover:text-white">Home</a> / About Us
                </p>
                <h2 class="text-3xl md:text-4xl font-bold uppercase text-white mt-2">
                    Xander Billiard – Where Passion Meets Precision
                </h2>
            </div>
        </section>

        <!-- Our Story Section (styled seperti screenshot) -->
        <section class="story-section py-16 border-b border-gray-800">
            <div class="max-w-7xl mx-auto px-6">
                <!-- Judul besar + garis horizontal ke kanan -->
                <div class="flex items-center gap-6 mb-10">
                    <h2 class="text-3xl md:text-4xl font-extrabold leading-tight">
                        Our Story: A Legacy of Excellence
                    </h2>
                    <div class="rule flex-1" style="height:3px; background:#D9D9D9;"></div>
                </div>

                <!-- 2 kolom: teks kiri, gambar kanan -->
                <div class="grid md:grid-cols-3 gap-10 items-start">
                    <div class="md:col-span-2 text-gray-300 text-lg leading-relaxed space-y-6">
                        <p>
                            Founded by a group of billiard lovers who wanted to make a difference in the sport, Xander
                            Billiard was
                            built with dedication, precision, and a deep appreciation for the game. We understand that
                            every cue,
                            every shot, and every table tells a story—stories of skill, strategy, and the pursuit of
                            excellence.
                            That's why we are committed to providing only the best equipment, accessories, and services
                            to help
                            players perform at their peak. From custom cue repairs and precision balancing to advanced
                            training
                            resources and expert coaching, we've got everything a billiard player needs to elevate their
                            game.
                        </p>
                        <p>
                            But we offer more than just products and services—we create an environment where players can
                            grow,
                            learn, and connect with others who share the same passion. Through our training programs,
                            community
                            events, and mentorship initiatives, we provide a space where players can sharpen their
                            skills and gain
                            valuable insights from seasoned professionals. Whether you're just starting out or looking
                            to refine your
                            techniques, we are dedicated to helping every player unlock their full potential. At Xander
                            Billiard, we
                            believe in the power of community, precision, and unwavering dedication to the game.
                        </p>
                    </div>

                    <div class="md:col-span-1 flex flex-col h-full">
                        <div
                            class="rounded-2xl overflow-hidden shadow-xl ring-1 ring-white/10 bg-neutral-800/40 h-full flex">
                            <img src="{{ asset('images/about/player1.png') }}" alt="Billiard Player"
                                class="w-full h-full object-cover">
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Billiard Balls Image -->
        <section class="billiard-balls-section py-12">
            <div class="max-w-7xl mx-auto px-6">
                <div class="mb-10">
                    <div style="height:3px; background:#D9D9D9; border:none;" class="rounded"></div>
                </div>
                <img src="{{ asset('images/about/player2.png') }}" alt="Billiard Balls"
                    class="w-full h-auto rounded-lg">
            </div>
        </section>

        <!-- Two Column Section -->
        <section class="two-column-section mt-0 pb-16 border-b border-gray-800">
            <div class="max-w-7xl mx-auto px-6">
                <div class="grid md:grid-cols-2 gap-12 items-start">
                    <div class="flex flex-col h-full">
                        <h3 class="text-3xl font-bold mb-2">How We Operate:</h3>
                        <h4 class="text-3xl font-bold mb-6">A Commitment to Quality and Community</h4>
                        <p class="text-gray-300 flex-1">
                            At Xander Billiard, we do things differently. We don't just sell products—we cultivate
                            experiences.
                            Our shop offers an extensive range of carefully curated billiard supplies, while our venue
                            services
                            ensure players have access to top-tier facilities. Our technical team provides hands-on
                            services like
                            cue balancing, tip installation, and refinishing, so every piece of equipment is in peak
                            condition.
                            We also believe in fostering a strong community by organizing events, sparring sessions, and
                            educational
                            content to help players grow and improve.
                        </p>
                    </div>
                    <div class="flex flex-col h-full">
                        <h3 class="text-3xl font-bold mb-2">Who We Serve:</h3>
                        <h4 class="text-3xl font-bold mb-6">Every Player, Every Level</h4>
                        <p class="text-gray-300 flex-1">
                            Whether you're a seasoned professional, a casual player, or someone just discovering the
                            world
                            of billiards, Xander Billiard is here for you. We provide high-quality gear, expert
                            maintenance,
                            and a welcoming space for practice, competition, and learning. We also connect athletes with
                            opportunities, helping them find sparring partners, training sessions, and even competitive
                            events to test their skills.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <section class="team-section py-16 border-b border-gray-800">
            <div class="max-w-7xl mx-auto px-6">

                <!-- Garis tebal di atas team section -->
                <div class="mb-10">
                    <div style="height:3px; background:#D9D9D9; border:none;" class="rounded"></div>
                </div>

                <!-- Grid 4 foto (desktop), caption di bawah -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-8">
                    <figure class="space-y-3">
                        <div
                            class="team-member-img w-full h-[360px] rounded-2xl overflow-hidden bg-neutral-800 shadow-lg ring-1 ring-white/10">
                            <img src="{{ asset('images/about/player3.png') }}" alt="Aisha Putri"
                                class="w-full h-full object-cover" loading="lazy">
                        </div>
                        <figcaption>
                            <h3 class="text-lg font-semibold">Aisha Putri</h3>
                            <p class="text-gray-400 text-sm">Cue Specialist</p>
                        </figcaption>
                    </figure>

                    <figure class="space-y-3">
                        <div
                            class="team-member-img w-full h-[360px] rounded-2xl overflow-hidden bg-neutral-800 shadow-lg ring-1 ring-white/10">
                            <img src="{{ asset('images/about/player4.png') }}" alt="Nadia Rahma"
                                class="w-full h-full object-cover" loading="lazy">
                        </div>
                        <figcaption>
                            <h3 class="text-lg font-semibold">Nadia Rahma</h3>
                            <p class="text-gray-400 text-sm">Venue Manager</p>
                        </figcaption>
                    </figure>

                    <figure class="space-y-3">
                        <div
                            class="team-member-img w-full h-[360px] rounded-2xl overflow-hidden bg-neutral-800 shadow-lg ring-1 ring-white/10">
                            <img src="{{ asset('images/about/player5.png') }}" alt="Dimas Pratama"
                                class="w-full h-full object-cover" loading="lazy">
                        </div>
                        <figcaption>
                            <h3 class="text-lg font-semibold">Dimas Pratama</h3>
                            <p class="text-gray-400 text-sm">Head Coach</p>
                        </figcaption>
                    </figure>

                    <figure class="space-y-3">
                        <div
                            class="team-member-img w-full h-[360px] rounded-2xl overflow-hidden bg-neutral-800 shadow-lg ring-1 ring-white/10">
                            <img src="{{ asset('images/about/player6.png') }}" alt="Rafi Alvaro"
                                class="w-full h-full object-cover" loading="lazy">
                        </div>
                        <figcaption>
                            <h3 class="text-lg font-semibold">Rafi Alvaro</h3>
                            <p class="text-gray-400 text-sm">Equipment Tech</p>
                        </figcaption>
                    </figure>
                </div>

                <!-- Heading & paragraf seperti contoh gambar -->
                <div class="team-heading-section grid md:grid-cols-2 gap-12 mt-12 items-center">
                    <div>
                        <div class="w-full flex justify-center">
                            <h2 class="text-11xl md:text-4xl font-extrabold leading-tight mb-0 text-center">
                                The Faces Behind <br> Xander Billiard
                            </h2>
                        </div>
                    </div>
                    <div>
                        <p class="text-gray-300 text-lg leading-relaxed mb-0 md:-ml-12">
                            Xander Billiard isn't just a brand—it's a family. Our team is made up of passionate players,
                            skilled technicians, and dedicated professionals who live and breathe billiards. We believe
                            that the best way to serve our customers is by sharing our knowledge and enthusiasm for the
                            game.
                            Whether you meet us in-store, online, or at one of our many events, you'll always be greeted
                            by
                            people who truly care about helping you become a better player.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Join Community Section -->
        <section class="join-community-section py-16 border-b border-gray-800">
            <div class="max-w-7xl mx-auto px-6">
        
                <!-- Garis tebal di atas section -->
                <div class="mb-10">
                    <div style="height:3px; background:#D9D9D9; border:none;" class="rounded"></div>
                </div>
        
                <div class="flex flex-col md:flex-row gap-12">
                    <div class="md:w-1/3">
                        <img src="{{ asset('images/about/player7.png') }}" alt="Billiard Community"
                            class="w-full h-auto rounded-lg">
                    </div>
                    <div class="md:w-2/3 text-center-mobile">
                        <h2 class="text-3xl font-bold mb-6">Join the Xander Billiard Community</h2>
                        <p class="text-gray-300 mb-6">
                            We invite you to become a part of our growing community. Follow us for the latest billiard
                            news,
                            join our training sessions, or stop by our venue to experience the game like never before.
                            Whether you're looking to refine your skills, connect with fellow players, or find the best
                            gear
                            in the game, Xander Billiard is your ultimate destination.
                        </p>
                        <p class="text-gray-300 mb-8">
                            Let's keep the passion alive, one shot at a time. Welcome to Xander Billiard!
                        </p>
                        <a href="#"
                            class="join-button bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-md inline-block">
                            Join Now!
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        @include('partials.footer')
    </div>

    <!-- iOS rubber-band guard: cegah bounce atas/bawah -->
    <script>
        (function() {
            const scroller = document.getElementById('page-root');
            if (!scroller) return;

            function nudgeEdges() {
                if (scroller.scrollTop <= 0) scroller.scrollTop = 1;
                const max = scroller.scrollHeight - scroller.clientHeight;
                if (scroller.scrollTop >= max) scroller.scrollTop = max - 1;
            }

            let startY = 0;

            scroller.addEventListener('touchstart', function(e) {
                startY = e.touches && e.touches.length ? e.touches[0].clientY : 0;
                nudgeEdges();
            }, {
                passive: true
            });

            scroller.addEventListener('touchmove', function(e) {
                const y = e.touches && e.touches.length ? e.touches[0].clientY : 0;
                const dy = y - startY;
                const atTop = scroller.scrollTop <= 0;
                const atBottom = scroller.scrollTop + scroller.clientHeight >= scroller.scrollHeight;
                if ((atTop && dy > 0) || (atBottom && dy < 0)) {
                    e.preventDefault();
                }
            }, {
                passive: false
            });

            document.addEventListener('touchmove', function(e) {
                if (!e.target.closest('#page-root')) e.preventDefault();
            }, {
                passive: false
            });
        })();
    </script>
</body>

</html>
