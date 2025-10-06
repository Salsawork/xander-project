<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Careers — Xander Billiard</title>
    @vite('resources/css/app.css')
    <style>
        :root {
            color-scheme: dark;
        }

        html,
        body {
            height: 100%;
            background: #0a0a0a;
            overflow: hidden;
            overscroll-behavior: none;
        }

        #page-root {
            height: 100%;
            min-height: 100svh;
            overflow-y: auto;
            overscroll-behavior: contain;
            -webkit-overflow-scrolling: touch;
            background: #0a0a0a url('{{ asset('images/bg/background_3.png') }}') center/cover no-repeat;
        }

        #page-root,
        html,
        body {
            overflow-x: hidden;
        }

        @supports(overflow:clip) {

            #page-root,
            html,
            body {
                overflow-x: clip;
            }
        }

        img {
            display: block;
        }

        .wrap {
            max-width: 1100px;
            margin: 0 auto;
            padding: 0 16px;
        }

        .hero {
            background: url('{{ asset('images/bg/product_breadcrumb.png') }}') center/cover no-repeat;
            padding: 96px 0 64px;
        }

        .title {
            font-weight: 900;
            letter-spacing: .2px;
        }

        .muted {
            color: #cfcfcf;
        }

        .grid {
            display: grid;
            gap: 18px;
        }

        @media(min-width:768px) {
            .cols-3 {
                grid-template-columns: repeat(3, 1fr);
            }

            .cols-2 {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .card {
            background: #141414;
            border: 1px solid rgba(255, 255, 255, .08);
            border-radius: 16px;
            padding: 18px;
            transition: .2s transform, .2s box-shadow;
        }

        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 40px rgba(0, 0, 0, .35);
        }

        .badge {
            display: inline-flex;
            gap: 8px;
            align-items: center;
            font-size: 12px;
            padding: 6px 10px;
            border-radius: 999px;
            background: #0f0f0f;
            border: 1px solid rgba(255, 255, 255, .08);
            color: #93c5fd;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            background: #00c2a2;
            color: #041;
            font-weight: 800;
            border: none;
            border-radius: 12px;
            padding: 12px 16px;
            text-decoration: none;
        }

        .btn.ghost {
            background: transparent;
            color: #fff;
            border: 1px solid rgba(255, 255, 255, .16);
        }

        .btn.alt {
            background: #111;
            color: #fff;
            border: 1px solid rgba(255, 255, 255, .16);
        }

        .btn.wide {
            width: 100%;
        }

        .rule {
            height: 3px;
            background: #D9D9D9;
            border: none;
        }

        @media(max-width:768px) {
            .hero {
                padding: 64px 24px;
                text-align: center;
            }
        }

        /* Filters */
        .filters {
            display: grid;
            gap: 12px;
            grid-template-columns: 1fr;
        }

        @media(min-width:768px) {
            .filters {
                grid-template-columns: 1.4fr 1fr 1fr 1fr;
            }
        }

        .input,
        .select {
            background: #0f0f0f;
            border: 1px solid rgba(255, 255, 255, .14);
            color: #fff;
            border-radius: 12px;
            padding: 10px 12px;
            width: 100%;
        }

        .tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            padding: 4px 10px;
            border-radius: 999px;
            background: #0c0c0c;
            border: 1px solid rgba(255, 255, 255, .08);
            color: #d4d4d4;
        }

        .tag b {
            color: #fff;
            font-weight: 800;
        }
    </style>
</head>

