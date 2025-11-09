<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AwardsTableSeeder extends Seeder
{
    public function run(): void
    {
        // Для примера — заполним награды для клиники с ID = 1
        DB::table('awards')->insert([
            [
                'clinic_id' => 6,
                'title' => 'Диплом ветеринарной ассоциации',
                'description' => 'Признание за достижения в области хирургии животных.',
                'image' => 'clinics/awards/diploma2.webp',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'clinic_id' => 6,
                'title' => 'Сертификат качества обслуживания',
                'description' => 'Подтверждение высокого уровня клиентского сервиса в 2024 году.',
                'image' => 'clinics/awards/diploma2.webp',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'clinic_id' => 6,
                'title' => 'Премия за инновации в ветеринарии',
                'description' => 'Награда за внедрение передовых технологий диагностики.',
                'image' => 'clinics/awards/diploma1.webp',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'clinic_id' => 6,
                'title' => 'Премия за инновации в ветеринарии',
                'description' => 'Награда за внедрение передовых технологий диагностики.',
                'image' => 'clinics/awards/diploma6.webp',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'clinic_id' => 6,
                'title' => 'Премия за инновации в ветеринарии',
                'description' => 'Награда за внедрение передовых технологий диагностики.',
                'image' => 'clinics/awards/diploma3.webp',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'clinic_id' => 6,
                'title' => 'Премия за инновации в ветеринарии',
                'description' => 'Награда за внедрение передовых технологий диагностики.',
                'image' => 'clinics/awards/diploma7.webp',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'clinic_id' => 1,
                'title' => 'Премия за инновации в ветеринарии',
                'description' => 'Награда за внедрение передовых технологий диагностики.',
                'image' => 'clinics/awards/diploma8.webp',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'clinic_id' => 4,
                'title' => 'Премия за инновации в ветеринарии',
                'description' => 'Награда за внедрение передовых технологий диагностики.',
                'image' => 'clinics/awards/diploma8.webp',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'clinic_id' => 3,
                'title' => 'Премия за инновации в ветеринарии',
                'description' => 'Награда за внедрение передовых технологий диагностики.',
                'image' => 'clinics/awards/diploma8.webp',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'clinic_id' => 6,
                'title' => 'Премия за инновации в ветеринарии',
                'description' => 'Награда за внедрение передовых технологий диагностики.',
                'image' => 'clinics/awards/diploma2.webp',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'clinic_id' => 5,
                'title' => 'Премия за инновации в ветеринарии',
                'description' => 'Награда за внедрение передовых технологий диагностики.',
                'image' => 'clinics/awards/diploma.webp',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'clinic_id' => 1,
                'title' => 'Премия за инновации в ветеринарии',
                'description' => 'Награда за внедрение передовых технологий диагностики.',
                'image' => 'clinics/awards/diploma2.webp',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'clinic_id' => 1,
                'title' => 'Премия за инновации в ветеринарии',
                'description' => 'Награда за внедрение передовых технологий диагностики.',
                'image' => 'clinics/awards/diploma2.webp',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'clinic_id' => 1,
                'title' => 'Премия за инновации в ветеринарии',
                'description' => 'Красавцы',
                'image' => 'clinics/awards/diploma2.webp',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
