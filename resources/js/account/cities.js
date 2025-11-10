import $ from 'jquery';
import 'select2';

document.addEventListener('DOMContentLoaded', () => {
    const citySelect = $('#city-select');
    const newCityFields = document.getElementById('new-city-fields');
    const saveCityBtn = document.getElementById('save-city-btn');
    const userCitySlug = document.querySelector('meta[name="user-city-slug"]')?.content || '';

    fetch('/cities/all')
        .then(res => res.json())
        .then(cities => {
            cities.forEach(city => citySelect.append(new Option(city.name, city.slug)));
            citySelect.append(new Option('+ Моего города нет в списке', 'add_new_city'));
            citySelect.select2({ placeholder: 'Введите город...', width: '100%' });
            if (userCitySlug) citySelect.val(userCitySlug).trigger('change');
        });

    citySelect.on('change', function() {
        const value = $(this).val();
        if (value === 'add_new_city') {
            newCityFields.style.display = 'block';
        } else {
            newCityFields.style.display = 'none';
            if (value) {
                fetch('/account/update-city', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({ city_slug: value })
                });
            }
        }
    });

    saveCityBtn.addEventListener('click', () => {
        const name = document.getElementById('new-name').value.trim();
        const country = document.getElementById('new-country').value.trim();
        const region = document.getElementById('new-region').value.trim();
        if (!name || !country || !region) return alert('Заполните все поля.');

        fetch('/cities/add', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify({ name, country, region })
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const newOption = new Option(data.city.name, data.city.slug, true, true);
                    citySelect.append(newOption).trigger('change');
                    newCityFields.style.display = 'none';
                    alert('Город добавлен!');
                } else alert('Ошибка при добавлении города');
            });
    });
});
