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
                'animal_id' => 42, // Собака
                'name' => 'Бёрд',
                'birth_date' => '2024-03-15',
                'color' => 'Подпалый',
                'gender' => 'Самка',
                'photo' => 'pets/Bird_dog.jpg',
            ],
            [
                'user_id' => 1,
                'animal_id' => 83, // Кошка
                'name' => 'Рыся',
                'birth_date' => '2016-11-02',
                'color' => 'Коричневый',
                'gender' => 'Самка',
                'photo' => 'pets/Ryska.jpg',
            ],
            [
                'user_id' => 1,
                'animal_id' => 29, // Собака
                'name' => 'Пряник',
                'birth_date' => '2013-04-12',
                'color' => 'Палевый',
                'gender' => 'Самец',
                'photo' => 'pets/pryanik.jpg',
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
