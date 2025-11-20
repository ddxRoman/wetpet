<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Review;
use App\Models\User;
use App\Models\Clinic;
use App\Models\Doctor;
use Illuminate\Support\Arr;
use InvalidArgumentException;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $users   = User::all();
        $clinics = Clinic::all();
        $doctors = Doctor::all();

        // Если нет пользователей — смысла нет продолжать
        if ($users->isEmpty()) {
            $this->command->warn('❗ Нет пользователей. Добавь User перед запуском.');
            return;
        }

        // Если нет ни клиник, ни докторов — сидер бесполезен
        if ($clinics->isEmpty() && $doctors->isEmpty()) {
            $this->command->warn('❗ Нет ни клиник, ни докторов.');
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

        $petid = range(1, 10);

        for ($i = 0; $i < 2000; $i++) {

            $user = $users->random();

            // Случайно выбираем: clinic или doctor
            $isClinic = rand(0, 1);

            // Выбираем объект, но только если он существует
            if ($isClinic && $clinics->count() > 0) {
                $reviewable     = $clinics->random();
                $reviewableType = Clinic::class;
            } elseif ($doctors->count() > 0) {
                $reviewable     = $doctors->random();
                $reviewableType = Doctor::class;
            } else {
                // Вообще ничего нет — пропускаем итерацию
                continue;
            }

            Review::create([
                'user_id'         => $user->id,
                'reviewable_id'   => $reviewable->id,
                'reviewable_type' => $reviewableType,
                'review_date'     => now()->subDays(rand(0, 365)),
                'rating'          => rand(1, 5),
                'content'         => fake()->paragraph(2),

                'liked' => implode(', ', Arr::random(
                    $likedOptions,
                    rand(1, min(3, count($likedOptions)))
                )),

                'disliked' => rand(0, 2)
                    ? implode(', ', Arr::random(
                        $dislikedOptions,
                        rand(1, min(2, count($dislikedOptions)))
                    ))
                    : null,

                'pet_id' => Arr::random($petid),

                'receipt_path' => fake()->boolean(50)
                    ? 'storage/receipts/example_' . rand(1, 10) . '.webp'
                    : null,
            ]);
        }

        $this->command->info('✅ Добавлены отзывы для клиник и докторов.');
    }
}
