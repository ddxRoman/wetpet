<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CitiesTableSeeder extends Seeder
{
    public function run()
    {
        $cities = [
            'Москва',
            'Санкт-Петербург',
            'Казань',
            'Екатеринбург',
            'Новосибирск',
            'Нижний Новгород',
            'Самара',
            'Краснодар',
            'Белгород',
            'Воронеж',
            'Сочи',
            'Донецк',
            'Макеевка',
            'Луганск',
            'Севастополь',
            'Уфа',
            'Красноярск',
            'Владивосток',
        ];

        foreach ($cities as $name) {
            DB::table('cities')->insert([
                'name' => $name,
                'slug' => Str::slug($name),
                'verified' => 'verified',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
