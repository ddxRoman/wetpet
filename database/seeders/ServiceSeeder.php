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
            // Терапевт
['name' => 'Первичный приём','specialization' => 'Терапия', 'specialization_doctor' => 'Терапевт'],
['name' => 'Повторный приём','specialization' => 'Терапия', 'specialization_doctor' => 'Терапевт'],
['name' => 'Общий осмотр животного','specialization' => 'Терапия', 'specialization_doctor' => 'Терапевт'],
['name' => 'Диагностика заболеваний','specialization' => 'Терапия', 'specialization_doctor' => 'Терапевт'],
['name' => 'Назначение лечения','specialization' => 'Терапия', 'specialization_doctor' => 'Терапевт'],
['name' => 'Составление плана лечения','specialization' => 'Терапия', 'specialization_doctor' => 'Терапевт'],
['name' => 'Интерпретация анализов','specialization' => 'Терапия', 'specialization_doctor' => 'Терапевт'],
['name' => 'Профилактический осмотр','specialization' => 'Терапия', 'specialization_doctor' => 'Терапевт'],
['name' => 'Вакцинация','specialization' => 'Терапия', 'specialization_doctor' => 'Терапевт'],
['name' => 'Дегельминтизация','specialization' => 'Терапия', 'specialization_doctor' => 'Терапевт'],
['name' => 'Обработка от паразитов','specialization' => 'Терапия', 'specialization_doctor' => 'Терапевт'],
['name' => 'Лечение инфекционных заболеваний','specialization' => 'Терапия', 'specialization_doctor' => 'Терапевт'],
['name' => 'Лечение хронических заболеваний','specialization' => 'Терапия', 'specialization_doctor' => 'Терапевт'],
['name' => 'Симптоматическое лечение','specialization' => 'Терапия', 'specialization_doctor' => 'Терапевт'],
['name' => 'Капельницы (инфузионная терапия)','specialization' => 'Терапия', 'specialization_doctor' => 'Терапевт'],
['name' => 'Инъекции','specialization' => 'Терапия', 'specialization_doctor' => 'Терапевт'],
['name' => 'Консультация по кормлению','specialization' => 'Терапия', 'specialization_doctor' => 'Терапевт'],
['name' => 'Консультация по уходу','specialization' => 'Терапия', 'specialization_doctor' => 'Терапевт'],
['name' => 'Онлайн-консультация','specialization' => 'Терапия', 'specialization_doctor' => 'Терапевт'],
['name' => 'Выезд на дом','specialization' => 'Терапия', 'specialization_doctor' => 'Терапевт'],

