<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Review;
use App\Models\ReviewPhoto;
use Illuminate\Support\Facades\Storage;

class ReviewPhotoSeeder extends Seeder
{
    public function run(): void
    {
        // Если нет отзывов — выходим
        $reviews = Review::all();
        if ($reviews->isEmpty()) {
            $this->command->warn('Нет отзывов — ReviewPhotoSeeder пропущен.');
            return;
        }

        // Убедимся, что папка существует
        Storage::disk('public')->makeDirectory('reviews/photos');

        // Для каждого отзыва добавим случайное количество фото (0–3)
        foreach ($reviews as $review) {
            $photoCount = rand(0, 3);

            for ($i = 0; $i < $photoCount; $i++) {
                // Для теста можно использовать один и тот же файл-заглушку
                $path = 'reviews/photos/sample' . rand(1, 5) . '.webp';

                ReviewPhoto::create([
                    'review_id' => $review->id,
                    'photo_path' => $path,
                ]);
            }
        }

        $this->command->info('✅ Добавлены тестовые фото к отзывам!');
    }
}
