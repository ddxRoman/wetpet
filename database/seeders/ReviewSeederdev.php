<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Review;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Specialist;
use App\Models\Organization;
use App\Models\User;
use App\Models\Pet;

class ReviewSeederdev extends Seeder
{
    /**
     * Наборы шаблонов текста для положительных, нейтральных и отрицательных отзывов.
     * Подбираются автоматически в зависимости от сгенерированной оценки.
     */
    private array $positiveContents = [
        'Отличная клиника, очень внимательное отношение к животным.',
        'Врач от Бога! Спасли нашего питомца, огромная благодарность.',
        'Очень современное оборудование, всё сделали быстро и точно.',
        'Лучшая клиника в городе, теперь только сюда.',
        'Удобная парковка, внутри уютно, есть аптека.',
        'Провели сложную операцию, всё прошло успешно.',
        'Персонал вежливый, врачи компетентные, рекомендую всем знакомым.',
        'Подробно объяснили схему лечения, спасибо за терпение.',
        'Быстро приняли без записи, помогли в экстренной ситуации.',
        'Хирургия высокого уровня, животное быстро восстановилось.',
    ];

    private array $neutralContents = [
        'Хорошие врачи, но пришлось подождать в очереди около 20 минут.',
        'Цены выше среднего, а ремонт в клинике староват.',
        'Хорошая клиника, но дозвониться бывает сложно.',
        'В целом неплохо, но хотелось бы более гибкий график записи.',
        'Приём прошёл нормально, без особых нареканий.',
        'Врач опытный, но общение немного суховатое.',
        'Ожидание немного затянулось, но результат устроил.',
    ];

    private array $negativeContents = [
        'Администратор нахамил по телефону, записываться не стал.',
        'Поставили неверный диагноз, пришлось перелечиваться в другом месте.',
        'Долго ждали приёма, персонал невнимательный.',
        'Цены завышены, а качество обслуживания оставляет желать лучшего.',
        'Не понравилось отношение к животному во время осмотра.',
        'Обещали перезвонить, но так и не перезвонили.',
    ];

    private array $likedOptions = [
        'Вежливый персонал', 'Квалифицированные врачи', 'Профессионализм',
        'Новое оборудование', 'Чистота', 'Инфраструктура', 'Безопасность',
        'Оперативность', 'Психология животных', 'Терпеливость, знания',
        'Качество', 'Золотые руки, чуткость', null,
    ];

    private array $dislikedOptions = [
        'Очередь', 'Высокая стоимость', 'Сложно дозвониться',
        'Сервис, хамство', 'Низкая квалификация', null, null, null,
    ];

    /**
     * Запуск сидера
     */
    public function run(): void
    {
        // Очищаем таблицу
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('reviews')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Реальные id пользователей — отзыв обязательно должен ссылаться на существующего пользователя
        $userIds = User::pluck('id')->all();
        if (empty($userIds)) {
            $this->command->warn('⚠ В таблице users нет записей — отзывы не могут быть созданы (нужен хотя бы один user).');
            return;
        }

        // Реальные id питомцев (необязательно, но для реалистичности)
        $petIds = Pet::pluck('id')->all();

        // Для каждого "отзываемого" типа собираем реальные id из БД,
        // чтобы отзывы ссылались на существующие клиники/врачей/специалистов/организации
        $allReviewableTypes = [
            Clinic::class       => Clinic::pluck('id')->all(),
            Doctor::class       => Doctor::pluck('id')->all(),
            Specialist::class   => Specialist::pluck('id')->all(),
            Organization::class => Organization::pluck('id')->all(),
        ];

        // Явно сообщаем, если какая-то сущность не будет участвовать в генерации —
        // иначе легко не заметить, что, например, в таблице doctors нет записей,
        // и отзывы сгенерируются только для одного типа.
        foreach ($allReviewableTypes as $modelClass => $ids) {
            if (empty($ids)) {
                $this->command->warn('⚠ Таблица "'.(new $modelClass())->getTable().'" пуста — отзывы для '.class_basename($modelClass).' созданы не будут. Сначала засейдите эту сущность (например php artisan db:seed) и запустите сидер снова.');
            }
        }

        // Оставляем только те типы, у которых реально есть записи в БД
        $reviewableTypes = array_filter($allReviewableTypes, fn ($ids) => !empty($ids));

        if (empty($reviewableTypes)) {
            $this->command->warn('⚠ Нет ни одной клиники/врача/специалиста/организации — отзывы не могут быть созданы.');
            return;
        }

        $reviewableTypeKeys = array_keys($reviewableTypes);

        $totalReviews = 100000;
        $now = now();
        $rows = [];

        for ($i = 0; $i < $totalReviews; $i++) {
            // Случайный тип объекта отзыва и случайный существующий id этого типа
            $type = $reviewableTypeKeys[array_rand($reviewableTypeKeys)];
            $ids  = $reviewableTypes[$type];
            $reviewableId = $ids[array_rand($ids)];

            // Случайная оценка от 1 до 5
            $rating = random_int(1, 5);

            // Текст отзыва подбираем в зависимости от оценки, чтобы данные выглядели правдоподобно
            $content = match (true) {
                $rating >= 4  => $this->positiveContents[array_rand($this->positiveContents)],
                $rating === 3 => $this->neutralContents[array_rand($this->neutralContents)],
                default       => $this->negativeContents[array_rand($this->negativeContents)],
            };

            // Случайная дата за последние ~2 года
            $reviewDate = now()->subDays(random_int(0, 730));

            $rows[] = [
                'user_id'          => $userIds[array_rand($userIds)],
                'reviewable_type'  => $type,
                'reviewable_id'    => $reviewableId,
                'review_date'      => $reviewDate->format('Y-m-d'),
                'rating'           => $rating,
                'content'          => $content,
                'liked'            => $this->likedOptions[array_rand($this->likedOptions)],
                'disliked'         => $this->dislikedOptions[array_rand($this->dislikedOptions)],
                'pet_id'           => !empty($petIds) && random_int(0, 1) === 1
                    ? $petIds[array_rand($petIds)]
                    : null,
                'receipt_path'     => null,
                'receipt_verified' => 'pending',
                'created_at'       => $now,
                'updated_at'       => $now,
            ];

            // Вставляем пачками по 500 записей, чтобы не перегружать память/запрос
            if (count($rows) === 500) {
                DB::table('reviews')->insert($rows);
                $rows = [];
            }
        }

        if (!empty($rows)) {
            DB::table('reviews')->insert($rows);
        }

        $this->command->info("✅ Успешно добавлено {$totalReviews} случайных отзывов.");
    }
}