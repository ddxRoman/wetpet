<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Animal;

class AdSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userIds = User::pluck('id')->toArray();
        $animalIds = Animal::pluck('id')->toArray();

        if (empty($userIds) || empty($animalIds)) {
            $this->command->warn('Для запуска сидера объявлений нужны пользователи и записи в таблице animals!');
            return;
        }

        $cities = ['Краснодар', 'Сочи', 'Новороссийск', 'Армавир', 'Геленджик'];
        
        // Твои базовые заголовки для примера
        $baseTitles = [
            'Продам клетку для попугая',
            'Отдам котят в добрые руки',
            'Корм для собак премиум класса',
            'Аквариум на 100 литров',
            'Услуги выгула собак',
            'Переноска для кошек (новая)',
            'Лежанка для крупной собаки',
            'Когтеточка ручной работы',
            'Обменяю террариум на фильтр',
            'Игрушки для активных щенков'
        ];

        $data = [];

        for ($i = 0; $i < 200; $i++) {
            $priceType = fake()->randomElement(['fixed', 'free', 'exchange']);
            
            // Генерируем заголовок: либо из списка, либо случайный набор слов через fake
            $title = fake()->boolean(70) 
                ? fake()->randomElement($baseTitles) 
                : Str::ucfirst(fake()->words(3, true));

            $photos = [
                'ads/sample1.jpg',
                'ads/sample2.jpg',
                'ads/sample3.jpg',
                'ads/sample4.jpg',
                'ads/sample5.jpg'
            ];

            $data[] = [
                'user_id'      => fake()->randomElement($userIds),
                'animal_id'    => fake()->randomElement($animalIds),
                'title'        => $title,
                'description'  => fake()->realText(400), // Более реалистичный текст
                'price'        => $priceType === 'fixed' ? fake()->randomFloat(2, 300, 50000) : null,
                'price_type'   => $priceType,
                'phone'        => '+7 (9' . fake()->numberBetween(10, 99) . ') ' . fake()->numberBetween(100, 999) . '-' . fake()->numberBetween(10, 99) . '-' . fake()->numberBetween(10, 99),
                'city'         => fake()->randomElement($cities),
                'address'      => fake()->address(),
                'condition'    => fake()->randomElement(['new', 'used']),
                'photos'       => json_encode(fake()->randomElements($photos, fake()->numberBetween(1, 5))),
                'is_active'    => true,
                'moderated_at' => now(),
                'created_at'   => fake()->dateTimeBetween('-1 month', 'now'),
                'updated_at'   => now(),
            ];

            // Чтобы не перегружать память при вставке 200 записей, вставляем пачками по 50
            if (count($data) === 50) {
                DB::table('ads')->insert($data);
                $data = [];
            }
        }

        // Вставляем остаток, если есть
        if (!empty($data)) {
            DB::table('ads')->insert($data);
        }

        $this->command->info('Сидер объявлений на 200 записей успешно выполнен!');
    }
}