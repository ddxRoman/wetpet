<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserTableSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Роман Оксентий',
                'nickname' => 'DDX',
                'email' => 'ddxman@mail.ru',
                'birth_date' => '12.05.1994',
                'avatar' => 'avatars/ejWLZaF7SgpdXEToQxdi3j9n8LSStnuFMFkfLZdE.jpg',
                'phone' => '+79667404577',
                'password' => Hash::make('12345678'),
                'city_id' => 20, // ID города из таблицы cities
            ],
            [
                'name' => 'Анна Петрова',
                'nickname' => 'annapet',
                'email' => 'ddx2man@mail.ru',
                                'birth_date' => '-----',
                                'avatar' => '',
                                
                'phone' => '+7964334577',
                'password' => Hash::make('12345678'),
                'city_id' => 2,
            ],
            [
                'name' => 'Сергей Смирнов',
                'nickname' => 'sergeydev',
                'email' => 'ddx1man@mail.ru',
                                'birth_date' => '-----',
                                'avatar' => '',
                                
                'phone' => '+7964545447',
                'password' => Hash::make('12345678'),
                'city_id' => 3,
            ],
            [
                'name' => 'Валерия Кошечка',
                'nickname' => 'kitty',
                'email' => 'valaria.mas@yandex.ru',
                                'birth_date' => '-----',
                                'avatar' => '',
                                
                'phone' => '+79645433447',
                'password' => Hash::make('12345678'),
                'city_id' => 3,
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']], // если пользователь уже есть — обновим
                $user
            );
        }
    }
}
