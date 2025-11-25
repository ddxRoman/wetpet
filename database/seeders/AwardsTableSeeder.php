<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AwardsTableSeeder extends Seeder
{
    public function run(): void
    {
        $clinics = DB::table('clinics')->pluck('id')->toArray();
        $doctors = DB::table('doctors')->pluck('id')->toArray();

        $awards = [
            'Диплом ветеринарной ассоциации',
            'Сертификат качества обслуживания',
            'Премия за инновации в ветеринарии',
            'Премия за лучшую хирургию',
            'Выбор клиентов',
        ];

        for ($i = 0; $i < 50; $i++) {

            // 50/50 — награда либо врачу, либо клинике
            $assignToDoctor = rand(0,1) === 1;

            DB::table('awards')->insert([
                'clinic_id' => $assignToDoctor ? null : $clinics[array_rand($clinics)],
                'doctor_id' => $assignToDoctor ? $doctors[array_rand($doctors)] : null,
                'title'     => $awards[array_rand($awards)],
                'description' => fake()->sentence(),
                'image'     => 'clinics/awards/diploma' . rand(1,8) . '.webp',
                'confirmed' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
