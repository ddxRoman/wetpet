<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Review;
use App\Models\User;
use App\Models\Clinic;
use Illuminate\Support\Arr;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $clinics = Clinic::all();

        if ($users->isEmpty() || $clinics->isEmpty()) {
            $this->command->warn('❗ Не найдено пользователей или клиник. Добавь данные перед запуском сидера.');
            return;
        }

        $likedOptions = [
            'Вежливый персонал', 'Чисто и уютно', 'Современное оборудование', 'Квалифицированные врачи',
            'Быстрая запись на приём', 'Отличное отношение к животным', 'Удобное расположение', 'Хорошие цены'
        ];

        $dislikedOptions = [
            'Долгая очередь', 'Неудобная парковка', 'Высокие цены', 'Не всегда отвечают на звонки',
            'Нет терминала', 'Слишком холодно в зале ожидания', 'Мало специалистов по редким животным'
        ];

        $petTypes = ['Кошка', 'Собака', 'Попугай', 'Хомяк', 'Кролик', 'Черепаха', 'Игуана'];
        $petNames = ['Мурзик', 'Барсик', 'Шарик', 'Лаки', 'Сэм', 'Белка', 'Рыжик', 'Пушок', 'Тоша', 'Кузя'];

        for ($i = 0; $i < 100; $i++) {
            $user = $users->random();
            $clinic = $clinics->random();

            Review::create([
                'user_id' => $user->id,
                'reviewable_id' => $clinic->id,
                'reviewable_type' => Clinic::class,
                'review_date' => now()->subDays(rand(0, 365)),

                'rating' => rand(1, 5),
                'content' => fake()->paragraph(2),
                'liked' => Arr::random($likedOptions, rand(1, 3)) ? implode(', ', Arr::random($likedOptions, rand(1, 3))) : null,
                'disliked' => Arr::random($dislikedOptions, rand(0, 2)) ? implode(', ', Arr::random($dislikedOptions, rand(0, 2))) : null,

                'pet_name' => Arr::random($petNames),
                'pet_type' => Arr::random($petTypes),
                'pet_age' => rand(1, 15),

                'receipt_verified' => fake()->boolean(80), // 80% проверено
                'receipt_path' => fake()->boolean(50) ? 'storage/receipts/example_' . rand(1, 10) . '.webp' : null,
            ]);
        }

        $this->command->info('✅ Добавлено 100 случайных отзывов.');
    }

    

}
