<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Pet;

class PetSeeder extends Seeder
{
    /**
     * Запуск сидера
     */
    public function run(): void
    {
        // Очищаем таблицу
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('pets')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $pets = [
            [
                'user_id' => 1,
                'animal_id' => 1, // Собака
                'name' => 'Бёрд',
                'birth_date' => '2020-03-15',
                'color' => 'Подпалый',
                'gender' => 'Самка',
                'photo' => 'pets/bonya.webp',
            ],
            [
                'user_id' => 1,
                'animal_id' => 2, // Кошка
                'name' => 'Марсель',
                'birth_date' => '2018-11-02',
                'color' => 'Чёрный',
                'gender' => 'Самец',
                'photo' => 'pets/marsel.webp',
            ],
            [
                'user_id' => 1,
                'animal_id' => 1, // Собака
                'name' => 'Боня',
                'birth_date' => '2020-03-15',
                'color' => 'Рыжий',
                'gender' => 'Самка',
                'photo' => 'pets/bonya.webp',
            ],
        ];

        foreach ($pets as $pet) {
            Pet::create([
                ...$pet,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('✅ Питомцы успешно добавлены.');
    }
}
