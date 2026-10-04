/**
 * Добавление нового населённого пункта прямо в выпадающем списке «Город» (Choices.js).
 *
 * Если в строке поиска введено название, которого нет в списке, внизу выпадающего списка
 * появляется кнопка «Добавить этот населённый пункт». По нажатию название подставляется в поле
 * «Город» — в базу оно пока НЕ пишется. При отправке формы (кнопка «Сохранить») в запрос уходит
 * city_name + region (см. applyNewCity), а сервер создаёт город в таблице cities (large_city = 0)
 * и крепит его к создаваемой карточке.
 */

import { askDuplicate } from './duplicate-popup';

const NEW_PREFIX = 'new:';

// Для сравнения: без учёта регистра, ё = е, лишних пробелов и пробелов вокруг дефиса
const norm = (s) => String(s ?? '')
    .toLowerCase()
    .replace(/ё/g, 'е')
    .replace(/\s*-\s*/g, '-')
    .replace(/\s+/g, ' ')
    .trim();

// Название, как его увидит пользователь: лишние пробелы убраны, первая буква заглавная
const tidy = (s) => {
    const t = String(s ?? '').replace(/\s+/g, ' ').trim();
    return t ? t.charAt(0).toUpperCase() + t.slice(1) : '';
};

/**
 * @param {object}   o
 * @param {HTMLSelectElement} o.select        select с name="city_id"
 * @param {Choices}  o.choices                экземпляр Choices для этого select
 * @param {HTMLSelectElement} o.regionSelect  select с регионом (город всегда крепится к региону)
 * @param {() => string[]} o.getKnownNames    названия городов, уже загруженных в список
 */
export function enableAddCity({ select, choices, regionSelect, getKnownNames }) {
    if (!select || !choices || select.dataset.addCityReady) return;
    select.dataset.addCityReady = '1';

    const dropdown = choices.dropdown.element;

    /* ---------- блок с кнопкой внизу выпадающего списка ---------- */
    const footer = document.createElement('div');
    footer.style.cssText = 'display:none;padding:10px 12px;border-top:1px solid #e5e7eb;background:#fff;';

    const hint = document.createElement('div');
    hint.style.cssText = 'font-size:12px;color:#6b7280;margin-bottom:8px;word-break:break-word;';

    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'btn btn-sm btn-primary';
    button.textContent = 'Добавить этот населённый пункт';

    footer.append(hint, button);
    dropdown.appendChild(footer);

    let typed = '';
    const hideFooter = () => { footer.style.display = 'none'; };

    /* ---------- показываем кнопку, когда в поиске введён город, которого нет в списке ---------- */
    select.addEventListener('search', (e) => {
        typed = tidy(e.detail?.value);

        // минимум две буквы — иначе это не название
        if (typed.length < 2 || !/\p{L}.*\p{L}/u.test(typed)) {
            hideFooter();
            return;
        }

        const exists = (getKnownNames() || []).some((name) => norm(name) === norm(typed));
        if (exists) {
            hideFooter();
            return;
        }

        const hasRegion = !!regionSelect?.value;
        hint.textContent = hasRegion
            ? `«${typed}» нет в списке.`
            : 'Сначала выберите регион — населённый пункт добавляется в выбранный регион.';
        button.disabled = !hasRegion;
        footer.style.display = 'block';
    });

    /* ---------- нажали «Добавить этот населённый пункт» ---------- */
    button.addEventListener('click', async () => {
        if (!regionSelect?.value || !typed) return;

        const name = typed;

        // Перед добавлением спрашиваем про похожие населённые пункты региона: «Динская» ~ «Станица Динская»
        button.disabled = true;
        try {
            const params = new URLSearchParams({ name, region: regionSelect.value });
            const response = await fetch('/api/cities/similar?' + params.toString(), { headers: { 'Accept': 'application/json' } });
            const data = response.ok ? await response.json() : {};

            if (Array.isArray(data.duplicates) && data.duplicates.length) {
                const answer = await askDuplicate({ items: data.duplicates, noLabel: 'Нет, добавить новый' });

                if (answer.action === 'yes' && answer.item) {
                    // «Да» — выбираем существующий населённый пункт вместо нового
                    delete select.dataset.newCity;
                    [String(answer.item.id), answer.item.id].forEach((value) => choices.setChoiceByValue(value));
                    choices.clearInput();
                    choices.hideDropdown();
                    hideFooter();
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                    return;
                }

                if (answer.action !== 'no') return;   // окно закрыли — ничего не делаем
            }
        } catch (e) {
            // не удалось проверить — не мешаем добавить город
        } finally {
            button.disabled = !regionSelect?.value;
        }

        select.dataset.newCity = name;

        // Подставляем название в поле «Город» (пока только в форме, в базу не пишем)
        choices.setChoices(
            [{ value: NEW_PREFIX + name, label: name, selected: true }],
            'value', 'label', false
        );

        choices.clearInput();
        choices.hideDropdown();
        hideFooter();

        // Остальные обработчики (например, список клиник по городу) должны узнать о смене города
        select.dispatchEvent(new Event('change', { bubbles: true }));
    });

    /* ---------- выбрали другой город / сменили регион — «новый» город сбрасываем ---------- */
    select.addEventListener('change', () => {
        if (!String(select.value).startsWith(NEW_PREFIX)) {
            delete select.dataset.newCity;
        }
    });

    regionSelect?.addEventListener('change', () => {
        delete select.dataset.newCity;
        hideFooter();
    });

}

/**
 * Вызывается при отправке формы: если выбран «новый» город, вместо city_id уходит city_name —
 * сервер создаст город в таблице cities и привяжет его к создаваемой карточке.
 * Принимает и возвращает FormData, чтобы вызывать прямо в месте new FormData(form).
 */
export function applyNewCity(form, formData) {
    const select = form?.querySelector('select[name="city_id"]');
    const name = select?.dataset.newCity;

    if (name && String(select.value).startsWith(NEW_PREFIX)) {
        formData.set('city_id', '');
        formData.set('city_name', name);
    }

    return formData;
}