// Диагност
['name' => 'Консультация диагноста','specialization' => 'Диагностика', 'specialization_doctor' => 'Диагност'],
['name' => 'Комплексная диагностика','specialization' => 'Диагностика', 'specialization_doctor' => 'Диагност'],
['name' => 'Сбор анамнеза','specialization' => 'Диагностика', 'specialization_doctor' => 'Диагност'],
['name' => 'Интерпретация анализов','specialization' => 'Диагностика', 'specialization_doctor' => 'Диагност'],
['name' => 'Лабораторная диагностика','specialization' => 'Диагностика', 'specialization_doctor' => 'Диагност'],
['name' => 'УЗИ диагностика','specialization' => 'Диагностика', 'specialization_doctor' => 'Диагност'],
['name' => 'Рентген диагностика','specialization' => 'Диагностика', 'specialization_doctor' => 'Диагност'],
['name' => 'ЭКГ','specialization' => 'Диагностика', 'specialization_doctor' => 'Диагност'],
['name' => 'Эхокардиография','specialization' => 'Диагностика', 'specialization_doctor' => 'Диагност'],
['name' => 'Эндоскопия','specialization' => 'Диагностика', 'specialization_doctor' => 'Диагност'],
['name' => 'Биопсия','specialization' => 'Диагностика', 'specialization_doctor' => 'Диагност'],
['name' => 'Цитологические исследования','specialization' => 'Диагностика', 'specialization_doctor' => 'Диагност'],
['name' => 'Гистологические исследования','specialization' => 'Диагностика', 'specialization_doctor' => 'Диагност'],
['name' => 'Аллергодиагностика','specialization' => 'Диагностика', 'specialization_doctor' => 'Диагност'],
['name' => 'Гормональные исследования','specialization' => 'Диагностика', 'specialization_doctor' => 'Диагност'],
['name' => 'Инфекционная диагностика','specialization' => 'Диагностика', 'specialization_doctor' => 'Диагност'],
['name' => 'Паразитологические исследования','specialization' => 'Диагностика', 'specialization_doctor' => 'Диагност'],
['name' => 'Контроль эффективности лечения','specialization' => 'Диагностика', 'specialization_doctor' => 'Диагност'],
['name' => 'Второе мнение по диагнозу','specialization' => 'Диагностика', 'specialization_doctor' => 'Диагност'],
['name' => 'Онлайн-консультация','specialization' => 'Диагностика', 'specialization_doctor' => 'Диагност'],
// Хирург
['name' => 'Консультация хирурга','specialization' => 'Хирургия', 'specialization_doctor' => 'Хирург'],
['name' => 'Предоперационный осмотр','specialization' => 'Хирургия', 'specialization_doctor' => 'Хирург'],
['name' => 'Планирование операции','specialization' => 'Хирургия', 'specialization_doctor' => 'Хирург'],
['name' => 'Кастрация','specialization' => 'Хирургия', 'specialization_doctor' => 'Хирург'],
['name' => 'Стерилизация','specialization' => 'Хирургия', 'specialization_doctor' => 'Хирург'],
['name' => 'Удаление новообразований','specialization' => 'Хирургия', 'specialization_doctor' => 'Хирург'],
['name' => 'Абдоминальные операции','specialization' => 'Хирургия', 'specialization_doctor' => 'Хирург'],
['name' => 'Ортопедические операции','specialization' => 'Хирургия', 'specialization_doctor' => 'Хирург'],
['name' => 'Травматологические операции','specialization' => 'Хирургия', 'specialization_doctor' => 'Хирург'],
['name' => 'Удаление инородных тел','specialization' => 'Хирургия', 'specialization_doctor' => 'Хирург'],
['name' => 'Пластическая хирургия','specialization' => 'Хирургия', 'specialization_doctor' => 'Хирург'],
['name' => 'Стоматологические операции','specialization' => 'Хирургия', 'specialization_doctor' => 'Хирург'],
['name' => 'Операции на мягких тканях','specialization' => 'Хирургия', 'specialization_doctor' => 'Хирург'],
['name' => 'Операции на костях и суставах','specialization' => 'Хирургия', 'specialization_doctor' => 'Хирург'],
['name' => 'Наложение швов','specialization' => 'Хирургия', 'specialization_doctor' => 'Хирург'],
['name' => 'Обработка ран','specialization' => 'Хирургия', 'specialization_doctor' => 'Хирург'],
['name' => 'Послеоперационное наблюдение','specialization' => 'Хирургия', 'specialization_doctor' => 'Хирург'],
['name' => 'Снятие швов','specialization' => 'Хирургия', 'specialization_doctor' => 'Хирург'],
['name' => 'Экстренная хирургическая помощь','specialization' => 'Хирургия', 'specialization_doctor' => 'Хирург'],
['name' => 'Выезд на дом','specialization' => 'Хирургия', 'specialization_doctor' => 'Хирург'],

    // Грумер
    ['name' => 'Гигиеническая стрижка','specialization' => 'Грумминг', 'specialization_doctor' => 'Грумер'],
    ['name' => 'Модельная стрижка','specialization' => 'Грумминг', 'specialization_doctor' => 'Грумер'],
    ['name' => 'Экспресс-линька','specialization' => 'Грумминг', 'specialization_doctor' => 'Грумер'],
    ['name' => 'Тримминг','specialization' => 'Грумминг', 'specialization_doctor' => 'Грумер'],
    ['name' => 'Стриппинг','specialization' => 'Грумминг', 'specialization_doctor' => 'Грумер'],
    ['name' => 'Купание и сушка','specialization' => 'Грумминг', 'specialization_doctor' => 'Грумер'],
    ['name' => 'Чистка ушей','specialization' => 'Грумминг', 'specialization_doctor' => 'Грумер'],
    ['name' => 'Стрижка когтей','specialization' => 'Грумминг', 'specialization_doctor' => 'Грумер'],
    ['name' => 'Чистка зубов','specialization' => 'Грумминг', 'specialization_doctor' => 'Грумер'],
    ['name' => 'Вычесывание колтунов','specialization' => 'Грумминг', 'specialization_doctor' => 'Грумер'],
    ['name' => 'Удаление колтунов','specialization' => 'Грумминг', 'specialization_doctor' => 'Грумер'],
    ['name' => 'SPA-уход','specialization' => 'Грумминг', 'specialization_doctor' => 'Грумер'],
    ['name' => 'Озонотерапия шерсти','specialization' => 'Грумминг', 'specialization_doctor' => 'Грумер'],
    ['name' => 'Груминг кошек','specialization' => 'Грумминг', 'specialization_doctor' => 'Грумер'],
    ['name' => 'Груминг собак','specialization' => 'Грумминг', 'specialization_doctor' => 'Грумер'],
    ['name' => 'Подготовка к выставке','specialization' => 'Грумминг', 'specialization_doctor' => 'Грумер'],
    ['name' => 'Окрашивание шерсти','specialization' => 'Грумминг', 'specialization_doctor' => 'Грумер'],
    ['name' => 'Креативный груминг','specialization' => 'Грумминг', 'specialization_doctor' => 'Грумер'],
    ['name' => 'Полировка шерсти','specialization' => 'Грумминг', 'specialization_doctor' => 'Грумер'],
    ['name' => 'Парфюмирование','specialization' => 'Грумминг', 'specialization_doctor' => 'Грумер'],


    // Кинолог
    ['name' => 'Общий курс дрессировки (ОКД)','specialization' => 'Кинология', 'specialization_doctor' => 'Кинолог'],
    ['name' => 'Управляемая городская собака (УГС)','specialization' => 'Кинология', 'specialization_doctor' => 'Кинолог'],
    ['name' => 'Коррекция поведения','specialization' => 'Кинология', 'specialization_doctor' => 'Кинолог'],
    ['name' => 'Дрессировка щенков','specialization' => 'Кинология', 'specialization_doctor' => 'Кинолог'],
    ['name' => 'Индивидуальные занятия','specialization' => 'Кинология', 'specialization_doctor' => 'Кинолог'],
    ['name' => 'Групповые занятия','specialization' => 'Кинология', 'specialization_doctor' => 'Кинолог'],
    ['name' => 'Социализация собаки','specialization' => 'Кинология', 'specialization_doctor' => 'Кинолог'],
    ['name' => 'Обучение базовым командам','specialization' => 'Кинология', 'specialization_doctor' => 'Кинолог'],
    ['name' => 'Обучение послушанию','specialization' => 'Кинология', 'specialization_doctor' => 'Кинолог'],
    ['name' => 'Антиагрессия','specialization' => 'Кинология', 'specialization_doctor' => 'Кинолог'],
    ['name' => 'Работа со страхами','specialization' => 'Кинология', 'specialization_doctor' => 'Кинолог'],
    ['name' => 'Подготовка к выставкам','specialization' => 'Кинология', 'specialization_doctor' => 'Кинолог'],
    ['name' => 'Хендлинг','specialization' => 'Кинология', 'specialization_doctor' => 'Кинолог'],
    ['name' => 'Ноузворк (поиск запахов)','specialization' => 'Кинология', 'specialization_doctor' => 'Кинолог'],
    ['name' => 'Аджилити','specialization' => 'Кинология', 'specialization_doctor' => 'Кинолог'],
    ['name' => 'Защитно-караульная служба (ЗКС)','specialization' => 'Кинология', 'specialization_doctor' => 'Кинолог'],
    ['name' => 'Обучение охране территории','specialization' => 'Кинология', 'specialization_doctor' => 'Кинолог'],
    ['name' => 'Обучение поисково-спасательной службе','specialization' => 'Кинология', 'specialization_doctor' => 'Кинолог'],
    ['name' => 'Консультация кинолога','specialization' => 'Кинология', 'specialization_doctor' => 'Кинолог'],
    ['name' => 'Онлайн-дрессировка','specialization' => 'Кинология', 'specialization_doctor' => 'Кинолог'],


    // Тренер по аджилити
