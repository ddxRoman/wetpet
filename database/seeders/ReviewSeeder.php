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

    [
        'user_id' => 2,
        'reviewable_type' => \App\Models\Clinic::class,
        'reviewable_id' => 1,
        'review_date' => '2026-03-10',
        'rating' => 4,
        'content' => 'Когда у нас случилась критическая ситуация (сильное носовое кровотечение) - к нам выбежали все врачи за секунду и оказали скорую помощь! Также успокоили меня. Вообще часто бывали в этой клинике. Не приходится сильно долго ждать прием, есть парковка рядом. Хорошие общие впечатления, но не все врачи нам подошли.',
        'liked' => 'Оперативность',
        'disliked' => 'Надо выбрать своего врача',
        'pet_id' => null,
        'receipt_path' => null,
    ],
    [
        'user_id' => 2,
        'reviewable_type' => \App\Models\Clinic::class,
        'reviewable_id' => 60,
        'review_date' => '2026-03-10',
        'rating' => 5,
        'content' => 'Пока не могу сказать о лечении, мы в процессе. Но очень понравился персонал на ресепшн. Сама клиника достаточно большая и выглядит все чистенько, опрятно.',
        'liked' => 'Персонал',
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
