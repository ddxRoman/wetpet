<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Faker\Factory as Faker;

class UserTableSeeder extends Seeder
{
    public function run(): void
    {
        // --- Существующие пользователи ---
        $users = [
            [
                'name' => 'Роман Оксентий',
                'nickname' => 'DDX',
                'email' => 'ddxman@mail.ru',
                'birth_date' => '12.05.1994',
                'avatar' => 'avatars/5jtUKDlJGZoEGsQECo7NuxedJ8tFgboctKWJVQhL.webp',
                'phone' => '+79667404577',
                'password' => Hash::make('12345678'),
                'city_id' => 21,
                'is_admin' => true,
            ],
            [
                'name' => 'Анна Петрова',
                'nickname' => 'annapet',
                'email' => 'ddx2man@mail.ru',
                'birth_date' => null,
                'avatar' => '',
                'phone' => '+7964334577',
                'password' => Hash::make('12345678'),
                'city_id' => 2,
            ],
            [
                'name' => 'Сергей Смирнов',
                'nickname' => 'sergeydev',
                'email' => 'ddx1man@mail.ru',
                'birth_date' => null,
                'avatar' => '',
                'phone' => '+7964545447',
                'password' => Hash::make('12345678'),
                'city_id' => 3,
            ],
            [
                'name' => 'Валерия Кошечка',
                'nickname' => 'kitty',
                'email' => 'valaria.mas@yandex.ru',
                'birth_date' => null,
                'avatar' => '',
                'phone' => '+79645433447',
                'password' => Hash::make('12345678'),
                'city_id' => 3,
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                $user
            );
        }

        // --- Генерация 1000 случайных пользователей ---
        $faker = Faker::create('ru_RU');

        for ($i = 0; $i < 10; $i++) {
            User::create([
                'name' => $faker->name(),
                'nickname' => $faker->userName(),
                'email' => $faker->unique()->safeEmail(),
                'birth_date' => $faker->date('d.m.Y'),
                'avatar' => null,
                'phone' => $faker->unique()->numerify('+79#########'),
                'password' => Hash::make('password'),
                'city_id' => rand(1, 30), // если у тебя 30 городов
                'is_admin' => false,
                'status' => 'active',
            ]);
        }
    }
}