['name' => 'Введение в аджилити','specialization' => 'Аджилити', 'specialization_doctor' => 'Тренер по аджилити'],
['name' => 'Базовая подготовка собаки к аджилити','specialization' => 'Аджилити', 'specialization_doctor' => 'Тренер по аджилити'],
['name' => 'Обучение прохождению снарядов','specialization' => 'Аджилити', 'specialization_doctor' => 'Тренер по аджилити'],
['name' => 'Тоннели и мягкие препятствия','specialization' => 'Аджилити', 'specialization_doctor' => 'Тренер по аджилити'],
['name' => 'Барьеры и прыжковые элементы','specialization' => 'Аджилити', 'specialization_doctor' => 'Тренер по аджилити'],
['name' => 'Слалом','specialization' => 'Аджилити', 'specialization_doctor' => 'Тренер по аджилити'],
['name' => 'Контактные зоны (горка, бум, качели)','specialization' => 'Аджилити', 'specialization_doctor' => 'Тренер по аджилити'],
['name' => 'Развитие скорости и координации','specialization' => 'Аджилити', 'specialization_doctor' => 'Тренер по аджилити'],
['name' => 'Построение трасс','specialization' => 'Аджилити', 'specialization_doctor' => 'Тренер по аджилити'],
['name' => 'Прохождение трасс разного уровня','specialization' => 'Аджилити', 'specialization_doctor' => 'Тренер по аджилити'],
['name' => 'Подготовка к соревнованиям','specialization' => 'Аджилити', 'specialization_doctor' => 'Тренер по аджилити'],
['name' => 'Разбор ошибок на трассе','specialization' => 'Аджилити', 'specialization_doctor' => 'Тренер по аджилити'],
['name' => 'Индивидуальные тренировки','specialization' => 'Аджилити', 'specialization_doctor' => 'Тренер по аджилити'],
['name' => 'Групповые тренировки','specialization' => 'Аджилити', 'specialization_doctor' => 'Тренер по аджилити'],
['name' => 'Онлайн-консультации по аджилити','specialization' => 'Аджилити', 'specialization_doctor' => 'Тренер по аджилити'],
['name' => 'Фитнес для собак (под аджилити)','specialization' => 'Аджилити', 'specialization_doctor' => 'Тренер по аджилити'],
['name' => 'Развитие мотивации и драйва','specialization' => 'Аджилити', 'specialization_doctor' => 'Тренер по аджилити'],
['name' => 'Обучение работе с хендлером','specialization' => 'Аджилити', 'specialization_doctor' => 'Тренер по аджилити'],
['name' => 'Подготовка щенков к аджилити','specialization' => 'Аджилити', 'specialization_doctor' => 'Тренер по аджилити'],
['name' => 'Реабилитационные тренировки после травм','specialization' => 'Аджилити', 'specialization_doctor' => 'Тренер по аджилити'],

    // Зоопсихолог
