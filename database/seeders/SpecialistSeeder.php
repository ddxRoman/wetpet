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
            ['name' => 'Артем Соколов', 'specialization' => 'Кинолог', 'date_of_birth' => '1988-05-14', 'city_id' => 31, 'experience' => 12, 'exotic_animals' => 'Нет', 'On_site_assistance' => 'Да', 'description' => 'Коррекция поведения, ОКД, работа со сложными случаями агрессии.'],
            ['name' => 'Марина Власова', 'specialization' => 'Грумер', 'date_of_birth' => '1995-11-22', 'city_id' => 31, 'experience' => 5, 'exotic_animals' => 'Да', 'On_site_assistance' => 'Нет', 'description' => 'Стрижка собак и кошек всех пород, тримминг, подготовка к выставкам.'],
            ['name' => 'Ирина Мельникова', 'specialization' => 'Зоопсихолог', 'date_of_birth' => '1990-03-09', 'city_id' => 31, 'experience' => 8, 'exotic_animals' => 'Нет', 'On_site_assistance' => 'Да', 'description' => 'Помогаю понять мотивы поведения вашего питомца и наладить контакт.'],
            ['name' => 'Алексей Попов', 'specialization' => 'Тренер по аджилити', 'date_of_birth' => '1992-07-30', 'city_id' => 31, 'experience' => 6, 'exotic_animals' => 'Нет', 'On_site_assistance' => 'Нет', 'description' => 'Подготовка собак к соревнованиям по аджилити, спортивная дрессура.'],
            ['name' => 'Ольга Семенова', 'specialization' => 'Фелинолог', 'date_of_birth' => null, 'city_id' => 31, 'experience' => 15, 'exotic_animals' => 'Нет', 'On_site_assistance' => 'Да', 'description' => 'Консультации по разведению, генетике и содержанию кошек.'],
            ['name' => 'Елена Кузнецова', 'specialization' => 'Зооняня', 'date_of_birth' => '1998-02-15', 'city_id' => 31, 'experience' => 3, 'exotic_animals' => 'Да', 'On_site_assistance' => 'Да', 'description' => 'Присмотр за питомцем на вашей территории, выгул и кормление.'],
            ['name' => 'Сергей Данилов', 'specialization' => 'Заводчик', 'date_of_birth' => null, 'city_id' => 31, 'experience' => 20, 'exotic_animals' => 'Нет', 'On_site_assistance' => 'Нет', 'description' => 'Племенное разведение лабрадоров-ретриверов.'],
            ['name' => 'Татьяна Морозова', 'specialization' => 'Грумер', 'date_of_birth' => '1993-09-18', 'city_id' => 31, 'experience' => 7, 'exotic_animals' => 'Нет', 'On_site_assistance' => 'Да', 'description' => 'Гигиенический груминг, вычесывание подшерстка, экспресс-линька.'],
            ['name' => 'Виталий Павлов', 'specialization' => 'Кинолог', 'date_of_birth' => '1985-04-05', 'city_id' => 31, 'experience' => 18, 'exotic_animals' => 'Нет', 'On_site_assistance' => 'Да', 'description' => 'Подготовка защитно-караульных собак, работа с охранными породами.'],
            ['name' => 'Ксения Белова', 'specialization' => 'Зоопсихолог', 'date_of_birth' => '1996-12-01', 'city_id' => 31, 'experience' => 4, 'exotic_animals' => 'Да', 'On_site_assistance' => 'Да', 'description' => 'Специалист по поведению кошек и мелких грызунов.'],
            ['name' => 'Андрей Воробьев', 'specialization' => 'Тренер по аджилити', 'date_of_birth' => '1989-08-21', 'city_id' => 31, 'experience' => 10, 'exotic_animals' => 'Нет', 'On_site_assistance' => 'Нет', 'description' => 'Индивидуальные и групповые занятия на тренировочной площадке.'],
            ['name' => 'Юлия Котова', 'specialization' => 'Фелинолог', 'date_of_birth' => '1987-10-10', 'city_id' => 31, 'experience' => null, 'exotic_animals' => 'Нет', 'On_site_assistance' => 'Да', 'description' => 'Экспертная оценка экстерьера кошек, помощь в подборе пар.'],
            ['name' => 'Николай Савин', 'specialization' => 'Зооняня', 'date_of_birth' => '2000-01-25', 'city_id' => 31, 'experience' => 2, 'exotic_animals' => 'Нет', 'On_site_assistance' => 'Да', 'description' => 'Выгул собак крупных пород, активные игры, сопровождение к ветеринару.'],
            ['name' => 'Анна Родионова', 'specialization' => 'Грумер', 'date_of_birth' => '1994-06-14', 'city_id' => 31, 'experience' => 6, 'exotic_animals' => 'Нет', 'On_site_assistance' => 'Нет', 'description' => 'Креативное окрашивание, выставочный груминг терьеров.'],
            ['name' => 'Игорь Борисов', 'specialization' => 'Кинолог', 'date_of_birth' => '1980-02-28', 'city_id' => 31, 'experience' => 22, 'exotic_animals' => 'Нет', 'On_site_assistance' => 'Да', 'description' => 'Дрессировка собак-поводырей и служебных собак.'],
            ['name' => 'Валентина Лисина', 'specialization' => 'Зоопсихолог', 'date_of_birth' => null, 'city_id' => 31, 'experience' => 11, 'exotic_animals' => 'Нет', 'On_site_assistance' => 'Да', 'description' => 'Работа с фобиями, страхом громких звуков и сепарационной тревогой.'],
            ['name' => 'Максим Жуков', 'specialization' => 'Заводчик', 'date_of_birth' => '1983-05-19', 'city_id' => 31, 'experience' => 14, 'exotic_animals' => 'Нет', 'On_site_assistance' => 'Нет', 'description' => 'Питомник немецких овчарок рабочих линий.'],
            ['name' => 'Светлана Орлова', 'specialization' => 'Зооняня', 'date_of_birth' => '1997-03-31', 'city_id' => 31, 'experience' => 4, 'exotic_animals' => 'Нет', 'On_site_assistance' => 'Да', 'description' => 'Домашняя передержка для маленьких собак в комфортных условиях.'],
            ['name' => 'Дмитрий Соловьев', 'specialization' => 'Кинолог', 'date_of_birth' => '1991-11-08', 'city_id' => 31, 'experience' => 9, 'exotic_animals' => 'Нет', 'On_site_assistance' => 'Да', 'description' => 'Групповые занятия для щенков, социализация в городских условиях.'],
            ['name' => 'Наталья Зайцева', 'specialization' => 'Грумер', 'date_of_birth' => '1988-07-22', 'city_id' => 31, 'experience' => 13, 'exotic_animals' => 'Нет', 'On_site_assistance' => 'Нет', 'description' => 'Мастер по работе с длинношерстными породами кошек.'],
            ['name' => 'Роман Федоров', 'specialization' => 'Тренер по аджилити', 'date_of_birth' => '1994-04-12', 'city_id' => 31, 'experience' => 5, 'exotic_animals' => 'Нет', 'On_site_assistance' => 'Нет', 'description' => 'Начальный курс аджилити для всех пород.'],
            ['name' => 'Оксана Новикова', 'specialization' => 'Зоопсихолог', 'date_of_birth' => null, 'city_id' => 31, 'experience' => 7, 'exotic_animals' => 'Да', 'On_site_assistance' => 'Да', 'description' => 'Коррекция пищевого поведения и нежелательных привычек.'],
            ['name' => 'Станислав Волков', 'specialization' => 'Кинолог', 'date_of_birth' => '1986-10-30', 'city_id' => 31, 'experience' => 16, 'exotic_animals' => 'Нет', 'On_site_assistance' => 'Да', 'description' => 'Ноузворк и поисковая работа для собак.'],
            ['name' => 'Мария Тихонова', 'specialization' => 'Фелинолог', 'date_of_birth' => '1992-01-14', 'city_id' => 31, 'experience' => 8, 'exotic_animals' => 'Нет', 'On_site_assistance' => 'Да', 'description' => 'Консультации по психофизиологии кошачьих.'],
            ['name' => 'Виктория Лебедева', 'specialization' => 'Грумер', 'date_of_birth' => '1999-05-27', 'city_id' => 31, 'experience' => null, 'exotic_animals' => 'Нет', 'On_site_assistance' => 'Да', 'description' => 'Чистка зубов ультразвуком без наркоза, гигиена ушей и когтей.'],
            ['name' => 'Денис Медведев', 'specialization' => 'Заводчик', 'date_of_birth' => '1979-09-03', 'city_id' => 31, 'experience' => 25, 'exotic_animals' => 'Нет', 'On_site_assistance' => 'Нет', 'description' => 'Разведение сибирских хаски, подбор щенков для спорта.'],
            ['name' => 'Анастасия Громова', 'specialization' => 'Зооняня', 'date_of_birth' => '1995-08-16', 'city_id' => 31, 'experience' => 6, 'exotic_animals' => 'Нет', 'On_site_assistance' => 'Да', 'description' => 'Профессиональный выгул с элементами дрессировки.'],
            ['name' => 'Павел Никитин', 'specialization' => 'Кинолог', 'date_of_birth' => '1982-12-12', 'city_id' => 31, 'experience' => 20, 'exotic_animals' => 'Нет', 'On_site_assistance' => 'Да', 'description' => 'Коррекция поведения охотничьих собак.'],
            ['name' => 'Дарья Макарова', 'specialization' => 'Зоопсихолог', 'date_of_birth' => '1990-06-05', 'city_id' => 31, 'experience' => 9, 'exotic_animals' => 'Да', 'On_site_assistance' => 'Да', 'description' => 'Решение проблем агрессии и нечистоплотности в доме.'],
            ['name' => 'Егор Степанов', 'specialization' => 'Тренер по аджилити', 'date_of_birth' => null, 'city_id' => 31, 'experience' => 7, 'exotic_animals' => 'Нет', 'On_site_assistance' => 'Нет', 'description' => 'Занятия для активных собак на профессиональных снарядах.'],
            ['name' => 'Лидия Тарасова', 'specialization' => 'Фелинолог', 'date_of_birth' => '1984-11-20', 'city_id' => 31, 'experience' => 12, 'exotic_animals' => 'Нет', 'On_site_assistance' => 'Нет', 'description' => 'Помощь в организации домашних питомников кошек.'],
            ['name' => 'Артур Кириллов', 'specialization' => 'Кинолог', 'date_of_birth' => '1993-02-09', 'city_id' => 31, 'experience' => 8, 'exotic_animals' => 'Нет', 'On_site_assistance' => 'Да', 'description' => 'Послушание для собак малых и средних пород.'],
            ['name' => 'Евгения Сафонова', 'specialization' => 'Грумер', 'date_of_birth' => '1996-04-30', 'city_id' => 31, 'experience' => 4, 'exotic_animals' => 'Да', 'On_site_assistance' => 'Да', 'description' => 'Бережный груминг для пожилых животных и щенков.'],
            ['name' => 'Олег Беляев', 'specialization' => 'Заводчик', 'date_of_birth' => '1975-07-17', 'city_id' => 31, 'experience' => 30, 'exotic_animals' => 'Нет', 'On_site_assistance' => 'Нет', 'description' => 'Породное разведение ротвейлеров, консультации владельцев.'],
            ['name' => 'Валерия Власова', 'specialization' => 'Зооняня', 'date_of_birth' => '2001-10-12', 'city_id' => 31, 'experience' => 2, 'exotic_animals' => 'Нет', 'On_site_assistance' => 'Да', 'description' => 'Дневная передержка и сопровождение в поездках.'],
            ['name' => 'Михаил Козлов', 'specialization' => 'Кинолог', 'date_of_birth' => '1981-03-24', 'city_id' => 31, 'experience' => 19, 'exotic_animals' => 'Нет', 'On_site_assistance' => 'Да', 'description' => 'Коррекция пищевой агрессии и страха людей.'],
            ['name' => 'Вероника Шилова', 'specialization' => 'Зоопсихолог', 'date_of_birth' => '1992-05-05', 'city_id' => 31, 'experience' => 6, 'exotic_animals' => 'Нет', 'On_site_assistance' => 'Да', 'description' => 'Анализ взаимодействия нескольких питомцев в одной семье.'],
            ['name' => 'Константин Исаев', 'specialization' => 'Грумер', 'date_of_birth' => '1987-12-28', 'city_id' => 31, 'experience' => 10, 'exotic_animals' => 'Да', 'On_site_assistance' => 'Нет', 'description' => 'Профессиональное вычесывание крупных собак, удаление колтунов.'],
            ['name' => 'Инна Антонова', 'specialization' => 'Фелинолог', 'date_of_birth' => null, 'city_id' => 31, 'experience' => 14, 'exotic_animals' => 'Нет', 'On_site_assistance' => 'Да', 'description' => 'Консультант по натуральному питанию кошек.'],
            ['name' => 'Семен Фролов', 'specialization' => 'Тренер по аджилити', 'date_of_birth' => '1995-09-09', 'city_id' => 31, 'experience' => 4, 'exotic_animals' => 'Нет', 'On_site_assistance' => 'Нет', 'description' => 'Фрисби и аджилити для начинающих.'],
            ['name' => 'Алла Ермакова', 'specialization' => 'Зооняня', 'date_of_birth' => '1994-06-21', 'city_id' => 31, 'experience' => 5, 'exotic_animals' => 'Да', 'On_site_assistance' => 'Да', 'description' => 'Уход за птицами и экзотическими животными на время отпуска.'],
            ['name' => 'Кирилл Уваров', 'specialization' => 'Кинолог', 'date_of_birth' => '1989-01-13', 'city_id' => 31, 'experience' => 11, 'exotic_animals' => 'Нет', 'On_site_assistance' => 'Да', 'description' => 'Бытовая дрессировка, комфортная прогулка на провисшем поводке.'],
            ['name' => 'Светлана Соколова', 'specialization' => 'Грумер', 'date_of_birth' => '1991-08-04', 'city_id' => 31, 'experience' => 9, 'exotic_animals' => 'Нет', 'On_site_assistance' => 'Да', 'description' => 'Спа-процедуры для животных, озонотерапия.'],
            ['name' => 'Антон Кравцов', 'specialization' => 'Заводчик', 'date_of_birth' => '1980-05-15', 'city_id' => 31, 'experience' => 17, 'exotic_animals' => 'Нет', 'On_site_assistance' => 'Нет', 'description' => 'Разведение французских бульдогов.'],
            ['name' => 'Наталья Борисова', 'specialization' => 'Зоопсихолог', 'date_of_birth' => '1993-11-30', 'city_id' => 31, 'experience' => 6, 'exotic_animals' => 'Нет', 'On_site_assistance' => 'Да', 'description' => 'Помощь в адаптации животного из приюта.'],
            ['name' => 'Владислав Поляков', 'specialization' => 'Кинолог', 'date_of_birth' => '1984-07-22', 'city_id' => 31, 'experience' => 15, 'exotic_animals' => 'Нет', 'On_site_assistance' => 'Да', 'description' => 'Удаленная консультация по поведению и онлайн-тренировки.'],
            ['name' => 'Олеся Коновалова', 'specialization' => 'Грумер', 'date_of_birth' => '1997-02-14', 'city_id' => 31, 'experience' => 3, 'exotic_animals' => 'Нет', 'On_site_assistance' => 'Да', 'description' => 'Стрижка когтей, уход за лапами и экспресс-чистка.'],
            ['name' => 'Борис Марков', 'specialization' => 'Тренер по аджилити', 'date_of_birth' => '1990-10-01', 'city_id' => 31, 'experience' => 8, 'exotic_animals' => 'Нет', 'On_site_assistance' => 'Нет', 'description' => 'Развитие выносливости и координации у спортивных собак.'],
            ['name' => 'Елена Панфилова', 'specialization' => 'Фелинолог', 'date_of_birth' => '1985-06-19', 'city_id' => 31, 'experience' => 16, 'exotic_animals' => 'Нет', 'On_site_assistance' => 'Да', 'description' => 'Специалист по породам: мейн-кун и британская короткошерстная.'],
            ['name' => 'Дмитрий Щербаков', 'specialization' => 'Заводчик', 'date_of_birth' => '1978-04-04', 'city_id' => 31, 'experience' => 22, 'exotic_animals' => 'Нет', 'On_site_assistance' => 'Нет', 'description' => 'Племенная работа с породой бигль.'],
        ];
    $data = collect($specialists)->map(function ($specialist) {
        return array_merge($specialist, [
            'organization_id' => null,
            'photo' => null,
            'slug' => Str::slug($specialist['name']), 
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    })->toArray();

    DB::table('specialists')->insert($data);
}
}