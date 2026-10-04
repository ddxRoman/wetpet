/**
 * Окно «Мы нашли похожую клинику / организацию / специалиста / врача / населённый пункт. Это она?»
 *
 * askDuplicate({ items }) возвращает Promise с одним из ответов:
 *   { action: 'yes', item }  — пользователь выбрал найденную запись («Да, это она»);
 *   { action: 'no' }         — «Нет, создать новую»;
 *   { action: 'cancel' }     — окно закрыли (крестик, Esc, клик мимо) — ничего не делаем.
 *
 * items — массив из ответа сервера (DuplicateFinder): { type, label, pron, name, address, phone, url }.
 */

const el = (tag, css, text) => {
    const node = document.createElement(tag);
    if (css) node.style.cssText = css;
    if (text !== undefined && text !== null) node.textContent = text;
    return node;
};

export function askDuplicate({ items, noLabel = 'Нет, создать новую' }) {
    return new Promise((resolve) => {
        const list = Array.isArray(items) ? items : [];
        const first = list[0] || {};
        const single = list.length <= 1;
        const sameType = list.every((i) => i.type === first.type);

        const overlay = el('div',
            'position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:2500;display:flex;align-items:center;justify-content:center;padding:16px;');
        overlay.setAttribute('role', 'dialog');
        overlay.setAttribute('aria-modal', 'true');

        const box = el('div',
            'background:#fff;border-radius:14px;max-width:520px;width:100%;max-height:92vh;display:flex;flex-direction:column;box-shadow:0 12px 48px rgba(0,0,0,.3);');

        /* ---------- заголовок ---------- */
        const head = el('div', 'padding:18px 22px 6px;display:flex;justify-content:space-between;gap:12px;align-items:flex-start;');
        const question = single
            ? `Мы нашли ${first.adj || 'похожую'} ${first.label || 'запись'}. Это ${first.pron || 'она'}?`
            : (sameType
                ? `Мы нашли похожие записи (${list.length}). Это одна из них?`
                : 'Мы нашли похожие записи. Это одна из них?');
        const title = el('div', 'font-size:18px;font-weight:700;color:#1f2937;', question);
        const closeBtn = el('button', 'background:none;border:none;font-size:20px;line-height:1;cursor:pointer;color:#6b7280;', '✕');
        closeBtn.type = 'button';
        closeBtn.setAttribute('aria-label', 'Закрыть');
        head.append(title, closeBtn);

        const hint = el('div', 'padding:0 22px 10px;font-size:13px;color:#6b7280;',
            'Чтобы не плодить дубли, проверьте: возможно, такая запись уже есть на сайте.');

        /* ---------- найденные записи ---------- */
        const body = el('div', 'padding:6px 22px 4px;overflow-y:auto;display:flex;flex-direction:column;gap:10px;');

        list.forEach((item) => {
            const card = el('div', 'border:1px solid #e5e7eb;border-radius:12px;padding:12px 14px;');

            card.appendChild(el('div', 'font-weight:600;color:#111827;', item.name || '—'));
            if (!single || item.type === 'city') {
                const kind = el('div', 'font-size:12px;color:#9ca3af;margin-top:1px;',
                    { clinic: 'Клиника', organization: 'Организация', doctor: 'Врач', specialist: 'Специалист', city: 'Населённый пункт' }[item.type] || '');
                card.appendChild(kind);
            }
            if (item.address) card.appendChild(el('div', 'font-size:13px;color:#4b5563;margin-top:4px;', '📍 ' + item.address));
            if (item.phone)   card.appendChild(el('div', 'font-size:13px;color:#4b5563;margin-top:2px;', '📞 ' + item.phone));

            const actions = el('div', 'display:flex;flex-wrap:wrap;gap:8px;align-items:center;margin-top:10px;');

            const yes = el('button', 'background:#ff8c00;border:none;color:#fff;font-weight:600;border-radius:20px;padding:7px 18px;cursor:pointer;',
                `Да, это ${item.pron || 'она'}`);
            yes.type = 'button';
            yes.addEventListener('click', () => done({ action: 'yes', item }));
            actions.appendChild(yes);

            if (item.url) {
                const link = el('a', 'font-size:13px;color:#2563eb;text-decoration:none;', 'Посмотреть карточку ↗');
                link.href = item.url;
                link.target = '_blank';
                link.rel = 'noopener';
                actions.appendChild(link);
            }

            card.appendChild(actions);
            body.appendChild(card);
        });

        /* ---------- «Нет, создать новую» ---------- */
        const footer = el('div', 'padding:12px 22px 18px;display:flex;justify-content:flex-end;');
        const no = el('button', 'background:#fff;border:2px solid #d1d5db;color:#374151;font-weight:600;border-radius:20px;padding:8px 20px;cursor:pointer;', noLabel);
        no.type = 'button';
        no.addEventListener('click', () => done({ action: 'no' }));
        footer.appendChild(no);

        box.append(head, hint, body, footer);
        overlay.appendChild(box);

        /* ---------- закрытие ---------- */
        const onKey = (e) => { if (e.key === 'Escape') done({ action: 'cancel' }); };

        function done(answer) {
            document.removeEventListener('keydown', onKey, true);
            overlay.remove();
            resolve(answer);
        }

        closeBtn.addEventListener('click', () => done({ action: 'cancel' }));
        overlay.addEventListener('mousedown', (e) => { if (e.target === overlay) done({ action: 'cancel' }); });
        document.addEventListener('keydown', onKey, true);

        document.body.appendChild(overlay);
        no.focus();
    });
}
