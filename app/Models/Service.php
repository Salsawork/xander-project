<?php

namespace App\Models;

use Illuminate\Support\Str;

class Service
{
    /**
     * Dummy data (tanpa DB)
     * image: siapkan file di public/images/services/.. atau biarkan fallback ke placeholder.
     */
    private const DATA = [
        [
            'title' => 'Tip Installation',
            'slug'  => 'tip-installation',
            'short_description' => 'Pasang tip baru dengan presisi untuk kontrol yang lebih konsisten.',
            'description' => "Layanan pemasangan tip baru untuk meningkatkan kontrol, feel, dan konsistensi tembakan.\nTermasuk pembersihan ringan dan pengecekan kesejajaran. Cocok untuk pemain yang ingin upgrade tip atau mengganti tip aus.",
            'image' => 'images/services/tip-installation.jpg',
            'price_min' => 50000, 'price_max' => 120000,
            'duration_min' => 15, 'duration_max' => 25,
            'tags' => ['tip','install','precision'],
            'is_active' => true,
        ],
        [
            'title' => 'Tip Reshaping',
            'slug'  => 'tip-reshaping',
            'short_description' => 'Bentuk ulang tip agar kembali optimal.',
            'description' => "Membentuk ulang tip yang aus agar kembali sesuai radius. Hasilnya: putaran (spin) lebih konsisten dan akurasi meningkat.",
            'image' => 'images/services/tip-reshaping.jpg',
            'price_min' => 30000, 'price_max' => 80000,
            'duration_min' => 10, 'duration_max' => 20,
            'tags' => ['tip','reshape'],
            'is_active' => true,
        ],
        [
            'title' => 'Shaft Cleaning',
            'slug'  => 'shaft-cleaning',
            'short_description' => 'Bersihkan shaft dari kotoran & residu untuk feel yang licin.',
            'description' => "Pembersihan shaft menyeluruh untuk mengurangi gesekan. Termasuk perawatan permukaan agar licin dan nyaman di tangan.",
            'image' => 'images/services/shaft-cleaning.jpg',
            'price_min' => 40000, 'price_max' => 90000,
            'duration_min' => 15, 'duration_max' => 30,
            'tags' => ['shaft','cleaning'],
            'is_active' => true,
        ],
        [
            'title' => 'Grip Replacement',
            'slug'  => 'grip-replacement',
            'short_description' => 'Ganti grip untuk pegangan yang lebih mantap.',
            'description' => "Penggantian grip dengan material pilihan (karet/linen) untuk kenyamanan dan kontrol terbaik.",
            'image' => 'images/services/grip-replacement.jpg',
            'price_min' => 60000, 'price_max' => 150000,
            'duration_min' => 20, 'duration_max' => 35,
            'tags' => ['grip','replacement'],
            'is_active' => true,
        ],
        [
            'title' => 'Balancing & Refinishing',
            'slug'  => 'balancing-refinishing',
            'short_description' => 'Setel keseimbangan & finishing cue.',
            'description' => "Penyesuaian balance point, pengecekan komponen, dan refinishing ringan pada permukaan cue untuk tampilan dan performa optimal.",
            'image' => 'images/services/balancing-refinishing.jpg',
            'price_min' => 120000, 'price_max' => 300000,
            'duration_min' => 40, 'duration_max' => 75,
            'tags' => ['balance','refinish'],
            'is_active' => true,
        ],
        [
            'title' => 'Ferrule Replacement',
            'slug'  => 'ferrule-replacement',
            'short_description' => 'Ganti ferrule untuk stabilitas tembakan.',
            'description' => "Penggantian ferrule retak/aus dengan bahan berkualitas. Termasuk kalibrasi ulang penyambungan agar performa kembali stabil.",
            'image' => 'images/services/ferrule-replacement.jpg',
            'price_min' => 150000, 'price_max' => 350000,
            'duration_min' => 45, 'duration_max' => 80,
            'tags' => ['ferrule','repair'],
            'is_active' => true,
        ],
    ];

    /** @return array<\stdClass> */
    public static function all(): array
    {
        return array_map(fn($it) => (object) $it, array_values(self::DATA));
    }

    /** @return array<\stdClass> */
    public static function active(): array
    {
        return array_map(
            fn($it) => (object) $it,
            array_values(array_filter(self::DATA, fn($it) => $it['is_active'] ?? false))
        );
    }

    public static function findBySlug(string $slug): ?\stdClass
    {
        foreach (self::DATA as $it) {
            if (($it['slug'] ?? '') === Str::slug($slug)) {
                return (object) $it;
            }
        }
        return null;
    }

    /** @return array<\stdClass> */
    public static function take(int $n): array
    {
        return array_slice(self::active(), 0, $n);
    }

    /** @return array<\stdClass> */
    public static function related(string $slug, int $n = 3): array
    {
        $items = array_values(array_filter(self::active(), fn($it) => $it->slug !== $slug));
        shuffle($items);
        return array_slice($items, 0, $n);
    }
}