<body class="text-white">
    <div id="page-root">
        @include('partials.navbar')

        <section class="hero">
            <div class="wrap">
                <p class="text-sm text-gray-400">Home / Company / Careers</p>
                <h1 class="title text-3xl md:text-4xl mt-2">Join Xander Billiard</h1>
                <p class="muted mt-2 max-w-3xl">Lowongan fokus ke operasional & layanan — bukan IT developer. Cocok
                    untuk kamu yang suka melayani pelanggan, rapi, dan sigap di lapangan.</p>
            </div>
        </section>

        <main class="wrap py-12">
            <!-- Benefits -->
            <h2 class="text-2xl font-extrabold mb-3">Benefits</h2>
            <div class="grid cols-3 mb-8">
                <div class="card">
                    <h3 class="font-bold mb-1">Remote-friendly (peran tertentu)</h3>
                    <p class="muted">CS Chat/WA bisa hybrid.</p>
                </div>
                <div class="card">
                    <h3 class="font-bold mb-1">Learning Budget</h3>
                    <p class="muted">Pelatihan layanan, coaching, safety.</p>
                </div>
                <div class="card">
                    <h3 class="font-bold mb-1">Impact Nyata</h3>
                    <p class="muted">Melayani komunitas biliar lokal.</p>
                </div>
            </div>

            <hr class="rule mb-6" />

            <!-- FILTERS -->
            <div class="card mb-6">
                <div class="filters">
                    <input id="q" class="input"
                        placeholder="Cari posisi (contoh: kasir, customer service, supervisor)" />
                    <select id="type" class="select">
                        <option value="">Semua Tipe</option>
                        <option>Full-time</option>
                        <option>Part-time</option>
                        <option>Contract</option>
                        <option>Internship</option>
                    </select>
                    <select id="dept" class="select">
                        <option value="">Semua Departemen</option>
                        <option>Operasional Toko</option>
                        <option>Customer Care</option>
                        <option>Fasilitas</option>
                        <option>Supply Chain</option>
                        <option>Event</option>
                        <option>Coaching</option>
                        <option>Manajemen</option>
                    </select>
                    <select id="loc" class="select">
                        <option value="">Semua Lokasi</option>
                        <option>Bogor</option>
                        <option>Jakarta</option>
                        <option>Bogor/Jakarta</option>
                        <option>Remote</option>
                        <option>Remote/Bogor</option>
                    </select>
                </div>
            </div>

            <!-- OPEN ROLES (OPERASIONAL & STORE) -->
            <h2 class="text-2xl font-extrabold mb-3">Open Roles — Operasional & Layanan</h2>
            @php
                $jobs = [
                    [
                        'title' => 'Penjaga Toko / Kasir',
                        'type' => 'Full-time',
                        'shift' => 'Shift',
                        'location' => 'Bogor',
                        'dept' => 'Operasional Toko',
                        'desc' =>
                            'Layani pelanggan di counter, input transaksi POS, kelola kas, bantu booking meja, upsell membership.',
                        'req' => [
                            'Ramah & komunikatif',
                            'Mau bekerja shift (malam/akhir pekan)',
                            'Pengalaman kasir jadi nilai plus',
                        ],
                    ],
                    [
                        'title' => 'Customer Service (Chat/WhatsApp)',
                        'type' => 'Full-time',
                        'shift' => 'Office',
                        'location' => 'Remote/Bogor',
                        'dept' => 'Customer Care',
                        'desc' => 'Respon chat/WA, follow-up booking, bantu kendala akun, koordinasi dengan store.',
                        'req' => [
                            'Mengetik cepat & rapi',
                            'Bahasa Indonesia baik, sopan',
                            'Bisa kerja terstruktur (SOP)',
                        ],
                    ],
                    [
                        'title' => 'Cleaning & Maintenance',
                        'type' => 'Part-time',
                        'shift' => 'Shift',
                        'location' => 'Bogor',
                        'dept' => 'Fasilitas',
                        'desc' => 'Menjaga kebersihan meja, cue, ruang; cek rutin peralatan; bantu penataan venue.',
                        'req' => ['Detail kebersihan', 'Fisik prima', 'Siap kerja pagi/malam'],
                    ],
                    [
                        'title' => 'Inventory & Gudang',
                        'type' => 'Full-time',
                        'shift' => 'Office',
                        'location' => 'Bogor',
                        'dept' => 'Supply Chain',
                        'desc' => 'Monitoring stok aksesori, restock antar-cabang, input PO, rekonsiliasi stok.',
                        'req' => ['Terbiasa spreadsheet', 'Teliti angka & barang', 'SIM C jadi nilai tambah'],
                    ],
                    [
                        'title' => 'Event Crew (Turnamen/Promo)',
                        'type' => 'Part-time',
                        'shift' => 'Shift',
                        'location' => 'Bogor/Jakarta',
                        'dept' => 'Event',
                        'desc' => 'Persiapan venue, registrasi peserta, dokumentasi dasar, kelancaran rundown.',
                        'req' => ['Komunikatif', 'Siap kerja akhir pekan', 'Paham dasar protokol event'],
                    ],
                    [
                        'title' => 'Instruktur Biliar (Coach) — Paruh Waktu',
                        'type' => 'Contract',
                        'shift' => 'Shift',
                        'location' => 'Bogor/Jakarta',
                        'dept' => 'Coaching',
                        'desc' => 'Melatih dasar teknik biliar untuk pemula, paket kelas privat/kelompok.',
                        'req' => [
                            'Pengalaman bermain/kompetisi',
                            'Komunikasi jelas & sabar',
                            'Portofolio/rekam jejak singkat',
                        ],
                    ],
                ];

                $mgmt = [
                    [
                        'title' => 'Shift Supervisor',
                        'type' => 'Full-time',
                        'shift' => 'Shift',
                        'location' => 'Bogor',
                        'dept' => 'Manajemen',
                        'desc' =>
                            'Pimpin shift harian, atur jadwal, handle eskalasi pelanggan, jaga standar operasional.',
                        'req' => [
                            'Pengalaman retail/hospitality 1-2 tahun',
                            'Leadership dasar',
                            'Siap kerja malam/akhir pekan',
                        ],
                    ],
                    [
                        'title' => 'Operations Coordinator',
                        'type' => 'Full-time',
                        'shift' => 'Office',
                        'location' => 'Bogor',
                        'dept' => 'Manajemen',
                        'desc' =>
                            'Koordinasi antar divisi (store, CS, gudang), buat laporan harian/mingguan, follow-up SOP.',
                        'req' => ['Terstruktur & rapi dokumen', 'Komunikasi lintas tim', 'Bisa Excel/Spreadsheet'],
                    ],
                ];
            @endphp

            <div id="jobs" class="grid" data-section="roles">
                @foreach ($jobs as $job)
                    <div class="card flex flex-col md:flex-row md:items-center md:justify-between gap-4"
                        data-title="{{ Str::slug($job['title'] . ' ' . $job['desc'] . ' ' . implode(' ', $job['req'])) }}"
                        data-type="{{ $job['type'] }}" data-dept="{{ $job['dept'] }}"
                        data-location="{{ $job['location'] }}">
                        <div>
                            <div class="badge">{{ $job['type'] }} • {{ $job['location'] }} • {{ $job['dept'] }}</div>
                            <h3 class="text-xl font-bold mt-2 mb-1">{{ $job['title'] }}</h3>
                            <p class="muted m-0">{{ $job['desc'] }}</p>
                            <div class="mt-3 flex flex-wrap gap-2">
                                @foreach ($job['req'] as $r)
                                    <span class="tag">{{ $r }}</span>
                                @endforeach
                                <span class="tag"><b>Shift:</b> {{ $job['shift'] }}</span>
                            </div>
                        </div>
                        <div class="flex flex-col gap-2 min-w-[220px]">
                            <a class="btn wide"
                                href="mailto:hr@xanderbilliard.com?subject=Apply: {{ urlencode($job['title']) }}">
                                Apply via Email
                            </a>
                            <a class="btn alt wide"
                                href="https://wa.me/62XXXXXXXXXX?text={{ urlencode('Halo Xander Billiard, saya ingin melamar posisi: ' . $job['title']) }}"
                                target="_blank" rel="noopener">
                                Chat via WhatsApp
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- OPEN MANAGEMENT -->
            <h2 class="text-2xl font-extrabold mt-10 mb-3">Open Management</h2>
            <div id="mgmt" class="grid" data-section="management">
                @foreach ($mgmt as $job)
                    <div class="card flex flex-col md:flex-row md:items-center md:justify-between gap-4"
                        data-title="{{ Str::slug($job['title'] . ' ' . $job['desc'] . ' ' . implode(' ', $job['req'])) }}"
                        data-type="{{ $job['type'] }}" data-dept="{{ $job['dept'] }}"
                        data-location="{{ $job['location'] }}">
                        <div>
                            <div class="badge">{{ $job['type'] }} • {{ $job['location'] }} • {{ $job['dept'] }}
                            </div>
                            <h3 class="text-xl font-bold mt-2 mb-1">{{ $job['title'] }}</h3>
                            <p class="muted m-0">{{ $job['desc'] }}</p>
                            <div class="mt-3 flex flex-wrap gap-2">
                                @foreach ($job['req'] as $r)
                                    <span class="tag">{{ $r }}</span>
                                @endforeach
                            </div>
                        </div>
                        <div class="flex flex-col gap-2 min-w-[220px]">
                            <a class="btn wide"
                                href="mailto:hr@xanderbilliard.com?subject=Apply: {{ urlencode($job['title']) }}">
                                Apply via Email
                            </a>
                            <a class="btn alt wide"
                                href="https://wa.me/62XXXXXXXXXX?text={{ urlencode('Halo Xander Billiard, saya ingin melamar posisi: ' . $job['title']) }}"
                                target="_blank" rel="noopener">
                                Chat via WhatsApp
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">
                <a class="btn ghost" href="mailto:hr@xanderbilliard.com?subject=Talent%20Pool">Kirim CV ke Talent
                    Pool</a>
            </div>

            <h2 class="text-2xl font-extrabold mt-10 mb-3">Proses Rekrut</h2>
            <ol class="list-decimal pl-6 text-gray-300 space-y-1">
                <li>Screening CV/Portofolio</li>
                <li>Interview (Behavioral/Case)</li>
                <li>Trial/On-the-Job (opsional)</li>
                <li>Offer</li>
            </ol>
        </main>

        @include('partials.footer')
    </div>

    <script>
        // ===== iOS/Android overscroll guard pada container scroll =====
        (function() {
            const s = document.getElementById('page-root');
            if (!s) return;

            function n() {
                if (s.scrollTop <= 0) s.scrollTop = 1;
                const m = s.scrollHeight - s.clientHeight;
                if (s.scrollTop >= m) s.scrollTop = m - 1;
            }
            let y = 0;
            s.addEventListener('touchstart', e => {
                y = (e.touches?.[0]?.clientY) || 0;
                n();
            }, {
                passive: true
            });
            s.addEventListener('touchmove', e => {
                const ny = (e.touches?.[0]?.clientY) || 0;
                const dy = ny - y;
                const atTop = s.scrollTop <= 0;
                const atBot = s.scrollTop + s.clientHeight >= s.scrollHeight;
                if ((atTop && dy > 0) || (atBot && dy < 0)) e.preventDefault();
            }, {
                passive: false
            });
            document.addEventListener('touchmove', e => {
                if (!e.target.closest('#page-root')) e.preventDefault();
            }, {
                passive: false
            });
        })();

        // ===== Simple client-side filters =====
        (function() {
            const q = document.getElementById('q');
            const type = document.getElementById('type');
            const dept = document.getElementById('dept');
            const loc = document.getElementById('loc');
            const lists = [document.getElementById('jobs'), document.getElementById('mgmt')];

            function norm(s) {
                return (s || '').toLowerCase();
            }

            function apply() {
                const qv = norm(q.value);
                const tv = norm(type.value);
                const dv = norm(dept.value);
                const lv = norm(loc.value);

                lists.forEach(list => {
                    if (!list) return;
                    list.querySelectorAll('.card').forEach(card => {
                        const t = norm(card.getAttribute('data-title'));
                        const ty = norm(card.getAttribute('data-type'));
                        const dp = norm(card.getAttribute('data-dept'));
                        const lc = norm(card.getAttribute('data-location'));

                        const okQ = !qv || t.includes(qv);
                        const okT = !tv || ty === tv;
                        const okD = !dv || dp === dv;
                        const okL = !lv || lc === lv;

                        card.style.display = (okQ && okT && okD && okL) ? '' : 'none';
                    });
                });
            }

            [q, type, dept, loc].forEach(el => el && el.addEventListener('input', apply));
            apply();
        })();
    </script>
</body>

</html>
