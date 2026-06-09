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
            [
                'name' => 'Анна Вагаршаковна Симонян',
                'specialization' => 'Груммер',
                'date_of_birth' => Null,
                'city_id' => 31,
                'organization_id' => null,
                'experience' => Null,
                'exotic_animals' => 'Нет',
                'On_site_assistance' => 'нет',
                'photo' => '/specialists/anna_wagashkowna.avif',
                'description' =>
                '*гигиеническая стрижка
                    *вычесывание колтунов
                    *стрижка по стандартам породы
                    *стрижка когтей
                    *чистка ушей
                    *Экспресс линька'
            ],

            [
                'name' => 'Надежда Анатольевна Золотарёва',
                'specialization' => 'Кинолог, Груммер',
                'date_of_birth' => Null,
                'city_id' => 31,
                'organization_id' => null,
                'experience' => Null,
                'exotic_animals' => 'Нет',
                'On_site_assistance' => 'нет',
                'photo' => '/specialists/nadezhda_zolotoreva.jpg',
                'description' =>
                ' '
            ],
            [
                'name' => 'Ирина Юрьевна Арыныч',
                'specialization' => 'Груммер',
                'date_of_birth' => Null,
                'city_id' => 31,
                'organization_id' => null,
                'experience' => Null,
                'exotic_animals' => 'Нет',
                'On_site_assistance' => 'нет',
                'photo' => Null,
                'description' =>
                ' '
            ],
            [
                'name' => 'Наталья Владимировна Миронова',
                'specialization' => 'Груммер',
                'date_of_birth' => Null,
                'city_id' => 31,
                'organization_id' => null,
                'experience' => Null,
                'exotic_animals' => 'Нет',
                'On_site_assistance' => 'нет',
                'photo' => '/specialists/Mironova Natalya.jpg',
                'description' =>
                ' '
            ],
            [
                'name' => 'Анастасия Вадимовна Сибенкова',
                'specialization' => 'Груммер',
                'date_of_birth' => Null,
                'city_id' => 31,
                'organization_id' => null,
                'experience' => Null,
                'exotic_animals' => 'Нет',
                'On_site_assistance' => 'нет',
                'photo' => '/specialists/sibenkova-anastasiya-vadimovna.jpg',
                'description' =>
                ' '
            ],
            [
                'name' => 'Беата Мирчевна Палонина',
                'specialization' => 'Груммер',
                'date_of_birth' => Null,
                'city_id' => 31,
                'organization_id' => null,
                'experience' => Null,
                'exotic_animals' => 'Нет',
                'On_site_assistance' => 'нет',
                'photo' => Null,
                'description' =>
                ' '
            ],
            [
                'name' => 'Марианна Сергеевна Асатурян',
                'specialization' => 'Кинолог',
                'date_of_birth' => Null,
                'city_id' => 31,
                'organization_id' => null,
                'experience' => Null,
                'exotic_animals' => 'Нет',
                'On_site_assistance' => 'Да',
                'photo' => '/specialists/marianna-sergeevna-asaturian.webp',
                'description' =>
                ' '
            ],
            [
                'name' => 'Алексей Николаевич Сычов',
                'specialization' => 'Кинолог',
                'date_of_birth' => Null,
                'city_id' => 31,
                'organization_id' => null,
                'experience' => Null,
                'exotic_animals' => 'Нет',
                'On_site_assistance' => 'Да',
                'photo' => '/specialists/aleksei-nikolaevic-sycov.webp',
                'description' =>
                ' '
            ],
            [
                'name' => 'Анна Михайловна Трояновская',
                'specialization' => 'Кинолог',
                'date_of_birth' => Null,
                'city_id' => 31,
                'organization_id' => null,
                'experience' => Null,
                'exotic_animals' => 'Нет',
                'On_site_assistance' => 'Да',
                'photo' => '/specialists/anna-mixailovna-troianovskaia.webp',
                'description' =>
                ' '
            ],
            [
                'name' => 'Ольга Леонидовна Кормщикова',
                'specialization' => 'Кинолог',
                'date_of_birth' => Null,
                'city_id' => 31,
                'organization_id' => null,
                'experience' => Null,
                'exotic_animals' => 'Нет',
                'On_site_assistance' => 'Да',
                'photo' => '/specialists/olga-leonidovna-kormshhikova.webp',
                'description' =>
                ' '
            ],
            [
                'name' => 'Эвелина Олеговна Гогуа',
                'specialization' => 'Зооняня',
                'date_of_birth' => Null,
                'city_id' => 31,
                'organization_id' => null,
                'experience' => Null,
                'exotic_animals' => 'Нет',
                'On_site_assistance' => 'Да',
                'photo' => '/specialists/evelina-olegovna-gogua.webp',
                'description' =>
                ' '
            ],
            [
                'name' => 'Мария Анатольевна Зенченко',
                'specialization' => 'Зооняня',
                'date_of_birth' => Null,
                'city_id' => 31,
                'organization_id' => null,
                'experience' => Null,
                'exotic_animals' => 'Нет',
                'On_site_assistance' => 'Да',
                'photo' => '/specialists/mariia-anatolevna-zencenko.webp',
                'description' =>
                ' '
            ],
            [
                'name' => 'Григорий Николаевич Тугарин',
                'specialization' => 'Зоотакси, Зооняня',
                'date_of_birth' => Null,
                'city_id' => 31,
                'organization_id' => null,
                'experience' => Null,
                'exotic_animals' => 'Нет',
                'On_site_assistance' => 'Да',
                'photo' => '/specialists/grigorii-nikolaevic-tugarin.webp',
                'description' =>
                ' '
            ],
            [
                'name' => 'Елена Александровна Чалкина',
                'specialization' => 'Зоотакси, Зооняня',
                'date_of_birth' => Null,
                'city_id' => 31,
                'organization_id' => null,
                'experience' => Null,
                'exotic_animals' => 'Нет',
                'On_site_assistance' => 'Да',
                'photo' => '/specialists/elena-aleksandrovna-calkina.webp',
                'description' =>
                ' '
            ],
            [
                'name' => 'Елизавета Раджевна Устинова',
                'specialization' => 'Зоотакси, Зооняня',
                'date_of_birth' => Null,
                'city_id' => 31,
                'organization_id' => null,
                'experience' => Null,
                'exotic_animals' => 'Нет',
                'On_site_assistance' => 'Да',
                'photo' => '/specialists/elizaveta-radzevna-ustinova.webp',
                'description' =>
                ' '
            ],
            [
                'name' => 'Анастасия Вячеславовна Михайлюта',
                'specialization' => 'Зооняня',
                'date_of_birth' => Null,
                'city_id' => 31,
                'organization_id' => null,
                'experience' => Null,
                'exotic_animals' => 'Нет',
                'On_site_assistance' => 'Да',
                'photo' => '/specialists/anastasiia-viaceslavovna-mixailiuta.webp',
                'description' =>
                ' '
            ],
            [
                'name' => 'Марина Зарубина',
                'specialization' => 'Грумер',
                'date_of_birth' => null,
                'city_id' => 31,
                'organization_id' => 33,
                'experience' => 33, // С 1991 года
                'exotic_animals' => 'Нет',
                'On_site_assistance' => 'Нет',
                'photo' => '/specialists/willy_band/marina_zarubina.jpg',
                'description' => 'Основоположник грумерской династии Зарубиных. В груминге с 1991 года. Начинала с монопородного груминга шнауцеров. Один из родоначальников краснодарской индустрии груминга.',
            ],
            [
                'name' => 'Юлия Зарубина',
                'specialization' => 'Грумер',
                'date_of_birth' => null,
                'city_id' => 31,
                'organization_id' => 33,
                'experience' => 33, // С 1991 года
                'exotic_animals' => 'Нет',
                'On_site_assistance' => 'Нет',
                'photo' => '/specialists/willy_band/yuliya_zarubina.jpg',
                'description' => 'Основоположник грумерской династии Зарубиных. В профессии с 1991 года. Эксперт с огромным опытом выставочной подготовки и салонного груминга.',
            ],
            [
                'name' => 'Касьян Зарубин',
                'specialization' => 'Грумер-универсал',
                'date_of_birth' => null,
                'city_id' => 31,
                'organization_id' => 33,
                'experience' => 17, // С 2007 года
                'exotic_animals' => 'Нет',
                'On_site_assistance' => 'Нет',
                'photo' => '/specialists/willy_band/kasyan_zarubina.jpg',
                'description' => '3-е поколение династии Зарубиных. Дипломированный по системе EGA Грумер-Универсал. Первый на Юге России обладатель сертификата EGA «Мастер груминг-салона» (2014 г.). Прошел аттестацию у Умберто Леманна.',
            ],
            [
                'name' => 'Яровая Анастасия',
                'specialization' => 'Грумер',
                'date_of_birth' => null,
                'city_id' => 31,
                'organization_id' => 33,
                'experience' => null,
                'exotic_animals' => 'Нет',
                'On_site_assistance' => 'Нет',
                'photo' => '/specialists/willy_band/anastasiya_yarovaya.jpg',
                'description' => 'Профессиональный грумер, нашедший призвание после смены деятельности в финансах. Посвящает жизнь качественному и профессиональному уходу за животными.',
            ],
            [
                'name' => 'Кирияк Надежда',
                'specialization' => 'Грумер',
                'date_of_birth' => null,
                'city_id' => 31,
                'organization_id' => 33,
                'experience' => null,
                'exotic_animals' => 'Нет',
                'On_site_assistance' => 'Нет',
                'photo' => '/specialists/willy_band/nadezhda_kiriyak.jpg',
                'description' => 'Специалист команды Willy Band. Постоянно совершенствует навыки в области современного салонного груминга собак и кошек.',
            ],
            [
                'name' => 'Марина',
                'specialization' => 'Шеф-грумер',
                'date_of_birth' => null,
                'city_id' => 31,
                'organization_id' => null,
                'experience' => null,
                'exotic_animals' => 'Нет',
                'On_site_assistance' => 'Нет',
                'photo' => '/specialists/pesdyk/Marina.jpg',
                'description' => 'Специалист команды Willy Band. Постоянно совершенствует навыки в области современного салонного груминга собак и кошек.',
            ],
            [
                'name' => 'Анна',
                'specialization' => 'Грумер',
                'date_of_birth' => null,
                'city_id' => 31,
                'organization_id' => null,
                'experience' => null,
                'exotic_animals' => 'Нет',
                'On_site_assistance' => 'Нет',
                'photo' => '/specialists/pesdyk/Anna.jpg',
                'description' => 'Специалист команды Willy Band. Постоянно совершенствует навыки в области современного салонного груминга собак и кошек.',
            ],
            [
                'name' => 'Юлианна',
                'specialization' => 'Шеф-грумер',
                'date_of_birth' => null,
                'city_id' => 31,
                'organization_id' => null,
                'experience' => null,
                'exotic_animals' => 'Нет',
                'On_site_assistance' => 'Нет',
                'photo' => '/specialists/pesdyk/Yulianna.webp',
                'description' => 'Специалист команды Willy Band. Постоянно совершенствует навыки в области современного салонного груминга собак и кошек.',
            ],
        ];
        $data = collect($specialists)->map(function ($specialist) {
            return array_merge($specialist, [
                'organization_id' => $specialist['organization_id'],
                'photo' => $specialist['photo'],
                'slug' => Str::slug($specialist['name']),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        })->toArray();

        DB::table('specialists')->insert($data);
    }
}
