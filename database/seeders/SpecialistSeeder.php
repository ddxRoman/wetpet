<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SpecialistSeeder extends Seeder
{
public function run(): void
{
    // 1. Отключаем проверку внешних ключей
    DB::statement('SET FOREIGN_KEY_CHECKS=0;');
    
    // 2. Очищаем таблицу
    DB::table('specialists')->truncate();
    
    // 3. Включаем проверку обратно
    DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $specialists = [
            ['name' => 'Анна Вагаршаковна Симонян', 
            'specialization' => 'Груммер', 
            'date_of_birth' => Null, 
            'city_id' => 31, 
            'experience' => Null, 
            'exotic_animals' => 'Нет', 
            'On_site_assistance' => 'нет', 
            'photo' => Null,
            'description' => 
                    '*гигиеническая стрижка
                    *вычесывание колтунов
                    *стрижка по стандартам породы
                    *стрижка когтей
                    *чистка ушей
                    *Экспресс линька'],

            ['name' => 'Надежда Анатольевна Золотарёва', 
            'specialization' => 'Кинолог, Груммер', 
            'date_of_birth' => Null, 
            'city_id' => 31, 
            'experience' => Null, 
            'exotic_animals' => 'Нет', 
            'On_site_assistance' => 'нет', 
            'photo' => Null,
            'description' => 
            ' '],
            ['name' => 'Ирина Юрьевна Арыныч', 
            'specialization' => 'Груммер', 
            'date_of_birth' => Null, 
            'city_id' => 31, 
            'experience' => Null, 
            'exotic_animals' => 'Нет', 
            'On_site_assistance' => 'нет', 
            'photo' => Null,
            'description' => 
            ' '],
            ['name' => 'Наталья Владимировна Миронова', 
            'specialization' => 'Груммер', 
            'date_of_birth' => Null, 
            'city_id' => 31, 
            'experience' => Null, 
            'exotic_animals' => 'Нет', 
            'On_site_assistance' => 'нет', 
            'photo' => Null,
            'description' => 
            ' '],
            ['name' => 'Анастасия Вадимовна Сибенкова', 
            'specialization' => 'Груммер', 
            'date_of_birth' => Null, 
            'city_id' => 31, 
            'experience' => Null, 
            'exotic_animals' => 'Нет', 
            'On_site_assistance' => 'нет', 
            'photo' => Null,
            'description' => 
            ' '],
            ['name' => 'Беата Мирчевна Палонина', 
            'specialization' => 'Груммер', 
            'date_of_birth' => Null, 
            'city_id' => 31, 
            'experience' => Null, 
            'exotic_animals' => 'Нет', 
            'On_site_assistance' => 'нет', 
            'photo' => Null,
            'description' => 
            ' '],
            ['name' => 'Марианна Сергеевна Асатурян', 
            'specialization' => 'Кинолог', 
            'date_of_birth' => Null, 
            'city_id' => 31, 
            'experience' => Null, 
            'exotic_animals' => 'Нет', 
            'On_site_assistance' => 'Да', 
            'photo' => 'specialists/marianna-sergeevna-asaturian.webp',
            'description' => 
            ' '],
            ['name' => 'Алексей Николаевич Сычов', 
            'specialization' => 'Кинолог', 
            'date_of_birth' => Null, 
            'city_id' => 31, 
            'experience' => Null, 
            'exotic_animals' => 'Нет', 
            'On_site_assistance' => 'Да', 
            'photo' => 'specialists/aleksei-nikolaevic-sycov.webp',
            'description' => 
            ' '],
            ['name' => 'Анна Михайловна Трояновская', 
            'specialization' => 'Кинолог', 
            'date_of_birth' => Null, 
            'city_id' => 31, 
            'experience' => Null, 
            'exotic_animals' => 'Нет', 
            'On_site_assistance' => 'Да', 
            'photo' => 'specialists/anna-mixailovna-troianovskaia.webp',
            'description' => 
            ' '],
            ['name' => 'Ольга Леонидовна Кормщикова', 
            'specialization' => 'Кинолог', 
            'date_of_birth' => Null, 
            'city_id' => 31, 
            'experience' => Null, 
            'exotic_animals' => 'Нет', 
            'On_site_assistance' => 'Да', 
            'photo' => 'specialists/olga-leonidovna-kormshhikova.webp',
            'description' => 
            ' '],
            ['name' => 'Эвелина Олеговна Гогуа', 
            'specialization' => 'Зооняня', 
            'date_of_birth' => Null, 
            'city_id' => 31, 
            'experience' => Null, 
            'exotic_animals' => 'Нет', 
            'On_site_assistance' => 'Да', 
            'photo' => 'specialists/evelina-olegovna-gogua.webp',
            'description' => 
            ' '],
            ['name' => 'Мария Анатольевна Зенченко', 
            'specialization' => 'Зооняня', 
            'date_of_birth' => Null, 
            'city_id' => 31, 
            'experience' => Null, 
            'exotic_animals' => 'Нет', 
            'On_site_assistance' => 'Да', 
            'photo' => 'specialists/mariia-anatolevna-zencenko.webp',
            'description' => 
            ' '],
            ['name' => 'Григорий Николаевич Тугарин', 
            'specialization' => 'Зоотакси, Зооняня', 
            'date_of_birth' => Null, 
            'city_id' => 31, 
            'experience' => Null, 
            'exotic_animals' => 'Нет', 
            'On_site_assistance' => 'Да', 
            'photo' => 'specialists/grigorii-nikolaevic-tugarin.webp',
            'description' => 
            ' '],
            ['name' => 'Елена Александровна Чалкина', 
            'specialization' => 'Зоотакси, Зооняня', 
            'date_of_birth' => Null, 
            'city_id' => 31, 
            'experience' => Null, 
            'exotic_animals' => 'Нет', 
            'On_site_assistance' => 'Да', 
            'photo' => 'specialists/elena-aleksandrovna-calkina.webp',
            'description' => 
            ' '],
            ['name' => 'Елизавета Раджевна Устинова', 
            'specialization' => 'Зоотакси, Зооняня', 
            'date_of_birth' => Null, 
            'city_id' => 31, 
            'experience' => Null, 
            'exotic_animals' => 'Нет', 
            'On_site_assistance' => 'Да', 
            'photo' => 'specialists/elizaveta-radzevna-ustinova.webp',
            'description' => 
            ' '],
            ['name' => 'Анастасия Вячеславовна Михайлюта', 
            'specialization' => 'Зооняня', 
            'date_of_birth' => Null, 
            'city_id' => 31, 
            'experience' => Null, 
            'exotic_animals' => 'Нет', 
            'On_site_assistance' => 'Да', 
            'photo' => 'specialists/anastasiia-viaceslavovna-mixailiuta.webp',
            'description' => 
            ' '],
                ];
    $data = collect($specialists)->map(function ($specialist) {
        return array_merge($specialist, [
            'organization_id' => null,
            'photo' => $specialist['photo'],
            'slug' => Str::slug($specialist['name']), 
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    })->toArray();

    DB::table('specialists')->insert($data);
}
}