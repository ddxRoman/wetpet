<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Service;

class ServiceSeeder extends Seeder
{
    /**
     * Запуск сидера
     */
    public function run(): void
    {
        // Очищаем таблицу
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('services')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $services = [
            [
                'name' => 'Первичный приём',
                'specialization' => 'Терапия',
                'specialization_doctor' => 'Терапевт',
            ],
            [
                'name' => 'Повторный приём',
                'specialization' => 'Терапия',
                'specialization_doctor' => 'Терапевт',
            ],
            [
                'name' => 'Общий осмотр',
                'specialization' => 'Терапия',
                'specialization_doctor' => 'Терапевт',
            ],
                        [
                'name' => 'Консультация',
                'specialization' => 'Терапия',
                'specialization_doctor' => 'Терапевт',
            ],
                        [
                'name' => 'Узи Сердца',
                'specialization' => 'Диагностика',
                'specialization_doctor' => 'Диагност',
            ],
                        [
                'name' => 'Узи брюшной полости',
                'specialization' => 'Диагностика',
                'specialization_doctor' => 'Диагност',
            ],
                        [
                'name' => 'Рентген грудной клетки',
                'specialization' => 'Рентген',
                'specialization_doctor' => 'Хирург',
            ],
                        [
                'name' => 'Рентген конечностей',
                'specialization' => 'Рентген',
                'specialization_doctor' => 'Хирург',
            ],
                        [
                'name' => 'Анализ крови общий',
                'specialization' => 'Диагностика',
                'specialization_doctor' => 'Диагност',
            ],
    // Грумер
    ['name' => 'Гигиеническая стрижка','specialization' => 'Грумминг', 'specialization_doctor' => 'Грумер'],
    ['name' => 'Модельная стрижка по стандарту породы', 'specialization' => 'Грумминг', 'specialization_doctor' => 'Грумер'],
    ['name' => 'Тримминг (для жесткошерстных пород)', 'specialization' => 'Грумминг', 'specialization_doctor' => 'Грумер'],
    ['name' => 'Экспресс-линька', 'specialization' => 'Грумминг', 'specialization_doctor' => 'Грумер'],
    ['name' => 'Купание и профессиональная сушка', 'specialization' => 'Грумминг', 'specialization_doctor' => 'Грумер'],
    ['name' => 'Разбор колтунов любой сложности', 'specialization' => 'Грумминг', 'specialization_doctor' => 'Грумер'],
    ['name' => 'Стрижка когтей и подпиливание', 'specialization' => 'Грумминг', 'specialization_doctor' => 'Грумер'],
    ['name' => 'Чистка ушей и удаление лишней шерсти', 'specialization' => 'Грумминг', 'specialization_doctor' => 'Грумер'],
    ['name' => 'Гигиена зубов (без ультразвука)', 'specialization' => 'Грумминг', 'specialization_doctor' => 'Грумер'],
    ['name' => 'Окрашивание шерсти (креативный груминг)', 'specialization' => 'Грумминг', 'specialization_doctor' => 'Грумер'],

    // Кинолог
    ['name' => 'Базовый курс дрессировки (ОКД)', 'specialization' => 'Дрессировка', 'specialization_doctor' => 'Кинолог'],
    ['name' => 'Воспитание щенка с нуля', 'specialization' => 'Дрессировка', 'specialization_doctor' => 'Кинолог'],
    ['name' => 'Коррекция агрессивного поведения', 'specialization' => 'Дрессировка', 'specialization_doctor' => 'Кинолог'],
    ['name' => 'Социализация собаки в городских условиях', 'specialization' => 'Дрессировка', 'specialization_doctor' => 'Кинолог'],
    ['name' => 'Курс «Управляемая городская собака»', 'specialization' => 'Дрессировка', 'specialization_doctor' => 'Кинолог'],
    ['name' => 'Обучение командам на расстоянии', 'specialization' => 'Дрессировка', 'specialization_doctor' => 'Кинолог'],
    ['name' => 'Подготовка к сдаче нормативов', 'specialization' => 'Дрессировка', 'specialization_doctor' => 'Кинолог'],
    ['name' => 'Индивидуальная консультация по выбору породы', 'specialization' => 'Дрессировка', 'specialization_doctor' => 'Кинолог'],
    ['name' => 'Отработка выдержки и самоконтроля', 'specialization' => 'Дрессировка', 'specialization_doctor' => 'Кинолог'],
    ['name' => 'Работа с фобиями и страхами на улице', 'specialization' => 'Дрессировка', 'specialization_doctor' => 'Кинолог'],

    // Тренер по аджилити
    ['name' => 'Ознакомительное занятие на снарядах', 'specialization' => 'Спорт', 'specialization_doctor' => 'Тренер по аджилити'],
    ['name' => 'Отработка преодоления барьеров', 'specialization' => 'Спорт', 'specialization_doctor' => 'Тренер по аджилити'],
    ['name' => 'Обучение прохождению туннеля', 'specialization' => 'Спорт', 'specialization_doctor' => 'Тренер по аджилити'],
    ['name' => 'Работа со слаломом', 'specialization' => 'Спорт', 'specialization_doctor' => 'Тренер по аджилити'],
    ['name' => 'Скоростное прохождение трассы', 'specialization' => 'Спорт', 'specialization_doctor' => 'Тренер по аджилити'],
    ['name' => 'Укрепление контакта проводника и собаки', 'specialization' => 'Спорт', 'specialization_doctor' => 'Тренер по аджилити'],
    ['name' => 'Подготовка к соревнованиям по аджилити', 'specialization' => 'Спорт', 'specialization_doctor' => 'Тренер по аджилити'],
    ['name' => 'Обучение зонарным снарядам (бум, качели)', 'specialization' => 'Спорт', 'specialization_doctor' => 'Тренер по аджилити'],
    ['name' => 'Фитнес-тренинг для спортивных собак', 'specialization' => 'Спорт', 'specialization_doctor' => 'Тренер по аджилити'],
    ['name' => 'Групповые тренировки на выносливость', 'specialization' => 'Спорт', 'specialization_doctor' => 'Тренер по аджилити'],

    // Зоопсихолог
    ['name' => 'Консультация по деструктивному поведению', 'specialization' => 'Психология', 'specialization_doctor' => 'Зоопсихолог'],
    ['name' => 'Решение проблем с чистоплотностью в доме', 'specialization' => 'Психология', 'specialization_doctor' => 'Зоопсихолог'],
    ['name' => 'Работа с сепарационной тревогой (страх одиночества)', 'specialization' => 'Психология', 'specialization_doctor' => 'Зоопсихолог'],
    ['name' => 'Помощь при адаптации животного из приюта', 'specialization' => 'Психология', 'specialization_doctor' => 'Зоопсихолог'],
    ['name' => 'Налаживание контакта между несколькими питомцами', 'specialization' => 'Психология', 'specialization_doctor' => 'Зоопсихолог'],
    ['name' => 'Подготовка животного к появлению ребенка в семье', 'specialization' => 'Психология', 'specialization_doctor' => 'Зоопсихолог'],
    ['name' => 'Анализ причин чрезмерного лая или вокала', 'specialization' => 'Психология', 'specialization_doctor' => 'Зоопсихолог'],
    ['name' => 'Коррекция пищевого поведения', 'specialization' => 'Психология', 'specialization_doctor' => 'Зоопсихолог'],
    ['name' => 'Оценка психоэмоционального состояния питомца', 'specialization' => 'Психология', 'specialization_doctor' => 'Зоопсихолог'],
    ['name' => 'Разработка программы обогащения среды', 'specialization' => 'Психология', 'specialization_doctor' => 'Зоопсихолог'],

    // Фелинолог
    ['name' => 'Консультация по подбору породы кошки', 'specialization' => 'Кошки', 'specialization_doctor' => 'Фелинолог'],
    ['name' => 'Оценка экстерьера для выставок', 'specialization' => 'Кошки', 'specialization_doctor' => 'Фелинолог'],
    ['name' => 'Подбор пар для племенного разведения', 'specialization' => 'Кошки', 'specialization_doctor' => 'Фелинолог'],
    ['name' => 'Консультация по генетике окрасов', 'specialization' => 'Кошки', 'specialization_doctor' => 'Фелинолог'],
    ['name' => 'Сопровождение во время беременности и родов', 'specialization' => 'Кошки', 'specialization_doctor' => 'Фелинолог'],
    ['name' => 'Разработка рациона для котят', 'specialization' => 'Кошки', 'specialization_doctor' => 'Фелинолог'],
    ['name' => 'Помощь в оформлении документов (родословные)', 'specialization' => 'Кошки', 'specialization_doctor' => 'Фелинолог'],
    ['name' => 'Экспертиза племенного качества помета', 'specialization' => 'Кошки', 'specialization_doctor' => 'Фелинолог'],
    ['name' => 'Рекомендации по уходу за выставочной шерстью', 'specialization' => 'Кошки', 'specialization_doctor' => 'Фелинолог'],
    ['name' => 'Обучение основам груминга кошек', 'specialization' => 'Кошки', 'specialization_doctor' => 'Фелинолог'],

    // Заводчик
    ['name' => 'Продажа породистых щенков/котят', 'specialization' => 'Разведение', 'specialization_doctor' => 'Заводчик'],
    ['name' => 'Резервирование животных из будущих пометов', 'specialization' => 'Разведение', 'specialization_doctor' => 'Заводчик'],
    ['name' => 'Консультативная поддержка владельцев 24/7', 'specialization' => 'Разведение', 'specialization_doctor' => 'Заводчик'],
    ['name' => 'Услуги по вязке (племенные кобели/коты)', 'specialization' => 'Разведение', 'specialization_doctor' => 'Заводчик'],
    ['name' => 'Помощь в предпродажной подготовке помета', 'specialization' => 'Разведение', 'specialization_doctor' => 'Заводчик'],
    ['name' => 'Оформление акта вязки и чипирование', 'specialization' => 'Разведение', 'specialization_doctor' => 'Заводчик'],
    ['name' => 'Передержка выпускников питомника', 'specialization' => 'Разведение', 'specialization_doctor' => 'Заводчик'],
    ['name' => 'Организация доставки питомца в другие города', 'specialization' => 'Разведение', 'specialization_doctor' => 'Заводчик'],
    ['name' => 'Клеймение помета', 'specialization' => 'Разведение', 'specialization_doctor' => 'Заводчик'],
    ['name' => 'Инструктаж по особенностям развития линии', 'specialization' => 'Разведение', 'specialization_doctor' => 'Заводчик'],

    // Зооняня
    ['name' => 'Дневной присмотр на дому у владельца', 'specialization' => 'Уход', 'specialization_doctor' => 'Зооняня'],
    ['name' => 'Кормление по установленному графику', 'specialization' => 'Уход', 'specialization_doctor' => 'Зооняня'],
    ['name' => 'Гигиенический выгул (до 30 минут)', 'specialization' => 'Уход', 'specialization_doctor' => 'Зооняня'],
    ['name' => 'Активные игры и развлечение питомца', 'specialization' => 'Уход', 'specialization_doctor' => 'Зооняня'],
    ['name' => 'Дача лекарственных препаратов (таблетки/капли)', 'specialization' => 'Уход', 'specialization_doctor' => 'Зооняня'],
    ['name' => 'Фото и видеоотчеты в режиме реального времени', 'specialization' => 'Уход', 'specialization_doctor' => 'Зооняня'],
    ['name' => 'Присмотр за пожилыми животными', 'specialization' => 'Уход', 'specialization_doctor' => 'Зооняня'],
    ['name' => 'Уборка туалета или клеток', 'specialization' => 'Уход', 'specialization_doctor' => 'Зооняня'],
    ['name' => 'Сопровождение в ветеринарную клинику', 'specialization' => 'Уход', 'specialization_doctor' => 'Зооняня'],
    ['name' => 'Ночное дежурство с питомцем', 'specialization' => 'Уход', 'specialization_doctor' => 'Зооняня'],
        ];
foreach ($services as $service) {
    DB::table('services')->insert([
        ...$service,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}
        $this->command->info('✅ Услуги успешно добавлены.');
    }
}
