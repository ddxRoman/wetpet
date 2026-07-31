<script>
(function () {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

    // ── Загрузка документа ────────────────────────────────────
    document.addEventListener('submit', function (e) {
        const form = e.target;
        if (!form.classList.contains('doc-upload-form')) return;
        e.preventDefault();

        const status     = form.querySelector('.upload-status');
        const ownerRowId = form.dataset.ownerRowId;
        const entityType = form.dataset.entityType;
        const docsList   = document.getElementById(form.dataset.docsList);

        const fd = new FormData(form);
        fd.set('owner_row_id', ownerRowId);
        fd.set('entity_type', entityType);

        status.textContent = 'Загрузка…';

        fetch('{{ route("owner.documents.upload") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: fd,
        })
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                status.textContent = '⚠️ Ошибка загрузки';
                return;
            }

            status.textContent = '✓ Загружено';

            // Добавляем строки документов в список без перезагрузки
            // (контроллер возвращает МАССИВ documents, т.к. поддерживается мультизагрузка)
            if (docsList && Array.isArray(data.documents)) {
                data.documents.forEach(doc => {
                    const row = document.createElement('div');
                    row.className = 'd-flex align-items-center justify-content-between border rounded-3 px-3 py-2 bg-light';
                    row.id = 'doc-row-' + doc.id;
                    row.innerHTML = `
                        <a href="${doc.url}" target="_blank"
                           class="text-decoration-none text-dark d-flex align-items-center gap-2">
                            <span>📄</span><span>${doc.name}</span>
                        </a>
                        <button type="button"
                                class="btn btn-sm btn-outline-danger rounded-pill btn-delete-doc"
                                data-id="${doc.id}"
                                style="padding:2px 10px;font-size:12px;">
                            Удалить
                        </button>`;
                    docsList.appendChild(row);
                });
            }

            // Сбрасываем форму
            form.reset();
            setTimeout(() => { status.textContent = ''; }, 3000);
        })
        .catch(() => { status.textContent = '⚠️ Ошибка загрузки'; });
    });

    // ── Удаление документа ────────────────────────────────────
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-delete-doc');
        if (!btn) return;
        if (!confirm('Удалить документ?')) return;

        fetch('/owner/documents/' + btn.dataset.id, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.getElementById('doc-row-' + btn.dataset.id)?.remove();
            }
        });
    });

    // ── Отмена заявки ─────────────────────────────────────────
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-cancel-claim');
        if (!btn) return;

        if (!confirm(`Отменить заявку на «${btn.dataset.name}»? Все загруженные документы будут удалены.`)) return;

        btn.disabled = true;
        btn.textContent = 'Отмена…';

        fetch(`/owner/claim/${btn.dataset.type}/${btn.dataset.id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                // Убираем карточку заявки из DOM
                btn.closest('.claim-accordion-item')?.remove();

                // Если карточек больше нет — перезагружаем страницу
                // чтобы показать пустое состояние или редирект
                const remaining = document.querySelectorAll('.btn-cancel-claim');
                if (remaining.length === 0) {
                    window.location.reload();
                }
            } else {
                btn.disabled = false;
                btn.textContent = '✕ Отменить заявку';
                alert(data.message || 'Не удалось отменить заявку.');
            }
        })
        .catch(() => {
            btn.disabled = false;
            btn.textContent = '✕ Отменить заявку';
            alert('Ошибка соединения. Попробуйте ещё раз.');
        });
    });

    // ── ЧАТ С АДМИНИСТРАТОРОМ ───────────────────────────────────
    function renderChatMessages(box, messages) {
        if (!messages.length) {
            box.innerHTML = '<div class="text-muted text-center py-3 small">Сообщений пока нет. Напишите администратору, если у вас есть вопросы.</div>';
            return;
        }
        box.innerHTML = messages.map(m => `
            <div class="d-flex ${m.is_admin ? 'justify-content-start' : 'justify-content-end'} mb-2">
                <div class="rounded-3 px-3 py-2" style="max-width:80%;background:${m.is_admin ? '#fff' : '#d1f0ff'};border:1px solid ${m.is_admin ? '#e0e0e0' : '#b3e0ff'};">
                    <div class="fw-semibold small" style="font-size:11px;color:${m.is_admin ? '#dc3545' : '#0d6efd'};">
                        ${m.is_admin ? '👤 Администратор' : '🧑 Вы'}
                    </div>
                    <div style="font-size:13px;white-space:pre-wrap;">${m.text.replace(/</g, '&lt;')}</div>
                    <div class="text-muted text-end" style="font-size:10px;">${m.created_at}</div>
                </div>
            </div>
        `).join('');
        box.scrollTop = box.scrollHeight;
    }

    function loadChatMessages(box) {
        const ownerRowId = box.dataset.ownerRowId;
        const entityType = box.dataset.entityType;

        fetch(`{{ route('owner.claim.messages.get') }}?owner_row_id=${ownerRowId}&entity_type=${entityType}`, {
            headers: { 'Accept': 'application/json' },
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) renderChatMessages(box, data.messages);
        })
        .catch(() => {});
    }

    // Загружаем сообщения для всех чатов при загрузке страницы
    document.querySelectorAll('.claim-chat-box').forEach(box => {
        loadChatMessages(box);
        // Обновляем каждые 10 секунд (простой поллинг вместо вебсокетов)
        setInterval(() => loadChatMessages(box), 10000);
    });

    // Отправка сообщения
    document.addEventListener('submit', function (e) {
        const form = e.target;
        if (!form.classList.contains('claim-chat-form')) return;
        e.preventDefault();

        const input      = form.querySelector('input[name="message"]');
        const message     = input.value.trim();
        if (!message) return;

        const ownerRowId = form.dataset.ownerRowId;
        const entityType = form.dataset.entityType;
        const box        = document.getElementById(form.dataset.chatBox);
        const btn        = form.querySelector('button[type="submit"]');

        btn.disabled = true;

        fetch('{{ route("owner.claim.messages.send") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'Accept':       'application/json',
            },
            body: JSON.stringify({
                owner_row_id: ownerRowId,
                entity_type:  entityType,
                message:      message,
            }),
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                input.value = '';
                loadChatMessages(box);
            }
        })
        .finally(() => { btn.disabled = false; });
    });
})();
</script>
