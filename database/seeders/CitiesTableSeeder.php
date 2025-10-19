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
        ];

        foreach ($cities as $name) {
            DB::table('cities')->insert([
                'name' => $name,
                'slug' => Str::slug($name),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