['name' => 'Консультация зоопсихолога','specialization' => 'Зоопсихология', 'specialization_doctor' => 'Зоопсихолог'],
['name' => 'Диагностика поведения животного','specialization' => 'Зоопсихология', 'specialization_doctor' => 'Зоопсихолог'],
['name' => 'Коррекция поведения','specialization' => 'Зоопсихология', 'specialization_doctor' => 'Зоопсихолог'],
['name' => 'Агрессия у животных','specialization' => 'Зоопсихология', 'specialization_doctor' => 'Зоопсихолог'],
['name' => 'Страхи и фобии','specialization' => 'Зоопсихология', 'specialization_doctor' => 'Зоопсихолог'],
['name' => 'Сепарационная тревога','specialization' => 'Зоопсихология', 'specialization_doctor' => 'Зоопсихолог'],
['name' => 'Деструктивное поведение','specialization' => 'Зоопсихология', 'specialization_doctor' => 'Зоопсихолог'],
['name' => 'Проблемы с туалетом','specialization' => 'Зоопсихология', 'specialization_doctor' => 'Зоопсихолог'],
['name' => 'Адаптация животного в новом доме','specialization' => 'Зоопсихология', 'specialization_doctor' => 'Зоопсихолог'],
['name' => 'Социализация животных','specialization' => 'Зоопсихология', 'specialization_doctor' => 'Зоопсихолог'],
['name' => 'Проблемы общения с другими животными','specialization' => 'Зоопсихология', 'specialization_doctor' => 'Зоопсихолог'],
['name' => 'Проблемы поведения у кошек','specialization' => 'Зоопсихология', 'specialization_doctor' => 'Зоопсихолог'],
['name' => 'Проблемы поведения у собак','specialization' => 'Зоопсихология', 'specialization_doctor' => 'Зоопсихолог'],
['name' => 'Подбор методов воспитания','specialization' => 'Зоопсихология', 'specialization_doctor' => 'Зоопсихолог'],
['name' => 'Подготовка к появлению нового питомца','specialization' => 'Зоопсихология', 'specialization_doctor' => 'Зоопсихолог'],
['name' => 'Помощь при смене условий содержания','specialization' => 'Зоопсихология', 'specialization_doctor' => 'Зоопсихолог'],
['name' => 'Работа с гиперактивностью','specialization' => 'Зоопсихология', 'specialization_doctor' => 'Зоопсихолог'],
['name' => 'Пищевое поведение','specialization' => 'Зоопсихология', 'specialization_doctor' => 'Зоопсихолог'],
['name' => 'Онлайн-консультация','specialization' => 'Зоопсихология', 'specialization_doctor' => 'Зоопсихолог'],
['name' => 'Составление плана коррекции','specialization' => 'Зоопсихология', 'specialization_doctor' => 'Зоопсихолог'],

    // Фелинолог
