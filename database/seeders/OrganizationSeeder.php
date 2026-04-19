<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Organization;
use Illuminate\Support\Str;

class OrganizationSeeder extends Seeder
{
    public function run(): void
    {
        $clinics = [
            'ВетКлассик', 'ЗооДоктор', 'Белый Клык', 'Хвостатый Друг', 'Айболит 24',
            'Ветеринарный Центр №1', 'Лапа Помощи', 'Ковчег', 'БиоРитм', 'ЗооСпектр',
            'Друг Человека', 'ВетЗабота', 'Пушистый Мир', 'Верные Друзья', 'ЗооСтатус',
            'МедВет', 'ЗооЛюкс', 'Энимал Центр', 'ВетСервис', 'Доктор Хвостов'
        ];

        foreach ($clinics as $name) {
            Organization::create([
                'name' => $name,
                'slug' => Str::slug($name) . '-' . rand(100, 999),
                'country' => 'Россия',
                'region' => 'Краснодарский край', // Можешь заменить на свой
                'city' => 'Краснодар',
                'street' => fake()->streetName(),
                'house' => fake()->buildingNumber(),
                'address_comment' => 'Вход со двора',
                'description' => 'Профессиональная ветеринарная помощь для ваших питомцев. Современное оборудование и опытные врачи.',
                'phone1' => '+7 (999) ' . rand(100, 999) . '-' . rand(10, 99) . '-' . rand(10, 99),
                'email' => Str::slug($name) . '@example.com',
                'schedule' => 'с 09:00 до 21:00',
                'workdays' => 'Пн–Вс',
                'website' => 'https://' . Str::slug($name) . '.ru',
            ]);
        }

        $this->command->info('Создано 20 организаций.');
    }
}