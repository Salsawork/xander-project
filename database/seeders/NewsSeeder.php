<?php

namespace Database\Seeders;

use App\Models\News;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class NewsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $news = [
            [
                'title' => 'Xander Billiard Championship 2025',
                'content' => 'Turnamen billiard terbesar di tahun 2025 akan segera digelar. Persiapkan diri Anda untuk kompetisi yang menegangkan!',
                'image_url' => 'images/news/news-1.png',
                'published_at' => Carbon::now(),
                'is_featured' => true,
                'is_popular' => true,
                'category' => 'Championship'
            ],
            [
                'title' => 'Tips & Trik Mengasah Kemampuan Billiard',
                'content' => 'Pelajari teknik-teknik dasar dan lanjutan untuk meningkatkan permainan billiard Anda dari para profesional.',
                'image_url' => 'images/news/news-nobg.png',
                'published_at' => Carbon::now()->subDays(2),
                'is_featured' => false,
                'is_popular' => true,
                'category' => 'Tips'
            ],
            [
                'title' => 'Sejarah Turnamen Billiard Internasional',
                'content' => 'Mengulik sejarah panjang turnamen billiard internasional dan perkembangannya hingga saat ini.',
                'image_url' => 'images/news/news-1.png',
                'published_at' => Carbon::now()->subWeek(),
                'is_featured' => false,
                'is_popular' => false,
                'category' => 'History'
            ],
            [
                'title' => 'Rahasia Menjadi Pemain Billiard Profesional',
                'content' => 'Temukan rahasia dan latihan rutin yang dilakukan oleh para pemain billiard profesional untuk mencapai puncak karir mereka.',
                'image_url' => 'images/news/news-nobg.png',
                'published_at' => Carbon::now()->subDays(3),
                'is_featured' => true,
                'is_popular' => true,
                'category' => 'Tips'
            ],
            [
                'title' => 'Review Meja Billiard Terbaik 2025',
                'content' => 'Simak ulasan lengkap tentang meja billiard terbaik di tahun 2025 dengan berbagai fitur dan keunggulannya.',
                'image_url' => 'images/news/news-1.png',
                'published_at' => Carbon::now()->subDays(5),
                'is_featured' => false,
                'is_popular' => true,
                'category' => 'Review'
            ],
            [
                'title' => 'Jadwal Turnamen Bulan Ini',
                'content' => 'Jangan lewatkan jadwal turnamen billiard terdekat di kota Anda. Daftarkan diri Anda sekarang juga!',
                'image_url' => 'images/news/news-nobg.png',
                'published_at' => Carbon::now()->subDays(1),
                'is_featured' => true,
                'is_popular' => false,
                'category' => 'Event'
            ],
            [
                'title' => 'Teknik Dasar Pukulan Billiard untuk Pemula',
                'content' => 'Pelajari teknik-teknik dasar pukulan dalam billiard yang wajib dikuasai oleh para pemula.',
                'image_url' => 'images/news/news-1.png',
                'published_at' => Carbon::now()->subDays(4),
                'is_featured' => false,
                'is_popular' => true,
                'category' => 'Tips'
            ],
            [
                'title' => 'Profil Juara Dunia Billiard 2024',
                'content' => 'Kenali lebih dekat sosok dibalik kemenangan spektakuler di kejuaraan dunia billiard tahun lalu.',
                'image_url' => 'images/news/news-nobg.png',
                'published_at' => Carbon::now()->subDays(6),
                'is_featured' => true,
                'is_popular' => true,
                'category' => 'Profile'
            ]
        ];

        foreach ($news as $item) {
            News::create($item);
        }
    }
}