['name' => 'Консультация фелинолога','specialization' => 'Фелинология', 'specialization_doctor' => 'Фелинолог'],
['name' => 'Подбор породы кошки','specialization' => 'Фелинология', 'specialization_doctor' => 'Фелинолог'],
['name' => 'Оценка экстерьера кошки','specialization' => 'Фелинология', 'specialization_doctor' => 'Фелинолог'],
['name' => 'Подготовка к выставке','specialization' => 'Фелинология', 'specialization_doctor' => 'Фелинолог'],
['name' => 'Хендлинг кошек','specialization' => 'Фелинология', 'specialization_doctor' => 'Фелинолог'],
['name' => 'Консультация по уходу за кошкой','specialization' => 'Фелинология', 'specialization_doctor' => 'Фелинолог'],
['name' => 'Консультация по кормлению','specialization' => 'Фелинология', 'specialization_doctor' => 'Фелинолог'],
['name' => 'Разведение кошек','specialization' => 'Фелинология', 'specialization_doctor' => 'Фелинолог'],
['name' => 'Подбор пары для вязки','specialization' => 'Фелинология', 'specialization_doctor' => 'Фелинолог'],
['name' => 'Сопровождение беременности кошки','specialization' => 'Фелинология', 'specialization_doctor' => 'Фелинолог'],
['name' => 'Помощь при родах','specialization' => 'Фелинология', 'specialization_doctor' => 'Фелинолог'],
['name' => 'Оценка помёта','specialization' => 'Фелинология', 'specialization_doctor' => 'Фелинолог'],
['name' => 'Консультация по содержанию котят','specialization' => 'Фелинология', 'specialization_doctor' => 'Фелинолог'],
['name' => 'Социализация котят','specialization' => 'Фелинология', 'specialization_doctor' => 'Фелинолог'],
['name' => 'Проблемы поведения кошек','specialization' => 'Фелинология', 'specialization_doctor' => 'Фелинолог'],
['name' => 'Коррекция поведения кошек','specialization' => 'Фелинология', 'specialization_doctor' => 'Фелинолог'],
['name' => 'Подготовка документов для выставок','specialization' => 'Фелинология', 'specialization_doctor' => 'Фелинолог'],
['name' => 'Регистрация в фелинологических системах','specialization' => 'Фелинология', 'specialization_doctor' => 'Фелинолог'],
['name' => 'Онлайн-консультация','specialization' => 'Фелинология', 'specialization_doctor' => 'Фелинолог'],
['name' => 'Аудит питомника','specialization' => 'Фелинология', 'specialization_doctor' => 'Фелинолог'],

    // Заводчик
