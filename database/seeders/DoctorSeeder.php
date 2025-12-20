<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DoctorSeeder extends Seeder
{
    /**
     * Запуск сидера
     */
    public function run(): void
    {
        // Отключаем внешние ключи и очищаем таблицу
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('doctors')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $doctors = [
            [
                'name' => 'Решетникова Наталья Генриховна',
                'date_of_birth' => NULL,
                'city_id' => 21,
                'specialization' => 'Главный ветеринарный врач',
                'clinic_id' => 1,
                'experience' => NULL,
                'exotic_animals' => NULL,
                'On_site_assistance' => NULL,
                'photo' => 'doctors/biosfera/reshetnikova-200x200.jpg',
                'description' => 'основатель и владелец ветеринарной клиники «Биосфера»',
            ],
            [
                'name' => 'Тимченко Дарья Борисовна',
                'date_of_birth' => NULL,
                'city_id' => 21,
                'specialization' => 'хирургия, онкология, стоматология, узи, эндоскопия и малоинвазивная хирургия.',
                'clinic_id' => 1,
                'experience' => NULL,
                'exotic_animals' => NULL,
                'On_site_assistance' => NULL,
                'photo' => 'doctors/biosfera/tymchenko.png',
                'description' => 'Заместитель главного врача',
            ],
            [
                'name' => 'Лопушинская Ангелина Михайловна',
                'date_of_birth' => NULL,
                'city_id' => 21,
                'specialization' => 'эндокринология, офтальмология, дерматология, нефрология',
                'clinic_id' => 1,
                'experience' => NULL,
                'exotic_animals' => NULL,
                'On_site_assistance' => NULL,
                'photo' => 'doctors/biosfera/lopyshanskay.png',
                'description' => 'Ветеринарный врач Заведующая отделения терапии',
            ],
            [
                'name' => 'Синельникова Версения Сергеевна',
                'date_of_birth' => NULL,
                'city_id' => 21,
                'specialization' => 'интенсивная терапия, анестезиология',
                'clinic_id' => 1,
                'experience' => NULL,
                'exotic_animals' => NULL,
                'On_site_assistance' => NULL,
                'photo' => 'doctors/biosfera/Sinelnikova.jpeg',
                'description' => 'Ветеринарный врач Заведующая отделения ОРИТ ',
            ],
            [
                'name' => 'Федосеева Наталья Геннадьевна',
                'date_of_birth' => NULL,
                'city_id' => 21,
                'specialization' => 'терапия, УЗИ',
                'clinic_id' => 1,
                'experience' => NULL,
                'exotic_animals' => NULL,
                'On_site_assistance' => NULL,
                'photo' => 'doctors/biosfera/fedoseeva.jpg',
                'description' => 'Ветеринарный врач',
            ],
            [
                'name' => 'Глушкова Юлия Владимировна',
                'date_of_birth' => NULL,
                'city_id' => 21,
                'specialization' => 'рентгенология, терапия',
                'clinic_id' => 1,
                'experience' => NULL,
                'exotic_animals' => NULL,
                'On_site_assistance' => NULL,
                'photo' => 'doctors/biosfera/glushkova.jpg',
                'description' => 'Ветеринарный врач',
            ],
            [
                'name' => 'Макаренко Вероника Александровна',
                'date_of_birth' => NULL,
                'city_id' => 21,
                'specialization' => 'кардиология, анестезиология, интенсивная терапия',
                'clinic_id' => 1,
                'experience' => NULL,
                'exotic_animals' => NULL,
                'On_site_assistance' => NULL,
                'photo' => 'doctors/biosfera/Makarenko-Veronika-Aleksandrovna-3h3.jpg',
                'description' => 'Ветеринарный врач',
            ],
            [
                'name' => 'Соболева Батина Гажиевна',
                'date_of_birth' => NULL,
                'city_id' => 21,
                'specialization' => 'терапия, репродуктология, УЗИ диагностика',
                'clinic_id' => 1,
                'experience' => NULL,
                'exotic_animals' => NULL,
                'On_site_assistance' => NULL,
                'photo' => 'doctors/biosfera/Batina.jpg',
                'description' => 'Ветеринарный врач',
            ],
            [
                'name' => 'Абауи Мишель Муфид',
                'date_of_birth' => NULL,
                'city_id' => 21,
                'specialization' => 'хирургия, неврология, терапия',
                'clinic_id' => 1,
                'experience' => NULL,
                'exotic_animals' => NULL,
                'On_site_assistance' => NULL,
                'photo' => 'doctors/biosfera/Abaui.jpg',
                'description' => 'Ветеринарный врач',
            ],
            [
                'name' => 'Стаценко Татьяна Сергеевна',
                'date_of_birth' => NULL,
                'city_id' => 21,
                'specialization' => 'Асистент',
                'clinic_id' => 1,
                'experience' => NULL,
                'exotic_animals' => NULL,
                'On_site_assistance' => NULL,
                'photo' => 'doctors/biosfera/stacienko-Tanja.jpeg',
                'description' => 'ассистент ветеринарного врача',
            ],
            [
                'name' => 'Мартиросян Гагик Артавазович',
                'date_of_birth' => NULL,
                'city_id' => 21,
                'specialization' => 'хирургия, эндоскопия, терапия',
                'clinic_id' => 1,
                'experience' => NULL,
                'exotic_animals' => NULL,
                'On_site_assistance' => NULL,
                'photo' => 'doctors/biosfera/martyrosiayan.png',
                'description' => '',
            ],
        ];

        foreach ($doctors as $doctor) {
            DB::table('doctors')->insert([
                ...$doctor,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
