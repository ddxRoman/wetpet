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
                'avatar' => 'avatars/me-avatar.jpg',
                'phone' => '+79667404577',
                'password' => Hash::make('12345678'),
                'city_id' => 31,
                'is_admin' => true,
                'created_at' => '2013-04-12 06:51:47',
            ],
            [
                'name' => 'Анастасия',
                'nickname' => Null,
                'email' => 'worklaca@mail.ru',
                'birth_date' => '03.10.1988',
                'avatar' => Null,
                'phone' => Null,
                'password' => 'lacamomila158',
                'city_id' => 31,
                'is_admin' => false,
                'created_at' => '2026-03-10 06:51:47',

            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                $user
            );
        }

        // // --- Генерация 1000 случайных пользователей ---
        // $faker = Faker::create('ru_RU');

        // for ($i = 0; $i < 10; $i++) {
        //     User::create([
        //         'name' => $faker->name(),
        //         'nickname' => $faker->userName(),
        //         'email' => $faker->unique()->safeEmail(),
        //         'birth_date' => $faker->date('d.m.Y'),
        //         'avatar' => null,
        //         'phone' => $faker->unique()->numerify('+79#########'),
        //         'password' => Hash::make('password'),
        //         'city_id' => rand(1, 30), // если у тебя 30 городов
        //         'is_admin' => false,
        //         'status' => 'active',
        //     ]);
        // }
    }
}
