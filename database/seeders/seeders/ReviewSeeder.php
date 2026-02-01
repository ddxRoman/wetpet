<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Review;
use App\Models\Clinic;
use App\Models\Doctor;

class ReviewSeeder extends Seeder
{
    /**
     * Запуск сидера
     */
    public function run(): void
    {
        // Очищаем таблицу
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('reviews')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $reviews = [
            // ===== Отзывы о клиниках =====
            [
                'user_id' => 1,
                'reviewable_type' => Clinic::class,
                'reviewable_id' => 1,
                'review_date' => '2024-05-12',
                'rating' => 5,
                'content' => 'Отличная клиника, очень внимательное отношение к животным.',
                'liked' => 'Вежливый персонал, Квалифицированные врачи',
                'disliked' => null,
                'pet_id' => null,
                'receipt_path' => null,
            ],
        ];

        foreach ($reviews as $review) {
            Review::create([
                ...$review,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('✅ Отзывы успешно добавлены.');
    }
}