['name' => 'Консультация заводчика','specialization' => 'Разведение животных', 'specialization_doctor' => 'Заводчик'],
['name' => 'Продажа щенков','specialization' => 'Разведение животных', 'specialization_doctor' => 'Заводчик'],
['name' => 'Продажа котят','specialization' => 'Разведение животных', 'specialization_doctor' => 'Заводчик'],
['name' => 'Подбор питомца','specialization' => 'Разведение животных', 'specialization_doctor' => 'Заводчик'],
['name' => 'Подбор пары для вязки','specialization' => 'Разведение животных', 'specialization_doctor' => 'Заводчик'],
['name' => 'Организация вязки','specialization' => 'Разведение животных', 'specialization_doctor' => 'Заводчик'],
['name' => 'Сопровождение беременности','specialization' => 'Разведение животных', 'specialization_doctor' => 'Заводчик'],
['name' => 'Помощь при родах','specialization' => 'Разведение животных', 'specialization_doctor' => 'Заводчик'],
['name' => 'Выращивание помёта','specialization' => 'Разведение животных', 'specialization_doctor' => 'Заводчик'],
['name' => 'Социализация щенков и котят','specialization' => 'Разведение животных', 'specialization_doctor' => 'Заводчик'],
['name' => 'Первичная вакцинация','specialization' => 'Разведение животных', 'specialization_doctor' => 'Заводчик'],
['name' => 'Чипирование','specialization' => 'Разведение животных', 'specialization_doctor' => 'Заводчик'],
['name' => 'Оформление документов на питомца','specialization' => 'Разведение животных', 'specialization_doctor' => 'Заводчик'],
['name' => 'Консультация по уходу','specialization' => 'Разведение животных', 'specialization_doctor' => 'Заводчик'],
['name' => 'Консультация по кормлению','specialization' => 'Разведение животных', 'specialization_doctor' => 'Заводчик'],
['name' => 'Передержка животных','specialization' => 'Разведение животных', 'specialization_doctor' => 'Заводчик'],
['name' => 'Подготовка к выставке','specialization' => 'Разведение животных', 'specialization_doctor' => 'Заводчик'],
['name' => 'Оценка экстерьера','specialization' => 'Разведение животных', 'specialization_doctor' => 'Заводчик'],
['name' => 'Консультация после покупки','specialization' => 'Разведение животных', 'specialization_doctor' => 'Заводчик'],
['name' => 'Резервирование питомца','specialization' => 'Разведение животных', 'specialization_doctor' => 'Заводчик'],

    // Зооняня
['name' => 'Выгул животных','specialization' => 'Зооняня', 'specialization_doctor' => 'Зооняня'],
['name' => 'Кормление животных','specialization' => 'Зооняня', 'specialization_doctor' => 'Зооняня'],
['name' => 'Уход за питомцем на дому','specialization' => 'Зооняня', 'specialization_doctor' => 'Зооняня'],
['name' => 'Передержка животных','specialization' => 'Зооняня', 'specialization_doctor' => 'Зооняня'],
['name' => 'Дневная няня для питомца','specialization' => 'Зооняня', 'specialization_doctor' => 'Зооняня'],
['name' => 'Ночная передержка','specialization' => 'Зооняня', 'specialization_doctor' => 'Зооняня'],
['name' => 'Посещение питомца на дому','specialization' => 'Зооняня', 'specialization_doctor' => 'Зооняня'],
['name' => 'Уборка лотка/клетки','specialization' => 'Зооняня', 'specialization_doctor' => 'Зооняня'],
['name' => 'Игры и активность','specialization' => 'Зооняня', 'specialization_doctor' => 'Зооняня'],
['name' => 'Присмотр за питомцем во время отпуска','specialization' => 'Зооняня', 'specialization_doctor' => 'Зооняня'],
['name' => 'Уход за пожилыми животными','specialization' => 'Зооняня', 'specialization_doctor' => 'Зооняня'],
['name' => 'Уход за щенками и котятами','specialization' => 'Зооняня', 'specialization_doctor' => 'Зооняня'],
['name' => 'Доставка питомца (зоотакси)','specialization' => 'Зооняня', 'specialization_doctor' => 'Зооняня'],
['name' => 'Сопровождение к ветеринару','specialization' => 'Зооняня', 'specialization_doctor' => 'Зооняня'],
['name' => 'Приём лекарств питомцем','specialization' => 'Зооняня', 'specialization_doctor' => 'Зооняня'],
['name' => 'Фото- и видеоотчёт','specialization' => 'Зооняня', 'specialization_doctor' => 'Зооняня'],
['name' => 'Онлайн-наблюдение за питомцем','specialization' => 'Зооняня', 'specialization_doctor' => 'Зооняня'],
['name' => 'Экстренный выезд','specialization' => 'Зооняня', 'specialization_doctor' => 'Зооняня'],
['name' => 'Уход за экзотическими животными','specialization' => 'Зооняня', 'specialization_doctor' => 'Зооняня'],
['name' => 'Консультация по уходу','specialization' => 'Зооняня', 'specialization_doctor' => 'Зооняня'],
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
