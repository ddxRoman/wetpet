import $ from 'jquery';
import 'select2';
console.log('cities.js loaded');

document.addEventListener('DOMContentLoaded', () => {

    const citySelect = $('#city-select');

    // 🔴 ДОБАВЛЕНО: защита, если select отсутствует на странице
    if (!citySelect.length) return;

    const newCityFields = document.getElementById('new-city-fields');
    const saveCityBtn = document.getElementById('save-city-btn');

    // 🔴 ДОБАВЛЕНО: защита от null
    if (!newCityFields || !saveCityBtn) return;

    // 🔴 ДОБАВЛЕНО: безопасное получение CSRF
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    if (!csrfToken) {
        console.error('CSRF token not found');
        return;
    }

    // === ID города пользователя ===
    const userCityId = document.getElementById('user-city-id')?.value || '';

    // === Загрузка городов ===
    fetch('/cities/all')
        .then(res => res.json())
        .then(cities => {

            // 🟡 ИЗМЕНЕНО: защита от не-массива
            if (!Array.isArray(cities)) {
                console.error('cities is not array', cities);
                return;
            }

            citySelect.empty();

            // 🟡 ИЗМЕНЕНО: аккуратный поиск текущего города
            const currentCity = cities.find(
                city => String(city.id) === String(userCityId)
            );

            // === Текущий город первым ===
            if (currentCity) {
                citySelect.append(
                    new Option(currentCity.name, currentCity.id, true, true)
                );
            }

            // === Остальные города ===
            cities.forEach(city => {
                if (!currentCity || String(city.id) !== String(currentCity.id)) {
                    citySelect.append(
                        new Option(city.name, city.id)
                    );
                }
            });

            // === Добавить "моего города нет" ===
            citySelect.append(
                new Option('+ Моего города нет в списке', 'add_new_city')
            );

            // === Инициализация select2 ===
            citySelect.select2({
                placeholder: 'Введите город...',
                width: '100%'
            });
        })
        .catch(err => {
            console.error('Ошибка загрузки городов:', err);
        });

    // === Смена города ===
    citySelect.on('change', function () {
        const value = $(this).val();

        if (value === 'add_new_city') {
            newCityFields.style.display = 'block';
            return;
        }

        newCityFields.style.display = 'none';

        if (!value) return;

        fetch('/account/update-city', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ city_id: value })
        })
        .then(res => {
            if (!res.ok) throw new Error('Ошибка сохранения города');
            return res.json();
        })
        .then(() => {
            alert('Город успешно обновлён');
        })
        .catch(err => {
            console.error(err);
            alert('Не удалось сохранить город');
        });
    });

    // === Добавление нового города ===
    saveCityBtn.addEventListener('click', () => {
        const name = document.getElementById('new-name').value.trim();
        const country = document.getElementById('new-country').value.trim();
        const region = document.getElementById('new-region').value.trim();

        if (!name || !country || !region) {
            alert('Заполните все поля');
            return;
        }

        fetch('/cities/add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ name, country, region })
        })
        .then(res => res.json())
        .then(data => {
            if (!data?.city) {
                alert('Ошибка при добавлении города');
                return;
            }

            // 🟡 ИЗМЕНЕНО: корректное добавление нового option
            const option = new Option(
                data.city.name,
                data.city.id,
                true,
                true
            );

            citySelect.append(option).trigger('change');
            newCityFields.style.display = 'none';
        })
        .catch(err => {
            console.error(err);
            alert('Ошибка при добавлении города');
        });
    });

});
