<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ReviewReceipt;
use App\Models\Review;
use App\Models\Clinic;
use Illuminate\Support\Str;

class ReviewReceiptSeeder extends Seeder
{
    /**
     * Запуск сидера.
     */
    public function run(): void
    {
        // Проверяем, что есть клиники и отзывы
        $clinic = Clinic::inRandomOrder()->first();
        $review = Review::inRandomOrder()->first();

        if (!$clinic || !$review) {
            $this->command->warn('⛔ Нет данных в таблицах clinics или reviews. Сначала добавьте их.');
            return;
        }

        // Создаём несколько примеров чеков
        $statuses = ['pending', 'verified', 'rejected'];

        foreach (range(1, 10) as $i) {
            ReviewReceipt::create([
                'review_id' => $review->id,
                'clinic_id' => $clinic->id,
                 'path' => "clinics/review_receipts/fake_receipt_" . rand(1, 8) . ".webp", // 👈 случайный номер 1–8
                'status' => 'pending',

            ]);
        }

        $this->command->info('✅ Сидирование таблицы review_receipts завершено (10 записей).');
    }
}
