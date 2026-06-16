<?php

namespace Database\Seeders;

use App\Models\News;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class NewsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Твои заготовленные качественные новости
        $newsItems = [
            [
                'title' => 'Открытие нового ветеринарного центра «Зверозор» в Краснодаре',
                'region_id' => 31, 
                'image' => 'news/covers/clinic_open.webp',
                                    'seo_title'   =>   'Test', 
                    'seo_description'       =>  'Test',
                    'og_image'       => 'Test', 
                'images' => [
                    'news/galleries/open_1.webp',
                    'news/galleries/open_2.webp',
                    'news/galleries/open_3.webp',
                ],

                'excerpt' => 'Рады сообщить об открытии ультрасовременного ветеринарного комплекса с круглосуточным стационаром и передовым диагностическим оборудованием.',
                'content' => '<p>Новый ветеринарный комплекс предлагает полный спектр услуг для ваших любимцев. У нас работают лучшие хирурги, терапевты и узкопрофильные специалисты региона.</p><p>В клинике установлено современное оборудование для МРТ и КТ-диагностики, что позволит выявлять сложные патологии на ранних стадиях. Ждем вас и ваших питомцев круглосуточно!</p>',
                'is_published' => true,
                'views' => 142,
            ],
            [
                'title' => 'Как правильно подготовить собаку к вакцинации: советы ветерира',
                'region_id' => null, 
                'image' => 'news/covers/vaccination_dog.webp',
                                                    'seo_title'   =>   'Test', 
                    'seo_description'       =>  'Test',
                    'og_image'       => 'Test', 
                'images' => [
                    'news/galleries/vaccine_guide_1.webp',
                ],
                'excerpt' => 'Сезон прививок в самом разгаре. Разбираемся вместе с ведущими экспертами, какие анализы необходимо сдать перед процедурой и как уберечь питомца от стресса.',
                'content' => '<p>Вакцинация — это главный способ защитить собаку от смертельно опасных инфекций, таких как чума плотоядных, парвовирусный энтерит и бешенство.</p><p>За 10–14 дней до прививки обязательно проведите дегельминтизацию. В день процедуры собака должна быть клинически здорова, с нормальной температурой тела. Ограничьте сильные физические нагрузки в первые сутки после укола.</p>',
                'is_published' => true,
                'views' => 389,
            ],
            [
                'title' => 'Правильный уход за экзотическими животными в домашних условиях',
                'region_id' => null, 
                'image' => 'news/covers/exotic_pets.webp',
                                                    'seo_title'   =>   'Test', 
                    'seo_description'       =>  'Test',
                    'og_image'       => 'Test', 
                'images' => [], 
                'excerpt' => 'Содержание рептилий, шиншилл и редких птиц требует соблюдения строгих правил температурного режима и рациона.',
                'content' => '<p>Каждый владелец игуаны или хамелеона сталкивается с необходимостью создания правильного микроклимата. Обычной коробкой или стандартным аквариумом здесь не обойтись.</p><p>Вам понадобятся УФ-лампы определенного спектра, термоковрики и специализированные подкормки с кальцием. Помните, что самолечение экзотов часто приводит к необратимым последствиям — при первых симптомах вялости обращайтесь к ратологам и герпетологам.</p>',
                'is_published' => true,
                'views' => 95,
            ],
        ];

        // Создаем или обновляем фиксированные новости
        foreach ($newsItems as $item) {
            $slug = Str::slug($item['title']);

            News::updateOrCreate(
                ['slug' => $slug],
                [
                    'region_id'    => $item['region_id'],
                    'title'        => $item['title'],
                    'image'        => $item['image'],
                    'images'       => $item['images'], 
                    'seo_title'       => $item['seo_title'], 
                    'seo_description'       => $item['seo_description'], 
                    'og_image'       => $item['og_image'], 
                    'excerpt'      => $item['excerpt'],
                    'content'      => $item['content'],
                    'is_published' => $item['is_published'],
                    'views'        => $item['views'],
                ]
            );
        }

        // 2. Генерация 10 случайных новостей (с русской локализацией для Faker)
        $faker = \Faker\Factory::create('ru_RU');

        for ($i = 1; $i <= 30; $i++) {
            // Генерируем случайный, но читаемый заголовок
            $title = rtrim($faker->realText(50), '.'); 
            $slug = Str::slug($title);

            // Обернем случайный текст в параграфы для контента
            $content = '<p>' . $faker->realText(300) . '</p><p>' . $faker->realText(200) . '</p>';

            News::updateOrCreate(
                ['slug' => $slug],
                [
                    // Случайно ставим либо Краснодар (31), либо общероссийскую (null)
                    'region_id'    => $faker->randomElement([31, null]), 
                    'title'        => $title,
                    // Используем заглушки картинок, чтобы верстка не плыла
                    'image'        => 'news/covers/placeholder_' . $faker->numberBetween(1, 3) . '.webp',
                    'images'       => [
                        'news/galleries/gallery_placeholder_1.webp',
                        'news/galleries/gallery_placeholder_2.webp'
                    ], 
                    'excerpt'      => $faker->realText(150),
                    'content'      => $content,
                    'is_published' => true,
                    'views'        => $faker->numberBetween(10, 500),
                ]
            );
        }
    }
}