import $ from 'jquery';
import 'select2';

document.addEventListener('DOMContentLoaded', () => {
    const citySelect = $('#city-select');
    const newCityFields = document.getElementById('new-city-fields');
    const saveCityBtn = document.getElementById('save-city-btn');

    // ✅ Получаем CSRF-токен
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    // ✅ 1️⃣ Получаем ID города пользователя из таблицы users (через hidden input)
    const userCityId = document.getElementById('user-city-id')?.value || '';

    // Показываем ID города (из users.city_id)
    // alert('ID города пользователя из таблицы users: ' + userCityId);

    // === Загружаем список городов из таблицы cities ===
    fetch('/cities/all')
        .then(res => res.json())
        .then(cities => {
            console.log('📦 Ответ от /cities/all:', cities);

            const cityWithId2 = cities.find(city => String(city.id) === String(userCityId));

            // alert('✅ Город с id найден: ' + (cityWithId2?.name || '(без названия)'));

            // Очистим select
            citySelect.empty();

            // Убедимся, что это массив
            if (!Array.isArray(cities)) {
                alert('⚠️ Ошибка: /cities/all вернул не массив');
                console.error('Ответ:', cities);
                return;
            }

            // ✅ Если найден город пользователя — добавим его первым
            if (cityWithId2) {
                const cityName = cityWithId2.name || cityWithId2.title || '(без названия)';
                citySelect.append(new Option(cityName));
            }

            // Добавляем остальные города (кроме текущего)
            cities.forEach(city => {
                if (String(city.id) !== String(userCityId)) {
                    const cityName = city.name || city.title || '(без названия)';
                    citySelect.append(new Option(cityName, city.id));
                }
            });

            // Добавляем пункт "Моего города нет в списке"
            citySelect.append(new Option('+ Моего города нет в списке', 'add_new_city'));

            // Инициализация select2
            citySelect.select2({
                placeholder: 'Введите город...',
                width: '100%'
            });

            // ✅ Устанавливаем выбранный город пользователя
            if (cityWithId2) {
                const cityName = cityWithId2.name || cityWithId2.title || '(без названия)';
                citySelect.val(String(cityWithId2.id)).trigger('change.select2');
                alert('✅ Город выбран по умолчанию: ' + cityName);
                console.log('✅ Текущий город установлен:', cityName);
            } else if (userCityId) {
                alert('⚠️ Город с таким ID (' + userCityId + ') не найден в списке cities.');
                console.warn('Список cities:', cities);
            } else {
                alert('У пользователя нет города (city_id пуст).');
            }
        })
        .catch(err => {
            console.error('Ошибка загрузки городов:', err);
            // alert('Ошибка при загрузке городов: ' + err.message);
        });

    // === Обработка смены города ===
    citySelect.on('change', function () {
        const value = $(this).val();
        if (value === 'add_new_city') {
            newCityFields.style.display = 'block';
        } else {
            newCityFields.style.display = 'none';
            if (value) {
                fetch('/account/update-city', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ city_id: value })
                })
                .then(res => {
                    if (!res.ok) return res.text().then(t => Promise.reject(t));
                    return res.json();
                })
                .then(json => {
                    console.log('Город обновлён', json);
                    alert('Город успешно обновлён!');
                })
                .catch(err => {
                    console.error('Ошибка при сохранении города:', err);
                    alert('Не удалось сохранить город');
                });
            }
        }
    });

    // === Добавление нового города ===
    saveCityBtn.addEventListener('click', () => {
        const name = document.getElementById('new-name').value.trim();
        const country = document.getElementById('new-country').value.trim();
        const region = document.getElementById('new-region').value.trim();

        if (!name || !country || !region) return alert('Заполните все поля.');

        fetch('/cities/add', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ name, country, region })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const newOption = new Option(data.city.name, data.city.id, true, true);
                citySelect.append(newOption).trigger('change');
                newCityFields.style.display = 'none';
                alert('Город добавлен!');
            } else {
                alert('Ошибка при добавлении города');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Ошибка при добавлении города');
        });
    });
});
