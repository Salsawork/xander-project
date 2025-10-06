<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\AthleteDetail;
use App\Models\SparringSchedule;

class AthleteDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Data atlet yang sudah ada di UserSeeder
        $athleteData = [
            'alex@athlete.com' => [
                'name' => 'Alex Murphy',
                'handicap' => 'Handicap V',
                'experience_years' => 12,
                'specialty' => '9-Ball, Straight Pool',
                'location' => 'Jakarta, Indonesia',
                'bio' => 'Saya sudah berdedikasi lebih dari satu dekade untuk menguasai seni biliar, berkompetisi di turnamen nasional dan internasional dan menyempurnakan keterampilan saya dengan setiap permainan.',
                'price_per_session' => 50000,
                'image' => 'athlete-1.png'
            ],
            'jessica@athlete.com' => [
                'name' => 'Jessica Lee',
                'handicap' => 'Handicap IV',
                'experience_years' => 8,
                'specialty' => '8-Ball, 10-Ball',
                'location' => 'Bandung, Indonesia',
                'bio' => 'Pemain biliar profesional dengan pengalaman mengajar lebih dari 5 tahun. Spesialis dalam teknik kontrol bola dan strategi permainan.',
                'price_per_session' => 45000,
                'image' => 'athlete-2.png'
            ],
            'daniel@athlete.com' => [
                'name' => 'Daniel Cruz',
                'handicap' => 'Handicap VI',
                'experience_years' => 15,
                'specialty' => 'Snooker, Carom',
                'location' => 'Surabaya, Indonesia',
                'bio' => 'Juara nasional biliar 3 kali berturut-turut. Ahli dalam permainan defensif dan teknik posisi.',
                'price_per_session' => 60000,
                'image' => 'athlete-3.png'
            ]
        ];

        // Buat athlete details untuk setiap atlet
        foreach ($athleteData as $username => $data) {
            // Cari user berdasarkan username
            $athlete = User::where('email', $username)->first();
            
            if ($athlete) {
                // Buat atau update athlete detail
                AthleteDetail::updateOrCreate(
                    ['user_id' => $athlete->id],
                    [
                        'handicap' => $data['handicap'],
                        'experience_years' => $data['experience_years'],
                        'specialty' => $data['specialty'],
                        'location' => $data['location'],
                        'bio' => $data['bio'],
                        'price_per_session' => $data['price_per_session'],
                        'image' => $data['image']
                    ]
                );

                // Buat jadwal sparring untuk atlet ini
                $this->createSchedulesForAthlete($athlete->id);
            }
        }
    }

    /**
     * Buat jadwal sparring untuk atlet tertentu
     */
    private function createSchedulesForAthlete($athleteId)
    {
        // Hapus jadwal lama
        SparringSchedule::where('athlete_id', $athleteId)->delete();

        // Buat jadwal untuk 7 hari ke depan
        for ($day = 0; $day < 7; $day++) {
            $date = date('Y-m-d', strtotime("+$day day"));
            
            // Jadwal pagi (9-12)
            for ($hour = 9; $hour < 12; $hour++) {
                SparringSchedule::create([
                    'athlete_id' => $athleteId,
                    'date' => $date,
                    'start_time' => sprintf('%02d:00:00', $hour),
                    'end_time' => sprintf('%02d:00:00', $hour + 1),
                    'is_booked' => false
                ]);
            }
            
            // Jadwal sore (13-16)
            for ($hour = 13; $hour < 16; $hour++) {
                SparringSchedule::create([
                    'athlete_id' => $athleteId,
                    'date' => $date,
                    'start_time' => sprintf('%02d:00:00', $hour),
                    'end_time' => sprintf('%02d:00:00', $hour + 1),
                    'is_booked' => false
                ]);
            }
            
            // Jadwal malam (18-21)
            for ($hour = 18; $hour < 21; $hour++) {
                SparringSchedule::create([
                    'athlete_id' => $athleteId,
                    'date' => $date,
                    'start_time' => sprintf('%02d:00:00', $hour),
                    'end_time' => sprintf('%02d:00:00', $hour + 1),
                    'is_booked' => false
                ]);
            }
        }
    }
}